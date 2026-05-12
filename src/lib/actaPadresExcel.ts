import { loadExcelLibraries } from './utils'
import type { ActaReunionPadres, ParticipanteReunion, TemaAgenda, CompromisoReunion } from './types/actaPadres'
import { LUGARES_REUNION, TIPOS_REUNION_PADRES } from './types/actaPadres'

export async function generateActaPadresExcel(acta: ActaReunionPadres): Promise<Blob> {
  const { ExcelJS } = await loadExcelLibraries()

  const workbook = new ExcelJS.Workbook()
  const ws = workbook.addWorksheet('Acta de Padres')

  const primaryColor = '14B8A6'
  const headerBg = '1E3A5F'
  const headerText = 'FFFFFF'
  const altRowBg = 'F0FDFA'

  ws.mergeCells('A1:F1')
  const titleCell = ws.getCell('A1')
  titleCell.value = `ACTA DE REUNIÓN DE PADRES DE FAMILIA`
  titleCell.font = { bold: true, size: 14, color: { argb: headerText } }
  titleCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: headerBg } }
  titleCell.alignment = { horizontal: 'center', vertical: 'middle' }
  ws.getRow(1).height = 25

  ws.mergeCells('A2:F2')
  const subTitle = ws.getCell('A2')
  subTitle.value = acta.institucion || 'Institución Educativa Instituto Guática'
  subTitle.font = { bold: true, size: 11 }
  subTitle.alignment = { horizontal: 'center' }
  ws.getRow(2).height = 18

  ws.mergeCells('A3:F3')
  const typeCell = ws.getCell('A3')
  typeCell.value = `Tipo: ${getTipoLabel(acta.tipo)} | Fecha: ${acta.fecha}`
  typeCell.font = { size: 10, italic: true }
  typeCell.alignment = { horizontal: 'center' }
  ws.getRow(3).height = 15

  ws.addRow([])

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

  const infoData = [
    ['Fecha', acta.fecha],
    ['Hora', `${acta.horaInicio || '—'} - ${acta.horaFin || '—'}`],
    ['Lugar', getLugarLabel(acta.lugar)],
    ['Tipo', getTipoLabel(acta.tipo)],
    ['Grado/Grupo', acta.grado ? `${acta.grado}${acta.grupo ? ' - ' + acta.grupo : ''}` : '—'],
    ['Tema principal', acta.temaPrincipal],
    ['Próxima reunión', acta.proxFechaReunion || '—'],
    ['Docente creador', acta.docenteCreador],
  ]

  const infoHeader = ws.addRow(['Campo', 'Valor'])
  infoHeader.getCell(1).font = { bold: true, color: { argb: headerText } }
  infoHeader.getCell(2).font = { bold: true, color: { argb: headerText } }
  infoHeader.getCell(1).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: primaryColor } }
  infoHeader.getCell(2).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: primaryColor } }

  infoData.forEach((row, idx) => {
    const dataRow = ws.addRow(row)
    if (idx % 2 === 1) {
      dataRow.getCell(1).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: altRowBg } }
      dataRow.getCell(2).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: altRowBg } }
    }
  })

  ws.addRow([])

  const partHeader = ws.addRow(['Nombre', 'Rol', 'Hijos en la institución', 'Grados'])
  partHeader.getCell(1).font = { bold: true, color: { argb: headerText } }
  partHeader.getCell(2).font = { bold: true, color: { argb: headerText } }
  partHeader.getCell(3).font = { bold: true, color: { argb: headerText } }
  partHeader.getCell(4).font = { bold: true, color: { argb: headerText } }
  partHeader.eachCell((cell) => {
    cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: primaryColor } }
  })

  acta.participantes.forEach((p: ParticipanteReunion) => {
    ws.addRow([p.nombre, getRolLabel(p.rol), p.hijosEnInstitucion ? String(p.hijosEnInstitucion) : '—', p.hijosGrados || '—'])
  })

  if (acta.temasAgenda.length > 0) {
    ws.addRow([])
    const agendaHeader = ws.addRow(['Tema', 'Descripción', 'Tratado'])
    agendaHeader.getCell(1).font = { bold: true, color: { argb: headerText } }
    agendaHeader.getCell(2).font = { bold: true, color: { argb: headerText } }
    agendaHeader.getCell(3).font = { bold: true, color: { argb: headerText } }
    agendaHeader.eachCell((cell) => {
      cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: primaryColor } }
    })

    acta.temasAgenda.forEach((t: TemaAgenda) => {
      ws.addRow([t.nombre, t.descripcion || '—', t.tratado ? 'Sí' : 'No'])
    })
  }

  if (acta.acuerdos) {
    ws.addRow([])
    ws.addRow(['Acuerdos', acta.acuerdos])
    ws.getRow(ws.rowCount).getCell(1).font = { bold: true }
  }

  if (acta.compromisos.length > 0) {
    ws.addRow([])
    const compHeader = ws.addRow(['Actividad', 'Responsable', 'Fecha límite', 'Estado'])
    compHeader.getCell(1).font = { bold: true, color: { argb: headerText } }
    compHeader.getCell(2).font = { bold: true, color: { argb: headerText } }
    compHeader.getCell(3).font = { bold: true, color: { argb: headerText } }
    compHeader.getCell(4).font = { bold: true, color: { argb: headerText } }
    compHeader.eachCell((cell) => {
      cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: primaryColor } }
    })

    acta.compromisos.forEach((c: CompromisoReunion) => {
      ws.addRow([c.actividad, c.responsable, c.fechaLimite || '—', getEstadoLabel(c.estado)])
    })
  }

  if (acta.observacionesGenerales) {
    ws.addRow([])
    ws.addRow(['Observaciones generales', acta.observacionesGenerales])
    ws.getRow(ws.rowCount).getCell(1).font = { bold: true }
  }

  ws.addRow([])
  const legalRow = ws.addRow([
    'Base Legal: La presente acta se ajusta a lo dispuesto en la Ley 115 de 1994 (artículos 22° y 23°), el Decreto 1286 de 2005 (artículo 23°) y el Decreto 1860 de 1994 (artículos 16 al 20).',
  ])
  legalRow.getCell(1).font = { italic: true, size: 9 }
  ws.mergeCells(`A${legalRow.number}:F${legalRow.number}`)

  ws.addRow([])
  const signHeader = ws.addRow(['Firmas'])
  signHeader.getCell(1).font = { bold: true }
  signHeader.getCell(1).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: altRowBg } }
  ws.mergeCells(`A${signHeader.number}:F${signHeader.number}`)

  acta.firmas.forEach((f) => {
    ws.addRow([f.nombre, f.rol, f.firma || '—'])
  })

  ws.columns = [
    { width: 30 },
    { width: 25 },
    { width: 25 },
    { width: 25 },
    { width: 20 },
    { width: 20 },
  ]

  return workbook.xlsx.writeBuffer().then(buffer => new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' }))
}

export function buildActaPadresExcelName(acta: ActaReunionPadres): string {
  const safe = (s: string) =>
    (s || 'sin_dato').replace(/[\/\\:*?"<>|]/g, '').replace(/\s+/g, '_').slice(0, 40)
  const grado = acta.grado || 'sin_grado'
  return `Acta_Padres_${safe(grado)}_${safe(acta.fecha)}.xlsx`
}