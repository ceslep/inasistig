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
        savePlaneadorLocal,
        getPlaneadoresLocales,
        getPlaneadorLocal,
        deletePlaneadorLocal,
        clearPlaneadoresLocales,
        exportPlaneadoresLocales,
        importPlaneadoresLocales,
        type PlaneadorLocal,
        uploadTemasDocente,
        getTemasDocente,
        type TemaDocenteEntry,
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

    // Local Planeaciones
    let showLocalPanel = $state(false);
    let planeacionesLocales = $state<PlaneadorLocal[]>([]);
    let fileInputRef: HTMLInputElement | undefined = undefined;

    // PDF Preview
    let showPdfPreview = $state(false);
    let pdfUrl = $state<string | null>(null);
    let isGeneratingPdf = $state(false);

    // Temas del Docente (JSON)
    let isLoadingTemas = $state(false);
    let isUploadingTemas = $state(false);
    let temasDocente = $state<TemaDocenteEntry[]>([]);
    let temasJsonInputRef: HTMLInputElement | undefined = undefined;
    let selectedTemas = $state<string[]>([]);
    let selectedActividades = $state<string[]>([]);
    let showTemasSection = $state(true);

    // Firma del docente
    let showFirmaModal = $state(false);
    let firmaCanvas = $state<HTMLCanvasElement | null>(null);

    // Inicializar canvas de firma cuando se abre el modal
    $effect(() => {
        if (showFirmaModal && firmaCanvas) {
            const canvas = firmaCanvas;
            setTimeout(() => {
                const ctx = canvas.getContext("2d");
                if (ctx) {
                    ctx.lineWidth = 2.5;
                    ctx.lineCap = "round";
                    ctx.strokeStyle = "#1e293b";

                    let drawing = false;

                    const getPos = (e: MouseEvent | TouchEvent) => {
                        const rect = canvas.getBoundingClientRect();
                        const scaleX = canvas.width / rect.width;
                        const scaleY = canvas.height / rect.height;
                        const clientX = 'touches' in e ? e.touches[0].clientX : (e as MouseEvent).clientX;
                        const clientY = 'touches' in e ? e.touches[0].clientY : (e as MouseEvent).clientY;
                        return {
                            x: (clientX - rect.left) * scaleX,
                            y: (clientY - rect.top) * scaleY,
                        };
                    };

                    const start = (e: MouseEvent | TouchEvent) => {
                        e.preventDefault();
                        drawing = true;
                        ctx.beginPath();
                        const p = getPos(e);
                        ctx.moveTo(p.x, p.y);
                    };

                    const move = (e: MouseEvent | TouchEvent) => {
                        if (!drawing) return;
                        e.preventDefault();
                        const p = getPos(e);
                        ctx.lineTo(p.x, p.y);
                        ctx.stroke();
                    };

                    const stop = () => {
                        drawing = false;
                    };

                    canvas.onmousedown = start;
                    canvas.onmousemove = move;
                    canvas.onmouseup = stop;
                    canvas.onmouseleave = stop;
                    canvas.ontouchstart = start;
                    canvas.ontouchmove = move;
                    canvas.ontouchend = stop;
                }
            }, 100);
        }
    });

    const guardarFirma = (): void => {
        if (firmaCanvas) {
            const canvas = firmaCanvas as HTMLCanvasElement;
            const dataUrl = canvas.toDataURL("image/png");
            formData.firma_docente = dataUrl;
            formData.fecha_firma = new Date().toISOString();
        }
        showFirmaModal = false;
    };

    const limpiarFirma = (): void => {
        if (firmaCanvas) {
            const canvas = firmaCanvas as HTMLCanvasElement;
            const ctx = canvas.getContext("2d");
            if (ctx) ctx.clearRect(0, 0, canvas.width, canvas.height);
        }
    };

    const loadPlaneacionesLocales = (): void => {
        planeacionesLocales = getPlaneadoresLocales();
    };

    const handleExportLocal = (): void => {
        exportPlaneadoresLocales();
        Swal.fire({
            icon: "success",
            title: "Exportado",
            text: "Planeaciones exportadas como JSON",
            timer: 2000,
        });
    };

    const handleImportLocal = (): void => {
        fileInputRef?.click();
    };

    const handleFileSelect = async (event: Event): Promise<void> => {
        const input = event.target as HTMLInputElement;
        const file = input.files?.[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            const jsonData = e.target?.result as string;
            const result = importPlaneadoresLocales(jsonData);
            loadPlaneacionesLocales();
            Swal.fire({
                icon: result.success ? "success" : "error",
                title: result.success ? "Importado" : "Error",
                text: result.message,
            });
        };
        reader.readAsText(file);
        input.value = "";
    };

    const handleDeleteLocal = async (id_local: string): Promise<void> => {
        const result = await Swal.fire({
            icon: "warning",
            title: "Eliminar",
            text: "¿Está seguro de eliminar esta planeación local?",
            showCancelButton: true,
            confirmButtonText: "Eliminar",
            cancelButtonText: "Cancelar",
            confirmButtonColor: "#ef4444",
        });

        if (result.isConfirmed) {
            deletePlaneadorLocal(id_local);
            loadPlaneacionesLocales();
            Swal.fire({
                icon: "success",
                title: "Eliminado",
                text: "Planeación eliminada",
                timer: 1500,
            });
        }
    };

    const handleClearAllLocal = async (): Promise<void> => {
        const result = await Swal.fire({
            icon: "warning",
            title: "Limpiar todo",
            text: "¿Está seguro de eliminar todas las planeaciones locales?",
            showCancelButton: true,
            confirmButtonText: "Limpiar todo",
            cancelButtonText: "Cancelar",
            confirmButtonColor: "#ef4444",
        });

        if (result.isConfirmed) {
            clearPlaneadoresLocales();
            loadPlaneacionesLocales();
            Swal.fire({
                icon: "success",
                title: "Limpiado",
                text: "Todas las planeaciones locales eliminadas",
                timer: 1500,
            });
        }
    };

    const loadPlaneacionLocal = (planeacion: PlaneadorLocal): void => {
        formData.docente = planeacion.docente;
        formData.grado = planeacion.grade;
        formData.subject = planeacion.subject;
        formData.period = planeacion.period;
        formData.dba = planeacion.dba;
        formData.standard = planeacion.standard;
        formData.competency = planeacion.competency;
        formData.has_piar = planeacion.has_piar;
        formData.piar_description = planeacion.piar_description;
        formData.exploration = planeacion.exploration;
        formData.structuring = planeacion.structuring;
        formData.practice = planeacion.practice;
        formData.transfer = planeacion.transfer;
        formData.assessment_moment = planeacion.assessment_moment;
        formData.eval_criteria = planeacion.eval_criteria;
        formData.eval_evidence = planeacion.eval_evidence;
        formData.eval_type = planeacion.eval_type;
        formData.resources = planeacion.resources;
        
        showLocalPanel = false;
        currentStep = 0;
        
        Swal.fire({
            icon: "success",
            title: "Cargada",
            text: "Planeación local cargada para edición",
            timer: 2000,
        });
    };

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
        learning_objectives: "",
        competencias: "",
        exploration: "",
        exploration_activities: [] as string[],
        structuring: "",
        structuring_activities: [] as string[],
        practice: "",
        practice_activities: [] as string[],
        transfer: "",
        transfer_activities: [] as string[],
        assessment_moment: "",
        assessment_activities: [] as string[],
        indicadores_logro: "",
        
        // Tiempos estimados (en minutos)
        tiempo_exploracion: 10,
        tiempo_estructuracion: 20,
        tiempo_practica: 25,
        tiempo_transferencia: 15,
        tiempo_valoracion: 10,

        // 5. Evaluación y Recursos (MEJORADA)
        eval_type: "Formativa",
        eval_modalidades: [] as string[],
        eval_instrumentos: [] as string[],
        eval_criterios: [] as string[],
        eval_evidencias: [] as string[],
        eval_criteria: "",
        eval_evidence: "",
        eval_ponderacion_conceptos: 30,
        eval_ponderacion_procedimientos: 40,
        eval_ponderacion_actitudes: 30,
        eval_descripcion_auto: "",
        
        // 6. Temporalidad (Paso nuevo)
        resources: "",
        planeacion_tipo: "",
        periodo_academico: "",
        fecha_inicio: "",
        fecha_fin: "",
        
        // 7. Firma del docente
        firma_docente: "",
        fecha_firma: "",
    });

    // --- ACTIVIDADES SUGERIDAS POR MOMENTO (Normativa MEN) ---
    interface ActividadSugerida {
        id: string;
        icono: string;
        titulo: string;
        descripcion: string;
        plantilla: string;
    }

    const ACTIVIDADES_EXPLORACION: ActividadSugerida[] = [
        { id: "preguntas", icono: "🎯", titulo: "Preguntas generadoras", descripcion: "Preguntas que despiertan el interés y activan conocimientos previos", plantilla: "Se plantea la pregunta generadora: ¿Qué sabemos sobre" },
        { id: "video", icono: "📺", titulo: "Video introductorio", descripcion: "Recurso audiovisual para introducir el tema", plantilla: "Se presenta un video sobre " },
        { id: "lluvia", icono: "💡", titulo: "Lluvia de ideas", descripcion: "Recuperación colectiva de saberes previos", plantilla: "Se realiza una lluvia de ideas sobre " },
        { id: "imagenes", icono: "🖼️", titulo: "Análisis de imágenes", descripcion: "Imágenes o fotografías para generar discusión", plantilla: "Se muestran imágenes relacionadas con " },
        { id: "juego", icono: "🎮", titulo: "Dinámica/Juego", descripcion: "Actividad lúdica para motivar el aprendizaje", plantilla: "Se realiza una dinámica para " },
        { id: "situacion", icono: "📌", titulo: "Situación problema", descripcion: "Presentación de un problema real o simulado", plantilla: "Se presenta una situación problemática relacionada con " },
        { id: "lectura", icono: "📖", titulo: "Lectura inicial", descripcion: "Texto breve para explorar conocimientos", plantilla: "Se realiza una lectura exploratory sobre " },
        { id: "experiencia", icono: "🗣️", titulo: "Experiencias previas", descripcion: "Diálogo sobre experiencias personales relacionadas", plantilla: "Se invita a compartir experiencias previas sobre " },
    ];

    const ACTIVIDADES_ESTRUCTURACION: ActividadSugerida[] = [
        { id: "exposicion", icono: "📝", titulo: "Exposición magistral", descripcion: "Explicación del docente con apoyo de recursos", plantilla: "El docente explica los conceptos de " },
        { id: "mapa", icono: "🗺️", titulo: "Mapa conceptual", descripcion: "Construcción de mapa conceptual conjunto", plantilla: "Se construye un mapa conceptual sobre " },
        { id: "demostracion", icono: "🔬", titulo: "Demostración", descripcion: "Demostración práctica del concepto", plantilla: "Se realiza una demostración de " },
        { id: "lectura_guiada", icono: "📖", titulo: "Lectura guiada", descripcion: "Lectura de texto con orientación del docente", plantilla: "Se realiza lectura guiada de " },
        { id: "simulacion", icono: "💻", titulo: "Simulación/TIC", descripcion: "Uso de herramientas digitales para ejemplificar", plantilla: "Se utiliza simulación digital para " },
        { id: "ejemplo", icono: "✏️", titulo: "Ejemplificación", descripcion: "Ejemplos concretos del concepto", plantilla: "Se presentan ejemplos de " },
        { id: "analogia", icono: "🔗", titulo: "Analogías", descripcion: "Explicación mediante analogías conocidas", plantilla: "Se establecen analogías con " },
        { id: "definicion", icono: "📋", titulo: "Definición formal", descripcion: "Presentación de definiciones y terminología", plantilla: "Se definen los términos: " },
    ];

    const ACTIVIDADES_PRACTICA: ActividadSugerida[] = [
        { id: "ejercicios", icono: "✏️", titulo: "Ejercicios individuales", descripcion: "Práctica individual con ejercicios", plantilla: "Los estudiantes resuelven ejercicios de " },
        { id: "trabajo_grupo", icono: "👥", titulo: "Trabajo colaborativo", descripcion: "Actividad en pequeños grupos", plantilla: "Se trabaja en grupos para " },
        { id: "investigacion", icono: "🔍", titulo: "Investigación", descripcion: "Búsqueda de información complementaria", plantilla: "Los estudiantes investigan sobre " },
        { id: "proyecto", icono: "🎨", titulo: "Proyecto creativo", descripcion: "Creación de producto artístico o académico", plantilla: "Los estudiantes crean un proyecto sobre " },
        { id: "caso", icono: "📊", titulo: "Análisis de casos", descripcion: "Estudio de casos reales o simulados", plantilla: "Se analizan casos relacionados con " },
        { id: "laboratorio", icono: "🧪", titulo: "Laboratorio", descripcion: "Práctica experimental", plantilla: "Se realiza práctica de laboratorio sobre " },
        { id: "debate", icono: "🎤", titulo: "Debate/Discusión", descripcion: "Discusión guiada sobre el tema", plantilla: "Se genera un debate sobre " },
        { id: "taller", icono: "🔧", titulo: "Taller práctico", descripcion: "Aplicación práctica de conocimientos", plantilla: "Se desarrolla taller de " },
    ];

    const ACTIVIDADES_TRANSFERENCIA: ActividadSugerida[] = [
        { id: "problema_real", icono: "🌎", titulo: "Problema real", descripcion: "Aplicación a situaciones de la vida real", plantilla: "Los estudiantes resuelven un problema real: " },
        { id: "exposicion", icono: "🎤", titulo: "Exposición oral", descripcion: "Presentación de resultados al grupo", plantilla: "Los estudiantes exponen sus hallazgos sobre " },
        { id: "producto", icono: "📄", titulo: "Producto final", descripcion: "Elaboración de producto tangible", plantilla: "Se elabora el producto: " },
        { id: "tarea", icono: "🏠", titulo: "Tarea para casa", descripcion: "Actividad para continuar en casa", plantilla: "Se asigna tarea relacionada con " },
        { id: "servicio", icono: "🤝", titulo: "Servicio comunitario", descripcion: "Aplicación en beneficio de la comunidad", plantilla: "Se vincula con la comunidad para " },
        { id: "entrevista", icono: "🗣️", titulo: "Entrevista", descripcion: "Recolección de información externa", plantilla: "Los estudiantes realizan entrevistas sobre " },
        { id: "experimento", icono: "🔬", titulo: "Experimento", descripcion: "Experimentación autónoma", plantilla: "Los estudiantes experimentan con " },
        { id: "presentacion", icono: "📽️", titulo: "Presentación digital", descripcion: "Creación de presentación multimedia", plantilla: "Se crea presentación digital sobre " },
    ];

    const ACTIVIDADES_VALORACION: ActividadSugerida[] = [
        { id: "autoeval", icono: "📝", titulo: "Autoevaluación", descripcion: "Reflexión del estudiante sobre su propio aprendizaje", plantilla: "Los estudiantes reflexionan sobre lo aprendido: ¿Qué aprendí?" },
        { id: "coeval", icono: "👥", titulo: "Coevaluación", descripcion: "Evaluación entre compañeros", plantilla: "Se realiza coevaluación del trabajo en equipo" },
        { id: "metacog", icono: "💭", titulo: "Metacognición", descripcion: "Reflexión sobre el proceso de aprendizaje", plantilla: "Se promueve la metacognición: ¿Cómo aprendí?" },
        { id: "rubrica", icono: "📋", titulo: "Rúbrica", descripcion: "Evaluación con criterios definidos", plantilla: "Se evalúa según rúbrica de " },
        { id: "portafolio", icono: "📁", titulo: "Portafolio", descripcion: "Recopilación de evidencias de aprendizaje", plantilla: "Se recopilfan evidencias en portafolio" },
        { id: "cierre", icono: "✅", titulo: "Síntesis/Cierre", descripcion: "Resumen de aprendizajes logrados", plantilla: "Se realiza síntesis de los aprendizajes: " },
        { id: "retroalimentacion", icono: "💬", titulo: "Retroalimentación", descripcion: "Retroalimentación grupal e individual", plantilla: "Se ofrece retroalimentación sobre " },
        { id: "prueba", icono: "📋", titulo: "Prueba corta", descripcion: "Evaluación breve de conocimientos", plantilla: "Se aplica prueba corta de " },
    ];

    // --- DATOS PARA EVALUACIÓN (Decreto 1290) ---
    
    // Tipos de evaluación
    const TIPOS_EVALUACION = [
        { id: "diagnostica", icono: "🔍", titulo: "Diagnóstica", descripcion: "Identifica conocimientos previos al inicio", color: "blue" },
        { id: "formativa", icono: "📈", titulo: "Formativa", descripcion: "Evalúa durante el proceso de aprendizaje", color: "green" },
        { id: "sumativa", icono: "🎯", titulo: "Sumativa", descripcion: "Evalúa resultados al final del período", color: "purple" },
    ];

    // Modalidades de evaluación (Decreto 1290)
    const MODALIDADES_EVALUACION = [
        { id: "heteroevaluacion", icono: "👨‍🏫", titulo: "Heteroevaluación", descripcion: "El docente evalúa al estudiante" },
        { id: "coevaluacion", icono: "👥", titulo: "Coevaluación", descripcion: "Los estudiantes se evalúan entre sí" },
        { id: "autoevaluacion", icono: "🙋", titulo: "Autoevaluación", descripcion: "El estudiante se evalúa a sí mismo" },
    ];

    // Instrumentos de evaluación
    const INSTRUMENTOS_EVALUACION = [
        { id: "rubrica", icono: "📋", titulo: "Rúbrica", descripcion: "Criterios con niveles de desempeño" },
        { id: "lista_chequeo", icono: "☑️", titulo: "Lista de chequeo", descripcion: "Verificación de criterios binaria" },
        { id: "escala_valoracion", icono: "📊", titulo: "Escala de valoración", descripcion: "Niveles cualitativos o numéricos" },
        { id: "prueba_escrita", icono: "📝", titulo: "Prueba escrita", descripcion: "Examen escrito de conocimientos" },
        { id: "proyecto", icono: "🎨", titulo: "Proyecto", descripcion: "Trabajo extendedo" },
        { id: "portafolio", icono: "📁", titulo: "Portafolio", descripcion: "Colección de evidencias" },
        { id: "exposicion_oral", icono: "🎤", titulo: "Exposición oral", descripcion: "Presentación verbal" },
        { id: "mapa_conceptual", icono: "🗺️", titulo: "Mapa conceptual", descripcion: "Representación visual de conceptos" },
        { id: "ensayo", icono: "✍️", titulo: "Ensayo", descripcion: "Texto argumentativo" },
        { id: "cuaderno_campo", icono: "📓", titulo: "Cuaderno de campo", descripcion: "Registro de observaciones" },
        { id: "diario_reflexivo", icono: "📓", titulo: "Diario reflexivo", descripcion: "Reflexión escrita del proceso" },
        { id: "video", icono: "🎬", titulo: "Producción audiovisual", descripcion: "Video o contenido multimedia" },
    ];

    // Criterios de evaluación
    const CRITERIOS_EVALUACION = [
        { id: "comprension_conceptual", icono: "💡", titulo: "Comprensión conceptual", descripcion: "Entiende y explica conceptos" },
        { id: "aplicacion", icono: "🔧", titulo: "Aplicación", descripcion: "Utiliza conocimientos en situaciones nuevas" },
        { id: "analisis_critico", icono: "🔍", titulo: "Análisis crítico", descripcion: "Descompone y examina información" },
        { id: "creatividad", icono: "🎨", titulo: "Creatividad", descripcion: "Propone soluciones innovadoras" },
        { id: "colaboracion", icono: "🤝", titulo: "Colaboración", descripcion: "Trabaja efectivamente en equipo" },
        { id: "comunicacion", icono: "💬", titulo: "Comunicación", descripcion: "Expresa ideas con claridad" },
        { id: "investigacion", icono: "🔬", titulo: "Investigación", descripcion: "Busca y procesa información" },
        { id: "pensamiento_logico", icono: "🧠", titulo: "Pensamiento lógico", descripcion: "Razona de manera estructurada" },
        { id: "actitudinal", icono: "❤️", titulo: "Competencia actitudinal", descripcion: "Valores y actitudes" },
        { id: "autonomia", icono: "🚀", titulo: "Autonomía", descripcion: "Trabaja de forma independiente" },
    ];

    // Evidencias de aprendizaje
    const EVIDENCIAS_APRENDIZAJE = [
        { id: "mapa_conceptual", icono: "🗺️", titulo: "Mapa conceptual" },
        { id: "exposicion_oral", icono: "🎤", titulo: "Exposición oral" },
        { id: "proyecto_final", icono: "🎨", titulo: "Proyecto final" },
        { id: "prueba_escrita", icono: "📝", titulo: "Prueba escrita" },
        { id: "portafolio", icono: "📁", titulo: "Portafolio" },
        { id: "ensayo", icono: "✍️", titulo: "Ensayo" },
        { id: "video", icono: "🎬", titulo: "Producción audiovisual" },
        { id: "informe", icono: "📄", titulo: "Informe técnico" },
        { id: "presentacion_digital", icono: "📽️", titulo: "Presentación digital" },
        { id: "laboratorio", icono: "🧪", titulo: "Informe de laboratorio" },
        { id: "diario_reflexivo", icono: "📓", titulo: "Diario reflexivo" },
        { id: "trabajo_grupo", icono: "👥", titulo: "Trabajo colaborativo" },
    ];

    // Escala de valoración Decreto 1290
    const ESCALA_VALORACION = [
        { valor: 1.0, label: "Bajo", descripcion: "No alcanza los aprendizajes básicos" },
        { valor: 2.0, label: "Básico", descripcion: "Alcanza los aprendizajes mínimos" },
        { valor: 3.0, label: "Satisfactorio", descripcion: "Alcanza los aprendizajes esperados" },
        { valor: 4.0, label: "Alto", descripcion: "Supera los aprendizajes esperados" },
        { valor: 5.0, label: "Superior", descripcion: "Demuestra excelencianlos aprendizajes" },
    ];

    // --- DATOS PARA RECURSOS DIDÁCTICOS ---
    
    // Categorías de recursos
    const CATEGORIAS_RECURSOS = [
        { id: "tecnologicos", icono: "💻", titulo: "Tecnológicos", descripcion: "Equipos y herramientas digitales" },
        { id: "impresos", icono: "📄", titulo: "Impresos", descripcion: "Materiales escritos y fotocopiados" },
        { id: "audiovisuales", icono: "📺", titulo: "Audiovisuales", descripcion: "Videos, presentaciones y audio" },
        { id: "bibliograficos", icono: "📚", titulo: "Bibliográficos", descripcion: "Libros y publicaciones" },
        { id: "laboratorio", icono: "🧪", titulo: "Laboratorio", descripcion: "Materiales científicos" },
        { id: "ludicos", icono: "🎮", titulo: "Juegos/Lúdicos", descripcion: "Juegos educativos" },
        { id: "entorno", icono: "🌳", titulo: "Entorno", descripcion: "Recursos del medio ambiente" },
        { id: "colombia_aprende", icono: "🇨🇴", titulo: "Colombia Aprende", descripcion: "Recursos oficiales del MEN" },
    ];

    // Recursos por categoría
    const RECURSOS_POR_CATEGORIA: Record<string, { id: string; icono: string; titulo: string; descripcion: string }[]> = {
        tecnologicos: [
            { id: "computador", icono: "💻", titulo: "Computador/Tablet", descripcion: "Equipo de cómputo para actividades" },
            { id: "video_beam", icono: "📽️", titulo: "Video Beam/Proyector", descripcion: "Equipo de proyección" },
            { id: "internet", icono: "🌐", titulo: "Internet/WiFi", descripcion: "Conexión a internet" },
            { id: "classroom", icono: "🏫", titulo: "Google Classroom", descripcion: "Plataforma educativa Google" },
            { id: "moodle", icono: "🎓", titulo: "Moodle", descripcion: "Plataforma LMS institucional" },
            { id: "teams", icono: "👥", titulo: "Microsoft Teams", descripcion: "Plataforma de Microsoft" },
            { id: "kahoot", icono: "🎯", titulo: "Kahoot/Quizizz", descripcion: "Herramientas de evaluación gamificada" },
            { id: "canva", icono: "🎨", titulo: "Canva/Genially", descripcion: "Diseño gráfico digital" },
            { id: "excel", icono: "📊", titulo: "Excel/Word", descripcion: "Paquete de oficina" },
            { id: "youtube", icono: "▶️", titulo: "YouTube/Educaplay", descripcion: "Videos educativos" },
        ],
        impresos: [
            { id: "guias", icono: "📄", titulo: "Guías de trabajo", descripcion: "Hojas de actividades" },
            { id: "fichas", icono: "📋", titulo: "Fichas de actividades", descripcion: "Tarjetas con ejercicios" },
            { id: "cartillas", icono: "📚", titulo: "Cartillas", descripcion: "Material educativo impreso" },
            { id: "hojas_ejercicios", icono: "✏️", titulo: "Hojas de ejercicios", descripcion: "Ejercicios impresos" },
            { id: "rubricas", icono: "📝", titulo: "Rúbricas impresas", descripcion: "Criterios de evaluación" },
            { id: "fotocopias", icono: "🖨️", titulo: "Fotocopias", descripcion: "Material fotocopiado" },
            { id: "cuaderno_actividades", icono: "📓", titulo: "Cuaderno de actividades", descripcion: "Cuaderno laboral" },
            { id: "laminas", icono: "🖼️", titulo: "Láminas/Afiches", descripcion: "Material visual grande" },
        ],
        audiovisuales: [
            { id: "videos", icono: "🎬", titulo: "Videos educativos", descripcion: "Contenido audiovisual" },
            { id: "presentaciones", icono: "📽️", titulo: "Presentaciones", descripcion: "PowerPoint/Canva" },
            { id: "documentales", icono: "🎥", titulo: "Documentales", descripcion: "Videos documentales" },
            { id: "podcasts", icono: "🎧", titulo: "Podcasts/Audio", descripcion: "Contenido de audio" },
            { id: "canciones", icono: "🎵", titulo: "Canciones educativas", descripcion: "Música con contenido" },
            { id: "imagenes", icono: "🖼️", titulo: "Imágenes/Fotografías", descripcion: "Recursos visuales" },
            { id: "animaciones", icono: "✨", titulo: "Animaciones", descripcion: "Contenido animado" },
        ],
        bibliograficos: [
            { id: "libro_texto", icono: "📖", titulo: "Libro de texto", descripcion: "Texto escolar oficial" },
            { id: "diccionario", icono: "📕", titulo: "Diccionario", descripcion: "Diccionario de la lengua" },
            { id: "enciclopedia", icono: "📗", titulo: "Enciclopedia", descripcion: "Referencia general" },
            { id: "prensa", icono: "📰", titulo: "Artículo de prensa", descripcion: "Noticias y artículos" },
            { id: "revistas", icono: "📒", titulo: "Revistas académicas", descripcion: "Publicaciones científicas" },
            { id: "lecturas", icono: "📚", titulo: "Lecturas complementarias", descripcion: "Textos adicionales" },
            { id: "comics", icono: "📓", titulo: "Cómics/Libros ilustrados", descripcion: "Narrativa visual" },
        ],
        laboratorio: [
            { id: "material_didactico", icono: "🔬", titulo: "Material didáctico", descripcion: "Materiales de apoyo" },
            { id: "instrumentos", icono: "📏", titulo: "Instrumentos de medición", descripcion: "Regla, transportador, etc." },
            { id: "reactivos", icono: "🧪", titulo: "Reactivos/Químicos", descripcion: "Materiales de química" },
            { id: "modelos", icono: "🧬", titulo: "Modelo anatómico", descripcion: "Réplicas del cuerpo" },
            { id: "microscopio", icono: "🔍", titulo: "Microscopio", descripcion: "Equipo óptico" },
            { id: "muestras", icono: "🦠", titulo: "Specimen/Muestras", descripcion: "Muestras biológicas" },
            { id: "balanza", icono: "⚖️", titulo: "Balanza", descripcion: "Instrumento de peso" },
        ],
        ludicos: [
            { id: "juegos_mesa", icono: "🎲", titulo: "Juegos de mesa", descripcion: "Juegos de tablero" },
            { id: "flashcards", icono: "🃏", titulo: "Flashcards/Tarjetas", descripcion: "Tarjetas de memoria" },
            { id: "domino", icono: "🎯", titulo: "Dominó educativo", descripcion: "Dominó temático" },
            { id: "tangram", icono: "🧩", titulo: "Tangram/Geoplano", descripcion: "Material geométrico" },
            { id: "rompecabezas", icono: "🧩", titulo: "Rompecabezas", descripcion: "Piezas encajables" },
            { id: "dados", icono: "🎲", titulo: "Dados/Juegos de azar", descripcion: "Dados temáticos" },
            { id: " Lego", icono: "🧱", titulo: "Lego/Bloques", descripcion: "Construcción" },
        ],
        entorno: [
            { id: "materiales_naturales", icono: "🍂", titulo: "Materiales naturales", descripcion: "Recursos naturales" },
            { id: "plantas", icono: "🌱", titulo: "Planta/Material vegetal", descripcion: "Recursos vegetales" },
            { id: "mapas", icono: "🗺️", titulo: "Mapa/Globo terráqueo", descripcion: "Recursos geográficos" },
            { id: "campo", icono: "🌳", titulo: "Campo/Jardín escolar", descripcion: "Espacio exterior" },
            { id: "minerales", icono: "🪨", titulo: "Minerales/Rocas", descripcion: "Recursos geológicos" },
            { id: "herramientas", icono: "🔧", titulo: "Herramientas básicas", descripcion: "Utensilios" },
        ],
        colombia_aprende: [
            { id: "plataforma_colombia", icono: "🌐", titulo: "Plataforma Colombia Aprende", descripcion: "Portal oficial MEN" },
            { id: "recursos_men", icono: "📚", titulo: "Recursos educativos MEN", descripcion: "Materiales oficiales" },
            { id: "conectividad", icono: "📶", titulo: "Conectividad escolar", descripcion: "Internet educativo" },
            { id: "biblioteca_digital", icono: "📖", titulo: "Biblioteca digital", descripcion: "Recursos digitales" },
            { id: "aula_horizontal", icono: "🏫", titulo: "Aula Horizontal", descripcion: "Plataforma educativa" },
            { id: "recursos_cyd", icono: "🇨🇴", titulo: "Cátedra de la Paz", descripcion: "Recursos de convivencia" },
        ],
    };

    // Estado para recursos
    let recursosCategorias = $state<string[]>([]);
    let recursosSeleccionados = $state<string[]>([]);
    let recursoPersonalizado = $state("");
    let recursosPersonalizadosLista = $state<string[]>([]);

    // --- DATOS PARA TEMPORALIDAD (Paso 6) ---
    
    // Tipos de planeación
    const TIPOS_PLANEACION = [
        { id: "clase_unica", icono: "📅", titulo: "Clase única", descripcion: "Una sesión de clase (45-120 min)", dias: 1 },
        { id: "semanal", icono: "📅", titulo: "Semanal", descripcion: "Planeación para una semana", dias: 7 },
        { id: "quincenal", icono: "📅", titulo: "Quincenal", descripcion: "Planeación para 15 días", dias: 15 },
        { id: "mensual", icono: "📅", titulo: "Mensual", descripcion: "Planeación para un mes", dias: 30 },
        { id: "bimestral", icono: "📅", titulo: "Bimestral", descripcion: "Período de 2 meses", dias: 60 },
        { id: "trimestral", icono: "📅", titulo: "Trimestral/Período", descripcion: "Período académico (10-12 semanas)", dias: 90 },
        { id: "semestral", icono: "📅", titulo: "Semestral", descripcion: "Semestre completo (20 semanas)", dias: 180 },
        { id: "anual", icono: "📅", titulo: "Anual", descripcion: "Año lectivo completo", dias: 200 },
    ];

    // Períodos académicos
    const PERIODOS_ACADEMICOS = [
        { id: "p1", label: "Período 1", tipo: "trimestral" },
        { id: "p2", label: "Período 2", tipo: "trimestral" },
        { id: "p3", label: "Período 3", tipo: "trimestral" },
        { id: "p4", label: "Período 4", tipo: "trimestral" },
        { id: "sem1", label: "Semestre 1", tipo: "semestral" },
        { id: "sem2", label: "Semestre 2", tipo: "semestral" },
        { id: "anual", label: "Año lectivo", tipo: "anual" },
    ];

    // Funciones de temporalidad
    function calcularDuracion(fechaInicio: string, fechaFin: string): { dias: number; semanas: number } {
        if (!fechaInicio || !fechaFin) return { dias: 0, semanas: 0 };
        
        const inicio = new Date(fechaInicio);
        const fin = new Date(fechaFin);
        const diffTime = fin.getTime() - inicio.getTime();
        const diffDias = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        return {
            dias: diffDias + 1, // Incluir día de inicio
            semanas: Math.ceil((diffDias + 1) / 7)
        };
    }

    let duracionCalculada = $derived(calcularDuracion(formData.fecha_inicio, formData.fecha_fin));

    function getPeriodosPorTipo(): { id: string; label: string }[] {
        const tipo = TIPOS_PLANEACION.find(t => t.id === formData.planeacion_tipo);
        if (!tipo) return PERIODOS_ACADEMICOS;
        
        if (tipo.dias <= 7) {
            return PERIODOS_ACADEMICOS.filter(p => p.tipo === 'trimestral' || p.tipo === 'semestral' || p.tipo === 'anual');
        } else if (tipo.dias <= 30) {
            return PERIODOS_ACADEMICOS.filter(p => p.tipo === 'trimestral' || p.tipo === 'anual');
        } else if (tipo.dias <= 90) {
            return PERIODOS_ACADEMICOS.filter(p => p.tipo === 'trimestral');
        } else if (tipo.dias <= 180) {
            return PERIODOS_ACADEMICOS.filter(p => p.tipo === 'semestral');
        }
        return PERIODOS_ACADEMICOS;
    }

    // --- PLANTILLAS PARA OBJETIVOS E INDICADORES ---
    const PLANTILLAS_OBJETIVO = [
        "El estudiante comprenderá el concepto de [TEMA] mediante [RECURSO]",
        "El estudiante aplicará [PROCEDIMIENTO] para resolver situaciones relacionadas con [TEMA]",
        "El estudiante analizará [CONTENIDO] identificando sus componentes principales",
        "El estudiante creará [PRODUCTO] demostrando dominio del tema",
        "El estudiante valorará la importancia de [TEMA] en su contexto",
    ];

    const PLANTILLAS_INDICADOR = [
        "Identifica correctamente los conceptos de [TEMA]",
        "Resuelve correctamente los ejercicios propuestos",
        "Participa activamente en las actividades colaborativas",
        "Entrega el producto esperado en el tiempo indicado",
        "Demuestra comprensión mediante [EVIDENCIA]",
    ];

    // --- FUNCIONES AUXILIARES ---
    
    function getTema(): string {
        return formData.subject || "el tema";
    }

    function agregarActividad(momento: string, actividad: ActividadSugerida) {
        const tema = getTema();
        
        if (momento === 'exploration' && !formData.exploration_activities.includes(actividad.id)) {
            formData.exploration_activities = [...formData.exploration_activities, actividad.id];
            // Auto-llenar descripción
            const textoPlantilla = actividad.plantilla.replace(/\[TEMA\]/g, tema);
            if (formData.exploration) {
                formData.exploration += "\n• " + textoPlantilla;
            } else {
                formData.exploration = "• " + textoPlantilla;
            }
        } else if (momento === 'structuring' && !formData.structuring_activities.includes(actividad.id)) {
            formData.structuring_activities = [...formData.structuring_activities, actividad.id];
            const textoPlantilla = actividad.plantilla.replace(/\[TEMA\]/g, tema);
            if (formData.structuring) {
                formData.structuring += "\n• " + textoPlantilla;
            } else {
                formData.structuring = "• " + textoPlantilla;
            }
        } else if (momento === 'practice' && !formData.practice_activities.includes(actividad.id)) {
            formData.practice_activities = [...formData.practice_activities, actividad.id];
            const textoPlantilla = actividad.plantilla.replace(/\[TEMA\]/g, tema);
            if (formData.practice) {
                formData.practice += "\n• " + textoPlantilla;
            } else {
                formData.practice = "• " + textoPlantilla;
            }
        } else if (momento === 'transfer' && !formData.transfer_activities.includes(actividad.id)) {
            formData.transfer_activities = [...formData.transfer_activities, actividad.id];
            const textoPlantilla = actividad.plantilla.replace(/\[TEMA\]/g, tema);
            if (formData.transfer) {
                formData.transfer += "\n• " + textoPlantilla;
            } else {
                formData.transfer = "• " + textoPlantilla;
            }
        } else if (momento === 'assessment' && !formData.assessment_activities.includes(actividad.id)) {
            formData.assessment_activities = [...formData.assessment_activities, actividad.id];
            const textoPlantilla = actividad.plantilla.replace(/\[TEMA\]/g, tema);
            if (formData.assessment_moment) {
                formData.assessment_moment += "\n• " + textoPlantilla;
            } else {
                formData.assessment_moment = "• " + textoPlantilla;
            }
        }
    }

    function quitarActividad(momento: string, actividadId: string) {
        if (momento === 'exploration') {
            formData.exploration_activities = formData.exploration_activities.filter(id => id !== actividadId);
        } else if (momento === 'structuring') {
            formData.structuring_activities = formData.structuring_activities.filter(id => id !== actividadId);
        } else if (momento === 'practice') {
            formData.practice_activities = formData.practice_activities.filter(id => id !== actividadId);
        } else if (momento === 'transfer') {
            formData.transfer_activities = formData.transfer_activities.filter(id => id !== actividadId);
        } else if (momento === 'assessment') {
            formData.assessment_activities = formData.assessment_activities.filter(id => id !== actividadId);
        }
    }

    function getActividadSeleccionada(momento: string, actividadId: string): ActividadSugerida | undefined {
        const actividades = momento === 'exploration' ? ACTIVIDADES_EXPLORACION
            : momento === 'structuring' ? ACTIVIDADES_ESTRUCTURACION
            : momento === 'practice' ? ACTIVIDADES_PRACTICA
            : momento === 'transfer' ? ACTIVIDADES_TRANSFERENCIA
            : ACTIVIDADES_VALORACION;
        return actividades.find(a => a.id === actividadId);
    }

    // --- COMPETENCIAS SUGERIDAS ---
    const COMPETENCIAS_SUGERIDAS = [
        "Pensamiento crítico y análisis",
        "Comunicación efectiva (oral/escrita)",
        "Colaboración y trabajo en equipo",
        "Uso de herramientas tecnológicas (TIC)",
        "Competencias ciudadanas",
        "Creatividad e innovación",
        "Resolución de problemas",
        "Aprendizaje autónomo",
        "Investigación y pensamiento científico",
        "Competencias laborales generales"
    ];

    function agregarCompetencia(comp: string) {
        if (formData.competencias) {
            if (!formData.competencias.includes(comp)) {
                formData.competencias += "\n• " + comp;
            }
        } else {
            formData.competencias = "• " + comp;
        }
    }

    // --- INDICADORES DE LOGRO SUGERIDOS ---
    const INDICADORES_SUGERIDOS = [
        "Identifica correctamente los conceptos del tema",
        "Explica con sus propias palabras los conceptos aprendidos",
        "Resuelve correctamente los ejercicios propuestos",
        "Aplica los conocimientos en situaciones nuevas",
        "Participa activamente en las actividades colaborativas",
        "Entrega el producto esperado en el tiempo indicado",
        "Demuestra comprensión mediante exposición oral",
        "Elabora productos escritos de calidad",
        "Utiliza correctamente herramientas y recursos",
        "Reflexiona sobre su proceso de aprendizaje"
    ];

    let indicadoresSeleccionados = $state<string[]>([]);

    function toggleIndicador(indicador: string) {
        if (indicadoresSeleccionados.includes(indicador)) {
            indicadoresSeleccionados = indicadoresSeleccionados.filter(i => i !== indicador);
        } else {
            indicadoresSeleccionados = [...indicadoresSeleccionados, indicador];
        }
        // Actualizar formData
        formData.indicadores_logro = indicadoresSeleccionados.map(i => "• " + i).join("\n");
    }

    // --- TEMAS DEL DOCENTE (DERIVADOS Y FUNCIONES) ---

    let temasParaGradoPeriodo = $derived.by(() => {
        if (!formData.grado || !formData.periodo_academico) return [];
        const grado = formData.grado.toLowerCase().trim();
        const periodo = formData.periodo_academico.toLowerCase().trim();
        return temasDocente.filter(
            (t) => t.grado.toLowerCase().trim() === grado && t.periodo.toLowerCase().trim() === periodo
        );
    });

    let temasDisponibles = $derived.by(() => {
        const all = temasParaGradoPeriodo.flatMap((t) => t.temas);
        return [...new Set(all)];
    });

    let actividadesDisponibles = $derived.by(() => {
        const all = temasParaGradoPeriodo.flatMap((t) => t.actividades);
        return [...new Set(all)];
    });

    let actividadesFromTemas = $derived.by(() => {
        return temasParaGradoPeriodo
            .flatMap((t) => t.actividades)
            .filter(Boolean)
            .join("\n");
    });

    $effect(() => {
        const loadTemas = async () => {
            if (currentStep !== 3 || !formData.docente) return;
            isLoadingTemas = true;
            try {
                temasDocente = await getTemasDocente(formData.docente);
            } catch (error) {
                console.error("Error cargando temas del docente:", error);
                temasDocente = [];
            } finally {
                isLoadingTemas = false;
            }
        };
        loadTemas();
    });

    async function handleTemasFileUpload(event: Event): Promise<void> {
        const input = event.target as HTMLInputElement;
        const file = input.files?.[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = async (e) => {
            try {
                const jsonData = JSON.parse(e.target?.result as string);
                if (!Array.isArray(jsonData)) {
                    throw new Error("El archivo debe contener un array JSON");
                }
                for (const entry of jsonData) {
                    if (
                        typeof entry.grado !== "string" ||
                        typeof entry.periodo !== "string" ||
                        !Array.isArray(entry.temas) ||
                        !Array.isArray(entry.actividades)
                    ) {
                        throw new Error(
                            "Cada entrada debe tener: grado (string), periodo (string), temas (string[]), actividades (string[])"
                        );
                    }
                }

                isUploadingTemas = true;
                await uploadTemasDocente(formData.docente, jsonData as TemaDocenteEntry[]);
                temasDocente = jsonData as TemaDocenteEntry[];
                selectedTemas = [];
                selectedActividades = [];

                Swal.fire({
                    icon: "success",
                    title: "Temas cargados",
                    text: `Se subieron ${jsonData.length} entrada(s) de temas correctamente`,
                    timer: 2500,
                });
            } catch (error) {
                Swal.fire({
                    icon: "error",
                    title: "Error al cargar temas",
                    text: error instanceof Error ? error.message : "Formato de archivo inválido",
                    confirmButtonColor: "#ef4444",
                });
            } finally {
                isUploadingTemas = false;
            }
        };
        reader.readAsText(file);
        input.value = "";
    }

    function toggleTema(tema: string): void {
        if (selectedTemas.includes(tema)) {
            selectedTemas = selectedTemas.filter((t) => t !== tema);
        } else {
            selectedTemas = [...selectedTemas, tema];
        }
    }

    function toggleActividad(actividad: string): void {
        if (selectedActividades.includes(actividad)) {
            selectedActividades = selectedActividades.filter((a) => a !== actividad);
        } else {
            selectedActividades = [...selectedActividades, actividad];
        }
    }

    function aplicarTemasSeleccionados(): void {
        if (selectedTemas.length === 0 && selectedActividades.length === 0) return;

        // Aplicar temas en exploración
        if (selectedTemas.length > 0) {
            const temasText = selectedTemas.map((t) => `• ${t}`).join("\n");
            const prefix = "📚 Mis Temas:\n";

            if (formData.exploration) {
                formData.exploration += "\n" + prefix + temasText;
            } else {
                formData.exploration = prefix + temasText;
            }
        }

        // Aplicar actividades seleccionadas en estructuración
        if (selectedActividades.length > 0) {
            const actText = selectedActividades.map((a) => `• ${a}`).join("\n");
            const actPrefix = "📋 Mis Actividades:\n";
            
            if (formData.structuring) {
                formData.structuring += "\n" + actPrefix + actText;
            } else {
                formData.structuring = actPrefix + actText;
            }
        }

        selectedTemas = [];
        selectedActividades = [];

        Swal.fire({
            icon: "success",
            title: "Temas aplicados",
            text: "Se insertaron los temas en Exploración y las actividades en Estructuración",
            timer: 2000,
        });
    }

    // --- GENERADOR DE OBJETIVOS AUTOMÁTICO ---
    function generarObjetivos() {
        const tema = getTema();
        const objetivos = [
            `• El estudiante comprenderá el concepto de ${tema} mediante explicación magistral y ejemplos`,
            `• El estudiante aplicará procedimientos relacionados con ${tema} en ejercicios prácticos`,
            `• El estudiante analizará situaciones problemáticas relacionadas con ${tema}`,
            `• El estudiante valorará la importancia de ${tema} en su contexto personal y social`
        ];
        formData.learning_objectives = objetivos.join("\n");
    }

    // --- PRESETS DE TIEMPO ---
    function setTiempoPreset(preset: "corto" | "estandar" | "extendido") {
        if (preset === "corto") {
            formData.tiempo_exploracion = 8;
            formData.tiempo_estructuracion = 15;
            formData.tiempo_practica = 15;
            formData.tiempo_transferencia = 5;
            formData.tiempo_valoracion = 2;
        } else if (preset === "estandar") {
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

    // --- LIMPIAR DESCRIPCIONES ---
    function limpiarDescripcion(momento: string) {
        if (momento === 'exploration') {
            formData.exploration = "";
            formData.exploration_activities = [];
        } else if (momento === 'structuring') {
            formData.structuring = "";
            formData.structuring_activities = [];
        } else if (momento === 'practice') {
            formData.practice = "";
            formData.practice_activities = [];
        } else if (momento === 'transfer') {
            formData.transfer = "";
            formData.transfer_activities = [];
        } else if (momento === 'assessment') {
            formData.assessment_moment = "";
            formData.assessment_activities = [];
        }
    }

    // --- FUNCIONES DE EVALUACIÓN ---

    // Toggle modalidad
    function toggleModalidad(modalidadId: string) {
        if (formData.eval_modalidades.includes(modalidadId)) {
            formData.eval_modalidades = formData.eval_modalidades.filter(m => m !== modalidadId);
        } else {
            formData.eval_modalidades = [...formData.eval_modalidades, modalidadId];
        }
        autoGenerarDescripcionEval();
    }

    // Toggle instrumento
    function toggleInstrumento(instrumentoId: string) {
        if (formData.eval_instrumentos.includes(instrumentoId)) {
            formData.eval_instrumentos = formData.eval_instrumentos.filter(i => i !== instrumentoId);
        } else {
            formData.eval_instrumentos = [...formData.eval_instrumentos, instrumentoId];
        }
        autoGenerarDescripcionEval();
    }

    // Toggle criterio
    function toggleCriterio(criterioId: string) {
        if (formData.eval_criterios.includes(criterioId)) {
            formData.eval_criterios = formData.eval_criterios.filter(c => c !== criterioId);
        } else {
            formData.eval_criterios = [...formData.eval_criterios, criterioId];
        }
        autoGenerarDescripcionEval();
    }

    // Toggle evidencia
    function toggleEvidencia(evidenciaId: string) {
        if (formData.eval_evidencias.includes(evidenciaId)) {
            formData.eval_evidencias = formData.eval_evidencias.filter(e => e !== evidenciaId);
        } else {
            formData.eval_evidencias = [...formData.eval_evidencias, evidenciaId];
        }
        autoGenerarDescripcionEval();
    }

    // Auto-generar descripción de evaluación
    function autoGenerarDescripcionEval() {
        const tipo = TIPOS_EVALUACION.find(t => t.id === formData.eval_type);
        const tipoTexto = tipo ? `${tipo.icono} ${tipo.titulo}` : "Evaluación";
        
        const modalidades = formData.eval_modalidades.map(m => {
            const mod = MODALIDADES_EVALUACION.find(x => x.id === m);
            return mod ? `${mod.icono} ${mod.titulo}` : m;
        });
        
        const instrumentos = formData.eval_instrumentos.map(i => {
            const inst = INSTRUMENTOS_EVALUACION.find(x => x.id === i);
            return inst ? `${inst.icono} ${inst.titulo}` : i;
        });
        
        const criterios = formData.eval_criterios.map(c => {
            const crit = CRITERIOS_EVALUACION.find(x => x.id === c);
            return crit ? crit.titulo : c;
        });
        
        const evidencias = formData.eval_evidencias.map(e => {
            const ev = EVIDENCIAS_APRENDIZAJE.find(x => x.id === e);
            return ev ? ev.titulo : e;
        });

        let descripcion = `Se aplicará ${tipoTexto}`;
        
        if (modalidades.length > 0) {
            descripcion += ` con ${modalidades.join(", ")}`;
        }
        
        if (instrumentos.length > 0) {
            descripcion += `. Instrumentos: ${instrumentos.join(", ")}`;
        }
        
        if (criterios.length > 0) {
            descripcion += `. Se evaluará: ${criterios.join(", ")}`;
        }
        
        if (evidencias.length > 0) {
            descripcion += `. Evidencias esperadas: ${evidencias.join(", ")}`;
        }
        
        descripcion += `. Ponderación: Conceptos ${formData.eval_ponderacion_conceptos}%, Procedimientos ${formData.eval_ponderacion_procedimientos}%, Actitudes ${formData.eval_ponderacion_actitudes}%`;
        
        formData.eval_descripcion_auto = descripcion;
        formData.eval_criteria = criterios.join(", ");
        formData.eval_evidence = evidencias.join(", ");
    }

    // Resetear evaluación
    function resetearEvaluacion() {
        formData.eval_modalidades = [];
        formData.eval_instrumentos = [];
        formData.eval_criterios = [];
        formData.eval_evidencias = [];
        formData.eval_descripcion_auto = "";
        formData.eval_criteria = "";
        formData.eval_evidence = "";
    }

    // --- FUNCIONES DE RECURSOS DIDÁCTICOS ---

    // Toggle categoría
    function toggleCategoria(categoriaId: string) {
        if (recursosCategorias.includes(categoriaId)) {
            recursosCategorias = recursosCategorias.filter(c => c !== categoriaId);
        } else {
            recursosCategorias = [...recursosCategorias, categoriaId];
        }
    }

    // Toggle recurso
    function toggleRecurso(recursoId: string) {
        if (recursosSeleccionados.includes(recursoId)) {
            recursosSeleccionados = recursosSeleccionados.filter(r => r !== recursoId);
        } else {
            recursosSeleccionados = [...recursosSeleccionados, recursoId];
        }
        generarDescripcionRecursos();
    }

    // Agregar recurso personalizado
    function agregarRecursoPersonalizado() {
        if (recursoPersonalizado.trim() && !recursosPersonalizadosLista.includes(recursoPersonalizado.trim())) {
            recursosPersonalizadosLista = [...recursosPersonalizadosLista, recursoPersonalizado.trim()];
            recursoPersonalizado = "";
            generarDescripcionRecursos();
        }
    }

    // Quitar recurso personalizado
    function quitarRecursoPersonalizado(recurso: string) {
        recursosPersonalizadosLista = recursosPersonalizadosLista.filter(r => r !== recurso);
        generarDescripcionRecursos();
    }

    // Generar descripción de recursos
    function generarDescripcionRecursos() {
        const recursos: string[] = [];
        
        // Agregar recursos por categoría
        recursosSeleccionados.forEach(id => {
            for (const categoria of Object.values(RECURSOS_POR_CATEGORIA)) {
                const recurso = categoria.find(r => r.id === id);
                if (recurso) {
                    recursos.push(`• ${recurso.icono} ${recurso.titulo}`);
                    break;
                }
            }
        });
        
        // Agregar recursos personalizados
        recursosPersonalizadosLista.forEach(r => {
            recursos.push(`• 📌 ${r}`);
        });
        
        formData.resources = recursos.join("\n");
    }

    // Resetear recursos
    function resetearRecursos() {
        recursosCategorias = [];
        recursosSeleccionados = [];
        recursoPersonalizado = "";
        recursosPersonalizadosLista = [];
        formData.resources = "";
    }

    // Contador de recursos por categoría
    function getCountPorCategoria(categoriaId: string): number {
        const recursos = RECURSOS_POR_CATEGORIA[categoriaId] || [];
        return recursos.filter(r => recursosSeleccionados.includes(r.id)).length;
    }

    let tiempoTotal = $derived(
        formData.tiempo_exploracion + formData.tiempo_estructuracion + 
        formData.tiempo_practica + formData.tiempo_transferencia + formData.tiempo_valoracion
    );

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
        if (currentStep === 1)
            return formData.planeacion_tipo !== "" && formData.periodo_academico !== "";
        if (currentStep === 2) return formData.standard.length > 0;
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
        if (currentStep < 5) currentStep++;
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

    const generatePDF = async (): Promise<void> => {
        isGeneratingPdf = true;
        
        try {
            const [{ default: jsPDF }, { default: autoTable }] = await Promise.all([
                import('jspdf'),
                import('jspdf-autotable')
            ]);
            
            const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'letter' });
            const pageWidth = doc.internal.pageSize.getWidth();
            const margin = 15;
            let yPos = 10;
            
            // Colores
            const COLOR_INDIGO: [number, number, number] = [79, 70, 229];
            const COLOR_GRAY_LIGHT: [number, number, number] = [243, 244, 246];
            const COLOR_GRAY_MEDIUM: [number, number, number] = [209, 213, 219];
            
            // HEADER
            doc.setFillColor(COLOR_INDIGO[0], COLOR_INDIGO[1], COLOR_INDIGO[2]);
            doc.rect(0, 0, pageWidth, 35, 'F');
            
            doc.setTextColor(255, 255, 255);
            doc.setFontSize(18);
            doc.setFont('helvetica', 'bold');
            doc.text('PLANEADOR DE CLASES', margin, 18);
            
            doc.setFontSize(10);
            doc.setFont('helvetica', 'normal');
            doc.text('Según lineamientos MEN - Colombia', margin, 26);
            
            doc.setFontSize(8);
            doc.text('Sistema de Gestión Académica', pageWidth - margin, 18, { align: 'right' });
            
            yPos = 42;
            doc.setTextColor(0, 0, 0);
            
            // Función helper para secciones
            const addSection = (title: string, contentFn: () => void) => {
                doc.setFillColor(COLOR_GRAY_LIGHT[0], COLOR_GRAY_LIGHT[1], COLOR_GRAY_LIGHT[2]);
                doc.rect(margin, yPos - 5, pageWidth - 2 * margin, 8, 'F');
                doc.setFontSize(12);
                doc.setFont('helvetica', 'bold');
                doc.setTextColor(79, 70, 229);
                doc.text(title, margin, yPos);
                yPos += 10;
                doc.setTextColor(0, 0, 0);
                contentFn();
                yPos += 5;
            };
            
            // 1. INFORMACIÓN BÁSICA
            addSection('1. INFORMACIÓN BÁSICA', () => {
                doc.setFontSize(10);
                doc.setFont('helvetica', 'normal');
                doc.text(`Docente: ${formData.docente || 'No especificado'}`, margin, yPos);
                doc.text(`Grado: ${formData.grado || 'No especificado'}`, 110, yPos);
                yPos += 6;
                doc.text(`Materia: ${formData.subject || 'No especificada'}`, margin, yPos);
                doc.text(`Período: ${formData.period || 'No especificado'}`, 110, yPos);
                yPos += 8;
            });
            
            // 2. TEMPORALIDAD
            addSection('2. TEMPORALIDAD', () => {
                doc.setFontSize(10);
                doc.setFont('helvetica', 'normal');
                const tipoPlan = TIPOS_PLANEACION.find(t => t.id === formData.planeacion_tipo);
                const periodoAcad = getPeriodosPorTipo().find(p => p.id === formData.periodo_academico);
                doc.text(`Tipo de Planeación: ${tipoPlan?.titulo || 'No especificado'}`, margin, yPos);
                doc.text(`Período Académico: ${periodoAcad?.label || 'No especificado'}`, 110, yPos);
                yPos += 6;
                doc.text(`Fecha Inicio: ${formData.fecha_inicio || 'No especificada'}`, margin, yPos);
                doc.text(`Fecha Fin: ${formData.fecha_fin || 'No especificada'}`, 110, yPos);
                yPos += 8;
            });
            
            // 3. REFERENTES DE CALIDAD
            addSection('3. REFERENTES DE CALIDAD (DBA/EBC)', () => {
                // DBA seleccionados
                const dbasSelected = dbas.filter(d => formData.dba.includes(d.id));
                if (dbasSelected.length > 0) {
                    autoTable(doc, {
                        startY: yPos,
                        head: [['Código DBA', 'Descripción']],
                        body: dbasSelected.map(d => [d.codigo, d.descripcion.substring(0, 150) + (d.descripcion.length > 150 ? '...' : '')]),
                        margin: { left: margin, right: margin },
                        headStyles: { fillColor: COLOR_INDIGO, fontSize: 9 },
                        bodyStyles: { fontSize: 8 },
                        theme: 'striped',
                    });
                    yPos = (doc as any).lastAutoTable.finalY + 8;
                }
                
                // EBC seleccionados
                const ebcsSelected = ebcs.filter(e => formData.standard.includes(e.id));
                if (ebcsSelected.length > 0) {
                    autoTable(doc, {
                        startY: yPos,
                        head: [['Código EBC', 'Descripción']],
                        body: ebcsSelected.map(e => [e.codigo, e.descripcion.substring(0, 150) + (e.descripcion.length > 150 ? '...' : '')]),
                        margin: { left: margin, right: margin },
                        headStyles: { fillColor: [16, 185, 129] as [number, number, number], fontSize: 9 },
                        bodyStyles: { fontSize: 8 },
                        theme: 'striped',
                    });
                    yPos = (doc as any).lastAutoTable.finalY + 8;
                }
                
                if (formData.competency) {
                    doc.setFontSize(10);
                    doc.text(`Competencia Ciudadana: ${formData.competency}`, margin, yPos);
                    yPos += 8;
                }
            });
            
            // 4. SECUENCIA DIDÁCTICA
            addSection('4. SECUENCIA DIDÁCTICA', () => {
                doc.setFontSize(10);
                
                const momentos = [
                    { label: 'Exploración', desc: formData.exploration, tiempo: formData.tiempo_exploracion, acts: formData.exploration_activities },
                    { label: 'Estructuración', desc: formData.structuring, tiempo: formData.tiempo_estructuracion, acts: formData.structuring_activities },
                    { label: 'Práctica', desc: formData.practice, tiempo: formData.tiempo_practica, acts: formData.practice_activities },
                    { label: 'Transferencia', desc: formData.transfer, tiempo: formData.tiempo_transferencia, acts: formData.transfer_activities },
                    { label: 'Valoración', desc: formData.assessment_moment, tiempo: formData.tiempo_valoracion, acts: formData.assessment_activities },
                ];
                
                for (const momento of momentos) {
                    if (momento.desc || momento.acts.length > 0) {
                        doc.setFont('helvetica', 'bold');
                        doc.text(`${momento.label} (${momento.tiempo} min):`, margin, yPos);
                        yPos += 5;
                        doc.setFont('helvetica', 'normal');
                        if (momento.desc) {
                            const lines = doc.splitTextToSize(momento.desc, pageWidth - 2 * margin);
                            doc.text(lines, margin, yPos);
                            yPos += lines.length * 4 + 2;
                        }
                        if (momento.acts.length > 0) {
                            doc.text(`Actividades: ${momento.acts.join(', ')}`, margin, yPos);
                            yPos += 6;
                        }
                        yPos += 3;
                    }
                }
            });
            
            // 5. EVALUACIÓN
            addSection('5. EVALUACIÓN DE APRENDIZAJES', () => {
                doc.setFontSize(10);
                doc.setFont('helvetica', 'normal');
                doc.text(`Tipo de Evaluación: ${formData.eval_type}`, margin, yPos);
                yPos += 6;
                
                if (formData.eval_modalidades.length > 0) {
                    doc.text(`Modalidades: ${formData.eval_modalidades.join(', ')}`, margin, yPos);
                    yPos += 6;
                }
                
                if (formData.eval_instrumentos.length > 0) {
                    doc.text(`Instrumentos: ${formData.eval_instrumentos.join(', ')}`, margin, yPos);
                    yPos += 6;
                }
                
                if (formData.eval_criterios.length > 0) {
                    doc.text(`Criterios: ${formData.eval_criterios.join(', ')}`, margin, yPos);
                    yPos += 6;
                }
                
                if (formData.eval_evidencias.length > 0) {
                    doc.text(`Evidencias: ${formData.eval_evidencias.join(', ')}`, margin, yPos);
                    yPos += 6;
                }
                
                // Ponderación
                autoTable(doc, {
                    startY: yPos,
                    head: [['Componente', 'Ponderación']],
                    body: [
                        ['Conceptos', `${formData.eval_ponderacion_conceptos}%`],
                        ['Procedimientos', `${formData.eval_ponderacion_procedimientos}%`],
                        ['Actitudes', `${formData.eval_ponderacion_actitudes}%`],
                    ],
                    margin: { left: margin, right: margin },
                        headStyles: { fillColor: COLOR_INDIGO as [number, number, number], fontSize: 9 },
                        bodyStyles: { fontSize: 9 },
                        theme: 'striped',
                    });
                    yPos = (doc as any).lastAutoTable.finalY + 8;
            });
            
            // 6. RECURSOS DIDÁCTICOS
            addSection('6. RECURSOS DIDÁCTICOS', () => {
                doc.setFontSize(10);
                doc.setFont('helvetica', 'normal');
                if (formData.resources) {
                    const lines = doc.splitTextToSize(formData.resources, pageWidth - 2 * margin);
                    doc.text(lines, margin, yPos);
                    yPos += lines.length * 5 + 5;
                } else {
                    doc.text('No se han seleccionado recursos.', margin, yPos);
                    yPos += 6;
                }
            });
            
            // FOOTER con número de página
            const pageCount = doc.getNumberOfPages();
            for (let i = 1; i <= pageCount; i++) {
                doc.setPage(i);
                doc.setFontSize(8);
                doc.setTextColor(128, 128, 128);
                doc.text(`Página ${i} de ${pageCount}`, pageWidth / 2, 290, { align: 'center' });
                doc.text(`Generado: ${new Date().toLocaleDateString('es-CO')}`, margin, 290);
                doc.text('Sistema de Gestión Académica - Colombia', pageWidth - margin, 290, { align: 'right' });
            }
            
            pdfUrl = doc.output('bloburl') as unknown as string;
            showPdfPreview = true;
        } catch (error) {
            console.error('Error generando PDF:', error);
            await Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo generar el PDF. Intente nuevamente.',
                confirmButtonColor: '#ef4444',
            });
        } finally {
            isGeneratingPdf = false;
        }
    };

    onMount(() => {
        loadData();
    });

    async function handleSubmit() {
        isLoading = true;
        let guardadoOnline = false;
        
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

        try {
            validatePlaneacion(planeacionData);
            const result = await savePlaneador(planeacionData);
            
            if (result.success) {
                guardadoOnline = true;
                Swal.fire({
                    icon: "success",
                    title: "¡Guardado!",
                    text:
                        result.message ||
                        "Planeación guardada exitosamente según normativa MEN.",
                    confirmButtonColor: "#10b981",
                    timer: 3000,
                });
            }
        } catch (error) {
            console.warn("Backend no disponible, guardando localmente:", error);
            const savedLocal = savePlaneadorLocal(planeacionData);
            Swal.fire({
                icon: "info",
                title: "Guardado localmente",
                text: `Sin conexión. La planeación se guardó en el navegador (ID: ${savedLocal.id_local.substring(0, 12)}...).`,
                confirmButtonColor: "#f59e0b",
                timer: 4000,
            });
        }

        if (guardadoOnline) {
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
                learning_objectives: "",
                competencias: "",
                exploration: "",
                exploration_activities: [],
                structuring: "",
                structuring_activities: [],
                practice: "",
                practice_activities: [],
                transfer: "",
                transfer_activities: [],
                assessment_moment: "",
                assessment_activities: [],
                indicadores_logro: "",
                tiempo_exploracion: 10,
                tiempo_estructuracion: 20,
                tiempo_practica: 25,
                tiempo_transferencia: 15,
                tiempo_valoracion: 10,
                eval_type: "Formativa",
                eval_modalidades: [],
                eval_instrumentos: [],
                eval_criterios: [],
                eval_evidencias: [],
                eval_criteria: "",
                eval_evidence: "",
                eval_ponderacion_conceptos: 30,
                eval_ponderacion_procedimientos: 40,
                eval_ponderacion_actitudes: 30,
                eval_descripcion_auto: "",
                resources: "",
                planeacion_tipo: "",
                periodo_academico: "",
                fecha_inicio: "",
                fecha_fin: "",
                firma_docente: "",
                fecha_firma: "",
            };
            currentStep = 0;
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
            <div class="flex gap-2">
                <!-- Botón Firma -->
                <button
                    onclick={() => showFirmaModal = true}
                    class="p-2 rounded-lg transition-colors flex items-center gap-2 {formData.firma_docente ? 'bg-green-500 hover:bg-green-400' : 'bg-amber-500 hover:bg-amber-400'}"
                    aria-label="Firmar planeación"
                    title={formData.firma_docente ? "Firma guardada - Click para modificar" : "Firmar planeación"}
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                </button>
                <!-- Botón PDF -->
                <button
                    onclick={generatePDF}
                    disabled={isGeneratingPdf}
                    class="p-2 rounded-lg bg-blue-500 hover:bg-blue-400 transition-colors flex items-center gap-2"
                    aria-label="Generar PDF"
                    title="Generar PDF"
                >
                    {#if isGeneratingPdf}
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    {:else}
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    {/if}
                </button>
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
            class="flex justify-between items-center px-1 md:px-4 py-2 md:py-3 bg-white/50 border-b border-indigo-100 overflow-x-auto"
        >
            {#each ["Datos", "Temporalidad", "Referentes", "Didáctica", "Evaluación", "Recursos"] as label, index}
                <button
                    type="button"
                    onclick={() => currentStep = index}
                    class="flex flex-col items-center relative z-10 flex-shrink-0 focus:outline-none group"
                >
                    <div
                        class="w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center text-xs md:text-sm font-bold transition-all duration-300 cursor-pointer
                            {currentStep === index ? 'bg-indigo-600 text-white shadow-lg scale-110 ring-2 ring-indigo-300' : ''}
                            {currentStep > index ? 'bg-green-500 text-white' : ''}
                            {currentStep < index ? 'bg-gray-200 text-gray-500 group-hover:bg-gray-300 group-hover:scale-105' : ''}"
                    >
                        {#if currentStep > index}
                            <svg class="w-3 h-3 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        {:else}
                            {index + 1}
                        {/if}
                    </div>
                    <span class="hidden md:inline text-[9px] md:text-xs mt-1 font-medium text-gray-600 whitespace-nowrap">
                        {label}
                    </span>
                    <span class="md:hidden text-[8px] mt-0.5 font-medium text-gray-500">
                        {label.substring(0, 4)}
                    </span>
                </button>
                {#if index < 5}
                    <div class="hidden md:flex flex-1 h-1 bg-gray-200 mx-0.5 md:mx-1 rounded min-w-[8px]">
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

            <!-- PASO 3: REFERENTES DE CALIDAD (MEN) -->
            {#if currentStep === 2}
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

            <!-- PASO 4: SECUENCIA DIDÁCTICA -->
            {#if currentStep === 3}
                <div class="space-y-6 animate-fade-in">
                    <!-- Sección: Cargar Mis Temas JSON -->
                    <div class="bg-gradient-to-r from-amber-50 to-orange-50 p-4 rounded-xl border border-amber-200">
                        <div class="flex items-center justify-between flex-wrap gap-3">
                            <div>
                                <h3 class="font-bold text-amber-800 flex items-center gap-2">
                                    📂 Mis Temas Personalizados
                                </h3>
                                <p class="text-xs text-amber-600 mt-1">
                                    Carga tu JSON con temas y actividades propias
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <input
                                    type="file"
                                    accept=".json"
                                    bind:this={temasJsonInputRef}
                                    onchange={handleTemasFileUpload}
                                    class="hidden"
                                />
                                <button
                                    type="button"
                                    onclick={() => temasJsonInputRef?.click()}
                                    disabled={isUploadingTemas}
                                    class="px-4 py-2 bg-amber-600 hover:bg-amber-700 disabled:bg-amber-400 text-white rounded-lg font-medium text-sm transition-colors flex items-center gap-2"
                                >
                                    {isUploadingTemas ? '⏳ Subiendo...' : '📤 Subir JSON'}
                                </button>
                            </div>
                        </div>

                        {#if temasDocente.length > 0}
                            <div class="mt-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-medium text-amber-700">
                                        ✅ Temas cargados ({temasDocente.length} entradas)
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        Selecciona un grado y período para ver temas
                                    </span>
                                </div>
                                
                                <!-- Selector de Grado y Período -->
                                <div class="flex flex-wrap gap-2 items-center">
                                    <select
                                        bind:value={formData.grado}
                                        class="text-sm px-3 py-2 rounded-lg border border-amber-300 focus:ring-amber-500 focus:border-amber-500 bg-white"
                                    >
                                        <option value="">Seleccionar Grado</option>
                                        {#each [...new Set(temasDocente.map(t => t.grado))] as grado}
                                            <option value={grado}>{grado}</option>
                                        {/each}
                                    </select>
                                    
                                    <select
                                        bind:value={formData.periodo_academico}
                                        class="text-sm px-3 py-2 rounded-lg border border-amber-300 focus:ring-amber-500 focus:border-amber-500 bg-white"
                                    >
                                        <option value="">Seleccionar Período</option>
                                        {#each [...new Set(temasDocente.map(t => t.periodo))] as periodo}
                                            <option value={periodo}>{periodo}</option>
                                        {/each}
                                    </select>

                                    {#if selectedTemas.length > 0 || selectedActividades.length > 0}
                                        <button
                                            type="button"
                                            onclick={aplicarTemasSeleccionados}
                                            class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium text-sm transition-colors"
                                        >
                                            ✅ Aplicar ({selectedTemas.length + selectedActividades.length})
                                        </button>
                                    {/if}
                                </div>

                                <!-- Temas Disponibles -->
                                {#if temasDisponibles.length > 0}
                                    <div class="bg-white p-3 rounded-lg border border-amber-200">
                                        <label class="text-xs font-medium text-gray-600 mb-2 block">
                                            Temas disponibles (click para seleccionar):
                                        </label>
                                        <div class="flex flex-wrap gap-2">
                                            {#each temasDisponibles as tema}
                                                {@const selected = selectedTemas.includes(tema)}
                                                <button
                                                    type="button"
                                                    onclick={() => toggleTema(tema)}
                                                    class="px-3 py-1.5 rounded-full text-sm font-medium transition-all duration-200
                                                        {selected ? 'bg-amber-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-amber-100 border border-gray-200'}"
                                                >
                                                    {selected ? '✓ ' : '+ '}{tema}
                                                </button>
                                            {/each}
                                        </div>
                                    </div>
                                {/if}

                                <!-- Actividades Disponibles -->
                                {#if actividadesDisponibles.length > 0}
                                    <div class="bg-white p-3 rounded-lg border border-purple-200">
                                        <label class="text-xs font-medium text-purple-700 mb-2 block">
                                            Actividades disponibles (click para seleccionar):
                                        </label>
                                        <div class="flex flex-wrap gap-2">
                                            {#each actividadesDisponibles as actividad}
                                                {@const selected = selectedActividades.includes(actividad)}
                                                <button
                                                    type="button"
                                                    onclick={() => toggleActividad(actividad)}
                                                    class="px-3 py-1.5 rounded-full text-sm font-medium transition-all duration-200
                                                        {selected ? 'bg-purple-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-purple-100 border border-gray-200'}"
                                                >
                                                    {selected ? '✓ ' : '+ '}{actividad}
                                                </button>
                                            {/each}
                                        </div>
                                    </div>
                                {/if}

                                <!-- Resumen por grado/período -->
                                <details class="group">
                                    <summary class="text-xs text-gray-500 cursor-pointer hover:text-amber-600 flex items-center gap-1">
                                        <span class="group-open:rotate-90 transition-transform">▶</span>
                                        Ver todas las entradas ({temasDocente.length})
                                    </summary>
                                    <div class="mt-2 max-h-40 overflow-y-auto space-y-1">
                                        {#each temasDocente as entry}
                                            <div class="text-xs bg-gray-50 p-2 rounded border border-gray-200">
                                                <span class="font-medium text-indigo-700">{entry.grado}</span> - 
                                                <span class="font-medium text-green-700">{entry.periodo}</span>
                                                <span class="text-gray-500"> ({entry.temas.length} temas)</span>
                                            </div>
                                        {/each}
                                    </div>
                                </details>
                            </div>
                        {:else if isLoadingTemas}
                            <div class="mt-3 text-center text-sm text-gray-500">
                                ⏳ Cargando temas...
                            </div>
                        {:else}
                            <div class="mt-3 text-xs text-gray-500 bg-white p-3 rounded-lg">
                                <strong>Formato esperado del JSON:</strong>
                                <pre class="mt-1 p-2 bg-gray-100 rounded text-xs overflow-x-auto">{`[
  {
    "grado": "6",
    "periodo": "Período 1",
    "temas": ["Tema 1", "Tema 2"],
    "actividades": ["Actividad 1", "Actividad 2"]
  }
]`}</pre>
                            </div>
                        {/if}
                    </div>

                    <!-- Objetivos y Competencias -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-xl border border-blue-200">
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-sm font-bold text-blue-800">🎯 Objetivos de Aprendizaje</label>
                                <button 
                                    type="button"
                                    onclick={generarObjetivos}
                                    class="text-xs px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors flex items-center gap-1 shadow-sm"
                                    title="Generar objetivos automáticamente"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    Auto-generar
                                </button>
                            </div>
                            <textarea
                                bind:value={formData.learning_objectives}
                                rows="3"
                                class="w-full text-sm rounded-lg border-blue-200 focus:ring-blue-500 focus:border-blue-500 bg-white/80"
                                placeholder="El estudiante será capaz de... (O usa el botón para auto-generar)"
                            ></textarea>
                        </div>
                        
                        <!-- Competencias con chips -->
                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-4 rounded-xl border border-green-200">
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-sm font-bold text-green-800">🤝 Competencias a Desarrollar</label>
                            </div>
                            <!-- Chips de competencias -->
                            <div class="flex flex-wrap gap-1.5 mb-2">
                                {#each COMPETENCIAS_SUGERIDAS.slice(0, 5) as comp}
                                    <button 
                                        type="button"
                                        onclick={() => agregarCompetencia(comp)}
                                        class="text-xs px-2 py-1 bg-white hover:bg-green-100 text-green-700 rounded-full border border-green-200 transition-colors"
                                        title="Agregar competencia"
                                    >
                                        + {comp.split(' ')[0]}
                                    </button>
                                {/each}
                            </div>
                            <textarea
                                bind:value={formData.competencias}
                                rows="3"
                                class="w-full text-sm rounded-lg border-green-200 focus:ring-green-500 focus:border-green-500 bg-white/80"
                                placeholder="Competencias... (Usa los chips de arriba)"
                            ></textarea>
                        </div>
                    </div>

                    <!-- Indicadores de Logro con chips -->
                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 p-4 rounded-xl border border-purple-200">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-bold text-purple-800">📊 Indicadores de Logro</label>
                        </div>
                        <!-- Chips de indicadores -->
                        <div class="flex flex-wrap gap-1.5 mb-3">
                            {#each INDICADORES_SUGERIDOS as indicador}
                                {@const selected = indicadoresSeleccionados.includes(indicador)}
                                <button 
                                    type="button"
                                    onclick={() => toggleIndicador(indicador)}
                                    class="text-xs px-2.5 py-1 rounded-full border transition-all duration-200 {selected ? 'bg-purple-600 text-white border-purple-600 shadow-sm' : 'bg-white text-purple-700 border-purple-200 hover:bg-purple-50'}"
                                    title="Click para seleccionar"
                                >
                                    {selected ? '✓ ' : '+ '}{indicador.split(' ').slice(0, 3).join(' ')}...
                                </button>
                            {/each}
                        </div>
                        <textarea
                            bind:value={formData.indicadores_logro}
                            rows="2"
                            class="w-full text-sm rounded-lg border-purple-200 focus:ring-purple-500 focus:border-purple-500 bg-white/80"
                            placeholder="Indicadores... (Selecciona los chips de arriba o escribe)"
                        ></textarea>
                    </div>

                    <!-- Tiempos por momento con presets -->
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-sm font-semibold text-gray-700">⏱️ Distribución del Tiempo</label>
                            <!-- Presets de tiempo -->
                            <div class="flex gap-2">
                                <button 
                                    type="button"
                                    onclick={() => setTiempoPreset("corto")}
                                    class="text-xs px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium transition-colors"
                                    title="Clase corta (45 min)"
                                >
                                    🔵 Corta (45m)
                                </button>
                                <button 
                                    type="button"
                                    onclick={() => setTiempoPreset("estandar")}
                                    class="text-xs px-3 py-1.5 bg-purple-500 hover:bg-purple-600 text-white rounded-lg font-medium transition-colors"
                                    title="Clase estándar (80 min)"
                                >
                                    🟣 Estándar (80m)
                                </button>
                                <button 
                                    type="button"
                                    onclick={() => setTiempoPreset("extendido")}
                                    class="text-xs px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white rounded-lg font-medium transition-colors"
                                    title="Clase extendida (120 min)"
                                >
                                    🟢 Extendida (120m)
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                            <div>
                                <label class="text-xs text-gray-500 block mb-1">Exploración</label>
                                <div class="flex items-center gap-1">
                                    <input type="number" bind:value={formData.tiempo_exploracion} min="1" max="60" class="w-14 text-sm p-1.5 rounded border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" />
                                    <span class="text-xs text-gray-500">min</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                    <div class="bg-blue-500 h-1.5 rounded-full" style="width: {(formData.tiempo_exploracion / 120) * 100}%"></div>
                                </div>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500 block mb-1">Estructuración</label>
                                <div class="flex items-center gap-1">
                                    <input type="number" bind:value={formData.tiempo_estructuracion} min="1" max="60" class="w-14 text-sm p-1.5 rounded border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" />
                                    <span class="text-xs text-gray-500">min</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                    <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {(formData.tiempo_estructuracion / 120) * 100}%"></div>
                                </div>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500 block mb-1">Práctica</label>
                                <div class="flex items-center gap-1">
                                    <input type="number" bind:value={formData.tiempo_practica} min="1" max="60" class="w-14 text-sm p-1.5 rounded border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" />
                                    <span class="text-xs text-gray-500">min</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                    <div class="bg-purple-500 h-1.5 rounded-full" style="width: {(formData.tiempo_practica / 120) * 100}%"></div>
                                </div>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500 block mb-1">Transferencia</label>
                                <div class="flex items-center gap-1">
                                    <input type="number" bind:value={formData.tiempo_transferencia} min="1" max="60" class="w-14 text-sm p-1.5 rounded border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" />
                                    <span class="text-xs text-gray-500">min</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                    <div class="bg-orange-500 h-1.5 rounded-full" style="width: {(formData.tiempo_transferencia / 120) * 100}%"></div>
                                </div>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500 block mb-1">Valoración</label>
                                <div class="flex items-center gap-1">
                                    <input type="number" bind:value={formData.tiempo_valoracion} min="1" max="60" class="w-14 text-sm p-1.5 rounded border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" />
                                    <span class="text-xs text-gray-500">min</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                    <div class="bg-green-500 h-1.5 rounded-full" style="width: {(formData.tiempo_valoracion / 120) * 100}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Momentos de la Clase con Actividades Interactivas -->
                    <div class="space-y-4">
                        <!-- MOMENTO 1: EXPLORACIÓN -->
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 border-b border-gray-100">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm">1</span>
                                        <div>
                                            <h3 class="font-bold text-gray-800">Exploración (Inicio)</h3>
                                            <p class="text-xs text-gray-500">Recuperar saberes previos y generar motivación</p>
                                        </div>
                                    </div>
                                    <span class="text-sm text-blue-600 font-medium">{formData.tiempo_exploracion} min</span>
                                </div>
                            </div>
                            <div class="p-4 space-y-4">
                                <!-- Actividades sugeridas -->
                                <div>
                                    <label class="text-xs font-medium text-gray-600 mb-2 block">🎯 Haz click para agregar actividades:</label>
                                    <div class="flex flex-wrap gap-2">
                                        {#each ACTIVIDADES_EXPLORACION as act}
                                            {@const selected = formData.exploration_activities.includes(act.id)}
                                            <button
                                                type="button"
                                                onclick={() => selected ? quitarActividad('exploration', act.id) : agregarActividad('exploration', act)}
                                                class="px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-1.5
                                                    {selected ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-blue-100 border border-gray-200'}"
                                            >
                                                <span>{act.icono}</span>
                                                <span>{act.titulo}</span>
                                            </button>
                                        {/each}
                                    </div>
                                </div>
                                <!-- Descripción -->
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-medium text-gray-600">Descripción de actividades:</label>
                                    <button 
                                        type="button"
                                        onclick={() => limpiarDescripcion('exploration')}
                                        class="text-xs px-2 py-1 text-red-600 hover:bg-red-50 rounded transition-colors flex items-center gap-1"
                                        title="Limpiar todo"
                                    >
                                        🗑️ Limpiar
                                    </button>
                                </div>
                                <textarea
                                    bind:value={formData.exploration}
                                    rows="3"
                                    class="w-full text-sm rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="👆 Click en las actividades de arriba para auto-completar..."
                                ></textarea>
                            </div>
                        </div>

                        <!-- MOMENTO 2: ESTRUCTURACIÓN -->
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 p-4 border-b border-gray-100">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold text-sm">2</span>
                                        <div>
                                            <h3 class="font-bold text-gray-800">Estructuración (Desarrollo)</h3>
                                            <p class="text-xs text-gray-500">Construcción y reconstrucción del conocimiento</p>
                                        </div>
                                    </div>
                                    <span class="text-sm text-indigo-600 font-medium">{formData.tiempo_estructuracion} min</span>
                                </div>
                            </div>
                            <div class="p-4 space-y-4">
                                <div>
                                    <label class="text-xs font-medium text-gray-600 mb-2 block">📚 Haz click para agregar actividades:</label>
                                    <div class="flex flex-wrap gap-2">
                                        {#each ACTIVIDADES_ESTRUCTURACION as act}
                                            {@const selected = formData.structuring_activities.includes(act.id)}
                                            <button
                                                type="button"
                                                onclick={() => selected ? quitarActividad('structuring', act.id) : agregarActividad('structuring', act)}
                                                class="px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-1.5
                                                    {selected ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-indigo-100 border border-gray-200'}"
                                            >
                                                <span>{act.icono}</span>
                                                <span>{act.titulo}</span>
                                            </button>
                                        {/each}
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-medium text-gray-600">Descripción de actividades:</label>
                                    <button 
                                        type="button"
                                        onclick={() => limpiarDescripcion('structuring')}
                                        class="text-xs px-2 py-1 text-red-600 hover:bg-red-50 rounded transition-colors flex items-center gap-1"
                                        title="Limpiar todo"
                                    >
                                        🗑️ Limpiar
                                    </button>
                                </div>
                                <textarea
                                    bind:value={formData.structuring}
                                    rows="3"
                                    class="w-full text-sm rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="👆 Click en las actividades de arriba para auto-completar..."
                                ></textarea>
                            </div>
                        </div>

                        <!-- MOMENTO 3: PRÁCTICA -->
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                            <div class="bg-gradient-to-r from-purple-50 to-pink-50 p-4 border-b border-gray-100">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center font-bold text-sm">3</span>
                                        <div>
                                            <h3 class="font-bold text-gray-800">Práctica (Aplicación)</h3>
                                            <p class="text-xs text-gray-500">Ejercitación y trabajo colaborativo</p>
                                        </div>
                                    </div>
                                    <span class="text-sm text-purple-600 font-medium">{formData.tiempo_practica} min</span>
                                </div>
                            </div>
                            <div class="p-4 space-y-4">
                                <div>
                                    <label class="text-xs font-medium text-gray-600 mb-2 block">✏️ Haz click para agregar actividades:</label>
                                    <div class="flex flex-wrap gap-2">
                                        {#each ACTIVIDADES_PRACTICA as act}
                                            {@const selected = formData.practice_activities.includes(act.id)}
                                            <button
                                                type="button"
                                                onclick={() => selected ? quitarActividad('practice', act.id) : agregarActividad('practice', act)}
                                                class="px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-1.5
                                                    {selected ? 'bg-purple-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-purple-100 border border-gray-200'}"
                                            >
                                                <span>{act.icono}</span>
                                                <span>{act.titulo}</span>
                                            </button>
                                        {/each}
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-medium text-gray-600">Descripción de actividades:</label>
                                    <button 
                                        type="button"
                                        onclick={() => limpiarDescripcion('practice')}
                                        class="text-xs px-2 py-1 text-red-600 hover:bg-red-50 rounded transition-colors flex items-center gap-1"
                                        title="Limpiar todo"
                                    >
                                        🗑️ Limpiar
                                    </button>
                                </div>
                                <textarea
                                    bind:value={formData.practice}
                                    rows="3"
                                    class="w-full text-sm rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500"
                                    placeholder="👆 Click en las actividades de arriba para auto-completar..."
                                ></textarea>
                            </div>
                        </div>

                        <!-- MOMENTO 4: TRANSFERENCIA -->
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                            <div class="bg-gradient-to-r from-orange-50 to-amber-50 p-4 border-b border-gray-100">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="w-8 h-8 bg-orange-600 text-white rounded-full flex items-center justify-center font-bold text-sm">4</span>
                                        <div>
                                            <h3 class="font-bold text-gray-800">Transferencia (Cierre)</h3>
                                            <p class="text-xs text-gray-500">Aplicación en nuevos contextos</p>
                                        </div>
                                    </div>
                                    <span class="text-sm text-orange-600 font-medium">{formData.tiempo_transferencia} min</span>
                                </div>
                            </div>
                            <div class="p-4 space-y-4">
                                <div>
                                    <label class="text-xs font-medium text-gray-600 mb-2 block">🌎 Haz click para agregar actividades:</label>
                                    <div class="flex flex-wrap gap-2">
                                        {#each ACTIVIDADES_TRANSFERENCIA as act}
                                            {@const selected = formData.transfer_activities.includes(act.id)}
                                            <button
                                                type="button"
                                                onclick={() => selected ? quitarActividad('transfer', act.id) : agregarActividad('transfer', act)}
                                                class="px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-1.5
                                                    {selected ? 'bg-orange-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-orange-100 border border-gray-200'}"
                                            >
                                                <span>{act.icono}</span>
                                                <span>{act.titulo}</span>
                                            </button>
                                        {/each}
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-medium text-gray-600">Descripción de actividades:</label>
                                    <button 
                                        type="button"
                                        onclick={() => limpiarDescripcion('transfer')}
                                        class="text-xs px-2 py-1 text-red-600 hover:bg-red-50 rounded transition-colors flex items-center gap-1"
                                        title="Limpiar todo"
                                    >
                                        🗑️ Limpiar
                                    </button>
                                </div>
                                <textarea
                                    bind:value={formData.transfer}
                                    rows="3"
                                    class="w-full text-sm rounded-lg border-gray-300 focus:ring-orange-500 focus:border-orange-500"
                                    placeholder="👆 Click en las actividades de arriba para auto-completar..."
                                ></textarea>
                            </div>
                        </div>

                        <!-- MOMENTO 5: VALORACIÓN -->
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                            <div class="bg-gradient-to-r from-green-50 to-teal-50 p-4 border-b border-gray-100">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-sm">5</span>
                                        <div>
                                            <h3 class="font-bold text-gray-800">Valoración (Evaluación)</h3>
                                            <p class="text-xs text-gray-500">Metacognición y evidencia de aprendizaje</p>
                                        </div>
                                    </div>
                                    <span class="text-sm text-green-600 font-medium">{formData.tiempo_valoracion} min</span>
                                </div>
                            </div>
                            <div class="p-4 space-y-4">
                                <div>
                                    <label class="text-xs font-medium text-gray-600 mb-2 block">📋 Haz click para agregar actividades:</label>
                                    <div class="flex flex-wrap gap-2">
                                        {#each ACTIVIDADES_VALORACION as act}
                                            {@const selected = formData.assessment_activities.includes(act.id)}
                                            <button
                                                type="button"
                                                onclick={() => selected ? quitarActividad('assessment', act.id) : agregarActividad('assessment', act)}
                                                class="px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-1.5
                                                    {selected ? 'bg-green-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-green-100 border border-gray-200'}"
                                            >
                                                <span>{act.icono}</span>
                                                <span>{act.titulo}</span>
                                            </button>
                                        {/each}
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-medium text-gray-600">Descripción de actividades:</label>
                                    <button 
                                        type="button"
                                        onclick={() => limpiarDescripcion('assessment')}
                                        class="text-xs px-2 py-1 text-red-600 hover:bg-red-50 rounded transition-colors flex items-center gap-1"
                                        title="Limpiar todo"
                                    >
                                        🗑️ Limpiar
                                    </button>
                                </div>
                                <textarea
                                    bind:value={formData.assessment_moment}
                                    rows="3"
                                    class="w-full text-sm rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500"
                                    placeholder="👆 Click en las actividades de arriba para auto-completar..."
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen visual -->
                    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 p-4 rounded-xl border border-indigo-200">
                        <h4 class="font-semibold text-indigo-800 mb-3 text-sm">📊 Resumen de la Secuencia Didáctica</h4>
                        <div class="grid grid-cols-5 gap-2 text-center">
                            <div class="bg-white p-2 rounded-lg shadow-sm">
                                <div class="text-xs text-gray-500">Exploración</div>
                                <div class="font-bold text-blue-600">{formData.tiempo_exploracion}m</div>
                                <div class="text-xs text-gray-400">{formData.exploration_activities.length} acts</div>
                            </div>
                            <div class="bg-white p-2 rounded-lg shadow-sm">
                                <div class="text-xs text-gray-500">Estructuración</div>
                                <div class="font-bold text-indigo-600">{formData.tiempo_estructuracion}m</div>
                                <div class="text-xs text-gray-400">{formData.structuring_activities.length} acts</div>
                            </div>
                            <div class="bg-white p-2 rounded-lg shadow-sm">
                                <div class="text-xs text-gray-500">Práctica</div>
                                <div class="font-bold text-purple-600">{formData.tiempo_practica}m</div>
                                <div class="text-xs text-gray-400">{formData.practice_activities.length} acts</div>
                            </div>
                            <div class="bg-white p-2 rounded-lg shadow-sm">
                                <div class="text-xs text-gray-500">Transferencia</div>
                                <div class="font-bold text-orange-600">{formData.tiempo_transferencia}m</div>
                                <div class="text-xs text-gray-400">{formData.transfer_activities.length} acts</div>
                            </div>
                            <div class="bg-white p-2 rounded-lg shadow-sm">
                                <div class="text-xs text-gray-500">Valoración</div>
                                <div class="font-bold text-green-600">{formData.tiempo_valoracion}m</div>
                                <div class="text-xs text-gray-400">{formData.assessment_activities.length} acts</div>
                            </div>
                        </div>
                    </div>
                </div>
            {/if}

            <!-- PASO 5: EVALUACIÓN -->
            {#if currentStep === 4}
                <div class="space-y-6 animate-fade-in">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-indigo-800 border-l-4 border-indigo-500 pl-3">
                                📊 Evaluación de Aprendizajes
                            </h2>
                            <p class="text-sm text-gray-500 mt-1">
                                🎯 Sistema interactivo - ¡Selecciona y listo!
                            </p>
                        </div>
                        <button 
                            type="button"
                            onclick={resetearEvaluacion}
                            class="text-xs px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg transition-colors"
                        >
                            🔄 Reiniciar
                        </button>
                    </div>

                    <!-- TIPO DE EVALUACIÓN -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-xl border border-blue-200">
                        <label class="text-sm font-bold text-blue-800 mb-3 block">🔍 Tipo de Evaluación (Decreto 1290)</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            {#each TIPOS_EVALUACION as tipo}
                                {@const selected = formData.eval_type === tipo.id}
                                <button
                                    type="button"
                                    onclick={() => { formData.eval_type = tipo.id; autoGenerarDescripcionEval(); }}
                                    class="p-4 rounded-xl border-2 transition-all duration-200 text-left {selected 
                                        ? 'border-blue-500 bg-white shadow-lg ring-2 ring-blue-200' 
                                        : 'border-gray-200 bg-white/50 hover:border-blue-300 hover:bg-white'}"
                                >
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-2xl">{tipo.icono}</span>
                                        <span class="font-bold {selected ? 'text-blue-700' : 'text-gray-700'}">{tipo.titulo}</span>
                                    </div>
                                    <p class="text-xs text-gray-500">{tipo.descripcion}</p>
                                </button>
                            {/each}
                        </div>
                    </div>

                    <!-- MODALIDADES DE EVALUACIÓN -->
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-4 rounded-xl border border-green-200">
                        <label class="text-sm font-bold text-green-800 mb-3 block">🎓 Modalidades de Evaluación (Artículo 6 - Decreto 1290)</label>
                        <div class="flex flex-wrap gap-3">
                            {#each MODALIDADES_EVALUACION as mod}
                                {@const selected = formData.eval_modalidades.includes(mod.id)}
                                <button
                                    type="button"
                                    onclick={() => toggleModalidad(mod.id)}
                                    class="px-4 py-2.5 rounded-xl border-2 transition-all duration-200 flex items-center gap-2 {selected 
                                        ? 'bg-green-600 border-green-600 text-white shadow-lg' 
                                        : 'bg-white border-gray-200 text-gray-700 hover:border-green-300 hover:bg-green-50'}"
                                >
                                    <span class="text-lg">{mod.icono}</span>
                                    <span class="font-medium">{mod.titulo}</span>
                                    {#if selected}
                                        <span class="ml-1">✓</span>
                                    {/if}
                                </button>
                            {/each}
                        </div>
                    </div>

                    <!-- INSTRUMENTOS DE EVALUACIÓN -->
                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 p-4 rounded-xl border border-purple-200">
                        <label class="text-sm font-bold text-purple-800 mb-3 block">📋 Instrumentos de Evaluación - Click para seleccionar:</label>
                        <div class="flex flex-wrap gap-2">
                            {#each INSTRUMENTOS_EVALUACION as inst}
                                {@const selected = formData.eval_instrumentos.includes(inst.id)}
                                <button
                                    type="button"
                                    onclick={() => toggleInstrumento(inst.id)}
                                    class="px-3 py-2 rounded-lg border-2 transition-all duration-200 flex items-center gap-1.5 text-sm {selected 
                                        ? 'bg-purple-600 border-purple-600 text-white shadow-md' 
                                        : 'bg-white border-gray-200 text-gray-700 hover:border-purple-300 hover:bg-purple-50'}"
                                >
                                    <span>{inst.icono}</span>
                                    <span>{inst.titulo}</span>
                                </button>
                            {/each}
                        </div>
                    </div>

                    <!-- CRITERIOS DE EVALUACIÓN -->
                    <div class="bg-gradient-to-r from-orange-50 to-amber-50 p-4 rounded-xl border border-orange-200">
                        <label class="text-sm font-bold text-orange-800 mb-3 block">🎯 Criterios de Evaluación - Selecciona los que aplicarás:</label>
                        <div class="flex flex-wrap gap-2">
                            {#each CRITERIOS_EVALUACION as crit}
                                {@const selected = formData.eval_criterios.includes(crit.id)}
                                <button
                                    type="button"
                                    onclick={() => toggleCriterio(crit.id)}
                                    class="px-3 py-2 rounded-lg border-2 transition-all duration-200 flex items-center gap-1.5 text-sm {selected 
                                        ? 'bg-orange-600 border-orange-600 text-white shadow-md' 
                                        : 'bg-white border-gray-200 text-gray-700 hover:border-orange-300 hover:bg-orange-50'}"
                                >
                                    <span>{crit.icono}</span>
                                    <span>{crit.titulo}</span>
                                </button>
                            {/each}
                        </div>
                    </div>

                    <!-- EVIDENCIAS DE APRENDIZAJE -->
                    <div class="bg-gradient-to-r from-cyan-50 to-teal-50 p-4 rounded-xl border border-cyan-200">
                        <label class="text-sm font-bold text-cyan-800 mb-3 block">📦 Evidencias de Aprendizaje - Productos esperados:</label>
                        <div class="flex flex-wrap gap-2">
                            {#each EVIDENCIAS_APRENDIZAJE as ev}
                                {@const selected = formData.eval_evidencias.includes(ev.id)}
                                <button
                                    type="button"
                                    onclick={() => toggleEvidencia(ev.id)}
                                    class="px-3 py-2 rounded-lg border-2 transition-all duration-200 flex items-center gap-1.5 text-sm {selected 
                                        ? 'bg-cyan-600 border-cyan-600 text-white shadow-md' 
                                        : 'bg-white border-gray-200 text-gray-700 hover:border-cyan-300 hover:bg-cyan-50'}"
                                >
                                    <span>{ev.icono}</span>
                                    <span>{ev.titulo}</span>
                                </button>
                            {/each}
                        </div>
                    </div>

                    <!-- PONDERACIÓN -->
                    <div class="bg-gradient-to-r from-slate-50 to-gray-50 p-4 rounded-xl border border-slate-200">
                        <label class="text-sm font-bold text-slate-800 mb-4 block">⚖️ Ponderación (Debe sumar 100%)</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm text-gray-600">📚 Conceptos</span>
                                    <span class="text-sm font-bold text-blue-600">{formData.eval_ponderacion_conceptos}%</span>
                                </div>
                                <input 
                                    type="range" 
                                    bind:value={formData.eval_ponderacion_conceptos}
                                    min="0" max="100"
                                    class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600"
                                    onchange={autoGenerarDescripcionEval}
                                />
                            </div>
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm text-gray-600">🔧 Procedimientos</span>
                                    <span class="text-sm font-bold text-purple-600">{formData.eval_ponderacion_procedimientos}%</span>
                                </div>
                                <input 
                                    type="range" 
                                    bind:value={formData.eval_ponderacion_procedimientos}
                                    min="0" max="100"
                                    class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-purple-600"
                                    onchange={autoGenerarDescripcionEval}
                                />
                            </div>
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm text-gray-600">❤️ Actitudes</span>
                                    <span class="text-sm font-bold text-green-600">{formData.eval_ponderacion_actitudes}%</span>
                                </div>
                                <input 
                                    type="range" 
                                    bind:value={formData.eval_ponderacion_actitudes}
                                    min="0" max="100"
                                    class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-green-600"
                                    onchange={autoGenerarDescripcionEval}
                                />
                            </div>
                        </div>
                        <div class="mt-3 text-center">
                            <span class="text-sm font-bold {formData.eval_ponderacion_conceptos + formData.eval_ponderacion_procedimientos + formData.eval_ponderacion_actitudes === 100 ? 'text-green-600' : 'text-red-600'}">
                                Total: {formData.eval_ponderacion_conceptos + formData.eval_ponderacion_procedimientos + formData.eval_ponderacion_actitudes}%
                            </span>
                        </div>
                    </div>

                    <!-- ESCALA DE VALORACIÓN -->
                    <div class="bg-gradient-to-r from-amber-50 to-yellow-50 p-4 rounded-xl border border-amber-200">
                        <label class="text-sm font-bold text-amber-800 mb-3 block">📊 Escala de Valoración (Decreto 1290)</label>
                        <div class="grid grid-cols-5 gap-2">
                            {#each ESCALA_VALORACION as escala}
                                <div class="text-center p-2 bg-white rounded-lg border border-amber-200">
                                    <div class="text-lg font-bold text-amber-600">{escala.valor}</div>
                                    <div class="text-xs font-medium text-gray-700">{escala.label}</div>
                                </div>
                            {/each}
                        </div>
                    </div>

                    <!-- DESCRIPCIÓN AUTO-GENERADA -->
                    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 p-4 rounded-xl border border-indigo-200">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-bold text-indigo-800">📝 Descripción de Evaluación (Auto-generada)</label>
                            <button 
                                type="button"
                                onclick={autoGenerarDescripcionEval}
                                class="text-xs px-2 py-1 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 rounded transition-colors"
                            >
                                🔄 Actualizar
                            </button>
                        </div>
                        <textarea
                            bind:value={formData.eval_descripcion_auto}
                            rows="4"
                            class="w-full text-sm rounded-lg border-indigo-200 focus:ring-indigo-500 focus:border-indigo-500 bg-white/80"
                            placeholder="👆 Selecciona las opciones de arriba para auto-generar la descripción..."
                        ></textarea>
                    </div>

                    <!-- RESUMEN VISUAL -->
                    <div class="bg-gradient-to-r from-slate-100 to-gray-100 p-4 rounded-xl border border-slate-200">
                        <h4 class="font-semibold text-slate-800 mb-3 text-sm">📋 Resumen de la Evaluación</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-center">
                            <div class="bg-white p-3 rounded-lg shadow-sm">
                                <div class="text-xs text-gray-500">Tipo</div>
                                <div class="font-bold text-blue-600">{TIPOS_EVALUACION.find(t => t.id === formData.eval_type)?.titulo || 'No seleccionado'}</div>
                            </div>
                            <div class="bg-white p-3 rounded-lg shadow-sm">
                                <div class="text-xs text-gray-500">Modalidades</div>
                                <div class="font-bold text-green-600">{formData.eval_modalidades.length}</div>
                            </div>
                            <div class="bg-white p-3 rounded-lg shadow-sm">
                                <div class="text-xs text-gray-500">Instrumentos</div>
                                <div class="font-bold text-purple-600">{formData.eval_instrumentos.length}</div>
                            </div>
                            <div class="bg-white p-3 rounded-lg shadow-sm">
                                <div class="text-xs text-gray-500">Evidencias</div>
                                <div class="font-bold text-cyan-600">{formData.eval_evidencias.length}</div>
                            </div>
                        </div>
                    </div>
                </div>
            {/if}

            <!-- PASO 6: RECURSOS DIDÁCTICOS -->
            {#if currentStep === 5}
                <div class="space-y-6 animate-fade-in">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-indigo-800 border-l-4 border-indigo-500 pl-3">
                                📦 Recursos Didácticos
                            </h2>
                            <p class="text-sm text-gray-500 mt-1">
                                🎯 Sistema interactivo - ¡Selecciona los recursos y listo!
                            </p>
                        </div>
                        <button 
                            type="button"
                            onclick={resetearRecursos}
                            class="text-xs px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg transition-colors"
                        >
                            🔄 Reiniciar
                        </button>
                    </div>

                    <!-- CATEGORÍAS DE RECURSOS -->
                    <div class="bg-gradient-to-r from-cyan-50 to-blue-50 p-4 rounded-xl border border-cyan-200">
                        <label class="text-sm font-bold text-cyan-800 mb-3 block">📂 Categorías de Recursos - Selecciona una o varias:</label>
                        <div class="flex flex-wrap gap-2">
                            {#each CATEGORIAS_RECURSOS as cat}
                                {@const selected = recursosCategorias.includes(cat.id)}
                                {@const count = getCountPorCategoria(cat.id)}
                                <button
                                    type="button"
                                    onclick={() => toggleCategoria(cat.id)}
                                    class="px-4 py-2.5 rounded-xl border-2 transition-all duration-200 flex items-center gap-2 {selected 
                                        ? 'bg-cyan-600 border-cyan-600 text-white shadow-lg' 
                                        : 'bg-white border-gray-200 text-gray-700 hover:border-cyan-300 hover:bg-cyan-50'}"
                                >
                                    <span class="text-lg">{cat.icono}</span>
                                    <span class="font-medium">{cat.titulo}</span>
                                    {#if count > 0}
                                        <span class="ml-1 px-2 py-0.5 bg-white/20 rounded-full text-xs">{count}</span>
                                    {/if}
                                </button>
                            {/each}
                        </div>
                    </div>

                    <!-- RECURSOS POR CATEGORÍA SELECCIONADA -->
                    {#if recursosCategorias.length > 0}
                        <div class="space-y-4">
                            {#each recursosCategorias as catId}
                                {@const categoria = CATEGORIAS_RECURSOS.find(c => c.id === catId)}
                                {@const recursos = RECURSOS_POR_CATEGORIA[catId] || []}
                                <div class="bg-white p-4 rounded-xl border border-gray-200">
                                    <div class="flex items-center gap-2 mb-3">
                                        <span class="text-xl">{categoria?.icono}</span>
                                        <h3 class="font-bold text-gray-800">{categoria?.titulo}</h3>
                                        <span class="text-xs text-gray-500">({recursos.filter(r => recursosSeleccionados.includes(r.id)).length} seleccionados)</span>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        {#each recursos as recurso}
                                            {@const selected = recursosSeleccionados.includes(recurso.id)}
                                            <button
                                                type="button"
                                                onclick={() => toggleRecurso(recurso.id)}
                                                class="px-3 py-2 rounded-lg border-2 transition-all duration-200 flex items-center gap-1.5 text-sm {selected 
                                                    ? 'bg-indigo-600 border-indigo-600 text-white shadow-md' 
                                                    : 'bg-gray-50 border-gray-200 text-gray-700 hover:border-indigo-300 hover:bg-indigo-50'}"
                                                title={recurso.descripcion}
                                            >
                                                <span>{recurso.icono}</span>
                                                <span>{recurso.titulo}</span>
                                            </button>
                                        {/each}
                                    </div>
                                </div>
                            {/each}
                        </div>
                    {:else}
                        <div class="bg-amber-50 p-4 rounded-xl border border-amber-200 text-center">
                            <p class="text-amber-700">👆 Selecciona una categoría arriba para ver los recursos disponibles</p>
                        </div>
                    {/if}

                    <!-- RECURSOS PERSONALIZADOS -->
                    <div class="bg-gradient-to-r from-orange-50 to-amber-50 p-4 rounded-xl border border-orange-200">
                        <label class="text-sm font-bold text-orange-800 mb-3 block">➕ Agregar Recurso Personalizado</label>
                        <div class="flex gap-2">
                            <input
                                type="text"
                                bind:value={recursoPersonalizado}
                                placeholder="Escribe un recurso no listed..."
                                class="flex-1 rounded-lg border-orange-200 focus:ring-orange-500 focus:border-orange-500 p-2.5 bg-white/80"
                                onkeydown={(e) => e.key === 'Enter' && agregarRecursoPersonalizado()}
                            />
                            <button
                                type="button"
                                onclick={agregarRecursoPersonalizado}
                                class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-medium transition-colors"
                            >
                                Agregar
                            </button>
                        </div>
                        {#if recursosPersonalizadosLista.length > 0}
                            <div class="flex flex-wrap gap-2 mt-3">
                                {#each recursosPersonalizadosLista as recurso}
                                    <span class="px-3 py-1.5 bg-white border border-orange-200 rounded-full text-sm text-orange-700 flex items-center gap-1">
                                        📌 {recurso}
                                        <button 
                                            type="button"
                                            onclick={() => quitarRecursoPersonalizado(recurso)}
                                            class="ml-1 text-orange-400 hover:text-orange-600"
                                        >
                                            ×
                                        </button>
                                    </span>
                                {/each}
                            </div>
                        {/if}
                    </div>

                    <!-- RECURSOS SELECCIONADOS -->
                    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 p-4 rounded-xl border border-indigo-200">
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-sm font-bold text-indigo-800">📝 Recursos Seleccionados (Auto-generados)</label>
                            <button 
                                type="button"
                                onclick={generarDescripcionRecursos}
                                class="text-xs px-2 py-1 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 rounded transition-colors"
                            >
                                🔄 Actualizar
                            </button>
                        </div>
                        {#if recursosSeleccionados.length > 0 || recursosPersonalizadosLista.length > 0}
                            <div class="bg-white p-3 rounded-lg border border-indigo-100 max-h-48 overflow-y-auto">
                                {#each recursosSeleccionados as recursoId}
                                    {@const recurso = Object.values(RECURSOS_POR_CATEGORIA).flat().find(r => r.id === recursoId)}
                                    {#if recurso}
                                        <div class="flex items-center gap-2 py-1 border-b border-gray-100 last:border-0">
                                            <span>{recurso.icono}</span>
                                            <span class="text-sm text-gray-700">{recurso.titulo}</span>
                                        </div>
                                    {/if}
                                {/each}
                                {#each recursosPersonalizadosLista as recurso}
                                    <div class="flex items-center gap-2 py-1 border-b border-gray-100 last:border-0">
                                        <span>📌</span>
                                        <span class="text-sm text-gray-700">{recurso}</span>
                                    </div>
                                {/each}
                            </div>
                        {:else}
                            <p class="text-sm text-gray-500 text-center py-4">👆 Selecciona recursos de las categorías de arriba</p>
                        {/if}
                    </div>

                    <!-- RESUMEN VISUAL -->
                    <div class="bg-gradient-to-r from-slate-100 to-gray-100 p-4 rounded-xl border border-slate-200">
                        <h4 class="font-semibold text-slate-800 mb-3 text-sm">📊 Resumen de Recursos</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-center">
                            <div class="bg-white p-3 rounded-lg shadow-sm">
                                <div class="text-xs text-gray-500">Categorías</div>
                                <div class="font-bold text-cyan-600">{recursosCategorias.length}</div>
                            </div>
                            <div class="bg-white p-3 rounded-lg shadow-sm">
                                <div class="text-xs text-gray-500">Recursos</div>
                                <div class="font-bold text-indigo-600">{recursosSeleccionados.length}</div>
                            </div>
                            <div class="bg-white p-3 rounded-lg shadow-sm">
                                <div class="text-xs text-gray-500">Personalizados</div>
                                <div class="font-bold text-orange-600">{recursosPersonalizadosLista.length}</div>
                            </div>
                            <div class="bg-white p-3 rounded-lg shadow-sm">
                                <div class="text-xs text-gray-500">Total</div>
                                <div class="font-bold text-green-600">{recursosSeleccionados.length + recursosPersonalizadosLista.length}</div>
                            </div>
                        </div>
                    </div>

                    <!-- MENSAJE FINAL -->
                    <div class="bg-green-50 p-4 rounded-xl border border-green-200 text-center">
                        <p class="text-green-800 font-medium text-lg">
                            🎉 ¡Planeación lista para generar!
                        </p>
                        <p class="text-xs text-green-600 mt-1">
                            Al guardar, se generará un PDF alineado con los formatos institucionales y normativa MEN.
                        </p>
                    </div>
                </div>
            {/if}

            <!-- PASO 2: TEMPORALIDAD -->
            {#if currentStep === 1}
                <div class="space-y-6 animate-fade-in">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-indigo-800 border-l-4 border-indigo-500 pl-3">
                                ⏱️ Temporalidad y Fechas Reales
                            </h2>
                            <p class="text-sm text-gray-500 mt-1">
                                🎯 Define la duración y período de tu planeación
                            </p>
                        </div>
                    </div>

                    <!-- TIPO DE PLANEACIÓN -->
                    <div class="bg-gradient-to-r from-violet-50 to-purple-50 p-4 rounded-xl border border-violet-200">
                        <label class="text-sm font-bold text-violet-800 mb-3 block">📅 Tipo de Planeación - Selecciona:</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            {#each TIPOS_PLANEACION as tipo}
                                {@const selected = formData.planeacion_tipo === tipo.id}
                                <button
                                    type="button"
                                    onclick={() => formData.planeacion_tipo = tipo.id}
                                    class="p-3 rounded-xl border-2 transition-all duration-200 text-left {selected 
                                        ? 'border-violet-500 bg-white shadow-lg ring-2 ring-violet-200' 
                                        : 'border-gray-200 bg-white/50 hover:border-violet-300 hover:bg-white'}"
                                >
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xl">{tipo.icono}</span>
                                        <span class="font-bold text-sm {selected ? 'text-violet-700' : 'text-gray-700'}">{tipo.titulo}</span>
                                    </div>
                                    <p class="text-xs text-gray-500">{tipo.descripcion}</p>
                                </button>
                            {/each}
                        </div>
                    </div>

                    <!-- FECHAS REALES -->
                    <div class="bg-gradient-to-r from-blue-50 to-cyan-50 p-4 rounded-xl border border-blue-200">
                        <label class="text-sm font-bold text-blue-800 mb-3 block">📆 Fechas Reales (yyyy-mm-dd)</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-1">📥 Fecha Inicio</label>
                                <input
                                    id="fecha_inicio"
                                    type="date"
                                    bind:value={formData.fecha_inicio}
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2.5 bg-white/80"
                                />
                            </div>
                            <div>
                                <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-1">📤 Fecha Fin</label>
                                <input
                                    id="fecha_fin"
                                    type="date"
                                    bind:value={formData.fecha_fin}
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2.5 bg-white/80"
                                />
                            </div>
                        </div>
                        
                        <!-- DURACIÓN CALCULADA -->
                        {#if formData.fecha_inicio && formData.fecha_fin}
                            <div class="mt-4 p-3 bg-white rounded-lg border border-blue-200">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-700">⏱️ Duración calculada:</span>
                                    <span class="text-lg font-bold text-blue-600">
                                        {duracionCalculada.dias} días ({duracionCalculada.semanas} semanas académicas)
                                    </span>
                                </div>
                            </div>
                        {/if}
                    </div>

                    <!-- PERÍODO ACADÉMICO -->
                    <div class="bg-gradient-to-r from-emerald-50 to-teal-50 p-4 rounded-xl border border-emerald-200">
                        <label class="text-sm font-bold text-emerald-800 mb-3 block">📚 Período Académico - Selecciona:</label>
                        <div class="flex flex-wrap gap-2">
                            {#each getPeriodosPorTipo() as periodo}
                                {@const selected = formData.periodo_academico === periodo.id}
                                <button
                                    type="button"
                                    onclick={() => formData.periodo_academico = periodo.id}
                                    class="px-4 py-2.5 rounded-xl border-2 transition-all duration-200 {selected 
                                        ? 'bg-emerald-600 border-emerald-600 text-white shadow-lg' 
                                        : 'bg-white border-gray-200 text-gray-700 hover:border-emerald-300 hover:bg-emerald-50'}"
                                >
                                    <span class="font-medium">{periodo.label}</span>
                                </button>
                            {/each}
                        </div>
                    </div>

                    <!-- RESUMEN VISUAL -->
                    <div class="bg-gradient-to-r from-slate-100 to-gray-100 p-4 rounded-xl border border-slate-200">
                        <h4 class="font-semibold text-slate-800 mb-3 text-sm">📊 Resumen de Temporalidad</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-center">
                            <div class="bg-white p-3 rounded-lg shadow-sm">
                                <div class="text-xs text-gray-500">Tipo</div>
                                <div class="font-bold text-violet-600 text-sm">{TIPOS_PLANEACION.find(t => t.id === formData.planeacion_tipo)?.titulo || 'No seleccionado'}</div>
                            </div>
                            <div class="bg-white p-3 rounded-lg shadow-sm">
                                <div class="text-xs text-gray-500">Período</div>
                                <div class="font-bold text-emerald-600 text-sm">{getPeriodosPorTipo().find(p => p.id === formData.periodo_academico)?.label || 'No seleccionado'}</div>
                            </div>
                            <div class="bg-white p-3 rounded-lg shadow-sm">
                                <div class="text-xs text-gray-500">Días</div>
                                <div class="font-bold text-blue-600">{duracionCalculada.dias || 0}</div>
                            </div>
                            <div class="bg-white p-3 rounded-lg shadow-sm">
                                <div class="text-xs text-gray-500">Semanas</div>
                                <div class="font-bold text-purple-600">{duracionCalculada.semanas || 0}</div>
                            </div>
                        </div>
                    </div>

                </div>
            {/if}

            <!-- Panel de Planeaciones Locales -->
            <div class="mt-6 bg-amber-50 rounded-xl border border-amber-200 overflow-hidden">
                <button
                    type="button"
                    onclick={() => { showLocalPanel = !showLocalPanel; if (showLocalPanel) loadPlaneacionesLocales(); }}
                    class="w-full px-4 py-3 flex items-center justify-between bg-amber-100 hover:bg-amber-200 transition-colors text-left"
                >
                    <span class="flex items-center gap-2 font-medium text-amber-800">
                        <span>📂</span> Planeaciones Locales ({planeacionesLocales.length}/100)
                    </span>
                    <span class="text-amber-600">{showLocalPanel ? '▼' : '▶'}</span>
                </button>

                {#if showLocalPanel}
                    <div class="p-4 space-y-4">
                        {#if planeacionesLocales.length === 0}
                            <p class="text-amber-700 text-center py-4">No hay planeaciones guardadas localmente.</p>
                        {:else}
                            <div class="max-h-64 overflow-y-auto space-y-2">
                                {#each planeacionesLocales as planeacion, index}
                                    <div class="flex items-center justify-between bg-white p-3 rounded-lg border border-amber-200">
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-sm text-gray-800 truncate">
                                                {planeacion.subject || 'Sin materia'} - {planeacion.grade || 'Sin grado'}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {new Date(planeacion.fecha_local).toLocaleString('es-CO')}
                                            </p>
                                        </div>
                                        <div class="flex gap-2 ml-2">
                                            <button
                                                type="button"
                                                onclick={() => loadPlaneacionLocal(planeacion)}
                                                class="px-2 py-1 text-xs bg-blue-100 hover:bg-blue-200 text-blue-700 rounded"
                                            >
                                                Cargar
                                            </button>
                                            <button
                                                type="button"
                                                onclick={() => handleDeleteLocal(planeacion.id_local)}
                                                class="px-2 py-1 text-xs bg-red-100 hover:bg-red-200 text-red-700 rounded"
                                            >
                                                ✕
                                            </button>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        {/if}

                        <div class="flex flex-wrap gap-2 pt-2 border-t border-amber-200">
                            <button
                                type="button"
                                onclick={handleExportLocal}
                                disabled={planeacionesLocales.length === 0}
                                class="px-3 py-1.5 text-sm bg-emerald-100 hover:bg-emerald-200 text-emerald-700 rounded-lg disabled:opacity-50"
                            >
                                📥 Exportar JSON
                            </button>
                            <button
                                type="button"
                                onclick={handleImportLocal}
                                class="px-3 py-1.5 text-sm bg-purple-100 hover:bg-purple-200 text-purple-700 rounded-lg"
                            >
                                📤 Importar JSON
                            </button>
                            <button
                                type="button"
                                onclick={handleClearAllLocal}
                                disabled={planeacionesLocales.length === 0}
                                class="px-3 py-1.5 text-sm bg-red-100 hover:bg-red-200 text-red-700 rounded-lg disabled:opacity-50"
                            >
                                🗑️ Limpiar todo
                            </button>
                            <input
                                type="file"
                                accept=".json"
                                bind:this={fileInputRef}
                                onchange={handleFileSelect}
                                class="hidden"
                            />
                        </div>
                    </div>
                {/if}
            </div>

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

                {#if currentStep < 5}
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

    <!-- PDF Preview Modal -->
    {#if showPdfPreview && pdfUrl}
        <div 
            class="fixed inset-0 z-50 flex items-center justify-center p-2 md:p-4 bg-black/60 backdrop-blur-sm"
            transition:fade={{ duration: 200 }}
        >
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-5xl h-[90vh] flex flex-col overflow-hidden">
                <div class="flex items-center justify-between p-4 bg-indigo-600 text-white flex-shrink-0">
                    <h3 class="font-bold text-lg flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Vista Previa PDF - Planeador de Clases
                    </h3>
                    <div class="flex gap-2">
                        <a 
                            href={pdfUrl} 
                            download={`Planeacion_${formData.subject || 'clases'}_${formData.grado || ''}.pdf`}
                            class="px-4 py-2 bg-green-500 hover:bg-green-400 text-white rounded-lg text-sm font-medium flex items-center gap-2 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Descargar
                        </a>
                        <button 
                            onclick={() => showPdfPreview = false}
                            class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg text-sm font-medium transition-colors"
                        >
                            Cerrar
                        </button>
                    </div>
                </div>
                <iframe 
                    src={pdfUrl} 
                    title="Vista Previa PDF" 
                    class="flex-1 w-full border-none bg-gray-100"
                />
            </div>
        </div>
    {/if}

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
                    <Piar onBack={() => showPiarModal = false} />
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
