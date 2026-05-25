<script lang="ts">
  import { onMount } from "svelte";
  import horariosData from "../../lib/horarios.json";
  import {
    DIAS,
    getDocentesList,
    getGruposDisponibles,
    getDiaFromFecha,
    getSlotsDelDia,
    aplicarAusencias,
    asignarAutomaticamente,
    getSlotsLibresPorAusencia,
    formatoDia,
    ROLES_SIN_LIMITE,
  } from "../../lib/coberturaUtils";
  import { coberturaSheetsService } from "../../services/coberturaSheetsService";
  import type { SlotInfo, CoberturaSugerida, CoberturaHistorica, Ausencia, SugerenciaGrupo } from "../../lib/coberturaUtils";
  import { analizarGruposAAusentar } from "../../lib/coberturaUtils";
  import AnalisisView from "./AnalisisView.svelte";
  import AsignacionesView from "./AsignacionesView.svelte";
  import HistorialCoberturas from "./HistorialCoberturas.svelte";
  import WhatsAppReport from "./WhatsAppReport.svelte";
  import Swal from "sweetalert2";
  import ModuleHeader from "../ModuleHeader.svelte";
  import { Flame, GraduationCap, Car, Heart, Shield, Stethoscope, Briefcase, Calendar, Users, Scale, Skull, Laptop, Award } from "@lucide/svelte";

  let { onBack }: { onBack: () => void } = $props();

  type SubView = "cobertura" | "historial";
  let subView = $state<SubView>("cobertura");
  let step = $state(1);
  let loading = $state(false);

  let docentes = $state(getDocentesList(horariosData));
  let grupos = $state(getGruposDisponibles(horariosData));

  let fechaSeleccionada = $state("");
  let diaSeleccionado = $state("");
  let docentesAusentes = $state<{ nombre: string; tipo: string }[]>([]);
  let gruposAusentes = $state<{ grupo: string; horaInicio: number }[]>([]);

  let slotsDelDia = $state<SlotInfo[]>([]);
  let slotsConAusencia = $state<SlotInfo[]>([]);
  let coberturasSugeridas = $state<CoberturaSugerida[]>([]);
  let coberturasHistoricas = $state<CoberturaHistorica[]>([]);
  let gruposSugeridosAAusentar = $state<SugerenciaGrupo[]>([]);
  let mostrarModalGrupos = $state(false);
  let mostrarReporteWhatsApp = $state(false);
  let coberturasGuardadas = $state<CoberturaSugerida[]>([]);
  let vistaPreviaReporte = $state(false);
  let mostrarModalTipoAusencia = $state(false);
  let docenteSeleccionado = $state("");

  const isDev = import.meta.env.DEV;

  function getFechaHoy(): string {
    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, "0");
    const d = String(now.getDate()).padStart(2, "0");
    return `${y}-${m}-${d}`;
  }

  function getFechaSemana(): string[] {
    const hoy = new Date(getFechaHoy() + "T00:00:00");
    const diaSemana = hoy.getDay();
    const lunes = new Date(hoy);
    lunes.setDate(hoy.getDate() - (diaSemana === 0 ? 6 : diaSemana - 1));
    const dias: string[] = [];
    for (let i = 0; i < 5; i++) {
      const d = new Date(lunes);
      d.setDate(lunes.getDate() + i);
      const y = d.getFullYear();
      const m = String(d.getMonth() + 1).padStart(2, "0");
      const day = String(d.getDate()).padStart(2, "0");
      dias.push(`${y}-${m}-${day}`);
    }
    return dias;
  }

  function seleccionarDia(dia: string) {
    diaSeleccionado = dia;
    const idx = DIAS.indexOf(dia as any);
    const fechas = getFechaSemana();
    if (fechas[idx]) fechaSeleccionada = fechas[idx];
  }

  async function loadHistorico() {
    try {
      loading = true;
      coberturasHistoricas = await coberturaSheetsService.getCoberturas();
    } catch (e: any) {
      Swal.fire("Error", e.message, "error");
    } finally {
      loading = false;
    }
  }

  function analizarHorasLibres() {
    if (!diaSeleccionado) {
      Swal.fire("Atención", "Selecciona un día primero", "warning");
      return;
    }

    const allSlots = getSlotsDelDia(diaSeleccionado, horariosData);
    slotsDelDia = allSlots;

    const ausencias: Ausencia[] = [
      ...docentesAusentes.map((n) => ({ tipo: "docente" as const, nombre: n.nombre, motivo: n.tipo })),
      ...gruposAusentes.map((g) => ({ tipo: "grupo" as const, nombre: g.grupo, horaInicio: g.horaInicio, motivo: "GRUPO AUSENTE" })),
    ];

    slotsConAusencia = aplicarAusencias(allSlots, ausencias, horariosData);
    step = 2;
  }

