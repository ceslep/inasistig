<script>
    import { onMount } from "svelte";
    const browser = typeof window !== "undefined";

    let { onBack } = $props();

    // Svelte 5 Runes
    let piarState = $state({
        currentStep: 1,
        isSaving: false,
        showPreview: false,
        formData: {
            // Información General
            general: {
                fechaDiligenciamiento: "",
                lugarDiligenciamiento: "",
                personaDiligencia: "",
                rolPersona: "",
                nombres: "",
                apellidos: "",
                lugarNacimiento: "",
                fechaNacimiento: "",
                edad: "",
                tipoDocumento: "TI",
                numeroDocumento: "",
                departamento: "",
                municipio: "",
                direccion: "",
                barrio: "",
                telefono: "",
                correo: "",
                centroProteccion: "NO",
                lugarCentroProteccion: "",
                gradoAspira: "",
                grupoEtnico: "NO",
                cualGrupoEtnico: "",
                victimaConflicto: "NO",
                registroVictima: "NO",
            },
            // Entorno Salud
            salud: {
                afiliacionSalud: "SI",
                eps: "",
                tipoAfiliacion: "Subsidiado",
                lugarEmergencia: "",
                atencionSalud: "NO",
                frecuenciaAtencion: "",
                diagnosticoMedico: "NO",
                cualDiagnostico: "",
                asisteTerapias: "NO",
                tipoTerapia: "",
                frecuenciaTerapia: "",
                tratamientoEnfermedad: "NO",
                cualTratamiento: "",
                consumeMedicamentos: "NO",
                medicamentosDetalle: "",
                productosApoyo: "NO",
                cualesProductos: "",
            },
            // Entorno Hogar
            hogar: {
                nombreMadre: "",
                ocupacionMadre: "",
                nivelEducativoMadre: "",
                nombrePadre: "",
                ocupacionPadre: "",
                nivelEducativoPadre: "",
                nombreCuidador: "",
                parentescoCuidador: "",
                nivelEducativoCuidador: "",
                telefonoCuidador: "",
                correoCuidador: "",
                numeroHermanos: "",
                lugarOcupa: "",
                quienesApoyan: "",
                personasVive: "",
                bajoProteccion: "NO",
                recibeSubsidio: "NO",
                cualSubsidio: "",
            },
            // Entorno Educativo
            educativo: {
                vinculadoOtraInstitucion: "NO",
                cualInstitucion: "",
                ultimoGrado: "",
                aproboUltimoGrado: "SI",
                observacionesTrayectoria: "",
                informePedagogico: "NO",
                institucionInforme: "",
                programasComplementarios: "NO",
                cualesProgramas: "",
                nombreInstitucionMatricula: "",
                sede: "",
                medioTransporte: "",
                distanciaTiempo: "",
            },
            // PIAR - Características del Estudiante
            piar: {
                fechaElaboracion: "",
                institucionEducativa: "",
                sede: "",
                jornada: "",
                docentesElaboran: "",
                caracteristicasEstudiante: "",
                habilidadesCompetencias: "",
            },
            // Ajustes Razonables - Trimestres
            ajustes: {
                primerTrimestre: [
                    {
                        area: "Matemáticas",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        area: "Ciencias",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        area: "Lenguaje",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        area: "Convivencia",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        area: "Socialización",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        area: "Participación",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        area: "Autonomía",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        area: "Autocontrol",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                ],
                segundoTrimestre: [
                    {
                        area: "Matemáticas",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        area: "Ciencias",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        area: "Lenguaje",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        area: "Convivencia",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        area: "Socialización",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        area: "Participación",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        area: "Autonomía",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        area: "Autocontrol",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                ],
                tercerTrimestre: [
                    {
                        area: "Matemáticas",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        area: "Ciencias",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        area: "Lenguaje",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        area: "Convivencia",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        area: "Socialización",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        area: "Participación",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        area: "Autonomía",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                    {
                        area: "Autocontrol",
                        objetivos: "",
                        barreras: "",
                        ajustes: "",
                        evaluacion: "",
                    },
                ],
            },
            // Recomendaciones
            recomendaciones: {
                familia: { acciones: "", estrategias: "" },
                docentes: { acciones: "", estrategias: "" },
                directivos: { acciones: "", estrategias: "" },
                administrativos: { acciones: "", estrategias: "" },
                pares: { acciones: "", estrategias: "" },
            },
            // Acta de Acuerdo
            acta: {
                fecha: "",
                institucionSede: "",
                nombreEstudiante: "",
                documentoIdentificacion: "",
                edad: "",
                grado: "",
                equipoDirectivos: "",
                equipoDocentes: "",
                familiaNombres: "",
                parentesco: "",
                compromisosAula: "",
                actividadesCasa: [],
            },
        },
    });

    // Steps configuration
    const steps = $state([
        { id: 0, name: "Información General", icon: "📋" },
        { id: 1, name: "Entorno Salud", icon: "🏥" },
        { id: 2, name: "Entorno Hogar", icon: "🏠" },
        { id: 3, name: "Entorno Educativo", icon: "📚" },
        { id: 4, name: "PIAR - Características", icon: "👤" },
        { id: 5, name: "Ajustes Trimestre 1", icon: "📊" },
        { id: 6, name: "Ajustes Trimestre 2", icon: "📈" },
        { id: 7, name: "Ajustes Trimestre 3", icon: "📉" },
        { id: 8, name: "Recomendaciones", icon: "💡" },
        { id: 9, name: "Acta de Acuerdo", icon: "✍️" },
    ]);

    // Pedagogical helpers for inclusive education
    const inclusiveEducationHelpers = $state({
        barrerasComunes: [
            "Accesibilidad física limitada",
            "Materiales no adaptados",
            "Comunicación limitada",
            "Falta de apoyos visuales",
            "Tiempo insuficiente para actividades",
            "Ambiente sensorialmente sobrecargado",
            "Expectativas académicas rígidas",
            "Falta de capacitación docente",
            "Actitudes discriminatorias",
            "Infraestructura inadecuada",
        ],
        ajustesRazonablesSugeridos: [
            "Adaptación de materiales (tamaño, color, textura)",
            "Tiempo adicional para pruebas y actividades",
            "Uso de tecnología asistiva",
            "Apoyos visuales y pictogramas",
            "Modificación de criterios de evaluación",
            "Ubicación preferencial en el aula",
            "Descansos programados",
            "Instrucciones claras y segmentadas",
            "Uso de organizadores gráficos",
            "Flexibilización curricular",
        ],
        tiposDiscapacidad: [
            "Física",
            "Visual",
            "Auditiva",
            "Intelectual",
            "Psicosocial",
            "Múltiple",
            "Trastorno del Espectro Autista",
            "Dificultades Específicas de Aprendizaje",
            "TDAH",
            "Altas Capacidades",
        ],
        estrategiasInclusion: [
            "Aprendizaje cooperativo",
            "Tutoría entre pares",
            "Diseño Universal para el Aprendizaje (DUA)",
            "Enseñanza multinivel",
            "Adaptaciones de acceso",
            "Adaptaciones curriculares",
            "Apoyos especializados",
            "Tecnologías de apoyo",
        ],
    });

    // Local storage key
    const STORAGE_KEY = "piar_form_data_v1";

    // Auto-save timer
    let autoSaveTimer;

    // Load data from localStorage on mount
    onMount(() => {
        if (browser) {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                try {
                    piarState.formData = JSON.parse(saved);
                } catch (e) {
                    console.error("Error loading saved data:", e);
                }
            }
            // Auto-save every 30 seconds
            autoSaveTimer = setInterval(saveToLocal, 30000);
        }
    });

    // Save to localStorage
    function saveToLocal() {
        if (browser) {
            piarState.isSaving = true;
            try {
                localStorage.setItem(
                    STORAGE_KEY,
                    JSON.stringify(piarState.formData),
                );
                showNotification("Datos guardados automáticamente", "success");
            } catch (e) {
                showNotification("Error al guardar datos", "error");
            } finally {
                piarState.isSaving = false;
            }
        }
    }

    // Clear local data
    function clearLocalData() {
        if (
            browser &&
            confirm("¿Está seguro de borrar todos los datos guardados?")
        ) {
            localStorage.removeItem(STORAGE_KEY);
            location.reload();
        }
    }

    // Export to JSON
    function exportToJSON() {
        const dataStr = JSON.stringify(piarState.formData, null, 2);
        const blob = new Blob([dataStr], { type: "application/json" });
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = `PIAR_${piarState.formData.general.nombres || "estudiante"}_${new Date().toISOString().split("T")[0]}.json`;
        a.click();
        URL.revokeObjectURL(url);
        showNotification("Datos exportados exitosamente", "success");
    }

    // Import from JSON
    function importFromJSON(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                try {
                    piarState.formData = JSON.parse(e.target.result);
                    saveToLocal();
                    showNotification(
                        "Datos importados exitosamente",
                        "success",
                    );
                } catch (err) {
                    showNotification("Error al importar datos", "error");
                }
            };
            reader.readAsText(file);
        }
    }

    // Generate PDF (simplified version - in production use jsPDF or similar)
    function generatePDF() {
        piarState.showPreview = true;
        showNotification(
            "Vista previa generada. Use Ctrl+P para imprimir como PDF",
            "info",
        );
        setTimeout(() => {
            window.print();
        }, 500);
    }

    // Navigation
    function nextStep() {
        if (piarState.currentStep < steps.length - 1) {
            piarState.currentStep++;
            saveToLocal();
        }
    }

    function prevStep() {
        if (piarState.currentStep > 0) {
            piarState.currentStep--;
        }
    }

    function goToStep(step) {
        piarState.currentStep = step;
    }

    // Notification system
    let notifications = $state([]);

    function showNotification(message, type = "info") {
        const id = Date.now();
        notifications = [...notifications, { id, message, type }];
        setTimeout(() => {
            notifications = notifications.filter((n) => n.id !== id);
        }, 3000);
    }

    function addActividadCasa() {
        piarState.formData.acta.actividadesCasa.push({
            nombre: "",
            descripcion: "",
            frecuencia: "S",
        });
    }

    function removeActividadCasa(index) {
        piarState.formData.acta.actividadesCasa =
            piarState.formData.acta.actividadesCasa.filter(
                (_, i) => i !== index,
            );
    }

    // Calculate completion percentage
    function getCompletionPercentage() {
        const totalFields = countFields(piarState.formData);
        const filledFields = countFilledFields(piarState.formData);
        return totalFields > 0
            ? Math.round((filledFields / totalFields) * 100)
            : 0;
    }

    function countFields(obj) {
        let count = 0;
        for (const key in obj) {
            if (typeof obj[key] === "object" && obj[key] !== null) {
                if (Array.isArray(obj[key])) {
                    count += obj[key].length * 4; // Approximate fields per adjustment
                } else {
                    count += countFields(obj[key]);
                }
            } else {
                count++;
            }
        }
        return count;
    }

    function countFilledFields(obj) {
        let count = 0;
        for (const key in obj) {
            if (typeof obj[key] === "object" && obj[key] !== null) {
                if (Array.isArray(obj[key])) {
                    count +=
                        obj[key].filter((item) =>
                            Object.values(item).some(
                                (v) => v && v.toString().trim() !== "",
                            ),
                        ).length * 4;
                } else {
                    count += countFilledFields(obj[key]);
                }
            } else if (obj[key] && obj[key].toString().trim() !== "") {
                count++;
            }
        }
        return count;
    }

    // Helper modal state
    let showHelperModal = $state(false);
    let helperModalContent = $state({ title: "", items: [] });
    let helperTarget = $state({ section: "", index: null, field: "" });

    function openHelperModal(
        title,
        items,
        section = "",
        index = null,
        field = "",
    ) {
        helperModalContent = { title, items };
        helperTarget = { section, index, field };
        showHelperModal = true;
    }

    function closeHelperModal() {
        showHelperModal = false;
    }

    function insertHelperText(text) {
        const { section, index, field } = helperTarget;
        if (section && field) {
            let target;
            if (section.includes(".")) {
                const parts = section.split(".");
                target = piarState.formData;
                for (const part of parts) {
                    target = target[part];
                }
            } else {
                target = piarState.formData[section];
            }

            if (index !== null && target[index]) {
                const currentVal = target[index][field];
                target[index][field] = currentVal
                    ? `${currentVal}\n${text}`
                    : text;
            } else if (index === null) {
                const currentVal = target[field];
                target[field] = currentVal ? `${currentVal}\n${text}` : text;
            }
            saveToLocal();
        }
        closeHelperModal();
    }
