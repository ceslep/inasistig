<script lang="ts">
  import ModuleHeader from "./ModuleHeader.svelte";
  import horariosData from "../lib/horarios.json";
  import CoberturasManager from "./horarios/CoberturasManager.svelte";
  import type { CoberturaHistorica } from "../lib/coberturaUtils";
  import { coberturaSheetsService } from "../services/coberturaSheetsService";
  import { getSemanaDelAno } from "../lib/coberturaUtils";
  import { User } from "@lucide/svelte";

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

  const docenteActual = $derived(
    docenteSeleccionado
      ? horariosData.find((h: HorarioDocente) => h.docente === docenteSeleccionado)
      : null
  );

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
    try {
      if (coberturasHistoricas.length === 0) {
        coberturasHistoricas = await coberturaSheetsService.getCoberturas();
      }
    } catch {}

    const carga = calcularCargaLaboral(docente);
    const puedeCubrir = carga.horasDisponiblesCobertura;

    let htmlContent = `
      <div style="text-align:left; font-family:Arial,sans-serif;">
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px; margin-bottom:16px;">
          <div style="background:#dcfce7; padding:12px; border-radius:8px; text-align:center;">
            <div style="font-size:24px; font-weight:bold; color:#166534;">${carga.horasClase}</div>
            <div style="font-size:11px; color:#166534;">Horas Clase</div>
          </div>
          <div style="background:#fed7aa; padding:12px; border-radius:8px; text-align:center;">
            <div style="font-size:24px; font-weight:bold; color:#9a3412;">${carga.horasDescanso}</div>
            <div style="font-size:11px; color:#9a3412;">DESC/PEDAG</div>
          </div>
          <div style="background:#e0e7ff; padding:12px; border-radius:8px; text-align:center;">
            <div style="font-size:24px; font-weight:bold; color:#3730a3;">${carga.horasLibres}</div>
            <div style="font-size:11px; color:#3730a3;">Horas Libres</div>
          </div>
        </div>

        <div style="background:#f3f4f6; padding:12px; border-radius:8px; margin-bottom:16px;">
          <div style="font-weight:bold; margin-bottom:8px; color:#374151;">Distribución por día:</div>
          <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:4px; font-size:12px;">
            <div><strong>LUN:</strong> ${carga.porDia.lunes}h</div>
            <div><strong>MAR:</strong> ${carga.porDia.martes}h</div>
            <div><strong>MIE:</strong> ${carga.porDia.miercoles}h</div>
            <div><strong>JUE:</strong> ${carga.porDia.jueves}h</div>
            <div><strong>VIE:</strong> ${carga.porDia.viernes}h</div>
          </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
          <div style="background:#fef3c7; padding:10px; border-radius:8px; text-align:center;">
            <div style="font-size:18px; font-weight:bold; color:#92400e;">${carga.coberturasSemana}</div>
            <div style="font-size:10px; color:#92400e;">Coberturas esta semana</div>
          </div>
          <div style="background:#dbeafe; padding:10px; border-radius:8px; text-align:center;">
            <div style="font-size:18px; font-weight:bold; color:#1e40af;">${carga.ultimaSemanaCoberturas}</div>
            <div style="font-size:10px; color:#1e40af;">Coberturas hace 1-2 sem</div>
          </div>
        </div>
      </div>
    `;

    if (puedeCubrir > 0) {
      htmlContent += `
        <div style="margin-top:12px; padding:10px; background:#bbf7d0; border-radius:8px; text-align:center;">
          <div style="font-size:18px; font-weight:bold; color:#166534;">${puedeCubrir} horas disponibles para cubrir</div>
          <div style="font-size:10px; color:#166534;">(dentro del límite 1h/día, 2h/semana)</div>
        </div>
      `;
    } else if (carga.coberturasSemana >= 2) {
      htmlContent += `
        <div style="margin-top:12px; padding:10px; background:#fee2e2; border-radius:8px; text-align:center;">
          <div style="font-size:14px; font-weight:bold; color:#991b1b;">Límite semanal alcanzado (2h)</div>
          <div style="font-size:10px; color:#991b1b;">No puede cubrir más esta semana</div>
        </div>
      `;
    }

    const { default: Swal } = await import("sweetalert2");
    Swal.fire({
      title: `Carga Laboral: ${docente.docente}`,
      html: htmlContent,
      confirmButtonText: "Cerrar",
      width: "450px",
    });
  }
