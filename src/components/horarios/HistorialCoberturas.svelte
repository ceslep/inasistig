<script lang="ts">
  import { onMount } from "svelte";
  import Chart from "chart.js/auto";
  import type { CoberturaHistorica } from "../../lib/coberturaUtils";
  import { formatoDia, formatoHora } from "../../lib/coberturaUtils";
  import { coberturaSheetsService } from "../../services/coberturaSheetsService";
  import Swal from "sweetalert2";
  import { BarChart3, PieChart as PieChartIcon, Filter, X, TrendingUp, Users, Clock } from "@lucide/svelte";

  let {
    coberturasHistoricas,
    loading,
    onReload,
    onGenerarReporte,
    onGenerarWhatsApp,
  }: {
    coberturasHistoricas: CoberturaHistorica[];
    loading: boolean;
    onReload: () => void;
    onGenerarReporte?: (fecha: string) => void;
    onGenerarWhatsApp?: (fecha: string) => void;
  } = $props();

  let filterFechaDesde = $state("");
  let filterFechaHasta = $state("");
  let filterDocenteAusente = $state("");
  let filterDocenteCubre = $state("");
  let filterEstado = $state("");
  let showStats = $state(false);
  let barChartCanvas = $state.raw<HTMLCanvasElement | undefined>(undefined);
  let doughnutChartCanvas = $state.raw<HTMLCanvasElement | undefined>(undefined);
  let barChartInstance: Chart | null = null;
  let doughnutChartInstance: Chart | null = null;

  $effect(() => {
    if (coberturasHistoricas.length > 0) {
      const todasFechas = [...new Set(coberturasHistoricas.map((c) => c.fecha))].sort().reverse();
      if (todasFechas.length > 0) {
        const maxFecha = todasFechas[0];
        filterFechaHasta = maxFecha;
        const d = new Date(maxFecha + "T00:00:00");
        d.setDate(d.getDate() - 5);
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, "0");
        const day = String(d.getDate()).padStart(2, "0");
        filterFechaDesde = `${y}-${m}-${day}`;
      }
    }
  });

  const filtradas = $derived(
    coberturasHistoricas.filter((c) => {
      if (filterFechaDesde && c.fecha < filterFechaDesde) return false;
      if (filterFechaHasta && c.fecha > filterFechaHasta) return false;
      if (filterDocenteAusente && !c.docente_ausente.toLowerCase().includes(filterDocenteAusente.toLowerCase())) return false;
      if (filterDocenteCubre && !c.docente_cubre.toLowerCase().includes(filterDocenteCubre.toLowerCase())) return false;
      if (filterEstado && c.estado !== filterEstado) return false;
      return true;
    })
  );

  const docentesAusentesList = $derived.by(() => {
    const set = new Set<string>();
    for (const c of coberturasHistoricas) if (c.docente_ausente) set.add(c.docente_ausente);
    return [...set].sort();
  });

  const docentesCubreList = $derived.by(() => {
    const set = new Set<string>();
    for (const c of coberturasHistoricas) if (c.docente_cubre) set.add(c.docente_cubre);
    return [...set].sort();
  });

  const estadosList = $derived(["aprobado", "rechazado", "pendiente"]);

  const statsData = $derived.by(() => {
    const total = filtradas.length;
    const aprobadas = filtradas.filter(c => c.estado === "aprobado").length;
    const rechazadas = filtradas.filter(c => c.estado === "rechazado").length;
    const pendientes = filtradas.filter(c => c.estado === "pendiente").length;
    const docentesCubren = new Set(filtradas.map(c => c.docente_cubre)).size;
    const gruposUnicos = new Set(filtradas.map(c => c.grupo_a_cubrir)).size;
    const porDia: Record<string, number> = {};
    for (const c of filtradas) {
      porDia[c.dia_semana] = (porDia[c.dia_semana] || 0) + 1;
    }
    const porHora: Record<number, number> = {};
    for (const c of filtradas) {
      porHora[c.hora] = (porHora[c.hora] || 0) + 1;
    }
    return { total, aprobadas, rechazadas, pendientes, docentesCubren, gruposUnicos, porDia, porHora };
  });

  const fechasDisponibles = $derived.by(() => {
    const fechas = [...new Set(coberturasHistoricas.map((c) => c.fecha))].sort().reverse();
    return fechas.slice(0, 5);
  });

  let fechaSeleccionadaReporte = $state("");

  // Selección múltiple para borrado por lotes.
  const claveCobertura = (c: CoberturaHistorica) =>
    `${c.fecha}|${c.hora}|${c.docente_cubre}|${c.docente_ausente}`;

  let seleccionadas = $state<Set<string>>(new Set());
  let eliminando = $state(false);

  const todasSeleccionadas = $derived(
    filtradas.length > 0 && filtradas.every((c) => seleccionadas.has(claveCobertura(c)))
  );

  // Limpiar selecciones que ya no estén en la lista filtrada (al cambiar filtros/recargar).
  $effect(() => {
    const visibles = new Set(filtradas.map(claveCobertura));
    let cambio = false;
    const next = new Set<string>();
    for (const k of seleccionadas) {
      if (visibles.has(k)) next.add(k);
      else cambio = true;
    }
    if (cambio) seleccionadas = next;
  });

  function toggleSeleccion(c: CoberturaHistorica) {
    const k = claveCobertura(c);
    const next = new Set(seleccionadas);
    if (next.has(k)) next.delete(k);
    else next.add(k);
    seleccionadas = next;
  }

  function toggleTodas() {
    if (todasSeleccionadas) {
      seleccionadas = new Set();
    } else {
      seleccionadas = new Set(filtradas.map(claveCobertura));
    }
  }

  function destroyCharts() {
    if (barChartInstance) {
      barChartInstance.destroy();
      barChartInstance = null;
    }
    if (doughnutChartInstance) {
      doughnutChartInstance.destroy();
      doughnutChartInstance = null;
    }
  }

  function renderCharts() {
    if (!barChartCanvas || !doughnutChartCanvas) return;
    if (filtradas.length === 0) return;

    const stats = statsData;

    if (barChartInstance) barChartInstance.destroy();
    if (doughnutChartInstance) doughnutChartInstance.destroy();

    const diaLabels = ["lunes", "martes", "miercoles", "jueves", "viernes"];
    const diaData = diaLabels.map(d => stats.porDia[d] || 0);
    const diaColors = ["#3b82f6", "#10b981", "#8b5cf6", "#f59e0b", "#ec4899"];

    barChartInstance = new Chart(barChartCanvas, {
      type: "bar",
      data: {
        labels: diaLabels.map(d => d.charAt(0).toUpperCase() + d.slice(1)),
        datasets: [{
          label: "Coberturas por día",
          data: diaData,
          backgroundColor: diaColors,
          borderRadius: 8,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (ctx) => `${ctx.parsed.y} cobertura(s)`,
            },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { stepSize: 1 },
          },
        },
      },
    });

    const estadoLabels = ["Aprobadas", "Rechazadas", "Pendientes"];
    const estadoData = [stats.aprobadas, stats.rechazadas, stats.pendientes];
    const estadoColors = ["#22c55e", "#ef4444", "#eab308"];

    doughnutChartInstance = new Chart(doughnutChartCanvas, {
      type: "doughnut",
      data: {
        labels: estadoLabels,
        datasets: [{
          data: estadoData,
          backgroundColor: estadoColors,
          borderWidth: 2,
          borderColor: "rgb(var(--card-bg))",
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
            labels: { padding: 12, font: { size: 12 } },
          },
          tooltip: {
            callbacks: {
              label: (ctx) => {
                const total = stats.total;
                const val = ctx.parsed as number;
                const pct = total > 0 ? ((val / total) * 100).toFixed(1) : "0";
                return `${ctx.label}: ${val} (${pct}%)`;
              },
            },
          },
        },
      },
    });
  }

  function toggleStats() {
    showStats = !showStats;
    if (showStats) {
      setTimeout(renderCharts, 50);
    } else {
      destroyCharts();
    }
  }

  async function eliminarCobertura(c: CoberturaHistorica) {
    const result = await Swal.fire({
      title: "¿Eliminar cobertura?",
      html: `Eliminar cobertura de <b>${c.docente_cubre}</b> el ${c.fecha} hora ${formatoHora(c.hora)}?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, eliminar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#ef4444",
    });

    if (!result.isConfirmed) return;

    try {
      await coberturaSheetsService.deleteCobertura(c.fecha, c.hora, c.docente_cubre);
      try {
        await coberturaSheetsService.deleteLiberadosPorFecha(c.fecha);
      } catch {
        /* no bloquear si falla el borrado de liberados */
      }
      await Swal.fire("Eliminado", "Cobertura y grupos liberados del día eliminados", "success");
      onReload();
    } catch (e: any) {
      Swal.fire("Error", e.message, "error");
    }
  }

  async function eliminarSeleccionadas() {
    const aEliminar = filtradas.filter((c) => seleccionadas.has(claveCobertura(c)));
    if (aEliminar.length === 0) return;

    const result = await Swal.fire({
      title: `¿Eliminar ${aEliminar.length} cobertura(s)?`,
      html: `Se eliminarán <b>${aEliminar.length}</b> registro(s) del historial. Esta acción no se puede deshacer.`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, eliminar todas",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#ef4444",
    });

    if (!result.isConfirmed) return;

    eliminando = true;
    let fallidas: string[] = [];
    try {
      const keys = aEliminar.map((c) => ({
        fecha: c.fecha,
        hora: c.hora,
        docente_cubre: c.docente_cubre,
        docente_ausente: c.docente_ausente,
      }));
      try {
        const res = await coberturaSheetsService.deleteCoberturasBatch(keys);
        // notFound trae las keys que no se encontraron en la hoja.
        fallidas = (res.notFound as Array<{ fecha?: string; hora?: number; docente_cubre?: string }>).map(
          (k) => `${k.docente_cubre ?? "?"} (${k.fecha ?? "?"} h${k.hora ?? "?"})`
        );
      } catch {
        // Error de transporte → reportar todas como fallidas.
        fallidas = aEliminar.map((c) => `${c.docente_cubre} (${c.fecha} h${c.hora})`);
      }
      // Borrar los grupos liberados de cada fecha afectada.
      const fechasAfectadas = [...new Set(aEliminar.map((c) => c.fecha))];
      for (const fecha of fechasAfectadas) {
        try {
          await coberturaSheetsService.deleteLiberadosPorFecha(fecha);
        } catch {
          /* no bloquear si falla el borrado de liberados */
        }
      }
      seleccionadas = new Set();
      if (fallidas.length > 0) {
        await Swal.fire({
          icon: "warning",
          title: "Eliminación parcial",
          html: `Se eliminaron ${aEliminar.length - fallidas.length} de ${aEliminar.length}.<br>No se pudieron eliminar:<br><small>${fallidas.join("<br>")}</small>`,
          confirmButtonColor: "#ef4444",
        });
      } else {
        await Swal.fire("Eliminadas", `Se eliminaron ${aEliminar.length} cobertura(s) y los grupos liberados de su(s) fecha(s).`, "success");
      }
      onReload();
    } finally {
      eliminando = false;
    }
  }
</script>

<div class="rounded-2xl border" style="border-color: rgb(var(--border-primary)); background-color: rgb(var(--card-bg));">
  <div class="p-4 border-b" style="border-color: rgb(var(--border-primary));">
    <div class="flex items-center justify-between mb-2">
      <h2 class="text-lg font-bold" style="color: rgb(var(--text-primary));">Historial de Coberturas</h2>
      {#if fechasDisponibles.length > 0 && onGenerarReporte}
        <div class="flex items-center gap-2">
          <select
            bind:value={fechaSeleccionadaReporte}
            class="px-3 py-1.5 rounded-lg text-sm border"
            style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border-color: rgb(var(--border-primary));"
          >
            <option value="">Seleccionar fecha...</option>
            {#each fechasDisponibles as fecha}
              {@const dia = coberturasHistoricas.find(c => c.fecha === fecha)?.dia_semana}
              <option value={fecha}>{formatoDia(dia || "")} {fecha}</option>
            {/each}
          </select>
          <button
            onclick={() => fechaSeleccionadaReporte && onGenerarReporte(fechaSeleccionadaReporte)}
            disabled={!fechaSeleccionadaReporte}
            class="px-4 py-2 rounded-lg text-sm font-medium transition-all disabled:opacity-50"
            style="background-color: rgb(var(--accent-primary)); color: white;"
            title="Generar reporte PDF del día"
          >
            📄 PDF
          </button>
          <button
            onclick={() => fechaSeleccionadaReporte && onGenerarWhatsApp && onGenerarWhatsApp(fechaSeleccionadaReporte)}
            disabled={!fechaSeleccionadaReporte}
            class="px-4 py-2 rounded-lg text-sm font-medium transition-all disabled:opacity-50"
            style="background-color: #25D366; color: white;"
            title="Generar imagen para WhatsApp"
          >
            📱 WhatsApp
          </button>
        </div>
      {/if}
    </div>
    <div class="flex items-center justify-between gap-3 mt-1 flex-wrap">
      <p class="text-xs" style="color: rgb(var(--text-secondary));">
        {filtradas.length} registro(s) • Hoja: <span class="font-mono">historial</span>
      </p>
      {#if seleccionadas.size > 0}
        <button
          onclick={eliminarSeleccionadas}
          disabled={eliminando}
          class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-semibold text-white transition-all disabled:opacity-60"
          style="background-color: #ef4444;"
        >
          {#if eliminando}
            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Eliminando...
          {:else}
            🗑️ Eliminar {seleccionadas.size} seleccionada(s)
          {/if}
        </button>
      {/if}
    </div>
  </div>

  <div class="p-4 flex flex-wrap gap-4 border-b" style="border-color: rgb(var(--border-primary));">
    <div>
      <label class="text-xs block mb-1" for="filter-desde" style="color: rgb(var(--text-secondary));">Desde</label>
      <input
        id="filter-desde"
        type="date"
        bind:value={filterFechaDesde}
        class="px-3 py-1.5 rounded-lg text-sm border"
        style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border-color: rgb(var(--border-primary));"
      />
    </div>
    <div>
      <label class="text-xs block mb-1" for="filter-hasta" style="color: rgb(var(--text-secondary));">Hasta</label>
      <input
        id="filter-hasta"
        type="date"
        bind:value={filterFechaHasta}
        class="px-3 py-1.5 rounded-lg text-sm border"
        style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border-color: rgb(var(--border-primary));"
      />
    </div>
    <div>
      <label class="text-xs block mb-1" for="filter-ausente" style="color: rgb(var(--text-secondary));">Ausente</label>
      <select
        id="filter-ausente"
        bind:value={filterDocenteAusente}
        class="px-3 py-1.5 rounded-lg text-sm border"
        style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border-color: rgb(var(--border-primary));"
      >
        <option value="">Todos</option>
        {#each docentesAusentesList as d}
          <option value={d}>{d}</option>
        {/each}
      </select>
    </div>
    <div>
      <label class="text-xs block mb-1" for="filter-cubre" style="color: rgb(var(--text-secondary));">Cubre</label>
      <select
        id="filter-cubre"
        bind:value={filterDocenteCubre}
        class="px-3 py-1.5 rounded-lg text-sm border"
        style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border-color: rgb(var(--border-primary));"
      >
        <option value="">Todos</option>
        {#each docentesCubreList as d}
          <option value={d}>{d}</option>
        {/each}
      </select>
    </div>
    <div>
      <label class="text-xs block mb-1" for="filter-estado" style="color: rgb(var(--text-secondary));">Estado</label>
      <select
        id="filter-estado"
        bind:value={filterEstado}
        class="px-3 py-1.5 rounded-lg text-sm border"
        style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border-color: rgb(var(--border-primary));"
      >
        <option value="">Todos</option>
        {#each estadosList as e}
          <option value={e}>{e}</option>
        {/each}
      </select>
    </div>
    <div class="flex items-end gap-2">
      {#if filterDocenteAusente || filterDocenteCubre || filterEstado}
        <button
          onclick={() => { filterDocenteAusente = ""; filterDocenteCubre = ""; filterEstado = ""; }}
          class="px-3 py-2 rounded-lg text-xs font-medium transition-all"
          style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-secondary)); border: 1px solid rgb(var(--border-primary));"
          title="Limpiar filtros"
        >
          <X size={14} />
        </button>
      {/if}
      <button
        onclick={onReload}
        class="px-4 py-2 rounded-lg text-sm font-medium transition-all"
        style="background-color: rgb(var(--accent-primary)); color: white;"
      >
        🔄 Recargar
      </button>
      <button
        onclick={toggleStats}
        class="px-4 py-2 rounded-lg text-sm font-medium transition-all"
        style="background-color: {showStats ? 'rgb(var(--accent-primary))' : 'rgb(var(--bg-secondary))'}; color: {showStats ? 'white' : 'rgb(var(--text-primary))'}; border: 1px solid rgb(var(--border-primary));"
        title="Ver estadísticas"
      >
        <BarChart3 size={16} />
      </button>
    </div>
  </div>

  {#if loading}
    <div class="p-8 text-center" style="color: rgb(var(--text-secondary));">
      Cargando...
    </div>
  {:else if filtradas.length === 0}
    <div class="p-8 text-center" style="color: rgb(var(--text-secondary));">
      No hay coberturas registradas.
    </div>
  {:else}
    {#if showStats}
      <div class="p-4 border-b" style="border-color: rgb(var(--border-primary));">
        <div class="flex items-center gap-2 mb-4">
          <TrendingUp size={20} style="color: rgb(var(--accent-primary));" />
          <h3 class="text-base font-bold" style="color: rgb(var(--text-primary));">Estadísticas del Historial</h3>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
          <div class="p-4 rounded-xl text-center" style="background-color: rgb(var(--bg-secondary)); border: 1px solid rgb(var(--border-primary));">
            <div class="text-2xl font-bold" style="color: rgb(var(--accent-primary));">{statsData.total}</div>
            <div class="text-xs" style="color: rgb(var(--text-secondary));">Total coberturas</div>
          </div>
          <div class="p-4 rounded-xl text-center" style="background-color: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.3);">
            <div class="text-2xl font-bold" style="color: #22c55e;">{statsData.aprobadas}</div>
            <div class="text-xs" style="color: rgb(var(--text-secondary));">Aprobadas</div>
          </div>
          <div class="p-4 rounded-xl text-center" style="background-color: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.3);">
            <div class="text-2xl font-bold" style="color: #ef4444;">{statsData.rechazadas}</div>
            <div class="text-xs" style="color: rgb(var(--text-secondary));">Rechazadas</div>
          </div>
          <div class="p-4 rounded-xl text-center" style="background-color: rgba(234,179,8,0.12); border: 1px solid rgba(234,179,8,0.3);">
            <div class="text-2xl font-bold" style="color: #eab308;">{statsData.pendientes}</div>
            <div class="text-xs" style="color: rgb(var(--text-secondary));">Pendientes</div>
          </div>
          <div class="p-4 rounded-xl text-center" style="background-color: rgb(var(--bg-secondary)); border: 1px solid rgb(var(--border-primary));">
            <div class="text-2xl font-bold" style="color: rgb(var(--text-primary));">{statsData.docentesCubren}</div>
            <div class="text-xs flex items-center justify-center gap-1" style="color: rgb(var(--text-secondary));">
              <Users size={12} /> Docentes cubriendo
            </div>
          </div>
          <div class="p-4 rounded-xl text-center" style="background-color: rgb(var(--bg-secondary)); border: 1px solid rgb(var(--border-primary));">
            <div class="text-2xl font-bold" style="color: rgb(var(--text-primary));">{statsData.gruposUnicos}</div>
            <div class="text-xs flex items-center justify-center gap-1" style="color: rgb(var(--text-secondary));">
              <Clock size={12} /> Grupos cubiertos
            </div>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="rounded-xl p-4" style="background-color: rgb(var(--bg-secondary)); border: 1px solid rgb(var(--border-primary));">
            <h4 class="text-sm font-semibold mb-3" style="color: rgb(var(--text-primary));">Coberturas por día</h4>
            <div class="relative" style="height: 200px;">
              <canvas bind:this={barChartCanvas}></canvas>
            </div>
          </div>
          <div class="rounded-xl p-4" style="background-color: rgb(var(--bg-secondary)); border: 1px solid rgb(var(--border-primary));">
            <h4 class="text-sm font-semibold mb-3" style="color: rgb(var(--text-primary));">Distribución por estado</h4>
            <div class="relative" style="height: 200px;">
              <canvas bind:this={doughnutChartCanvas}></canvas>
            </div>
          </div>
        </div>
      </div>
    {/if}
    <div class="overflow-x-auto">
      <table class="w-full text-sm" style="border-collapse: collapse;">
        <thead>
          <tr style="background-color: rgb(var(--bg-secondary));">
            <th class="p-3 text-center w-10" style="color: rgb(var(--text-primary));">
              <input
                type="checkbox"
                checked={todasSeleccionadas}
                onchange={toggleTodas}
                aria-label="Seleccionar todas"
                class="w-4 h-4 accent-[rgb(var(--accent-primary))] cursor-pointer"
              />
            </th>
            <th class="p-3 text-left font-bold uppercase tracking-wider text-xs" style="color: rgb(var(--text-primary));">Fecha</th>
            <th class="p-3 text-left font-bold uppercase tracking-wider text-xs" style="color: rgb(var(--text-primary));">Día</th>
            <th class="p-3 text-center font-bold uppercase tracking-wider text-xs" style="color: rgb(var(--text-primary));">Hora</th>
            <th class="p-3 text-left font-bold uppercase tracking-wider text-xs" style="color: rgb(var(--text-primary));">Ausente</th>
            <th class="p-3 text-left font-bold uppercase tracking-wider text-xs" style="color: rgb(var(--text-primary));">Grupo Ausente</th>
            <th class="p-3 text-left font-bold uppercase tracking-wider text-xs" style="color: rgb(var(--text-primary));">Cubre</th>
            <th class="p-3 text-left font-bold uppercase tracking-wider text-xs" style="color: rgb(var(--text-primary));">Grupo a cubrir</th>
            <th class="p-3 text-center font-bold uppercase tracking-wider text-xs" style="color: rgb(var(--text-primary));">Estado</th>
            <th class="p-3 text-center font-bold uppercase tracking-wider text-xs" style="color: rgb(var(--text-primary));">Acción</th>
          </tr>
        </thead>
        <tbody>
          {#each filtradas as c}
            {@const sel = seleccionadas.has(claveCobertura(c))}
            <tr class="border-t" style="border-color: rgb(var(--border-primary)); background-color: {sel ? 'rgba(239,68,68,0.08)' : 'transparent'};">
              <td class="p-3 text-center">
                <input
                  type="checkbox"
                  checked={sel}
                  onchange={() => toggleSeleccion(c)}
                  aria-label="Seleccionar cobertura de {c.docente_cubre} el {c.fecha}"
                  class="w-4 h-4 accent-[rgb(var(--accent-primary))] cursor-pointer"
                />
              </td>
              <td class="p-3" style="color: rgb(var(--text-primary));">{c.fecha}</td>
              <td class="p-3" style="color: rgb(var(--text-secondary));">{formatoDia(c.dia_semana)}</td>
              <td class="p-3 text-center font-bold" style="color: rgb(var(--accent-primary));">{formatoHora(c.hora)}</td>
              <td class="p-3" style="color: rgb(var(--text-secondary));">{c.docente_ausente}</td>
              <td class="p-3" style="color: rgb(var(--text-secondary));">{c.grupo_ausente || "-"}</td>
              <td class="p-3 font-medium" style="color: rgb(var(--accent-primary));">{c.docente_cubre}</td>
              <td class="p-3" style="color: rgb(var(--text-secondary));">{c.grupo_a_cubrir}</td>
              <td class="p-3 text-center">
                <span
                  class="px-2 py-1 rounded text-xs font-bold"
                  style="
                    background-color: {c.estado === 'aprobado' ? 'rgba(34,197,94,0.15)' : c.estado === 'rechazado' ? 'rgba(239,68,68,0.15)' : 'rgba(234,179,8,0.15)'};
                    color: {c.estado === 'aprobado' ? '#22c55e' : c.estado === 'rechazado' ? '#ef4444' : '#eab308'};
                  "
                >
                  {c.estado}
                </span>
              </td>
              <td class="p-3 text-center">
                <button
                  onclick={() => eliminarCobertura(c)}
                  class="text-red-500 hover:text-red-700 text-sm px-2 py-1 rounded"
                  title="Eliminar"
                >
                  🗑️
                </button>
              </td>
            </tr>
          {/each}
        </tbody>
      </table>
    </div>
  {/if}
</div>