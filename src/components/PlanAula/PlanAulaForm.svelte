<script lang="ts">
  import { onMount } from "svelte";
  import { fade } from "svelte/transition";
  import Swal from "sweetalert2";
  import { docenteName, findMatchingDocente } from "../../lib/authStore";
  import { getDocentes, getMaterias, saveActaArea, getAllActaArea } from "../../../api/service";
  import { gdriveService, isUploading } from "../../lib/gdriveService";
  import { GOOGLE_CLIENT_ID, SPREADSHEET_ID_ACTA, WORKSHEET_TITLE_ACTA } from "../../constants";
  import eieLogo from "../../assets/eie.png";
  import ModuleHeader from "../ModuleHeader.svelte";
  import TeacherSelector from "./TeacherSelector.svelte";
  import AreaSelector from "./AreaSelector.svelte";
  import PeriodSelector from "./PeriodSelector.svelte";
  import GradeSelector from "./GradeSelector.svelte";
  import IntensitySelector from "./IntensitySelector.svelte";
  import RecordFilter from "./RecordFilter.svelte";
  import DriveFolderPicker from "../DriveFolderPicker.svelte";
  import UploadProgressModal from "../UploadProgressModal.svelte";

  let { onBack }: { onBack: () => void } = $props();

  let docentes = $state<string[]>([]);
  let materias = $state<string[]>([]);
  let isLoadingDocentes = $state(false);
  let isLoadingMaterias = $state(false);

  let formData = $state({
    area: "",
    docente: "",
    grado: "",
    intensidad: "",
    periodo: "I",
    contenidos: "",
    indicadores: "",
    dba: "",
    criterios: "",
    actividades: "",
  });

  let saving = $state(false);
  let savedRows = $state<any[]>([]);
  let loadingHistory = $state(false);

