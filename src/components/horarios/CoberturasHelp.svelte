<script lang="ts">
  import { ChevronRight, ChevronLeft, X, HelpCircle, Info, FileText, Calendar, Clock, UserCheck } from "@lucide/svelte";

  let {
    pasoActual = 1,
    onClose,
  }: {
    pasoActual?: number;
    onClose: () => void;
  } = $props();

  type Paso = {
    numero: number;
    titulo: string;
    icono: typeof HelpCircle;
    color: string;
    descripcion: string;
    detalles: string[];
    ejemplo?: string;
    consejo?: string;
  };

  const pasos: Paso[] = [
    {
      numero: 1,
      titulo: "Seleccionar Día y Registrar Ausencias",
      icono: Calendar,
      color: "#3b82f6",
      descripcion: "El primer paso es elegir el día y registrar quién falta.",
      detalles: [
        "Haz clic en el botón del día (LUN, MAR, MIE, JUE, VIE)",
        "La fecha se selecciona automáticamente según el día",
        "Si necesitas otra fecha, usa el calendario",
        "Busca el docente que falta en la lista de 'Docentes ausentes'",
        "Haz clic en el checkbox junto a su nombre",
        "Selecciona el TIPO de ausencia (Incapacidad, Capacitación, etc.)",
        "Si un grupo completo falta, usa el botón 'LIBERAR GRUPOS'",
      ],
      ejemplo: "Si la profesora López está enferma el martes: clic en MAR → buscar 'López' → seleccionar tipo 'INCAPACIDAD'",
      consejo: "Solo puedes seleccionar días hábiles (lunes a viernes). Los festivos de Colombia están bloqueados automáticamente.",
    },
    {
      numero: 2,
      titulo: "Analizar Horas Libres",
      icono: Clock,
      color: "#f59e0b",
      descripcion: "El sistema identifica qué clases quedan sin profesor.",
      detalles: [
        "Haz clic en 'Analizar horas libres'",
        "El sistema revisa todas las horas del día",
        "Identifica dónde hay docentes ausentes",
        "Muestra las horas que necesitan cobertura",
        "Las horas aparecen en rojo si no tienen profesor",
      ],
      ejemplo: "Después de marcar a López como ausente, el sistema mostrará las horas donde ella tenía clase.",
      consejo: "Si ves muchas horas en rojo, puede ser porque marcaste mal los grupos ausentes.",
    },
    {
      numero: 3,
      titulo: "Revisar y Aprobar Coberturas",
      icono: UserCheck,
      color: "#10b981",
      descripcion: "Revisa las coberturas sugeridas y apruébalas o modifícalas.",
      detalles: [
        "Verás una tabla con todas las horas sin profesor",
        "Para CADA hora, el sistema propone un docente",
        "Revisa la propuesta: ¿el docente propuesto es adecuado?",
        "Para APROBAR: haz clic en el botón ✓",
        "Para CAMBIAR docente: haz clic en el campo 'Cubre' y elige otro",
        "Las filas VERDES ya están aprobadas",
        "Las filas AMARILLAS están pendientes",
        "Las filas ROJAS tienen una violación (límite superado)",
      ],
      ejemplo: "Si el sistema propone a 'Juan' para cubrir pero prefieres a 'María', haz clic en el campo 'Cubre' y selecciona 'María'.",
      consejo: "Los docentes con rol especial (ORIENTADOR, COORDINADOR, BIBLIOTECA) no tienen límite de cobertura. úsalos si no hay otras opciones.",
    },
  ];

  const pasoActualData = $derived(pasos.find((p) => p.numero === pasoActual) ?? pasos[0]);
  const indicePaso = $derived(pasos.findIndex((p) => p.numero === pasoActual));

  function pasoAnterior() {
    if (indicePaso > 0) {
      window.dispatchEvent(new CustomEvent("cobertura-help-paso", { detail: pasos[indicePaso - 1].numero }));
    }
  }

  function pasoSiguiente() {
    if (indicePaso < pasos.length - 1) {
      window.dispatchEvent(new CustomEvent("cobertura-help-paso", { detail: pasos[indicePaso + 1].numero }));
    }
  }

  const esPrimerPaso = $derived(indicePaso === 0);
  const esUltimoPaso = $derived(indicePaso === pasos.length - 1);
</script>

