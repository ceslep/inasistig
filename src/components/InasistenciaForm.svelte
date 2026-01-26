<script lang="ts">
import { saveInasistencias, getDocentes, getMaterias, getEstudiantes } from "../../api/service";
  import {
    SPREADSHEET_ID,
  } from "../constants";

  import eieLogo from "../assets/eie.png";

  let docentes: string[] = [];
  let materias: Array<{ materia: string }> = [];
  let estudiantes: Array<{ nombre: string; grado: number }> = [];
  let isLoadingDocentes = false;
  let isLoadingMaterias = false;
  let isLoadingEstudiantes = false;

  let formData = {
    docente: "",
    materia: "",
    horas: "",
    grado: "",
    fecha: new Date().toISOString().split("T")[0],
    observaciones: "",
  };

  let inasistencias: Array<{ nombre: string; motivo: string }> = [];

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
  ];

let isLoading = false;
  let message = "";
  let isDarkMode = true;
  let showThemeOptions = false;

  // Function to toggle theme
  const toggleTheme = () => {
    isDarkMode = !isDarkMode;
    if (isDarkMode) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
    localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
    showThemeOptions = false;
  };

  // Function to set specific theme
  const setTheme = (theme: 'light' | 'dark' | 'system') => {
    console.log('Setting theme to:', theme); // Debug log
    
    if (theme === 'dark') {
      isDarkMode = true;
      document.documentElement.classList.add('dark');
      document.documentElement.setAttribute('data-theme', 'dark');
      console.log('Classes after setting dark:', document.documentElement.classList.toString());
    } else if (theme === 'light') {
      isDarkMode = false;
      document.documentElement.classList.remove('dark');
      document.documentElement.removeAttribute('data-theme');
      console.log('Classes after setting light:', document.documentElement.classList.toString());
    } else if (theme === 'system') {
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      isDarkMode = prefersDark;
      if (prefersDark) {
        document.documentElement.classList.add('dark');
        document.documentElement.setAttribute('data-theme', 'dark');
      } else {
        document.documentElement.classList.remove('dark');
        document.documentElement.removeAttribute('data-theme');
      }
      console.log('Classes after setting system:', document.documentElement.classList.toString());
    }
    localStorage.setItem('theme', theme);
    showThemeOptions = false;
  };

  // Function to toggle theme options dropdown
  const toggleThemeOptions = () => {
    showThemeOptions = !showThemeOptions;
  };

  // Set initial theme on mount
  const initializeTheme = () => {
    const savedTheme = localStorage.getItem('theme') || 'dark'; // Default to dark
    console.log('Initializing with theme:', savedTheme); // Debug log
    
    if (savedTheme === 'light') {
      isDarkMode = false;
      document.documentElement.classList.remove('dark');
      document.documentElement.removeAttribute('data-theme');
    } else if (savedTheme === 'system') {
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      isDarkMode = prefersDark;
      if (prefersDark) {
        document.documentElement.classList.add('dark');
        document.documentElement.setAttribute('data-theme', 'dark');
      } else {
        document.documentElement.classList.remove('dark');
        document.documentElement.removeAttribute('data-theme');
      }
    } else {
      isDarkMode = true;
      document.documentElement.classList.add('dark');
      document.documentElement.setAttribute('data-theme', 'dark');
    }
  };

  // Initialize after DOM is ready
  if (typeof document !== 'undefined') {
    // Wait for next tick to ensure DOM is ready
    setTimeout(() => {
      initializeTheme();
      console.log('Final HTML classes:', document.documentElement.classList.toString());
    }, 0);
  }

  // Close theme options when clicking outside
  const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.theme-dropdown')) {
      showThemeOptions = false;
    }
  };

  // Add event listener for outside clicks
  if (typeof window !== 'undefined') {
    document.addEventListener('click', handleClickOutside);
  }

// Cargar docentes al montar el componente
  const loadDocentes = async () => {
    isLoadingDocentes = true;
    try {
      const data = await getDocentes();
      docentes = data;
    } catch (error) {
      console.error("Error cargando docentes:", error);
    } finally {
      isLoadingDocentes = false;
    }
  };

// Cargar materias al montar el componente
  const loadMaterias = async () => {
    isLoadingMaterias = true;
    try {
      const data = await getMaterias();
      materias = data;
    } catch (error) {
      console.error("Error cargando materias:", error);
    } finally {
      isLoadingMaterias = false;
    }
  };

  loadDocentes();
  loadMaterias();

