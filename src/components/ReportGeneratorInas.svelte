<script lang="ts">
  import { onMount } from "svelte";
  import {
    getDocentes,
    getMaterias,
    getEstudiantes,
    getInasistencias,
  } from "../../api/service";
  import { normalize, getDocenteNumber, formatDateDisplay, loadExcelLibraries } from '../lib/utils';
  import Loader from "./Loader.svelte";
  import { theme } from "../lib/themeStore";
  import { Download, X, Loader2, FileSpreadsheet, Cloud, FolderOpen } from '@lucide/svelte';
  import {
    SPREADSHEET_ID,
    WORKSHEET_TITLE,
    periodos,
    GOOGLE_CLIENT_ID,
  } from "../constants";
  import { gdriveService, isUploading } from "../lib/gdriveService";
  import DriveFolderPicker from "./DriveFolderPicker.svelte";
  import eieLogo from "../assets/eie.png";
  import Swal from "sweetalert2";
  import ReportGeneratorInasView from "./ReportGeneratorInasView.svelte";

  let { onClose, initialDocente = "" }: { onClose: () => void; initialDocente?: string } = $props();

  interface InasistenciaData {
    fecha: string;
    docente: string;
    materia: string;
    grado: string;
    nombre: string;
    motivo: string;
    horas: string;
  }

let docentes: string[] = $state([]);
   let materias: { materia: string }[] = $state([]);
   let estudiantes: { nombre: string; grado: number | string }[] = $state([]);
   
   let selectedDocente = $state("");
   let selectedMateria = $state("");
   let selectedGrado = $state("");
   let selectedPeriodo = $state(periodos[0].nombre);
   
   // Keep selectedDocente in sync with initialDocente prop
   $effect(() => {
     selectedDocente = initialDocente || "";
   });

  // Drive upload state
  let showFolderPicker = $state(false);
  let excelBlobToUpload = $state<Blob | null>(null);
  let excelFileNameToUpload = $state("");

   // Verificar si el docente tiene "-"
   let docenteHasDash = $derived(selectedDocente.includes("-"));

   // Filtrar grupos según el número del docente
   let docenteNumber = $derived(getDocenteNumber(selectedDocente));

   let filteredGrados = $derived(docenteNumber
     ? [...new Set(estudiantes.map((e) => e.grado.toString()))].filter((g) =>
         g.startsWith(`${docenteNumber}-`),
       )
     : [...new Set(estudiantes.map((e) => e.grado.toString()))].filter((g) =>
         !g.includes('-')
       ));

