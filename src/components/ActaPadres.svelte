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
    TIPOS_REUNION_PADRES, ROLES_PARTICIPANTE, TEMAS_AGENDA_PREDEFINIDOS,
    ESTADOS_COMPROMISO, LUGARES_REUNION, BASE_LEGAL_TEXT_PADRES,
    ACTIVIDADES_PREDETERMINADAS_PADRES,
    type ActaReunionPadres, type ParticipanteReunion, type TemaAgenda,
    type CompromisoReunion, type FirmaActaReunion,
  } from '../lib/types/actaPadres'
  import { generateActaPadresPdf, buildActaPadresFileName } from '../lib/actaPadresPdf'
  import { generateActaPadresExcel, buildActaPadresExcelName } from '../lib/actaPadresExcel'
  import { gdriveService } from '../lib/gdriveService'
  import { GOOGLE_CLIENT_ID, SAVE_ACTA_PADRES_URL, SPREADSHEET_ID_PADRES, WORKSHEET_TITLE_PADRES } from '../constants'
  import { theme } from '../lib/themeStore'
  import { docenteName } from '../lib/authStore'
  import ModuleHeader from './ModuleHeader.svelte'
  import DriveFolderPicker from './DriveFolderPicker.svelte'
  import DatePicker from './anotador/DatePicker.svelte'
  import TimeSelector from './TimeSelector.svelte'
  import { Accordion } from './anotador'
  import { SelectField } from './anotador'

  interface Props {
    onBack: () => void
  }
  const { onBack }: Props = $props()

  interface Estudiante {
    nombre: string
    grado: number | string
  }

  const STORAGE_KEY = 'acta_padres_draft'
  const today = new Date().toLocaleDateString('en-CA')

  let estudiantes: Estudiante[] = $state([])
  let isLoadingEstudiantes = $state(false)

  const createInitialActa = (): ActaReunionPadres => ({
    id: `padres_${Date.now()}`,
    institucion: 'Institución Educativa Instituto Guática',
    tipo: 'ordinaria',
    fecha: today,
    horaInicio: '08:00',
    horaFin: '09:00',
    lugar: 'auditorio',
    grado: '',
    grupo: '',
    temaPrincipal: '',
    participantes: [
      { nombre: $docenteName || '', rol: 'docente', hijosEnInstitucion: 0, hijosGrados: '' },
    ],
    temasAgenda: TEMAS_AGENDA_PREDEFINIDOS.map(n => ({ nombre: n, descripcion: '', tratado: false })),
    acuerdos: '',
    compromisos: [{ actividad: '', responsable: '', fechaLimite: '', estado: 'pendiente' }],
    observacionesGenerales: '',
    proxFechaReunion: '',
    actaLeidaAprobada: false,
    firmas: [
      { nombre: $docenteName || '', rol: 'docente' },
      { nombre: '', rol: 'representante' },
    ],
    fotos: [],
    fotosFirmas: [],
    docenteCreador: $docenteName || '',
    createdAt: new Date().toISOString(),
  })

  let acta = $state<ActaReunionPadres>(createInitialActa())
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

  let fotos = $state<string[]>([])
  let fotosFirmas = $state<string[]>([])
  let isDraggingPhoto = $state(false)
  let isDraggingFirmaPhoto = $state(false)
  let fileInputRef = $state<HTMLInputElement>()
  let fileInputFirmasRef = $state<HTMLInputElement>()

  const addFoto = (base64: string) => {
    if (fotos.length < 4) {
      fotos = [...fotos, base64]
    }
  }

  const removeFoto = (index: number) => {
    fotos = fotos.filter((_, i) => i !== index)
  }

  const removeFotoFirma = (index: number) => {
    fotosFirmas = fotosFirmas.filter((_, i) => i !== index)
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

  const handleDropFirmas = (e: DragEvent) => {
    e.preventDefault()
    isDraggingFirmaPhoto = false
    if (e.dataTransfer?.files) {
      processFilesFirmas(Array.from(e.dataTransfer.files))
    }
  }

  const handleDragOverFirmas = (e: DragEvent) => {
    e.preventDefault()
    isDraggingFirmaPhoto = true
  }

  const handleDragLeaveFirmas = () => {
    isDraggingFirmaPhoto = false
  }

  const processFilesFirmas = (files: File[]) => {
    files.forEach(file => {
      if (!file.type.startsWith('image/')) return
      const reader = new FileReader()
      reader.onload = (ev) => {
        const base64 = ev.target?.result as string
        if (fotosFirmas.length < 4) {
          fotosFirmas = [...fotosFirmas, base64]
        }
      }
      reader.readAsDataURL(file)
    })
  }

  const handleFileSelectFirmas = (e: Event) => {
    const input = e.target as HTMLInputElement
    if (!input.files?.length) return
    processFilesFirmas(Array.from(input.files))
    input.value = ''
  }

  $effect(() => {
    acta.fotos = fotos
    acta.fotosFirmas = fotosFirmas
  })

  let expanded = $state<Record<string, boolean>>({
    info: true,
    participantes: true,
    agenda: true,
    acuerdos: false,
    compromisos: true,
    cierre: true,
  })
  const toggle = (k: string) => (expanded[k] = !expanded[k])

  let saveTimeout: ReturnType<typeof setTimeout> | null = null

  const getDocenteNumber = (docente: string): string | null => {
    const match = docente.match(/-(\d+)$/)
    return match ? match[1] : null
  }

  let docenteNumber = $derived(getDocenteNumber($docenteName || ''))

  let filteredGrados = $derived(
    docenteNumber
      ? [...new Set(estudiantes.map(e => e.grado.toString()))].filter(g =>
          g.startsWith(`${docenteNumber}-`)
        ).map(g => g.replace(/0(\d)$/, '°$1').replace(/(\d{1,2})0(\d)/, '$1°$2'))
      : [...new Set(estudiantes.map(e => e.grado.toString()))].filter(g =>
          !g.includes('-')
        ).map(g => g.replace(/0(\d)$/, '°$1').replace(/(\d{1,2})0(\d)/, '$1°$2'))
  )

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

  onMount(() => {
    loadData()
    try {
      const saved = localStorage.getItem(STORAGE_KEY)
      if (saved) {
        const draft = JSON.parse(saved) as ActaReunionPadres
        const hoursSinceSave = (Date.now() - new Date(draft.createdAt).getTime()) / (1000 * 60 * 60)
        if (hoursSinceSave < 24 && draft.id.startsWith('padres_')) {
          acta = draft
        }
      }
    } catch (e) {
      console.warn('No se pudo cargar el borrador:', e)
    }
  })

  const addParticipante = () => {
    acta.participantes = [...acta.participantes, { nombre: '', rol: 'acudiente', hijosEnInstitucion: 0, hijosGrados: '' }]
  }
  const removeParticipante = (i: number) => {
    if (acta.participantes.length > 1) {
      acta.participantes = acta.participantes.filter((_, idx) => idx !== i)
    }
  }

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

  const openFirma = (rol: string) => { firmaTarget = rol }
  const closeFirma = () => { firmaTarget = null; firmaCanvas = null }

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

  const validate = (): { ok: boolean; errores: string[] } => {
    const errores: string[] = []
    if (!acta.fecha) errores.push('Fecha de la reunión')
    if (!acta.horaInicio) errores.push('Hora de inicio')
    if (!acta.temaPrincipal) errores.push('Tema principal')
    if (acta.participantes.length === 0) errores.push('Al menos un participante')
    if (!acta.actaLeidaAprobada) errores.push('Debe confirmar la lectura y aprobación del acta')
    return { ok: errores.length === 0, errores }
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
      title: '¿Guardar acta de padres?',
      html: `<p>Se enviará el registro a la hoja de cálculo de Google Sheets.</p>`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#14B8A6',
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
        acta.grado || '',
        acta.grupo || '',
        acta.temaPrincipal,
        JSON.stringify(acta.participantes),
        JSON.stringify(acta.temasAgenda),
        acta.acuerdos || '',
        JSON.stringify(acta.compromisos),
        acta.observacionesGenerales || '',
        acta.proxFechaReunion || '',
        acta.actaLeidaAprobada ? 'SI' : 'NO',
        new Date().toISOString(),
      ]

      const response = await fetch(SAVE_ACTA_PADRES_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ datos: [row], spreadsheetId: SPREADSHEET_ID_PADRES, worksheetTitle: WORKSHEET_TITLE_PADRES }),
      })

      if (!response.ok) throw new Error('Error en la respuesta del servidor')

      lastSaved = new Date()
      localStorage.removeItem(STORAGE_KEY)
      await Swal.fire({
        icon: 'success',
        title: '¡Guardado!',
        text: 'El acta de reunión de padres se ha registrado exitosamente.',
        confirmButtonColor: '#14B8A6',
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
      const blob = await generateActaPadresPdf(acta)
      const fileName = buildActaPadresFileName(acta)
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
      const blob = await generateActaPadresExcel(acta)
      const fileName = buildActaPadresExcelName(acta)
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
        const blob = await generateActaPadresPdf(acta)
        pendingBlob = blob
        pendingFileName = buildActaPadresFileName(acta)
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
          confirmButtonColor: '#14B8A6',
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

<ModuleHeader title="Acta de Reunión de Padres" subtitle="Participación familia-escuela" {onBack}>
  {#snippet actions()}
    <div class="flex items-center gap-2">
      <button
        onclick={handleGeneratePdf}
        disabled={isGeneratingPdf}
        class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium bg-[rgb(var--bg-secondary))] border border-[rgb(var--border-primary))] hover:border-[rgb(var--accent-primary))] transition-all disabled:opacity-50"
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
        class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium bg-[rgb(var--bg-secondary))] border border-[rgb(var--border-primary))] hover:border-[rgb(var--accent-primary))] transition-all disabled:opacity-50"
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
        class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-bold bg-teal-500 hover:bg-teal-600 text-white transition-all disabled:opacity-50"
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
    <div class="flex items-start gap-3 p-4 rounded-xl bg-teal-50 dark:bg-teal-900/20 border border-teal-200 dark:border-teal-800">
      <Info class="w-5 h-5 text-teal-600 mt-0.5 shrink-0" />
      <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-teal-800 dark:text-teal-200">
          Base legal: Decreto 1286 de 2005, artículo 23
        </p>
        <p class="text-xs text-teal-700 dark:text-teal-300 mt-1">
          Las instituciones educativas deben dejar constancia escrita de las reuniones de padres mediante actas. Este formato documenta la participación de la familia en el proceso educativo.
        </p>
      </div>
      <button onclick={() => showInfo = false} class="p-1 hover:bg-teal-100 dark:hover:bg-teal-900/40 rounded">
        <X class="w-4 h-4 text-teal-600" />
      </button>
    </div>
  {/if}

  {#if lastSaved}
    <div class="flex items-center gap-2 text-sm text-emerald-600 dark:text-emerald-400">
      <Check class="w-4 h-4" />
      <span>Guardado el {lastSaved.toLocaleString('es-CO')}</span>
    </div>
  {/if}

  <!-- INFO GENERAL -->
  <Accordion title="Información General" isExpanded={expanded.info} onToggle={() => toggle('info')} color="#14B8A6">
    <div class="space-y-4">
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-medium text-[rgb(var--text-muted))] mb-1" for="fecha-padres">Fecha *</label>
          <DatePicker id="fecha-padres" bind:value={acta.fecha} />
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
          <label class="block text-xs font-medium text-[rgb(var--text-muted))] mb-1" for="tipo-padres">Tipo</label>
          <select
            id="tipo-padres"
            bind:value={acta.tipo}
            class="w-full px-3 py-2 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-secondary))] text-[rgb(var--text-primary))] text-sm"
          >
            {#each TIPOS_REUNION_PADRES as t}
              <option value={t.value}>{t.label} - {t.descripcion}</option>
            {/each}
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-[rgb(var--text-muted))] mb-1" for="lugar-padres">Lugar</label>
          <select
            id="lugar-padres"
            bind:value={acta.lugar}
            class="w-full px-3 py-2 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-secondary))] text-[rgb(var--text-primary))] text-sm"
          >
            {#each LUGARES_REUNION as l}
              <option value={l.value}>{l.label}</option>
            {/each}
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-[rgb(var--text-muted))] mb-1" for="prox-fecha-padres">Próxima reunión</label>
          <DatePicker id="prox-fecha-padres" bind:value={acta.proxFechaReunion} />
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <span class="block text-xs font-medium text-[rgb(var--text-muted))] mb-1">Grado</span>
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
                {@const isSelected = acta.grado === grado}
                <button
                  type="button"
                  onclick={() => { acta.grado = isSelected ? '' : grado }}
                  class="px-3 py-1.5 rounded-lg border text-sm font-medium transition-all {isSelected
                    ? 'bg-teal-500 text-white border-teal-500'
                    : 'bg-[rgb(var--bg-secondary))] border-[rgb(var--border-primary))] text-[rgb(var--text-primary))] hover:border-teal-400'}"
                >
                  {grado}
                </button>
              {/each}
            </div>
          {/if}
        </div>
        <div>
          <label class="block text-xs font-medium text-[rgb(var--text-muted))] mb-1" for="grupo-padres">Grupo</label>
          <select
            id="grupo-padres"
            bind:value={acta.grupo}
            class="w-full px-3 py-2 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-secondary))] text-[rgb(var--text-primary))] text-sm"
          >
            <option value="">Todos</option>
            {#each ['A', 'B', 'C', 'D', 'E'] as g}
              <option value={g}>{g}</option>
            {/each}
          </select>
        </div>
      </div>

      <div>
        <label class="block text-xs font-medium text-[rgb(var--text-muted))] mb-1" for="tema-padres">Tema Principal *</label>
        <input
          id="tema-padres"
          type="text"
          bind:value={acta.temaPrincipal}
          placeholder="Tema central de la reunión"
          class="w-full px-3 py-2 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-secondary))] text-[rgb(var--text-primary))] text-sm"
        />
      </div>
    </div>
  </Accordion>

  <!-- PARTICIPANTES -->
  <Accordion title="Participantes" isExpanded={expanded.participantes} onToggle={() => toggle('participantes')} color="#14B8A6">
    <div class="space-y-3">
      {#each acta.participantes as participante, i (i)}
        <div class="p-3 rounded-lg bg-[rgb(var--bg-secondary))] border border-[rgb(var--border-primary))]">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-2">
            <input
              type="text"
              bind:value={participante.nombre}
              placeholder="Nombre completo"
              class="px-3 py-2 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-primary))] text-sm"
            />
            <select
              bind:value={participante.rol}
              class="px-3 py-2 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-primary))] text-sm"
            >
              {#each ROLES_PARTICIPANTE as r}
                <option value={r.value}>{r.label}</option>
              {/each}
            </select>
          </div>
          <div class="grid grid-cols-2 gap-2">
            <div>
              <span class="block text-xs text-[rgb(var--text-muted))] mb-1">Hijos en la institución</span>
              <input
                type="number"
                bind:value={participante.hijosEnInstitucion}
                min="0"
                placeholder="Cant."
                class="w-full px-3 py-1.5 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-primary))] text-sm"
              />
            </div>
            <div>
              <span class="block text-xs text-[rgb(var--text-muted))] mb-1">Grados de hijos</span>
              <input
                type="text"
                bind:value={participante.hijosGrados}
                placeholder="Ej: 6°, 8°"
                class="w-full px-3 py-1.5 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-primary))] text-sm"
              />
            </div>
          </div>
          {#if acta.participantes.length > 1}
            <div class="flex justify-end mt-2">
              <button
                type="button"
                onclick={() => removeParticipante(i)}
                class="p-2 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-colors"
              >
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          {/if}
        </div>
      {/each}
      <button
        type="button"
        onclick={addParticipante}
        class="flex items-center gap-2 px-4 py-2 rounded-lg border border-dashed border-[rgb(var--border-primary))] text-sm text-[rgb(var--text-muted))] hover:border-teal-500 hover:text-teal-600 transition-colors"
      >
        <Plus class="w-4 h-4" />
        Agregar participante
      </button>
    </div>
  </Accordion>

  <!-- AGENDA -->
  <Accordion title="Agenda Tratada" isExpanded={expanded.agenda} onToggle={() => toggle('agenda')} color="#14B8A6">
    <div class="space-y-3">
      <p class="text-xs text-[rgb(var--text-muted))]">
        Marque los temas que fueron tratados durante la reunión
      </p>
      {#each acta.temasAgenda as tema, i (i)}
        <div class="p-3 rounded-lg bg-[rgb(var--bg-secondary))] border border-[rgb(var--border-primary))]">
          <div class="flex items-start gap-3">
            <input
              type="checkbox"
              bind:checked={tema.tratado}
              class="w-4 h-4 mt-1 rounded accent-teal-500"
            />
            <div class="flex-1">
              <p class="text-sm font-medium">{tema.nombre}</p>
              <input
                type="text"
                bind:value={tema.descripcion}
                placeholder="Notas o acuerdos del tema..."
                class="mt-1 w-full px-3 py-1.5 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-primary))] text-sm"
              />
            </div>
          </div>
        </div>
      {/each}

      <div>
        <label class="block text-xs font-medium text-[rgb(var--text-muted))] mb-1" for="acuerdos-padres">Acuerdos generales</label>
        <textarea
          id="acuerdos-padres"
          bind:value={acta.acuerdos}
          placeholder="Resumen de acuerdos alcanzados en la reunión..."
          rows="3"
          class="w-full px-3 py-2 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-secondary))] text-[rgb(var--text-primary))] text-sm resize-none"
        ></textarea>
      </div>
    </div>
  </Accordion>

  <!-- COMPROMISOS -->
  <Accordion title="Compromisos" isExpanded={expanded.compromisos} onToggle={() => toggle('compromisos')} color="#14B8A6">
    <div class="space-y-3">
      {#each acta.compromisos as compromiso, i (i)}
        <div class="p-3 rounded-lg bg-[rgb(var--bg-secondary))] border border-[rgb(var--border-primary))]">
          <div class="flex items-end gap-2 mb-2">
            <div class="flex-1">
              <label class="block text-xs text-[rgb(var--text-muted))] mb-1" for="actividad-padres-{i}">Actividad</label>
              <input
                id="actividad-padres-{i}"
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
          <div class="grid grid-cols-3 gap-2">
            <div>
              <label class="block text-xs text-[rgb(var--text-muted))] mb-1" for="responsable-padres-{i}">Responsable</label>
              <input
                id="responsable-padres-{i}"
                type="text"
                bind:value={compromiso.responsable}
                placeholder="Nombre"
                class="w-full px-3 py-2 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-primary))] text-sm"
              />
            </div>
            <div>
              <span class="block text-xs text-[rgb(var--text-muted))] mb-1">Fecha límite</span>
              <DatePicker
                id="fecha-limite-padres-{i}"
                bind:value={compromiso.fechaLimite}
              />
            </div>
            <div>
              <label class="block text-xs text-[rgb(var--text-muted))] mb-1" for="estado-padres-{i}">Estado</label>
              <select
                id="estado-padres-{i}"
                bind:value={compromiso.estado}
                class="w-full px-3 py-2 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-primary))] text-sm"
              >
                {#each ESTADOS_COMPROMISO as e}
                  <option value={e.value}>{e.label}</option>
                {/each}
              </select>
            </div>
          </div>
        </div>
      {/each}
      <button
        type="button"
        onclick={addCompromiso}
        class="flex items-center gap-2 px-4 py-2 rounded-lg border border-dashed border-[rgb(var--border-primary))] text-sm text-[rgb(var--text-muted))] hover:border-teal-500 hover:text-teal-600 transition-colors"
      >
        <Plus class="w-4 h-4" />
        Agregar compromiso
      </button>
    </div>
  </Accordion>

  <!-- CIERRE -->
  <Accordion title="Cierre y Firmas" isExpanded={expanded.cierre} onToggle={() => toggle('cierre')} color="#14B8A6">
    <div class="space-y-4">
      <div>
        <label class="block text-xs font-medium text-[rgb(var--text-muted))] mb-1" for="observaciones-padres">Observaciones generales</label>
        <textarea
          id="observaciones-padres"
          bind:value={acta.observacionesGenerales}
          placeholder="Observaciones adicionales de la reunión..."
          rows="2"
          class="w-full px-3 py-2 rounded-lg border border-[rgb(var--border-primary))] bg-[rgb(var--bg-secondary))] text-[rgb(var--text-primary))] text-sm resize-none"
        ></textarea>
      </div>

      <label class="flex items-start gap-3 p-4 rounded-xl bg-[rgb(var--bg-secondary))] border border-[rgb(var--border-primary))] cursor-pointer">
        <input
          type="checkbox"
          bind:checked={acta.actaLeidaAprobada}
          class="w-5 h-5 mt-0.5 rounded accent-teal-500"
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
                <p class="text-sm font-bold capitalize">{firma.rol === 'representante' ? 'Representante de padres' : 'Docente'}</p>
                <p class="text-xs text-[rgb(var--text-muted))]">{firma.nombre || '—'}</p>
              </div>
              <button
                type="button"
                onclick={() => openFirma(firma.rol)}
                class="px-3 py-1.5 rounded-lg bg-teal-500 hover:bg-teal-600 text-white text-xs font-medium transition-colors"
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

  <!-- Evidencia Fotográfica -->
  <div class="mt-5 pt-4 border-t border-[rgb(var--border-primary))]">
    <div class="flex items-center justify-between mb-3">
<span class="block text-xs font-semibold text-[rgb(var--text-muted))] uppercase tracking-wide">
            Evidencia Fotográfica (máx 4)
          </span>
      <span class="text-xs text-[rgb(var--text-muted))]">{fotos.length}/4</span>
    </div>

    <div
      class="relative rounded-xl border-2 border-dashed transition-all cursor-pointer {isDraggingPhoto
        ? 'border-teal-500 bg-teal-500/10'
        : 'border-[rgb(var--border-primary))] hover:border-teal-400'}"
      style="min-height: 80px;"
      ondrop={handleDrop}
      ondragover={handleDragOver}
      ondragleave={handleDragLeave}
      onclick={() => fotos.length < 4 && fileInputRef?.click()}
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
          {#if fotos.length < 4}
            <div
              class="flex items-center justify-center h-20 rounded-lg border-2 border-dashed border-[rgb(var--border-primary))] hover:border-teal-400 transition-colors"
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

    {#if fotos.length >= 4}
      <p class="text-xs text-teal-600 dark:text-teal-400 mt-1 text-center">
        Máximo 4 fotos alcanzado
      </p>
    {/if}
  </div>

  <!-- Firmas fotográficas de padres -->
  <div class="mt-5 pt-4 border-t border-[rgb(var--border-primary))]">
    <div class="flex items-center justify-between mb-3">
      <span class="block text-xs font-semibold text-[rgb(var--text-muted))] uppercase tracking-wide">
        Firmas de Padres (fotografía)
      </span>
      <span class="text-xs text-[rgb(var--text-muted))]">{fotosFirmas.length}/4</span>
    </div>

    <div
      class="relative rounded-xl border-2 border-dashed transition-all cursor-pointer {isDraggingFirmaPhoto
        ? 'border-violet-500 bg-violet-500/10'
        : 'border-[rgb(var--border-primary))] hover:border-violet-400'}"
      style="min-height: 80px;"
      ondrop={handleDropFirmas}
      ondragover={handleDragOverFirmas}
      ondragleave={handleDragLeaveFirmas}
      onclick={() => fotosFirmas.length < 4 && fileInputFirmasRef?.click()}
      role="button"
      tabindex="0"
      onkeydown={(e) => { if (e.key === 'Enter' || e.key === ' ') fileInputFirmasRef?.click() }}
      aria-label="Agregar foto de firma"
    >
      <input
        bind:this={fileInputFirmasRef}
        type="file"
        accept="image/*"
        multiple
        class="hidden"
        onchange={handleFileSelectFirmas}
      />

      {#if fotosFirmas.length === 0}
        <div class="flex flex-col items-center justify-center py-5 gap-2">
          <Users class="w-8 h-8 text-[rgb(var--text-muted))]" />
          <p class="text-sm text-[rgb(var--text-muted))] text-center px-4">
            Arrastra fotos de firmas de padres aquí o haz clic para seleccionar
          </p>
        </div>
      {:else}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-3">
          {#each fotosFirmas as foto, idx (idx)}
            <div class="relative group" in:fade={{ duration: 150 }}>
              <img
                src={foto}
                alt="Firma de padre {idx + 1}"
                class="w-full h-20 object-cover rounded-lg border border-[rgb(var--border-primary))]"
              />
              <button
                type="button"
                onclick={(e) => { e.stopPropagation(); removeFotoFirma(idx) }}
                class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center shadow-md opacity-0 group-hover:opacity-100 transition-opacity"
                aria-label="Eliminar foto de firma"
              >
                <X class="w-3 h-3" />
              </button>
              <span class="absolute bottom-1 left-1 text-xs bg-black/60 text-white px-1 rounded">
                {idx + 1}
              </span>
            </div>
          {/each}
          {#if fotosFirmas.length < 4}
            <div
              class="flex items-center justify-center h-20 rounded-lg border-2 border-dashed border-[rgb(var--border-primary))] hover:border-violet-400 transition-colors"
              onclick={(e) => { e.stopPropagation(); fileInputFirmasRef?.click() }}
              role="button"
              tabindex="0"
              onkeydown={(e) => { if (e.key === 'Enter' || e.key === ' ') fileInputFirmasRef?.click() }}
            >
              <Plus class="w-6 h-6 text-[rgb(var--text-muted))]" />
            </div>
          {/if}
        </div>
      {/if}
    </div>

    {#if fotosFirmas.length >= 4}
      <p class="text-xs text-violet-600 dark:text-violet-400 mt-1 text-center">
        Máximo 4 fotos de firmas alcanzado
      </p>
    {/if}
  </div>

  <!-- Base legal -->
  <div class="p-4 rounded-xl bg-[rgb(var--bg-secondary))] border border-[rgb(var--border-primary))]">
    <p class="text-xs italic text-[rgb(var--text-muted))]">
      {BASE_LEGAL_TEXT_PADRES}
    </p>
  </div>

</div>

<!-- Firma Modal -->
{#if firmaTarget}
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" transition:fade>
    <div class="w-full max-w-md rounded-2xl bg-[rgb(var--bg-primary))] border border-[rgb(var--border-primary))] shadow-2xl p-6" transition:fly={{ y: 20 }}>
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold">Firma - {firmaTarget === 'representante' ? 'Representante de padres' : firmaTarget}</h3>
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
          class="px-4 py-2 rounded-xl text-sm font-bold bg-teal-500 hover:bg-teal-600 text-white transition-colors"
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