function recalcularCoberturas() {
    const allSlots = getSlotsDelDia(diaSeleccionado, horariosData);
    slotsDelDia = allSlots;

    const ausencias: Ausencia[] = [
      ...docentesAusentes.map((n) => ({ tipo: "docente" as const, nombre: n.nombre, motivo: n.tipo })),
      ...gruposAusentes.map((g) => ({ tipo: "grupo" as const, nombre: g.grupo, horaInicio: g.horaInicio, motivo: "GRUPO AUSENTE" })),
    ];

    slotsConAusencia = aplicarAusencias(allSlots, ausencias, horariosData);

    const libresPorAusencia = getSlotsLibresPorAusencia(slotsConAusencia);
    if (libresPorAusencia.length === 0) {
      coberturasSugeridas = [];
      gruposSugeridosAAusentar = [];
      return;
    }

    coberturasSugeridas = asignarAutomaticamente(
      libresPorAusencia,
      horariosData,
      coberturasHistoricas,
      diaSeleccionado,
      fechaSeleccionada
    );
    gruposSugeridosAAusentar = analizarGruposAAusentar(
      libresPorAusencia,
      coberturasSugeridas,
      gruposAusentes.map((g) => g.grupo),
      horariosData,
      diaSeleccionado
    );
  }

  function isGrupoAusente(grupo: string): boolean {
    return gruposAusentes.some((g) => g.grupo === grupo);
  }

  function getHoraInicio(grupo: string): number {
    return gruposAusentes.find((g) => g.grupo === grupo)?.horaInicio ?? 5;
  }

  function toggleGrupoAusente(grupo: string, checked: boolean, horaInicio: number = 5) {
    if (checked) {
      if (!gruposAusentes.some((g) => g.grupo === grupo)) {
        gruposAusentes = [...gruposAusentes, { grupo, horaInicio }];
      }
    } else {
      gruposAusentes = gruposAusentes.filter((g) => g.grupo !== grupo);
    }
  }

  function actualizarHoraInicio(grupo: string, horaInicio: number) {
    gruposAusentes = gruposAusentes.map((g) =>
      g.grupo === grupo ? { ...g, horaInicio } : g
    );
  }

  function agregarGrupoAusente(grupo: string, horaInicio: number = 5) {
    if (!gruposAusentes.some((g) => g.grupo === grupo)) {
      gruposAusentes = [...gruposAusentes, { grupo, horaInicio }];
      gruposSugeridosAAusentar = gruposSugeridosAAusentar.filter((g) => g.grupo !== grupo);
      recalcularCoberturas();
    }
  }

  function liberarGrupoDesdeHora(grupo: string, hora: number) {
    const horaInicio = hora + 1;
    if (!gruposAusentes.some((g) => g.grupo === grupo)) {
      gruposAusentes = [...gruposAusentes, { grupo, horaInicio }];
      gruposSugeridosAAusentar = gruposSugeridosAAusentar.filter((g) => g.grupo !== grupo);
      recalcularCoberturas();
    }
  }

  async function generarAsignaciones() {
    loading = true;
    try {
      await loadHistorico();
      recalcularCoberturas();

      if (coberturasSugeridas.length === 0) {
        Swal.fire("Sin horas libres", "No hay horas libres por ausencia en el día seleccionado", "info");
        return;
      }
      step = 3;
    } finally {
      loading = false;
    }
  }

  async function guardarCoberturas() {
    const aprobadas = coberturasSugeridas.filter((c) => c.aprobada);
    if (aprobadas.length === 0) {
      Swal.fire("Atención", "No hay coberturas seleccionadas para guardar", "warning");
      return;
    }

    loading = true;
    try {
      await coberturaSheetsService.deleteCoberturasPorFecha(fechaSeleccionada);

      for (const c of aprobadas) {
        await coberturaSheetsService.saveCobertura({
          fecha: fechaSeleccionada,
          dia_semana: diaSeleccionado,
          hora: c.hora,
          docente_ausente: c.docenteAusente,
          grupo_ausente: c.grupoAusente,
          docente_cubre: c.docenteCubre,
          grupo_a_cubrir: c.grupoACubrir,
          estado: "aprobado",
          motivo: c.motivoAusencia,
        });
      }

      await Swal.fire({
        icon: "success",
        title: "Coberturas guardadas",
        html: `Se guardaron ${aprobadas.length} cobertura(s) en el historial.`,
        showCancelButton: true,
        confirmButtonText: "Compartir WhatsApp",
        cancelButtonText: "Nueva sesión",
      }).then((r) => {
        if (r.isConfirmed) {
          coberturasGuardadas = [...aprobadas];
          mostrarReporteWhatsApp = true;
        } else if (r.dismiss === Swal.DismissReason.cancel) {
          resetSesion();
        }
      });
    } catch (e: any) {
      Swal.fire("Error", e.message, "error");
    } finally {
      loading = false;
    }
  }

  function resetSesion() {
    step = 1;
    docentesAusentes = [];
    gruposAusentes = [];
    slotsDelDia = [];
    slotsConAusencia = [];
    coberturasSugeridas = [];
    gruposSugeridosAAusentar = [];
    diaSeleccionado = "";
    fechaSeleccionada = "";
  }

  function toggleCobertura(index: number) {
    coberturasSugeridas[index].aprobada = !coberturasSugeridas[index].aprobada;
  }

  function aprobarTodo() {
    coberturasSugeridas = coberturasSugeridas.map((c) => ({
      ...c,
      aprobada: true,
    }));
  }

  function cambiarDocenteCubre(index: number, docente: string) {
    const docenteAnterior = coberturasSugeridas[index].docenteCubre;
    coberturasSugeridas[index].docenteCubre = docente;

    const ocupados = coberturasSugeridas
      .filter((c, i) => i !== index && c.docenteCubre && !ROLES_SIN_LIMITE.some(r => c.docenteCubre?.includes(r)))
      .map(c => c.docenteCubre);

    coberturasSugeridas = coberturasSugeridas.map((c, i) => {
      if (i === index) return c;
      const nuevosCobradores = horariosData
        .filter((h: any) => {
          if (ocupados.includes(h.docente)) return false;
          return true;
        })
        .map((h: any) => h.docente);
      const disponibles = [...nuevosCobradores, ...ROLES_SIN_LIMITE];
      if (docenteAnterior && !ocupados.includes(docenteAnterior) && !ROLES_SIN_LIMITE.some(r => docenteAnterior.includes(r))) {
        disponibles.push(docenteAnterior);
      }
      return { ...c, posiblesCobradores: disponibles };
    });
  }

  function liberarGrupoAsignacion(grupo: string, horaInicio: number) {
    agregarGrupoAusente(grupo, horaInicio);
  }

  function goToStep(s: number) {
    step = s;
  }

  onMount(() => {
    diaSeleccionado = "";
    fechaSeleccionada = "";
  });
