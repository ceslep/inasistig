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
  import { docenteName, findMatchingDocente } from "../lib/authStore";
  import { getCategoryColor } from "../lib/design-system";
  import { useDraftSave } from "../lib/useDraftSave";
  import eieLogo from "../assets/eie.png";
  import { slide } from "svelte/transition";
  import FeaturePopup from "./FeaturePopup.svelte";
  import ModuleHeader from "./ModuleHeader.svelte";
  import { Cloud, Filter, FileText, LayoutGrid, Moon, Sun, CloudMoon, Info, X, Search, ChevronDown, Check, Loader2, Send, WifiOff } from '@lucide/svelte';
  import { Accordion, CheckboxCard, Skeleton, SelectField, SlideOver, Tooltip, DatePicker } from './anotador';

  // --- Props ---
  interface AnotadorProps {
    onBack: () => void;
  }

  const { onBack }: AnotadorProps = $props();

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
  let docentes: string[] = $state([]);
  let materias: Materia[] = $state([]);
  let estudiantes: Estudiante[] = $state([]);

  let isLoadingDocentes = $state(false);
  let isLoadingMaterias = $state(false);
  let isLoadingEstudiantes = $state(false);
  let isLoadingOpciones = $state(false);

  let anotacionGrupos: Record<string, OpcionAnotacion[]> = $state({});
  let expandedCategories: Record<string, boolean> = $state({}); // State for accordion

  // Estado para búsqueda
  let searchTerm = $state("");
  let highlightedCategory = $state("");
  let lastSelectedMateria = $state("");

  const toggleCategory = (category: string) => {
    expandedCategories[category] = !expandedCategories[category];
  };

  // Función helper para actualizar selección de anotación
  const toggleAnotacionSeleccion = (categoria: string, opcionText: string) => {
    const opciones = anotacionGrupos[categoria];
    if (!opciones) return;
    
    const opcion = opciones.find(o => o.text === opcionText);
    if (opcion) {
      opcion.selected = !opcion.selected;

      console.log("🔄 Anotación toggle:", {
        categoria,
        texto: opcion.text,
        nuevoEstado: opcion.selected,
        totalSeleccionadas: Object.values(anotacionGrupos)
          .flat()
          .filter((o) => o.selected).length,
      });
    }
  };

  // getCategoryColor ahora se importa de design-system.ts

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
  $effect(() => {
    if (formData.materia && formData.materia !== lastSelectedMateria) {
      lastSelectedMateria = formData.materia;

      // Encontrar la categoría correspondiente
      let targetCategory =
        materiaToCategory[formData.materia.toUpperCase()] ||
        materiaToCategory[formData.materia] ||
        '';

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
        console.log('✨ Categoria destacada:', targetCategory);

        // Remover el highlight después de 3 segundos
        setTimeout(() => {
          highlightedCategory = '';
        }, 3000);
      }
    }
  });

  // Función de filtrado
  let filteredAnotacionGrupos: Record<string, OpcionAnotacion[]> = $derived.by(() => {
    return Object.entries(anotacionGrupos).reduce(
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
        }

        return acc;
      },
      {} as Record<string, OpcionAnotacion[]>,
    );
  });

  // Reactividad para el reordenamiento
  let sortedFilteredEntries: [string, OpcionAnotacion[]][] = $derived.by(() => {
    console.log('🔄 Reactividad actualizada');
    console.log(
      '📚 Materias disponibles:',
      materias.map((m) => m.materia),
    );
    console.log('✍️ Materia seleccionada:', formData.materia);
    console.log('📊 Categorías disponibles:', Object.keys(anotacionGrupos));
    console.log(
      '🔍 Categorías filtradas:',
      Object.keys(filteredAnotacionGrupos),
    );
    const entries = sortCategoriesByMateria(
      Object.entries(filteredAnotacionGrupos),
    );
    console.log(
      '📋 Entradas ordenadas:',
      entries.map(([cat]) => cat),
    );
    return entries;
  });

  // --- Formulario ---
  let formData = $state({
    fecha: new Date().toLocaleDateString('en-CA'),
    docente: localStorage.getItem("lastDocente") || "",
    materia: "",
    horas: "",
    grado: "",
    anotacion: "",
    observacion: "",
  });

  // --- Auto-guardado de borradores ---
  const { saveDraft, loadDraft, clearDraft, hasDraft } = useDraftSave();
  let showRestoreDraft = $state(false);
  let draftRestored = $state(false);
  let isOnline = $state(true);

  // Estado de conexión
  $effect(() => {
    isOnline = navigator.onLine;
    const handleOnline = () => isOnline = true;
    const handleOffline = () => isOnline = false;
    window.addEventListener('online', handleOnline);
    window.addEventListener('offline', handleOffline);
    return () => {
      window.removeEventListener('online', handleOnline);
      window.removeEventListener('offline', handleOffline);
    };
  });

  $effect(() => {
    if (draftRestored) return;
    if (!formData.docente && !formData.materia) return;
    
    const hasContent = formData.docente || formData.materia || formData.anotacion || formData.observacion;
    if (hasContent) {
      saveDraft(formData);
    }
  });

  const restoreDraft = () => {
    const draft = loadDraft();
    if (draft) {
      formData = {
        ...formData,
        docente: draft.docente,
        materia: draft.materia,
        grado: draft.grado,
        horas: draft.horas,
        fecha: draft.fecha,
        anotacion: draft.anotacion,
        observacion: draft.observacion,
      };
      draftRestored = true;
      showRestoreDraft = false;
      clearDraft();
      Swal.fire({
        icon: 'success',
        title: 'Borrador restaurado',
        text: 'Se恢复了 tu borrador guardado',
        timer: 2000,
        showConfirmButton: false,
      });
    }
  };

  const dismissDraft = () => {
    showRestoreDraft = false;
    clearDraft();
    draftRestored = true;
  };

  // Check for existing draft on mount
  $effect(() => {
    if (!draftRestored && hasDraft()) {
      showRestoreDraft = true;
    }
  });

  const clearFormDraft = () => {
    clearDraft();
    draftRestored = true;
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

  $effect(() => {
    if (formData.docente) {
      localStorage.setItem("lastDocente", formData.docente);
    }
  });

  let materiasSorted = $derived(
    formData.docente
      ? [...materias].sort((a, b) => {
          const aSaved = docenteMaterias[formData.docente]?.includes(a.materia);
          const bSaved = docenteMaterias[formData.docente]?.includes(b.materia);
          if (aSaved && !bSaved) return -1;
          if (!aSaved && bSaved) return 1;
          return a.materia.localeCompare(b.materia);
        })
      : materias
  );

  const getDocenteNumber = (docente: string): string | null => {
    const match = docente.match(/-(\d+)$/);
    return match ? match[1] : null;
  };

  let docenteNumber = $derived(getDocenteNumber(formData.docente));

  let filteredGrados = $derived(
    docenteNumber
      ? [...new Set(estudiantes.map((e) => e.grado.toString()))].filter((g) =>
          g.startsWith(`${docenteNumber}-`)
        )
      : [...new Set(estudiantes.map((e) => e.grado.toString()))].filter((g) => !g.includes('-'))
  );

  // --- Alertas Dismissibles ---
  let showInfoAlert = $state(
    INFO_ANOTADOR &&
    localStorage.getItem("dismissedInfoAnotadorContent") !== INFO_ANOTADOR
  );

  const dismissAlert = () => {
    showInfoAlert = false;
    localStorage.setItem("dismissedInfoAnotadorContent", INFO_ANOTADOR);
  };

  let isLoading = $state(false);
  let showFieldErrors = $state(false);

  // --- Último registro guardado (popup temporal) ---
  interface LastSavedInfo {
    fecha: string;
    docente: string;
    materia: string;
    grado: string;
    anotacion: string;
    timestamp: string;
  }
  let lastSaved: LastSavedInfo | null = $state(null);
  let lastSavedVisible = $state(false);

  const showLastSaved = (info: LastSavedInfo) => {
    lastSaved = info;
    lastSavedVisible = true;
  };

  let missingFields = $derived((() => {
    const fields: string[] = [];
    if (!formData.fecha) fields.push("fecha");
    if (!formData.docente) fields.push("docente");
    if (!formData.materia) fields.push("materia");
    if (!formData.grado) fields.push("grado");
    if (!formData.horas) fields.push("horas");
    const hasSelectedAnotacion = Object.values(anotacionGrupos).flat().some((o) => o.selected);
    if (!hasSelectedAnotacion) fields.push("anotacion");
    return fields;
  })());

  // --- Filtros ---
  let showFilter = $state(false);
  let showReportGenerator = $state(false);

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
  let showFeatureAlert = $state(true); // Control inmediato del popup

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

  let styles = $derived({
    bg: "rgb(var(--bg-primary))",
    text: "rgb(var(--text-primary))",
    label: "rgb(var(--text-secondary))",
    border: "rgb(var(--border-primary))",
    placeholder: "rgb(var(--text-muted))",
    icon: "rgb(var(--text-muted))",
    cardBg: "rgb(var(--card-bg))",
    cardBorder: "rgb(var(--card-border))",
    inputBg: "rgb(var(--bg-secondary))",
  });

  const toggleTheme = () => {
    theme.update((t) => {
      if (t === "light") return "dim";
      if (t === "dim") return "dark";
      return "light";
    });
  };

  let hasSelectedAnotacion = $derived(
    Object.values(anotacionGrupos).flat().some((o) => o.selected)
  );

  let isFormValid = $derived(
    formData.fecha &&
    formData.docente &&
    formData.materia &&
    formData.grado &&
    formData.horas &&
    hasSelectedAnotacion
  );

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

      if (!formData.docente) {
        const match = findMatchingDocente(docentes, $docenteName);
        if (match) formData.docente = match;
      }

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

      showLastSaved({
        fecha: formData.fecha,
        docente: formData.docente,
        materia: formData.materia,
        grado: formData.grado,
        anotacion: anotacionFinal,
        timestamp: new Date().toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' }),
      });

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

      // Limpiar borrador guardado
      clearFormDraft();

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

  // --- Keyboard Shortcuts ---
  const handleKeydown = (e: KeyboardEvent) => {
    if (e.ctrlKey || e.metaKey) {
      if (e.key === 'Enter') {
        e.preventDefault();
        const form = document.querySelector('form');
        if (form) form.requestSubmit();
      } else if (e.key === 'n' && !isLoading) {
        e.preventDefault();
        formData = {
          fecha: new Date().toLocaleDateString('en-CA'),
          docente: formData.docente,
          materia: "",
          grado: "",
          horas: "",
          anotacion: "",
          observacion: "",
        };
        for (const cat in anotacionGrupos) {
          anotacionGrupos[cat] = anotacionGrupos[cat].map((o) => ({ ...o, selected: false }));
        }
        clearFormDraft();
      }
    }
  };
</script>

<svelte:window onkeydown={handleKeydown} />

<ModuleHeader title="Anotador de Clase" subtitle="Seguimiento Ágil" {onBack} />

{#if !isOnline}
  <div 
    class="mx-4 mt-2 px-4 py-2 rounded-lg flex items-center gap-2 bg-amber-100 dark:bg-amber-900/30 border border-amber-300 dark:border-amber-700"
  >
    <WifiOff class="w-4 h-4 text-amber-600 dark:text-amber-400" />
    <span class="text-sm text-amber-800 dark:text-amber-200">Sin conexión - los datos se guardarán cuando se restablezca</span>
  </div>
{/if}

{#if showRestoreDraft && !draftRestored}
  <div 
    class="mx-4 mt-2 px-4 py-3 rounded-xl border flex items-center justify-between gap-3 animate-pulse"
    style="background-color: rgb(var(--bg-secondary)); border-color: rgb(var(--accent-primary));"
  >
    <div class="flex items-center gap-3">
      <div 
        class="p-2 rounded-lg"
        style="background-color: rgb(var(--accent-primary)); color: white;"
      >
        <FileText class="w-4 h-4" />
      </div>
      <div>
        <p class="text-sm font-medium" style="color: rgb(var(--text-primary));">
          Borrador guardado encontrado
        </p>
        <p class="text-xs" style="color: rgb(var(--text-secondary));">
          ¿Deseas restaurar tu trabajo anterior?
        </p>
      </div>
    </div>
    <div class="flex items-center gap-2">
      <button
        onclick={dismissDraft}
        class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-colors hover:bg-black/5"
        style="border-color: rgb(var(--border-primary)); color: rgb(var(--text-secondary));"
      >
        Descartar
      </button>
      <button
        onclick={restoreDraft}
        class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors"
        style="background-color: rgb(var(--accent-primary)); color: white;"
      >
        Restaurar
      </button>
    </div>
  </div>
{/if}

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
      <div class="flex flex-wrap justify-center lg:flex-col gap-3 w-full">
        <button
          onclick={openSheets}
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
          onclick={openFilters}
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
            onclick={openReportGenerator}
            class="inline-flex items-center justify-center gap-2 px-3 lg:px-4 py-2 lg:py-3 border rounded-lg transition-all duration-200 hover:bg-black/5 dark:hover:bg-white/5"
          style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
          title="Generar reportes PDF"
        >
          <FileText class="w-5 h-5" />
          <span class="text-sm font-medium hidden lg:inline">Reportes PDF</span>
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
             onclick={dismissAlert}
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

       <form onsubmit={handleSubmit} novalidate class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="space-y-2">
            <DatePicker
              id="fecha"
              label="Fecha"
              bind:value={formData.fecha}
              placeholder="Seleccione fecha"
              hasError={showFieldErrors && missingFields.includes('fecha')}
            />
          </div>

          <div class="space-y-2">
            <SelectField
              id="docente"
              label="Docente"
              bind:value={formData.docente}
              options={docentes.map(d => ({ value: d, label: d }))}
              placeholder={isLoadingDocentes ? "Cargando..." : "Seleccione docente"}
              selectType="docente"
              isLoading={isLoadingDocentes}
              hasError={showFieldErrors && missingFields.includes('docente')}
            />
          </div>

          <div class="space-y-2">
            <SelectField
              id="materia"
              label="Asignatura"
              bind:value={formData.materia}
              options={materiasSorted.map(m => ({ value: m.materia, label: m.materia }))}
              placeholder={isLoadingMaterias ? "Cargando..." : "Seleccione asignatura"}
              selectType="materia"
              isLoading={isLoadingMaterias}
              hasError={showFieldErrors && missingFields.includes('materia')}
            />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-2">
            <SelectField
              id="grado"
              label="Grado"
              bind:value={formData.grado}
              options={filteredGrados.map(g => ({ 
                value: g, 
                label: g.replace(/0(\d)$/, "°$1").replace(/(\d{1,2})0(\d)/, "$1°$2") 
              }))}
              placeholder={isLoadingEstudiantes ? "Cargando..." : "Seleccione grado"}
              selectType="grado"
              isLoading={isLoadingEstudiantes}
              hasError={showFieldErrors && missingFields.includes('grado')}
            />
          </div>

          <div class="space-y-2">
            <div class="flex items-center gap-2">
              <label
                for="horas"
                class="block text-sm font-medium"
                style="color: {styles.label};">Horas</label
              >
              <Tooltip title="Horas de clase" content="Seleccione el número de períodos de clase">
                <span class="text-xs" style="color: rgb(var(--text-muted));">1 hora = 1 período de clase</span>
              </Tooltip>
            </div>
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
                 onclick={() => (searchTerm = "")}
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

<div class="space-y-6">
          {#if isLoadingOpciones}
            <div class="space-y-4">
              {#each [1, 2, 3] as _}
                <div class="rounded-xl border p-4" style="background-color: rgb(var(--card-bg)); border-color: rgb(var(--border-primary));">
                  <Skeleton height="32px" width="200px" rounded="full" />
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <Skeleton height="140px" rounded="lg" />
                    <Skeleton height="140px" rounded="lg" />
                  </div>
                </div>
              {/each}
            </div>
          {:else}
            <div class="space-y-4">
              {#each sortedFilteredEntries as [categoria, opciones]}
                {@const catColor = getCategoryColor(categoria)}
                {@const filteredCount = opciones.length}
                <div
                  class="rounded-xl border overflow-hidden transition-all duration-200"
                  style="background-color: rgb(var(--card-bg)); border-color: rgb(var(--border-primary));"
                >
                  <Accordion
                    title={categoria}
                    isExpanded={expandedCategories[categoria]}
                    onToggle={() => toggleCategory(categoria)}
                    color={catColor}
                    count={filteredCount}
                    showCount={!!searchTerm}
                    highlighted={categoria === highlightedCategory}
                  >
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 pt-2">
                      {#each opciones as opcion}
                        <CheckboxCard
                          bind:checked={opcion.selected}
                          onchange={() => toggleAnotacionSeleccion(categoria, opcion.text)}
                          color={catColor}
                          bind:text={opcion.text}
                        />
                      {/each}
                    </div>
                  </Accordion>
                </div>
              {/each}
            </div>
          {/if}
        </div>

        <div class="space-y-2">
          <div class="flex items-center gap-2">
            <label
              for="observacion"
              class="block text-sm font-medium"
              style="color: {styles.label};">Observación</label
            >
            <Tooltip title="Observaciones adicionales" content="Información extra que deseas registrar sobre el estudiante o la situación">
              <span class="text-xs" style="color: rgb(var(--text-muted));">Opcional</span>
            </Tooltip>
          </div>
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
            class="group relative w-16 h-16 rounded-full transition-all duration-300 hover:scale-105 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
            aria-label="Guardar Anotación"
          >
            <!-- Shadow/elevation -->
            <span class="absolute inset-0 rounded-full bg-indigo-600 blur-xl opacity-50 group-hover:opacity-70 transition-opacity duration-300"></span>
            
            <!-- Main button -->
            <span 
              class="absolute inset-0.5 rounded-full flex items-center justify-center transition-all duration-300 {isLoading ? 'bg-indigo-400' : 'bg-indigo-600 group-hover:bg-indigo-500'}"
            >
              {#if isLoading}
                <Loader2 class="w-7 h-7 text-white animate-spin" />
              {:else}
                <!-- Paper plane icon with glow -->
                <span class="relative">
                  <span class="absolute inset-0 bg-white/30 blur-md rounded-full"></span>
                  <Send class="w-7 h-7 text-white transform rotate-45" />
                </span>
              {/if}
            </span>
            
            <!-- Ring effect on hover -->
            <span class="absolute inset-0 rounded-full border-2 border-indigo-400/0 group-hover:border-indigo-400/50 transition-all duration-300"></span>
          </button>
        </div>
      </form>
    </div>
  </main>
</div>

{#if showFilter}
  <SlideOver bind:isOpen={showFilter} onClose={closeFilters} title="Filtrar anotaciones" size="lg">
    <AnotadorFilter onClose={closeFilters} selectedDocente={formData.docente} />
  </SlideOver>
{/if}

{#if showReportGenerator}
  <ReportGenerator
    onClose={closeReportGenerator}
    initialDocente={formData.docente}
  />
{/if}

{#if lastSaved && lastSavedVisible}
  <div
    class="fixed bottom-4 right-4 z-50 max-w-xs w-full rounded-xl shadow-lg border p-4 transition-all duration-500"
    style="background-color: rgb(var(--card-bg)); border-color: rgb(var(--card-border));"
    class:opacity-0={!lastSavedVisible}
    class:translate-y-2={!lastSavedVisible}
  >
    <div class="flex items-start gap-3">
      <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
        <span class="text-green-600 text-sm">&#10003;</span>
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-xs font-semibold" style="color: rgb(var(--text-primary));">Última anotación guardada</p>
        <p class="text-[11px] mt-1 opacity-70" style="color: rgb(var(--text-secondary));">
          {lastSaved.fecha} &bull; {lastSaved.grado} &bull; {lastSaved.materia}
        </p>
        <p class="text-[11px] truncate opacity-60" style="color: rgb(var(--text-secondary));" title={lastSaved.anotacion}>
          {lastSaved.anotacion}
        </p>
        <p class="text-[10px] mt-1 opacity-40" style="color: rgb(var(--text-secondary));">{lastSaved.timestamp}</p>
      </div>
         <button
           class="text-xs opacity-40 hover:opacity-100 transition-opacity"
           style="color: rgb(var(--text-secondary));"
           onclick={() => { lastSavedVisible = false; }}
         >&times;</button>
    </div>
  </div>
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