<div class="fixed inset-0 z-[100] flex items-center justify-center p-4" style="background-color: rgba(0,0,0,0.6);" role="dialog" aria-modal="true" aria-labelledby="help-title">
  <div class="rounded-2xl w-full max-w-2xl max-h-[85vh] overflow-hidden flex flex-col" style="background-color: rgb(var(--bg-primary)); border: 1px solid rgb(var(--border-primary));">
    <div class="flex items-center justify-between p-4 border-b" style="border-color: rgb(var(--border-primary));">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: {pasoActualData.color}20;">
          <HelpCircle size={24} style="color: {pasoActualData.color};" />
        </div>
        <div>
          <h2 id="help-title" class="text-lg font-bold" style="color: rgb(var(--text-primary));">
            Ayuda: Gestión de Coberturas
          </h2>
          <p class="text-xs" style="color: rgb(var(--text-secondary));">
            Paso {pasoActualData.numero} de {pasos.length}
          </p>
        </div>
      </div>
      <button
        onclick={onClose}
        class="w-8 h-8 rounded-full flex items-center justify-center transition-colors"
        style="color: rgb(var(--text-secondary)); background-color: rgb(var(--bg-secondary));"
        aria-label="Cerrar ayuda"
      >
        <X size={18} />
      </button>
    </div>

    <div class="flex items-center justify-center gap-2 py-3" style="background-color: rgb(var(--bg-secondary));">
      {#each pasos as paso}
        <button
          onclick={() => window.dispatchEvent(new CustomEvent("cobertura-help-paso", { detail: paso.numero }))}
          class="w-3 h-3 rounded-full transition-all"
          style="background-color: {paso.numero === pasoActual ? paso.color : 'rgb(var(--border-primary))'};"
          aria-label="Ir al paso {paso.numero}"
        ></button>
      {/each}
    </div>

    <div class="flex-1 overflow-y-auto p-6">
      {#if true}
        {@const Icon = pasoActualData.icono}
        <div class="flex items-start gap-4 mb-4">
          <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0" style="background-color: {pasoActualData.color}15; border: 2px solid {pasoActualData.color}30;">
            <Icon size={28} style="color: {pasoActualData.color};" />
          </div>
          <div class="flex-1">
            <h3 class="text-base font-bold mb-1" style="color: rgb(var(--text-primary));">
              {pasoActualData.numero}. {pasoActualData.titulo}
            </h3>
            <p class="text-sm" style="color: rgb(var(--text-secondary));">
              {pasoActualData.descripcion}
            </p>
          </div>
        </div>
      {/if}

      <div class="space-y-2 mb-4">
        {#each pasoActualData.detalles as detalle, i}
          <div class="flex items-start gap-3 p-2 rounded-lg" style="background-color: rgb(var(--bg-secondary));">
            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0" style="background-color: {pasoActualData.color}; color: white;">
              {i + 1}
            </span>
            <p class="text-sm" style="color: rgb(var(--text-primary));">
              {detalle}
            </p>
          </div>
        {/each}
      </div>

      {#if pasoActualData.ejemplo}
        <div class="p-4 rounded-xl mb-4" style="background-color: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3);">
          <div class="flex items-start gap-2">
            <FileText size={18} class="shrink-0 mt-0.5" style="color: #3b82f6;" />
            <div>
              <p class="text-xs font-bold mb-1" style="color: #3b82f6;">EJEMPLO PRÁCTICO</p>
              <p class="text-sm" style="color: rgb(var(--text-primary));">
                {pasoActualData.ejemplo}
              </p>
            </div>
          </div>
        </div>
      {/if}

      {#if pasoActualData.consejo}
        <div class="p-3 rounded-lg flex items-start gap-2" style="background-color: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3);">
          <Info size={16} class="shrink-0 mt-0.5" style="color: #10b981;" />
          <p class="text-xs" style="color: rgb(var(--text-primary));">
            <strong>Consejo:</strong> {pasoActualData.consejo}
          </p>
        </div>
      {/if}
    </div>

    <div class="flex items-center justify-between p-4 border-t" style="border-color: rgb(var(--border-primary));">
      <button
        onclick={pasoAnterior}
        disabled={esPrimerPaso}
        class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all disabled:opacity-40 disabled:cursor-not-allowed"
        style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border: 1px solid rgb(var(--border-primary));"
      >
        <ChevronLeft size={16} />
        Anterior
      </button>

      <span class="text-xs font-medium" style="color: rgb(var(--text-secondary));">
        {pasos.map(p => p.numero).join(" · ")}
      </span>

      <button
        onclick={esUltimoPaso ? onClose : pasoSiguiente}
        class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold transition-all"
        style="background-color: {esUltimoPaso ? 'rgb(var(--accent-primary))' : pasoActualData.color}; color: white;"
      >
        {esUltimoPaso ? "Entendido" : "Siguiente"}
        {#if !esUltimoPaso}
          <ChevronRight size={16} />
        {/if}
      </button>
    </div>
  </div>
</div>