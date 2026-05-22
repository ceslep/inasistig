<script lang="ts">
  import ModuleHeader from "./ModuleHeader.svelte";
  import horariosData from "../lib/horarios.json";
  import CoberturasManager from "./horarios/CoberturasManager.svelte";

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
      <p class="text-sm text-zinc-500 dark:text-zinc-400">
        Selecciona un docente para ver su horario semanal
      </p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">
      {#each horariosData as docente (docente.docente)}
        <button
          onclick={() => seleccionarDocente(docente.docente)}
          class="p-3 rounded-xl text-center font-medium transition-all duration-200
                 bg-[rgb(var(--card-bg))] border border-[rgb(var(--border-primary))]
                 hover:border-[rgb(var(--accent-primary))] hover:shadow-md"
          style="color: rgb(var(--text-primary));"
        >
          {docente.docente}
        </button>
      {/each}
    </div>
  {:else}
    <button
      onclick={() => docenteSeleccionado = null}
      class="mb-4 flex items-center gap-2 px-4 py-2 rounded-lg text-sm transition-colors"
      style="background-color: rgb(var(--accent-primary)); color: white;"
    >
      ← Ver todos los docentes
    </button>

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