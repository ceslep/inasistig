<script lang="ts">
  import { onMount } from "svelte";
  import Swal from "sweetalert2";
  import { getEstudiantes, getMaterias, getDocentes } from "../../api/service";
  import { theme } from "../lib/themeStore";
  import { docenteName } from "../lib/authStore";
  import { SelectField, DatePicker } from './anotador';
  import ModuleHeader from "./ModuleHeader.svelte";
  import { gdriveService } from "../lib/gdriveService";
  import { GOOGLE_CLIENT_ID } from "../constants";
  import type { Decision } from "../lib/types/comision";
  import { DECISIONES, PERIDOS } from "../lib/types/comision";
  import { Save, FileDown, Cloud, Moon, Sun, Loader2, Users, X, Check } from '@lucide/svelte';
  import eieLogo from '../assets/eie.png';

  interface Props {
    onBack: () => void;
  }

  const { onBack }: Props = $props();

  interface Estudiante {
    nombre: string;
    grado: number | string;
  }

  interface Materia {
    materia: string;
  }

  let estudiantes: Estudiante[] = $state([]);
  let materias: Materia[] = $state([]);
  let docentes: string[] = $state([]);
  let isLoadingEstudiantes = $state(false);
  let isLoadingMaterias = $state(false);
  let isLoadingDocentes = $state(false);

  let formData = $state({
    fecha: new Date().toLocaleDateString('en-CA'),
    periodo: '' as 'UNO' | 'DOS' | 'TRES' | 'CUATRO' | '',
    grado: '',
    presidente: '',
  });

  let selectedDocentes: string[] = $state([]);
  let isLoading = $state(false);
  let showFieldErrors = $state(false);

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

  let filteredGrados = $derived(
    [...new Set(estudiantes.map((e) => e.grado.toString()))].filter((g) => !g.includes("-"))
  );

  let estudiantesFiltrados = $derived(
    formData.grado
      ? estudiantes.filter((e) => e.grado.toString() === formData.grado)
      : []
  );

  let missingFields = $derived.by(() => {
    const fields: string[] = [];
    if (!formData.fecha) fields.push("fecha");
    if (!formData.periodo) fields.push("periodo");
    if (!formData.grado) fields.push("grado");
    if (!formData.presidente) fields.push("presidente");
    return fields;
  });

  interface DecisionMateria {
    materia: string;
    decision: Decision;
  }

  interface StudentDecision {
    nombre: string;
    grado: string;
    decisiones: DecisionMateria[];
    observaciones: string;
  }

  let decisiones: StudentDecision[] = $state([]);
  let expandedStudents: string[] = $state([]);

  let allDecisionsMade = $derived(
    decisiones.length > 0 && decisiones.every(d => 
      d.decisiones.length > 0 && d.decisiones.every(dm => dm.decision !== 'pendiente')
    )
  );

  let statsDecisiones = $derived.by(() => {
    const stats = { promovido: 0, no_promovido: 0, plan_mejoramiento: 0, normal: 0, pendiente: 0 };
    for (const d of decisiones) {
      for (const dm of d.decisiones) {
        stats[dm.decision as keyof typeof stats]++;
      }
    }
    return stats;
  });

  const toggleTheme = () => {
    theme.update((t) => {
      if (t === "light") return "dim";
      if (t === "dim") return "dark";
      return "light";
    });
  };

  const loadData = async () => {
    isLoadingEstudiantes = true;
    isLoadingMaterias = true;
    isLoadingDocentes = true;

    try {
      const [estudiantesData, materiasData, docentesData] = await Promise.all([
        getEstudiantes(),
        getMaterias(),
        getDocentes(),
      ]);
      estudiantes = estudiantesData;
      materias = materiasData;
      docentes = docentesData;
    } catch (error) {
      console.error("Error cargando datos:", error);
    } finally {
      isLoadingEstudiantes = false;
      isLoadingMaterias = false;
      isLoadingDocentes = false;
    }
  };

  const initializeDecisiones = () => {
    if (!formData.grado) {
      decisiones = [];
      return;
    }
    decisiones = estudiantesFiltrados.map((e) => ({
      nombre: e.nombre,
      grado: formData.grado,
      decisiones: materias.map((m) => ({
        materia: m.materia,
        decision: 'normal' as Decision,
      })),
      observaciones: '',
    }));
  };

  const toggleDocente = (docente: string) => {
    if (selectedDocentes.includes(docente)) {
      selectedDocentes = selectedDocentes.filter(d => d !== docente);
    } else {
      selectedDocentes = [...selectedDocentes, docente];
    }
  };

  const setDecisionMateria = (estudianteNombre: string, materia: string, decision: Decision) => {
    decisiones = decisiones.map((d) => {
      if (d.nombre === estudianteNombre) {
        return {
          ...d,
          decisiones: d.decisiones.map((dm) => {
            if (dm.materia === materia) {
              return { ...dm, decision };
            }
            return dm;
          }),
        };
      }
      return d;
    });
  };

  const setObservaciones = (estudianteNombre: string, obs: string) => {
    decisiones = decisiones.map((d) => {
      if (d.nombre === estudianteNombre) {
        return { ...d, observaciones: obs };
      }
      return d;
    });
  };

  const selectAllDecisionsForStudent = (estudianteNombre: string, decision: Decision) => {
    decisiones = decisiones.map((d) => {
      if (d.nombre === estudianteNombre) {
        return {
          ...d,
          decisiones: d.decisiones.map((dm) => ({ ...dm, decision })),
        };
      }
      return d;
    });
  };

  const clearAllDecisionsForStudent = (estudianteNombre: string) => {
    decisiones = decisiones.map((d) => {
      if (d.nombre === estudianteNombre) {
        return {
          ...d,
          decisiones: d.decisiones.map((dm) => ({ ...dm, decision: 'pendiente' as Decision })),
        };
      }
      return d;
    });
  };

  const toggleExpanded = (estudianteNombre: string) => {
    if (expandedStudents.includes(estudianteNombre)) {
      expandedStudents = expandedStudents.filter(n => n !== estudianteNombre);
    } else {
      expandedStudents = [...expandedStudents, estudianteNombre];
    }
  };

  const getMateriaDecisionCount = (studentDecision: StudentDecision) => {
    const counts = { promovido: 0, no_promovido: 0, plan_mejoramiento: 0, normal: 0, pendiente: 0 };
    for (const dm of studentDecision.decisiones) {
      counts[dm.decision as keyof typeof counts]++;
    }
    return counts;
  };

  const getStudentOverallDecision = (studentDecision: StudentDecision) => {
    const counts = getMateriaDecisionCount(studentDecision);
    if (counts.no_promovido > 0) return 'no_promovido';
    if (counts.plan_mejoramiento > 0) return 'plan_mejoramiento';
    if (counts.pendiente > 0) return 'pendiente';
    if (counts.promovido > 0 && counts.normal === 0 && counts.promovido === studentDecision.decisiones.length) return 'promovido';
    if (counts.normal > 0 && counts.promovido === 0 && counts.no_promovido === 0 && counts.plan_mejoramiento === 0 && counts.pendiente === 0) return 'normal';
    if (counts.promovido > 0) return 'promovido';
    return 'pendiente';
  };

  const generatePayload = () => {
    return decisiones
      .filter(d => d.decisiones.some(dm => dm.decision !== 'normal'))
      .flatMap((d) =>
        d.decisiones
          .filter(dm => dm.decision !== 'normal')
          .map((dm) => [
            new Date().toISOString(),
            formData.fecha,
            formData.periodo,
            formData.grado,
            formData.presidente,
            selectedDocentes.join('; '),
            d.nombre,
            dm.materia,
            dm.decision,
            d.observaciones,
            $docenteName || '',
          ])
      );
  };

  const saveToSheets = async () => {
    isLoading = true;
    try {
      const payload = generatePayload();
      const response = await fetch('/gs/', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          spreadsheetId: 'comision_evaluacion',
          worksheetTitle: 'Actas',
          data: payload,
        }),
      });

      if (!response.ok) throw new Error('Error al guardar');

      await Swal.fire({
        icon: 'success',
        title: '¡Guardado!',
        text: 'Acta guardada exitosamente en Sheets',
        timer: 3000,
        showConfirmButton: false,
        position: 'top-end',
        toast: true,
      });
    } catch (error) {
      console.error('Error:', error);
      await Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudo guardar el acta',
        confirmButtonColor: '#ef4444',
      });
    } finally {
      isLoading = false;
    }
  };

  const loadPdfLibraries = async () => {
    const [{ default: jsPDF }, { default: autoTable }] = await Promise.all([
      import('jspdf'),
      import('jspdf-autotable'),
    ]);
    return { jsPDF, autoTable };
  };

  const COLOR_INDIGO: [number, number, number] = [99, 102, 241];
  const COLOR_EMERALD: [number, number, number] = [16, 185, 129];
  const COLOR_AMBER: [number, number, number] = [245, 158, 11];

  const formatDateForPDF = (dateStr: string) => {
    try {
      return new Date(dateStr).toLocaleDateString('es-CO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      });
    } catch {
      return dateStr;
    }
  };

  const generatePDF = async () => {
    if (!formData.periodo || !formData.grado) {
      await Swal.fire({
        icon: 'warning',
        title: 'Campos requeridos para PDF',
        text: 'Seleccione periodo y grado antes de generar el PDF',
        confirmButtonColor: '#6366f1',
      });
      return;
    }

    const { jsPDF, autoTable } = await loadPdfLibraries();
    const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'letter' });

    const pageWidth = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();
    const margin = 15;
    const contentWidth = pageWidth - margin * 2;

    const logoWidth = 25;
    const logoHeight = 25;
    const logoX = (pageWidth - logoWidth) / 2;
    const logoY = 12;

    doc.addImage(eieLogo, 'PNG', logoX, logoY, logoWidth, logoHeight);

    doc.setFontSize(16);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(30, 30, 30);
    doc.text('COMISION DE EVALUACION Y PROMOCION', pageWidth / 2, logoY + logoHeight + 8, { align: 'center' });

    doc.setFontSize(11);
    doc.setFont('helvetica', 'normal');
    doc.text('Instituto de Educacion Integral - EIE', pageWidth / 2, logoY + logoHeight + 14, { align: 'center' });
    doc.setTextColor(80, 80, 80);
    doc.setFontSize(9);
    doc.text('Formacion Integral, Excelencia y Transformacion Social', pageWidth / 2, logoY + logoHeight + 19, { align: 'center' });

    doc.setDrawColor(200, 200, 200);
    doc.line(margin, logoY + logoHeight + 23, pageWidth - margin, logoY + logoHeight + 23);

    doc.setTextColor(30, 30, 30);
    doc.setFontSize(10);
    doc.setFont('helvetica', 'bold');
    const infoY = logoY + logoHeight + 30;
    doc.text('INFORMACION DE LA SESION', margin, infoY);

    doc.setFont('helvetica', 'normal');
    doc.setFontSize(9);
    const infoData = [
      ['Fecha de Sesion:', formatDateForPDF(formData.fecha)],
      ['Periodo Academico:', formData.periodo],
      ['Grado/Grupo:', formData.grado.replace(/0(\d)$/, "°$1").replace(/(\d{1,2})0(\d)/, "$1°$2")],
      ['Presidente:', formData.presidente],
      ['Docentes Asistentes:', selectedDocentes.join(', ') || 'Ninguno'],
    ];

    let infoYPos = infoY + 6;
    for (const [label, value] of infoData) {
      doc.setFont('helvetica', 'bold');
      doc.text(label, margin, infoYPos);
      doc.setFont('helvetica', 'normal');
      doc.text(value, margin + 35, infoYPos);
      infoYPos += 5;
    }

    doc.setDrawColor(200, 200, 200);
    doc.line(margin, infoYPos + 2, pageWidth - margin, infoYPos + 2);

    doc.setFont('helvetica', 'bold');
    doc.setFontSize(10);
    doc.text('SEGUIMIENTO POR ESTUDIANTE', margin, infoYPos + 10);

    const estudiantesParaPDF = decisiones.filter(d => {
      const overall = getStudentOverallDecision(d);
      return overall !== 'normal';
    });

    const tableData = estudiantesParaPDF.map((d, i) => {
      const counts = getMateriaDecisionCount(d);
      const overall = getStudentOverallDecision(d);
      const decisionLabel = overall === 'promovido' ? 'PROMOVIDO' : overall === 'no_promovido' ? 'NO PROMOVIDO' : overall === 'plan_mejoramiento' ? 'PLAN MEJORAMIENTO' : 'PENDIENTE';
      return [
        (i + 1).toString(),
        d.nombre,
        `${counts.promovido}P / ${counts.no_promovido}N / ${counts.plan_mejoramiento}PM / ${counts.pendiente}PE`,
        decisionLabel,
        d.observaciones || '-',
      ];
    });

    autoTable(doc, {
      startY: infoYPos + 14,
      head: [['#', 'Estudiante', 'Resumen Materias', 'Decision', 'Observaciones']],
      body: tableData,
      theme: 'striped',
      headStyles: {
        fillColor: COLOR_INDIGO,
        textColor: 255,
        fontStyle: 'bold',
        fontSize: 8,
      },
      styles: {
        fontSize: 8,
        cellPadding: 4,
        lineColor: [200, 200, 200],
        lineWidth: 0.1,
      },
      columnStyles: {
        0: { cellWidth: 10, halign: 'center' },
        1: { cellWidth: 50 },
        2: { cellWidth: 35, halign: 'center', fontSize: 7 },
        3: { cellWidth: 35, halign: 'center', fontStyle: 'bold' },
        4: { cellWidth: 60 },
      },
      alternateRowStyles: {
        fillColor: [248, 248, 248],
      },
      didParseCell: (data) => {
        if (data.section === 'body' && data.column.index === 3) {
          const studentDecision = estudiantesParaPDF[data.row.index];
          if (!studentDecision) return;
          const decision = getStudentOverallDecision(studentDecision);
          if (decision === 'promovido') {
            data.cell.styles.fillColor = [220, 252, 231];
            data.cell.styles.textColor = [21, 128, 61];
          } else if (decision === 'no_promovido') {
            data.cell.styles.fillColor = [254, 226, 226];
            data.cell.styles.textColor = [220, 38, 38];
          } else if (decision === 'plan_mejoramiento') {
            data.cell.styles.fillColor = [254, 243, 199];
            data.cell.styles.textColor = [180, 83, 9];
          }
        }
      },
    });

    const finalY = (doc as any).lastAutoTable.finalY || 150;

    const firmaY = finalY + 25;
    doc.setFontSize(10);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(30, 30, 30);
    doc.text('FIRMAS', margin, firmaY);

    doc.setFontSize(9);
    doc.setFont('helvetica', 'normal');

    const firmaX1 = margin + 10;
    const firmaX2 = pageWidth / 2 + 10;
    const firmaLineY = firmaY + 20;

    doc.line(firmaX1, firmaLineY, firmaX1 + 70, firmaLineY);
    doc.line(firmaX2, firmaLineY, firmaX2 + 70, firmaLineY);

    doc.setFontSize(8);
    doc.text('Presidente', firmaX1 + 25, firmaLineY + 5, { align: 'center' });
    doc.text(formData.presidente || '_________________', firmaX1 + 25, firmaLineY + 10, { align: 'center' });

    doc.text('Lider de Comision', firmaX2 + 25, firmaLineY + 5, { align: 'center' });
    doc.text(selectedDocentes.length > 0 ? selectedDocentes[0] : '_________________', firmaX2 + 25, firmaLineY + 10, { align: 'center' });

    doc.setDrawColor(200, 200, 200);
    doc.line(margin, firmaY + 35, pageWidth - margin, firmaY + 35);

    doc.setDrawColor(200, 200, 200);
    doc.line(margin, finalY + 8, pageWidth - margin, finalY + 8);

    doc.setFontSize(8);
    doc.setTextColor(100, 100, 100);
    doc.text('Decreto 1290 de 2009 - Articulo 11', margin, finalY + 14);
    doc.text('Instituto de Educacion Integral - EIE', pageWidth - margin, finalY + 14, { align: 'right' });

    const totalPages = doc.getNumberOfPages();
    for (let i = 1; i <= totalPages; i++) {
      doc.setPage(i);
      doc.setFontSize(8);
      doc.setTextColor(150, 150, 150);
      doc.text(`Pagina ${i} de ${totalPages}`, pageWidth / 2, pageHeight - 8, { align: 'center' });
    }

    const filename = `Acta_Comision_${formData.grado}_${formData.periodo}_${formData.fecha}.pdf`;
    doc.save(filename);

    return filename;
  };

  let currentPdfBlob: Blob | null = null;

  const saveToDrive = async () => {
    if (!currentPdfBlob) {
      await Swal.fire({
        icon: 'info',
        title: 'Sin PDF',
        text: 'Genere el PDF primero usando el boton Generar PDF',
        confirmButtonColor: '#6366f1',
      });
      return;
    }
    isLoading = true;
    try {
      const filename = `Acta_Comision_${formData.grado}_${formData.periodo}_${formData.fecha}.pdf`;
      const blob = currentPdfBlob;

      const result = await gdriveService.uploadFile(
        blob,
        filename,
        'application/pdf',
        GOOGLE_CLIENT_ID,
      );
      if (result.success) {
        await Swal.fire({
          icon: 'success',
          title: '¡Guardado en Drive!',
          text: 'El acta se guardo en Google Drive',
          timer: 3000,
          showConfirmButton: false,
          position: 'top-end',
          toast: true,
        });
      }
    } catch (error) {
      console.error('Error:', error);
      await Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudo guardar en Drive',
        confirmButtonColor: '#ef4444',
      });
    } finally {
      isLoading = false;
    }
  };

  const generateAndSave = async () => {
    showFieldErrors = true;

    if (missingFields.length > 0) {
      await Swal.fire({
        icon: 'warning',
        title: 'Campos requeridos',
        html: `Complete: <strong>${missingFields.join(', ')}</strong>`,
        confirmButtonColor: '#6366f1',
      });
      return;
    }

    if (!formData.periodo || !formData.grado) {
      await Swal.fire({
        icon: 'warning',
        title: 'Campos requeridos para PDF',
        text: 'Seleccione periodo y grado antes de guardar',
        confirmButtonColor: '#6366f1',
      });
      return;
    }

    if (decisiones.length === 0) {
      await Swal.fire({
        icon: 'warning',
        title: 'Sin estudiantes',
        text: 'No hay estudiantes en este grado',
        confirmButtonColor: '#6366f1',
      });
      return;
    }

    isLoading = true;
    try {
      const { jsPDF, autoTable } = await loadPdfLibraries();
      const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'letter' });

      const pageWidth = doc.internal.pageSize.getWidth();

      doc.setFontSize(14);
      doc.setFont('helvetica', 'bold');
      doc.text('COMISION DE EVALUACION Y PROMOCION', pageWidth / 2, 20, { align: 'center' });

      doc.setFontSize(10);
      doc.setFont('helvetica', 'normal');
      doc.text('Instituto Guatica', pageWidth / 2, 26, { align: 'center' });

      doc.setFontSize(9);
      doc.text(`Fecha: ${formData.fecha}  |  Periodo: ${formData.periodo}  |  Grado: ${formData.grado}`, pageWidth / 2, 32, { align: 'center' });
      doc.text(`Presidente: ${formData.presidente}`, pageWidth / 2, 37, { align: 'center' });
      doc.text(`Docentes: ${selectedDocentes.join(', ')}`, pageWidth / 2, 42, { align: 'center' });

      const tableData = decisiones.map((d, i) => {
        const counts = getMateriaDecisionCount(d);
        const overall = getStudentOverallDecision(d);
        return [
          (i + 1).toString(),
          d.nombre,
          `${counts.promovido}/${d.decisiones.length}`,
          overall.replace('_', ' ').toUpperCase(),
          d.observaciones || '-',
        ];
      });

      autoTable(doc, {
        startY: 50,
        head: [['#', 'Estudiante', 'Materias', 'Decision', 'Observaciones']],
        body: tableData,
        theme: 'striped',
        headStyles: { fillColor: [99, 102, 241], textColor: 255 },
        styles: { fontSize: 8, cellPadding: 3 },
        columnStyles: {
          0: { cellWidth: 10 },
          1: { cellWidth: 45 },
          2: { cellWidth: 30 },
          3: { cellWidth: 30 },
          4: { cellWidth: 75 },
        },
      });

      const filename = `Acta_Comision_${formData.grado}_${formData.periodo}_${formData.fecha}.pdf`;
      currentPdfBlob = doc.output('blob');

      await saveToSheets();
      doc.save(filename);
      await saveToDrive();

      formData = { fecha: new Date().toLocaleDateString('en-CA'), periodo: '', grado: '', presidente: '' };
      selectedDocentes = [];
      decisiones = [];
      showFieldErrors = false;
    } catch (error) {
      console.error('Error:', error);
      await Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Ocurrio un error al generar el acta',
        confirmButtonColor: '#ef4444',
      });
    } finally {
      isLoading = false;
    }
  };

  $effect(() => {
    if (formData.grado) {
      initializeDecisiones();
    }
  });

  onMount(() => {
    loadData();
  });
