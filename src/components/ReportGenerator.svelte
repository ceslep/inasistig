<script lang="ts">
  import { onMount } from "svelte";
  import {
    getDocentes,
    getMaterias,
    getEstudiantes,
    getAnotador,
  } from "../../api/service";
  import { jsPDF } from "jspdf";
  import autoTable from "jspdf-autotable";
  import Loader from "./Loader.svelte";
  import { theme } from "../lib/themeStore";
  import {
    SPREADSHEET_ID_ANOTADOR,
    WORKSHEET_TITLE_ANOTADOR,
  } from "../constants";
  import eieLogo from "../assets/eie.png";

  export let onClose: () => void;
  export let initialDocente: string = "";

  interface AnotadorData {
    "Marca Temporal": string;
    Fecha: string;
    Docente: string;
    Asignatura: string;
    Grado: string;
    Horas: string;
    Anotación: string;
  }

  let docentes: string[] = [];
  let materias: { materia: string }[] = [];
  let estudiantes: { nombre: string; grado: number | string }[] = [];
  let anotadorData: AnotadorData[] = [];
  let filteredData: AnotadorData[] = [];

  let isLoading = false;
  let isLoadingData = false;

  let docenteMaterias: Record<string, string[]> = JSON.parse(
    localStorage.getItem("docenteMaterias") || "{}",
  );

  let selectedDocente = "";
  let selectedMateria = "";
  let selectedGrado = "";

  // Inicializar con el docente pasado desde Anotador.svelte
  onMount(() => {
    if (initialDocente) {
      selectedDocente = initialDocente;
    }
  });

  onMount(async () => {
    console.log("🚀 ReportGenerator montado");
    await loadInitialData();
  });

  const loadInitialData = async () => {
    isLoading = true;
    try {
      const [docentesData, materiasData, estudiantesData] = await Promise.all([
        getDocentes(),
        getMaterias(),
        getEstudiantes(),
      ]);
      docentes = docentesData;
      materias = materiasData;
      estudiantes = estudiantesData;

      // Cargar materias guardadas para docentes desde localStorage
      docenteMaterias = JSON.parse(
        localStorage.getItem("docenteMaterias") || "{}",
      );
    } catch (error) {
      console.error("Error cargando datos iniciales:", error);
    } finally {
      isLoading = false;
    }
  };

  const loadAnotadorData = async () => {
    isLoadingData = true;
    try {
      const payload: any = {
        spreadsheetId: SPREADSHEET_ID_ANOTADOR,
        worksheetTitle: WORKSHEET_TITLE_ANOTADOR,
      };
      if (selectedDocente) payload.docente = selectedDocente;
      if (selectedMateria) payload.materia = selectedMateria;
      if (selectedGrado) payload.grado = selectedGrado;

      console.log("🔍 Enviando payload a getAnotador:", payload);
      const response = await getAnotador(payload);
      console.log("📊 Respuesta de getAnotador:", response);

      // Intentar extraer datos de diferentes formatos posibles
      let rawData = response;
      if (response && typeof response === "object") {
        if (response.data) {
          rawData = response.data;
        } else if (response.records) {
          rawData = response.records;
        } else if (Array.isArray(response)) {
          rawData = response;
        }
      }

      // Asegurarnos de que sea un array
      anotadorData = Array.isArray(rawData) ? rawData : [];
      console.log("✅ Datos procesados:", anotadorData.length, "registros");
      console.log("📋 Primer registro:", anotadorData[0]);
      console.log(
        "🔍 Keys del primer registro:",
        anotadorData[0] ? Object.keys(anotadorData[0]) : "No hay registros",
      );

      // Si los datos vienen con formato .values, transformarlos
      if (anotadorData.length > 0 && (anotadorData[0] as any).values) {
        console.log("🔄 Transformando datos desde formato .values");
        const headers = [
          "Marca Temporal",
          "Fecha",
          "Docente",
          "Asignatura",
          "Grado",
          "Horas",
          "Anotación",
        ];
        anotadorData = anotadorData.map((row: any) => {
          const obj: any = {};
          headers.forEach((header, index) => {
            obj[header] = row.values?.[index] || "";
          });
          return obj;
        });
        console.log(
          "📊 Datos transformados:",
          anotadorData.length,
          "registros",
        );
        console.log("📋 Primer registro transformado:", anotadorData[0]);
      }

      applyFilters();
    } catch (error: any) {
      console.error("❌ Error cargando datos del anotador:", error);
      console.error("📋 Error details:", error?.message);
      if (error?.response) {
        console.error("🌐 Response status:", error.response.status);
        console.error("🌐 Response data:", error.response.data);
      }
      anotadorData = [];
      filteredData = [];
    } finally {
      isLoadingData = false;
    }
  };

  const applyFilters = () => {
    console.log("🔍 Aplicando filtros locales:", {
      totalRegistros: anotadorData.length,
      isArray: Array.isArray(anotadorData),
      docente: selectedDocente,
      materia: selectedMateria,
      grado: selectedGrado,
    });

    if (!Array.isArray(anotadorData)) {
      console.error(
        "❌ anotadorData no es un array:",
        typeof anotadorData,
        anotadorData,
      );
      filteredData = [];
      return;
    }

    filteredData = anotadorData.filter((item) => {
      // Validar que el item tenga la estructura esperada
      if (!item || typeof item !== "object") {
        console.log("⚠️ Registro inválido:", item);
        return false;
      }

      const matchesDocente =
        !selectedDocente || item["Docente"] === selectedDocente;
      const matchesMateria =
        !selectedMateria || item["Asignatura"] === selectedMateria;
      const matchesGrado = !selectedGrado || item["Grado"] === selectedGrado;

      // Debug para ver por qué un registro podría no coincidir
      if (!matchesDocente && selectedDocente) {
        console.log("❌ No coincide docente:", {
          expected: selectedDocente,
          actual: item["Docente"],
        });
      }
      if (!matchesMateria && selectedMateria) {
        console.log("❌ No coincide materia:", {
          expected: selectedMateria,
          actual: item["Asignatura"],
        });
      }
      if (!matchesGrado && selectedGrado) {
        console.log("❌ No coincide grado:", {
          expected: selectedGrado,
          actual: item["Grado"],
        });
      }

      return matchesDocente && matchesMateria && matchesGrado;
    });

    console.log("✅ Registros filtrados:", filteredData.length);
  };

  const handleFilter = () => {
    loadAnotadorData();
  };

  const handleClearFilters = () => {
    selectedDocente = "";
    selectedMateria = "";
    selectedGrado = "";
    anotadorData = [];
    filteredData = [];
  };

  const generatePDF = () => {
    if (filteredData.length === 0) {
      alert("No hay datos para generar el PDF");
      return;
    }

    const doc = new jsPDF();

    const title = "Anotador de Clases";
    const filters = [];
    if (selectedDocente) filters.push(`Docente: ${selectedDocente}`);
    if (selectedMateria) filters.push(`Asignatura: ${selectedMateria}`);
    if (selectedGrado) filters.push(`Grado: ${selectedGrado}`);

    // Encabezado con logo e institución
    let yPosition = 15;

    // Logo EIE - intento con diferentes métodos
    try {
      // Método 1: Intentar con la ruta del logo
      console.log("Intentando agregar logo...");

      // Método 2: Si falla el logo, usar texto alternativo grande
      doc.setFontSize(24);
      doc.setTextColor(99, 102, 241); // Color indigo como en la app
      doc.text("EIE", 105, yPosition, { align: "center" });
      yPosition += 12;
    } catch (error) {
      console.log("Error en el logo:", error);
      doc.setFontSize(16);
      doc.text("EIE", 105, yPosition, { align: "center" });
      yPosition += 10;
    }

    // Título del reporte
    doc.setTextColor(0, 0, 0); // Negro para el título
    doc.setFontSize(16);
    doc.text(title, 105, yPosition, { align: "center" });
    yPosition += 8;

    // Nombre de la institución
    doc.setFontSize(11);
    doc.text("Institución Educativa Instituto Guática", 105, yPosition, {
      align: "center",
    });
    yPosition += 8;

    // Línea separadora
    doc.setDrawColor(200, 200, 200);
    doc.setLineWidth(0.5);
    doc.line(20, yPosition, 190, yPosition);
    yPosition += 10;

    // Fecha de generación
    doc.setFontSize(10);
    doc.setTextColor(100, 100, 100);
    doc.text(
      `Fecha de generación: ${new Date().toLocaleDateString()}`,
      105,
      yPosition,
      { align: "center" },
    );
    yPosition += 8;

    // Filtros aplicados
    if (filters.length > 0) {
      doc.setTextColor(80, 80, 80);
      filters.forEach((filter) => {
        doc.text(filter, 20, yPosition);
        yPosition += 6;
      });
      yPosition += 8; // Espacio extra antes de la tabla
    }

    const tableData = filteredData.map((item) => [
      item["Fecha"] || "",
      item["Docente"] || "",
      item["Asignatura"] || "",
      item["Grado"] || "",
      item["Horas"] || "",
      item["Anotación"] || "",
      "", // Observación no existe en la hoja
    ]);

    // Agregar fila de totales
    const totalRow = [
      "",
      "",
      "",
      "TOTAL",
      totalHoras.toFixed(1), // Mostrar con 1 decimal
      `${filteredData.length} registros`,
      "",
    ];
    tableData.push(totalRow);

    console.log("📊 Totales calculados:", {
      totalHoras,
      registros: filteredData.length,
      totalRow,
    });

    autoTable(doc, {
      head: [["Fecha", "Docente", "Asignatura", "Grado", "Horas", "Anotación"]],
      body: tableData,
      startY: filters.length > 0 ? yPosition + 10 : yPosition,
      // Estilo especial para la fila de totales
      didParseCell: (data) => {
        if (data.row.index === tableData.length - 1) {
          // Última fila (totales)
          data.cell.styles.fillColor = [240, 240, 240];
          data.cell.styles.fontStyle = "bold";
          data.cell.styles.textColor = [0, 0, 0];
          // Alineación especial para las celdas de totales
          if (data.column.index === 1) {
            // Columna "TOTAL"
            data.cell.styles.halign = "center";
          }
          if (data.column.index === 3) {
            // Columna de horas totales
            data.cell.styles.halign = "center";
            data.cell.styles.fillColor = [230, 230, 230];
          }
          if (data.column.index === 4) {
            // Columna de registros totales
            data.cell.styles.halign = "center";
            data.cell.styles.fillColor = [230, 230, 230];
          }
        }
      },
      styles: {
        fontSize: 8,
        cellPadding: 3,
      },
      headStyles: {
        fillColor: [99, 102, 241],
        textColor: 255,
        fontSize: 9,
        fontStyle: "bold",
      },
      alternateRowStyles: {
        fillColor: [248, 250, 252],
      },
      columnStyles: {
        0: { cellWidth: 20 }, // Fecha
        1: { cellWidth: 30 }, // Docente
        2: { cellWidth: 30 }, // Asignatura
        3: { cellWidth: 15 }, // Grado
        4: { cellWidth: 15 }, // Horas
        5: { cellWidth: 60 }, // Anotación
      },
      margin: { top: 20, right: 20, bottom: 20, left: 20 },
    });

    const fileName = `reporte_anotaciones_${selectedDocente || "todos"}_${selectedMateria || "todas"}_${selectedGrado || "todos"}_${new Date().toISOString().split("T")[0]}.pdf`;
    doc.save(fileName);
  };

  // Calcular totales y materias destacadas
  $: totalHoras = filteredData.reduce((sum, item) => {
    const horas = parseFloat(item["Horas"] || "0");
    return sum + (isNaN(horas) ? 0 : horas);
  }, 0);

  $: sortedMaterias = selectedDocente
    ? [...materias].sort((a, b) => {
        const aSaved = docenteMaterias[selectedDocente]?.includes(a.materia);
        const bSaved = docenteMaterias[selectedDocente]?.includes(b.materia);
        if (aSaved && !bSaved) return -1;
        if (!aSaved && bSaved) return 1;
        return a.materia.localeCompare(b.materia);
      })
    : materias;

  $: styles = {
    bg: "rgb(var(--bg-primary))",
    text: "rgb(var(--text-primary))",
    label: "rgb(var(--text-secondary))",
    border: "rgb(var(--border-primary))",
    placeholder: "rgb(var(--text-muted))",
    cardBg: "rgb(var(--card-bg))",
    cardBorder: "rgb(var(--card-border))",
    inputBg: "rgb(var(--bg-secondary))",
  };
