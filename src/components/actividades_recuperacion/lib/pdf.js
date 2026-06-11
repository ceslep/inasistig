import { anioLectivoLabel } from './data.js'

function formatDate(dateStr) {
  try {
    return new Date(dateStr).toLocaleDateString('es-CO', {
      year: 'numeric', month: 'long', day: 'numeric'
    })
  } catch {
    return dateStr
  }
}

function generateHeaderHTML(escudoBase64, periodoLabel) {
  return `
  <div class="header">
    <div class="header-left">
      ${escudoBase64 ? `<img src="${escudoBase64}" alt="Escudo" class="escudo">` : ''}
      <div>
        <p class="inst-name">INSTITUCIÓN EDUCATIVA OFICIAL <strong>INSTITUTO GUÁTICA</strong></p>
        <p class="inst-detail">Res. 002879 del 13/Dic/2017 · NIT 891.401.438-5 · DANE 166318000537</p>
      </div>
    </div>
    <div class="header-right">
      <p class="title-text">ACTA DE ENTREGA</p>
      <p class="title-sub">Plan de Mejoramiento · ${periodoLabel} · ${anioLectivoLabel}</p>
    </div>
  </div>
  `
}

function generateInfoTable(item, todayFormatted) {
  const fechaFormatted = formatDate(item.fecha_limite)
  return `
  <table class="info-table">
    <tr>
      <td class="info-cell"><span class="lbl">Estudiante</span><span class="val bold">${item.estudiante || ''}</span></td>
      <td class="info-cell"><span class="lbl">Grupo</span><span class="val">${item.grupo || ''}</span></td>
      <td class="info-cell"><span class="lbl">Asignatura</span><span class="val">${item.asignatura || ''}</span></td>
    </tr>
    <tr>
      <td class="info-cell"><span class="lbl">Docente</span><span class="val">${item.docente || ''}</span></td>
      <td class="info-cell"><span class="lbl">Fecha de Entrega</span><span class="val">${todayFormatted}</span></td>
      <td class="info-cell"><span class="lbl">Fecha Límite</span><span class="val bold">${fechaFormatted}</span></td>
    </tr>
  </table>
  `
}

function generatePlanBlockWithTitle(item) {
  return `
  <div class="plan-section">
    <p class="plan-title">Plan de Mejoramiento / Refuerzo Académico — ${item.asignatura || ''}</p>
    <div class="plan-content">${item.plan || ''}</div>
  </div>
  `
}

function generateStudentInfoTable(studentName, records, todayFormatted) {
  const grupo = records[0]?.grupo || ''
  const asignaturas = [...new Set(records.map(r => r.asignatura))].join(', ')
  const docentes = [...new Set(records.map(r => r.docente))].join(', ')
  const primeraFechaLimite = records[0]?.fecha_limite || ''

  return `
  <table class="info-table">
    <tr>
      <td class="info-cell">
        <span class="lbl">Estudiante</span>
        <span class="val bold">${studentName}</span>
      </td>
      <td class="info-cell">
        <span class="lbl">Grupo</span>
        <span class="val">${grupo}</span>
      </td>
      <td class="info-cell">
        <span class="lbl">Asignatura(s)</span>
        <span class="val">${asignaturas}</span>
      </td>
    </tr>
    <tr>
      <td class="info-cell">
        <span class="lbl">Docente(s)</span>
        <span class="val">${docentes}</span>
      </td>
      <td class="info-cell">
        <span class="lbl">Fecha de Entrega</span>
        <span class="val">${todayFormatted}</span>
      </td>
      <td class="info-cell">
        <span class="lbl">Fecha Límite</span>
        <span class="val bold">${formatDate(primeraFechaLimite)}</span>
      </td>
    </tr>
  </table>
  `
}

function generateFooterWithFirmas() {
  return `
  <div class="footer-zone">
    <p class="notice"><strong>Nota:</strong> Este plan debe ser desarrollado por el estudiante en el período indicado con acompañamiento de los padres. Entregar en la fecha límite.</p>
    <div class="firmas">
      <div class="firma"><div class="firma-line"></div><p class="firma-label">Firma del Docente</p></div>
      <div class="firma"><div class="firma-line"></div><p class="firma-label">Firma del Coordinador</p></div>
      <div class="firma"><div class="firma-line"></div><p class="firma-label">Firma del Acudiente</p></div>
    </div>
  </div>
  `
}

function generateSingleRecordHTML(item, escudoBase64, periodoLabel) {
  const todayFormatted = formatDate(new Date().toISOString().split('T')[0])
  return `
  <div class="page-item">
    ${generateHeaderHTML(escudoBase64, periodoLabel)}
    ${generateInfoTable(item, todayFormatted)}
    ${generatePlanBlockWithTitle(item)}
    ${generateFooterWithFirmas()}
  </div>
  `
}

function generateStudentPageHTML(studentName, records, escudoBase64, periodoLabel) {
  const todayFormatted = formatDate(new Date().toISOString().split('T')[0])

  const recordBlocks = records.map((item, i) => {
    const isLast = i === records.length - 1
    return `
    <div class="student-record${isLast ? ' last' : ''}">
      <div class="record-header">
        <span class="record-asignatura">${item.asignatura || ''}</span>
        <span class="record-meta">${item.docente || ''} · Límite: ${formatDate(item.fecha_limite)}</span>
      </div>
      <div class="plan-section">
        <div class="plan-content">${item.plan || ''}</div>
      </div>
    </div>
    `
  }).join('')

  return `
  <div class="page-item">
    ${generateHeaderHTML(escudoBase64, periodoLabel)}
    ${generateStudentInfoTable(studentName, records, todayFormatted)}
    ${recordBlocks}
    ${generateFooterWithFirmas()}
  </div>
  `
}

