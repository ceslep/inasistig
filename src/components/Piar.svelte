<script lang="ts">
    import { onMount } from "svelte";
    import { fade } from "svelte/transition";

    import { getDocentes, getEstudiantes, savePiar, type PiarData } from "../../api/service";
    import { HTML2PDF_CDN_URL, GOOGLE_CLIENT_ID } from "../constants";
    import { docenteName, findMatchingDocente } from "../lib/authStore";

    // html2pdf se carga dinámicamente desde CDN
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    type Html2PdfWindow = Window & typeof globalThis & { html2pdf?: any };

    interface Estudiante {
        nombre: string;
        grado: number | string;
    }

    import ModuleHeader from "./ModuleHeader.svelte";
    import DriveFolderPicker from "./DriveFolderPicker.svelte";
    import UploadProgressModal from "./UploadProgressModal.svelte";
    import { gdriveService, isUploading } from "../lib/gdriveService";
    import { FileText, Save, Upload, Sparkles } from "@lucide/svelte";
    import Swal from "sweetalert2";

    let { onBack }: { onBack: () => void } = $props();

    // ==========================================
    // 1. ESTADO DE LA APLICACIÓN (SVELTE 5 RUNES)
    // ==========================================
    let currentStep = $state(1);
    let isGeneratingPdf = $state(false);
    let showSavedNotification = $state(false);

    // Drive Upload states
    let isSavingOnline = $state(false);
    let showUploadProgress = $state(false);
    let uploadPhase = $state<'saving' | 'generating' | 'uploading'>('saving');
    let uploadCurrent = $state(0);
    let uploadTotal = $state(0);
    let uploadCurrentFile = $state('');
    let uploadSuccessCount = $state(0);
    let uploadFailedCount = $state(0);
    let showFolderPicker = $state(false);
    let selectedFolderId = $state<string | null>(null);
    let pdfBlobToUpload = $state<Blob | null>(null);
    let pdfFileNameToUpload = $state('');

    // Datos de API para selección de docente y estudiante
    let docentes: string[] = $state([]);
    let estudiantes: Estudiante[] = $state.raw([]);
    let isLoadingDocentes = $state(false);
    let isLoadingEstudiantes = $state(false);
    let selectedGrado = $state("");
    let selectedEstudiante = $state("");

    let gradosDisponibles = $derived(
        [...new Set(estudiantes.map((e) => e.grado.toString()))].sort(),
    );

    let estudiantesFiltrados = $derived(
        selectedGrado
            ? estudiantes.filter(
                  (e) => e.grado.toString() === selectedGrado,
              )
            : [],
    );

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
        fechaDiligenciamiento: new Date().toLocaleDateString("en-CA"),
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

    // Cargar desde LocalStorage al iniciar + datos de API
    onMount(async () => {
        const saved = localStorage.getItem("piar_draft");
        if (saved) {
            try {
                const parsed = JSON.parse(saved);
                piar = { ...piar, ...parsed };
                // Si fecha vacía, poner fecha actual
                if (!piar.fechaDiligenciamiento) {
                    piar.fechaDiligenciamiento = new Date().toLocaleDateString("en-CA");
                }
            } catch (e) {
                console.error("Error cargando PIAR", e);
            }
        }

        // Cargar docentes y estudiantes
        isLoadingDocentes = true;
        isLoadingEstudiantes = true;
        try {
            const [docentesData, estudiantesData] = await Promise.all([
                getDocentes(),
                getEstudiantes(),
            ]);
            docentes = docentesData;
            estudiantes = estudiantesData;

            // Auto-seleccionar docente
            if (!piar.quienDiligencia) {
                const lastDocente = localStorage.getItem("lastDocente");
                if (lastDocente && docentesData.includes(lastDocente)) {
                    piar.quienDiligencia = lastDocente;
                } else {
                    const match = findMatchingDocente(docentesData, $docenteName);
                    if (match) piar.quienDiligencia = match;
                }
            }
        } catch (error) {
            console.error("Error cargando datos:", error);
        } finally {
            isLoadingDocentes = false;
            isLoadingEstudiantes = false;
        }
    });

    // Persistir docente seleccionado
    $effect(() => {
        if (piar.quienDiligencia) {
            localStorage.setItem("lastDocente", piar.quienDiligencia);
        }
    });

    // Auto-llenar nombre y apellidos al seleccionar estudiante
    // Formato: APELLIDO1 APELLIDO2 NOMBRE1 [NOMBRE2]
    function onEstudianteSelect(nombre: string) {
        selectedEstudiante = nombre;
        if (!nombre) return;
        const partes = nombre.trim().split(/\s+/);
        if (partes.length >= 4) {
            piar.apellidos = partes.slice(0, 2).join(" ");
            piar.nombres = partes.slice(2).join(" ");
        } else if (partes.length === 3) {
            piar.apellidos = partes.slice(0, 2).join(" ");
            piar.nombres = partes[2];
        } else {
            piar.nombres = nombre;
            piar.apellidos = "";
        }
    }

    // Guardado automático cada vez que 'piar' cambia usando $effect
    $effect(() => {
        localStorage.setItem("piar_draft", JSON.stringify(piar));
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

    // Guardar en Google Sheets
    async function guardarOnline(): Promise<void> {
        if (!piar.nombres || !piar.apellidos) {
            await Swal.fire({
                icon: "warning",
                title: "Faltan datos",
                text: "Por favor ingrese el nombre y apellido del estudiante.",
            });
            return;
        }

        isSavingOnline = true;
        showUploadProgress = true;
        uploadPhase = 'saving';
        uploadCurrent = 1;
        uploadTotal = 1;
        uploadCurrentFile = 'Guardando en Google Sheets...';

        try {
            const piarData: PiarData = {
                id: `PIAR_${Date.now()}_${Math.random().toString(36).substring(2, 9)}`,
                fecha_creacion: new Date().toISOString().split('T')[0],
                docente: piar.quienDiligencia,
                nombres: piar.nombres,
                apellidos: piar.apellidos,
                tipoDoc: piar.tipoDoc,
                numDoc: piar.numDoc,
                grado: selectedGrado || piar.gradoAspira,
                fechaNacimiento: piar.fechaNacimiento,
                lugarNacimiento: piar.lugarNacimiento,
                telefono: piar.telefono,
                correo: piar.correo,
                barrio: piar.barrio,
                eps: piar.eps,
                tieneDiagnostico: piar.tieneDiagnostico === "SI",
                cualDiagnostico: piar.cualDiagnostico,
                asisteTerapias: piar.asisteTerapias === "SI",
                terapias: piar.terapias.filter(t => t.tipo).map(t => `${t.tipo} (${t.frecuencia})`).join(', '),
                tratamientoMedico: piar.tratamientoMedico === "SI",
                cualTratamiento: piar.cualTratamiento,
                productosApoyo: piar.productosApoyo === "SI",
                cualesApoyos: piar.cualesApoyos,
                nombreMadre: piar.nombreMadre,
                nombrePadre: piar.nombrePadre,
                nombreCuidador: piar.nombreCuidador,
                telefonoCuidador: piar.telefonoCuidador,
                vinculadoOtraInstitucion: piar.vinculadoOtraInstitucion === "SI",
                observacionesGrado: piar.observacionesGrado,
                descripcionEstudiante: piar.descripcionEstudiante,
                habilidadesCompetencias: piar.habilidadesCompetencias,
                recomendacionesFamilia: piar.recomendacionesFamilia,
                compromisosEspecificos: piar.compromisosEspecificos,
            };

            await savePiar(piarData);
            uploadSuccessCount = 1;
            uploadCurrentFile = 'Guardado exitosamente en Google Sheets';

            await Swal.fire({
                icon: "success",
                title: "Guardado",
                text: "El PIAR se ha guardado en Google Sheets.",
                confirmButtonColor: "#22c55e",
            });
        } catch (error) {
            console.error("Error guardando PIAR:", error);
            uploadFailedCount = 1;
            await Swal.fire({
                icon: "error",
                title: "Error",
                text: "No se pudo guardar el PIAR. Verifique la conexión.",
                confirmButtonColor: "#ef4444",
            });
        } finally {
            isSavingOnline = false;
            showUploadProgress = false;
        }
    }

    // Generar PDF y guardar en Drive
    async function generarPDFYGuardarDrive(): Promise<void> {
        isGeneratingPdf = true;
        showUploadProgress = true;
        uploadPhase = 'generating';
        uploadCurrent = 0;
        uploadTotal = 2;
        uploadCurrentFile = 'Generando PDF...';

        try {
            await new Promise((r) => setTimeout(r, 200));

            const element = document.getElementById("documento-pdf");
            if (!element) throw new Error("No se encontró el elemento del documento");

            element.classList.remove("hidden");

            if ((window as Html2PdfWindow).html2pdf) {
                const fileName = `PIAR_${piar.nombres || 'Estudiante'}_${piar.apellidos || ''}.pdf`.replace(/\s+/g, '_');
                pdfFileNameToUpload = fileName;

                const opt = {
                    margin: 10,
                    filename: fileName,
                    image: { type: "jpeg", quality: 0.98 },
                    html2canvas: { scale: 2, useCORS: true },
                    jsPDF: {
                        unit: "mm",
                        format: "legal",
                        orientation: "portrait",
                    },
                };

                const pdf = (window as Html2PdfWindow).html2pdf().set(opt).from(element);
                const blob = await pdf.outputPdf('blob');
                pdfBlobToUpload = blob;

                uploadCurrent = 1;
                uploadPhase = 'uploading';
                uploadCurrentFile = 'Seleccionando carpeta de Drive...';
                showFolderPicker = true;
            } else {
                window.print();
                showUploadProgress = false;
            }
        } catch (error) {
            console.error("Error generando PDF:", error);
            await Swal.fire({
                icon: "error",
                title: "Error",
                text: "No se pudo generar el PDF.",
                confirmButtonColor: "#ef4444",
            });
            showUploadProgress = false;
        } finally {
            isGeneratingPdf = false;
            const element = document.getElementById("documento-pdf");
            if (element) element.classList.add("hidden");
        }
    }

    // Handle folder selection from Drive
    async function handleFolderSelected(folder: { id: string; name: string } | null): Promise<void> {
        if (!folder) {
            showFolderPicker = false;
            pdfBlobToUpload = null;
            pdfFileNameToUpload = '';
            showUploadProgress = false;
            return;
        }

        selectedFolderId = folder.id;
        showFolderPicker = false;
        uploadCurrentFile = `Subiendo PDF a Drive...`;

        const clientId = GOOGLE_CLIENT_ID;
        const result = await gdriveService.uploadFile(
            pdfBlobToUpload!,
            pdfFileNameToUpload,
            'application/pdf',
            clientId,
            folder.id
        );

        if (result.success) {
            uploadSuccessCount = 1;
            await Swal.fire({
                icon: "success",
                title: "Guardado en Drive",
                text: `El PDF se ha guardado en la carpeta "${folder.name}".`,
                confirmButtonColor: "#22c55e",
            });
        } else {
            uploadFailedCount = 1;
            await Swal.fire({
                icon: "error",
                title: "Error",
                text: `No se pudo subir el PDF: ${result.error}`,
                confirmButtonColor: "#ef4444",
            });
        }

        pdfBlobToUpload = null;
        pdfFileNameToUpload = '';
        showUploadProgress = false;
    }

    // Inyectar html2pdf por CDN
    onMount(() => {
        const script = document.createElement("script");
        script.src = HTML2PDF_CDN_URL;
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
        <label for={id} class="text-sm font-medium text-[rgb(var(--text-secondary))]"
            >{label}</label
        >
        {#if type === "textarea"}
            <textarea
                {id}
                class="border border-[rgb(var(--border-primary))] rounded-lg p-2.5 text-sm bg-[rgb(var(--bg-secondary))] text-[rgb(var(--text-primary))] focus:ring-2 focus:ring-[rgb(var(--accent-primary))]/40 focus:border-[rgb(var(--accent-primary))] outline-none transition-colors"
                rows="3"
                {placeholder}
                bind:value={obj[key]}
            ></textarea>
        {:else}
            <input
                {type}
                {id}
                class="border border-[rgb(var(--border-primary))] rounded-lg p-2.5 text-sm bg-[rgb(var(--bg-secondary))] text-[rgb(var(--text-primary))] focus:ring-2 focus:ring-[rgb(var(--accent-primary))]/40 focus:border-[rgb(var(--accent-primary))] outline-none transition-colors"
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
        <label for={id} class="text-sm font-medium text-[rgb(var(--text-secondary))]"
            >{label}</label
        >
        <select
            {id}
            class="border border-[rgb(var(--border-primary))] rounded-lg p-2.5 text-sm bg-[rgb(var(--bg-secondary))] text-[rgb(var(--text-primary))] focus:ring-2 focus:ring-[rgb(var(--accent-primary))]/40 focus:border-[rgb(var(--accent-primary))] outline-none transition-colors"
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
<ModuleHeader title="Registro PIAR" subtitle="Plan Individual de Ajustes Razonables" {onBack}>
    {#snippet actions()}
        {#if showSavedNotification}
            <span class="text-xs text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">
                Guardado localmente
            </span>
        {/if}
        <button
            onclick={resetForm}
            class="min-h-[44px] px-3 py-2 text-sm text-red-500 border border-red-500/30 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl font-medium transition-colors"
        >Nuevo / Borrar</button>
        <button
            onclick={guardarOnline}
            disabled={isSavingOnline || $isUploading}
            class="min-h-[44px] px-3 py-2 bg-blue-500/10 text-blue-600 border border-blue-500/30 rounded-xl font-bold transition-colors flex items-center gap-2 disabled:opacity-60"
        >
            {#if isSavingOnline}
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm">Guardando...</span>
            {:else}
                <Save class="w-4 h-4" />
                <span class="text-sm">Guardar en Nube</span>
            {/if}
        </button>
        <button
            onclick={generarPDFYGuardarDrive}
            disabled={isGeneratingPdf || $isUploading}
            class="min-h-[44px] px-3 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-xl font-bold transition-colors flex items-center gap-2 disabled:opacity-60"
        >
            {#if isGeneratingPdf}
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm">Procesando...</span>
            {:else}
                <Sparkles class="w-4 h-4" />
                <span class="text-sm">Generar y Enviar a Drive</span>
            {/if}
        </button>
        <button
            onclick={generarPDF}
            disabled={isGeneratingPdf}
            class="min-h-[44px] px-3 py-2 bg-emerald-500/10 text-emerald-600 border border-emerald-500/30 rounded-xl font-bold transition-colors flex items-center gap-2 disabled:opacity-60"
        >
            {#if isGeneratingPdf}
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm">Generando...</span>
            {:else}
                <FileText class="w-4 h-4" />
                <span class="text-sm">Exportar PDF</span>
            {/if}
        </button>
    {/snippet}
</ModuleHeader>

<div class="min-h-screen bg-[rgb(var(--bg-primary))] font-sans print:bg-white text-[rgb(var(--text-primary))]">

    <div
        class="max-w-7xl mx-auto p-4 md:p-6 grid grid-cols-1 md:grid-cols-4 gap-6 print:hidden"
    >
        <!-- Sidebar / Stepper -->
        <aside class="col-span-1">
            <div
                class="bg-[rgb(var(--card-bg))] rounded-xl border border-[rgb(var(--card-border))] p-4 sticky top-24"
            >
                <h2
                    class="text-sm font-bold text-[rgb(var(--text-muted))] uppercase tracking-wider mb-4"
                >
                    Progreso del PIAR
                </h2>
                <nav class="space-y-1.5">
                    {#each steps as step}
                        <button
                            onclick={() => (currentStep = step.id)}
                            class="w-full text-left px-3 py-2.5 rounded-lg text-sm font-medium transition-all cursor-pointer min-h-[44px] flex items-center {currentStep ===
                            step.id
                                ? 'bg-[rgb(var(--accent-primary))]/10 text-[rgb(var(--accent-primary))] border border-[rgb(var(--accent-primary))]/20'
                                : 'text-[rgb(var(--text-secondary))] hover:bg-[rgb(var(--bg-secondary))] border border-transparent'}"
                        >
                            <span
                                class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold mr-2 shrink-0 {currentStep ===
                                step.id
                                    ? 'bg-[rgb(var(--accent-primary))] text-white'
                                    : 'bg-[rgb(var(--bg-secondary))] text-[rgb(var(--text-muted))]'}"
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
                class="bg-[rgb(var(--card-bg))] rounded-xl border border-[rgb(var(--card-border))] p-5 md:p-8"
            >
                <!-- STEP 1: INFORMACIÓN GENERAL -->
                {#if currentStep === 1}
                    <div class="space-y-6 animate-in fade-in">
                        <div class="border-b pb-4 mb-4">
                            <h2 class="text-xl font-bold text-[rgb(var(--text-primary))] sm:text-2xl">
                                1. Información General del Estudiante
                            </h2>
                            <p class="text-sm text-[rgb(var(--text-muted))]">
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
                            <div class="flex flex-col gap-1 col-span-1 md:col-span-2">
                                <label for="p_dil" class="text-sm font-medium text-[rgb(var(--text-secondary))]">Persona que diligencia</label>
                                <select
                                    id="p_dil"
                                    bind:value={piar.quienDiligencia}
                                    disabled={isLoadingDocentes}
                                    class="border border-[rgb(var(--border-primary))] rounded-lg p-2.5 text-sm bg-[rgb(var(--bg-secondary))] text-[rgb(var(--text-primary))] focus:ring-2 focus:ring-[rgb(var(--accent-primary))]/40 focus:border-[rgb(var(--accent-primary))] outline-none transition-colors"
                                >
                                    <option value="">
                                        {isLoadingDocentes ? "Cargando docentes..." : "Seleccione docente"}
                                    </option>
                                    {#each docentes as docente}
                                        <option value={docente}>{docente}</option>
                                    {/each}
                                </select>
                            </div>
                        </div>

                        <!-- Selección de estudiante por grado -->
                        <h3
                            class="font-semibold text-lg mt-6 text-[rgb(var(--accent-primary))] bg-[rgb(var(--accent-primary))]/5 p-2.5 rounded-lg"
                        >
                            Seleccionar Estudiante
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="flex flex-col gap-1">
                                <label for="sel_grado" class="text-sm font-medium text-[rgb(var(--text-secondary))]">Grado</label>
                                <select
                                    id="sel_grado"
                                    bind:value={selectedGrado}
                                    disabled={isLoadingEstudiantes}
                                    class="border border-[rgb(var(--border-primary))] rounded-lg p-2.5 text-sm bg-[rgb(var(--bg-secondary))] text-[rgb(var(--text-primary))] focus:ring-2 focus:ring-[rgb(var(--accent-primary))]/40 focus:border-[rgb(var(--accent-primary))] outline-none transition-colors"
                                >
                                    <option value="">
                                        {isLoadingEstudiantes ? "Cargando..." : "Seleccione grado"}
                                    </option>
                                    {#each gradosDisponibles as g}
                                        <option value={g}>{g}</option>
                                    {/each}
                                </select>
                            </div>
                            <div class="flex flex-col gap-1 col-span-1 md:col-span-2">
                                <label for="sel_est" class="text-sm font-medium text-[rgb(var(--text-secondary))]">Estudiante</label>
                                <select
                                    id="sel_est"
                                    bind:value={selectedEstudiante}
                                    disabled={!selectedGrado || estudiantesFiltrados.length === 0}
                                    onchange={() => onEstudianteSelect(selectedEstudiante)}
                                    class="border border-[rgb(var(--border-primary))] rounded-lg p-2.5 text-sm bg-[rgb(var(--bg-secondary))] text-[rgb(var(--text-primary))] focus:ring-2 focus:ring-[rgb(var(--accent-primary))]/40 focus:border-[rgb(var(--accent-primary))] outline-none transition-colors"
                                >
                                    <option value="">
                                        {!selectedGrado ? "Primero seleccione un grado" : estudiantesFiltrados.length === 0 ? "Sin estudiantes en este grado" : "Seleccione estudiante"}
                                    </option>
                                    {#each estudiantesFiltrados as est}
                                        <option value={est.nombre}>{est.nombre}</option>
                                    {/each}
                                </select>
                            </div>
                        </div>

                        <h3
                            class="font-semibold text-lg mt-6 text-[rgb(var(--accent-primary))] bg-[rgb(var(--accent-primary))]/5 p-2.5 rounded-lg"
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
                            class="font-semibold text-lg mt-6 text-[rgb(var(--accent-primary))] bg-[rgb(var(--accent-primary))]/5 p-2.5 rounded-lg"
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
                            class="font-semibold text-lg mt-6 text-[rgb(var(--accent-primary))] bg-[rgb(var(--accent-primary))]/5 p-2.5 rounded-lg"
                        >
                            Condiciones Especiales
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div
                                class="col-span-1 md:col-span-2 flex items-center gap-4 bg-[rgb(var(--bg-secondary))] p-3 rounded-lg border border-[rgb(var(--border-primary))]"
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
                                class="col-span-1 md:col-span-2 flex items-center gap-4 bg-[rgb(var(--bg-secondary))] p-3 rounded-lg border border-[rgb(var(--border-primary))]"
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
                            <h2 class="text-xl font-bold text-[rgb(var(--text-primary))] sm:text-2xl">
                                2. Entorno Salud y Hogar
                            </h2>
                        </div>

                        <!-- SALUD -->
                        <h3
                            class="font-semibold text-lg text-[rgb(var(--accent-primary))] bg-[rgb(var(--accent-primary))]/5 p-2.5 rounded-lg border-l-4 border-[rgb(var(--accent-primary))]"
                        >
                            Salud
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div
                                class="col-span-1 flex flex-col gap-2 bg-[rgb(var(--bg-secondary))] p-3 rounded-lg border border-[rgb(var(--border-primary))]"
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
                            class="space-y-4 bg-amber-500/5 p-4 rounded-lg border border-amber-500/20"
                        >
                            <h4
                                class="text-sm font-bold text-amber-600 mb-2 flex items-center gap-2"
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
                                                class="text-sm font-medium text-[rgb(var(--text-secondary))]"
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
                            <div class="mt-4 pt-4 border-t border-amber-500/20">
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
                                class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 pt-4 border-t border-amber-500/20"
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
                            class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-[rgb(var(--bg-secondary))] p-4 border border-[rgb(var(--border-primary))] rounded-lg"
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
                            <h2 class="text-xl font-bold text-[rgb(var(--text-primary))] sm:text-2xl">
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
                                class="bg-[rgb(var(--bg-secondary))] p-4 border border-[rgb(var(--border-primary))] rounded-lg"
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
                                class="bg-[rgb(var(--bg-secondary))] p-4 border border-[rgb(var(--border-primary))] rounded-lg"
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

                        <h3 class="font-bold text-[rgb(var(--text-secondary))] mt-6 border-b pb-2">
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
                            <h2 class="text-xl font-bold text-[rgb(var(--text-primary))] sm:text-2xl">
                                4. Valoración Pedagógica Inicial
                            </h2>
                            <p class="text-sm text-[rgb(var(--text-muted))]">
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
                                    class="font-semibold text-[rgb(var(--text-secondary))]"
                                    >1. Descripción general del estudiante</label
                                >
                                <span class="text-xs text-[rgb(var(--text-muted))] block -mt-2"
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
                                    class="font-semibold text-[rgb(var(--text-secondary))]"
                                    >2. Descripción de habilidades, competencias
                                    y apoyos</label
                                >
                                <span class="text-xs text-[rgb(var(--text-muted))] block -mt-2"
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
                                <h2 class="text-xl font-bold text-[rgb(var(--text-primary))] sm:text-2xl">
                                    5. Ajustes Razonables por Trimestre
                                </h2>
                                <p class="text-sm text-[rgb(var(--text-muted))]">
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
                                            class="border border-[rgb(var(--border-primary))] rounded-lg overflow-hidden bg-[rgb(var(--card-bg))]"
                                        >
                                            <div
                                                class="bg-slate-100 p-3 font-semibold text-[rgb(var(--text-secondary))] border-b flex justify-between items-center"
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
                                                        class="block text-xs font-bold text-[rgb(var(--text-muted))] mb-1"
                                                        >Objetivos/Propósitos
                                                        (DBA)</label
                                                    >
                                                    <textarea
                                                        id={`obj_${tIndex}_${aIndex}`}
                                                        rows="2"
                                                        class="w-full text-sm p-2 border border-[rgb(var(--border-primary))] rounded-lg bg-[rgb(var(--bg-secondary))] text-[rgb(var(--text-primary))]"
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
                        <p class="text-xs text-[rgb(var(--text-muted))] mt-2">
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
                            <h2 class="text-xl font-bold text-[rgb(var(--text-primary))] sm:text-2xl">
                                6. Recomendaciones para el Plan de Mejoramiento
                            </h2>
                            <p class="text-sm text-[rgb(var(--text-muted))]">
                                Acciones institucionales para la eliminación de
                                barreras.
                            </p>
                        </div>

                        <div class="space-y-4">
                            <div class="bg-[rgb(var(--bg-secondary))] p-4 rounded-lg border border-[rgb(var(--border-primary))]">
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

                            <div class="bg-[rgb(var(--bg-secondary))] p-4 rounded-lg border border-[rgb(var(--border-primary))]">
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

                            <div class="bg-[rgb(var(--bg-secondary))] p-4 rounded-lg border border-[rgb(var(--border-primary))]">
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

                            <div class="bg-[rgb(var(--bg-secondary))] p-4 rounded-lg border border-[rgb(var(--border-primary))]">
                                <label
                                    for="rec_adm"
                                    class="font-bold text-[rgb(var(--text-secondary))] block mb-2"
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

                            <div class="bg-[rgb(var(--bg-secondary))] p-4 rounded-lg border border-[rgb(var(--border-primary))]">
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
                            <h2 class="text-xl font-bold text-[rgb(var(--text-primary))] sm:text-2xl">
                                7. Acta de Acuerdo (Anexo 3)
                            </h2>
                            <p class="text-sm text-[rgb(var(--text-muted))]">
                                Compromisos de la familia y el estudiante.
                            </p>
                        </div>

                        <div
                            class="bg-[rgb(var(--bg-secondary))] p-5 rounded-lg border border-[rgb(var(--border-primary))] text-sm text-[rgb(var(--text-secondary))] space-y-4 mb-6"
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
                                    class="w-full border border-[rgb(var(--border-primary))] rounded-lg p-2.5 bg-[rgb(var(--bg-secondary))] text-[rgb(var(--text-primary))] min-h-[80px]"
                                    bind:value={piar.compromisosEspecificos}
                                    placeholder="Añadir detalles adicionales a los del PIAR..."
                                ></textarea>
                            </div>
                        </div>

                        <h3 class="font-bold text-lg text-[rgb(var(--text-primary))]">
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
                                                    class="w-full border border-[rgb(var(--border-primary))] rounded p-1.5 bg-[rgb(var(--bg-secondary))] text-[rgb(var(--text-primary))]"
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

<!-- Drive Folder Picker for PDF Upload -->
{#if showFolderPicker}
    <DriveFolderPicker 
        onSelect={handleFolderSelected} 
        onClose={() => { showFolderPicker = false; pdfBlobToUpload = null; pdfFileNameToUpload = ''; }}
    />
{/if}

<!-- Upload Progress Modal -->
{#if showUploadProgress}
    <UploadProgressModal 
        phase={uploadPhase}
        current={uploadCurrent}
        total={uploadTotal}
        currentFile={uploadCurrentFile}
        successCount={uploadSuccessCount}
        failedCount={uploadFailedCount}
        fileType="pdf"
    />
{/if}
