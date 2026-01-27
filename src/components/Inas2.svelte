<script lang="ts">
    import { onMount, onDestroy } from "svelte";
    import {
        saveInasistencias,
        getDocentes,
        getMaterias,
        getEstudiantes,
    } from "../../api/service";
    import { SPREADSHEET_ID, WORKSHEET_TITLE } from "../constants";
    import eieLogo from "../assets/eie.png";

    // --- Interfaces para tipado estricto ---
    interface Estudiante {
        nombre: string;
        grado: number | string;
    }

    interface Materia {
        materia: string;
    }

    interface Inasistencia {
        nombre: string;
        motivo: string;
    }

    // --- Estado de datos ---
    let docentes: string[] = [];
    let materias: Materia[] = [];
    let estudiantes: Estudiante[] = [];

    let isLoadingDocentes = false;
    let isLoadingMaterias = false;
    let isLoadingEstudiantes = false;

    // --- Formulario ---
    let formData = {
        docente: "",
        materia: "",
        horas: "",
        grado: "",
        fecha: new Date().toISOString().split("T")[0],
        observaciones: "",
    };

    let inasistencias: Inasistencia[] = [];
    let isLoading = false;
    let message = "";

    // --- Tema y Estilos ---
    let isDarkMode = true;
    let showThemeOptions = false;
    let currentTheme: "light" | "dark" | "system" = "dark";
    let systemPreferenceListener: ((e: MediaQueryListEvent) => void) | null =
        null;

    // Optimización: Variable reactiva para estilos (se calcula solo cuando cambia isDarkMode)
    $: styles = {
        bg: isDarkMode ? "rgb(39, 39, 42)" : "rgb(255, 255, 255)",
        text: isDarkMode ? "rgb(250, 250, 250)" : "rgb(9, 9, 11)",
        label: isDarkMode ? "rgb(212, 212, 216)" : "rgb(63, 63, 70)",
        border: isDarkMode ? "rgb(82, 82, 91)" : "rgb(228, 228, 231)",
        placeholder: isDarkMode ? "rgb(161, 161, 170)" : "rgb(156, 163, 175)",
        icon: isDarkMode ? "rgb(161, 161, 170)" : "rgb(156, 163, 175)",
        cardBg: isDarkMode ? "rgb(24, 24, 27)" : "rgb(255, 255, 255)",
        cardBorder: isDarkMode ? "rgb(63, 63, 70)" : "rgb(228, 228, 231)",
        inputBg: isDarkMode ? "rgb(39, 39, 42)" : "rgb(255, 255, 255)",
    };

    // Optimización: Filtrado reactivo (se ejecuta solo cuando cambia estudiantes o el grado seleccionado)
    $: estudiantesFiltrados = formData.grado
        ? estudiantes.filter((e) => e.grado.toString() === formData.grado)
        : [];

    // --- Motivos predefinidos ---
    const motivos = [
        {
            value: "Sin excusa",
            label: "Sin excusa",
            icon: "🚫",
            bgColor: "bg-red-50",
            borderColor: "border-red-300",
            textColor: "text-red-700",
            darkBgColor: "dark:bg-red-950",
            darkBorderColor: "dark:border-red-800",
            darkTextColor: "dark:text-red-300",
        },
        {
            value: "Excusa",
            label: "Excusa",
            icon: "📄",
            bgColor: "bg-blue-50",
            borderColor: "border-blue-300",
            textColor: "text-blue-700",
            darkBgColor: "dark:bg-blue-950",
            darkBorderColor: "dark:border-blue-800",
            darkTextColor: "dark:text-blue-300",
        },
        {
            value: "Permiso",
            label: "Permiso",
            icon: "✅",
            bgColor: "bg-green-50",
            borderColor: "border-green-300",
            textColor: "text-green-700",
            darkBgColor: "dark:bg-green-950",
            darkBorderColor: "dark:border-green-800",
            darkTextColor: "dark:text-green-300",
        },
        {
            value: "Pacto de Aula",
            label: "Pacto de Aula",
            icon: "🤝",
            bgColor: "bg-purple-50",
            borderColor: "border-purple-300",
            textColor: "text-purple-700",
            darkBgColor: "dark:bg-purple-950",
            darkBorderColor: "dark:border-purple-800",
            darkTextColor: "dark:text-purple-300",
        },
        {
            value: "Uso del celular",
            label: "Uso del celular",
            icon: "📱",
            bgColor: "bg-orange-50",
            borderColor: "border-orange-300",
            textColor: "text-orange-700",
            darkBgColor: "dark:bg-orange-950",
            darkBorderColor: "dark:border-orange-800",
            darkTextColor: "dark:text-orange-300",
        },
        {
            value: "Desorden en Clase",
            label: "Desorden en Clase",
            icon: "🔊",
            bgColor: "bg-yellow-50",
            borderColor: "border-yellow-300",
            textColor: "text-yellow-700",
            darkBgColor: "dark:bg-yellow-950",
            darkBorderColor: "dark:border-yellow-800",
            darkTextColor: "dark:text-yellow-300",
        },
        {
            value: "Fuga",
            label: "Fuga",
            icon: "🏃‍♂️",
            bgColor: "bg-red-100",
            borderColor: "border-red-400",
            textColor: "text-red-800",
            darkBgColor: "dark:bg-red-950",
            darkBorderColor: "dark:border-red-800",
            darkTextColor: "dark:text-red-300",
        },
        {
            value: "LLegada Tarde",
            label: "Llegada Tarde",
            icon: "⏰",
            bgColor: "bg-indigo-50",
            borderColor: "border-indigo-300",
            textColor: "text-indigo-700",
            darkBgColor: "dark:bg-indigo-950",
            darkBorderColor: "dark:border-indigo-800",
            darkTextColor: "dark:text-indigo-300",
        },
        {
            value: "No realización de Aseo",
            label: "No realización de Aseo",
            icon: "🧹",
            bgColor: "bg-teal-50",
            borderColor: "border-teal-300",
            textColor: "text-teal-700",
            darkBgColor: "dark:bg-teal-950",
            darkBorderColor: "dark:border-teal-800",
            darkTextColor: "dark:text-teal-300",
        },
        {
            value: "Licencia por salud",
            label: "Licencia por salud",
            icon: "🏥",
            bgColor: "bg-cyan-50",
            borderColor: "border-cyan-300",
            textColor: "text-cyan-700",
            darkBgColor: "dark:bg-cyan-950",
            darkBorderColor: "dark:border-cyan-800",
            darkTextColor: "dark:text-cyan-300",
        },
        {
            value: "Incapacidad",
            label: "Incapacidad",
            icon: "🩺",
            bgColor: "bg-pink-50",
            borderColor: "border-pink-300",
            textColor: "text-pink-700",
            darkBgColor: "dark:bg-pink-950",
            darkBorderColor: "dark:border-pink-800",
            darkTextColor: "dark:text-pink-300",
        },
        {
            value: "Reunión interna",
            label: "Reunión interna",
            icon: "👥",
            bgColor: "bg-zinc-50",
            borderColor: "border-zinc-300",
            textColor: "text-zinc-700",
            darkBgColor: "dark:bg-zinc-950",
            darkBorderColor: "dark:border-zinc-800",
            darkTextColor: "dark:text-zinc-300",
        },
        {
            value: "Ignorar",
            label: "Ignorar",
            icon: "🚫",
            bgColor: "bg-zinc-50",
            borderColor: "border-zinc-300",
            textColor: "text-zinc-700",
            darkBgColor: "dark:bg-zinc-950",
            darkBorderColor: "dark:border-zinc-800",
            darkTextColor: "dark:text-zinc-300",
        },
    ];

    // --- Funciones de Tema ---
    const applyTheme = (theme: "light" | "dark" | "system") => {
        currentTheme = theme;
        if (theme === "light") isDarkMode = false;
        else if (theme === "dark") isDarkMode = true;
        else
            isDarkMode = window.matchMedia(
                "(prefers-color-scheme: dark)",
            ).matches;

        if (isDarkMode) {
            document.documentElement.classList.add("dark");
            document.documentElement.setAttribute("data-theme", "dark");
        } else {
            document.documentElement.classList.remove("dark");
            document.documentElement.removeAttribute("data-theme");
        }
        localStorage.setItem("theme", theme);
    };

    const setTheme = (theme: "light" | "dark" | "system") => {
        applyTheme(theme);
        showThemeOptions = false;
    };

    const toggleThemeOptions = () => (showThemeOptions = !showThemeOptions);

    const handleClickOutside = (event: MouseEvent) => {
        const target = event.target as HTMLElement;
        if (!target.closest(".theme-dropdown")) showThemeOptions = false;
    };

    // --- Carga de Datos ---
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
            console.error("Error cargando datos iniciales:", error);
        } finally {
            isLoadingDocentes = false;
            isLoadingMaterias = false;
            isLoadingEstudiantes = false;
        }
    };

    // --- Manejadores del Formulario ---
    const handleChange = (event: Event) => {
        const target = event.target as
            | HTMLInputElement
            | HTMLSelectElement
            | HTMLTextAreaElement;
        (formData as any)[target.name] = target.value;
    };

    // Lógica extraída para mejor rendimiento y claridad
    const handleInasistenciaChange = (
        estudianteNombre: string,
        nuevoMotivo: string,
    ) => {
        const index = inasistencias.findIndex(
            (i) => i.nombre === estudianteNombre,
        );

        if (nuevoMotivo === "" || nuevoMotivo === "Ignorar") {
            // Eliminar si existe
            if (index >= 0) {
                inasistencias.splice(index, 1);
                inasistencias = inasistencias; // Trigger reactividad
            }
        } else {
            // Actualizar o Agregar
            if (index >= 0) {
                inasistencias[index].motivo = nuevoMotivo;
            } else {
                inasistencias.push({
                    nombre: estudianteNombre,
                    motivo: nuevoMotivo,
                });
            }
            inasistencias = inasistencias; // Trigger reactividad
        }
    };

    const handleSubmit = async (event: Event) => {
        event.preventDefault();
        if (isLoading || inasistencias.length === 0) return;

        isLoading = true;
        message = "";

        try {
            const currentTimestamp = new Date().toISOString();
            const inasistenciasPayload = inasistencias.map((item) => [
                currentTimestamp,
                formData.docente,
                formData.fecha,
                formData.horas,
                formData.materia,
                item.motivo,
                formData.grado,
                item.nombre,
                formData.observaciones,
            ]);

            await saveInasistencias({
                spreadsheetId: SPREADSHEET_ID,
                worksheetTitle: WORKSHEET_TITLE,
                inasistencias: inasistenciasPayload,
            });

            message = `${inasistencias.length} inasistencia(s) registrada(s) exitosamente`;

            // Auto-ocultar mensaje después de 3 segundos
            setTimeout(() => {
                message = "";
            }, 3000);

            // Reseteo Correcto del Formulario
            formData = {
                ...formData,
                grado: "", // Resetear grado
                fecha: new Date().toISOString().split("T")[0], // Resetear fecha a hoy
                observaciones: "",
                horas: "", // Opcional: Resetear horas también
            };
            inasistencias = [];
        } catch (error) {
            console.error("Error enviando:", error);
            message = "Error al registrar la inasistencia";
        } finally {
            isLoading = false;
        }
    };

    // --- Ciclo de Vida ---
    onMount(() => {
        const mediaQuery = window.matchMedia("(prefers-color-scheme: dark)");
        systemPreferenceListener = () => {
            if (currentTheme === "system") applyTheme("system");
        };
        mediaQuery.addEventListener("change", systemPreferenceListener);

        const savedTheme =
            (localStorage.getItem("theme") as "light" | "dark" | "system") ||
            "system";
        applyTheme(savedTheme);

        loadData();
        document.addEventListener("click", handleClickOutside);
    });

    onDestroy(() => {
        if (systemPreferenceListener) {
            window
                .matchMedia("(prefers-color-scheme: dark)")
                .removeEventListener("change", systemPreferenceListener);
        }
        document.removeEventListener("click", handleClickOutside);
    });
