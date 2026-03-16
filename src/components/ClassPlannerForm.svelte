<script lang="ts">
  import { onMount, tick } from "svelte";
  import { fade, fly, scale } from "svelte/transition";
  import { cubicOut, elasticOut } from "svelte/easing";

  let { onBack }: { onBack: () => void } = $props();

  // ─── State ───────────────────────────────────────────────
  let currentStep = $state(0);
  let isMobile = $state(false);
  let sidebarOpen = $state(false);
  let showPdfPreview = $state(false);
  let isGeneratingPdf = $state(false);
  let isSaving = $state(false);
  let savedAnimation = $state(false);
  let activeTab = $state<"dba" | "ebc" | "piar">("dba");

  // Moment accordion state
  let openMoments = $state<Set<number>>(new Set([0]));

  const STEPS = [
    { id: 0, label: "Datos", icon: "user", color: "#6366f1" },
    { id: 1, label: "Tiempo", icon: "clock", color: "#0ea5e9" },
    { id: 2, label: "Referentes", icon: "book", color: "#8b5cf6" },
    { id: 3, label: "Secuencia", icon: "layers", color: "#f59e0b" },
    { id: 4, label: "Evaluación", icon: "check-circle", color: "#10b981" },
    { id: 5, label: "Recursos", icon: "package", color: "#f43f5e" },
  ];

  const MOMENTO_COLORS = ["#818cf8","#34d399","#fbbf24","#f87171","#a78bfa"];
  const MOMENTO_BG = ["#1e1b4b","#064e3b","#451a03","#450a0a","#2e1065"];

  const MOMENTOS = [
    { num: 1, name: "Exploración", desc: "Saberes previos y motivación", field: "exploration", time: "tiempo_exploracion" },
    { num: 2, name: "Estructuración", desc: "Construcción del conocimiento", field: "structuring", time: "tiempo_estructuracion" },
    { num: 3, name: "Práctica", desc: "Ejercitación colaborativa", field: "practice", time: "tiempo_practica" },
    { num: 4, name: "Transferencia", desc: "Aplicación en nuevos contextos", field: "transfer", time: "tiempo_transferencia" },
    { num: 5, name: "Valoración", desc: "Metacognición y evidencias", field: "assessment_moment", time: "tiempo_valoracion" },
  ];

  const ACTIVIDADES: Record<string, { id: string; label: string }[]> = {
    exploration: [
      { id: "preguntas", label: "Preguntas generadoras" },
      { id: "video", label: "Video introductorio" },
      { id: "lluvia", label: "Lluvia de ideas" },
      { id: "imagenes", label: "Análisis de imágenes" },
      { id: "juego", label: "Dinámica / Juego" },
      { id: "situacion", label: "Situación problema" },
    ],
    structuring: [
      { id: "exposicion", label: "Exposición magistral" },
      { id: "mapa", label: "Mapa conceptual" },
      { id: "demo", label: "Demostración" },
      { id: "lectura", label: "Lectura guiada" },
      { id: "sim", label: "Simulación TIC" },
      { id: "ejemplo", label: "Ejemplificación" },
    ],
    practice: [
      { id: "ejercicios", label: "Ejercicios individuales" },
      { id: "grupo", label: "Trabajo colaborativo" },
      { id: "investiga", label: "Investigación" },
      { id: "proyecto", label: "Proyecto creativo" },
      { id: "caso", label: "Análisis de casos" },
      { id: "debate", label: "Debate" },
    ],
    transfer: [
      { id: "real", label: "Problema real" },
      { id: "exporal", label: "Exposición oral" },
      { id: "producto", label: "Producto final" },
      { id: "tarea", label: "Tarea para casa" },
      { id: "servicio", label: "Servicio comunitario" },
      { id: "digital", label: "Presentación digital" },
    ],
    assessment_moment: [
      { id: "autoeval", label: "Autoevaluación" },
      { id: "coeval", label: "Coevaluación" },
      { id: "metacog", label: "Metacognición" },
      { id: "rubrica", label: "Rúbrica" },
      { id: "cierre", label: "Síntesis / Cierre" },
      { id: "retro", label: "Retroalimentación" },
    ],
  };

  type FormData = {
    docente: string;
    grado: string;
    subject: string;
    period: string;
    jornada: string;
    planeacion_tipo: string;
    periodo_academico: string;
    fecha_inicio: string;
    fecha_fin: string;
    dba: string[];
    standard: string[];
    dba_manual: string;
    has_piar: boolean;
    piar_tipo: string;
    piar_description: string;
    learning_objectives: string;
    competencias: string;
    indicadores_logro: string;
    exploration: string;
    exploration_activities: string[];
    tiempo_exploracion: number;
    structuring: string;
    structuring_activities: string[];
    tiempo_estructuracion: number;
    practice: string;
    practice_activities: string[];
    tiempo_practica: number;
    transfer: string;
    transfer_activities: string[];
    tiempo_transferencia: number;
    assessment_moment: string;
    assessment_activities: string[];
    tiempo_valoracion: number;
    eval_type: string;
    eval_modalidades: string[];
    eval_instrumentos: string[];
    eval_criterios: string[];
    eval_evidencias: string[];
    eval_ponderacion_conceptos: number;
    eval_ponderacion_procedimientos: number;
    eval_ponderacion_actitudes: number;
    resources: string;
    recursos_ids: string[];
  };

  let formData = $state<FormData>({
    docente: "",
    grado: "",
    subject: "",
    period: "",
    jornada: "Mañana",
    planeacion_tipo: "",
    periodo_academico: "",
    fecha_inicio: new Date().toISOString().split("T")[0],
    fecha_fin: new Date().toISOString().split("T")[0],
    dba: [],
    standard: [],
    dba_manual: "",
    has_piar: false,
    piar_tipo: "",
    piar_description: "",
    learning_objectives: "",
    competencias: "",
    indicadores_logro: "",
    exploration: "",
    exploration_activities: [],
    tiempo_exploracion: 10,
    structuring: "",
    structuring_activities: [],
    tiempo_estructuracion: 20,
    practice: "",
    practice_activities: [],
    tiempo_practica: 25,
    transfer: "",
    transfer_activities: [],
    tiempo_transferencia: 15,
    assessment_moment: "",
    assessment_activities: [],
    tiempo_valoracion: 10,
    eval_type: "formativa",
    eval_modalidades: [],
    eval_instrumentos: [],
    eval_criterios: [],
    eval_evidencias: [],
    eval_ponderacion_conceptos: 30,
    eval_ponderacion_procedimientos: 40,
    eval_ponderacion_actitudes: 30,
    resources: "",
    recursos_ids: [],
  });

  // ─── Derived ──────────────────────────────────────────────
  let tiempoTotal = $derived(
    formData.tiempo_exploracion +
    formData.tiempo_estructuracion +
    formData.tiempo_practica +
    formData.tiempo_transferencia +
    formData.tiempo_valoracion
  );

  let completedSteps = $derived.by(() => {
    const done = new Set<number>();
    if (formData.docente && formData.grado && formData.subject) done.add(0);
    if (formData.planeacion_tipo && formData.periodo_academico) done.add(1);
    if (formData.dba.length > 0 || formData.standard.length > 0 || formData.dba_manual) done.add(2);
    if (formData.exploration && formData.structuring && formData.practice && formData.transfer) done.add(3);
    if (formData.eval_type && formData.eval_modalidades.length > 0) done.add(4);
    if (formData.resources) done.add(5);
    return done;
  });

  let completionPct = $derived(Math.round((completedSteps.size / 6) * 100));

  function getMomentoActivities(field: string): string[] {
    const map: Record<string, string[]> = {
      exploration: formData.exploration_activities,
      structuring: formData.structuring_activities,
      practice: formData.practice_activities,
      transfer: formData.transfer_activities,
      assessment_moment: formData.assessment_activities,
    };
    return map[field] || [];
  }

  function getMomentoText(field: string): string {
    return (formData as Record<string, unknown>)[field] as string || "";
  }

  function toggleActivity(field: string, id: string) {
    const keyMap: Record<string, keyof FormData> = {
      exploration: "exploration_activities",
      structuring: "structuring_activities",
      practice: "practice_activities",
      transfer: "transfer_activities",
      assessment_moment: "assessment_activities",
    };
    const key = keyMap[field];
    const arr = formData[key] as string[];
    if (arr.includes(id)) {
      (formData as Record<string, unknown>)[key] = arr.filter(a => a !== id);
    } else {
      (formData as Record<string, unknown>)[key] = [...arr, id];
    }
    const act = ACTIVIDADES[field]?.find(a => a.id === id);
    if (act) {
      const textKey = field as keyof FormData;
      const current = formData[textKey] as string || "";
      if (!arr.includes(id)) {
        (formData as Record<string, unknown>)[textKey] = current
          ? current + "\n• " + act.label
          : "• " + act.label;
      }
    }
  }

  function toggleChip<T>(arr: T[], val: T): T[] {
    return arr.includes(val) ? arr.filter(v => v !== val) : [...arr, val];
  }

  function toggleMoment(i: number) {
    const s = new Set(openMoments);
    s.has(i) ? s.delete(i) : s.add(i);
    openMoments = s;
  }

  function setPreset(mins: number) {
    if (mins === 45) {
      formData.tiempo_exploracion = 8;
      formData.tiempo_estructuracion = 12;
      formData.tiempo_practica = 15;
      formData.tiempo_transferencia = 6;
      formData.tiempo_valoracion = 4;
    } else if (mins === 80) {
      formData.tiempo_exploracion = 10;
      formData.tiempo_estructuracion = 20;
      formData.tiempo_practica = 25;
      formData.tiempo_transferencia = 15;
      formData.tiempo_valoracion = 10;
    } else {
      formData.tiempo_exploracion = 15;
      formData.tiempo_estructuracion = 30;
      formData.tiempo_practica = 40;
      formData.tiempo_transferencia = 20;
      formData.tiempo_valoracion = 15;
    }
  }

  async function handleSave() {
    isSaving = true;
    await new Promise(r => setTimeout(r, 1400));
    isSaving = false;
    savedAnimation = true;
    setTimeout(() => savedAnimation = false, 2500);
  }

  function prevStep() { if (currentStep > 0) currentStep--; }
  function nextStep() { if (currentStep < 5) currentStep++; }

  onMount(() => {
    const check = () => { isMobile = window.innerWidth < 768; };
    check();
    window.addEventListener("resize", check);
    return () => window.removeEventListener("resize", check);
  });

  const TIPOS_PLAN = ["Clase única","Semanal","Quincenal","Mensual","Por período","Semestral","Anual"];
  const PERIODOS = ["Período 1","Período 2","Período 3","Período 4","Semestre 1","Semestre 2","Anual"];
  const GRADOS = ["PREESCOLAR","PRIMERO","SEGUNDO","TERCERO","CUARTO","QUINTO","SEXTO","SÉPTIMO","OCTAVO","NOVENO","DÉCIMO","ONCE"];
  const MATERIAS = ["Matemáticas","Lenguaje","Ciencias Naturales","Ciencias Sociales","Inglés","Ética","Ed. Artística","Ed. Física","Tecnología","Filosofía"];
  const EVAL_TIPOS = [
    { id: "diagnostica", label: "Diagnóstica", desc: "Identifica saberes previos" },
    { id: "formativa", label: "Formativa", desc: "Evalúa el proceso" },
    { id: "sumativa", label: "Sumativa", desc: "Evalúa resultados finales" },
  ];
  const MODALIDADES = ["Heteroevaluación","Coevaluación","Autoevaluación"];
  const INSTRUMENTOS = ["Rúbrica","Lista de chequeo","Prueba escrita","Proyecto","Exposición oral","Portafolio","Mapa conceptual","Ensayo"];
  const CRITERIOS = ["Comprensión conceptual","Aplicación","Análisis crítico","Creatividad","Colaboración","Comunicación","Investigación","Pensamiento lógico"];
  const RECURSOS_CATS = [
    { id: "tecnologicos", label: "Tecnológicos", color: "#6366f1", items: ["Computador/Tablet","Video beam","Internet/WiFi","Google Classroom","Kahoot/Quizizz","Canva/Genially"] },
    { id: "impresos", label: "Impresos", color: "#f59e0b", items: ["Guías de trabajo","Fichas de actividades","Cartillas","Rúbricas impresas","Fotocopias","Láminas/Afiches"] },
    { id: "audiovisuales", label: "Audiovisuales", color: "#10b981", items: ["Videos educativos","Presentaciones","Documentales","Podcasts/Audio","Imágenes/Fotografías"] },
    { id: "bibliograficos", label: "Bibliográficos", color: "#f43f5e", items: ["Libro de texto","Diccionario","Enciclopedia","Artículo de prensa","Revistas académicas"] },
  ];
  let selectedRecursosCat = $state("tecnologicos");
  const DBA_MOCK = [
    { id: "d1", code: "DBA 1", text: "Comprende que a partir de la variación de magnitudes es posible predecir comportamientos." },
    { id: "d2", code: "DBA 2", text: "Interpreta y produce representaciones de datos para responder preguntas estadísticas." },
    { id: "d3", code: "DBA 3", text: "Reconoce y describe figuras geométricas en el plano a partir de sus propiedades." },
    { id: "d4", code: "DBA 4", text: "Usa representaciones geométricas para resolver y formular problemas en las matemáticas." },
  ];
  const EBC_MOCK = [
    { id: "e1", code: "EBC 1", text: "Produzco textos escritos que evidencian el conocimiento que tengo de mis lectores." },
    { id: "e2", code: "EBC 2", text: "Comprendo textos literarios para propiciar el desarrollo de mi capacidad creativa y lúdica." },
  ];
