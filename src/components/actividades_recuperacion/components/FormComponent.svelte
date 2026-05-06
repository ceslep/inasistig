<script>
  import { onMount } from 'svelte'
  import { fetchAsignaturas, fetchEstudiantes, fetchDocentes } from '../lib/data.js'
  import SkeletonLoader from './SkeletonLoader.svelte'
  import Icon from '@iconify/svelte'
  import Swal from 'sweetalert2'

  let { toast = null } = $props()

  let estudiantes = $state([])
  let grupos = $state([])
  let docentesList = $state([])
  let asignaturas = $state([])
  let loadingData = $state(true)

  const today = new Date().toISOString().split('T')[0]

  let grupo = $state('')
  let asignatura = $state('')
  let docenteSeleccionado = $state('')
  let fechaLimite = $state(today)
  let planMejoramiento = $state('')
  let planesIndividuales = $state({})
  let loading = $state(false)

  // Shake animation state
  let shakeFields = $state({})

  function triggerShake(fieldId) {
    shakeFields[fieldId] = true
    shakeFields = shakeFields
    setTimeout(() => {
      shakeFields[fieldId] = false
      shakeFields = shakeFields
    }, 500)
  }

  let estudiantesFiltrados = $derived(
    grupo ? estudiantes.filter(e => e.grupo === grupo) : []
  )

  let charCount = $derived(planMejoramiento.length)
  let charProgress = $derived(Math.min((charCount / 40) * 100, 100))

  let expandedStudent = $state(null)
  let estudiantesSeleccionados = $state([])

  function toggleStudent(name) {
    if (estudiantesSeleccionados.includes(name)) {
      estudiantesSeleccionados = estudiantesSeleccionados.filter(n => n !== name)
    } else {
      estudiantesSeleccionados = [...estudiantesSeleccionados, name]
    }
  }

  let studentsWithIndividualPlan = $derived(
    Object.entries(planesIndividuales)
      .filter(([_, plan]) => (plan || '').length >= 40)
      .map(([name]) => name)
  )

  let isFormValid = $derived.by(() => {
    if (!grupo || !asignatura || !docenteSeleccionado || !fechaLimite) return false
    if (estudiantesSeleccionados.length === 0) return false
    if (new Date(fechaLimite) <= new Date(new Date().setHours(0, 0, 0, 0))) return false

    const allHaveIndividualPlan = estudiantesSeleccionados.every(name => studentsWithIndividualPlan.includes(name))

    if (allHaveIndividualPlan) {
      return true
    }

    return planMejoramiento.length >= 40
  })

  let validationItems = $derived.by(() => {
    const items = []

    items.push({
      id: 'grupo',
      label: 'Grupo',
      value: grupo,
      completed: !!grupo,
      detail: grupo ? grupo : null
    })

    items.push({
      id: 'asignatura',
      label: 'Asignatura',
      value: asignatura,
      completed: !!asignatura,
      detail: asignatura ? asignatura.substring(0, 30) + (asignatura.length > 30 ? '...' : '') : null
    })

    items.push({
      id: 'docente',
      label: 'Docente',
      value: docenteSeleccionado,
      completed: !!docenteSeleccionado,
      detail: docenteSeleccionado ? docenteSeleccionado : null
    })

    const isValidDate = new Date(fechaLimite) > new Date(new Date().setHours(0, 0, 0, 0))
    items.push({
      id: 'fecha',
      label: 'Fecha límite',
      value: fechaLimite,
      completed: isValidDate,
      detail: isValidDate ? formatDate(fechaLimite) : 'Debe ser posterior a hoy'
    })

    items.push({
      id: 'estudiantes',
      label: 'Estudiantes',
      value: estudiantesSeleccionados.length,
      completed: estudiantesSeleccionados.length > 0,
      detail: estudiantesSeleccionados.length > 0
        ? `${estudiantesSeleccionados.length} seleccionado${estudiantesSeleccionados.length > 1 ? 's' : ''}`
        : 'Sin estudiantes seleccionados'
    })

    const allHaveIndividualPlan = estudiantesSeleccionados.length > 0 && estudiantesSeleccionados.every(name => studentsWithIndividualPlan.includes(name))

    if (!allHaveIndividualPlan) {
      items.push({
        id: 'plan',
        label: 'Plan general',
        value: planMejoramiento.length,
        completed: planMejoramiento.length >= 40,
        detail: planMejoramiento.length >= 40
          ? `${planMejoramiento.length}/40 caracteres`
          : `${planMejoramiento.length}/40 caracteres (mínimo 40)`
      })
    } else {
      items.push({
        id: 'plan',
        label: estudiantesSeleccionados.length === 1 ? 'Plan individual' : 'Planes individuales',
        value: 1,
        completed: true,
        detail: 'Completado'
      })
    }

    return items
  })

  let completedCount = $derived(validationItems.filter(i => i.completed).length)
  let totalCount = $derived(validationItems.length)
  let progressPercent = $derived(Math.round((completedCount / totalCount) * 100))

  function formatDate(dateStr) {
    const date = new Date(dateStr + 'T00:00:00')
    return date.toLocaleDateString('es-CO', { day: 'numeric', month: 'short', year: 'numeric' })
  }

  onMount(async () => {
    try {
      const [estData, docData, matData] = await Promise.all([fetchEstudiantes(), fetchDocentes(), fetchAsignaturas()])
      estudiantes = estData.estudiantes
      grupos = estData.grupos
      docentesList = docData
      asignaturas = matData

      const params = new URLSearchParams(window.location.search)
      const teacherParam = params.get('teacher')
      if (teacherParam && docentesList.includes(teacherParam)) {
        docenteSeleccionado = teacherParam
      }
    } catch (err) {
      console.error('Error cargando datos:', err)
      Swal.fire({
        title: 'Error',
        text: 'No se pudieron cargar los datos',
        icon: 'error',
        confirmButtonColor: '#2563eb'
      })
    } finally {
      loadingData = false
    }
  })

  let studentSearch = $state('')

  let estudiantesBuscados = $derived(
    studentSearch
      ? estudiantesFiltrados.filter(e => e.nombreCompleto.toLowerCase().includes(studentSearch.toLowerCase()))
      : estudiantesFiltrados
  )

  async function handleSubmit() {
    // Shake invalid fields
    const fieldsToCheck = [
      { id: 'grupo', valid: !!grupo },
      { id: 'asignatura', valid: !!asignatura },
      { id: 'docente', valid: !!docenteSeleccionado },
      { id: 'fecha', valid: fechaLimite && new Date(fechaLimite) > new Date(new Date().setHours(0, 0, 0, 0)) },
      { id: 'estudiantes', valid: estudiantesSeleccionados.length > 0 }
    ]

    const invalidFields = fieldsToCheck.filter(f => !f.valid)
    if (invalidFields.length > 0) {
      invalidFields.forEach(f => triggerShake(f.id))
      Swal.fire({
        title: 'Campos incompletos',
        text: 'Por favor completa todos los campos del formulario.',
        icon: 'warning',
        confirmButtonColor: '#2563eb'
      })
      return
    }

    if (estudiantesSeleccionados.length === 0) {
      triggerShake('estudiantes')
      Swal.fire({
        title: 'Sin estudiantes',
        text: 'Debes seleccionar al menos un estudiante.',
        icon: 'warning',
        confirmButtonColor: '#2563eb'
      })
      return
    }

    const allHaveIndividualPlan = estudiantesSeleccionados.every(name => studentsWithIndividualPlan.includes(name))
    const hasGeneral = planMejoramiento.length >= 40

    if (!allHaveIndividualPlan && !hasGeneral) {
      triggerShake('plan')
      Swal.fire({
        title: 'Plan incompleto',
        text: estudiantesSeleccionados.length === 1
          ? 'Escribe un plan individual o un plan general (min 40 caracteres).'
          : 'El plan general es obligatorio si no todos tienen plan individual (min 40 caracteres).',
        icon: 'warning',
        confirmButtonColor: '#2563eb'
      })
      return
    }

    const todayDate = new Date()
    todayDate.setHours(0, 0, 0, 0)
    if (new Date(fechaLimite) <= todayDate) {
      triggerShake('fecha')
      Swal.fire({
        title: 'Fecha invalida',
        text: 'La fecha limite debe ser posterior a hoy.',
        icon: 'warning',
        confirmButtonColor: '#2563eb'
      })
      return
    }

    loading = true
    try {
      const response = await fetch('https://app.iedeoccidente.com/gs/gsartirec.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          grupo,
          asignatura,
          nombresEstudiante: estudiantesSeleccionados,
          planMejoramiento,
          planesIndividuales: Object.fromEntries(
            estudiantesSeleccionados.map(n => [n, planesIndividuales[n] || ''])
          ),
          docenteSeleccionado,
          fechaLimite
        })
      })

      if (response.ok) {
        // Use toast instead of SweetAlert for success
        if (toast?.show) {
          toast.show('Plan de mejoramiento registrado correctamente.', 'success')
        } else {
          Swal.fire({
            title: 'Registrado',
            text: 'El plan de mejoramiento se registro correctamente.',
            icon: 'success',
            confirmButtonColor: '#2563eb'
          })
        }
        grupo = ''
        asignatura = ''
        docenteSeleccionado = ''
        fechaLimite = today
        planMejoramiento = ''
        planesIndividuales = {}
        estudiantesSeleccionados = []
        expandedStudent = null
      } else {
        Swal.fire({ title: 'Error', text: 'Error al enviar el formulario.', icon: 'error', confirmButtonColor: '#2563eb' })
      }
    } catch {
      Swal.fire({ title: 'Sin conexion', text: 'No se pudo conectar con el servidor.', icon: 'error', confirmButtonColor: '#2563eb' })
    } finally {
      loading = false
    }
  }