// Cargar estudiantes al montar el componente
  const loadEstudiantes = async () => {
    isLoadingEstudiantes = true;
    try {
      const data = await getEstudiantes();
      estudiantes = data;
    } catch (error) {
      console.error("Error cargando estudiantes:", error);
    } finally {
      isLoadingEstudiantes = false;
    }
  };

  loadDocentes();
  loadMaterias();
  loadEstudiantes();

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
    isLoading = true;
    message = "";

    try {
      // Importar el SPREADSHEET_ID desde constants
      const { SPREADSHEET_ID } = await import("../constants");

      // Preparar el payload con la estructura de 9 columnas
      const currentTimestamp = new Date().toISOString();
      const inasistenciasPayload = inasistencias.map((item) => [
        currentTimestamp, // 0. Marca temporal
        formData.docente, // 1. Docente
        formData.fecha, // 2. Fecha
        formData.horas === "0"
          ? "Sin hora específica"
          : `${formData.horas} horas`, // 3. Horas de Inasistencia
        formData.materia, // 4. Asignatura
        item.motivo, // 5. Tipo de registro
        formData.grado, // 6. Grupo (Grado)
        formData.observaciones, // 7. Observaciones
        item.nombre, // 8. Estudiante
      ]);

      const payload = {
        spreadsheetId: SPREADSHEET_ID,
        worksheetTitle: new Date().toISOString().slice(0, 7), // YYYY-MM
        inasistencias: inasistenciasPayload,
      };

      await saveInasistencias(payload);
      message = `${inasistencias.length} inasistencia(s) registrada(s) exitosamente`;
      formData = {
        docente: "",
        materia: "",
        horas: "",
        grado: "",
        fecha: new Date().toISOString().split("T")[0],
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
</script>

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-950 py-6 px-4 sm:py-8 transition-colors duration-200">
  <!-- Theme dropdown container -->
  <div class="theme-dropdown fixed bottom-6 right-6 z-50">
    <!-- Theme options dropdown -->
    {#if showThemeOptions}
      <div class="absolute bottom-16 right-0 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-xl p-2 min-w-[160px] mb-2">
        <!-- Light mode option -->
        <button
          on:click|preventDefault={() => setTheme('light')}
          on:click={() => {
            console.log('Light mode clicked'); // Debug log
            setTheme('light');
          }}
          class="w-full flex items-center px-3 py-2 text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-md transition-colors"
        >
          <svg class="w-4 h-4 mr-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
          Modo Claro
        </button>
        
        <!-- Dark mode option -->
        <button
          on:click={() => {
            console.log('Dark mode clicked'); // Debug log
            setTheme('dark');
          }}
          class="w-full flex items-center px-3 py-2 text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-md transition-colors"
        >
          <svg class="w-4 h-4 mr-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
          </svg>
          Modo Oscuro
        </button>
        
        <!-- System mode option -->
        <button
          on:click={() => {
            console.log('System mode clicked'); // Debug log
            setTheme('system');
          }}
          class="w-full flex items-center px-3 py-2 text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-md transition-colors"
        >
          <svg class="w-4 h-4 mr-3 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
          Sistema
        </button>
      </div>
    {/if}

    <!-- Main theme toggle button -->
    <button
      on:click={toggleThemeOptions}
      class="w-12 h-12 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-full shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center group active:scale-95"
      aria-label="Theme options"
    >
      {#if isDarkMode}
        <!-- Sun icon for light mode -->
        <svg class="w-5 h-5 text-zinc-600 dark:text-zinc-300 group-hover:text-amber-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
      {:else}
        <!-- Moon icon for dark mode -->
        <svg class="w-5 h-5 text-zinc-600 group-hover:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>
      {/if}
    </button>
  </div>

  <div class="w-full max-w-2xl mx-auto bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 sm:p-8 transition-colors duration-200">
    <div class="flex justify-center mb-8">
      <img src={eieLogo} alt="EIE Logo" class="h-16 w-auto" />
    </div>

    <h1 class="text-2xl sm:text-3xl tracking-tight font-bold text-center text-zinc-900 dark:text-zinc-100 mb-8">
      Registrar Inasistencia
    </h1>

    <form on:submit={handleSubmit} class="space-y-6">
<!-- Modern input with Shadcn-style focus states -->
      <div class="relative">
        <label
          for="docente"
          class="block text-sm font-medium tracking-wide text-zinc-700 dark:text-zinc-300 mb-2"
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
          class="w-full px-3 py-2.5 text-base bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-900 disabled:opacity-50 transition-colors appearance-none cursor-pointer text-zinc-900 dark:text-zinc-100"
        >
          <option value="" class="text-zinc-900 dark:text-zinc-100">
            {isLoadingDocentes ? "Cargando..." : "Seleccione un docente"}
          </option>
          {#each docentes as docente}
            <option value={docente} class="text-zinc-900 dark:text-zinc-100">{docente}</option>
          {/each}
        </select>
        <!-- Custom dropdown arrow -->
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-zinc-400 dark:text-zinc-500">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </div>
      </div>

<div class="relative">
        <label
          for="materia"
          class="block text-sm font-medium tracking-wide text-zinc-700 dark:text-zinc-300 mb-2"
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
          class="w-full px-3 py-2.5 text-base bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-900 disabled:opacity-50 transition-colors appearance-none cursor-pointer text-zinc-900 dark:text-zinc-100"
        >
          <option value="" class="text-zinc-900 dark:text-zinc-100">
            {isLoadingMaterias ? "Cargando..." : "Seleccione una materia"}
          </option>
          {#each materias as materia}
            <option value={materia.materia} class="text-zinc-900 dark:text-zinc-100">{materia.materia}</option>
          {/each}
        </select>
        <!-- Custom dropdown arrow -->
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-zinc-400 dark:text-zinc-500">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </div>
      </div>

<div class="relative">
        <label for="horas" class="block text-sm font-medium tracking-wide text-zinc-700 dark:text-zinc-300 mb-2">
          Cantidad de Horas
        </label>
        <select
          id="horas"
          name="horas"
          bind:value={formData.horas}
          on:change={handleChange}
          required
          class="w-full px-3 py-2.5 text-base bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-900 transition-colors appearance-none cursor-pointer text-zinc-900 dark:text-zinc-100"
        >
          <option value="" class="text-zinc-900 dark:text-zinc-100">Seleccione horas</option>
          <option value="0" class="text-zinc-900 dark:text-zinc-100">Sin hora específica</option>
          <option value="1" class="text-zinc-900 dark:text-zinc-100">1 Hora</option>
          <option value="2" class="text-zinc-900 dark:text-zinc-100">2 Horas</option>
          <option value="3" class="text-zinc-900 dark:text-zinc-100">3 Horas</option>
          <option value="4" class="text-zinc-900 dark:text-zinc-100">4 Horas</option>
        </select>
        <!-- Custom dropdown arrow -->
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-zinc-400 dark:text-zinc-500">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </div>
      </div>

<div class="relative">
        <label for="grado" class="block text-sm font-medium tracking-wide text-zinc-700 dark:text-zinc-300 mb-2">
          Grado
        </label>
        <select
          id="grado"
          name="grado"
          bind:value={formData.grado}
          on:change={handleChange}
          required
          disabled={isLoadingEstudiantes}
          class="w-full px-3 py-2.5 text-base bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-900 disabled:opacity-50 transition-colors appearance-none cursor-pointer text-zinc-900 dark:text-zinc-100"
        >
          <option value="" class="text-zinc-900 dark:text-zinc-100">
            {isLoadingEstudiantes ? "Cargando..." : "Seleccione un grado"}
          </option>
          <option value="601" class="text-zinc-900 dark:text-zinc-100">6°1</option>
          <option value="602" class="text-zinc-900 dark:text-zinc-100">6°2</option>
          <option value="701" class="text-zinc-900 dark:text-zinc-100">7°1</option>
          <option value="702" class="text-zinc-900 dark:text-zinc-100">7°2</option>
          <option value="801" class="text-zinc-900 dark:text-zinc-100">8°1</option>
          <option value="802" class="text-zinc-900 dark:text-zinc-100">8°2</option>
          <option value="901" class="text-zinc-900 dark:text-zinc-100">9°1</option>
          <option value="902" class="text-zinc-900 dark:text-zinc-100">9°2</option>
          <option value="1001" class="text-zinc-900 dark:text-zinc-100">10°1</option>
          <option value="1101" class="text-zinc-900 dark:text-zinc-100">11°1</option>
          <option value="1102" class="text-zinc-900 dark:text-zinc-100">11°2</option>
        </select>
        <!-- Custom dropdown arrow -->
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-zinc-400 dark:text-zinc-500">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </div>
      </div>

{#if formData.grado && formData.horas !== ""}
        <div>
          <!-- Modern student list section -->
          <!-- svelte-ignore a11y_label_has_associated_control -->
          <label class="block text-sm font-medium tracking-wide text-zinc-700 dark:text-zinc-300 mb-3">
            Estudiantes del Grado {formData.grado} ({formData.horas === "0"
              ? "Sin hora específica"
              : formData.horas +
                (parseInt(formData.horas) === 1 ? " hora" : " horas")})
          </label>
          <!-- Bento-style card container -->
          <div
            class="space-y-2 max-h-72 overflow-y-auto border border-zinc-200 dark:border-zinc-700 rounded-xl p-3 bg-zinc-50/50 dark:bg-zinc-900/50 backdrop-blur-sm"
          >
{#each estudiantes.filter((e) => e.grado.toString() === formData.grado) as estudiante}
              {@const currentInasistencia = inasistencias.find(
                (item) => item.nombre === estudiante.nombre,
              )}
              {@const motivoSeleccionado = currentInasistencia?.motivo
                ? motivos.find((m) => m.value === currentInasistencia.motivo)
                : null}

              <!-- Modern student row card -->
              <div
                class="flex items-center justify-between p-3 bg-white dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-all duration-150"
              >
                <div class="flex-1">
                  <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100"
                    >{estudiante.nombre}</span
                  >
                  {#if motivoSeleccionado}
                    <div class="mt-2">
                      <!-- Modern badge with opacity and subtle borders -->
                      <span
                        class={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${motivoSeleccionado.bgColor} ${motivoSeleccionado.textColor} ${motivoSeleccionado.borderColor} border ${motivoSeleccionado.darkBgColor} ${motivoSeleccionado.darkTextColor} ${motivoSeleccionado.darkBorderColor}`}
                      >
                        <span class="mr-1">{motivoSeleccionado.icon}</span>
                        {motivoSeleccionado.label}
                      </span>
                    </div>
                  {/if}
                </div>

                <div class="relative">
                  <select
                    value={currentInasistencia?.motivo || ""}
                    on:change={(e) => {
                      const motivoValue = (e.target as HTMLSelectElement).value;
                      const existingIndex = inasistencias.findIndex(
                        (item) => item.nombre === estudiante.nombre,
                      );
                      if (existingIndex >= 0) {
                        if (motivoValue) {
                          inasistencias[existingIndex].motivo = motivoValue;
                        } else {
                          inasistencias.splice(existingIndex, 1);
                        }
                      } else if (motivoValue) {
                        inasistencias.push({
                          nombre: estudiante.nombre,
                          motivo: motivoValue,
                        });
                      }
                    }}
                    class="appearance-none bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-600 rounded-lg px-3 py-2 pr-8 text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-900 cursor-pointer hover:border-zinc-300 dark:hover:border-zinc-500 transition-colors text-zinc-900 dark:text-zinc-100"
                  >
                    <option value="" class="text-zinc-900 dark:text-zinc-100">Motivo...</option>
                    {#each motivos as motivo}
                      <option value={motivo.value} class="text-zinc-900 dark:text-zinc-100"
                        >{motivo.icon} {motivo.label}</option
                      >
                    {/each}
                  </select>
                  <!-- Modern dropdown arrow -->
                  <div
                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-zinc-400 dark:text-zinc-500"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </div>
                </div>
              </div>
            {/each}
          </div>
        </div>
      {/if}

<!-- Modern date input -->
      <div>
        <label for="fecha" class="block text-sm font-medium tracking-wide text-zinc-700 dark:text-zinc-300 mb-2">
          Fecha de Inasistencia/Registro
        </label>
        <input
          type="date"
          id="fecha"
          name="fecha"
          bind:value={formData.fecha}
          on:change={handleChange}
          required
          class="w-full px-3 py-2.5 text-base bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-900 transition-colors text-zinc-900 dark:text-zinc-100"
        />
      </div>

      <!-- Modern textarea -->
      <div>
        <label
          for="observaciones"
          class="block text-sm font-medium tracking-wide text-zinc-700 dark:text-zinc-300 mb-2"
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
          class="w-full px-3 py-2.5 text-base bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-900 resize-none transition-colors placeholder:text-zinc-400 dark:placeholder:text-zinc-500 text-zinc-900 dark:text-zinc-100"
        />
      </div>

      <!-- Modern sticky submit area -->
      <div class="flex items-center justify-between pt-4 mt-6 border-t border-zinc-200 dark:border-zinc-700">
        <div class="text-sm text-zinc-600 dark:text-zinc-400">
          Total de inasistencias:
          <span class="font-semibold text-zinc-900 dark:text-zinc-100">{inasistencias.length}</span>
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
            {inasistencias.length === 1 ? "Inasistencia" : "Inasistencias"}
          {/if}
        </button>
      </div>
    </form>

{#if message}
      <!-- Modern alert with proper dark mode support -->
      <div
        class={`mt-6 p-4 rounded-lg text-sm font-medium ${message.includes("exitosamente") 
          ? "bg-emerald-50 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-800" 
          : "bg-red-50 dark:bg-red-950 text-red-800 dark:text-red-200 border border-red-200 dark:border-red-800"}`}
      >
        {message}
      </div>
    {/if}
  </div>
</div>
