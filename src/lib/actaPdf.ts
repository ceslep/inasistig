import type { CellHookData } from 'jspdf-autotable'

import { loadPdfLibraries } from './utils'
import type { ActaReunion } from './types/actaArea'
import { ROLES, ESTADOS_ACUERDO, VOTOS } from './types/actaArea'
import eieLogo from '../assets/eie.png'

const LEGAL_TEXT =
  'El presente acta se ajusta a lo dispuesto en la Ley 115 de 1994 y el Decreto 1860 de 1994, ' +
  'y constituye evidencia del trabajo colaborativo del área académica.'

// --- Paleta institucional ---
const C = {
  primary: [79, 70, 229] as [number, number, number], // indigo-600
  primaryLight: [99, 102, 241] as [number, number, number], // indigo-500
  ink: [30, 41, 59] as [number, number, number], // slate-800
  muted: [100, 116, 139] as [number, number, number], // slate-500
  line: [203, 213, 225] as [number, number, number], // slate-300
  zebra: [241, 245, 249] as [number, number, number], // slate-100
  amber: [217, 119, 6] as [number, number, number],
  amberBg: [254, 243, 199] as [number, number, number],
  sky: [2, 132, 199] as [number, number, number],
  skyBg: [224, 242, 254] as [number, number, number],
  emerald: [5, 150, 105] as [number, number, number],
  emeraldBg: [209, 250, 229] as [number, number, number],
}

function rolLabel(value: string) {
  return ROLES.find((r) => r.value === value)?.label ?? value
}
function estadoLabel(value: string) {
  return ESTADOS_ACUERDO.find((e) => e.value === value)?.label ?? value
}
function votoLabel(value: string) {
  return VOTOS.find((v) => v.value === value)?.label ?? value
}

