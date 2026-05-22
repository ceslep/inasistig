<script lang="ts">
  import type { SlotInfo } from "../../lib/coberturaUtils";
  import { formatoDia, formatoHora, DIAS_ABREV } from "../../lib/coberturaUtils";

  type GrupoAusente = { grupo: string; horaInicio: number };
  type DocenteAusente = { nombre: string; tipo: string };

  let {
    diaSeleccionado,
    fechaSeleccionada,
    docentesAusentes,
    gruposAusentes,
    slots,
    loading = false,
    onGenerar,
    onBack,
    onOpenGruposModal,
  }: {
    diaSeleccionado: string;
    fechaSeleccionada: string;
    docentesAusentes: DocenteAusente[];
    gruposAusentes: GrupoAusente[];
    slots: SlotInfo[];
    loading?: boolean;
    onGenerar: () => void;
    onBack: () => void;
    onOpenGruposModal?: () => void;
  } = $props();

  const horas = Array(7).fill(0);
  let collapsedHoras = $state<Set<number>>(new Set());

  function toggleHora(hora: number) {
    if (collapsedHoras.has(hora)) {
      collapsedHoras.delete(hora);
    } else {
      collapsedHoras.add(hora);
    }
    collapsedHoras = new Set(collapsedHoras);
  }

  function getSlotsPorHora(hora: number) {
    return slots.filter((s) => s.hora === hora);
  }

  function getSlotStyle(tipo: string) {
    if (tipo === "clase") return { bg: "bg-emerald-200 dark:bg-emerald-800", text: "text-emerald-800 dark:text-emerald-200", border: "border-emerald-300 dark:border-emerald-600" };
    if (tipo === "desc" || tipo === "pedag") return { bg: "bg-orange-200 dark:bg-orange-800", text: "text-orange-800 dark:text-orange-200", border: "border-orange-300 dark:border-orange-600" };
    if (tipo === "libre") return { bg: "bg-white dark:bg-zinc-800", text: "text-zinc-400 dark:text-zinc-500", border: "border-2 border-dashed border-zinc-300 dark:border-zinc-600" };
    if (tipo === "libre_ausencia") return { bg: "bg-red-200 dark:bg-red-800", text: "text-red-800 dark:text-red-200", border: "border-red-400 dark:border-red-600" };
    return { bg: "bg-zinc-100 dark:bg-zinc-700", text: "text-zinc-600 dark:text-zinc-300", border: "border-zinc-300 dark:border-zinc-600" };
  }

  function getLabelSlot(tipo: string, slot: SlotInfo): string {
    if (tipo === "libre_ausencia") {
      if (slot.docenteAusente) return `LIBRE (${slot.docenteAusente.split(" ").pop()} ausente)`;
      if (slot.grupoAusente) return `LIBRE (grupo ${slot.grupoAusente} ausente)`;
      return "LIBRE";
    }
    if (tipo === "libre") return "LIBRE";
    if (tipo === "desc") return "DESC";
    if (tipo === "pedag") return "PEDAG";
    return slot.slot;
  }

  function getBadge(tipo: string, slot: SlotInfo) {
    if (tipo !== "libre_ausencia") return null;
    if (slot.docenteAusente) return { tipo: "docente", texto: slot.docenteAusente };
    if (slot.grupoAusente) return { tipo: "grupo", texto: `Grupo ${slot.grupoAusente}` };
    return null;
  }

  function contarLibresPorAusencia() {
    return slots.filter((s) => s.tipo === "libre_ausencia").length;
  }

  function contarDocentesDisponibles() {
    const horasLibres = new Set(slots.filter((s) => s.tipo === "libre").map((s) => s.docente));
    return horasLibres.size;
  }
</script>

