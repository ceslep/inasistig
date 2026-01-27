<script lang="ts">
  import { onMount, onDestroy } from "svelte";
  import Swal from "sweetalert2";
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
    horas: string;
    observaciones?: string;
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
  let individualHours: Record<string, string> = {}; // Almacena horas individuales antes de marcar motivo
  let isLoading = false;
  let message = "";

  // --- Funciones auxiliares ---
  const getSheetsUrl = () => {
    return `https://docs.google.com/spreadsheets/d/${SPREADSHEET_ID}`;
  };

  const openSheets = () => {
    window.open(getSheetsUrl(), "_blank");
  };

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
      value: "Transporte Escolar",
      label: "Transporte Escolar",
      icon: "🚌",
      bgColor: "bg-red-50",
      borderColor: "border-red-300",
      textColor: "text-red-700",
      darkBgColor: "dark:bg-red-950",
      darkBorderColor: "dark:border-red-800",
      darkTextColor: "dark:text-red-300",
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
      value: "No portar/sin uniforme",
      label: "No portar/sin uniforme",
      icon: "👚",
      bgColor: "bg-cyan-50",
      borderColor: "border-cyan-300",
      textColor: "text-cyan-700",
      darkBgColor: "dark:bg-cyan-950",
      darkBorderColor: "dark:border-cyan-800",
      darkTextColor: "dark:text-cyan-300",
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
    {
      value: "Psicoorientación",
      label: "Psicoorientación",
      icon: "🧠",
      bgColor: "bg-green-50",
      borderColor: "border-green-300",
      textColor: "text-green-700",
      darkBgColor: "dark:bg-green-950",
      darkBorderColor: "dark:border-green-800",
      darkTextColor: "dark:text-green-300",
    },
  ];

  // --- Funciones de Tema ---
  const applyTheme = (theme: "light" | "dark" | "system") => {
    currentTheme = theme;
    if (theme === "light") isDarkMode = false;
    else if (theme === "dark") isDarkMode = true;
    else isDarkMode = window.matchMedia("(prefers-color-scheme: dark)").matches;

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
      const [docentesData, materiasData, estudiantesData] = await Promise.all([
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
  const handleInasistenciaChange = (
    estudianteNombre: string,
    nuevoMotivo: string,
    nuevaHora?: string,
  ) => {
    const index = inasistencias.findIndex((i) => i.nombre === estudianteNombre);
    const hourToUse =
      nuevaHora || individualHours[estudianteNombre] || formData.horas;

    if (nuevoMotivo === "" || nuevoMotivo === "Ignorar") {
      if (index >= 0) {
        inasistencias.splice(index, 1);
        inasistencias = inasistencias;
      }
    } else {
      if (index >= 0) {
        inasistencias[index].motivo = nuevoMotivo;
        inasistencias[index].horas = hourToUse;
      } else {
        inasistencias.push({
          nombre: estudianteNombre,
          motivo: nuevoMotivo,
          horas: hourToUse,
        });
      }
      inasistencias = inasistencias;
    }
  };

  const handleIndividualHourChange = (
    estudianteNombre: string,
    nuevaHora: string,
  ) => {
    individualHours[estudianteNombre] = nuevaHora;
    const index = inasistencias.findIndex((i) => i.nombre === estudianteNombre);
    if (index >= 0) {
      inasistencias[index].horas = nuevaHora;
      inasistencias = inasistencias;
    }
  };

  const handleSubmit = async (event: Event) => {
    event.preventDefault();
    if (isLoading || inasistencias.length === 0) return;

    isLoading = true;
    try {
      const currentTimestamp = new Date().toISOString();
      const inasistenciasPayload = inasistencias.map((item) => [
        currentTimestamp,
        formData.docente,
        formData.fecha,
        item.horas,
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

      await Swal.fire({
        icon: "success",
        title: "¡Éxito!",
        text: `${inasistencias.length} inasistencia(s) registrada(s) exitosamente`,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
        position: "top-end",
        toast: true,
      });

      formData = {
        ...formData,
        grado: "",
        fecha: new Date().toISOString().split("T")[0],
        observaciones: "",
        horas: "",
      };
      inasistencias = [];
      individualHours = {};
    } catch (error) {
      console.error("Error enviando:", error);
      await Swal.fire({
        icon: "error",
        title: "Error",
        text: "Error al registrar la inasistencia",
        confirmButtonColor: "#ef4444",
      });
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
  class="min-h-screen flex flex-col lg:flex-row transition-colors duration-200"
  style="background-color: {isDarkMode
    ? 'rgb(9, 9, 11)'
    : 'rgb(250, 250, 250)'};"
>
  <!-- Sidebar (Escritorio) / Header (Móvil) -->
  <aside
    class="w-full lg:w-80 lg:h-screen lg:sticky lg:top-0 border-b lg:border-b-0 lg:border-r transition-colors duration-200 p-6 lg:p-8 flex flex-col flex-shrink-0 z-40"
    style="background-color: {styles.cardBg}; border-color: {styles.cardBorder};"
  >
    <div
      class="flex flex-row lg:flex-col items-center justify-between lg:justify-start gap-4 lg:gap-8"
    >
      <div class="flex items-center gap-4 lg:flex-col">
        <img src={eieLogo} alt="EIE Logo" class="h-12 lg:h-20 w-auto" />
        <h1
          class="text-xl lg:text-2xl tracking-tight font-bold lg:text-center"
          style="color: {styles.text};"
        >
          Registrar Inasistencias
        </h1>
      </div>

      <!-- Botones de Acción -->
      <div class="flex flex-row lg:flex-col gap-3 w-auto lg:w-full">
        <button
          on:click={openSheets}
          class="inline-flex items-center justify-center gap-2 px-3 lg:px-4 py-2 lg:py-3 border rounded-lg transition-all duration-200 hover:bg-black/5 dark:hover:bg-white/5"
          style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
          title="Abrir hoja de cálculo"
        >
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
            <path
              d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96z"
            />
          </svg>
          <span class="text-sm font-medium hidden lg:inline"
            >Hoja de Cálculo</span
          >
        </button>

        <div class="relative theme-dropdown">
          <button
            on:click={toggleThemeOptions}
            class="inline-flex items-center justify-center gap-2 px-3 lg:px-4 py-2 lg:py-3 border rounded-lg transition-all duration-200 hover:bg-black/5 dark:hover:bg-white/5 w-full"
            style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
            aria-label="Cambiar tema"
          >
            {#if currentTheme === "dark"}
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
                  d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
                ></path>
              </svg>
            {:else if currentTheme === "light"}
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
                  d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
                ></path>
              </svg>
            {:else}
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
                  d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                ></path>
              </svg>
            {/if}
            <span class="text-sm font-medium hidden lg:inline">
              {currentTheme === "system"
                ? "Sistema"
                : currentTheme === "light"
                  ? "Claro"
                  : "Oscuro"}
            </span>
          </button>

          {#if showThemeOptions}
            <div
              class="absolute top-full lg:top-auto lg:bottom-full mb-2 lg:mb-0 lg:mt-2 right-0 lg:left-0 lg:right-auto border rounded-lg shadow-xl p-2 min-w-[140px] animate-fade-in z-50 transition-colors duration-200"
              style="background-color: {styles.cardBg}; border-color: {styles.border};"
            >
              {#each ["light", "dark", "system"] as themeOption}
                <button
                  on:click={() => setTheme(themeOption as any)}
                  class="w-full flex items-center px-3 py-2 text-sm rounded-md transition-colors hover:bg-black/5 dark:hover:bg-white/5 text-left"
                  style="color: {styles.text};"
                >
                  <span class="capitalize">
                    {themeOption === "system"
                      ? "🖥️ Sistema"
                      : themeOption === "light"
                        ? "☀️ Claro"
                        : "🌙 Oscuro"}
                  </span>
                </button>
              {/each}
            </div>
          {/if}
        </div>
      </div>
    </div>
  </aside>

  <!-- Contenido Principal -->
  <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
    <div
      class="max-w-4xl mx-auto rounded-2xl p-6 lg:p-8 transition-colors duration-200 border"
      style="background-color: {styles.cardBg}; border-color: {styles.cardBorder};"
    >
      <form on:submit={handleSubmit} class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-2">
            <label
              for="docente"
              class="block text-sm font-medium"
              style="color: {styles.label};">Docente</label
            >
            <select
              id="docente"
              name="docente"
              bind:value={formData.docente}
              required
              disabled={isLoadingDocentes}
              class="w-full px-4 py-3 rounded-xl border focus:ring-2 focus:ring-indigo-500 transition-all outline-none appearance-none cursor-pointer disabled:opacity-50"
              style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
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

          <div class="space-y-2">
            <label
              for="materia"
              class="block text-sm font-medium"
              style="color: {styles.label};">Materia</label
            >
            <select
              id="materia"
              name="materia"
              bind:value={formData.materia}
              required
              disabled={isLoadingMaterias}
              class="w-full px-4 py-3 rounded-xl border focus:ring-2 focus:ring-indigo-500 transition-all outline-none appearance-none cursor-pointer disabled:opacity-50"
              style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
            >
              <option value=""
                >{isLoadingMaterias
                  ? "Cargando..."
                  : "Seleccione materia"}</option
              >
              {#each materias as materia}
                <option value={materia.materia}>{materia.materia}</option>
              {/each}
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-2">
            <label
              for="horas"
              class="block text-sm font-medium"
              style="color: {styles.label};">Cantidad de Horas</label
            >
            <select
              id="horas"
              name="horas"
              bind:value={formData.horas}
              required
              class="w-full px-4 py-3 rounded-xl border focus:ring-2 focus:ring-indigo-500 transition-all outline-none appearance-none cursor-pointer"
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

          <div class="space-y-2">
            <label
              for="grado"
              class="block text-sm font-medium"
              style="color: {styles.label};">Grado</label
            >
            <select
              id="grado"
              name="grado"
              bind:value={formData.grado}
              required
              disabled={isLoadingEstudiantes}
              class="w-full px-4 py-3 rounded-xl border focus:ring-2 focus:ring-indigo-500 transition-all outline-none appearance-none cursor-pointer disabled:opacity-50"
              style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
            >
              <option value=""
                >{isLoadingEstudiantes
                  ? "Cargando..."
                  : "Seleccione grado"}</option
              >
              {#each [...new Set(estudiantes.map( (e) => e.grado.toString(), ))] as g}
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
          <div class="space-y-3 animate-fade-in">
            <!-- svelte-ignore a11y_label_has_associated_control -->
            <label
              class="block text-sm font-semibold uppercase tracking-wider"
              style="color: {styles.label};"
            >
              Estudiantes del Grado {formData.grado}
            </label>
            <div
              class="space-y-2 max-h-[400px] overflow-y-auto border rounded-2xl p-4 bg-zinc-50/50 dark:bg-zinc-900/50 backdrop-blur-sm scrollbar-thin scrollbar-thumb-zinc-300 dark:scrollbar-thumb-zinc-600"
              style="border-color: {styles.border};"
            >
              {#each estudiantesFiltrados as estudiante (estudiante.nombre)}
                {@const currentInasistencia = inasistencias.find(
                  (i) => i.nombre === estudiante.nombre,
                )}
                {@const motivoSeleccionado = currentInasistencia
                  ? motivos.find((m) => m.value === currentInasistencia.motivo)
                  : null}

                <div
                  class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border rounded-xl transition-all duration-200 gap-4"
                  style="background-color: {styles.bg}; border-color: {motivoSeleccionado
                    ? 'transparent'
                    : styles.border};"
                  class:ring-2={motivoSeleccionado}
                  class:ring-indigo-500={motivoSeleccionado}
                >
                  <div class="flex-1 min-w-0">
                    <span
                      class="text-base font-medium block"
                      style="color: {styles.text};">{estudiante.nombre}</span
                    >
                    {#if motivoSeleccionado}
                      <span
                        class={`inline-flex items-center mt-2 px-2.5 py-0.5 rounded-full text-xs font-semibold border ${motivoSeleccionado.bgColor} ${motivoSeleccionado.textColor} ${motivoSeleccionado.borderColor} ${motivoSeleccionado.darkBgColor} ${motivoSeleccionado.darkTextColor} ${motivoSeleccionado.darkBorderColor}`}
                      >
                        <span class="mr-1.5">{motivoSeleccionado.icon}</span>
                        {motivoSeleccionado.label}
                      </span>
                    {/if}
                  </div>

                  <div
                    class="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto"
                  >
                    <!-- Selector de Hora Individual -->
                    <div class="relative w-full sm:w-32">
                      <select
                        value={currentInasistencia?.horas ||
                          individualHours[estudiante.nombre] ||
                          formData.horas}
                        on:change={(e) =>
                          handleIndividualHourChange(
                            estudiante.nombre,
                            e.currentTarget.value,
                          )}
                        class="w-full appearance-none border rounded-lg px-4 py-2 pr-10 text-sm focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all outline-none"
                        style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
                      >
                        <option value="">Hora...</option>
                        <option value="0">0 (Sin hora)</option>
                        {#each [1, 2, 3, 4] as h}
                          <option value={h.toString()}>{h}</option>
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
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                          />
                        </svg>
                      </div>
                    </div>

                    <!-- Selector de Motivo -->
                    <div class="relative w-full sm:w-56">
                      <select
                        value={currentInasistencia?.motivo || ""}
                        on:change={(e) =>
                          handleInasistenciaChange(
                            estudiante.nombre,
                            e.currentTarget.value,
                          )}
                        class="w-full appearance-none border rounded-lg px-4 py-2 pr-10 text-sm focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all outline-none"
                        style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
                      >
                        <option value="">Seleccionar motivo...</option>
                        {#each motivos as motivo}
                          <option value={motivo.value}
                            >{motivo.icon} {motivo.label}</option
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
                </div>
              {/each}

              {#if estudiantesFiltrados.length === 0}
                <div
                  class="text-center py-12 opacity-60"
                  style="color: {styles.text};"
                >
                  No hay estudiantes en este grado.
                </div>
              {/if}
            </div>
          </div>
        {/if}

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-2">
            <label
              for="fecha"
              class="block text-sm font-medium"
              style="color: {styles.label};">Fecha</label
            >
            <input
              type="date"
              id="fecha"
              name="fecha"
              bind:value={formData.fecha}
              required
              class="w-full px-4 py-3 rounded-xl border focus:ring-2 focus:ring-indigo-500 transition-all outline-none"
              style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
            />
          </div>

          <div class="space-y-2">
            <label
              for="observaciones"
              class="block text-sm font-medium"
              style="color: {styles.label};">Observaciones</label
            >
            <textarea
              id="observaciones"
              name="observaciones"
              bind:value={formData.observaciones}
              rows="3"
              placeholder="Opcional..."
              class="w-full px-4 py-3 rounded-xl border focus:ring-2 focus:ring-indigo-500 transition-all outline-none resize-none"
              style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
            />
          </div>
        </div>

        <div
          class="flex items-center justify-between pt-6 border-t"
          style="border-color: {styles.border};"
        >
          <div class="text-sm font-medium" style="color: {styles.label};">
            Inasistencias marcadas: <span class="text-indigo-500 font-bold ml-1"
              >{inasistencias.length}</span
            >
          </div>
        </div>
      </form>
    </div>
  </main>

  <button
    on:click={handleSubmit}
    disabled={isLoading ||
      inasistencias.length === 0 ||
      !formData.docente ||
      !formData.materia}
    class="fixed bottom-6 right-6 lg:bottom-10 lg:right-10 bg-green-600 hover:bg-green-700 text-white p-4 lg:p-5 rounded-full shadow-2xl hover:shadow-green-500/20 transform transition-all duration-300 hover:scale-110 active:scale-95 disabled:opacity-50 disabled:grayscale disabled:cursor-not-allowed z-50 group"
    title="Guardar inasistencias"
  >
    {#if isLoading}
      <svg
        class="animate-spin h-6 w-6 lg:h-7 lg:w-7"
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
    {:else}
      <svg
        class="h-6 w-6 lg:h-7 lg:w-7"
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

    {#if !isLoading && inasistencias.length > 0}
      <span
        class="absolute -top-1 -right-1 lg:-top-2 lg:-right-2 bg-red-500 text-white text-[10px] lg:text-xs font-bold rounded-full h-5 w-5 lg:h-6 lg:w-6 flex items-center justify-center ring-2 ring-white dark:ring-zinc-950 animate-bounce"
      >
        {inasistencias.length}
      </span>
    {/if}
  </button>
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
