import { loadPdfLibraries } from './utils'
import type { ActaReunionPadres, ParticipanteReunion, TemaAgenda, CompromisoReunion } from './types/actaPadres'
import { LUGARES_REUNION, TIPOS_REUNION_PADRES } from './types/actaPadres'
import eieLogo from '../assets/eie.png'

const LEGAL_TEXT = 'La presente acta se ajusta a lo dispuesto en la Ley 115 de 1994 (artículos 22° y 23°), el Decreto 1286 de 2005 (artículo 23°) y el Decreto 1860 de 1994 (artículos 16 al 20), en lo correspondiente a la participación de la familia en el proceso educativo.'

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

function getLugarLabel(value: string): string {
  const found = LUGARES_REUNION.find(l => l.value === value)
  return found ? found.label : value
}

function getTipoLabel(value: string): string {
  const found = TIPOS_REUNION_PADRES.find(t => t.value === value)
  return found ? found.label : value
}

function getEstadoLabel(value: string): string {
  switch (value) {
    case 'cumplido': return 'Cumplido'
    case 'en_proceso': return 'En proceso'
    default: return 'Pendiente'
  }
}

function getRolLabel(value: string): string {
  switch (value) {
    case 'padre': return 'Padre'
    case 'madre': return 'Madre'
    case 'acudiente': return 'Acudiente'
    case 'docente': return 'Docente'
    case 'coordinador': return 'Coordinador'
    case 'director': return 'Director'
    case 'invitado': return 'Invitado'
    default: return value
  }
}

