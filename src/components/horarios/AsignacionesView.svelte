<script lang="ts">
  import type { CoberturaSugerida, SugerenciaGrupo, HorarioDocente, CoberturaHistorica } from "../../lib/coberturaUtils";
  import { formatoDia, formatoHora, ROLES_SIN_LIMITE, esHoraLibrePorGrupoAusente } from "../../lib/coberturaUtils";
  import horariosData from "../../lib/horarios.json";
  import Swal from "sweetalert2";

  let {
    diaSeleccionado,
    fechaSeleccionada,
    coberturasSugeridas,
    gruposSugeridosAAusentar,
    coberturasHistoricas = [],
    gruposAusentes = [],
    loading,
    onToggle,
    onCambiarDocenteCubre,
    onAgregarGrupoAusente,
    onGuardar,
    onBack,
    onOpenGruposModal,
    onLiberarGrupoDesdeHora,
    onAprobarTodo,
  }: {
    diaSeleccionado: string;
    fechaSeleccionada: string;
    coberturasSugeridas: CoberturaSugerida[];
    gruposSugeridosAAusentar: SugerenciaGrupo[];
    coberturasHistoricas?: CoberturaHistorica[];
    gruposAusentes?: { grupo: string; horaInicio: number }[];
    loading: boolean;
    onToggle: (index: number) => void;
    onCambiarDocenteCubre: (index: number, docente: string) => void;
    onAgregarGrupoAusente: (grupo: string) => void;
    onGuardar: () => void;
    onBack: () => void;
    onOpenGruposModal?: () => void;
    onLiberarGrupoDesdeHora?: (grupo: string, hora: number, docenteAusente: string) => void;
    onAprobarTodo: () => void;
  } = $props();

  // Conteo de ocurrencias por docente en sesión (excluyendo roles sin límite y
  // horas que originalmente eran del grupo liberado — esas no cuentan porque
  // el docente ya las tenía libres por ausencia de grupo).
  const conteoSesion = $derived.by(() => {
    const m = new Map<string, number>();
    for (const c of coberturasSugeridas) {
      const d = c.docenteCubre;
      if (!d) continue;
      if (ROLES_SIN_LIMITE.some((r) => d.includes(r))) continue;
      if (esHoraLibrePorGrupoAusente(d, c.hora, diaSeleccionado, horariosData as HorarioDocente[], gruposAusentes)) {
        continue;
      }
      m.set(d, (m.get(d) || 0) + 1);
    }
    return m;
  });

  // Conteo histórico mismo día
  const conteoHistorico = $derived.by(() => {
    const m = new Map<string, number>();
    for (const cp of coberturasHistoricas) {
      if (cp.estado !== "aprobado") continue;
      if (cp.fecha !== fechaSeleccionada) continue;
      const d = cp.docente_cubre;
      if (!d) continue;
      if (ROLES_SIN_LIMITE.some((r) => d.includes(r))) continue;
      m.set(d, (m.get(d) || 0) + 1);
    }
    return m;
  });

  function esDuplicado(docente: string, hora: number): { dup: boolean; sesion: number; historico: number; porGrupoLiberado: boolean } {
    if (!docente) return { dup: false, sesion: 0, historico: 0, porGrupoLiberado: false };
    if (ROLES_SIN_LIMITE.some((r) => docente.includes(r))) return { dup: false, sesion: 0, historico: 0, porGrupoLiberado: false };
    const horaEsLibrePorGrupo = esHoraLibrePorGrupoAusente(docente, hora, diaSeleccionado, horariosData as HorarioDocente[], gruposAusentes);
    const s = conteoSesion.get(docente) || 0;
    const h = conteoHistorico.get(docente) || 0;
    // Si esta hora es libre por grupo liberado, no marcamos como duplicado real.
    if (horaEsLibrePorGrupo) {
      // Repite pero válido — aviso informativo si hay otros covers reales del mismo docente.
      const repiteReal = s >= 1 || h >= 1;
      return { dup: false, sesion: s, historico: h, porGrupoLiberado: repiteReal };
    }
    return { dup: s > 1 || h > 0, sesion: s, historico: h, porGrupoLiberado: false };
  }

  let seleccionadas = $state(0);
  let violaciones = $state(0);

  const dias = ["lunes", "martes", "miercoles", "jueves", "viernes"] as const;
  const diasAbreviado = ["LUN", "MAR", "MIE", "JUE", "VIE"];

  $effect(() => {
    seleccionadas = coberturasSugeridas.filter((c) => c.aprobada).length;
    violaciones = coberturasSugeridas.filter((c) => c.violation).length;
  });

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

  function abrirHorarioDocente(nombre: string) {
    if (ROLES_SIN_LIMITE.some((r) => nombre.includes(r))) {
      Swal.fire({
        icon: "info",
        title: nombre,
        text: "Rol administrativo sin horario fijo de clases.",
        confirmButtonText: "Cerrar",
      });
      return;
    }
    const horario = (horariosData as HorarioDocente[]).find((h) => h.docente === nombre);
    if (!horario) {
      Swal.fire("Error", `No se encontró el horario para ${nombre}`, "error");
      return;
    }

    let tableHtml = `
      <table style="width:100%; font-size:12px; border-collapse: collapse;">
        <thead>
          <tr style="background-color: #f3f4f6;">
            <th style="padding:8px; text-align:center; font-weight:bold; color:rgb(var(--text-secondary));">HORA</th>
            ${diasAbreviado.map((d) => `<th style="padding:8px; text-align:center; font-weight:bold; color:rgb(var(--text-secondary));">${d}</th>`).join("")}
          </tr>
        </thead>
        <tbody>
          ${Array(7)
            .fill(0)
            .map(
              (_, horaIdx) => `
            <tr>
              <td style="padding:6px; text-align:center; font-weight:bold; background-color:rgb(var(--bg-secondary)); color:rgb(var(--text-secondary));">${horaIdx + 1}</td>
              ${dias
                .map((dia) => {
                  const slot = horario[dia][horaIdx];
                  const estilo = getClaseSlot(slot);
                  return `<td style="padding:4px; text-align:center;">
                  <div class="px-2 py-2 rounded-lg text-xs font-bold min-h-[2.5rem] flex items-center justify-center ${estilo.bg} ${estilo.text} ${estilo.border}">
                    ${formatearMateria(slot)}
                  </div>
                </td>`;
                })
                .join("")}
            </tr>
          `
            )
            .join("")}
        </tbody>
      </table>
    `;

    Swal.fire({
      title: `Horario de ${nombre}`,
      html: tableHtml,
      confirmButtonText: "Cerrar",
      width: "650px",
    });
  }