let isLoading = $state(false);
let isGenerating = $state(false);
let showInlineView = $state(false);
let generatedReports = $state<Array<{blob: Blob; fileName: string; materia: string; grado: string}>>([]);
let isGeneratingAll = $state(false);


   const normalizeFecha = (fecha: string): string => {
     if (!fecha) return "";
     const parts = fecha.split("/");
     if (parts.length === 3) {
       const day = parts[0].padStart(2, "0");
       const month = parts[1].padStart(2, "0");
       const year = parts[2];
       return `${year}-${month}-${day}`;
     }
     return fecha;
   };

   // Helper function to generate Excel report for specific docente/materia/grado combination
   const generateReportForCombination = async (
     docente: string,
     materia: string,
     grado: string,
     periodo: any
   ): Promise<{blob: Blob; fileName: string} | null> => {
     try {
       // Load libraries dynamically
       const { ExcelJS } = await loadExcelLibraries();

       // 1. Obtener datos de inasistencias
       const response = await getInasistencias({
         spreadsheetId: SPREADSHEET_ID,
         worksheetTitle: WORKSHEET_TITLE,
       });

       if (!response?.records || !Array.isArray(response.records)) {
         throw new Error("No se pudieron obtener los registros de inasistencias");
       }

       const dataRows = response.records.slice(1);
       const targetDocente = normalize(docente);
       const targetMateria = normalize(materia);
       const targetGrado = grado.toString().trim();

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
           const matchGrado = itemGrado === targetGrado || itemGrado.startsWith(targetGrado) || targetGrado.startsWith(itemGrado);

           // Filtro por fecha del periodo - normalizar formato DD/M/YYYY a YYYY-MM-DD
           const fechaNorm = normalizeFecha(item.fecha);
           const itemDate = new Date(fechaNorm);
           const periodoInicio = new Date(periodo.fecha_inicio);
           const periodoFin = new Date(periodo.fecha_fin);
           periodoInicio.setHours(0, 0, 0, 0);
           periodoFin.setHours(23, 59, 59, 999);
           const matchPeriodo = itemDate >= periodoInicio && itemDate <= periodoFin;

           return matchDocente && matchMateria && matchGrado && matchPeriodo;
         });

       // 2. Obtener lista de estudiantes del grado
       const estudiantesGrado = estudiantes
         .filter(e => e.grado.toString() === grado)
         .map(e => e.nombre)
         .sort();

       if (estudiantesGrado.length === 0) {
         throw new Error(`No hay estudiantes registrados para el grado ${grado}`);
       }

       // 3. Determinar fechas de clase en el periodo (Lunes a Sábado)
       const allDatesInPeriod = generateDatesFromPeriodo(periodo);
       
       // Filtrar para incluir solo fechas que tengan al menos una inasistencia registrada
       const fechasConRegistros = allDatesInPeriod.filter(date => {
         const dateStr = date.toISOString().split("T")[0];
         return filteredInasistencias.some((reg: InasistenciaData) => {
           const regFecha = normalizeFecha(reg.fecha);
           return regFecha === dateStr;
         });
       });

       if (fechasConRegistros.length === 0) {
         // No throw error here - just return null so caller can handle it
         return null;
       }

       // 4. Crear Excel
       const workbook = new ExcelJS.Workbook();
       const worksheet = workbook.addWorksheet("Reporte de Inasistencias");

       const totalCols = fechasConRegistros.length + 2; // Estudiante + Fechas + Total

       // Título (Fila 1)
       const titleRow = worksheet.addRow([
         `REPORTE DE INASISTENCIAS - ${materia} - GRADO ${grado}`
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
         `Docente: ${docente} | Periodo: ${periodo.nombre} (${formatDateForExcel(periodo.fecha_inicio)} - ${formatDateForExcel(periodo.fecha_fin)})`
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
         worksheet.getColumn(i + 2).width = 22;
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
             (i: InasistenciaData) => normalize(i.nombre) === normalize(nombreEstudiante) && normalizeFecha(i.fecha) === dateStr
           );

           if (inasistencias.length > 0) {
             const inas = inasistencias[0];
             const horas = parseFloat(inas.horas) || 0;
             const motivo = inas.motivo || "";
             
             if (horas > 0) {
               rowData.push(horas.toString());
               totalHoras += horas;
             } else if (motivo) {
               rowData.push(motivo);
             } else {
               rowData.push("X");
             }
           } else {
             rowData.push("✓");
           }
         });

         rowData.push(totalHoras.toString());
         const row = worksheet.addRow(rowData);
         row.height = 30;

         // Estilos de celda
         row.getCell(1).font = { bold: true };
         row.getCell(1).fill = {
           type: "pattern",
           pattern: "solid",
           fgColor: { argb: "F2F2F2" }
         };

         fechasConRegistros.forEach((_, i) => {
           const cell = row.getCell(i + 2);
           cell.alignment = { horizontal: "center", vertical: "middle", wrapText: true };
           const val = cell.value?.toString() || "";
           const horas = parseFloat(val) || 0;
           
           if (val === "✓") {
             cell.font = { color: { argb: "00AA00" } };
           } else if (horas > 0 || isNaN(horas)) {
             cell.font = { color: { argb: "FF0000" }, bold: true };
             cell.fill = {
               type: "pattern",
               pattern: "solid",
               fgColor: { argb: "FFE6E6" }
             };
           } else {
             cell.font = { color: { argb: "FF0000" }, bold: true };
             cell.fill = {
               type: "pattern",
               pattern: "solid",
               fgColor: { argb: "FFE6E6" }
             };
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

       // 6. Generar archivo
       const buffer = await (workbook.xlsx as any).writeBuffer();
       const blob = new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
       const fileName = `Reporte_Inasistencias_${grado}_${materia.replace(/\s+/g, '_')}_${periodo.nombre}.xlsx`;

       return {blob, fileName};
     } catch (error) {
       console.error(`Error generating report for ${materia} - ${grado}:`, error);
       return null;
     }
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

  const formatDateForExcel = formatDateDisplay;

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
      const { saveAs } = await loadExcelLibraries();

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
       const result = await generateReportForCombination(
         selectedDocente,
         selectedMateria,
         selectedGrado,
         periodo
       );

       if (!result) {
         Swal.fire({
           icon: "info",
           title: "Sin datos",
           text: "No se encontraron inasistencias para los filtros seleccionados en este periodo.",
         });
         return;
       }

       // Preguntar si guardar localmente o en Drive
       const resultSwal = await Swal.fire({
         title: "¿Cómo desea guardar el reporte?",
         showDenyButton: true,
         showCancelButton: true,
         confirmButtonText: "Guardar localmente",
         denyButtonText: "Guardar en Drive",
         cancelButtonText: "Cancelar",
         confirmButtonColor: "#10b981",
         denyButtonColor: "#3b82f6",
         cancelButtonColor: "#6b7280",
       });

       if (resultSwal.isConfirmed) {
         // Guardar localmente
         saveAs(result.blob, result.fileName);
         await Swal.fire({
           icon: "success",
           title: "Reporte guardado",
           text: "El reporte se ha guardado en su dispositivo.",
           timer: 2000,
           showConfirmButton: false,
         });
       } else if (resultSwal.isDenied) {
         // Guardar en Drive
         excelBlobToUpload = result.blob;
         excelFileNameToUpload = result.fileName;
         showFolderPicker = true;
       }

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

   const generateAllReportsForDocente = async () => {
     if (!selectedDocente) {
       Swal.fire({
         icon: "warning",
         title: "Docente requerido",
         text: "Por favor seleccione un docente.",
       });
       return;
     }

     const periodo = periodos.find(p => p.nombre === selectedPeriodo);
     if (!periodo) {
       Swal.fire({ icon: "error", title: "Error", text: "Periodo no encontrado" });
       return;
     }

     isGeneratingAll = true;
     generatedReports = [];

     try {
       // Get all grades that have students for this docente
       const gradosDisponibles = filteredGrados;
       
       if (gradosDisponibles.length === 0) {
         throw new Error("No hay grados disponibles para este docente");
       }

       // Generate report for each grado
       for (const grado of gradosDisponibles) {
         const result = await generateReportForCombination(
           selectedDocente,
           selectedMateria || "", // Use first materia if none selected
           grado,
           periodo
         );

         if (result) {
           generatedReports = [...generatedReports, {
             ...result,
             materia: selectedMateria || "Todas",
             grado
           }];
         }
       }

       if (generatedReports.length === 0) {
         await Swal.fire({
           icon: "warning",
           title: "Sin datos",
           text: "No se encontraron inasistencias para el docente y período seleccionados.",
         });
         return;
       }

       // Show summary and proceed to Drive upload
       const reportList = generatedReports
         .map(r => `• ${r.grado}: ${r.fileName}`)
         .join('\n');

       const confirmResult = await Swal.fire({
         title: `Se generaron ${generatedReports.length} reportes`,
         html: `<pre style="text-align: left; margin: 10px 0; font-size: 12px;">${reportList}</pre>`,
         showDenyButton: true,
         showCancelButton: true,
         confirmButtonText: "Subir a Drive",
         denyButtonText: "Solo generar",
         cancelButtonText: "Cancelar",
         confirmButtonColor: "#3b82f6",
         denyButtonColor: "#10b981",
         cancelButtonColor: "#6b7280",
       });

       if (confirmResult.isConfirmed) {
         // Save all to Drive
         showFolderPicker = true;
       } else if (confirmResult.isDenied) {
         // Just generate locally (download all)
         for (const report of generatedReports) {
           const { saveAs } = await loadExcelLibraries();
           saveAs(report.blob, report.fileName);
         }
         await Swal.fire({
           icon: "success",
           title: "Reportes descargados",
           text: `Se descargaron ${generatedReports.length} reportes en su dispositivo.`,
           timer: 2000,
           showConfirmButton: false,
         });
         generatedReports = [];
       }
     } catch (error) {
       await Swal.fire({
         icon: "error",
         title: "Error",
         text: error instanceof Error ? error.message : "Error desconocido",
       });
     } finally {
       isGeneratingAll = false;
     }
   };

   const handleFolderSelected = async (folder: { id: string, name: string } | null) => {
     if (generatedReports.length === 0) {
       // Handle single file upload (legacy)
       if (!excelBlobToUpload) return;
       showFolderPicker = false;
       await gdriveService.uploadFile(
         excelBlobToUpload,
         excelFileNameToUpload,
         "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
         GOOGLE_CLIENT_ID,
         folder?.id
       );
       excelBlobToUpload = null;
       excelFileNameToUpload = "";
       await Swal.fire({
         icon: "success",
         title: "Guardado en Drive",
         text: "El reporte se ha guardado exitosamente en Google Drive.",
         timer: 2000,
         showConfirmButton: false,
       });
       return;
     }

     // Handle multiple file uploads (new feature)
     showFolderPicker = false;
     let successCount = 0;

     for (const report of generatedReports) {
       const result = await gdriveService.uploadFile(
         report.blob,
         report.fileName,
         "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
         GOOGLE_CLIENT_ID,
         folder?.id
       );
       if (result) successCount++;
     }

     await Swal.fire({
       icon: "success",
       title: "Reportes guardados en Drive",
       text: `Se guardaron exitosamente ${successCount} de ${generatedReports.length} reportes en Google Drive.`,
       timer: 3000,
       showConfirmButton: false,
     });

     generatedReports = [];
   };

   let styles = $derived({
     bg: "rgb(var(--bg-primary))",
     text: "rgb(var(--text-primary))",
     label: "rgb(var(--text-secondary))",
     border: "rgb(var(--border-primary))",
     cardBg: "rgb(var(--card-bg))",
     cardBorder: "rgb(var(--card-border))",
     inputBg: "rgb(var(--bg-secondary))",
   });
</script>

<div
  class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
  role="dialog"
  aria-modal="true"
  aria-labelledby="modal-title"
  tabindex="-1"
  onclick={(e: MouseEvent) => { if (e.target === e.currentTarget) onClose() }}
  onkeydown={(e: KeyboardEvent) => e.key === 'Escape' && onClose()}
>
  <div
    class="bg-white dark:bg-gray-900 rounded-2xl w-full max-w-2xl overflow-hidden shadow-2xl border"
    style="background-color: {styles.cardBg}; border-color: {styles.cardBorder}; color: {styles.text};"
  >
    <!-- Header -->
    <div class="flex items-center justify-between p-6 border-b" style="border-color: {styles.cardBorder};">
      <div class="flex items-center gap-3">
        <div class="p-2 rounded-lg bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-200">
          <FileSpreadsheet class="w-6 h-6" />
        </div>
        <div>
          <h2 id="modal-title" class="text-xl font-bold">Generador de Reporte Excel</h2>
          <p class="text-sm opacity-75">Inasistencias por materia y grado</p>
        </div>
      </div>
      <button onclick={onClose} class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" aria-label="Cerrar modal">
        <X class="w-5 h-5" />
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
              {#each filteredGrados as g}
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
             onclick={onClose}
             class="flex-1 px-6 py-3 border rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors font-medium"
             style="border-color: {styles.border};"
           >
             Cancelar
           </button>
           <div class="flex gap-2">
             <button
               onclick={generateExcel}
               disabled={isGenerating || !selectedDocente || !selectedMateria || !selectedGrado}
               class="flex-1 px-6 py-3 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white rounded-xl transition-all font-bold disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 shadow-lg hover:shadow-green-500/20"
             >
               {#if isGenerating}
                 <Loader2 class="animate-spin h-5 w-5 text-white" />
                 Generando...
               {:else}
                 <Download class="w-5 h-5" />
                 Descargar Excel
               {/if}
</button>
             <button
                 onclick={generateExcel}
                 disabled={isGenerating || !selectedDocente || !selectedMateria || !selectedGrado || $isUploading}
                 class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white rounded-xl transition-all font-bold disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
               >
                {#if $isUploading}
                  <Loader2 class="animate-spin h-4 w-4" />
                  Guardando...
                {:else}
                  <Cloud class="w-4 h-4" />
                  Guardar en Drive
                {/if}
              </button>
            </div>
          </div>

          <!-- Generate All Button -->
          <button
            onclick={generateAllReportsForDocente}
            disabled={isGeneratingAll || !selectedDocente || $isUploading}
            class="w-full px-6 py-3 bg-purple-600 hover:bg-purple-700 disabled:bg-gray-400 text-white rounded-xl transition-all font-bold disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            {#if isGeneratingAll}
              <Loader2 class="animate-spin h-5 w-5 text-white" />
              Generando todos...
            {:else}
              <FolderOpen class="w-5 h-5" />
              Generar y enviar todos a Drive
            {/if}
          </button>
      {/if}
    </div>
  </div>
</div>

{#if showInlineView}
   <ReportGeneratorInasView 
     onClose={() => showInlineView = false}
     initialDocente={selectedDocente}
   />
 {/if}

{#if showFolderPicker}
    <DriveFolderPicker 
      onSelect={handleFolderSelected} 
      onClose={() => { showFolderPicker = false; excelBlobToUpload = null; generatedReports = []; }}
    />
  {/if}
