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
  import { theme } from "../lib/themeStore";
  import { docenteName, findMatchingDocente } from "../lib/authStore";
  import { isOnline, pendingCount } from "../lib/networkStore";
  import { useDraftSave, type DiarioDraftData } from "../lib/useDraftSave";
  import DiarioAnotacionOptions from "./DiarioAnotacionOptions.svelte";
  import ReportGeneratorDiario from "./ReportGeneratorDiario.svelte";
  import { Cloud, FileText, Info, X, Send, Loader2, Check, AlertTriangle, ChevronDown, ChevronUp, Wifi, WifiOff, Eraser, Sun, Moon, History } from '@lucide/svelte';
  import ModuleHeader from "./ModuleHeader.svelte";
  import { Skeleton } from "./ui";
  import { SelectField, DatePicker, DiarioHistory } from './anotador';

  interface DiarioProps {
    onBack: () => void;
  }

  const { onBack }: DiarioProps = $props();

  const TODAY_ISO = new Date().toLocaleDateString('en-CA');

  interface Estudiante {
    nombre: string;
    grado: number | string;
  }

  interface Materia {
    materia: string;
  }

  // --- Estado de datos ---
  let docentes: string[] = $state([]);
  let materias: Materia[] = $state([]);
  let estudiantes: Estudiante[] = $state([]);

  let isLoadingDocentes = $state(false);
  let isLoadingMaterias = $state(false);
  let isLoadingEstudiantes = $state(false);
  let isLoading = $state(false);
  let showFieldErrors = $state(false);

  let touched: Record<string, boolean> = $state({});
  const markTouched = (id: string) => {
    touched = { ...touched, [id]: true };
  };
  const showError = (id: string) =>
    (showFieldErrors || touched[id]) && missingFields.includes(id);

  let showAllMaterias = $state(true);

  // --- Borrador ---
  const { saveDraft, loadDraft, clearDraft, hasDraft } = useDraftSave('diario_draft');
  let draftRestored = $state(false);
  let showDraftBanner = $state(false);

  // --- Último registro guardado ---
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

  // Materias múltiples para docente con "-"
  interface MateriaHoras {
    materia: string;
    horas: string;
  }
  let selectedMaterias: MateriaHoras[] = $state([]);
  let selectedDiarioAnots: string[] = $state([]);

  // --- Formulario ---
  let formData = $state({
    fecha: TODAY_ISO,
    docente: localStorage.getItem("lastDocenteDiario") || "",
    materia: "",
    grado: "",
    horas: "",
  });

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

  $effect(() => {
    if (formData.docente) {
      localStorage.setItem("lastDocenteDiario", formData.docente);
    }
  });

  // Auto-guardado de borrador
  $effect(() => {
    if (draftRestored) return;
    const hasContent = formData.docente || formData.materia || formData.grado || selectedDiarioAnots.length > 0 || selectedMaterias.length > 0;
    if (hasContent) {
      saveDraft({ formData, selectedDiarioAnots, selectedMaterias });
    }
  });

  // Check for existing draft on mount
  $effect(() => {
    if (!draftRestored && hasDraft()) {
      showDraftBanner = true;
    }
  });

  const restoreDraft = () => {
    const draft = loadDraft() as DiarioDraftData | null;
    if (draft && 'formData' in draft) {
      formData = { ...formData, ...draft.formData };
      selectedDiarioAnots = draft.selectedDiarioAnots ?? [];
      selectedMaterias = draft.selectedMaterias ?? [];
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

  const discardDraft = () => {
    formData = {
      fecha: TODAY_ISO,
      docente: localStorage.getItem("lastDocenteDiario") || "",
      materia: "",
      grado: "",
      horas: "",
    };
    selectedDiarioAnots = [];
    selectedMaterias = [];
    showDraftBanner = false;
    draftRestored = false;
    clearDraft();
  };

  // Derived state
  let docenteHasDash = $derived(formData.docente.includes("-"));

  const getDocenteNumber = (docente: string): string | null => {
    const match = docente.match(/-(\d+)$/);
    return match ? match[1] : null;
  };

  let docenteNumber = $derived(getDocenteNumber(formData.docente));

  let filteredGrados = $derived(
    docenteNumber
      ? [...new Set(estudiantes.map((e) => e.grado.toString()))].filter((g) =>
          g.startsWith(`${docenteNumber}-`),
        )
      : [...new Set(estudiantes.map((e) => e.grado.toString()))].filter((g) => !g.includes('-'))
  );

  let missingFields = $derived((() => {
    const fields: string[] = [];
    if (!formData.fecha) fields.push("fecha");
    if (!formData.docente) fields.push("docente");
    if (!formData.grado) fields.push("grado");
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
      if (!formData.horas) fields.push("horas");
    }
    return fields;
  })());

  const FIELD_LABELS: Record<string, string> = {
    fecha: "Fecha",
    docente: "Docente",
    materia: "Asignatura",
    materias: "Materias",
    grado: "Grado",
    horas: "Horas",
    diario: "Diario de campo",
  };

  const FIELD_TARGET_ID: Record<string, string> = {
    fecha: "fecha",
    docente: "docente",
    materia: "materia",
    materias: "materias-multi",
    grado: "grado",
    horas: "horas",
    diario: "diario",
  };

  const focusField = (id: string) => {
    const el = document.getElementById(id);
    if (el) {
      el.scrollIntoView({ behavior: 'smooth', block: 'center' });
      if (typeof (el as HTMLElement).focus === 'function') {
        (el as HTMLElement).focus();
      }
    }
  };

  // --- Report Generator ---
  let showReportGenerator = $state(false);

  const openReportGenerator = () => {
    showReportGenerator = true;
  };
  const closeReportGenerator = () => {
    showReportGenerator = false;
  };

  // --- Historial ---
  let showDiarioHistory = $state(false);

  const openDiarioHistory = (event: MouseEvent) => {
    event.preventDefault();
    event.stopPropagation();
    showFieldErrors = false;
    showDiarioHistory = true;
  };
  const closeDiarioHistory = () => {
    showDiarioHistory = false;
  };

  interface DiarioData {
    timestamp: string;
    fecha: string;
    horas: string;
    docente: string;
    materia: string;
    grado: string;
    diarioCampo: string;
  }

  const loadFromDiarioHistory = (data: DiarioData) => {
    formData = {
      ...formData,
      fecha: data.fecha,
      grado: data.grado,
      materia: data.materia,
    };

    if (data.diarioCampo) {
      selectedDiarioAnots = data.diarioCampo.split(" | ").filter(Boolean);
    }

    draftRestored = true;
    clearDraft();
    showDraftBanner = false;

    Swal.fire({
      icon: 'success',
      title: 'Diario cargado',
      text: 'El diario se ha cargado. Puedes editarlo antes de guardar.',
      timer: 2000,
      showConfirmButton: false,
    });
  };

  // Ordenar docentes: el reciente al top
  let docentesSorted = $derived.by(() => {
    const last = localStorage.getItem("lastDocenteDiario") || "";
    if (!last || !docentes.includes(last)) return docentes;
    return [last, ...docentes.filter((d) => d !== last)];
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

  // --- Alertas Dismissibles ---
  let showInfoAlert = $state(
    INFO_DIARIO &&
    localStorage.getItem("dismissedInfoDiarioContent") !== INFO_DIARIO
  );

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

  // Theme toggle
  const toggleTheme = () => {
    theme.update((t) => {
      if (t === "light") return "dim";
      if (t === "dim") return "dark";
      return "light";
    });
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

  let initialLoading = $derived(
    (isLoadingDocentes && docentes.length === 0) ||
    (isLoadingMaterias && materias.length === 0) ||
    (isLoadingEstudiantes && estudiantes.length === 0)
  );

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

  const handleSubmit = async (event: Event) => {
    event.preventDefault();
    if (isLoading) return;

    showFieldErrors = true;

    const diarioCampoFinal = selectedDiarioAnots.join(" | ");

    if (missingFields.length > 0) {
      const firstMissing = missingFields[0];
      const targetId = FIELD_TARGET_ID[firstMissing] ?? firstMissing;
      focusField(targetId);
      return;
    }

    const materiasToSave: MateriaHoras[] = docenteHasDash
      ? selectedMaterias
      : [{ materia: formData.materia, horas: formData.horas }];

    const materiasMsg = docenteHasDash
      ? selectedMaterias.map(m => `${m.materia} (${m.horas}h)`).join(", ")
      : `${formData.materia} (${formData.horas}h)`;

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

      const result = await saveDiario({
        spreadsheetId: SPREADSHEET_ID_DIARIO,
        worksheetTitle: WORKSHEET_TITLE_DIARIO,
        datos: payload,
      });

      const isOffline = result?.offline === true;

      for (const materiaData of materiasToSave) {
        saveMateriaForDocente(formData.docente, materiaData.materia);
      }

      showLastSaved({
        fecha: formData.fecha,
        docente: formData.docente,
        materias: materiasToSave.map(m => m.materia),
        grado: formData.grado,
        cantidad: payload.length,
        timestamp: new Date().toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' }),
      });

      if (isOffline) {
        const registrosMsg = payload.length === 1
          ? "Diario de Campo guardado en cola. Se enviará al recuperar conexión."
          : `${payload.length} registros guardados en cola. Se enviarán al recuperar conexión.`;
        await Swal.fire({
          icon: "warning",
          title: "Guardado offline",
          text: registrosMsg,
          timer: 3000,
          timerProgressBar: true,
          showConfirmButton: false,
          position: "top-end",
          toast: true,
        });
      }

      formData = {
        fecha: TODAY_ISO,
        docente: formData.docente,
        materia: "",
        grado: formData.grado,
        horas: "",
      };

      selectedDiarioAnots = [];
      selectedMaterias = [];
      showFieldErrors = false;
      touched = {};
      clearDraft();
      showDraftBanner = false;
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

  let fabTitle = $derived(
    missingFields.length === 0
      ? "Listo para guardar"
      : `Faltan: ${missingFields.map((f) => FIELD_LABELS[f] ?? f).join(", ")}`
  );

  // Limpiar formulario manualmente
  const handleClearForm = async () => {
    const hasContent =
      selectedDiarioAnots.length > 0 ||
      selectedMaterias.length > 0 ||
      formData.materia ||
      formData.grado;
    if (!hasContent) return;
    const result = await Swal.fire({
      icon: "question",
      title: "¿Limpiar formulario?",
      text: "Se borrarán los campos y selecciones actuales.",
      showCancelButton: true,
      confirmButtonText: "Sí, limpiar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#ef4444",
      cancelButtonColor: "#64748b",
      reverseButtons: true,
      background: $theme === "light" ? "#fff" : "#1e293b",
      color: $theme === "light" ? "#1e293b" : "#f1f5f9",
    });
    if (result.isConfirmed) {
      discardDraft();
      showFieldErrors = false;
      touched = {};
    }
  };

  // Keyboard shortcuts: Ctrl+Enter para enviar, Ctrl+N para nuevo
  const handleKeydown = (e: KeyboardEvent) => {
    if (e.ctrlKey || e.metaKey) {
      if (e.key === 'Enter') {
        e.preventDefault();
        const form = document.getElementById("diario-form") as HTMLFormElement | null;
        if (form && !isLoading) form.requestSubmit();
      } else if (e.key === 'n' && !isLoading) {
        e.preventDefault();
        formData = {
          fecha: TODAY_ISO,
          docente: formData.docente,
          materia: "",
          grado: "",
          horas: "",
        };
        selectedDiarioAnots = [];
        selectedMaterias = [];
        clearDraft();
      }
    }
  };

  // Auto-focus inteligente
  let didAutoFocus = false;
  $effect(() => {
    if (!initialLoading && !didAutoFocus && docentes.length > 0) {
      didAutoFocus = true;
      setTimeout(() => {
        const target = formData.docente ? "grado" : "docente";
        const el = document.getElementById(target) as HTMLElement | null;
        if (el) el.focus({ preventScroll: true });
      }, 50);
    }
  });

  onMount(() => {
    loadData();
  });
</script>

<svelte:window onkeydown={handleKeydown} />

<ModuleHeader title="Diario de Campo" subtitle="Reflexión Docente" {onBack}>
  {#snippet actions()}
    <!-- Theme toggle -->
    <button
      onclick={toggleTheme}
      class="min-h-[44px] min-w-[44px] flex items-center justify-center rounded-xl border transition-colors hover:bg-black/5 dark:hover:bg-white/5"
      style="background-color: rgb(var(--bg-secondary)); border-color: rgb(var(--border-primary)); color: rgb(var(--text-primary));"
      title={$theme === 'light' ? 'Modo oscuro' : 'Modo claro'}
      aria-label="Cambiar tema"
    >
      {#if $theme === 'dark'}
        <Sun class="w-5 h-5" />
      {:else}
        <Moon class="w-5 h-5" />
      {/if}
    </button>
    <!-- Indicador de red -->
    <div
      class="hidden sm:flex min-h-[44px] items-center gap-1.5 px-2.5 rounded-xl border"
      style="background-color: rgb(var(--bg-secondary)); border-color: rgb(var(--border-primary));"
      title={$isOnline ? ($pendingCount > 0 ? `${$pendingCount} en cola` : "En línea") : "Sin conexión"}
      aria-label={$isOnline ? "En línea" : "Sin conexión"}
    >
      {#if $isOnline}
        <Wifi class="w-4 h-4 {$pendingCount > 0 ? 'text-amber-500' : 'text-emerald-500'}" />
        {#if $pendingCount > 0}
          <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400">{$pendingCount}</span>
        {/if}
      {:else}
        <WifiOff class="w-4 h-4 text-red-500" />
      {/if}
    </div>
    <button
      onclick={openSheets}
      class="min-h-[44px] min-w-[44px] flex items-center justify-center gap-2 px-3 py-2 rounded-xl border transition-colors hover:bg-black/5 dark:hover:bg-white/5"
      style="background-color: rgb(var(--bg-secondary)); border-color: rgb(var(--border-primary)); color: rgb(var(--text-primary));"
      title="Abrir hoja de cálculo"
      aria-label="Abrir hoja de cálculo"
    >
      <Cloud class="w-5 h-5" />
      <span class="hidden md:inline text-xs font-bold">Hoja</span>
    </button>
    <button
      onclick={openReportGenerator}
      class="min-h-[44px] min-w-[44px] flex items-center justify-center gap-2 px-3 py-2 rounded-xl border transition-colors hover:bg-black/5 dark:hover:bg-white/5"
      style="background-color: rgb(var(--bg-secondary)); border-color: rgb(var(--border-primary)); color: rgb(var(--text-primary));"
      title="Generar reportes PDF"
      aria-label="Generar reportes PDF"
    >
      <FileText class="w-5 h-5" />
      <span class="hidden md:inline text-xs font-bold">Reportes</span>
    </button>
  {/snippet}
</ModuleHeader>

<div
  class="min-h-screen flex flex-col transition-colors duration-200"
  style="background-color: {styles.bg};"
>
  <main class="flex-1 p-4 sm:p-6 lg:p-8 pb-40">
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
              {INFO_DIARIO}
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

      <!-- Panel de validación en vivo -->
      <div
        class="mb-6 rounded-xl border-2 p-3 sm:p-4 transition-colors {missingFields.length === 0
          ? 'bg-emerald-50 dark:bg-emerald-500/15 border-emerald-500'
          : 'bg-amber-50 dark:bg-amber-500/15 border-amber-500'}"
      >
        {#if missingFields.length === 0}
          <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-300">
            <Check class="w-5 h-5" />
            <span class="text-sm font-semibold">Listo para guardar</span>
          </div>
        {:else}
          <div class="flex flex-col gap-2">
            <div class="flex items-center gap-2 text-amber-700 dark:text-amber-300">
              <AlertTriangle class="w-5 h-5" />
              <span class="text-sm font-semibold">
                {missingFields.length === 1
                  ? "Falta 1 campo"
                  : `Faltan ${missingFields.length} campos`}
              </span>
            </div>
            <div class="flex flex-wrap gap-2">
              {#each missingFields as field}
                <button
                  type="button"
                  onclick={() => focusField(FIELD_TARGET_ID[field] ?? field)}
                  class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide bg-amber-500 text-white border border-amber-600 shadow-sm hover:bg-amber-600 transition-colors"
                  aria-label={`Ir al campo ${FIELD_LABELS[field] ?? field}`}
                >
                  {FIELD_LABELS[field] ?? field}
                </button>
              {/each}
            </div>
          </div>
        {/if}
      </div>

      {#if initialLoading}
        <div class="space-y-6">
          <Skeleton variant="rect" class="h-12 w-full" />
          <Skeleton variant="rect" class="h-12 w-full" />
          <Skeleton variant="rect" class="h-12 w-full" />
          <Skeleton variant="rect" class="h-12 w-full" />
        </div>
      {:else}
        <form id="diario-form" onsubmit={handleSubmit} novalidate class="space-y-6">
          <!-- Fila 1: Fecha + Docente con componentes modernos -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <DatePicker
                id="fecha"
                label="Fecha"
                bind:value={formData.fecha}
                placeholder="Seleccione fecha"
                hasError={showError('fecha')}
              />
            </div>

            <div class="space-y-2">
              <SelectField
                id="docente"
                label="Docente"
                bind:value={formData.docente}
                options={docentesSorted.map(d => ({ value: d, label: d }))}
                placeholder={isLoadingDocentes ? "Cargando..." : "Seleccione docente"}
                selectType="docente"
                isLoading={isLoadingDocentes}
                hasError={showError('docente')}
              />
            </div>
          </div>

          <!-- Fila 2: Grado + Horas -->
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
                hasError={showError('grado')}
              />
            </div>

            {#if !docenteHasDash}
              <div class="space-y-2">
                <label
                  for="horas"
                  class="block text-sm font-medium"
                  style="color: {styles.label};">Horas</label
                >
                <select
                  id="horas"
                  bind:value={formData.horas}
                  class="w-full px-4 py-3 rounded-xl border focus:ring-2 focus:ring-indigo-500 transition-all outline-none appearance-none cursor-pointer {showError('horas') ? 'ring-2 ring-red-500 border-red-500' : ''}"
                  style="background-color: {styles.inputBg}; border-color: {showError('horas') ? '#ef4444' : styles.border}; color: {styles.text};"
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
            {/if}
          </div>

          <!-- Fila 3: Materia(s) -->
          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <span
                class="block text-sm font-medium"
                style="color: {styles.label};"
              >{docenteHasDash ? "Materias" : "Materia"}</span>
            </div>
            {#if docenteHasDash}
              <div id="materias-multi" class="space-y-3">
                {#if selectedMaterias.length === 0}
                  <p class="text-xs italic" style="color: {styles.placeholder};">
                    Aún no has seleccionado materias
                  </p>
                {:else}
                  <div class="flex flex-wrap gap-2">
                    {#each selectedMaterias as sm (sm.materia)}
                      <span
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/30"
                      >
                        <span>
                          {sm.materia} · {sm.horas ? `${sm.horas}h` : "sin horas"}
                        </span>
                        <button
                          type="button"
                          onclick={() => {
                            selectedMaterias = selectedMaterias.filter(
                              (m) => m.materia !== sm.materia,
                            );
                          }}
                          class="rounded-full hover:bg-blue-500/20 p-0.5 transition-colors"
                          aria-label={`Quitar ${sm.materia}`}
                        >
                          <X class="w-3 h-3" />
                        </button>
                      </span>
                    {/each}
                  </div>
                {/if}

                {#if materiasSorted.length > 6}
                  <button
                    type="button"
                    onclick={() => (showAllMaterias = !showAllMaterias)}
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline"
                  >
                    {#if showAllMaterias}
                      <ChevronUp class="w-4 h-4" />
                      <span>Ocultar materias</span>
                    {:else}
                      <ChevronDown class="w-4 h-4" />
                      <span>Mostrar todas las materias ({materiasSorted.length})</span>
                    {/if}
                  </button>
                {/if}

                {#if materiasSorted.length <= 6 || showAllMaterias}
                  <div
                    class="border rounded-xl p-2 lg:p-3 flex flex-col lg:flex-row lg:flex-wrap gap-2 lg:gap-2 {showError('materias') ? 'ring-2 ring-red-500 border-red-500' : ''}"
                    style="border-color: {showError('materias') ? '#ef4444' : styles.border}; background-color: {styles.inputBg};"
                  >
                    {#each materiasSorted as materia}
                      {@const isSaved = docenteMaterias[formData.docente]?.includes(materia.materia)}
                      {@const selectedIndex = selectedMaterias.findIndex(m => m.materia === materia.materia)}
                      {@const isSelected = selectedIndex >= 0}
                      <div
                        class="flex flex-col lg:flex-row lg:items-center gap-1 lg:gap-2 px-2 py-2 lg:py-1.5 rounded-lg border transition-all {isSelected ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/30 dark:border-indigo-500' : 'border-transparent'}"
                        style="border-color: {isSelected ? '#6366f1' : styles.border};"
                      >
                        <div class="flex items-center gap-1.5">
                          <input
                            type="checkbox"
                            checked={isSelected}
                            onchange={(e) => {
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
                            class="w-full lg:w-16 px-2 py-1.5 lg:py-1 text-sm rounded border appearance-none cursor-pointer {showError('horas') && !selectedMaterias[selectedIndex].horas ? 'ring-2 ring-red-500 border-red-500' : ''}"
                            style="background-color: {styles.inputBg}; border-color: {showError('horas') && !selectedMaterias[selectedIndex].horas ? '#ef4444' : styles.border}; color: {styles.text};"
                            aria-label={`Horas para ${materia.materia}`}
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
                {/if}
              </div>
            {:else}
              <SelectField
                id="materia"
                label="Asignatura"
                bind:value={formData.materia}
                options={materiasSorted.map(m => ({ value: m.materia, label: m.materia }))}
                placeholder={isLoadingMaterias ? "Cargando..." : "Seleccione asignatura"}
                selectType="materia"
                isLoading={isLoadingMaterias}
                hasError={showError('materia')}
              />
            {/if}
          </div>

          <!-- Fila 4: Diario de Campo -->
          <div id="diario" class="space-y-2">
            <DiarioAnotacionOptions bind:selectedDiarioAnots />
          </div>

          <!-- FAB -->
          <div class="fixed bottom-6 right-6 z-40 flex flex-col items-end gap-3">
            <!-- Botón Historial -->
            <button
              type="button"
              onclick={(e) => openDiarioHistory(e)}
              title="Ver historial"
              aria-label="Ver historial de diarios"
              class="w-12 h-12 rounded-full border shadow-lg transition-transform hover:scale-105 active:scale-95 flex items-center justify-center"
              style="background-color: rgb(var(--card-bg)); border-color: rgb(var(--border-primary)); color: rgb(var(--accent-primary));"
            >
              <History class="w-5 h-5" />
            </button>
            <button
              type="button"
              onclick={handleClearForm}
              title="Limpiar formulario"
              aria-label="Limpiar formulario"
              class="w-11 h-11 rounded-full border shadow-lg transition-transform hover:scale-105 active:scale-95 flex items-center justify-center"
              style="background-color: rgb(var(--bg-secondary)); border-color: rgb(var(--border-primary)); color: rgb(var(--text-secondary));"
            >
              <Eraser class="w-5 h-5" />
            </button>
            <button
              type="submit"
              disabled={isLoading}
              title={fabTitle}
              class="relative w-16 h-16 {missingFields.length === 0 ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-amber-500 hover:bg-amber-600'} disabled:bg-slate-400 dark:disabled:bg-slate-700 disabled:cursor-not-allowed disabled:scale-100 text-white rounded-full shadow-2xl transition-transform hover:scale-105 active:scale-95 flex items-center justify-center overflow-visible border-2 border-white dark:border-slate-800"
              aria-label="Guardar Diario de Campo (Ctrl+Enter)"
            >
              {#if isLoading}
                <Loader2 class="w-7 h-7 text-white animate-spin" />
              {:else}
                <Send class="w-7 h-7 text-white" />
              {/if}
              {#if selectedDiarioAnots.length >= 1}
                <span
                  class="absolute -top-1 -right-1 min-w-[22px] h-[22px] px-1.5 rounded-full bg-white text-indigo-600 text-xs font-bold flex items-center justify-center border-2 border-indigo-600 shadow"
                  aria-label={`${selectedDiarioAnots.length} anotaciones seleccionadas`}
                >
                  {selectedDiarioAnots.length}
                </span>
              {/if}
            </button>
          </div>
        </form>
      {/if}
    </div>
  </main>
</div>

{#if showReportGenerator}
  <ReportGeneratorDiario
    onClose={closeReportGenerator}
    initialDocente={formData.docente}
  />
{/if}

{#if showDiarioHistory}
  <DiarioHistory
    bind:isOpen={showDiarioHistory}
    onClose={closeDiarioHistory}
    onSelect={loadFromDiarioHistory}
    filterGrado={formData.grado}
    filterMateria={formData.materia}
  />
{/if}

{#if lastSaved && lastSavedVisible}
  <div
    class="fixed bottom-4 left-4 z-40 max-w-xs w-[calc(100%-2rem)] sm:w-72 rounded-xl shadow-lg border p-4 transition-all duration-500"
    style="background-color: rgb(var(--card-bg)); border-color: rgb(var(--card-border));"
    class:opacity-0={!lastSavedVisible}
    class:translate-y-2={!lastSavedVisible}
  >
    <div class="flex items-start gap-3">
      <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
        <span class="text-green-600 text-sm">&#10003;</span>
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-xs font-semibold" style="color: rgb(var(--text-primary));">Último diario guardado</p>
        <p class="text-[11px] mt-1 opacity-70" style="color: rgb(var(--text-secondary));">
          {lastSaved.fecha} · {lastSaved.grado} · {lastSaved.cantidad} reg.
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
        aria-label="Cerrar notificación"
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
</style>