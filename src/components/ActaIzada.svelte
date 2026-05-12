<script lang="ts">
  import { onMount } from 'svelte'
  import { fade, slide, fly } from 'svelte/transition'
  import Swal from 'sweetalert2'
  import {
    Plus, Trash2, Save, FileDown, Cloud, CloudOff, X, Info,
    AlertCircle, Loader2, Upload, Download, Check, Clock,
    Pencil, ChevronDown, ChevronUp, Flag, Users, BookOpen,
    ClipboardList, CheckSquare, PenLine, RotateCcw, Image,
  } from '@lucide/svelte'

  import { getDocentes, getMaterias, getEstudiantes } from '../../api/service'
  import {
    TIPOS_IZADA, MOMENTOS_DIA, GRADOS_BACHILLERATO,
    GRADOS_BACHILLERATO_MULTI, LUGARES_IZADA, AREAS_IZADA,
    ACTIVIDADES_PREDETERMINADAS, TEMAS_IZADA_COMUNES, HIMNOS,
    BASE_LEGAL_TEXT, MAX_FOTOS_IZADA,
    type ActaIzada, type ParticipanteActa, type ItemDesarrollo,
    type Conclusion, type Compromiso, type FirmaActa,
  } from '../lib/types/actaIzada'
  import { generateActaIzadaPdf, buildActaIzadaFileName } from '../lib/actaIzadaPdf'
  import { generateActaIzadaExcel, buildActaIzadaExcelName } from '../lib/actaIzadaExcel'
  import { gdriveService } from '../lib/gdriveService'
  import { GOOGLE_CLIENT_ID, SAVE_ACTA_IZADA_URL, SPREADSHEET_ID_IZADA, WORKSHEET_TITLE_IZADA } from '../constants'
  import { theme } from '../lib/themeStore'
  import { docenteName, findMatchingDocente } from '../lib/authStore'
  import ModuleHeader from './ModuleHeader.svelte'
  import DriveFolderPicker from './DriveFolderPicker.svelte'
  import DatePicker from './anotador/DatePicker.svelte'
  import TimeSelector from './TimeSelector.svelte'
  import { Accordion } from './anotador'

  interface Props {
    onBack: () => void
  }
  const { onBack }: Props = $props()

  // --- Tipos locales ---
  interface Estudiante {
    nombre: string
    grado: number | string
  }

  const STORAGE_KEY = 'acta_izada_draft'
  const today = new Date().toLocaleDateString('en-CA')

  // --- Estado de datos ---
  let estudiantes: Estudiante[] = $state([])
  let isLoadingEstudiantes = $state(false)

  const createInitialActa = (): ActaIzada => ({
    id: `izada_${Date.now()}`,
    institucion: 'Institución Educativa Instituto Guática',
    tipo: 'semanal',
    momento: 'matutino',
    fecha: today,
    horaInicio: '07:00',
    horaFin: '07:30',
    lugar: 'patio_principal',
    grados: ['6°'],
    grupos: ['A'],
    areaAcademica: '',
    areasAcademicas: [],
    temaPrincipal: '',
    subtema: '',
    participantes: [
      { nombre: $docenteName || '', rol: 'docente', cantidad: 1 },
    ],
    desarrollo: ACTIVIDADES_PREDETERMINADAS.map(a => ({ ...a })),
    conclusiones: [{ texto: '', cumplida: false }],
    compromisos: [{ actividad: '', responsable: '', fechaLimite: '', estado: 'pendiente' }],
    lema: '',
    himno: true,
    himnosRisaralda: [],
    promesa: true,
    discurso: false,
    minutoSilencio: true,
    actaLeidaAprobada: false,
    firmas: [
      { nombre: $docenteName || '', rol: 'docente' },
      { nombre: '', rol: 'director' },
    ],
    fotos: [],
    docenteCreador: $docenteName || '',
    createdAt: new Date().toISOString(),
  })

  let acta = $state<ActaIzada>(createInitialActa())
  let isLoading = $state(false)
  let isGeneratingPdf = $state(false)
  let isGeneratingExcel = $state(false)
  let showFolderPicker = $state(false)
  let pendingBlob = $state<Blob | null>(null)
  let pendingFileName = $state('')
  let pendingMimeType = $state('')
  let lastSaved = $state<Date | null>(null)
  let showInfo = $state(true)
  let firmaTarget = $state<string | null>(null)
  let firmaCanvas = $state<HTMLCanvasElement | null>(null)
  let showTemaSelector = $state(false)
  let temaSearch = $state('')

  // --- Fotos ---
  let fotos = $state<string[]>([])
  let isDraggingPhoto = $state(false)
  let fileInputRef = $state<HTMLInputElement>()

  const addFoto = (base64: string) => {
    if (fotos.length < MAX_FOTOS_IZADA) {
      fotos = [...fotos, base64]
    }
  }

  const removeFoto = (index: number) => {
    fotos = fotos.filter((_, i) => i !== index)
  }

  const handleFileSelect = (e: Event) => {
    const input = e.target as HTMLInputElement
    if (!input.files?.length) return
    processFiles(Array.from(input.files))
    input.value = ''
  }

  const processFiles = (files: File[]) => {
    files.forEach(file => {
      if (!file.type.startsWith('image/')) return
      const reader = new FileReader()
      reader.onload = (ev) => {
        const base64 = ev.target?.result as string
        addFoto(base64)
      }
      reader.readAsDataURL(file)
    })
  }

  const handleDrop = (e: DragEvent) => {
    e.preventDefault()
    isDraggingPhoto = false
    if (e.dataTransfer?.files) {
      processFiles(Array.from(e.dataTransfer.files))
    }
  }

  const handleDragOver = (e: DragEvent) => {
    e.preventDefault()
    isDraggingPhoto = true
  }

  const handleDragLeave = () => {
    isDraggingPhoto = false
  }

  // --- Grados dinámicos desde estudiantes ---
  let docenteNumber = $derived.by(() => {
    const match = ($docenteName || '').match(/-(\d+)$/)
    return match ? match[1] : null
  })

  let filteredGrados = $derived(
    docenteNumber
      ? [...new Set(estudiantes.map(e => e.grado.toString()))].filter(g =>
          g.startsWith(`${docenteNumber}-`)
        ).map(g => g.replace(/0(\d)$/, '°$1').replace(/(\d{1,2})0(\d)/, '$1°$2'))
      : [...new Set(estudiantes.map(e => e.grado.toString()))].filter(g =>
          !g.includes('-')
        ).map(g => g.replace(/0(\d)$/, '°$1').replace(/(\d{1,2})0(\d)/, '$1°$2'))
  )

  // --- Carga de datos ---
  const loadData = async () => {
    isLoadingEstudiantes = true
    try {
      const estudiantesData = await getEstudiantes()
      estudiantes = estudiantesData
    } catch (error) {
      console.error('Error cargando estudiantes:', error)
    } finally {
      isLoadingEstudiantes = false
    }
  }

  let expanded = $state<Record<string, boolean>>({
    info: true,
    participantes: true,
    desarrollo: true,
    himnos: true,
    conclusiones: false,
    compromisos: true,
    cierre: true,
  })
  const toggle = (k: string) => (expanded[k] = !expanded[k])

  const filteredTemas = $derived(
    TEMAS_IZADA_COMUNES.filter(t =>
      t.toLowerCase().includes(temaSearch.toLowerCase())
    ).slice(0, 10)
  )

  const filteredActividades = $derived(
    TEMAS_IZADA_COMUNES.filter(a =>
      a.toLowerCase().includes(temaSearch.toLowerCase())
    ).slice(0, 10)
  )

  // Auto-save draft
  let saveTimeout: ReturnType<typeof setTimeout> | null = null
  $effect(() => {
    const data = JSON.stringify(acta)
    if (saveTimeout) clearTimeout(saveTimeout)
    saveTimeout = setTimeout(() => {
      try {
        localStorage.setItem(STORAGE_KEY, data)
      } catch (e) {
        console.warn('No se pudo guardar el borrador:', e)
      }
    }, 2000)
  })

  // Sync fotos with acta.fotos
  $effect(() => {
    acta.fotos = fotos
  })

  onMount(() => {
    loadData()
    try {
      const saved = localStorage.getItem(STORAGE_KEY)
      if (saved) {
        const draft = JSON.parse(saved) as ActaIzada
        const hoursSinceSave = (Date.now() - new Date(draft.createdAt).getTime()) / (1000 * 60 * 60)
        if (hoursSinceSave < 24 && draft.id.startsWith('izada_')) {
          acta = draft
          fotos = draft.fotos || []
        }
      }
    } catch (e) {
      console.warn('No se pudo cargar el borrador:', e)
    }
  })

  // --- Participantes ---
  const addParticipante = () => {
    acta.participantes = [...acta.participantes, { nombre: '', rol: 'estudiante', cantidad: 1 }]
  }
  const removeParticipante = (i: number) => {
    if (acta.participantes.length > 1) {
      acta.participantes = acta.participantes.filter((_, idx) => idx !== i)
    }
  }

  // --- Desarrollo ---
  const addItemDesarrollo = () => {
    const lastOrden = acta.desarrollo.length > 0 ? acta.desarrollo[acta.desarrollo.length - 1].orden : 0
    acta.desarrollo = [
      ...acta.desarrollo,
      { orden: lastOrden + 1, actividad: '', descripcion: '', responsable: '', tiempoMin: 5 },
    ]
  }
  const removeItemDesarrollo = (i: number) => {
    if (acta.desarrollo.length > 1) {
      acta.desarrollo = acta.desarrollo.filter((_, idx) => idx !== i)
    }
  }

  // --- Conclusiones ---
  const addConclusion = () => {
    acta.conclusiones = [...acta.conclusiones, { texto: '', cumplida: false }]
  }
  const removeConclusion = (i: number) => {
    if (acta.conclusiones.length > 1) {
      acta.conclusiones = acta.conclusiones.filter((_, idx) => idx !== i)
    }
  }

  // --- Compromisos ---
  const addCompromiso = () => {
    acta.compromisos = [
      ...acta.compromisos,
      { actividad: '', responsable: '', fechaLimite: '', estado: 'pendiente' },
    ]
  }
  const removeCompromiso = (i: number) => {
    if (acta.compromisos.length > 1) {
      acta.compromisos = acta.compromisos.filter((_, idx) => idx !== i)
    }
  }

  // --- Firmas ---
  const openFirma = (rol: string) => {
    firmaTarget = rol
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
    const stop = () => { drawing = false }

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

  const guardarFirma = () => {
    if (!firmaCanvas || !firmaTarget) return
    const dataUrl = firmaCanvas.toDataURL('image/png')
    const firmaIdx = acta.firmas.findIndex(f => f.rol === firmaTarget)
    if (firmaIdx >= 0) {
      acta.firmas[firmaIdx].firma = dataUrl
    }
    closeFirma()
  }

  // --- Validation ---
  const validate = (): { ok: boolean; errores: string[] } => {
    const errores: string[] = []
    if (!acta.fecha) errores.push('Fecha de la ceremonia')
    if (!acta.horaInicio) errores.push('Hora de inicio')
    if (!acta.temaPrincipal) errores.push('Tema principal')
    if (acta.grados.length === 0) errores.push('Al menos un grado')
    if (acta.desarrollo.length === 0) errores.push('Al menos una actividad en el desarrollo')
    if (!acta.actaLeidaAprobada) errores.push('Debe confirmar la lectura y aprobación del acta')
    return { ok: errores.length === 0, errores }
  }

  // --- Save to Google Sheets (PHP backend) ---
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
      title: '¿Guardar acta de izadas?',
      html: `<p>Se enviará el registro a la hoja de cálculo de Google Sheets.</p>`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#F59E0B',
      confirmButtonText: 'Sí, guardar',
      cancelButtonText: 'Cancelar',
    })
    if (!confirm.isConfirmed) return

    isLoading = true
    try {
      const timestamp = new Date().toLocaleString('es-CO')
      const row = [
        timestamp,
        acta.id,
        acta.docenteCreador,
        acta.institucion,
        acta.fecha,
        acta.horaInicio,
        acta.horaFin,
        acta.lugar,
        acta.tipo,
        acta.momento,
        acta.grados.join(', '),
        acta.grupos.join(', '),
        acta.areasAcademicas.join(', '),
        acta.temaPrincipal,
        acta.subtema || '',
        JSON.stringify(acta.participantes),
        JSON.stringify(acta.desarrollo),
        JSON.stringify(acta.conclusiones),
        JSON.stringify(acta.compromisos),
        acta.promesa ? 'Sí' : 'No',
        acta.discurso ? 'Sí' : 'No',
        acta.minutoSilencio ? 'Sí' : 'No',
        acta.himnosRisaralda.join(', '),
        acta.actaLeidaAprobada ? 'SI' : 'NO',
        new Date().toISOString(),
        acta.fotos?.[0] || '',
        acta.fotos?.[1] || '',
        acta.fotos?.[2] || '',
        acta.fotos?.[3] || '',
      ]

      const response = await fetch(SAVE_ACTA_IZADA_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ datos: [row], spreadsheetId: SPREADSHEET_ID_IZADA, worksheetTitle: WORKSHEET_TITLE_IZADA }),
      })

      if (!response.ok) throw new Error('Error en la respuesta del servidor')

      lastSaved = new Date()
      localStorage.removeItem(STORAGE_KEY)
      await Swal.fire({
        icon: 'success',
        title: '¡Guardado!',
        text: 'El acta de izadas se ha registrado exitosamente.',
        confirmButtonColor: '#F59E0B',
        timer: 3000,
        timerProgressBar: true,
      })
    } catch (err) {
      console.error('Error al guardar:', err)
      await Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudo guardar el acta. Verifica tu conexión.',
        confirmButtonColor: '#ef4444',
      })
    } finally {
      isLoading = false
    }
  }

  // --- Generate PDF ---
  const handleGeneratePdf = async () => {
    const { ok, errores } = validate()
    if (!ok) {
      await Swal.fire({
        icon: 'warning',
        title: 'Campos requeridos',
        html: `Complete los siguientes campos para generar el PDF:<br/><br/><strong>${errores.join('<br/>')}</strong>`,
        confirmButtonColor: '#6366f1',
      })
      return
    }
    isGeneratingPdf = true
    try {
      const blob = await generateActaIzadaPdf(acta)
      const fileName = buildActaIzadaFileName(acta)
      const url = URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = fileName
      a.click()
      URL.revokeObjectURL(url)
    } catch (err) {
      console.error('Error generando PDF:', err)
      await Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo generar el PDF.', confirmButtonColor: '#ef4444' })
    } finally {
      isGeneratingPdf = false
    }
  }

  // --- Generate Excel ---
  const handleGenerateExcel = async () => {
    const { ok, errores } = validate()
    if (!ok) {
      await Swal.fire({
        icon: 'warning',
        title: 'Campos requeridos',
        html: `Complete los siguientes campos para generar el Excel:<br/><br/><strong>${errores.join('<br/>')}</strong>`,
        confirmButtonColor: '#6366f1',
      })
      return
    }
    isGeneratingExcel = true
    try {
      const blob = await generateActaIzadaExcel(acta)
      const fileName = buildActaIzadaExcelName(acta)
      const url = URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = fileName
      a.click()
      URL.revokeObjectURL(url)
    } catch (err) {
      console.error('Error generando Excel:', err)
      await Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo generar el Excel.', confirmButtonColor: '#ef4444' })
    } finally {
      isGeneratingExcel = false
    }
  }

  // --- Save to Google Drive ---
  const handleSaveToDrive = async () => {
    if (pendingBlob && pendingFileName && pendingMimeType) {
      showFolderPicker = true
    } else {
      const { ok, errores } = validate()
      if (!ok) {
        await Swal.fire({
          icon: 'warning',
          title: 'Campos requeridos',
          html: `Complete los siguientes campos para guardar en Drive:<br/><br/><strong>${errores.join('<br/>')}</strong>`,
          confirmButtonColor: '#6366f1',
        })
        return
      }
      try {
        const blob = await generateActaIzadaPdf(acta)
        pendingBlob = blob
        pendingFileName = buildActaIzadaFileName(acta)
        pendingMimeType = 'application/pdf'
        showFolderPicker = true
      } catch (err) {
        await Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo preparar el archivo para Drive.', confirmButtonColor: '#ef4444' })
      }
    }
  }

  const handleDriveSelect = async (folder: { id: string; name: string } | null) => {
    showFolderPicker = false
    if (!folder || !pendingBlob) return

    isLoading = true
    try {
      const result = await gdriveService.uploadFile(
        pendingBlob,
        pendingFileName,
        pendingMimeType,
        GOOGLE_CLIENT_ID,
        folder.id,
      )
      if (result.success) {
        await Swal.fire({
          icon: 'success',
          title: '¡Guardado en Drive!',
          text: `El archivo ${pendingFileName} se ha guardado en ${folder.name}.`,
          confirmButtonColor: '#F59E0B',
          timer: 3000,
          timerProgressBar: true,
        })
      } else {
        throw new Error(result.error || 'Error desconocido')
      }
    } catch (err) {
      console.error('Error guardando en Drive:', err)
      await Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo guardar en Google Drive.', confirmButtonColor: '#ef4444' })
    } finally {
      isLoading = false
      pendingBlob = null
      pendingFileName = ''
      pendingMimeType = ''
    }
  }
