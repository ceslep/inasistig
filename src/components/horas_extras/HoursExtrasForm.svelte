<script lang="ts">
  import { fade } from 'svelte/transition'
  import Swal from 'sweetalert2'
  import { Loader2, Check, X, Plus, Trash2, Clock, FileText, Save, Upload, Image, Eraser } from '@lucide/svelte'
  import { horasExtrasSheetsService } from './services/google_sheets_service.svelte'
  import { docenteName, findMatchingDocente } from '../../lib/authStore'
  import { getDocentes, getMaterias, getOpcionesAnotador, getEstudiantes } from '../../../api/service'
  import ModuleHeader from '../ModuleHeader.svelte'
  import horariosData from '../../lib/horarios.json'
  import infoHoras from '../../lib/info_horas.json'

  const ANOTADOR_SPREADSHEET_ID = "1Q6EcSvccB7BoJiw9PD2s5J4PB8AJmr-6v-yKhiE4E8k";
  const ANOTADOR_API_URL = "https://app.iedeoccidente.com/gs/get_anotador.php";

  let { onBack }: { onBack: () => void } = $props()

  type HorarioDocente = {
    docente: string
    lunes: string[]
    martes: string[]
    miercoles: string[]
    jueves: string[]
    viernes: string[]
  }

  interface RegistroHorasExtras {
    fecha: string
    dia: string
    mes: string
    año: string
    docente: string
    horaEntrada: string
    horaSalida: string
    gradoAtendido: string
    asignatura: string
    actividad: string
    horasExtras: number
    firmaBase64: string
    observaciones: string
  }

  const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']

  type BloqueHoras = { bloque: string; inicio: string; fin: string }

  const DIAS_MAP: Record<number, string> = {
    0: 'domingo', 1: 'lunes', 2: 'martes', 3: 'miercoles',
    4: 'jueves', 5: 'viernes', 6: 'sabado'
  }

  function getBloquesDelDia(diaSemana: string): BloqueHoras[] {
    const schedule = infoHoras.horario_escolar as Record<string, BloqueHoras[]>
    return schedule[diaSemana] || []
  }

  const MAX_FIRMA_SIZE = 100 * 1024

  let teacherName = $derived($docenteName || '')

  let docentesList = $state<string[]>([])
  let materiasList = $state<{ materia: string }[]>([])
  let actividadesList = $state<string[]>([])
  let actividadesFiltered = $state<string[]>([])
  let estudiantesList = $state<{ grado: string }[]>([])
  let gradosDisponibles = $state<string[]>([])
  let isLoadingData = $state(true)
  let isLoadingActividades = $state(false)

  let formData = $state({
    fecha: new Date().toISOString().split('T')[0],
    dia: new Date().getDate().toString().padStart(2, '0'),
    mes: monthNames[new Date().getMonth()],
    año: new Date().getFullYear().toString(),
    docente: '',
    horaEntrada: '',
    horaSalida: '',
    gradoAtendido: '',
    asignatura: '',
    actividad: '',
    firmaBase64: '',
    observaciones: ''
  })

  let selectedSlots = $state<number[]>([])
  let slotsDelDia = $state<{ hora: number; inicio: string; fin: string; contenido: string; materia: string; grupo: string; bloque: string }[]>([])
  let firmaPreview = $state('')

  let registros = $state<RegistroHorasExtras[]>([])
  let isSaving = $state(false)
  let saveStatus = $state<'idle' | 'saving' | 'saved' | 'error'>('idle')
  let notification = $state({ show: false, message: '', type: 'info' as 'info' | 'success' | 'error' })

  let editingIndex = $state<number | null>(null)
  let showForm = $state(false)

  function normalizeAccents(text: string): string {
    return text.toLowerCase().trim().normalize("NFD").replace(/[\u0300-\u036f]/g, "")
  }

  function getHorarioDocente(docenteNombre: string, dia: string) {
    const normalizedSearch = normalizeAccents(docenteNombre)
    const horario = (horariosData as HorarioDocente[]).find(h => normalizeAccents(h.docente) === normalizedSearch)
    if (!horario) return []
    const diaKey = dia as keyof HorarioDocente
    return horario[diaKey] || []
  }

  function parsearSlot(slot: string): { materia: string; grupo: string } {
    if (!slot) return { materia: '', grupo: '' }
    if (slot === 'DESC' || slot === 'PEDAG' || slot === 'DEESC') return { materia: slot, grupo: '' }
    const lastSpaceIdx = slot.lastIndexOf(' ')
    if (lastSpaceIdx > 0) {
      const grupo = slot.substring(lastSpaceIdx + 1)
      if (/^\d/.test(grupo)) {
        return { materia: slot.substring(0, lastSpaceIdx), grupo }
      }
    }
    return { materia: slot, grupo: '' }
  }

  function actualizarSlotsDelDia() {
    if (!formData.fecha || !formData.docente) {
      slotsDelDia = []
      return
    }

    const date = new Date(formData.fecha + 'T00:00:00')
    const diaSemana = DIAS_MAP[date.getDay()]

    if (!['lunes', 'martes', 'miercoles', 'jueves', 'viernes'].includes(diaSemana)) {
      slotsDelDia = []
      return
    }

    const horarios = getHorarioDocente(formData.docente, diaSemana)
    const bloques = getBloquesDelDia(diaSemana)

    slotsDelDia = bloques.map((bloque, idx) => {
      const contenido = horarios[idx] || ''
      const { materia, grupo } = parsearSlot(contenido)
      return {
        hora: idx + 1,
        inicio: formatearHora12a24(bloque.inicio),
        fin: formatearHora12a24(bloque.fin),
        contenido,
        materia,
        grupo,
        bloque: bloque.bloque
      }
    })
  }

  function formatearHora12a24(hora12: string): string {
    const match = hora12.match(/(\d+):(\d+)\s*(AM|PM)/i)
    if (!match) return hora12
    let hours = parseInt(match[1])
    const minutes = parseInt(match[2])
    const period = match[3].toUpperCase()
    if (period === 'PM' && hours !== 12) hours += 12
    if (period === 'AM' && hours === 12) hours = 0
    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`
  }

  function actualizarGrados() {
    if (!formData.docente) {
      gradosDisponibles = []
      return
    }

    if (estudiantesList.length > 0) {
      const gradosSet = new Set<string>()
      estudiantesList.forEach((est: { grado: string }) => {
        if (est.grado) {
          const g = est.grado.toString().trim()
          if (g) gradosSet.add(g)
        }
      })
      gradosDisponibles = [...gradosSet].sort((a, b) => {
        const numA = parseInt(a.replace(/[^0-9]/g, '')) || 0
        const numB = parseInt(b.replace(/[^0-9]/g, '')) || 0
        return numA - numB
      })
      return
    }

    const match = formData.docente.match(/^([^-]+)-?(\d+)?/)
    const docenteBase = match ? match[1].trim() : formData.docente
    const normalizedDocenteBase = normalizeAccents(docenteBase)

    const gradosSet = new Set<string>()
    ;(horariosData as HorarioDocente[]).forEach(h => {
      const normalizedH = normalizeAccents(h.docente)
      if (normalizedH === normalizedDocenteBase || normalizedH.startsWith(normalizedDocenteBase + '-')) {
        const dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'] as const
        dias.forEach(dia => {
          h[dia].forEach(slot => {
            if (slot && slot !== 'DESC' && slot !== 'PEDAG' && slot !== 'DEESC') {
              const parts = slot.split(' ')
              const lastPart = parts[parts.length - 1]
              if (/^\d/.test(lastPart)) {
                const formattedGrado = lastPart.replace(/^(\d)(\d)(\d)$/, '$1°$2$3')
                gradosSet.add(formattedGrado)
              }
            }
          })
        })
      }
    })

    gradosDisponibles = [...gradosSet].sort((a, b) => {
      const numA = parseInt(a.replace('°', '').replace(/(\d+)/, '$1'))
      const numB = parseInt(b.replace('°', '').replace(/(\d+)/, '$1'))
      return numA - numB
    })
  }

  async function loadInitialData() {
    isLoadingData = true
    try {
      const [docentesData, materiasData, opcionesData, estudiantesData] = await Promise.all([
        getDocentes(),
        getMaterias(),
        getOpcionesAnotador(),
        getEstudiantes()
      ])

      const teacherMap = new Map<string, string>()
      ;(docentesData as string[]).forEach((teacher: string) => {
        const normalized = teacher.trim().toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "")
        if (!teacherMap.has(normalized)) {
          teacherMap.set(normalized, teacher.trim())
        }
      })
      docentesList = Array.from(teacherMap.values()).sort((a, b) => a.localeCompare(b, 'es'))

      materiasList = materiasData || []

      const allActividades: string[] = []
      if (opcionesData && typeof opcionesData === 'object') {
        Object.values(opcionesData as Record<string, string[]>).forEach((items) => {
          allActividades.push(...items)
        })
      }
      actividadesList = [...new Set(allActividades)].sort()

      estudiantesList = estudiantesData || []

      const match = findMatchingDocente(docentesList, teacherName)
      if (match) {
        formData.docente = match
        actualizarGrados()
        actualizarSlotsDelDia()
      }
    } catch (error) {
      console.error('Error cargando datos:', error)
    } finally {
      isLoadingData = false
    }
  }

  loadInitialData()

  function toggleSlot(hora: number) {
    if (selectedSlots.includes(hora)) {
      selectedSlots = selectedSlots.filter(s => s !== hora)
    } else {
      selectedSlots = [...selectedSlots, hora].sort((a, b) => a - b)
    }

    if (selectedSlots.length > 0) {
      const primerSlot = slotsDelDia.find(s => s.hora === selectedSlots[0])
      const ultimoSlot = slotsDelDia.find(s => s.hora === selectedSlots[selectedSlots.length - 1])
      if (primerSlot && ultimoSlot) {
        formData.horaEntrada = primerSlot.inicio
        formData.horaSalida = ultimoSlot.fin
      }
    } else {
      formData.horaEntrada = ''
      formData.horaSalida = ''
    }
  }

  async function handleFirmaUpload(e: Event) {
    const target = e.target as HTMLInputElement
    const file = target.files?.[0]
    if (!file) return

    if (!file.type.includes('png')) {
      showNotification('Solo se permiten archivos PNG transparentes', 'error')
      return
    }

    if (file.size > MAX_FIRMA_SIZE) {
      showNotification('La imagen no puede superar los 100KB', 'error')
      return
    }

    const reader = new FileReader()
    reader.onload = (event) => {
      const base64 = event.target?.result as string
      formData.firmaBase64 = base64
      firmaPreview = base64
      showNotification('Firma cargada exitosamente', 'success')
    }
    reader.onerror = () => {
      showNotification('Error al cargar la firma', 'error')
    }
    reader.readAsDataURL(file)
  }

  function limpiarFirma() {
    formData.firmaBase64 = ''
    firmaPreview = ''
  }

  function resetForm() {
    const now = new Date()
    formData = {
      fecha: now.toISOString().split('T')[0],
      dia: now.getDate().toString().padStart(2, '0'),
      mes: monthNames[now.getMonth()],
      año: now.getFullYear().toString(),
      docente: formData.docente,
      horaEntrada: '',
      horaSalida: '',
      gradoAtendido: '',
      asignatura: '',
      actividad: '',
      firmaBase64: '',
      observaciones: ''
    }
    selectedSlots = []
    firmaPreview = ''
    editingIndex = null
    actualizarSlotsDelDia()
  }

  function showNotification(message: string, type: 'info' | 'success' | 'error') {
    notification = { show: true, message, type }
    setTimeout(() => { notification.show = false }, 4000)
  }

  function calcularHorasExtras(): number {
    if (!formData.horaEntrada || !formData.horaSalida) return 0
    const [h1, m1] = formData.horaEntrada.split(':').map(Number)
    const [h2, m2] = formData.horaSalida.split(':').map(Number)
    const min1 = h1 * 60 + (m1 || 0)
    const min2 = h2 * 60 + (m2 || 0)
    const diff = min2 - min1
    return diff > 0 ? Math.round((diff / 60) * 100) / 100 : 0
  }

  const horasExtrasCalculadas = $derived(calcularHorasExtras())

  function agregarRegistro() {
    if (!validarFormulario()) return

    const registro: RegistroHorasExtras = {
      fecha: formData.fecha,
      dia: formData.dia,
      mes: formData.mes,
      año: formData.año,
      docente: formData.docente,
      horaEntrada: formData.horaEntrada,
      horaSalida: formData.horaSalida,
      gradoAtendido: formData.gradoAtendido,
      asignatura: formData.asignatura,
      actividad: formData.actividad,
      horasExtras: horasExtrasCalculadas,
      firmaBase64: formData.firmaBase64,
      observaciones: formData.observaciones
    }

    if (editingIndex !== null) {
      registros[editingIndex] = registro
      showNotification('Registro actualizado', 'success')
    } else {
      registros = [...registros, registro]
      showNotification('Registro agregado', 'success')
    }

    resetForm()
    showForm = false
  }

  function validarFormulario(): boolean {
    if (!formData.fecha) {
      showNotification('Seleccione una fecha', 'error')
      return false
    }
    if (!formData.docente) {
      showNotification('Seleccione un docente', 'error')
      return false
    }
    if (!formData.horaEntrada || !formData.horaSalida) {
      showNotification('Seleccione las horas de entrada y salida', 'error')
      return false
    }
    if (!formData.gradoAtendido) {
      showNotification('Seleccione un grado', 'error')
      return false
    }
    if (!formData.asignatura) {
      showNotification('Seleccione una asignatura', 'error')
      return false
    }
    if (horasExtrasCalculadas <= 0) {
      showNotification('La hora de salida debe ser posterior a la de entrada', 'error')
      return false
    }
    return true
  }

  function editarRegistro(index: number) {
    const reg = registros[index]
    formData = {
      fecha: reg.fecha,
      dia: reg.dia,
      mes: reg.mes,
      año: reg.año,
      docente: reg.docente,
      horaEntrada: reg.horaEntrada,
      horaSalida: reg.horaSalida,
      gradoAtendido: reg.gradoAtendido,
      asignatura: reg.asignatura,
      actividad: reg.actividad,
      firmaBase64: reg.firmaBase64,
      observaciones: reg.observaciones
    }
    firmaPreview = reg.firmaBase64

    const matchingSlots = slotsDelDia.filter(h =>
      h.inicio === reg.horaEntrada || h.fin === reg.horaSalida ||
      (h.inicio >= reg.horaEntrada && h.fin <= reg.horaSalida)
    ).map(h => h.hora)
    selectedSlots = matchingSlots

    editingIndex = index
    showForm = true
  }

  function eliminarRegistro(index: number) {
    Swal.fire({
      title: '¿Eliminar registro?',
      text: 'Esta acción no se puede deshacer.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      cancelButtonText: 'Cancelar',
      confirmButtonText: 'Sí, eliminar'
    }).then((result) => {
      if (result.isConfirmed) {
        registros = registros.filter((_, i) => i !== index)
        showNotification('Registro eliminado', 'success')
      }
    })
  }

  async function guardarEnSheets() {
    if (registros.length === 0) {
      showNotification('No hay registros para guardar', 'error')
      return
    }

    isSaving = true
    saveStatus = 'saving'

    try {
      for (let i = 0; i < registros.length; i++) {
        const reg = registros[i]
        const values = [
          reg.fecha,
          reg.dia,
          reg.mes,
          reg.año,
          reg.docente,
          reg.horaEntrada,
          reg.horaSalida,
          reg.gradoAtendido,
          reg.asignatura,
          reg.actividad,
          reg.horasExtras.toString(),
          reg.firmaBase64,
          reg.observaciones
        ]

        const result = await horasExtrasSheetsService.saveRegistro(values, null)
        if (!result.success) {
          throw new Error(result.error || 'Error al guardar registro')
        }
      }

      saveStatus = 'saved'
      showNotification('¡Registros guardados exitosamente!', 'success')
      registros = []
    } catch (error) {
      console.error('Error al guardar:', error)
      saveStatus = 'error'
      showNotification('Error al guardar los registros', 'error')
    } finally {
      isSaving = false
    }
  }

  function toggleForm() {
    showForm = !showForm
    if (!showForm) {
      resetForm()
    }
  }

  function onFechaChange(e: Event) {
    const target = e.target as HTMLInputElement
    formData.fecha = target.value
    const date = new Date(formData.fecha + 'T00:00:00')
    formData.dia = date.getDate().toString().padStart(2, '0')
    formData.mes = monthNames[date.getMonth()]
    formData.año = date.getFullYear().toString()
    selectedSlots = []
    formData.horaEntrada = ''
    formData.horaSalida = ''
    actualizarSlotsDelDia()
  }

  function onDocenteChange() {
    formData.gradoAtendido = ''
    selectedSlots = []
    formData.horaEntrada = ''
    formData.horaSalida = ''
    actualizarGrados()
    actualizarSlotsDelDia()
  }

  function onGradoChange() {
    formData.asignatura = ''
    formData.actividad = ''
    actividadesFiltered = []
    if (formData.gradoAtendido && formData.asignatura) {
      loadActividadesFiltradas()
    }
  }

  async function onAsignaturaChange() {
    formData.actividad = ''
    if (formData.gradoAtendido && formData.asignatura) {
      await loadActividadesFiltradas()
    } else {
      actividadesFiltered = []
    }
  }

  async function loadActividadesFiltradas() {
    if (!formData.gradoAtendido || !formData.asignatura) {
      actividadesFiltered = []
      return
    }
    isLoadingActividades = true
    try {
      const body = {
        spreadsheetId: ANOTADOR_SPREADSHEET_ID,
        worksheetTitle: "Datos",
        filterDocente: formData.docente.toLowerCase().trim(),
        filterGrado: formData.gradoAtendido.toLowerCase().trim(),
        filterMateria: formData.asignatura.toLowerCase().trim(),
        returnActividades: true,
        maxRegistros: 10
      }
      const response = await fetch(ANOTADOR_API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
      })
      const result = await response.json()
      if (result.success && result.actividades) {
        actividadesFiltered = result.actividades
      } else {
        actividadesFiltered = []
      }
    } catch (error) {
      console.error('Error cargando actividades:', error)
      actividadesFiltered = []
    } finally {
      isLoadingActividades = false
    }
  }
</script>

<div class="min-h-screen bg-[rgb(var(--bg-primary))]">
  <ModuleHeader title="Horas Extras" subtitle="Registro de actividades para cobro" {onBack}>
    {#snippet actions()}
      <button
        onclick={toggleForm}
        class="flex items-center gap-2 px-4 py-2 rounded-xl bg-[rgb(var(--accent-primary))] text-white font-medium hover:opacity-90 transition-opacity"
      >
        {#if showForm}
          <X class="w-4 h-4" />
          Cancelar
        {:else}
          <Plus class="w-4 h-4" />
          Nuevo Registro
        {/if}
      </button>
    {/snippet}
  </ModuleHeader>

  <div class="max-w-6xl mx-auto p-4 space-y-6">
    {#if isLoadingData}
      <div class="flex items-center justify-center py-12">
        <Loader2 class="w-8 h-8 animate-spin text-[rgb(var(--accent-primary))]" />
        <span class="ml-3 text-[rgb(var(--text-muted))]">Cargando datos...</span>
      </div>
    {:else if showForm}
      <div in:fade class="bg-[rgb(var(--card-bg))] border border-[rgb(var(--card-border))] rounded-2xl p-6 shadow-lg">
        <h2 class="text-lg font-bold text-[rgb(var(--text-primary))] mb-4 flex items-center gap-2">
          <FileText class="w-5 h-5" />
          {editingIndex !== null ? 'Editar Registro' : 'Nuevo Registro de Hora Extra'}
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div class="space-y-1">
            <label for="fecha" class="text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Fecha</label>
            <input
              id="fecha"
              type="date"
              value={formData.fecha}
              onchange={onFechaChange}
              class="w-full px-4 py-2 rounded-xl border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))] focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-primary))]"
            />
          </div>

          <div class="space-y-1">
            <label for="docente" class="text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Docente</label>
            <select
              id="docente"
              bind:value={formData.docente}
              onchange={onDocenteChange}
              class="w-full px-4 py-2 rounded-xl border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))] focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-primary))] appearance-none cursor-pointer"
            >
              <option value="">Seleccione un docente</option>
              {#each docentesList as doc}
                <option value={doc}>{doc}</option>
              {/each}
            </select>
          </div>

          <div class="space-y-1">
            <label for="grado" class="text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Grado Atendido</label>
            <select
              id="grado"
              bind:value={formData.gradoAtendido}
              onchange={onGradoChange}
              class="w-full px-4 py-2 rounded-xl border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))] focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-primary))] appearance-none cursor-pointer"
            >
              <option value="">Seleccione un grado</option>
              {#each gradosDisponibles as grado}
                <option value={grado}>{grado}</option>
              {/each}
            </select>
          </div>

          <div class="space-y-1">
            <label for="asignatura" class="text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Asignatura</label>
            <select
              id="asignatura"
              bind:value={formData.asignatura}
              onchange={onAsignaturaChange}
              class="w-full px-4 py-2 rounded-xl border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))] focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-primary))] appearance-none cursor-pointer"
            >
              <option value="">Seleccione una asignatura</option>
              {#each materiasList as mat}
                <option value={mat.materia}>{mat.materia}</option>
              {/each}
            </select>
          </div>

          <div class="space-y-1 md:col-span-2">
            <label for="actividad" class="text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Actividad</label>
            <select
              id="actividad"
              bind:value={formData.actividad}
              disabled={isLoadingActividades || !formData.gradoAtendido || !formData.asignatura}
              class="w-full px-4 py-2 rounded-xl border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))] focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-primary))] appearance-none cursor-pointer disabled:opacity-50"
            >
              {#if isLoadingActividades}
                <option value="">Cargando actividades...</option>
              {:else if actividadesFiltered.length > 0}
                <option value="">Seleccione una actividad</option>
                {#each actividadesFiltered as act}
                  <option value={act}>{act}</option>
                {/each}
              {:else}
                <option value="">Seleccione grado y asignatura primero</option>
              {/if}
            </select>
          </div>

          <div class="space-y-1 md:col-span-3">
            <label class="text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Seleccionar Horas del Día</label>
            {#if slotsDelDia.length === 0}
              <div class="p-4 rounded-xl border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-secondary))] text-center text-sm text-[rgb(var(--text-muted))]">
                Seleccione fecha y docente para ver las horas disponibles
              </div>
            {:else}
              <div class="grid grid-cols-7 gap-2">
                {#each slotsDelDia as slot}
                  {@const isSelected = selectedSlots.includes(slot.hora)}
                  {@const isDisabled = slot.contenido === 'DESC' || slot.contenido === 'PEDAG' || slot.contenido === 'DEESC' || slot.bloque === 'Descanso' || slot.bloque === 'Almuerzo'}
                  <button
                    type="button"
                    onclick={() => !isDisabled && toggleSlot(slot.hora)}
                    disabled={isDisabled}
                    class="p-2 rounded-xl border text-center transition-all {isSelected ? 'bg-emerald-500 border-emerald-600 text-white' : isDisabled ? 'bg-zinc-100 border-zinc-200 text-zinc-400 cursor-not-allowed' : 'bg-[rgb(var(--bg-primary))] border-[rgb(var(--border-primary))] text-[rgb(var(--text-primary))] hover:border-[rgb(var(--accent-primary))]'}"
                  >
                    <div class="text-xs font-bold">{slot.bloque}</div>
                    <div class="text-[10px]">{slot.inicio}</div>
                    {#if slot.contenido && slot.contenido !== 'DESC' && slot.contenido !== 'PEDAG' && slot.contenido !== 'DEESC' && slot.bloque !== 'Descanso' && slot.bloque !== 'Almuerzo'}
                      <div class="text-[9px] mt-1 truncate max-w-full">{slot.grupo || slot.materia}</div>
                    {:else if slot.bloque === 'Descanso'}
                      <div class="text-[9px] mt-1">DESC</div>
                    {:else if slot.bloque === 'Almuerzo'}
                      <div class="text-[9px] mt-1">ALMUERZO</div>
                    {:else if slot.contenido === 'DESC'}
                      <div class="text-[9px] mt-1">DESC</div>
                    {:else if slot.contenido === 'PEDAG'}
                      <div class="text-[9px] mt-1">PEDAG</div>
                    {:else}
                      <div class="text-[9px] mt-1">{slot.inicio}</div>
                    {/if}
                  </button>
                {/each}
              </div>
            {/if}
          </div>

          <div class="space-y-1">
            <label for="hora_entrada" class="text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Hora de Entrada</label>
            <input
              id="hora_entrada"
              type="time"
              bind:value={formData.horaEntrada}
              class="w-full px-4 py-2 rounded-xl border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))] focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-primary))]"
            />
          </div>

          <div class="space-y-1">
            <label for="hora_salida" class="text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Hora de Salida</label>
            <input
              id="hora_salida"
              type="time"
              bind:value={formData.horaSalida}
              class="w-full px-4 py-2 rounded-xl border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))] focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-primary))]"
            />
          </div>

          <div class="space-y-1">
            <label class="text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Horas Calculadas</label>
            <div class="w-full px-4 py-2 rounded-xl border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-secondary))] text-[rgb(var(--text-primary))] font-mono font-bold text-center text-lg">
              {horasExtrasCalculadas.toFixed(2)}
            </div>
          </div>

          <div class="space-y-1 md:col-span-3">
            <label class="text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Firma (PNG transparente, máx 100KB)</label>
            <div class="flex items-center gap-4">
              <label
                class="flex items-center gap-2 px-4 py-2 rounded-xl bg-[rgb(var(--bg-secondary))] border border-[rgb(var(--border-primary))] text-[rgb(var(--text-primary))] hover:bg-[rgb(var(--bg-primary))] hover:border-[rgb(var(--accent-primary))] cursor-pointer transition-colors"
              >
                <Upload class="w-4 h-4" />
                <span class="text-sm">Cargar Firma PNG</span>
                <input
                  type="file"
                  accept="image/png"
                  onchange={handleFirmaUpload}
                  class="hidden"
                />
              </label>
              {#if firmaPreview}
                <div class="relative">
                  <img src={firmaPreview} alt="Firma" class="h-12 w-auto border border-[rgb(var(--border-primary))] rounded-lg bg-white" />
                  <button
                    type="button"
                    onclick={limpiarFirma}
                    class="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full hover:bg-red-600"
                  >
                    <X class="w-3 h-3" />
                  </button>
                </div>
              {/if}
            </div>
          </div>

          <div class="space-y-1 md:col-span-3">
            <label for="observaciones" class="text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Observaciones o Novedades</label>
            <textarea
              id="observaciones"
              bind:value={formData.observaciones}
              rows="2"
              class="w-full px-4 py-2 rounded-xl border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))] focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-primary))] resize-none"
              placeholder="Observaciones adicionales..."
            ></textarea>
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
          <button
            onclick={toggleForm}
            class="px-4 py-2 rounded-xl border border-[rgb(var(--border-primary))] text-[rgb(var(--text-primary))] font-medium hover:bg-[rgb(var(--bg-secondary))] transition-colors"
          >
            Cancelar
          </button>
          <button
            onclick={agregarRegistro}
            class="flex items-center gap-2 px-6 py-2 rounded-xl bg-[rgb(var(--accent-primary))] text-white font-medium hover:opacity-90 transition-opacity"
          >
            <Check class="w-4 h-4" />
            {editingIndex !== null ? 'Actualizar' : 'Agregar'}
          </button>
        </div>
      </div>
    {/if}

    {#if registros.length > 0}
      <div class="bg-[rgb(var(--card-bg))] border border-[rgb(var(--card-border))] rounded-2xl overflow-hidden shadow-lg">
        <div class="p-4 border-b border-[rgb(var(--border-primary))] flex items-center justify-between">
          <h2 class="text-lg font-bold text-[rgb(var(--text-primary))] flex items-center gap-2">
            <Clock class="w-5 h-5" />
            Registros Pendientes ({registros.length})
          </h2>
          <button
            onclick={guardarEnSheets}
            disabled={isSaving}
            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-500 text-white font-medium hover:bg-emerald-600 transition-colors disabled:opacity-50"
          >
            {#if isSaving}
              <Loader2 class="w-4 h-4 animate-spin" />
              Guardando...
            {:else}
              <Save class="w-4 h-4" />
              Guardar en Sheets
            {/if}
          </button>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-[rgb(var(--bg-secondary))]">
              <tr>
                <th class="px-3 py-3 text-left text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Fecha</th>
                <th class="px-3 py-3 text-left text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Docente</th>
                <th class="px-3 py-3 text-left text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Entrada</th>
                <th class="px-3 py-3 text-left text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Salida</th>
                <th class="px-3 py-3 text-left text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Horas</th>
                <th class="px-3 py-3 text-left text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Grado</th>
                <th class="px-3 py-3 text-left text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Asignatura</th>
                <th class="px-3 py-3 text-center text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Firma</th>
                <th class="px-3 py-3 text-center text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[rgb(var(--border-primary))]">
              {#each registros as reg, i}
                <tr class="hover:bg-[rgb(var(--bg-secondary))] transition-colors">
                  <td class="px-3 py-3 text-sm text-[rgb(var(--text-primary))]">{reg.fecha}</td>
                  <td class="px-3 py-3 text-sm text-[rgb(var(--text-primary))]">{reg.docente}</td>
                  <td class="px-3 py-3 text-sm text-[rgb(var(--text-primary))]">{reg.horaEntrada}</td>
                  <td class="px-3 py-3 text-sm text-[rgb(var(--text-primary))]">{reg.horaSalida}</td>
                  <td class="px-3 py-3 text-sm font-mono font-bold text-[rgb(var(--accent-primary))]">{reg.horasExtras.toFixed(2)}</td>
                  <td class="px-3 py-3 text-sm text-[rgb(var(--text-primary))]">{reg.gradoAtendido}</td>
                  <td class="px-3 py-3 text-sm text-[rgb(var(--text-primary))]">{reg.asignatura}</td>
                  <td class="px-3 py-3 text-center">
                    {#if reg.firmaBase64}
                      <img src={reg.firmaBase64} alt="Firma" class="h-8 w-auto inline-block" />
                    {:else}
                      <span class="text-[rgb(var(--text-muted))] text-xs">Sin firma</span>
                    {/if}
                  </td>
                  <td class="px-3 py-3 text-center">
                    <div class="flex items-center justify-center gap-1">
                      <button
                        onclick={() => editarRegistro(i)}
                        class="p-1.5 rounded-lg hover:bg-blue-100 text-blue-600 transition-colors"
                        title="Editar"
                      >
                        <FileText class="w-4 h-4" />
                      </button>
                      <button
                        onclick={() => eliminarRegistro(i)}
                        class="p-1.5 rounded-lg hover:bg-red-100 text-red-600 transition-colors"
                        title="Eliminar"
                      >
                        <Trash2 class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              {/each}
            </tbody>
          </table>
        </div>

        <div class="p-4 border-t border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-secondary))]">
          <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-[rgb(var(--text-muted))]">
              Total horas: <span class="font-bold text-[rgb(var(--text-primary))]">{registros.reduce((sum, r) => sum + r.horasExtras, 0).toFixed(2)}</span>
            </span>
            {#if saveStatus === 'saved'}
              <span class="flex items-center gap-1 text-sm font-medium text-emerald-600">
                <Check class="w-4 h-4" />
                Guardado exitosamente
              </span>
            {:else if saveStatus === 'error'}
              <span class="flex items-center gap-1 text-sm font-medium text-red-600">
                <X class="w-4 h-4" />
                Error al guardar
              </span>
            {/if}
          </div>
        </div>
      </div>
    {/if}

    {#if registros.length === 0 && !showForm}
      <div in:fade class="bg-[rgb(var(--card-bg))] border border-[rgb(var(--card-border))] rounded-2xl p-12 text-center shadow-lg">
        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-[rgb(var(--bg-secondary))] flex items-center justify-center">
          <Clock class="w-8 h-8 text-[rgb(var(--text-muted))]" />
        </div>
        <h3 class="text-lg font-bold text-[rgb(var(--text-primary))] mb-2">No hay registros de horas extras</h3>
        <p class="text-sm text-[rgb(var(--text-muted))] mb-6 max-w-md mx-auto">
          Presiona el botón "Nuevo Registro" para comenzar a registrar tus horas extras.
        </p>
        <button
          onclick={toggleForm}
          class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-[rgb(var(--accent-primary))] text-white font-medium hover:opacity-90 transition-opacity"
        >
          <Plus class="w-5 h-5" />
          Crear Primer Registro
        </button>
      </div>
    {/if}
  </div>
</div>

{#if notification.show}
  <div
    in:fade
    class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl text-white font-medium {(notification.type === 'success' ? 'bg-emerald-500' : notification.type === 'error' ? 'bg-red-500' : 'bg-blue-500')}"
  >
    {#if notification.type === 'success'}
      <Check class="w-5 h-5" />
    {:else if notification.type === 'error'}
      <X class="w-5 h-5" />
    {:else}
      <Clock class="w-5 h-5" />
    {/if}
    {notification.message}
  </div>
{/if}

<style>
  select option {
    background: rgb(var(--bg-primary));
    color: rgb(var(--text-primary));
  }
</style>