<div class="p-6 rounded-2xl border" style="border-color: rgb(var(--border-primary)); background-color: rgb(var(--card-bg));">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-lg font-bold" style="color: rgb(var(--text-primary));">
      Step 2 — Análisis de Horas Libres
    </h2>
    <button onclick={onBack} class="text-sm px-3 py-1 rounded-lg" style="color: rgb(var(--text-secondary));">
      ← Atrás
    </button>
  </div>

  <div class="mb-4 p-4 rounded-xl grid grid-cols-1 sm:grid-cols-3 gap-4 text-center" style="background-color: rgb(var(--bg-secondary));">
    <div>
      <p class="text-2xl font-bold" style="color: rgb(var(--accent-primary));">{formatoDia(diaSeleccionado)}</p>
      <p class="text-xs" style="color: rgb(var(--text-secondary));">📅 {fechaSeleccionada}</p>
    </div>
    <div>
      <p class="text-2xl font-bold" style="color: rgb(var(--accent-primary));">{contarLibresPorAusencia()}</p>
      <p class="text-xs" style="color: rgb(var(--text-secondary));">Horas libres por ausencia</p>
    </div>
    <div>
      <p class="text-2xl font-bold" style="color: rgb(var(--accent-primary));">{contarDocentesDisponibles()}</p>
      <p class="text-xs" style="color: rgb(var(--text-secondary));">Docentes con horas libres</p>
    </div>
  </div>

  {#if docentesAusentes.length > 0 || gruposAusentes.length > 0}
    <div class="mb-4 p-3 rounded-lg text-xs" style="background-color: rgb(var(--bg-secondary));">
      <div class="flex justify-between items-center mb-1">
        <p class="font-medium" style="color: rgb(var(--text-primary));">Ausencias registradas:</p>
        {#if onOpenGruposModal}
          <button
            onclick={onOpenGruposModal}
            class="px-2 py-1 rounded font-medium transition-all"
            style="background-color: rgb(var(--accent-primary)); color: white; font-size: 0.7rem;"
          >
            + LIBERAR GRUPOS
          </button>
        {/if}
      </div>
      <div class="flex flex-wrap gap-2">
        {#each docentesAusentes as d}
          <span class="px-2 py-1 rounded bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-200">{d.nombre} <span class="font-bold">({d.tipo})</span></span>
        {/each}
        {#each gruposAusentes as g}
          <span class="px-2 py-1 rounded bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-200">Grupo {typeof g === 'string' ? g : g.grupo} {typeof g !== 'string' && g.horaInicio > 1 ? `desde h${g.horaInicio}` : ''}</span>
        {/each}
      </div>
    </div>
  {/if}

  <div class="overflow-x-auto">
    <table class="w-full text-sm" style="border-collapse: collapse;">
      <thead>
        <tr style="background-color: rgb(var(--bg-secondary));">
          <th class="p-3 text-center font-bold uppercase tracking-wider w-16" style="color: rgb(var(--text-primary));">
            HORA
          </th>
          {#each DIAS_ABREV as _}
            <th class="p-3 text-center font-bold uppercase tracking-wider" style="color: rgb(var(--text-primary));">
              {diaSeleccionado.toUpperCase().slice(0, 3)}
            </th>
          {/each}
        </tr>
      </thead>
      <tbody>
        {#each horas as _, horaIdx}
          {@const slotsHora = getSlotsPorHora(horaIdx)}
          {@const hayAusencia = slotsHora.some((s) => s.tipo === "libre_ausencia")}
          <tr>
            <td class="p-3 text-center font-bold border-t" style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-secondary)); border-color: rgb(var(--border-primary));">
              {horaIdx + 1}
            </td>
            <td colspan="5" class="p-1 border-t" style="border-color: rgb(var(--border-primary));">
              <div class="flex gap-1 overflow-x-auto">
                {#each slotsHora as slot}
                  {@const estilo = getSlotStyle(slot.tipo)}
                  {@const badge = getBadge(slot.tipo, slot)}
                  <div class="relative flex-1 min-w-[80px]">
                    <div class="px-2 py-3 rounded-lg text-xs font-bold text-center {estilo.bg} {estilo.text} {estilo.border}">
                      {getLabelSlot(slot.tipo, slot)}
                    </div>
                    {#if badge}
                      <span class="absolute -top-1 -right-1 w-3 h-3 rounded-full {badge.tipo === 'docente' ? 'bg-red-500' : 'bg-amber-500'}"></span>
                    {/if}
                  </div>
                {/each}
              </div>
            </td>
          </tr>
        {/each}
      </tbody>
    </table>
  </div>

  <div class="mt-4 flex flex-wrap gap-4 text-xs" style="background-color: rgb(var(--bg-secondary));">
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
    <div class="flex items-center gap-2">
      <span class="px-2 py-1 rounded bg-red-200 dark:bg-red-800 text-red-800 dark:text-red-200 font-bold border border-red-400 dark:border-red-600">LIBRE AUSENCIA</span>
      <span class="text-zinc-500">Por docente/grupo ausente</span>
    </div>
  </div>

  <button
    onclick={onGenerar}
    disabled={loading}
    class="w-full mt-6 py-3 rounded-xl font-bold text-white transition-all disabled:opacity-50"
    style="background-color: rgb(var(--accent-primary));"
  >
    {loading ? "Generando..." : "Generar asignaciones sugeridas →"}
  </button>
</div>