</script>

<ModuleHeader
  title="Cobertura de Horas"
  onBack={onBack}
/>

<div class="p-4 max-w-7xl mx-auto">
  <div class="flex gap-2 mb-6">
    <button
      onclick={() => { subView = "cobertura"; resetSesion(); }}
      class="px-4 py-2 rounded-lg font-medium text-sm transition-all"
      style="background-color: {subView === 'cobertura' ? 'rgb(var(--accent-primary))' : 'rgb(var(--card-bg))'}; color: {subView === 'cobertura' ? 'white' : 'rgb(var(--text-primary))'}; border: 1px solid {subView === 'cobertura' ? 'rgb(var(--accent-primary))' : 'rgb(var(--border-primary))'};"
    >
      Gestionar Coberturas
    </button>
    <button
      onclick={() => { subView = "historial"; loadHistorico(); }}
      class="px-4 py-2 rounded-lg font-medium text-sm transition-all"
      style="background-color: {subView === 'historial' ? 'rgb(var(--accent-primary))' : 'rgb(var(--card-bg))'}; color: {subView === 'historial' ? 'white' : 'rgb(var(--text-primary))'}; border: 1px solid {subView === 'historial' ? 'rgb(var(--accent-primary))' : 'rgb(var(--border-primary))'};"
    >
      Ver Historial
    </button>
  </div>

  {#if subView === "historial"}
    <HistorialCoberturas {coberturasHistoricas} {loading} onReload={loadHistorico} />
  {:else}
    {#if step >= 1}
      <div class="flex items-center gap-2 mb-6 overflow-x-auto pb-2">
        {#each [1, 2, 3] as s}
          <div class="flex items-center gap-2">
            <button
              onclick={() => s <= step && goToStep(s)}
              class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm shrink-0 transition-all
                {s === step ? 'ring-2 ring-offset-2' : ''}"
              style="
                background-color: {s < step ? 'rgb(var(--accent-primary))' : s === step ? 'rgb(var(--accent-primary))' : 'rgb(var(--bg-secondary))'};
                color: {s <= step ? 'white' : 'rgb(var(--text-secondary))'};
                ring-color: {s === step ? 'rgb(var(--accent-primary))' : 'transparent'};
              "
            >
              {s}
            </button>
            {#if s < 3}
              <div class="w-8 h-0.5" style="background-color: {s < step ? 'rgb(var(--accent-primary))' : 'rgb(var(--border-primary))'};"></div>
            {/if}
          </div>
        {/each}
      </div>
    {/if}

    {#if step === 1}
      <div class="p-6 rounded-2xl border" style="border-color: rgb(var(--border-primary)); background-color: rgb(var(--card-bg));">
        <h2 class="text-lg font-bold mb-4" style="color: rgb(var(--text-primary));">Step 1 — Día y Ausencias</h2>

        <div class="mb-4">
          <p class="block text-sm font-medium mb-2" style="color: rgb(var(--text-secondary));">Día de la semana</p>
          <div class="flex gap-2 flex-wrap">
            {#each DIAS as dia, i}
              <button
                onclick={() => seleccionarDia(dia)}
                class="px-4 py-2 rounded-lg font-medium text-sm transition-all"
                style="
                  background-color: {diaSeleccionado === dia ? 'rgb(var(--accent-primary))' : 'rgb(var(--bg-secondary))'};
                  color: {diaSeleccionado === dia ? 'white' : 'rgb(var(--text-primary))'};
                  border: 1px solid {diaSeleccionado === dia ? 'rgb(var(--accent-primary))' : 'rgb(var(--border-primary))'};
                "
              >
                {dia.toUpperCase()}
              </button>
            {/each}
          </div>
          {#if fechaSeleccionada}
            <p class="text-xs mt-2" style="color: rgb(var(--text-secondary));">
              📅 {fechaSeleccionada} ({formatoDia(diaSeleccionado)})
            </p>
          {/if}
        </div>

        <div class="mb-4">
          <p class="block text-sm font-medium mb-2" style="color: rgb(var(--text-secondary));">
            Docentes ausentes
          </p>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-48 overflow-y-auto p-2 rounded-lg border" style="border-color: rgb(var(--border-primary)); background-color: rgb(var(--bg-secondary));">
            {#each docentes as docente}
              {@const ausencia = docentesAusentes.find((a) => a.nombre === docente)}
              <label class="flex items-center gap-2 cursor-pointer p-1 rounded hover:bg-[rgb(var(--card-bg))]">
                <input
                  type="checkbox"
                  checked={!!ausencia}
                  onchange={(e) => {
                    if (e.currentTarget.checked) {
                      docenteSeleccionado = docente;
                      mostrarModalTipoAusencia = true;
                    } else {
                      docentesAusentes = docentesAusentes.filter((a) => a.nombre !== docente);
                    }
                  }}
                  class="w-4 h-4 accent-[rgb(var(--accent-primary))]"
                />
                <span class="text-xs truncate" style="color: rgb(var(--text-primary));">{docente}</span>
                {#if ausencia}
                  <span class="text-[10px] px-1 rounded" style="background-color: rgb(var(--accent-primary)); color: white;">{ausencia.tipo}</span>
                {/if}
              </label>
            {/each}
          </div>
        </div>

        <div class="mb-6">
          <p class="block text-sm font-medium mb-2" style="color: rgb(var(--text-secondary));">
            Grupos ausentes
          </p>
          <button
            onclick={() => mostrarModalGrupos = true}
            class="w-full py-3 rounded-xl font-bold transition-all border-2"
            style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--accent-primary)); border-color: rgb(var(--accent-primary));"
          >
            LIBERAR GRUPOS {gruposAusentes.length > 0 ? `(${gruposAusentes.length})` : ""}
          </button>
          {#if gruposAusentes.length > 0}
            <div class="flex flex-wrap gap-2 mt-2">
              {#each gruposAusentes as g}
                <span class="px-2 py-1 rounded text-xs font-medium bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-200">
                  {g.grupo} {g.horaInicio > 1 ? `desde h${g.horaInicio}` : "desde h1"}
                </span>
              {/each}
            </div>
          {/if}
        </div>

        <button
          onclick={analizarHorasLibres}
          disabled={loading}
          class="w-full py-3 rounded-xl font-bold text-white transition-all flex items-center justify-center gap-2"
          style="background-color: rgb(var(--accent-primary)); opacity: {loading ? 0.7 : 1};"
        >
          {#if loading}
            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Calculando...
          {:else}
            Analizar horas libres →
          {/if}
        </button>
      </div>
    {/if}

    {#if step === 2}
      <AnalisisView
        {diaSeleccionado}
        {fechaSeleccionada}
        {docentesAusentes}
        {gruposAusentes}
        slots={slotsConAusencia}
        {loading}
        onGenerar={generarAsignaciones}
        onBack={() => step = 1}
        onOpenGruposModal={() => mostrarModalGrupos = true}
      />
    {/if}

    {#if step === 3}
      <AsignacionesView
        {diaSeleccionado}
        {fechaSeleccionada}
        {coberturasSugeridas}
        {gruposSugeridosAAusentar}
        {loading}
        onToggle={toggleCobertura}
        onCambiarDocenteCubre={cambiarDocenteCubre}
        onAgregarGrupoAusente={agregarGrupoAusente}
        onGuardar={guardarCoberturas}
        onBack={() => step = 2}
        onOpenGruposModal={() => mostrarModalGrupos = true}
        onLiberarGrupoDesdeHora={liberarGrupoDesdeHora}
        onAprobarTodo={aprobarTodo}
      />
      {#if isDev}
        <div class="mt-4 p-4 rounded-xl border-2 border-dashed" style="border-color: rgb(var(--accent-primary));">
          <p class="text-xs font-medium mb-2 text-center" style="color: rgb(var(--accent-primary));">MODO DESARROLLO</p>
          <button
            onclick={() => vistaPreviaReporte = true}
            class="w-full py-2 rounded-lg font-medium transition-all"
            style="background-color: rgb(var(--accent-primary)); color: white;"
          >
            Vista previa imagen WhatsApp
          </button>
        </div>
      {/if}
    {/if}

    {#if mostrarModalGrupos}
      <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(0,0,0,0.5);">
        <div class="rounded-xl p-6 w-full max-w-2xl max-h-[80vh] overflow-hidden flex flex-col" style="background-color: rgb(var(--bg-primary)); border: 1px solid rgb(var(--border-primary));">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold" style="color: rgb(var(--text-primary));">Liberar Grupos</h3>
            <button
              onclick={() => mostrarModalGrupos = false}
              class="w-8 h-8 flex items-center justify-center rounded-full"
              style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary));"
            >
              ✕
            </button>
          </div>

          <div class="flex-1 overflow-y-auto mb-4">
            <div class="flex gap-2 flex-wrap p-2 rounded-lg border" style="border-color: rgb(var(--border-primary)); background-color: rgb(var(--bg-secondary));">
              {#each grupos as grupo}
                <div class="flex items-center gap-1 px-2 py-1 rounded hover:bg-[rgb(var(--card-bg))]">
                  <input
                    type="checkbox"
                    checked={isGrupoAusente(grupo)}
                    onchange={(e) => toggleGrupoAusente(grupo, e.currentTarget.checked, 5)}
                    class="w-4 h-4 accent-[rgb(var(--accent-primary))]"
                  />
                  <span class="text-xs font-medium" style="color: rgb(var(--text-primary));">{grupo}</span>
                  {#if isGrupoAusente(grupo)}
                    <select
                      value={getHoraInicio(grupo)}
                      onchange={(e) => actualizarHoraInicio(grupo, parseInt(e.currentTarget.value))}
                      class="ml-1 px-1 py-0.5 text-xs rounded border"
                      style="background-color: rgb(var(--bg-primary)); color: rgb(var(--accent-primary)); border-color: rgb(var(--border-primary));"
                    >
                      <option value={1}>desde h1</option>
                      <option value={3}>desde h3</option>
                      <option value={5}>desde h5</option>
                      <option value={6}>desde h6</option>
                      <option value={7}>desde h7</option>
                    </select>
                  {/if}
                </div>
              {/each}
            </div>
          </div>

          {#if gruposAusentes.length > 0}
            <div class="mb-4 p-3 rounded-lg" style="background-color: rgb(var(--bg-secondary));">
              <p class="text-xs font-medium mb-2" style="color: rgb(var(--text-secondary));">Grupos seleccionados:</p>
              <div class="flex flex-wrap gap-2">
                {#each gruposAusentes as g}
                  <span class="px-2 py-1 rounded text-xs font-medium bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-200">
                    {g.grupo} {g.horaInicio > 1 ? `desde h${g.horaInicio}` : "desde h1"}
                  </span>
                {/each}
              </div>
            </div>
          {/if}

          <div class="flex gap-3">
            <button
              onclick={() => { gruposAusentes = []; }}
              class="flex-1 py-2 rounded-lg font-medium transition-all"
              style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border: 1px solid rgb(var(--border-primary));"
            >
              Limpiar todo
            </button>
            <button
              onclick={() => { mostrarModalGrupos = false; if (step >= 2) recalcularCoberturas(); }}
              class="flex-1 py-2 rounded-lg font-bold text-white transition-all"
              style="background-color: rgb(var(--accent-primary));"
            >
              Listo
            </button>
          </div>
        </div>
      </div>
    {/if}

    {#if mostrarModalTipoAusencia}
      <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(0,0,0,0.5);">
        <div class="rounded-xl p-6 w-full max-w-md" style="background-color: rgb(var(--bg-primary)); border: 1px solid rgb(var(--border-primary));">
          <h3 class="text-lg font-bold mb-4" style="color: rgb(var(--text-primary));">Tipo de ausencia</h3>
          <p class="text-sm mb-4" style="color: rgb(var(--text-secondary));">
            Selecciona el tipo de ausencia para <strong>{docenteSeleccionado}</strong>
          </p>
          <div class="grid grid-cols-2 gap-2 mb-4">
            {#each [
              { tipo: "CALAMIDAD", icono: Flame, color: "#f97316" },
              { tipo: "CAPACITACION", icono: GraduationCap, color: "#8b5cf6" },
              { tipo: "DESPLAZAMIENTO", icono: Car, color: "#06b6d4" },
              { tipo: "FAMILIAR", icono: Heart, color: "#ec4899" },
              { tipo: "FUERZA MAYOR", icono: Shield, color: "#6366f1" },
              { tipo: "INCAPACIDAD", icono: Stethoscope, color: "#ef4444" },
              { tipo: "LICENCIA", icono: Award, color: "#eab308" },
              { tipo: "LUTO", icono: Skull, color: "#1f2937" },
              { tipo: "MEDICO", icono: Stethoscope, color: "#10b981" },
              { tipo: "PERSONAL", icono: Briefcase, color: "#f59e0b" },
              { tipo: "QUINQUENIO", icono: Calendar, color: "#3b82f6" },
              { tipo: "REUNION", icono: Users, color: "#14b8a6" },
              { tipo: "SECRETARIA", icono: Laptop, color: "#0ea5e9" },
              { tipo: "SINDICATO", icono: Scale, color: "#78716c" },
            ] as item}
              {@const Icon = item.icono}
              <button
                onclick={() => {
                  docentesAusentes = [...docentesAusentes.filter((a) => a.nombre !== docenteSeleccionado), { nombre: docenteSeleccionado, tipo: item.tipo }];
                  mostrarModalTipoAusencia = false;
                  docenteSeleccionado = "";
                }}
                class="py-2 px-3 rounded-lg text-sm font-medium transition-all flex items-center gap-2"
                style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border: 1px solid rgb(var(--border-primary));"
              >
                <Icon size={16} style="color: {item.color}" />
                {item.tipo}
              </button>
            {/each}
          </div>
          <button
            onclick={() => { mostrarModalTipoAusencia = false; docenteSeleccionado = ""; }}
            class="w-full py-2 rounded-lg font-medium"
            style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border: 1px solid rgb(var(--border-primary));"
          >
            Cancelar
          </button>
        </div>
      </div>
    {/if}

    {#if mostrarReporteWhatsApp}
      <WhatsAppReport
        {diaSeleccionado}
        {fechaSeleccionada}
        coberturas={coberturasGuardadas}
        {gruposAusentes}
        {docentesAusentes}
        onClose={() => mostrarReporteWhatsApp = false}
      />
    {/if}

    {#if vistaPreviaReporte}
      <WhatsAppReport
        {diaSeleccionado}
        {fechaSeleccionada}
        coberturas={coberturasSugeridas}
        {gruposAusentes}
        {docentesAusentes}
        onClose={() => vistaPreviaReporte = false}
      />
    {/if}
  {/if}
</div>