export async function generateActaPadresPdf(acta: ActaReunionPadres): Promise<Blob> {
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
  doc.text('ACTA DE REUNIÓN DE PADRES DE FAMILIA', pageWidth / 2, y + 13, { align: 'center' })
  doc.setFont('helvetica', 'normal')
  doc.setFontSize(9)
  doc.text(`Tipo: ${getTipoLabel(acta.tipo)}`, pageWidth / 2, y + 19, { align: 'center' })
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
    ['Grado/Grupo', acta.grado ? `${acta.grado}${acta.grupo ? ' - ' + acta.grupo : ''}` : '—'],
    ['Tema principal', acta.temaPrincipal],
    ['Docente que registra', acta.docenteCreador],
  ]

  autoTable(doc, {
    startY: y,
    body: cabecera,
    theme: 'plain',
    styles: { fontSize: 9, cellPadding: 1.5 },
    columnStyles: { 0: { fontStyle: 'bold', cellWidth: 45 } },
    margin: { left: margin, right: margin },
  })
  y = (doc as unknown as { lastAutoTable: { finalY: number } }).lastAutoTable.finalY + 6

  if (acta.proxFechaReunion) {
    doc.setFont('helvetica', 'bold')
    doc.setFontSize(9)
    doc.text('Próxima reunión:', margin, y)
    doc.setFont('helvetica', 'normal')
    doc.text(acta.proxFechaReunion, margin + 38, y)
    y += 5
  }

  if (y > pageHeight - 60) {
    doc.addPage()
    y = margin
  }

  doc.setFont('helvetica', 'bold')
  doc.text('2. Participantes', margin, y)
  y += 2

  const participantesBody = acta.participantes.map((p: ParticipanteReunion) => [
    p.nombre,
    getRolLabel(p.rol),
    p.hijosEnInstitucion ? String(p.hijosEnInstitucion) : '—',
    p.hijosGrados || '—',
  ])

  autoTable(doc, {
    startY: y + 2,
    head: [['Nombre', 'Rol', 'Hijos en la institución', 'Grados']],
    body: participantesBody,
    styles: { fontSize: 9 },
    headStyles: { fillColor: [20, 184, 166] },
    margin: { left: margin, right: margin },
  })
  y = (doc as unknown as { lastAutoTable: { finalY: number } }).lastAutoTable.finalY + 6

  if (acta.temasAgenda.length > 0) {
    if (y > pageHeight - 60) {
      doc.addPage()
      y = margin
    }
    doc.setFont('helvetica', 'bold')
    doc.text('3. Agenda tratada', margin, y)
    y += 2

    const temasBody = acta.temasAgenda.map((t: TemaAgenda) => [
      t.nombre,
      t.descripcion || '—',
      t.tratado ? 'Sí' : 'No',
    ])

    autoTable(doc, {
      startY: y + 2,
      head: [['Tema', 'Descripción', 'Tratado']],
      body: temasBody,
      styles: { fontSize: 9 },
      headStyles: { fillColor: [20, 184, 166] },
      columnStyles: { 2: { cellWidth: 25 } },
      margin: { left: margin, right: margin },
    })
    y = (doc as unknown as { lastAutoTable: { finalY: number } }).lastAutoTable.finalY + 6
  }

  if (acta.acuerdos) {
    if (y > pageHeight - 60) {
      doc.addPage()
      y = margin
    }
    doc.setFont('helvetica', 'bold')
    doc.text('4. Acuerdos', margin, y)
    y += 2
    doc.setFont('helvetica', 'normal')
    doc.setFontSize(9)
    const acuerdosLines = doc.splitTextToSize(acta.acuerdos, pageWidth - margin * 2)
    doc.text(acuerdosLines, margin, y)
    y += acuerdosLines.length * 4 + 4
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
      body: acta.compromisos.map((c: CompromisoReunion) => [
        c.actividad,
        c.responsable,
        c.fechaLimite || '—',
        getEstadoLabel(c.estado),
      ]),
      styles: { fontSize: 9 },
      headStyles: { fillColor: [20, 184, 166] },
      margin: { left: margin, right: margin },
    })
    y = (doc as unknown as { lastAutoTable: { finalY: number } }).lastAutoTable.finalY + 6
  }

  if (acta.observacionesGenerales) {
    if (y > pageHeight - 60) {
      doc.addPage()
      y = margin
    }
    doc.setFont('helvetica', 'bold')
    doc.text('6. Observaciones generales', margin, y)
    y += 2
    doc.setFont('helvetica', 'normal')
    doc.setFontSize(9)
    const obsLines = doc.splitTextToSize(acta.observacionesGenerales, pageWidth - margin * 2)
    doc.text(obsLines, margin, y)
    y += obsLines.length * 4 + 4
  }

  if (y > pageHeight - 60) {
    doc.addPage()
    y = margin
  }

  if (acta.fotos && acta.fotos.length > 0) {
    if (y > pageHeight - 60) {
      doc.addPage()
      y = margin
    }
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

  if (acta.fotosFirmas && acta.fotosFirmas.length > 0) {
    if (y > pageHeight - 60) {
      doc.addPage()
      y = margin
    }
    doc.setFont('helvetica', 'bold')
    doc.setFontSize(11)
    doc.text('Firmas de Padres (fotografía)', margin, y)
    y += 6

    const fotoW = 40
    const fotoH = 35
    const gapX = 5
    const gapY = 5
    const cols = 2
    const startX = margin
    const maxY = pageHeight - 40

    for (let i = 0; i < acta.fotosFirmas.length; i++) {
      if (y + fotoH > maxY) {
        doc.addPage()
        y = margin
      }
      const col = i % cols
      const row = Math.floor(i / cols)
      const x = startX + col * (fotoW + gapX)
      const photoY = y + row * (fotoH + gapY)
      try {
        doc.addImage(acta.fotosFirmas[i], 'JPEG', x, photoY, fotoW, fotoH)
      } catch { /* ignore */ }
      doc.setFont('helvetica', 'normal')
      doc.setFontSize(7)
      doc.setTextColor(100)
      doc.text(`Firma ${i + 1}`, x + fotoW / 2, photoY + fotoH + 3, { align: 'center' })
    }
    const rowsFirmas = Math.ceil(acta.fotosFirmas.length / cols)
    y += rowsFirmas * (fotoH + gapY) + 8
  }

  if (y > pageHeight - 60) {
    doc.addPage()
    y = margin
  }

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
  const representanteFirma = acta.firmas.find(f => f.rol === 'representante')

  drawSignatureBox('Docente titular', docenteFirma?.nombre || '', docenteFirma?.firma || undefined, margin)
  drawSignatureBox('Representante de padres', representanteFirma?.nombre || '', representanteFirma?.firma || undefined, margin + signatureWidth + 10)

  doc.setFont('helvetica', 'normal')
  doc.setFontSize(7)
  doc.setTextColor(120)
  doc.text(`Generado el ${new Date().toLocaleString('es-CO')}`, pageWidth - margin, pageHeight - 8, { align: 'right' })

  return doc.output('blob')
}

export function buildActaPadresFileName(acta: ActaReunionPadres): string {
  const safe = (s: string) =>
    (s || 'sin_dato').replace(/[\/\\:*?"<>|]/g, '').replace(/\s+/g, '_').slice(0, 40)
  const grado = acta.grado || 'sin_grado'
  return `Acta_Padres_${safe(grado)}_${safe(acta.fecha)}.pdf`
}