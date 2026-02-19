<script lang="ts">
  import { onMount } from "svelte";
  import Swal from "sweetalert2";
  import {
    getDocentes,
    getMaterias,
    getEstudiantes,
    saveDiario,
  } from "../../api/service";
  import {
    SPREADSHEET_ID_DIARIO,
    WORKSHEET_TITLE_DIARIO,
    INFO_DIARIO,
  } from "../constants";
  import Loader from "./Loader.svelte";
  import { theme } from "../lib/themeStore";
  import eieLogo from "../assets/eie.png";
  import DiarioAnotacionOptions from "./DiarioAnotacionOptions.svelte";
  import ReportGeneratorDiario from "./ReportGeneratorDiario.svelte";

  export let onBack: () => void;

  // --- Interfaces ---
  interface Estudiante {
    nombre: string;
    grado: number | string;
  }

  interface Materia {
    materia: string;
  }

  // --- Estado de datos ---
  let docentes: string[] = [];
  let materias: Materia[] = [];
  let estudiantes: Estudiante[] = [];

  let isLoadingDocentes = false;
  let isLoadingMaterias = false;
  let isLoadingEstudiantes = false;
  let isLoading = false;
  let showFieldErrors = false;

  // Materias múltiples para docente con "-"
  interface MateriaHoras {
    materia: string;
    horas: string;
  }
  let selectedMaterias: MateriaHoras[] = [];

  // Verificar si el docente tiene "-"
  $: docenteHasDash = formData.docente.includes("-");

  // Extraer número del docente cuando tiene patrón "Nombre-número"
  const getDocenteNumber = (docente: string): string | null => {
    const match = docente.match(/-(\d+)$/);
    return match ? match[1] : null;
  };

  // Filtrar grupos según el número del docente
  $: docenteNumber = getDocenteNumber(formData.docente);

  $: filteredGrados = docenteNumber
    ? [...new Set(estudiantes.map((e) => e.grado.toString()))].filter((g) =>
        g.startsWith(`${docenteNumber}-`),
      )
    : [...new Set(estudiantes.map((e) => e.grado.toString()))].filter((g) => !g.includes('-'));

  $: missingFields = (() => {
    const fields: string[] = [];
    if (!formData.fecha) fields.push("fecha");
    if (!formData.docente) fields.push("docente");
    if (!formData.grado) fields.push("grado");
    if (!formData.horas) fields.push("horas");
    if (selectedDiarioAnots.length === 0) fields.push("diario");
    
    if (docenteHasDash) {
      if (selectedMaterias.length === 0) {
        fields.push("materias");
      } else {
        const sinHoras = selectedMaterias.filter(m => !m.horas);
        if (sinHoras.length > 0) fields.push("horas");
      }
    } else {
      if (!formData.materia) fields.push("materia");
    }
    return fields;
  })();

  // --- Report Generator ---
  let showReportGenerator = false;

  const openReportGenerator = () => {
    showReportGenerator = true;
  };
  const closeReportGenerator = () => {
    showReportGenerator = false;
  };

  let selectedDiarioAnots: string[] = []; // State to hold selected annotations from the child component

  // --- Feature Alert ---
  let showFeatureAlert = true; // Control inicial del popup
  const FEATURE_MESSAGE = "¡Nueva forma de anotar el Diario de Campo!";

  function checkFeatureAlertVisibility() {
    // FORZAR MOSTRAR PARA DESARROLLO - Cambiar a false en producción
    const DEBUG_FORCE_SHOW = false; // Set to false for production

    if (DEBUG_FORCE_SHOW) {
      return true;
    }

    const dismissed = localStorage.getItem("dismissedFeatureAlertDiario");

    // Si nunca fue descartado, mostrar siempre
    if (!dismissed) {
      return true;
    }

    const dismissedDate = new Date(dismissed);
    const now = new Date();
    const daysSinceDismissed =
      (now.getTime() - dismissedDate.getTime()) / (1000 * 60 * 60 * 24);

    // Mostrar si han pasado más de 5 días desde que se descartó (e.g., to remind users)
    return daysSinceDismissed > 5;
  }

  const dismissFeatureAlert = () => {
    localStorage.setItem(
      "dismissedFeatureAlertDiario",
      new Date().toISOString(),
    );
    showFeatureAlert = false; // Cerrar el popup inmediatamente
  };

  const tryFeatureNow = () => {
    // Cerrar el popup
    localStorage.setItem(
      "dismissedFeatureAlertDiario",
      new Date().toISOString(),
    );
    showFeatureAlert = false; // Just dismiss as there's no specific action to take here
  };

  // --- Formulario ---
  let formData = {
    fecha: new Date().toLocaleDateString('es-CO'),
    docente: localStorage.getItem("lastDocenteDiario") || "",
    materia: "",
    grado: "",
    horas: "",
    // diarioCampo will now be derived from selectedDiarioAnots
  };

  // --- Persistencia de Materias por Docente ---
  let docenteMaterias: Record<string, string[]> = JSON.parse(
    localStorage.getItem("docenteMateriasDiario") || "{}",
  );

  const saveMateriaForDocente = (docente: string, materia: string) => {
    if (!docente || !materia) return;
    if (!docenteMaterias[docente]) {
      docenteMaterias[docente] = [];
    }
    if (!docenteMaterias[docente].includes(materia)) {
      docenteMaterias[docente] = [...docenteMaterias[docente], materia];
      localStorage.setItem(
        "docenteMateriasDiario",
        JSON.stringify(docenteMaterias),
      );
    }
  };

  $: if (formData.docente) {
    localStorage.setItem("lastDocenteDiario", formData.docente);
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
    INFO_DIARIO &&
    localStorage.getItem("dismissedInfoDiarioContent") !== INFO_DIARIO;

  const dismissAlert = () => {
    showInfoAlert = false;
    localStorage.setItem("dismissedInfoDiarioContent", INFO_DIARIO);
  };

  const getSheetsUrl = () => {
    return `https://docs.google.com/spreadsheets/d/${SPREADSHEET_ID_DIARIO}`;
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
  $: isFormValid =
    formData.fecha &&
    formData.docente &&
    formData.materia &&
    formData.grado && // Added grado to validation
    formData.horas &&
    selectedDiarioAnots.length > 0; // Validate based on selectedDiarioAnots

  const loadData = async () => {
    isLoadingDocentes = true;
    isLoadingMaterias = true;
    isLoadingEstudiantes = true; // Added loading state for students

    try {
      const [docentesData, materiasData, estudiantesData] = await Promise.all([
        getDocentes(),
        getMaterias(),
        getEstudiantes(), // Fetch students
      ]);
      docentes = docentesData;
      materias = materiasData;
      estudiantes = estudiantesData; // Update students state
    } catch (error) {
      console.error("Error cargando datos iniciales:", error);
    } finally {
      isLoadingDocentes = false;
      isLoadingMaterias = false;
      isLoadingEstudiantes = false; // Reset loading state
    }
  };

  const handleSubmit = async (event: Event) => {
    event.preventDefault();
    if (isLoading) return;

    showFieldErrors = true;

    const diarioCampoFinal = selectedDiarioAnots.join(" | ");

    const camposFaltantes: { id: string; label: string }[] = [];
    if (!formData.fecha) camposFaltantes.push({ id: "fecha", label: "Fecha" });
    if (!formData.docente) camposFaltantes.push({ id: "docente", label: "Docente" });
    if (!formData.grado) camposFaltantes.push({ id: "grado", label: "Grado" });
    if (!formData.horas) camposFaltantes.push({ id: "horas", label: "Horas" });
    if (selectedDiarioAnots.length === 0) camposFaltantes.push({ id: "diario", label: "Diario de Campo" });

    if (docenteHasDash) {
      if (selectedMaterias.length === 0) {
        camposFaltantes.push({ id: "materia", label: "Materia(s)" });
      } else {
        const sinHoras = selectedMaterias.filter(m => !m.horas);
        if (sinHoras.length > 0) {
          camposFaltantes.push({ id: "horas", label: `Horas para: ${sinHoras.map(m => m.materia).join(", ")}` });
        }
      }
    } else {
      if (!formData.materia) camposFaltantes.push({ id: "materia", label: "Asignatura" });
    }

    if (camposFaltantes.length > 0) {
      await Swal.fire({
        icon: "warning",
        title: "Campos requeridos",
        html: `Complete los siguientes campos:<br/><br/><strong>${camposFaltantes.map(c => c.label).join("<br/>")}</strong>`,
        confirmButtonColor: "#6366f1",
      });
      
      const firstField = document.getElementById(camposFaltantes[0].id);
      if (firstField) {
        firstField.scrollIntoView({ behavior: "smooth", block: "center" });
        firstField.focus();
      }
      return;
    }

    if (selectedDiarioAnots.length === 0) {
      await Swal.fire({
        icon: "warning",
        title: "Sin diario de campo",
        text: "Debe seleccionar al menos una opción del diario de campo",
        confirmButtonColor: "#6366f1",
      });
      return;
    }

    // Determinar las materias a guardar
    const materiasToSave: MateriaHoras[] = docenteHasDash 
      ? selectedMaterias 
      : [{ materia: formData.materia, horas: formData.horas }];

    // Construir mensaje de confirmación según el tipo de docente
    const materiasMsg = docenteHasDash 
      ? selectedMaterias.map(m => `${m.materia} (${m.horas}h)`).join(", ")
      : `${formData.materia} (${formData.horas}h)`;

    // Confirmación con SweetAlert2
    const confirmResult = await Swal.fire({
      title: "¿Confirmar Registro?",
      html: `
        <div class="text-left space-y-2 p-2 rounded-xl bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10" style="font-size: 0.9rem; line-height: 1.4;">
          <div style="margin-bottom: 8px;"><strong style="color: #6366f1;">Fecha:</strong> ${formData.fecha}</div>
          <div style="margin-bottom: 8px;"><strong style="color: #6366f1;">Docente:</strong> ${formData.docente}</div>
          <div style="margin-bottom: 8px;"><strong style="color: #6366f1;">Asignatura(s):</strong> ${materiasMsg}</div>
          <div style="margin-bottom: 8px;"><strong style="color: #6366f1;">Grado:</strong> ${formData.grado}</div>
          <div style="margin-top: 12px; border-top: 1px solid rgba(0,0,0,0.1); padding-top: 8px;">
            <strong style="color: #6366f1;">Diario de Campo:</strong>
            <ul style="list-style-type: disc; padding-left: 20px; margin-top: 4px; font-size: 0.8rem; opacity: 0.8;">
              ${selectedDiarioAnots.map((t) => `<li>${t}</li>`).join("")}
            </ul>
          </div>
          ${docenteHasDash ? `<div style="margin-top: 8px; font-size: 0.75rem; color: #ef4444;">Se creará un registro por cada materia seleccionada (${selectedMaterias.length} registros)</div>` : ''}
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

      // Crear un payload por cada materia seleccionada
      const payload: string[][] = [];
      
      for (const materiaData of materiasToSave) {
        payload.push([
          currentTimestamp,
          formData.fecha,
          materiaData.horas,
          formData.docente,
          materiaData.materia,
          formData.grado,
          diarioCampoFinal,
        ]);
      }

      await saveDiario({
        spreadsheetId: SPREADSHEET_ID_DIARIO,
        worksheetTitle: WORKSHEET_TITLE_DIARIO,
        datos: payload,
      });

      // Persistir las materias para el docente solo después del éxito
      for (const materiaData of materiasToSave) {
        saveMateriaForDocente(formData.docente, materiaData.materia);
      }

      const registrosMsg = payload.length === 1 
        ? "Diario de Campo registrado exitosamente" 
        : `${payload.length} registros de Diario de Campo creados exitosamente`;

      await Swal.fire({
        icon: "success",
        title: "¡Éxito!",
        text: registrosMsg,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
        position: "top-end",
        toast: true,
      });

      formData = {
        fecha: new Date().toLocaleDateString('es-CO'),
        docente: formData.docente,
        materia: "",
        grado: formData.grado,
        horas: "",
      };

      // Reset the child component's selections
      selectedDiarioAnots = [];
      selectedMaterias = [];
      showFieldErrors = false;
    } catch (error) {
      console.error("Error enviando:", error);
      await Swal.fire({
        icon: "error",
        title: "Error",
        text: "Error al registrar el Diario de Campo",
        confirmButtonColor: "#ef4444",
      });
    } finally {
      isLoading = false;
    }
  };

  onMount(() => {
    loadData();
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
      class="flex flex-col lg:flex-col items-center justify-between lg:justify-start gap-4 lg:gap-8"
    >
      <div class="flex items-center gap-4 lg:flex-col">
        <img src={eieLogo} alt="EIE Logo" class="h-12 lg:h-20 w-auto" />
        <h1
          class="text-xl lg:text-2xl tracking-tight font-bold lg:text-center"
          style="color: {styles.text};"
        >
          Diario de Campo
        </h1>
      </div>

      <div class="flex flex-wrap justify-center lg:flex-col gap-3 w-full">
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

        <!-- Botón de Reportes PDF -->
        <button
          on:click={openReportGenerator}
          class="inline-flex items-center justify-center gap-2 px-3 lg:px-4 py-2 lg:py-3 border rounded-lg transition-all duration-200 hover:bg-black/5 dark:hover:bg-white/5"
          style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
          title="Generar reportes PDF"
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
              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
            />
          </svg>
          <span class="text-sm font-medium hidden lg:inline">Reportes PDF</span>
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
              {INFO_DIARIO}
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

      <form on:submit={handleSubmit} novalidate class="space-y-6">
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
              bind:value={formData.fecha}
              required
              class="w-full px-4 py-3 rounded-xl border focus:ring-2 focus:ring-indigo-500 transition-all outline-none {showFieldErrors && missingFields.includes('fecha') ? 'ring-2 ring-red-500 border-red-500' : ''}"
              style="background-color: {styles.inputBg}; border-color: {showFieldErrors && missingFields.includes('fecha') ? '#ef4444' : styles.border}; color: {styles.text}; color-scheme: {$theme ===
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
              class="w-full px-4 py-3 rounded-xl border focus:ring-2 focus:ring-indigo-500 transition-all outline-none appearance-none cursor-pointer disabled:opacity-50 {showFieldErrors && missingFields.includes('docente') ? 'ring-2 ring-red-500 border-red-500' : ''}"
              style="background-color: {styles.inputBg}; border-color: {showFieldErrors && missingFields.includes('docente') ? '#ef4444' : styles.border}; color: {styles.text};"
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
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-2 {docenteHasDash ? 'lg:col-span-2' : ''}">
            <div class="flex items-center justify-between">
              <label
                for="materia"
                class="block text-sm font-medium"
                style="color: {styles.label};">Materia</label
              >
            </div>
            {#if docenteHasDash}
              <div class="border rounded-xl p-2 lg:p-3 flex flex-col lg:flex-row lg:flex-wrap gap-2 lg:gap-2 {showFieldErrors && missingFields.includes('materias') ? 'ring-2 ring-red-500 border-red-500' : ''}" style="border-color: {showFieldErrors && missingFields.includes('materias') ? '#ef4444' : styles.border}; background-color: {styles.inputBg};">
                {#each materiasSorted as materia}
                  {@const isSaved = docenteMaterias[formData.docente]?.includes(materia.materia)}
                  {@const selectedIndex = selectedMaterias.findIndex(m => m.materia === materia.materia)}
                  {@const isSelected = selectedIndex >= 0}
                  <div class="flex flex-col lg:flex-row lg:items-center gap-1 lg:gap-2 px-2 py-2 lg:py-1.5 rounded-lg border transition-all {isSelected ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/30 dark:border-indigo-500' : 'border-transparent'}"
                    style="border-color: {isSelected ? '#6366f1' : styles.border};">
                    <div class="flex items-center gap-1.5">
                      <input
                        type="checkbox"
                        checked={isSelected}
                        on:change={(e) => {
                          if (e.currentTarget.checked) {
                            selectedMaterias = [...selectedMaterias, { materia: materia.materia, horas: formData.horas }];
                          } else {
                            selectedMaterias = selectedMaterias.filter(m => m.materia !== materia.materia);
                          }
                        }}
                        class="w-4 h-4 lg:w-5 lg:h-5 rounded text-indigo-500 focus:ring-indigo-500"
                        style="accent-color: #6366f1;"
                      />
                      <span class="text-sm lg:text-sm flex-shrink-0" style="color: {styles.text};">
                        {isSaved ? "⭐ " : ""}{materia.materia}
                      </span>
                    </div>
                    {#if isSelected}
                      <select
                        bind:value={selectedMaterias[selectedIndex].horas}
                        class="w-full lg:w-16 px-2 py-1.5 lg:py-1 text-sm rounded border appearance-none cursor-pointer {showFieldErrors && missingFields.includes('horas') && !selectedMaterias[selectedIndex].horas ? 'ring-2 ring-red-500 border-red-500' : ''}"
                        style="background-color: {styles.inputBg}; border-color: {showFieldErrors && missingFields.includes('horas') && !selectedMaterias[selectedIndex].horas ? '#ef4444' : styles.border}; color: {styles.text};"
                      >
                        <option value="">Horas...</option>
                        <option value="0">0</option>
                        {#each [1, 2, 3, 4] as h}
                          <option value={h.toString()}>{h}h</option>
                        {/each}
                      </select>
                    {/if}
                  </div>
                {/each}
              </div>
            {:else}
              <select
                id="materia"
                bind:value={formData.materia}
                required
                class="w-full px-4 py-3 rounded-xl border focus:ring-2 focus:ring-indigo-500 transition-all outline-none appearance-none cursor-pointer disabled:opacity-50 {showFieldErrors && missingFields.includes('materia') ? 'ring-2 ring-red-500 border-red-500' : ''}"
                style="background-color: {styles.inputBg}; border-color: {showFieldErrors && missingFields.includes('materia') ? '#ef4444' : styles.border}; color: {styles.text};"
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
            {/if}
          </div>

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
              class="w-full px-4 py-3 rounded-xl border focus:ring-2 focus:ring-indigo-500 transition-all outline-none appearance-none cursor-pointer disabled:opacity-50 {showFieldErrors && missingFields.includes('grado') ? 'ring-2 ring-red-500 border-red-500' : ''}"
              style="background-color: {styles.inputBg}; border-color: {showFieldErrors && missingFields.includes('grado') ? '#ef4444' : styles.border}; color: {styles.text};"
            >
              <option value=""
                >{isLoadingEstudiantes
                  ? "Cargando..."
                  : "Seleccione grado"}</option
              >
              {#each filteredGrados as g}
                <option value={g}
                  >{g
                    .replace(/0(\d)$/, "°$1")
                    .replace(/(\d{1,2})0(\d)/, "$1°$2")}</option
                >
              {/each}
            </select>
          </div>
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
            class="w-full px-4 py-3 rounded-xl border focus:ring-2 focus:ring-indigo-500 transition-all outline-none appearance-none cursor-pointer {showFieldErrors && missingFields.includes('horas') ? 'ring-2 ring-red-500 border-red-500' : ''}"
            style="background-color: {styles.inputBg}; border-color: {showFieldErrors && missingFields.includes('horas') ? '#ef4444' : styles.border}; color: {styles.text};"
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
          <!-- Diario de Campo options component -->
          <DiarioAnotacionOptions bind:selectedDiarioAnots />
        </div>

        <div class="fixed bottom-8 right-8 z-50">
          <button
            type="submit"
            disabled={isLoading}
            class="w-16 h-16 bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-400 dark:disabled:bg-slate-700 disabled:cursor-not-allowed disabled:scale-100 text-white rounded-full shadow-2xl transition-all duration-300 transform hover:scale-110 active:scale-95 backdrop-blur-sm bg-opacity-95 flex items-center justify-center overflow-hidden border border-white/20"
            aria-label="Guardar Diario de Campo"
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

{#if showReportGenerator}
  <ReportGeneratorDiario
    onClose={closeReportGenerator}
    initialDocente={formData.docente}
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
</style>