</script>

{#if loadingData}
  <SkeletonLoader type="form" />
{:else}
  <form onsubmit={(e) => { e.preventDefault(); handleSubmit() }} class="card p-6 sm:p-10 space-y-8">
    <!-- Validation Progress Bar (only when form is incomplete) -->
    {#if !isFormValid}
      <div class="sticky top-0 z-10 -mx-4 sm:-mx-6 px-4 sm:px-6 pb-4 sm:pb-6 bg-gradient-to-b from-white dark:from-slate-800 via-white/95 dark:via-slate-800/95 to-transparent">
        <div class="bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/50 dark:to-orange-950/50 border border-amber-200 dark:border-amber-800 rounded-xl p-4 shadow-lg shadow-amber-100/50 dark:shadow-amber-900/20">
          <!-- Header with progress -->
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              <Icon icon="mdi:clipboard-check-outline" class="text-amber-600 dark:text-amber-400 text-lg" />
              <span class="text-sm font-semibold text-amber-800 dark:text-amber-200">Progreso del formulario</span>
            </div>
            <div class="flex items-center gap-3">
              <span class="text-xs font-bold text-amber-600 dark:text-amber-400 bg-amber-100 dark:bg-amber-900/50 px-2 py-1 rounded-full">
                {completedCount}/{totalCount}
              </span>
              <span class="text-xs font-bold text-amber-700 dark:text-amber-300">{progressPercent}%</span>
            </div>
          </div>

          <!-- Progress bar -->
          <div class="h-2 bg-amber-100 dark:bg-amber-900/50 rounded-full overflow-hidden mb-3">
            <div class="h-full rounded-full bg-gradient-to-r from-amber-400 to-orange-400 transition-all duration-500 ease-out"
              style="width: {progressPercent}%"></div>
          </div>

          <!-- Items grid -->
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            {#each validationItems as item}
              <div class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition-all duration-300
                {item.completed ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300' : 'bg-amber-100/70 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300'}
                {item.completed ? 'field-complete' : ''}">
                {#if item.completed}
                  <Icon icon="mdi:check-circle" class="text-emerald-500 text-base shrink-0" />
                {:else}
                  <Icon icon="mdi:alert-circle-outline" class="text-amber-500 text-base shrink-0" />
                {/if}
                <div class="min-w-0">
                  <span class="font-medium">{item.label}:</span>
                  <span class="ml-1 truncate {item.completed ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'}">
                    {item.detail || (item.completed ? 'Completado' : 'Requerido')}
                  </span>
                </div>
              </div>
            {/each}
          </div>
        </div>
      </div>
    {/if}
    <!-- Section: Informacion General -->
    <div>
      <div class="flex items-center gap-2 mb-6">
        <div class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900/50 flex items-center justify-center">
          <Icon icon="mdi:information-outline" class="text-primary-600 dark:text-primary-400 text-lg" />
        </div>
        <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Informacion general</h2>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div class:shake={shakeFields.grupo}>
          <label for="grupo" class="field-label">
            <Icon icon="mdi:google-classroom" class="text-sm text-slate-400" />
            Grupo
          </label>
          <select id="grupo" bind:value={grupo} class="field-input">
            <option value="">Seleccione un grupo</option>
            {#each grupos as g}
              <option value={g}>{g}</option>
            {/each}
          </select>
        </div>

        <div class:shake={shakeFields.asignatura}>
          <label for="asignatura" class="field-label">
            <Icon icon="mdi:book-open-variant" class="text-sm text-slate-400" />
            Asignatura
          </label>
          <select id="asignatura" bind:value={asignatura} class="field-input">
            <option value="">Seleccione una asignatura</option>
            {#each asignaturas as a}
              <option value={a}>{a}</option>
            {/each}
          </select>
        </div>

        <div class:shake={shakeFields.docente}>
          <label for="docenteSeleccionado" class="field-label">
            <Icon icon="mdi:account-tie" class="text-sm text-slate-400" />
            Docente
          </label>
          <select id="docenteSeleccionado" bind:value={docenteSeleccionado} class="field-input">
            <option value="">Seleccione un docente</option>
            {#each docentesList as d}
              <option value={d}>{d}</option>
            {/each}
          </select>
        </div>

        <div class:shake={shakeFields.fecha}>
          <label for="fechaLimite" class="field-label">
            <Icon icon="mdi:calendar-clock" class="text-sm text-slate-400" />
            Fecha limite
          </label>
          <input type="date" id="fechaLimite" bind:value={fechaLimite} min={today} class="field-input" />
        </div>
      </div>
    </div>

    <!-- Divider -->
    <hr class="border-slate-100 dark:border-slate-700" />

    <!-- Section: Estudiantes -->
    <div class:shake={shakeFields.estudiantes}>
      <div class="flex items-center gap-2 mb-6">
        <div class="w-8 h-8 rounded-lg bg-violet-100 dark:bg-violet-900/50 flex items-center justify-center">
          <Icon icon="mdi:account-group" class="text-violet-600 dark:text-violet-400 text-lg" />
        </div>
        <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Estudiantes</h2>
        {#if !grupo}
          <span class="text-xs text-slate-400 dark:text-slate-500 ml-auto">Seleccione un grupo primero</span>
        {/if}
      </div>

      {#if !grupo}
        <div class="rounded-xl border border-dashed border-slate-200 dark:border-slate-600 bg-slate-50/50 dark:bg-slate-800/50 p-8 text-center">
          <Icon icon="mdi:account-search" class="text-3xl text-slate-300 dark:text-slate-600 mx-auto mb-2" />
          <p class="text-sm text-slate-400 dark:text-slate-500">Seleccione un grupo para ver los estudiantes</p>
        </div>
      {:else}
        <!-- Search -->
        <div class="flex flex-col sm:flex-row gap-2 mb-3">
          <div class="relative flex-1">
            <Icon icon="mdi:magnify" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base" />
            <input type="text" placeholder="Buscar estudiante..." bind:value={studentSearch}
              class="field-input pl-9 !py-2.5 text-sm" />
          </div>
          {#if estudiantesSeleccionados.length > 0}
            <button type="button" onclick={() => { planesIndividuales = {}; expandedStudent = null }}
              class="px-4 py-2.5 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all duration-200 flex items-center gap-2 shrink-0">
              <Icon icon="mdi:close-circle-outline" class="text-sm" />
              Limpiar seleccion
            </button>
          {/if}
        </div>

        <!-- Student list with inline textarea -->
        <div class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 max-h-[320px] overflow-y-auto divide-y divide-slate-50 dark:divide-slate-700">
          {#each estudiantesBuscados as est}
            {@const isSelected = estudiantesSeleccionados.includes(est.nombreCompleto)}
            {@const plan = planesIndividuales[est.nombreCompleto] || ''}
            {@const hasPlan = plan.length >= 40}
            {@const isExpanded = expandedStudent === est.nombreCompleto}
            <div class="transition-colors duration-150 {isSelected ? 'bg-primary-50/60 dark:bg-primary-950/30' : 'hover:bg-slate-50 dark:hover:bg-slate-700/50'}">
              <div class="flex items-center gap-3 px-4 py-3">
                <!-- Checkbox -->
                <button type="button" onclick={() => toggleStudent(est.nombreCompleto)}
                  class="w-5 h-5 rounded-md border-2 flex items-center justify-center shrink-0 transition-all duration-200
                    {isSelected ? 'bg-primary-600 border-primary-600' : 'border-slate-300 dark:border-slate-500 hover:border-primary-400'}">
                  {#if isSelected}
                    <Icon icon="mdi:check" class="text-white text-sm" />
                  {/if}
                </button>

                <!-- Name -->
                <span class="text-sm {isSelected ? 'text-primary-800 dark:text-primary-300 font-medium' : 'text-slate-600 dark:text-slate-300'} flex-1">
                  {est.nombreCompleto}
                </span>

                <!-- Expand button -->
                <button type="button" onclick={() => expandedStudent = expandedStudent === est.nombreCompleto ? null : est.nombreCompleto}
                  class="p-1 hover:bg-slate-100 dark:hover:bg-slate-600 rounded-lg transition-colors">
                  <Icon icon={isExpanded ? 'mdi:chevron-up' : 'mdi:chevron-down'}
                    class="text-slate-400 text-lg" />
                </button>
              </div>

              {#if isExpanded || isSelected}
                <div class="px-4 pb-4">
                  <textarea rows="4"
                    placeholder="Escribe aqui el plan de mejoramiento individual para {est.nombreCompleto}..."
                    value={planesIndividuales[est.nombreCompleto] || ''}
                    oninput={(e) => {
                      planesIndividuales[est.nombreCompleto] = e.target.value
                      planesIndividuales = planesIndividuales
                    }}
                    class="field-input resize-y text-sm"></textarea>
                  <div class="mt-2 flex items-center gap-3">
                    <div class="flex-1 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                      <div class="h-full rounded-full transition-all duration-300"
                        class:bg-rose-400={!hasPlan && plan.length > 0}
                        class:bg-emerald-400={hasPlan}
                        class:bg-slate-200={plan.length === 0}
                        style="width: {Math.min((plan.length / 40) * 100, 100)}%"></div>
                    </div>
                    <span class="text-xs font-medium tabular-nums w-14 text-right"
                      class:text-rose-500={!hasPlan && plan.length > 0}
                      class:text-emerald-500={hasPlan}
                      class:text-slate-400={plan.length === 0}>
                      {plan.length}/40
                    </span>
                  </div>
                </div>
              {/if}
            </div>
          {/each}
          {#if estudiantesBuscados.length === 0}
            <div class="p-6 text-center text-sm text-slate-400 dark:text-slate-500">
              No se encontraron estudiantes con "{studentSearch}"
            </div>
          {/if}
        </div>

        <!-- Selected count -->
        {#if estudiantesSeleccionados.length > 0}
          <div class="mt-3 flex items-center gap-2 flex-wrap">
            <span class="text-xs font-semibold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/30 px-2.5 py-1 rounded-full border border-primary-100 dark:border-primary-800">
              {estudiantesSeleccionados.length} seleccionado{estudiantesSeleccionados.length > 1 ? 's' : ''}
            </span>
            {#each estudiantesSeleccionados as name}
              <span class="inline-flex items-center gap-1 pl-2.5 pr-1 py-1 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs rounded-full">
                {name}
                <button type="button" onclick={() => toggleStudent(name)}
                  class="w-4 h-4 rounded-full hover:bg-slate-300 dark:hover:bg-slate-500 flex items-center justify-center transition-colors">
                  <Icon icon="mdi:close" class="text-[10px]" />
                </button>
              </span>
            {/each}
          </div>
          {#if estudiantesSeleccionados.length > 1}
            {@const hasSomeIndividual = estudiantesSeleccionados.some(n => studentsWithIndividualPlan.includes(n))}
            {@const allHaveIndividualPlan = estudiantesSeleccionados.every(n => studentsWithIndividualPlan.includes(n))}
            <div class="mt-2 text-xs {hasSomeIndividual ? 'text-violet-600 dark:text-violet-400 bg-violet-50 dark:bg-violet-950/50 border border-violet-200 dark:border-violet-800' : 'text-slate-500 dark:text-slate-400'} rounded-lg px-3 py-2 flex items-start gap-2">
              <Icon icon="mdi:information-outline" class="text-base shrink-0 mt-0.5" />
              {#if allHaveIndividualPlan}
                <span>Todos los estudiantes tienen <strong>plan individual</strong>. El plan general es opcional.</span>
              {:else if hasSomeIndividual}
                <span>Los estudiantes con plan individual usan el suyo. Los demas requieren el <strong>plan general</strong>.</span>
              {:else}
                <span>Todos requieren el <strong>plan general</strong> de abajo.</span>
              {/if}
            </div>
          {/if}
        {/if}
      {/if}
    </div>

    <!-- Divider -->
    <hr class="border-slate-100 dark:border-slate-700" />

    <!-- Section: Plan de mejoramiento -->
    <div class:shake={shakeFields.plan}>
      <div class="flex items-center gap-2 mb-6">
        <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center">
          <Icon icon="mdi:text-box-edit-outline" class="text-emerald-600 dark:text-emerald-400 text-lg" />
        </div>
        <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Plan de mejoramiento / Refuerzo</h2>
      </div>

      <!-- Plan general (siempre visible) -->
      <div class="mb-2">
        <label for="planMejoramiento" class="field-label mb-2">
          <Icon icon="mdi:account-group" class="text-sm text-emerald-500" />
          Plan general para estudiantes seleccionados
        </label>
        <textarea id="planMejoramiento" bind:value={planMejoramiento} rows="5"
          placeholder="Describe las actividades de refuerzo y mejoramiento academico para todos los estudiantes..."
          class="field-input resize-y"></textarea>

        <div class="mt-2 flex items-center gap-3">
          <div class="flex-1 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
            <div class="h-full rounded-full transition-all duration-300"
              class:bg-rose-400={charCount < 40}
              class:bg-emerald-400={charCount >= 40}
              style="width: {charProgress}%"></div>
          </div>
          <span class="text-xs font-medium tabular-nums"
            class:text-rose-500={charCount < 40}
            class:text-emerald-500={charCount >= 40}>
            {charCount}/40
          </span>
        </div>
      </div>


    </div>

    <!-- Submit -->
    <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-2">
      {#if !isFormValid && !loading}
        {@const missingItems = validationItems.filter(i => !i.completed)}
        <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
          <Icon icon="mdi:information-outline" class="text-slate-400" />
          <span>Completa:</span>
          {#each missingItems as item, i}
            <span class="text-amber-600 dark:text-amber-400 font-medium">{item.label}{i < missingItems.length - 1 ? ',' : ''}</span>
          {/each}
        </div>
      {/if}
      <button type="submit" disabled={loading || !isFormValid} class="btn-primary">
        {#if loading}
          <span class="submit-spinner"></span>
          Enviando...
        {:else}
          <Icon icon="mdi:send-variant" class="text-base" />
          Registrar plan
        {/if}
      </button>
    </div>
  </form>
{/if}

<style>
  .submit-spinner {
    width: 18px;
    height: 18px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
  }

  @keyframes spin {
    to { transform: rotate(360deg); }
  }
</style>
