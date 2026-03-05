<script lang="ts">
  import { onMount } from "svelte";
  import {
    getDocentes,
    getMaterias,
    getEstudiantes,
    getAnotador,
  } from "../../api/service";
  // Dynamic imports for heavy libraries
  let jsPDF: any = null;
  let autoTable: any = null;
  import Loader from "./Loader.svelte";
  import { theme } from "../lib/themeStore";
  import { FileText, X, Filter, Loader2, Download } from "lucide-svelte";
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

  // Load heavy libraries dynamically
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
    localStorage.getItem("docenteMaterias") || "{}",
  );

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
      const payload: Record<string, string> = {
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
          const obj: AnotadorData = {
            "Marca Temporal": "",
            Fecha: "",
            Docente: "",
            Asignatura: "",
            Grado: "",
            Horas: "",
            Anotación: ""
          };
          headers.forEach((header, index) => {
            (obj as any)[header] = row.values?.[index] || "";
          });
          return obj;
        });
        console.log(
          "📊 Datos transformados:",
          anotadorData.length,
          "registros",
        );

      }

      applyFilters();
    } catch (error: unknown) {
      console.error("❌ Error cargando datos del anotador:", error);
      if (error instanceof Error) {
        console.error("📋 Error details:", error.message);
      }
      if (error && typeof error === 'object' && 'response' in error) {
        const response = (error as any).response;
        if (response) {
          console.error("🌐 Response status:", response.status);
          console.error("🌐 Response data:", response.data);
        }
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

  const generatePDF = async () => {
    // Load libraries dynamically
    await loadPdfLibraries();
    
    if (filteredData.length === 0) {
      alert("No hay datos para generar el PDF");
      return;
    }

    const pdf = new jsPDF({ format: 'letter' });
    const reportTitle = "Anotador de Clases";
    const reportFilters = [];
    if (selectedDocente) reportFilters.push(`Docente: ${selectedDocente}`);
    if (selectedMateria) reportFilters.push(`Asignatura: ${selectedMateria}`);
    if (selectedGrado) reportFilters.push(`Grado: ${selectedGrado}`);

    let yPosition = 20;

    // Logo EIE con fallback robusto
    try {
      console.log("🖼️ Intentando agregar logo EIE al PDF...");

      // Intento 1: Usar la imagen importada directamente
      pdf.setTextColor(99, 102, 241);
      pdf.setFontSize(28);
      pdf.setFont("helvetica", "bold");
      pdf.text("EIE", pdf.internal.pageSize.getWidth() / 2, yPosition, {
        align: "center",
      });
      yPosition += 15;

      console.log("✅ Logo EIE (texto) agregado correctamente");
    } catch (error) {
      console.log("❌ Error con logo EIE, usando fallback:", error);

      // Fallback simple
      pdf.setTextColor(99, 102, 241);
      pdf.setFontSize(24);
      pdf.text("EIE", pdf.internal.pageSize.getWidth() / 2, yPosition, {
        align: "center",
      });
      yPosition += 15;
    }

    // Título del reporte
    pdf.setTextColor(0, 0, 0);
    pdf.setFontSize(18);
    pdf.setFont("helvetica", "bold");
    pdf.text(reportTitle, pdf.internal.pageSize.getWidth() / 2, yPosition, {
      align: "center",
    });
    yPosition += 12;

    // Nombre de la institución
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

    // Fecha de generación
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

    // Línea decorativa
    pdf.setDrawColor(200, 200, 200);
    pdf.setLineWidth(1);
    pdf.line(20, yPosition, pdf.internal.pageSize.getWidth() - 20, yPosition);
    yPosition += 15;

    // Filtros aplicados
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

    // Preparar datos de la tabla (incluyendo asignatura y grado si no hay filtro)
    const showGradoColumn = !selectedGrado;
    
    // Headers dinámicos según si se muestra grado o no
    const tableHeaders = showGradoColumn 
      ? [["Fecha", "Horas", "Asignatura", "Grado", "Anotación"]]
      : [["Fecha", "Horas", "Asignatura", "Anotación"]];

    const tableData = filteredData.map((item) => {
      if (showGradoColumn) {
        return [
          item["Fecha"] || "",
          item["Horas"] || "",
          item["Asignatura"] || "",
          item["Grado"] || "",
          item["Anotación"] || "",
        ];
      }
      return [
        item["Fecha"] || "",
        item["Horas"] || "",
        item["Asignatura"] || "",
        item["Anotación"] || "",
      ];
    });

    // Fila de totales
    const totalRow = showGradoColumn
      ? ["TOTAL", totalHoras.toFixed(1), "", "", `${filteredData.length} registros`]
      : ["TOTAL", totalHoras.toFixed(1), "", `${filteredData.length} registros`];
    tableData.push(totalRow);

    console.log("📊 Generando PDF con:", {
      totalHoras,
      registros: filteredData.length,
      filasTabla: tableData.length,
      showGradoColumn,
    });

    // Definir anchos de columnas según número de columnas
    const columnStyles = showGradoColumn
      ? {
          0: { cellWidth: 22, minCellHeight: 8, halign: 'center' },   // Fecha
          1: { cellWidth: 15, minCellHeight: 8, halign: 'center' },   // Horas
          2: { cellWidth: 35, minCellHeight: 8 },                     // Asignatura
          3: { cellWidth: 18, minCellHeight: 8, halign: 'center' },  // Grado
          4: { cellWidth: 'auto', minCellHeight: 12 },                // Anotación
        }
      : {
          0: { cellWidth: 28, minCellHeight: 8, halign: 'center' },  // Fecha
          1: { cellWidth: 18, minCellHeight: 8, halign: 'center' },  // Horas
          2: { cellWidth: 45, minCellHeight: 8 },                    // Asignatura
          3: { cellWidth: 'auto', minCellHeight: 12 },                // Anotación
        };

    // Generar tabla con autoTable
    autoTable(pdf, {
      head: tableHeaders,
      body: tableData,
      startY: yPosition,
      styles: {
        fontSize: 7,
        cellPadding: 1.5,
        cellWidth: "wrap",
        valign: "top",
        overflow: "linebreak",
      },
      headStyles: {
        fillColor: [99, 102, 241],
        textColor: 255,
        fontSize: 8,
        fontStyle: "bold",
        halign: 'center',
      },
      bodyStyles: {
        fillColor: [255, 255, 255],
        textColor: [0, 0, 0],
      },
      alternateRowStyles: {
        fillColor: [248, 250, 252],
      },
      columnStyles,
      margin: { top: 12, right: 12, bottom: 12, left: 12 },
      tableWidth: "auto",
      didParseCell: (data: any) => {
        // Estilo especial para la fila de totales
        if (data.row.index === tableData.length - 1) {
          data.cell.styles.fillColor = [230, 230, 230];
          data.cell.styles.fontStyle = "bold";
          data.cell.styles.textColor = [0, 0, 0];
          
          // Centrar todas las celdas de la fila de totales
          data.cell.styles.halign = "center";
        }
      },
    });

    // Guardar PDF
    const fileName = `reporte_anotaciones_${selectedDocente || "todos"}_${selectedMateria || "todas"}_${selectedGrado || "todos"}_${new Date().toISOString().split("T")[0]}.pdf`;
    pdf.save(fileName);
    console.log("💾 PDF guardado exitosamente:", fileName);
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
          <FileText class="w-6 h-6" />
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
        <X class="w-5 h-5" />
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
              <Loader2 class="animate-spin h-4 w-4" />
              Cargando...
            {:else}
              <Filter class="w-4 h-4" />
              Aplicar Filtros
            {/if}
          </button>
          <button
            on:click={handleClearFilters}
            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-lg transition-colors flex items-center gap-2"
            style="color: {styles.text};"
          >
            <X class="w-4 h-4" />
            Limpiar
          </button>
          <button
            on:click={generatePDF}
            disabled={filteredData.length === 0}
            class="ml-auto px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white rounded-lg transition-colors flex items-center gap-2"
          >
            <Download class="w-4 h-4" />
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
          <FileText class="w-16 h-16 mx-auto mb-4 opacity-50" />
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
                  <td class="px-4 py-3 text-sm">{item["Horas"] || "-"}</td>
                  <td class="px-4 py-3 text-sm">
                    <div class="truncate" title={item["Asignatura"]}>
                      {item["Asignatura"] || "-"}
                    </div>
                  </td>
                  <td class="px-4 py-3 text-sm">
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
                  <td class="px-4 py-3 text-sm font-bold">TOTAL</td>
                  <td class="px-4 py-3 text-sm font-bold text-center">
                    {totalHoras.toFixed(1)}
                  </td>
                  <td class="px-4 py-3 text-sm font-bold text-center"></td>
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