</script>

<ModuleHeader title="Acta de Izada de Bandera" subtitle="Ceremonia cívica - Formación patriótica" {onBack}>
  {#snippet actions()}
    <div class="flex items-center gap-2">
      <button
        onclick={handleGeneratePdf}
        disabled={isGeneratingPdf}
        class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium bg-[rgb(var(--bg-secondary))] border border-[rgb(var(--border-primary))] hover:border-[rgb(var(--accent-primary))] transition-all disabled:opacity-50"
        title="Generar PDF"
      >
        {#if isGeneratingPdf}
          <Loader2 class="w-4 h-4 animate-spin" />
        {:else}
          <FileDown class="w-4 h-4" />
        {/if}
        <span class="hidden sm:inline">PDF</span>
      </button>
      <button
        onclick={handleGenerateExcel}
        disabled={isGeneratingExcel}
        class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium bg-[rgb(var(--bg-secondary))] border border-[rgb(var(--border-primary))] hover:border-[rgb(var(--accent-primary))] transition-all disabled:opacity-50"
        title="Generar Excel"
      >
        {#if isGeneratingExcel}
          <Loader2 class="w-4 h-4 animate-spin" />
        {:else}
          <BookOpen class="w-4 h-4" />
        {/if}
        <span class="hidden sm:inline">Excel</span>
      </button>
      <button
        onclick={handleSaveToDrive}
        disabled={isLoading}
        class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white transition-all disabled:opacity-50"
        title="Guardar en Google Drive"
      >
        {#if isLoading}
          <Loader2 class="w-4 h-4 animate-spin" />
        {:else}
          <Cloud class="w-4 h-4" />
        {/if}
        <span class="hidden sm:inline">Drive</span>
      </button>
      <button
        onclick={handleSave}
        disabled={isLoading}
        class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-bold bg-amber-500 hover:bg-amber-600 text-white transition-all disabled:opacity-50"
        title="Guardar en Sheets"
      >
        {#if isLoading}
          <Loader2 class="w-4 h-4 animate-spin" />
        {:else}
          <Save class="w-4 h-4" />
        {/if}
        <span class="hidden sm:inline">Guardar</span>
      </button>
    </div>
  {/snippet}
</ModuleHeader>

<div class="mx-auto max-w-4xl px-4 py-6 space-y-4">

  {#if showInfo}
    <div class="flex items-start gap-3 p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
      <Info class="w-5 h-5 text-amber-600 mt-0.5 shrink-0" />
      <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
          Base legal: Decreto 1860 de 1994, artículos 36 al 40
        </p>
        <p class="text-xs text-amber-700 dark:text-amber-300 mt-1">
          La izadas de bandera es obligatoria en todas las instituciones educativas de Colombia. Este formato permite documentar la ceremonia semanal y generar los reportes correspondientes.
        </p>
      </div>
      <button onclick={() => showInfo = false} class="p-1 hover:bg-amber-100 dark:hover:bg-amber-900/40 rounded">
        <X class="w-4 h-4 text-amber-600" />
      </button>
    </div>
  {/if}

  {#if lastSaved}
    <div class="flex items-center gap-2 text-sm text-emerald-600 dark:text-emerald-400">
      <Check class="w-4 h-4" />
      <span>Guardado el {lastSaved.toLocaleString('es-CO')}</span>
    </div>
  {/if}

  <!-- HIMNOS -->
  <Accordion title="Himnos" isExpanded={expanded.himnos ?? true} onToggle={() => toggle('himnos')} color="#8B5CF6">
    <div class="space-y-3">
      <p class="text-xs text-[rgb(var--text-muted))]">
        Seleccione los himnos que fueron cantados en la ceremonia
      </p>
      <div class="flex flex-wrap gap-3">
        {#each HIMNOS as h}
          {@const isSelected = acta.himnosRisaralda.includes(h.value)}
          <button
            type="button"
            onclick={() => {
              if (isSelected) {
                acta.himnosRisaralda = acta.himnosRisaralda.filter(x => x !== h.value)
              } else {
                acta.himnosRisaralda = [...acta.himnosRisaralda, h.value]
              }
            }}
            class="px-4 py-2.5 rounded-xl border-2 text-sm font-semibold transition-all {isSelected
              ? 'bg-violet-600 text-white border-violet-600 shadow-md'
              : 'bg-[rgb(var--bg-secondary))] border-[rgb(var--border-primary))] text-[rgb(var--text-primary))] hover:border-violet-400 hover:bg-violet-50 dark:hover:bg-violet-900/20'}"
            title={h.label}
          >
            <span class="block">{h.abrev}</span>
            <span class="block text-xs opacity-80 mt-0.5">{h.label}</span>
          </button>
        {/each}
      </div>
      {#if acta.himnosRisaralda.length > 0}
        <p class="text-xs text-violet-600 dark:text-violet-400 font-medium">
          {acta.himnosRisaralda.length} himno(s) seleccionado(s)
        </p>
      {/if}
    </div>
  </Accordion>

  <!-- INFO GENERAL -->
  <Accordion title="Información General" isExpanded={expanded.info} onToggle={() => toggle('info')} color="#F59E0B">
<div class="space-y-4">
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-medium text-[rgb(var--text-muted))] mb-1" for="fecha-izada">Fecha *</label>
          <DatePicker id="fecha-izada" bind:value={acta.fecha} />
        </div>
<div>
          <span class="block text-xs font-medium text-[rgb(var--text-muted))] mb-1">Hora inicio *</span>
          <TimeSelector bind:value={acta.horaInicio} label="Hora inicio" />
        </div>
        <div>
          <span class="block text-xs font-medium text-[rgb(var--text-muted))] mb-1">Hora fin</span>
          <TimeSelector bind:value={acta.horaFin} label="Hora fin" />
        </div>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <div>
<label class="block text-xs font-medium text-[rgb(var--text-muted))] mb-1" for="tipo-izada">Tipo</label>
          <select
            id="tipo-izada"
            bind:value={acta.tipo}
            class="w-full px-3 py-2 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-secondary))] text-[rgb(var--text-primary))] text-sm"
          >
            {#each TIPOS_IZADA as t}
              <option value={t.value}>{t.label} - {t.descripcion}</option>
            {/each}
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-[rgb(var--text-muted))] mb-1" for="momento-izada">Momento</label>
          <select
            id="momento-izada"
            bind:value={acta.momento}
            class="w-full px-3 py-2 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-secondary))] text-[rgb(var--text-primary))] text-sm"
          >
            {#each MOMENTOS_DIA as m}
              <option value={m.value}>{m.label}</option>
            {/each}
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-[rgb(var--text-muted))] mb-1" for="lugar-izada">Lugar</label>
          <select
            id="lugar-izada"
            bind:value={acta.lugar}
            class="w-full px-3 py-2 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-secondary))] text-[rgb(var--text-primary))] text-sm"
          >
            {#each LUGARES_IZADA as l}
              <option value={l.value}>{l.label}</option>
            {/each}
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <span class="block text-xs font-medium text-[rgb(var--text-muted))] mb-2">Grados *</span>
          {#if isLoadingEstudiantes}
            <div class="flex items-center gap-2 text-sm text-[rgb(var--text-muted))]">
              <Loader2 class="w-4 h-4 animate-spin" />
              <span>Cargando grados...</span>
            </div>
          {:else if filteredGrados.length === 0}
            <div class="text-sm text-[rgb(var--text-muted))]">Sin grados disponibles</div>
          {:else}
            <div class="flex flex-wrap gap-2">
              {#each filteredGrados as grado}
                {@const isSelected = acta.grados.includes(grado)}
                <button
                  type="button"
                  onclick={() => {
                    if (isSelected) {
                      acta.grados = acta.grados.filter(gr => gr !== grado)
                    } else {
                      acta.grados = [...acta.grados, grado]
                    }
                  }}
                  class="px-3 py-1.5 rounded-lg border text-sm font-medium transition-all {isSelected
                    ? 'bg-amber-500 text-white border-amber-500'
                    : 'bg-[rgb(var--bg-secondary))] border-[rgb(var--border-primary))] text-[rgb(var--text-primary))] hover:border-amber-400'}"
                >
                  {grado}
                </button>
              {/each}
            </div>
          {/if}
        </div>
        <div>
          <span class="block text-xs font-medium text-[rgb(var--text-muted))] mb-2">Grupos</span>
          <div class="flex flex-wrap gap-2">
            {#each ['A', 'B', 'C', 'D', 'E'] as grupo}
              {@const isSelected = acta.grupos.includes(grupo)}
              <button
                type="button"
                onclick={() => {
                  if (isSelected) {
                    acta.grupos = acta.grupos.filter(gr => gr !== grupo)
                  } else {
                    acta.grupos = [...acta.grupos, grupo]
                  }
                }}
                class="px-3 py-1.5 rounded-lg border text-sm font-medium transition-all {isSelected
                  ? 'bg-amber-500 text-white border-amber-500'
                  : 'bg-[rgb(var--bg-secondary))] border-[rgb(var--border-primary))] text-[rgb(var--text-primary))] hover:border-amber-400'}"
              >
                {grupo}
              </button>
            {/each}
          </div>
        </div>
        <div>
          <span class="block text-xs font-medium text-[rgb(var--text-muted))] mb-2">Áreas Académicas</span>
          <div class="flex flex-wrap gap-2">
            {#each AREAS_IZADA as area}
              {@const isSelected = acta.areasAcademicas.includes(area)}
              <button
                type="button"
                onclick={() => {
                  if (isSelected) {
                    acta.areasAcademicas = acta.areasAcademicas.filter(a => a !== area)
                  } else {
                    acta.areasAcademicas = [...acta.areasAcademicas, area]
                  }
                }}
                class="px-3 py-1.5 rounded-lg border text-sm font-medium transition-all {isSelected
                  ? 'bg-amber-500 text-white border-amber-500'
                  : 'bg-[rgb(var--bg-secondary))] border-[rgb(var--border-primary))] text-[rgb(var--text-primary))] hover:border-amber-400'}"
              >
                {area}
              </button>
            {/each}
          </div>
        </div>
      </div>

      <div>
        <label class="block text-xs font-medium text-[rgb(var--text-muted))] mb-1" for="tema-principal-izada">Tema Principal *</label>
        <div class="relative">
          <input
            id="tema-principal-izada"
            type="text"
            bind:value={acta.temaPrincipal}
            onfocus={() => showTemaSelector = true}
            placeholder="Seleccione o escriba el tema"
            class="w-full px-3 py-2 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-secondary))] text-[rgb(var--text-primary))] text-sm"
          />
          {#if showTemaSelector}
            <div class="absolute z-50 mt-1 w-full bg-[rgb(var(--bg-primary))] border border-[rgb(var--border-primary))] rounded-lg shadow-lg max-h-48 overflow-y-auto">
              <input
                type="text"
                bind:value={temaSearch}
                placeholder="Buscar tema..."
                class="w-full px-3 py-2 border-b border-[rgb(var--border-primary))] text-sm"
              />
              {#each filteredTemas as tema}
                <button
                  type="button"
                  onclick={() => { acta.temaPrincipal = tema; showTemaSelector = false; }}
                  class="w-full px-3 py-2 text-left text-sm hover:bg-[rgb(var--bg-secondary))] transition-colors"
                >
                  {tema}
                </button>
              {/each}
            </div>
          {/if}
        </div>
      </div>

      <div>
        <label class="block text-xs font-medium text-[rgb(var--text-muted))] mb-1" for="subtema-izada">Subtema</label>
        <input
          id="subtema-izada"
          type="text"
          bind:value={acta.subtema}
          placeholder="Subtema o aspekto específico"
          class="w-full px-3 py-2 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-secondary))] text-[rgb(var--text-primary))] text-sm"
        />
      </div>

      <div class="space-y-3">
        <label class="flex items-center gap-2 text-sm cursor-pointer">
          <input type="checkbox" bind:checked={acta.promesa} class="w-4 h-4 rounded accent-amber-500" />
          <span>Promesa a la Bandera</span>
        </label>
        <label class="flex items-center gap-2 text-sm cursor-pointer">
          <input type="checkbox" bind:checked={acta.discurso} class="w-4 h-4 rounded accent-amber-500" />
          <span>Discurso/Palabras alusivas</span>
        </label>
        <label class="flex items-center gap-2 text-sm cursor-pointer">
          <input type="checkbox" bind:checked={acta.minutoSilencio} class="w-4 h-4 rounded accent-amber-500" />
          <span>Minuto de silencio</span>
        </label>
      </div>

      
    </div>
  </Accordion>

  <!-- PARTICIPANTES -->
  <Accordion title="Participantes" isExpanded={expanded.participantes} onToggle={() => toggle('participantes')} color="#F59E0B">
    <div class="space-y-3">
      {#each acta.participantes as participante, i (i)}
        <div class="flex items-center gap-2 p-3 rounded-lg bg-[rgb(var--bg-secondary))] border border-[rgb(var--border-primary))]">
          <div class="flex-1 grid grid-cols-3 gap-2">
            <input
              type="text"
              bind:value={participante.nombre}
              placeholder="Nombre o grupo"
              class="px-3 py-2 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-primary))] text-sm"
            />
            <select
              bind:value={participante.rol}
              class="px-3 py-2 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-primary))] text-sm"
            >
              <option value="docente">Docente</option>
              <option value="estudiante">Estudiante(s)</option>
              <option value="coordinador">Coordinador</option>
              <option value="director">Director</option>
            </select>
            {#if participante.rol === 'estudiante'}
              <input
                type="number"
                bind:value={participante.cantidad}
                min="1"
                placeholder="Cant."
                class="px-3 py-2 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-primary))] text-sm w-20"
              />
            {:else}
              <div></div>
            {/if}
          </div>
          <button
            type="button"
            onclick={() => removeParticipante(i)}
            class="p-2 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-colors"
          >
            <Trash2 class="w-4 h-4" />
          </button>
        </div>
      {/each}
      <button
        type="button"
        onclick={addParticipante}
        class="flex items-center gap-2 px-4 py-2 rounded-lg border border-dashed border-[rgb(var--border-primary))] text-sm text-[rgb(var--text-muted))] hover:border-amber-500 hover:text-amber-600 transition-colors"
      >
        <Plus class="w-4 h-4" />
        Agregar participante
      </button>
    </div>
  </Accordion>

  <!-- DESARROLLO -->
  <Accordion title="Desarrollo de la Ceremonia" isExpanded={expanded.desarrollo} onToggle={() => toggle('desarrollo')} color="#F59E0B">
    <div class="space-y-3">
      <p class="text-xs text-[rgb(var--text-muted))]">
        Actividades de la izadas de bandera conforme al Decreto 1860 de 1994.
      </p>
      {#each acta.desarrollo as item, i (i)}
        <div class="p-3 rounded-lg bg-[rgb(var--bg-secondary))] border border-[rgb(var--border-primary))]">
          <div class="flex items-center gap-2 mb-2">
            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 text-xs font-bold">
              {item.orden}
            </span>
            <input
              type="text"
              bind:value={item.actividad}
              placeholder="Nombre de la actividad"
              class="flex-1 px-3 py-1.5 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-primary))] text-sm font-medium"
            />
            <input
              type="number"
              bind:value={item.tiempoMin}
              min="1"
              max="60"
              class="w-16 px-2 py-1.5 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-primary))] text-sm text-center"
            />
            <span class="text-xs text-[rgb(var--text-muted))]">min</span>
            {#if acta.desarrollo.length > 1}
              <button
                type="button"
                onclick={() => removeItemDesarrollo(i)}
                class="p-1.5 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg"
              >
                <Trash2 class="w-3.5 h-3.5" />
              </button>
            {/if}
          </div>
          <textarea
            bind:value={item.descripcion}
            placeholder="Descripción de la actividad"
            rows="2"
            class="w-full px-3 py-1.5 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-primary))] text-sm resize-none"
          ></textarea>
          <input
            type="text"
            bind:value={item.responsable}
            placeholder="Responsable"
            class="mt-1 w-full px-3 py-1.5 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-primary))] text-sm"
          />
        </div>
      {/each}
      <button
        type="button"
        onclick={addItemDesarrollo}
        class="flex items-center gap-2 px-4 py-2 rounded-lg border border-dashed border-[rgb(var--border-primary))] text-sm text-[rgb(var--text-muted))] hover:border-amber-500 hover:text-amber-600 transition-colors"
      >
        <Plus class="w-4 h-4" />
        Agregar actividad
      </button>

      <!-- Evidencia Fotográfica -->
      <div class="mt-5 pt-4 border-t border-[rgb(var--border-primary))]">
        <div class="flex items-center justify-between mb-3">
          <span class="block text-xs font-semibold text-[rgb(var--text-muted))] uppercase tracking-wide">
            Evidencia Fotográfica (máx {MAX_FOTOS_IZADA})
          </span>
          <span class="text-xs text-[rgb(var--text-muted))]">{fotos.length}/{MAX_FOTOS_IZADA}</span>
        </div>

        <!-- Drop zone -->
        <div
          class="relative rounded-xl border-2 border-dashed transition-all cursor-pointer {isDraggingPhoto
            ? 'border-amber-500 bg-amber-500/10'
            : 'border-[rgb(var--border-primary))] hover:border-amber-400'}"
          style="min-height: 80px;"
          ondrop={handleDrop}
          ondragover={handleDragOver}
          ondragleave={handleDragLeave}
          onclick={() => fotos.length < MAX_FOTOS_IZADA && fileInputRef?.click()}
          role="button"
          tabindex="0"
          onkeydown={(e) => { if (e.key === 'Enter' || e.key === ' ') fileInputRef?.click() }}
          aria-label="Agregar foto"
        >
          <input
            bind:this={fileInputRef}
            type="file"
            accept="image/*"
            multiple
            class="hidden"
            onchange={handleFileSelect}
          />

          {#if fotos.length === 0}
            <div class="flex flex-col items-center justify-center py-5 gap-2">
              <Image class="w-8 h-8 text-[rgb(var--text-muted))]" />
              <p class="text-sm text-[rgb(var--text-muted))] text-center px-4">
                Arrastra fotos aquí o haz clic para seleccionar
              </p>
            </div>
          {:else}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-3">
              {#each fotos as foto, idx (idx)}
                <div class="relative group" in:fade={{ duration: 150 }}>
                  <img
                    src={foto}
                    alt="Evidencia {idx + 1}"
                    class="w-full h-20 object-cover rounded-lg border border-[rgb(var--border-primary))]"
                  />
                  <button
                    type="button"
                    onclick={(e) => { e.stopPropagation(); removeFoto(idx) }}
                    class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center shadow-md opacity-0 group-hover:opacity-100 transition-opacity"
                    aria-label="Eliminar foto"
                  >
                    <X class="w-3 h-3" />
                  </button>
                  <span class="absolute bottom-1 left-1 text-xs bg-black/60 text-white px-1 rounded">
                    {idx + 1}
                  </span>
                </div>
              {/each}
              {#if fotos.length < MAX_FOTOS_IZADA}
                <div
                  class="flex items-center justify-center h-20 rounded-lg border-2 border-dashed border-[rgb(var--border-primary))] hover:border-amber-400 transition-colors"
                  onclick={(e) => { e.stopPropagation(); fileInputRef?.click() }}
                  role="button"
                  tabindex="0"
                  onkeydown={(e) => { if (e.key === 'Enter' || e.key === ' ') fileInputRef?.click() }}
                >
                  <Plus class="w-6 h-6 text-[rgb(var--text-muted))]" />
                </div>
              {/if}
            </div>
          {/if}
        </div>

        {#if fotos.length >= MAX_FOTOS_IZADA}
          <p class="text-xs text-amber-600 dark:text-amber-400 mt-1 text-center">
           Máximo {MAX_FOTOS_IZADA} fotos alcanzado
          </p>
        {/if}
      </div>
    </div>
  </Accordion>

  <!-- CONCLUSIONES -->
  <Accordion title="Conclusiones" isExpanded={expanded.conclusiones} onToggle={() => toggle('conclusiones')} color="#F59E0B">
    <div class="space-y-3">
      {#each acta.conclusiones as conclusion, i (i)}
        <div class="flex items-center gap-2 p-3 rounded-lg bg-[rgb(var--bg-secondary))] border border-[rgb(var--border-primary))]">
          <input
            type="text"
            bind:value={conclusion.texto}
            placeholder="Conclusión de la ceremonia"
            class="flex-1 px-3 py-2 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-primary))] text-sm"
          />
          <label class="flex items-center gap-1 text-xs cursor-pointer">
            <input type="checkbox" bind:checked={conclusion.cumplida} class="w-4 h-4 rounded accent-emerald-500" />
            <span>Cumplida</span>
          </label>
          {#if acta.conclusiones.length > 1}
            <button
              type="button"
              onclick={() => removeConclusion(i)}
              class="p-2 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          {/if}
        </div>
      {/each}
      <button
        type="button"
        onclick={addConclusion}
        class="flex items-center gap-2 px-4 py-2 rounded-lg border border-dashed border-[rgb(var--border-primary))] text-sm text-[rgb(var--text-muted))] hover:border-amber-500 hover:text-amber-600 transition-colors"
      >
        <Plus class="w-4 h-4" />
        Agregar conclusión
      </button>
    </div>
  </Accordion>

  <!-- COMPROMISOS -->
  <Accordion title="Compromisos" isExpanded={expanded.compromisos} onToggle={() => toggle('compromisos')} color="#F59E0B">
    <div class="space-y-3">
      {#each acta.compromisos as compromiso, i (i)}
        <div class="p-3 rounded-lg bg-[rgb(var--bg-secondary))] border border-[rgb(var--border-primary))]">
          <div class="flex items-end gap-2 mb-2">
            <div class="flex-1">
              <label class="block text-xs text-[rgb(var--text-muted))] mb-1" for="actividad-{i}">Actividad</label>
              <input
                id="actividad-{i}"
                type="text"
                bind:value={compromiso.actividad}
                placeholder="Descripción del compromiso"
                class="w-full px-3 py-2 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-primary))] text-sm"
              />
            </div>
            {#if acta.compromisos.length > 1}
              <button
                type="button"
                onclick={() => removeCompromiso(i)}
                class="p-2 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg mb-1"
              >
                <Trash2 class="w-4 h-4" />
              </button>
            {/if}
          </div>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            <div>
              <label class="block text-xs text-[rgb(var--text-muted))] mb-1" for="responsable-{i}">Responsable</label>
              <input
                id="responsable-{i}"
                type="text"
                bind:value={compromiso.responsable}
                placeholder="Nombre"
                class="w-full px-3 py-2 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-primary))] text-sm"
              />
            </div>
            <div>
              <span class="block text-xs text-[rgb(var--text-muted))] mb-1">Fecha límite</span>
              <DatePicker
                  id="fecha-limite-{i}"
                  bind:value={compromiso.fechaLimite}
                />
            </div>
            <div>
              <label class="block text-xs text-[rgb(var--text-muted))] mb-1" for="estado-{i}">Estado</label>
              <select
                id="estado-{i}"
                bind:value={compromiso.estado}
                class="w-full px-3 py-2 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-primary))] text-sm"
              >
                <option value="pendiente">Pendiente</option>
                <option value="en_curso">En curso</option>
                <option value="cumplido">Cumplido</option>
              </select>
            </div>
          </div>
        </div>
      {/each}
      <button
        type="button"
        onclick={addCompromiso}
        class="flex items-center gap-2 px-4 py-2 rounded-lg border border-dashed border-[rgb(var--border-primary))] text-sm text-[rgb(var--text-muted))] hover:border-amber-500 hover:text-amber-600 transition-colors"
      >
        <Plus class="w-4 h-4" />
        Agregar compromiso
      </button>
    </div>
  </Accordion>

  <!-- CIERRE -->
  <Accordion title="Cierre y Firmas" isExpanded={expanded.cierre} onToggle={() => toggle('cierre')} color="#F59E0B">
    <div class="space-y-4">
      <label class="flex items-start gap-3 p-4 rounded-xl bg-[rgb(var--bg-secondary))] border border-[rgb(var--border-primary))] cursor-pointer">
        <input
          type="checkbox"
          bind:checked={acta.actaLeidaAprobada}
          class="w-5 h-5 mt-0.5 rounded accent-amber-500"
        />
        <div>
          <p class="text-sm font-medium">Acta leída y aprobada</p>
          <p class="text-xs text-[rgb(var--text-muted))] mt-1">
            Confirma que el contenido del acta fue leído y aprobado por los participantes.
          </p>
        </div>
      </label>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        {#each acta.firmas as firma}
          <div class="p-4 rounded-xl bg-[rgb(var--bg-secondary))] border border-[rgb(var--border-primary))]">
            <div class="flex items-center justify-between mb-3">
              <div>
                <p class="text-sm font-bold capitalize">{firma.rol}</p>
                <p class="text-xs text-[rgb(var--text-muted))]">{firma.nombre || '—'}</p>
              </div>
              <button
                type="button"
                onclick={() => openFirma(firma.rol)}
                class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium transition-colors"
              >
                Firmar
              </button>
            </div>
            {#if firma.firma}
              <div class="p-2 rounded-lg bg-white border border-[rgb(var--border-primary))]">
                <img src={firma.firma} alt="Firma" class="h-12 object-contain" />
              </div>
            {:else}
              <div class="h-12 rounded-lg bg-gray-100 dark:bg-gray-800 border-2 border-dashed border-[rgb(var--border-primary))] flex items-center justify-center">
                <span class="text-xs text-[rgb(var--text-muted))]">Sin firma</span>
              </div>
            {/if}
          </div>
        {/each}
      </div>
    </div>
  </Accordion>

  <!-- Base legal -->
  <div class="p-4 rounded-xl bg-[rgb(var--bg-secondary))] border border-[rgb(var--border-primary))]">
    <p class="text-xs italic text-[rgb(var--text-muted))]">
      {BASE_LEGAL_TEXT}
    </p>
  </div>

</div>

<!-- Firma Modal -->
{#if firmaTarget}
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" transition:fade>
    <div class="w-full max-w-md rounded-2xl bg-[rgb(var--bg-primary))] border border-[rgb(var--border-primary))] shadow-2xl p-6" transition:fly={{ y: 20 }}>
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold">Firma - {firmaTarget}</h3>
        <button onclick={closeFirma} class="p-2 hover:bg-[rgb(var--bg-secondary))] rounded-lg">
          <X class="w-5 h-5" />
        </button>
      </div>
      <p class="text-sm text-[rgb(var--text-muted))] mb-4">Dibuje su firma en el recuadro</p>
      <canvas
        bind:this={firmaCanvas}
        width="400"
        height="150"
        class="w-full border-2 border-dashed border-[rgb(var--border-primary))] rounded-xl cursor-crosshair bg-white"
      ></canvas>
      <div class="flex items-center justify-end gap-3 mt-4">
        <button
          onclick={closeFirma}
          class="px-4 py-2 rounded-xl text-sm font-medium border border-[rgb(var--border-primary))] hover:bg-[rgb(var--bg-secondary))] transition-colors"
        >
          Cancelar
        </button>
        <button
          onclick={guardarFirma}
          class="px-4 py-2 rounded-xl text-sm font-bold bg-amber-500 hover:bg-amber-600 text-white transition-colors"
        >
          Guardar firma
        </button>
      </div>
    </div>
  </div>
{/if}

<!-- Drive Folder Picker -->
{#if showFolderPicker}
  <DriveFolderPicker
    onSelect={handleDriveSelect}
    onClose={() => showFolderPicker = false}
    isSaving={isLoading}
  />
{/if}

<style>
  :global(#tema-selector-trigger) {
    display: none;
  }
</style>