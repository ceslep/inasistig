<script lang="ts">
    import { onMount } from "svelte";
    import { fade, fly } from "svelte/transition";
    import Swal from "sweetalert2";
    import { theme } from "../lib/themeStore";
    import {
        savePlaneador,
        getDocentes,
        getMaterias,
        getEstudiantes,
        type PlaneadorData,
        type NormativaItem,
    } from "../../api/service";
    import { URL_DBA_EBC } from "../constants";
    import Piar from "./Piar.svelte";

    let { onBack }: { onBack: () => void } = $props();

    let isLoading = $state(false);
    let isLoadingDocentes = $state(false);
    let isLoadingMaterias = $state(false);
    let isLoadingEstudiantes = $state(false);
    let isLoadingDBAs = $state(false);
    let isLoadingEBCs = $state(false);

    // PIAR Modal
    let showPiarModal = $state(false);

    // --- Datos ---
    let docentes = $state<string[]>([]);
    let materias = $state<{ materia: string }[]>([]);
    let estudiantes = $state<{ nombre: string; grado: number | string }[]>([]);

    let dbas = $state<NormativaItem[]>([]);
    let ebcs = $state<NormativaItem[]>([]);

    // --- Búsqueda y Filtros ---
    let dbaSearch = $state("");
    let ebcSearch = $state("");
    let dbaFilterComponente = $state<string | null>(null);
    let ebcFilterComponente = $state<string | null>(null);

    // Clasificación de componentes por palabras clave
    const COMPONENTES_KEYWORDS: Record<string, string[]> = {
        naturaleza: ["naturaleza", "conocimiento", "ciencia", "teórico", "concepto", "fundamento", "evolución", "análisis"],
        apropiacion: ["apropiación", "uso", "utilizar", "herramienta", "empleo", "manejo", "aplicar"],
        solucion: ["solución", "problema", "diseñar", "construir", "desarrollar", "crear", "implementar", "proponer"],
        sociedad: ["sociedad", "impacto", "ambiente", "ético", "comunidad", "cultura", "responsabilidad", "derechos"]
    };

    function clasificarComponente(texto: string): string {
        const t = texto.toLowerCase();
        for (const [comp, palabras] of Object.entries(COMPONENTES_KEYWORDS)) {
            if (palabras.some(p => t.includes(p))) return comp;
        }
        return "general";
    }

    // Listas derivadas con filtros
    let filteredDbas = $derived.by(() => {
        const withComponente = dbas.map(d => ({...d, componente: clasificarComponente(d.descripcion)}));
        let result = withComponente;
        if (dbaFilterComponente) {
            result = result.filter(d => d.componente === dbaFilterComponente);
        }
        if (dbaSearch) {
            const s = dbaSearch.toLowerCase();
            result = result.filter(d => d.descripcion.toLowerCase().includes(s));
        }
        return result;
    });

    let filteredEbcs = $derived.by(() => {
        const withComponente = ebcs.map(e => ({...e, componente: clasificarComponente(e.descripcion)}));
        let result = withComponente;
        if (ebcFilterComponente) {
            result = result.filter(e => e.componente === ebcFilterComponente);
        }
        if (ebcSearch) {
            const s = ebcSearch.toLowerCase();
            result = result.filter(e => e.descripcion.toLowerCase().includes(s));
        }
        return result;
    });

    // Componentes únicos disponibles
    let dbaComponentes = $derived([...new Set(dbas.map(d => clasificarComponente(d.descripcion)))]);
    let ebcComponentes = $derived([...new Set(ebcs.map(e => clasificarComponente(e.descripcion)))]);

    // Contadores por componente
    let dbaCountsByComponente = $derived.by(() => {
        const counts: Record<string, number> = {};
        dbas.forEach(d => {
            const comp = clasificarComponente(d.descripcion);
            counts[comp] = (counts[comp] || 0) + 1;
        });
        return counts;
    });

    let ebcCountsByComponente = $derived.by(() => {
        const counts: Record<string, number> = {};
        ebcs.forEach(e => {
            const comp = clasificarComponente(e.descripcion);
            counts[comp] = (counts[comp] || 0) + 1;
        });
        return counts;
    });

    const LABELS_COMPONENTE: Record<string, string> = {
        naturaleza: "Naturaleza",
        apropiacion: "Apropiación",
        solucion: "Solución",
        sociedad: "Sociedad",
        general: "General"
    };

    const COLORES_COMPONENTE: Record<string, { bg: string; text: string }> = {
        naturaleza: { bg: "bg-blue-100", text: "text-blue-700" },
        apropiacion: { bg: "bg-green-100", text: "text-green-700" },
        solucion: { bg: "bg-orange-100", text: "text-orange-700" },
        sociedad: { bg: "bg-pink-100", text: "text-pink-700" },
        general: { bg: "bg-gray-100", text: "text-gray-700" }
    };

    // --- LÓGICA Y ESTADO (SVELTE 5 RUNES) ---

    // Estado reactivo para controlar el paso actual del Stepper (0 a 4)
    let currentStep = $state(0);

    // Estado reactivo principal del formulario
    // Inicializamos con valores vacíos o por defecto
    let formData = $state({
        // 1. Datos Básicos
        docente: localStorage.getItem("lastDocente") || "",
        grado: "",
        subject: "",
        period: "",

        // 2. Referentes de Calidad (MEN)
        dba: [] as string[], // Derechos Básicos de Aprendizaje seleccionados
        standard: [] as string[], // Estándar Básico de Competencia
        competency: "", // Competencia (Ciudadana/Socioemocional)

        // 3. Inclusión (PIAR)
        has_piar: false,
        piar_description: "",

        // 4. Secuencia Didáctica (Momentos de la clase)
        exploration: "",
        structuring: "",
        practice: "",
        transfer: "",
        assessment_moment: "",

        // 5. Evaluación y Recursos
        eval_criteria: "",
        eval_evidence: "",
        eval_type: "Formativa",
        resources: "",
    });

    // Materias por docente
    let docenteMaterias: Record<string, string[]> = JSON.parse(
        localStorage.getItem("docenteMaterias") || "{}",
    );
    let selectedMaterias = $state<{ materia: string; horas: string }[]>([]);

    // Verificar si el docente tiene "-"
    let docenteHasDash = $derived(formData.docente.includes("-"));

    const GRADOS_SIN_GUION = [
        { value: "SEXTO", label: "SEXTO" },
        { value: "SEPTIMO", label: "SEPTIMO" },
        { value: "OCTAVO", label: "OCTAVO" },
        { value: "NOVENO", label: "NOVENO" },
        { value: "DECIMO", label: "DECIMO" },
        { value: "ONCE", label: "ONCE" },
    ];

    const GRADOS_CON_GUION = [
        { value: "PREESCOLAR", label: "PREESCOLAR" },
        { value: "PRIMERO", label: "PRIMERO" },
        { value: "SEGUNDO", label: "SEGUNDO" },
        { value: "TERCERO", label: "TERCERO" },
        { value: "CUARTO", label: "CUARTO" },
        { value: "QUINTO", label: "QUINTO" },
    ];

    // Extraer número del docente cuando tiene patrón "Nombre-número"
    const getDocenteNumber = (docente: string): string | null => {
        const match = docente.match(/-(\d+)$/);
        return match ? match[1] : null;
    };

    interface GradoOption {
        value: string;
        label: string;
    }

    // Persistencia de materias por docente
    $effect(() => {
        if (formData.docente) {
            localStorage.setItem("lastDocente", formData.docente);
        }
    });

    // Materias ordenadas por docente
    let materiasSorted = $derived(
        formData.docente
            ? [...materias].sort((a, b) => {
                  const aSaved = docenteMaterias[formData.docente]?.includes(
                      a.materia,
                  );
                  const bSaved = docenteMaterias[formData.docente]?.includes(
                      b.materia,
                  );
                  if (aSaved && !bSaved) return -1;
                  if (!aSaved && bSaved) return 1;
                  return a.materia.localeCompare(b.materia);
              })
            : materias,
    );

    const standardOptions = [
        "Comprendo textos literarios para propiciar el desarrollo de mi capacidad creativa...",
        "Produzco textos orales y escritos que evidencian el conocimiento alcanzado...",
    ];

    // Lógica derivada para validación simple (ejemplo)
    let isStepValid = $derived.by(() => {
        if (currentStep === 0)
            return formData.docente !== "" && formData.grado !== "";
        if (currentStep === 1) return formData.standard.length > 0;
        // Se puede expandir la validación según necesidad
        return true;
    });

    // Grados únicos de los estudiantes filtrados por docente
    let docenteNumber = $derived(getDocenteNumber(formData.docente));

    const gradoOrder: Record<string, number> = {
        PREESCOLAR: 0,
        PRIMERO: 1,
        SEGUNDO: 2,
        TERCERO: 3,
        CUARTO: 4,
        QUINTO: 5,
        SEXTO: 6,
        SEPTIMO: 7,
        OCTAVO: 8,
        NOVENO: 9,
        DECIMO: 10,
        ONCE: 11,
    };

    let filteredGrados = $derived.by((): GradoOption[] => {
        if (docenteNumber) {
            return GRADOS_CON_GUION;
        }
        return GRADOS_SIN_GUION;
    });

    // Funciones de navegación
    function nextStep() {
        if (currentStep < 4) currentStep++;
    }

    function prevStep() {
        if (currentStep > 0) currentStep--;
    }

    function toggleDBA(dba: string) {
        if (formData.dba.includes(dba)) {
            formData.dba = formData.dba.filter((d) => d !== dba);
        } else {
            formData.dba = [...formData.dba, dba];
        }
    }

    function toggleEBC(ebc: string) {
        if (formData.standard.includes(ebc)) {
            formData.standard = formData.standard.filter((e) => e !== ebc);
        } else {
            formData.standard = [...formData.standard, ebc];
        }
    }

    $effect(() => {
        const loadNormativa = async () => {
            if (!formData.subject || !formData.grado) {
                dbas = [];
                ebcs = [];
                return;
            }

            isLoadingDBAs = true;
            isLoadingEBCs = true;
            try {
                const materia = formData.subject.toLowerCase().trim();
                const grado = formData.grado.toLowerCase().trim();

                const [dbasResponse, ebcsResponse] = await Promise.all([
                    fetch(`${URL_DBA_EBC}?tipo=DBA&materia=${materia}&grado=${grado}`),
                    fetch(`${URL_DBA_EBC}?tipo=EBC&materia=${materia}&grado=${grado}`),
                ]);

                const [dbasJson, ebcsJson] = await Promise.all([
                    dbasResponse.json(),
                    ebcsResponse.json(),
                ]);

                const transformar = (json: any, tipo: string): NormativaItem[] => {
                    const items = json?.data?.[0]?.dbas || [];
                    return items.map((texto: string, i: number) => ({
                        id: texto,
                        codigo: `${tipo} ${i + 1}`,
                        descripcion: texto
                    }));
                };

                dbas = transformar(dbasJson, "DBA");
                ebcs = transformar(ebcsJson, "EBC");
            } catch (error) {
                console.error("Error cargando Normativa:", error);
                dbas = [];
                ebcs = [];
            } finally {
                isLoadingDBAs = false;
                isLoadingEBCs = false;
            }
        };
        loadNormativa();
    });

    // Carga de datos
    const loadData = async () => {
        isLoadingDocentes = true;
        isLoadingMaterias = true;
        isLoadingEstudiantes = true;
        try {
            const [docentesData, materiasData, estudiantesData] =
                await Promise.all([
                    getDocentes(),
                    getMaterias(),
                    getEstudiantes(),
                ]);
            docentes = docentesData;
            materias = materiasData;
            estudiantes = estudiantesData;
        } catch (error) {
            console.error("Error cargando datos:", error);
        } finally {
            isLoadingDocentes = false;
            isLoadingMaterias = false;
            isLoadingEstudiantes = false;
        }
    };

    onMount(() => {
        loadData();
    });

    async function handleSubmit() {
        isLoading = true;
        try {
            const planeacionData: PlaneadorData = {
                docente: formData.docente,
                institution: "",
                campus: "",
                grade: formData.grado,
                subject: formData.subject,
                period: formData.period,
                dba: formData.dba,
                standard: formData.standard,
                competency: formData.competency,
                has_piar: formData.has_piar,
                piar_description: formData.piar_description,
                exploration: formData.exploration,
                structuring: formData.structuring,
                practice: formData.practice,
                transfer: formData.transfer,
                assessment_moment: formData.assessment_moment,
                eval_criteria: formData.eval_criteria,
                eval_evidence: formData.eval_evidence,
                eval_type: formData.eval_type,
                resources: formData.resources,
            };

            const validatePlaneacion = (data: PlaneadorData): boolean => {
                if (!data.dba?.length)
                    throw new Error("Seleccione al menos un DBA");
                if (!data.standard?.length)
                    throw new Error("Seleccione al menos un EBC");

                const dbaIds = new Set(dbas.map((d) => d.id));
                const ebcIds = new Set(ebcs.map((e) => e.id));

                if (data.dba.some((id) => !dbaIds.has(id))) {
                    throw new Error(
                        "Un DBA seleccionado no corresponde al grado/área actual",
                    );
                }
                if (data.standard.some((id) => !ebcIds.has(id))) {
                    throw new Error(
                        "Un EBC seleccionado no corresponde al área actual",
                    );
                }
                return true;
            };

            validatePlaneacion(planeacionData);

            const result = await savePlaneador(planeacionData);

            if (result.success) {
                Swal.fire({
                    icon: "success",
                    title: "¡Guardado!",
                    text:
                        result.message ||
                        "Planeación guardada exitosamente según normativa MEN.",
                    confirmButtonColor: "#10b981",
                    timer: 3000,
                });

                // Reset form
                formData = {
                    docente: localStorage.getItem("lastDocente") || "",
                    grado: "",
                    subject: "",
                    period: "",
                    dba: [],
                    standard: [],
                    competency: "",
                    has_piar: false,
                    piar_description: "",
                    exploration: "",
                    structuring: "",
                    practice: "",
                    transfer: "",
                    assessment_moment: "",
                    eval_criteria: "",
                    eval_evidence: "",
                    eval_type: "Formativa",
                    resources: "",
                };
                currentStep = 0;
            } else {
                throw new Error(result.message || "Error desconocido");
            }
        } catch (error) {
            console.error("Error guardando planeación:", error);
            Swal.fire({
                icon: "error",
                title: "Error",
                text:
                    error instanceof Error
                        ? error.message
                        : "Error al guardar la planeación. Intente nuevamente.",
                confirmButtonColor: "#ef4444",
            });
        } finally {
            isLoading = false;
        }
    }
