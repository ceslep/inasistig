import { loadPdfLibraries } from './utils'
import type { ActaReunion } from './types/actaArea'
import { ROLES, ESTADOS_ACUERDO, VOTOS } from './types/actaArea'
import eieLogo from '../assets/eie.png'

const LEGAL_TEXT =
  'El presente acta se ajusta a lo dispuesto en la Ley 115 de 1994 y el Decreto 1860 de 1994.'

function rolLabel(value: string) {
  return ROLES.find((r) => r.value === value)?.label ?? value
}
function estadoLabel(value: string) {
  return ESTADOS_ACUERDO.find((e) => e.value === value)?.label ?? value
}
function votoLabel(value: string) {
  return VOTOS.find((v) => v.value === value)?.label ?? value
}

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

export async function generateActaPdf(acta: ActaReunion): Promise<Blob> {
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
    } catch {
      // ignore logo failure
    }
  }

  doc.setFont('helvetica', 'bold')
  doc.setFontSize(13)
  doc.text(acta.institucion || 'Institución Educativa', pageWidth / 2, y + 6, {
    align: 'center',
  })
  doc.setFontSize(11)
  doc.text('ACTA DE REUNIÓN DE ÁREA', pageWidth / 2, y + 13, { align: 'center' })
  doc.setFont('helvetica', 'normal')
  doc.setFontSize(9)
  doc.text(`Área: ${acta.areaAcademica}`, pageWidth / 2, y + 19, { align: 'center' })
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
    ['Hora inicio', acta.horaInicio],
    ['Hora fin', acta.horaFin || '—'],
    ['Lugar', acta.lugar || '—'],
    ['Asignaturas', acta.asignaturas.join(', ') || '—'],
    ['Grados', acta.grados.join(', ') || '—'],
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

  doc.setFont('helvetica', 'bold')
  doc.text('2. Participantes', margin, y)
  y += 2
  autoTable(doc, {
    startY: y + 2,
    head: [['Nombre', 'Rol']],
    body: acta.participantes.map((p) => [p.nombre, rolLabel(p.rol)]),
    styles: { fontSize: 9 },
    headStyles: { fillColor: [99, 102, 241] },
    margin: { left: margin, right: margin },
  })
  y = (doc as unknown as { lastAutoTable: { finalY: number } }).lastAutoTable.finalY + 6

  doc.setFont('helvetica', 'bold')
  doc.text('3. Orden del día', margin, y)
  autoTable(doc, {
    startY: y + 2,
    head: [['#', 'Tema', 'Responsable', 'Tiempo (min)']],
    body: acta.ordenDia.map((o, i) => [
      String(i + 1),
      o.descripcion,
      o.responsable,
      String(o.tiempoMin || ''),
    ]),
    styles: { fontSize: 9 },
    headStyles: { fillColor: [99, 102, 241] },
    margin: { left: margin, right: margin },
  })
  y = (doc as unknown as { lastAutoTable: { finalY: number } }).lastAutoTable.finalY + 6

  if (acta.desarrollo.some((d) => d.discusion || d.decisiones)) {
    doc.setFont('helvetica', 'bold')
    doc.text('4. Desarrollo de la reunión', margin, y)
    autoTable(doc, {
      startY: y + 2,
      head: [['Tema', 'Discusión', 'Decisiones', 'Votación']],
      body: acta.desarrollo.map((d) => [
        acta.ordenDia[d.temaIndex]?.descripcion || `Tema ${d.temaIndex + 1}`,
        d.discusion || '—',
        d.decisiones || '—',
        votoLabel(d.votacion),
      ]),
      styles: { fontSize: 8, cellWidth: 'wrap' },
      headStyles: { fillColor: [99, 102, 241] },
      columnStyles: {
        0: { cellWidth: 30 },
        1: { cellWidth: 60 },
        2: { cellWidth: 60 },
        3: { cellWidth: 25 },
      },
      margin: { left: margin, right: margin },
    })
    y = (doc as unknown as { lastAutoTable: { finalY: number } }).lastAutoTable.finalY + 6
  }

  doc.setFont('helvetica', 'bold')
  doc.text('5. Acuerdos y compromisos', margin, y)
  autoTable(doc, {
    startY: y + 2,
    head: [['Actividad', 'Responsable', 'Fecha límite', 'Estado']],
    body: acta.acuerdos.map((a) => [
      a.actividad,
      a.responsable,
      a.fechaLimite || '—',
      estadoLabel(a.estado),
    ]),
    styles: { fontSize: 9 },
    headStyles: { fillColor: [99, 102, 241] },
    margin: { left: margin, right: margin },
  })
  y = (doc as unknown as { lastAutoTable: { finalY: number } }).lastAutoTable.finalY + 6

  if (acta.proxima.fecha || acta.proxima.hora || acta.proxima.lugar) {
    doc.setFont('helvetica', 'bold')
    doc.text('6. Próxima reunión', margin, y)
    y += 5
    doc.setFont('helvetica', 'normal')
    doc.setFontSize(9)
    doc.text(
      `Fecha: ${acta.proxima.fecha || '—'}   Hora: ${acta.proxima.hora || '—'}   Lugar: ${acta.proxima.lugar || '—'}`,
      margin,
      y,
    )
    y += 6
  }

  if (y > pageHeight - 70) {
    doc.addPage()
    y = margin
  }

  doc.setFont('helvetica', 'italic')
  doc.setFontSize(9)
  const legalLines = doc.splitTextToSize(LEGAL_TEXT, pageWidth - margin * 2)
  doc.text(legalLines, margin, y)
  y += legalLines.length * 4 + 8

  const signatureWidth = (pageWidth - margin * 2 - 10) / 2
  const sigBoxHeight = 25
  const sigStartY = y

  const drawSignatureBox = (
    label: string,
    name: string,
    dataUrl: string | undefined,
    x: number,
  ) => {
    if (dataUrl) {
      try {
        doc.addImage(dataUrl, 'PNG', x + 5, sigStartY, signatureWidth - 10, sigBoxHeight)
      } catch {
        // ignore
      }
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

  const coordinador = acta.participantes.find((p) => p.rol === 'coordinador')
  const secretario = acta.participantes.find((p) => p.rol === 'secretario')

  drawSignatureBox(
    'Coordinador de área',
    coordinador?.nombre || '',
    acta.firmaCoordinador || undefined,
    margin,
  )
  drawSignatureBox(
    'Secretario ad hoc',
    secretario?.nombre || '',
    acta.firmaSecretario || undefined,
    margin + signatureWidth + 10,
  )

  doc.setFont('helvetica', 'normal')
  doc.setFontSize(7)
  doc.setTextColor(120)
  doc.text(
    `Generado el ${new Date().toLocaleString('es-CO')}`,
    pageWidth - margin,
    pageHeight - 8,
    { align: 'right' },
  )

  return doc.output('blob')
}

export function buildActaFileName(acta: ActaReunion): string {
  const safe = (s: string) =>
    (s || 'sin_dato').replace(/[\/\\:*?"<>|]/g, '').replace(/\s+/g, '_').slice(0, 40)
  return `Acta_${safe(acta.areaAcademica)}_${safe(acta.fecha)}.pdf`
}
