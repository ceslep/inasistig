<script lang="ts">
    import { onMount } from "svelte";
    import { fade } from "svelte/transition";

    // html2pdf se carga dinámicamente desde CDN
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    type Html2PdfWindow = Window & typeof globalThis & { html2pdf?: any };

    let { onBack }: { onBack: () => void } = $props();

    // ==========================================
    // 1. ESTADO DE LA APLICACIÓN (SVELTE 5 RUNES)
    // ==========================================
    let currentStep = $state(1);
    let isGeneratingPdf = $state(false);
    let showSavedNotification = $state(false);

    const steps = [
        { id: 1, title: "Info. General" },
        { id: 2, title: "Salud y Hogar" },
        { id: 3, title: "Entorno Educativo" },
        { id: 4, title: "Valoración Pedagógica" },
        { id: 5, title: "Ajustes Trimestrales" },
        { id: 6, title: "Recomendaciones" },
        { id: 7, title: "Acta de Acuerdo" },
    ];

    // Estructura de datos completa del PIAR
    let piar = $state({
        // Info General
        fechaDiligenciamiento: "",
        quienDiligencia: "",
        rol: "",
        nombres: "",
        apellidos: "",
        lugarNacimiento: "",
        edad: "",
        fechaNacimiento: "",
        tipoDoc: "TI",
        numDoc: "",
        departamento: "",
        municipio: "",
        direccion: "",
        barrio: "",
        telefono: "",
        correo: "",
        centroProteccion: "NO",
        lugarProteccion: "",
        gradoAspira: "",
        grupoEtnico: "Ninguno",
        victimaConflicto: "No",
        registroVictima: "No",

        // Salud
        afiliacionSalud: "SI",
        eps: "",
        regimenSalud: "Subsidiado",
        lugarEmergencia: "",
        atencionSalud: "NO",
        frecuenciaSalud: "",
        tieneDiagnostico: "NO",
        cualDiagnostico: "",
        asisteTerapias: "NO",
        terapias: [
            { tipo: "", frecuencia: "" },
            { tipo: "", frecuencia: "" },
        ],
        tratamientoMedico: "NO",
        cualTratamiento: "",
        consumeMedicamentos: "NO",
        medicamentos: "",
        productosApoyo: "NO",
        cualesApoyos: "",

        // Hogar
        nombreMadre: "",
        ocupacionMadre: "",
        nivelEduMadre: "",
        nombrePadre: "",
        ocupacionPadre: "",
        nivelEduPadre: "",
        nombreCuidador: "",
        parentescoCuidador: "",
        nivelEduCuidador: "",
        telefonoCuidador: "",
        correoCuidador: "",
        numHermanos: "",
        lugarOcupa: "",
        quienApoyaCrianza: "",
        personasConQuienVive: "",
        bajoProteccion: "NO",
        recibeSubsidio: "NO",
        cualSubsidio: "",

        // Educativo
        vinculadoOtraInstitucion: "NO",
        porQueNoVinculado: "",
        cualesInstituciones: "",
        ultimoGrado: "",
        aprobo: "SI",
        observacionesGrado: "",
        recibeInforme: "NO",
        institucionInforme: "",
        programasComplementarios: "NO",
        cualesProgramas: "",
        institucionActual: "",
        sedeActual: "",
        medioTransporte: "",
        distanciaTiempo: "",

        // Valoración (Anexo 2)
        descripcionEstudiante: "",
        habilidadesCompetencias: "",

        // Ajustes Trimestrales
        trimestres: [
            {
                nombre: "Primer trimestre",
                areas: [
                    {
                        nombre: "Matemáticas",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        nombre: "Lenguaje",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        nombre: "Ciencias",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        nombre: "Convivencia / Socialización",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                ],
            },
            {
                nombre: "Segundo trimestre",
                areas: [
                    {
                        nombre: "Matemáticas",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        nombre: "Lenguaje",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        nombre: "Ciencias",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        nombre: "Convivencia / Socialización",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                ],
            },
            {
                nombre: "Tercer trimestre",
                areas: [
                    {
                        nombre: "Matemáticas",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        nombre: "Lenguaje",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        nombre: "Ciencias",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        nombre: "Convivencia / Socialización",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                ],
            },
        ],

        // Recomendaciones
        recomendacionesFamilia: "",
        recomendacionesDocentes: "",
        recomendacionesDirectivos: "",
        recomendacionesAdministrativos: "",
        recomendacionesPares: "",

        // Acta de Acuerdo (Anexo 3)
        compromisosEspecificos: "",
        actividadesCasa: [
            { nombre: "", descripcion: "", frecuencia: "D" },
            { nombre: "", descripcion: "", frecuencia: "S" },
        ],
    });

    // ==========================================
    // 2. BASES DE DATOS PEDAGÓGICAS (Sugerencias)
    // ==========================================
    const diagnosticosComunes = [
        "Trastorno del Espectro Autista (TEA)",
        "Discapacidad Intelectual",
        "Trastorno por Déficit de Atención e Hiperactividad (TDAH)",
        "Discapacidad Auditiva / Hipoacusia",
        "Discapacidad Visual / Baja visión",
        "Discapacidad Física / Motora",
        "Trastornos Específicos del Aprendizaje (Dislexia, Discalculia)",
        "Discapacidad Psicosocial",
        "Síndrome de Down",
        "Sin diagnóstico médico formal (En proceso)",
    ];

    const barrerasComunes = [
        "Metodológicas: Actividades no acordes al estilo de aprendizaje.",
        "Comunicativas: Dificultad para comprender instrucciones orales complejas.",
        "Evaluativas: Tiempos rígidos para la presentación de pruebas.",
        "Físicas: Accesibilidad limitada en el aula o colegio.",
        "Actitudinales: Sobreprotección o bajas expectativas del entorno.",
        "Sociales: Dificultad para interactuar con pares.",
    ];

    const ajustesComunes = [
        "Uso de material concreto y manipulativo.",
        "Apoyos visuales (Pausas activas, pictogramas, agendas).",
        "Tiempos extendidos para la entrega de actividades y evaluaciones.",
        "Evaluación oral en lugar de escrita.",
        "Reducción de la cantidad de ejercicios (priorizando calidad/concepto).",
        "Uso de tecnología (lectores de pantalla, calculadoras).",
        "Trabajo cooperativo o tutoría de pares.",
        "Instrucciones cortas, fragmentadas y verificadas.",
    ];

    // ==========================================
    // 3. LÓGICA DE ALMACENAMIENTO Y PDF
    // ==========================================

    // Cargar desde LocalStorage al iniciar
    onMount(() => {
        const saved = localStorage.getItem("piar_draft");
        if (saved) {
            try {
                const parsed = JSON.parse(saved);
                // Hacemos merge para no perder campos nuevos si actualizamos la app
                piar = { ...piar, ...parsed };
            } catch (e) {
                console.error("Error cargando PIAR", e);
            }
        }
    });

    // Guardado automático cada vez que 'piar' cambia usando $effect
    $effect(() => {
        localStorage.setItem("piar_draft", JSON.stringify(piar));
        // Pequeño indicador visual de guardado
        showSavedNotification = true;
        const timer = setTimeout(() => (showSavedNotification = false), 2000);
        return () => clearTimeout(timer);
    });

    function resetForm() {
        if (
            confirm(
                "¿Está seguro de borrar todo el formulario? Esta acción no se puede deshacer.",
            )
        ) {
            localStorage.removeItem("piar_draft");
            location.reload();
        }
    }

    async function generarPDF() {
        isGeneratingPdf = true;

        // Esperar un momento para asegurar que el DOM actualizó cualquier estado visual
        await new Promise((r) => setTimeout(r, 200));

        const element = document.getElementById("documento-pdf");
        if (!element) return;

        // Mostramos temporalmente el div de impresión
        element.classList.remove("hidden");

        try {
            // Usamos html2pdf si está inyectado, sino caemos en window.print()
            if ((window as Html2PdfWindow).html2pdf) {
                const opt = {
                    margin: 10,
                    filename: `PIAR_${piar.nombres}_${piar.apellidos}.pdf`,
                    image: { type: "jpeg", quality: 0.98 },
                    html2canvas: { scale: 2, useCORS: true },
                    jsPDF: {
                        unit: "mm",
                        format: "legal",
                        orientation: "portrait",
                    },
                };
                await (window as Html2PdfWindow).html2pdf().set(opt).from(element).save();
            } else {
                // Fallback nativo: el CSS print manejará la visibilidad
                window.print();
            }
        } catch (error) {
            console.error("Error generando PDF", error);
            alert(
                "Hubo un error al generar el PDF. Intente usar (Ctrl+P) o instale html2pdf.",
            );
        } finally {
            // Ocultamos de nuevo el div
            element.classList.add("hidden");
            isGeneratingPdf = false;
        }
    }

    // Inyectar html2pdf por CDN
    onMount(() => {
        const script = document.createElement("script");
        script.src =
            "https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js";
        document.head.appendChild(script);
    });

    // Utilidades de navegación
    const nextStep = () => {
        if (currentStep < steps.length) currentStep++;
    };
    const prevStep = () => {
        if (currentStep > 1) currentStep--;
    };
</script>

<!-- ==========================================
     COMPONENTES REUTILIZABLES (SNIPPETS SVELTE 5)
     ========================================== -->
{#snippet inputGroup(
    label: string,
    id: string,
    type = "text",
    placeholder = "",
    width = "w-full",
    obj: any,
    key: string,
)}
    <div class={`flex flex-col gap-1 ${width}`}>
        <label for={id} class="text-sm font-medium text-slate-700"
            >{label}</label
        >
        {#if type === "textarea"}
            <textarea
                {id}
                class="border border-slate-300 rounded-md p-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                rows="3"
                {placeholder}
                bind:value={obj[key]}
            ></textarea>
        {:else}
            <input
                {type}
                {id}
                class="border border-slate-300 rounded-md p-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                {placeholder}
                bind:value={obj[key]}
            />
        {/if}
    </div>
{/snippet}

{#snippet selectGroup(
    label: string,
    id: string,
    options: string[] | { value: string; label: string }[],
    width = "w-full",
    obj: any,
    key: string,
)}
    <div class={`flex flex-col gap-1 ${width}`}>
        <label for={id} class="text-sm font-medium text-slate-700"
            >{label}</label
        >
        <select
            {id}
            class="border border-slate-300 rounded-md p-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white"
            bind:value={obj[key]}
        >
            <option value="" disabled selected>Seleccione...</option>
            {#each options as opt}
                {#if typeof opt === "string"}
                    <option value={opt}>{opt}</option>
                {:else}
                    <option value={opt.value}>{opt.label}</option>
                {/if}
            {/each}
        </select>
    </div>
{/snippet}

<!-- ==========================================
     INTERFAZ DE USUARIO PRINCIPAL
     ========================================== -->
<div class="min-h-screen bg-slate-50 font-sans print:bg-white text-slate-800">
    <!-- Topbar -->
    <header
        class="bg-blue-800 text-white p-4 shadow-md sticky top-0 z-10 print:hidden flex justify-between items-center"
    >
        <div class="flex items-center gap-4">
            <button
                onclick={onBack}
                class="p-2 hover:bg-blue-700 rounded-lg transition-colors"
                title="Volver al Dashboard"
            >
                <svg
                    class="w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"
                    />
                </svg>
            </button>
            <div>
                <h1 class="text-xl font-bold">Generador PIAR</h1>
                <p class="text-sm text-blue-200">
                    Plan Individual de Ajustes Razonables (Anexos 1, 2 y 3)
                </p>
            </div>
        </div>
        <div class="flex gap-3 items-center">
            <span
                class="text-xs text-green-300 bg-blue-900 px-2 py-1 rounded-full transition-opacity duration-300 {showSavedNotification
                    ? 'opacity-100'
                    : 'opacity-0'}"
            >
                Guardado localmente ✓
            </span>
            <button
                onclick={resetForm}
                class="px-3 py-1.5 text-sm bg-red-600 hover:bg-red-700 rounded-md font-medium transition-colors"
                >Nuevo / Borrar</button
            >
            <button
                onclick={generarPDF}
                disabled={isGeneratingPdf}
                class="px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-slate-400 rounded-md font-bold transition-colors shadow flex items-center gap-2"
            >
                {#if isGeneratingPdf}
                    <svg
                        class="animate-spin h-4 w-4 text-white"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        ><circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        ></circle><path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path></svg
                    >
                    Generando...
                {:else}
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        ><path
                            fill-rule="evenodd"
                            d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z"
                            clip-rule="evenodd"
                        /></svg
                    >
                    Exportar a PDF
                {/if}
            </button>
        </div>
    </header>

    <div
        class="max-w-7xl mx-auto p-4 md:p-6 grid grid-cols-1 md:grid-cols-4 gap-6 print:hidden"
    >
        <!-- Sidebar / Stepper -->
        <aside class="col-span-1">
            <div
                class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sticky top-24"
            >
                <h2
                    class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4"
                >
                    Progreso del PIAR
                </h2>
                <nav class="space-y-2">
                    {#each steps as step}
                        <button
                            onclick={() => (currentStep = step.id)}
                            class="w-full text-left px-4 py-3 rounded-lg text-sm font-medium transition-all {currentStep ===
                            step.id
                                ? 'bg-blue-50 text-blue-700 border border-blue-200 shadow-sm'
                                : 'text-slate-600 hover:bg-slate-50 border border-transparent'}"
                        >
                            <span
                                class="inline-block w-6 h-6 rounded-full text-center leading-6 mr-2 {currentStep ===
                                step.id
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-slate-200 text-slate-500'}"
                            >
                                {step.id}
                            </span>
                            {step.title}
                        </button>
                    {/each}
                </nav>
            </div>
        </aside>

        <!-- Main Form Area -->
        <main class="col-span-1 md:col-span-3">
            <div
                class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 md:p-8"
            >
                <!-- STEP 1: INFORMACIÓN GENERAL -->
                {#if currentStep === 1}
                    <div class="space-y-6 animate-in fade-in">
                        <div class="border-b pb-4 mb-4">
                            <h2 class="text-2xl font-bold text-slate-800">
                                1. Información General del Estudiante
                            </h2>
                            <p class="text-sm text-slate-500">
                                Datos para la matrícula (Anexo 1 PIAR)
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {@render inputGroup(
                                "Fecha Diligenciamiento",
                                "f_dil",
                                "date",
                                "",
                                "col-span-1",
                                piar,
                                "fechaDiligenciamiento",
                            )}
                            {@render inputGroup(
                                "Persona que diligencia",
                                "p_dil",
                                "text",
                                "Nombre del docente/orientador",
                                "col-span-1 md:col-span-2",
                                piar,
                                "quienDiligencia",
                            )}
                        </div>

                        <h3
                            class="font-semibold text-lg mt-6 text-blue-800 bg-blue-50 p-2 rounded"
                        >
                            Datos Personales
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {@render inputGroup(
                                "Nombres",
                                "nombres",
                                "text",
                                "",
                                "col-span-1",
                                piar,
                                "nombres",
                            )}
                            {@render inputGroup(
                                "Apellidos",
                                "apellidos",
                                "text",
                                "",
                                "col-span-1",
                                piar,
                                "apellidos",
                            )}
                            {@render inputGroup(
                                "Lugar de Nacimiento",
                                "lugarnac",
                                "text",
                                "",
                                "col-span-1",
                                piar,
                                "lugarNacimiento",
                            )}
                            <div class="grid grid-cols-2 gap-2">
                                {@render inputGroup(
                                    "Edad",
                                    "edad",
                                    "number",
                                    "",
                                    "col-span-1",
                                    piar,
                                    "edad",
                                )}
                                {@render inputGroup(
                                    "Fecha Nacimiento",
                                    "fnac",
                                    "date",
                                    "",
                                    "col-span-1",
                                    piar,
                                    "fechaNacimiento",
                                )}
                            </div>
                            {@render selectGroup(
                                "Tipo Documento",
                                "tdoc",
                                ["RC", "TI", "CC", "CE", "PPT", "Otro"],
                                "col-span-1",
                                piar,
                                "tipoDoc",
                            )}
                            {@render inputGroup(
                                "No. Identificación",
                                "numdoc",
                                "text",
                                "",
                                "col-span-1",
                                piar,
                                "numDoc",
                            )}
                        </div>

                        <h3
                            class="font-semibold text-lg mt-6 text-blue-800 bg-blue-50 p-2 rounded"
                        >
                            Ubicación y Contacto
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {@render inputGroup(
                                "Departamento",
                                "dep",
                                "text",
                                "",
                                "col-span-1",
                                piar,
                                "departamento",
                            )}
                            {@render inputGroup(
                                "Municipio",
                                "mun",
                                "text",
                                "",
                                "col-span-1",
                                piar,
                                "municipio",
                            )}
                            {@render inputGroup(
                                "Dirección",
                                "dir",
                                "text",
                                "",
                                "col-span-1",
                                piar,
                                "direccion",
                            )}
                            {@render inputGroup(
                                "Barrio/Vereda",
                                "barrio",
                                "text",
                                "",
                                "col-span-1",
                                piar,
                                "barrio",
                            )}
                            {@render inputGroup(
                                "Teléfono(s)",
                                "tel",
                                "text",
                                "",
                                "col-span-1",
                                piar,
                                "telefono",
                            )}
                            {@render inputGroup(
                                "Correo Electrónico",
                                "email",
                                "email",
                                "",
                                "col-span-1",
                                piar,
                                "correo",
                            )}
                        </div>

                        <h3
                            class="font-semibold text-lg mt-6 text-blue-800 bg-blue-50 p-2 rounded"
                        >
                            Condiciones Especiales
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div
                                class="col-span-1 md:col-span-2 flex items-center gap-4 bg-slate-50 p-3 rounded border"
                            >
                                <span class="text-sm font-medium"
                                    >¿Está en centro de protección (ICBF, etc.)?</span
                                >
                                <label
                                    ><input
                                        type="radio"
                                        bind:group={piar.centroProteccion}
                                        value="NO"
                                        class="mr-1"
                                    /> No</label
                                >
                                <label
                                    ><input
                                        type="radio"
                                        bind:group={piar.centroProteccion}
                                        value="SI"
                                        class="mr-1"
                                    /> Sí</label
                                >
                                {#if piar.centroProteccion === "SI"}
                                    {@render inputGroup(
                                        "¿Dónde?",
                                        "lugarprot",
                                        "text",
                                        "",
                                        "flex-1",
                                        piar,
                                        "lugarProteccion",
                                    )}
                                {/if}
                            </div>
                            {@render inputGroup(
                                "Grado al que aspira",
                                "grado",
                                "text",
                                "Ej: 5° Primaria",
                                "col-span-1",
                                piar,
                                "gradoAspira",
                            )}
                            {@render selectGroup(
                                "Grupo Étnico",
                                "etnia",
                                [
                                    "Ninguno",
                                    "Indígena",
                                    "Afrocolombiano",
                                    "Raizal",
                                    "Palenquero",
                                    "RROM",
                                ],
                                "col-span-1",
                                piar,
                                "grupoEtnico",
                            )}
                            <div
                                class="col-span-1 md:col-span-2 flex items-center gap-4 bg-slate-50 p-3 rounded border"
                            >
                                <span class="text-sm font-medium"
                                    >¿Víctima del conflicto armado?</span
                                >
                                <label
                                    ><input
                                        type="radio"
                                        bind:group={piar.victimaConflicto}
                                        value="No"
                                        class="mr-1"
                                    /> No</label
                                >
                                <label
                                    ><input
                                        type="radio"
                                        bind:group={piar.victimaConflicto}
                                        value="Si"
                                        class="mr-1"
                                    /> Sí</label
                                >
                                <span
                                    class="text-sm font-medium ml-4 border-l pl-4"
                                    >¿Cuenta con registro?</span
                                >
                                <label
                                    ><input
                                        type="radio"
                                        bind:group={piar.registroVictima}
                                        value="No"
                                        class="mr-1"
                                    /> No</label
                                >
                                <label
                                    ><input
                                        type="radio"
                                        bind:group={piar.registroVictima}
                                        value="Si"
                                        class="mr-1"
                                    /> Sí</label
                                >
                            </div>
                        </div>
                    </div>
                {/if}

                <!-- STEP 2: SALUD Y HOGAR -->
                {#if currentStep === 2}
                    <div class="space-y-6 animate-in fade-in">
                        <div class="border-b pb-4 mb-4">
                            <h2 class="text-2xl font-bold text-slate-800">
                                2. Entorno Salud y Hogar
                            </h2>
                        </div>

                        <!-- SALUD -->
                        <h3
                            class="font-semibold text-lg text-blue-800 bg-blue-50 p-2 rounded border-l-4 border-blue-500"
                        >
                            Salud
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div
                                class="col-span-1 flex flex-col gap-2 bg-slate-50 p-3 rounded border"
                            >
                                <span class="text-sm font-medium"
                                    >Afiliación a Salud</span
                                >
                                <div class="flex gap-4">
                                    <label
                                        ><input
                                            type="radio"
                                            bind:group={piar.afiliacionSalud}
                                            value="SI"
                                            class="mr-1"
                                        /> Sí</label
                                    >
                                    <label
                                        ><input
                                            type="radio"
                                            bind:group={piar.afiliacionSalud}
                                            value="NO"
                                            class="mr-1"
                                        /> No</label
                                    >
                                </div>
                                {#if piar.afiliacionSalud === "SI"}
                                    <div class="flex gap-4 mt-2">
                                        <label
                                            ><input
                                                type="radio"
                                                bind:group={piar.regimenSalud}
                                                value="Contributivo"
                                                class="mr-1 text-sm"
                                            /> Contributivo</label
                                        >
                                        <label
                                            ><input
                                                type="radio"
                                                bind:group={piar.regimenSalud}
                                                value="Subsidiado"
                                                class="mr-1 text-sm"
                                            /> Subsidiado</label
                                        >
                                    </div>
                                    {@render inputGroup(
                                        "EPS",
                                        "eps",
                                        "text",
                                        "",
                                        "w-full mt-2",
                                        piar,
                                        "eps",
                                    )}
                                {/if}
                            </div>
                            {@render inputGroup(
                                "Lugar de atención (Emergencia)",
                                "emerg",
                                "text",
                                "",
                                "col-span-1",
                                piar,
                                "lugarEmergencia",
                            )}
                        </div>

                        <div
                            class="space-y-4 bg-yellow-50 p-4 rounded-lg border border-yellow-200"
                        >
                            <h4
                                class="text-sm font-bold text-yellow-800 mb-2 flex items-center gap-2"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                    ><path
                                        fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd"
                                    /></svg
                                >
                                Información Médica / Discapacidad
                            </h4>

                            <!-- Pedagogo Hint: Diagnóstico -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="col-span-1">
                                    <span class="text-sm font-medium block mb-1"
                                        >¿Tiene diagnóstico médico?</span
                                    >
                                    <div class="flex gap-4">
                                        <label
                                            ><input
                                                type="radio"
                                                bind:group={
                                                    piar.tieneDiagnostico
                                                }
                                                value="SI"
                                            /> Sí</label
                                        >
                                        <label
                                            ><input
                                                type="radio"
                                                bind:group={
                                                    piar.tieneDiagnostico
                                                }
                                                value="NO"
                                            /> No</label
                                        >
                                    </div>
                                </div>
                                {#if piar.tieneDiagnostico === "SI"}
                                    <div class="col-span-2">
                                        <div class="flex flex-col gap-1">
                                            <label
                                                for="diag"
                                                class="text-sm font-medium text-slate-700"
                                                >¿Cuál? (Puede escribir o
                                                seleccionar sugerencia)</label
                                            >
                                            <input
                                                list="diag-sugerencias"
                                                class="border border-slate-300 rounded-md p-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none w-full"
                                                bind:value={
                                                    piar.cualDiagnostico
                                                }
                                                placeholder="Ej: Trastorno del Espectro Autista"
                                            />
                                            <datalist id="diag-sugerencias">
                                                {#each diagnosticosComunes as diag}
                                                    <option value={diag}
                                                    ></option>
                                                {/each}
                                            </datalist>
                                        </div>
                                    </div>
                                {/if}
                            </div>

                            <!-- Terapias -->
                            <div class="mt-4 pt-4 border-t border-yellow-200">
                                <span class="text-sm font-medium block mb-2"
                                    >¿Asiste a terapias? (Fonoaudiología, T.
                                    Ocupacional, Psicología)</span
                                >
                                <div class="flex gap-4 mb-3">
                                    <label
                                        ><input
                                            type="radio"
                                            bind:group={piar.asisteTerapias}
                                            value="SI"
                                        /> Sí</label
                                    >
                                    <label
                                        ><input
                                            type="radio"
                                            bind:group={piar.asisteTerapias}
                                            value="NO"
                                        /> No</label
                                    >
                                </div>
                                {#if piar.asisteTerapias === "SI"}
                                    {#each piar.terapias as terapia, i}
                                        <div class="flex gap-2 mb-2">
                                            {@render inputGroup(
                                                `Terapia ${i + 1}`,
                                                `t_${i}`,
                                                "text",
                                                "Ej: Psicología",
                                                "flex-1",
                                                terapia,
                                                "tipo",
                                            )}
                                            {@render inputGroup(
                                                "Frecuencia",
                                                `f_${i}`,
                                                "text",
                                                "Ej: Semanal",
                                                "w-1/3",
                                                terapia,
                                                "frecuencia",
                                            )}
                                        </div>
                                    {/each}
                                {/if}
                            </div>

                            <!-- Medicamentos y Apoyos -->
                            <div
                                class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 pt-4 border-t border-yellow-200"
                            >
                                <div class="flex flex-col gap-2">
                                    <span class="text-sm font-medium"
                                        >¿Consume medicamentos?</span
                                    >
                                    <div class="flex gap-4">
                                        <label
                                            ><input
                                                type="radio"
                                                bind:group={
                                                    piar.consumeMedicamentos
                                                }
                                                value="SI"
                                            /> Sí</label
                                        ><label
                                            ><input
                                                type="radio"
                                                bind:group={
                                                    piar.consumeMedicamentos
                                                }
                                                value="NO"
                                            /> No</label
                                        >
                                    </div>
                                    {#if piar.consumeMedicamentos === "SI"}
                                        {@render inputGroup(
                                            "¿Cuáles y horarios? (Aclarar si es en clase)",
                                            "meds",
                                            "textarea",
                                            "",
                                            "w-full",
                                            piar,
                                            "medicamentos",
                                        )}
                                    {/if}
                                </div>
                                <div class="flex flex-col gap-2">
                                    <span class="text-sm font-medium"
                                        >¿Cuenta con productos de apoyo?
                                        (Movilidad, audífonos, gafas)</span
                                    >
                                    <div class="flex gap-4">
                                        <label
                                            ><input
                                                type="radio"
                                                bind:group={piar.productosApoyo}
                                                value="SI"
                                            /> Sí</label
                                        ><label
                                            ><input
                                                type="radio"
                                                bind:group={piar.productosApoyo}
                                                value="NO"
                                            /> No</label
                                        >
                                    </div>
                                    {#if piar.productosApoyo === "SI"}
                                        {@render inputGroup(
                                            "¿Cuáles?",
                                            "apoyos",
                                            "text",
                                            "",
                                            "w-full",
                                            piar,
                                            "cualesApoyos",
                                        )}
                                    {/if}
                                </div>
                            </div>
                        </div>

                        <!-- HOGAR -->
                        <h3
                            class="font-semibold text-lg mt-8 text-green-800 bg-green-50 p-2 rounded border-l-4 border-green-500"
                        >
                            Hogar y Familia
                        </h3>

                        <div
                            class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-white p-4 border rounded-lg shadow-sm"
                        >
                            {@render inputGroup(
                                "Nombre de la Madre",
                                "madre",
                                "text",
                                "",
                                "col-span-1",
                                piar,
                                "nombreMadre",
                            )}
                            {@render inputGroup(
                                "Ocupación",
                                "oc_madre",
                                "text",
                                "",
                                "col-span-1",
                                piar,
                                "ocupacionMadre",
                            )}
                            {@render selectGroup(
                                "Nivel Educativo",
                                "edu_madre",
                                [
                                    "Primaria",
                                    "Bachillerato",
                                    "Técnico",
                                    "Tecnólogo",
                                    "Universitario",
                                    "Ninguno",
                                ],
                                "col-span-1",
                                piar,
                                "nivelEduMadre",
                            )}

                            {@render inputGroup(
                                "Nombre del Padre",
                                "padre",
                                "text",
                                "",
                                "col-span-1",
                                piar,
                                "nombrePadre",
                            )}
                            {@render inputGroup(
                                "Ocupación",
                                "oc_padre",
                                "text",
                                "",
                                "col-span-1",
                                piar,
                                "ocupacionPadre",
                            )}
                            {@render selectGroup(
                                "Nivel Educativo",
                                "edu_padre",
                                [
                                    "Primaria",
                                    "Bachillerato",
                                    "Técnico",
                                    "Tecnólogo",
                                    "Universitario",
                                    "Ninguno",
                                ],
                                "col-span-1",
                                piar,
                                "nivelEduPadre",
                            )}
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {@render inputGroup(
                                "Nombre Cuidador Principal",
                                "cuid",
                                "text",
                                "",
                                "col-span-1",
                                piar,
                                "nombreCuidador",
                            )}
                            {@render inputGroup(
                                "Parentesco",
                                "par_cuid",
                                "text",
                                "",
                                "col-span-1",
                                piar,
                                "parentescoCuidador",
                            )}
                            {@render selectGroup(
                                "Nivel Edu. Cuidador",
                                "edu_cuid",
                                [
                                    "Primaria",
                                    "Bachillerato",
                                    "Técnico",
                                    "Tecnólogo",
                                    "Universitario",
                                    "Ninguno",
                                ],
                                "col-span-1",
                                piar,
                                "nivelEduCuidador",
                            )}
                            {@render inputGroup(
                                "Teléfono Cuidador",
                                "tel_cuid",
                                "text",
                                "",
                                "col-span-1",
                                piar,
                                "telefonoCuidador",
                            )}
                            {@render inputGroup(
                                "No. Hermanos",
                                "herm",
                                "number",
                                "",
                                "col-span-1",
                                piar,
                                "numHermanos",
                            )}
                            {@render inputGroup(
                                "Lugar que ocupa",
                                "lugar_herm",
                                "text",
                                "Ej: 2 de 3",
                                "col-span-1",
                                piar,
                                "lugarOcupa",
                            )}
                        </div>
                    </div>
                {/if}

                <!-- STEP 3: ENTORNO EDUCATIVO -->
                {#if currentStep === 3}
                    <div class="space-y-6 animate-in fade-in">
                        <div class="border-b pb-4 mb-4">
                            <h2 class="text-2xl font-bold text-slate-800">
                                3. Entorno Educativo
                            </h2>
                        </div>

                        <div
                            class="bg-indigo-50 p-5 rounded-lg border border-indigo-100 space-y-4"
                        >
                            <h3 class="font-bold text-indigo-800">
                                Trayectoria Escolar Previa
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-2">
                                    <span class="text-sm font-medium"
                                        >¿Ha estado vinculado en otra
                                        institución?</span
                                    >
                                    <div class="flex gap-4">
                                        <label
                                            ><input
                                                type="radio"
                                                bind:group={
                                                    piar.vinculadoOtraInstitucion
                                                }
                                                value="SI"
                                            /> Sí</label
                                        ><label
                                            ><input
                                                type="radio"
                                                bind:group={
                                                    piar.vinculadoOtraInstitucion
                                                }
                                                value="NO"
                                            /> No</label
                                        >
                                    </div>
                                    {#if piar.vinculadoOtraInstitucion === "NO"}
                                        {@render inputGroup(
                                            "¿Por qué no?",
                                            "pqno",
                                            "text",
                                            "",
                                            "w-full",
                                            piar,
                                            "porQueNoVinculado",
                                        )}
                                    {:else}
                                        {@render inputGroup(
                                            "¿Cuáles instituciones?",
                                            "inst_prev",
                                            "text",
                                            "",
                                            "w-full",
                                            piar,
                                            "cualesInstituciones",
                                        )}
                                    {/if}
                                </div>

                                <div
                                    class="flex flex-col gap-2 border-l border-indigo-200 pl-4"
                                >
                                    {@render inputGroup(
                                        "Último grado cursado",
                                        "ultg",
                                        "text",
                                        "",
                                        "w-full",
                                        piar,
                                        "ultimoGrado",
                                    )}
                                    <span class="text-sm font-medium mt-2"
                                        >¿Aprobó?</span
                                    >
                                    <div class="flex gap-4">
                                        <label
                                            ><input
                                                type="radio"
                                                bind:group={piar.aprobo}
                                                value="SI"
                                            /> Sí</label
                                        ><label
                                            ><input
                                                type="radio"
                                                bind:group={piar.aprobo}
                                                value="NO"
                                            /> No</label
                                        >
                                    </div>
                                </div>
                            </div>
                            {@render inputGroup(
                                "Observaciones de trayectoria",
                                "obs_tray",
                                "textarea",
                                "Describa brevemente su historia escolar",
                                "w-full",
                                piar,
                                "observacionesGrado",
                            )}
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div
                                class="bg-white p-4 border rounded-lg shadow-sm"
                            >
                                <span class="text-sm font-medium block mb-2"
                                    >¿Se recibe informe pedagógico previo o
                                    PIAR?</span
                                >
                                <div class="flex gap-4 mb-2">
                                    <label
                                        ><input
                                            type="radio"
                                            bind:group={piar.recibeInforme}
                                            value="SI"
                                        /> Sí</label
                                    ><label
                                        ><input
                                            type="radio"
                                            bind:group={piar.recibeInforme}
                                            value="NO"
                                        /> No</label
                                    >
                                </div>
                                {#if piar.recibeInforme === "SI"}
                                    {@render inputGroup(
                                        "¿De qué institución?",
                                        "inst_inf",
                                        "text",
                                        "",
                                        "w-full",
                                        piar,
                                        "institucionInforme",
                                    )}
                                {/if}
                            </div>
                            <div
                                class="bg-white p-4 border rounded-lg shadow-sm"
                            >
                                <span class="text-sm font-medium block mb-2"
                                    >¿Asiste a programas complementarios?
                                    (Deportes, arte)</span
                                >
                                <div class="flex gap-4 mb-2">
                                    <label
                                        ><input
                                            type="radio"
                                            bind:group={
                                                piar.programasComplementarios
                                            }
                                            value="SI"
                                        /> Sí</label
                                    ><label
                                        ><input
                                            type="radio"
                                            bind:group={
                                                piar.programasComplementarios
                                            }
                                            value="NO"
                                        /> No</label
                                    >
                                </div>
                                {#if piar.programasComplementarios === "SI"}
                                    {@render inputGroup(
                                        "¿Cuáles?",
                                        "prog_comp",
                                        "text",
                                        "",
                                        "w-full",
                                        piar,
                                        "cualesProgramas",
                                    )}
                                {/if}
                            </div>
                        </div>

                        <h3 class="font-bold text-slate-700 mt-6 border-b pb-2">
                            Información de matrícula actual
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {@render inputGroup(
                                "Institución Educativa",
                                "ie_act",
                                "text",
                                "",
                                "col-span-1",
                                piar,
                                "institucionActual",
                            )}
                            {@render inputGroup(
                                "Sede",
                                "sede_act",
                                "text",
                                "",
                                "col-span-1",
                                piar,
                                "sedeActual",
                            )}
                            {@render inputGroup(
                                "Medio de transporte escolar",
                                "transp",
                                "text",
                                "Ej: Ruta escolar, a pie, moto...",
                                "col-span-1",
                                piar,
                                "medioTransporte",
                            )}
                            {@render inputGroup(
                                "Distancia Tiempo (Hogar-Sede)",
                                "dist",
                                "text",
                                "Ej: 30 minutos",
                                "col-span-1",
                                piar,
                                "distanciaTiempo",
                            )}
                        </div>
                    </div>
                {/if}

                <!-- STEP 4: VALORACIÓN PEDAGÓGICA (Anexo 2 Inicio) -->
                {#if currentStep === 4}
                    <div class="space-y-6 animate-in fade-in">
                        <div class="border-b pb-4 mb-4">
                            <h2 class="text-2xl font-bold text-slate-800">
                                4. Valoración Pedagógica Inicial
                            </h2>
                            <p class="text-sm text-slate-500">
                                Anexo 2 PIAR: Reconocimiento del estudiante.
                            </p>
                        </div>

                        <!-- Consejo Pedagógico -->
                        <div
                            class="bg-blue-50 border-l-4 border-blue-600 p-4 text-sm text-blue-800 rounded-r shadow-sm"
                        >
                            <p class="font-bold mb-1 flex items-center gap-1">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                    ><path
                                        fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd"
                                    /></svg
                                >
                                Tip Pedagógico (Enfoque de Capacidades)
                            </p>
                            <p>
                                Evita centrarte en el déficit ("No puede, no
                                sabe"). Escribe desde lo que el estudiante <b
                                    >sí logra hacer</b
                                > y sus intereses. Esto será la clave para formular
                                los ajustes en el siguiente paso.
                            </p>
                        </div>

                        <div class="space-y-6">
                            <div class="flex flex-col gap-2">
                                <label
                                    for="desc1"
                                    class="font-semibold text-slate-700"
                                    >1. Descripción general del estudiante</label
                                >
                                <span class="text-xs text-slate-500 block -mt-2"
                                    >Énfasis en gustos, intereses, aspectos que
                                    le desagradan y expectativas.</span
                                >
                                <textarea
                                    id="desc1"
                                    class="w-full border border-slate-300 rounded-md p-3 min-h-[120px] focus:ring-2 focus:ring-blue-500 outline-none"
                                    placeholder="Ej: A Juan le encantan los dinosaurios y dibujar. Se frustra cuando hay mucho ruido en el salón. Su familia espera que mejore su lectura este año..."
                                    bind:value={piar.descripcionEstudiante}
                                ></textarea>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label
                                    for="desc2"
                                    class="font-semibold text-slate-700"
                                    >2. Descripción de habilidades, competencias
                                    y apoyos</label
                                >
                                <span class="text-xs text-slate-500 block -mt-2"
                                    >Indique lo que hace, puede hacer o requiere
                                    apoyo para favorecer su proceso educativo.</span
                                >
                                <textarea
                                    id="desc2"
                                    class="w-full border border-slate-300 rounded-md p-3 min-h-[150px] focus:ring-2 focus:ring-blue-500 outline-none"
                                    placeholder="Ej: Reconoce vocales y números hasta el 10. Logra mantenerse sentado periodos cortos. Requiere apoyo visual constante (pictogramas) para seguir rutinas y apoyo en motricidad fina para el agarre del lápiz..."
                                    bind:value={piar.habilidadesCompetencias}
                                ></textarea>
                            </div>
                        </div>
                    </div>
                {/if}

                <!-- STEP 5: AJUSTES RAZONABLES -->
                {#if currentStep === 5}
                    <div class="space-y-6 animate-in fade-in">
                        <div
                            class="border-b pb-4 mb-4 flex justify-between items-end"
                        >
                            <div>
                                <h2 class="text-2xl font-bold text-slate-800">
                                    5. Ajustes Razonables por Trimestre
                                </h2>
                                <p class="text-sm text-slate-500">
                                    De acuerdo con los DBA (Derechos Básicos de
                                    Aprendizaje)
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-2 overflow-x-auto pb-2 border-b">
                            {#each piar.trimestres as trim, tIndex}
                                <div class="w-full shrink-0 space-y-4">
                                    <h3
                                        class="font-bold text-lg bg-indigo-600 text-white p-2 rounded text-center"
                                    >
                                        {trim.nombre}
                                    </h3>

                                    <div
                                        class="bg-indigo-50 border-l-4 border-indigo-600 p-3 text-xs text-indigo-800 mb-4 rounded shadow-sm"
                                    >
                                        <strong>Guía rápida:</strong><br />
                                        - <b>Barreras:</b> Obstáculos en el
                                        entorno (metodología, material,
                                        actitud).<br />
                                        - <b>Ajustes:</b> Estrategias puntuales (material
                                        concreto, tiempo extra, lectura oral).
                                    </div>

                                    {#each trim.areas as area, aIndex}
                                        <div
                                            class="border border-slate-200 rounded-lg overflow-hidden shadow-sm bg-white"
                                        >
                                            <div
                                                class="bg-slate-100 p-3 font-semibold text-slate-700 border-b flex justify-between items-center"
                                            >
                                                <span>{area.nombre}</span>
                                            </div>
                                            <div
                                                class="p-4 grid grid-cols-1 lg:grid-cols-2 gap-4"
                                            >
                                                <!-- Objetivos -->
                                                <div
                                                    class="col-span-1 lg:col-span-2"
                                                >
                                                    <label
                                                        for={`obj_${tIndex}_${aIndex}`}
                                                        class="block text-xs font-bold text-slate-600 mb-1"
                                                        >Objetivos/Propósitos
                                                        (DBA)</label
                                                    >
                                                    <textarea
                                                        id={`obj_${tIndex}_${aIndex}`}
                                                        rows="2"
                                                        class="w-full text-sm p-2 border rounded bg-slate-50"
                                                        bind:value={
                                                            area.objetivos
                                                        }
                                                        placeholder="Objetivo esperado para el grado..."
                                                    ></textarea>
                                                </div>

                                                <!-- Barreras -->
                                                <div>
                                                    <label
                                                        for={`bar_${tIndex}_${aIndex}`}
                                                        class="block text-xs font-bold text-red-600 mb-1"
                                                        >Barreras en el contexto</label
                                                    >
                                                    <input
                                                        list="barreras-list"
                                                        id={`bar_${tIndex}_${aIndex}`}
                                                        class="w-full text-sm p-2 border border-red-200 rounded bg-red-50 mb-1"
                                                        bind:value={
                                                            area.barreras
                                                        }
                                                        placeholder="Seleccione o escriba..."
                                                    />
                                                    <datalist
                                                        id="barreras-list"
                                                    >
                                                        {#each barrerasComunes as b}
                                                            <option value={b}
                                                            ></option>
                                                        {/each}
                                                    </datalist>
                                                </div>

                                                <!-- Ajustes -->
                                                <div>
                                                    <label
                                                        for={`aju_${tIndex}_${aIndex}`}
                                                        class="block text-xs font-bold text-green-600 mb-1"
                                                        >Ajustes Razonables
                                                        (Estrategias)</label
                                                    >
                                                    <input
                                                        list="ajustes-list"
                                                        id={`aju_${tIndex}_${aIndex}`}
                                                        class="w-full text-sm p-2 border border-green-200 rounded bg-green-50 mb-1"
                                                        bind:value={
                                                            area.ajustes
                                                        }
                                                        placeholder="Seleccione o escriba..."
                                                    />
                                                    <datalist id="ajustes-list">
                                                        {#each ajustesComunes as a}
                                                            <option value={a}
                                                            ></option>
                                                        {/each}
                                                    </datalist>
                                                </div>

                                                <!-- Evaluación -->
                                                <div
                                                    class="col-span-1 lg:col-span-2 mt-2"
                                                >
                                                    <label
                                                        for={`eva_${tIndex}_${aIndex}`}
                                                        class="block text-xs font-bold text-blue-600 mb-1"
                                                        >Evaluación del Ajuste</label
                                                    >
                                                    <textarea
                                                        id={`eva_${tIndex}_${aIndex}`}
                                                        rows="1"
                                                        class="w-full text-sm p-2 border border-blue-200 rounded bg-blue-50"
                                                        bind:value={
                                                            area.evaluacion
                                                        }
                                                        placeholder="Observaciones sobre si el ajuste funcionó..."
                                                    ></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    {/each}
                                </div>
                            {/each}
                        </div>
                        <p class="text-xs text-slate-400 mt-2">
                            * Para agregar más áreas, debe modificarse el código
                            fuente, se muestran las 4 principales sugeridas en
                            formato general.
                        </p>
                    </div>
                {/if}

                <!-- STEP 6: RECOMENDACIONES -->
                {#if currentStep === 6}
                    <div class="space-y-6 animate-in fade-in">
                        <div class="border-b pb-4 mb-4">
                            <h2 class="text-2xl font-bold text-slate-800">
                                6. Recomendaciones para el Plan de Mejoramiento
                            </h2>
                            <p class="text-sm text-slate-500">
                                Acciones institucionales para la eliminación de
                                barreras.
                            </p>
                        </div>

                        <div class="space-y-4">
                            <div class="bg-white p-4 rounded border shadow-sm">
                                <label
                                    for="rec_fam"
                                    class="font-bold text-orange-700 block mb-2"
                                    >Familia / Cuidadores</label
                                >
                                <textarea
                                    id="rec_fam"
                                    class="w-full border rounded p-3 text-sm min-h-[80px]"
                                    bind:value={piar.recomendacionesFamilia}
                                    placeholder="Ej: Mantener rutinas visuales en casa, apoyo en tareas de lectura 20 min diarios."
                                ></textarea>
                            </div>

                            <div class="bg-white p-4 rounded border shadow-sm">
                                <label
                                    for="rec_doc"
                                    class="font-bold text-blue-700 block mb-2"
                                    >Docentes</label
                                >
                                <textarea
                                    id="rec_doc"
                                    class="w-full border rounded p-3 text-sm min-h-[80px]"
                                    bind:value={piar.recomendacionesDocentes}
                                    placeholder="Ej: Implementar Diseño Universal de Aprendizaje (DUA), permitir uso de calculadoras en pruebas."
                                ></textarea>
                            </div>

                            <div class="bg-white p-4 rounded border shadow-sm">
                                <label
                                    for="rec_dir"
                                    class="font-bold text-purple-700 block mb-2"
                                    >Directivos</label
                                >
                                <textarea
                                    id="rec_dir"
                                    class="w-full border rounded p-3 text-sm min-h-[80px]"
                                    bind:value={piar.recomendacionesDirectivos}
                                    placeholder="Ej: Gestionar capacitación en autismo para docentes de la sede."
                                ></textarea>
                            </div>

                            <div class="bg-white p-4 rounded border shadow-sm">
                                <label
                                    for="rec_adm"
                                    class="font-bold text-slate-700 block mb-2"
                                    >Administrativos</label
                                >
                                <textarea
                                    id="rec_adm"
                                    class="w-full border rounded p-3 text-sm min-h-[80px]"
                                    bind:value={
                                        piar.recomendacionesAdministrativos
                                    }
                                    placeholder="Ej: Garantizar acceso a primer piso para estudiante con silla de ruedas."
                                ></textarea>
                            </div>

                            <div class="bg-white p-4 rounded border shadow-sm">
                                <label
                                    for="rec_par"
                                    class="font-bold text-green-700 block mb-2"
                                    >Pares (Compañeros)</label
                                >
                                <textarea
                                    id="rec_par"
                                    class="w-full border rounded p-3 text-sm min-h-[80px]"
                                    bind:value={piar.recomendacionesPares}
                                    placeholder="Ej: Fomentar el programa de 'padrinos' para apoyo en descansos."
                                ></textarea>
                            </div>
                        </div>
                    </div>
                {/if}

                <!-- STEP 7: ACTA DE ACUERDO -->
                {#if currentStep === 7}
                    <div class="space-y-6 animate-in fade-in">
                        <div class="border-b pb-4 mb-4">
                            <h2 class="text-2xl font-bold text-slate-800">
                                7. Acta de Acuerdo (Anexo 3)
                            </h2>
                            <p class="text-sm text-slate-500">
                                Compromisos de la familia y el estudiante.
                            </p>
                        </div>

                        <div
                            class="bg-slate-50 p-5 rounded border text-sm text-slate-700 space-y-4 mb-6"
                        >
                            <p>
                                La educación inclusiva es un proceso
                                permanente... La inclusión solo es posible
                                cuando se unen los esfuerzos del colegio, el
                                estudiante y la familia. (Dec. 1421/2017)
                            </p>

                            <div>
                                <label
                                    for="comp_esp"
                                    class="font-bold block mb-1"
                                    >Compromisos específicos (Aula/Institución):</label
                                >
                                <textarea
                                    id="comp_esp"
                                    class="w-full border rounded p-2 bg-white min-h-[80px]"
                                    bind:value={piar.compromisosEspecificos}
                                    placeholder="Añadir detalles adicionales a los del PIAR..."
                                ></textarea>
                            </div>
                        </div>

                        <h3 class="font-bold text-lg text-slate-800">
                            Compromisos en Casa (Actividades)
                        </h3>
                        <div
                            class="border rounded-lg overflow-hidden shadow-sm"
                        >
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-200">
                                    <tr>
                                        <th class="p-3">Nombre de Actividad</th>
                                        <th class="p-3"
                                            >Descripción de estrategia</th
                                        >
                                        <th class="p-3 w-32">Frecuencia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {#each piar.actividadesCasa as act}
                                        <tr class="border-b">
                                            <td class="p-2"
                                                ><input
                                                    class="w-full border rounded p-1"
                                                    bind:value={act.nombre}
                                                /></td
                                            >
                                            <td class="p-2"
                                                ><input
                                                    class="w-full border rounded p-1"
                                                    bind:value={act.descripcion}
                                                /></td
                                            >
                                            <td class="p-2">
                                                <select
                                                    class="w-full border rounded p-1 bg-white"
                                                    bind:value={act.frecuencia}
                                                >
                                                    <option value="D"
                                                        >Diaria</option
                                                    >
                                                    <option value="S"
                                                        >Semanal</option
                                                    >
                                                    <option value="P"
                                                        >Permanente</option
                                                    >
                                                </select>
                                            </td>
                                        </tr>
                                    {/each}
                                </tbody>
                            </table>
                        </div>

                        <div
                            class="mt-8 p-6 bg-green-50 rounded-xl border-2 border-green-200 text-center space-y-4"
                        >
                            <h3 class="text-xl font-bold text-green-800">
                                ¡PIAR Completado!
                            </h3>
                            <p class="text-green-700 text-sm">
                                Asegúrese de revisar toda la información antes
                                de generar el documento final para impresión o
                                guardado en PDF.
                            </p>
                            <button
                                onclick={generarPDF}
                                disabled={isGeneratingPdf}
                                class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg shadow-lg transition-transform hover:scale-105 disabled:opacity-50"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    ><path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                                    /></svg
                                >
                                Generar PDF Oficial
                            </button>
                        </div>
                    </div>
                {/if}

                <!-- Form Navigation Bottom -->
                <div class="mt-8 flex justify-between border-t pt-4">
                    <button
                        onclick={prevStep}
                        class="px-4 py-2 bg-slate-200 hover:bg-slate-300 rounded font-medium disabled:opacity-30 transition-colors"
                        disabled={currentStep === 1}
                    >
                        Anterior
                    </button>
                    <button
                        onclick={nextStep}
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-bold shadow transition-colors"
                        style={currentStep === steps.length
                            ? "display:none"
                            : ""}
                    >
                        Siguiente Sección
                    </button>
                </div>
            </div>
        </main>
    </div>

    <!-- ==========================================
		 PLANTILLA DE IMPRESIÓN (OCULTA EN PANTALLA, VISIBLE EN PDF)
		 ========================================== -->
    <div
        id="documento-pdf"
        class="hidden print:block bg-white text-black p-8 font-serif text-[11px] leading-tight max-w-[800px] mx-auto"
    >
        <style>
            /* Estilos específicos para forzar la tabla estilo Word al imprimir */
            .pdf-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 15px;
                page-break-inside: avoid;
            }
            .pdf-table th,
            .pdf-table td {
                border: 1px solid #000;
                padding: 4px;
                vertical-align: top;
            }
            .pdf-header {
                background-color: #e5e7eb;
                font-weight: bold;
                text-align: center;
            }
            .section-title {
                background-color: #d1d5db;
                font-weight: bold;
                padding: 5px;
                margin-top: 15px;
                margin-bottom: 5px;
                border: 1px solid #000;
                text-transform: uppercase;
            }
            .page-break {
                page-break-before: always;
            }
        </style>

        <!-- ANEXO 1 -->
        <h1 class="text-center font-bold text-sm mb-4">
            INFORMACIÓN GENERAL DEL ESTUDIANTE <br /> (Información para la matrícula
            – Anexo 1 PIAR)
        </h1>

        <table class="pdf-table">
            <tbody>
                <tr>
                    <td colspan="2"
                        ><b>Fecha y Lugar de Diligenciamiento:</b>
                        {piar.fechaDiligenciamiento}</td
                    >
                </tr>
                <tr>
                    <td
                        ><b>Nombre de la Persona que diligencia:</b>
                        {piar.quienDiligencia}</td
                    >
                    <td><b>Rol:</b> {piar.rol}</td>
                </tr>
            </tbody>
        </table>

        <div class="section-title">1. Información general del estudiante</div>
        <table class="pdf-table">
            <tbody>
                <tr
                    ><td><b>Nombres:</b> {piar.nombres}</td><td colspan="2"
                        ><b>Apellidos:</b> {piar.apellidos}</td
                    ></tr
                >
                <tr
                    ><td><b>Lugar de nacimiento:</b> {piar.lugarNacimiento}</td
                    ><td><b>Edad:</b> {piar.edad}</td><td
                        ><b>Fecha nac:</b> {piar.fechaNacimiento}</td
                    ></tr
                >
                <tr
                    ><td><b>Tipo Doc:</b> {piar.tipoDoc}</td><td colspan="2"
                        ><b>No Identificación:</b> {piar.numDoc}</td
                    ></tr
                >
                <tr
                    ><td><b>Departamento:</b> {piar.departamento}</td><td
                        colspan="2"><b>Municipio:</b> {piar.municipio}</td
                    ></tr
                >
                <tr
                    ><td colspan="2"><b>Dirección:</b> {piar.direccion}</td><td
                        ><b>Barrio/Vereda:</b> {piar.barrio}</td
                    ></tr
                >
                <tr
                    ><td><b>Teléfono:</b> {piar.telefono}</td><td colspan="2"
                        ><b>Correo:</b> {piar.correo}</td
                    ></tr
                >
                <tr
                    ><td colspan="2"
                        ><b>¿Está en centro de protección?</b>
                        {piar.centroProteccion === "SI" ? "SÍ" : "NO"}
                        {piar.centroProteccion === "SI"
                            ? ` (${piar.lugarProteccion})`
                            : ""}</td
                    ><td><b>Grado aspira:</b> {piar.gradoAspira}</td></tr
                >
                <tr
                    ><td colspan="3"
                        ><b>¿Se reconoce o pertenece a grupo étnico?</b>
                        {piar.grupoEtnico}</td
                    ></tr
                >
                <tr
                    ><td colspan="3"
                        ><b>¿Víctima del conflicto armado?</b>
                        {piar.victimaConflicto === "SI" ? "SÍ" : "NO"} |
                        <b>Registro:</b>
                        {piar.registroVictima === "SI" ? "SÍ" : "NO"}</td
                    ></tr
                >
            </tbody>
        </table>

        <div class="section-title">Entorno Salud</div>
        <table class="pdf-table">
            <tbody>
                <tr
                    ><td colspan="2"
                        ><b>Afiliación salud:</b>
                        {piar.afiliacionSalud} | <b>EPS:</b>
                        {piar.eps} | <b>Régimen:</b>
                        {piar.regimenSalud}</td
                    ></tr
                >
                <tr
                    ><td colspan="2"
                        ><b>Lugar atención emergencia:</b>
                        {piar.lugarEmergencia}</td
                    ></tr
                >
                <tr
                    ><td
                        ><b>¿Tiene diagnóstico médico?</b>
                        {piar.tieneDiagnostico}</td
                    ><td><b>Cuál:</b> {piar.cualDiagnostico}</td></tr
                >
                <tr>
                    <td colspan="2">
                        <b>¿Asiste a terapias?</b>
                        {piar.asisteTerapias}<br />
                        {#each piar.terapias as t}
                            {#if t.tipo}
                                - {t.tipo} ({t.frecuencia}) <br />
                            {/if}
                        {/each}
                    </td>
                </tr>
                <tr
                    ><td colspan="2"
                        ><b>¿Consume medicamentos?</b>
                        {piar.consumeMedicamentos} | <b>Cuáles:</b>
                        {piar.medicamentos}</td
                    ></tr
                >
                <tr
                    ><td colspan="2"
                        ><b>¿Productos de apoyo?</b>
                        {piar.productosApoyo} | <b>Cuáles:</b>
                        {piar.cualesApoyos}</td
                    ></tr
                >
            </tbody>
        </table>

        <div class="section-title">Entorno Hogar</div>
        <table class="pdf-table">
            <tbody>
                <tr
                    ><td><b>Madre:</b> {piar.nombreMadre}</td><td
                        ><b>Ocupación:</b> {piar.ocupacionMadre}</td
                    ><td><b>Nivel Edu:</b> {piar.nivelEduMadre}</td></tr
                >
                <tr
                    ><td><b>Padre:</b> {piar.nombrePadre}</td><td
                        ><b>Ocupación:</b> {piar.ocupacionPadre}</td
                    ><td><b>Nivel Edu:</b> {piar.nivelEduPadre}</td></tr
                >
                <tr
                    ><td><b>Cuidador:</b> {piar.nombreCuidador}</td><td
                        ><b>Parentesco:</b> {piar.parentescoCuidador}</td
                    ><td><b>Nivel Edu:</b> {piar.nivelEduCuidador}</td></tr
                >
                <tr
                    ><td colspan="3"
                        ><b>Teléfono Cuidador:</b> {piar.telefonoCuidador}</td
                    ></tr
                >
                <tr
                    ><td colspan="2"><b>No. Hermanos:</b> {piar.numHermanos}</td
                    ><td><b>Lugar que ocupa:</b> {piar.lugarOcupa}</td></tr
                >
            </tbody>
        </table>

        <div class="section-title">Entorno Educativo</div>
        <table class="pdf-table">
            <tbody>
                <tr
                    ><td colspan="2"
                        ><b>¿Vinculado otra institución?</b>
                        {piar.vinculadoOtraInstitucion} | <b>Cuáles:</b>
                        {piar.cualesInstituciones} | <b>Por qué no:</b>
                        {piar.porQueNoVinculado}</td
                    ></tr
                >
                <tr
                    ><td><b>Último grado:</b> {piar.ultimoGrado}</td><td
                        ><b>Aprobó:</b> {piar.aprobo}</td
                    ></tr
                >
                <tr
                    ><td colspan="2"
                        ><b>Observaciones:</b> {piar.observacionesGrado}</td
                    ></tr
                >
                <tr
                    ><td colspan="2"
                        ><b>¿Recibe informe cualitativo/PIAR previo?</b>
                        {piar.recibeInforme} | <b>De dónde:</b>
                        {piar.institucionInforme}</td
                    ></tr
                >
                <tr
                    ><td colspan="2"
                        ><b>Instrucción actual:</b>
                        {piar.institucionActual} | <b>Sede:</b>
                        {piar.sedeActual}</td
                    ></tr
                >
            </tbody>
        </table>

        <div class="page-break"></div>

        <!-- ANEXO 2 -->
        <h1 class="text-center font-bold text-sm mb-4">
            Plan Individual de Ajustes Razonables – PIAR – ANEXO 2
        </h1>

        <table class="pdf-table mb-4">
            <tbody>
                <tr
                    ><td><b>Estudiante:</b> {piar.nombres} {piar.apellidos}</td
                    ><td><b>Doc. Identidad:</b> {piar.numDoc}</td></tr
                >
                <tr
                    ><td><b>Edad:</b> {piar.edad}</td><td
                        ><b>Grado:</b> {piar.gradoAspira}</td
                    ></tr
                >
            </tbody>
        </table>

        <table class="pdf-table">
            <tbody>
                <tr class="pdf-header"
                    ><td
                        >Descripción general del estudiante (Gustos, intereses,
                        desagrados, expectativas)</td
                    ></tr
                >
                <tr
                    ><td class="p-4"
                        >{piar.descripcionEstudiante || "Sin información."}</td
                    ></tr
                >
                <tr class="pdf-header"
                    ><td
                        >Descripción de lo que hace, puede hacer o requiere
                        apoyo (Habilidades/Competencias)</td
                    ></tr
                >
                <tr
                    ><td class="p-4"
                        >{piar.habilidadesCompetencias ||
                            "Sin información."}</td
                    ></tr
                >
            </tbody>
        </table>

        <div class="section-title text-center">
            Ajustes Razonables por Trimestre
        </div>

        {#each piar.trimestres as trim}
            <h3 class="font-bold mt-4 mb-2">{trim.nombre}</h3>
            <table class="pdf-table text-[10px]">
                <thead>
                    <tr class="pdf-header">
                        <th style="width: 15%">ÁREA</th>
                        <th style="width: 25%">OBJETIVOS / PROPÓSITOS</th>
                        <th style="width: 20%">BARRERAS EVIDENCIADAS</th>
                        <th style="width: 20%">AJUSTES RAZONABLES</th>
                        <th style="width: 20%">EVALUACIÓN AJUSTES</th>
                    </tr>
                </thead>
                <tbody>
                    {#each trim.areas as area}
                        <tr>
                            <td class="font-bold align-middle text-center"
                                >{area.nombre}</td
                            >
                            <td>{area.objetivos}</td>
                            <td>{area.barreras}</td>
                            <td>{area.ajustes}</td>
                            <td>{area.evaluacion}</td>
                        </tr>
                    {/each}
                </tbody>
            </table>
        {/each}

        <div class="page-break"></div>

        <div class="section-title">
            7. Recomendaciones Plan de Mejoramiento Institucional
        </div>
        <table class="pdf-table">
            <thead>
                <tr class="pdf-header"
                    ><th style="width: 20%">ACTORES</th><th
                        >ACCIONES / ESTRATEGIAS A IMPLEMENTAR</th
                    ></tr
                >
            </thead>
            <tbody>
                <tr
                    ><td><b>Familia / Cuidadores</b></td><td
                        >{piar.recomendacionesFamilia}</td
                    ></tr
                >
                <tr
                    ><td><b>Docentes</b></td><td
                        >{piar.recomendacionesDocentes}</td
                    ></tr
                >
                <tr
                    ><td><b>Directivos</b></td><td
                        >{piar.recomendacionesDirectivos}</td
                    ></tr
                >
                <tr
                    ><td><b>Administrativos</b></td><td
                        >{piar.recomendacionesAdministrativos}</td
                    ></tr
                >
                <tr
                    ><td><b>Pares (Compañeros)</b></td><td
                        >{piar.recomendacionesPares}</td
                    ></tr
                >
            </tbody>
        </table>

        <div class="mt-8">
            <p><b>Firmas de quienes realizan la valoración:</b></p>
            <div class="flex justify-between mt-12 px-10">
                <div class="border-t border-black pt-2 w-48 text-center">
                    Docente / Apoyo
                </div>
                <div class="border-t border-black pt-2 w-48 text-center">
                    Coordinador
                </div>
            </div>
        </div>

        <div class="page-break"></div>

        <!-- ANEXO 3 -->
        <h1 class="text-center font-bold text-sm mb-4">
            ACTA DE ACUERDO <br /> Plan Individual de Ajustes Razonables – PIAR –
            ANEXO 3
        </h1>
        <p class="text-justify mb-4">
            Según el Decreto 1421 de 2017 la educación inclusiva es un proceso
            permanente que reconoce, valora y responde a la diversidad... La
            inclusión solo es posible cuando se unen los esfuerzos del colegio,
            el estudiante y la familia.
        </p>

        <p class="mb-2"><b>La Familia se compromete a:</b></p>
        <div class="border p-2 min-h-[60px] mb-4">
            {piar.compromisosEspecificos}
        </div>

        <p class="mb-2">
            <b>Y en casa apoyará con las siguientes actividades:</b>
        </p>
        <table class="pdf-table">
            <thead>
                <tr class="pdf-header"
                    ><th>Nombre de la Actividad</th><th
                        >Descripción de la estrategia</th
                    ><th>Frecuencia (D/S/P)</th></tr
                >
            </thead>
            <tbody>
                {#each piar.actividadesCasa as act}
                    {#if act.nombre}
                        <tr
                            ><td>{act.nombre}</td><td>{act.descripcion}</td><td
                                class="text-center">{act.frecuencia}</td
                            ></tr
                        >
                    {/if}
                {/each}
            </tbody>
        </table>

        <div class="mt-16">
            <p><b>Firma de los Actores comprometidos:</b></p>
            <div class="grid grid-cols-2 gap-12 mt-12">
                <div class="border-t border-black pt-2 text-center">
                    Acudiente / Familia
                </div>
                <div class="border-t border-black pt-2 text-center">
                    Docente de Aula
                </div>
                <div class="border-t border-black pt-2 text-center mt-12">
                    Directivo Docente
                </div>
                <div class="border-t border-black pt-2 text-center mt-12">
                    Docente de Apoyo (Opcional)
                </div>
            </div>
        </div>
    </div>
</div>
