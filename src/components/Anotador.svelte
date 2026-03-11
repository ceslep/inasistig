<script lang="ts">
  import { onMount } from "svelte";
  import Swal from "sweetalert2";
  import {
    getDocentes,
    getMaterias,
    getEstudiantes,
    getOpcionesAnotador3 as getOpcionesAnotador,
    saveAnotador,
  } from "../../api/service";
  import {
    SPREADSHEET_ID_ANOTADOR,
    WORKSHEET_TITLE_ANOTADOR,
    INFO_ANOTADOR,
  } from "../constants";
  import Loader from "./Loader.svelte";
  import AnotadorFilter from "./AnotadorFilter.svelte";
  import ReportGenerator from "./ReportGenerator.svelte";
  import { theme } from "../lib/themeStore";
  import eieLogo from "../assets/eie.png";
  import { slide } from "svelte/transition";
  import FeaturePopup from "./FeaturePopup.svelte";
  import { Cloud, Filter, FileText, LayoutGrid, Moon, Sun, CloudMoon, Info, X, Search, ChevronDown, Check, Loader2, Send } from "lucide-svelte";

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
  let expandedCategories: Record<string, boolean> = {}; // New state for accordion

  // Estado para búsqueda
  let searchTerm = "";
  let filteredAnotacionGrupos: Record<string, OpcionAnotacion[]> = {};
  let sortedFilteredEntries: [string, OpcionAnotacion[]][] = [];
  let highlightedCategory = "";
  let lastSelectedMateria = "";

  const toggleCategory = (category: string) => {
    expandedCategories[category] = !expandedCategories[category];
    // This line is needed to trigger reactivity when updating an object property
    expandedCategories = expandedCategories;
  };

  // Función helper para actualizar selección de anotación y forzar reactividad
  const toggleAnotacionSeleccion = (categoria: string, opcion: OpcionAnotacion) => {
    const index = anotacionGrupos[categoria].indexOf(opcion);

    if (index !== -1) {
      // Create a new option object with the toggled selected state
      const updatedOption = {
        ...opcion,
        selected: !opcion.selected,
      };

      // Create a new array for the category with the updated option
      const updatedCategoryOptions = [
        ...anotacionGrupos[categoria].slice(0, index),
        updatedOption,
        ...anotacionGrupos[categoria].slice(index + 1),
      ];

      // Create a new anotacionGrupos object with the updated category array
      anotacionGrupos = {
        ...anotacionGrupos,
        [categoria]: updatedCategoryOptions,
      };

      console.log("🔄 Anotación toggle:", {
        categoria,
        index,
        texto: updatedOption.text,
        nuevoEstado: updatedOption.selected,
        totalSeleccionadas: Object.values(anotacionGrupos)
          .flat()
          .filter((o) => o.selected).length,
      });
    }
  };

  const getCategoryColor = (cat: string) => {
    const colors: Record<string, string> = {
      // Categorías reales del JSON
      "CÁTEDRA DE LA PAZ": "#8b5cf6", // Violet
      "CIENCIAS NATURALES Y EDUCACIÓN AMBIENTAL": "#10b981", // Emerald
      "CIENCIAS SOCIALES (HISTORIA, GEOGRAFÍA Y": "#f59e0b", // Amber
      "DIRECCIÓN DE GRUPO": "#3b82f6", // Blue
      "EDUCACIÓN ARTÍSTICA": "#f43f5e", // Rose
      "EDUCACIÓN FÍSICA, RECREACIÓN Y DEPORTES": "#ef4444", // Red
      "EDUCACIÓN RELIGIOSA, ÉTICA Y V. HUMANOS": "#8b5cf6", // Violet
      EMPRENDIMIENTO: "#f97316", // Orange
      ESTADÍSTICA: "#06b6d4", // Cyan
      "FILOSOFÍA Y CIENCIAS SOCIALES (CIENCIAS": "#6366f1", // Indigo
      FÍSICA: "#3b82f6", // Blue
      INGLÉS: "#14b8a6", // Teal
      "LENGUA CASTELLANA": "#10b981", // Emerald
      MATEMÁTICAS: "#3b82f6", // Blue
      "PROYECTO Y EMPRENDIMIENTO": "#f97316", // Orange
      QUÍMICA: "#06b6d4", // Cyan
      "TECNOLOGÍA E INFORMÁTICA": "#6366f1", // Indigo
      "ÉTICA PROFESIONAL": "#8b5cf6", // Violet

      // Categorías anteriores (mantener por compatibilidad)
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

  // Mapeo de materias a categorías correspondientes (con nombres exactos del JSON)
  const materiaToCategory: Record<string, string> = {
    MATEMÁTICAS: "MATEMÁTICAS",
    "LENGUA CASTELLANA": "LENGUA CASTELLANA",
    INGLÉS: "INGLÉS",
    "CIENCIAS NATURALES Y EDUCACIÓN AMBIENTAL":
      "CIENCIAS NATURALES Y EDUCACIÓN AMBIENTAL",
    "CIENCIAS NATURALES": "CIENCIAS NATURALES Y EDUCACIÓN AMBIENTAL",
    "CIENCIAS SOCIALES (HISTORIA, GEOGRAFÍA Y":
      "CIENCIAS SOCIALES (HISTORIA, GEOGRAFÍA Y",
    "CIENCIAS SOCIALES": "CIENCIAS SOCIALES (HISTORIA, GEOGRAFÍA Y",
    FÍSICA: "FÍSICA",
    QUÍMICA: "QUÍMICA",
    "EDUCACIÓN ARTÍSTICA": "EDUCACIÓN ARTÍSTICA",
    "EDUCACIÓN FÍSICA, RECREACIÓN Y DEPORTES":
      "EDUCACIÓN FÍSICA, RECREACIÓN Y DEPORTES",
    "EDUCACIÓN FÍSICA": "EDUCACIÓN FÍSICA, RECREACIÓN Y DEPORTES",
    "TECNOLOGÍA E INFORMÁTICA": "TECNOLOGÍA E INFORMÁTICA",
    TECNOLOGÍA: "TECNOLOGÍA E INFORMÁTICA",
    INFORMÁTICA: "TECNOLOGÍA E INFORMÁTICA",
    EMPRENDIMIENTO: "EMPRENDIMIENTO",
    "FILOSOFÍA Y CIENCIAS SOCIALES (CIENCIAS":
      "FILOSOFÍA Y CIENCIAS SOCIALES (CIENCIAS",
    FILOSOFÍA: "FILOSOFÍA Y CIENCIAS SOCIALES (CIENCIAS",
    ESTADÍSTICA: "ESTADÍSTICA",
    "EDUCACIÓN RELIGIOSA, ÉTICA Y V. HUMANOS":
      "EDUCACIÓN RELIGIOSA, ÉTICA Y V. HUMANOS",
    ÉTICA: "EDUCACIÓN RELIGIOSA, ÉTICA Y V. HUMANOS",
    "DIRECCIÓN DE GRUPO": "DIRECCIÓN DE GRUPO",
    "PROYECTO Y EMPRENDIMIENTO": "PROYECTO Y EMPRENDIMIENTO",
    PROYECTO: "PROYECTO Y EMPRENDIMIENTO",
    "CÁTEDRA DE LA PAZ": "CÁTEDRA DE LA PAZ",
    "ÉTICA PROFESIONAL": "ÉTICA PROFESIONAL",
  };

  // Función para ordenar categorías según materia seleccionada
  const sortCategoriesByMateria = (entries: [string, OpcionAnotacion[]][]) => {
    if (!formData.materia) return entries;

    // Buscar coincidencia exacta o parcial
    let targetCategory =
      materiaToCategory[formData.materia.toUpperCase()] ||
      materiaToCategory[formData.materia] ||
      "";

    // Si no encuentra coincidencia exacta, buscar coincidencia parcial
    if (!targetCategory) {
      const materiaUpper = formData.materia.toUpperCase();
      for (const [key, value] of Object.entries(materiaToCategory)) {
        if (key.includes(materiaUpper) || materiaUpper.includes(key)) {
          targetCategory = value;
          break;
        }
      }
    }

    console.log(
      "Materia seleccionada:",
      formData.materia,
      "→ Categoría:",
      targetCategory,
    );

    return entries.sort(([catA], [catB]) => {
      // La categoría correspondiente va primero
      if (catA === targetCategory && catB !== targetCategory) return -1;
      if (catB === targetCategory && catA !== targetCategory) return 1;

      // Mantener orden alfabético para el resto
      return catA.localeCompare(catB);
    });
  };

  // Detectar cambios en la materia seleccionada
  $: if (formData.materia && formData.materia !== lastSelectedMateria) {
    lastSelectedMateria = formData.materia;

    // Encontrar la categoría correspondiente
    let targetCategory =
      materiaToCategory[formData.materia.toUpperCase()] ||
      materiaToCategory[formData.materia] ||
      "";

    // Buscar coincidencia parcial si no encuentra exacta
    if (!targetCategory) {
      const materiaUpper = formData.materia.toUpperCase();
      for (const [key, value] of Object.entries(materiaToCategory)) {
        if (key.includes(materiaUpper) || materiaUpper.includes(key)) {
          targetCategory = value;
          break;
        }
      }
    }

    if (targetCategory) {
      highlightedCategory = targetCategory;
      console.log("✨ Categoria destacada:", targetCategory);

      // Remover el highlight después de 3 segundos
      setTimeout(() => {
        highlightedCategory = "";
      }, 3000);
    }
  }

  // Reactividad para forzar el reordenamiento
  $: {
    console.log("🔄 Reactividad actualizada");
    console.log(
      "📚 Materias disponibles:",
      materias.map((m) => m.materia),
    );
    console.log("✍️ Materia seleccionada:", formData.materia);
    console.log("📊 Categorías disponibles:", Object.keys(anotacionGrupos));
    console.log(
      "🔍 Categorías filtradas:",
      Object.keys(filteredAnotacionGrupos),
    );
    sortedFilteredEntries = sortCategoriesByMateria(
      Object.entries(filteredAnotacionGrupos),
    );
    console.log(
      "📋 Entradas ordenadas:",
      sortedFilteredEntries.map(([cat]) => cat),
    );
  }

  // Función de filtrado
  $: filteredAnotacionGrupos = Object.entries(anotacionGrupos).reduce(
    (acc, [categoria, opciones]) => {
      if (!searchTerm.trim()) {
        acc[categoria] = opciones;
        return acc;
      }

      const term = searchTerm.toLowerCase().trim();
      const filteredOpciones = opciones.filter((opcion) =>
        opcion.text.toLowerCase().includes(term),
      );

      // Solo incluir la categoría si encuentra coincidencias en el texto de las opciones
      if (filteredOpciones.length > 0) {
        acc[categoria] = filteredOpciones;
        // No auto-expandir categorías, el docente decide cuándo abrir
      }

      return acc;
    },
    {} as Record<string, OpcionAnotacion[]>,
  );

  // --- Formulario ---
  let formData = {
    fecha: new Date().toLocaleDateString('en-CA'),
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

  // --- Alertas Dismissibles ---
  let showInfoAlert =
    INFO_ANOTADOR &&
    localStorage.getItem("dismissedInfoAnotadorContent") !== INFO_ANOTADOR;

  const dismissAlert = () => {
    showInfoAlert = false;
    localStorage.setItem("dismissedInfoAnotadorContent", INFO_ANOTADOR);
  };

  let isLoading = false;
  let showFieldErrors = false;

  $: missingFields = (() => {
    const fields: string[] = [];
    if (!formData.fecha) fields.push("fecha");
    if (!formData.docente) fields.push("docente");
    if (!formData.materia) fields.push("materia");
    if (!formData.grado) fields.push("grado");
    if (!formData.horas) fields.push("horas");
    const hasSelectedAnotacion = Object.values(anotacionGrupos).flat().some((o) => o.selected);
    if (!hasSelectedAnotacion) fields.push("anotacion");
    return fields;
  })();

  // --- Filtros ---
  let showFilter = false;
  let showReportGenerator = false;

  const openFilters = () => {
    showFilter = true;
  };
  const closeFilters = () => {
    showFilter = false;
  };

  const openReportGenerator = () => {
    showReportGenerator = true;
  };
  const closeReportGenerator = () => {
    showReportGenerator = false;
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
    const daysSinceDismissed =
      (now.getTime() - dismissedDate.getTime()) / (1000 * 60 * 60 * 24);

    // Mostrar si han pasado más de 5 días desde que se descartó
    return daysSinceDismissed > 5;
  }

  const dismissFeatureAlert = () => {
    localStorage.setItem(
      "dismissedFeatureAlertAnotador",
      new Date().toISOString(),
    );
    showFeatureAlert = false; // Cerrar el popup inmediatamente
  };

  const tryFeatureNow = () => {
    // Cerrar el popup
    localStorage.setItem(
      "dismissedFeatureAlertAnotador",
      new Date().toISOString(),
    );
    // Abrir el panel de filtros
    openFilters();
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

  // Debug para validación del formulario
  $: {
    console.log("🔍 Validación formulario:", {
      fecha: formData.fecha,
      docente: formData.docente,
      materia: formData.materia,
      grado: formData.grado,
      horas: formData.horas,
      hasSelectedAnotacion,
      isFormValid,
      totalAnotaciones: Object.values(anotacionGrupos).flat().length,
      selectedCount: Object.values(anotacionGrupos)
        .flat()
        .filter((o) => o.selected).length,
    });
  }

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
      const initialExpandedState: Record<string, boolean> = {}; // Initialize expanded state
      for (const [cat, items] of Object.entries(
        opcionesData as Record<string, string[]>,
      )) {
        transformed[cat] = items.map((text) => ({ text, selected: false }));
        initialExpandedState[cat] = false; // All categories start minimized
      }
      anotacionGrupos = transformed;
      expandedCategories = initialExpandedState;
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

    showFieldErrors = true;

    const selectedTexts = Object.values(anotacionGrupos)
      .flat()
      .filter((o) => o.selected)
      .map((o) => o.text);
    const anotacionFinal = selectedTexts.join(" | ");

    const camposFaltantes: { id: string; label: string }[] = [];
    if (!formData.fecha) camposFaltantes.push({ id: "fecha", label: "Fecha" });
    if (!formData.docente) camposFaltantes.push({ id: "docente", label: "Docente" });
    if (!formData.materia) camposFaltantes.push({ id: "materia", label: "Asignatura" });
    if (!formData.grado) camposFaltantes.push({ id: "grado", label: "Grado" });
    if (!formData.horas) camposFaltantes.push({ id: "horas", label: "Horas" });
    if (selectedTexts.length === 0) camposFaltantes.push({ id: "search", label: "Anotación(es)" });

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

    if (selectedTexts.length === 0) {
      await Swal.fire({
        icon: "warning",
        title: "Sin anotaciones",
        text: "Debe seleccionar al menos una anotación",
        confirmButtonColor: "#6366f1",
      });
      return;
    }

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

      const result = await saveAnotador({
        spreadsheetId: SPREADSHEET_ID_ANOTADOR,
        worksheetTitle: WORKSHEET_TITLE_ANOTADOR,
        datos: payload,
      });

      const isOffline = result?.offline === true;

      // Persistir la materia para el docente solo después del éxito
      saveMateriaForDocente(formData.docente, formData.materia);

      await Swal.fire({
        icon: isOffline ? "warning" : "success",
        title: isOffline ? "Guardado offline" : "¡Éxito!",
        text: isOffline
          ? "Anotación guardada en cola. Se enviará al recuperar conexión."
          : "Anotación registrada exitosamente",
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
        position: "top-end",
        toast: true,
      });

      formData = {
    fecha: new Date().toLocaleDateString('en-CA'),
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
      // Forzar reactividad completa
      anotacionGrupos = { ...anotacionGrupos };
      showFieldErrors = false;
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
    // Inicializar el estado del popup de feature
    showFeatureAlert = checkFeatureAlertVisibility();
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
          Anotador de Clase
        </h1>
      </div>

      <div class="flex flex-wrap justify-center lg:flex-col gap-3 w-full">
        <button
          on:click={openSheets}
          class="inline-flex items-center justify-center gap-2 px-3 lg:px-4 py-2 lg:py-3 border rounded-lg transition-all duration-200 hover:bg-black/5 dark:hover:bg-white/5"
          style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
          title="Abrir hoja de cálculo"
        >
          <Cloud class="w-5 h-5" />
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
          <Filter class="w-5 h-5" />
          <span class="text-sm font-medium hidden lg:inline">Filtros</span>
          {#if showFeatureAlert}
            <div
              class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full animate-ping"
            ></div>
            <div
              class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"
            ></div>
          {/if}
        </button>

        <!-- Botón de Reportes PDF -->
        <button
          on:click={openReportGenerator}
          class="inline-flex items-center justify-center gap-2 px-3 lg:px-4 py-2 lg:py-3 border rounded-lg transition-all duration-200 hover:bg-black/5 dark:hover:bg-white/5"
          style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
          title="Generar reportes PDF"
        >
          <FileText class="w-5 h-5" />
          <span class="text-sm font-medium hidden lg:inline">Reportes PDF</span>
        </button>

        <button
          on:click={onBack}
          class="inline-flex items-center justify-center gap-2 px-3 lg:px-4 py-2 lg:py-3 border rounded-lg transition-all duration-200 hover:bg-black/5 dark:hover:bg-white/5"
          style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
          title="Volver al Dashboard"
        >
          <LayoutGrid class="w-5 h-5" />
          <span class="text-sm font-medium hidden lg:inline">Dashboard</span>
        </button>

        <button
          on:click={toggleTheme}
          class="inline-flex items-center justify-center gap-2 px-3 lg:px-4 py-2 lg:py-3 border rounded-lg transition-all duration-200 hover:bg-black/5 dark:hover:bg-white/5 w-full"
          style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
          aria-label="Cambiar tema"
        >
          {#if $theme === "dark"}
            <Moon class="w-5 h-5 text-indigo-500" />
          {:else if $theme === "light"}
            <Sun class="w-5 h-5 text-amber-500" />
          {:else}
            <CloudMoon class="w-5 h-5 text-indigo-400" />
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
            <Info class="w-5 h-5" />
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
            <X class="w-4 h-4" />
          </button>
        </div>
      {/if}

      <!-- Feature Popup Component -->
      <FeaturePopup
        featureMessage={FEATURE_MESSAGE}
        description="Ahora puedes filtrar tus anotaciones de forma avanzada. ¡Pruébalo!"
        onTryNow={tryFeatureNow}
        onDismiss={dismissFeatureAlert}
        targetSelector="#filter-button-target"
        showPopup={false}
        colorTheme="purple"
      />

      <form on:submit={handleSubmit} novalidate class="space-y-6">
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
              {#each [1, 2, 3, 4] as h}
                <option value={h.toString()}
                  >{h} {h === 1 ? "Hora" : "Horas"}</option
                >
              {/each}
            </select>
          </div>
        </div>

        <!-- Campo de búsqueda -->
        <div class="space-y-2">
          <label
            for="search"
            class="block text-sm font-medium"
            style="color: {styles.label};"
          >
            Buscar anotaciones
          </label>
          <div class="relative">
            <input
              type="text"
              id="search"
              bind:value={searchTerm}
              placeholder="Buscar por palabra clave o categoría..."
              class="w-full px-4 py-3 pl-12 rounded-xl border focus:ring-2 focus:ring-indigo-500 transition-all outline-none"
              style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
            />
            <Search class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5" style="color: {styles.placeholder};" />
            {#if searchTerm}
              <button
                on:click={() => (searchTerm = "")}
                class="absolute right-4 top-1/2 transform -translate-y-1/2 p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                style="color: {styles.placeholder};"
                title="Limpiar búsqueda"
              >
                <X class="w-4 h-4" />
              </button>
            {/if}
          </div>
          {#if searchTerm && Object.keys(filteredAnotacionGrupos).length === 0}
            <div class="text-center py-8" style="color: {styles.placeholder};">
              <Search class="w-12 h-12 mx-auto mb-4 opacity-50" />
              <p class="text-sm">
                No se encontraron anotaciones para "{searchTerm}"
              </p>
            </div>
          {/if}
        </div>

        <div class="space-y-8">
          {#if isLoadingOpciones}
            <div class="flex items-center justify-center py-12">
              <Loader message="Cargando opciones de anotación..." />
            </div>
          {:else}
            {#each sortedFilteredEntries as [categoria, opciones]}
              {@const catColor = getCategoryColor(categoria)}
              {@const filteredCount = opciones.length}
              <div class="space-y-4">
                <button
                  type="button"
                  on:click={() => toggleCategory(categoria)}
                  class="flex items-center gap-3 w-full text-left cursor-pointer group focus:outline-none"
                >
                  <div class="flex items-center gap-3 flex-1">
                    <h3
                      class="text-xs font-black uppercase tracking-widest px-3 py-1 rounded-full text-white flex-shrink-0 transition-all duration-300 group-hover:shadow-md {categoria ===
                      highlightedCategory
                        ? 'animate-glow ring-4 ring-opacity-50'
                        : ''}"
                      style="background-color: {catColor}; {categoria ===
                      highlightedCategory
                        ? `box-shadow: 0 0 25px ${catColor}60, 0 0 50px ${catColor}30; ring-color: ${catColor}; border: 2px solid ${catColor};`
                        : ''}"
                    >
                      {categoria}
                      {#if categoria === highlightedCategory}
                        <span class="ml-2 inline-block animate-bounce">✨</span>
                      {/if}
                    </h3>
                    {#if searchTerm}
                      <span
                        class="text-xs font-medium"
                        style="color: {catColor};"
                      >
                        ({filteredCount} resultado{filteredCount !== 1
                          ? "s"
                          : ""})
                      </span>
                    {/if}
                  </div>
                  {#if !searchTerm}
                    <div
                      class="h-px flex-1 transition-all duration-300 group-hover:opacity-50"
                      style="background-color: {catColor}; opacity: 0.2;"
                    ></div>
                  {/if}
                  <ChevronDown class="w-5 h-5 text-gray-500 transform transition-transform duration-200 flex-shrink-0 {expandedCategories[categoria] ? 'rotate-180' : ''}" />
                </button>

                {#if expandedCategories[categoria]}
                  <div
                    transition:slide
                    class="grid grid-cols-1 md:grid-cols-2 gap-4"
                  >
                    {#each opciones as opcion}
                      {@const isHighlighted =
                        searchTerm &&
                        opcion.text
                          .toLowerCase()
                          .includes(searchTerm.toLowerCase())}
                      <div
                        class="relative group flex flex-col p-0 rounded-2xl border transition-all duration-200 shadow-sm hover:shadow-md"
                        style="
                          background-color: {opcion.selected
                          ? `${catColor}10`
                          : isHighlighted
                            ? `${catColor}05`
                            : styles.inputBg};
                          border-color: {opcion.selected
                          ? catColor
                          : isHighlighted
                            ? catColor
                            : styles.border};
                        "
                      >
                        <div class="flex items-start gap-1 p-4">
                          <label class="flex-shrink-0 cursor-pointer p-1 mt-1">
                            <input
                              type="checkbox"
                              checked={opcion.selected}
                              class="hidden"
                              on:change={() => toggleAnotacionSeleccion(categoria, opcion)}
                            />
                            <!-- svelte-ignore a11y_click_events_have_key_events -->
                            <div
                              class="w-5 h-5 rounded border flex items-center justify-center transition-colors cursor-pointer"
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
                                <Check class="w-3 h-3 text-white" />
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
                {/if}
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
            disabled={isLoading}
            class="w-16 h-16 bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-400 dark:disabled:bg-slate-700 disabled:cursor-not-allowed disabled:scale-100 text-white rounded-full shadow-2xl transition-all duration-300 transform hover:scale-110 active:scale-95 backdrop-blur-sm bg-opacity-95 flex items-center justify-center overflow-hidden border border-white/20"
            aria-label="Guardar Anotación"
          >
            {#if isLoading}
              <Loader2 class="animate-spin h-7 w-7 text-white" />
            {:else}
              <Send class="w-8 h-8 transform rotate-90" />
            {/if}
          </button>
        </div>
      </form>
    </div>
  </main>
</div>

{#if showFilter}
  <AnotadorFilter onClose={closeFilters} selectedDocente={formData.docente} />
{/if}

{#if showReportGenerator}
  <ReportGenerator
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

  @keyframes glow-pulse {
    0%,
    100% {
      box-shadow:
        0 0 10px currentColor,
        0 0 20px currentColor;
      transform: scale(1);
    }
    50% {
      box-shadow:
        0 0 20px currentColor,
        0 0 40px currentColor;
      transform: scale(1.05);
    }
  }

  .animate-glow {
    animation: glow-pulse 2s ease-in-out 3;
  }
</style>
