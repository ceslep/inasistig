import { loadPdfLibraries } from './utils'
import type { ActaIzada, ItemDesarrollo, Conclusion, Compromiso } from './types/actaIzada'
import { LUGARES_IZADA } from './types/actaIzada'
import eieLogo from '../assets/eie.png'

const LEGAL_TEXT = 'La presente acta se ajusta a lo dispuesto en la Ley 115 de 1994 (artículos 5°, 22° y 23°) y el Decreto 1860 de 1994 (artículos 36 al 40) en lo correspondiente a ceremonies cívicas y formación patriótica.'

async function imageToDataUrl(src: string): Promise<string | null> {
  try {
    const res = await fetch(src)
    const blob = await res.blob()
    return await new Promise<string>((resolve, reject) => {
      const reader = new FileReader()
      reader.onloadend = () => resolve(reader.result as string)
      reader.onerror = reject
      reader.readAsDataURL(blob)
    })
  } catch {
    return null
  }
}

function estadoColor(estado: string): string {
  switch (estado) {
    case 'cumplido': return '✓'
    case 'en_curso': return '→'
    default: return '○'
  }
}

export async function generateActaIzadaPdf(acta: ActaIzada): Promise<Blob> {
  const { jsPDF, autoTable } = await loadPdfLibraries()
  const doc = new jsPDF({ unit: 'mm', format: 'a4' })

  const pageWidth = doc.internal.pageSize.getWidth()
  const pageHeight = doc.internal.pageSize.getHeight()
  const margin = 15
  let y = margin

  const logoData = await imageToDataUrl(eieLogo)
  if (logoData) {
    try {
      doc.addImage(logoData, 'PNG', margin, y, 18, 18)
    } catch { /* ignore */ }
  }

  doc.setFont('helvetica', 'bold')
  doc.setFontSize(13)
  doc.text(acta.institucion || 'Institución Educativa Instituto Guática', pageWidth / 2, y + 6, { align: 'center' })
  doc.setFontSize(11)
  doc.text('ACTA DE IZADA DE BANDERA', pageWidth / 2, y + 13, { align: 'center' })
  doc.setFont('helvetica', 'normal')
  doc.setFontSize(9)
  const tipoLabel = acta.tipo === 'semanal' ? 'Semanal' : acta.tipo === 'especial' ? 'Especial' : 'Patriótica'
  doc.text(`Tipo: ${tipoLabel} | Momento: ${acta.momento === 'matutino' ? 'Matutina' : 'Vespertina'}`, pageWidth / 2, y + 19, { align: 'center' })
  y += 26

  doc.setDrawColor(180)
  doc.line(margin, y, pageWidth - margin, y)
  y += 5

  doc.setFontSize(10)
  doc.setFont('helvetica', 'bold')
  doc.text('1. Información general', margin, y)
  y += 5
  doc.setFont('helvetica', 'normal')

  const cabecera: [string, string][] = [
    ['Fecha', acta.fecha],
    ['Hora inicio', acta.horaInicio || '—'],
    ['Hora fin', acta.horaFin || '—'],
    ['Lugar', getLugarLabel(acta.lugar)],
    ['Grados', acta.grados.join(', ')],
    ['Grupos', acta.grupos.join(', ')],
    ['Área', acta.areaAcademica || '—'],
    ['Tema principal', acta.temaPrincipal],
    ['Docente que registra', acta.docenteCreador],
  ]

  function getLugarLabel(value: string): string {
    const found = LUGARES_IZADA.find(l => l.value === value)
    return found ? found.label : value
  }
  autoTable(doc, {
    startY: y,
    body: cabecera,
    theme: 'plain',
    styles: { fontSize: 9, cellPadding: 1.5 },
    columnStyles: { 0: { fontStyle: 'bold', cellWidth: 45 } },
    margin: { left: margin, right: margin },
  })
  y = (doc as unknown as { lastAutoTable: { finalY: number } }).lastAutoTable.finalY + 6

  if (acta.subtema) {
    doc.setFont('helvetica', 'bold')
    doc.setFontSize(9)
    doc.text('Subtema:', margin, y)
    doc.setFont('helvetica', 'normal')
    doc.text(acta.subtema, margin + 20, y)
    y += 4
  }

  doc.setFont('helvetica', 'bold')
  doc.text('2. Participantes', margin, y)
  y += 2
  const participantesBody = acta.participantes.map(p => [
    p.nombre,
    p.rol === 'docente' ? 'Docente' : p.rol === 'estudiante' ? 'Estudiante' : p.rol === 'coordinador' ? 'Coordinador' : 'Director',
    p.cantidad ? String(p.cantidad) : '1'
  ])
  autoTable(doc, {
    startY: y + 2,
    head: [['Nombre / Grupo', 'Rol', 'Cant.']],
    body: participantesBody,
    styles: { fontSize: 9 },
    headStyles: { fillColor: [245, 158, 11] },
    margin: { left: margin, right: margin },
  })
  y = (doc as unknown as { lastAutoTable: { finalY: number } }).lastAutoTable.finalY + 6

  if (acta.desarrollo.length > 0) {
    doc.setFont('helvetica', 'bold')
    doc.text('3. Desarrollo de la izada', margin, y)
    autoTable(doc, {
      startY: y + 2,
      head: [['#', 'Actividad', 'Descripción', 'Responsable', 'Tiempo']],
      body: acta.desarrollo.map((d: ItemDesarrollo) => [
        String(d.orden),
        d.actividad,
        d.descripcion,
        d.responsable,
        `${d.tiempoMin} min`,
      ]),
      styles: { fontSize: 8 },
      headStyles: { fillColor: [245, 158, 11] },
      columnStyles: {
        0: { cellWidth: 10 },
        1: { cellWidth: 35 },
        2: { cellWidth: 60 },
        3: { cellWidth: 40 },
        4: { cellWidth: 20 },
      },
      margin: { left: margin, right: margin },
    })
    y = (doc as unknown as { lastAutoTable: { finalY: number } }).lastAutoTable.finalY + 6
  }

  if (acta.conclusiones.length > 0) {
    if (y > pageHeight - 60) {
      doc.addPage()
      y = margin
    }
    doc.setFont('helvetica', 'bold')
    doc.text('4. Conclusiones', margin, y)
    y += 2
    const conclusionesBody = acta.conclusiones.map((c: Conclusion) => [
      c.texto,
      c.cumplida ? 'Sí' : 'No',
    ])
    autoTable(doc, {
      startY: y + 2,
      head: [['Conclusión', 'Cumplida']],
      body: conclusionesBody,
      styles: { fontSize: 9 },
      headStyles: { fillColor: [245, 158, 11] },
      margin: { left: margin, right: margin },
    })
    y = (doc as unknown as { lastAutoTable: { finalY: number } }).lastAutoTable.finalY + 6
  }

  if (acta.compromisos.length > 0) {
    if (y > pageHeight - 60) {
      doc.addPage()
      y = margin
    }
    doc.setFont('helvetica', 'bold')
    doc.text('5. Compromisos', margin, y)
    autoTable(doc, {
      startY: y + 2,
      head: [['Actividad', 'Responsable', 'Fecha límite', 'Estado']],
      body: acta.compromisos.map((c: Compromiso) => [
        c.actividad,
        c.responsable,
        c.fechaLimite || '—',
        c.estado === 'cumplido' ? 'Cumplido' : c.estado === 'en_curso' ? 'En curso' : 'Pendiente',
      ]),
      styles: { fontSize: 9 },
      headStyles: { fillColor: [245, 158, 11] },
      margin: { left: margin, right: margin },
    })
    y = (doc as unknown as { lastAutoTable: { finalY: number } }).lastAutoTable.finalY + 6
  }

  if (y > pageHeight - 60) {
    doc.addPage()
    y = margin
  }

  // Evidencia Fotográfica
  if (acta.fotos && acta.fotos.length > 0) {
    doc.setFont('helvetica', 'bold')
    doc.setFontSize(11)
    doc.text('Evidencia Fotográfica', margin, y)
    y += 6

    const fotoW = 40
    const fotoH = 35
    const gapX = 5
    const gapY = 5
    const cols = 2
    const startX = margin
    const maxY = pageHeight - 40

    for (let i = 0; i < acta.fotos.length; i++) {
      if (y + fotoH > maxY) {
        doc.addPage()
        y = margin
      }
      const col = i % cols
      const row = Math.floor(i / cols)
      const x = startX + col * (fotoW + gapX)
      const photoY = y + row * (fotoH + gapY)
      try {
        doc.addImage(acta.fotos[i], 'JPEG', x, photoY, fotoW, fotoH)
      } catch { /* ignore */ }
      doc.setFont('helvetica', 'normal')
      doc.setFontSize(7)
      doc.setTextColor(100)
      doc.text(`Foto ${i + 1}`, x + fotoW / 2, photoY + fotoH + 3, { align: 'center' })
    }
    const rows = Math.ceil(acta.fotos.length / cols)
    y += rows * (fotoH + gapY) + 8
  }

  doc.setFont('helvetica', 'italic')
  doc.setFontSize(9)
  const legalLines = doc.splitTextToSize(LEGAL_TEXT, pageWidth - margin * 2)
  doc.text(legalLines, margin, y)
  y += legalLines.length * 4 + 8

  const signatureWidth = (pageWidth - margin * 2 - 10) / 2
  const sigBoxHeight = 25
  const sigStartY = y

  const drawSignatureBox = (label: string, name: string, dataUrl: string | undefined, x: number) => {
    if (dataUrl) {
      try {
        doc.addImage(dataUrl, 'PNG', x + 5, sigStartY, signatureWidth - 10, sigBoxHeight)
      } catch { /* ignore */ }
    }
    doc.setDrawColor(120)
    doc.line(x, sigStartY + sigBoxHeight + 2, x + signatureWidth, sigStartY + sigBoxHeight + 2)
    doc.setFont('helvetica', 'bold')
    doc.setFontSize(9)
    doc.text(label, x + signatureWidth / 2, sigStartY + sigBoxHeight + 7, { align: 'center' })
    doc.setFont('helvetica', 'normal')
    doc.setFontSize(8)
    if (name) doc.text(name, x + signatureWidth / 2, sigStartY + sigBoxHeight + 12, { align: 'center' })
  }

  const docenteFirma = acta.firmas.find(f => f.rol === 'docente')
  const directorFirma = acta.firmas.find(f => f.rol === 'director')

  drawSignatureBox('Docente titular', docenteFirma?.nombre || '', docenteFirma?.firma || undefined, margin)
  drawSignatureBox('Director/Coordinador', directorFirma?.nombre || '', directorFirma?.firma || undefined, margin + signatureWidth + 10)

  doc.setFont('helvetica', 'normal')
  doc.setFontSize(7)
  doc.setTextColor(120)
  doc.text(`Generado el ${new Date().toLocaleString('es-CO')}`, pageWidth - margin, pageHeight - 8, { align: 'right' })

  return doc.output('blob')
}

export function buildActaIzadaFileName(acta: ActaIzada): string {
  const safe = (s: string) =>
    (s || 'sin_dato').replace(/[\/\\:*?"<>|]/g, '').replace(/\s+/g, '_').slice(0, 40)
  const tipo = acta.tipo || 'semanal'
  const grados = acta.grados.join('_') || 'sin_grado'
  return `Acta_Izada_${safe(grados)}_${tipo}_${safe(acta.fecha)}.pdf`
}