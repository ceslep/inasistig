<script lang="ts">
  import type { CoberturaSugerida, CoberturaLiberado } from "../../lib/coberturaUtils";
  import { formatoHora, formatoDia } from "../../lib/coberturaUtils";
  import infoHoras from "../../lib/info_horas.json";
  import html2canvas from "html2canvas";
  import Swal from "sweetalert2";
  import eieLogo from "../../assets/eie.png";

  type DocenteAusente = { nombre: string; tipo: string };
  type GrupoAusenteLocal = { grupo: string; horaInicio: number };

  let {
    diaSeleccionado,
    fechaSeleccionada,
    coberturas,
    gruposAusentes = [],
    docentesAusentes = [],
    modoPDF = false,
    onClose,
    liberadosData = [],
  }: {
    diaSeleccionado: string;
    fechaSeleccionada: string;
    coberturas: CoberturaSugerida[];
    gruposAusentes?: GrupoAusenteLocal[];
    docentesAusentes?: DocenteAusente[];
    modoPDF?: boolean;
    onClose: () => void;
    liberadosData?: CoberturaLiberado[];
  } = $props();

  const gruposNoAsisten = $derived(
    gruposAusentes.filter((g) => g.horaInicio === 1)
  );

  type Liberado = { grupo: string; hora: number; docenteAusente?: string; motivo: string };
  const gruposLiberadosIntraDia = $derived.by<Liberado[]>(() => {
    const lista: Liberado[] = [];
    const vistos = new Set<string>();

    for (const c of coberturas) {
      if (c.docenteCubre) continue;
      if (!c.grupoAusente && !c.grupoACubrir) continue;
      const g = c.grupoAusente || c.grupoACubrir;
      const key = `${g}-${c.hora}`;
      if (vistos.has(key)) continue;
      vistos.add(key);
      lista.push({
        grupo: g,
        hora: c.hora + 1,
        docenteAusente: c.docenteAusente,
        motivo: c.docenteAusente ? `Sin cubridor — ${c.docenteAusente} ausente` : "Sin cubridor",
      });
    }

    for (const g of gruposAusentes) {
      if (g.horaInicio <= 1) continue;
      const key = `${g.grupo}-${g.horaInicio - 1}`;
      if (vistos.has(key)) continue;
      vistos.add(key);
      lista.push({
        grupo: g.grupo,
        hora: g.horaInicio,
        motivo: "Grupo liberado",
      });
    }

    return lista.sort((a, b) => a.hora - b.hora || a.grupo.localeCompare(b.grupo));
  });

  const gruposNoAsistenDesdeLiberados = $derived(
    liberadosData.filter((l) => l.hora_liberada === 1)
  );

  const gruposLiberadosDesdeLiberados = $derived(
    liberadosData.filter((l) => l.hora_liberada > 1)
  );

  let generando = $state(false);
  let pdfBlob = $state<Blob | null>(null);
  let pdfUrl = $state<string | null>(null);
  let mostrandoPreview = $state(false);
  let generandoPDF = $state(false);
  let infoAdicional = $state("");

  const sugerenciasInfo = [
    "Reunión de Docentes 1:30 pm",
    "Comisión de Evaluación y Promoción",
    "Consejo Directivo",
    "Día de la Familia",
    "Actividades Culturales",
    "Cicla",
    "Capacitación Docente",
    "Auditoría Académica",
    "Simulacro de Evacuación",
    "Celebración del Mes del Niño",
  ];

  function agregarSugerencia(sugerencia: string) {
    if (infoAdicional.trim() === "") {
      infoAdicional = sugerencia;
    } else {
      infoAdicional = `${infoAdicional}\n${sugerencia}`;
    }
  }

  function formatearFecha(fecha: string): string {
    if (!fecha) return "";
    const [y, m, d] = fecha.split("-");
    const meses = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
    return `${parseInt(d)} ${meses[parseInt(m) - 1]} ${y}`;
  }

  function horaReal(h: number): string {
    const schedule = (infoHoras.horario_escolar as Record<string, {inicio: string; fin: string; bloque: string}[]>)[diaSeleccionado];
    if (!schedule) return `Hora ${h}`;
    // El array incluye bloques de Descanso/Almuerzo intercalados, por eso se busca
    // el bloque por su nombre ("Hora N") en vez de indexar por posición.
    const slot = schedule.find((s) => s.bloque === `Hora ${h}`);
    return slot ? slot.inicio : `Hora ${h}`;
  }

  type GrupoNoAsistenUnico = { grupo: string; horaInicio: number; motivos: string[] };
  const gruposNoAsistenUnicos = $derived.by<GrupoNoAsistenUnico[]>(() => {
    const mapa = new Map<string, GrupoNoAsistenUnico>();
    for (const g of gruposNoAsisten) {
      if (!mapa.has(g.grupo)) {
        mapa.set(g.grupo, { grupo: g.grupo, horaInicio: g.horaInicio, motivos: [] });
      }
    }
    for (const l of gruposNoAsistenDesdeLiberados) {
      if (!mapa.has(l.grupo)) {
        mapa.set(l.grupo, { grupo: l.grupo, horaInicio: 1, motivos: [] });
      }
      if (l.motivo) {
        const entry = mapa.get(l.grupo)!;
        if (!entry.motivos.includes(l.motivo)) {
          entry.motivos.push(l.motivo);
        }
      }
    }
    return [...mapa.values()].sort((a, b) => a.grupo.localeCompare(b.grupo));
  });

  type LiberadoUnico = { grupo: string; hora: number; motivos: string[] };
  const gruposLiberadosUnicos = $derived.by<LiberadoUnico[]>(() => {
    const mapa = new Map<string, LiberadoUnico>();
    for (const l of gruposLiberadosIntraDia) {
      const key = `${l.grupo}-${l.hora}`;
      if (!mapa.has(key)) {
        mapa.set(key, { grupo: l.grupo, hora: l.hora, motivos: [] });
      }
      if (l.motivo && !mapa.get(key)!.motivos.includes(l.motivo)) {
        mapa.get(key)!.motivos.push(l.motivo);
      }
    }
    for (const l of gruposLiberadosDesdeLiberados) {
      const key = `${l.grupo}-${l.hora_liberada}`;
      if (!mapa.has(key)) {
        mapa.set(key, { grupo: l.grupo, hora: l.hora_liberada, motivos: [] });
      }
      if (l.motivo && !mapa.get(key)!.motivos.includes(l.motivo)) {
        mapa.get(key)!.motivos.push(l.motivo);
      }
    }
    return [...mapa.values()].sort((a, b) => a.hora - b.hora || a.grupo.localeCompare(b.grupo));
  });

  async function generarPDFBlob(): Promise<Blob> {
    const [{ default: jsPDF }, { default: autoTable }] = await Promise.all([
      import('jspdf'),
      import('jspdf-autotable')
    ]);

    const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'letter' });
    const pageWidth = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();
    const margin = 15;
    const footerMargin = 18;
    let yPos = 8;

    const checkPageBreak = (requiredSpace: number) => {
      if (yPos + requiredSpace > pageHeight - footerMargin) {
        doc.addPage();
        yPos = 8;
      }
    };

    const cleanText = (text: string): string => {
      return (text || '').replace(/[\u{1F300}-\u{1F9FF}]|[\u{2700}-\u{27BF}]|[\u{1F000}-\u{1F02F}]|[\u{1F0A0}-\u{1F0FF}]|[\u{1F100}-\u{1F64F}]|[\u{1F680}-\u{1F6FF}]|[\u{1F1E0}-\u{1F1FF}]/gu, '').trim();
    };

    const logoWidth = 20;
    const logoHeight = 20;
    const logoX = margin;
    const logoY = 8;

    try {
      doc.addImage(eieLogo, 'PNG', logoX, logoY, logoWidth, logoHeight);
    } catch (e) {
      console.error("Error al agregar el logo:", e);
    }

    yPos = logoY + 2;

    doc.setFont('helvetica', 'bold');
    doc.setFontSize(16);
    doc.setTextColor(0, 0, 0);
    doc.text('INSTITUCION EDUCATIVA', logoX + logoWidth + 4, yPos + 6, { align: 'left' });
    yPos += 6;
    doc.setFontSize(14);
    doc.text('INSTITUTO GUATICA', logoX + logoWidth + 4, yPos + 5, { align: 'left' });
    yPos += 6;
    doc.setFontSize(11);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(60, 60, 60);
    doc.text(`Reporte de Coberturas - ${formatoDia(diaSeleccionado)}`, pageWidth / 2, yPos + 4, { align: 'center' });
    yPos += 5;
    doc.text(formatearFecha(fechaSeleccionada), pageWidth / 2, yPos + 4, { align: 'center' });
    yPos += 5;
    doc.setDrawColor(180, 180, 180);
    doc.line(margin, yPos + 3, pageWidth - margin, yPos + 3);
    yPos += 6;

    if (gruposNoAsistenUnicos.length > 0) {
      doc.setFont('helvetica', 'bold');
      doc.setFontSize(10);
      doc.setTextColor(0, 0, 0);
      doc.text('GRUPOS QUE NO ASISTEN:', margin + 2, yPos + 3.5);
      yPos += 6;
      doc.setFont('helvetica', 'normal');
      doc.setFontSize(9);
      for (const g of gruposNoAsistenUnicos) {
        checkPageBreak(5);
        const texto = `Grupo ${g.grupo} (no asisten)${g.motivos.length > 0 ? ` — ${g.motivos.join(", ")}` : ""}`;
        const lineas = doc.splitTextToSize(cleanText(texto), pageWidth - margin * 2);
        for (const linea of lineas) {
          checkPageBreak(4);
          doc.text(`• ${linea}`, margin + 3, yPos);
          yPos += 4;
        }
      }
      yPos += 3;
    }

    if (gruposLiberadosUnicos.length > 0) {
      doc.setFont('helvetica', 'bold');
      doc.setFontSize(10);
      doc.setTextColor(0, 0, 0);
      doc.text('GRUPOS LIBERADOS DURANTE LA JORNADA:', margin + 2, yPos + 3.5);
      yPos += 6;

      autoTable(doc, {
        startY: yPos,
        margin: { left: margin, right: margin },
        head: [['Grupo', 'Libre desde', 'Motivo']],
        body: gruposLiberadosUnicos.map(l => [
          l.grupo,
          `Hora ${l.hora} (${horaReal(l.hora)})`,
          l.motivos.join(", ")
        ]),
        styles: { fontSize: 9, cellPadding: 2 },
        headStyles: { fillColor: [243, 244, 246], textColor: [0, 0, 0], fontStyle: 'bold' },
        alternateRowStyles: { fillColor: [249, 250, 251] },
        didParseCell: (data) => {
          if (data.section === 'body' && data.column.index === 2) {
            data.cell.styles.fontStyle = 'italic';
            data.cell.styles.textColor = [100, 100, 100];
          }
        }
      });
      yPos = (doc as any).lastAutoTable.finalY + 4;
    }

    doc.setFont('helvetica', 'bold');
    doc.setFontSize(10);
    doc.setTextColor(0, 0, 0);
    doc.text('COBERTURAS DEL DÍA:', margin + 2, yPos + 3.5);
    yPos += 6;

    autoTable(doc, {
      startY: yPos,
      margin: { left: margin, right: margin },
      head: [['Hora', 'Ausente', 'Cubre', 'Grupo']],
      body: coberturas.map(cov => [
        formatoHora(cov.hora),
        cov.docenteAusente,
        (cov.docenteCubre || 'Por asignar').replace(/@$/, ''),
        cov.grupoAusente || cov.grupoACubrir || '-'
      ]),
      styles: { fontSize: 9, cellPadding: 2 },
      headStyles: { fillColor: [243, 244, 246], textColor: [0, 0, 0], fontStyle: 'bold' },
      alternateRowStyles: { fillColor: [249, 250, 251] },
      didParseCell: (data) => {
        if (data.section === 'body') {
          if (data.column.index === 2 || data.column.index === 3) {
            data.cell.styles.fontStyle = 'bold';
          }
          if (data.column.index === 2) {
            data.cell.styles.textColor = [2, 132, 63];
          }
        }
      }
    });
    yPos = (doc as any).lastAutoTable.finalY + 4;

    if (infoAdicional.trim()) {
      doc.setFont('helvetica', 'bold');
      doc.setFontSize(10);
      doc.setTextColor(0, 0, 0);
      doc.text('INFORMACIÓN ADICIONAL:', margin + 2, yPos + 3.5);
      yPos += 6;
      doc.setFont('helvetica', 'normal');
      doc.setFontSize(9);
      doc.setTextColor(12, 74, 110);
      const lineasInfo = doc.splitTextToSize(cleanText(infoAdicional), pageWidth - margin * 2);
      for (const linea of lineasInfo) {
        checkPageBreak(4);
        doc.text(linea, margin + 2, yPos);
        yPos += 4;
      }
      yPos += 3;
    }

    const totalPages = (doc as any).internal.pages.length - 1;
    for (let i = 1; i <= totalPages; i++) {
      doc.setPage(i);
      checkPageBreak(20);
      yPos = pageHeight - 25;
      doc.setDrawColor(180, 180, 180);
      doc.line(margin, yPos, pageWidth - margin, yPos);
      yPos += 5;
      doc.setFontSize(8);
      doc.setFont('helvetica', 'normal');
      doc.setTextColor(150, 150, 150);
      doc.text('Generado por Sistema de Inasistencias', pageWidth / 2, yPos, { align: 'center' });
    }

    return doc.output('blob');
  }

  async function generarYMostrarPDF() {
    if (generandoPDF) return;
    generandoPDF = true;
    try {
      if (pdfUrl) {
        URL.revokeObjectURL(pdfUrl);
        pdfUrl = null;
      }
      const blob = await generarPDFBlob();
      pdfBlob = blob;
      const url = URL.createObjectURL(blob);
      pdfUrl = url;
      mostrandoPreview = true;

      const esMovil = window.innerWidth < 768;
      if (esMovil) {
        const link = document.createElement("a");
        link.href = url;
        link.download = `Coberturas_${fechaSeleccionada}.pdf`;
        link.click();
      }
    } catch (e) {
      console.error(e);
      Swal.fire("Error", "No se pudo generar el PDF", "error");
    } finally {
      generandoPDF = false;
    }
  }

  function resumenWhatsAppTexto(): string {
    const lineas: string[] = [];
    lineas.push(`*INSTITUTO GUATICA*`);
    lineas.push(`Reporte de Coberturas — ${formatoDia(diaSeleccionado)} ${formatearFecha(fechaSeleccionada)}`);

    if (gruposNoAsistenUnicos.length > 0) {
      lineas.push("");
      lineas.push("⚠️ *GRUPOS QUE NO ASISTEN:*");
      for (const g of gruposNoAsistenUnicos) {
        lineas.push(`• ${g.grupo}${g.motivos.length > 0 ? ` — ${g.motivos.join(", ")}` : ""}`);
      }
    }

    if (gruposLiberadosUnicos.length > 0) {
      lineas.push("");
      lineas.push("🔔 *GRUPOS LIBERADOS DURANTE LA JORNADA:*");
      for (const l of gruposLiberadosUnicos) {
        lineas.push(`• ${l.grupo} — Hora ${l.hora} (${horaReal(l.hora)})${l.motivos.length > 0 ? ` — ${l.motivos.join(", ")}` : ""}`);
      }
    }

    if (coberturas.length > 0) {
      lineas.push("");
      lineas.push(`📋 *COBERTURAS (${coberturas.length}):*`);
      for (const cov of coberturas) {
        lineas.push(`• ${formatoHora(cov.hora)} — ${cov.docenteAusente} → ${(cov.docenteCubre || "Por asignar").replace(/@$/, '')} (${cov.grupoAusente || cov.grupoACubrir || "-"})`);
      }
      if (coberturas.some((c) => (c.docenteCubre || "").trimEnd().endsWith("@"))) {
        lineas.push("");
        lineas.push("_@ = cubre en su hora libre_");
      }
    }

    return lineas.join("\n");
  }

  async function compartirWhatsApp(blob: Blob) {
    const archivo = new File([blob], `Coberturas_${fechaSeleccionada}.png`, { type: "image/png" });
    const texto = resumenWhatsAppTexto();

    // Web Share API con archivos: única vía que adjunta la imagen real a WhatsApp (móvil).
    const navAny = navigator as Navigator & {
      canShare?: (data?: ShareData) => boolean;
      share?: (data: ShareData) => Promise<void>;
    };
    const puedeCompartirArchivo =
      typeof navAny.canShare === "function" &&
      typeof navAny.share === "function" &&
      navAny.canShare({ files: [archivo] });

    if (puedeCompartirArchivo) {
      try {
        await navAny.share!({
          files: [archivo],
          title: "Reporte de Coberturas",
          text: texto,
        });
      } catch (e) {
        // AbortError = usuario canceló el diálogo de compartir; no es un fallo.
        if ((e as DOMException)?.name !== "AbortError") {
          console.error(e);
        }
      }
      return;
    }

    // Fallback (escritorio sin Web Share): abrir WhatsApp con el texto. La imagen ya se
    // descargó, debe adjuntarse manualmente.
    await Swal.fire({
      icon: "info",
      title: "Compartir por WhatsApp",
      html: "Este dispositivo no permite adjuntar la imagen automáticamente. Se abrirá WhatsApp con el texto del reporte; adjunta manualmente la imagen descargada.",
      confirmButtonText: "Abrir WhatsApp",
    });
    window.open(`https://wa.me/?text=${encodeURIComponent(texto)}`, "_blank");
  }

  async function generarImagen() {
    if (generando) return;
    generando = true;
    try {
      const elemento = document.getElementById("report-card");
      if (!elemento) {
        generando = false;
        return;
      }

      const modalContent = elemento.closest('[style*="max-h"]') as HTMLElement;
      const scrollContainer = modalContent?.querySelector('[class*="overflow-y-auto"]') as HTMLElement;

      if (scrollContainer) {
        const maxScroll = scrollContainer.scrollHeight;
        scrollContainer.style.overflow = "visible";
        scrollContainer.style.maxHeight = "none";
        scrollContainer.scrollTop = maxScroll + 500;
        await new Promise((r) => setTimeout(r, 300));
      }

      elemento.style.height = "auto";
      elemento.style.maxHeight = "none";
      elemento.style.overflow = "visible";

      await new Promise((r) => setTimeout(r, 200));

      const altoCompleto = elemento.scrollHeight;
      const anchoCompleto = elemento.scrollWidth;

      elemento.style.height = `${altoCompleto + 50}px`;

      await new Promise((r) => setTimeout(r, 100));

      const canvas = await html2canvas(elemento, {
        scale: 2,
        backgroundColor: "#ffffff",
        useCORS: true,
        logging: false,
        imageTimeout: 0,
        width: Math.max(anchoCompleto, 400),
        height: altoCompleto + 50,
        windowWidth: Math.max(anchoCompleto + 50, 450),
        windowHeight: altoCompleto + 100,
      });

      elemento.style.height = "";
      elemento.style.maxHeight = "";
      elemento.style.overflow = "";

      if (scrollContainer) {
        scrollContainer.style.overflow = "";
        scrollContainer.style.maxHeight = "";
        scrollContainer.scrollTop = 0;
      }

      const blob: Blob | null = await new Promise((resolve) =>
        canvas.toBlob((b) => resolve(b), "image/png")
      );

      const link = document.createElement("a");
      link.download = `Coberturas_${fechaSeleccionada}.png`;
      link.href = blob ? URL.createObjectURL(blob) : canvas.toDataURL("image/png");
      link.click();
      if (blob && link.href.startsWith("blob:")) {
        setTimeout(() => URL.revokeObjectURL(link.href), 5000);
      }

      const res = await Swal.fire({
        icon: "success",
        title: "Imagen generada",
        text: "La imagen se descargó correctamente. ¿Compartir ahora por WhatsApp?",
        showCancelButton: true,
        confirmButtonText: "Compartir por WhatsApp",
        confirmButtonColor: "#25D366",
        cancelButtonText: "Cerrar",
      });

      if (res.isConfirmed && blob) {
        await compartirWhatsApp(blob);
      }
    } catch (e) {
      console.error(e);
      Swal.fire("Error", "No se pudo generar la imagen", "error");
    } finally {
      generando = false;
    }
  }