</script>

<div class="p-6 rounded-2xl border" style="border-color: rgb(var(--border-primary)); background-color: rgb(var(--card-bg));">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-lg font-bold" style="color: rgb(var(--text-primary));">
      Step 3 — Asignaciones Sugeridas
    </h2>
    <button onclick={onBack} class="text-sm px-3 py-1 rounded-lg" style="color: rgb(var(--text-secondary));">
      ← Atrás
    </button>
  </div>

  <div class="mb-4 p-4 rounded-xl grid grid-cols-1 sm:grid-cols-3 gap-4 text-center" style="background-color: rgb(var(--bg-secondary));">
    <div>
      <p class="text-2xl font-bold" style="color: rgb(var(--accent-primary));">{formatoDia(diaSeleccionado)}</p>
      <p class="text-xs" style="color: rgb(var(--text-secondary));">{fechaSeleccionada}</p>
    </div>
    <div>
      <p class="text-2xl font-bold" style="color: rgb(var(--accent-primary));">{seleccionadas}</p>
      <p class="text-xs" style="color: rgb(var(--text-secondary));">Coberturas seleccionadas</p>
    </div>
    <div>
      <p class="text-2xl font-bold" style="color: {violaciones > 0 ? '#ef4444' : 'rgb(var(--accent-primary))'};">{violaciones}</p>
      <p class="text-xs" style="color: rgb(var(--text-secondary));">Con violación de reglas</p>
    </div>
  </div>
    <div class="flex justify-end gap-2 mb-2">
      <button
        onclick={onAprobarTodo}
        class="px-3 py-1.5 rounded-lg font-medium transition-all text-xs"
        style="background-color: rgb(var(--accent-primary)); color: white;"
      >
        ✓ APROBAR TODO
      </button>
      {#if onOpenGruposModal}
        <button
          onclick={onOpenGruposModal}
          class="px-3 py-1.5 rounded-lg font-medium transition-all text-xs"
          style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--accent-primary)); border: 1px solid rgb(var(--accent-primary));"
        >
          + LIBERAR GRUPOS
        </button>
      {/if}
    </div>

  {#if gruposSugeridosAAusentar.length > 0}
    <div class="mb-4 p-4 rounded-xl border" style="border-color: rgb(var(--accent-primary)); background-color: rgba(99, 102, 241, 0.05);">
      <p class="text-sm font-bold mb-2" style="color: rgb(var(--accent-primary));">💡 Grupos sugeridos para ausentar (reducir coberturas)</p>
      <p class="text-xs mb-3" style="color: rgb(var(--text-secondary));">Estos grupos generan 2 o más horas libres. Ausentarlos reduce la carga de coberturas.</p>
      <div class="flex gap-2 flex-wrap">
        {#each gruposSugeridosAAusentar as sug}
          <button
            onclick={() => onAgregarGrupoAusente(sug.grupo)}
            class="px-3 py-2 rounded-lg text-sm font-medium transition-all flex items-center gap-2"
            style="background-color: rgb(var(--accent-primary)); color: white;"
          >
            <span>Grupo {sug.grupo}</span>
            <span class="text-xs opacity-75">({sug.horasAfectadas} horas)</span>
          </button>
        {/each}
      </div>
    </div>
  {/if}

  {#if coberturasSugeridas.length === 0}
    <div class="text-center py-8">
      <p class="text-zinc-500">No hay coberturas sugeridas para el día seleccionado.</p>
    </div>
  {:else}
    <div class="overflow-x-auto mb-6">
      <table class="w-full text-sm" style="border-collapse: collapse;">
        <thead>
          <tr style="background-color: rgb(var(--bg-secondary));">
            <th class="p-3 text-center font-bold uppercase tracking-wider" style="color: rgb(var(--text-primary));">Hora</th>
            <th class="p-3 text-center font-bold uppercase tracking-wider" style="color: rgb(var(--text-primary));">Ausente</th>
            <th class="p-3 text-center font-bold uppercase tracking-wider" style="color: rgb(var(--text-primary));">Grupo</th>
            <th class="p-3 text-center font-bold uppercase tracking-wider" style="color: rgb(var(--text-primary));">Cubre</th>
            <th class="p-3 text-center font-bold uppercase tracking-wider" style="color: rgb(var(--text-primary));">Grupo a cubrir</th>
            <th class="p-3 text-center font-bold uppercase tracking-wider" style="color: rgb(var(--text-primary));">Estado</th>
            <th class="p-3 text-center font-bold uppercase tracking-wider" style="color: rgb(var(--text-primary));">Aprobar</th>
          </tr>
        </thead>
        <tbody>
          {#each coberturasSugeridas as cov, i}
            {@const esViolacion = !!cov.violation}
            {@const checked = cov.aprobada && !esViolacion}
            {@const dupInfo = esDuplicado(cov.docenteCubre, cov.hora)}
            <tr
              class="transition-colors"
              style="border-color: rgb(var(--border-primary)); background-color: {esViolacion ? 'rgba(239,68,68,0.05)' : 'transparent'};"
            >
              <td class="p-3 text-center font-bold border-t" style="border-color: rgb(var(--border-primary)); color: rgb(var(--text-primary));">
                {formatoHora(cov.hora)}{console.log("RENDER ROW", i, cov.hora, cov.docenteAusente, cov.grupoAusente, cov.docenteCubre)}
              </td>
              <td class="p-3 text-center border-t" style="border-color: rgb(var(--border-primary));">
                <button
                  onclick={() => abrirHorarioDocente(cov.docenteAusente)}
                  class="font-medium hover:underline cursor-pointer"
                  style="color: rgb(var(--accent-primary));"
                  title="Ver horario semanal"
                >
                  {cov.docenteAusente}
                </button>
              </td>
              <td class="p-3 text-center border-t" style="border-color: rgb(var(--border-primary)); color: rgb(var(--text-secondary));">
                {cov.grupoAusente || "-"}
              </td>
              <td class="p-3 text-center border-t" style="border-color: rgb(var(--border-primary));">
                <select
                  value={cov.docenteCubre || ""}
                  onchange={(e) => onCambiarDocenteCubre(i, e.currentTarget.value)}
                  class="w-full max-w-[180px] px-2 py-1.5 rounded-lg text-sm font-medium border transition-all {dupInfo.dup ? 'cobertura-blink-dup' : ''}"
                  style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--accent-primary)); border-color: {dupInfo.dup ? '#ef4444' : dupInfo.porGrupoLiberado ? '#eab308' : 'rgb(var(--border-primary))'}; border-width: {dupInfo.dup || dupInfo.porGrupoLiberado ? '2px' : '1px'};"
                >
                  {#if cov.posiblesCobradores.length > 0}
                    <optgroup label="Docentes disponibles">
                      {#each cov.posiblesCobradores as docente}
                        <option value={docente}>{docente}</option>
                      {/each}
                    </optgroup>
                  {/if}
                  <optgroup label="Opciones adicionales">
                    <option value="ORIENTACION">ORIENTACION</option>
                    <option value="COORDINADOR">COORDINADOR</option>
                    <option value="BIBLIOTECA">BIBLIOTECA</option>
                  </optgroup>
                </select>
                {#if dupInfo.dup}
                  <div class="text-xs mt-1 font-semibold" style="color: #ef4444;">
                    ⚠️ {dupInfo.sesion > 1 ? `Repetido (${dupInfo.sesion}× hoy)` : ""}{dupInfo.sesion > 1 && dupInfo.historico > 0 ? " · " : ""}{dupInfo.historico > 0 ? `Ya cubrió ${dupInfo.historico}h en historial` : ""}
                  </div>
                {:else if dupInfo.porGrupoLiberado}
                  <div class="text-xs mt-1 font-medium" style="color: #b45309;">
                    ℹ️ Repite válido — hora libre por grupo liberado
                  </div>
                {/if}
              </td>
              <td class="p-3 text-center border-t" style="border-color: rgb(var(--border-primary));">
                {#if cov.docenteCubre}
                  <button
                    onclick={() => abrirHorarioDocente(cov.docenteCubre)}
                    class="font-medium hover:underline cursor-pointer"
                    style="color: rgb(var(--accent-primary));"
                    title="Ver horario semanal de {cov.docenteCubre}"
                  >
                    {cov.grupoACubrir}
                  </button>
                {:else}
                  <span style="color: rgb(var(--text-secondary));">{cov.grupoACubrir}</span>
                {/if}
              </td>
              <td class="p-3 text-center border-t" style="border-color: rgb(var(--border-primary));">
                {#if esViolacion}
                  <span class="px-2 py-1 rounded text-xs font-bold bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-200">
                    {cov.violation}
                  </span>
                {:else if cov.aprobada}
                  <span class="px-2 py-1 rounded text-xs font-bold bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-200">
                    ✓ Aprobada
                  </span>
                {:else}
                  <span class="px-2 py-1 rounded text-xs font-bold bg-zinc-100 dark:bg-zinc-700 text-zinc-500 dark:text-zinc-400">
                    Pendiente
                  </span>
                {/if}
                {#if cov.grupoAusente && (cov.hora === 5 || cov.hora === 6 || cov.hora === 7) && onLiberarGrupoDesdeHora}
                  <span class="text-xs" style="color: rgb(var(--text-secondary));">|</span>
                  <button
                    onclick={() => { console.log("CLICK Liberar", cov.grupoAusente, cov.hora, cov.docenteAusente); onLiberarGrupoDesdeHora(cov.grupoAusente, cov.hora, cov.docenteAusente); }}
                    class="ml-2 px-3 py-1 rounded text-xs font-bold transition-all border-2"
                    style="background-color: #ef4444; color: white; border-color: #b91c1c;"
                  >
                    🗑️ Liberar {cov.grupoAusente} (hora {cov.hora + 1})
                  </button>
                {/if}
              </td>
              <td class="p-3 text-center border-t" style="border-color: rgb(var(--border-primary));">
                <button
                  onclick={() => onToggle(i)}
                  class="w-8 h-8 rounded-full flex items-center justify-center transition-all mx-auto"
                  style="background-color: {cov.aprobada ? 'rgb(var(--accent-primary))' : 'rgb(var(--bg-secondary))'}; color: {cov.aprobada ? 'white' : 'rgb(var(--text-secondary))'};"
                >
                  {cov.aprobada ? "✓" : ""}
                </button>
              </td>
            </tr>
          {/each}
        </tbody>
      </table>
    </div>
  {/if}

  <div class="flex gap-3">
    <button
      onclick={onBack}
      class="flex-1 py-3 rounded-xl font-medium transition-all"
      style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border: 1px solid rgb(var(--border-primary));"
    >
      ← Volver al análisis
    </button>
    <button
      onclick={onGuardar}
      disabled={loading || seleccionadas === 0}
      class="flex-1 py-3 rounded-xl font-bold text-white transition-all disabled:opacity-50 flex items-center justify-center gap-2"
      style="background-color: rgb(var(--accent-primary)); opacity: {loading ? 0.7 : 1};"
    >
      {#if loading}
        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Guardando...
      {:else}
        Guardar {seleccionadas} cobertura(s)
      {/if}
    </button>
  </div>
</div>

<style>
  @keyframes cobertura-blink {
    0%, 100% {
      box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
      border-color: #ef4444;
    }
    50% {
      box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
      border-color: #fca5a5;
    }
  }
  .cobertura-blink-dup {
    animation: cobertura-blink 1s ease-in-out infinite;
  }
</style>