</script>

<!-- ══════════════════════════════════════════════════════════ -->
<!-- MARKUP -->
<!-- ══════════════════════════════════════════════════════════ -->

<div class="planner-root">
  <!-- ── Ambient background ──────────────────────────────── -->
  <div class="ambient-bg" aria-hidden="true">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
    <div class="grid-overlay"></div>
  </div>

  <!-- ── App shell ────────────────────────────────────────── -->
  <div class="app-shell">

    <!-- ── Top bar ──────────────────────────────────────────── -->
    <header class="topbar glass-strong">
      <div class="topbar-left">
        <button class="icon-btn subtle" onclick={onBack} title="Volver">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"/>
          </svg>
        </button>
        <div class="brand-mark">
          <div class="brand-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
              <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
            </svg>
          </div>
          <div class="brand-text">
            <span class="brand-name">Planeador</span>
            <span class="brand-sub">I.E. Instituto Guática</span>
          </div>
        </div>
      </div>

      <!-- Progress pill (mobile) -->
      {#if isMobile}
        <div class="progress-pill">
          <div class="progress-pill-fill" style="width: {completionPct}%"></div>
          <span class="progress-pill-text">{completionPct}%</span>
        </div>
      {/if}

      <nav class="topbar-actions">
        {#if savedAnimation}
          <div class="saved-toast" transition:fly={{ y: -6, duration: 300 }}>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
            Guardado
          </div>
        {/if}
        <button class="icon-btn subtle" title="Vista previa PDF">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
          </svg>
        </button>
        <button class="btn-save-top {isSaving ? 'saving' : ''}" onclick={handleSave}>
          {#if isSaving}
            <svg class="spin" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 1 10 10"/></svg>
            Guardando…
          {:else}
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Guardar
          {/if}
        </button>
        {#if isMobile}
          <button class="icon-btn accent" onclick={() => sidebarOpen = !sidebarOpen} title="Resumen">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
          </button>
        {/if}
      </nav>
    </header>

    <!-- ── Step rail ────────────────────────────────────────── -->
    <div class="step-rail glass">
      {#each STEPS as step, i}
        <button
          class="step-item {currentStep === i ? 'active' : ''} {completedSteps.has(i) ? 'done' : ''}"
          onclick={() => currentStep = i}
          title={step.label}
        >
          <div class="step-bullet" style="--step-color: {step.color}">
            {#if completedSteps.has(i)}
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
            {:else}
              <span>{i + 1}</span>
            {/if}
          </div>
          <span class="step-label">{step.label}</span>
          {#if i < STEPS.length - 1}
            <div class="step-connector {completedSteps.has(i) ? 'done' : ''}"></div>
          {/if}
        </button>
      {/each}
    </div>

    <!-- ── Main content ─────────────────────────────────────── -->
    <div class="content-area">
      <div class="content-grid">

        <!-- ── FORM PANEL ──────────────────────────────────── -->
        <main class="form-panel">

          <!-- ══ STEP 0: DATOS BÁSICOS ══ -->
          {#if currentStep === 0}
            <div class="form-card" in:fly={{ y: 16, duration: 350, easing: cubicOut }}>
              <div class="card-header">
                <div class="card-header-icon" style="background: #6366f120; color: #818cf8">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div>
                  <h2 class="card-title">Información de la clase</h2>
                  <p class="card-desc">Datos de identificación y contexto institucional</p>
                </div>
              </div>
              <div class="card-body">
                <div class="field-grid-2">
                  <div class="field">
                    <label class="field-label">Docente <span class="req">*</span></label>
                    <input class="field-input" bind:value={formData.docente} placeholder="Nombre completo del docente" />
                  </div>
                  <div class="field">
                    <label class="field-label">Jornada</label>
                    <select class="field-input" bind:value={formData.jornada}>
                      {#each ["Mañana","Tarde","Nocturna","Única","Sabatina"] as j}
                        <option>{j}</option>
                      {/each}
                    </select>
                  </div>
                </div>
                <div class="field-grid-2">
                  <div class="field">
                    <label class="field-label">Grado <span class="req">*</span></label>
                    <select class="field-input" bind:value={formData.grado}>
                      <option value="">Seleccione grado</option>
                      {#each GRADOS as g}<option>{g}</option>{/each}
                    </select>
                  </div>
                  <div class="field">
                    <label class="field-label">Asignatura <span class="req">*</span></label>
                    <select class="field-input" bind:value={formData.subject}>
                      <option value="">Seleccione asignatura</option>
                      {#each MATERIAS as m}<option>{m}</option>{/each}
                    </select>
                  </div>
                </div>
                <div class="field-grid-2">
                  <div class="field">
                    <label class="field-label">Área (Ley 115)</label>
                    <select class="field-input">
                      <option>Matemáticas</option>
                      <option>Humanidades</option>
                      <option>Ciencias Naturales</option>
                      <option>Ciencias Sociales</option>
                      <option>Educación Artística</option>
                    </select>
                  </div>
                  <div class="field">
                    <label class="field-label">Período académico</label>
                    <select class="field-input" bind:value={formData.period}>
                      <option value="">Seleccione</option>
                      {#each PERIODOS as p}<option>{p}</option>{/each}
                    </select>
                  </div>
                </div>
              </div>
            </div>

          <!-- ══ STEP 1: TEMPORALIDAD ══ -->
          {:else if currentStep === 1}
            <div class="form-card" in:fly={{ y: 16, duration: 350, easing: cubicOut }}>
              <div class="card-header">
                <div class="card-header-icon" style="background: #0ea5e920; color: #38bdf8">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div>
                  <h2 class="card-title">Temporalidad</h2>
                  <p class="card-desc">Duración, tipo y período de la planeación</p>
                </div>
              </div>
              <div class="card-body">
                <div class="field">
                  <label class="field-label">Tipo de planeación <span class="req">*</span></label>
                  <div class="chip-grid-4">
                    {#each TIPOS_PLAN as t}
                      <button
                        class="type-chip {formData.planeacion_tipo === t ? 'selected' : ''}"
                        onclick={() => formData.planeacion_tipo = t}
                      >{t}</button>
                    {/each}
                  </div>
                </div>
                <div class="field-grid-2">
                  <div class="field">
                    <label class="field-label">Fecha inicio</label>
                    <input class="field-input" type="date" bind:value={formData.fecha_inicio} />
                  </div>
                  <div class="field">
                    <label class="field-label">Fecha fin</label>
                    <input class="field-input" type="date" bind:value={formData.fecha_fin} />
                  </div>
                </div>
                <div class="field">
                  <label class="field-label">Período académico <span class="req">*</span></label>
                  <div class="periodo-row">
                    {#each PERIODOS as p}
                      <button
                        class="periodo-pill {formData.periodo_academico === p ? 'selected' : ''}"
                        onclick={() => formData.periodo_academico = p}
                      >{p}</button>
                    {/each}
                  </div>
                </div>
              </div>
            </div>

          <!-- ══ STEP 2: REFERENTES ══ -->
          {:else if currentStep === 2}
            <div class="form-card" in:fly={{ y: 16, duration: 350, easing: cubicOut }}>
              <div class="card-header">
                <div class="card-header-icon" style="background: #8b5cf620; color: #a78bfa">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                </div>
                <div>
                  <h2 class="card-title">Referentes de calidad</h2>
                  <p class="card-desc">DBA, estándares y ajustes inclusivos</p>
                </div>
              </div>

              <!-- Tabs -->
              <div class="card-tabs">
                {#each [["dba","DBA"],["ebc","Estándares EBC"],["piar","PIAR"]] as [id, label]}
                  <button
                    class="card-tab {activeTab === id ? 'active' : ''}"
                    onclick={() => activeTab = id as "dba"|"ebc"|"piar"}
                  >{label}</button>
                {/each}
              </div>

              <div class="card-body">
                {#if activeTab === "dba"}
                  <div class="search-box">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" placeholder="Buscar en los DBA..." class="search-input" />
                  </div>
                  <div class="dba-list">
                    {#each DBA_MOCK as dba}
                      <label class="dba-row {formData.dba.includes(dba.id) ? 'checked' : ''}">
                        <input
                          type="checkbox"
                          class="dba-checkbox"
                          checked={formData.dba.includes(dba.id)}
                          onchange={() => formData.dba = toggleChip(formData.dba, dba.id)}
                        />
                        <div class="dba-content">
                          <span class="dba-code">{dba.code}</span>
                          <p class="dba-text">{dba.text}</p>
                        </div>
                      </label>
                    {/each}
                  </div>

                {:else if activeTab === "ebc"}
                  <div class="dba-list">
                    {#each EBC_MOCK as ebc}
                      <label class="dba-row ebc {formData.standard.includes(ebc.id) ? 'checked' : ''}">
                        <input
                          type="checkbox"
                          class="dba-checkbox"
                          checked={formData.standard.includes(ebc.id)}
                          onchange={() => formData.standard = toggleChip(formData.standard, ebc.id)}
                        />
                        <div class="dba-content">
                          <span class="dba-code ebc">{ebc.code}</span>
                          <p class="dba-text">{ebc.text}</p>
                        </div>
                      </label>
                    {/each}
                  </div>
                  <div class="field" style="margin-top:1rem">
                    <label class="field-label">Estándar manual (si no aparece en la lista)</label>
                    <textarea class="field-input" rows="3" bind:value={formData.dba_manual} placeholder="Escriba el estándar o DBA correspondiente..."></textarea>
                  </div>

                {:else}
                  <div class="piar-toggle-row">
                    <div>
                      <p class="piar-toggle-title">Ajustes razonables (PIAR)</p>
                      <p class="piar-toggle-desc">Decreto 1421 de 2017 — esta planeación requiere adaptaciones curriculares</p>
                    </div>
                    <button
                      class="toggle-btn {formData.has_piar ? 'on' : ''}"
                      onclick={() => formData.has_piar = !formData.has_piar}
                      role="switch"
                      aria-checked={formData.has_piar}
                    >
                      <span class="toggle-thumb"></span>
                    </button>
                  </div>
                  {#if formData.has_piar}
                    <div transition:fly={{ y: 8, duration: 250 }}>
                      <div class="field">
                        <label class="field-label">Tipo de condición</label>
                        <div class="chip-flow">
                          {#each ["Visual","Auditiva","Cognitiva","Motriz","Múltiple","Talento excepcional"] as t}
                            <button
                              class="flow-chip {formData.piar_tipo === t ? 'selected' : ''}"
                              onclick={() => formData.piar_tipo = t}
                            >{t}</button>
                          {/each}
                        </div>
                      </div>
                      <div class="field">
                        <label class="field-label">Descripción del ajuste</label>
                        <textarea class="field-input" rows="4" bind:value={formData.piar_description} placeholder="Describa los ajustes específicos para cada momento de la clase..."></textarea>
                      </div>
                    </div>
                  {/if}
                {/if}
              </div>
            </div>

          <!-- ══ STEP 3: SECUENCIA ══ -->
          {:else if currentStep === 3}
            <div class="form-card" in:fly={{ y: 16, duration: 350, easing: cubicOut }}>
              <div class="card-header">
                <div class="card-header-icon" style="background: #f59e0b20; color: #fbbf24">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                </div>
                <div class="card-header-main">
                  <h2 class="card-title">Secuencia didáctica</h2>
                  <p class="card-desc">5 momentos pedagógicos MEN</p>
                </div>
                <button class="ai-trigger-btn" title="Generar con IA">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                  IA
                </button>
              </div>

              <!-- Objetivos row -->
              <div class="card-body" style="padding-bottom: 0">
                <div class="field-grid-2">
                  <div class="field">
                    <label class="field-label">Objetivos de aprendizaje</label>
                    <textarea class="field-input" rows="2" bind:value={formData.learning_objectives} placeholder="El estudiante será capaz de..."></textarea>
                  </div>
                  <div class="field">
                    <label class="field-label">Indicadores de logro</label>
                    <textarea class="field-input" rows="2" bind:value={formData.indicadores_logro} placeholder="El estudiante demuestra..."></textarea>
                  </div>
                </div>
              </div>

              <!-- Time bar -->
              <div class="time-strip">
                <div class="time-bar-visual">
                  {#each [
                    { t: formData.tiempo_exploracion, c: "#818cf8", label: "Exploración" },
                    { t: formData.tiempo_estructuracion, c: "#34d399", label: "Estructuración" },
                    { t: formData.tiempo_practica, c: "#fbbf24", label: "Práctica" },
                    { t: formData.tiempo_transferencia, c: "#f87171", label: "Transf." },
                    { t: formData.tiempo_valoracion, c: "#a78bfa", label: "Valoración" },
                  ] as seg}
                    <div
                      class="time-seg-bar"
                      style="flex: {seg.t}; background: {seg.c}"
                      title="{seg.label}: {seg.t} min"
                    ></div>
                  {/each}
                </div>
                <div class="time-legend">
                  {#each [
                    { t: formData.tiempo_exploracion, c: "#818cf8", label: "Exploración" },
                    { t: formData.tiempo_estructuracion, c: "#34d399", label: "Estructuración" },
                    { t: formData.tiempo_practica, c: "#fbbf24", label: "Práctica" },
                    { t: formData.tiempo_transferencia, c: "#f87171", label: "Transferencia" },
                    { t: formData.tiempo_valoracion, c: "#a78bfa", label: "Valoración" },
                  ] as seg}
                    <span class="time-legend-item" style="color: {seg.c}">{seg.label} {seg.t}m</span>
                  {/each}
                  <span class="time-total-badge">Total: {tiempoTotal} min</span>
                </div>
                <div class="time-presets">
                  {#each [[45,"45m"],[80,"80m"],[120,"120m"]] as [mins, label]}
                    <button class="preset-btn" onclick={() => setPreset(mins as number)}>{label}</button>
                  {/each}
                </div>
              </div>

              <!-- Momentos accordion -->
              <div class="momentos-list">
                {#each MOMENTOS as m, i}
                  {@const acts = getMomentoActivities(m.field)}
                  {@const text = getMomentoText(m.field)}
                  {@const isOpen = openMoments.has(i)}
                  {@const isFilled = text.trim().length > 0}
                  <div class="momento-card {isOpen ? 'open' : ''}">
                    <button class="momento-header" onclick={() => toggleMoment(i)}>
                      <div class="momento-bullet" style="background: {MOMENTO_COLORS[i]}20; color: {MOMENTO_COLORS[i]}; border: 1px solid {MOMENTO_COLORS[i]}40">
                        {m.num}
                      </div>
                      <div class="momento-meta">
                        <span class="momento-name">{m.name}</span>
                        <span class="momento-desc">{m.desc}</span>
                      </div>
                      <div class="momento-right">
                        <span class="momento-time-pill" style="background: {MOMENTO_COLORS[i]}15; color: {MOMENTO_COLORS[i]}">
                          {(formData as Record<string, unknown>)[m.time] as number} min
                        </span>
                        {#if isFilled}
                          <div class="filled-dot" style="background: {MOMENTO_COLORS[i]}"></div>
                        {:else}
                          <div class="empty-dot"></div>
                        {/if}
                        <svg class="chevron {isOpen ? 'open' : ''}" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                      </div>
                    </button>
                    {#if isOpen}
                      <div class="momento-body" transition:fly={{ y: -6, duration: 200, easing: cubicOut }}>
                        <div class="time-mini-row">
                          <label class="mini-label">Tiempo (min)</label>
                          <input
                            type="number"
                            class="time-mini-input"
                            min="1" max="90"
                            bind:value={(formData as Record<string, unknown>)[m.time]}
                          />
                        </div>
                        <div class="actvs-label">Actividades sugeridas:</div>
                        <div class="actvs-row">
                          {#each ACTIVIDADES[m.field] || [] as act}
                            <button
                              class="actv-chip {acts.includes(act.id) ? 'selected' : ''}"
                              style="{acts.includes(act.id) ? `background:${MOMENTO_COLORS[i]}20;border-color:${MOMENTO_COLORS[i]}60;color:${MOMENTO_COLORS[i]}` : ''}"
                              onclick={() => toggleActivity(m.field, act.id)}
                            >{act.label}</button>
                          {/each}
                        </div>
                        <textarea
                          class="field-input momento-textarea"
                          rows="3"
                          bind:value={(formData as Record<string, unknown>)[m.field]}
                          placeholder="Haz clic en las actividades de arriba o escribe aquí..."
                        ></textarea>
                      </div>
                    {/if}
                  </div>
                {/each}
              </div>
            </div>

          <!-- ══ STEP 4: EVALUACIÓN ══ -->
          {:else if currentStep === 4}
            <div class="form-card" in:fly={{ y: 16, duration: 350, easing: cubicOut }}>
              <div class="card-header">
                <div class="card-header-icon" style="background: #10b98120; color: #34d399">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                </div>
                <div>
                  <h2 class="card-title">Evaluación de aprendizajes</h2>
                  <p class="card-desc">Decreto 1290 — criterios, instrumentos y ponderación</p>
                </div>
              </div>
              <div class="card-body">
                <div class="field">
                  <label class="field-label">Tipo de evaluación</label>
                  <div class="eval-tipo-row">
                    {#each EVAL_TIPOS as t}
                      <button
                        class="eval-tipo-card {formData.eval_type === t.id ? 'selected' : ''}"
                        onclick={() => formData.eval_type = t.id}
                      >
                        <span class="eval-tipo-name">{t.label}</span>
                        <span class="eval-tipo-desc">{t.desc}</span>
                      </button>
                    {/each}
                  </div>
                </div>
                <div class="field">
                  <label class="field-label">Modalidades</label>
                  <div class="chip-flow">
                    {#each MODALIDADES as m}
                      <button
                        class="flow-chip {formData.eval_modalidades.includes(m) ? 'selected' : ''}"
                        onclick={() => formData.eval_modalidades = toggleChip(formData.eval_modalidades, m)}
                      >{m}</button>
                    {/each}
                  </div>
                </div>
                <div class="field">
                  <label class="field-label">Instrumentos</label>
                  <div class="chip-flow">
                    {#each INSTRUMENTOS as inst}
                      <button
                        class="flow-chip {formData.eval_instrumentos.includes(inst) ? 'selected' : ''}"
                        onclick={() => formData.eval_instrumentos = toggleChip(formData.eval_instrumentos, inst)}
                      >{inst}</button>
                    {/each}
                  </div>
                </div>
                <div class="field">
                  <label class="field-label">Criterios de evaluación</label>
                  <div class="chip-flow">
                    {#each CRITERIOS as c}
                      <button
                        class="flow-chip {formData.eval_criterios.includes(c) ? 'selected' : ''}"
                        onclick={() => formData.eval_criterios = toggleChip(formData.eval_criterios, c)}
                      >{c}</button>
                    {/each}
                  </div>
                </div>
                <!-- Ponderación -->
                <div class="field">
                  <label class="field-label">Ponderación — total: {formData.eval_ponderacion_conceptos + formData.eval_ponderacion_procedimientos + formData.eval_ponderacion_actitudes}%</label>
                  <div class="ponder-block">
                    {#each [
                      { label: "Conceptos", key: "eval_ponderacion_conceptos", color: "#818cf8" },
                      { label: "Procedimientos", key: "eval_ponderacion_procedimientos", color: "#34d399" },
                      { label: "Actitudes", key: "eval_ponderacion_actitudes", color: "#fbbf24" },
                    ] as row}
                      <div class="ponder-row">
                        <span class="ponder-label">{row.label}</span>
                        <input
                          type="range" min="0" max="100" step="5"
                          bind:value={(formData as Record<string, unknown>)[row.key]}
                          class="ponder-range"
                          style="accent-color: {row.color}"
                        />
                        <span class="ponder-val" style="color: {row.color}">{(formData as Record<string, unknown>)[row.key]}%</span>
                      </div>
                    {/each}
                  </div>
                </div>
              </div>
            </div>

          <!-- ══ STEP 5: RECURSOS ══ -->
          {:else if currentStep === 5}
            <div class="form-card" in:fly={{ y: 16, duration: 350, easing: cubicOut }}>
              <div class="card-header">
                <div class="card-header-icon" style="background: #f43f5e20; color: #fb7185">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                </div>
                <div>
                  <h2 class="card-title">Recursos didácticos</h2>
                  <p class="card-desc">Materiales, herramientas y medios</p>
                </div>
              </div>
              <div class="card-body">
                <div class="field">
                  <label class="field-label">Categoría</label>
                  <div class="res-cats">
                    {#each RECURSOS_CATS as cat}
                      <button
                        class="res-cat-btn {selectedRecursosCat === cat.id ? 'selected' : ''}"
                        style="{selectedRecursosCat === cat.id ? `border-color:${cat.color}; color:${cat.color}; background:${cat.color}15` : ''}"
                        onclick={() => selectedRecursosCat = cat.id}
                      >
                        <span class="res-cat-dot" style="background:{cat.color}"></span>
                        {cat.label}
                      </button>
                    {/each}
                  </div>
                </div>
                <div class="field">
                  <label class="field-label">Recursos disponibles</label>
                  <div class="chip-flow">
                    {#each RECURSOS_CATS.find(c => c.id === selectedRecursosCat)?.items || [] as item}
                      <button
                        class="flow-chip {formData.recursos_ids.includes(item) ? 'selected' : ''}"
                        onclick={() => formData.recursos_ids = toggleChip(formData.recursos_ids, item)}
                      >{item}</button>
                    {/each}
                  </div>
                </div>
                <div class="field">
                  <label class="field-label">Recursos adicionales / personalizados</label>
                  <textarea class="field-input" rows="3" bind:value={formData.resources} placeholder="Describe otros recursos que utilizarás..."></textarea>
                </div>
                <!-- Summary -->
                {#if formData.recursos_ids.length > 0}
                  <div class="resources-summary">
                    <p class="res-summary-label">Recursos seleccionados ({formData.recursos_ids.length})</p>
                    <div class="chip-flow">
                      {#each formData.recursos_ids as r}
                        <span class="res-summary-chip">
                          {r}
                          <button onclick={() => formData.recursos_ids = formData.recursos_ids.filter(x => x !== r)}>×</button>
                        </span>
                      {/each}
                    </div>
                  </div>
                {/if}
              </div>
            </div>
          {/if}

          <!-- ── Bottom nav ─────────────────────────────────── -->
          <div class="bottom-nav glass">
            <button class="nav-btn-ghost" onclick={prevStep} disabled={currentStep === 0}>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
              Anterior
            </button>
            <div class="nav-pips">
              {#each STEPS as _, i}
                <button
                  class="nav-pip {i === currentStep ? 'active' : ''} {completedSteps.has(i) ? 'done' : ''}"
                  onclick={() => currentStep = i}
                ></button>
              {/each}
            </div>
            {#if currentStep < 5}
              <button class="nav-btn-primary" onclick={nextStep}>
                Siguiente
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 6 15 12 9 18"/></svg>
              </button>
            {:else}
              <button class="nav-btn-primary success" onclick={handleSave}>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Guardar planeación
              </button>
            {/if}
          </div>
        </main>

        <!-- ── SIDEBAR ─────────────────────────────────────── -->
        <aside class="sidebar {isMobile && !sidebarOpen ? 'hidden' : ''}">

          <!-- Completeness card -->
          <div class="side-card glass">
            <div class="completion-header">
              <span class="completion-pct">{completionPct}%</span>
              <span class="completion-label">completado</span>
            </div>
            <div class="completion-ring-wrap">
              <svg class="completion-ring" width="72" height="72" viewBox="0 0 72 72">
                <circle cx="36" cy="36" r="28" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="5"/>
                <circle
                  cx="36" cy="36" r="28"
                  fill="none"
                  stroke="url(#ringGrad)"
                  stroke-width="5"
                  stroke-linecap="round"
                  stroke-dasharray="{2 * Math.PI * 28}"
                  stroke-dashoffset="{2 * Math.PI * 28 * (1 - completionPct / 100)}"
                  transform="rotate(-90 36 36)"
                  style="transition: stroke-dashoffset 0.6s cubic-bezier(.4,0,.2,1)"
                />
                <defs>
                  <linearGradient id="ringGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#818cf8"/>
                    <stop offset="100%" stop-color="#34d399"/>
                  </linearGradient>
                </defs>
              </svg>
            </div>
            <div class="completion-checklist">
              {#each STEPS as step, i}
                <div class="check-row {completedSteps.has(i) ? 'done' : ''}">
                  <div class="check-icon">
                    {#if completedSteps.has(i)}
                      <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                    {:else}
                      <div class="check-empty"></div>
                    {/if}
                  </div>
                  <span>{step.label}</span>
                </div>
              {/each}
            </div>
          </div>

          <!-- Recent -->
          <div class="side-card glass">
            <div class="side-section-title">Planeaciones recientes</div>
            {#each [
              { abbr: "MAT", label: "Matemáticas · 8°", meta: "P1 · hace 2 días", color: "#818cf8" },
              { abbr: "LEN", label: "Lenguaje · 7°", meta: "P2 · hace 5 días", color: "#34d399" },
              { abbr: "CN", label: "Ciencias · 9°", meta: "P2 · hace 1 sem.", color: "#fbbf24" },
            ] as item}
              <div class="recent-item">
                <div class="recent-thumb" style="background:{item.color}20;color:{item.color}">{item.abbr}</div>
                <div class="recent-info">
                  <span class="recent-name">{item.label}</span>
                  <span class="recent-meta">{item.meta}</span>
                </div>
                <button class="recent-load">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
              </div>
            {/each}
            <div class="side-actions">
              <button class="side-action-btn">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                Duplicar
              </button>
              <button class="side-action-btn">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Importar
              </button>
              <button class="side-action-btn">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Exportar
              </button>
            </div>
          </div>

          <!-- Mobile close -->
          {#if isMobile && sidebarOpen}
            <button class="close-sidebar" onclick={() => sidebarOpen = false} transition:fade={{ duration: 200 }}>
              Cerrar panel
            </button>
          {/if}
        </aside>

      </div>
    </div>
  </div>

  <!-- Mobile sidebar overlay -->
  {#if isMobile && sidebarOpen}
    <button class="sidebar-overlay" onclick={() => sidebarOpen = false} transition:fade={{ duration: 200 }} aria-label="Cerrar sidebar"></button>
  {/if}
</div>

<!-- ══════════════════════════════════════════════════════════ -->
<!-- STYLES -->
<!-- ══════════════════════════════════════════════════════════ -->
<style>
  /* ── Reset / Root ─────────────────────────────────────── */
  :global(*, *::before, *::after) { box-sizing: border-box; }

  .planner-root {
    --c-bg: #0a0a0f;
    --c-surface: #12121a;
    --c-surface-2: #1a1a26;
    --c-surface-3: #222236;
    --c-border: rgba(255,255,255,0.07);
    --c-border-2: rgba(255,255,255,0.12);
    --c-text: #e8e8f0;
    --c-text-2: #9090a8;
    --c-text-3: #5a5a7a;
    --c-accent: #818cf8;
    --c-accent-2: #34d399;
    --c-gold: #fbbf24;
    --c-danger: #f87171;
    --radius: 12px;
    --radius-sm: 8px;
    --radius-pill: 100px;
    min-height: 100vh;
    background: var(--c-bg);
    color: var(--c-text);
    font-family: 'DM Sans', 'Instrument Sans', system-ui, sans-serif;
    position: relative;
    overflow-x: hidden;
  }

  /* ── Ambient background ───────────────────────────────── */
  .ambient-bg {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 0;
    overflow: hidden;
  }
  .orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.25;
    animation: orbFloat 12s ease-in-out infinite;
  }
  .orb-1 {
    width: 500px; height: 500px;
    background: radial-gradient(circle, #4f46e5, transparent);
    top: -200px; left: -100px;
    animation-delay: 0s;
  }
  .orb-2 {
    width: 400px; height: 400px;
    background: radial-gradient(circle, #0891b2, transparent);
    top: 40%; right: -150px;
    animation-delay: -4s;
  }
  .orb-3 {
    width: 350px; height: 350px;
    background: radial-gradient(circle, #7c3aed, transparent);
    bottom: -100px; left: 30%;
    animation-delay: -8s;
  }
  @keyframes orbFloat {
    0%, 100% { transform: translateY(0) scale(1); }
    50% { transform: translateY(-30px) scale(1.05); }
  }
  .grid-overlay {
    position: absolute;
    inset: 0;
    background-image:
      linear-gradient(rgba(255,255,255,0.015) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,0.015) 1px, transparent 1px);
    background-size: 40px 40px;
  }

  /* ── Glass ────────────────────────────────────────────── */
  .glass {
    background: rgba(18, 18, 28, 0.6);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid var(--c-border);
  }
  .glass-strong {
    background: rgba(18, 18, 28, 0.85);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 1px solid var(--c-border);
  }

  /* ── App shell ────────────────────────────────────────── */
  .app-shell {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }

  /* ── Topbar ───────────────────────────────────────────── */
  .topbar {
    position: sticky;
    top: 0;
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 1.5rem;
    height: 56px;
    border-top: none;
    border-left: none;
    border-right: none;
    border-radius: 0;
  }
  .topbar-left {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .brand-mark {
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .brand-icon {
    width: 32px;
    height: 32px;
    border-radius: var(--radius-sm);
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .brand-icon svg { stroke: #fff; }
  .brand-text {
    display: flex;
    flex-direction: column;
    gap: 1px;
  }
  .brand-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--c-text);
    letter-spacing: -0.01em;
  }
  .brand-sub {
    font-size: 11px;
    color: var(--c-text-3);
  }
  .topbar-actions {
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .progress-pill {
    flex: 1;
    max-width: 120px;
    height: 20px;
    border-radius: 100px;
    background: var(--c-surface-3);
    position: relative;
    overflow: hidden;
    margin: 0 12px;
  }
  .progress-pill-fill {
    height: 100%;
    background: linear-gradient(90deg, #4f46e5, #818cf8);
    border-radius: 100px;
    transition: width 0.5s cubic-bezier(.4,0,.2,1);
  }
  .progress-pill-text {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 600;
    color: #fff;
  }
  .saved-toast {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 500;
    color: var(--c-accent-2);
    background: rgba(52,211,153,0.1);
    border: 1px solid rgba(52,211,153,0.25);
    border-radius: var(--radius-pill);
    padding: 4px 10px;
  }
  .icon-btn {
    width: 34px;
    height: 34px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--c-border);
    background: transparent;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s;
    color: var(--c-text-2);
  }
  .icon-btn.subtle { border-color: transparent; }
  .icon-btn:hover {
    background: var(--c-surface-3);
    border-color: var(--c-border-2);
    color: var(--c-text);
  }
  .icon-btn.accent {
    background: rgba(99,102,241,0.15);
    border-color: rgba(99,102,241,0.3);
    color: var(--c-accent);
  }
  .btn-save-top {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 0 14px;
    height: 34px;
    border-radius: var(--radius-sm);
    border: 1px solid rgba(99,102,241,0.4);
    background: rgba(99,102,241,0.15);
    color: var(--c-accent);
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
  }
  .btn-save-top:hover {
    background: rgba(99,102,241,0.25);
    border-color: rgba(99,102,241,0.6);
  }
  .btn-save-top.saving {
    opacity: 0.7;
    cursor: default;
  }
  .spin {
    animation: spin 1s linear infinite;
  }
  @keyframes spin {
    to { transform: rotate(360deg); }
  }

  /* ── Step rail ────────────────────────────────────────── */
  .step-rail {
    display: flex;
    align-items: center;
    padding: 0 1.5rem;
    height: 52px;
    border-top: none;
    border-left: none;
    border-right: none;
    border-radius: 0;
    border-top: 1px solid var(--c-border);
    overflow-x: auto;
    scrollbar-width: none;
    gap: 0;
  }
  .step-rail::-webkit-scrollbar { display: none; }
  .step-item {
    display: flex;
    align-items: center;
    gap: 0;
    cursor: pointer;
    background: none;
    border: none;
    padding: 0;
    flex-shrink: 0;
  }
  .step-bullet {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    border: 1.5px solid var(--c-border-2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 600;
    color: var(--c-text-3);
    transition: all 0.25s;
    background: var(--c-surface);
    flex-shrink: 0;
  }
  .step-item.active .step-bullet {
    background: var(--step-color, #6366f1);
    border-color: var(--step-color, #6366f1);
    color: #fff;
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--step-color, #6366f1) 25%, transparent);
  }
  .step-item.done .step-bullet {
    background: rgba(52,211,153,0.15);
    border-color: rgba(52,211,153,0.4);
    color: var(--c-accent-2);
  }
  .step-label {
    font-size: 11px;
    color: var(--c-text-3);
    margin-left: 6px;
    font-weight: 500;
    transition: color 0.2s;
    white-space: nowrap;
  }
  .step-item.active .step-label { color: var(--c-text); }
  .step-item.done .step-label { color: var(--c-text-2); }
  .step-connector {
    width: 28px;
    height: 1px;
    background: var(--c-border-2);
    margin: 0 6px;
    transition: background 0.3s;
    flex-shrink: 0;
  }
  .step-connector.done { background: rgba(52,211,153,0.35); }

  /* ── Content ──────────────────────────────────────────── */
  .content-area {
    flex: 1;
    padding: 1.5rem;
    max-width: 1100px;
    margin: 0 auto;
    width: 100%;
  }
  .content-grid {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 1.25rem;
    align-items: start;
  }
  @media (max-width: 900px) {
    .content-grid { grid-template-columns: 1fr; }
  }

  /* ── Form card ────────────────────────────────────────── */
  .form-panel { display: flex; flex-direction: column; gap: 1rem; }
  .form-card {
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--radius);
    overflow: hidden;
  }
  .card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    border-bottom: 1px solid var(--c-border);
    background: var(--c-surface-2);
  }
  .card-header-main { flex: 1; }
  .card-header-icon {
    width: 36px;
    height: 36px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .card-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--c-text);
    letter-spacing: -0.01em;
    margin: 0;
  }
  .card-desc {
    font-size: 11px;
    color: var(--c-text-3);
    margin: 2px 0 0;
  }
  .card-body { padding: 20px; }

  /* ── Tabs ─────────────────────────────────────────────── */
  .card-tabs {
    display: flex;
    border-bottom: 1px solid var(--c-border);
    padding: 0 20px;
    background: var(--c-surface-2);
  }
  .card-tab {
    padding: 10px 16px;
    font-size: 12px;
    font-weight: 500;
    color: var(--c-text-3);
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    transition: all 0.15s;
    font-family: inherit;
  }
  .card-tab:hover { color: var(--c-text-2); }
  .card-tab.active {
    color: var(--c-accent);
    border-bottom-color: var(--c-accent);
  }

  /* ── Fields ───────────────────────────────────────────── */
  .field { margin-bottom: 16px; }
  .field:last-child { margin-bottom: 0; }
  .field-label {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 500;
    color: var(--c-text-2);
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }
  .req { color: #f87171; font-size: 10px; }
  .field-input {
    width: 100%;
    background: var(--c-surface-3);
    border: 1px solid var(--c-border);
    border-radius: var(--radius-sm);
    padding: 9px 12px;
    font-size: 13px;
    color: var(--c-text);
    font-family: inherit;
    transition: border-color 0.15s, box-shadow 0.15s;
    resize: vertical;
    line-height: 1.6;
  }
  .field-input:focus {
    outline: none;
    border-color: rgba(99,102,241,0.5);
    box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
  }
  .field-input::placeholder { color: var(--c-text-3); }
  .field-input option {
    background: var(--c-surface-2);
    color: var(--c-text);
  }
  .field-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-bottom: 16px;
  }
  @media (max-width: 520px) {
    .field-grid-2 { grid-template-columns: 1fr; }
  }

  /* ── Chips ────────────────────────────────────────────── */
  .chip-grid-4 {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 6px;
    margin-top: 4px;
  }
  @media (max-width: 520px) {
    .chip-grid-4 { grid-template-columns: repeat(2, 1fr); }
  }
  .type-chip {
    padding: 8px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--c-border);
    background: var(--c-surface-3);
    font-size: 11px;
    font-weight: 500;
    color: var(--c-text-2);
    cursor: pointer;
    transition: all 0.15s;
    text-align: center;
    font-family: inherit;
  }
  .type-chip:hover { border-color: var(--c-border-2); color: var(--c-text); }
  .type-chip.selected {
    background: rgba(99,102,241,0.15);
    border-color: rgba(99,102,241,0.4);
    color: var(--c-accent);
  }
  .chip-flow {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 4px;
  }
  .flow-chip {
    padding: 5px 12px;
    border-radius: var(--radius-pill);
    border: 1px solid var(--c-border);
    background: var(--c-surface-3);
    font-size: 11px;
    font-weight: 500;
    color: var(--c-text-2);
    cursor: pointer;
    transition: all 0.15s;
    font-family: inherit;
    white-space: nowrap;
  }
  .flow-chip:hover { border-color: var(--c-border-2); color: var(--c-text); }
  .flow-chip.selected {
    background: rgba(99,102,241,0.15);
    border-color: rgba(99,102,241,0.4);
    color: var(--c-accent);
  }
  .periodo-row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 4px;
  }
  .periodo-pill {
    padding: 6px 14px;
    border-radius: var(--radius-pill);
    border: 1px solid var(--c-border);
    background: var(--c-surface-3);
    font-size: 11px;
    font-weight: 600;
    color: var(--c-text-2);
    cursor: pointer;
    transition: all 0.15s;
    font-family: inherit;
  }
  .periodo-pill:hover { border-color: var(--c-border-2); color: var(--c-text); }
  .periodo-pill.selected {
    background: rgba(99,102,241,0.15);
    border-color: rgba(99,102,241,0.5);
    color: var(--c-accent);
  }

  /* ── DBA list ─────────────────────────────────────────── */
  .search-box {
    display: flex;
    align-items: center;
    gap: 8px;
    background: var(--c-surface-3);
    border: 1px solid var(--c-border);
    border-radius: var(--radius-sm);
    padding: 8px 12px;
    margin-bottom: 12px;
  }
  .search-box svg { flex-shrink: 0; color: var(--c-text-3); }
  .search-input {
    flex: 1;
    background: none;
    border: none;
    outline: none;
    font-size: 13px;
    color: var(--c-text);
    font-family: inherit;
  }
  .search-input::placeholder { color: var(--c-text-3); }
  .dba-list { display: flex; flex-direction: column; gap: 2px; }
  .dba-row {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 10px;
    border-radius: var(--radius-sm);
    cursor: pointer;
    transition: background 0.15s;
    border: 1px solid transparent;
  }
  .dba-row:hover { background: var(--c-surface-2); }
  .dba-row.checked {
    background: rgba(99,102,241,0.06);
    border-color: rgba(99,102,241,0.15);
  }
  .dba-checkbox {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
    margin-top: 2px;
    accent-color: var(--c-accent);
    cursor: pointer;
  }
  .dba-content { flex: 1; min-width: 0; }
  .dba-code {
    display: inline-block;
    font-size: 10px;
    font-weight: 700;
    color: var(--c-accent);
    background: rgba(99,102,241,0.12);
    border: 1px solid rgba(99,102,241,0.2);
    border-radius: 4px;
    padding: 1px 6px;
    margin-bottom: 4px;
    letter-spacing: 0.02em;
  }
  .dba-code.ebc { color: var(--c-accent-2); background: rgba(52,211,153,0.1); border-color: rgba(52,211,153,0.2); }
  .dba-text {
    font-size: 12px;
    color: var(--c-text-2);
    line-height: 1.6;
  }
  .dba-row.ebc.checked { background: rgba(52,211,153,0.06); border-color: rgba(52,211,153,0.15); }

  /* ── PIAR ─────────────────────────────────────────────── */
  .piar-toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 12px;
    background: var(--c-surface-2);
    border-radius: var(--radius-sm);
    border: 1px solid var(--c-border);
    margin-bottom: 16px;
  }
  .piar-toggle-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--c-text);
    margin: 0 0 2px;
  }
  .piar-toggle-desc {
    font-size: 11px;
    color: var(--c-text-3);
    margin: 0;
  }
  .toggle-btn {
    width: 40px;
    height: 22px;
    border-radius: var(--radius-pill);
    background: var(--c-surface-3);
    border: 1px solid var(--c-border-2);
    cursor: pointer;
    position: relative;
    transition: all 0.25s;
    flex-shrink: 0;
    padding: 0;
  }
  .toggle-btn.on {
    background: rgba(99,102,241,0.3);
    border-color: rgba(99,102,241,0.5);
  }
  .toggle-thumb {
    position: absolute;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: var(--c-text-3);
    top: 2px;
    left: 2px;
    transition: all 0.25s cubic-bezier(.4,0,.2,1);
  }
  .toggle-btn.on .toggle-thumb {
    left: 20px;
    background: var(--c-accent);
  }

  /* ── Momentos ─────────────────────────────────────────── */
  .time-strip {
    border-top: 1px solid var(--c-border);
    border-bottom: 1px solid var(--c-border);
    padding: 12px 20px;
    background: var(--c-surface-2);
  }
  .time-bar-visual {
    height: 8px;
    border-radius: 100px;
    overflow: hidden;
    display: flex;
    gap: 2px;
    margin-bottom: 6px;
  }
  .time-seg-bar {
    border-radius: 2px;
    transition: flex 0.4s cubic-bezier(.4,0,.2,1);
    min-width: 4px;
  }
  .time-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 8px;
  }
  .time-legend-item { font-size: 10px; font-weight: 500; }
  .time-total-badge {
    font-size: 10px;
    font-weight: 700;
    color: var(--c-text-2);
    background: var(--c-surface-3);
    border: 1px solid var(--c-border);
    border-radius: var(--radius-pill);
    padding: 1px 8px;
    margin-left: auto;
  }
  .time-presets {
    display: flex;
    gap: 6px;
  }
  .preset-btn {
    padding: 3px 10px;
    border-radius: var(--radius-pill);
    border: 1px solid var(--c-border-2);
    background: var(--c-surface-3);
    font-size: 10px;
    font-weight: 600;
    color: var(--c-text-2);
    cursor: pointer;
    transition: all 0.15s;
    font-family: inherit;
  }
  .preset-btn:hover {
    border-color: var(--c-accent);
    color: var(--c-accent);
    background: rgba(99,102,241,0.1);
  }
  .momentos-list {
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 6px;
  }
  .momento-card {
    border-radius: var(--radius-sm);
    border: 1px solid var(--c-border);
    background: var(--c-surface);
    overflow: hidden;
    transition: border-color 0.2s;
  }
  .momento-card.open { border-color: var(--c-border-2); }
  .momento-header {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    background: none;
    border: none;
    cursor: pointer;
    text-align: left;
    font-family: inherit;
    transition: background 0.15s;
  }
  .momento-header:hover { background: var(--c-surface-2); }
  .momento-bullet {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    flex-shrink: 0;
  }
  .momento-meta { flex: 1; min-width: 0; }
  .momento-name {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--c-text);
  }
  .momento-desc {
    display: block;
    font-size: 10px;
    color: var(--c-text-3);
    margin-top: 1px;
  }
  .momento-right {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
  }
  .momento-time-pill {
    font-size: 10px;
    font-weight: 700;
    border-radius: var(--radius-pill);
    padding: 2px 8px;
    border: 1px solid transparent;
  }
  .filled-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    opacity: 0.8;
  }
  .empty-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    border: 1.5px solid var(--c-border-2);
  }
  .chevron { color: var(--c-text-3); transition: transform 0.25s cubic-bezier(.4,0,.2,1); }
  .chevron.open { transform: rotate(180deg); }
  .momento-body {
    padding: 12px 14px;
    border-top: 1px solid var(--c-border);
    background: var(--c-surface-2);
  }
  .time-mini-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
  }
  .mini-label { font-size: 11px; color: var(--c-text-3); flex: 1; }
  .time-mini-input {
    width: 60px;
    background: var(--c-surface-3);
    border: 1px solid var(--c-border);
    border-radius: 6px;
    padding: 4px 8px;
    font-size: 12px;
    color: var(--c-text);
    text-align: center;
    font-family: inherit;
  }
  .time-mini-input:focus { outline: none; border-color: rgba(99,102,241,0.5); }
  .actvs-label {
    font-size: 10px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--c-text-3);
    margin-bottom: 6px;
  }
  .actvs-row { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 10px; }
  .actv-chip {
    padding: 4px 10px;
    border-radius: var(--radius-pill);
    border: 1px solid var(--c-border);
    background: var(--c-surface);
    font-size: 11px;
    color: var(--c-text-2);
    cursor: pointer;
    transition: all 0.15s;
    font-family: inherit;
    white-space: nowrap;
  }
  .actv-chip:hover { border-color: var(--c-border-2); color: var(--c-text); }
  .momento-textarea { font-size: 12px; min-height: 72px; }
  .ai-trigger-btn {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 5px 10px;
    border-radius: var(--radius-pill);
    border: 1px solid rgba(99,102,241,0.3);
    background: rgba(99,102,241,0.1);
    color: var(--c-accent);
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
    font-family: inherit;
    white-space: nowrap;
    margin-left: auto;
  }
  .ai-trigger-btn:hover { background: rgba(99,102,241,0.2); border-color: rgba(99,102,241,0.5); }

  /* ── Evaluación ───────────────────────────────────────── */
  .eval-tipo-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    margin-top: 4px;
  }
  @media (max-width: 520px) {
    .eval-tipo-row { grid-template-columns: 1fr; }
  }
  .eval-tipo-card {
    padding: 12px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--c-border);
    background: var(--c-surface-3);
    cursor: pointer;
    text-align: left;
    transition: all 0.15s;
    font-family: inherit;
  }
  .eval-tipo-card:hover { border-color: var(--c-border-2); }
  .eval-tipo-card.selected {
    border-color: rgba(99,102,241,0.4);
    background: rgba(99,102,241,0.1);
  }
  .eval-tipo-name {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--c-text);
    margin-bottom: 3px;
  }
  .eval-tipo-desc {
    display: block;
    font-size: 11px;
    color: var(--c-text-3);
  }
  .ponder-block {
    background: var(--c-surface-2);
    border: 1px solid var(--c-border);
    border-radius: var(--radius-sm);
    padding: 12px;
    margin-top: 4px;
    display: flex;
    flex-direction: column;
    gap: 10px;
  }
  .ponder-row { display: flex; align-items: center; gap: 10px; }
  .ponder-label { font-size: 11px; color: var(--c-text-2); width: 110px; flex-shrink: 0; }
  .ponder-range { flex: 1; height: 4px; cursor: pointer; }
  .ponder-val { font-size: 12px; font-weight: 700; width: 32px; text-align: right; flex-shrink: 0; }

  /* ── Recursos ─────────────────────────────────────────── */
  .res-cats { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 4px; }
  .res-cat-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: var(--radius-pill);
    border: 1px solid var(--c-border);
    background: var(--c-surface-3);
    font-size: 11px;
    font-weight: 500;
    color: var(--c-text-2);
    cursor: pointer;
    transition: all 0.15s;
    font-family: inherit;
  }
  .res-cat-btn:hover { border-color: var(--c-border-2); color: var(--c-text); }
  .res-cat-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
  .resources-summary {
    padding: 12px;
    background: var(--c-surface-2);
    border: 1px solid var(--c-border);
    border-radius: var(--radius-sm);
    margin-top: 8px;
  }
  .res-summary-label {
    font-size: 10px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--c-text-3);
    margin: 0 0 8px;
  }
  .res-summary-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: var(--radius-pill);
    background: rgba(52,211,153,0.1);
    border: 1px solid rgba(52,211,153,0.2);
    font-size: 11px;
    color: var(--c-accent-2);
    margin: 2px;
  }
  .res-summary-chip button {
    background: none;
    border: none;
    color: var(--c-text-3);
    cursor: pointer;
    font-size: 13px;
    line-height: 1;
    padding: 0;
  }

  /* ── Bottom nav ───────────────────────────────────────── */
  .bottom-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-radius: var(--radius);
  }
  .nav-pips { display: flex; gap: 5px; }
  .nav-pip {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    border: none;
    background: var(--c-surface-3);
    cursor: pointer;
    transition: all 0.2s;
    padding: 0;
  }
  .nav-pip.active {
    width: 20px;
    border-radius: var(--radius-pill);
    background: var(--c-accent);
  }
  .nav-pip.done { background: var(--c-accent-2); opacity: 0.5; }
  .nav-btn-ghost {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 0 14px;
    height: 36px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--c-border);
    background: transparent;
    font-size: 12px;
    font-weight: 500;
    color: var(--c-text-2);
    cursor: pointer;
    transition: all 0.15s;
    font-family: inherit;
  }
  .nav-btn-ghost:hover:not(:disabled) { background: var(--c-surface-2); border-color: var(--c-border-2); color: var(--c-text); }
  .nav-btn-ghost:disabled { opacity: 0.3; cursor: default; }
  .nav-btn-primary {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 0 18px;
    height: 36px;
    border-radius: var(--radius-sm);
    border: 1px solid rgba(99,102,241,0.4);
    background: linear-gradient(135deg, #4f46e5, #6366f1);
    font-size: 12px;
    font-weight: 600;
    color: #fff;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 4px 14px rgba(99,102,241,0.3);
    font-family: inherit;
  }
  .nav-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,0.4); }
  .nav-btn-primary:active { transform: scale(0.98); }
  .nav-btn-primary.success {
    background: linear-gradient(135deg, #059669, #10b981);
    border-color: rgba(16,185,129,0.4);
    box-shadow: 0 4px 14px rgba(16,185,129,0.3);
  }

  /* ── Sidebar ──────────────────────────────────────────── */
  .sidebar {
    position: sticky;
    top: 108px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    transition: transform 0.3s cubic-bezier(.4,0,.2,1), opacity 0.3s;
  }
  .sidebar.hidden {
    position: fixed;
    top: 0;
    right: -320px;
    bottom: 0;
    width: 300px;
    overflow-y: auto;
    background: var(--c-surface);
    z-index: 200;
    padding: 1rem;
  }
  @media (max-width: 900px) {
    .sidebar {
      position: fixed;
      top: 0;
      right: -320px;
      bottom: 0;
      width: 300px;
      overflow-y: auto;
      background: var(--c-surface);
      border-left: 1px solid var(--c-border);
      z-index: 200;
      padding: 1rem;
      transition: right 0.3s cubic-bezier(.4,0,.2,1);
    }
    .sidebar.hidden { right: -320px; }
    .sidebar:not(.hidden) { right: 0; }
  }
  .sidebar-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
    z-index: 199;
    border: none;
    cursor: pointer;
  }
  .side-card {
    border-radius: var(--radius);
    overflow: hidden;
    padding: 16px;
  }
  .completion-header {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    margin-bottom: 12px;
  }
  .completion-pct {
    font-size: 28px;
    font-weight: 700;
    color: var(--c-text);
    letter-spacing: -0.03em;
  }
  .completion-label {
    font-size: 11px;
    color: var(--c-text-3);
  }
  .completion-ring-wrap {
    display: flex;
    justify-content: center;
    margin: -4px 0 12px;
  }
  .completion-ring {
    overflow: visible;
  }
  .completion-checklist {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }
  .check-row {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    color: var(--c-text-3);
    transition: color 0.2s;
  }
  .check-row.done { color: var(--c-text-2); }
  .check-icon {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 1.5px solid var(--c-border-2);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.2s;
  }
  .check-row.done .check-icon {
    background: rgba(52,211,153,0.15);
    border-color: rgba(52,211,153,0.4);
    color: var(--c-accent-2);
  }
  .check-empty {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--c-border-2);
  }
  .side-section-title {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--c-text-3);
    margin-bottom: 12px;
  }
  .recent-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid var(--c-border);
    cursor: pointer;
    transition: opacity 0.15s;
  }
  .recent-item:last-of-type { border-bottom: none; }
  .recent-item:hover { opacity: 0.8; }
  .recent-thumb {
    width: 36px;
    height: 36px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 700;
    flex-shrink: 0;
  }
  .recent-info { flex: 1; min-width: 0; }
  .recent-name {
    display: block;
    font-size: 12px;
    font-weight: 500;
    color: var(--c-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .recent-meta { font-size: 10px; color: var(--c-text-3); }
  .recent-load {
    width: 24px;
    height: 24px;
    border-radius: 6px;
    border: 1px solid var(--c-border);
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: var(--c-text-3);
    transition: all 0.15s;
  }
  .recent-load:hover { background: var(--c-surface-2); color: var(--c-text); }
  .side-actions {
    display: flex;
    gap: 6px;
    margin-top: 12px;
    flex-wrap: wrap;
  }
  .side-action-btn {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 5px 10px;
    border-radius: var(--radius-pill);
    border: 1px solid var(--c-border);
    background: var(--c-surface-3);
    font-size: 10px;
    font-weight: 500;
    color: var(--c-text-2);
    cursor: pointer;
    transition: all 0.15s;
    font-family: inherit;
  }
  .side-action-btn:hover { border-color: var(--c-border-2); color: var(--c-text); }
  .close-sidebar {
    width: 100%;
    padding: 10px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--c-border);
    background: var(--c-surface-3);
    font-size: 12px;
    color: var(--c-text-2);
    cursor: pointer;
    margin-top: 8px;
    font-family: inherit;
  }

  /* ── Responsive ───────────────────────────────────────── */
  @media (max-width: 768px) {
    .content-area { padding: 1rem 0.75rem; }
    .card-body { padding: 14px; }
    .topbar { padding: 0 1rem; }
    .step-rail { padding: 0 1rem; }
    .step-label { display: none; }
    .step-connector { width: 16px; }
  }
  @media (max-width: 480px) {
    .card-header { padding: 12px 14px; }
    .card-header-icon { display: none; }
  }
</style>