<script lang="ts">
  import { onMount } from "svelte";
  import Swal from "sweetalert2";
  import {
    getDocentes,
    getMaterias,
    getEstudiantes,
    getOpcionesAnotador2 as getOpcionesAnotador,
    saveAnotador,
  } from "../../api/service";
  import {
    SPREADSHEET_ID_ANOTADOR,
    WORKSHEET_TITLE_ANOTADOR,
    INFO_ANOTADOR,
  } from "../constants";
  import Loader from "./Loader.svelte";
  import AnotadorFilter from "./AnotadorFilter.svelte";
  import { theme } from "../lib/themeStore";
  import eieLogo from "../assets/eie.png";

  export let onBack: () => void;

  // --- Interfaces ---
  interface Estudiante {
    nombre: string;
    grado: number | string;
  }

  interface Materia {
    materia: string;
  }

  interface OpcionAnotacion {
    text: string;
    selected: boolean;
  }

  // --- Estado de datos ---
  let docentes: string[] = [];
  let materias: Materia[] = [];
  let estudiantes: Estudiante[] = [];

  let isLoadingDocentes = false;
  let isLoadingMaterias = false;
  let isLoadingEstudiantes = false;
  let isLoadingOpciones = false;

  let anotacionGrupos: Record<string, OpcionAnotacion[]> = {};

  const getCategoryColor = (cat: string) => {
    const colors: Record<string, string> = {
      "Estrategias de Enseñanza-Aprendizaje": "#6366f1", // Indigo
      "Evaluación y Verificación de Saberes": "#10b981", // Emerald
      "Enfoque STEM+ y Contexto Rural": "#f59e0b", // Amber
      "Prácticas de Laboratorio y Mantenimiento": "#f43f5e", // Rose
      "Ciudadanía Digital y Ética": "#8b5cf6", // Violet
      "Pensamiento Computacional y Programación": "#06b6d4", // Cyan
      "Recursos Analógicos y Contingencias": "#f97316", // Orange
      "Ofimática y Competencias Productivas": "#14b8a6", // Teal
      "Gestión Administrativa y Proyectos Transversales": "#3b82f6", // Blue
    };
    return colors[cat] || "#6366f1";
  };

  // --- Formulario ---
  let formData = {
    fecha: new Date().toISOString().split("T")[0],
    docente: localStorage.getItem("lastDocente") || "",
    materia: "",
    horas: "",
    grado: "",
    anotacion: "",
    observacion: "",
  };

  // --- Persistencia de Materias por Docente ---
  let docenteMaterias: Record<string, string[]> = JSON.parse(
    localStorage.getItem("docenteMaterias") || "{}",
  );

  const saveMateriaForDocente = (docente: string, materia: string) => {
    if (!docente || !materia) return;
    if (!docenteMaterias[docente]) {
      docenteMaterias[docente] = [];
    }
    if (!docenteMaterias[docente].includes(materia)) {
      docenteMaterias[docente] = [...docenteMaterias[docente], materia];
      localStorage.setItem("docenteMaterias", JSON.stringify(docenteMaterias));
    }
  };

  $: if (formData.docente) {
    localStorage.setItem("lastDocente", formData.docente);
  }

  $: materiasSorted = formData.docente
    ? [...materias].sort((a, b) => {
        const aSaved = docenteMaterias[formData.docente]?.includes(a.materia);
        const bSaved = docenteMaterias[formData.docente]?.includes(b.materia);
        if (aSaved && !bSaved) return -1;
        if (!aSaved && bSaved) return 1;
        return a.materia.localeCompare(b.materia);
      })
    : materias;

  // --- Alertas Dismissibles ---
  let showInfoAlert =
    INFO_ANOTADOR &&
    localStorage.getItem("dismissedInfoAnotadorContent") !== INFO_ANOTADOR;

  const dismissAlert = () => {
    showInfoAlert = false;
    localStorage.setItem("dismissedInfoAnotadorContent", INFO_ANOTADOR);
  };

  let isLoading = false;

  // --- Filtros ---
  let showFilter = false;
  const openFilters = () => {
    showFilter = true;
  };
  const closeFilters = () => {
    showFilter = false;
  };

  // --- Alertas Dismissibles ---
  let showFeatureAlert = true; // Control inmediato del popup
  
  const FEATURE_MESSAGE = "¡Nueva función de filtrado avanzado disponible!";
  
  function checkFeatureAlertVisibility() {
    // FORZAR MOSTRAR PARA DESARROLLO - Cambiar a false en producción
    const DEBUG_FORCE_SHOW = true;
    
    if (DEBUG_FORCE_SHOW) {
      return true;
    }
    
    const dismissed = localStorage.getItem("dismissedFeatureAlertAnotador");
    
    // Si nunca fue descartado, mostrar siempre
    if (!dismissed) {
      return true;
    }
    
    const dismissedDate = new Date(dismissed);
    const now = new Date();
    const daysSinceDismissed = (now.getTime() - dismissedDate.getTime()) / (1000 * 60 * 60 * 24);
    
    // Mostrar si han pasado más de 5 días desde que se descartó
    return daysSinceDismissed > 5;
  }

  const dismissFeatureAlert = () => {
    localStorage.setItem("dismissedFeatureAlertAnotador", new Date().toISOString());
    showFeatureAlert = false; // Cerrar el popup inmediatamente
  };

  const tryFeatureNow = () => {
    // Cerrar el popup
    localStorage.setItem("dismissedFeatureAlertAnotador", new Date().toISOString());
    // Abrir el panel de filtros
    openFilters();
  };

  // Posición del popup relativa al botón de filtros
  let popupPosition = { top: 0, left: 0, arrowDirection: 'down' };
  
  const updatePopupPosition = () => {
    if (typeof window !== 'undefined') {
      const filterButton = document.querySelector('#filter-button-target');
      if (filterButton) {
        const rect = filterButton.getBoundingClientRect();
        popupPosition = {
          top: rect.bottom + 10,
          left: rect.left + rect.width / 2,
          arrowDirection: 'down'
        };
      }
    }
  };

  const getSheetsUrl = () => {
    return `https://docs.google.com/spreadsheets/d/${SPREADSHEET_ID_ANOTADOR}`;
  };

  const openSheets = () => {
    window.open(getSheetsUrl(), "_blank");
  };

  // Reactividad para estilos
  $: styles = {
    bg: "rgb(var(--bg-primary))",
    text: "rgb(var(--text-primary))",
    label: "rgb(var(--text-secondary))",
    border: "rgb(var(--border-primary))",
    placeholder: "rgb(var(--text-muted))",
    icon: "rgb(var(--text-muted))",
    cardBg: "rgb(var(--card-bg))",
    cardBorder: "rgb(var(--card-border))",
    inputBg: "rgb(var(--bg-secondary))",
  };

  const toggleTheme = () => {
    theme.update((t) => {
      if (t === "light") return "dim";
      if (t === "dim") return "dark";
      return "light";
    });
  };

  // Validación de formulario
  $: hasSelectedAnotacion = Object.values(anotacionGrupos)
    .flat()
    .some((o) => o.selected);

  $: isFormValid =
    formData.fecha &&
    formData.docente &&
    formData.materia &&
    formData.grado &&
    formData.horas &&
    hasSelectedAnotacion;

  const loadData = async () => {
    isLoadingDocentes = true;
    isLoadingMaterias = true;
    isLoadingEstudiantes = true;
    isLoadingOpciones = true;

    try {
      const [docentesData, materiasData, estudiantesData, opcionesData] =
        await Promise.all([
          getDocentes(),
          getMaterias(),
          getEstudiantes(),
          getOpcionesAnotador(),
        ]);
      docentes = docentesData;
      materias = materiasData;
      estudiantes = estudiantesData;

      // Transformar opciones a objetos editables
      const transformed: Record<string, OpcionAnotacion[]> = {};
      for (const [cat, items] of Object.entries(
        opcionesData as Record<string, string[]>,
      )) {
        transformed[cat] = items.map((text) => ({ text, selected: false }));
      }
      anotacionGrupos = transformed;
    } catch (error) {
      console.error("Error cargando datos iniciales:", error);
    } finally {
      isLoadingDocentes = false;
      isLoadingMaterias = false;
      isLoadingEstudiantes = false;
      isLoadingOpciones = false;
    }
  };

  const handleSubmit = async (event: Event) => {
    event.preventDefault();
    if (isLoading) return;

    const selectedTexts = Object.values(anotacionGrupos)
      .flat()
      .filter((o) => o.selected)
      .map((o) => o.text);
    const anotacionFinal = selectedTexts.join(" | ");

    // Confirmación con SweetAlert2
    const confirmResult = await Swal.fire({
      title: "¿Confirmar Registro?",
      html: `
        <div class="text-left space-y-2 p-2 rounded-xl bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10" style="font-size: 0.9rem; line-height: 1.4;">
          <div style="margin-bottom: 8px;"><strong style="color: #6366f1;">Fecha:</strong> ${formData.fecha}</div>
          <div style="margin-bottom: 8px;"><strong style="color: #6366f1;">Docente:</strong> ${formData.docente}</div>
          <div style="margin-bottom: 8px;"><strong style="color: #6366f1;">Asignatura:</strong> ${formData.materia}</div>
          <div style="margin-bottom: 8px;"><strong style="color: #6366f1;">Grado:</strong> ${formData.grado}</div>
          <div style="margin-bottom: 8px;"><strong style="color: #6366f1;">Horas:</strong> ${formData.horas}</div>
          <div style="margin-top: 12px; border-top: 1px solid rgba(0,0,0,0.1); padding-top: 8px;">
            <strong style="color: #6366f1;">Anotaciones:</strong>
            <ul style="list-style-type: disc; padding-left: 20px; margin-top: 4px; font-size: 0.8rem; opacity: 0.8;">
              ${selectedTexts.map((t) => `<li>${t}</li>`).join("")}
            </ul>
          </div>
          ${
            formData.observacion
              ? `<div style="margin-top: 12px;"><strong style="color: #6366f1;">Observación:</strong> <span style="font-size: 0.8rem;">${formData.observacion}</span></div>`
              : ""
          }
        </div>
      `,
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Si, de acuerdo",
      cancelButtonText: "No",
      confirmButtonColor: "#4f46e5",
      cancelButtonColor: "#64748b",
      reverseButtons: true,
      background: $theme === "light" ? "#fff" : "#1e293b",
      color: $theme === "light" ? "#1e293b" : "#f1f5f9",
    });

    if (!confirmResult.isConfirmed) return;

    isLoading = true;
    try {
      const currentTimestamp = new Date().toLocaleString();

      const payload = [
        [
          currentTimestamp,
          formData.fecha,
          formData.docente,
          formData.materia,
          formData.grado,
          formData.horas,
          anotacionFinal,
          formData.observacion,
        ],
      ];

      await saveAnotador({
        spreadsheetId: SPREADSHEET_ID_ANOTADOR,
        worksheetTitle: WORKSHEET_TITLE_ANOTADOR,
        datos: payload, // Reusing the parameter name 'inasistencias' from the service
      });

      // Persistir la materia para el docente solo después del éxito
      saveMateriaForDocente(formData.docente, formData.materia);

      await Swal.fire({
        icon: "success",
        title: "¡Éxito!",
        text: `Anotación registrada exitosamente`,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
        position: "top-end",
        toast: true,
      });

      formData = {
        fecha: new Date().toISOString().split("T")[0],
        docente: formData.docente,
        materia: formData.materia,
        grado: formData.grado,
        horas: "",
        anotacion: "",
        observacion: "",
      };

      // Resetear selecciones
      for (const cat in anotacionGrupos) {
        anotacionGrupos[cat] = anotacionGrupos[cat].map((o) => ({
          ...o,
          selected: false,
        }));
      }
    } catch (error) {
      console.error("Error enviando:", error);
      await Swal.fire({
        icon: "error",
        title: "Error",
        text: "Error al registrar la anotación",
        confirmButtonColor: "#ef4444",
      });
    } finally {
      isLoading = false;
    }
  };

  onMount(() => {
    loadData();
    updatePopupPosition();
    // Inicializar el estado del popup de feature
    showFeatureAlert = checkFeatureAlertVisibility();
    // Actualizar posición cuando cambia el tamaño de ventana
    if (typeof window !== 'undefined') {
      window.addEventListener('resize', updatePopupPosition);
      return () => window.removeEventListener('resize', updatePopupPosition);
    }
  });