</script>

<svelte:head>
    <title>PIAR - Plan Individual de Ajustes Razonables</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</svelte:head>

<div
    class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 print:bg-white"
>
    <!-- Notifications -->
    <div class="fixed top-4 right-4 z-50 space-y-2 print:hidden">
        {#each notifications as notification}
            <div
                class="px-4 py-3 rounded-lg shadow-lg transform transition-all duration-300 animate-slide-in
				{notification.type === 'success'
                    ? 'bg-green-500'
                    : notification.type === 'error'
                      ? 'bg-red-500'
                      : 'bg-blue-500'} 
				text-white"
            >
                {notification.message}
            </div>
        {/each}
    </div>

    <!-- Helper Modal -->
    {#if showHelperModal}
        <div
            class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 print:hidden"
        >
            <div
                class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[80vh] overflow-y-auto"
            >
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-gray-800">
                            {helperModalContent.title}
                        </h3>
                        <button
                            onclick={closeHelperModal}
                            class="text-gray-500 hover:text-gray-700 text-2xl"
                            >×</button
                        >
                    </div>
                    <div class="space-y-2">
                        {#each helperModalContent.items as item}
                            <button
                                onclick={() => insertHelperText(item)}
                                class="w-full text-left p-3 rounded-lg hover:bg-indigo-50 transition-colors border border-gray-200"
                            >
                                {item}
                            </button>
                        {/each}
                    </div>
                </div>
            </div>
        </div>
    {/if}

    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 print:hidden">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div
                class="flex flex-col md:flex-row justify-between items-center gap-4"
            >
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">
                        📋 PIAR - Plan Individual de Ajustes Razonables
                    </h1>
                    <p class="text-sm text-gray-600">
                        Decreto 1421 de 2017 - Educación Inclusiva (Paso 1)
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button
                        onclick={onBack}
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors flex items-center gap-2 border border-gray-300 font-medium"
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
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"
                            />
                        </svg>
                        Volver
                    </button>
                    <button
                        onclick={saveToLocal}
                        class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors flex items-center gap-2"
                    >
                        💾 Guardar
                    </button>
                    <button
                        onclick={exportToJSON}
                        class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors flex items-center gap-2"
                    >
                        📤 Exportar
                    </button>
                    <label
                        class="px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-lg transition-colors flex items-center gap-2 cursor-pointer"
                    >
                        📥 Importar
                        <input
                            type="file"
                            accept=".json"
                            onchange={importFromJSON}
                            class="hidden"
                        />
                    </label>
                    <button
                        onclick={generatePDF}
                        class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg transition-colors flex items-center gap-2"
                    >
                        📄 Generar PDF
                    </button>
                    <button
                        onclick={clearLocalData}
                        class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors flex items-center gap-2"
                    >
                        🗑️ Limpiar
                    </button>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="mt-4">
                <div class="flex justify-between text-sm text-gray-600 mb-2">
                    <span>Progreso del formulario</span>
                    <span>{getCompletionPercentage()}% completado</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div
                        class="bg-gradient-to-r from-indigo-500 to-purple-500 h-3 rounded-full transition-all duration-500"
                        style="width: {getCompletionPercentage()}%"
                    ></div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-6">
        <!-- Step Navigation -->
        <nav class="mb-6 print:hidden">
            <div class="flex flex-wrap gap-2 justify-center">
                {#each steps as step}
                    <button
                        onclick={() => goToStep(step.id)}
                        class="px-3 py-2 rounded-lg text-sm font-medium transition-all
							{piarState.currentStep === step.id
                            ? 'bg-indigo-500 text-white shadow-lg'
                            : 'bg-white text-gray-600 hover:bg-indigo-50'}
							border {piarState.currentStep === step.id
                            ? 'border-indigo-500'
                            : 'border-gray-200'}"
                    >
                        {step.icon}
                        {step.name}
                    </button>
                {/each}
            </div>
        </nav>

        <!-- Form Steps -->
        <div
            class="bg-white rounded-xl shadow-lg p-6 print:shadow-none print:p-0"
        >
            <!-- Step 0: Información General -->
            {#if piarState.currentStep === 0}
                <section class="space-y-6">
                    <h2 class="text-2xl font-bold text-gray-800 border-b pb-2">
                        📋 Información General del Estudiante
                    </h2>

                    <div
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
                    >
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Fecha de Diligenciamiento</label
                            >
                            <input
                                type="date"
                                bind:value={
                                    piarState.formData.general
                                        .fechaDiligenciamiento
                                }
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Lugar de Diligenciamiento</label
                            >
                            <input
                                type="text"
                                bind:value={
                                    piarState.formData.general
                                        .lugarDiligenciamiento
                                }
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Persona que Diligencia</label
                            >
                            <input
                                type="text"
                                bind:value={
                                    piarState.formData.general.personaDiligencia
                                }
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Rol</label
                            >
                            <select
                                bind:value={
                                    piarState.formData.general.rolPersona
                                }
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            >
                                <option value="">Seleccione...</option>
                                <option value="Docente">Docente</option>
                                <option value="Coordinador">Coordinador</option>
                                <option value="Psicorientador"
                                    >Psicorientador</option
                                >
                                <option value="Docente de Apoyo"
                                    >Docente de Apoyo</option
                                >
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">
                            Datos del Estudiante
                        </h3>
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
                        >
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Nombres</label
                                >
                                <input
                                    type="text"
                                    bind:value={
                                        piarState.formData.general.nombres
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Apellidos</label
                                >
                                <input
                                    type="text"
                                    bind:value={
                                        piarState.formData.general.apellidos
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Fecha de Nacimiento</label
                                >
                                <input
                                    type="date"
                                    bind:value={
                                        piarState.formData.general
                                            .fechaNacimiento
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Edad</label
                                >
                                <input
                                    type="number"
                                    bind:value={piarState.formData.general.edad}
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Lugar de Nacimiento</label
                                >
                                <input
                                    type="text"
                                    bind:value={
                                        piarState.formData.general
                                            .lugarNacimiento
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Tipo de Documento</label
                                >
                                <select
                                    bind:value={
                                        piarState.formData.general.tipoDocumento
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                >
                                    <option value="TI">TI</option>
                                    <option value="CC">CC</option>
                                    <option value="RC">RC</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Número de Documento</label
                                >
                                <input
                                    type="text"
                                    bind:value={
                                        piarState.formData.general
                                            .numeroDocumento
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Departamento</label
                                >
                                <input
                                    type="text"
                                    bind:value={
                                        piarState.formData.general.departamento
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Municipio</label
                                >
                                <input
                                    type="text"
                                    bind:value={
                                        piarState.formData.general.municipio
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">
                            Información de Contacto y Ubicación
                        </h3>
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
                        >
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Dirección</label
                                >
                                <input
                                    type="text"
                                    bind:value={
                                        piarState.formData.general.direccion
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Barrio/Vereda</label
                                >
                                <input
                                    type="text"
                                    bind:value={
                                        piarState.formData.general.barrio
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Teléfono</label
                                >
                                <input
                                    type="tel"
                                    bind:value={
                                        piarState.formData.general.telefono
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Correo Electrónico</label
                                >
                                <input
                                    type="email"
                                    bind:value={
                                        piarState.formData.general.correo
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Grado al que Aspira</label
                                >
                                <input
                                    type="text"
                                    bind:value={
                                        piarState.formData.general.gradoAspira
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">
                            Información Especial
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >¿Está en Centro de Protección?</label
                                >
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="centroProteccion"
                                            value="NO"
                                            bind:group={
                                                piarState.formData.general
                                                    .centroProteccion
                                            }
                                        />
                                        <span>No</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="centroProteccion"
                                            value="SI"
                                            bind:group={
                                                piarState.formData.general
                                                    .centroProteccion
                                            }
                                        />
                                        <span>Sí</span>
                                    </label>
                                </div>
                                {#if piarState.formData.general.centroProteccion === "SI"}
                                    <input
                                        type="text"
                                        placeholder="¿Dónde?"
                                        bind:value={
                                            piarState.formData.general
                                                .lugarCentroProteccion
                                        }
                                        class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    />
                                {/if}
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >¿Pertenece a Grupo Étnico?</label
                                >
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="grupoEtnico"
                                            value="NO"
                                            bind:group={
                                                piarState.formData.general
                                                    .grupoEtnico
                                            }
                                        />
                                        <span>No</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="grupoEtnico"
                                            value="SI"
                                            bind:group={
                                                piarState.formData.general
                                                    .grupoEtnico
                                            }
                                        />
                                        <span>Sí</span>
                                    </label>
                                </div>
                                {#if piarState.formData.general.grupoEtnico === "SI"}
                                    <input
                                        type="text"
                                        placeholder="¿Cuál?"
                                        bind:value={
                                            piarState.formData.general
                                                .cualGrupoEtnico
                                        }
                                        class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    />
                                {/if}
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >¿Víctima del Conflicto Armado?</label
                                >
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="victimaConflicto"
                                            value="NO"
                                            bind:group={
                                                piarState.formData.general
                                                    .victimaConflicto
                                            }
                                        />
                                        <span>No</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="victimaConflicto"
                                            value="SI"
                                            bind:group={
                                                piarState.formData.general
                                                    .victimaConflicto
                                            }
                                        />
                                        <span>Sí</span>
                                    </label>
                                </div>
                                {#if piarState.formData.general.victimaConflicto === "SI"}
                                    <div class="mt-2">
                                        <label class="flex items-center gap-2">
                                            <input
                                                type="checkbox"
                                                checked={piarState.formData
                                                    .general.registroVictima ===
                                                    "SI"}
                                                onchange={(e) =>
                                                    (piarState.formData.general.registroVictima =
                                                        e.target.checked
                                                            ? "SI"
                                                            : "NO")}
                                            />
                                            <span>¿Cuenta con registro?</span>
                                        </label>
                                    </div>
                                {/if}
                            </div>
                        </div>
                    </div>

                    <!-- Pedagogical Helper -->
                    <div
                        class="bg-indigo-50 border border-indigo-200 rounded-lg p-4"
                    >
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="font-semibold text-indigo-800">
                                    💡 Ayuda Pedagógica
                                </h4>
                                <p class="text-sm text-indigo-600">
                                    Acceda a sugerencias para educación
                                    inclusiva
                                </p>
                            </div>
                            <button
                                onclick={() =>
                                    openHelperModal(
                                        "Tipos de Discapacidad",
                                        inclusiveEducationHelpers.tiposDiscapacidad,
                                        "piar",
                                        null,
                                        "caracteristicasEstudiante",
                                    )}
                                class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg text-sm"
                            >
                                Ver Ayudas
                            </button>
                        </div>
                    </div>
                </section>
            {/if}

            <!-- Step 1: Entorno Salud -->
            {#if piarState.currentStep === 1}
                <section class="space-y-6">
                    <h2 class="text-2xl font-bold text-gray-800 border-b pb-2">
                        🏥 Entorno Salud
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >¿Afiliación al Sistema de Salud?</label
                            >
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2">
                                    <input
                                        type="radio"
                                        name="afiliacionSalud"
                                        value="SI"
                                        bind:group={
                                            piarState.formData.salud
                                                .afiliacionSalud
                                        }
                                    />
                                    <span>Sí</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input
                                        type="radio"
                                        name="afiliacionSalud"
                                        value="NO"
                                        bind:group={
                                            piarState.formData.salud
                                                .afiliacionSalud
                                        }
                                    />
                                    <span>No</span>
                                </label>
                            </div>
                        </div>
                        {#if piarState.formData.salud.afiliacionSalud === "SI"}
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >EPS</label
                                >
                                <input
                                    type="text"
                                    bind:value={piarState.formData.salud.eps}
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Tipo de Afiliación</label
                                >
                                <select
                                    bind:value={
                                        piarState.formData.salud.tipoAfiliacion
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                >
                                    <option value="Contributivo"
                                        >Contributivo</option
                                    >
                                    <option value="Subsidiado"
                                        >Subsidiado</option
                                    >
                                </select>
                            </div>
                        {/if}
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Lugar de Atención en Emergencia</label
                            >
                            <input
                                type="text"
                                bind:value={
                                    piarState.formData.salud.lugarEmergencia
                                }
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            />
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">
                            Atención Médica
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >¿Está siendo atendido por el sector salud?</label
                                >
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="atencionSalud"
                                            value="SI"
                                            bind:group={
                                                piarState.formData.salud
                                                    .atencionSalud
                                            }
                                        />
                                        <span>Sí</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="atencionSalud"
                                            value="NO"
                                            bind:group={
                                                piarState.formData.salud
                                                    .atencionSalud
                                            }
                                        />
                                        <span>No</span>
                                    </label>
                                </div>
                                {#if piarState.formData.salud.atencionSalud === "SI"}
                                    <input
                                        type="text"
                                        placeholder="Frecuencia"
                                        bind:value={
                                            piarState.formData.salud
                                                .frecuenciaAtencion
                                        }
                                        class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    />
                                {/if}
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >¿Tiene diagnóstico médico?</label
                                >
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="diagnosticoMedico"
                                            value="SI"
                                            bind:group={
                                                piarState.formData.salud
                                                    .diagnosticoMedico
                                            }
                                        />
                                        <span>Sí</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="diagnosticoMedico"
                                            value="NO"
                                            bind:group={
                                                piarState.formData.salud
                                                    .diagnosticoMedico
                                            }
                                        />
                                        <span>No</span>
                                    </label>
                                </div>
                                {#if piarState.formData.salud.diagnosticoMedico === "SI"}
                                    <input
                                        type="text"
                                        placeholder="¿Cuál?"
                                        bind:value={
                                            piarState.formData.salud
                                                .cualDiagnostico
                                        }
                                        class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    />
                                {/if}
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">
                            Terapias y Tratamientos
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >¿Asiste a terapias?</label
                                >
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="asisteTerapias"
                                            value="SI"
                                            bind:group={
                                                piarState.formData.salud
                                                    .asisteTerapias
                                            }
                                        />
                                        <span>Sí</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="asisteTerapias"
                                            value="NO"
                                            bind:group={
                                                piarState.formData.salud
                                                    .asisteTerapias
                                            }
                                        />
                                        <span>No</span>
                                    </label>
                                </div>
                                {#if piarState.formData.salud.asisteTerapias === "SI"}
                                    <div class="mt-2 space-y-2">
                                        <input
                                            type="text"
                                            placeholder="¿Cuál terapia?"
                                            bind:value={
                                                piarState.formData.salud
                                                    .tipoTerapia
                                            }
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        />
                                        <input
                                            type="text"
                                            placeholder="Frecuencia"
                                            bind:value={
                                                piarState.formData.salud
                                                    .frecuenciaTerapia
                                            }
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        />
                                    </div>
                                {/if}
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >¿Recibe tratamiento por enfermedad
                                    particular?</label
                                >
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="tratamientoEnfermedad"
                                            value="SI"
                                            bind:group={
                                                piarState.formData.salud
                                                    .tratamientoEnfermedad
                                            }
                                        />
                                        <span>Sí</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="tratamientoEnfermedad"
                                            value="NO"
                                            bind:group={
                                                piarState.formData.salud
                                                    .tratamientoEnfermedad
                                            }
                                        />
                                        <span>No</span>
                                    </label>
                                </div>
                                {#if piarState.formData.salud.tratamientoEnfermedad === "SI"}
                                    <input
                                        type="text"
                                        placeholder="¿Cuál?"
                                        bind:value={
                                            piarState.formData.salud
                                                .cualTratamiento
                                        }
                                        class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    />
                                {/if}
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">
                            Medicamentos y Productos de Apoyo
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >¿Consume medicamentos?</label
                                >
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="consumeMedicamentos"
                                            value="SI"
                                            bind:group={
                                                piarState.formData.salud
                                                    .consumeMedicamentos
                                            }
                                        />
                                        <span>Sí</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="consumeMedicamentos"
                                            value="NO"
                                            bind:group={
                                                piarState.formData.salud
                                                    .consumeMedicamentos
                                            }
                                        />
                                        <span>No</span>
                                    </label>
                                </div>
                                {#if piarState.formData.salud.consumeMedicamentos === "SI"}
                                    <textarea
                                        placeholder="Frecuencia, horario, nombre del medicamento"
                                        bind:value={
                                            piarState.formData.salud
                                                .medicamentosDetalle
                                        }
                                        class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        rows="3"
                                    ></textarea>
                                {/if}
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >¿Cuenta con productos de apoyo?</label
                                >
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="productosApoyo"
                                            value="SI"
                                            bind:group={
                                                piarState.formData.salud
                                                    .productosApoyo
                                            }
                                        />
                                        <span>Sí</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="productosApoyo"
                                            value="NO"
                                            bind:group={
                                                piarState.formData.salud
                                                    .productosApoyo
                                            }
                                        />
                                        <span>No</span>
                                    </label>
                                </div>
                                {#if piarState.formData.salud.productosApoyo === "SI"}
                                    <input
                                        type="text"
                                        placeholder="¿Cuáles? (movilidad, comunicación, independencia)"
                                        bind:value={
                                            piarState.formData.salud
                                                .cualesProductos
                                        }
                                        class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    />
                                {/if}
                            </div>
                        </div>
                    </div>

                    <!-- Pedagogical Helper -->
                    <div
                        class="bg-indigo-50 border border-indigo-200 rounded-lg p-4"
                    >
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="font-semibold text-indigo-800">
                                    💡 Consideraciones de Salud para el Aula
                                </h4>
                                <p class="text-sm text-indigo-600">
                                    Aspectos importantes para la inclusión
                                    educativa
                                </p>
                            </div>
                            <button
                                onclick={() =>
                                    openHelperModal(
                                        "Ajustes por Condiciones de Salud",
                                        [
                                            "Permitir pausas activas según necesidad",
                                            "Flexibilizar tiempos de entrega",
                                            "Coordinar con familia administración de medicamentos",
                                            "Adaptar actividades físicas",
                                            "Garantizar accesibilidad a baños",
                                            "Tener plan de emergencia conocido por todo el personal",
                                        ],
                                        "salud",
                                        null,
                                        "cualDiagnostico",
                                    )}
                                class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg text-sm"
                            >
                                Ver Sugerencias
                            </button>
                        </div>
                    </div>
                </section>
            {/if}

            <!-- Step 2: Entorno Hogar -->
            {#if piarState.currentStep === 2}
                <section class="space-y-6">
                    <h2 class="text-2xl font-bold text-gray-800 border-b pb-2">
                        🏠 Entorno Hogar
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <h3
                                class="text-lg font-semibold text-gray-700 mb-4"
                            >
                                Información de la Madre
                            </h3>
                            <div class="space-y-3">
                                <input
                                    type="text"
                                    placeholder="Nombre"
                                    bind:value={
                                        piarState.formData.hogar.nombreMadre
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                                <input
                                    type="text"
                                    placeholder="Ocupación"
                                    bind:value={
                                        piarState.formData.hogar.ocupacionMadre
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                                <select
                                    bind:value={
                                        piarState.formData.hogar
                                            .nivelEducativoMadre
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                >
                                    <option value="">Nivel Educativo</option>
                                    <option value="Primaria">Primaria</option>
                                    <option value="Bachillerato"
                                        >Bachillerato</option
                                    >
                                    <option value="Técnico">Técnico</option>
                                    <option value="Tecnólogo">Tecnólogo</option>
                                    <option value="Universitario"
                                        >Universitario</option
                                    >
                                </select>
                            </div>
                        </div>

                        <div class="border rounded-lg p-4 bg-gray-50">
                            <h3
                                class="text-lg font-semibold text-gray-700 mb-4"
                            >
                                Información del Padre
                            </h3>
                            <div class="space-y-3">
                                <input
                                    type="text"
                                    placeholder="Nombre"
                                    bind:value={
                                        piarState.formData.hogar.nombrePadre
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                                <input
                                    type="text"
                                    placeholder="Ocupación"
                                    bind:value={
                                        piarState.formData.hogar.ocupacionPadre
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                                <select
                                    bind:value={
                                        piarState.formData.hogar
                                            .nivelEducativoPadre
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                >
                                    <option value="">Nivel Educativo</option>
                                    <option value="Primaria">Primaria</option>
                                    <option value="Bachillerato"
                                        >Bachillerato</option
                                    >
                                    <option value="Técnico">Técnico</option>
                                    <option value="Tecnólogo">Tecnólogo</option>
                                    <option value="Universitario"
                                        >Universitario</option
                                    >
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">
                            Cuidador Principal
                        </h3>
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
                        >
                            <input
                                type="text"
                                placeholder="Nombre del Cuidador"
                                bind:value={
                                    piarState.formData.hogar.nombreCuidador
                                }
                                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            />
                            <input
                                type="text"
                                placeholder="Parentesco"
                                bind:value={
                                    piarState.formData.hogar.parentescoCuidador
                                }
                                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            />
                            <select
                                bind:value={
                                    piarState.formData.hogar
                                        .nivelEducativoCuidador
                                }
                                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            >
                                <option value="">Nivel Educativo</option>
                                <option value="Primaria">Primaria</option>
                                <option value="Bachillerato"
                                    >Bachillerato</option
                                >
                                <option value="Técnico">Técnico</option>
                                <option value="Tecnólogo">Tecnólogo</option>
                                <option value="Universitario"
                                    >Universitario</option
                                >
                            </select>
                            <input
                                type="tel"
                                placeholder="Teléfono"
                                bind:value={
                                    piarState.formData.hogar.telefonoCuidador
                                }
                                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            />
                            <input
                                type="email"
                                placeholder="Correo Electrónico"
                                bind:value={
                                    piarState.formData.hogar.correoCuidador
                                }
                                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            />
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">
                            Composición Familiar
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Número de Hermanos</label
                                >
                                <input
                                    type="number"
                                    bind:value={
                                        piarState.formData.hogar.numeroHermanos
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Lugar que Ocupa</label
                                >
                                <input
                                    type="text"
                                    placeholder="Ej: Mayor, del medio, menor"
                                    bind:value={
                                        piarState.formData.hogar.lugarOcupa
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                            </div>
                            <div class="md:col-span-2">
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Personas con quien Vive</label
                                >
                                <textarea
                                    bind:value={
                                        piarState.formData.hogar.personasVive
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    rows="2"
                                ></textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >¿Quiénes Apoyan la Crianza?</label
                                >
                                <textarea
                                    bind:value={
                                        piarState.formData.hogar.quienesApoyan
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    rows="2"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">
                            Protección y Subsidios
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >¿Está bajo Protección?</label
                                >
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="bajoProteccion"
                                            value="NO"
                                            bind:group={
                                                piarState.formData.hogar
                                                    .bajoProteccion
                                            }
                                        />
                                        <span>No</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="bajoProteccion"
                                            value="SI"
                                            bind:group={
                                                piarState.formData.hogar
                                                    .bajoProteccion
                                            }
                                        />
                                        <span>Sí</span>
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >¿Recibe Subsidio?</label
                                >
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="recibeSubsidio"
                                            value="NO"
                                            bind:group={
                                                piarState.formData.hogar
                                                    .recibeSubsidio
                                            }
                                        />
                                        <span>No</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="recibeSubsidio"
                                            value="SI"
                                            bind:group={
                                                piarState.formData.hogar
                                                    .recibeSubsidio
                                            }
                                        />
                                        <span>Sí</span>
                                    </label>
                                </div>
                                {#if piarState.formData.hogar.recibeSubsidio === "SI"}
                                    <input
                                        type="text"
                                        placeholder="¿Cuál? (Prosperidad Social, ICBF, Familias en Acción, etc.)"
                                        bind:value={
                                            piarState.formData.hogar
                                                .cualSubsidio
                                        }
                                        class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    />
                                {/if}
                            </div>
                        </div>
                    </div>

                    <!-- Pedagogical Helper -->
                    <div
                        class="bg-indigo-50 border border-indigo-200 rounded-lg p-4"
                    >
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="font-semibold text-indigo-800">
                                    💡 Fortalecimiento del Entorno Familiar
                                </h4>
                                <p class="text-sm text-indigo-600">
                                    Estrategias para involucrar a la familia
                                </p>
                            </div>
                            <button
                                onclick={() =>
                                    openHelperModal(
                                        "Estrategias de Participación Familiar",
                                        [
                                            "Escuela de padres especializada",
                                            "Comunicación constante casa-colejo",
                                            "Involucrar familia en diseño de ajustes",
                                            "Capacitación en manejo de apoyos",
                                            "Reuniones periódicas de seguimiento",
                                            "Guías de apoyo para el hogar",
                                        ],
                                        "hogar",
                                        null,
                                        "quienesApoyan",
                                    )}
                                class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg text-sm"
                            >
                                Ver Estrategias
                            </button>
                        </div>
                    </div>
                </section>
            {/if}

            <!-- Step 3: Entorno Educativo -->
            {#if piarState.currentStep === 3}
                <section class="space-y-6">
                    <h2 class="text-2xl font-bold text-gray-800 border-b pb-2">
                        📚 Entorno Educativo
                    </h2>

                    <div class="border-t pt-4">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">
                            Trayectoria Educativa
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >¿Ha estado en otra institución?</label
                                >
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="vinculadoOtraInstitucion"
                                            value="NO"
                                            bind:group={
                                                piarState.formData.educativo
                                                    .vinculadoOtraInstitucion
                                            }
                                        />
                                        <span>No</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="vinculadoOtraInstitucion"
                                            value="SI"
                                            bind:group={
                                                piarState.formData.educativo
                                                    .vinculadoOtraInstitucion
                                            }
                                        />
                                        <span>Sí</span>
                                    </label>
                                </div>
                                {#if piarState.formData.educativo.vinculadoOtraInstitucion === "SI"}
                                    <input
                                        type="text"
                                        placeholder="¿Cuáles instituciones?"
                                        bind:value={
                                            piarState.formData.educativo
                                                .cualInstitucion
                                        }
                                        class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    />
                                {/if}
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Último Grado Cursado</label
                                >
                                <input
                                    type="text"
                                    bind:value={
                                        piarState.formData.educativo.ultimoGrado
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >¿Aprobó?</label
                                >
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="aproboUltimoGrado"
                                            value="SI"
                                            bind:group={
                                                piarState.formData.educativo
                                                    .aproboUltimoGrado
                                            }
                                        />
                                        <span>Sí</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="aproboUltimoGrado"
                                            value="NO"
                                            bind:group={
                                                piarState.formData.educativo
                                                    .aproboUltimoGrado
                                            }
                                        />
                                        <span>No</span>
                                    </label>
                                </div>
                            </div>
                            <div class="md:col-span-2">
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Observaciones de Trayectoria</label
                                >
                                <textarea
                                    bind:value={
                                        piarState.formData.educativo
                                            .observacionesTrayectoria
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    rows="3"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">
                            Informes y Programas
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >¿Recibe Informe Pedagógico/PIAR?</label
                                >
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="informePedagogico"
                                            value="NO"
                                            bind:group={
                                                piarState.formData.educativo
                                                    .informePedagogico
                                            }
                                        />
                                        <span>No</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="informePedagogico"
                                            value="SI"
                                            bind:group={
                                                piarState.formData.educativo
                                                    .informePedagogico
                                            }
                                        />
                                        <span>Sí</span>
                                    </label>
                                </div>
                                {#if piarState.formData.educativo.informePedagogico === "SI"}
                                    <input
                                        type="text"
                                        placeholder="¿De qué institución?"
                                        bind:value={
                                            piarState.formData.educativo
                                                .institucionInforme
                                        }
                                        class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    />
                                {/if}
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >¿Programas Complementarios?</label
                                >
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="programasComplementarios"
                                            value="NO"
                                            bind:group={
                                                piarState.formData.educativo
                                                    .programasComplementarios
                                            }
                                        />
                                        <span>No</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="programasComplementarios"
                                            value="SI"
                                            bind:group={
                                                piarState.formData.educativo
                                                    .programasComplementarios
                                            }
                                        />
                                        <span>Sí</span>
                                    </label>
                                </div>
                                {#if piarState.formData.educativo.programasComplementarios === "SI"}
                                    <input
                                        type="text"
                                        placeholder="¿Cuáles? (Deportes, danzas, música, etc.)"
                                        bind:value={
                                            piarState.formData.educativo
                                                .cualesProgramas
                                        }
                                        class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    />
                                {/if}
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">
                            Institución de Matricula
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <input
                                type="text"
                                placeholder="Nombre de la Institución"
                                bind:value={
                                    piarState.formData.educativo
                                        .nombreInstitucionMatricula
                                }
                                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            />
                            <input
                                type="text"
                                placeholder="Sede"
                                bind:value={piarState.formData.educativo.sede}
                                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            />
                            <input
                                type="text"
                                placeholder="Medio de Transporte"
                                bind:value={
                                    piarState.formData.educativo.medioTransporte
                                }
                                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            />
                            <input
                                type="text"
                                placeholder="Distancia/Tiempo"
                                bind:value={
                                    piarState.formData.educativo.distanciaTiempo
                                }
                                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            />
                        </div>
                    </div>

                    <!-- Pedagogical Helper -->
                    <div
                        class="bg-indigo-50 border border-indigo-200 rounded-lg p-4"
                    >
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="font-semibold text-indigo-800">
                                    💡 Transición Educativa
                                </h4>
                                <p class="text-sm text-indigo-600">
                                    Aspectos clave para el éxito en la
                                    transición
                                </p>
                            </div>
                            <button
                                onclick={() =>
                                    openHelperModal(
                                        "Estrategias de Transición",
                                        inclusiveEducationHelpers.estrategiasInclusion,
                                        "educativo",
                                        null,
                                        "observacionesTrayectoria",
                                    )}
                                class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg text-sm"
                            >
                                Ver Estrategias
                            </button>
                        </div>
                    </div>
                </section>
            {/if}

            <!-- Step 4: PIAR Características -->
            {#if piarState.currentStep === 4}
                <section class="space-y-6">
                    <h2 class="text-2xl font-bold text-gray-800 border-b pb-2">
                        👤 PIAR - Características del Estudiante
                    </h2>

                    <div
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
                    >
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Fecha de Elaboración</label
                            >
                            <input
                                type="date"
                                bind:value={
                                    piarState.formData.piar.fechaElaboracion
                                }
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Institución Educativa</label
                            >
                            <input
                                type="text"
                                bind:value={
                                    piarState.formData.piar.institucionEducativa
                                }
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Sede</label
                            >
                            <input
                                type="text"
                                bind:value={piarState.formData.piar.sede}
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Jornada</label
                            >
                            <select
                                bind:value={piarState.formData.piar.jornada}
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            >
                                <option value="">Seleccione...</option>
                                <option value="Mañana">Mañana</option>
                                <option value="Tarde">Tarde</option>
                                <option value="Noche">Noche</option>
                                <option value="Completa">Completa</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Docentes que Elaboran y Cargo</label
                            >
                            <input
                                type="text"
                                bind:value={
                                    piarState.formData.piar.docentesElaboran
                                }
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            />
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">
                            1.a Características del Estudiante
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                >
                                    Descripción general (gustos, intereses,
                                    aspectos que le desagradan, expectativas)
                                </label>
                                <textarea
                                    bind:value={
                                        piarState.formData.piar
                                            .caracteristicasEstudiante
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    rows="5"
                                    placeholder="Describa las características del estudiante con énfasis en sus gustos, intereses, expectativas del estudiante y la familia..."
                                ></textarea>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                >
                                    Habilidades, competencias y aprendizajes (lo
                                    que hace, puede hacer o requiere apoyo)
                                </label>
                                <textarea
                                    bind:value={
                                        piarState.formData.piar
                                            .habilidadesCompetencias
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    rows="5"
                                    placeholder="Indique las habilidades, competencias, cualidades y aprendizajes con los que cuenta el estudiante..."
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Pedagogical Helper -->
                    <div
                        class="bg-indigo-50 border border-indigo-200 rounded-lg p-4"
                    >
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="font-semibold text-indigo-800">
                                    💡 Descripción desde la Diversidad
                                </h4>
                                <p class="text-sm text-indigo-600">
                                    Enfoque de capacidades, no limitaciones
                                </p>
                            </div>
                            <button
                                onclick={() =>
                                    openHelperModal(
                                        "Aspectos a Considerar",
                                        [
                                            "Describir fortalezas antes que dificultades",
                                            "Mencionar intereses y motivaciones",
                                            "Incluir preferencias de aprendizaje",
                                            "Documentar apoyos que funcionan",
                                            "Considerar contexto cultural y familiar",
                                            "Involucrar al estudiante en la descripción",
                                        ],
                                        "piar",
                                        null,
                                        "caracteristicasEstudiante",
                                    )}
                                class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg text-sm"
                            >
                                Ver Guía
                            </button>
                        </div>
                    </div>
                </section>
            {/if}

            <!-- Steps 5-7: Ajustes por Trimestre -->
            {#if piarState.currentStep >= 5 && piarState.currentStep <= 7}
                {@const trimestreIndex = piarState.currentStep - 5}
                {@const trimestreNombre = ["Primer", "Segundo", "Tercer"][
                    trimestreIndex
                ]}
                {@const trimestreKey = [
                    "primerTrimestre",
                    "segundoTrimestre",
                    "tercerTrimestre",
                ][trimestreIndex]}
                {@const ajustesData = piarState.formData.ajustes[trimestreKey]}

                <section class="space-y-6">
                    <h2 class="text-2xl font-bold text-gray-800 border-b pb-2">
                        📊 Ajustes Razonables - {trimestreNombre} Trimestre
                    </h2>

                    <!-- Pedagogical Helper -->
                    <div
                        class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4"
                    >
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="font-semibold text-amber-800">
                                    💡 Sugerencias de Ajustes
                                </h4>
                                <p class="text-sm text-amber-600">
                                    Herramientas para diseñar ajustes razonables
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <button
                                    onclick={() =>
                                        openHelperModal(
                                            "Barreras Comunes",
                                            inclusiveEducationHelpers.barrerasComunes,
                                        )}
                                    class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm"
                                >
                                    Barreras
                                </button>
                                <button
                                    onclick={() =>
                                        openHelperModal(
                                            "Ajustes Sugeridos",
                                            inclusiveEducationHelpers.ajustesRazonablesSugeridos,
                                        )}
                                    class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm"
                                >
                                    Ajustes
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-indigo-100">
                                    <th
                                        class="border p-2 text-left text-sm font-semibold"
                                        >Área</th
                                    >
                                    <th
                                        class="border p-2 text-left text-sm font-semibold"
                                        >Objetivos/Propósitos</th
                                    >
                                    <th
                                        class="border p-2 text-left text-sm font-semibold"
                                        >Barreras</th
                                    >
                                    <th
                                        class="border p-2 text-left text-sm font-semibold"
                                        >Ajustes Razonables</th
                                    >
                                    <th
                                        class="border p-2 text-left text-sm font-semibold"
                                        >Evaluación</th
                                    >
                                </tr>
                            </thead>
                            <tbody>
                                {#each ajustesData as ajuste, index}
                                    <tr class="hover:bg-gray-50">
                                        <td
                                            class="border p-2 font-medium text-sm"
                                            >{ajuste.area}</td
                                        >
                                        <td class="border p-2">
                                            <textarea
                                                bind:value={ajuste.objetivos}
                                                class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-indigo-500"
                                                rows="2"
                                            ></textarea>
                                        </td>
                                        <td class="border p-2 relative group">
                                            <textarea
                                                bind:value={ajuste.barreras}
                                                class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-indigo-500"
                                                rows="2"
                                            ></textarea>
                                            <button
                                                type="button"
                                                class="absolute right-3 bottom-3 opacity-0 group-hover:opacity-100 transition-opacity bg-amber-100 hover:bg-amber-200 text-amber-700 rounded p-1 text-xs"
                                                onclick={() =>
                                                    openHelperModal(
                                                        "Barreras Comunes",
                                                        inclusiveEducationHelpers.barrerasComunes,
                                                        `ajustes.${trimestreKey}`,
                                                        index,
                                                        "barreras",
                                                    )}
                                                title="Sugerencias">💡</button
                                            >
                                        </td>
                                        <td class="border p-2 relative group">
                                            <textarea
                                                bind:value={ajuste.ajustes}
                                                class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-indigo-500"
                                                rows="2"
                                            ></textarea>
                                            <button
                                                type="button"
                                                class="absolute right-3 bottom-3 opacity-0 group-hover:opacity-100 transition-opacity bg-amber-100 hover:bg-amber-200 text-amber-700 rounded p-1 text-xs"
                                                onclick={() =>
                                                    openHelperModal(
                                                        "Ajustes Sugeridos",
                                                        inclusiveEducationHelpers.ajustesRazonablesSugeridos,
                                                        `ajustes.${trimestreKey}`,
                                                        index,
                                                        "ajustes",
                                                    )}
                                                title="Sugerencias">💡</button
                                            >
                                        </td>
                                        <td class="border p-2">
                                            <textarea
                                                bind:value={ajuste.evaluacion}
                                                class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-indigo-500"
                                                rows="2"
                                            ></textarea>
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>

                    <div
                        class="bg-blue-50 border border-blue-200 rounded-lg p-4"
                    >
                        <p class="text-sm text-blue-700">
                            <strong>Nota:</strong> Para educación inicial y Preescolar,
                            los propósitos se orientarán de acuerdo con las bases
                            curriculares para la educación inicial y los DBA de transición.
                        </p>
                    </div>
                </section>
            {/if}

            <!-- Step 8: Recomendaciones -->
            {#if piarState.currentStep === 8}
                <section class="space-y-6">
                    <h2 class="text-2xl font-bold text-gray-800 border-b pb-2">
                        💡 Recomendaciones para el Plan de Mejoramiento
                    </h2>

                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-indigo-100">
                                    <th
                                        class="border p-3 text-left text-sm font-semibold"
                                        >Actores</th
                                    >
                                    <th
                                        class="border p-3 text-left text-sm font-semibold"
                                        >Acciones</th
                                    >
                                    <th
                                        class="border p-3 text-left text-sm font-semibold"
                                        >Estrategias a Implementar</th
                                    >
                                </tr>
                            </thead>
                            <tbody>
                                {#each [{ key: "familia", label: "Familia, Cuidadores" }, { key: "docentes", label: "Docentes" }, { key: "directivos", label: "Directivos" }, { key: "administrativos", label: "Administrativos" }, { key: "pares", label: "Pares (Compañeros)" }] as actor}
                                    <tr class="hover:bg-gray-50">
                                        <td class="border p-3 font-medium"
                                            >{actor.label}</td
                                        >
                                        <td class="border p-3">
                                            <textarea
                                                bind:value={
                                                    piarState.formData
                                                        .recomendaciones[
                                                        actor.key
                                                    ].acciones
                                                }
                                                class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-indigo-500"
                                                rows="2"
                                                placeholder="Describa las acciones..."
                                            ></textarea>
                                        </td>
                                        <td class="border p-3">
                                            <textarea
                                                bind:value={
                                                    piarState.formData
                                                        .recomendaciones[
                                                        actor.key
                                                    ].estrategias
                                                }
                                                class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-indigo-500"
                                                rows="2"
                                                placeholder="Describa las estrategias..."
                                            ></textarea>
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>

                    <!-- Pedagogical Helper -->
                    <div
                        class="bg-indigo-50 border border-indigo-200 rounded-lg p-4"
                    >
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="font-semibold text-indigo-800">
                                    💡 Eliminación de Barreras
                                </h4>
                                <p class="text-sm text-indigo-600">
                                    Enfoque sistémico para la inclusión
                                </p>
                            </div>
                            <button
                                onclick={() =>
                                    openHelperModal(
                                        "Estrategias Institucionales",
                                        [
                                            "Capacitación docente en educación inclusiva",
                                            "Adaptación de infraestructura",
                                            "Políticas institucionales claras",
                                            "Presupuesto para apoyos",
                                            "Trabajo colaborativo entre actores",
                                            "Seguimiento y evaluación continua",
                                        ],
                                        "acta",
                                        null,
                                        "compromisosAula",
                                    )}
                                class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg text-sm"
                            >
                                Ver Estrategias
                            </button>
                        </div>
                    </div>
                </section>
            {/if}

            <!-- Step 9: Acta de Acuerdo -->
            {#if piarState.currentStep === 9}
                <section class="space-y-6">
                    <h2 class="text-2xl font-bold text-gray-800 border-b pb-2">
                        ✍️ Acta de Acuerdo - PIAR
                    </h2>

                    <div
                        class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4"
                    >
                        <p class="text-sm text-blue-700">
                            <strong>Decreto 1421 de 2017:</strong> La educación inclusiva
                            es un proceso permanente que reconoce, valora y responde
                            a la diversidad de características, intereses, posibilidades
                            y expectativas de los estudiantes.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Fecha</label
                            >
                            <input
                                type="date"
                                bind:value={piarState.formData.acta.fecha}
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Institución y Sede</label
                            >
                            <input
                                type="text"
                                bind:value={
                                    piarState.formData.acta.institucionSede
                                }
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Nombre del Estudiante</label
                            >
                            <input
                                type="text"
                                bind:value={
                                    piarState.formData.acta.nombreEstudiante
                                }
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Documento de Identificación</label
                            >
                            <input
                                type="text"
                                bind:value={
                                    piarState.formData.acta
                                        .documentoIdentificacion
                                }
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Edad</label
                            >
                            <input
                                type="number"
                                bind:value={piarState.formData.acta.edad}
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Grado</label
                            >
                            <input
                                type="text"
                                bind:value={piarState.formData.acta.grado}
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            />
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">
                            Equipo Institucional
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Equipo Directivos</label
                                >
                                <textarea
                                    bind:value={
                                        piarState.formData.acta.equipoDirectivos
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    rows="2"
                                ></textarea>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Equipo Docentes</label
                                >
                                <textarea
                                    bind:value={
                                        piarState.formData.acta.equipoDocentes
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    rows="2"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">
                            Familia
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Nombres</label
                                >
                                <input
                                    type="text"
                                    bind:value={
                                        piarState.formData.acta.familiaNombres
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Parentesco</label
                                >
                                <input
                                    type="text"
                                    bind:value={
                                        piarState.formData.acta.parentesco
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">
                            Compromisos
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Compromisos en el Aula</label
                                >
                                <textarea
                                    bind:value={
                                        piarState.formData.acta.compromisosAula
                                    }
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    rows="3"
                                    placeholder="Incluya aquí los compromisos específicos para implementar en el aula..."
                                ></textarea>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Actividades de Apoyo en Casa</label
                                >
                                <div class="space-y-2">
                                    {#each piarState.formData.acta.actividadesCasa as actividad, index}
                                        <div
                                            class="flex gap-2 items-start p-3 bg-gray-50 rounded-lg"
                                        >
                                            <div class="flex-1 space-y-2">
                                                <input
                                                    type="text"
                                                    placeholder="Nombre de la Actividad"
                                                    bind:value={
                                                        actividad.nombre
                                                    }
                                                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm"
                                                />
                                                <input
                                                    type="text"
                                                    placeholder="Descripción de la Estrategia"
                                                    bind:value={
                                                        actividad.descripcion
                                                    }
                                                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm"
                                                />
                                                <select
                                                    bind:value={
                                                        actividad.frecuencia
                                                    }
                                                    class="px-2 py-1 border border-gray-300 rounded text-sm"
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
                                            </div>
                                            <button
                                                onclick={() =>
                                                    removeActividadCasa(index)}
                                                class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-sm"
                                            >
                                                ×
                                            </button>
                                        </div>
                                    {/each}
                                    <button
                                        onclick={addActividadCasa}
                                        class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg text-sm"
                                    >
                                        + Agregar Actividad
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">
                            Firmas de los Actores Comprometidos
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="border rounded-lg p-4 text-center">
                                <div
                                    class="h-20 border-b-2 border-gray-300 mb-2"
                                ></div>
                                <p class="text-sm text-gray-600">Estudiante</p>
                            </div>
                            <div class="border rounded-lg p-4 text-center">
                                <div
                                    class="h-20 border-b-2 border-gray-300 mb-2"
                                ></div>
                                <p class="text-sm text-gray-600">
                                    Acudiente / Familia
                                </p>
                            </div>
                            <div class="border rounded-lg p-4 text-center">
                                <div
                                    class="h-20 border-b-2 border-gray-300 mb-2"
                                ></div>
                                <p class="text-sm text-gray-600">
                                    Docentes / Directivos
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
            {/if}

            <!-- Navigation Buttons -->
            <div class="flex justify-between mt-8 pt-4 border-t print:hidden">
                <button
                    onclick={prevStep}
                    disabled={piarState.currentStep === 0}
                    class="px-6 py-3 bg-gray-500 hover:bg-gray-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-white rounded-lg transition-colors"
                >
                    ← Anterior
                </button>
                {#if piarState.currentStep < steps.length - 1}
                    <button
                        onclick={nextStep}
                        class="px-6 py-3 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg transition-colors"
                    >
                        Siguiente →
                    </button>
                {:else}
                    <button
                        onclick={generatePDF}
                        class="px-6 py-3 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors"
                    >
                        📄 Generar PDF Final
                    </button>
                {/if}
            </div>
        </div>
    </main>

    <!-- Print/PDF Preview -->
    {#if piarState.showPreview}
        <div
            class="fixed inset-0 bg-white z-50 overflow-auto p-8 print:static print:z-auto"
        >
            <div class="max-w-4xl mx-auto">
                <button
                    onclick={() => (piarState.showPreview = false)}
                    class="mb-4 px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg print:hidden"
                >
                    ← Volver al Formulario
                </button>

                <!-- PDF Content -->
                <div class="bg-white p-8">
                    <h1 class="text-2xl font-bold text-center mb-2">
                        PLAN INDIVIDUAL DE AJUSTES RAZONABLES - PIAR
                    </h1>
                    <p class="text-center text-sm text-gray-600 mb-6">
                        Decreto 1421 de 2017 - Ministerio de Educación Nacional
                    </p>

                    <!-- Content based on form data -->
                    <div class="space-y-6">
                        <section>
                            <h2 class="text-lg font-bold border-b mb-3">
                                INFORMACIÓN GENERAL
                            </h2>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <p>
                                    <strong>Estudiante:</strong>
                                    {piarState.formData.general.nombres}
                                    {piarState.formData.general.apellidos}
                                </p>
                                <p>
                                    <strong>Documento:</strong>
                                    {piarState.formData.general.tipoDocumento}
                                    {piarState.formData.general.numeroDocumento}
                                </p>
                                <p>
                                    <strong>Edad:</strong>
                                    {piarState.formData.general.edad} años
                                </p>
                                <p>
                                    <strong>Grado:</strong>
                                    {piarState.formData.general.gradoAspira}
                                </p>
                            </div>
                        </section>

                        <section>
                            <h2 class="text-lg font-bold border-b mb-3">
                                CARACTERÍSTICAS DEL ESTUDIANTE
                            </h2>
                            <p class="text-sm">
                                {piarState.formData.piar
                                    .caracteristicasEstudiante}
                            </p>
                        </section>

                        <section>
                            <h2 class="text-lg font-bold border-b mb-3">
                                AJUSTES RAZONABLES
                            </h2>
                            {#each ["Primer", "Segundo", "Tercer"] as trimestre, i}
                                <div class="mb-4">
                                    <h3 class="font-semibold text-sm">
                                        {trimestre} Trimestre
                                    </h3>
                                    {#each [piarState.formData.ajustes.primerTrimestre, piarState.formData.ajustes.segundoTrimestre, piarState.formData.ajustes.tercerTrimestre][i] as ajuste}
                                        {#if ajuste.objetivos || ajuste.ajustes}
                                            <div class="text-sm ml-4 mb-2">
                                                <p>
                                                    <strong
                                                        >{ajuste.area}:</strong
                                                    >
                                                </p>
                                                <p>
                                                    Objetivos: {ajuste.objetivos}
                                                </p>
                                                <p>Ajustes: {ajuste.ajustes}</p>
                                            </div>
                                        {/if}
                                    {/each}
                                </div>
                            {/each}
                        </section>

                        <section>
                            <h2 class="text-lg font-bold border-b mb-3">
                                FIRMAS
                            </h2>
                            <div class="grid grid-cols-3 gap-4 mt-8">
                                <div class="text-center">
                                    <div
                                        class="border-b border-gray-400 h-12 mb-2"
                                    ></div>
                                    <p class="text-xs">Estudiante</p>
                                </div>
                                <div class="text-center">
                                    <div
                                        class="border-b border-gray-400 h-12 mb-2"
                                    ></div>
                                    <p class="text-xs">Acudiente</p>
                                </div>
                                <div class="text-center">
                                    <div
                                        class="border-b border-gray-400 h-12 mb-2"
                                    ></div>
                                    <p class="text-xs">Institución</p>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    {/if}

    <!-- Footer -->
    <footer class="bg-white border-t mt-8 py-4 print:hidden">
        <div class="max-w-7xl mx-auto px-4 text-center text-sm text-gray-600">
            <p>
                PIAR - Plan Individual de Ajustes Razonables | Decreto 1421 de
                2017
            </p>
            <p class="mt-1">
                Ministerio de Educación Nacional - Educación Inclusiva
            </p>
        </div>
    </footer>
</div>

<style>
    :global(.animate-slide-in) {
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @media print {
        .print\:hidden {
            display: none !important;
        }
        .print\:static {
            position: static !important;
        }
        .print\:z-auto {
            z-index: auto !important;
        }
        body {
            background: white !important;
        }
    }
</style>
