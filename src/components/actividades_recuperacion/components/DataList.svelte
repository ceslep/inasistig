<script>
  import { onMount } from "svelte";
  import { generatePDF } from "../lib/pdf.js";
  import escudo from "../../../assets/eie.png";
  import SkeletonLoader from "./SkeletonLoader.svelte";
  import Icon from "@iconify/svelte";

  let data = $state([]);
  let loading = $state(true);
  let escudoBase64 = $state("");
  let searchText = $state("");
  let filterGrupo = $state("");
  let filterDocente = $state("");
  let expandedStudent = $state(null);
  let selectedStudent = $state(null);

  let uniqueGrupos = $derived(
    [...new Set(data.map((d) => d.grupo))].sort(
      (a, b) => Number(a) - Number(b),
    ),
  );
  let uniqueDocentes = $derived(
    [...new Set(data.map((d) => d.docente))].sort(),
  );

  let filteredData = $derived(
    data.filter((item) => {
      const matchesSearch =
        !searchText ||
        Object.values(item).some((v) =>
          String(v).toLowerCase().includes(searchText.toLowerCase()),
        );
      const matchesGrupo = !filterGrupo || item.grupo === filterGrupo;
      const matchesDocente = !filterDocente || item.docente === filterDocente;
      return matchesSearch && matchesGrupo && matchesDocente;
    }),
  );

  let uniqueStudents = $derived(
    Object.values(
      filteredData.reduce((acc, item) => {
        if (!acc[item.estudiante]) {
          acc[item.estudiante] = {
            estudiante: item.estudiante,
            grupo: item.grupo,
            records: [],
          };
        }
        acc[item.estudiante].records.push(item);
        return acc;
      }, {}),
    ).sort((a, b) => a.estudiante.localeCompare(b.estudiante)),
  );

  // --- Estadísticas globales reactivas ---
  let stats = $derived({
    totalEstudiantes: uniqueStudents.length,
    totalPlanes: filteredData.length,
    totalAsignaturas: [...new Set(filteredData.map((d) => d.asignatura))]
      .length,
    totalDocentes: [...new Set(filteredData.map((d) => d.docente))].length,
    totalGrupos: [...new Set(filteredData.map((d) => d.grupo))].length,
    promedioPlanesPorEstudiante:
      uniqueStudents.length > 0
        ? (filteredData.length / uniqueStudents.length).toFixed(1)
        : "0",
    asignaturaTop: (() => {
      const counts = {};
      filteredData.forEach((d) => {
        counts[d.asignatura] = (counts[d.asignatura] || 0) + 1;
      });
      const sorted = Object.entries(counts).sort((a, b) => b[1] - a[1]);
      return sorted[0]
        ? { nombre: sorted[0][0], cantidad: sorted[0][1] }
        : null;
    })(),
    fechaProxima: (() => {
      const hoy = new Date();
      hoy.setHours(0, 0, 0, 0);
      const futuras = filteredData
        .map((d) => new Date(d.fecha_limite))
        .filter((d) => d >= hoy)
        .sort((a, b) => a - b);
      return futuras[0] || null;
    })(),
  });

  // --- Distribución por asignatura para mini-chart ---
  let asignaturaDistribucion = $derived(() => {
    const counts = {};
    filteredData.forEach((d) => {
      counts[d.asignatura] = (counts[d.asignatura] || 0) + 1;
    });
    const total = filteredData.length || 1;
    return Object.entries(counts)
      .sort((a, b) => b[1] - a[1])
      .slice(0, 5)
      .map(([nombre, cantidad]) => ({
        nombre,
        cantidad,
        porcentaje: Math.round((cantidad / total) * 100),
      }));
  });

  const fieldLabels = {
    grupo: { label: "Grupo", icon: "mdi:google-classroom" },
    asignatura: { label: "Asignatura", icon: "mdi:book-open-variant" },
    docente: { label: "Docente", icon: "mdi:account-tie" },
    estudiante: { label: "Estudiante", icon: "mdi:account" },
    plan: { label: "Plan de mejoramiento", icon: "mdi:text-box-edit-outline" },
    fecha_limite: { label: "Fecha limite", icon: "mdi:calendar-clock" },
    fecha_registro: { label: "Fecha de registro", icon: "mdi:clock-outline" },
  };

  function clearFilters() {
    searchText = "";
    filterGrupo = "";
    filterDocente = "";
  }

  let hasActiveFilters = $derived(searchText || filterGrupo || filterDocente);

  // Active filter chips for sticky bar
  let activeFilterChips = $derived(() => {
    const chips = [];
    if (searchText)
      chips.push({
        key: "search",
        label: `"${searchText}"`,
        icon: "mdi:magnify",
        clear: () => (searchText = ""),
      });
    if (filterGrupo)
      chips.push({
        key: "grupo",
        label: `Grupo ${filterGrupo}`,
        icon: "mdi:google-classroom",
        clear: () => (filterGrupo = ""),
      });
    if (filterDocente)
      chips.push({
        key: "docente",
        label: filterDocente,
        icon: "mdi:account-tie",
        clear: () => (filterDocente = ""),
      });
    return chips;
  });

  onMount(async () => {
    await Promise.allSettled([
      (async () => {
        try {
          const res = await fetch(
            "https://app.iedeoccidente.com/gs/getgsartirec.php",
          );
          data = await res.json();
        } catch (err) {
          console.error("Error fetching data:", err);
        } finally {
          loading = false;
        }
      })(),
      (async () => {
        try {
          const res = await fetch(escudo);
          const blob = await res.blob();
          const reader = new FileReader();
          reader.onloadend = () => {
            escudoBase64 = reader.result;
          };
          reader.readAsDataURL(blob);
        } catch {
          /* ignore */
        }
      })(),
    ]);
  });

  function formatDate(dateStr) {
    try {
      return new Date(dateStr).toLocaleDateString("es-CO", {
        year: "numeric",
        month: "long",
        day: "numeric",
      });
    } catch {
      return dateStr;
    }
  }

  function formatDateShort(dateStr) {
    try {
      return new Date(dateStr).toLocaleDateString("es-CO", {
        month: "short",
        day: "numeric",
      });
    } catch {
      return dateStr;
    }
  }

  function diasRestantes(fechaStr) {
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    const fecha = new Date(fechaStr);
    fecha.setHours(0, 0, 0, 0);
    return Math.ceil((fecha - hoy) / (1000 * 60 * 60 * 24));
  }

  function estadoFecha(fechaStr) {
    const dias = diasRestantes(fechaStr);
    if (dias < 0)
      return {
        label: "Vencido",
        color:
          "text-rose-600 bg-rose-50 dark:bg-rose-950 border-rose-200 dark:border-rose-800",
        icon: "mdi:alert-circle",
        dot: "bg-rose-500",
      };
    if (dias <= 3)
      return {
        label: `${dias}d restante${dias !== 1 ? "s" : ""}`,
        color:
          "text-amber-600 bg-amber-50 dark:bg-amber-950 border-amber-200 dark:border-amber-800",
        icon: "mdi:clock-alert",
        dot: "bg-amber-500",
      };
    if (dias <= 7)
      return {
        label: `${dias} días`,
        color:
          "text-blue-600 bg-blue-50 dark:bg-blue-950 border-blue-200 dark:border-blue-800",
        icon: "mdi:calendar-clock",
        dot: "bg-blue-500",
      };
    return {
      label: `${dias} días`,
      color:
        "text-emerald-600 bg-emerald-50 dark:bg-emerald-950 border-emerald-200 dark:border-emerald-800",
      icon: "mdi:calendar-check",
      dot: "bg-emerald-500",
    };
  }

  function toggleExpanded(estudiante) {
    expandedStudent = expandedStudent === estudiante ? null : estudiante;
  }

  function openStudentDetail(student) {
    selectedStudent = student;
  }

  function closeStudentDetail() {
    selectedStudent = null;
  }

  // Portal action: monta el nodo directamente en <body> para escapar
  // de cualquier ancestro con transform/backdrop-filter que rompa position:fixed
  function portal(node) {
    document.body.appendChild(node);
    return {
      destroy() {
        if (node.parentNode) node.parentNode.removeChild(node);
      },
    };
  }

  // Cerrar modal con tecla Escape
  function handleKeydown(e) {
    if (e.key === "Escape" && selectedStudent) closeStudentDetail();
  }

  const avatarGradients = [
    "from-primary-400 to-primary-600",
    "from-violet-400 to-violet-600",
    "from-emerald-400 to-emerald-600",
    "from-amber-400 to-amber-600",
    "from-rose-400 to-rose-600",
    "from-cyan-400 to-cyan-600",
    "from-fuchsia-400 to-fuchsia-600",
    "from-teal-400 to-teal-600",
  ];

  function getAvatarGradient(name) {
    let hash = 0;
    for (let i = 0; i < name.length; i++)
      hash = name.charCodeAt(i) + ((hash << 5) - hash);
    return avatarGradients[Math.abs(hash) % avatarGradients.length];
  }

  function getInitials(name) {
    return name
      .split(" ")
      .slice(0, 2)
      .map((n) => n[0])
      .join("")
      .toUpperCase();
  }

  function handleGeneratePDF(item) {
    const studentRecords = filteredData.filter(
      (d) => d.estudiante === item.estudiante,
    );
    generatePDF(studentRecords, escudoBase64);
  }

  function handleGenerateGroupPDF() {
    const sorted = [...filteredData].sort((a, b) =>
      a.estudiante.localeCompare(b.estudiante),
    );
    generatePDF(sorted, escudoBase64);
  }

  const colors = [
    "bg-primary-500",
    "bg-violet-500",
    "bg-emerald-500",
    "bg-amber-500",
    "bg-rose-500",
  ];