export function generatePDF(items, escudoBase64, periodoLabel) {
  if (!Array.isArray(items)) {
    items = [items]
  }

  const studentName = items[0]?.estudiante || ''

  const groupedByStudent = items.reduce((acc, item) => {
    const key = item.estudiante || '__unknown__'
    if (!acc[key]) acc[key] = []
    acc[key].push(item)
    return acc
  }, {})

  const studentGroups = Object.values(groupedByStudent)

  let pagesHTML = ''

  studentGroups.forEach((group, studentIndex) => {
    const isLastStudent = studentIndex === studentGroups.length - 1
    const studentName = group[0]?.estudiante || ''
    pagesHTML += generateStudentPageHTML(studentName, group, escudoBase64, periodoLabel)
    if (!isLastStudent) {
      pagesHTML += '<div class="page-break"></div>'
    }
  })

  const html = `<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Plan de Mejoramiento - ${studentName}</title>
<style>
  @page { size: letter; margin: 10mm 12mm; }
  * { margin: 0; padding: 0; box-sizing: border-box; }

  html, body {
    font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
    color: #222;
    font-size: 13px;
    line-height: 1.4;
    height: 100%;
  }

  /* Cada acta ocupa exactamente una página */
  .page-item {
    min-height: 100vh;
    height: auto;
    display: flex;
    flex-direction: column;
    overflow: visible;
  }

  .page-item.multi {
    height: auto;
    min-height: 100vh;
    display: block;
  }

  /* ---- Encabezado ---- */
  .header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 8px;
    margin-bottom: 10px;
    border-bottom: 1.5px solid #333;
    flex-shrink: 0;
  }

  .header-left {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .escudo {
    width: 40px;
    height: 40px;
    object-fit: contain;
  }

  .inst-name {
    font-size: 12px;
    color: #222;
  }

  .inst-detail {
    font-size: 10px;
    color: #666;
    margin-top: 1px;
  }

  .header-right {
    text-align: right;
  }

  .title-text {
    font-size: 15px;
    font-weight: 700;
    color: #111;
    letter-spacing: 1px;
  }

  .title-sub {
    font-size: 11px;
    color: #555;
    margin-top: 2px;
  }

  /* ---- Tabla de datos ---- */
  .info-table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #aaa;
    margin-bottom: 10px;
    flex-shrink: 0;
  }

  .info-cell {
    border: 1px solid #ccc;
    padding: 7px 11px;
    width: 33.33%;
  }

  .lbl {
    display: block;
    font-size: 10.5px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 1px;
  }

  .lbl-inline {
    font-size: 10.5px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }

  .val {
    display: block;
    font-size: 13px;
    color: #222;
  }

  .val.bold { font-weight: 700; }

  /* ---- Plan ---- */
  .plan-section {
    border: 1px solid #aaa;
    display: flex;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
    margin-bottom: 8px;
  }

  .plan-section:last-child {
    margin-bottom: 0;
  }

  .plan-title {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    padding: 7px 11px;
    border-bottom: 1px solid #aaa;
    color: #222;
    flex-shrink: 0;
    background: #f9f9f9;
  }

  .plan-content {
    padding: 11px 13px;
    font-size: 13px;
    line-height: 1.5;
    color: #222;
    white-space: pre-wrap;
    flex: 1;
    overflow: hidden;
  }

  /* ---- Zona inferior fija: nota + firmas ---- */
  .footer-zone {
    flex-shrink: 0;
    margin-top: auto;
    margin-bottom: 0;
  }

  .notice {
    font-size: 11px;
    color: #555;
    line-height: 1.4;
    margin-bottom: 10px;
  }

  .firmas {
    display: flex;
    justify-content: space-between;
    gap: 24px;
    padding-top: 8px;
    border-top: 1px dashed #aaa;
  }

  .firma {
    flex: 1;
    text-align: center;
  }

  .firma-line {
    border-top: 1px solid #333;
    margin-top: 30px;
    margin-bottom: 4px;
  }

  .firma-label {
    font-size: 11px;
    color: #444;
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }

  .page-break {
    page-break-after: always;
    height: 0;
  }

  /* ---- Multi-record student layout ---- */
  .student-record {
    display: flex;
    flex-direction: column;
    padding-top: 0;
    flex-shrink: 0;
  }

  .student-record.last {
    flex: 1;
    flex-shrink: 0;
  }

  .record-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 9px 13px;
    background: #f0f0f0;
    border: 1px solid #aaa;
    border-bottom: none;
  }

  .record-asignatura {
    font-size: 13px;
    font-weight: 700;
    color: #111;
  }

  .record-meta {
    font-size: 11px;
    color: #666;
    text-align: right;
  }

  .record-info-row {
    display: flex;
    gap: 20px;
    padding: 4px 10px;
    background: #fafafa;
    border: 1px solid #aaa;
    border-top: none;
    border-bottom: none;
    font-size: 9px;
    color: #333;
  }

  @media print {
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .page-item {
      height: auto;
      min-height: 100vh;
      page-break-after: auto;
      page-break-inside: avoid;
    }
    .page-break { page-break-after: always; }
    .student-record { page-break-inside: avoid; }
    .plan-section {
      flex-shrink: 0;
    }
  }
</style>
</head>
<body>
  ${pagesHTML}

  <script>
    window.onload = function() {
      window.print();
    };
  <\/script>
</body>
</html>`

  const printWindow = window.open('', '_blank')
  printWindow.document.write(html)
  printWindow.document.close()
}
