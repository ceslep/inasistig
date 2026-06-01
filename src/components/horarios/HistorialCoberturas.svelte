<script lang="ts">
  import type { CoberturaHistorica } from "../../lib/coberturaUtils";
  import { formatoDia, formatoHora } from "../../lib/coberturaUtils";
  import { coberturaSheetsService } from "../../services/coberturaSheetsService";
  import Swal from "sweetalert2";

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
      return true;
    })
  );

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
    <div class="flex items-end">
      <button
        onclick={onReload}
        class="px-4 py-2 rounded-lg text-sm font-medium transition-all"
        style="background-color: rgb(var(--accent-primary)); color: white;"
      >
        🔄 Recargar
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