</script>

<div
    class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-4 md:p-8 font-sans text-slate-800"
>
    <!-- Contenedor Principal con efecto Glassmorphism -->
    <div
        class="max-w-4xl mx-auto bg-white/70 backdrop-blur-lg rounded-2xl shadow-xl border border-white/50 overflow-hidden"
    >
        <!-- Header -->
        <header
            class="bg-indigo-600 text-white p-6 flex justify-between items-start"
        >
            <div>
                <h1 class="text-2xl font-bold flex items-center gap-2">
                    <span>📚</span> Planeador de Clases MEN - Colombia
                </h1>
                <p class="text-indigo-100 text-sm mt-1">
                    Diseñado bajo lineamientos de Estándares Básicos y DBA
                </p>
            </div>
            <button
                onclick={onBack}
                class="p-2 rounded-lg bg-indigo-700 hover:bg-indigo-800 transition-colors"
                aria-label="Volver al Dashboard"
            >
                <svg
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>
        </header>

        <!-- Stepper Indicator -->
        <div
            class="flex justify-between items-center px-6 py-4 bg-white/50 border-b border-indigo-100"
        >
            {#each ["Datos", "Referentes", "Didáctica", "Evaluación", "Recursos"] as label, index}
                <div class="flex flex-col items-center relative z-10">
                    <div
                        class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300"
                        class:bg-indigo-600={currentStep >= index}
                        class:bg-gray-200={currentStep < index}
                        class:text-white={currentStep >= index}
                        class:text-gray-500={currentStep < index}
                    >
                        {index + 1}
                    </div>
                    <span
                        class="text-xs mt-1 font-medium text-gray-600 hidden md:block"
                        >{label}</span
                    >
                </div>
                {#if index < 4}
                    <div class="flex-1 h-1 bg-gray-200 mx-2 rounded">
                        <div
                            class="h-full bg-indigo-400 transition-all duration-500"
                            style="width: {currentStep > index ? '100%' : '0%'}"
                        ></div>
                    </div>
                {/if}
            {/each}
        </div>

        <!-- Form Body -->
        <form
            class="p-6 md:p-8 space-y-6"
            onsubmit={(e) => {
                e.preventDefault();
                handleSubmit();
            }}
        >
            <!-- PASO 1: DATOS BÁSICOS -->
            {#if currentStep === 0}
                <div class="space-y-4 animate-fade-in">
                    <h2
                        class="text-xl font-semibold text-indigo-800 border-l-4 border-indigo-500 pl-3"
                    >
                        Información de la Clase
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                for="docente"
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Docente</label
                            >
                            <select
                                id="docente"
                                bind:value={formData.docente}
                                disabled={isLoadingDocentes}
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2.5 bg-white/80 disabled:opacity-50"
                            >
                                <option value=""
                                    >{isLoadingDocentes
                                        ? "Cargando..."
                                        : "Seleccione docente"}</option
                                >
                                {#each docentes as docente}
                                    <option value={docente}>{docente}</option>
                                {/each}
                            </select>
                        </div>
                        <div>
                            <label
                                for="grado"
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Grado</label
                            >
                            <select
                                id="grado"
                                bind:value={formData.grado}
                                disabled={isLoadingEstudiantes}
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2.5 bg-white/80 disabled:opacity-50"
                            >
                                <option value=""
                                    >{isLoadingEstudiantes
                                        ? "Cargando..."
                                        : "Seleccione grado"}</option
                                >
                                {#each filteredGrados as g}
                                    <option value={g.value}>{g.label}</option>
                                {/each}
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label
                                for="materia"
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Asignatura / Área</label
                            >
                            {#if docenteHasDash}
                                <div
                                    class="border rounded-lg p-2 flex flex-col lg:flex-row lg:flex-wrap gap-2"
                                >
                                    {#each materiasSorted as materia}
                                        {@const isSaved = docenteMaterias[
                                            formData.docente
                                        ]?.includes(materia.materia)}
                                        {@const selectedIndex =
                                            selectedMaterias.findIndex(
                                                (m) =>
                                                    m.materia ===
                                                    materia.materia,
                                            )}
                                        {@const isSelected = selectedIndex >= 0}
                                        <label
                                            class="flex items-center gap-2 px-3 py-2 rounded-lg border cursor-pointer transition-all {isSelected
                                                ? 'border-indigo-500 bg-indigo-50'
                                                : 'border-transparent'}"
                                        >
                                            <input
                                                type="checkbox"
                                                checked={isSelected}
                                                onchange={(e) => {
                                                    if (
                                                        e.currentTarget.checked
                                                    ) {
                                                        selectedMaterias = [
                                                            ...selectedMaterias,
                                                            {
                                                                materia:
                                                                    materia.materia,
                                                                horas: "",
                                                            },
                                                        ];
                                                    } else {
                                                        selectedMaterias =
                                                            selectedMaterias.filter(
                                                                (m) =>
                                                                    m.materia !==
                                                                    materia.materia,
                                                            );
                                                    }
                                                }}
                                                class="w-4 h-4 rounded text-indigo-600"
                                            />
                                            <span class="text-sm">
                                                {isSaved
                                                    ? "⭐ "
                                                    : ""}{materia.materia}
                                            </span>
                                        </label>
                                    {/each}
                                </div>
                            {:else}
                                <select
                                    id="materia"
                                    bind:value={formData.subject}
                                    disabled={isLoadingMaterias}
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2.5 bg-white/80 disabled:opacity-50"
                                >
                                    <option value=""
                                        >{isLoadingMaterias
                                            ? "Cargando..."
                                            : "Seleccione materia"}</option
                                    >
                                    {#each materiasSorted as materia}
                                        {@const isSaved = docenteMaterias[
                                            formData.docente
                                        ]?.includes(materia.materia)}
                                        <option value={materia.materia}>
                                            {isSaved
                                                ? "⭐ "
                                                : ""}{materia.materia}
                                        </option>
                                    {/each}
                                </select>
                            {/if}
                        </div>
                    </div>
                </div>
            {/if}

            <!-- PASO 2: REFERENTES DE CALIDAD (MEN) -->
            {#if currentStep === 1}
                <div class="space-y-6 animate-fade-in">
                    <h2
                        class="text-xl font-semibold text-indigo-800 border-l-4 border-indigo-500 pl-3"
                    >
                        Referentes de Calidad (Normativa)
                    </h2>

                    <!-- DBA Selection -->
                    {#if !formData.subject || !formData.grado}
                        <div class="bg-gradient-to-r from-amber-50 to-yellow-50 p-6 rounded-xl border border-amber-200">
                            <div class="flex items-center gap-3">
                                <div class="p-3 bg-amber-100 rounded-full">
                                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-amber-800">Selección requerida</h3>
                                    <p class="text-sm text-amber-700">Seleccione un grado y una materia en el paso anterior para ver los DBA disponibles.</p>
                                </div>
                            </div>
                        </div>
                    {:else if isLoadingDBAs}
                        <div class="bg-white p-6 rounded-xl border border-gray-200">
                            <div class="flex items-center gap-3 mb-4">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                <h3 class="font-semibold text-gray-800">Derechos Básicos de Aprendizaje (DBA)</h3>
                            </div>
                            <div class="space-y-3">
                                {#each Array(4) as _, i}
                                    <div class="animate-pulse flex gap-3">
                                        <div class="w-5 h-5 bg-gray-200 rounded"></div>
                                        <div class="flex-1 space-y-2">
                                            <div class="h-4 bg-gray-200 rounded w-1/4"></div>
                                            <div class="h-3 bg-gray-100 rounded w-3/4"></div>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        </div>
                    {:else if dbas.length === 0}
                        <div class="bg-gradient-to-r from-red-50 to-orange-50 p-6 rounded-xl border border-red-200">
                            <div class="flex items-center gap-3">
                                <div class="p-3 bg-red-100 rounded-full">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-red-800">Sin resultados</h3>
                                    <p class="text-sm text-red-700">No se encontraron DBA para la combinación seleccionada.</p>
                                </div>
                            </div>
                        </div>
                    {:else}
                        <!-- DBA Section -->
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                            <!-- Header -->
                            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 p-4 border-b border-gray-100">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-indigo-100 rounded-lg">
                                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-gray-800">Derechos Básicos de Aprendizaje (DBA)</h3>
                                            <p class="text-xs text-gray-500">Marco normativo: Resolución 0256 de 2016</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-medium px-2 py-1 bg-indigo-100 text-indigo-700 rounded-full">
                                            {dbas.length} disponibles
                                        </span>
                                    </div>
                                </div>

                                <!-- Search Input -->
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <input 
                                        type="text"
                                        bind:value={dbaSearch}
                                        placeholder="Buscar en descripción..."
                                        class="w-full pl-10 pr-10 py-2.5 text-sm rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none"
                                    />
                                    {#if dbaSearch}
                                        <button 
                                            onclick={() => dbaSearch = ""} 
                                            aria-label="Limpiar búsqueda"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    {/if}
                                </div>
                            </div>

                            <!-- Filter Chips -->
                            <div class="p-4 border-b border-gray-100 bg-gray-50">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <button 
                                        type="button"
                                        onclick={() => dbaFilterComponente = null}
                                        class="px-3 py-1.5 rounded-full text-xs font-medium transition-all duration-200
                                               {dbaFilterComponente === null 
                                                   ? 'bg-indigo-600 text-white shadow-md' 
                                                   : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'}"
                                    >
                                        Todos ({dbas.length})
                                    </button>
                                    {#each dbaComponentes as comp}
                                        {@const count = dbaCountsByComponente[comp] || 0}
                                        <button 
                                            type="button"
                                            onclick={() => dbaFilterComponente = comp}
                                            class="px-3 py-1.5 rounded-full text-xs font-medium transition-all duration-200 capitalize
                                                   {dbaFilterComponente === comp 
                                                       ? 'bg-indigo-600 text-white shadow-md' 
                                                       : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'}"
                                        >
                                            {LABELS_COMPONENTE[comp] || comp} ({count})
                                        </button>
                                    {/each}
                                </div>
                            </div>

                            <!-- Results Count -->
                            <div class="px-4 py-2 bg-gray-50 border-b border-gray-100">
                                <p class="text-xs text-gray-500">
                                    Mostrando <span class="font-semibold text-indigo-600">{filteredDbas.length}</span> de <span class="font-semibold">{dbas.length}</span> resultados
                                    {#if dbaFilterComponente}
                                        <span class="text-gray-400"> · Filtrado por: {LABELS_COMPONENTE[dbaFilterComponente] || dbaFilterComponente}</span>
                                    {/if}
                                </p>
                            </div>

                            <!-- Items List -->
                            <div class="max-h-96 overflow-y-auto divide-y divide-gray-100">
                                {#each filteredDbas as dba, index}
                                    {@const isSelected = formData.dba.includes(dba.id)}
                                    {@const compColor = COLORES_COMPONENTE[dba.componente] || COLORES_COMPONENTE.general}
                                    <label 
                                        class="flex items-start gap-3 p-4 cursor-pointer transition-all duration-200 hover:bg-indigo-50/50
                                               {isSelected ? 'bg-indigo-50' : 'bg-white'}"
                                    >
                                        <div class="relative flex-shrink-0 mt-0.5">
                                            <input 
                                                type="checkbox" 
                                                checked={isSelected}
                                                onchange={() => toggleDBA(dba.id)}
                                                class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer transition-all"
                                            />
                                        </div>
                                        
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-2 flex-wrap">
                                                <span class="px-2 py-1 text-xs font-bold rounded-md bg-indigo-100 text-indigo-700">
                                                    {dba.codigo}
                                                </span>
                                                <span class="px-2 py-1 text-xs font-medium rounded-md capitalize {compColor.bg} {compColor.text}">
                                                    {LABELS_COMPONENTE[dba.componente] || dba.componente}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-700 leading-relaxed line-clamp-3">
                                                {dba.descripcion}
                                            </p>
                                        </div>
                                    </label>
                                {/each}
                            </div>

                            <!-- Empty Filter Results -->
                            {#if filteredDbas.length === 0 && dbaSearch}
                                <div class="p-8 text-center">
                                    <div class="p-4 bg-gray-100 rounded-full inline-flex mb-4">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <p class="text-gray-600 font-medium">No se encontraron resultados</p>
                                    <p class="text-sm text-gray-500 mt-1">Intente con otros términos de búsqueda</p>
                                    <button 
                                        type="button"
                                        onclick={() => { dbaSearch = ""; dbaFilterComponente = null; }}
                                        class="mt-4 px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors"
                                    >
                                        Limpiar filtros
                                    </button>
                                </div>
                            {/if}
                        </div>
                    {/if}

                    <!-- Estándares EBC -->
                    {#if !formData.subject || !formData.grado}
                        <div class="bg-gradient-to-r from-amber-50 to-yellow-50 p-6 rounded-xl border border-amber-200">
                            <div class="flex items-center gap-3">
                                <div class="p-3 bg-amber-100 rounded-full">
                                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-amber-800">Selección requerida</h3>
                                    <p class="text-sm text-amber-700">Seleccione un grado y una materia en el paso anterior para ver los EBC disponibles.</p>
                                </div>
                            </div>
                        </div>
                    {:else if isLoadingEBCs}
                        <div class="bg-white p-6 rounded-xl border border-gray-200">
                            <div class="flex items-center gap-3 mb-4">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                                <h3 class="font-semibold text-gray-800">Estándares Básicos de Competencia (EBC)</h3>
                            </div>
                            <div class="space-y-3">
                                {#each Array(4) as _, i}
                                    <div class="animate-pulse flex gap-3">
                                        <div class="w-5 h-5 bg-gray-200 rounded"></div>
                                        <div class="flex-1 space-y-2">
                                            <div class="h-4 bg-gray-200 rounded w-1/4"></div>
                                            <div class="h-3 bg-gray-100 rounded w-3/4"></div>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        </div>
                    {:else if ebcs.length === 0}
                        <div class="bg-gradient-to-r from-red-50 to-orange-50 p-6 rounded-xl border border-red-200">
                            <div class="flex items-center gap-3">
                                <div class="p-3 bg-red-100 rounded-full">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-red-800">Sin resultados</h3>
                                    <p class="text-sm text-red-700">No se encontraron EBC para la combinación seleccionada.</p>
                                </div>
                            </div>
                        </div>
                    {:else}
                        <!-- EBC Section -->
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                            <!-- Header -->
                            <div class="bg-gradient-to-r from-purple-50 to-pink-50 p-4 border-b border-gray-100">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-purple-100 rounded-lg">
                                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-gray-800">Estándares Básicos de Competencia (EBC)</h3>
                                            <p class="text-xs text-gray-500">Estándares curriculares MEN</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-medium px-2 py-1 bg-purple-100 text-purple-700 rounded-full">
                                            {ebcs.length} disponibles
                                        </span>
                                    </div>
                                </div>

                                <!-- Search Input -->
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <input 
                                        type="text"
                                        bind:value={ebcSearch}
                                        placeholder="Buscar en descripción..."
                                        class="w-full pl-10 pr-10 py-2.5 text-sm rounded-lg border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none"
                                    />
                                    {#if ebcSearch}
                                        <button 
                                            onclick={() => ebcSearch = ""} 
                                            aria-label="Limpiar búsqueda"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    {/if}
                                </div>
                            </div>

                            <!-- Filter Chips -->
                            <div class="p-4 border-b border-gray-100 bg-gray-50">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <button 
                                        type="button"
                                        onclick={() => ebcFilterComponente = null}
                                        class="px-3 py-1.5 rounded-full text-xs font-medium transition-all duration-200
                                               {ebcFilterComponente === null 
                                                   ? 'bg-purple-600 text-white shadow-md' 
                                                   : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'}"
                                    >
                                        Todos ({ebcs.length})
                                    </button>
                                    {#each ebcComponentes as comp}
                                        {@const count = ebcCountsByComponente[comp] || 0}
                                        <button 
                                            type="button"
                                            onclick={() => ebcFilterComponente = comp}
                                            class="px-3 py-1.5 rounded-full text-xs font-medium transition-all duration-200 capitalize
                                                   {ebcFilterComponente === comp 
                                                       ? 'bg-purple-600 text-white shadow-md' 
                                                       : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'}"
                                        >
                                            {LABELS_COMPONENTE[comp] || comp} ({count})
                                        </button>
                                    {/each}
                                </div>
                            </div>

                            <!-- Results Count -->
                            <div class="px-4 py-2 bg-gray-50 border-b border-gray-100">
                                <p class="text-xs text-gray-500">
                                    Mostrando <span class="font-semibold text-purple-600">{filteredEbcs.length}</span> de <span class="font-semibold">{ebcs.length}</span> resultados
                                    {#if ebcFilterComponente}
                                        <span class="text-gray-400"> · Filtrado por: {LABELS_COMPONENTE[ebcFilterComponente] || ebcFilterComponente}</span>
                                    {/if}
                                </p>
                            </div>

                            <!-- Items List -->
                            <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                                {#each filteredEbcs as ebc, index}
                                    {@const isSelected = formData.standard.includes(ebc.id)}
                                    {@const compColor = COLORES_COMPONENTE[ebc.componente] || COLORES_COMPONENTE.general}
                                    <label 
                                        class="flex items-start gap-3 p-4 cursor-pointer transition-all duration-200 hover:bg-purple-50/50
                                               {isSelected ? 'bg-purple-50' : 'bg-white'}"
                                    >
                                        <div class="relative flex-shrink-0 mt-0.5">
                                            <input 
                                                type="checkbox" 
                                                checked={isSelected}
                                                onchange={() => toggleEBC(ebc.id)}
                                                class="w-5 h-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500 cursor-pointer transition-all"
                                            />
                                        </div>
                                        
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-2 flex-wrap">
                                                <span class="px-2 py-1 text-xs font-bold rounded-md bg-purple-100 text-purple-700">
                                                    {ebc.codigo}
                                                </span>
                                                <span class="px-2 py-1 text-xs font-medium rounded-md capitalize {compColor.bg} {compColor.text}">
                                                    {LABELS_COMPONENTE[ebc.componente] || ebc.componente}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-700 leading-relaxed line-clamp-3">
                                                {ebc.descripcion}
                                            </p>
                                        </div>
                                    </label>
                                {/each}
                            </div>

                            <!-- Empty Filter Results -->
                            {#if filteredEbcs.length === 0 && ebcSearch}
                                <div class="p-8 text-center">
                                    <div class="p-4 bg-gray-100 rounded-full inline-flex mb-4">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <p class="text-gray-600 font-medium">No se encontraron resultados</p>
                                    <p class="text-sm text-gray-500 mt-1">Intente con otros términos de búsqueda</p>
                                    <button 
                                        type="button"
                                        onclick={() => { ebcSearch = ""; ebcFilterComponente = null; }}
                                        class="mt-4 px-4 py-2 text-sm font-medium text-purple-600 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors"
                                    >
                                        Limpiar filtros
                                    </button>
                                </div>
                            {/if}
                        </div>
                    {/if}

                    <!-- PIAR Toggle -->
                    <div
                        class="flex items-center justify-between bg-yellow-50 p-4 rounded-lg border border-yellow-200"
                    >
                        <div>
                            <h3 class="font-semibold text-yellow-800">
                                Ajustes Razonables (PIAR)
                            </h3>
                            <p class="text-xs text-yellow-700">
                                ¿Requiere adaptaciones curriculares para
                                estudiantes con discapacidad?
                            </p>
                        </div>
                        <label
                            for="has_piar"
                            class="relative inline-flex items-center cursor-pointer"
                        >
                            <input
                                id="has_piar"
                                type="checkbox"
                                bind:checked={formData.has_piar}
                                class="sr-only peer"
                            />
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-500"
                            ></div>
                        </label>
                    </div>

                    {#if formData.has_piar}
                        <div class="space-y-4">
                            <label
                                for="piar_description"
                                class="block text-sm font-medium text-gray-700"
                            >Descripción de Ajustes Razonables</label>
                            <textarea
                                id="piar_description"
                                bind:value={formData.piar_description}
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 p-3 bg-white/80"
                                placeholder="Describa los ajustes razonables específicos (Decreto 1421 de 2017)..."
                                rows="3"
                            ></textarea>
                            
                            <!-- Botón para abrir PIAR completo -->
                            <button
                                type="button"
                                onclick={() => showPiarModal = true}
                                class="flex items-center justify-center gap-2 w-full py-3 px-4 bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 text-white font-semibold rounded-lg shadow-md transition-all duration-200"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Crear Acta PIAR Completa
                            </button>
                            
                            <p class="text-xs text-gray-500 text-center">
                                Genere un documento oficial completo con firma digital para el estudiante
                            </p>
                        </div>
                    {/if}
                </div>
            {/if}

            <!-- PASO 3: SECUENCIA DIDÁCTICA -->
            {#if currentStep === 2}
                <div class="space-y-4 animate-fade-in">
                    <h2
                        class="text-xl font-semibold text-indigo-800 border-l-4 border-indigo-500 pl-3"
                    >
                        Momentos de la Clase
                    </h2>
                    <p class="text-sm text-gray-500 mb-4">
                        Estructura pedagógica basada en el ciclo de aprendizaje.
                    </p>

                    <div class="grid gap-4">
                        <div
                            class="bg-white p-4 rounded-lg shadow-sm border border-gray-200"
                        >
                            <label
                                for="exploration"
                                class="block text-sm font-bold text-indigo-700 mb-1"
                                >1. Exploración</label
                            >
                            <textarea
                                id="exploration"
                                bind:value={formData.exploration}
                                rows="2"
                                class="w-full text-sm rounded border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Actividades para recuperar saberes previos y motivar..."
                            ></textarea>
                        </div>

                        <div
                            class="bg-white p-4 rounded-lg shadow-sm border border-gray-200"
                        >
                            <label
                                for="structuring"
                                class="block text-sm font-bold text-indigo-700 mb-1"
                                >2. Estructuración</label
                            >
                            <textarea
                                id="structuring"
                                bind:value={formData.structuring}
                                rows="2"
                                class="w-full text-sm rounded border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Explicación teórica, conceptualización y modelado..."
                            ></textarea>
                        </div>

                        <div
                            class="bg-white p-4 rounded-lg shadow-sm border border-gray-200"
                        >
                            <label
                                for="practice"
                                class="block text-sm font-bold text-indigo-700 mb-1"
                                >3. Práctica</label
                            >
                            <textarea
                                id="practice"
                                bind:value={formData.practice}
                                rows="2"
                                class="w-full text-sm rounded border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Ejercicios guiados y trabajo colaborativo..."
                            ></textarea>
                        </div>

                        <div
                            class="bg-white p-4 rounded-lg shadow-sm border border-gray-200"
                        >
                            <label
                                for="transfer"
                                class="block text-sm font-bold text-indigo-700 mb-1"
                                >4. Transferencia</label
                            >
                            <textarea
                                id="transfer"
                                bind:value={formData.transfer}
                                rows="2"
                                class="w-full text-sm rounded border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Aplicación del conocimiento en nuevos contextos..."
                            ></textarea>
                        </div>

                        <div
                            class="bg-white p-4 rounded-lg shadow-sm border border-gray-200"
                        >
                            <label
                                for="assessment_moment"
                                class="block text-sm font-bold text-indigo-700 mb-1"
                                >5. Valoración</label
                            >
                            <textarea
                                id="assessment_moment"
                                bind:value={formData.assessment_moment}
                                rows="2"
                                class="w-full text-sm rounded border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Cierre de la clase y metacognición..."
                            ></textarea>
                        </div>
                    </div>
                </div>
            {/if}

            <!-- PASO 4: EVALUACIÓN -->
            {#if currentStep === 3}
                <div class="space-y-4 animate-fade-in">
                    <h2
                        class="text-xl font-semibold text-indigo-800 border-l-4 border-indigo-500 pl-3"
                    >
                        Evaluación de Aprendizajes
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                for="eval_type"
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Tipo de Evaluación</label
                            >
                            <select
                                id="eval_type"
                                bind:value={formData.eval_type}
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2.5 bg-white/80"
                            >
                                <option value="Formativa"
                                    >Formativa (Proceso)</option
                                >
                                <option value="Sumativa"
                                    >Sumativa (Resultado)</option
                                >
                                <option value="Diagnóstica">Diagnóstica</option>
                            </select>
                        </div>
                        <div>
                            <label
                                for="eval_criteria"
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Criterios de Evaluación</label
                            >
                            <input
                                id="eval_criteria"
                                type="text"
                                bind:value={formData.eval_criteria}
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2.5 bg-white/80"
                                placeholder="¿Qué se va a evaluar?"
                            />
                        </div>
                    </div>

                    <div>
                        <label
                            for="eval_evidence"
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Evidencias de Aprendizaje</label
                        >
                        <textarea
                            id="eval_evidence"
                            bind:value={formData.eval_evidence}
                            rows="3"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2.5 bg-white/80"
                            placeholder="Productos tangibles o intangibles que demuestran el aprendizaje (Ej: Mapa conceptual, exposición oral)..."
                        ></textarea>
                    </div>
                </div>
            {/if}

            <!-- PASO 5: RECURSOS -->
            {#if currentStep === 4}
                <div class="space-y-4 animate-fade-in">
                    <h2
                        class="text-xl font-semibold text-indigo-800 border-l-4 border-indigo-500 pl-3"
                    >
                        Recursos Didácticos
                    </h2>

                    <div>
                        <label
                            for="resources"
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Recursos Físicos y Digitales</label
                        >
                        <textarea
                            id="resources"
                            bind:value={formData.resources}
                            rows="5"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2.5 bg-white/80"
                            placeholder="Ej: Video beam, guías impresas, plataforma Colombia Aprende, laboratorios..."
                        ></textarea>
                    </div>

                    <div
                        class="bg-green-50 p-4 rounded-lg border border-green-200 text-center"
                    >
                        <p class="text-green-800 font-medium">
                            ¡Planeación lista para generar!
                        </p>
                        <p class="text-xs text-green-600">
                            Al guardar, se generará un PDF alineado con los
                            formatos institucionales.
                        </p>
                    </div>
                </div>
            {/if}

            <!-- Navigation Buttons -->
            <div class="flex justify-between pt-6 border-t border-gray-200">
                <button
                    type="button"
                    disabled={currentStep === 0}
                    onclick={prevStep}
                    class="px-6 py-2 rounded-lg text-sm font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed bg-gray-100 text-gray-700 hover:bg-gray-200"
                >
                    Anterior
                </button>

                {#if currentStep < 4}
                    <button
                        type="button"
                        onclick={nextStep}
                        class="px-6 py-2 rounded-lg text-sm font-medium transition-colors bg-indigo-600 text-white hover:bg-indigo-700 shadow-md hover:shadow-lg"
                    >
                        Siguiente
                    </button>
                {:else}
                    <button
                        type="submit"
                        disabled={isLoading}
                        class="px-6 py-2 rounded-lg text-sm font-medium transition-colors bg-green-600 text-white hover:bg-green-700 shadow-md hover:shadow-lg flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {#if isLoading}
                            <svg
                                class="animate-spin h-4 w-4"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            Guardando...
                        {:else}
                            <span>💾</span> Guardar Planeación
                        {/if}
                    </button>
                {/if}
            </div>
        </form>
    </div>

    <!-- PIAR Modal -->
    {#if showPiarModal}
        <div 
            class="fixed inset-0 z-50 flex items-center justify-center p-2 md:p-4"
            transition:fade={{ duration: 200 }}
        >
            <!-- Backdrop -->
            <button 
                type="button"
                class="absolute inset-0 bg-black/60 backdrop-blur-sm cursor-default"
                onclick={() => showPiarModal = false}
                aria-label="Cerrar modal"
            ></button>
            
            <!-- Modal Content -->
            <div 
                class="relative bg-white rounded-2xl shadow-2xl w-full max-w-6xl max-h-[95vh] overflow-hidden flex flex-col"
                transition:fly={{ y: 20, duration: 300 }}
            >
                <!-- Header -->
                <div class="flex items-center justify-between p-4 bg-slate-800 text-white flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-slate-700 rounded-lg">
                            <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-bold text-lg">Registro PIAR Completo</h2>
                            <p class="text-xs text-slate-400">Acta de Ajustes Razonables - Decreto 1421 de 2017</p>
                        </div>
                    </div>
                    <button 
                        type="button"
                        onclick={() => showPiarModal = false}
                        class="p-2 hover:bg-slate-700 rounded-lg transition-colors"
                        aria-label="Cerrar modal"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <!-- Content -->
                <div class="flex-1 overflow-y-auto bg-slate-50">
                    <Piar />
                </div>
            </div>
        </div>
    {/if}
</div>

<style>
    /* Animación simple para la transición entre pasos */
    .animate-fade-in {
        animation: fadeIn 0.4s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
