<script lang="ts">
  import { onMount } from "svelte";
  import {
    getDocentes,
    getMaterias,
    getEstudiantes,
    getInasistencias,
  } from "../../api/service";
  // Dynamic imports for heavy libraries
  let ExcelJS: any = null;
  let saveAs: any = null;
  import Loader from "./Loader.svelte";
  import { theme } from "../lib/themeStore";
  import {
    SPREADSHEET_ID,
    WORKSHEET_TITLE,
    periodos,
  } from "../constants";
  import eieLogo from "../assets/eie.png";
  import Swal from "sweetalert2";

  export let onClose: () => void;
  export let initialDocente: string = "";

  interface InasistenciaData {
    fecha: string;
    docente: string;
    materia: string;
    grado: string;
    nombre: string;
    motivo: string;
    horas: string;
  }

  let docentes: string[] = [];
  let materias: { materia: string }[] = [];
  let estudiantes: { nombre: string; grado: number | string }[] = [];
  
  let selectedDocente = initialDocente || "";
  let selectedMateria = "";
  let selectedGrado = "";
  let selectedPeriodo = periodos[0].nombre;

  let isLoading = false;
  let isGenerating = false;

  // Load heavy libraries dynamically
  const loadLibraries = async () => {
    if (!ExcelJS || !saveAs) {
      const [exceljsModule, fileSaverModule] = await Promise.all([
        import('exceljs'),
        import('file-saver')
      ]);
      ExcelJS = exceljsModule.default;
      saveAs = fileSaverModule.default;
    }
  };

  const normalize = (str: any) => {
    if (!str) return "";
    return str.toString()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .replace(/[^a-zA-Z0-9\s]/g, "")
      .replace(/\s+/g, " ")
      .trim()
      .toLowerCase();
  };

  const generateDatesFromPeriodo = (periodo: any): Date[] => {
    const dates: Date[] = [];
    const current = new Date(periodo.fecha_inicio);
    const end = new Date(periodo.fecha_fin);

    while (current <= end) {
      const dayOfWeek = current.getUTCDay();
      // Lunes (1) a Sábado (6)
      if (dayOfWeek >= 1 && dayOfWeek <= 6) {
        dates.push(new Date(current));
      }
      current.setUTCDate(current.getUTCDate() + 1);
    }

    return dates;
  };

  const formatDateForExcel = (date: Date): string => {
    const months = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
    return `${date.getUTCDate()}/${months[date.getUTCMonth()]}/${date.getUTCFullYear().toString().substr(2)}`;
  };

  onMount(async () => {
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
    } catch (error) {
      console.error("Error cargando datos iniciales:", error);
    } finally {
      isLoading = false;
    }
  });

  const generateExcel = async () => {
    // Load libraries dynamically
    await loadLibraries();
    
    if (!selectedDocente || !selectedMateria || !selectedGrado) {
      Swal.fire({
        icon: "warning",
        title: "Campos incompletos",
        text: "Por favor seleccione Docente, Materia y Grado.",
      });
      return;
    }

    const periodo = periodos.find(p => p.nombre === selectedPeriodo);
    if (!periodo) {
      Swal.fire({ icon: "error", title: "Error", text: "Periodo no encontrado" });
      return;
    }

    isGenerating = true;
    try {
      // 1. Obtener datos de inasistencias
      const response = await getInasistencias({
        spreadsheetId: SPREADSHEET_ID,
        worksheetTitle: WORKSHEET_TITLE,
      });

      if (!response?.records || !Array.isArray(response.records)) {
        throw new Error("No se pudieron obtener los registros de inasistencias");
      }

      const dataRows = response.records.slice(1);
      const targetDocente = normalize(selectedDocente);
      const targetMateria = normalize(selectedMateria);
      const targetGrado = selectedGrado.toString().trim();

      const filteredInasistencias = dataRows
        .map((row: any) => {
          const values = row.values || [];
          return {
            fecha: values[2] || "",
            docente: values[1] || "",
            materia: values[4] || "",
            grado: (values[6] || "").toString().trim(),
            nombre: values[7] || "",
            motivo: values[5] || "",
            horas: values[3] || "0",
          } as InasistenciaData;
        })
        .filter((item: InasistenciaData) => {
          const itemDocente = normalize(item.docente);
          const itemMateria = normalize(item.materia);
          const itemGrado = item.grado;

          // Filtro por docente, materia y grado usando normalize
          const matchDocente = itemDocente.includes(targetDocente) || targetDocente.includes(itemDocente);
          const matchMateria = itemMateria.includes(targetMateria) || targetMateria.includes(itemMateria);
          const matchGrado = itemGrado === targetGrado;

          // Filtro por fecha del periodo
          const d = new Date(item.fecha);
          const matchPeriodo = d >= periodo.fecha_inicio && d <= periodo.fecha_fin;

          return matchDocente && matchMateria && matchGrado && matchPeriodo;
        });

      // 2. Obtener lista de estudiantes del grado
      const estudiantesGrado = estudiantes
        .filter(e => e.grado.toString() === selectedGrado)
        .map(e => e.nombre)
        .sort();

      if (estudiantesGrado.length === 0) {
        throw new Error("No hay estudiantes registrados para el grado seleccionado");
      }

      // 3. Determinar fechas de clase en el periodo (Lunes a Sábado)
      const allDatesInPeriod = generateDatesFromPeriodo(periodo);
      
      // Filtrar para incluir solo fechas que tengan al menos una inasistencia registrada
      // O podríamos mostrar todas las fechas del periodo. asd.txt filtra fechas con registros.
      const fechasConRegistros = allDatesInPeriod.filter(date => {
        const dateStr = date.toISOString().split("T")[0];
        return filteredInasistencias.some((reg: InasistenciaData) => reg.fecha === dateStr);
      });

      if (fechasConRegistros.length === 0) {
        Swal.fire({
          icon: "info",
          title: "Sin datos",
          text: "No se encontraron inasistencias para los filtros seleccionados en este periodo.",
        });
        isGenerating = false;
        return;
      }

      // 4. Crear Excel
      const workbook = new ExcelJS.Workbook();
      const worksheet = workbook.addWorksheet("Reporte de Inasistencias");

      const totalCols = fechasConRegistros.length + 2; // Estudiante + Fechas + Total

      // Título (Fila 1)
      const titleRow = worksheet.addRow([
        `REPORTE DE INASISTENCIAS - ${selectedMateria} - GRADO ${selectedGrado}`
      ]);
      titleRow.height = 30;
      titleRow.font = { bold: true, size: 16, color: { argb: "FFFFFF" } };
      titleRow.fill = {
        type: "pattern",
        pattern: "solid",
        fgColor: { argb: "2E75B6" },
      };
      titleRow.alignment = { vertical: "middle", horizontal: "center" };
      worksheet.mergeCells(1, 1, 1, totalCols);

      // Info (Fila 2)
      const infoRow = worksheet.addRow([
        `Docente: ${selectedDocente} | Periodo: ${periodo.nombre} (${formatDateForExcel(periodo.fecha_inicio)} - ${formatDateForExcel(periodo.fecha_fin)})`
      ]);
      infoRow.height = 20;
      infoRow.font = { bold: true, size: 10, color: { argb: "FFFFFF" } };
      infoRow.fill = {
        type: "pattern",
        pattern: "solid",
        fgColor: { argb: "5B9BD5" },
      };
      infoRow.alignment = { vertical: "middle", horizontal: "left" };
      worksheet.mergeCells(2, 1, 2, totalCols);

      // Encabezados (Fila 3)
      const headerValues = [
        "Estudiantes",
        ...fechasConRegistros.map(d => formatDateForExcel(d)),
        "Total Horas"
      ];
      const headerRow = worksheet.addRow(headerValues);
      headerRow.height = 25;
      headerRow.font = { bold: true, size: 11, color: { argb: "FFFFFF" } };
      headerRow.alignment = { vertical: "middle", horizontal: "center" };

      // Estilo de encabezados
      worksheet.getColumn(1).width = 35;
      fechasConRegistros.forEach((_, i) => {
        worksheet.getColumn(i + 2).width = 12;
      });
      worksheet.getColumn(totalCols).width = 15;

      headerRow.eachCell((cell: any, colNumber: any) => {
        cell.fill = {
          type: "pattern",
          pattern: "solid",
          fgColor: { argb: colNumber === totalCols ? "FF6B6B" : "4472C4" },
        };
        cell.border = {
          top: { style: "thin" },
          left: { style: "thin" },
          bottom: { style: "thin" },
          right: { style: "thin" },
        };
      });

      // 5. Agregar datos de estudiantes
      estudiantesGrado.forEach((nombreEstudiante) => {
        const rowData = [nombreEstudiante];
        let totalHoras = 0;

        fechasConRegistros.forEach((date) => {
          const dateStr = date.toISOString().split("T")[0];
          const inasistencias = filteredInasistencias.filter(
            (i: InasistenciaData) => normalize(i.nombre) === normalize(nombreEstudiante) && i.fecha === dateStr
          );

          if (inasistencias.length > 0) {
            const horas = inasistencias.reduce((sum: number, i: InasistenciaData) => sum + (parseFloat(i.horas) || 0), 0);
            rowData.push(horas > 0 ? horas.toString() : "X");
            totalHoras += horas;
          } else {
            rowData.push("✓");
          }
        });

        rowData.push(totalHoras.toString());
        const row = worksheet.addRow(rowData);

        // Estilos de celda
        row.getCell(1).font = { bold: true };
        row.getCell(1).fill = {
          type: "pattern",
          pattern: "solid",
          fgColor: { argb: "F2F2F2" }
        };

        fechasConRegistros.forEach((_, i) => {
          const cell = row.getCell(i + 2);
          cell.alignment = { horizontal: "center" };
          const val = cell.value?.toString();
          if (val === "X" || (parseFloat(val || "0") > 0)) {
            cell.font = { color: { argb: "FF0000" }, bold: true };
            cell.fill = {
              type: "pattern",
              pattern: "solid",
              fgColor: { argb: "FFE6E6" }
            };
          } else {
            cell.font = { color: { argb: "00AA00" } };
          }
        });

        const totalCell = row.getCell(totalCols);
        totalCell.font = { bold: true, color: { argb: totalHoras > 0 ? "FF0000" : "00AA00" } };
        totalCell.alignment = { horizontal: "center" };
        totalCell.fill = {
          type: "pattern",
          pattern: "solid",
          fgColor: { argb: totalHoras > 0 ? "FFE6E6" : "E6FFE6" }
        };

        // Bordes para toda la fila
        row.eachCell((cell: any) => {
          cell.border = {
            top: { style: "thin" },
            left: { style: "thin" },
            bottom: { style: "thin" },
            right: { style: "thin" }
          };
        });
      });

      // 6. Descargar
      const buffer = await (workbook.xlsx as any).writeBuffer();
      const blob = new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
      const fileName = `Reporte_Inasistencias_${selectedGrado}_${selectedMateria.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.xlsx`;
      
      saveAs(blob, fileName);

      Swal.fire({
        icon: "success",
        title: "Reporte generado",
        text: `Se ha generado el reporte con ${estudiantesGrado.length} estudiantes.`,
        timer: 2000,
        showConfirmButton: false,
      });

    } catch (error: unknown) {
      console.error("Error generando Excel:", error);
      const errorMessage = error instanceof Error ? error.message : "Error desconocido";
      Swal.fire({
        icon: "error",
        title: "Error",
        text: errorMessage,
      });
    } finally {
      isGenerating = false;
    }
  };

  $: styles = {
    bg: "rgb(var(--bg-primary))",
    text: "rgb(var(--text-primary))",
    label: "rgb(var(--text-secondary))",
    border: "rgb(var(--border-primary))",
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
  on:keydown={(e: KeyboardEvent) => e.key === 'Escape' && onClose()}
>
  <div
    class="bg-white dark:bg-gray-900 rounded-2xl w-full max-w-2xl overflow-hidden shadow-2xl border"
    style="background-color: {styles.cardBg}; border-color: {styles.cardBorder}; color: {styles.text};"
  >
    <!-- Header -->
    <div class="flex items-center justify-between p-6 border-b" style="border-color: {styles.cardBorder};">
      <div class="flex items-center gap-3">
        <div class="p-2 rounded-lg bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-200">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <div>
          <h2 id="modal-title" class="text-xl font-bold">Generador de Reporte Excel</h2>
          <p class="text-sm opacity-75">Inasistencias por materia y grado</p>
        </div>
      </div>
      <button on:click={onClose} class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" aria-label="Cerrar modal">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Content -->
    <div class="p-6 space-y-6">
      {#if isLoading}
        <div class="flex justify-center py-12">
          <Loader message="Cargando datos..." />
        </div>
      {:else}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-2">
            <label for="rep-docente" class="block text-sm font-medium" style="color: {styles.label};">Docente</label>
            <select id="rep-docente" bind:value={selectedDocente} class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 outline-none" style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};">
              <option value="">Seleccione docente</option>
              {#each docentes as docente}
                <option value={docente}>{docente}</option>
              {/each}
            </select>
          </div>

          <div class="space-y-2">
            <label for="rep-materia" class="block text-sm font-medium" style="color: {styles.label};">Materia</label>
            <select id="rep-materia" bind:value={selectedMateria} class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 outline-none" style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};">
              <option value="">Seleccione materia</option>
              {#each materias as materia}
                <option value={materia.materia}>{materia.materia}</option>
              {/each}
            </select>
          </div>

          <div class="space-y-2">
            <label for="rep-grado" class="block text-sm font-medium" style="color: {styles.label};">Grado</label>
            <select id="rep-grado" bind:value={selectedGrado} class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 outline-none" style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};">
              <option value="">Seleccione grado</option>
              {#each [...new Set(estudiantes.map(e => e.grado.toString()))] as g}
                <option value={g}>{g.replace(/0(\d)$/, "°$1").replace(/(\d{1,2})0(\d)/, "$1°$2")}</option>
              {/each}
            </select>
          </div>

          <div class="space-y-2">
            <label for="rep-periodo" class="block text-sm font-medium" style="color: {styles.label};">Periodo</label>
            <select id="rep-periodo" bind:value={selectedPeriodo} class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 outline-none" style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};">
              {#each periodos as p}
                <option value={p.nombre}>Periodo {p.nombre}</option>
              {/each}
            </select>
          </div>
        </div>

        <div class="pt-4 flex gap-3">
          <button
            on:click={onClose}
            class="flex-1 px-6 py-3 border rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors font-medium"
            style="border-color: {styles.border};"
          >
            Cancelar
          </button>
          <button
            on:click={generateExcel}
            disabled={isGenerating || !selectedDocente || !selectedMateria || !selectedGrado}
            class="flex-[2] px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl transition-all font-bold disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 shadow-lg hover:shadow-green-500/20"
          >
            {#if isGenerating}
              <svg class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Generando...
            {:else}
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
              Descargar Excel
            {/if}
          </button>
        </div>
      {/if}
    </div>
  </div>
</div>
