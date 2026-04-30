<script lang="ts">
  import { onMount } from "svelte";
  import Swal from "sweetalert2";
  import {
    getDocentes,
    getMaterias,
    getEstudiantes,
    getDiario,
  } from "../../api/service";
  import { getDocenteNumber, loadPdfLibraries } from '../lib/utils';
  import Loader from "./Loader.svelte";
  import { theme } from "../lib/themeStore";
  import { FileText, X, Filter, Loader2, Download, Cloud, Sparkles } from '@lucide/svelte';
  import {
    SPREADSHEET_ID_DIARIO,
    WORKSHEET_TITLE_DIARIO,
    GOOGLE_CLIENT_ID
  } from "../constants";
  import { gdriveService, isUploading } from "../lib/gdriveService";
  import DriveFolderPicker from "./DriveFolderPicker.svelte";
  import UploadProgressModal from "./UploadProgressModal.svelte";

  let { onClose, initialDocente = "" }: { onClose: () => void; initialDocente?: string } = $props();

  interface DiarioData {
    "Marca Temporal": string;
    Fecha: string;
    Horas: string;
    Docente: string;
    Asignatura: string;
    Grado: string;
    "Diario de Campo": string;
  }

  let showFolderPicker = $state(false);
  let pdfBlobToUpload = $state<Blob | null>(null);
  let pdfFileNameToUpload = $state("");

  let docentes: string[] = $state([]);
  let materias: { materia: string }[] = $state([]);
  let estudiantes: { nombre: string; grado: number | string }[] = $state([]);
  let diarioData: DiarioData[] = $state([]);
  let filteredData: DiarioData[] = $state([]);

  let isLoading = $state(false);
  let isLoadingData = $state(false);

  let docenteMaterias: Record<string, string[]> = $state(JSON.parse(
    localStorage.getItem("docenteMateriasDiario") || "{}",
  ));

  let selectedDocente = $state("");
  let selectedMateria = $state("");
  let selectedGrado = $state("");

  // Estado para carga automática de datos
  let autoLoaded = $state(false);
  
  // Estado para generar todos los reportes
  let isGeneratingAll = $state(false);
  let generatedPDFs = $state<Array<{blob: Blob; fileName: string; grado: string}>>([]);
  
  // Estado para progreso de upload
  type UploadPhase = 'idle' | 'generating' | 'uploading' | 'done';
  let uploadPhase = $state<UploadPhase>('idle');
  let uploadCurrent = $state(0);
  let uploadTotal = $state(0);
  let uploadCurrentFile = $state('');
  let uploadSuccessCount = $state(0);
  let uploadFailedCount = $state(0);
  let showUploadProgress = $state(false);

  const saveMateriaForDocente = (docente: string, materia: string): void => {
    if (!docente || !materia) return;
    if (!docenteMaterias[docente]) {
      docenteMaterias[docente] = [];
    }
    if (!docenteMaterias[docente].includes(materia)) {
      docenteMaterias[docente] = [...docenteMaterias[docente], materia];
      localStorage.setItem("docenteMateriasDiario", JSON.stringify(docenteMaterias));
    }
  };

  $effect(() => {
    if (selectedDocente && selectedMateria) {
      saveMateriaForDocente(selectedDocente, selectedMateria);
    }
  });

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

  // Cargar datos automáticamente cuando cambia el docente
  $effect(() => {
    if (selectedDocente && !autoLoaded) {
      loadDiarioData();
      autoLoaded = true;
    }
  });

  // Recargar datos cuando cambian los filtros después de la carga inicial
  $effect(() => {
    if (autoLoaded && (selectedMateria || selectedGrado)) {
      const timeout = setTimeout(() => {
        loadDiarioData();
      }, 500);
      return () => clearTimeout(timeout);
    }
  });

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

  const generatePDF = async (saveToDrive = false) => {
    const { jsPDF, autoTable } = await loadPdfLibraries();

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

    const fileNameParts = [selectedDocente];
    if (selectedMateria) fileNameParts.push(selectedMateria);
    if (selectedGrado) fileNameParts.push(selectedGrado);
    const fileName = `reporte_diario_${fileNameParts.join("_")}.pdf`;
    
    if (saveToDrive) {
      const blob = pdf.output("blob");
      if (!blob || blob.size === 0) {
        await Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudo generar el archivo PDF.',
          confirmButtonColor: '#ef4444',
        });
        return;
      }
      pdfBlobToUpload = blob;
      pdfFileNameToUpload = fileName;
      showFolderPicker = true;
    } else {
      pdf.save(fileName);
    }
  };

  const handleFolderSelected = async (folder: { id: string, name: string } | null) => {
    showFolderPicker = false;
    showUploadProgress = true;
    uploadSuccessCount = 0;
    uploadFailedCount = 0;

    // Handle single file upload (legacy)
    if (generatedPDFs.length === 0) {
      if (!pdfBlobToUpload) {
        showUploadProgress = false;
        return;
      }

      uploadPhase = 'uploading';
      uploadCurrent = 1;
      uploadTotal = 1;
      uploadCurrentFile = pdfFileNameToUpload;

      const result = await gdriveService.uploadFile(
        pdfBlobToUpload,
        pdfFileNameToUpload,
        "application/pdf",
        GOOGLE_CLIENT_ID,
        folder?.id
      );

      if (result.success) {
        uploadSuccessCount = 1;
      } else {
        uploadFailedCount = 1;
      }

      uploadPhase = 'done';
      pdfBlobToUpload = null;
      pdfFileNameToUpload = "";

      await Swal.fire({
        icon: uploadFailedCount > 0 ? "warning" : "success",
        title: uploadFailedCount > 0 ? "Archivo con problemas" : "Guardado en Drive",
        text: uploadFailedCount > 0 
          ? `El archivo no se pudo guardar: ${result.error}` 
          : "El reporte se ha guardado exitosamente en Google Drive.",
        confirmButtonColor: "#3b82f6",
      });
      showUploadProgress = false;
      return;
    }

    // Handle multiple file uploads
    uploadPhase = 'uploading';
    uploadTotal = generatedPDFs.length;

    for (let i = 0; i < generatedPDFs.length; i++) {
      const report = generatedPDFs[i];
      uploadCurrent = i + 1;
      uploadCurrentFile = report.fileName;

      const result = await gdriveService.uploadFile(
        report.blob,
        report.fileName,
        "application/pdf",
        GOOGLE_CLIENT_ID,
        folder?.id
      );

      if (result.success) {
        uploadSuccessCount++;
      } else {
        uploadFailedCount++;
      }
    }

    uploadPhase = 'done';

    await Swal.fire({
      icon: uploadFailedCount > 0 ? "warning" : "success",
      title: uploadFailedCount > 0 ? "Algunos archivos no se guardaron" : "Reportes guardados",
      text: uploadFailedCount > 0
        ? `Se guardaron ${uploadSuccessCount} de ${uploadTotal} reportes.`
        : `Se guardaron exitosamente ${uploadSuccessCount} reportes en Google Drive.`,
      confirmButtonColor: "#3b82f6",
    });

    generatedPDFs = [];
    showUploadProgress = false;
  };

  // Función para generar reportes de todos los grados del docente
  const generateAllReportsForDocente = async () => {
    if (!selectedDocente) {
      await Swal.fire({
        icon: "warning",
        title: "Docente requerido",
        text: "Por favor seleccione un docente.",
      });
      return;
    }

    isGeneratingAll = true;
    generatedPDFs = [];
    showUploadProgress = true;
    uploadPhase = 'generating';
    uploadCurrent = 0;
    uploadTotal = 0;
    uploadSuccessCount = 0;
    uploadFailedCount = 0;
    uploadCurrentFile = 'Preparando generación de reportes...';

    try {
      const gradosDisponibles = filteredGrados;
      
      if (filteredGrados.length === 0) {
        showUploadProgress = false;
        throw new Error("No hay grados disponibles para este docente");
      }

      // Cargar todos los datos del docente sin filtro de grado
      const payload: Record<string, string> = {
        spreadsheetId: SPREADSHEET_ID_DIARIO,
        worksheetTitle: WORKSHEET_TITLE_DIARIO,
      };
      if (selectedDocente) payload.docente = selectedDocente;
      if (selectedMateria) payload.materia = selectedMateria;

      const response = await getDiario(payload);
      
      let rawData = response;
      if (response && typeof response === "object") {
        if (response.data) rawData = response.data;
        else if (response.records) rawData = response.records;
        else if (Array.isArray(response)) rawData = response;
      }
      
      const allData: DiarioData[] = Array.isArray(rawData) ? rawData : [];

      // Transformar si viene con formato .values
      if (allData.length > 0 && (allData[0] as any).values) {
        const headers = ["Marca Temporal", "Fecha", "Horas", "Docente", "Asignatura", "Grado", "Diario de Campo"];
        const transformedData = allData.map((row: any) => {
          const obj: DiarioData = {
            "Marca Temporal": "", Fecha: "", Horas: "", Docente: "", Asignatura: "", Grado: "", "Diario de Campo": ""
          };
          headers.forEach((header, index) => {
            (obj as any)[header] = row.values?.[index] || "";
          });
          return obj;
        });
        allData.length = 0;
        allData.push(...transformedData);
      }

      // Obtener las materias únicas que el docente ha registrado
      const materiasDelDocente = [...new Set(
        allData
          .filter(item => item["Docente"] === selectedDocente)
          .map(item => item["Asignatura"])
      )].filter(Boolean);

      const materiasAProcesar = materiasDelDocente.length > 0 
        ? materiasDelDocente 
        : [...new Set(allData.map(item => item["Asignatura"]))].filter(Boolean);

      // Crear lista de combinaciones (materia + grado) que tienen datos
      const combinacionesConDatos: Array<{materia: string, grado: string}> = [];
      
      for (const materia of materiasAProcesar) {
        for (const grado of gradosDisponibles) {
          const tieneDatos = allData.some(item => 
            item["Docente"] === selectedDocente && 
            item["Asignatura"] === materia && 
            item["Grado"] === grado
          );
          if (tieneDatos) {
            combinacionesConDatos.push({ materia, grado });
          }
        }
      }

      uploadTotal = combinacionesConDatos.length;

      // Generar PDF para cada combinación materia + grado
      for (let i = 0; i < combinacionesConDatos.length; i++) {
        const { materia, grado } = combinacionesConDatos[i];
        uploadCurrent = i + 1;
        uploadCurrentFile = `Generando reporte ${i + 1} de ${uploadTotal}: ${materia} - Grado ${grado}`;

        const dataForGrado = allData.filter((item) => 
          item["Docente"] === selectedDocente && 
          item["Asignatura"] === materia && 
          item["Grado"] === grado
        );

        if (dataForGrado.length === 0) {
          continue;
        }

        const { jsPDF, autoTable } = await loadPdfLibraries();
        
        const pdf = new jsPDF({ format: 'letter' });
        const reportTitle = "Diario de Campo";
        const reportFilters = [
          `Docente: ${selectedDocente}`,
          `Asignatura: ${materia}`,
          `Grado: ${grado}`
        ];

        let yPosition = 20;

        pdf.setTextColor(99, 102, 241);
        pdf.setFontSize(28);
        pdf.setFont("helvetica", "bold");
        pdf.text("EIE", pdf.internal.pageSize.getWidth() / 2, yPosition, { align: "center" });
        yPosition += 15;

        pdf.setTextColor(0, 0, 0);
        pdf.setFontSize(18);
        pdf.setFont("helvetica", "bold");
        pdf.text(reportTitle, pdf.internal.pageSize.getWidth() / 2, yPosition, { align: "center" });
        yPosition += 12;

        pdf.setFontSize(12);
        pdf.setFont("helvetica", "normal");
        pdf.text("Institución Educativa Instituto Guática", pdf.internal.pageSize.getWidth() / 2, yPosition, { align: "center" });
        yPosition += 10;

        pdf.setTextColor(60, 60, 60);
        pdf.setFontSize(10);
        pdf.text(`Fecha de generación: ${new Date().toLocaleDateString()}`, pdf.internal.pageSize.getWidth() / 2, yPosition, { align: "center" });
        yPosition += 15;

        pdf.setDrawColor(200, 200, 200);
        pdf.setLineWidth(1);
        pdf.line(20, yPosition, pdf.internal.pageSize.getWidth() - 20, yPosition);
        yPosition += 15;

        if (reportFilters.length > 0) {
          pdf.setTextColor(80, 80, 80);
          pdf.setFontSize(9);
          reportFilters.forEach((filter) => {
            pdf.text(filter, 20, yPosition, { align: "left" });
            yPosition += 6;
          });
          yPosition += 10;
        }

        const tableHeaders = [["Fecha", "Horas", "Asignatura", "Grado", "Diario de Campo"]];
        const tableData = dataForGrado.map((item) => [
          item["Fecha"] || "",
          item["Horas"] || "",
          item["Asignatura"] || "",
          item["Grado"] || "",
          item["Diario de Campo"] || "",
        ]);

        const totalHorasGrado = dataForGrado.reduce((sum, item) => sum + (parseFloat(item["Horas"] || "0") || 0), 0);
        tableData.push(["TOTAL", totalHorasGrado.toFixed(1), "", "", `${dataForGrado.length} registros`]);

        type ColumnStyleMap = Record<string, Partial<import('jspdf-autotable').Styles>>;
        const columnStyles: ColumnStyleMap = {
          0: { cellWidth: 25, minCellHeight: 8, halign: 'center' },
          1: { cellWidth: 15, minCellHeight: 8, halign: 'center' },
          2: { cellWidth: 30, minCellHeight: 8 },
          3: { cellWidth: 15, minCellHeight: 8, halign: 'center' },
          4: { cellWidth: 'auto', minCellHeight: 12 },
        };

        autoTable(pdf, {
          head: tableHeaders,
          body: tableData,
          startY: yPosition,
          styles: { fontSize: 7, cellPadding: 1.5, cellWidth: "wrap", valign: "top", overflow: "linebreak" },
          headStyles: { fillColor: [99, 102, 241], textColor: 255, fontSize: 8, fontStyle: "bold", halign: 'center' },
          bodyStyles: { fillColor: [255, 255, 255], textColor: [0, 0, 0] },
          alternateRowStyles: { fillColor: [248, 250, 252] },
          columnStyles,
          margin: { top: 12, right: 12, bottom: 12, left: 12 },
          tableWidth: "auto",
          didParseCell: (data: any) => {
            if (data.row.index === tableData.length - 1) {
              data.cell.styles.fillColor = [230, 230, 230];
              data.cell.styles.fontStyle = "bold";
              data.cell.styles.textColor = [0, 0, 0];
              data.cell.styles.halign = "center";
            }
          },
        });

        const blob = pdf.output("blob");
        const fileName = `reporte_diario_${materia}_${grado}.pdf`;

        generatedPDFs = [...generatedPDFs, { blob, fileName, grado }];
      }

      if (generatedPDFs.length === 0) {
        showUploadProgress = false;
        await Swal.fire({
          icon: "warning",
          title: "Sin datos",
          text: "No se encontraron registros para el docente y período seleccionados.",
        });
        return;
      }

      showUploadProgress = false;
      showFolderPicker = true;
    } catch (error) {
      showUploadProgress = false;
      await Swal.fire({
        icon: "error",
        title: "Error",
        text: error instanceof Error ? error.message : "Error desconocido",
      });
    } finally {
      isGeneratingAll = false;
    }
  };

  let totalHoras = $derived(filteredData.reduce((sum, item) => {
    const horas = parseFloat(item["Horas"] || "0");
    return sum + (isNaN(horas) ? 0 : horas);
  }, 0));

  let sortedMaterias = $derived(selectedDocente
    ? [...materias].sort((a, b) => {
        const aSaved = docenteMaterias[selectedDocente]?.includes(a.materia);
        const bSaved = docenteMaterias[selectedDocente]?.includes(b.materia);
        if (aSaved && !bSaved) return -1;
        if (!aSaved && bSaved) return 1;
        return a.materia.localeCompare(b.materia);
      })
    : materias);

  let styles = $derived({
    bg: "rgb(var(--bg-primary))",
    text: "rgb(var(--text-primary))",
    label: "rgb(var(--text-secondary))",
    border: "rgb(var(--border-primary))",
    placeholder: "rgb(var(--text-muted))",
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
  onclick={(e) => e.target === e.currentTarget && onClose()}
  onkeydown={(e) => e.key === "Escape" && onClose()}
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
          <FileText class="w-6 h-6" />
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
        onclick={onClose}
        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
        aria-label="Cerrar ventana"
      >
        <X class="w-5 h-5" />
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
            onclick={handleFilter}
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
            onclick={handleClearFilters}
            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-lg transition-colors flex items-center gap-2"
            style="color: {styles.text};"
          >
            <X class="w-4 h-4" />
            Limpiar
          </button>
          
          <div class="ml-auto flex gap-2">
            <button
              onclick={() => generatePDF(false)}
              disabled={filteredData.length === 0}
              class="px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white rounded-lg transition-colors flex items-center gap-2"
            >
              <Download class="w-4 h-4" />
              Descargar PDF
            </button>
            <button
              onclick={() => generatePDF(true)}
              disabled={!selectedDocente || $isUploading}
              class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white rounded-lg transition-colors flex items-center gap-2"
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
          <!-- Botón Generar todos y enviar -->
          <div class="mt-3">
            <button
              onclick={generateAllReportsForDocente}
              disabled={isGeneratingAll || !selectedDocente || $isUploading}
              class="w-full px-4 py-3 bg-gradient-to-r from-purple-600 via-pink-500 to-indigo-600 hover:from-purple-700 hover:via-pink-600 hover:to-indigo-700 disabled:bg-gray-400 text-white rounded-lg transition-all flex items-center justify-between font-semibold shadow-lg shadow-purple-500/20"
            >
              <div class="flex items-center gap-2">
                {#if isGeneratingAll}
                  <Loader2 class="animate-spin h-4 w-4" />
                {:else}
                  <Cloud class="w-4 h-4" />
                {/if}
                <span class="text-sm">{isGeneratingAll ? 'Generando todos...' : 'Generar todos y enviar'}</span>
              </div>
              {#if !isGeneratingAll && selectedDocente}
                <div class="px-2 py-1 rounded-full bg-white/20 backdrop-blur text-xs font-medium flex items-center gap-1">
                  {filteredGrados.length} grado{filteredGrados.length !== 1 ? 's' : ''}
                </div>
              {/if}
            </button>
          </div>
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
          <FileText class="w-16 h-16 mx-auto mb-4 opacity-50" />
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
              {#each [...filteredData].sort((a, b) => (b["Fecha"] || "").localeCompare(a["Fecha"] || "")) as item, index}
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

{#if showFolderPicker}
  <DriveFolderPicker 
    onSelect={handleFolderSelected} 
    onClose={() => { showFolderPicker = false; pdfBlobToUpload = null; generatedPDFs = []; }}
  />
{/if}

{#if showUploadProgress}
  <UploadProgressModal 
    phase={uploadPhase}
    current={uploadCurrent}
    total={uploadTotal}
    currentFile={uploadCurrentFile}
    successCount={uploadSuccessCount}
    failedCount={uploadFailedCount}
    fileType="pdf"
  />
{/if}