</script>

<div
  class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
  role="dialog"
  aria-modal="true"
  aria-labelledby="modal-title"
  tabindex="-1"
  on:click|self={onClose}
  on:keydown={(e) => e.key === "Escape" && onClose()}
>
  <div
    class="bg-white dark:bg-gray-900 rounded-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden shadow-2xl"
    style="background-color: {styles.cardBg}; color: {styles.text};"
    role="document"
  >
    <!-- Header -->
    <div
      class="flex items-center justify-between p-6 border-b"
      style="border-color: {styles.cardBorder};"
    >
      <div class="flex items-center gap-3">
        <div
          class="p-2 rounded-lg bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200"
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
              d="M9 17v2a2 2 0 002 2h6a2 2 0 002-2v-2M9 17H5a2 2 0 01-2-2v-6a2 2 0 012-2h4M9 7V5a2 2 0 012-2h6a2 2 0 012 2v2M9 7h10a2 2 0 012 2v6a2 2 0 01-2 2H9M9 7V17m0 0h10"
            />
          </svg>
        </div>
        <div>
          <h2 id="modal-title" class="text-xl font-bold">
            Generador de Reportes PDF
          </h2>
          <p class="text-sm opacity-75">
            Filtre y genere reportes de anotaciones
          </p>
        </div>
      </div>
      <button
        on:click={onClose}
        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
        aria-label="Cerrar ventana de reportes"
        title="Cerrar"
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
            d="M6 18L18 6M6 6l12 12"
          />
        </svg>
      </button>
    </div>

    <!-- Content -->
    <div class="p-6 overflow-y-auto" style="max-height: calc(90vh - 140px);">
      <!-- Filters -->
      <div
        class="mb-6 p-4 rounded-xl border"
        style="background-color: {styles.inputBg}; border-color: {styles.border};"
      >
        <h3 class="text-lg font-semibold mb-4">Filtros</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="space-y-2">
            <label
              for="docente-filter"
              class="block text-sm font-medium"
              style="color: {styles.label};"
            >
              Docente
            </label>
            <select
              id="docente-filter"
              bind:value={selectedDocente}
              class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 transition-all outline-none"
              style="background-color: {styles.cardBg}; border-color: {styles.border}; color: {styles.text};"
            >
              <option value="">Todos los docentes</option>
              {#each docentes as docente}
                <option value={docente}>{docente}</option>
              {/each}
            </select>
          </div>

          <div class="space-y-2">
            <label
              for="materia-filter"
              class="block text-sm font-medium"
              style="color: {styles.label};"
            >
              Asignatura
            </label>
            <select
              id="materia-filter"
              bind:value={selectedMateria}
              class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 transition-all outline-none"
              style="background-color: {styles.cardBg}; border-color: {styles.border}; color: {styles.text};"
            >
              <option value="">Todas las asignaturas</option>
              {#each sortedMaterias as materia}
                {@const isSaved =
                  selectedDocente &&
                  docenteMaterias[selectedDocente]?.includes(materia.materia)}
                <option
                  value={materia.materia}
                  style={isSaved ? "color: #6366f1; font-weight: 600;" : ""}
                >
                  {isSaved ? "⭐ " : ""}{materia.materia}
                </option>
              {/each}
            </select>
          </div>

          <div class="space-y-2">
            <label
              for="grado-filter"
              class="block text-sm font-medium"
              style="color: {styles.label};"
            >
              Grado
            </label>
            <select
              id="grado-filter"
              bind:value={selectedGrado}
              class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 transition-all outline-none"
              style="background-color: {styles.cardBg}; border-color: {styles.border}; color: {styles.text};"
            >
              <option value="">Todos los grados</option>
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

        <div class="flex gap-3 mt-4">
          <button
            on:click={handleFilter}
            disabled={isLoadingData}
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors disabled:opacity-50 flex items-center gap-2"
          >
            {#if isLoadingData}
              <svg
                class="animate-spin h-4 w-4"
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
              Cargando...
            {:else}
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
                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"
                />
              </svg>
              Aplicar Filtros
            {/if}
          </button>
          <button
            on:click={handleClearFilters}
            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-lg transition-colors flex items-center gap-2"
            style="color: {styles.text};"
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
            Limpiar
          </button>
          <button
            on:click={generatePDF}
            disabled={filteredData.length === 0}
            class="ml-auto px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white rounded-lg transition-colors flex items-center gap-2"
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
            Generar PDF
          </button>
        </div>
      </div>

      <!-- Results -->
      <div class="mb-4">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold">
            Resultados ({filteredData.length} registros)
          </h3>
          {#if filteredData.length > 0}
            <div class="text-sm opacity-75">
              {selectedDocente && `Docente: ${selectedDocente}`}
              {selectedMateria && ` | Asignatura: ${selectedMateria}`}
              {selectedGrado && ` | Grado: ${selectedGrado}`}
            </div>
          {/if}
        </div>
      </div>

      {#if isLoadingData}
        <div class="flex items-center justify-center py-12">
          <Loader message="Cargando datos del anotador..." />
        </div>
      {:else if filteredData.length === 0}
        <div class="text-center py-12" style="color: {styles.placeholder};">
          <svg
            class="w-16 h-16 mx-auto mb-4 opacity-50"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 17v2a2 2 0 002 2h6a2 2 0 002-2v-2M9 17H5a2 2 0 01-2-2v-6a2 2 0 012-2h4M9 7V5a2 2 0 012-2h6a2 2 0 012 2v2M9 7h10a2 2 0 012 2v6a2 2 0 01-2 2H9M9 7V17m0 0h10"
            />
          </svg>
          <p class="text-lg font-medium mb-2">No hay datos disponibles</p>
          <p class="text-sm">
            {selectedDocente || selectedMateria || selectedGrado
              ? "No se encontraron registros con los filtros seleccionados. Intente con otros criterios."
              : "Seleccione filtros y haga clic en 'Aplicar Filtros' para cargar los datos."}
          </p>
        </div>
      {:else}
        <div
          class="overflow-x-auto rounded-lg border"
          style="border-color: {styles.border};"
        >
          <table class="w-full">
            <thead style="background-color: {styles.inputBg};">
              <tr>
                <th
                  class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider"
                  style="color: {styles.label};"
                >
                  Fecha
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider"
                  style="color: {styles.label};"
                >
                  Docente
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider"
                  style="color: {styles.label};"
                >
                  Asignatura
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider"
                  style="color: {styles.label};"
                >
                  Grado
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider"
                  style="color: {styles.label};"
                >
                  Horas
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider"
                  style="color: {styles.label};"
                >
                  Anotación
                </th>
              </tr>
            </thead>
            <tbody>
              {#each filteredData as item, index}
                <tr
                  class="border-t transition-colors hover:bg-gray-50 dark:hover:bg-gray-800"
                  style="border-color: {styles.border};"
                >
                  <td class="px-4 py-3 text-sm">{item["Fecha"] || "-"}</td>
                  <td class="px-4 py-3 text-sm">{item["Docente"] || "-"}</td>
                  <td class="px-4 py-3 text-sm">{item["Asignatura"] || "-"}</td>
                  <td class="px-4 py-3 text-sm">{item["Grado"] || "-"}</td>
                  <td class="px-4 py-3 text-sm">{item["Horas"] || "-"}</td>
                  <td class="px-4 py-3 text-sm max-w-xs">
                    <div class="truncate" title={item["Anotación"]}>
                      {item["Anotación"] || "-"}
                    </div>
                  </td>
                </tr>
              {/each}
              <!-- Fila de totales -->
              {#if filteredData.length > 0}
                <tr
                  class="border-t-2 font-bold bg-gray-100 dark:bg-gray-800"
                  style="border-color: {styles.border}; border-top-width: 2px;"
                >
                  <td class="px-4 py-3 text-sm" colspan="3"></td>
                  <td class="px-4 py-3 text-sm font-bold"
                    >{totalHoras.toFixed(0)}</td
                  >
                  <td class="px-4 py-3 text-sm max-w-xs">
                    {filteredData.length} totalHoras
                  </td>
                </tr>
              {/if}
            </tbody>
          </table>
        </div>
      {/if}
    </div>
  </div>
</div>
