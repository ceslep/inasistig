<script lang="ts">
  import ModuleHeader from "./ModuleHeader.svelte";
  import horariosData from "../lib/horarios.json";
  import CoberturasManager from "./horarios/CoberturasManager.svelte";
  import type { CoberturaHistorica } from "../lib/coberturaUtils";
  import { coberturaSheetsService } from "../services/coberturaSheetsService";
  import { getSemanaDelAno } from "../lib/coberturaUtils";
  import { User, Search, ArrowLeft, BarChart3, Calendar, X } from "@lucide/svelte";

  let { onBack }: { onBack: () => void } = $props();

  type ViewMode = "horario" | "coberturas";
  let viewMode = $state<ViewMode>("horario");

  type HorarioDocente = {
    docente: string;
    lunes: string[];
    martes: string[];
    miercoles: string[];
    jueves: string[];
    viernes: string[];
  };

  const dias = ["lunes", "martes", "miercoles", "jueves", "viernes"] as const;
  const diasAbreviado = ["LUN", "MAR", "MIE", "JUE", "VIE"];

  let docenteSeleccionado = $state<string | null>(null);
  let coberturasHistoricas = $state<CoberturaHistorica[]>([]);
  let filtroDocente = $state("");
  let cargandoCarga = $state(false);

  const docenteActual = $derived(
    docenteSeleccionado
      ? horariosData.find((h: HorarioDocente) => h.docente === docenteSeleccionado)
      : null
  );

  const docentesFiltrados = $derived.by(() => {
    const q = filtroDocente.trim().toLocaleLowerCase("es");
    if (!q) return horariosData as HorarioDocente[];
    return (horariosData as HorarioDocente[]).filter((h) =>
      h.docente.toLocaleLowerCase("es").includes(q)
    );
  });

  function seleccionarDocente(nombre: string) {
    docenteSeleccionado = docenteSeleccionado === nombre ? null : nombre;
  }

  function getClaseSlot(contenido: string): { bg: string; text: string; border: string } {
    if (!contenido) return { bg: "bg-white dark:bg-zinc-800", text: "text-zinc-300 dark:text-zinc-500", border: "border-2 border-dashed border-zinc-300 dark:border-zinc-600" };
    if (contenido === "DESC" || contenido === "PEDAG" || contenido === "DEESC") return { bg: "bg-orange-200 dark:bg-orange-800", text: "text-orange-800 dark:text-orange-200", border: "border border-orange-300 dark:border-orange-600" };
    return { bg: "bg-emerald-200 dark:bg-emerald-800", text: "text-emerald-800 dark:text-emerald-200", border: "border border-emerald-300 dark:border-emerald-600" };
  }

  function formatearMateria(contenido: string): string {
    if (!contenido) return "LIBRE";
    if (contenido === "DESC" || contenido === "PEDAG" || contenido === "DEESC") return contenido;
    return contenido.replace(/\n/g, " ");
  }

  function calcularCargaLaboral(docente: HorarioDocente) {
    let horasClase = 0;
    let horasDescanso = 0;
    let horasLibres = 0;
    const porDia = { lunes: 0, martes: 0, miercoles: 0, jueves: 0, viernes: 0 };
    const horasDisponibles: { dia: string; hora: number }[] = [];

    for (const dia of dias) {
      const jornada = docente[dia];
      for (let i = 0; i < jornada.length; i++) {
        const slot = jornada[i];
        if (!slot || slot === "") {
          horasLibres++;
          porDia[dia]++;
          horasDisponibles.push({ dia, hora: i });
        } else if (slot === "DESC" || slot === "PEDAG" || slot === "DEESC") {
          horasDescanso++;
        } else {
          horasClase++;
        }
      }
    }

    const hoy = new Date();
    const hace14dias = new Date(hoy);
    hace14dias.setDate(hoy.getDate() - 14);
    const hace7dias = new Date(hoy);
    hace7dias.setDate(hoy.getDate() - 7);
    const semanaActual = getSemanaDelAno(hoy.toISOString().split("T")[0]);

    const coberturasSemana = coberturasHistoricas.filter((c) => {
      if (c.docente_cubre !== docente.docente) return false;
      if (c.estado !== "aprobado") return false;
      const cpSemana = getSemanaDelAno(c.fecha);
      return cpSemana === semanaActual;
    });

    const ultimaSemanaCoberturas = coberturasHistoricas.filter((c) => {
      if (c.docente_cubre !== docente.docente) return false;
      if (c.estado !== "aprobado") return false;
      if (c.dia_semana !== "") return false;
      const cpFecha = new Date(c.fecha + "T00:00:00");
      return cpFecha >= hace14dias && cpFecha < hace7dias;
    });

    const horasDisponiblesCobertura = horasDisponibles.filter((h) => {
      if (h.dia === "lunes" && h.hora === 6) {
        const allDaysLibre = dias.every((d) => docente[d as keyof HorarioDocente][6] === "");
        if (allDaysLibre) return false;
      }
      const diaCoberturas = coberturasSemana.filter((c) => c.dia_semana === h.dia);
      return diaCoberturas.length === 0;
    });

    return {
      horasClase,
      horasDescanso,
      horasLibres,
      porDia,
      coberturasSemana: coberturasSemana.length,
      ultimaSemanaCoberturas: ultimaSemanaCoberturas.length,
      horasDisponiblesCobertura: horasDisponiblesCobertura.length,
    };
  }

  async function verCargaLaboral(docente: HorarioDocente) {
    cargandoCarga = true;
    try {
      if (coberturasHistoricas.length === 0) {
        coberturasHistoricas = await coberturaSheetsService.getCoberturas();
      }
    } catch {}
    finally {
      cargandoCarga = false;
    }

    const carga = calcularCargaLaboral(docente);
    const puedeCubrir = carga.horasDisponiblesCobertura;

    // Tokens semánticos con buen contraste light + dark
    const stat = (val: number | string, label: string, hueLight: string, hueDark: string) => `
      <div class="cl-stat" style="--bg-l:${hueLight}; --bg-d:${hueDark};">
        <div class="cl-stat-val">${val}</div>
        <div class="cl-stat-lbl">${label}</div>
      </div>`;

    let htmlContent = `
      <style>
        .cl-wrap { text-align:left; font: 14px/1.5 system-ui,sans-serif; color: rgb(var(--text-primary)); }
        .cl-grid-3 { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-bottom:12px; }
        .cl-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
        .cl-grid-5 { display:grid; grid-template-columns:repeat(5,1fr); gap:6px; font-size:12px; }
        .cl-stat { padding:12px; border-radius:10px; text-align:center;
          background: var(--bg-l); color: #0b3b1f; border: 1px solid rgb(var(--border-primary)); }
        :is(.dim, .dark) .cl-stat { background: var(--bg-d); color: rgb(var(--text-primary)); }
        .cl-stat-val { font-size:22px; font-weight:700; line-height:1.1; }
        .cl-stat-lbl { font-size:11px; opacity:.85; margin-top:2px; }
        .cl-section { background: rgb(var(--bg-secondary)); padding:12px; border-radius:10px; margin-bottom:12px;
          border: 1px solid rgb(var(--border-primary)); }
        .cl-section h4 { font-weight:600; margin:0 0 8px 0; color: rgb(var(--text-secondary)); font-size:12px; text-transform:uppercase; letter-spacing:.04em; }
        .cl-banner { margin-top:8px; padding:12px; border-radius:10px; text-align:center; border:1px solid; }
        .cl-banner-ok { background: rgba(34,197,94,.12); border-color: rgba(34,197,94,.4); color:#14532d; }
        :is(.dim, .dark) .cl-banner-ok { color:#bbf7d0; }
        .cl-banner-bad { background: rgba(239,68,68,.12); border-color: rgba(239,68,68,.4); color:#7f1d1d; }
        :is(.dim, .dark) .cl-banner-bad { color:#fecaca; }
      </style>
      <div class="cl-wrap">
        <div class="cl-grid-3">
          ${stat(carga.horasClase, "Horas Clase", "#dcfce7", "rgba(34,197,94,.18)")}
          ${stat(carga.horasDescanso, "DESC/PEDAG", "#fed7aa", "rgba(249,115,22,.2)")}
          ${stat(carga.horasLibres, "Horas Libres", "#e0e7ff", "rgba(99,102,241,.22)")}
        </div>
        <div class="cl-section">
          <h4>Distribución por día</h4>
          <div class="cl-grid-5">
            <div><strong>LUN:</strong> ${carga.porDia.lunes}h</div>
            <div><strong>MAR:</strong> ${carga.porDia.martes}h</div>
            <div><strong>MIE:</strong> ${carga.porDia.miercoles}h</div>
            <div><strong>JUE:</strong> ${carga.porDia.jueves}h</div>
            <div><strong>VIE:</strong> ${carga.porDia.viernes}h</div>
          </div>
        </div>
        <div class="cl-grid-2">
          ${stat(carga.coberturasSemana, "Coberturas esta semana", "#fef3c7", "rgba(234,179,8,.2)")}
          ${stat(carga.ultimaSemanaCoberturas, "Coberturas hace 1-2 sem", "#dbeafe", "rgba(59,130,246,.2)")}
        </div>
        ${
          puedeCubrir > 0
            ? `<div class="cl-banner cl-banner-ok" role="status">
                 <div style="font-size:18px; font-weight:700;">${puedeCubrir} horas disponibles para cubrir</div>
                 <div style="font-size:11px; opacity:.85;">(dentro del límite 1h/día, 2h/semana)</div>
               </div>`
            : carga.coberturasSemana >= 2
            ? `<div class="cl-banner cl-banner-bad" role="alert">
                 <div style="font-size:14px; font-weight:700;">Límite semanal alcanzado (2h)</div>
                 <div style="font-size:11px; opacity:.85;">No puede cubrir más esta semana</div>
               </div>`
            : ""
        }
      </div>
    `;

    const { default: Swal } = await import("sweetalert2");
    Swal.fire({
      title: `Carga Laboral: ${docente.docente}`,
      html: htmlContent,
      confirmButtonText: "Cerrar",
      width: "500px",
    });
  }
