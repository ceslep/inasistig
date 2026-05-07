<script lang="ts">
  import { onMount, onDestroy } from "svelte";
  import Swal from "sweetalert2";
  import {
    saveInasistencias,
    getDocentes,
    getMaterias,
    getEstudiantes,
  } from "../../api/service";
  import {
    SPREADSHEET_ID,
    WORKSHEET_TITLE,
    INFO_INASISTENCIA,
    URL_LOCKER_STUDIO,
  } from "../constants";

  const FEATURE_MESSAGE = "¡Nueva función de filtrado avanzado disponible!";
  const FEATURE_MESSAGE_REPORT =
    "¡Nueva función de reporte en Excel disponible!";

  import { theme } from "../lib/themeStore";
  import { docenteName, findMatchingDocente } from "../lib/authStore";
  import { useDraftSave, type InasistenciaDraftData } from "../lib/useDraftSave";
  import { Cloud, Filter, FileDown, Eye, BarChart3, LayoutGrid, Moon, Sun, CloudMoon, Info, X, Clock, ChevronDown, Pencil, Loader2, Check, Send } from '@lucide/svelte';
  import eieLogo from "../assets/eie.png";
  import InasistenciaFilter from "./InasistenciaFilter.svelte";
  import ReportGeneratorInas from "./ReportGeneratorInas.svelte";
  import ReportGeneratorInasView from "./ReportGeneratorInasView.svelte";
  import FeaturePopup from "./FeaturePopup.svelte";
  import ModuleHeader from "./ModuleHeader.svelte";
  import { SelectField, DatePicker } from './anotador';

  interface Props {
    onBack: () => void;
  }

  const { onBack }: Props = $props();

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
    observaciones: string;
  }

  // --- Estado de datos ---
  let docentes: string[] = $state([]);
  let materias: Materia[] = $state([]);
  let estudiantes: Estudiante[] = $state([]);

  let isLoadingDocentes = $state(false);
  let isLoadingMaterias = $state(false);
  let isLoadingEstudiantes = $state(false);

  // --- Formulario ---
  let formData = $state({
    docente: localStorage.getItem("lastDocente") || "",
    materia: "",
    horas: "",
    grado: "",
    fecha: new Date().toLocaleDateString('en-CA'),
    observaciones: "",
  });

  let inasistencias: Inasistencia[] = $state([]);
  let individualHours: Record<string, string> = $state({});
  let openObservations: Record<string, boolean> = $state({});
  let isLoading = $state(false);
  let message = $state("");
  let showFieldErrors = $state(false);

  // --- Último registro guardado (popup temporal) ---
  interface LastSavedInfo {
    fecha: string;
    docente: string;
    materias: string[];
    grado: string;
    cantidad: number;
    timestamp: string;
  }
  let lastSaved: LastSavedInfo | null = $state(null);
  let lastSavedVisible = $state(false);

  const showLastSaved = (info: LastSavedInfo) => {
    lastSaved = info;
    lastSavedVisible = true;
  };

  let missingFields = $derived.by(() => {
    const fields: string[] = [];
    if (!formData.docente) fields.push("docente");
    if (!formData.fecha) fields.push("fecha");
    if (!formData.grado) fields.push("grado");
    if (docenteHasDash) {
      if (selectedMaterias.length === 0) fields.push("materias");
      else {
        const sinHoras = selectedMaterias.filter(m => !m.horas);
        if (sinHoras.length > 0) fields.push("horas");
      }
    } else {
      if (!formData.materia) fields.push("materia");
      if (!formData.horas) fields.push("horas");
    }
    return fields;
  });

  // Materias múltiples para docente con "-"
  interface MateriaHoras {
    materia: string;
    horas: string;
  }
  let selectedMaterias: MateriaHoras[] = $state([]);

  // Verificar si el docente tiene "-"
  let docenteHasDash = $derived(formData.docente.includes("-"));

  // --- Filtros ---
  let showFilter = $state(false);
  let showReportGenerator = $state(false);
  let showOnlineReport = $state(false);

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

  const openOnlineReport = () => {
    showOnlineReport = true;
  };
  const closeOnlineReport = () => {
    showOnlineReport = false;
  };

  // --- Estado para Report Generator ---
  let reportGeneratorLoading = $state(false);

  // --- Borradores ---
  const { saveDraft, loadDraft, clearDraft, hasDraft } = useDraftSave('inasistencia_draft');
  let draftRestored = $state(false);
  let showDraftBanner = $state(false);

  $effect(() => {
    if (draftRestored) return;
    const hasContent = formData.docente || formData.materia || formData.grado || inasistencias.length > 0;
    if (hasContent) {
      saveDraft({ formData, inasistencias });
    }
  });

  $effect(() => {
    if (!draftRestored && hasDraft()) {
      showDraftBanner = true;
    }
  });

  const restoreDraft = () => {
    const draft = loadDraft() as InasistenciaDraftData | null;
    if (draft && 'inasistencias' in draft) {
      formData = { ...formData, ...draft.formData };
      inasistencias = draft.inasistencias || [];
      draftRestored = true;
      showDraftBanner = false;
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
    showDraftBanner = false;
    clearDraft();
    draftRestored = true;
  };

  // --- Keyboard shortcuts ---
  const handleKeydown = (e: KeyboardEvent) => {
    if (e.ctrlKey || e.metaKey) {
      if (e.key === 'Enter') {
        e.preventDefault();
        const form = document.querySelector('form');
        if (form && !isLoading) form.requestSubmit();
      } else if (e.key === 'n' && !isLoading) {
        e.preventDefault();
        formData = {
          ...formData,
          grado: "",
          fecha: new Date().toLocaleDateString('en-CA'),
          observaciones: "",
          horas: "",
        };
        inasistencias = [];
        clearDraft();
      }
    }
  };

  // --- Alertas Dismissibles ---
  let showInfoAlert = $state(
    INFO_INASISTENCIA &&
    localStorage.getItem("dismissedInfoInasistenciaContent") !==
      INFO_INASISTENCIA,
  );

  let showFeatureAlert = $state(true); // Control inmediato del popup for filters
  let showFeatureAlertReport = $state(true); // Control inmediato del popup for report

  function shouldShowFeatureAlert(featureKey: string) {
    // FORZAR MOSTRAR PARA DESARROLLO - Cambiar a false en producción
    const DEBUG_FORCE_SHOW = true;

    if (DEBUG_FORCE_SHOW) {
      return true;
    }

    const dismissed = localStorage.getItem(`dismissed${featureKey}Alert`);

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

  const dismissInfoAlert = () => {
    localStorage.setItem("dismissedInfoInasistenciaContent", INFO_INASISTENCIA);
    showInfoAlert = false;
  };

  const dismissFeatureAlert = () => {
    localStorage.setItem(
      "dismissedFeatureAlertFilters",
      new Date().toISOString(),
    );
    showFeatureAlert = false; // Cerrar el popup inmediatamente
  };

  const tryFeatureNow = () => {
    // Cerrar el popup
    localStorage.setItem(
      "dismissedFeatureAlertFilters",
      new Date().toISOString(),
    );
    // Abrir el panel de filtros
    openFilters();
  };

  const dismissFeatureAlertReport = () => {
    localStorage.setItem(
      "dismissedFeatureAlertReport",
      new Date().toISOString(),
    );
    showFeatureAlertReport = false;
  };

  const tryFeatureNowReport = () => {
    // Cerrar el popup
    localStorage.setItem(
      "dismissedFeatureAlertReport",
      new Date().toISOString(),
    );
    // Abrir el generador de reportes
    openReportGenerator();
    showFeatureAlertReport = false;
  };

  // Forzar reset del mensaje para desarrollo (comentar en producción)
  const resetFeatureAlert = () => {
    localStorage.removeItem("dismissedFeatureAlertFilters");
    localStorage.removeItem("dismissedFeatureAlertReport");
  };

  // --- Persistencia de Materias por Docente ---
  let docenteMaterias: Record<string, string[]> = $state(JSON.parse(
    localStorage.getItem("docenteMaterias") || "{}",
  ));

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

  // No longer saving reactively to allow "normal" operation until first success

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

  const getSheetsUrl = () => {
    return `https://docs.google.com/spreadsheets/d/${SPREADSHEET_ID}`;
  };

  const openSheets = () => {
    window.open(getSheetsUrl(), "_blank");
  };

  const openLocker = () => {
    window.open(URL_LOCKER_STUDIO, "_blank");
  };

  let estudiantesFiltrados = $derived(
    formData.grado
      ? estudiantes.filter((e) => e.grado.toString() === formData.grado)
      : []
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
      : [...new Set(estudiantes.map((e) => e.grado.toString()))].filter((g) =>
          !g.includes("-")
        )
  );

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
      obligaHoras: true,
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
      obligaHoras: true,
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
      obligaHoras: false,
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
      obligaHoras: true,
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
      obligaHoras: true,
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
      obligaHoras: false,
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
      obligaHoras: false,
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
      obligaHoras: false,
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
      obligaHoras: false,
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
      obligaHoras: true,
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
      obligaHoras: false,
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
      obligaHoras: true,
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
      obligaHoras: true,
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
      obligaHoras: false,
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
      obligaHoras: false,
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
      obligaHoras: false,
    },
    {
      value: "Retirado por el acudiente",
      label: "Retirado por el acudiente",
      icon: "👨",
      bgColor: "bg-green-50",
      borderColor: "border-green-300",
      textColor: "text-green-700",
      darkBgColor: "dark:bg-green-950",
      darkBorderColor: "dark:border-green-800",
      darkTextColor: "dark:text-green-300",
      obligaHoras: false,
    },
  ];

  // --- Funciones de Tema ---
  const toggleTheme = () => {
    theme.update((t) => {
      if (t === "light") return "dim";
      if (t === "dim") return "dark";
      return "light";
    });
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

      if (!formData.docente) {
        const match = findMatchingDocente(docentes, $docenteName);
        if (match) formData.docente = match;
      }
    } catch (error) {
      console.error("Error cargando datos iniciales:", error);
    } finally {
      isLoadingDocentes = false;
      isLoadingMaterias = false;
      isLoadingEstudiantes = false;
    }
  };

  // --- Manejadores del Formulario ---
  const removeInasistencia = (estudianteNombre: string, motivoValue: string) => {
    inasistencias = inasistencias.filter(
      (i) => !(i.nombre === estudianteNombre && i.motivo === motivoValue),
    );
  };

  const handleInasistenciaChange = (
    estudianteNombre: string,
    nuevoMotivo: string,
    nuevaHora?: string,
  ) => {
    if (nuevoMotivo === "" || nuevoMotivo === "Ignorar") {
      inasistencias = inasistencias.filter((i) => i.nombre !== estudianteNombre);
      return;
    }

    const selectedMotivo = motivos.find((m) => m.value === nuevoMotivo);
    if (!selectedMotivo) return;

    let hourToUse =
      nuevaHora || individualHours[estudianteNombre] || formData.horas;

    // Si el motivo no obliga horas, forzamos a "0"
    if (!selectedMotivo.obligaHoras) {
      hourToUse = "0";
    }

    if (selectedMotivo.obligaHoras) {
      // Si es un motivo que OBLIGA horas, reemplazamos cualquier otro que también obligue horas
      // pero mantenemos los que NO obligan horas
      inasistencias = inasistencias.filter((i) => {
        if (i.nombre !== estudianteNombre) return true;
        const m = motivos.find((mot) => mot.value === i.motivo);
        return m && !m.obligaHoras;
      });
    }

    // Verificar si ya tiene este motivo exacto
    const alreadyHasIt = inasistencias.some(
      (i) => i.nombre === estudianteNombre && i.motivo === nuevoMotivo,
    );

    if (!alreadyHasIt) {
      // Obtener observaciones existentes para este estudiante si las hay
      const existingObs =
        inasistencias.find((i) => i.nombre === estudianteNombre)
          ?.observaciones || "";

      inasistencias = [
        ...inasistencias,
        {
          nombre: estudianteNombre,
          motivo: nuevoMotivo,
          horas: hourToUse,
          observaciones: existingObs,
        },
      ];
    }
  };

  const handleIndividualObservationChange = (
    estudianteNombre: string,
    nuevaObs: string,
  ) => {
    inasistencias = inasistencias.map((i) => {
      if (i.nombre === estudianteNombre) {
        return { ...i, observaciones: nuevaObs };
      }
      return i;
    });
  };

  const toggleObservation = (estudianteNombre: string) => {
    openObservations[estudianteNombre] = !openObservations[estudianteNombre];
  };

  const handleIndividualHourChange = (
    estudianteNombre: string,
    nuevaHora: string,
  ) => {
    individualHours[estudianteNombre] = nuevaHora;
    inasistencias = inasistencias.map((i) => {
      if (i.nombre === estudianteNombre) {
        const m = motivos.find((mot) => mot.value === i.motivo);
        if (m && m.obligaHoras) {
          return { ...i, horas: nuevaHora };
        }
      }
      return i;
    });
  };

  const handleSubmit = async (event: Event) => {
    event.preventDefault();
    if (isLoading) return;

    showFieldErrors = true;

    const camposFaltantes: { id: string; label: string }[] = [];
    if (!formData.docente) camposFaltantes.push({ id: "docente", label: "Docente" });
    if (!formData.fecha) camposFaltantes.push({ id: "fecha", label: "Fecha" });
    if (!formData.grado) camposFaltantes.push({ id: "grado", label: "Grado" });

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
      if (!formData.materia) camposFaltantes.push({ id: "materia", label: "Materia" });
      if (!formData.horas) camposFaltantes.push({ id: "horas", label: "Horas" });
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

    if (inasistencias.length === 0) {
      await Swal.fire({
        icon: "warning",
        title: "Sin inasistencias",
        text: "Debe seleccionar al menos un estudiante con un motivo de inasistencia",
        confirmButtonColor: "#6366f1",
      });
      return;
    }

    isLoading = true;
    try {
      const currentTimestamp = new Date().toISOString();

      // Validar materias seleccionadas
      const materiasToSave: MateriaHoras[] = docenteHasDash 
        ? selectedMaterias 
        : [{ materia: formData.materia, horas: formData.horas }];
      
      if (docenteHasDash && selectedMaterias.length === 0) {
        await Swal.fire({
          icon: "warning",
          title: "Atención",
          text: "Seleccione al menos una materia",
          confirmButtonColor: "#6366f1",
        });
        return;
      }

      // Validar que cada materia tenga horas
      const materiasSinHoras = materiasToSave.filter(m => !m.horas);
      if (materiasSinHoras.length > 0) {
        await Swal.fire({
          icon: "warning",
          title: "Atención",
          text: `Seleccione horas para: ${materiasSinHoras.map(m => m.materia).join(", ")}`,
          confirmButtonColor: "#6366f1",
        });
        return;
      }

      // Crear payload para cada materia seleccionada
      const inasistenciasPayload: (string | number)[][] = [];
      const filteredInasistencias = inasistencias.filter(
        (item) => item.motivo && item.motivo !== "Ignorar",
      );

      for (const materiaData of materiasToSave) {
        for (const item of filteredInasistencias) {
          inasistenciasPayload.push([
            currentTimestamp,
            formData.docente,
            formData.fecha,
            materiaData.horas,
            materiaData.materia,
            item.motivo,
            formData.grado,
            item.nombre,
            item.observaciones || formData.observaciones,
          ]);
        }
      }

      const result = await saveInasistencias({
        spreadsheetId: SPREADSHEET_ID,
        worksheetTitle: WORKSHEET_TITLE,
        inasistencias: inasistenciasPayload as any,
      });

      const isOffline = result?.offline === true;

      // Persistir las materias para el docente solo después del éxito
      for (const materiaData of materiasToSave) {
        saveMateriaForDocente(formData.docente, materiaData.materia);
      }

      showLastSaved({
        fecha: formData.fecha,
        docente: formData.docente,
        materias: materiasToSave.map(m => m.materia),
        grado: formData.grado,
        cantidad: inasistenciasPayload.length,
        timestamp: new Date().toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' }),
      });

      await Swal.fire({
        icon: isOffline ? "warning" : "success",
        title: isOffline ? "Guardado offline" : "¡Éxito!",
        text: isOffline
          ? `${inasistenciasPayload.length} inasistencia(s) guardada(s) en cola. Se enviarán al recuperar conexión.`
          : `${inasistenciasPayload.length} inasistencia(s) registrada(s) exitosamente`,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
        position: "top-end",
        toast: true,
      });

      formData = {
        ...formData,
        grado: "",
        fecha: new Date().toLocaleDateString('en-CA'),
        observaciones: "",
        horas: "",
      };
      inasistencias = [];
      individualHours = {};
      openObservations = {};
      selectedMaterias = [];
      showFieldErrors = false;
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

  onMount(() => {
    loadData();
    // Inicializar el estado del popup de feature
    showFeatureAlert = shouldShowFeatureAlert("FeatureAlertFilters");
    showFeatureAlertReport = shouldShowFeatureAlert("FeatureAlertReport");
  });
</script>

<svelte:window onkeydown={handleKeydown} />

<ModuleHeader title="Registro Diario" subtitle="Gestión de Asistencia" {onBack} />

<div
  class="min-h-screen flex flex-col lg:flex-row transition-colors duration-200"
  style="background-color: {styles.bg};"
>
  <!-- Sidebar (Escritorio) / Header (Móvil) -->
  <aside
    class="w-full lg:w-80 lg:h-screen lg:sticky lg:top-0 border-b lg:border-b-0 lg:border-r transition-colors duration-200 p-6 lg:p-8 flex flex-col flex-shrink-0 z-40"
    style="background-color: {styles.cardBg}; border-color: {styles.cardBorder};"
  >
    <div
      class="flex flex-col lg:flex-col items-center justify-between lg:justify-start gap-4 lg:gap-8"
    >
      <!-- Botones de Acción -->
      <div class="flex flex-wrap justify-center lg:flex-col gap-3 w-full">
        <!-- Theme Toggle -->
        <button
          onclick={toggleTheme}
          class="inline-flex items-center justify-center gap-2 px-3 lg:px-4 py-2 lg:py-3 border rounded-lg transition-all duration-200 hover:bg-black/5 dark:hover:bg-white/5"
          style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
          title={$theme === 'light' ? 'Modo oscuro' : 'Modo claro'}
        >
          {#if $theme === 'dark'}
            <Sun class="w-5 h-5" />
          {:else}
            <Moon class="w-5 h-5" />
          {/if}
          <span class="text-sm font-medium hidden lg:inline">Tema</span>
        </button>

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
          class="inline-flex items-center justify-center gap-2 px-3 lg:px-4 py-2 lg:py-3 border rounded-lg transition-all duration-200 hover:bg-black/5 dark:hover:bg-white/5 relative"
          style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
          title="Abrir Looker Studio"
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

        <!-- Botón de Reportes Excel -->
        <button
          id="report-button-target"
          onclick={openReportGenerator}
          class="inline-flex items-center justify-center gap-2 px-3 lg:px-4 py-2 lg:py-3 border rounded-lg transition-all duration-200 hover:bg-black/5 dark:hover:bg-white/5 relative"
          style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
          title="Generar reportes Excel"
        >
          <FileDown class="w-5 h-5" />
          <span class="text-sm font-medium hidden lg:inline">Reporte Excel</span>
          {#if showFeatureAlertReport}
            <div
              class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full animate-ping"
            ></div>
            <div
              class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full"
            ></div>
          {/if}
        </button>

        <!-- Botón de Reporte Online -->
         <button
           onclick={openOnlineReport}
           class="inline-flex items-center justify-center gap-2 px-3 lg:px-4 py-2 lg:py-3 border rounded-lg transition-all duration-200 hover:bg-black/5 dark:hover:bg-white/5"
          style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
          title="Ver reporte online"
        >
          <Eye class="w-5 h-5" />
          <span class="text-sm font-medium hidden lg:inline">Ver Online</span>
        </button>

         <!-- Botón de Locker Studio -->
         <button
           onclick={openLocker}
           class="inline-flex items-center justify-center gap-2 px-3 lg:px-4 py-2 lg:py-3 border rounded-lg transition-all duration-200 hover:bg-black/5 dark:hover:bg-white/5"
          style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
          title="Abrir Looker Studio"
        >
          <BarChart3 class="w-5 h-5" />
          <span class="text-sm font-medium hidden lg:inline">Looker Studio</span
          >
        </button>



      </div>
    </div>
  </aside>

  <!-- Contenido Principal -->
  <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
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
              {INFO_INASISTENCIA}
            </p>
          </div>
           <button
             onclick={dismissInfoAlert}
             class="absolute top-3 right-3 p-1.5 rounded-lg hover:bg-green-100 dark:hover:bg-indigo-700 text-green-600 hover:text-green-800 dark:text-indigo-400 dark:hover:text-indigo-100 transition-colors"
            aria-label="Cerrar alerta"
          >
            <X class="w-4 h-4" />
          </button>
        </div>
      {/if}

      <!-- Banner de borrador restaurado -->
      {#if showDraftBanner && !draftRestored}
        <div
          class="mb-4 px-4 py-3 rounded-xl border flex items-center justify-between gap-3 animate-pulse"
          style="background-color: rgb(var(--bg-secondary)); border-color: rgb(var(--accent-primary));"
        >
          <div class="flex items-center gap-3">
            <div
              class="p-2 rounded-lg"
              style="background-color: rgb(var(--accent-primary)); color: white;"
            >
              <FileDown class="w-4 h-4" />
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

      <!-- Feature Popup Component -->
      <FeaturePopup
        featureMessage={FEATURE_MESSAGE}
        description="Ahora puedes filtrar tus datos de forma avanzada. ¡Pruébalo!"
        onTryNow={tryFeatureNow}
        onDismiss={dismissFeatureAlert}
        targetSelector="#filter-button-target"
        showPopup={false}
        colorTheme="blue"
      />

      <!-- Feature Popup for Report Generator -->
      <FeaturePopup
        featureMessage={FEATURE_MESSAGE_REPORT}
        description="Ahora puedes generar reportes de inasistencias en formato Excel. ¡Descarga y analiza tus datos!"
        onTryNow={tryFeatureNowReport}
        onDismiss={dismissFeatureAlertReport}
        targetSelector="#report-button-target"
        showPopup={false}
        colorTheme="green"
      />

       <form onsubmit={handleSubmit} class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

          <div class="space-y-2 {docenteHasDash ? 'lg:col-span-2' : ''}">
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
                         onchange={(e) => {
                           if (e.currentTarget.checked) {
                             selectedMaterias = [...selectedMaterias, { materia: materia.materia, horas: "" }];
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
              <SelectField
                id="materia"
                label="Materia"
                bind:value={formData.materia}
                options={materiasSorted.map(m => ({ 
                  value: m.materia, 
                  label: (docenteMaterias[formData.docente]?.includes(m.materia) ? '⭐ ' : '') + m.materia 
                }))}
                placeholder={isLoadingMaterias ? "Cargando..." : "Seleccione materia"}
                selectType="materia"
                isLoading={isLoadingMaterias}
                hasError={showFieldErrors && missingFields.includes('materia')}
              />
            {/if}
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
              class="space-y-2 max-h-[700px] overflow-y-auto border rounded-2xl p-4 bg-zinc-50/50 dark:bg-zinc-900/50 backdrop-blur-sm scrollbar-thin scrollbar-thumb-zinc-300 dark:scrollbar-thumb-zinc-600"
              style="border-color: {styles.border};"
            >
              {#each estudiantesFiltrados as estudiante (estudiante.nombre)}
                {@const studentInasistencias = inasistencias.filter(
                  (i) => i.nombre === estudiante.nombre,
                )}
                {@const hasAnyInasistencia = studentInasistencias.length > 0}
                {@const hasObligaHoras = studentInasistencias.some((i) => {
                  const m = motivos.find((mot) => mot.value === i.motivo);
                  return m && m.obligaHoras;
                })}

                <div
                  class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border rounded-xl transition-all duration-200 gap-4"
                  style="background-color: {styles.bg}; border-color: {hasAnyInasistencia
                    ? 'transparent'
                    : styles.border};"
                  class:ring-2={hasAnyInasistencia}
                  class:ring-indigo-500={hasAnyInasistencia}
                >
                  <div class="flex-1 min-w-0">
                    <span
                      class="text-base font-medium block"
                      style="color: {styles.text};">{estudiante.nombre}</span
                    >
                    <div class="flex flex-wrap gap-2 mt-2">
                      {#each studentInasistencias as inas}
                        {@const m = motivos.find((mot) => mot.value === inas.motivo)}
                        {#if m}
                          <span
                            class={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border ${m.bgColor} ${m.textColor} ${m.borderColor} ${m.darkBgColor} ${m.darkTextColor} ${m.darkBorderColor}`}
                          >
                            <span class="mr-1.5">{m.icon}</span>
                             {m.label}
                             <button
                               type="button"
                               onclick={() =>
                                 removeInasistencia(estudiante.nombre, inas.motivo)}
                               class="ml-1.5 hover:opacity-70 transition-opacity"
                              aria-label="Eliminar motivo"
                            >
                              <svg
                                class="w-3 h-3"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                              >
                                <path
                                  stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="3"
                                  d="M6 18L18 6M6 6l12 12"
                                />
                              </svg>
                            </button>
                          </span>
                        {/if}
                      {/each}
                    </div>
                  </div>

                  <div
                    class="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto"
                  >
                    <!-- Selector de Hora Individual -->
                    <div
                      class="relative w-full sm:w-32 transition-opacity duration-200"
                      class:opacity-40={!hasObligaHoras}
                    >
                      <select
                        value={studentInasistencias.find((i) => {
                          const m = motivos.find((mot) => mot.value === i.motivo);
                          return m && m.obligaHoras;
                        })?.horas ||
                          individualHours[estudiante.nombre] ||
                          formData.horas}
                          onchange={(e) =>
                            handleIndividualHourChange(
                              estudiante.nombre,
                              e.currentTarget.value,
                            )}
                        disabled={!hasObligaHoras}
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
                        value=""
                        onchange={(e) => {
                          handleInasistenciaChange(
                            estudiante.nombre,
                            e.currentTarget.value,
                          );
                          e.currentTarget.value = "";
                        }}
                        class="w-full appearance-none border rounded-lg px-4 py-2 pr-10 text-sm focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all outline-none"
                        style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
                      >
                        <option value="">Añadir motivo...</option>
                        <option value="Ignorar">🚫 Limpiar todo</option>
                        {#each motivos as motivo}
                          {@const isSelected = studentInasistencias.some(
                            (i) => i.motivo === motivo.value,
                          )}
                          <option value={motivo.value} disabled={isSelected}>
                            {motivo.icon} {motivo.label} {isSelected ? "✓" : ""}
                          </option>
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

                  {#if hasAnyInasistencia}
                    <div class="flex items-center gap-2">
                      <button
                        type="button"
                         onclick={() => toggleObservation(estudiante.nombre)}
                        class="p-2 rounded-lg transition-colors hover:bg-black/5 dark:hover:bg-white/5"
                        style="color: {studentInasistencias[0]?.observaciones
                          ? 'rgb(var(--text-primary))'
                          : styles.icon};"
                        title="Agregar observación"
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
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                          />
                        </svg>
                      </button>
                    </div>
                  {/if}
                </div>

                {#if hasAnyInasistencia && (openObservations[estudiante.nombre] || studentInasistencias[0]?.observaciones)}
                  <div class="px-4 pb-4 animate-fade-in">
                    <textarea
                      value={studentInasistencias[0]?.observaciones || ""}
                      oninput={(e) =>
                        handleIndividualObservationChange(
                          estudiante.nombre,
                          e.currentTarget.value,
                        )}
                      placeholder="Escribe una observación específica para este estudiante..."
                      rows="2"
                      class="w-full px-4 py-2 rounded-xl border focus:ring-2 focus:ring-indigo-500 transition-all outline-none resize-none text-sm"
                      style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
                    ></textarea>
                  </div>
                {/if}
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
            ></textarea>
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
    onclick={handleSubmit}
    disabled={isLoading}
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

{#if showFilter}
  <InasistenciaFilter
    onClose={closeFilters}
    selectedDocente={formData.docente}
  />
{/if}

{#if showReportGenerator}
  <ReportGeneratorInas
    onClose={closeReportGenerator}
    initialDocente={formData.docente}
  />
{/if}

{#if showOnlineReport}
  <ReportGeneratorInasView
    onClose={closeOnlineReport}
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
        <p class="text-xs font-semibold" style="color: rgb(var(--text-primary));">Último registro guardado</p>
        <p class="text-[11px] mt-1 opacity-70" style="color: rgb(var(--text-secondary));">
          {lastSaved.fecha} &bull; {lastSaved.grado} &bull; {lastSaved.cantidad} reg.
        </p>
        <p class="text-[11px] truncate opacity-60" style="color: rgb(var(--text-secondary));" title={lastSaved.materias.join(', ')}>
          {lastSaved.materias.join(', ')}
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

  @keyframes pulse-slow {
    0%,
    100% {
      opacity: 1;
      transform: scale(1);
    }
    50% {
      opacity: 0.95;
      transform: scale(1.01);
    }
  }
  .animate-pulse-slow {
    animation: pulse-slow 3s ease-in-out infinite;
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