</script>

<svelte:window onkeydown={(e) => { if (e.ctrlKey && e.key === 'Enter') generateAndSave(); }} />

<ModuleHeader title="Comision de Evaluacion" subtitle="Decreto 1290 de 2009" {onBack} />

<div
  class="min-h-screen flex flex-col lg:flex-row transition-colors duration-200"
  style="background-color: {styles.bg};"
>
  <aside
    class="w-full lg:w-80 lg:h-screen lg:sticky lg:top-0 border-b lg:border-b-0 lg:border-r transition-colors duration-200 p-6 lg:p-8 flex flex-col flex-shrink-0 z-40"
    style="background-color: {styles.cardBg}; border-color: {styles.cardBorder};"
  >
    <div class="flex flex-col lg:flex-col items-center justify-between lg:justify-start gap-4 lg:gap-8">
      <div class="flex flex-wrap justify-center lg:flex-col gap-3 w-full">
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
          onclick={saveToSheets}
          disabled={isLoading}
          class="inline-flex items-center justify-center gap-2 px-3 lg:px-4 py-2 lg:py-3 border rounded-lg transition-all duration-200 hover:bg-black/5 dark:hover:bg-white/5 disabled:opacity-50"
          style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
          title="Guardar en Sheets"
        >
          <Cloud class="w-5 h-5" />
          <span class="text-sm font-medium hidden lg:inline">Sheets</span>
        </button>

        <button
          onclick={generatePDF}
          disabled={isLoading}
          class="inline-flex items-center justify-center gap-2 px-3 lg:px-4 py-2 lg:py-3 border rounded-lg transition-all duration-200 hover:bg-black/5 dark:hover:bg-white/5 disabled:opacity-50"
          style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
          title="Generar PDF"
        >
          <FileDown class="w-5 h-5" />
          <span class="text-sm font-medium hidden lg:inline">PDF</span>
        </button>

        <button
          onclick={saveToDrive}
          disabled={isLoading}
          class="inline-flex items-center justify-center gap-2 px-3 lg:px-4 py-2 lg:py-3 border rounded-lg transition-all duration-200 hover:bg-black/5 dark:hover:bg-white/5 disabled:opacity-50"
          style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};"
          title="Guardar en Drive"
        >
          <Save class="w-5 h-5" />
          <span class="text-sm font-medium hidden lg:inline">Drive</span>
        </button>
      </div>
    </div>

    <div class="mt-8 space-y-4">
      <div class="p-4 rounded-xl border" style="background-color: {styles.inputBg}; border-color: {styles.border};">
        <h4 class="text-xs font-semibold uppercase tracking-wider mb-3" style="color: {styles.label};">Resumen</h4>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between">
            <span style="color: {styles.text};">Total:</span>
            <span class="font-bold" style="color: rgb(var(--accent-primary));">{decisiones.length}</span>
          </div>
          <div class="flex justify-between">
            <span class="flex items-center gap-1" style="color: {styles.text};">✅ Promovido:</span>
            <span class="font-bold text-green-600">{statsDecisiones.promovido}</span>
          </div>
          <div class="flex justify-between">
            <span class="flex items-center gap-1" style="color: {styles.text};">❌ No Promovido:</span>
            <span class="font-bold text-red-600">{statsDecisiones.no_promovido}</span>
          </div>
          <div class="flex justify-between">
            <span class="flex items-center gap-1" style="color: {styles.text};">📋 Plan:</span>
            <span class="font-bold text-amber-600">{statsDecisiones.plan_mejoramiento}</span>
          </div>
          <div class="flex justify-between">
            <span class="flex items-center gap-1" style="color: {styles.text};">⏳ Pendiente:</span>
            <span class="font-bold text-zinc-500">{statsDecisiones.pendiente}</span>
          </div>
        </div>
      </div>

      {#if allDecisionsMade}
        <div class="p-3 rounded-lg border bg-green-50 dark:bg-green-900/30 border-green-200 dark:border-green-700">
          <p class="text-xs text-green-700 dark:text-green-300 text-center">Todas las decisiones tomadas</p>
        </div>
      {/if}
    </div>
  </aside>

  <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
    <div
      class="max-w-6xl mx-auto rounded-2xl p-6 lg:p-8 transition-colors duration-200 border"
      style="background-color: {styles.cardBg}; border-color: {styles.cardBorder};"
    >
      <form onsubmit={(e) => { e.preventDefault(); generateAndSave(); }} class="space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <DatePicker
            id="fecha"
            label="Fecha de Reunión"
            bind:value={formData.fecha}
            hasError={showFieldErrors && missingFields.includes('fecha')}
          />

          <SelectField
            id="periodo"
            label="Periodo"
            bind:value={formData.periodo}
            options={PERIDOS.map(p => ({ value: p, label: p }))}
            placeholder="Seleccione periodo"
            hasError={showFieldErrors && missingFields.includes('periodo')}
          />

          <SelectField
            id="grado"
            label="Grado"
            bind:value={formData.grado}
            options={filteredGrados.map(g => ({ 
              value: g, 
              label: g.replace(/0(\d)$/, "°$1").replace(/(\d{1,2})0(\d)/, "$1°$2") 
            }))}
            placeholder={isLoadingEstudiantes ? "Cargando..." : "Seleccione grado"}
            isLoading={isLoadingEstudiantes}
            hasError={showFieldErrors && missingFields.includes('grado')}
          />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <SelectField
            id="presidente"
            label="Presidente de Sesión"
            bind:value={formData.presidente}
            options={docentes.map(d => ({ value: d, label: d }))}
            placeholder={isLoadingDocentes ? "Cargando..." : "Seleccione presidente"}
            isLoading={isLoadingDocentes}
            hasError={showFieldErrors && missingFields.includes('presidente')}
          />

          <div class="space-y-2">
            <label class="block text-sm font-medium" style="color: {styles.label};">
              Docentes Asistentes
            </label>
            <div class="flex flex-wrap gap-1.5 p-2 border rounded-xl min-h-[36px] {showFieldErrors && selectedDocentes.length === 0 ? 'ring-2 ring-red-500 border-red-500' : ''}"
              style="background-color: {styles.inputBg}; border-color: {showFieldErrors && selectedDocentes.length === 0 ? '#ef4444' : styles.border};"
            >
              {#each selectedDocentes as docente}
                <span 
                  class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200"
                >
                  {docente.split(' ')[0]}
                  <button
                    type="button"
                    onclick={() => toggleDocente(docente)}
                    class="ml-0.5 hover:bg-indigo-200 dark:hover:bg-indigo-800 rounded-full p-0.5"
                  >
                    <X class="w-3 h-3" />
                  </button>
                </span>
              {/each}
              {#if selectedDocentes.length === 0}
                <span class="text-xs" style="color: {styles.placeholder};">Click en + para agregar</span>
              {/if}
            </div>
            <details class="mt-1">
              <summary class="text-xs cursor-pointer hover:underline" style="color: {styles.label};">
                + Agregar docente ({docentes.length})
              </summary>
              <div class="mt-1 p-2 border rounded-xl max-h-32 overflow-y-auto" style="background-color: {styles.bg}; border-color: {styles.border};">
                {#each docentes.filter(d => !selectedDocentes.includes(d)) as docente}
                  <button
                    type="button"
                    onclick={() => toggleDocente(docente)}
                    class="w-full text-left px-2 py-1 text-xs rounded-lg hover:bg-black/5 dark:hover:bg-white/5 transition-colors"
                    style="color: {styles.text};"
                  >
                    {docente}
                  </button>
                {/each}
              </div>
            </details>
            {#if showFieldErrors && selectedDocentes.length === 0}
              <p class="text-xs text-red-500">Seleccione al menos un docente</p>
            {/if}
          </div>
        </div>

        {#if formData.grado && estudiantesFiltrados.length > 0}
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-bold" style="color: {styles.text};">
                Estudiantes - Grado {formData.grado}
              </h3>
              <span class="text-sm" style="color: {styles.label};">
                {estudiantesFiltrados.length} estudiantes
              </span>
            </div>

            {#each estudiantesFiltrados as estudiante, i}
              {@const studentDecision = decisiones.find(d => d.nombre === estudiante.nombre)}
              {@const overallDecision = studentDecision ? getStudentOverallDecision(studentDecision) : 'pendiente'}
              {@const decisionObj = DECISIONES.find(d => d.value === overallDecision)}
              {@const counts = studentDecision ? getMateriaDecisionCount(studentDecision) : {promovido: 0, no_promovido: 0, plan_mejoramiento: 0, pendiente: 0}}
              {@const isExpanded = expandedStudents.includes(estudiante.nombre)}
              
              <div 
                class="border rounded-xl overflow-hidden transition-all duration-200"
                style="border-color: {styles.border}; background-color: {styles.cardBg};"
              >
                <button
                  type="button"
                  onclick={() => toggleExpanded(estudiante.nombre)}
                  class="w-full flex items-center gap-4 p-4 hover:bg-black/5 dark:hover:bg-white/5 transition-colors"
                >
                  <span class="text-sm font-medium w-6" style="color: {styles.label};">{i + 1}</span>
                  <span class="flex-1 text-left font-medium" style="color: {styles.text};">{estudiante.nombre}</span>
                  <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                      {#each [
                        {key: 'promovido', icon: '✓', color: 'text-green-600'},
                        {key: 'no_promovido', icon: '✗', color: 'text-red-600'},
                        {key: 'plan_mejoramiento', icon: '📋', color: 'text-amber-600'},
                        {key: 'pendiente', icon: '○', color: 'text-zinc-400'}
                      ] as item}
                        {@const count = counts[item.key as keyof typeof counts]}
                        {#if count > 0}
                          <span class="text-xs {item.color}" title="{item.key}: {count}">{count}</span>
                        {/if}
                      {/each}
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {decisionObj?.bgColor} {decisionObj?.textColor}">
                      {decisionObj?.icon} {overallDecision.replace('_', ' ')}
                    </span>
                    <svg 
                      class="w-5 h-5 transition-transform duration-200 {isExpanded ? 'rotate-180' : ''}" 
                      style="color: {styles.icon};"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                  </div>
                </button>

                {#if isExpanded}
                  <div class="border-t p-4 space-y-4" style="border-color: {styles.border}; background-color: {styles.bg};">
                    <div class="flex items-center justify-between">
                      <h4 class="text-sm font-semibold" style="color: {styles.label};">Decisiones por Materia</h4>
                      <div class="flex gap-2">
                        <button
                          type="button"
                          onclick={() => selectAllDecisionsForStudent(estudiante.nombre, 'promovido')}
                          class="text-xs px-3 py-1.5 rounded-lg border hover:bg-green-100 dark:hover:bg-green-900 transition-colors"
                          style="border-color: {styles.border}; color: {styles.text};"
                        >
                          Todo Promovido
                        </button>
                        <button
                          type="button"
                          onclick={() => clearAllDecisionsForStudent(estudiante.nombre)}
                          class="text-xs px-3 py-1.5 rounded-lg border hover:bg-black/5 transition-colors"
                          style="border-color: {styles.border}; color: {styles.label};"
                        >
                          Limpiar
                        </button>
                      </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                      {#each materias as materia}
                        {@const dm = studentDecision?.decisiones.find(dm => dm.materia === materia.materia)}
                        {@const dmDecisionObj = dm ? DECISIONES.find(d => d.value === dm.decision) : null}
                        <div 
                          class="p-3 rounded-lg border transition-all hover:shadow-md"
                          style="border-color: {styles.border}; background-color: {styles.cardBg};"
                        >
                          <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium truncate flex-1" style="color: {styles.text};" title={materia.materia}>
                              {materia.materia}
                            </span>
                          </div>
                          <select
                            value={dm?.decision || 'pendiente'}
                            onchange={(e) => setDecisionMateria(estudiante.nombre, materia.materia, e.currentTarget.value as Decision)}
                            class="w-full appearance-none border rounded-lg px-3 py-2 text-sm cursor-pointer transition-all outline-none {dmDecisionObj?.bgColor} {dmDecisionObj?.textColor}"
                            style="border-color: {styles.border}; background-color: {dmDecisionObj?.bgColor};"
                          >
                            {#each DECISIONES as d}
                              <option value={d.value}>{d.icon} {d.label}</option>
                            {/each}
                          </select>
                        </div>
                      {/each}
                    </div>

                    <div class="pt-3 border-t" style="border-color: {styles.border};">
                      <label class="block text-xs font-medium mb-2" style="color: {styles.label};">Observaciones</label>
                      <input
                        type="text"
                        value={studentDecision?.observaciones || ''}
                        oninput={(e) => setObservaciones(estudiante.nombre, e.currentTarget.value)}
                        placeholder="Agregar observaciones sobre este estudiante..."
                        class="w-full px-4 py-2.5 text-sm rounded-lg border transition-all outline-none"
                        style="background-color: {styles.cardBg}; border-color: {styles.border}; color: {styles.text};"
                      />
                    </div>
                  </div>
                {/if}
              </div>
            {/each}
          </div>

          <div class="flex flex-col sm:flex-row gap-4 justify-end">
            <button
              type="button"
              onclick={generatePDF}
              disabled={isLoading}
              class="inline-flex items-center justify-center gap-2 px-6 py-3 border rounded-xl font-medium transition-all duration-200 hover:bg-black/5 disabled:opacity-50"
              style="border-color: {styles.border}; color: {styles.text};"
            >
              {#if isLoading}
                <Loader2 class="w-5 h-5 animate-spin" />
              {:else}
                <FileDown class="w-5 h-5" />
              {/if}
              Generar PDF
            </button>

            <button
              type="submit"
              disabled={isLoading}
              class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-bold transition-all duration-200 hover:opacity-90 disabled:opacity-50"
              style="background-color: rgb(var(--accent-primary)); color: white;"
            >
              {#if isLoading}
                <Loader2 class="w-5 h-5 animate-spin" />
              {:else}
                <Save class="w-5 h-5" />
              {/if}
              Guardar Todo
            </button>
          </div>
        {:else if formData.grado}
          <div class="text-center py-12">
            <Users class="w-12 h-12 mx-auto mb-4" style="color: {styles.icon};" />
            <p class="text-lg font-medium" style="color: {styles.text};">No hay estudiantes en este grado</p>
            <p class="text-sm" style="color: {styles.label};">Seleccione otro grado o espere</p>
          </div>
        {:else}
          <div class="text-center py-12">
            <Users class="w-12 h-12 mx-auto mb-4" style="color: {styles.icon};" />
            <p class="text-lg font-medium" style="color: {styles.text};">Seleccione un grado para ver los estudiantes</p>
          </div>
        {/if}
      </form>
    </div>
  </main>
</div>

<style>
  select option {
    background-color: rgb(var(--card-bg));
    color: rgb(var(--text-primary));
  }
</style>