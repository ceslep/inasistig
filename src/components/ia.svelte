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

    // Estado de datos
    let docentes: string[] = [];
    let materias: Array<{ materia: string }> = [];
    let estudiantes: Array<{ nombre: string; grado: number }> = [];
    let isLoadingDocentes = false;
    let isLoadingMaterias = false;
    let isLoadingEstudiantes = false;

    // Formulario
    let formData = {
        docente: "",
        materia: "",
        horas: "",
        grado: "",
        fecha: new Date().toLocaleDateString('en-CA'),
        observaciones: "",
    };

    let inasistencias: Array<{
        nombre: string;
        motivo: string;
        observaciones?: string;
    }> = [];
    let isLoading = false;
    let message = "";
    let isDarkMode = true;
    let showThemeOptions = false;
    let currentTheme: "light" | "dark" | "system" = "dark";

    // Helper function for theme-based styles
    const getThemeStyles = () => ({
        bg: isDarkMode ? "rgb(39, 39, 42)" : "rgb(255, 255, 255)",
        text: isDarkMode ? "rgb(250, 250, 250)" : "rgb(9, 9, 11)",
        label: isDarkMode ? "rgb(212, 212, 216)" : "rgb(63, 63, 70)",
        border: isDarkMode ? "rgb(82, 82, 91)" : "rgb(228, 228, 231)",
        placeholder: isDarkMode ? "rgb(161, 161, 170)" : "rgb(156, 163, 175)",
        icon: isDarkMode ? "rgb(161, 161, 170)" : "rgb(156, 163, 175)",
    });

    // Helper functions for student inasistencias
    const getStudentMotivo = (studentName: string) => {
        const student = inasistencias.find(
            (item) => item.nombre === studentName,
        );
        return student?.motivo || "";
    };

    const setStudentMotivo = (studentName: string, motivo: string) => {
        const existingIndex = inasistencias.findIndex(
            (item) => item.nombre === studentName,
        );
        console.log(`🎯 Seteando motivo para ${studentName}: "${motivo}"`);
        console.log(`📋 Array ANTES:`, inasistencias);

        if (existingIndex >= 0) {
            if (motivo) {
                console.log(
                    `✏️ Actualizando existente en índice ${existingIndex}`,
                );
                inasistencias[existingIndex].motivo = motivo;
            } else {
                console.log(`🗑️ Eliminando en índice ${existingIndex}`);
                inasistencias.splice(existingIndex, 1);
            }
        } else if (motivo) {
            console.log(`➕ Agregando nuevo: ${studentName}`);
            inasistencias.push({ nombre: studentName, motivo });
        }

        console.log(`📋 Array DESPUÉS:`, inasistencias);
        console.log(`🔢 Total: ${inasistencias.length}`);
    };
    let systemPreferenceListener: ((e: MediaQueryListEvent) => void) | null =
        null;

    // Motivos predefinidos
    const motivos = [
        {
            value: "Sin excusa",
            label: "Sin excusa",
            color: "bg-red-500",
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
            color: "bg-blue-500",
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
            color: "bg-green-500",
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
            color: "bg-purple-500",
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
            color: "bg-orange-500",
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
            color: "bg-yellow-500",
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
            color: "bg-red-600",
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
            color: "bg-indigo-500",
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
            color: "bg-teal-500",
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
            color: "bg-cyan-500",
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
            color: "bg-pink-500",
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
            color: "bg-zinc-500",
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
            color: "bg-zinc-500",
            icon: "🚫",
            bgColor: "bg-zinc-50",
            borderColor: "border-zinc-300",
            textColor: "text-zinc-700",
            darkBgColor: "dark:bg-zinc-950",
            darkBorderColor: "dark:border-zinc-800",
            darkTextColor: "dark:text-zinc-300",
        },
    ];

    // Aplica el tema seleccionado y actualiza el DOM
    const applyTheme = (theme: "light" | "dark" | "system") => {
        currentTheme = theme;

        // Determinar modo efectivo
        if (theme === "light") {
            isDarkMode = false;
        } else if (theme === "dark") {
            isDarkMode = true;
        } else {
            // Modo sistema
            const prefersDark = window.matchMedia(
                "(prefers-color-scheme: dark)",
            ).matches;
            isDarkMode = prefersDark;
        }

        // Actualizar clases del DOM
        if (isDarkMode) {
            document.documentElement.classList.add("dark");
            document.documentElement.setAttribute("data-theme", "dark");
        } else {
            document.documentElement.classList.remove("dark");
            document.documentElement.removeAttribute("data-theme");
        }

        // Guardar preferencia
        localStorage.setItem("theme", theme);
    };

    // Establecer tema específico
    const setTheme = (theme: "light" | "dark" | "system") => {
        applyTheme(theme);
        showThemeOptions = false;
    };

    // Alternar visibilidad del menú de temas
    const toggleThemeOptions = () => {
        showThemeOptions = !showThemeOptions;
    };

    // Cerrar menú al hacer clic fuera
    const handleClickOutside = (event: MouseEvent) => {
        const target = event.target as HTMLElement;
        if (!target.closest(".theme-dropdown")) {
            showThemeOptions = false;
        }
    };

    // Cargar datos
    const loadDocentes = async () => {
        isLoadingDocentes = true;
        try {
            docentes = await getDocentes();
        } catch (error) {
            console.error("Error cargando docentes:", error);
        } finally {
            isLoadingDocentes = false;
        }
    };

    const loadMaterias = async () => {
        isLoadingMaterias = true;
        try {
            materias = await getMaterias();
        } catch (error) {
            console.error("Error cargando materias:", error);
        } finally {
            isLoadingMaterias = false;
        }
    };

    const loadEstudiantes = async () => {
        isLoadingEstudiantes = true;
        try {
            estudiantes = await getEstudiantes();
        } catch (error) {
            console.error("Error cargando estudiantes:", error);
        } finally {
            isLoadingEstudiantes = false;
        }
    };

    // Manejar envío de formulario
    const handleChange = (event: Event) => {
        const target = event.target as
            | HTMLInputElement
            | HTMLSelectElement
            | HTMLTextAreaElement;
        const { name, value } = target;
        (formData as any)[name] = value;
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
                formData.horas === "0"
                    ? "Sin hora específica"
                    : `${formData.horas} horas`,
                formData.materia,
                item.motivo,
                formData.grado,
                formData.observaciones,
                item.nombre,
                item.observaciones || "",
            ]);

            const payload: any = {
                spreadsheetId: SPREADSHEET_ID,
                worksheetTitle: WORKSHEET_TITLE,
                inasistencias: inasistenciasPayload,
            };

            // Log payload to console before sending
            console.log("📤 Payload a enviar al backend:", payload);
            console.log("📋 Form data completo:", formData);
            console.log("👥 Inasistencias registradas:", inasistencias);

            await saveInasistencias(payload);

            message = `${inasistencias.length} inasistencia(s) registrada(s) exitosamente`;
            formData = {
                docente: "",
                materia: "",
                horas: "",
                fecha: "",
                grado: new Date().toISOString().split("T")[0],
                observaciones: "",
            };
            inasistencias = [];
        } catch (error) {
            console.error("Error detallado:", error);
            message = "Error al registrar la inasistencia";
        } finally {
            isLoading = false;
        }
    };

    // Inicialización en ciclo de vida de Svelte
    onMount(() => {
        // Configurar listener para cambios del sistema
        const mediaQuery = window.matchMedia("(prefers-color-scheme: dark)");
        systemPreferenceListener = (e) => {
            if (currentTheme === "system") {
                applyTheme("system");
            }
        };
        mediaQuery.addEventListener("change", systemPreferenceListener);

        // Cargar preferencia guardada o usar sistema por defecto
        const savedTheme =
            (localStorage.getItem("theme") as "light" | "dark" | "system") ||
            "system";
        applyTheme(savedTheme);

        // Cargar datos una sola vez
        loadDocentes();
        loadMaterias();
        loadEstudiantes();

        // Evento para cerrar menú de temas
        document.addEventListener("click", handleClickOutside);
    });

    onDestroy(() => {
        // Limpiar listeners
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
    <!-- Theme dropdown container -->
    <div class="theme-dropdown fixed bottom-6 right-6 z-50">
        <!-- Theme options dropdown -->
        {#if showThemeOptions}
            <div
                class="absolute bottom-16 right-0 border rounded-lg shadow-xl p-2 min-w-[180px] mb-2 animate-fade-in"
                style="background-color: {getThemeStyles()
                    .bg}; border-color: {getThemeStyles().border};"
            >
                <!-- Light mode option -->
                <button
                    on:click={() => setTheme("light")}
                    class="w-full flex items-center px-3 py-2 text-sm rounded-md transition-colors"
                    style="color: {getThemeStyles().text};"
                >
                    {#if !isDarkMode}
                        <svg
                            class="w-4 h-4 mr-2 text-emerald-500 flex-shrink-0"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>
                    {/if}
                    <svg
                        class="w-4 h-4 mr-3 text-amber-500 flex-shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
                        />
                    </svg>
                    Modo Claro
                </button>

                <!-- Dark mode option -->
                <button
                    on:click={() => setTheme("dark")}
                    class="w-full flex items-center px-3 py-2 text-sm rounded-md transition-colors"
                    style="color: {getThemeStyles().text};"
                >
                    <svg
                        class="w-4 h-4 mr-2 text-emerald-500 flex-shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>
                    <svg
                        class="w-4 h-4 mr-3 text-indigo-500 flex-shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
                        />
                    </svg>
                    Modo Oscuro
                </button>

                <!-- System mode option -->
                <button
                    on:click={() => setTheme("system")}
                    class="w-full flex items-center px-3 py-2 text-sm rounded-md transition-colors"
                    style="color: {getThemeStyles().text};"
                >
                    <svg
                        class="w-4 h-4 mr-3 text-zinc-500 flex-shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                        />
                    </svg>
                    Preferencia del Sistema
                </button>
            </div>
        {/if}

        <!-- Main theme toggle button -->
        <button
            on:click={toggleThemeOptions}
            class="w-12 h-12 border rounded-full shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center group active:scale-95"
            style="background-color: {getThemeStyles()
                .bg}; border-color: {getThemeStyles().border};"
            aria-label="Opciones de tema"
        >
            {#if isDarkMode}
                <!-- Moon icon for dark mode -->
                <svg
                    class="w-5 h-5 text-zinc-600 dark:text-zinc-300 group-hover:text-indigo-500 transition-colors"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
                    />
                </svg>
            {:else}
                <!-- Sun icon for light mode -->
                <svg
                    class="w-5 h-5 text-zinc-600 dark:text-zinc-300 group-hover:text-amber-500 transition-colors"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
                    />
                </svg>
            {/if}
        </button>
    </div>

    <div
        class="w-full max-w-2xl mx-auto rounded-xl p-6 sm:p-8 transition-colors duration-200 border"
        style="
      background-color: {isDarkMode ? 'rgb(24, 24, 27)' : 'rgb(255, 255, 255)'};
      border-color: {isDarkMode ? 'rgb(63, 63, 70)' : 'rgb(228, 228, 231)'};
    "
    >
        <div class="flex justify-center mb-8">
            <img src={eieLogo} alt="EIE Logo" class="h-16 w-auto" />
        </div>

        <h1
            class="text-2xl sm:text-3xl tracking-tight font-bold text-center mb-8"
            style="color: {isDarkMode
                ? 'rgb(250, 250, 250)'
                : 'rgb(9, 9, 11)'};"
        >
            Registrar Inasistencia
        </h1>

        <form on:submit={handleSubmit} class="space-y-6">
            <!-- Docente -->
            <div class="relative">
                <label
                    for="docente"
                    class="block text-sm font-medium tracking-wide mb-2"
                    style="color: {getThemeStyles().label};"
                >
                    Docente
                </label>
                <select
                    id="docente"
                    name="docente"
                    bind:value={formData.docente}
                    on:change={handleChange}
                    required
                    disabled={isLoadingDocentes}
                    class="w-full px-3 py-2.5 text-base rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 transition-colors appearance-none cursor-pointer"
                    style="
            background-color: {getThemeStyles().bg};
            border-color: {getThemeStyles().border};
            color: {getThemeStyles().text};
          "
                >
                    <option value="" style="color: {getThemeStyles().text};">
                        {isLoadingDocentes
                            ? "Cargando..."
                            : "Seleccione un docente"}
                    </option>
                    {#each docentes as docente}
                        <option
                            value={docente}
                            style="color: {getThemeStyles().text};"
                            >{docente}</option
                        >
                    {/each}
                </select>
                <!-- Custom dropdown arrow -->
                <div
                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3"
                    style="color: {getThemeStyles().icon};"
                >
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 9l-7 7-7-7"
                        />
                    </svg>
                </div>
            </div>

            <!-- Materia -->
            <div class="relative">
                <label
                    for="materia"
                    class="block text-sm font-medium tracking-wide mb-2"
                    style="color: {getThemeStyles().label};"
                >
                    Materia
                </label>
                <select
                    id="materia"
                    name="materia"
                    bind:value={formData.materia}
                    on:change={handleChange}
                    required
                    disabled={isLoadingMaterias}
                    class="w-full px-3 py-2.5 text-base rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 transition-colors appearance-none cursor-pointer"
                    style="background-color: {getThemeStyles()
                        .bg}; border-color: {getThemeStyles()
                        .border}; color: {getThemeStyles().text};"
                >
                    <option value="" style="color: {getThemeStyles().text};">
                        {isLoadingMaterias
                            ? "Cargando..."
                            : "Seleccione una materia"}
                    </option>
                    {#each materias as materia}
                        <option
                            value={materia.materia}
                            style="color: {getThemeStyles().text};"
                            >{materia.materia}</option
                        >
                    {/each}
                </select>
                <!-- Custom dropdown arrow -->
                <div
                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3"
                    style="color: {getThemeStyles().icon};"
                >
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 9l-7 7-7-7"
                        />
                    </svg>
                </div>
            </div>

            <!-- Horas -->
            <div class="relative">
                <label
                    for="horas"
                    class="block text-sm font-medium tracking-wide mb-2"
                    style="color: {getThemeStyles().label};"
                >
                    Cantidad de Horas
                </label>
                <select
                    id="horas"
                    name="horas"
                    bind:value={formData.horas}
                    on:change={handleChange}
                    required
                    class="w-full px-3 py-2.5 text-base bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-900 transition-colors appearance-none cursor-pointer text-zinc-900 dark:text-zinc-100"
                    style="background-color: {getThemeStyles()
                        .bg}; border-color: {getThemeStyles()
                        .border}; color: {getThemeStyles().text};"
                >
                    <option value="" style="color: {getThemeStyles().text};"
                        >Seleccione horas</option
                    >
                    <option value="0" style="color: {getThemeStyles().text};"
                        >Sin hora específica</option
                    >
                    <option value="1" style="color: {getThemeStyles().text};"
                        >1 Hora</option
                    >
                    <option value="2" style="color: {getThemeStyles().text};"
                        >2 Horas</option
                    >
                    <option value="3" style="color: {getThemeStyles().text};"
                        >3 Horas</option
                    >
                    <option value="4" style="color: {getThemeStyles().text};"
                        >4 Horas</option
                    >
                </select>
                <!-- Custom dropdown arrow -->
                <div
                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3"
                    style="color: {getThemeStyles().icon};"
                >
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 9l-7 7-7-7"
                        />
                    </svg>
                </div>
            </div>

            <!-- Grado -->
            <div class="relative">
                <label
                    for="grado"
                    class="block text-sm font-medium tracking-wide mb-2"
                    style="color: {getThemeStyles().label};"
                >
                    Grado
                </label>
                <select
                    id="grado"
                    name="grado"
                    bind:value={formData.grado}
                    on:change={handleChange}
                    required
                    disabled={isLoadingEstudiantes}
                    class="w-full px-3 py-2.5 text-base rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 transition-colors appearance-none cursor-pointer"
                    style="background-color: {getThemeStyles()
                        .bg}; border-color: {getThemeStyles()
                        .border}; color: {getThemeStyles().text};"
                >
                    <option value="" style="color: {getThemeStyles().text};">
                        {isLoadingEstudiantes
                            ? "Cargando..."
                            : "Seleccione un grado"}
                    </option>
                    <option value="601" style="color: {getThemeStyles().text};"
                        >6°1</option
                    >
                    <option value="602" style="color: {getThemeStyles().text};"
                        >6°2</option
                    >
                    <option value="701" style="color: {getThemeStyles().text};"
                        >7°1</option
                    >
                    <option value="702" style="color: {getThemeStyles().text};"
                        >7°2</option
                    >
                    <option value="801" style="color: {getThemeStyles().text};"
                        >8°1</option
                    >
                    <option value="802" style="color: {getThemeStyles().text};"
                        >8°2</option
                    >
                    <option value="901" style="color: {getThemeStyles().text};"
                        >9°1</option
                    >
                    <option value="902" style="color: {getThemeStyles().text};"
                        >9°2</option
                    >
                    <option value="1001" style="color: {getThemeStyles().text};"
                        >10°1</option
                    >
                    <option value="1101" style="color: {getThemeStyles().text};"
                        >11°1</option
                    >
                    <option value="1102" style="color: {getThemeStyles().text};"
                        >11°2</option
                    >
                </select>
                <!-- Custom dropdown arrow -->
                <div
                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3"
                    style="color: {getThemeStyles().icon};"
                >
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 9l-7 7-7-7"
                        />
                    </svg>
                </div>
            </div>

            <!-- Estudiantes -->
            {#if formData.grado && formData.horas !== ""}
                <div>
                    <!-- svelte-ignore a11y_label_has_associated_control -->
                    <label
                        class="block text-sm font-medium tracking-wide mb-3"
                        style="color: {getThemeStyles().label};"
                    >
                        Estudiantes del Grado {formData.grado} ({formData.horas ===
                        "0"
                            ? "Sin hora específica"
                            : formData.horas +
                              (parseInt(formData.horas) === 1
                                  ? " hora"
                                  : " horas")})
                    </label>
                    <!-- Bento-style card container -->
                    <div
                        class="space-y-2 max-h-72 overflow-y-auto border border-zinc-200 dark:border-zinc-700 rounded-xl p-3 bg-zinc-50/50 dark:bg-zinc-900/50 backdrop-blur-sm"
                    >
                        {#each estudiantes.filter((e) => e.grado.toString() === formData.grado) as estudiante, index}
                            {@const currentInasistencia = inasistencias.find(
                                (item) => item.nombre === estudiante.nombre,
                            )}
                            {@const motivoSeleccionado =
                                currentInasistencia?.motivo
                                    ? motivos.find(
                                          (m) =>
                                              m.value ===
                                              currentInasistencia.motivo,
                                      )
                                    : null}

                            <!-- Debug logs for tracking -->
                            {#if typeof window !== "undefined"}
                                {console.log(
                                    "Estudiante:",
                                    estudiante.nombre,
                                    "Inasistencia actual:",
                                    currentInasistencia,
                                )}
                            {/if}

                            <!-- Modern student row card -->
                            <div
                                class="flex items-center justify-between p-3 border rounded-lg transition-all duration-150"
                                style="background-color: {getThemeStyles()
                                    .bg}; border-color: {getThemeStyles()
                                    .border};"
                            >
                                <div class="flex-1">
                                    <span
                                        class="text-sm font-medium text-zinc-900 dark:text-zinc-100"
                                        style="color: {getThemeStyles().text};"
                                        >{estudiante.nombre}</span
                                    >
                                    {#if motivoSeleccionado}
                                        <div class="mt-2">
                                            <!-- Modern badge with opacity and subtle borders -->
                                            <span
                                                class={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${motivoSeleccionado.bgColor} ${motivoSeleccionado.textColor} ${motivoSeleccionado.borderColor} border ${motivoSeleccionado.darkBgColor} ${motivoSeleccionado.darkTextColor} ${motivoSeleccionado.darkBorderColor}`}
                                            >
                                                <span class="mr-1"
                                                    >{motivoSeleccionado.icon}</span
                                                >
                                                {motivoSeleccionado.label}
                                            </span>
                                        </div>
                                    {/if}
                                </div>

                                <div class="relative">
                                    <select
                                        value={getStudentMotivo(
                                            estudiante.nombre,
                                        )}
                                        on:change={(e) => {
                                            const motivoValue = (
                                                e.target as HTMLSelectElement
                                            ).value;
                                            setStudentMotivo(
                                                estudiante.nombre,
                                                motivoValue,
                                            );
                                        }}
                                        on:change={(e) => {
                                            const motivoValue = (
                                                e.target as HTMLSelectElement
                                            ).value;
                                            console.log(
                                                `🎯 Cambio en estudiante: ${estudiante.nombre}`,
                                            );
                                            console.log(
                                                `📝 Motivo seleccionado: "${motivoValue}"`,
                                            );
                                            console.log(
                                                `📋 Array de inasistencias ANTES:`,
                                                inasistencias,
                                            );

                                            const existingIndex =
                                                inasistencias.findIndex(
                                                    (item) =>
                                                        item.nombre ===
                                                        estudiante.nombre,
                                                );
                                            console.log(
                                                `🔍 Índice encontrado: ${existingIndex}`,
                                            );

                                            if (existingIndex >= 0) {
                                                if (motivoValue) {
                                                    console.log(
                                                        `✏️ Actualizando estudiante existente en índice ${existingIndex}`,
                                                    );
                                                    inasistencias[
                                                        existingIndex
                                                    ].motivo = motivoValue;
                                                    if (
                                                        !inasistencias[
                                                            existingIndex
                                                        ].observaciones
                                                    ) {
                                                        inasistencias[
                                                            existingIndex
                                                        ].observaciones = "";
                                                    }
                                                } else {
                                                    console.log(
                                                        `🗑️ Eliminando estudiante en índice ${existingIndex}`,
                                                    );
                                                    inasistencias.splice(
                                                        existingIndex,
                                                        1,
                                                    );
                                                }
                                            } else if (motivoValue) {
                                                console.log(
                                                    `➕ Agregando nuevo estudiante: ${estudiante.nombre} con motivo: ${motivoValue}`,
                                                );
                                                inasistencias.push({
                                                    nombre: estudiante.nombre,
                                                    motivo: motivoValue,
                                                    observaciones: "",
                                                });
                                            }

                                            console.log(
                                                `📋 Array de inasistencias DESPUÉS:`,
                                                inasistencias,
                                            );
                                            console.log(
                                                `🔢 Total de inasistencias: ${inasistencias.length}`,
                                            );
                                        }}
                                        class="appearance-none border rounded-lg px-3 py-2 pr-8 text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 cursor-pointer transition-colors"
                                        style="background-color: {getThemeStyles()
                                            .bg}; border-color: {getThemeStyles()
                                            .border}; color: {getThemeStyles()
                                            .text};"
                                    >
                                        <option
                                            value=""
                                            style="color: {getThemeStyles()
                                                .text};">Motivo...</option
                                        >
                                        {#each motivos as motivo}
                                            <option
                                                value={motivo.value}
                                                style="color: {getThemeStyles()
                                                    .text};"
                                                >{motivo.icon}
                                                {motivo.label}</option
                                            >
                                        {/each}
                                    </select>
                                    <!-- Custom dropdown arrow -->
                                    <div
                                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3"
                                        style="color: {getThemeStyles().icon};"
                                    >
                                        <svg
                                            class="w-4 h-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 9l-7 7-7-7"
                                            />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        {/each}
                    </div>
                </div>
            {/if}

            <!-- Fecha -->
            <div>
                <label
                    for="fecha"
                    class="block text-sm font-medium tracking-wide mb-2"
                    style="color: {getThemeStyles().label};"
                >
                    Fecha de Inasistencia/Registro
                </label>
                <input
                    type="date"
                    id="fecha"
                    name="fecha"
                    bind:value={formData.fecha}
                    on:change={handleChange}
                    required
                    class="w-full px-3 py-2.5 text-base border rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                    style="background-color: {getThemeStyles()
                        .bg}; border-color: {getThemeStyles()
                        .border}; color: {getThemeStyles().text};"
                />
            </div>

            <!-- Observaciones -->
            <div>
                <label
                    for="observaciones"
                    class="block text-sm font-medium tracking-wide mb-2"
                    style="color: {getThemeStyles().label};"
                >
                    Observaciones
                </label>
                <!-- svelte-ignore element_invalid_self_closing_tag -->
                <textarea
                    id="observaciones"
                    name="observaciones"
                    bind:value={formData.observaciones}
                    on:change={handleChange}
                    rows={3}
                    placeholder="Añadir observaciones opcionales..."
                    class="w-full px-3 py-2.5 text-base border rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 resize-none transition-colors"
                    style="background-color: {getThemeStyles()
                        .bg}; border-color: {getThemeStyles()
                        .border}; color: {getThemeStyles()
                        .text}; placeholder-color: {getThemeStyles()
                        .placeholder};"
                />
            </div>

            <!-- Submit area -->
            <div
                class="flex items-center justify-between pt-4 mt-6 border-t border-zinc-200 dark:border-zinc-700"
            >
                <div class="text-sm text-zinc-600 dark:text-zinc-400">
                    Total de inasistencias:
                    <span class="font-semibold text-zinc-900 dark:text-zinc-100"
                        >{inasistencias.length}</span
                    >
                </div>

                <button
                    type="submit"
                    disabled={isLoading || inasistencias.length === 0}
                    class="active:scale-[0.98] bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 px-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-150 flex items-center font-medium"
                >
                    {#if isLoading}
                        <svg
                            class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
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
                        Registrando...
                    {:else}
                        <svg
                            class="-ml-1 mr-2 h-4 w-4"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        Registrar {inasistencias.length}
                        {inasistencias.length === 1
                            ? "Inasistencia"
                            : "Inasistencias"}
                    {/if}
                </button>
            </div>
        </form>

        {#if message}
            <div
                class={`mt-6 p-4 rounded-lg text-sm font-medium ${
                    message.includes("exitosamente")
                        ? "text-emerald-800 border border-emerald-200"
                        : "text-red-800 border border-red-200"
                }`}
                style={`background-color: ${
                    message.includes("exitosamente")
                        ? isDarkMode
                            ? "rgb(6, 78, 59)"
                            : "rgb(236, 253, 245)"
                        : isDarkMode
                          ? "rgb(127, 29, 29)"
                          : "rgb(254, 242, 242)"
                };
        border-color: ${
            message.includes("exitosamente")
                ? isDarkMode
                    ? "rgb(34, 197, 94)"
                    : "rgb(167, 243, 208)"
                : isDarkMode
                  ? "rgb(248, 113, 113)"
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
        };`}
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
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .animate-fade-in {
        animation: fade-in 0.2s ease-out forwards;
    }
</style>
