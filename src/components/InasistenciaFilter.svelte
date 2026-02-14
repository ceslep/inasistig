<script lang="ts">
  import { onMount } from "svelte";
  import Swal from "sweetalert2";
  import {
    getInasistencias,
    getDocentes,
    getMaterias,
    getEstudiantes,
  } from "../../api/service";
  import { SPREADSHEET_ID, WORKSHEET_TITLE } from "../constants";
  import { theme } from "../lib/themeStore";
  import eieLogo from "../assets/eie.png";

  export let onClose: () => void;
  export let selectedDocente: string = "";

  // --- Interfaces para tipado ---
  interface InasistenciaData {
    timestamp: string;
    docente: string;
    fecha: string;
    horas: string;
    materia: string;
    motivo: string;
    grado: string;
    nombre: string;
    observaciones: string;
  }

  interface APIResponse {
    success: boolean;
    records: Array<{
      rowIndex: number;
      values: string[];
    }>;
  }

  // --- Estado de datos ---
  let inasistencias: InasistenciaData[] = [];
  let docentes: string[] = [];
  let materias: { materia: string }[] = [];
  let estudiantes: { nombre: string; grado: string | number }[] = [];

  let isLoading = false;
  let isLoadingData = false;
  let filtrarPorFecha = false;
  let inasistenciasFiltradas: InasistenciaData[] = [];

  // --- Filtros ---
  // --- Filtros ---
  let filtros = {
    docente: selectedDocente || "",
    materia: "",
    grado: "",
    fechaInicio: "",
    fechaFin: "",
    motivo: "",
  };

  // --- Inicializar fechas ---
  const initializeDates = () => {
    const today = new Date();

    // Por defecto: último mes
    const oneMonthAgo = new Date(
      today.getFullYear(),
      today.getMonth() - 1,
      today.getDate(),
    );

    // Formatear fechas en zona horaria local (YYYY-MM-DD)
    const formatLocalDate = (date: Date) => {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, "0");
      const day = String(date.getDate()).padStart(2, "0");
      return `${year}-${month}-${day}`;
    };

    filtros.fechaInicio = formatLocalDate(oneMonthAgo);
    filtros.fechaFin = formatLocalDate(today);
  };

  // --- Funciones para preset de fechas ---
  const setFechaPreset = (preset: string) => {
    const today = new Date();
    let startDate: Date;

    switch (preset) {
      case "hoy":
        startDate = new Date(today);
        break;
      case "semana":
        startDate = new Date(today);
        startDate.setDate(today.getDate() - 7);
        break;
      case "mes":
        startDate = new Date(today);
        startDate.setDate(today.getDate() - 30);
        break;
      case "mes_actual":
        startDate = new Date(today.getFullYear(), today.getMonth(), 1);
        break;
      case "trimestre":
        startDate = new Date(today);
        startDate.setDate(today.getDate() - 90);
        break;
      default:
        initializeDates();
        return;
    }

    // Formatear fechas en zona horaria local (YYYY-MM-DD)
    const formatLocalDate = (date: Date) => {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, "0");
      const day = String(date.getDate()).padStart(2, "0");
      return `${year}-${month}-${day}`;
    };

    filtros.fechaInicio = formatLocalDate(startDate);
    filtros.fechaFin = formatLocalDate(today);
    filtrarPorFecha = true;

    console.log("✅ setFechaPreset ejecutado:", {
      preset,
      fechaInicio: filtros.fechaInicio,
      fechaFin: filtros.fechaFin,
      filtrarPorFecha,
    });
  };

  // --- Motivos con iconos ---
  const motivosConfig = [
    {
      value: "Sin excusa",
      icon: "🚫",
      color: "bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200",
    },
    {
      value: "Excusa",
      icon: "📄",
      color: "bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200",
    },
    {
      value: "LLegada Tarde",
      icon: "⏰",
      color:
        "bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200",
    },
    {
      value: "Transporte Escolar",
      icon: "🚌",
      color: "bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200",
    },
    {
      value: "Permiso",
      icon: "✅",
      color:
        "bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200",
    },
    {
      value: "No portar/sin uniforme",
      icon: "👚",
      color: "bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-200",
    },
    {
      value: "Pacto de Aula",
      icon: "🤝",
      color:
        "bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200",
    },
    {
      value: "Uso del celular",
      icon: "📱",
      color:
        "bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200",
    },
    {
      value: "Desorden en Clase",
      icon: "🔊",
      color:
        "bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200",
    },
    {
      value: "Fuga",
      icon: "🏃‍♂️",
      color: "bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200",
    },
    {
      value: "No realización de Aseo",
      icon: "🧹",
      color: "bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200",
    },
    {
      value: "Licencia por salud",
      icon: "🏥",
      color: "bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-200",
    },
    {
      value: "Incapacidad",
      icon: "🩺",
      color: "bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200",
    },
    {
      value: "Reunión interna",
      icon: "👥",
      color: "bg-zinc-100 text-zinc-800 dark:bg-zinc-900 dark:text-zinc-200",
    },
    {
      value: "Psicoorientación",
      icon: "🧠",
      color:
        "bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200",
    },
  ];

  const getMotivoConfig = (motivoName: string) => {
    return (
      motivosConfig.find((m) => m.value === motivoName) || {
        icon: "❓",
        color: "bg-gray-100 text-gray-800",
      }
    );
  };

  // --- Datos únicos para filtros (extraídos de las inasistencias y APIs) ---
  let docentesUnicos: string[] = [];
  let materiasUnicas: string[] = [];
  let gradosUnicos: string[] = [];
  let motivosUnicos: string[] = [];

  // --- Datos filtrados para selects dependientes ---
  $: materiasPorDocente = filtros.docente
    ? [
        ...new Set(
          inasistencias
            .filter((i) => i.docente === filtros.docente)
            .map((i) => i.materia)
            .filter(Boolean),
        ),
      ].sort()
    : materiasUnicas;

  $: gradosPorDocente = filtros.docente
    ? [
        ...new Set(
          inasistencias
            .filter((i) => i.docente === filtros.docente)
            .map((i) => i.grado)
            .filter(Boolean),
        ),
      ].sort((a, b) => {
        const aNum = parseInt(a);
        const bNum = parseInt(b);
        if (!isNaN(aNum) && !isNaN(bNum)) {
          return aNum - bNum;
        }
        return a.localeCompare(b);
      })
    : gradosUnicos;

  $: gradosPorMateria = filtros.materia
    ? [
        ...new Set(
          inasistencias
            .filter((i) => i.materia === filtros.materia)
            .map((i) => i.grado)
            .filter(Boolean),
        ),
      ].sort((a, b) => {
        const aNum = parseInt(a);
        const bNum = parseInt(b);
        if (!isNaN(aNum) && !isNaN(bNum)) {
          return aNum - bNum;
        }
        return a.localeCompare(b);
      })
    : gradosPorDocente;

  $: motivosPorFiltros =
    filtros.docente || filtros.materia || filtros.grado
      ? [
          ...new Set(
            inasistencias
              .filter((i) => {
                if (filtros.docente && i.docente !== filtros.docente)
                  return false;
                if (filtros.materia && i.materia !== filtros.materia)
                  return false;
                if (filtros.grado && i.grado !== filtros.grado) return false;
                return true;
              })
              .map((i) => i.motivo)
              .filter(Boolean),
          ),
        ].sort()
      : motivosUnicos;

  // --- Estilos reactivos ---
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

  // --- Normalizar fecha a formato YYYY-MM-DD para comparaciones ---
  const normalizeFecha = (fecha: string): string => {
    if (!fecha) return "";
    
    // Si ya está en formato YYYY-MM-DD, retornarlo
    if (/^\d{4}-\d{2}-\d{2}$/.test(fecha)) {
      return fecha;
    }
    
    // Si viene en formato DD/MM/YYYY, convertir
    const parts = fecha.split("/");
    if (parts.length === 3) {
      const day = parts[0].padStart(2, "0");
      const month = parts[1].padStart(2, "0");
      const year = parts[2].length === 2 ? `20${parts[2]}` : parts[2];
      return `${year}-${month}-${day}`;
    }
    
    // Intentar parsear como fecha
    const date = new Date(fecha);
    if (!isNaN(date.getTime())) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, "0");
      const day = String(date.getDate()).padStart(2, "0");
      return `${year}-${month}-${day}`;
    }
    
    return fecha;
  };

  // --- Inasistencias filtradas ---
  $: {
    console.log("🔄 Filtro reactivo ejecutándose:", {
      totalInasistencias: inasistencias.length,
      filtrarPorFecha,
      fechaInicio: filtros.fechaInicio,
      fechaFin: filtros.fechaFin,
    });

    inasistenciasFiltradas = inasistencias.filter((item) => {
      if (filtros.docente && item.docente !== filtros.docente) return false;
      if (filtros.materia && item.materia !== filtros.materia) return false;
      if (filtros.grado && item.grado !== filtros.grado) return false;
      if (filtros.motivo && item.motivo !== filtros.motivo) return false;

      if (filtrarPorFecha) {
        const itemFecha = normalizeFecha(item.fecha);

        if (filtros.fechaInicio && itemFecha < filtros.fechaInicio)
          return false;
        if (filtros.fechaFin && itemFecha > filtros.fechaFin) return false;
      }

      return true;
    });

    console.log(
      `📊 Resultados filtrados: ${inasistenciasFiltradas.length} de ${inasistencias.length}`,
    );
  }

  // --- Auto-inicializar fechas cuando se activa el filtro ---
  $: if (filtrarPorFecha && (!filtros.fechaInicio || !filtros.fechaFin)) {
    initializeDates();
  }

  // --- Cargar datos iniciales ---
  const loadData = async () => {
    isLoadingData = true;
    try {
      const [inasistenciasData, docentesData, materiasData, estudiantesData] =
        await Promise.all([
          getInasistencias({
            spreadsheetId: SPREADSHEET_ID,
            worksheetTitle: WORKSHEET_TITLE,
          }),
          getDocentes(),
          getMaterias(),
          getEstudiantes(),
        ]);

      // Debug: Verificar la estructura de los datos
      console.log("Respuesta cruda de inasistencias:", inasistenciasData);
      console.log("Tipo de dato:", typeof inasistenciasData);
      console.log("Es array:", Array.isArray(inasistenciasData));
      if (inasistenciasData && typeof inasistenciasData === "object") {
        console.log("Keys:", Object.keys(inasistenciasData));
      }

      // --- Función para procesar las inasistencias del API ---
      const procesarInasistencias = (data: any): InasistenciaData[] => {
        if (!data?.records || !Array.isArray(data.records)) return [];

        // La primera fila contiene los encabezados
        const headers = data.records[0]?.values || [];
        const dataRows = data.records.slice(1); // Omitir la fila de encabezados

        return dataRows
          .map((row: any) => {
            const values = row.values || [];
            return {
              timestamp: values[0] || "",
              docente: values[1] || "",
              fecha: values[2] || "",
              horas: values[3] || "",
              materia: values[4] || "",
              motivo: values[5] || "",
              grado: values[6] || "",
              nombre: values[7] || "",
              observaciones: values[8] || "",
            };
          })
          .filter((item: InasistenciaData) => item.docente && item.fecha); // Solo registros válidos
      };

      // --- Función helper para extraer array de diferentes estructuras de respuesta ---
      const extractArray = (data: any): any[] => {
        if (Array.isArray(data)) return data;
        if (data?.data && Array.isArray(data.data)) return data.data;
        if (data?.rows && Array.isArray(data.rows)) return data.rows;
        if (data?.values && Array.isArray(data.values)) return data.values;
        if (data?.inasistencias && Array.isArray(data.inasistencias))
          return data.inasistencias;
        return [];
      };

      // Procesar datos
      inasistencias = procesarInasistencias(inasistenciasData);
      docentes = extractArray(docentesData);
      materias = extractArray(materiasData);
      estudiantes = extractArray(estudiantesData);

      // Extraer valores únicos para filtros (combinando datos de APIs y de inasistencias)

      // Docentes: combinar de la API de docentes y de las inasistencias
      // Docentes: extraer solo de las inasistencias (registros existentes)
      docentesUnicos =
        inasistencias.length > 0
          ? [
              ...new Set(inasistencias.map((i) => i.docente).filter(Boolean)),
            ].sort()
          : [];

      // Materias: combinar de la API de materias y de las inasistencias
      // Materias: extraer solo de las inasistencias (registros existentes)
      materiasUnicas =
        inasistencias.length > 0
          ? [
              ...new Set(inasistencias.map((i) => i.materia).filter(Boolean)),
            ].sort()
          : [];

      // Grados: combinar de la API de estudiantes y de las inasistencias
      const gradosFromAPI = [
        ...new Set(
          estudiantes
            .map((e) => {
              const grado = typeof e === "string" ? e : e.grado || "";
              return grado.toString();
            })
            .filter(Boolean),
        ),
      ];
      const gradosFromInasistencias =
        inasistencias.length > 0
          ? inasistencias.map((i) => i.grado).filter(Boolean)
          : [];
      gradosUnicos = [
        ...new Set([...gradosFromAPI, ...gradosFromInasistencias]),
      ].sort((a, b) => {
        // Ordenar grados numéricamente si es posible
        const aNum = parseInt(a);
        const bNum = parseInt(b);
        if (!isNaN(aNum) && !isNaN(bNum)) {
          return aNum - bNum;
        }
        return a.localeCompare(b);
      });

      // Motivos: extraer solo de las inasistencias (no hay API para esto)
      motivosUnicos =
        inasistencias.length > 0
          ? [
              ...new Set(inasistencias.map((i) => i.motivo).filter(Boolean)),
            ].sort()
          : [];

      // Inicializar fechas después de cargar los datos
      initializeDates();

      console.log("Datos cargados:", {
        inasistencias: inasistencias.length,
        docentes: docentes.length,
        docentesUnicos: docentesUnicos.length,
        materiasUnicas: materiasUnicas.length,
        gradosUnicos: gradosUnicos.length,
        motivosUnicos: motivosUnicos.length,
      });
    } catch (error) {
      console.error("Error cargando datos:", error);
      await Swal.fire({
        icon: "error",
        title: "Error",
        text: "No se pudieron cargar los datos de inasistencias",
        confirmButtonColor: "#ef4444",
      });
    } finally {
      isLoadingData = false;
    }
  };

  // --- Limpiar filtros ---
  const limpiarFiltros = () => {
    filtros = {
      docente: selectedDocente || "",
      materia: "",
      grado: "",
      fechaInicio: "",
      fechaFin: "",
      motivo: "",
    };
    initializeDates();
    filtrarPorFecha = false;
  };

  // --- Exportar a CSV ---
  const exportarCSV = () => {
    if (inasistenciasFiltradas.length === 0) {
      Swal.fire({
        icon: "warning",
        title: "Sin datos",
        text: "No hay datos para exportar",
        confirmButtonColor: "#f59e0b",
      });
      return;
    }

    const headers = [
      "Fecha",
      "Docente",
      "Materia",
      "Grado",
      "Estudiante",
      "Motivo",
      "Horas",
      "Observaciones",
    ];

    const csvContent = [
      headers.join(","),
      ...inasistenciasFiltradas.map((item) =>
        [
          item.fecha,
          `"${item.docente}"`,
          `"${item.materia}"`,
          item.grado,
          `"${item.nombre}"`,
          `"${item.motivo}"`,
          item.horas,
          `"${item.observaciones}"`,
        ].join(","),
      ),
    ].join("\n");

    const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute(
      "download",
      `inasistencias_${new Date().toISOString().split("T")[0]}.csv`,
    );
    link.style.visibility = "hidden";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  // --- Formatear fecha ---
  const formatearFecha = (fecha: string) => {
    try {
      // Parsear manualmente para evitar problemas de zona horaria
      // La fecha viene como "YYYY-MM-DD"
      const [year, month, day] = fecha.split("-");
      if (year && month && day) {
        return `${day}/${month}/${year}`;
      }
      return fecha;
    } catch {
      return fecha;
    }
  };

  // --- Calcular total de horas por estudiante ---
  const calculateTotalHours = (studentName: string) => {
    if (!filtros.docente || !filtros.materia || !filtros.grado) return;

    // Filtrar inasistencias del estudiante con TODOS los criterios
    const studentInasistencias = inasistencias.filter((item) => {
      // Filtros básicos obligatorios
      if (item.nombre !== studentName) return false;
      if (item.docente !== filtros.docente) return false;
      if (item.materia !== filtros.materia) return false;
      if (item.grado !== filtros.grado) return false;

      // Filtro de fecha si está activo
      if (filtrarPorFecha) {
        const itemFecha = normalizeFecha(item.fecha);

        if (filtros.fechaInicio && itemFecha < filtros.fechaInicio)
          return false;
        if (filtros.fechaFin && itemFecha > filtros.fechaFin) return false;
      }
      return true;
    });

    // Calcular suma de horas
    const totalHours = studentInasistencias.reduce((sum, item) => {
      const hours = parseInt(item.horas) || 0;
      return sum + hours;
    }, 0);

    // Mensaje sobre el rango de fechas
    let dateMessage = "";
    if (filtrarPorFecha && filtros.fechaInicio && filtros.fechaFin) {
      dateMessage = `entre ${formatearFecha(filtros.fechaInicio)} y ${formatearFecha(filtros.fechaFin)}`;
    } else {
      dateMessage = "en total (histórico)";
    }

    Swal.fire({
      icon: "info",
      title: studentName,
      html: `
        <div class="text-left space-y-2">
          <p><strong>Materia:</strong> ${filtros.materia}</p>
          <p><strong>Total inasistencias:</strong> ${totalHours} horas</p>
          <p class="text-sm text-gray-500 text-center mt-4">(${dateMessage})</p>
        </div>
      `,
      confirmButtonColor: "#4f46e5",
    });
  };

  onMount(() => {
    loadData();
  });
</script>

<div
  class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
  on:click|self={onClose}
  role="button"
  tabindex="0"
  aria-label="Cerrar ventana"
  on:keydown={(e) => {
    if (e.key === "Enter" || e.key === " ") {
      onClose();
    }
  }}
>
  <div
    class="w-full max-w-7xl max-h-[95vh] bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl overflow-hidden flex flex-col"
    style="background-color: {styles.cardBg};"
  >
    <!-- Header -->
    <div
      class="flex items-center justify-between p-3 sm:p-4 border-b"
      style="border-color: {styles.cardBorder};"
    >
      <div class="flex items-center gap-3">
        <img src={eieLogo} alt="EIE Logo" class="h-8 w-auto hidden sm:block" />
        <div>
          <h2
            class="text-lg sm:text-xl font-bold"
            style="color: {styles.text};"
          >
            Inasistencias
          </h2>
          <p class="text-xs hidden sm:block" style="color: {styles.label};">
            Filtra y consulta los registros
          </p>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <button
          on:click={exportarCSV}
          class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors"
          disabled={inasistenciasFiltradas.length === 0}
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
              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            />
          </svg>
          Exportar CSV
        </button>
        <button
          on:click={onClose}
          class="p-2 rounded-lg transition-colors hover:bg-black/5 dark:hover:bg-white/5"
          style="color: {styles.text};"
          aria-label="Cerrar"
        >
          <svg
            class="w-6 h-6"
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

    <!-- Contenido -->
    <div class="flex-1 overflow-hidden flex flex-col min-h-0">
      {#if isLoadingData}
        <div class="flex-1 flex items-center justify-center">
          <div class="text-center">
            <div
              class="animate-spin w-8 h-8 border-4 border-indigo-500 border-t-transparent rounded-full mx-auto mb-4"
            ></div>
            <p style="color: {styles.text};">Cargando datos...</p>
          </div>
        </div>
      {:else}
        <!-- Filtros -->
        <div
          class="p-3 sm:p-4 border-b"
          style="border-color: {styles.cardBorder};"
        >
          <div class="flex items-center justify-between mb-2 sm:mb-3">
            <h3 class="text-lg font-semibold" style="color: {styles.text};">
              Filtros
              {#if filtros.docente || filtros.materia || filtros.grado || filtros.motivo || filtros.fechaInicio || filtros.fechaFin}
                <span
                  class="ml-2 px-2 py-1 text-xs bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 rounded-full"
                >
                  Activos
                </span>
              {/if}
            </h3>
            <button
              on:click={limpiarFiltros}
              class="text-sm px-3 py-1 rounded-lg border transition-colors hover:bg-black/5 dark:hover:bg-white/5"
              style="border-color: {styles.border}; color: {styles.text};"
            >
              Limpiar filtros
            </button>
          </div>

          <!-- Presets de fechas -->
          <div
            class="mb-3 p-1.5 sm:p-2 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg"
          >
            <p
              class="text-[10px] sm:text-xs font-medium mb-1.5"
              style="color: {styles.text};"
            >
              Rápido: Fechas
            </p>
            <div class="flex items-center mb-2">
              <input
                id="checkFiltrarFecha"
                type="checkbox"
                bind:checked={filtrarPorFecha}
                class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 dark:bg-zinc-700"
              />
              <label
                for="checkFiltrarFecha"
                class="ml-2 text-xs font-medium"
                style="color: {styles.text};"
              >
                Activar filtro por fechas
              </label>
            </div>
            <div class="flex flex-wrap gap-1 sm:gap-2">
              <button
                on:click={() => setFechaPreset("hoy")}
                class="text-[10px] sm:text-xs px-1.5 sm:px-2 py-1 bg-white dark:bg-zinc-800 border rounded hover:bg-indigo-100 dark:hover:bg-indigo-800/50 transition-colors"
                style="border-color: {styles.border}; color: {styles.text};"
              >
                Hoy
              </button>
              <button
                on:click={() => setFechaPreset("semana")}
                class="text-[10px] sm:text-xs px-1.5 sm:px-2 py-1 bg-white dark:bg-zinc-800 border rounded hover:bg-indigo-100 dark:hover:bg-indigo-800/50 transition-colors"
                style="border-color: {styles.border}; color: {styles.text};"
              >
                Últimos 7 días
              </button>
              <button
                on:click={() => setFechaPreset("mes")}
                class="text-[10px] sm:text-xs px-1.5 sm:px-2 py-1 bg-white dark:bg-zinc-800 border rounded hover:bg-indigo-100 dark:hover:bg-indigo-800/50 transition-colors"
                style="border-color: {styles.border}; color: {styles.text};"
              >
                Últimos 30 días
              </button>
              <button
                on:click={() => setFechaPreset("mes_actual")}
                class="text-[10px] sm:text-xs px-1.5 sm:px-2 py-1 bg-white dark:bg-zinc-800 border rounded hover:bg-indigo-100 dark:hover:bg-indigo-800/50 transition-colors"
                style="border-color: {styles.border}; color: {styles.text};"
              >
                Mes actual
              </button>
              <button
                on:click={() => setFechaPreset("trimestre")}
                class="text-[10px] sm:text-xs px-1.5 sm:px-2 py-1 bg-white dark:bg-zinc-800 border rounded hover:bg-indigo-100 dark:hover:bg-indigo-800/50 transition-colors"
                style="border-color: {styles.border}; color: {styles.text};"
              >
                Último trimestre
              </button>
            </div>
          </div>

          <div
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4"
          >
            <div class="flex gap-3 sm:gap-4">
              <div class="flex-1 space-y-1.5 sm:space-y-2">
                <label
                  for="filtroDocente"
                  class="block text-xs sm:text-sm font-medium"
                  style="color: {styles.label};"
                >
                  Docente
                </label>
                <select
                  id="filtroDocente"
                  bind:value={filtros.docente}
                  class="w-full px-2 sm:px-3 py-1.5 sm:py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 outline-none text-xs sm:text-sm"
                  style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
                >
                  <option value="">Todos los docentes</option>
                  {#each docentesUnicos as docente}
                    <option value={docente}>{docente}</option>
                  {/each}
                </select>
              </div>

              <div class="flex-1 space-y-1.5 sm:space-y-2">
                <label
                  for="filtroMateria"
                  class="block text-xs sm:text-sm font-medium"
                  style="color: {styles.label};"
                >
                  Materia {filtros.docente ? `(del docente)` : ""}
                </label>
                <select
                  id="filtroMateria"
                  bind:value={filtros.materia}
                  class="w-full px-2 sm:px-3 py-1.5 sm:py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 outline-none text-xs sm:text-sm"
                  style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
                >
                  <option value="">
                    {filtros.docente
                      ? "Todas las materias del docente"
                      : "Todas las materias"}
                  </option>
                  {#each materiasPorDocente as materia}
                    <option value={materia}>{materia}</option>
                  {/each}
                </select>
                {#if filtros.docente && materiasPorDocente.length === 0}
                  <p
                    class="text-[10px] sm:text-xs text-amber-600 dark:text-amber-400 mt-1"
                  >
                    Este docente no tiene registros de materias
                  </p>
                {/if}
              </div>
            </div>

            <div class="flex gap-3 sm:gap-4">
              <div class="flex-1 space-y-1.5 sm:space-y-2">
                <label
                  for="filtroGrado"
                  class="block text-xs sm:text-sm font-medium"
                  style="color: {styles.label};"
                >
                  Grado {filtros.docente || filtros.materia ? `(filtrado)` : ""}
                </label>
                <select
                  id="filtroGrado"
                  bind:value={filtros.grado}
                  class="w-full px-2 sm:px-3 py-1.5 sm:py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 outline-none text-xs sm:text-sm"
                  style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
                >
                  <option value="">
                    {filtros.docente || filtros.materia
                      ? "Todos los grados filtrados"
                      : "Todos los grados"}
                  </option>
                  {#each gradosPorMateria as grado}
                    <option value={grado}>{grado}</option>
                  {/each}
                </select>
                {#if (filtros.docente || filtros.materia) && gradosPorMateria.length === 0}
                  <p
                    class="text-[10px] sm:text-xs text-amber-600 dark:text-amber-400 mt-1"
                  >
                    No hay grados para los filtros seleccionados
                  </p>
                {/if}
              </div>

              <div class="flex-1 space-y-1.5 sm:space-y-2">
                <label
                  for="filtroMotivo"
                  class="block text-xs sm:text-sm font-medium"
                  style="color: {styles.label};"
                >
                  Motivo
                </label>
                <select
                  id="filtroMotivo"
                  bind:value={filtros.motivo}
                  class="w-full px-2 sm:px-3 py-1.5 sm:py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 outline-none text-xs sm:text-sm"
                  style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
                >
                  <option value="">
                    {filtros.docente || filtros.materia || filtros.grado
                      ? "Todos los motivos filtrados"
                      : "Todos los motivos"}
                  </option>
                  {#each motivosPorFiltros as motivo}
                    {@const config = getMotivoConfig(motivo)}
                    <option value={motivo}>
                      {config.icon}
                      {motivo}
                    </option>
                  {/each}
                </select>
                {#if (filtros.docente || filtros.materia || filtros.grado) && motivosPorFiltros.length === 0}
                  <p
                    class="text-[10px] sm:text-xs text-amber-600 dark:text-amber-400 mt-1"
                  >
                    No hay motivos para los filtros seleccionados
                  </p>
                {/if}
              </div>
            </div>

            <div class="flex gap-3 sm:gap-4">
              <div class="flex-1 space-y-1.5 sm:space-y-2">
                <label
                  for="filtroFechaInicio"
                  class="block text-xs sm:text-sm font-medium"
                  style="color: {styles.label};"
                >
                  Fecha desde
                </label>
                <input
                  id="filtroFechaInicio"
                  type="date"
                  bind:value={filtros.fechaInicio}
                  disabled={!filtrarPorFecha}
                  class="w-full px-2 sm:px-3 py-1.5 sm:py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 outline-none text-xs sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                  style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
                />
              </div>

              <div class="flex-1 space-y-1.5 sm:space-y-2">
                <label
                  for="filtroFechaFin"
                  class="block text-xs sm:text-sm font-medium"
                  style="color: {styles.label};"
                >
                  Fecha hasta
                </label>
                <input
                  id="filtroFechaFin"
                  type="date"
                  bind:value={filtros.fechaFin}
                  disabled={!filtrarPorFecha}
                  class="w-full px-2 sm:px-3 py-1.5 sm:py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 outline-none text-xs sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                  style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Resultados -->
        <div class="flex-1 flex flex-col p-2 sm:p-4 min-h-0">
          <div class="mb-4 px-0 sm:px-3">
            <p class="text-sm" style="color: {styles.label};">
              Se encontraron <span class="font-bold text-indigo-500"
                >{inasistenciasFiltradas.length}</span
              > registros
            </p>
          </div>

          {#if inasistenciasFiltradas.length === 0}
            <div class="flex-1 flex items-center justify-center">
              <div class="text-center py-12 px-4">
                <svg
                  class="w-16 h-16 mx-auto mb-4 opacity-50"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                  style="color: {styles.icon};"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                  />
                </svg>
                <p class="text-sm sm:text-base" style="color: {styles.text};">
                  No se encontraron registros con los filtros seleccionados
                </p>
              </div>
            </div>
          {:else}
            <!-- Vista Desktop: Tabla -->
            <div class="hidden lg:flex flex-1 flex-col overflow-hidden min-h-0">
              <div
                class="flex-1 overflow-auto min-w-full border rounded-lg"
                style="border-color: {styles.cardBorder};"
              >
                <table class="w-full border-collapse text-sm min-w-[800px]">
                  <thead
                    class="sticky top-0 z-10 border-b"
                    style="background-color: {styles.cardBg}; border-color: {styles.border};"
                  >
                    <tr>
                      <th
                        class="text-left p-2 font-semibold whitespace-nowrap"
                        style="color: {styles.text};">Fecha</th
                      >
                      <th
                        class="text-left p-2 font-semibold whitespace-nowrap"
                        style="color: {styles.text};">Docente</th
                      >
                      <th
                        class="text-left p-2 font-semibold whitespace-nowrap"
                        style="color: {styles.text};">Materia</th
                      >
                      <th
                        class="text-center p-2 font-semibold whitespace-nowrap"
                        style="color: {styles.text};">Grado</th
                      >
                      <th
                        class="text-left p-2 font-semibold whitespace-nowrap"
                        style="color: {styles.text};">Estudiante</th
                      >
                      <th
                        class="text-center p-2 font-semibold whitespace-nowrap"
                        style="color: {styles.text};">Motivo</th
                      >
                      <th
                        class="text-center p-2 font-semibold whitespace-nowrap"
                        style="color: {styles.text};">Horas</th
                      >
                      <th
                        class="text-left p-2 font-semibold whitespace-nowrap"
                        style="color: {styles.text};">Observaciones</th
                      >
                    </tr>
                  </thead>
                  <tbody>
                    {#each inasistenciasFiltradas as item, index}
                      {@const config = getMotivoConfig(item.motivo)}
                      <tr
                        class="border-b transition-colors hover:bg-black/5 dark:hover:bg-white/5 text-xs"
                        style="border-color: {styles.border};"
                      >
                        <td class="p-2" style="color: {styles.text};"
                          >{formatearFecha(item.fecha)}</td
                        >
                        <td
                          class="p-2 truncate max-w-[150px]"
                          style="color: {styles.text};"
                          title={item.docente}>{item.docente}</td
                        >
                        <td
                          class="p-2 truncate max-w-[150px]"
                          style="color: {styles.text};"
                          title={item.materia}>{item.materia}</td
                        >
                        <td
                          class="p-2 text-center"
                          style="color: {styles.text};">{item.grado}</td
                        >
                        <td
                          class="p-2 truncate max-w-[150px]"
                          style="color: {styles.text};"
                          title={item.nombre}
                        >
                          {#if filtros.docente && filtros.materia && filtros.grado}
                            <button
                              class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium text-left truncate w-full"
                              on:click={() => calculateTotalHours(item.nombre)}
                            >
                              {item.nombre}
                            </button>
                          {:else}
                            {item.nombre}
                          {/if}
                        </td>
                        <td class="p-2 text-center">
                          <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium {config.color}"
                          >
                            <span>{config.icon}</span>
                            {item.motivo}
                          </span>
                        </td>
                        <td
                          class="p-2 text-center"
                          style="color: {styles.text};">{item.horas}</td
                        >
                        <td class="p-2" style="color: {styles.text};">
                          <span class="truncate block max-w-xs text-xs">
                            {item.observaciones || "-"}
                          </span>
                        </td>
                      </tr>
                    {/each}
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Vista Tablet -->
            <div class="hidden md:flex lg:hidden flex-1 overflow-auto">
              <div
                class="min-w-full border rounded-lg"
                style="border-color: {styles.cardBorder};"
              >
                <table class="w-full border-collapse text-xs">
                  <thead
                    class="sticky top-0 z-10 border-b"
                    style="background-color: {styles.cardBg}; border-color: {styles.border};"
                  >
                    <tr>
                      <th
                        class="text-left p-2 font-semibold whitespace-nowrap"
                        style="color: {styles.text};">Fecha</th
                      >
                      <th
                        class="text-left p-2 font-semibold whitespace-nowrap"
                        style="color: {styles.text};">Docente</th
                      >
                      <th
                        class="text-left p-2 font-semibold whitespace-nowrap"
                        style="color: {styles.text};">Materia</th
                      >
                      <th
                        class="text-left p-2 font-semibold whitespace-nowrap"
                        style="color: {styles.text};">Estudiante</th
                      >
                      <th
                        class="text-center p-2 font-semibold whitespace-nowrap"
                        style="color: {styles.text};">Motivo</th
                      >
                      <th
                        class="text-center p-2 font-semibold whitespace-nowrap"
                        style="color: {styles.text};">Horas</th
                      >
                      <th
                        class="text-center p-2 font-semibold whitespace-nowrap"
                        style="color: {styles.text};">Grado</th
                      >
                    </tr>
                  </thead>
                  <tbody>
                    {#each inasistenciasFiltradas as item, index}
                      {@const config = getMotivoConfig(item.motivo)}
                      <tr
                        class="border-b transition-colors hover:bg-black/5 dark:hover:bg-white/5 text-xs"
                        style="border-color: {styles.border};"
                      >
                        <td
                          class="p-2 whitespace-nowrap"
                          style="color: {styles.text};"
                          >{formatearFecha(item.fecha)}</td
                        >
                        <td class="p-2" style="color: {styles.text};">
                          <span
                            class="truncate block max-w-[120px]"
                            title={item.docente}>{item.docente}</span
                          >
                        </td>
                        <td
                          class="p-2 truncate max-w-[120px]"
                          style="color: {styles.text};"
                          title={item.materia}>{item.materia}</td
                        >
                        <td class="p-2" style="color: {styles.text};">
                          <span
                            class="truncate block max-w-[100px]"
                            title={item.nombre}
                          >
                            {#if filtros.docente && filtros.materia && filtros.grado}
                              <button
                                class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium text-left truncate w-full"
                                on:click={() =>
                                  calculateTotalHours(item.nombre)}
                              >
                                {item.nombre}
                              </button>
                            {:else}
                              {item.nombre}
                            {/if}
                          </span>
                        </td>
                        <td class="p-2 text-center">
                          <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium {config.color}"
                          >
                            <span>{config.icon}</span>
                            {item.motivo}
                          </span>
                        </td>
                        <td
                          class="p-2 whitespace-nowrap text-center"
                          style="color: {styles.text};">{item.horas}</td
                        >
                        <td
                          class="p-2 whitespace-nowrap text-center"
                          style="color: {styles.text};">{item.grado}</td
                        >
                      </tr>
                    {/each}
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Vista Mobile: Tarjetas -->
            <div class="flex-1 overflow-y-auto md:hidden px-1">
              <div class="space-y-2">
                {#each inasistenciasFiltradas as item, index}
                  {@const config = getMotivoConfig(item.motivo)}
                  <div
                    class="border rounded-lg p-2 transition-colors hover:bg-black/5 dark:hover:bg-white/5"
                    style="border-color: {styles.cardBorder}; background-color: {styles.cardBg};"
                  >
                    <!-- Header compacto de la tarjeta -->
                    <div class="flex justify-between items-center mb-1.5">
                      <div class="flex-1 min-w-0">
                        {#if filtros.docente && filtros.materia && filtros.grado}
                          <button
                            class="font-semibold text-xs text-indigo-600 dark:text-indigo-400 truncate hover:underline text-left w-full"
                            on:click={() => calculateTotalHours(item.nombre)}
                          >
                            {item.nombre}
                          </button>
                        {:else}
                          <p
                            class="font-semibold text-xs text-indigo-600 dark:text-indigo-400 truncate"
                          >
                            {item.nombre}
                          </p>
                        {/if}
                        <p
                          class="text-[10px] opacity-75"
                          style="color: {styles.label};"
                        >
                          {item.grado} • {formatearFecha(item.fecha)}
                        </p>
                      </div>
                      <div class="flex flex-col items-end ml-2 flex-shrink-0">
                        <span
                          class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium {config.color} mb-0.5"
                        >
                          <span>{config.icon}</span>
                          {item.motivo}
                        </span>
                        <span
                          class="text-[10px] font-semibold"
                          style="color: {styles.text};"
                        >
                          {item.horas}h
                        </span>
                      </div>
                    </div>

                    <!-- Información compacta del docente y materia -->
                    <div
                      class="flex gap-3 text-[10px] mb-1.5"
                      style="color: {styles.text};"
                    >
                      <div class="flex items-center flex-1 min-w-0">
                        <svg
                          class="w-3 h-3 mr-1 opacity-60 flex-shrink-0"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                          />
                        </svg>
                        <span class="truncate">{item.docente}</span>
                      </div>
                      <div class="flex items-center flex-1 min-w-0">
                        <svg
                          class="w-3 h-3 mr-1 opacity-60 flex-shrink-0"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                          />
                        </svg>
                        <span class="truncate" title={item.materia}
                          >{item.materia}</span
                        >
                      </div>
                    </div>

                    <!-- Observaciones si existen -->
                    {#if item.observaciones}
                      <div
                        class="text-[10px] p-1.5 rounded"
                        style="background-color: {styles.inputBg}; color: {styles.label};"
                      >
                        <div class="flex items-start">
                          <svg
                            class="w-3 h-3 mr-1 mt-0.5 flex-shrink-0 opacity-60"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                          >
                            <path
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                          </svg>
                          <span class="line-clamp-1">{item.observaciones}</span>
                        </div>
                      </div>
                    {/if}
                  </div>
                {/each}
              </div>
            </div>
          {/if}
        </div>
      {/if}
    </div>
  </div>
</div>
