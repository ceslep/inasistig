<script lang="ts">
  import { onMount } from "svelte";
  import {
    getDocentes,
    getMaterias,
    getEstudiantes,
    getDiario,
  } from "../../api/service";
  let jsPDF: any = null;
  let autoTable: any = null;
  import Loader from "./Loader.svelte";
  import { theme } from "../lib/themeStore";
  import {
    SPREADSHEET_ID_DIARIO,
    WORKSHEET_TITLE_DIARIO,
  } from "../constants";

  export let onClose: () => void;
  export let initialDocente: string = "";

  interface DiarioData {
    "Marca Temporal": string;
    Fecha: string;
    Horas: string;
    Docente: string;
    Asignatura: string;
    Grado: string;
    "Diario de Campo": string;
  }

  let docentes: string[] = [];
  let materias: { materia: string }[] = [];
  let estudiantes: { nombre: string; grado: number | string }[] = [];
  let diarioData: DiarioData[] = [];
  let filteredData: DiarioData[] = [];

  let isLoading = false;
  let isLoadingData = false;

  const loadPdfLibraries = async () => {
    if (!jsPDF || !autoTable) {
      const [jspdfModule, autoTableModule] = await Promise.all([
        import('jspdf'),
        import('jspdf-autotable')
      ]);
      jsPDF = jspdfModule.default;
      autoTable = autoTableModule.default;
    }
  };

  let docenteMaterias: Record<string, string[]> = JSON.parse(
    localStorage.getItem("docenteMateriasDiario") || "{}",
  );

  let selectedDocente = "";
  let selectedMateria = "";
  let selectedGrado = "";

  // Extraer número del docente cuando tiene patrón "Nombre-número"
  const getDocenteNumber = (docente: string): string | null => {
    const match = docente.match(/-(\d+)$/);
    return match ? match[1] : null;
  };

  // Verificar si el docente tiene "-"
  $: docenteHasDash = selectedDocente.includes("-");

  // Filtrar grupos según el número del docente
  $: docenteNumber = getDocenteNumber(selectedDocente);

  $: filteredGrados = docenteNumber
    ? [...new Set(estudiantes.map((e) => e.grado.toString()))].filter((g) =>
        g.startsWith(`${docenteNumber}-`),
      )
    : [...new Set(estudiantes.map((e) => e.grado.toString()))].filter((g) =>
        !g.includes('-')
      );

  onMount(() => {
    if (initialDocente) {
      selectedDocente = initialDocente;
    }
  });

  onMount(async () => {
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

      docenteMaterias = JSON.parse(
        localStorage.getItem("docenteMateriasDiario") || "{}",
      );
    } catch (error) {
      console.error("Error cargando datos iniciales:", error);
    } finally {
      isLoading = false;
    }
  };

  const loadDiarioData = async () => {
    isLoadingData = true;
    try {
      const payload: Record<string, string> = {
        spreadsheetId: SPREADSHEET_ID_DIARIO,
        worksheetTitle: WORKSHEET_TITLE_DIARIO,
      };
      if (selectedDocente) payload.docente = selectedDocente;
      if (selectedMateria) payload.materia = selectedMateria;
      if (selectedGrado) payload.grado = selectedGrado;

      const response = await getDiario(payload);

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

      diarioData = Array.isArray(rawData) ? rawData : [];

      if (diarioData.length > 0 && (diarioData[0] as any).values) {
        const headers = [
          "Marca Temporal",
          "Fecha",
          "Horas",
          "Docente",
          "Asignatura",
          "Grado",
          "Diario de Campo",
        ];
        diarioData = diarioData.map((row: any) => {
          const obj: DiarioData = {
            "Marca Temporal": "",
            Fecha: "",
            Horas: "",
            Docente: "",
            Asignatura: "",
            Grado: "",
            "Diario de Campo": ""
          };
          headers.forEach((header, index) => {
            (obj as any)[header] = row.values?.[index] || "";
          });
          return obj;
        });
      }

      applyFilters();
    } catch (error: unknown) {
      console.error("Error cargando datos del diario:", error);
      diarioData = [];
      filteredData = [];
    } finally {
      isLoadingData = false;
    }
  };

  const applyFilters = () => {
    if (!Array.isArray(diarioData)) {
      filteredData = [];
      return;
    }

    filteredData = diarioData.filter((item) => {
      if (!item || typeof item !== "object") return false;

      const matchesDocente =
        !selectedDocente || item["Docente"] === selectedDocente;
      const matchesMateria =
        !selectedMateria || item["Asignatura"] === selectedMateria;
      const matchesGrado = !selectedGrado || item["Grado"] === selectedGrado;

      return matchesDocente && matchesMateria && matchesGrado;
    });
  };

  const handleFilter = () => {
    loadDiarioData();
  };

  const handleClearFilters = () => {
    selectedDocente = "";
    selectedMateria = "";
    selectedGrado = "";
    diarioData = [];
    filteredData = [];
  };

  const generatePDF = async () => {
    await loadPdfLibraries();
    
    if (filteredData.length === 0) {
      alert("No hay datos para generar el PDF");
      return;
    }

    const pdf = new jsPDF({ format: 'letter' });
    const reportTitle = "Diario de Campo";
    const reportFilters = [];
    if (selectedDocente) reportFilters.push(`Docente: ${selectedDocente}`);
    if (selectedMateria) reportFilters.push(`Asignatura: ${selectedMateria}`);
    if (selectedGrado) reportFilters.push(`Grado: ${selectedGrado}`);

    let yPosition = 20;

    pdf.setTextColor(99, 102, 241);
    pdf.setFontSize(28);
    pdf.setFont("helvetica", "bold");
    pdf.text("EIE", pdf.internal.pageSize.getWidth() / 2, yPosition, {
      align: "center",
    });
    yPosition += 15;

    pdf.setTextColor(0, 0, 0);
    pdf.setFontSize(18);
    pdf.setFont("helvetica", "bold");
    pdf.text(reportTitle, pdf.internal.pageSize.getWidth() / 2, yPosition, {
      align: "center",
    });
    yPosition += 12;

    pdf.setTextColor(0, 0, 0);
    pdf.setFontSize(12);
    pdf.setFont("helvetica", "normal");
    pdf.text(
      "Institución Educativa Instituto Guática",
      pdf.internal.pageSize.getWidth() / 2,
      yPosition,
      { align: "center" },
    );
    yPosition += 10;

    pdf.setTextColor(60, 60, 60);
    pdf.setFontSize(10);
    pdf.setFont("helvetica", "normal");
    pdf.text(
      `Fecha de generación: ${new Date().toLocaleDateString()}`,
      pdf.internal.pageSize.getWidth() / 2,
      yPosition,
      { align: "center" },
    );
    yPosition += 15;

    pdf.setDrawColor(200, 200, 200);
    pdf.setLineWidth(1);
    pdf.line(20, yPosition, pdf.internal.pageSize.getWidth() - 20, yPosition);
    yPosition += 15;

    if (reportFilters.length > 0) {
      pdf.setTextColor(80, 80, 80);
      pdf.setFontSize(9);
      pdf.setFont("helvetica", "normal");
      reportFilters.forEach((filter) => {
        pdf.text(filter, 20, yPosition, { align: "left" });
        yPosition += 6;
      });
      yPosition += 10;
    }

    const tableData = filteredData.map((item) => [
      item["Fecha"] || "",
      item["Horas"] || "",
      item["Asignatura"] || "",
      item["Grado"] || "",
      item["Diario de Campo"] || "",
    ]);

    tableData.push([
      "TOTAL",
      totalHoras.toFixed(1),
      "",
      "",
      `${filteredData.length} registros`,
    ]);

    autoTable(pdf, {
      head: [["Fecha", "Horas", "Asignatura", "Grado", "Diario de Campo"]],
      body: tableData,
      startY: yPosition,
      styles: {
        fontSize: 8,
        cellPadding: 2,
        cellWidth: "wrap",
        valign: "top",
        overflow: "linebreak",
      },
      headStyles: {
        fillColor: [99, 102, 241],
        textColor: 255,
        fontSize: 9,
        fontStyle: "bold",
        halign: "center",
      },
      bodyStyles: {
        fillColor: [255, 255, 255],
        textColor: [0, 0, 0],
      },
      alternateRowStyles: {
        fillColor: [248, 250, 252],
      },
      columnStyles: {
        0: { cellWidth: 25, minCellHeight: 8, halign: "center" },
        1: { cellWidth: 15, minCellHeight: 8, halign: "center" },
        2: { cellWidth: 35, minCellHeight: 8 },
        3: { cellWidth: 20, minCellHeight: 8, halign: "center" },
        4: { cellWidth: "auto", minCellHeight: 12 },
      },
      margin: { top: 15, right: 15, bottom: 15, left: 15 },
      tableWidth: "auto",
      didParseCell: (data: any) => {
        if (data.row.index === tableData.length - 1) {
          data.cell.styles.fillColor = [240, 240, 240];
          data.cell.styles.fontStyle = "bold";
          data.cell.styles.textColor = [0, 0, 0];
        }
      },
    });

    const fileName = `reporte_diario_${selectedDocente || "todos"}_${selectedMateria || "todas"}_${selectedGrado || "todos"}_${new Date().toISOString().split("T")[0]}.pdf`;
    pdf.save(fileName);
  };

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
              d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            />
          </svg>
        </div>
        <div>
          <h2 id="modal-title" class="text-xl font-bold">
            Generador de Reportes PDF
          </h2>
          <p class="text-sm opacity-75">
            Filtre y genere reportes del Diario de Campo
          </p>
        </div>
      </div>
      <button
        on:click={onClose}
        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
        aria-label="Cerrar ventana"
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

    <div class="p-6 overflow-y-auto" style="max-height: calc(90vh - 140px);">
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
          <Loader message="Cargando datos del diario..." />
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
              d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            />
          </svg>
          <p class="text-lg font-medium mb-2">No hay datos disponibles</p>
          <p class="text-sm">
            {selectedDocente || selectedMateria || selectedGrado
              ? "No se encontraron registros con los filtros seleccionados."
              : "Seleccione filtros y haga clic en 'Aplicar Filtros'."}
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
                  Horas
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
                  Diario de Campo
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
                  <td class="px-4 py-3 text-sm">{item["Horas"] || "-"}</td>
                  <td class="px-4 py-3 text-sm">{item["Asignatura"] || "-"}</td>
                  <td class="px-4 py-3 text-sm">{item["Grado"] || "-"}</td>
                  <td class="px-4 py-3 text-sm">
                    <div class="truncate" title={item["Diario de Campo"]}>
                      {item["Diario de Campo"] || "-"}
                    </div>
                  </td>
                </tr>
              {/each}
              {#if filteredData.length > 0}
                <tr
                  class="border-t-2 font-bold bg-gray-100 dark:bg-gray-800"
                  style="border-color: {styles.border}; border-top-width: 2px;"
                >
                  <td class="px-4 py-3 text-sm font-bold">TOTAL</td>
                  <td class="px-4 py-3 text-sm font-bold text-center">
                    {totalHoras.toFixed(1)}
                  </td>
                  <td class="px-4 py-3 text-sm"></td>
                  <td class="px-4 py-3 text-sm"></td>
                  <td class="px-4 py-3 text-sm font-bold text-center">
                    {filteredData.length} registros
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
