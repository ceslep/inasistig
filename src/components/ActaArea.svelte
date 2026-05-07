<script lang="ts">
  import { onMount } from 'svelte'
  import { fade, fly, slide } from 'svelte/transition'
  import Swal from 'sweetalert2'
  import {
    Plus,
    Trash2,
    PenLine,
    Save,
    FileDown,
    Cloud,
    X,
    Info,
    AlertCircle,
    Loader2,
  } from '@lucide/svelte'

  import { saveActa, getDocentes, getMaterias } from '../../api/service'
  import {
    SPREADSHEET_ID_ACTA,
    WORKSHEET_TITLE_ACTA,
    INFO_ACTA,
    GOOGLE_CLIENT_ID,
  } from '../constants'
  import { theme } from '../lib/themeStore'
  import { docenteName } from '../lib/authStore'
  import { useDraftSave } from '../lib/useDraftSave'
  import { gdriveService } from '../lib/gdriveService'
  import { generateActaPdf, buildActaFileName } from '../lib/actaPdf'
  import {
    AREAS_ACADEMICAS,
    GRADOS,
    ROLES,
    ESTADOS_ACUERDO,
    VOTOS,
    type ActaReunion,
    type DesarrolloItem,
    type Rol,
    type EstadoAcuerdo,
    type Voto,
  } from '../lib/types/actaArea'

  import ModuleHeader from './ModuleHeader.svelte'
  import DriveFolderPicker from './DriveFolderPicker.svelte'
  import { Accordion, DatePicker, SelectField } from './anotador'

  interface Props {
    onBack: () => void
  }
  const { onBack }: Props = $props()

  const STORAGE_KEY = 'acta_area_draft'
  const draft = useDraftSave(STORAGE_KEY)

  const today = new Date().toLocaleDateString('en-CA')

  const createInitialActa = (): ActaReunion => ({
    id: `acta_${Date.now()}`,
    institucion: 'Institución Educativa Instituto Guática',
    areaAcademica: '',
    asignaturas: [],
    grados: [],
    fecha: today,
    horaInicio: '',
    horaFin: '',
    lugar: '',
    participantes: [{ nombre: $docenteName || '', rol: 'coordinador' }],
    ordenDia: [{ descripcion: '', responsable: '', tiempoMin: 15 }],
    desarrollo: [],
    acuerdos: [{ actividad: '', responsable: '', fechaLimite: '', estado: 'pendiente' }],
    proxima: { fecha: '', hora: '', lugar: '' },
    actaLeidaAprobada: false,
    firmaCoordinador: '',
    firmaSecretario: '',
    docenteCreador: $docenteName || '',
    createdAt: new Date().toISOString(),
  })

  let acta = $state<ActaReunion>(createInitialActa())

  let asignaturaInput = $state('')
  let asignaturaPickerOpen = $state(false)

  let docentesList = $state<string[]>([])
  let materiasList = $state<string[]>([])
  let isLoadingRefData = $state(false)

  const docenteOptions = $derived(
    docentesList.map((d) => ({ value: d, label: d })),
  )
  const materiaOptions = $derived(
    materiasList.map((m) => ({ value: m, label: m })),
  )
  const asignaturasDisponibles = $derived(
    materiasList.filter((m) => !acta.asignaturas.includes(m)),
  )

  let expanded = $state<Record<string, boolean>>({
    cabecera: true,
    participantes: true,
    orden: true,
    desarrollo: false,
    acuerdos: true,
    proxima: false,
    cierre: true,
  })
  const toggle = (k: string) => (expanded[k] = !expanded[k])

  let isLoading = $state(false)
  let isGeneratingPdf = $state(false)
  let pendingPdfBlob = $state<Blob | null>(null)
  let pendingPdfName = $state('')
  let showFolderPicker = $state(false)
  let showInfo = $state(true)

  // Firma canvas modal
  let firmaTarget = $state<'coordinador' | 'secretario' | { participanteIndex: number } | null>(
    null,
  )
  let firmaCanvas = $state<HTMLCanvasElement | null>(null)

  // Sync desarrollo with ordenDia changes
  $effect(() => {
    const ordenLen = acta.ordenDia.length
    const current = acta.desarrollo
    if (current.length === ordenLen) return
    const next: DesarrolloItem[] = []
    for (let i = 0; i < ordenLen; i++) {
      next.push(
        current[i] ?? {
          temaIndex: i,
          discusion: '',
          decisiones: '',
          votacion: 'na',
        },
      )
    }
    acta.desarrollo = next.map((d, i) => ({ ...d, temaIndex: i }))
  })

  // Autosave draft
  $effect(() => {
    const snapshot = $state.snapshot(acta)
    draft.saveDraft(snapshot as unknown as Record<string, unknown>)
  })

  const loadRefData = async () => {
    isLoadingRefData = true
    try {
      const [docentesData, materiasData] = await Promise.all([
        getDocentes(),
        getMaterias(),
      ])
      docentesList = Array.isArray(docentesData) ? docentesData : []
      materiasList = Array.isArray(materiasData)
        ? (materiasData as Array<{ materia?: string } | string>)
            .map((m) => (typeof m === 'string' ? m : m?.materia || ''))
            .filter(Boolean)
        : []
    } catch (e) {
      console.warn('No se pudieron cargar docentes/materias:', e)
    } finally {
      isLoadingRefData = false
    }
  }

  onMount(() => {
    try {
      const saved = localStorage.getItem(STORAGE_KEY)
      if (saved) {
        const parsed = JSON.parse(saved)
        if (parsed && parsed.id && parsed.institucion !== undefined) {
          acta = {
            ...createInitialActa(),
            ...parsed,
            participantes: parsed.participantes || [],
            ordenDia: parsed.ordenDia || [],
            desarrollo: parsed.desarrollo || [],
            acuerdos: parsed.acuerdos || [],
            proxima: parsed.proxima || { fecha: '', hora: '', lugar: '' },
          }
        }
      }
    } catch (e) {
      console.warn('No se pudo restaurar borrador acta:', e)
    }
    loadRefData()
  })

  // --- Asignaturas chips ---
  const addAsignatura = () => {
    const v = asignaturaInput.trim()
    if (!v) return
    if (!acta.asignaturas.includes(v)) acta.asignaturas = [...acta.asignaturas, v]
    asignaturaInput = ''
  }
  const removeAsignatura = (a: string) => {
    acta.asignaturas = acta.asignaturas.filter((x) => x !== a)
  }

  const toggleGrado = (g: string) => {
    if (acta.grados.includes(g)) acta.grados = acta.grados.filter((x) => x !== g)
    else acta.grados = [...acta.grados, g]
  }

  // --- Participantes ---
  const addParticipante = () => {
    acta.participantes = [...acta.participantes, { nombre: '', rol: 'miembro' }]
  }
  const removeParticipante = (i: number) => {
    acta.participantes = acta.participantes.filter((_, idx) => idx !== i)
  }

  // --- Orden del día ---
  const addOrden = () => {
    acta.ordenDia = [
      ...acta.ordenDia,
      { descripcion: '', responsable: '', tiempoMin: 10 },
    ]
  }
  const removeOrden = (i: number) => {
    if (acta.ordenDia.length <= 1) return
    acta.ordenDia = acta.ordenDia.filter((_, idx) => idx !== i)
  }

  // --- Acuerdos ---
  const addAcuerdo = () => {
    acta.acuerdos = [
      ...acta.acuerdos,
      { actividad: '', responsable: '', fechaLimite: '', estado: 'pendiente' },
    ]
  }
  const removeAcuerdo = (i: number) => {
    acta.acuerdos = acta.acuerdos.filter((_, idx) => idx !== i)
  }

  // --- Firma canvas ---
  const openFirma = (target: typeof firmaTarget) => {
    firmaTarget = target
  }
  const closeFirma = () => {
    firmaTarget = null
    firmaCanvas = null
  }

  $effect(() => {
    if (!firmaTarget || !firmaCanvas) return
    const canvas = firmaCanvas
    const ctx = canvas.getContext('2d')
    if (!ctx) return
    ctx.clearRect(0, 0, canvas.width, canvas.height)
    ctx.fillStyle = '#ffffff'
    ctx.fillRect(0, 0, canvas.width, canvas.height)
    ctx.strokeStyle = '#1e293b'
    ctx.lineWidth = 2.2
    ctx.lineCap = 'round'
    ctx.lineJoin = 'round'

    let drawing = false
    const pos = (e: MouseEvent | TouchEvent) => {
      const rect = canvas.getBoundingClientRect()
      const scaleX = canvas.width / rect.width
      const scaleY = canvas.height / rect.height
      const point = 'touches' in e ? e.touches[0] : (e as MouseEvent)
      return { x: (point.clientX - rect.left) * scaleX, y: (point.clientY - rect.top) * scaleY }
    }
    const start = (e: MouseEvent | TouchEvent) => {
      e.preventDefault()
      drawing = true
      const { x, y } = pos(e)
      ctx.beginPath()
      ctx.moveTo(x, y)
    }
    const move = (e: MouseEvent | TouchEvent) => {
      if (!drawing) return
      e.preventDefault()
      const { x, y } = pos(e)
      ctx.lineTo(x, y)
      ctx.stroke()
    }
    const stop = () => {
      drawing = false
    }

    canvas.addEventListener('mousedown', start)
    canvas.addEventListener('mousemove', move)
    canvas.addEventListener('mouseup', stop)
    canvas.addEventListener('mouseleave', stop)
    canvas.addEventListener('touchstart', start, { passive: false })
    canvas.addEventListener('touchmove', move, { passive: false })
    canvas.addEventListener('touchend', stop)

    return () => {
      canvas.removeEventListener('mousedown', start)
      canvas.removeEventListener('mousemove', move)
      canvas.removeEventListener('mouseup', stop)
      canvas.removeEventListener('mouseleave', stop)
      canvas.removeEventListener('touchstart', start)
      canvas.removeEventListener('touchmove', move)
      canvas.removeEventListener('touchend', stop)
    }
  })

  const limpiarFirma = () => {
    if (!firmaCanvas) return
    const ctx = firmaCanvas.getContext('2d')
    if (!ctx) return
    ctx.fillStyle = '#ffffff'
    ctx.fillRect(0, 0, firmaCanvas.width, firmaCanvas.height)
  }

  const guardarFirma = () => {
    if (!firmaCanvas || !firmaTarget) return
    const dataUrl = firmaCanvas.toDataURL('image/png')
    if (firmaTarget === 'coordinador') acta.firmaCoordinador = dataUrl
    else if (firmaTarget === 'secretario') acta.firmaSecretario = dataUrl
    else if (typeof firmaTarget === 'object' && 'participanteIndex' in firmaTarget) {
      acta.participantes[firmaTarget.participanteIndex].firma = dataUrl
    }
    closeFirma()
  }

  // --- Validation ---
  const validate = (): { ok: boolean; errores: string[] } => {
    const errores: string[] = []
    if (!acta.institucion.trim()) errores.push('Institución educativa')
    if (!acta.areaAcademica) errores.push('Área académica')
    if (!acta.fecha) errores.push('Fecha de reunión')
    if (!acta.horaInicio) errores.push('Hora de inicio')
    if (acta.grados.length === 0) errores.push('Al menos un grado')

    const hayCoordOSec = acta.participantes.some(
      (p) => (p.rol === 'coordinador' || p.rol === 'secretario') && p.nombre.trim(),
    )
    if (!hayCoordOSec)
      errores.push('Al menos un participante debe ser coordinador o secretario (con nombre)')

    const ordenValido = acta.ordenDia.some((o) => o.descripcion.trim())
    if (!ordenValido) errores.push('Al menos un tema en el orden del día')

    const acuerdoValido = acta.acuerdos.some((a) => a.actividad.trim())
    if (!acuerdoValido) errores.push('Al menos un acuerdo con actividad')

    if (!acta.actaLeidaAprobada)
      errores.push('Debe confirmar la lectura y aprobación del acta')

    return { ok: errores.length === 0, errores }
  }

  // --- Save ---
  const flattenToRow = (a: ActaReunion): string[][] => {
    const timestamp = new Date().toLocaleString('es-CO')
    return [
      [
        timestamp,
        a.id,
        a.docenteCreador,
        a.institucion,
        a.areaAcademica,
        a.asignaturas.join('; '),
        a.grados.join('; '),
        a.fecha,
        a.horaInicio,
        a.horaFin,
        a.lugar,
        JSON.stringify(a.participantes.map((p) => ({ nombre: p.nombre, rol: p.rol }))),
        JSON.stringify(a.ordenDia),
        JSON.stringify(a.desarrollo),
        JSON.stringify(a.acuerdos),
        JSON.stringify(a.proxima),
        a.actaLeidaAprobada ? 'SI' : 'NO',
        a.firmaCoordinador ? 'SI' : 'NO',
        a.firmaSecretario ? 'SI' : 'NO',
      ],
    ]
  }

  const handleSave = async () => {
    const { ok, errores } = validate()
    if (!ok) {
      await Swal.fire({
        icon: 'warning',
        title: 'Campos requeridos',
        html: `Complete los siguientes campos:<br/><br/><strong>${errores.join('<br/>')}</strong>`,
        confirmButtonColor: '#6366f1',
      })
      return
    }

    const confirm = await Swal.fire({
      title: '¿Confirmar registro del acta?',
      html: `
        <div class="text-left space-y-2 p-2 rounded-xl bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10" style="font-size:0.9rem;">
          <div><strong style="color:#6366f1;">Área:</strong> ${acta.areaAcademica}</div>
          <div><strong style="color:#6366f1;">Fecha:</strong> ${acta.fecha} ${acta.horaInicio}</div>
          <div><strong style="color:#6366f1;">Grados:</strong> ${acta.grados.join(', ')}</div>
          <div><strong style="color:#6366f1;">Participantes:</strong> ${acta.participantes.length}</div>
          <div><strong style="color:#6366f1;">Temas:</strong> ${acta.ordenDia.filter(o => o.descripcion.trim()).length}</div>
          <div><strong style="color:#6366f1;">Acuerdos:</strong> ${acta.acuerdos.filter(a => a.actividad.trim()).length}</div>
        </div>`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Sí, guardar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#4f46e5',
      cancelButtonColor: '#64748b',
      reverseButtons: true,
      background: $theme === 'light' ? '#fff' : '#1e293b',
      color: $theme === 'light' ? '#1e293b' : '#f1f5f9',
    })
    if (!confirm.isConfirmed) return

    isLoading = true
    try {
      const result = await saveActa({
        spreadsheetId: SPREADSHEET_ID_ACTA,
        worksheetTitle: WORKSHEET_TITLE_ACTA,
        datos: flattenToRow(acta),
      })
      const isOffline = result?.offline === true
      await Swal.fire({
        icon: isOffline ? 'warning' : 'success',
        title: isOffline ? 'Guardado offline' : '¡Acta registrada!',
        text: isOffline
          ? 'Acta guardada en cola. Se enviará al recuperar conexión.'
          : 'Acta registrada exitosamente.',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
        position: 'top-end',
        toast: true,
      })
      draft.clearDraft()
      acta = createInitialActa()
    } catch (e) {
      console.error('Error guardando acta:', e)
      await Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No fue posible guardar el acta. Intenta de nuevo.',
        confirmButtonColor: '#ef4444',
      })
    } finally {
      isLoading = false
    }
  }

  // --- PDF ---
  const handleGeneratePdf = async () => {
    const { ok, errores } = validate()
    if (!ok) {
      await Swal.fire({
        icon: 'warning',
        title: 'Acta incompleta',
        html: `Complete antes de generar PDF:<br/><br/><strong>${errores.join('<br/>')}</strong>`,
        confirmButtonColor: '#6366f1',
      })
      return
    }
    isGeneratingPdf = true
    try {
      const blob = await generateActaPdf($state.snapshot(acta) as ActaReunion)
      const fileName = buildActaFileName(acta)
      pendingPdfBlob = blob
      pendingPdfName = fileName

      const choice = await Swal.fire({
        icon: 'success',
        title: 'PDF generado',
        text: '¿Qué deseas hacer?',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Subir a Drive',
        denyButtonText: 'Descargar local',
        cancelButtonText: 'Cerrar',
        confirmButtonColor: '#4f46e5',
        denyButtonColor: '#0ea5e9',
        cancelButtonColor: '#64748b',
        background: $theme === 'light' ? '#fff' : '#1e293b',
        color: $theme === 'light' ? '#1e293b' : '#f1f5f9',
      })
      if (choice.isConfirmed) {
        showFolderPicker = true
      } else if (choice.isDenied) {
        downloadBlob(blob, fileName)
      }
    } catch (e) {
      console.error('Error generando PDF:', e)
      await Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No fue posible generar el PDF.',
        confirmButtonColor: '#ef4444',
      })
    } finally {
      isGeneratingPdf = false
    }
  }

  const downloadBlob = (blob: Blob, fileName: string) => {
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = fileName
    document.body.appendChild(a)
    a.click()
    a.remove()
    URL.revokeObjectURL(url)
  }

  const handleFolderSelected = async (folder: { id: string; name: string } | null) => {
    showFolderPicker = false
    if (!pendingPdfBlob) return
    if (!folder) {
      if (pendingPdfBlob) downloadBlob(pendingPdfBlob, pendingPdfName)
      pendingPdfBlob = null
      pendingPdfName = ''
      return
    }
    try {
      const result = await gdriveService.uploadFile(
        pendingPdfBlob,
        pendingPdfName,
        'application/pdf',
        GOOGLE_CLIENT_ID,
        folder.id,
      )
      if (result.success) {
        await Swal.fire({
          icon: 'success',
          title: 'Subido a Drive',
          text: 'El acta se guardó correctamente en Google Drive.',
          timer: 3000,
          showConfirmButton: false,
          toast: true,
          position: 'top-end',
        })
      } else {
        throw new Error(result.error || 'Error desconocido')
      }
    } catch (e) {
      console.error(e)
      await Swal.fire({
        icon: 'error',
        title: 'Error subiendo',
        text: 'Descarga local como respaldo.',
        confirmButtonColor: '#ef4444',
      })
      if (pendingPdfBlob) downloadBlob(pendingPdfBlob, pendingPdfName)
    } finally {
      pendingPdfBlob = null
      pendingPdfName = ''
    }
  }

  const areaOptions = AREAS_ACADEMICAS.map((a) => ({ value: a, label: a }))
  const rolOptions = ROLES.map((r) => ({ value: r.value, label: r.label }))
  const estadoOptions = ESTADOS_ACUERDO.map((e) => ({ value: e.value, label: e.label }))
  const votoOptions = VOTOS.map((v) => ({ value: v.value, label: v.label }))

  const fieldClass =
    'w-full px-3 py-2 rounded-lg bg-[rgb(var(--bg-secondary))] border border-[rgb(var(--border-primary))] text-[rgb(var(--text-primary))] focus:outline-none focus:border-[rgb(var(--accent-primary))] transition-colors'
