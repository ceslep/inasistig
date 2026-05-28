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
      await Swal.fire("Eliminado", "Cobertura eliminada del historial", "success");
      onReload();
    } catch (e: any) {
      Swal.fire("Error", e.message, "error");
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
    <p class="text-xs mt-1" style="color: rgb(var(--text-secondary));">
      {filtradas.length} registro(s) • Hoja: <span class="font-mono">historial</span>
    </p>
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
            <tr class="border-t" style="border-color: rgb(var(--border-primary));">
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