</script>

<svelte:window onkeydown={handleKeydown} />

<div class="space-y-5">
  <!-- Filtros -->
  <div class="card p-5 sm:p-6">
    <div
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5"
    >
      <div class="flex items-center gap-2">
        <div
          class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900/50 flex items-center justify-center"
        >
          <Icon
            icon="mdi:format-list-bulleted"
            class="text-primary-600 dark:text-primary-400 text-lg"
          />
        </div>
        <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">
          Registros
        </h2>
      </div>
      <div class="flex items-center gap-2">
        {#if hasActiveFilters}
          <button
            onclick={clearFilters}
            class="text-xs font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 flex items-center gap-1 transition-colors"
          >
            <Icon icon="mdi:filter-remove" class="text-sm" />
            Limpiar filtros
          </button>
        {/if}
        {#if filterGrupo && filteredData.length > 0}
          <button
            onclick={handleGenerateGroupPDF}
            class="btn-danger text-xs flex items-center gap-1"
          >
            <Icon icon="mdi:file-pdf-box" class="text-sm" />
            PDF Grupo {filterGrupo}
          </button>
        {/if}
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <div class="relative">
        <Icon
          icon="mdi:magnify"
          class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base"
        />
        <input
          type="text"
          placeholder="Buscar en todos los campos..."
          bind:value={searchText}
          class="field-input pl-10"
        />
      </div>
      <select bind:value={filterGrupo} class="field-input">
        <option value="">Todos los grupos</option>
        {#each uniqueGrupos as g}
          <option value={g}>Grupo {g}</option>
        {/each}
      </select>
      <select bind:value={filterDocente} class="field-input">
        <option value="">Todos los docentes</option>
        {#each uniqueDocentes as d}
          <option value={d}>{d}</option>
        {/each}
      </select>
    </div>
  </div>

  <!-- Sticky filter chips bar -->
  {#if hasActiveFilters && !loading}
    <div class="sticky-filter-bar">
      <div class="flex items-center gap-2 flex-wrap">
        <Icon
          icon="mdi:filter-variant"
          class="text-primary-500 dark:text-primary-400 text-sm shrink-0"
        />
        <span
          class="text-xs font-medium text-slate-500 dark:text-slate-400 shrink-0"
          >Filtros activos:</span
        >
        {#each activeFilterChips() as chip (chip.key)}
          <span class="filter-chip">
            <Icon icon={chip.icon} class="text-xs" />
            <span class="truncate max-w-[150px]">{chip.label}</span>
            <button
              onclick={chip.clear}
              class="w-4 h-4 rounded-full hover:bg-primary-200 dark:hover:bg-primary-700 flex items-center justify-center transition-colors ml-0.5"
            >
              <Icon icon="mdi:close" class="text-[10px]" />
            </button>
          </span>
        {/each}
        <span
          class="text-xs text-slate-400 dark:text-slate-500 ml-auto shrink-0"
        >
          {filteredData.length} resultado{filteredData.length !== 1 ? "s" : ""}
        </span>
      </div>
    </div>
  {/if}

  <!-- Estadísticas globales -->
  {#if loading}
    <SkeletonLoader type="cards" />
  {:else if data.length > 0}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
      <!-- Estudiantes -->
      <div class="stat-card group stagger-item" style="animation-delay: 0ms">
        <div class="flex items-center justify-between mb-3">
          <div
            class="w-10 h-10 rounded-xl bg-primary-100 dark:bg-primary-900/50 flex items-center justify-center transition-transform group-hover:scale-110"
          >
            <Icon
              icon="mdi:account-group"
              class="text-primary-600 dark:text-primary-400 text-xl"
            />
          </div>
          {#if hasActiveFilters}
            <span
              class="text-[10px] font-medium text-primary-500 bg-primary-50 dark:bg-primary-900/50 px-1.5 py-0.5 rounded-md"
              >Filtrado</span
            >
          {/if}
        </div>
        <p
          class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 tracking-tight"
        >
          {stats.totalEstudiantes}
        </p>
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
          Estudiantes
        </p>
      </div>

      <!-- Planes -->
      <div class="stat-card group stagger-item" style="animation-delay: 80ms">
        <div class="flex items-center justify-between mb-3">
          <div
            class="w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-900/50 flex items-center justify-center transition-transform group-hover:scale-110"
          >
            <Icon
              icon="mdi:text-box-multiple"
              class="text-violet-600 dark:text-violet-400 text-xl"
            />
          </div>
          <span
            class="text-[10px] font-medium text-slate-400 dark:text-slate-500"
            >{stats.promedioPlanesPorEstudiante}/est.</span
          >
        </div>
        <p
          class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 tracking-tight"
        >
          {stats.totalPlanes}
        </p>
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
          Planes de mejoramiento
        </p>
      </div>

      <!-- Asignaturas -->
      <div class="stat-card group stagger-item" style="animation-delay: 160ms">
        <div class="flex items-center justify-between mb-3">
          <div
            class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center transition-transform group-hover:scale-110"
          >
            <Icon
              icon="mdi:book-open-variant"
              class="text-emerald-600 dark:text-emerald-400 text-xl"
            />
          </div>
          {#if stats.asignaturaTop}
            <span
              class="text-[10px] font-medium text-emerald-500 bg-emerald-50 dark:bg-emerald-900/50 px-1.5 py-0.5 rounded-md truncate max-w-[80px]"
              title="Más frecuente: {stats.asignaturaTop.nombre}"
            >
              Top: {stats.asignaturaTop.cantidad}
            </span>
          {/if}
        </div>
        <p
          class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 tracking-tight"
        >
          {stats.totalAsignaturas}
        </p>
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
          Asignaturas
        </p>
      </div>

      <!-- Docentes -->
      <div class="stat-card group stagger-item" style="animation-delay: 240ms">
        <div class="flex items-center justify-between mb-3">
          <div
            class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center transition-transform group-hover:scale-110"
          >
            <Icon
              icon="mdi:account-tie"
              class="text-amber-600 dark:text-amber-400 text-xl"
            />
          </div>
          <span
            class="text-[10px] font-medium text-slate-400 dark:text-slate-500"
            >{stats.totalGrupos} grupos</span
          >
        </div>
        <p
          class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 tracking-tight"
        >
          {stats.totalDocentes}
        </p>
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
          Docentes
        </p>
      </div>
    </div>

    <!-- Barra de distribución por asignatura -->
    {#if asignaturaDistribucion().length > 0}
      <div class="card p-4 sm:p-5 stagger-item" style="animation-delay: 320ms">
        <div class="flex items-center gap-2 mb-3">
          <Icon icon="mdi:chart-bar" class="text-slate-400 text-base" />
          <h3
            class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"
          >
            Distribución por asignatura
          </h3>
          <span class="text-[10px] text-slate-400 dark:text-slate-500 ml-auto"
            >Top 5</span
          >
        </div>
        <div class="space-y-2.5">
          {#each asignaturaDistribucion() as item, i}
            <div class="flex items-center gap-3">
              <span
                class="text-xs text-slate-600 dark:text-slate-300 w-28 sm:w-40 truncate font-medium"
                title={item.nombre}>{item.nombre}</span
              >
              <div
                class="flex-1 h-6 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden relative"
              >
                <div
                  class="h-full rounded-full transition-all duration-700 ease-out {colors[
                    i % colors.length
                  ]}"
                  style="width: {item.porcentaje}%"
                ></div>
                <span
                  class="absolute inset-0 flex items-center justify-end pr-2 text-[10px] font-bold {item.porcentaje >
                  50
                    ? 'text-white'
                    : 'text-slate-500 dark:text-slate-300'}"
                >
                  {item.cantidad}
                </span>
              </div>
              <span
                class="text-[10px] text-slate-400 dark:text-slate-500 w-8 text-right font-medium"
                >{item.porcentaje}%</span
              >
            </div>
          {/each}
        </div>
      </div>
    {/if}

    <!-- Próxima fecha límite -->
    {#if stats.fechaProxima}
      <div
        class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/50 dark:to-orange-950/50 border border-amber-200/60 dark:border-amber-800/60 stagger-item"
        style="animation-delay: 400ms"
      >
        <Icon icon="mdi:bell-ring-outline" class="text-amber-500 text-lg" />
        <p class="text-xs text-amber-700 dark:text-amber-300">
          <span class="font-semibold">Próxima fecha límite:</span>
          {formatDate(stats.fechaProxima.toISOString())}
          <span class="text-amber-500 dark:text-amber-400 ml-1"
            >({diasRestantes(stats.fechaProxima.toISOString())} días)</span
          >
        </p>
      </div>
    {/if}
  {/if}

  <!-- Resultados -->
  {#if !loading && uniqueStudents.length === 0}
    <!-- Empty state ilustrado -->
    <div class="card p-12 text-center stagger-item">
      <div class="mx-auto mb-6 w-48 h-48">
        <svg
          viewBox="0 0 200 200"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
          class="w-full h-full"
        >
          <circle
            cx="100"
            cy="100"
            r="90"
            class="fill-slate-100 dark:fill-slate-800"
          />
          <rect
            x="45"
            y="75"
            width="110"
            height="80"
            rx="8"
            class="fill-slate-200 dark:fill-slate-700"
          />
          <path
            d="M45 83C45 78.5817 48.5817 75 53 75H80L90 65H137C141.418 65 145 68.5817 145 73V75H45V83Z"
            class="fill-slate-300 dark:fill-slate-600"
          />
          <rect
            x="40"
            y="85"
            width="120"
            height="70"
            rx="6"
            class="fill-white dark:fill-slate-700"
            stroke-width="1.5"
            class:stroke-slate-200={true}
          />
          <circle
            cx="100"
            cy="115"
            r="18"
            class="stroke-primary-400 dark:stroke-primary-500"
            stroke-width="3"
            fill="none"
          />
          <line
            x1="113"
            y1="128"
            x2="125"
            y2="140"
            class="stroke-primary-400 dark:stroke-primary-500"
            stroke-width="3"
            stroke-linecap="round"
          />
          <text
            x="95"
            y="122"
            class="fill-primary-400 dark:fill-primary-500"
            font-size="20"
            font-weight="bold"
            font-family="sans-serif">?</text
          >
          <circle
            cx="50"
            cy="55"
            r="3"
            class="fill-primary-200 dark:fill-primary-700"
            opacity="0.6"
          />
          <circle
            cx="160"
            cy="60"
            r="4"
            class="fill-violet-200 dark:fill-violet-700"
            opacity="0.6"
          />
          <circle
            cx="35"
            cy="150"
            r="3"
            class="fill-emerald-200 dark:fill-emerald-700"
            opacity="0.6"
          />
          <circle
            cx="170"
            cy="145"
            r="5"
            class="fill-amber-200 dark:fill-amber-700"
            opacity="0.5"
          />
        </svg>
      </div>
      <p class="text-slate-600 dark:text-slate-300 font-semibold text-lg mb-1">
        No se encontraron registros
      </p>
      <p class="text-sm text-slate-400 dark:text-slate-500 max-w-sm mx-auto">
        {#if hasActiveFilters}
          No hay resultados para los filtros actuales. Intenta ajustar los
          criterios de busqueda.
        {:else}
          Aun no hay planes de mejoramiento registrados. Usa el formulario para
          crear el primero.
        {/if}
      </p>
      {#if hasActiveFilters}
        <button
          onclick={clearFilters}
          class="mt-4 inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/30 hover:bg-primary-100 dark:hover:bg-primary-900/50 rounded-xl transition-colors"
        >
          <Icon icon="mdi:filter-remove" class="text-base" />
          Limpiar filtros
        </button>
      {/if}
    </div>
  {:else if !loading}
    <!-- Resumen de resultados -->
    <div class="flex items-center justify-between px-1">
      <p class="text-xs text-slate-400 dark:text-slate-500">
        Mostrando <span class="font-semibold text-slate-600 dark:text-slate-300"
          >{uniqueStudents.length}</span
        >
        estudiante{uniqueStudents.length !== 1 ? "s" : ""} con
        <span class="font-semibold text-slate-600 dark:text-slate-300"
          >{filteredData.length}</span
        > planes
      </p>
    </div>

    <!-- Grid responsivo multi-columna -->
    <div class="student-grid">
      {#each uniqueStudents as student, idx}
        {@const recordCount = student.records.length}
        {@const peorEstado = student.records.reduce((peor, r) => {
          const dias = diasRestantes(r.fecha_limite);
          return dias < peor ? dias : peor;
        }, Infinity)}
        {@const statusColor =
          peorEstado < 0
            ? "rose"
            : peorEstado <= 3
              ? "amber"
              : peorEstado <= 7
                ? "blue"
                : "emerald"}
        {@const statusDotClass = `bg-${statusColor}-500`}
        {@const statusBorderClass = `border-t-${statusColor}-500`}

        <button
          onclick={() => openStudentDetail(student)}
          class="student-card card border-t-4 {statusBorderClass} text-left cursor-pointer group stagger-item"
          style="animation-delay: {Math.min(idx * 60, 600)}ms"
        >
          <!-- Alerta blink si tiene más de 2 registros -->
          {#if recordCount > 2}
            <div
              class="alert-badge"
              title="Este estudiante tiene {recordCount} planes de mejoramiento"
            >
              <span class="alert-ring"></span>
              <span class="alert-ring alert-ring-2"></span>
              <span class="alert-icon-wrap">
                <Icon icon="mdi:alert" class="text-[13px] relative z-10" />
              </span>
              <span class="alert-label">{recordCount} planes</span>
            </div>
          {/if}

          <!-- Top: Avatar + nombre -->
          <div class="flex items-start gap-3 mb-3">
            <div
              class="w-11 h-11 rounded-xl bg-gradient-to-br {getAvatarGradient(
                student.estudiante,
              )} flex items-center justify-center text-white font-bold text-xs shadow-md shrink-0"
            >
              {getInitials(student.estudiante)}
            </div>
            <div class="flex-1 min-w-0">
              <h3
                class="text-[13px] font-bold text-slate-800 dark:text-slate-100 leading-tight line-clamp-2"
              >
                {student.estudiante}
              </h3>
              <p
                class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5 flex items-center gap-1"
              >
                <Icon icon="mdi:google-classroom" class="text-xs" />
                Grupo {student.grupo}
              </p>
            </div>
          </div>

          <!-- Asignaturas como chips -->
          <div class="flex flex-wrap gap-1.5 mb-3">
            {#each student.records as record}
              {@const estado = estadoFecha(record.fecha_limite)}
              <span
                class="inline-flex items-center gap-1 text-[10px] font-medium px-2 py-1 rounded-lg border {estado.color}"
              >
                <span class="w-1.5 h-1.5 rounded-full {estado.dot} shrink-0"
                ></span>
                <span class="truncate max-w-[120px]">{record.asignatura}</span>
              </span>
            {/each}
          </div>

          <!-- Footer: count + fecha -->
          <div
            class="mt-auto flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-700"
          >
            <div class="flex items-center gap-1.5">
              <Icon
                icon="mdi:text-box-multiple"
                class="text-xs text-slate-400 dark:text-slate-500"
              />
              <span
                class="text-[11px] font-semibold text-slate-500 dark:text-slate-400"
              >
                {recordCount}
                {recordCount === 1 ? "plan" : "planes"}
              </span>
            </div>
            <span
              class="text-[10px] text-slate-400 dark:text-slate-500 flex items-center gap-1"
            >
              <Icon icon="mdi:calendar-clock" class="text-[11px]" />
              {formatDateShort(student.records[0].fecha_limite)}
            </span>
          </div>

          <!-- Hover overlay hint -->
          <div
            class="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none flex items-center justify-center bg-slate-900/5 dark:bg-slate-100/5"
          >
            <span
              class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm text-xs font-semibold text-primary-600 dark:text-primary-400 px-3 py-1.5 rounded-full shadow-lg"
            >
              <Icon icon="mdi:eye-outline" class="text-sm inline -mt-0.5" /> Ver
              detalle
            </span>
          </div>
        </button>
      {/each}
    </div>
  {/if}
</div>

<!-- ============================================================
     MODAL — montado vía portal directamente en <body>
     Esto evita que position:fixed quede atrapado en el stacking
     context de ancestros con transform o backdrop-filter.
     ============================================================ -->
{#if selectedStudent}
  <div use:portal>
    <!-- svelte-ignore a11y_no_static_element_interactions, a11y_click_events_have_key_events -->
    <div
      class="modal-backdrop"
      onclick={closeStudentDetail}
      onkeydown={(e) => e.key === 'Escape' && closeStudentDetail()}
      role="dialog"
      aria-modal="true"
      aria-label="Detalle del estudiante"
      tabindex="-1"
    >
      <!-- svelte-ignore a11y_no_static_element_interactions, a11y_click_events_have_key_events -->
      <div class="modal-content" onclick={(e) => e.stopPropagation()} onkeydown={(e) => e.stopPropagation()}>
        <!-- Header del modal -->
        <div
          class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700 shrink-0"
        >
          <div class="flex items-center gap-3">
            <div
              class="w-12 h-12 rounded-xl bg-gradient-to-br {getAvatarGradient(
                selectedStudent.estudiante,
              )} flex items-center justify-center text-white font-bold text-sm shadow-md shrink-0"
            >
              {getInitials(selectedStudent.estudiante)}
            </div>
            <div>
              <h3
                class="text-base font-bold text-slate-800 dark:text-slate-100"
              >
                {selectedStudent.estudiante}
              </h3>
              <p
                class="text-xs text-slate-400 dark:text-slate-500 flex items-center gap-1.5"
              >
                <Icon icon="mdi:google-classroom" class="text-sm" />
                Grupo {selectedStudent.grupo}
                <span class="text-slate-200 dark:text-slate-600">|</span>
                {selectedStudent.records.length}
                {selectedStudent.records.length === 1 ? "plan" : "planes"}
              </p>
            </div>
          </div>
          <button
            onclick={closeStudentDetail}
            class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition-colors text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
            aria-label="Cerrar"
          >
            <Icon icon="mdi:close" class="text-lg" />
          </button>
        </div>

        <!-- Body del modal -->
        <div class="p-5 space-y-3 overflow-y-auto modal-body">
          {#each selectedStudent.records as record, idx}
            {@const estado = estadoFecha(record.fecha_limite)}
            <div
              class="rounded-xl border border-slate-100 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 p-4 space-y-3"
            >
              <!-- Header del plan -->
              <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5">
                <span
                  class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200"
                >
                  <Icon
                    icon="mdi:book-open-variant"
                    class="text-sm text-primary-500"
                  />
                  {record.asignatura}
                </span>
                <span
                  class="inline-flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400"
                >
                  <Icon icon="mdi:account-tie" class="text-sm text-slate-400" />
                  {record.docente}
                </span>
                <span
                  class="inline-flex items-center gap-1 text-[11px] font-medium border px-2 py-0.5 rounded-full {estado.color}"
                >
                  <span class="w-1.5 h-1.5 rounded-full {estado.dot}"></span>
                  {estado.label}
                </span>
              </div>

              <!-- Plan de mejoramiento -->
              <div
                class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line"
              >
                {record.plan}
              </div>

              <!-- Fechas -->
              <div
                class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-slate-400 dark:text-slate-500"
              >
                <span class="flex items-center gap-1">
                  <Icon icon="mdi:calendar-clock" class="text-xs" />
                  Límite:
                  <span class="font-medium text-slate-500 dark:text-slate-400"
                    >{formatDate(record.fecha_limite)}</span
                  >
                </span>
                <span class="flex items-center gap-1">
                  <Icon icon="mdi:clock-outline" class="text-xs" />
                  Registro: {formatDateShort(record.fecha_registro)}
                </span>
              </div>
            </div>
          {/each}
        </div>

        <!-- Footer del modal -->
        <div
          class="flex justify-end gap-2 p-5 border-t border-slate-100 dark:border-slate-700 shrink-0"
        >
          <button
            onclick={closeStudentDetail}
            class="px-4 py-2 text-xs font-medium text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors"
          >
            Cerrar
          </button>
          <button
            onclick={() => handleGeneratePDF(selectedStudent.records[0])}
            class="btn-danger"
          >
            <Icon icon="mdi:file-pdf-box" class="text-sm" />
            Generar PDF
          </button>
        </div>
      </div>
    </div>
  </div>
{/if}

<style>
  .stat-card {
    background: #ffffff;
    border-radius: 1rem;
    padding: 1.25rem;
    border: 1px solid #e2e8f0;
    box-shadow:
      0 2px 8px rgba(148, 163, 184, 0.2),
      0 1px 2px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
  }

  :global(.dark) .stat-card {
    background: rgba(30, 41, 59, 0.9);
    border-color: rgba(51, 65, 85, 0.7);
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.25);
  }

  .stat-card:hover {
    box-shadow: 0 8px 28px rgba(148, 163, 184, 0.3);
    border-color: #cbd5e1;
    transform: translateY(-2px);
  }

  :global(.dark) .stat-card:hover {
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35);
    border-color: rgba(71, 85, 105, 0.8);
  }

  .line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  /* Sticky filter chips bar */
  .sticky-filter-bar {
    position: sticky;
    top: 0;
    z-index: 20;
    padding: 0.625rem 1rem;
    border-radius: 0.875rem;
    border: 1px solid rgba(59, 130, 246, 0.25);
    backdrop-filter: blur(16px);
    background: rgba(239, 246, 255, 0.96);
    box-shadow:
      0 4px 16px rgba(59, 130, 246, 0.12),
      0 1px 3px rgba(0, 0, 0, 0.06);
    animation: staggerIn 0.3s ease-out forwards;
  }

  :global(.dark) .sticky-filter-bar {
    background: rgba(30, 41, 59, 0.85);
    border-color: rgba(59, 130, 246, 0.2);
  }

  .filter-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem 0.25rem 0.625rem;
    font-size: 0.6875rem;
    font-weight: 600;
    color: #1d4ed8;
    background: #eff6ff;
    border: 1px solid rgba(59, 130, 246, 0.35);
    border-radius: 9999px;
    transition: all 0.2s;
    animation: staggerIn 0.25s ease-out forwards;
    box-shadow: 0 1px 2px rgba(59, 130, 246, 0.08);
  }

  :global(.dark) .filter-chip {
    color: #93c5fd;
    background: rgba(30, 58, 138, 0.3);
    border-color: rgba(59, 130, 246, 0.3);
  }

  .filter-chip:hover {
    background: #dbeafe;
    border-color: rgba(59, 130, 246, 0.5);
  }

  :global(.dark) .filter-chip:hover {
    background: rgba(30, 58, 138, 0.5);
  }

  /* Grid responsivo: 1 col mobile, 2 tablet, 3 desktop, 4 wide */
  .student-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }

  @media (min-width: 540px) {
    .student-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (min-width: 900px) {
    .student-grid {
      grid-template-columns: repeat(3, 1fr);
    }
  }

  @media (min-width: 1280px) {
    .student-grid {
      grid-template-columns: repeat(4, 1fr);
    }
  }

  /* Tarjeta de estudiante */
  .student-card {
    position: relative;
    display: flex;
    flex-direction: column;
    padding: 1.25rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
  }

  .student-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(148, 163, 184, 0.2);
  }

  :global(.dark) .student-card:hover {
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
  }

  /* ============================================================
     MODAL
     position: fixed funciona correctamente porque el nodo está
     montado directamente en <body> vía la action `portal`,
     sin ningún ancestro con transform o backdrop-filter.
     ============================================================ */
  /* ============================================================
     ALERTA ROJA — tarjetas con más de 2 registros
     ============================================================ */

  /* Contenedor badge: esquina superior derecha de la tarjeta */
  .alert-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 9px 3px 6px;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.04em;
    color: #fff;
    background: #dc2626;
    border-radius: 999px;
    z-index: 3;
    /* Titileo principal: el badge entero parpadea entre rojo vivo y rojo oscuro */
    animation: alertBlink 0.85s step-start infinite;
    /* Borde blanco fino para que resalte sobre cualquier fondo */
    outline: 2px solid rgba(255, 255, 255, 0.55);
    outline-offset: 1px;
  }

  /* El ícono también oscila de tamaño para mayor impacto */
  .alert-badge :global(svg) {
    animation: iconShake 0.85s step-start infinite;
    flex-shrink: 0;
  }

  /* Texto del contador */
  .alert-label {
    line-height: 1;
    white-space: nowrap;
  }

  /* Anillo expansivo #1 — se expande y desvanece como sonar */
  .alert-ring {
    position: absolute;
    inset: -4px;
    border-radius: 999px;
    border: 2px solid #ef4444;
    animation: alertRing 1.6s ease-out infinite;
    pointer-events: none;
  }

  /* Anillo #2 — desfasado 0.5s para efecto sonar continuo */
  .alert-ring-2 {
    animation-delay: 0.55s;
  }

  /* Ícono centrado con z-index para quedar encima de los anillos */
  .alert-icon-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    z-index: 1;
  }

  /* Titileo clásico: alterna entre rojo vivo y un rojo más oscuro/apagado */
  @keyframes alertBlink {
    0%,
    49% {
      background: #dc2626;
      box-shadow:
        0 0 0 0 rgba(220, 38, 38, 0),
        0 2px 12px rgba(220, 38, 38, 0.7);
    }
    50%,
    100% {
      background: #7f1d1d;
      box-shadow:
        0 0 0 0 rgba(220, 38, 38, 0),
        0 2px 4px rgba(220, 38, 38, 0.2);
    }
  }

  /* Shake del ícono sincronizado con el blink */
  @keyframes iconShake {
    0%,
    49% {
      transform: scale(1.2) rotate(-8deg);
    }
    50%,
    100% {
      transform: scale(0.9) rotate(4deg);
    }
  }

  /* Anillo sonar: escala de 1 → 1.8 y desaparece */
  @keyframes alertRing {
    0% {
      transform: scale(1);
      opacity: 0.8;
    }
    70% {
      transform: scale(1.9);
      opacity: 0.15;
    }
    100% {
      transform: scale(2.2);
      opacity: 0;
    }
  }

  :global(.modal-backdrop) {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(15, 23, 42, 0.55);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    animation: fadeIn 0.2s ease-out;
  }

  :global(.modal-content) {
    background: #ffffff;
    border-radius: 1.25rem;
    width: 100%;
    max-width: 600px;
    max-height: calc(100vh - 2rem);
    display: flex;
    flex-direction: column;
    border: 1px solid #e2e8f0;
    box-shadow:
      0 25px 60px rgba(15, 23, 42, 0.22),
      0 8px 20px rgba(15, 23, 42, 0.1);
    animation: slideUp 0.25s ease-out;
  }

  :global(.dark) :global(.modal-content) {
    background: #1e293b;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.45);
  }

  :global(.modal-body) {
    flex: 1 1 auto;
    overflow-y: auto;
    min-height: 0; /* necesario para que flex+overflow funcione */
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
    }
    to {
      opacity: 1;
    }
  }

  @keyframes slideUp {
    from {
      opacity: 0;
      transform: translateY(20px) scale(0.97);
    }
    to {
      opacity: 1;
      transform: translateY(0) scale(1);
    }
  }
</style>