// PDF generation and Drive upload
  let pdfBlob = $state<Blob | null>(null);
  let pdfFileName = $state<string>('PlanAula.pdf');
  let showFolderPickerPdf = $state(false);
  let uploadPhasePdf = $state<'idle' | 'uploading' | 'done'>('idle');
  let uploadCurrentPdf = $state(0);
  let uploadTotalPdf = $state(0);
  let uploadCurrentFilePdf = $state('');
  let uploadSuccessCountPdf = $state(0);
  let uploadFailedCountPdf = $state(0);
  let showUploadProgressPdf = $state(false);

  // Upload state for RecordFilter Drive saves
  let showFolderPickerRecords = $state(false);
  let recordsBlobToUpload = $state<Blob | null>(null);
  let recordsFileNameToUpload = $state('');
  let showUploadProgressRecords = $state(false);
  let uploadPhaseRecords = $state<'idle' | 'uploading' | 'done'>('idle');
  let uploadCurrentRecords = $state(0);
  let uploadTotalRecords = $state(0);
  let uploadCurrentFileRecords = $state('');
  let uploadSuccessCountRecords = $state(0);
  let uploadFailedCountRecords = $state(0);


  onMount(async () => {
    await loadInitialData();
    await fetchHistory();
  });

  async function loadInitialData() {
    isLoadingDocentes = true;
    isLoadingMaterias = true;

    try {
      const [docentesData, materiasData] = await Promise.all([
        getDocentes(),
        getMaterias(),
      ]);
      docentes = docentesData;
      materias = materiasData;

      if (!formData.docente && $docenteName) {
        const match = findMatchingDocente(docentes, $docenteName);
        if (match) formData.docente = match;
      }
    } catch (error) {
      console.error("Error cargando datos iniciales:", error);
    } finally {
      isLoadingDocentes = false;
      isLoadingMaterias = false;
    }
  }

  async function fetchHistory() {
    loadingHistory = true;
    try {
      const result = await getAllActaArea();
      if (result && result.length > 0) {
        const headerRow = result[0];
        const hasTimestamp = headerRow[0] && (
          headerRow[0].toLowerCase().includes('time') ||
          headerRow[0].toLowerCase().includes('marca') ||
          headerRow[0].toLowerCase().includes('fecha') && headerRow[0].toLowerCase().includes('hora')
        );
        const dataRows = result.slice(hasTimestamp ? 1 : 0);
        savedRows = hasTimestamp
          ? dataRows.map((row: any[]) => row.slice(1))
          : dataRows;
      } else {
        savedRows = [];
      }
    } catch (error) {
      console.error("Error al cargar historia:", error);
      savedRows = [];
    } finally {
      loadingHistory = false;
    }
  }

  function selectRecord(record: any[]) {
    formData = {
      area: record[0] || "",
      docente: record[1] || "",
      grado: record[2] || "",
      intensidad: record[3] || "",
      periodo: record[4] || "I",
      contenidos: record[5] || "",
      indicadores: record[6] || "",
      dba: record[7] || "",
      criterios: record[8] || "",
      actividades: record[9] || "",
    };
    Swal.fire({
      toast: true,
      position: "top-end",
      icon: "info",
      title: "Registro cargado",
      showConfirmButton: false,
      timer: 2000,
    });
  }

  function resetForm() {
    formData = {
      area: "",
      docente: "",
      grado: "",
      intensidad: "",
      periodo: "I",
      contenidos: "",
      indicadores: "",
      dba: "",
      criterios: "",
      actividades: "",
    };
    Swal.fire({
      toast: true,
      position: "top-end",
      icon: "success",
      title: "Formulario limpiado",
      showConfirmButton: false,
      timer: 2000,
    });
  }

  async function handleSave() {
    const requiredFields = [
      { key: "area", label: "ÁREA" },
      { key: "docente", label: "DOCENTE" },
      { key: "grado", label: "GRADO" },
      { key: "intensidad", label: "INTENSIDAD HORARIA" },
      { key: "periodo", label: "PERIODO" },
      { key: "contenidos", label: "CONTENIDOS" },
      { key: "indicadores", label: "INDICADORES" },
      { key: "dba", label: "DBA" },
      { key: "criterios", label: "CRITERIOS" },
      { key: "actividades", label: "ACTIVIDADES" },
    ];

    const missingFields = requiredFields
      .filter((field) => !formData[field.key as keyof typeof formData])
      .map((field) => field.label);

    if (missingFields.length > 0) {
      Swal.fire({
        icon: "warning",
        title: "Información Incompleta",
        html: `Por favor complete los siguientes campos:<br><br><b>${missingFields.join(", ")}</b>`,
        confirmButtonColor: "#3085d6",
      });
      return;
    }

    saving = true;
    try {
      const currentTimestamp = new Date().toLocaleString();

      const payload: string[][] = [];

      payload.push([
        currentTimestamp,
        formData.area || '',
        formData.docente || '',
        formData.grado || '',
        formData.intensidad || '',
        formData.periodo || '',
        formData.contenidos || '',
        formData.indicadores || '',
        formData.dba || '',
        formData.criterios || '',
        formData.actividades || '',
      ]);

      const result = await saveActaArea({
        spreadsheetId: SPREADSHEET_ID_ACTA,
        worksheetTitle: WORKSHEET_TITLE_ACTA,
        datos: payload,
      });

      if (result.success) {
        Swal.fire("Éxito", result.message || "Plan de Aula guardado correctamente.", "success");
        await fetchHistory();
      } else {
        Swal.fire("Error", result.message || "Error al guardar el plan de aula.", "error");
      }
    } catch (error) {
      console.error("Error al guardar:", error);
      Swal.fire("Error", "Error al guardar el plan de aula.", "error");
    } finally {
      saving = false;
    }
  }

  async function generatePdfBlob() {
    try {
      const { default: jsPDF } = await import('jspdf');
      const { default: autoTable } = await import('jspdf-autotable');

      const doc = new jsPDF();
      const pageWidth = doc.internal.pageSize.getWidth();
      const pageHeight = doc.internal.pageSize.getHeight();
      const margin = 15;
      let yPos = 20;

      // Header
      doc.setFontSize(16);
      doc.setTextColor(0, 0, 0);
      doc.text('PLAN DE AULA', margin, yPos);
      yPos += 10;

      doc.setFontSize(10);
      doc.text(`Área: ${formData.area}`, margin, yPos);
      yPos += 7;
      doc.text(`Docente: ${formData.docente}`, margin, yPos);
      yPos += 7;
      doc.text(`Grado: ${formData.grado}`, margin, yPos);
      yPos += 7;
      doc.text(`Intensidad Semanal: ${formData.intensidad}`, margin, yPos);
      yPos += 7;
      doc.text(`Período: ${formData.periodo}`, margin, yPos);
      yPos += 10;

      // Sections
      const sections = [
        { label: 'CONTENIDOS', value: formData.contenidos },
        { label: 'INDICADORES', value: formData.indicadores },
        { label: 'DBA', value: formData.dba },
        { label: 'CRITERIOS', value: formData.criterios },
        { label: 'ACTIVIDADES Y RECURSOS', value: formData.actividades },
      ];

      for (const { label, value } of sections) {
        if (value.trim() !== '') {
          doc.setFontSize(12);
          doc.setTextColor(0, 0, 150);
          doc.text(label, margin, yPos);
          yPos += 6;
          doc.setFontSize(10);
          doc.setTextColor(0, 0, 0);
          const lines = doc.splitTextToSize(value, pageWidth - 2 * margin);
          for (const line of lines) {
            doc.text(line, margin, yPos);
            yPos += 6;
            if (yPos > pageHeight - 20) {
              doc.addPage();
              yPos = 20;
            }
          }
          yPos += 4;
        }
      }

      const blob = doc.output('blob');
      const fileName = `PlanAula_${formData.docente || 'SinDocente'}_${formData.grado || 'SinGrado'}_${new Date().toISOString().slice(0,10)}.pdf`.replace(/[^\w.-]/g, '_');
      return { blob, fileName };
    } catch (error) {
      console.error('Error generating PDF:', error);
      throw error;
    }
  }

  async function handleSavePdfToDrive() {
    if (!formData.docente || !formData.grado) {
      Swal.fire({
        icon: 'warning',
        title: 'Datos incompletos',
        text: 'Por favor seleccione docente y grado antes de generar el PDF.',
        confirmButtonColor: '#3085d6',
      });
      return;
    }

    try {
      const { blob, fileName } = await generatePdfBlob();
      pdfBlob = blob;
      pdfFileName = fileName;
      showFolderPickerPdf = true;
    } catch (error) {
      console.error('Error preparing PDF:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudo generar el PDF. Intente nuevamente.',
        confirmButtonColor: '#ef4444',
      });
    }
  }

  function handleFolderSelectedPdf(folder: { id: string; name: string } | null) {
    showFolderPickerPdf = false;
    if (!folder) {
      return;
    }

    showUploadProgressPdf = true;
    uploadPhasePdf = 'uploading';
    uploadCurrentPdf = 1;
    uploadTotalPdf = 1;
    uploadCurrentFilePdf = pdfFileName;

    gdriveService.uploadFile(
      pdfBlob!,
      pdfFileName,
      'application/pdf',
      GOOGLE_CLIENT_ID,
      folder.id
    ).then((result: { success: boolean; fileId?: string; error?: string }) => {
      if (result.success) {
        uploadSuccessCountPdf = 1;
      } else {
        uploadFailedCountPdf = 1;
        console.error('Upload error:', result.error);
      }
      uploadPhasePdf = 'done';

      Swal.fire({
        icon: uploadFailedCountPdf > 0 ? 'warning' : 'success',
        title: uploadFailedCountPdf > 0 ? 'Advertencia' : 'Éxito',
        text: uploadFailedCountPdf > 0
          ? `El PDF no se pudo guardar: ${result.error}`
          : 'El PDF se ha guardado exitosamente en Google Drive.',
        confirmButtonColor: '#3b82f6',
      });

      // Reset
      pdfBlob = null;
      pdfFileName = 'PlanAula.pdf';
      showUploadProgressPdf = false;
    }).catch((error: unknown) => {
      console.error('Upload failed:', error);
      uploadFailedCountPdf = 1;
      uploadPhasePdf = 'done';
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Error al subir el PDF a Google Drive.',
        confirmButtonColor: '#ef4444',
      });
      pdfBlob = null;
      pdfFileName = 'PlanAula.pdf';
      showUploadProgressPdf = false;
    });
  }

  function handlePrint() {
    window.print();
  }

  function handleRecordsSaveToDrive(blob: Blob, fileName: string) {
    recordsBlobToUpload = blob;
    recordsFileNameToUpload = fileName;
    showFolderPickerRecords = true;
  }

  async function handleFolderSelectedRecords(folder: { id: string; name: string } | null) {
    showFolderPickerRecords = false;
    if (!folder || !recordsBlobToUpload) return;

    showUploadProgressRecords = true;
    uploadPhaseRecords = 'uploading';
    uploadCurrentRecords = 1;
    uploadTotalRecords = 1;
    uploadCurrentFileRecords = recordsFileNameToUpload;
    uploadSuccessCountRecords = 0;
    uploadFailedCountRecords = 0;

    try {
      const result = await gdriveService.uploadFile(
        recordsBlobToUpload,
        recordsFileNameToUpload,
        'application/pdf',
        GOOGLE_CLIENT_ID,
        folder.id
      );

      if (result.success) {
        uploadSuccessCountRecords = 1;
      } else {
        uploadFailedCountRecords = 1;
        console.error('Upload error:', result.error);
      }
      uploadPhaseRecords = 'done';

      Swal.fire({
        icon: uploadFailedCountRecords > 0 ? 'warning' : 'success',
        title: uploadFailedCountRecords > 0 ? 'Advertencia' : 'Éxito',
        text: uploadFailedCountRecords > 0
          ? `El PDF no se pudo guardar: ${result.error}`
          : 'El PDF se ha guardado exitosamente en Google Drive.',
        confirmButtonColor: '#3b82f6',
      });
    } catch (error) {
      console.error('Upload failed:', error);
      uploadFailedCountRecords = 1;
      uploadPhaseRecords = 'done';
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Error al subir el PDF a Google Drive.',
        confirmButtonColor: '#ef4444',
      });
    } finally {
      recordsBlobToUpload = null;
      recordsFileNameToUpload = '';
      showUploadProgressRecords = false;
    }
  }