</script>

<div
    class="min-h-screen py-6 px-4 sm:py-8 transition-colors duration-200"
    style="background-color: {isDarkMode
        ? 'rgb(9, 9, 11)'
        : 'rgb(250, 250, 250)'};"
>
    <div class="theme-dropdown fixed bottom-6 right-6 z-50">
        {#if showThemeOptions}
            <div
                class="absolute bottom-16 right-0 border rounded-lg shadow-xl p-2 min-w-[180px] mb-2 animate-fade-in"
                style="background-color: {styles.bg}; border-color: {styles.border};"
            >
                {#each ["light", "dark", "system"] as themeOption}
                    <button
                        on:click={() => setTheme(themeOption as any)}
                        class="w-full flex items-center px-3 py-2 text-sm rounded-md transition-colors hover:bg-black/5 dark:hover:bg-white/5"
                        style="color: {styles.text};"
                    >
                        <span class="capitalize"
                            >{themeOption === "system"
                                ? "Sistema"
                                : themeOption === "light"
                                  ? "Claro"
                                  : "Oscuro"}</span
                        >
                    </button>
                {/each}
            </div>
        {/if}

        <button
            on:click={toggleThemeOptions}
            class="w-12 h-12 border rounded-full shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center group active:scale-95"
            style="background-color: {styles.bg}; border-color: {styles.border};"
            aria-label="Opciones de tema"
        >
            {#if isDarkMode}
                <svg
                    class="w-5 h-5 text-zinc-300"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    ><path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
                    /></svg
                >
            {:else}
                <svg
                    class="w-5 h-5 text-zinc-600"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    ><path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
                    /></svg
                >
            {/if}
        </button>
    </div>

    <div
        class="w-full max-w-2xl mx-auto rounded-xl p-6 sm:p-8 transition-colors duration-200 border"
        style="background-color: {styles.cardBg}; border-color: {styles.cardBorder};"
    >
        <div class="flex justify-center mb-8">
            <img src={eieLogo} alt="EIE Logo" class="h-16 w-auto" />
        </div>

        <h1
            class="text-2xl sm:text-3xl tracking-tight font-bold text-center mb-8"
            style="color: {styles.text};"
        >
            Registrar Inasistencia
        </h1>

        <form on:submit={handleSubmit} class="space-y-6">
            <div class="relative">
                <label
                    for="docente"
                    class="block text-sm font-medium tracking-wide mb-2"
                    style="color: {styles.label};">Docente</label
                >
                <select
                    id="docente"
                    name="docente"
                    bind:value={formData.docente}
                    required
                    disabled={isLoadingDocentes}
                    class="w-full px-3 py-2.5 text-base rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 transition-colors appearance-none cursor-pointer"
                    style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
                >
                    <option value=""
                        >{isLoadingDocentes
                            ? "Cargando..."
                            : "Seleccione un docente"}</option
                    >
                    {#each docentes as docente}
                        <option value={docente}>{docente}</option>
                    {/each}
                </select>
            </div>

            <div class="relative">
                <label
                    for="materia"
                    class="block text-sm font-medium tracking-wide mb-2"
                    style="color: {styles.label};">Materia</label
                >
                <select
                    id="materia"
                    name="materia"
                    bind:value={formData.materia}
                    required
                    disabled={isLoadingMaterias}
                    class="w-full px-3 py-2.5 text-base rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 transition-colors appearance-none cursor-pointer"
                    style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
                >
                    <option value=""
                        >{isLoadingMaterias
                            ? "Cargando..."
                            : "Seleccione una materia"}</option
                    >
                    {#each materias as materia}
                        <option value={materia.materia}
                            >{materia.materia}</option
                        >
                    {/each}
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="relative">
                    <label
                        for="horas"
                        class="block text-sm font-medium tracking-wide mb-2"
                        style="color: {styles.label};">Cantidad de Horas</label
                    >
                    <select
                        id="horas"
                        name="horas"
                        bind:value={formData.horas}
                        required
                        class="w-full px-3 py-2.5 text-base rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 appearance-none cursor-pointer"
                        style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
                    >
                        <option value="">Seleccione horas</option>
                        <option value="0">Sin hora específica</option>
                        {#each [1, 2, 3, 4] as h}
                            <option value={h.toString()}
                                >{h} {h === 1 ? "Hora" : "Horas"}</option
                            >
                        {/each}
                    </select>
                </div>

                <div class="relative">
                    <label
                        for="grado"
                        class="block text-sm font-medium tracking-wide mb-2"
                        style="color: {styles.label};">Grado</label
                    >
                    <select
                        id="grado"
                        name="grado"
                        bind:value={formData.grado}
                        required
                        disabled={isLoadingEstudiantes}
                        class="w-full px-3 py-2.5 text-base rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 transition-colors appearance-none cursor-pointer"
                        style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
                    >
                        <option value=""
                            >{isLoadingEstudiantes
                                ? "Cargando..."
                                : "Seleccione un grado"}</option
                        >
                        {#each ["601", "602", "701", "702", "801", "802", "901", "902", "1001", "1101", "1102"] as g}
                            <option value={g}
                                >{g
                                    .replace(/0(\d)$/, "°$1")
                                    .replace(/(\d{1,2})0(\d)/, "$1°$2")}</option
                            >
                        {/each}
                    </select>
                </div>
            </div>

            {#if formData.grado && formData.horas !== ""}
                <div class="animate-fade-in">
                    <!-- svelte-ignore a11y_label_has_associated_control -->
                    <label
                        class="block text-sm font-medium tracking-wide mb-3"
                        style="color: {styles.label};"
                    >
                        Estudiantes del Grado {formData.grado}
                    </label>

                    <div
                        class="space-y-2 max-h-[500px] overflow-y-auto border rounded-xl p-3 bg-zinc-50/50 dark:bg-zinc-900/50 backdrop-blur-sm scrollbar-thin scrollbar-thumb-zinc-300 dark:scrollbar-thumb-zinc-600"
                        style="border-color: {styles.border};"
                    >
                        {#each estudiantesFiltrados as estudiante (estudiante.nombre)}
                            {@const currentInasistencia = inasistencias.find(
                                (i) => i.nombre === estudiante.nombre,
                            )}
                            {@const motivoSeleccionado = currentInasistencia
                                ? motivos.find(
                                      (m) =>
                                          m.value ===
                                          currentInasistencia.motivo,
                                  )
                                : null}

                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between p-3 border rounded-lg transition-all duration-150 hover:shadow-sm gap-3"
                                style="background-color: {styles.bg}; border-color: {motivoSeleccionado
                                    ? 'transparent'
                                    : styles.border};"
                                class:ring-1={motivoSeleccionado}
                                class:ring-indigo-500={motivoSeleccionado}
                            >
                                <div class="flex-1 min-w-0">
                                    <span
                                        class="text-sm font-medium truncate block"
                                        style="color: {styles.text};"
                                    >
                                        {estudiante.nombre}
                                    </span>

                                    {#if motivoSeleccionado}
                                        <div class="mt-2 animate-fade-in">
                                            <span
                                                class={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border ${motivoSeleccionado.bgColor} ${motivoSeleccionado.textColor} ${motivoSeleccionado.borderColor} ${motivoSeleccionado.darkBgColor} ${motivoSeleccionado.darkTextColor} ${motivoSeleccionado.darkBorderColor}`}
                                            >
                                                <span class="mr-1"
                                                    >{motivoSeleccionado.icon}</span
                                                >
                                                {motivoSeleccionado.label}
                                            </span>
                                        </div>
                                    {/if}
                                </div>

                                <div class="relative w-full sm:w-48">
                                    <select
                                        value={currentInasistencia?.motivo ||
                                            ""}
                                        on:change={(e) =>
                                            handleInasistenciaChange(
                                                estudiante.nombre,
                                                e.currentTarget.value,
                                            )}
                                        class="w-full appearance-none border rounded-lg px-3 py-2 pr-8 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-colors"
                                        style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
                                    >
                                        <option value=""
                                            >Seleccionar motivo...</option
                                        >
                                        {#each motivos as motivo}
                                            <option value={motivo.value}
                                                >{motivo.icon}
                                                {motivo.label}</option
                                            >
                                        {/each}
                                    </select>

                                    <div
                                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3"
                                        style="color: {styles.icon};"
                                    >
                                        <svg
                                            class="w-4 h-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            ><path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 9l-7 7-7-7"
                                            /></svg
                                        >
                                    </div>
                                </div>
                            </div>
                        {/each}

                        {#if estudiantesFiltrados.length === 0}
                            <div
                                class="text-center py-8 text-sm opacity-60"
                                style="color: {styles.text}"
                            >
                                No se encontraron estudiantes en este grado.
                            </div>
                        {/if}
                    </div>
                </div>
            {/if}

            <div>
                <label
                    for="fecha"
                    class="block text-sm font-medium tracking-wide mb-2"
                    style="color: {styles.label};">Fecha</label
                >
                <input
                    type="date"
                    id="fecha"
                    name="fecha"
                    bind:value={formData.fecha}
                    required
                    class="w-full px-3 py-2.5 text-base border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors"
                    style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
                />
            </div>

            <div>
                <label
                    for="observaciones"
                    class="block text-sm font-medium tracking-wide mb-2"
                    style="color: {styles.label};">Observaciones</label
                >
                <!-- svelte-ignore element_invalid_self_closing_tag -->
                <textarea
                    id="observaciones"
                    name="observaciones"
                    bind:value={formData.observaciones}
                    rows="3"
                    placeholder="Opcional..."
                    class="w-full px-3 py-2.5 text-base border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none transition-colors"
                    style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text}; placeholder-color: {styles.placeholder};"
                />
            </div>

            <div
                class="flex items-center justify-between pt-4 mt-6 border-t"
                style="border-color: {styles.border};"
            >
                <div class="text-sm" style="color: {styles.label};">
                    Total: <span
                        class="font-semibold"
                        style="color: {styles.text};"
                        >{inasistencias.length}</span
                    >
                </div>

                <button
                    type="submit"
                    disabled={isLoading || inasistencias.length === 0}
                    class="active:scale-[0.98] bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 px-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-150 flex items-center font-medium shadow-md"
                >
                    {#if isLoading}
                        <svg
                            class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
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
                        Enviando...
                    {:else}
                        Registrar {inasistencias.length > 0
                            ? `(${inasistencias.length})`
                            : ""}
                    {/if}
                </button>
            </div>
        </form>

        {#if message}
            <div
                class={`mt-6 p-4 rounded-lg text-sm font-medium animate-fade-in border flex items-center justify-center`}
                style={`
            background-color: ${
                message.includes("exitosamente")
                    ? isDarkMode
                        ? "rgba(6, 78, 59, 0.5)"
                        : "rgb(236, 253, 245)"
                    : isDarkMode
                      ? "rgba(127, 29, 29, 0.5)"
                      : "rgb(254, 242, 242)"
            };
            border-color: ${
                message.includes("exitosamente")
                    ? isDarkMode
                        ? "rgb(6, 78, 59)"
                        : "rgb(167, 243, 208)"
                    : isDarkMode
                      ? "rgb(127, 29, 29)"
                      : "rgb(254, 202, 202)"
            };
            color: ${
                message.includes("exitosamente")
                    ? isDarkMode
                        ? "rgb(209, 213, 219)"
                        : "rgb(21, 128, 61)"
                    : isDarkMode
                      ? "rgb(254, 202, 202)"
                      : "rgb(153, 27, 27)"
            };
        `}
            >
                {message}
            </div>
        {/if}
    </div>
</div>

<style>
    @keyframes fade-in {
        from {
            opacity: 0;
            transform: translateY(5px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .animate-fade-in {
        animation: fade-in 0.3s ease-out forwards;
    }
    /* Estilos para scrollbar personalizado */
    .scrollbar-thin::-webkit-scrollbar {
        width: 6px;
    }
    .scrollbar-thin::-webkit-scrollbar-track {
        background: transparent;
    }
    .scrollbar-thin::-webkit-scrollbar-thumb {
        background-color: rgba(156, 163, 175, 0.5);
        border-radius: 20px;
    }
</style>
