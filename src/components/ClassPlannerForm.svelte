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
        fetchNormativa,
        type PlaneadorData,
        type NormativaItem,
    } from "../../api/service";

    let { onBack }: { onBack: () => void } = $props();

    let isLoading = $state(false);
    let isLoadingDocentes = $state(false);
    let isLoadingMaterias = $state(false);
    let isLoadingEstudiantes = $state(false);
    let isLoadingDBAs = $state(false);
    let isLoadingEBCs = $state(false);

    // --- Datos ---
    let docentes = $state<string[]>([]);
    let materias = $state<{ materia: string }[]>([]);
    let estudiantes = $state<{ nombre: string; grado: number | string }[]>([]);

    let dbas = $state<NormativaItem[]>([]);
    let ebcs = $state<NormativaItem[]>([]);

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
                const area = formData.subject.toLowerCase().trim();
                const grado = formData.grado.toLowerCase().trim();

                const [dbasData, ebcsData] = await Promise.all([
                    fetchNormativa("DBA", { grado, area }),
                    fetchNormativa("EBC", { grado, area }),
                ]);

                dbas = dbasData;
                ebcs = ebcsData;
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
                        <div
                            class="bg-yellow-50 p-4 rounded-lg border border-yellow-200 text-yellow-800 text-sm"
                        >
                            Seleccione un grado y una materia en el paso
                            anterior para ver los DBA disponibles.
                        </div>
                    {:else if isLoadingDBAs}
                        <div
                            class="bg-blue-50 p-4 rounded-lg border border-blue-100 text-center"
                        >
                            <div
                                class="animate-spin h-6 w-6 border-2 border-blue-500 border-t-transparent rounded-full mx-auto"
                            ></div>
                            <p class="text-sm text-blue-600 mt-2">
                                Cargando DBAs...
                            </p>
                        </div>
                    {:else if dbas.length === 0}
                        <div
                            class="bg-yellow-50 p-4 rounded-lg border border-yellow-200 text-yellow-800 text-sm"
                        >
                            No se encontraron DBA para la combinación
                            seleccionada.
                        </div>
                    {:else}
                        <div
                            class="bg-blue-50 p-4 rounded-lg border border-blue-100"
                        >
                            <p
                                class="block text-sm font-bold text-blue-800 mb-1"
                            >
                                Derechos Básicos de Aprendizaje (DBA)
                            </p>
                            <p class="text-xs text-blue-600 mb-3">
                                Seleccione los DBA que se trabajarán en esta
                                unidad (Resolución 0256 de 2016).
                            </p>
                            <div class="space-y-3 max-h-96 overflow-y-auto">
                                {#each dbas as dba}
                                    <label
                                        class="flex items-start space-x-3 cursor-pointer hover:bg-blue-100 p-3 rounded transition border border-blue-200"
                                    >
                                        <input
                                            type="checkbox"
                                            checked={formData.dba.includes(
                                                dba.id,
                                            )}
                                            onchange={() => toggleDBA(dba.id)}
                                            class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                        />
                                        <div class="flex-1">
                                            <div
                                                class="flex items-center gap-2 mb-1"
                                            >
                                                <span
                                                    class="text-xs font-bold text-indigo-600 bg-indigo-100 px-2 py-0.5 rounded"
                                                >
                                                    {dba.codigo}
                                                </span>
                                                {#if dba.metadata?.dimension}
                                                    <span
                                                        class="text-xs text-purple-600 bg-purple-50 px-2 py-0.5 rounded"
                                                    >
                                                        {dba.metadata.dimension}
                                                    </span>
                                                {/if}
                                            </div>
                                            <p
                                                class="text-sm text-gray-700 mb-1"
                                            >
                                                {dba.descripcion}
                                            </p>
                                            {#if dba.metadata?.evidencia}
                                                <p
                                                    class="text-xs text-green-600"
                                                >
                                                    <strong>Evidencia:</strong>
                                                    {dba.metadata.evidencia}
                                                </p>
                                            {/if}
                                        </div>
                                    </label>
                                {/each}
                            </div>
                            <p class="text-xs text-blue-500 mt-2">
                                {dbas.length} DBA(s) disponible(s) para {formData.subject}
                                - {formData.grado}
                            </p>
                        </div>
                    {/if}

                    <!-- Estándares EBC -->
                    {#if !formData.subject || !formData.grado}
                        <div
                            class="bg-yellow-50 p-4 rounded-lg border border-yellow-200 text-yellow-800 text-sm"
                        >
                            Seleccione un grado y una materia en el paso
                            anterior para ver los EBC disponibles.
                        </div>
                    {:else if isLoadingEBCs}
                        <div
                            class="bg-purple-50 p-4 rounded-lg border border-purple-100 text-center"
                        >
                            <div
                                class="animate-spin h-6 w-6 border-2 border-purple-500 border-t-transparent rounded-full mx-auto"
                            ></div>
                            <p class="text-sm text-purple-600 mt-2">
                                Cargando EBCs...
                            </p>
                        </div>
                    {:else if ebcs.length === 0}
                        <div
                            class="bg-yellow-50 p-4 rounded-lg border border-yellow-200 text-yellow-800 text-sm"
                        >
                            No se encontraron EBC para la combinación
                            seleccionada.
                        </div>
                    {:else}
                        <div
                            class="bg-purple-50 p-4 rounded-lg border border-purple-100"
                        >
                            <p
                                class="block text-sm font-bold text-purple-800 mb-1"
                            >
                                Estándares Básicos de Competencia (EBC)
                            </p>
                            <p class="text-xs text-purple-600 mb-3">
                                Seleccione el estándar que se trabajarán en esta
                                unidad.
                            </p>
                            <!-- <select
                                id="standard"
                                bind:value={formData.standard}
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2.5 bg-white/80 mb-3"
                            >
                                <option value="">Seleccione el estándar...</option>
                                {#each ebcOptions as ebc}
                                    <option value={ebc.id}>{ebc.id} - {ebc.descripcion.substring(0, 80)}...</option>
                                {/each}
                            </select> -->
                            <div class="space-y-3 max-h-64 overflow-y-auto">
                                {#each ebcs as ebc}
                                    <label
                                        class="flex items-start space-x-3 cursor-pointer hover:bg-purple-100 p-3 rounded transition border border-purple-200"
                                    >
                                        <input
                                            type="checkbox"
                                            checked={formData.standard.includes(
                                                ebc.id,
                                            )}
                                            onchange={() => toggleEBC(ebc.id)}
                                            class="mt-1 h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                                        />
                                        <div class="flex-1">
                                            <div
                                                class="flex items-center gap-2 mb-1"
                                            >
                                                <span
                                                    class="text-xs font-bold text-purple-600 bg-purple-100 px-2 py-0.5 rounded"
                                                >
                                                    {ebc.codigo}
                                                </span>
                                                {#if ebc.metadata?.dimension}
                                                    <span
                                                        class="text-xs text-pink-600 bg-pink-50 px-2 py-0.5 rounded"
                                                    >
                                                        {ebc.metadata.dimension}
                                                    </span>
                                                {/if}
                                            </div>
                                            <p
                                                class="text-sm text-gray-700 mb-1"
                                            >
                                                {ebc.descripcion}
                                            </p>
                                            {#if ebc.metadata?.evidencia}
                                                <p
                                                    class="text-xs text-green-600"
                                                >
                                                    <strong>Evidencia:</strong>
                                                    {ebc.metadata.evidencia}
                                                </p>
                                            {/if}
                                        </div>
                                    </label>
                                {/each}
                            </div>
                            <p class="text-xs text-purple-500 mt-2">
                                {ebcs.length} EBC(s) disponible(s) para {formData.subject}
                                - {formData.grado}
                            </p>
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
                        <label
                            for="piar_description"
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Descripción de Ajustes Razonables</label
                        >
                        <textarea
                            id="piar_description"
                            bind:value={formData.piar_description}
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 p-3 bg-white/80"
                            placeholder="Describa los ajustes razonables específicos (Decreto 1421 de 2017)..."
                        ></textarea>
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