</script>

<ModuleHeader title="Horario General" {onBack} />

<div class="p-4 max-w-7xl mx-auto">
  <div class="flex gap-2 mb-4">
    <button
      onclick={() => viewMode = "horario"}
      class="px-4 py-2 rounded-lg font-medium text-sm transition-all"
      style="background-color: {viewMode === 'horario' ? 'rgb(var(--accent-primary))' : 'rgb(var(--card-bg))'}; color: {viewMode === 'horario' ? 'white' : 'rgb(var(--text-primary))'}; border: 1px solid {viewMode === 'horario' ? 'rgb(var(--accent-primary))' : 'rgb(var(--border-primary))'};"
    >
      Ver Horario
    </button>
    <button
      onclick={() => viewMode = "coberturas"}
      class="px-4 py-2 rounded-lg font-medium text-sm transition-all"
      style="background-color: {viewMode === 'coberturas' ? 'rgb(var(--accent-primary))' : 'rgb(var(--card-bg))'}; color: {viewMode === 'coberturas' ? 'white' : 'rgb(var(--text-primary))'}; border: 1px solid {viewMode === 'coberturas' ? 'rgb(var(--accent-primary))' : 'rgb(var(--border-primary))'};"
    >
      Gestionar Coberturas
    </button>
  </div>

  {#if viewMode === "coberturas"}
    <CoberturasManager onBack={() => viewMode = "horario"} />
  {:else if !docenteActual}
    <div class="mb-4">
      <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-3">
        Selecciona un docente para ver su horario semanal
      </p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
      {#each horariosData as docente (docente.docente)}
        <button
          onclick={() => seleccionarDocente(docente.docente)}
          class="p-4 rounded-xl text-center transition-all duration-200 flex flex-col items-center gap-2
                 bg-[rgb(var(--card-bg))] border-2 border-[rgb(var(--border-primary))]
                 hover:border-[rgb(var(--accent-primary))] hover:shadow-lg hover:scale-105"
          style="color: rgb(var(--text-primary));"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: rgb(var(--accent-primary));">
            <User size={20} class="text-white" />
          </div>
          <span class="text-xs font-semibold leading-tight">{docente.docente}</span>
        </button>
      {/each}
    </div>
  {:else}
    <div class="flex items-center gap-3 mb-4">
      <button
        onclick={() => docenteSeleccionado = null}
        class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm transition-colors"
        style="background-color: rgb(var(--accent-primary)); color: white;"
      >
        ← Ver todos
      </button>
      <button
        onclick={() => verCargaLaboral(docenteActual)}
        class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm transition-colors"
        style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border: 1px solid rgb(var(--border-primary));"
      >
        📊 Carga Laboral
      </button>
    </div>

    <div class="rounded-2xl overflow-hidden border" style="border-color: rgb(var(--border-primary));">
      <div
        class="p-4 text-center font-bold text-lg"
        style="background-color: rgb(var(--accent-primary)); color: white;"
      >
        {docenteActual.docente}
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr style="background-color: rgb(var(--bg-secondary));">
              <th
                class="p-3 text-center font-bold uppercase tracking-wider w-16"
                style="color: rgb(var(--text-primary));"
              >
                HORA
              </th>
              {#each diasAbreviado as dia}
                <th
                  class="p-3 text-center font-bold uppercase tracking-wider"
                  style="color: rgb(var(--text-primary));"
                >
                  {dia}
                </th>
              {/each}
            </tr>
          </thead>
          <tbody>
            {#each Array(7) as _, horaIdx}
              <tr>
                <td
                  class="p-3 text-center font-bold border-t"
                  style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-secondary)); border-color: rgb(var(--border-primary));"
                >
                  {horaIdx + 1}
                </td>
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