</script>

<ModuleHeader title="Horario General" {onBack} />

<div class="p-4 max-w-7xl mx-auto">
  <div role="tablist" aria-label="Vista de horarios" class="flex gap-2 mb-4">
    <button
      type="button"
      role="tab"
      aria-selected={viewMode === "horario"}
      aria-controls="panel-horario"
      onclick={() => viewMode = "horario"}
      class="px-4 py-2 rounded-lg font-medium text-sm transition-colors min-h-[44px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
      style="background-color: {viewMode === 'horario' ? 'rgb(var(--accent-primary))' : 'rgb(var(--card-bg))'}; color: {viewMode === 'horario' ? 'white' : 'rgb(var(--text-primary))'}; border: 1px solid {viewMode === 'horario' ? 'rgb(var(--accent-primary))' : 'rgb(var(--border-primary))'}; --tw-ring-color: rgb(var(--accent-primary));"
    >
      Ver Horario
    </button>
    <button
      type="button"
      role="tab"
      aria-selected={viewMode === "coberturas"}
      aria-controls="panel-coberturas"
      onclick={() => viewMode = "coberturas"}
      class="px-4 py-2 rounded-lg font-medium text-sm transition-colors min-h-[44px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
      style="background-color: {viewMode === 'coberturas' ? 'rgb(var(--accent-primary))' : 'rgb(var(--card-bg))'}; color: {viewMode === 'coberturas' ? 'white' : 'rgb(var(--text-primary))'}; border: 1px solid {viewMode === 'coberturas' ? 'rgb(var(--accent-primary))' : 'rgb(var(--border-primary))'}; --tw-ring-color: rgb(var(--accent-primary));"
    >
      Gestionar Coberturas
    </button>
  </div>

  {#if viewMode === "coberturas"}
    <div id="panel-coberturas" role="tabpanel">
      <CoberturasManager onBack={() => viewMode = "horario"} />
    </div>
  {:else if !docenteActual}
    <div id="panel-horario" role="tabpanel" class="mb-4">
      <p id="filtro-docente-hint" class="text-sm text-zinc-500 dark:text-zinc-400 mb-3">
        Selecciona un docente para ver su horario semanal
      </p>
      <div class="relative mb-4 max-w-md">
        <label for="filtro-docente" class="sr-only">Filtrar docentes por nombre</label>
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none" style="color: rgb(var(--text-secondary));">
          <Search size={18} aria-hidden="true" />
        </div>
        <input
          id="filtro-docente"
          type="search"
          bind:value={filtroDocente}
          placeholder="Buscar docente..."
          aria-describedby="filtro-docente-hint filtro-resultados"
          autocomplete="off"
          class="w-full pl-10 pr-10 py-3 rounded-xl text-sm border focus-visible:outline-none focus-visible:ring-2"
          style="background-color: rgb(var(--card-bg)); color: rgb(var(--text-primary)); border-color: rgb(var(--border-primary)); --tw-ring-color: rgb(var(--accent-primary));"
        />
        {#if filtroDocente}
          <button
            type="button"
            onclick={() => (filtroDocente = "")}
            aria-label="Limpiar filtro"
            class="absolute inset-y-0 right-0 flex items-center pr-3 hover:opacity-70 transition-opacity"
            style="color: rgb(var(--text-secondary));"
          >
            <X size={18} aria-hidden="true" />
          </button>
        {/if}
      </div>
      <p id="filtro-resultados" class="sr-only" aria-live="polite">
        {docentesFiltrados.length} docente{docentesFiltrados.length === 1 ? "" : "s"} encontrado{docentesFiltrados.length === 1 ? "" : "s"}
      </p>
    </div>

    {#if docentesFiltrados.length === 0}
      <div class="text-center py-12 rounded-xl border-2 border-dashed" style="border-color: rgb(var(--border-primary)); color: rgb(var(--text-secondary));">
        <p class="text-sm">No se encontraron docentes con "{filtroDocente}"</p>
      </div>
    {:else}
      <ul role="list" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
        {#each docentesFiltrados as docente (docente.docente)}
          <li>
            <button
              type="button"
              onclick={() => seleccionarDocente(docente.docente)}
              aria-label={`Ver horario de ${docente.docente}`}
              class="w-full p-4 rounded-xl text-center transition-transform duration-200 flex flex-col items-center gap-2
                     bg-[rgb(var(--card-bg))] border-2 border-[rgb(var(--border-primary))]
                     hover:border-[rgb(var(--accent-primary))] hover:shadow-lg motion-safe:hover:scale-[1.03]
                     focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
              style="color: rgb(var(--text-primary)); --tw-ring-color: rgb(var(--accent-primary));"
            >
              <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: rgb(var(--accent-primary));">
                <User size={20} class="text-white" aria-hidden="true" />
              </div>
              <span class="text-xs font-semibold leading-tight">{docente.docente}</span>
            </button>
          </li>
        {/each}
      </ul>
    {/if}
  {:else}
    <div id="panel-horario" role="tabpanel" class="flex items-center gap-3 mb-4 flex-wrap">
      <button
        type="button"
        onclick={() => docenteSeleccionado = null}
        class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm transition-colors min-h-[44px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
        style="background-color: rgb(var(--accent-primary)); color: white; --tw-ring-color: rgb(var(--accent-primary));"
      >
        <ArrowLeft size={16} aria-hidden="true" />
        <span>Ver todos</span>
      </button>
      <button
        type="button"
        onclick={() => verCargaLaboral(docenteActual)}
        disabled={cargandoCarga}
        aria-busy={cargandoCarga}
        class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm transition-colors min-h-[44px] disabled:opacity-60 disabled:cursor-not-allowed focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
        style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border: 1px solid rgb(var(--border-primary)); --tw-ring-color: rgb(var(--accent-primary));"
      >
        {#if cargandoCarga}
          <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          <span>Cargando...</span>
        {:else}
          <BarChart3 size={16} aria-hidden="true" />
          <span>Carga Laboral</span>
        {/if}
      </button>
    </div>

    <div class="rounded-2xl overflow-hidden border" style="border-color: rgb(var(--border-primary));">
      <div
        class="p-4 text-center font-bold text-lg flex items-center justify-center gap-2"
        style="background-color: rgb(var(--accent-primary)); color: white;"
      >
        <Calendar size={20} aria-hidden="true" />
        <span>{docenteActual.docente}</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <caption class="sr-only">Horario semanal de {docenteActual.docente}</caption>
          <thead>
            <tr style="background-color: rgb(var(--bg-secondary));">
              <th
                scope="col"
                class="p-3 text-center font-bold uppercase tracking-wider w-16"
                style="color: rgb(var(--text-primary));"
              >
                HORA
              </th>
              {#each diasAbreviado as dia}
                <th
                  scope="col"
                  class="p-3 text-center font-bold uppercase tracking-wider"
                  style="color: rgb(var(--text-primary));"
                >
                  {dia}
                </th>
              {/each}
            </tr>
          </thead>
          <tbody>
            {#each Array(7) as _, horaIdx (horaIdx)}
              <tr>
                <th
                  scope="row"
                  class="p-3 text-center font-bold border-t"
                  style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-secondary)); border-color: rgb(var(--border-primary));"
                >
                  <span aria-label={`Hora ${horaIdx + 1}`}>{horaIdx + 1}</span>
                </th>
                {#each dias as dia}
                  {@const slot = docenteActual[dia][horaIdx]}
                  {@const estilo = getClaseSlot(slot)}
                  <td
                    class="p-1 text-center border-t border-r"
                    style="border-color: rgb(var(--border-primary));"
                  >
                    <div
                      class="px-2 py-3 rounded-lg text-xs font-bold whitespace-pre-wrap min-h-[2.8rem] flex items-center justify-center {estilo.bg} {estilo.text} {estilo.border}"
                    >
                      {formatearMateria(slot)}
                    </div>
                  </td>
                {/each}
              </tr>
            {/each}
          </tbody>
        </table>
      </div>

      <div class="p-4 flex flex-wrap gap-4 text-xs" style="background-color: rgb(var(--bg-secondary));">
        <div class="flex items-center gap-2">
          <span class="px-2 py-1 rounded bg-emerald-200 dark:bg-emerald-800 text-emerald-800 dark:text-emerald-200 font-bold border border-emerald-300 dark:border-emerald-600">MATERIA</span>
          <span class="text-zinc-500">Clase asignada</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="px-2 py-1 rounded bg-orange-200 dark:bg-orange-800 text-orange-800 dark:text-orange-200 font-bold border border-orange-300 dark:border-orange-600">DESC/PEDAG</span>
          <span class="text-zinc-500">Descanso / Pedagógico</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="px-2 py-1 rounded border-2 border-dashed border-zinc-300 dark:border-zinc-600 text-zinc-400 dark:text-zinc-500 font-bold">LIBRE</span>
          <span class="text-zinc-500">Sin clase</span>
        </div>
      </div>
    </div>
  {/if}
</div>

<style>
  table {
    border-collapse: collapse;
  }
</style>