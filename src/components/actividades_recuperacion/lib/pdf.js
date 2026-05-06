import { periodoLabel, anioLectivoLabel } from './data.js'

function formatDate(dateStr) {
  try {
    return new Date(dateStr).toLocaleDateString('es-CO', {
      year: 'numeric', month: 'long', day: 'numeric'
    })
  } catch {
    return dateStr
  }
}

function generateHeaderHTML(escudoBase64) {
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

function generateItemHTML(item, escudoBase64, includeHeader, isLast) {
  const fechaFormatted = formatDate(item.fecha_limite)
  const todayFormatted = formatDate(new Date().toISOString().split('T')[0])

  return `
  <div class="page-item">
    ${includeHeader ? generateHeaderHTML(escudoBase64) : ''}

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

    <div class="plan-section">
      <p class="plan-title">Plan de Mejoramiento / Refuerzo Académico</p>
      <div class="plan-content">${item.plan || ''}</div>
    </div>

    <div class="footer-zone">
      <p class="notice"><strong>Nota:</strong> Este plan debe ser desarrollado por el estudiante en el período indicado con acompañamiento de los padres. Entregar en la fecha límite.</p>
      <div class="firmas">
        <div class="firma"><div class="firma-line"></div><p class="firma-label">Firma del Docente</p></div>
        <div class="firma"><div class="firma-line"></div><p class="firma-label">Firma del Coordinador</p></div>
        <div class="firma"><div class="firma-line"></div><p class="firma-label">Firma del Acudiente</p></div>
      </div>
    </div>
  </div>
  ${!isLast ? '<div class="page-break"></div>' : ''}
  `
}

export function generatePDF(items, escudoBase64) {
  if (!Array.isArray(items)) {
    items = [items]
  }

  const studentName = items[0]?.estudiante || ''
  const itemsHTML = items.map((item, index) =>
    generateItemHTML(item, escudoBase64, true, index === items.length - 1)
  ).join('')

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
    font-size: 10px;
    line-height: 1.4;
    height: 100%;
  }

  /* Cada acta ocupa exactamente una página */
  .page-item {
    height: 100vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
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
    font-size: 9px;
    color: #222;
  }

  .inst-detail {
    font-size: 7px;
    color: #666;
    margin-top: 1px;
  }

  .header-right {
    text-align: right;
  }

  .title-text {
    font-size: 12px;
    font-weight: 700;
    color: #111;
    letter-spacing: 1px;
  }

  .title-sub {
    font-size: 8px;
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
    padding: 5px 8px;
    width: 33.33%;
  }

  .lbl {
    display: block;
    font-size: 7.5px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 1px;
  }

  .val {
    display: block;
    font-size: 10px;
    color: #222;
  }

  .val.bold { font-weight: 700; }

  /* ---- Plan: crece para llenar todo el espacio disponible ---- */
  .plan-section {
    border: 1px solid #aaa;
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
  }

  .plan-title {
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    padding: 5px 8px;
    border-bottom: 1px solid #aaa;
    color: #222;
    flex-shrink: 0;
  }

  .plan-content {
    padding: 8px 10px;
    font-size: 10px;
    line-height: 1.5;
    color: #222;
    white-space: pre-wrap;
    flex: 1;
    overflow: hidden;
  }

  /* ---- Zona inferior fija: nota + firmas ---- */
  .footer-zone {
    flex-shrink: 0;
    margin-top: 10px;
  }

  .notice {
    font-size: 8px;
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
    font-size: 8px;
    color: #444;
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }

  .page-break {
    page-break-after: always;
    height: 0;
  }

  @media print {
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .page-item {
      height: auto;
      min-height: 100vh;
      page-break-inside: avoid;
    }
    .plan-section {
      flex: 1;
    }
    .page-break { page-break-after: always; }
  }
</style>
</head>
<body>
  ${itemsHTML}

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