</script>

<div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(0,0,0,0.5);">
  <div class="rounded-xl w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col" style="background-color: rgb(var(--bg-primary)); border: 1px solid rgb(var(--border-primary));">
    <div class="flex justify-between items-center p-4 border-b shrink-0" style="border-color: rgb(var(--border-primary));">
      <h3 class="text-lg font-bold" style="color: rgb(var(--text-primary));">{modoPDF ? "Reporte PDF" : "Reporte para WhatsApp"}</h3>
      <button
        onclick={onClose}
        class="w-8 h-8 flex items-center justify-center rounded-full"
        style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary));"
      >
        ✕
      </button>
    </div>

    <div class="flex-1 overflow-y-auto p-4 min-h-0">
      <div class="mb-4">
        <label for="info-adicional" class="block text-sm font-medium mb-1" style="color: rgb(var(--text-secondary));">
          Información adicional (opcional)
        </label>
        <textarea
          id="info-adicional"
          bind:value={infoAdicional}
          placeholder="Ejemplos: Reunión de Docentes 1:30 pm, Comisión de Evaluación y Promoción, Consejo Directivo, Día de la Familia, Actividades Culturales, Cicla..."
          rows={3}
          class="w-full px-3 py-2 rounded-lg text-sm border resize-none focus-visible:outline-none focus-visible:ring-2"
          style="background-color: rgb(var(--card-bg)); color: rgb(var(--text-primary)); border-color: rgb(var(--border-primary)); --tw-ring-color: rgb(var(--accent-primary)); resize: vertical;"
        ></textarea>
        <p class="text-xs mt-1 mb-2" style="color: rgb(var(--text-muted));">
          Sugerencias (haz clic para agregar):
        </p>
        <div class="flex flex-wrap gap-2">
          {#each sugerenciasInfo as sugerencia}
            <button
              type="button"
              onclick={() => agregarSugerencia(sugerencia)}
              class="px-2 py-1 rounded-full text-xs font-medium transition-all hover:opacity-80"
              style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-secondary)); border: 1px solid rgb(var(--border-primary));"
            >
              + {sugerencia}
            </button>
          {/each}
        </div>
      </div>

      {#if modoPDF}
        {#if mostrandoPreview && pdfUrl}
          <iframe src={pdfUrl} title="Vista previa del reporte PDF" class="w-full flex-1 min-h-[600px] border rounded"></iframe>
        {:else if generandoPDF}
          <div class="flex flex-col items-center justify-center py-16 gap-4">
            <svg class="animate-spin h-10 w-10" viewBox="0 0 24 24" aria-hidden="true" style="color: rgb(var(--accent-primary));">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <p class="text-sm font-medium" style="color: rgb(var(--text-secondary));">Generando PDF...</p>
          </div>
        {:else}
          <div id="report-card" class="bg-white text-black" style="font-family: Arial, sans-serif; background-color: #ffffff; color: #000000; padding: 40px 50px; max-width: 8.5in; margin: 0 auto;">
            <div class="mb-6 pb-3" style="border-bottom: 2px solid #000; display: flex; align-items: center; gap: 12px;">
              <img src={eieLogo} alt="Escudo" style="height: 70px; width: auto; object-fit: contain; flex-shrink: 0;" />
              <div class="flex-1">
                <h1 class="text-2xl font-bold" style="color: #000; margin: 0 0 4px 0;">INSTITUCION EDUCATIVA</h1>
                <h2 class="text-xl font-bold" style="color: #000; margin: 0;">INSTITUTO GUATICA</h2>
              </div>
            </div>
            <p class="text-sm text-center" style="color: #333; margin: 0 0 4px 0;">Reporte de Coberturas - {formatoDia(diaSeleccionado)}</p>
            <p class="text-sm text-center" style="color: #333; margin: 0;">{formatearFecha(fechaSeleccionada)}</p>

            {#if gruposNoAsistenUnicos.length > 0}
              <div style="margin: 16px 0;">
                <p class="font-bold" style="margin: 0 0 8px 0; text-decoration: underline;">GRUPOS QUE NO ASISTEN:</p>
                <ul style="list-style: disc; padding-left: 24px; margin: 0;">
                  {#each gruposNoAsistenUnicos as g}
                    <li>Grupo {g.grupo} (no asisten){g.motivos.length > 0 ? ` — ${g.motivos.join(", ")}` : ""}</li>
                  {/each}
                </ul>
              </div>
            {/if}

            {#if gruposLiberadosUnicos.length > 0}
              <div style="margin: 16px 0;">
                <p class="font-bold" style="margin: 0 0 8px 0; text-decoration: underline;">GRUPOS LIBERADOS DURANTE LA JORNADA:</p>
                <table style="width: 100%; font-size: 12px; border-collapse: collapse;">
                  <thead>
                    <tr style="background-color: #f3f4f6;">
                      <th style="padding: 6px; text-align: left; font-weight: bold; border: 1px solid #000;">Grupo</th>
                      <th style="padding: 6px; text-align: left; font-weight: bold; border: 1px solid #000;">Se libera desde</th>
                      <th style="padding: 6px; text-align: left; font-weight: bold; border: 1px solid #000;">Motivo</th>
                    </tr>
                  </thead>
                  <tbody>
                    {#each gruposLiberadosUnicos as l}
                      <tr>
                        <td style="padding: 6px; border: 1px solid #000;">{l.grupo}</td>
                        <td style="padding: 6px; border: 1px solid #000;">Hora {l.hora} ({horaReal(l.hora)})</td>
                        <td style="padding: 6px; border: 1px solid #000;">{l.motivos.join(", ")}</td>
                      </tr>
                    {/each}
                  </tbody>
                </table>
                <p style="font-size: 10px; color: #666; margin: 8px 0 0 0; font-style: italic; text-decoration: underline;">
                  📌 Nota Importante: Directores de grupo avisar a padres y acudientes.
                </p>
              </div>
            {/if}

            <div style="margin: 16px 0;">
              <p class="font-bold" style="margin: 0 0 8px 0; text-decoration: underline;">COBERTURAS DEL DIA:</p>
              <table style="width: 100%; font-size: 12px; border-collapse: collapse;">
                <thead>
                  <tr style="background-color: #f3f4f6;">
                    <th style="padding: 6px; text-align: left; font-weight: bold; border: 1px solid #000;">Hora</th>
                    <th style="padding: 6px; text-align: left; font-weight: bold; border: 1px solid #000;">Ausente</th>
                    <th style="padding: 6px; text-align: left; font-weight: bold; border: 1px solid #000;">Cubre</th>
                    <th style="padding: 6px; text-align: left; font-weight: bold; border: 1px solid #000;">Grupo</th>
                  </tr>
                </thead>
                <tbody>
                  {#each coberturas as cov}
                    <tr>
                      <td style="padding: 4px; border: 1px solid #000;">{formatoHora(cov.hora)}</td>
                      <td style="padding: 4px; border: 1px solid #000;">{cov.docenteAusente}</td>
                      <td style="padding: 4px; border: 1px solid #000;">{(cov.docenteCubre || "Por asignar").replace(/@$/g, '')}</td>
                      <td style="padding: 4px; border: 1px solid #000;">{cov.grupoAusente || cov.grupoACubrir}</td>
                    </tr>
                  {/each}
                </tbody>
              </table>
            </div>

            {#if infoAdicional.trim()}
              <div style="margin: 12px 0; padding: 10px; background-color: #f0f9ff; border: 2px solid #0ea5e9; border-radius: 8px;">
                <p style="font-size: 12px; font-weight: bold; margin: 0 0 6px 0; color: #0369a1; text-decoration: underline;">INFORMACIÓN ADICIONAL:</p>
                <p style="font-size: 11px; color: #0c4a6e; margin: 0; white-space: pre-wrap; line-height: 1.5;">{infoAdicional}</p>
              </div>
            {/if}

            <div style="margin-top: 20px; padding-top: 12px; border-top: 1px solid #000;">
              <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                <div style="text-align: center;">
                  <div style="width: 200px; border-bottom: 1px solid #000; margin-bottom: 4px;">&nbsp;</div>
                  <p style="margin: 0; font-size: 11px;">Firma Director(a)</p>
                </div>
                <div style="text-align: center;">
                  <div style="width: 150px; border-bottom: 1px solid #000; margin-bottom: 4px;">&nbsp;</div>
                  <p style="margin: 0; font-size: 11px;">Fecha</p>
                </div>
              </div>
            </div>

            <div class="text-center" style="margin-top: 30px; font-size: 10px; color: #666;">
              Generado por Sistema de Inasistencias
            </div>
          </div>
        {/if}
      {:else if generando}
          <div class="flex flex-col items-center justify-center py-16 gap-4">
            <svg class="animate-spin h-10 w-10" viewBox="0 0 24 24" aria-hidden="true" style="color: rgb(var(--accent-primary));">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <p class="text-sm font-medium" style="color: rgb(var(--text-secondary));">Generando imagen...</p>
          </div>
        {:else}
        <div id="report-card" class="bg-white rounded-lg p-5 text-gray-800" style="font-family: Arial, sans-serif; background-color: #ffffff; color: #374151;">
          <div class="mb-5 pb-4 border-b flex items-center gap-4" style="border-color: #e5e7eb;">
            <img src={eieLogo} alt="Escudo" style="height: 60px; width: auto; object-fit: contain; flex-shrink: 0;" />
            <div class="flex-1 text-center">
              <h1 class="text-xl font-bold" style="color: #1e40af;">INSTITUTO GUATICA</h1>
              <p class="text-base font-medium" style="color: #6b7280;">Reporte de Coberturas - {formatoDia(diaSeleccionado)}</p>
              <p class="text-sm" style="color: #9ca3af;">{formatearFecha(fechaSeleccionada)}</p>
            </div>
            <div style="width: 60px; flex-shrink: 0;"></div>
          </div>

          {#if gruposNoAsisten.length > 0 || gruposNoAsistenDesdeLiberados.length > 0}
            <div style="margin-bottom: 20px; padding: 16px; background-color: #fef3c7; border: 3px solid #f59e0b; border-radius: 12px;">
              <p style="font-size: 16px; font-weight: bold; color: #92400e; margin: 0 0 8px 0;">
                ⚠️ AVISO A PADRES Y ACUDIENTES
              </p>
              <p style="font-size: 13px; color: #78350f; margin: 0 0 12px 0; line-height: 1.5;">
                Los siguientes grupos <strong>NO ASISTIRÁN</strong> el día {formatearFecha(fechaSeleccionada)}. Por favor no enviar los estudiantes al colegio:
              </p>
              <table style="width: 100%; font-size: 13px; line-height: 1.6; border-collapse: collapse;">
                <thead>
                  <tr style="background-color: #fde68a;">
                    <th style="padding: 8px; text-align: left; font-weight: bold; color: #92400e; border: 1px solid #f59e0b;">Grupo</th>
                    <th style="padding: 8px; text-align: left; font-weight: bold; color: #92400e; border: 1px solid #f59e0b;">No asisten desde</th>
                  </tr>
                </thead>
                <tbody>
                  {#each gruposNoAsistenUnicos as g}
                    <tr>
                      <td style="padding: 8px; font-weight: bold; color: #b45309; border: 1px solid #fde68a; background-color: #fffbeb;">{g.grupo}</td>
                      <td style="padding: 8px; color: #78350f; border: 1px solid #fde68a; background-color: #fffbeb; font-weight: bold;">No asisten{g.motivos.length > 0 ? ` — ${g.motivos.join(", ")}` : ""}</td>
                    </tr>
                  {/each}
                </tbody>
              </table>
              <p style="font-size: 11px; color: #92400e; margin: 10px 0 0 0; font-style: italic;">
                Compartir esta información con los acudientes vía WhatsApp.
              </p>
            </div>
          {/if}

          {#if gruposLiberadosIntraDia.length > 0 || gruposLiberadosDesdeLiberados.length > 0}
            <div style="margin-bottom: 20px; padding: 16px; background-color: #fef3c7; border: 3px solid #f97316; border-radius: 12px;">
              <p style="font-size: 16px; font-weight: bold; color: #9a3412; margin: 0 0 8px 0;">
                🔔 GRUPOS LIBERADOS DURANTE LA JORNADA
              </p>
              <p style="font-size: 13px; color: #7c2d12; margin: 0 0 12px 0; line-height: 1.5;">
                Estos grupos quedan <strong>sin clase</strong> desde la hora indicada:
              </p>
              <table style="width: 100%; font-size: 13px; line-height: 1.6; border-collapse: collapse;">
                <thead>
                  <tr style="background-color: #fed7aa;">
                    <th style="padding: 8px; text-align: left; font-weight: bold; color: #9a3412; border: 1px solid #f97316;">Grupo</th>
                    <th style="padding: 8px; text-align: left; font-weight: bold; color: #9a3412; border: 1px solid #f97316;">Libre desde</th>
                    <th style="padding: 8px; text-align: left; font-weight: bold; color: #9a3412; border: 1px solid #f97316;">Motivo</th>
                  </tr>
                </thead>
                <tbody>
                  {#each gruposLiberadosUnicos as l}
                    <tr>
                      <td style="padding: 8px; font-weight: bold; color: #9a3412; border: 1px solid #fed7aa; background-color: #fff7ed;">{l.grupo}</td>
                      <td style="padding: 8px; color: #7c2d12; border: 1px solid #fed7aa; background-color: #fff7ed;">Hora {l.hora} ({horaReal(l.hora)})</td>
                      <td style="padding: 8px; color: #7c2d12; border: 1px solid #fed7aa; background-color: #fff7ed; font-style: italic;">{l.motivos.join(", ")}</td>
                    </tr>
                  {/each}
                </tbody>
              </table>
              <p style="font-size: 11px; color: #9a3412; margin: 10px 0 0 0; font-style: italic;">
                📌 Nota Importante: Directores de grupo avisar a padres y acudientes.
              </p>
            </div>
          {/if}

          <div style="margin-bottom: 20px;">
            <p style="font-size: 15px; font-weight: 600; margin: 0 0 12px 0; color: #374151;">
              Coberturas asignadas ({coberturas.length})
            </p>
            <table style="width: 100%; font-size: 13px; line-height: 1.6; border-collapse: collapse;">
              <thead>
                <tr style="background-color: #f3f4f6;">
                  <th style="padding: 10px; text-align: left; font-weight: bold; color: #374151;">Hora</th>
                  <th style="padding: 10px; text-align: left; font-weight: bold; color: #374151;">Ausente</th>
                  <th style="padding: 10px; text-align: left; font-weight: bold; color: #374151;">Cubre</th>
                </tr>
              </thead>
              <tbody>
                {#each coberturas as cov}
                  <tr style="border-bottom: 1px solid #f3f4f6;">
                    <td style="padding: 10px; font-weight: 600; color: #1e40af;">{formatoHora(cov.hora)}</td>
                    <td style="padding: 10px; color: #6b7280;">
                      <div style="font-weight: 500;">{cov.docenteAusente}</div>
                      <div style="font-size: 11px; color: #9ca3af;">Grupo {cov.grupoAusente || cov.grupoACubrir}</div>
                    </td>
                    <td style="padding: 10px; font-weight: 600; color: #059669;">{(cov.docenteCubre || "Por asignar").replace(/@$/g, '')}</td>
                  </tr>
                {/each}
              </tbody>
            </table>
          </div>

          {#if docentesAusentes.length > 0 || gruposAusentes.length === 0}
            <div style="padding-top: 16px; border-top: 1px solid #e5e7eb;">
              <p style="font-size: 15px; font-weight: bold; margin: 0 0 12px 0; color: #374151;">RESUMEN DE AUSENCIAS</p>
              <div style="font-size: 13px; color: #6b7280;">
                {#if docentesAusentes.length > 0}
                  <p style="margin: 0 0 8px 0;"><strong>Docentes ausentes:</strong></p>
                  <ul style="list-style: disc; padding-left: 24px; margin: 0;">
                    {#each docentesAusentes as d}
                      <li>{d.nombre} <span style="font-weight: 600; color: #dc2626;">({d.tipo})</span></li>
                    {/each}
                  </ul>
                {/if}
                {#if gruposAusentes.length === 0 && docentesAusentes.length === 0}
                  <p style="margin: 0;">No hay ausencias registradas.</p>
                {/if}
              </div>
            </div>
          {/if}

          {#if infoAdicional.trim()}
            <div style="margin-top: 16px; padding: 16px; background-color: #f0f9ff; border: 2px solid #0ea5e9; border-radius: 12px;">
              <p style="font-size: 14px; font-weight: bold; margin: 0 0 8px 0; color: #0369a1;">INFORMACIÓN ADICIONAL:</p>
              <p style="font-size: 13px; color: #0c4a6e; margin: 0; white-space: pre-wrap; line-height: 1.6;">{infoAdicional}</p>
            </div>
          {/if}

          <div class="text-center text-sm pt-4 mt-4 border-t" style="color: #9ca3af;">
            Generado por Inasistig
          </div>
        </div>
      {/if}
    </div>

    <div class="flex gap-3 p-4 border-t shrink-0" style="border-color: rgb(var(--border-primary));">
      {#if modoPDF}
        {#if mostrandoPreview}
          <button
            onclick={() => {
              if (pdfUrl) {
                const link = document.createElement("a");
                link.href = pdfUrl;
                link.download = `Coberturas_${fechaSeleccionada}.pdf`;
                link.click();
              }
            }}
            class="flex-1 py-2 rounded-lg font-medium transition-all flex items-center justify-center gap-2"
            style="background-color: rgb(var(--accent-primary)); color: white;"
          >
            📥 Descargar PDF
          </button>
        {:else}
          <button
            onclick={generarYMostrarPDF}
            disabled={generandoPDF}
            class="flex-1 py-2 rounded-lg font-medium transition-all flex items-center justify-center gap-2 disabled:opacity-60"
            style="background-color: rgb(var(--accent-primary)); color: white;"
            aria-busy={generandoPDF}
          >
            {#if generandoPDF}
              <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
              </svg>
              Generando PDF...
            {:else}
              📄 Generar PDF
            {/if}
          </button>
        {/if}
        <button
          onclick={() => window.print()}
          class="flex-1 py-2 rounded-lg font-medium transition-all flex items-center justify-center gap-2"
          style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border: 1px solid rgb(var(--border-primary));"
        >
          🖨️ Imprimir
        </button>
        <button
          onclick={onClose}
          class="flex-1 py-2 rounded-lg font-medium transition-all"
          style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border: 1px solid rgb(var(--border-primary));"
        >
          Cerrar
        </button>
      {:else}
        <button
          onclick={generarImagen}
          disabled={generando}
          class="flex-1 py-2 rounded-lg font-medium transition-all flex items-center justify-center gap-2 disabled:opacity-60"
          style="background-color: rgb(var(--accent-primary)); color: white;"
          aria-busy={generando}
        >
          {#if generando}
            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" aria-hidden="true">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Generando imagen...
          {:else}
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Descargar Imagen
          {/if}
        </button>
        <button
          onclick={onClose}
          class="flex-1 py-2 rounded-lg font-medium transition-all"
          style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border: 1px solid rgb(var(--border-primary));"
        >
          Cerrar
        </button>
      {/if}
    </div>

    <style>
      @media print {
        #report-card {
          padding: 20px !important;
          max-width: 100% !important;
          font-size: 11px !important;
        }
        #report-card table {
          font-size: 10px !important;
        }
        #report-card th, #report-card td {
          padding: 4px !important;
        }
        #report-card h1 { font-size: 16px !important; }
        #report-card h2 { font-size: 14px !important; }
        #report-card p { margin: 4px 0 !important; }
        #report-card .text-sm { font-size: 10px !important; }
      }
    </style>
  </div>
</div>