</script>

<div
  class="min-h-screen flex flex-col lg:flex-row transition-colors duration-200"
  style="background-color: {styles.bg};"
>
  <!-- Sidebar -->
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
          Anotador de Clase
        </h1>
      </div>

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

        <!-- Botón de Filtros -->
        <button
          id="filter-button-target"
          on:click={openFilters}
          class="inline-flex items-center justify-center gap-2 px-3 lg:px-4 py-2 lg:py-3 border rounded-lg transition-all duration-200 hover:bg-black/5 dark:hover:bg-white/5"
          style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
          title="Abrir filtros de anotaciones"
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
              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"
            />
          </svg>
          <span class="text-sm font-medium hidden lg:inline">Filtros</span>
          {#if showFeatureAlert}
            <div class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full animate-ping"></div>
            <div class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></div>
          {/if}
        </button>

        <button
          on:click={onBack}
          class="inline-flex items-center justify-center gap-2 px-3 lg:px-4 py-2 lg:py-3 border rounded-lg transition-all duration-200 hover:bg-black/5 dark:hover:bg-white/5"
          style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
          title="Volver al Dashboard"
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
              d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"
            />
          </svg>
          <span class="text-sm font-medium hidden lg:inline">Dashboard</span>
        </button>

        <button
          on:click={toggleTheme}
          class="inline-flex items-center justify-center gap-2 px-3 lg:px-4 py-2 lg:py-3 border rounded-lg transition-all duration-200 hover:bg-black/5 dark:hover:bg-white/5 w-full"
          style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
          aria-label="Cambiar tema"
        >
          {#if $theme === "dark"}
            <svg
              class="w-5 h-5 text-indigo-500"
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
          {:else if $theme === "light"}
            <svg
              class="w-5 h-5 text-amber-500"
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
              class="w-5 h-5 text-indigo-400"
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
          {/if}
          <span class="text-sm font-medium hidden lg:inline capitalize">
            {$theme}
          </span>
        </button>
      </div>
    </div>
  </aside>

  <!-- Contenido Principal -->
  <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto pb-32">
    <div
      class="max-w-4xl mx-auto rounded-2xl p-6 lg:p-8 transition-colors duration-200 border"
      style="background-color: {styles.cardBg}; border-color: {styles.cardBorder};"
    >
      {#if showInfoAlert}
        <div
          class="mb-6 p-4 rounded-xl border flex items-start gap-4 animate-fade-in relative transition-all duration-300 bg-green-50 dark:bg-indigo-900/40 border-green-200 dark:border-indigo-700 shadow-sm"
        >
          <div
            class="p-2 rounded-lg bg-green-100 dark:bg-indigo-800 text-green-700 dark:text-indigo-200 flex-shrink-0"
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
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </div>
          <div class="flex-1 pr-6 pt-0.5">
            <h4
              class="text-sm font-bold mb-1 text-green-900 dark:text-indigo-100"
            >
              Información
            </h4>
            <p
              class="text-sm leading-relaxed text-green-800 dark:text-indigo-200"
            >
              {INFO_ANOTADOR}
            </p>
          </div>
          <button
            on:click={dismissAlert}
            class="absolute top-3 right-3 p-1.5 rounded-lg hover:bg-green-100 dark:hover:bg-indigo-700 text-green-600 hover:text-green-800 dark:text-indigo-400 dark:hover:text-indigo-100 transition-colors"
            aria-label="Cerrar alerta"
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
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>
      {/if}

      {#if showFeatureAlert}
        <!-- Overlay oscuro para destacar el botón -->
        <div class="fixed inset-0 bg-black/30 z-40" style="pointer-events: none;"></div>
        
        <!-- Popup que señala al botón de filtros -->
        <div 
          class="fixed z-50 animate-bounce-in"
          style="top: {popupPosition.top}px; left: {popupPosition.left}px; transform: translateX(-50%);"
        >
          <!-- Flecha apuntando hacia abajo (al botón) -->
          <div class="relative">
            <div 
              class="absolute w-0 h-0 border-l-8 border-r-8 border-t-8 border-l-transparent border-r-transparent border-t-blue-600 dark:border-t-blue-400"
              style="top: -8px; left: 50%; transform: translateX(-50%);"
            ></div>
            
            <!-- Contenido del popup -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-700 dark:to-indigo-700 text-white p-4 rounded-xl shadow-2xl border border-blue-400 dark:border-blue-500 min-w-[280px] max-w-[320px]">
              <!-- Header con ícono y texto NUEVO -->
              <div class="flex items-center gap-2 mb-3">
                <div class="p-1.5 bg-white/20 rounded-lg">
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
                      d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"
                    />
                  </svg>
                </div>
                <span class="inline-block px-2 py-0.5 text-xs font-bold rounded-full bg-white/25 animate-pulse">
                  ¡NUEVO!
                </span>
              </div>
              
              <!-- Mensaje principal -->
              <h3 class="font-bold text-base mb-2">
                {FEATURE_MESSAGE}
              </h3>
              
              <!-- Descripción -->
              <p class="text-sm text-blue-100 mb-3">
                Ahora puedes filtrar tus anotaciones de forma avanzada. ¡Pruébalo!
              </p>
              
              <!-- Botones de acción -->
              <div class="flex gap-2">
                <button
                  on:click={tryFeatureNow}
                  class="px-3 py-2 rounded-lg text-sm bg-white text-blue-600 font-semibold hover:bg-blue-50 transition-colors"
                >
                  Probar ahora
                </button>
                <button
                  on:click={dismissFeatureAlert}
                  class="px-3 py-2 rounded-lg text-sm hover:bg-white/20 transition-colors"
                  aria-label="Cerrar popup"
                >
                  Más tarde
                </button>
              </div>
              
              <!-- Botón cerrar -->
              <button
                on:click={dismissFeatureAlert}
                class="absolute top-2 right-2 p-1 rounded-lg hover:bg-white/20 transition-colors"
                aria-label="Cerrar notificación"
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
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>
      {/if}
 
      <form on:submit={handleSubmit} class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="space-y-2">
            <label
              for="fecha"
              class="block text-sm font-medium"
              style="color: {styles.label};">Fecha</label
            >
            <input
              type="date"
              id="fecha"
              bind:value={formData.fecha}
              required
              class="w-full px-4 py-3 rounded-xl border focus:ring-2 focus:ring-indigo-500 transition-all outline-none"
              style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text}; color-scheme: {$theme ===
              'light'
                ? 'light'
                : 'dark'};"
            />
          </div>

          <div class="space-y-2">
            <label
              for="docente"
              class="block text-sm font-medium"
              style="color: {styles.label};">Docente</label
            >
            <select
              id="docente"
              bind:value={formData.docente}
              required
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
              style="color: {styles.label};">Asignatura</label
            >
            <select
              id="materia"
              bind:value={formData.materia}
              required
              class="w-full px-4 py-3 rounded-xl border focus:ring-2 focus:ring-indigo-500 transition-all outline-none appearance-none cursor-pointer disabled:opacity-50"
              style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
            >
              <option value=""
                >{isLoadingMaterias
                  ? "Cargando..."
                  : "Seleccione asignatura"}</option
              >
              {#each materiasSorted as materia}
                {@const isSaved = docenteMaterias[formData.docente]?.includes(
                  materia.materia,
                )}
                <option
                  value={materia.materia}
                  style={isSaved ? "color: #6366f1; font-weight: 600;" : ""}
                >
                  {isSaved ? "⭐ " : ""}{materia.materia}
                </option>
              {/each}
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-2">
            <label
              for="grado"
              class="block text-sm font-medium"
              style="color: {styles.label};">Grado</label
            >
            <select
              id="grado"
              bind:value={formData.grado}
              required
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

          <div class="space-y-2">
            <label
              for="horas"
              class="block text-sm font-medium"
              style="color: {styles.label};">Horas</label
            >
            <select
              id="horas"
              bind:value={formData.horas}
              required
              class="w-full px-4 py-3 rounded-xl border focus:ring-2 focus:ring-indigo-500 transition-all outline-none appearance-none cursor-pointer"
              style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
            >
              <option value="">Seleccione horas</option>
              {#each [1, 2, 3, 4] as h}
                <option value={h.toString()}
                  >{h} {h === 1 ? "Hora" : "Horas"}</option
                >
              {/each}
            </select>
          </div>
        </div>

        <div class="space-y-8">
          {#if isLoadingOpciones}
            <div class="flex items-center justify-center py-12">
              <Loader message="Cargando opciones de anotación..." />
            </div>
          {:else}
            {#each Object.entries(anotacionGrupos) as [categoria, opciones]}
              {@const catColor = getCategoryColor(categoria)}
              <div class="space-y-4">
                <div class="flex items-center gap-3">
                  <h3
                    class="text-xs font-black uppercase tracking-widest px-3 py-1 rounded-full text-white"
                    style="background-color: {catColor};"
                  >
                    {categoria}
                  </h3>
                  <div
                    class="h-px flex-1"
                    style="background-color: {catColor}; opacity: 0.2;"
                  ></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  {#each opciones as opcion}
                    <div
                      class="relative group flex flex-col p-0 rounded-2xl border transition-all duration-200 shadow-sm hover:shadow-md"
                      style="
                        background-color: {opcion.selected
                        ? `${catColor}10`
                        : styles.inputBg};
                        border-color: {opcion.selected
                        ? catColor
                        : styles.border};
                      "
                    >
                      <div class="flex items-start gap-1 p-4">
                        <label class="flex-shrink-0 cursor-pointer p-1 mt-1">
                          <input
                            type="checkbox"
                            bind:checked={opcion.selected}
                            class="hidden"
                          />
                          <div
                            class="w-5 h-5 rounded border flex items-center justify-center transition-colors"
                            style="
                              border-color: {opcion.selected
                              ? catColor
                              : styles.border};
                              background-color: {opcion.selected
                              ? catColor
                              : 'transparent'};
                            "
                          >
                            {#if opcion.selected}
                              <svg
                                class="w-3 h-3 text-white"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                              >
                                <path
                                  fill-rule="evenodd"
                                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                  clip-rule="evenodd"
                                />
                              </svg>
                            {/if}
                          </div>
                        </label>

                        <textarea
                          bind:value={opcion.text}
                          rows="8"
                          class="w-full bg-transparent border-none focus:ring-0 text-base font-medium leading-relaxed resize-none p-1 transition-colors min-h-[180px] pb-6"
                          style="color: {styles.text};"
                          placeholder="Escriba aquí..."
                        ></textarea>
                      </div>
                    </div>
                  {/each}
                </div>
              </div>
            {/each}
          {/if}
        </div>

        <div class="space-y-2">
          <label
            for="observacion"
            class="block text-sm font-medium"
            style="color: {styles.label};">Observación</label
          >
          <textarea
            id="observacion"
            bind:value={formData.observacion}
            rows="3"
            class="w-full px-4 py-3 rounded-xl border focus:ring-2 focus:ring-indigo-500 transition-all outline-none resize-none"
            style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
            placeholder="Observaciones adicionales (opcional)..."
          ></textarea>
        </div>

        <div class="fixed bottom-8 right-8 z-50">
          <button
            type="submit"
            disabled={isLoading || !isFormValid}
            class="w-16 h-16 bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-400 dark:disabled:bg-slate-700 disabled:cursor-not-allowed disabled:scale-100 text-white rounded-full shadow-2xl transition-all duration-300 transform hover:scale-110 active:scale-95 backdrop-blur-sm bg-opacity-95 flex items-center justify-center overflow-hidden border border-white/20"
            aria-label="Guardar Anotación"
            title={!isFormValid
              ? "Complete todos los campos requeridos y al menos una anotación"
              : "Guardar Anotación"}
          >
            {#if isLoading}
              <svg
                class="animate-spin h-7 w-7 text-white"
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
            {:else}
              <svg
                class="w-8 h-8 transform rotate-90"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
                />
              </svg>
            {/if}
          </button>
        </div>
      </form>
    </div>
  </main>
</div>

{#if showFilter}
  <AnotadorFilter
    onClose={closeFilters}
    selectedDocente={formData.docente}
  />
{/if}

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
  
  @keyframes bounce-in {
    0% {
      opacity: 0;
      transform: translateX(-50%) translateY(-20px) scale(0.9);
    }
    60% {
      opacity: 1;
      transform: translateX(-50%) translateY(2px) scale(1.02);
    }
    100% {
      opacity: 1;
      transform: translateX(-50%) translateY(0) scale(1);
    }
  }
  .animate-bounce-in {
    animation: bounce-in 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
  }
</style>