</script>

<div class="flex h-screen flex-col bg-[rgb(var(--bg-primary))]">
  <ModuleHeader title="Plan de Aula" subtitle="Planeación académica detallada" {onBack}>
    {#snippet actions()}
      <button
        onclick={resetForm}
        class="flex items-center gap-2 rounded-lg bg-[rgb(var(--bg-secondary))] border border-[rgb(var(--border-primary))] px-4 py-2 text-sm font-medium text-[rgb(var(--text-primary))] transition-colors duration-200 hover:border-[rgb(var(--accent-primary))]"
      >
        Nuevo
      </button>
      <button
        onclick={handleSavePdfToDrive}
        class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors duration-200 hover:bg-blue-700"
      >
        Guardar PDF en Drive
      </button>
      <button
        onclick={handleSave}
        disabled={saving}
        class="flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition-colors duration-200 hover:bg-emerald-700 disabled:opacity-50"
      >
        {saving ? "Guardando..." : "Guardar en Drive"}
      </button>
    {/snippet}
  </ModuleHeader>

  {#if showFolderPickerPdf}
    <DriveFolderPicker
      onSelect={handleFolderSelectedPdf}
      onClose={() => { showFolderPickerPdf = false; pdfBlob = null; pdfFileName = 'PlanAula.pdf'; }}
    />
  {/if}

  {#if showUploadProgressPdf}
    <UploadProgressModal
      phase={uploadPhasePdf}
      current={uploadCurrentPdf}
      total={uploadTotalPdf}
      currentFile={uploadCurrentFilePdf}
      successCount={uploadSuccessCountPdf}
      failedCount={uploadFailedCountPdf}
      fileType="pdf"
    />
  {/if}

  {#if showFolderPickerRecords}
    <DriveFolderPicker
      onSelect={handleFolderSelectedRecords}
      onClose={() => { showFolderPickerRecords = false; recordsBlobToUpload = null; recordsFileNameToUpload = ''; }}
    />
  {/if}

  {#if showUploadProgressRecords}
    <UploadProgressModal
      phase={uploadPhaseRecords}
      current={uploadCurrentRecords}
      total={uploadTotalRecords}
      currentFile={uploadCurrentFileRecords}
      successCount={uploadSuccessCountRecords}
      failedCount={uploadFailedCountRecords}
      fileType="pdf"
    />
  {/if}

  <div class="flex flex-1 overflow-hidden" in:fade={{ duration: 300 }}>
    <aside class="w-72 flex-shrink-0 border-r border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-secondary))]">
      <div class="flex h-full flex-col p-4">
        <div class="mb-3 flex items-center justify-between">
          <h3 class="text-sm font-bold text-[rgb(var(--text-primary))]">Registros Guardados</h3>
          <button
            onclick={fetchHistory}
            class="rounded p-1 text-[rgb(var(--text-muted))] transition-colors hover:bg-[rgb(var(--bg-tertiary))] hover:text-[rgb(var(--text-primary))]"
            title="Actualizar"
          >
            <svg class="h-4 w-4 {loadingHistory ? 'animate-spin' : ''}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
              <path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/>
              <path d="M16 16h5v5"/>
            </svg>
          </button>
        </div>

        {#if loadingHistory}
          <div class="flex flex-1 items-center justify-center">
            <div class="h-8 w-8 border-2 border-[rgb(var(--accent-primary))] border-t-transparent rounded-full animate-spin"></div>
          </div>
        {:else if savedRows.length === 0}
          <div class="flex flex-1 items-center justify-center">
            <p class="text-sm text-[rgb(var(--text-muted))]">No hay registros guardados</p>
          </div>
        {:else}
          <RecordFilter records={savedRows} onSelectRecord={selectRecord} onSaveToDrive={handleRecordsSaveToDrive} />
        {/if}
      </div>
    </aside>

    <main class="flex-1 overflow-y-auto p-6">
      <div class="mx-auto max-w-6xl">
        <div class="mb-6 overflow-hidden rounded-xl border border-[rgb(var(--border-primary))] bg-[rgb(var(--card-bg))]">
          <table class="w-full border-collapse">
            <tbody>
              <tr>
                <td rowspan="2" class="w-20 border border-[rgb(var(--border-primary))] p-3 text-center align-middle">
                  {#if eieLogo}
                    <img src={eieLogo} alt="Escudo" class="mx-auto h-16 w-16 object-contain" />
                  {:else}
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-16 w-16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                      <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                      <path d="M9 12l2 2 4-4"/>
                    </svg>
                  {/if}
                </td>
                <td class="border border-[rgb(var(--border-primary))] p-3">
                  <h1 class="text-lg font-bold text-[rgb(var(--text-primary))]">INSTITUCIÓN EDUCATIVA INSTITUTO GUÁTICA</h1>
                  <p class="text-sm text-[rgb(var(--text-muted))]">DANE</p>
                </td>
                <td rowspan="2" class="border border-[rgb(var(--border-primary))] p-3 align-middle">
                  <h2 class="text-sm font-bold text-[rgb(var(--text-primary))]">GESTIÓN ACADÉMICA</h2>
                </td>
                <td class="w-36 border border-[rgb(var(--border-primary))] p-2 text-center text-xs font-bold text-[rgb(var(--text-muted))]">CÓDIGO</td>
              </tr>
              <tr>
                <td class="border border-[rgb(var(--border-primary))] p-3"></td>
                <td class="border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-tertiary))] p-3 text-center">
                  <div class="text-sm font-bold text-[rgb(var(--text-primary))]">PLAN DE AULA</div>
                  <div class="text-xs text-[rgb(var(--text-muted))]">2026</div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="mb-6 rounded-xl border border-[rgb(var(--border-primary))] bg-[rgb(var(--card-bg))] p-4">
          <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="flex flex-col gap-1.5">
              <label for="area" class="text-xs font-bold uppercase text-[rgb(var(--text-muted))]">Área:</label>
              <AreaSelector bind:value={formData.area} materias={materias} loading={isLoadingMaterias} />
            </div>
            <div class="flex flex-col gap-1.5 md:col-span-2">
              <label for="docente" class="text-xs font-bold uppercase text-[rgb(var(--text-muted))]">Docente:</label>
              <TeacherSelector bind:value={formData.docente} docentes={docentes} loading={isLoadingDocentes} />
            </div>
            <div class="flex flex-col gap-1.5">
              <label for="periodo" class="text-xs font-bold uppercase text-[rgb(var(--text-muted))]">Período:</label>
              <PeriodSelector bind:value={formData.periodo} />
            </div>
            <div class="flex flex-col gap-1.5">
              <label for="grado" class="text-xs font-bold uppercase text-[rgb(var(--text-muted))]">Grado:</label>
              <GradeSelector bind:value={formData.grado} />
            </div>
            <div class="flex flex-col gap-1.5">
              <label for="intensidad" class="text-xs font-bold uppercase text-[rgb(var(--text-muted))]">Intensidad Semanal:</label>
              <IntensitySelector bind:value={formData.intensidad} />
            </div>
          </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-[rgb(var(--border-primary))]">
          <table class="w-full border-collapse">
            <thead>
              <tr class="bg-[rgb(var(--bg-tertiary))]">
                <th class="border border-[rgb(var(--border-primary))] p-3 text-center text-xs font-bold text-[rgb(var(--text-primary))]">
                  Estándares básicos de competencia<br/><span class="font-normal text-[rgb(var(--text-muted))]">CONTENIDOS</span>
                </th>
                <th class="border border-[rgb(var(--border-primary))] p-3 text-center text-xs font-bold text-[rgb(var(--text-primary))]">
                  INDICADORES DE DESEMPEÑO
                </th>
                <th class="border border-[rgb(var(--border-primary))] p-3 text-center text-xs font-bold text-[rgb(var(--text-primary))]">
                  DERECHOS BÁSICOS DE APRENDIZAJE<br/><span class="font-normal text-[rgb(var(--text-muted))]">DBA</span>
                </th>
                <th class="border border-[rgb(var(--border-primary))] p-3 text-center text-xs font-bold text-[rgb(var(--text-primary))]">
                  CRITERIOS DE EVALUACIÓN
                </th>
                <th class="border border-[rgb(var(--border-primary))] p-3 text-center text-xs font-bold text-[rgb(var(--text-primary))]">
                  ACTIVIDADES Y RECURSOS
                </th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="border border-[rgb(var(--border-primary))] align-top">
                  <textarea
                    bind:value={formData.contenidos}
                    placeholder="Describa los estándares y contenidos..."
                    class="min-h-[300px] w-full resize-none bg-transparent p-3 text-sm text-[rgb(var(--text-primary))] placeholder:text-[rgb(var(--text-muted))] focus:outline-none"
                  ></textarea>
                </td>
                <td class="border border-[rgb(var(--border-primary))] align-top">
                  <textarea
                    bind:value={formData.indicadores}
                    placeholder="Describa los indicadores de desempeño..."
                    class="min-h-[300px] w-full resize-none bg-transparent p-3 text-sm text-[rgb(var(--text-primary))] placeholder:text-[rgb(var(--text-muted))] focus:outline-none"
                  ></textarea>
                </td>
                <td class="border border-[rgb(var(--border-primary))] align-top">
                  <textarea
                    bind:value={formData.dba}
                    placeholder="Describa los DBA..."
                    class="min-h-[300px] w-full resize-none bg-transparent p-3 text-sm text-[rgb(var(--text-primary))] placeholder:text-[rgb(var(--text-muted))] focus:outline-none"
                  ></textarea>
                </td>
                <td class="border border-[rgb(var(--border-primary))] align-top">
                  <textarea
                    bind:value={formData.criterios}
                    placeholder="Describa los criterios de evaluación..."
                    class="min-h-[300px] w-full resize-none bg-transparent p-3 text-sm text-[rgb(var(--text-primary))] placeholder:text-[rgb(var(--text-muted))] focus:outline-none"
                  ></textarea>
                </td>
                <td class="border border-[rgb(var(--border-primary))] align-top">
                  <textarea
                    bind:value={formData.actividades}
                    placeholder="Describa las actividades y recursos..."
                    class="min-h-[300px] w-full resize-none bg-transparent p-3 text-sm text-[rgb(var(--text-primary))] placeholder:text-[rgb(var(--text-muted))] focus:outline-none"
                  ></textarea>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>

<style>
  @media print {
    :global(body) {
      background-color: white !important;
      color: black !important;
    }

    :global(aside),
    :global(header) {
      display: none !important;
    }

    main {
      padding: 0 !important;
      overflow: visible !important;
    }

    .min-h-\[300px\] {
      min-height: 200px !important;
    }

    textarea {
      color: black !important;
      background: transparent !important;
    }
  }
</style>