import { loadExcelLibraries } from './utils'
import type { ActaIzada, ItemDesarrollo, Conclusion, Compromiso } from './types/actaIzada'
import { LUGARES_IZADA } from './types/actaIzada'

export async function generateActaIzadaExcel(acta: ActaIzada): Promise<Blob> {
  const { ExcelJS } = await loadExcelLibraries()

  const workbook = new ExcelJS.Workbook()
  const ws = workbook.addWorksheet('Acta de Izada')

  const primaryColor = 'F59E0B'
  const headerBg = '1E3A5F'
  const headerText = 'FFFFFF'
  const altRowBg = 'FEF3C7'

  ws.mergeCells('A1:G1')
  const titleCell = ws.getCell('A1')
  titleCell.value = `ACTA DE IZADA DE BANDERA - ${acta.grados.join(', ')} ${acta.grupos.join(', ')}`
  titleCell.font = { bold: true, size: 14, color: { argb: headerText } }
  titleCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: headerBg } }
  titleCell.alignment = { horizontal: 'center', vertical: 'middle' }
  ws.getRow(1).height = 25

  ws.mergeCells('A2:G2')
  const subTitle = ws.getCell('A2')
  subTitle.value = acta.institucion || 'Institución Educativa Instituto Guática'
  subTitle.font = { bold: true, size: 11 }
  subTitle.alignment = { horizontal: 'center' }
  ws.getRow(2).height = 18

  ws.addRow([])

  const infoData = [
    ['Fecha', acta.fecha],
    ['Hora', `${acta.horaInicio || '—'} - ${acta.horaFin || '—'}`],
    ['Lugar', getLugarLabel(acta.lugar)],
    ['Tipo', acta.tipo === 'semanal' ? 'Semanal' : acta.tipo === 'especial' ? 'Especial' : 'Patriótica'],
    ['Momento', acta.momento === 'matutino' ? 'Matutina' : 'Vespertina'],
    ['Grados', acta.grados.join(', ')],
    ['Grupos', acta.grupos.join(', ')],
    ['Área Académica', acta.areaAcademica || '—'],
    ['Tema Principal', acta.temaPrincipal],
    ['Subtema', acta.subtema || '—'],
    ['Docente Creador', acta.docenteCreador],
  ]

  function getLugarLabel(value: string): string {
    const found = LUGARES_IZADA.find(l => l.value === value)
    return found ? found.label : value
  }

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

  const partHeader = ws.addRow(['Nombre / Tipo', 'Rol', 'Cantidad'])
  partHeader.getCell(1).font = { bold: true, color: { argb: headerText } }
  partHeader.getCell(2).font = { bold: true, color: { argb: headerText } }
  partHeader.getCell(3).font = { bold: true, color: { argb: headerText } }
  partHeader.eachCell((cell) => {
    cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: primaryColor } }
  })

  acta.participantes.forEach((p) => {
    const rolLabel = p.rol === 'docente' ? 'Docente' : p.rol === 'estudiante' ? 'Estudiante' : p.rol === 'coordinador' ? 'Coordinador' : 'Director'
    const row = ws.addRow([p.nombre, rolLabel, p.cantidad ? String(p.cantidad) : '1'])
    row.alignment = { horizontal: 'left' }
  })

  ws.addRow([])

  const devHeader = ws.addRow(['#', 'Actividad', 'Descripción', 'Responsable', 'Tiempo (min)'])
  devHeader.getCell(1).font = { bold: true, color: { argb: headerText } }
  devHeader.getCell(2).font = { bold: true, color: { argb: headerText } }
  devHeader.getCell(3).font = { bold: true, color: { argb: headerText } }
  devHeader.getCell(4).font = { bold: true, color: { argb: headerText } }
  devHeader.getCell(5).font = { bold: true, color: { argb: headerText } }
  devHeader.eachCell((cell) => {
    cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: primaryColor } }
  })

  acta.desarrollo.forEach((d: ItemDesarrollo) => {
    ws.addRow([String(d.orden), d.actividad, d.descripcion, d.responsable, String(d.tiempoMin)])
  })

  if (acta.conclusiones.length > 0) {
    ws.addRow([])
    const concHeader = ws.addRow(['Conclusión', 'Cumplida'])
    concHeader.getCell(1).font = { bold: true, color: { argb: headerText } }
    concHeader.getCell(2).font = { bold: true, color: { argb: headerText } }
    concHeader.eachCell((cell) => {
      cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: primaryColor } }
    })
    acta.conclusiones.forEach((c: Conclusion) => {
      ws.addRow([c.texto, c.cumplida ? 'Sí' : 'No'])
    })
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
    acta.compromisos.forEach((c: Compromiso) => {
      const estadoLabel = c.estado === 'cumplido' ? 'Cumplido' : c.estado === 'en_curso' ? 'En curso' : 'Pendiente'
      ws.addRow([c.actividad, c.responsable, c.fechaLimite || '—', estadoLabel])
    })
  }

  ws.addRow([])
  const legalRow = ws.addRow([
    'Base Legal: La presente acta se ajusta a lo dispuesto en la Ley 115 de 1994 (artículos 5°, 22° y 23°) y el Decreto 1860 de 1994 (artículos 36 al 40).',
  ])
  legalRow.getCell(1).font = { italic: true, size: 9 }
  ws.mergeCells(`A${legalRow.number}:G${legalRow.number}`)

  ws.addRow([])
  const signHeader = ws.addRow(['Firmas'])
  signHeader.getCell(1).font = { bold: true }
  signHeader.getCell(1).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: altRowBg } }
  ws.mergeCells(`A${signHeader.number}:G${signHeader.number}`)

  acta.firmas.forEach((f) => {
    ws.addRow([f.nombre, f.rol, f.firma || '—'])
  })

  ws.columns = [
    { width: 30 },
    { width: 25 },
    { width: 40 },
    { width: 25 },
    { width: 15 },
    { width: 20 },
    { width: 15 },
  ]

  return workbook.xlsx.writeBuffer().then(buffer => new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' }))
}

export function buildActaIzadaExcelName(acta: ActaIzada): string {
  const safe = (s: string) =>
    (s || 'sin_dato').replace(/[\/\\:*?"<>|]/g, '').replace(/\s+/g, '_').slice(0, 40)
  const grados = acta.grados.join('_') || 'sin_grado'
  return `Acta_Izada_${safe(grados)}_${safe(acta.tipo)}_${safe(acta.fecha)}.xlsx`
}