</script>

<div class="min-h-screen bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))]">
  <ModuleHeader title="Acta de Reunión de Área" subtitle="Registro normativo Ley 115 / Decreto 1860" {onBack}>
    {#snippet actions()}
      <button
        type="button"
        onclick={handleGeneratePdf}
        disabled={isGeneratingPdf}
        class="hidden sm:flex items-center gap-2 px-3 py-2 rounded-xl bg-[rgb(var(--bg-secondary))] border border-[rgb(var(--border-primary))] hover:border-[rgb(var(--accent-primary))] disabled:opacity-50 transition-colors"
        title="Generar PDF"
      >
        {#if isGeneratingPdf}
          <Loader2 class="w-4 h-4 animate-spin" />
        {:else}
          <FileDown class="w-4 h-4" />
        {/if}
        <span class="text-sm">PDF</span>
      </button>
    {/snippet}
  </ModuleHeader>

  <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 space-y-6">
    {#if showInfo}
      <div
        in:fade={{ duration: 200 }}
        class="flex items-start gap-3 p-4 rounded-xl bg-indigo-500/10 border border-indigo-500/30"
      >
        <Info class="w-5 h-5 text-indigo-500 flex-shrink-0 mt-0.5" />
        <p class="text-sm text-[rgb(var(--text-secondary))] flex-1">{INFO_ACTA}</p>
        <button
          aria-label="Cerrar"
          onclick={() => (showInfo = false)}
          class="text-[rgb(var(--text-muted))] hover:text-[rgb(var(--text-primary))]"
        >
          <X class="w-4 h-4" />
        </button>
      </div>
    {/if}

    <!-- 1. Cabecera -->
    <section class="rounded-2xl bg-[rgb(var(--card-bg))] border border-[rgb(var(--card-border))] p-4 sm:p-6 space-y-4">
      <Accordion
        title="1. INFORMACIÓN GENERAL"
        bind:isExpanded={expanded.cabecera}
        onToggle={() => toggle('cabecera')}
        color="#6366f1"
      >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
          <label class="block">
            <span class="text-sm font-semibold mb-1 block">Institución educativa *</span>
            <input type="text" bind:value={acta.institucion} class={fieldClass} />
          </label>

          <SelectField
            id="area"
            label="Área académica *"
            bind:value={acta.areaAcademica}
            options={areaOptions}
            placeholder="Seleccione área"
            showSearch={false}
          />

          <div class="md:col-span-2">
            <span class="text-sm font-semibold mb-1 block">Asignaturas involucradas *</span>
            <button
              type="button"
              onclick={() => (asignaturaPickerOpen = true)}
              class="{fieldClass} flex items-center justify-between text-left"
            >
              <span class="truncate">
                {acta.asignaturas.length === 0
                  ? (isLoadingRefData ? 'Cargando…' : 'Selecciona una o varias asignaturas')
                  : `${acta.asignaturas.length} seleccionada${acta.asignaturas.length > 1 ? 's' : ''}`}
              </span>
              <Plus class="w-4 h-4 opacity-60" />
            </button>
            {#if acta.asignaturas.length > 0}
              <div class="flex flex-wrap gap-2 mt-2">
                {#each acta.asignaturas as a (a)}
                  <span
                    in:fade={{ duration: 150 }}
                    class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-indigo-500/15 text-indigo-600 dark:text-indigo-300 text-xs font-medium"
                  >
                    {a}
                    <button
                      type="button"
                      onclick={() => removeAsignatura(a)}
                      aria-label={`Quitar ${a}`}
                      class="hover:text-rose-500"
                    >
                      <X class="w-3 h-3" />
                    </button>
                  </span>
                {/each}
              </div>
            {/if}
          </div>

          <div class="md:col-span-2">
            <span class="text-sm font-semibold mb-2 block">Grados *</span>
            <div class="flex flex-wrap gap-2">
              {#each GRADOS as g (g)}
                {@const sel = acta.grados.includes(g)}
                <button
                  type="button"
                  onclick={() => toggleGrado(g)}
                  class="px-4 py-2 rounded-xl border-2 font-semibold text-sm transition-all"
                  class:bg-indigo-500={sel}
                  class:text-white={sel}
                  class:border-indigo-500={sel}
                  class:bg-transparent={!sel}
                  class:border-[rgb(var(--border-primary))]={!sel}
                  class:text-[rgb(var(--text-primary))]={!sel}
                >
                  {g}
                </button>
              {/each}
            </div>
          </div>

          <DatePicker id="fecha" label="Fecha de reunión *" bind:value={acta.fecha} />

          <label class="block">
            <span class="text-sm font-semibold mb-1 block">Hora inicio *</span>
            <input type="time" bind:value={acta.horaInicio} class={fieldClass} />
          </label>

          <label class="block">
            <span class="text-sm font-semibold mb-1 block">Hora fin</span>
            <input type="time" bind:value={acta.horaFin} class={fieldClass} />
          </label>

          <label class="block">
            <span class="text-sm font-semibold mb-1 block">Lugar</span>
            <input
              type="text"
              bind:value={acta.lugar}
              placeholder="Sala de profesores, biblioteca…"
              class={fieldClass}
            />
          </label>
        </div>
      </Accordion>
    </section>

    <!-- 2. Participantes -->
    <section class="rounded-2xl bg-[rgb(var(--card-bg))] border border-[rgb(var(--card-border))] p-4 sm:p-6 space-y-4">
      <Accordion
        title="2. PARTICIPANTES"
        bind:isExpanded={expanded.participantes}
        onToggle={() => toggle('participantes')}
        color="#0ea5e9"
      >
        <div class="space-y-3 pt-2">
          {#each acta.participantes as p, i (i)}
            <div
              in:fly={{ y: 6, duration: 200 }}
              class="grid grid-cols-12 gap-2 items-end p-3 rounded-xl bg-[rgb(var(--bg-secondary))]"
            >
              <div class="col-span-12 sm:col-span-6">
                <SelectField
                  id={`participante-${i}`}
                  label="Nombre completo"
                  value={p.nombre}
                  options={docenteOptions}
                  placeholder={isLoadingRefData ? 'Cargando docentes…' : 'Selecciona docente'}
                  isLoading={isLoadingRefData}
                  selectType="docente"
                  onchange={(v) => (acta.participantes[i].nombre = v)}
                />
              </div>
              <div class="col-span-7 sm:col-span-3">
                <SelectField
                  id={`rol-${i}`}
                  label="Rol"
                  bind:value={p.rol as string}
                  options={rolOptions}
                  showSearch={false}
                  onchange={(v) => (acta.participantes[i].rol = v as Rol)}
                />
              </div>
              <div class="col-span-3 sm:col-span-2 flex flex-col">
                <span class="text-xs font-semibold mb-1 opacity-80">Firma</span>
                <button
                  type="button"
                  onclick={() => openFirma({ participanteIndex: i })}
                  class="flex items-center justify-center gap-1 px-2 py-2 rounded-lg border border-dashed border-[rgb(var(--border-primary))] hover:border-indigo-500 text-xs"
                >
                  <PenLine class="w-3.5 h-3.5" />
                  {p.firma ? 'Refirmar' : 'Firmar'}
                </button>
              </div>
              <div class="col-span-2 sm:col-span-1 flex justify-end">
                <button
                  type="button"
                  onclick={() => removeParticipante(i)}
                  aria-label="Eliminar participante"
                  class="p-2 rounded-lg text-rose-500 hover:bg-rose-500/10"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
          {/each}
          <button
            type="button"
            onclick={addParticipante}
            class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl border-2 border-dashed border-[rgb(var(--border-primary))] hover:border-indigo-500 text-sm font-semibold transition-colors"
          >
            <Plus class="w-4 h-4" /> Agregar participante
          </button>
          <p class="text-xs text-[rgb(var(--text-muted))] flex items-start gap-1.5">
            <AlertCircle class="w-3.5 h-3.5 flex-shrink-0 mt-0.5" />
            Mínimo legal: un coordinador o secretario con nombre.
          </p>
        </div>
      </Accordion>
    </section>

    <!-- 3. Orden del día -->
    <section class="rounded-2xl bg-[rgb(var(--card-bg))] border border-[rgb(var(--card-border))] p-4 sm:p-6 space-y-4">
      <Accordion
        title="3. ORDEN DEL DÍA"
        bind:isExpanded={expanded.orden}
        onToggle={() => toggle('orden')}
        color="#10b981"
      >
        <div class="space-y-3 pt-2">
          {#each acta.ordenDia as o, i (i)}
            <div
              in:fly={{ y: 6, duration: 200 }}
              class="grid grid-cols-12 gap-2 items-end p-3 rounded-xl bg-[rgb(var(--bg-secondary))]"
            >
              <span class="col-span-12 text-xs font-bold text-emerald-500">Tema {i + 1}</span>
              <label class="col-span-12 sm:col-span-6 block">
                <span class="text-xs font-semibold mb-1 block opacity-80">Descripción</span>
                <input type="text" bind:value={o.descripcion} class={fieldClass} />
              </label>
              <div class="col-span-7 sm:col-span-4">
                <SelectField
                  id={`orden-resp-${i}`}
                  label="Responsable"
                  value={o.responsable}
                  options={docenteOptions}
                  placeholder={isLoadingRefData ? 'Cargando…' : 'Selecciona docente'}
                  isLoading={isLoadingRefData}
                  selectType="docente"
                  onchange={(v) => (acta.ordenDia[i].responsable = v)}
                />
              </div>
              <label class="col-span-4 sm:col-span-1 block">
                <span class="text-xs font-semibold mb-1 block opacity-80">Min</span>
                <input type="number" min="1" bind:value={o.tiempoMin} class={fieldClass} />
              </label>
              <div class="col-span-1 flex justify-end">
                <button
                  type="button"
                  onclick={() => removeOrden(i)}
                  disabled={acta.ordenDia.length <= 1}
                  aria-label="Eliminar tema"
                  class="p-2 rounded-lg text-rose-500 hover:bg-rose-500/10 disabled:opacity-30"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
          {/each}
          <button
            type="button"
            onclick={addOrden}
            class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl border-2 border-dashed border-[rgb(var(--border-primary))] hover:border-emerald-500 text-sm font-semibold transition-colors"
          >
            <Plus class="w-4 h-4" /> Agregar tema
          </button>
        </div>
      </Accordion>
    </section>

    <!-- 4. Desarrollo -->
    <section class="rounded-2xl bg-[rgb(var(--card-bg))] border border-[rgb(var(--card-border))] p-4 sm:p-6 space-y-4">
      <Accordion
        title="4. DESARROLLO DE LA REUNIÓN"
        bind:isExpanded={expanded.desarrollo}
        onToggle={() => toggle('desarrollo')}
        color="#f59e0b"
      >
        <div class="space-y-3 pt-2">
          {#each acta.desarrollo as d, i (i)}
            {@const tema = acta.ordenDia[i]}
            <div class="p-3 rounded-xl bg-[rgb(var(--bg-secondary))] space-y-2">
              <p class="text-xs font-bold text-amber-500">
                Tema {i + 1}: {tema?.descripcion || '(sin descripción)'}
              </p>
              <label class="block">
                <span class="text-xs font-semibold mb-1 block opacity-80">Discusión</span>
                <textarea bind:value={d.discusion} rows="2" class={fieldClass}></textarea>
              </label>
              <label class="block">
                <span class="text-xs font-semibold mb-1 block opacity-80">Decisiones tomadas</span>
                <textarea bind:value={d.decisiones} rows="2" class={fieldClass}></textarea>
              </label>
              <div class="max-w-xs">
                <SelectField
                  id={`voto-${i}`}
                  label="Votación"
                  bind:value={d.votacion as string}
                  options={votoOptions}
                  showSearch={false}
                  onchange={(v) => (acta.desarrollo[i].votacion = v as Voto)}
                />
              </div>
            </div>
          {:else}
            <p class="text-sm text-[rgb(var(--text-muted))] italic py-3 text-center">
              Agrega temas en el Orden del día para registrar el desarrollo.
            </p>
          {/each}
        </div>
      </Accordion>
    </section>

    <!-- 5. Acuerdos -->
    <section class="rounded-2xl bg-[rgb(var(--card-bg))] border border-[rgb(var(--card-border))] p-4 sm:p-6 space-y-4">
      <Accordion
        title="5. ACUERDOS Y COMPROMISOS"
        bind:isExpanded={expanded.acuerdos}
        onToggle={() => toggle('acuerdos')}
        color="#ef4444"
      >
        <div class="space-y-3 pt-2">
          {#each acta.acuerdos as a, i (i)}
            <div
              in:fly={{ y: 6, duration: 200 }}
              class="grid grid-cols-12 gap-2 items-end p-3 rounded-xl bg-[rgb(var(--bg-secondary))]"
            >
              <label class="col-span-12 sm:col-span-5 block">
                <span class="text-xs font-semibold mb-1 block opacity-80">Actividad</span>
                <input type="text" bind:value={a.actividad} class={fieldClass} />
              </label>
              <div class="col-span-6 sm:col-span-3">
                <SelectField
                  id={`acuerdo-resp-${i}`}
                  label="Responsable"
                  value={a.responsable}
                  options={docenteOptions}
                  placeholder={isLoadingRefData ? 'Cargando…' : 'Selecciona docente'}
                  isLoading={isLoadingRefData}
                  selectType="docente"
                  onchange={(v) => (acta.acuerdos[i].responsable = v)}
                />
              </div>
              <label class="col-span-6 sm:col-span-2 block">
                <span class="text-xs font-semibold mb-1 block opacity-80">Fecha límite</span>
                <input type="date" bind:value={a.fechaLimite} class={fieldClass} />
              </label>
              <div class="col-span-10 sm:col-span-1">
                <SelectField
                  id={`estado-${i}`}
                  label="Estado"
                  bind:value={a.estado as string}
                  options={estadoOptions}
                  showSearch={false}
                  onchange={(v) => (acta.acuerdos[i].estado = v as EstadoAcuerdo)}
                />
              </div>
              <div class="col-span-2 sm:col-span-1 flex justify-end">
                <button
                  type="button"
                  onclick={() => removeAcuerdo(i)}
                  aria-label="Eliminar acuerdo"
                  class="p-2 rounded-lg text-rose-500 hover:bg-rose-500/10"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
          {/each}
          <button
            type="button"
            onclick={addAcuerdo}
            class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl border-2 border-dashed border-[rgb(var(--border-primary))] hover:border-rose-500 text-sm font-semibold transition-colors"
          >
            <Plus class="w-4 h-4" /> Agregar acuerdo
          </button>
        </div>
      </Accordion>
    </section>

    <!-- 6. Próxima reunión -->
    <section class="rounded-2xl bg-[rgb(var(--card-bg))] border border-[rgb(var(--card-border))] p-4 sm:p-6 space-y-4">
      <Accordion
        title="6. PRÓXIMA REUNIÓN (OPCIONAL)"
        bind:isExpanded={expanded.proxima}
        onToggle={() => toggle('proxima')}
        color="#8b5cf6"
      >
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
          <label class="block">
            <span class="text-sm font-semibold mb-1 block">Fecha</span>
            <input type="date" bind:value={acta.proxima.fecha} class={fieldClass} />
          </label>
          <label class="block">
            <span class="text-sm font-semibold mb-1 block">Hora</span>
            <input type="time" bind:value={acta.proxima.hora} class={fieldClass} />
          </label>
          <label class="block">
            <span class="text-sm font-semibold mb-1 block">Lugar</span>
            <input type="text" bind:value={acta.proxima.lugar} class={fieldClass} />
          </label>
        </div>
      </Accordion>
    </section>

    <!-- 7. Cierre -->
    <section class="rounded-2xl bg-[rgb(var(--card-bg))] border border-[rgb(var(--card-border))] p-4 sm:p-6 space-y-4">
      <Accordion
        title="7. CIERRE Y VALIDACIÓN LEGAL"
        bind:isExpanded={expanded.cierre}
        onToggle={() => toggle('cierre')}
        color="#dc2626"
      >
        <div class="space-y-4 pt-2">
          <p class="text-xs italic text-[rgb(var(--text-muted))] p-3 rounded-lg bg-[rgb(var(--bg-secondary))]">
            "El presente acta se ajusta a lo dispuesto en la Ley 115 de 1994 y el Decreto 1860 de 1994."
          </p>

          <label class="flex items-start gap-3 cursor-pointer p-3 rounded-xl bg-[rgb(var(--bg-secondary))]">
            <input
              type="checkbox"
              bind:checked={acta.actaLeidaAprobada}
              class="mt-0.5 w-5 h-5 accent-indigo-600"
            />
            <span class="text-sm">Confirmo que el acta fue leída y aprobada por los participantes.</span>
          </label>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <button
              type="button"
              onclick={() => openFirma('coordinador')}
              class="p-4 rounded-xl border-2 border-dashed border-[rgb(var(--border-primary))] hover:border-indigo-500 transition-colors text-left"
            >
              <div class="flex items-center gap-2 mb-2">
                <PenLine class="w-4 h-4 text-indigo-500" />
                <span class="text-sm font-semibold">Firma Coordinador de área</span>
              </div>
              {#if acta.firmaCoordinador}
                <img src={acta.firmaCoordinador} alt="Firma coordinador" class="h-16 bg-white rounded" />
              {:else}
                <p class="text-xs text-[rgb(var(--text-muted))]">Toca para firmar</p>
              {/if}
            </button>

            <button
              type="button"
              onclick={() => openFirma('secretario')}
              class="p-4 rounded-xl border-2 border-dashed border-[rgb(var(--border-primary))] hover:border-indigo-500 transition-colors text-left"
            >
              <div class="flex items-center gap-2 mb-2">
                <PenLine class="w-4 h-4 text-indigo-500" />
                <span class="text-sm font-semibold">Firma Secretario ad hoc</span>
              </div>
              {#if acta.firmaSecretario}
                <img src={acta.firmaSecretario} alt="Firma secretario" class="h-16 bg-white rounded" />
              {:else}
                <p class="text-xs text-[rgb(var(--text-muted))]">Toca para firmar</p>
              {/if}
            </button>
          </div>
        </div>
      </Accordion>
    </section>

    <!-- Action bar -->
    <div class="sticky bottom-4 z-20 flex flex-col sm:flex-row gap-2 p-3 rounded-2xl bg-[rgb(var(--card-bg))] border border-[rgb(var(--card-border))] shadow-lg backdrop-blur-sm">
      <button
        type="button"
        onclick={handleGeneratePdf}
        disabled={isGeneratingPdf}
        class="flex-1 flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-[rgb(var(--bg-secondary))] border border-[rgb(var(--border-primary))] hover:border-indigo-500 disabled:opacity-50 font-semibold transition-colors"
      >
        {#if isGeneratingPdf}
          <Loader2 class="w-4 h-4 animate-spin" />
        {:else}
          <FileDown class="w-4 h-4" />
        {/if}
        Generar PDF
      </button>
      <button
        type="button"
        onclick={handleSave}
        disabled={isLoading}
        class="flex-1 flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-50 font-semibold transition-colors"
      >
        {#if isLoading}
          <Loader2 class="w-4 h-4 animate-spin" />
        {:else}
          <Save class="w-4 h-4" />
        {/if}
        Guardar acta
      </button>
    </div>
  </div>
</div>

<!-- Firma modal -->
{#if firmaTarget}
  <div
    in:fade={{ duration: 150 }}
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
    role="dialog"
    aria-modal="true"
  >
    <div
      in:fly={{ y: 20, duration: 200 }}
      class="w-full max-w-lg rounded-2xl bg-[rgb(var(--card-bg))] border border-[rgb(var(--card-border))] p-5 space-y-4"
    >
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold flex items-center gap-2">
          <PenLine class="w-5 h-5 text-indigo-500" /> Firma digital
        </h3>
        <button onclick={closeFirma} aria-label="Cerrar" class="p-1 rounded-lg hover:bg-black/5 dark:hover:bg-white/5">
          <X class="w-5 h-5" />
        </button>
      </div>
      <p class="text-xs text-[rgb(var(--text-muted))]">
        Dibuja la firma con el dedo o el ratón.
      </p>
      <canvas
        bind:this={firmaCanvas}
        width="500"
        height="180"
        class="w-full rounded-lg border border-[rgb(var(--border-primary))] bg-white touch-none"
      ></canvas>
      <div class="flex gap-2">
        <button
          type="button"
          onclick={limpiarFirma}
          class="flex-1 py-2 rounded-lg border border-[rgb(var(--border-primary))] hover:border-rose-500 text-sm font-semibold"
        >
          Limpiar
        </button>
        <button
          type="button"
          onclick={guardarFirma}
          class="flex-1 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 text-sm font-semibold"
        >
          Guardar firma
        </button>
      </div>
    </div>
  </div>
{/if}

<!-- Asignaturas picker modal -->
{#if asignaturaPickerOpen}
  <div
    in:fade={{ duration: 150 }}
    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 p-0 sm:p-4"
    role="dialog"
    aria-modal="true"
    onclick={(e) => { if (e.target === e.currentTarget) asignaturaPickerOpen = false }}
    onkeydown={(e) => { if (e.key === 'Escape') asignaturaPickerOpen = false }}
    tabindex="-1"
  >
    <div
      in:fly={{ y: 20, duration: 200 }}
      class="w-full max-w-lg max-h-[85vh] flex flex-col rounded-t-2xl sm:rounded-2xl bg-[rgb(var(--card-bg))] border border-[rgb(var(--card-border))]"
    >
      <div class="flex items-center justify-between p-4 border-b border-[rgb(var(--border-primary))]">
        <h3 class="text-base font-bold">Asignaturas involucradas</h3>
        <button
          type="button"
          onclick={() => (asignaturaPickerOpen = false)}
          aria-label="Cerrar"
          class="p-1 rounded-lg hover:bg-black/5 dark:hover:bg-white/5"
        >
          <X class="w-5 h-5" />
        </button>
      </div>
      <div class="p-3 border-b border-[rgb(var(--border-primary))]">
        <input
          type="text"
          bind:value={asignaturaInput}
          placeholder="Buscar o agregar nueva…"
          onkeydown={(e) => {
            if (e.key === 'Enter') {
              e.preventDefault()
              addAsignatura()
            }
          }}
          class={fieldClass}
        />
      </div>
      <div class="flex-1 overflow-auto">
        {#each materiasList.filter((m) => m.toLowerCase().includes(asignaturaInput.toLowerCase())) as m (m)}
          {@const checked = acta.asignaturas.includes(m)}
          <button
            type="button"
            onclick={() => {
              if (checked) removeAsignatura(m)
              else acta.asignaturas = [...acta.asignaturas, m]
            }}
            class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm hover:bg-indigo-500/10 transition-colors"
          >
            <span
              class="w-4 h-4 rounded border flex items-center justify-center text-white text-xs"
              class:bg-indigo-500={checked}
              class:border-indigo-500={checked}
              class:border-[rgb(var(--border-primary))]={!checked}
            >
              {checked ? '✓' : ''}
            </span>
            <span class="flex-1">{m}</span>
          </button>
        {:else}
          <p class="px-4 py-4 text-xs italic text-[rgb(var(--text-muted))]">
            {isLoadingRefData ? 'Cargando materias…' : 'Sin coincidencias. Enter para agregar manualmente.'}
          </p>
        {/each}
        {#if asignaturaInput.trim() && !materiasList.some((m) => m.toLowerCase() === asignaturaInput.trim().toLowerCase()) && !acta.asignaturas.includes(asignaturaInput.trim())}
          <button
            type="button"
            onclick={addAsignatura}
            class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border-t border-[rgb(var(--border-primary))]"
          >
            <Plus class="w-3.5 h-3.5" />
            Agregar "{asignaturaInput.trim()}"
          </button>
        {/if}
      </div>
      <div class="p-3 border-t border-[rgb(var(--border-primary))] flex items-center justify-between gap-2">
        <span class="text-xs text-[rgb(var(--text-muted))]">
          {acta.asignaturas.length} seleccionada{acta.asignaturas.length === 1 ? '' : 's'}
        </span>
        <button
          type="button"
          onclick={() => (asignaturaPickerOpen = false)}
          class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 text-sm font-semibold"
        >
          Listo
        </button>
      </div>
    </div>
  </div>
{/if}

<!-- Drive folder picker -->
{#if showFolderPicker}
  <DriveFolderPicker
    onSelect={handleFolderSelected}
    onClose={() => {
      showFolderPicker = false
      pendingPdfBlob = null
    }}
  />
{/if}