// Estilo (fondo/texto) por estado de acuerdo, para las "insignias" de la tabla.
function estadoStyle(label: string): { fill: [number, number, number]; text: [number, number, number] } {
  const l = label.toLowerCase()
  if (l.includes('cerrado')) return { fill: C.emeraldBg, text: C.emerald }
  if (l.includes('curso')) return { fill: C.skyBg, text: C.sky }
  return { fill: C.amberBg, text: C.amber } // pendiente / desconocido
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
  const contentWidth = pageWidth - margin * 2
  const footerReserve = 16
  let y = margin

  const lastY = () =>
    (doc as unknown as { lastAutoTable: { finalY: number } }).lastAutoTable.finalY

  // Salto de página manual cuando falta espacio para bloques dibujados a mano.
  const ensureSpace = (needed: number) => {
    if (y + needed > pageHeight - footerReserve) {
      doc.addPage()
      y = margin
    }
  }

  // Título de sección: barra de color + número + texto + regla inferior.
  const sectionTitle = (label: string, color: [number, number, number] = C.primary) => {
    ensureSpace(12)
    doc.setFillColor(...color)
    doc.roundedRect(margin, y - 3.5, 2.5, 6.5, 1, 1, 'F')
    doc.setFont('helvetica', 'bold')
    doc.setFontSize(11)
    doc.setTextColor(...C.ink)
    doc.text(label, margin + 5, y + 1.5)
    y += 4
    doc.setDrawColor(...C.line)
    doc.setLineWidth(0.2)
    doc.line(margin, y, pageWidth - margin, y)
    y += 5
  }

  // ===== Encabezado (solo página 1) =====
  const logoData = await imageToDataUrl(eieLogo)

  doc.setFillColor(...C.primary)
  doc.rect(0, 0, pageWidth, 3, 'F') // franja superior

  y = margin + 2
  if (logoData) {
    try {
      doc.addImage(logoData, 'PNG', margin, y, 20, 20)
    } catch {
      // ignore logo failure
    }
  }

  doc.setFont('helvetica', 'bold')
  doc.setFontSize(15)
  doc.setTextColor(...C.ink)
  doc.text(acta.institucion || 'Institución Educativa', pageWidth / 2, y + 6, { align: 'center' })

  doc.setFontSize(12)
  doc.setTextColor(...C.primary)
  doc.text('ACTA DE REUNIÓN DE ÁREA', pageWidth / 2, y + 13, { align: 'center' })

  doc.setFont('helvetica', 'normal')
  doc.setFontSize(9)
  doc.setTextColor(...C.muted)
  doc.text(acta.areaAcademica || 'Área académica', pageWidth / 2, y + 19, { align: 'center' })

  y += 25
  doc.setDrawColor(...C.primaryLight)
  doc.setLineWidth(0.6)
  doc.line(margin, y, pageWidth - margin, y)
  y += 7

  // ===== 1. Información general =====
  sectionTitle('1. Información general')
  autoTable(doc, {
    startY: y,
    body: [
      ['Fecha', acta.fecha || '—', 'Hora', `${acta.horaInicio || '—'}${acta.horaFin ? ' – ' + acta.horaFin : ''}`],
      ['Lugar', acta.lugar || '—', 'Grados', acta.grados.join(', ') || '—'],
      ['Asignaturas', acta.asignaturas.join(', ') || '—', 'Registra', acta.docenteCreador || '—'],
    ],
    theme: 'grid',
    styles: { fontSize: 9, cellPadding: 2, lineColor: C.line, lineWidth: 0.1, textColor: C.ink },
    columnStyles: {
      0: { fontStyle: 'bold', cellWidth: 28, fillColor: C.zebra, textColor: C.muted },
      1: { cellWidth: contentWidth / 2 - 28 },
      2: { fontStyle: 'bold', cellWidth: 28, fillColor: C.zebra, textColor: C.muted },
      3: { cellWidth: contentWidth / 2 - 28 },
    },
    margin: { left: margin, right: margin, bottom: footerReserve },
  })
  y = lastY() + 7

  // ===== 2. Participantes =====
  sectionTitle('2. Participantes')
  autoTable(doc, {
    startY: y,
    head: [['#', 'Nombre', 'Rol']],
    body:
      acta.participantes.length > 0
        ? acta.participantes.map((p, i) => [String(i + 1), p.nombre || '—', rolLabel(p.rol)])
        : [['—', 'Sin participantes registrados', '—']],
    theme: 'striped',
    styles: { fontSize: 9, cellPadding: 2, textColor: C.ink },
    headStyles: { fillColor: C.primary, textColor: [255, 255, 255], fontStyle: 'bold' },
    alternateRowStyles: { fillColor: C.zebra },
    columnStyles: { 0: { cellWidth: 12, halign: 'center' }, 2: { cellWidth: 45 } },
    margin: { left: margin, right: margin, top: margin, bottom: footerReserve },
  })
  y = lastY() + 7

  // ===== 3. Orden del día =====
  sectionTitle('3. Orden del día')
  const totalMin = acta.ordenDia.reduce((s, o) => s + (o.tiempoMin || 0), 0)
  autoTable(doc, {
    startY: y,
    head: [['#', 'Tema', 'Responsable', 'Min']],
    body:
      acta.ordenDia.length > 0
        ? acta.ordenDia.map((o, i) => [
            String(i + 1),
            o.descripcion || '—',
            o.responsable || '—',
            String(o.tiempoMin || ''),
          ])
        : [['—', 'Sin temas registrados', '—', '']],
    foot: acta.ordenDia.length > 0 ? [['', 'Tiempo total estimado', '', `${totalMin} min`]] : undefined,
    theme: 'striped',
    styles: { fontSize: 9, cellPadding: 2, textColor: C.ink },
    headStyles: { fillColor: C.primary, textColor: [255, 255, 255], fontStyle: 'bold' },
    footStyles: { fillColor: C.zebra, textColor: C.muted, fontStyle: 'bold' },
    alternateRowStyles: { fillColor: C.zebra },
    columnStyles: {
      0: { cellWidth: 12, halign: 'center' },
      2: { cellWidth: 45 },
      3: { cellWidth: 16, halign: 'center' },
    },
    margin: { left: margin, right: margin, top: margin, bottom: footerReserve },
  })
  y = lastY() + 7

  // ===== 4. Desarrollo (condicional) =====
  if (acta.desarrollo.some((d) => d.discusion || d.decisiones)) {
    sectionTitle('4. Desarrollo de la reunión', C.amber)
    autoTable(doc, {
      startY: y,
      head: [['Tema', 'Discusión', 'Decisiones', 'Votación']],
      body: acta.desarrollo.map((d) => [
        acta.ordenDia[d.temaIndex]?.descripcion || `Tema ${d.temaIndex + 1}`,
        d.discusion || '—',
        d.decisiones || '—',
        votoLabel(d.votacion),
      ]),
      theme: 'grid',
      styles: { fontSize: 8, cellPadding: 2, valign: 'top', textColor: C.ink, lineColor: C.line, lineWidth: 0.1 },
      headStyles: { fillColor: C.amber, textColor: [255, 255, 255], fontStyle: 'bold' },
      columnStyles: {
        0: { cellWidth: 32, fontStyle: 'bold' },
        1: { cellWidth: 58 },
        2: { cellWidth: 58 },
        3: { cellWidth: 22, halign: 'center' },
      },
      margin: { left: margin, right: margin, top: margin, bottom: footerReserve },
    })
    y = lastY() + 7
  }

  // ===== 5. Acuerdos y compromisos =====
  sectionTitle('5. Acuerdos y compromisos', C.emerald)
  autoTable(doc, {
    startY: y,
    head: [['#', 'Actividad', 'Responsable', 'Fecha límite', 'Estado']],
    body:
      acta.acuerdos.length > 0
        ? acta.acuerdos.map((a, i) => [
            String(i + 1),
            a.actividad || '—',
            a.responsable || '—',
            a.fechaLimite || '—',
            estadoLabel(a.estado),
          ])
        : [['—', 'Sin acuerdos registrados', '—', '—', '—']],
    theme: 'striped',
    styles: { fontSize: 9, cellPadding: 2, valign: 'middle', textColor: C.ink },
    headStyles: { fillColor: C.emerald, textColor: [255, 255, 255], fontStyle: 'bold' },
    alternateRowStyles: { fillColor: C.zebra },
    columnStyles: {
      0: { cellWidth: 10, halign: 'center' },
      2: { cellWidth: 38 },
      3: { cellWidth: 26, halign: 'center' },
      4: { cellWidth: 24, halign: 'center', fontStyle: 'bold' },
    },
    // Colorea la celda de "Estado" como insignia.
    didParseCell: (data: CellHookData) => {
      if (data.section === 'body' && data.column.index === 4) {
        const label = data.cell.text.join(' ')
        if (label && label !== '—') {
          const s = estadoStyle(label)
          data.cell.styles.fillColor = s.fill
          data.cell.styles.textColor = s.text
        }
      }
    },
    margin: { left: margin, right: margin, top: margin, bottom: footerReserve },
  })
  y = lastY() + 7

  // ===== 6. Próxima reunión (condicional) =====
  if (acta.proxima.fecha || acta.proxima.hora || acta.proxima.lugar) {
    sectionTitle('6. Próxima reunión', C.sky)
    ensureSpace(16)
    doc.setFillColor(...C.skyBg)
    doc.setDrawColor(...C.sky)
    doc.setLineWidth(0.2)
    doc.roundedRect(margin, y, contentWidth, 12, 2, 2, 'FD')
    doc.setFont('helvetica', 'bold')
    doc.setFontSize(9)
    doc.setTextColor(...C.sky)
    const col = contentWidth / 3
    doc.text('FECHA', margin + 5, y + 4.5)
    doc.text('HORA', margin + col + 5, y + 4.5)
    doc.text('LUGAR', margin + col * 2 + 5, y + 4.5)
    doc.setFont('helvetica', 'normal')
    doc.setTextColor(...C.ink)
    doc.text(acta.proxima.fecha || '—', margin + 5, y + 9)
    doc.text(acta.proxima.hora || '—', margin + col + 5, y + 9)
    doc.text(doc.splitTextToSize(acta.proxima.lugar || '—', col - 8), margin + col * 2 + 5, y + 9)
    y += 18
  }

  // ===== Texto legal =====
  ensureSpace(16)
  doc.setFillColor(...C.zebra)
  const legalLines = doc.splitTextToSize(LEGAL_TEXT, contentWidth - 8)
  const legalH = legalLines.length * 3.8 + 6
  doc.roundedRect(margin, y, contentWidth, legalH, 2, 2, 'F')
  doc.setFont('helvetica', 'italic')
  doc.setFontSize(8.5)
  doc.setTextColor(...C.muted)
  doc.text(legalLines, margin + 4, y + 5)
  y += legalH + 6

  // ===== Firmas =====
  const sigBoxHeight = 22
  ensureSpace(sigBoxHeight + 16)
  const signatureWidth = (contentWidth - 12) / 2
  const sigStartY = y

  const drawSignatureBox = (
    label: string,
    name: string,
    dataUrl: string | undefined,
    x: number,
  ) => {
    if (dataUrl) {
      try {
        doc.addImage(dataUrl, 'PNG', x + 6, sigStartY, signatureWidth - 12, sigBoxHeight)
      } catch {
        // ignore
      }
    }
    doc.setDrawColor(...C.muted)
    doc.setLineWidth(0.3)
    doc.line(x + 4, sigStartY + sigBoxHeight + 2, x + signatureWidth - 4, sigStartY + sigBoxHeight + 2)
    doc.setFont('helvetica', 'bold')
    doc.setFontSize(9)
    doc.setTextColor(...C.ink)
    doc.text(label, x + signatureWidth / 2, sigStartY + sigBoxHeight + 7, { align: 'center' })
    if (name) {
      doc.setFont('helvetica', 'normal')
      doc.setFontSize(8)
      doc.setTextColor(...C.muted)
      doc.text(name, x + signatureWidth / 2, sigStartY + sigBoxHeight + 11.5, { align: 'center' })
    }
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
    margin + signatureWidth + 12,
  )

  // ===== Pie de página en todas las páginas =====
  const pageCount = doc.getNumberOfPages()
  const generado = `Generado el ${new Date().toLocaleString('es-CO')}`
  for (let p = 1; p <= pageCount; p++) {
    doc.setPage(p)
    doc.setDrawColor(...C.line)
    doc.setLineWidth(0.2)
    doc.line(margin, pageHeight - 11, pageWidth - margin, pageHeight - 11)
    doc.setFont('helvetica', 'normal')
    doc.setFontSize(7)
    doc.setTextColor(...C.muted)
    doc.text(generado, margin, pageHeight - 7)
    doc.text(`Página ${p} de ${pageCount}`, pageWidth - margin, pageHeight - 7, { align: 'right' })
  }

  return doc.output('blob')
}

export function buildActaFileName(acta: ActaReunion): string {
  const safe = (s: string) =>
    (s || 'sin_dato').replace(/[\/\\:*?"<>|]/g, '').replace(/\s+/g, '_').slice(0, 40)
  return `Acta_${safe(acta.areaAcademica)}_${safe(acta.fecha)}.pdf`
}
