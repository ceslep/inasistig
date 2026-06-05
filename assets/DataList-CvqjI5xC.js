import{p as Bt,s as X,b as ct,o as qt,j as k,e as Vt,$ as Wt,f as Ie,b3 as c,k as r,i as G,m as e,A as ee,t as S,r as o,g as p,N as w,y as a,B as ve,af as Yt,au as Me,h as Zt,v as O,q as W,l as vt,n as Ne,bY as Jt,x,J as pt,d as Kt}from"./svelte-DojZHhY2.js";import{d as Qt,g as Pe,c as Oe,i as Xt,p as ea}from"./App-BmgoulmC.js";import{e as ta}from"./index-HgPFlp0T.js";import{S as aa}from"./SkeletonLoader-BX3XAJDA.js";import"./pdf-wbV6RFuj.js";import"./ui-DZFw2LIZ.js";function Te(d){try{return new Date(d).toLocaleDateString("es-CO",{year:"numeric",month:"long",day:"numeric"})}catch{return d}}function xt(d,z){return`
  <div class="header">
    <div class="header-left">
      ${d?`<img src="${d}" alt="Escudo" class="escudo">`:""}
      <div>
        <p class="inst-name">INSTITUCIÓN EDUCATIVA OFICIAL <strong>INSTITUTO GUÁTICA</strong></p>
        <p class="inst-detail">Res. 002879 del 13/Dic/2017 · NIT 891.401.438-5 · DANE 166318000537</p>
      </div>
    </div>
    <div class="header-right">
      <p class="title-text">ACTA DE ENTREGA</p>
      <p class="title-sub">Plan de Mejoramiento · ${z} · ${Qt}</p>
    </div>
  </div>
  `}function sa(d,z){const E=Te(d.fecha_limite);return`
  <table class="info-table">
    <tr>
      <td class="info-cell"><span class="lbl">Estudiante</span><span class="val bold">${d.estudiante||""}</span></td>
      <td class="info-cell"><span class="lbl">Grupo</span><span class="val">${d.grupo||""}</span></td>
      <td class="info-cell"><span class="lbl">Asignatura</span><span class="val">${d.asignatura||""}</span></td>
    </tr>
    <tr>
      <td class="info-cell"><span class="lbl">Docente</span><span class="val">${d.docente||""}</span></td>
      <td class="info-cell"><span class="lbl">Fecha de Entrega</span><span class="val">${z}</span></td>
      <td class="info-cell"><span class="lbl">Fecha Límite</span><span class="val bold">${E}</span></td>
    </tr>
  </table>
  `}function ra(d){return`
  <div class="plan-section">
    <p class="plan-title">Plan de Mejoramiento / Refuerzo Académico — ${d.asignatura||""}</p>
    <div class="plan-content">${d.plan||""}</div>
  </div>
  `}function ut(){return`
  <div class="footer-zone">
    <p class="notice"><strong>Nota:</strong> Este plan debe ser desarrollado por el estudiante en el período indicado con acompañamiento de los padres. Entregar en la fecha límite.</p>
    <div class="firmas">
      <div class="firma"><div class="firma-line"></div><p class="firma-label">Firma del Docente</p></div>
      <div class="firma"><div class="firma-line"></div><p class="firma-label">Firma del Coordinador</p></div>
      <div class="firma"><div class="firma-line"></div><p class="firma-label">Firma del Acudiente</p></div>
    </div>
  </div>
  `}function la(d,z,E){const I=Te(new Date().toISOString().split("T")[0]);return`
  <div class="page-item">
    ${xt(z,E)}
    ${sa(d,I)}
    ${ra(d)}
    ${ut()}
  </div>
  `}function oa(d,z,E,I){return Te(new Date().toISOString().split("T")[0]),`
  <div class="page-item multi">
    ${z.map((b,h)=>{const F=h===0,$=h===z.length-1;return`
    <div class="student-record ${F?"first":""} ${$?"last":""}">
      ${F?xt(E,I):""}
      <div class="record-header">
        <span class="record-asignatura">${b.asignatura||""}</span>
        <span class="record-meta">${b.docente||""} · Límite: ${Te(b.fecha_limite)}</span>
      </div>
      <div class="record-info-row">
        <span><span class="lbl-inline">Estudiante:</span> <strong>${b.estudiante||""}</strong></span>
        <span><span class="lbl-inline">Grupo:</span> ${b.grupo||""}</span>
      </div>
      <div class="plan-section">
        <div class="plan-content">${b.plan||""}</div>
      </div>
      ${$?ut():""}
    </div>
    `}).join("")}
  </div>
  `}function mt(d,z,E){Array.isArray(d)||(d=[d]);const I=d[0]?.estudiante||"",pe=d.reduce((u,me)=>{const te=me.estudiante||"__unknown__";return u[te]||(u[te]=[]),u[te].push(me),u},{}),b=Object.values(pe);let h="";b.forEach((u,me)=>{const te=me===b.length-1;u.length===1?h+=la(u[0],z,E):h+=oa(I,u,z,E),te||(h+='<div class="page-break"></div>')});const F=`<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Plan de Mejoramiento - ${I}</title>
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

  .lbl-inline {
    font-size: 7.5px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }

  .val {
    display: block;
    font-size: 10px;
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
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    padding: 5px 8px;
    border-bottom: 1px solid #aaa;
    color: #222;
    flex-shrink: 0;
    background: #f9f9f9;
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

  /* ---- Multi-record student layout ---- */
  .student-record {
    display: flex;
    flex-direction: column;
    padding-top: 0;
  }

  .student-record.last {
    flex: 1;
  }

  .record-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 10px;
    background: #f0f0f0;
    border: 1px solid #aaa;
    border-bottom: none;
  }

  .record-asignatura {
    font-size: 10px;
    font-weight: 700;
    color: #111;
  }

  .record-meta {
    font-size: 8px;
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
      page-break-inside: avoid;
    }
    .page-break { page-break-after: always; }
    .plan-section {
      flex: 1;
    }
  }
</style>
</head>
<body>
  ${h}

  <script>
    window.onload = function() {
      window.print();
    };
  <\/script>
</body>
</html>`,$=window.open("","_blank");$.document.write(F),$.document.close()}var ia=x('<button class="text-xs font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 flex items-center gap-1 transition-colors svelte-m6227o"><!> Limpiar filtros</button>'),na=x('<button class="btn-danger text-xs flex items-center gap-1 svelte-m6227o"><!> </button>'),da=x('<option class="svelte-m6227o"> </option>'),ca=x('<option class="svelte-m6227o"> </option>'),va=x('<option class="svelte-m6227o"> </option>'),pa=x('<span class="filter-chip svelte-m6227o"><!> <span class="truncate max-w-[150px] svelte-m6227o"> </span> <button class="w-4 h-4 rounded-full hover:bg-primary-200 dark:hover:bg-primary-700 flex items-center justify-center transition-colors ml-0.5 svelte-m6227o"><!></button></span>'),ma=x('<div class="sticky-filter-bar svelte-m6227o"><div class="flex items-center gap-2 flex-wrap svelte-m6227o"><!> <span class="text-xs font-medium text-slate-500 dark:text-slate-400 shrink-0 svelte-m6227o">Filtros activos:</span> <!> <span class="text-xs text-slate-400 dark:text-slate-500 ml-auto shrink-0 svelte-m6227o"> </span></div></div>'),xa=x('<span class="text-[10px] font-medium text-primary-500 bg-primary-50 dark:bg-primary-900/50 px-1.5 py-0.5 rounded-md svelte-m6227o">Filtrado</span>'),ua=x('<span class="text-[10px] font-medium text-emerald-500 bg-emerald-50 dark:bg-emerald-900/50 px-1.5 py-0.5 rounded-md truncate max-w-[80px] svelte-m6227o"> </span>'),fa=x('<div class="flex items-center gap-3 svelte-m6227o"><span class="text-xs text-slate-600 dark:text-slate-300 w-28 sm:w-40 truncate font-medium svelte-m6227o"> </span> <div class="flex-1 h-6 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden relative svelte-m6227o"><div></div> <span> </span></div> <span class="text-[10px] text-slate-400 dark:text-slate-500 w-8 text-right font-medium svelte-m6227o"> </span></div>'),ga=x('<div class="card p-4 sm:p-5 stagger-item svelte-m6227o" style="animation-delay: 320ms"><div class="flex items-center gap-2 mb-3 svelte-m6227o"><!> <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider svelte-m6227o">Distribución por asignatura</h3> <span class="text-[10px] text-slate-400 dark:text-slate-500 ml-auto svelte-m6227o">Top 5</span></div> <div class="space-y-2.5 svelte-m6227o"></div></div>'),ba=x('<div class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/50 dark:to-orange-950/50 border border-amber-200/60 dark:border-amber-800/60 stagger-item svelte-m6227o" style="animation-delay: 400ms"><!> <p class="text-xs text-amber-700 dark:text-amber-300 svelte-m6227o"><span class="font-semibold svelte-m6227o">Próxima fecha límite:</span> <span class="text-amber-500 dark:text-amber-400 ml-1 svelte-m6227o"> </span></p></div>'),ha=x('<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 svelte-m6227o"><div class="stat-card group stagger-item svelte-m6227o" style="animation-delay: 0ms"><div class="flex items-center justify-between mb-3 svelte-m6227o"><div class="w-10 h-10 rounded-xl bg-primary-100 dark:bg-primary-900/50 flex items-center justify-center transition-transform group-hover:scale-110 svelte-m6227o"><!></div> <!></div> <p class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 tracking-tight svelte-m6227o"> </p> <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 svelte-m6227o">Estudiantes</p></div> <div class="stat-card group stagger-item svelte-m6227o" style="animation-delay: 80ms"><div class="flex items-center justify-between mb-3 svelte-m6227o"><div class="w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-900/50 flex items-center justify-center transition-transform group-hover:scale-110 svelte-m6227o"><!></div> <span class="text-[10px] font-medium text-slate-400 dark:text-slate-500 svelte-m6227o"> </span></div> <p class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 tracking-tight svelte-m6227o"> </p> <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 svelte-m6227o">Planes de mejoramiento</p></div> <div class="stat-card group stagger-item svelte-m6227o" style="animation-delay: 160ms"><div class="flex items-center justify-between mb-3 svelte-m6227o"><div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center transition-transform group-hover:scale-110 svelte-m6227o"><!></div> <!></div> <p class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 tracking-tight svelte-m6227o"> </p> <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 svelte-m6227o">Asignaturas</p></div> <div class="stat-card group stagger-item svelte-m6227o" style="animation-delay: 240ms"><div class="flex items-center justify-between mb-3 svelte-m6227o"><div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center transition-transform group-hover:scale-110 svelte-m6227o"><!></div> <span class="text-[10px] font-medium text-slate-400 dark:text-slate-500 svelte-m6227o"> </span></div> <p class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 tracking-tight svelte-m6227o"> </p> <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 svelte-m6227o">Docentes</p></div></div> <!> <!>',1),_a=x('<button class="mt-4 inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/30 hover:bg-primary-100 dark:hover:bg-primary-900/50 rounded-xl transition-colors svelte-m6227o"><!> Limpiar filtros</button>'),ya=x('<div class="card p-12 text-center stagger-item svelte-m6227o"><div class="mx-auto mb-6 w-48 h-48 svelte-m6227o"><svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full svelte-m6227o"><circle cx="100" cy="100" r="90" class="fill-slate-100 dark:fill-slate-800 svelte-m6227o"></circle><rect x="45" y="75" width="110" height="80" rx="8" class="fill-slate-200 dark:fill-slate-700 svelte-m6227o"></rect><path d="M45 83C45 78.5817 48.5817 75 53 75H80L90 65H137C141.418 65 145 68.5817 145 73V75H45V83Z" class="fill-slate-300 dark:fill-slate-600 svelte-m6227o"></path><rect x="40" y="85" width="120" height="70" rx="6" stroke-width="1.5"></rect><circle cx="100" cy="115" r="18" class="stroke-primary-400 dark:stroke-primary-500 svelte-m6227o" stroke-width="3" fill="none"></circle><line x1="113" y1="128" x2="125" y2="140" class="stroke-primary-400 dark:stroke-primary-500 svelte-m6227o" stroke-width="3" stroke-linecap="round"></line><text x="95" y="122" class="fill-primary-400 dark:fill-primary-500 svelte-m6227o" font-size="20" font-weight="bold" font-family="sans-serif">?</text><circle cx="50" cy="55" r="3" class="fill-primary-200 dark:fill-primary-700 svelte-m6227o" opacity="0.6"></circle><circle cx="160" cy="60" r="4" class="fill-violet-200 dark:fill-violet-700 svelte-m6227o" opacity="0.6"></circle><circle cx="35" cy="150" r="3" class="fill-emerald-200 dark:fill-emerald-700 svelte-m6227o" opacity="0.6"></circle><circle cx="170" cy="145" r="5" class="fill-amber-200 dark:fill-amber-700 svelte-m6227o" opacity="0.5"></circle></svg></div> <p class="text-slate-600 dark:text-slate-300 font-semibold text-lg mb-1 svelte-m6227o">No se encontraron registros</p> <p class="text-sm text-slate-400 dark:text-slate-500 max-w-sm mx-auto svelte-m6227o"><!></p> <!></div>'),ka=x('<div class="alert-badge svelte-m6227o"><span class="alert-ring svelte-m6227o"></span> <span class="alert-ring alert-ring-2 svelte-m6227o"></span> <span class="alert-icon-wrap svelte-m6227o"><!></span> <span class="alert-label svelte-m6227o"> </span></div>'),wa=x('<span><span></span> <span class="truncate max-w-[120px] svelte-m6227o"> </span></span>'),$a=x(`<button><!> <div class="flex items-start gap-3 mb-3 svelte-m6227o"><div> </div> <div class="flex-1 min-w-0 svelte-m6227o"><h3 class="text-[13px] font-bold text-slate-800 dark:text-slate-100 leading-tight line-clamp-2 svelte-m6227o"> </h3> <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5 flex items-center gap-1 svelte-m6227o"><!> </p></div></div> <div class="flex flex-wrap gap-1.5 mb-3 svelte-m6227o"></div> <div class="mt-auto flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-700 svelte-m6227o"><div class="flex items-center gap-1.5 svelte-m6227o"><!> <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 svelte-m6227o"> </span></div> <span class="text-[10px] text-slate-400 dark:text-slate-500 flex items-center gap-1 svelte-m6227o"><!> </span></div> <div class="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none flex items-center justify-center bg-slate-900/5 dark:bg-slate-100/5 svelte-m6227o"><span class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm text-xs font-semibold text-primary-600 dark:text-primary-400 px-3 py-1.5 rounded-full shadow-lg svelte-m6227o"><!> Ver
              detalle</span></div></button>`),ja=x('<div class="flex items-center justify-between px-1 svelte-m6227o"><p class="text-xs text-slate-400 dark:text-slate-500 svelte-m6227o">Mostrando <span class="font-semibold text-slate-600 dark:text-slate-300 svelte-m6227o"> </span> <span class="font-semibold text-slate-600 dark:text-slate-300 svelte-m6227o"> </span> planes</p></div> <div class="student-grid svelte-m6227o"></div>',1),Da=x('<div class="rounded-xl border border-slate-100 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 p-4 space-y-3 svelte-m6227o"><div class="flex flex-wrap items-center gap-x-3 gap-y-1.5 svelte-m6227o"><span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 svelte-m6227o"><!> </span> <span class="inline-flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 svelte-m6227o"><!> </span> <span><span></span> </span></div> <div class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line svelte-m6227o"> </div> <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-slate-400 dark:text-slate-500 svelte-m6227o"><span class="flex items-center gap-1 svelte-m6227o"><!> Límite: <span class="font-medium text-slate-500 dark:text-slate-400 svelte-m6227o"> </span></span> <span class="flex items-center gap-1 svelte-m6227o"><!> </span></div></div>'),Pa=x('<div class="svelte-m6227o"><div class="modal-backdrop svelte-m6227o" role="dialog" aria-modal="true" aria-label="Detalle del estudiante" tabindex="-1"><div class="modal-content svelte-m6227o"><div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700 shrink-0 svelte-m6227o"><div class="flex items-center gap-3 svelte-m6227o"><div> </div> <div class="svelte-m6227o"><h3 class="text-base font-bold text-slate-800 dark:text-slate-100 svelte-m6227o"> </h3> <p class="text-xs text-slate-400 dark:text-slate-500 flex items-center gap-1.5 svelte-m6227o"><!> <span class="text-slate-200 dark:text-slate-600 svelte-m6227o">|</span> </p></div></div> <button class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition-colors text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 svelte-m6227o" aria-label="Cerrar"><!></button></div> <div class="p-5 space-y-3 overflow-y-auto modal-body svelte-m6227o"></div> <div class="flex justify-end gap-2 p-5 border-t border-slate-100 dark:border-slate-700 shrink-0 svelte-m6227o"><button class="px-4 py-2 text-xs font-medium text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors svelte-m6227o">Cerrar</button> <button class="btn-danger svelte-m6227o"><!> Generar PDF</button></div></div></div></div>'),Sa=x('<div class="space-y-5 svelte-m6227o"><div class="card p-5 sm:p-6 svelte-m6227o"><div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5 svelte-m6227o"><div class="flex items-center gap-2 svelte-m6227o"><div class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900/50 flex items-center justify-center svelte-m6227o"><!></div> <h2 class="text-base font-bold text-slate-800 dark:text-slate-100 svelte-m6227o">Registros</h2></div> <div class="flex items-center gap-2 svelte-m6227o"><!> <!></div></div> <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 svelte-m6227o"><div class="relative svelte-m6227o"><!> <input type="text" placeholder="Buscar en todos los campos..." class="field-input pl-10 svelte-m6227o"/></div> <select class="field-input svelte-m6227o"></select> <select class="field-input svelte-m6227o"><option class="svelte-m6227o">Todos los grupos</option><!></select> <select class="field-input svelte-m6227o"><option class="svelte-m6227o">Todos los docentes</option><!></select></div></div> <!> <!> <!></div> <!>',1);function za(d,z){Bt(z,!0);let E=X(ct([])),I=X(!0),pe=X(""),b=X(""),h=X(""),F=X(""),$=X(ct(Pe())),u=X(null),me=w(()=>[...new Set(e(E).map(t=>t.grupo))].sort((t,s)=>Number(t)-Number(s))),te=w(()=>[...new Set(e(E).map(t=>t.docente))].sort()),He=w(()=>Oe(e($))),j=w(()=>e(E).filter(t=>{const s=!e(b)||Object.values(t).some(_=>String(_).toLowerCase().includes(e(b).toLowerCase())),l=!e(h)||t.grupo===e(h),i=!e(F)||t.docente===e(F),m=!e($)||t.periodo===e($);return s&&l&&i&&m})),ae=w(()=>Object.values(e(j).reduce((t,s)=>(t[s.estudiante]||(t[s.estudiante]={estudiante:s.estudiante,grupo:s.grupo,records:[]}),t[s.estudiante].records.push(s),t),{})).sort((t,s)=>t.estudiante.localeCompare(s.estudiante))),M=w(()=>({totalEstudiantes:e(ae).length,totalPlanes:e(j).length,totalAsignaturas:[...new Set(e(j).map(t=>t.asignatura))].length,totalDocentes:[...new Set(e(j).map(t=>t.docente))].length,totalGrupos:[...new Set(e(j).map(t=>t.grupo))].length,promedioPlanesPorEstudiante:e(ae).length>0?(e(j).length/e(ae).length).toFixed(1):"0",asignaturaTop:(()=>{const t={};e(j).forEach(l=>{t[l.asignatura]=(t[l.asignatura]||0)+1});const s=Object.entries(t).sort((l,i)=>i[1]-l[1]);return s[0]?{nombre:s[0][0],cantidad:s[0][1]}:null})(),fechaProxima:(()=>{const t=new Date;return t.setHours(0,0,0,0),e(j).map(l=>new Date(l.fecha_limite)).filter(l=>l>=t).sort((l,i)=>l-i)[0]||null})()})),Re=w(()=>()=>{const t={};e(j).forEach(l=>{t[l.asignatura]=(t[l.asignatura]||0)+1});const s=e(j).length||1;return Object.entries(t).sort((l,i)=>i[1]-l[1]).slice(0,5).map(([l,i])=>({nombre:l,cantidad:i,porcentaje:Math.round(i/s*100)}))});function Ue(){k(b,""),k(h,""),k(F,""),k($,Pe(),!0)}let ke=w(()=>e(b)||e(h)||e(F)||e($)!==Pe()),ft=w(()=>()=>{const t=[];return e(b)&&t.push({key:"search",label:`"${e(b)}"`,icon:"mdi:magnify",clear:()=>k(b,"")}),e(h)&&t.push({key:"grupo",label:`Grupo ${e(h)}`,icon:"mdi:google-classroom",clear:()=>k(h,"")}),e(F)&&t.push({key:"docente",label:e(F),icon:"mdi:account-tie",clear:()=>k(F,"")}),e($)!==Pe()&&t.push({key:"periodo",label:Oe(e($)),icon:"mdi:calendar",clear:()=>k($,Pe(),!0)}),t});qt(async()=>{await Promise.allSettled([(async()=>{try{const t=await fetch("https://app.iedeoccidente.com/gs/getgsartirec.php");k(E,await t.json(),!0)}catch(t){console.error("Error fetching data:",t)}finally{k(I,!1)}})(),(async()=>{try{const s=await(await fetch(ta)).blob(),l=new FileReader;l.onloadend=()=>{k(pe,l.result,!0)},l.readAsDataURL(s)}catch{}})()])});function Be(t){try{return new Date(t).toLocaleDateString("es-CO",{year:"numeric",month:"long",day:"numeric"})}catch{return t}}function qe(t){try{return new Date(t).toLocaleDateString("es-CO",{month:"short",day:"numeric"})}catch{return t}}function Ce(t){const s=new Date;s.setHours(0,0,0,0);const l=new Date(t);return l.setHours(0,0,0,0),Math.ceil((l-s)/(1e3*60*60*24))}function Ve(t){const s=Ce(t);return s<0?{label:"Vencido",color:"text-rose-600 bg-rose-50 dark:bg-rose-950 border-rose-200 dark:border-rose-800",icon:"mdi:alert-circle",dot:"bg-rose-500"}:s<=3?{label:`${s}d restante${s!==1?"s":""}`,color:"text-amber-600 bg-amber-50 dark:bg-amber-950 border-amber-200 dark:border-amber-800",icon:"mdi:clock-alert",dot:"bg-amber-500"}:s<=7?{label:`${s} días`,color:"text-blue-600 bg-blue-50 dark:bg-blue-950 border-blue-200 dark:border-blue-800",icon:"mdi:calendar-clock",dot:"bg-blue-500"}:{label:`${s} días`,color:"text-emerald-600 bg-emerald-50 dark:bg-emerald-950 border-emerald-200 dark:border-emerald-800",icon:"mdi:calendar-check",dot:"bg-emerald-500"}}function gt(t){k(u,t,!0)}function we(){k(u,null)}function bt(t){return document.body.appendChild(t),{destroy(){t.parentNode&&t.parentNode.removeChild(t)}}}function ht(t){t.key==="Escape"&&e(u)&&we()}const We=["from-primary-400 to-primary-600","from-violet-400 to-violet-600","from-emerald-400 to-emerald-600","from-amber-400 to-amber-600","from-rose-400 to-rose-600","from-cyan-400 to-cyan-600","from-fuchsia-400 to-fuchsia-600","from-teal-400 to-teal-600"];function Ye(t){let s=0;for(let l=0;l<t.length;l++)s=t.charCodeAt(l)+((s<<5)-s);return We[Math.abs(s)%We.length]}function Ze(t){return t.split(" ").slice(0,2).map(s=>s[0]).join("").toUpperCase()}function _t(t){const s=e(j).filter(l=>l.estudiante===t.estudiante);mt(s,e(pe),e(He))}function yt(){const t=[...e(j)].sort((s,l)=>s.estudiante.localeCompare(l.estudiante));mt(t,e(pe),e(He))}const Je=["bg-primary-500","bg-violet-500","bg-emerald-500","bg-amber-500","bg-rose-500"];var Ke=Sa();Vt("keydown",Wt,ht);var Qe=Ie(Ke),Xe=a(Qe),et=a(Xe),tt=a(et),kt=a(tt),wt=a(kt);c(wt,{icon:"mdi:format-list-bulleted",class:"text-primary-600 dark:text-primary-400 text-lg"});var $t=r(tt,2),at=a($t);{var jt=t=>{var s=ia(),l=a(s);c(l,{icon:"mdi:filter-remove",class:"text-sm"}),O("click",s,Ue),p(t,s)};G(at,t=>{e(ke)&&t(jt)})}var Dt=r(at,2);{var Pt=t=>{var s=na(),l=a(s);c(l,{icon:"mdi:file-pdf-box",class:"text-sm"});var i=r(l);S(()=>o(i,` PDF Grupo ${e(h)??""}`)),O("click",s,yt),p(t,s)};G(Dt,t=>{e(h)&&e(j).length>0&&t(Pt)})}var St=r(et,2),st=a(St),rt=a(st);c(rt,{icon:"mdi:magnify",class:"absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base"});var Tt=r(rt,2),Ee=r(st,2);ee(Ee,21,()=>ea,ve,(t,s)=>{const l=w(()=>Xt(e(s).nombre));var i=da(),m=a(i),_={};S(A=>{i.disabled=!e(l),o(m,`${A??""} ${e(l)?"":"(Fuera de rango)"}`),_!==(_=e(s).nombre)&&(i.value=(i.__value=e(s).nombre)??"")},[()=>Oe(e(s).nombre)]),p(t,i)});var Fe=r(Ee,2),Ae=a(Fe);Ae.value=Ae.__value="";var Ct=r(Ae);ee(Ct,17,()=>e(me),ve,(t,s)=>{var l=ca(),i=a(l),m={};S(()=>{o(i,`Grupo ${e(s)??""}`),m!==(m=e(s))&&(l.value=(l.__value=e(s))??"")}),p(t,l)});var lt=r(Fe,2),Le=a(lt);Le.value=Le.__value="";var Et=r(Le);ee(Et,17,()=>e(te),ve,(t,s)=>{var l=va(),i=a(l),m={};S(()=>{o(i,e(s)),m!==(m=e(s))&&(l.value=(l.__value=e(s))??"")}),p(t,l)});var ot=r(Xe,2);{var Ft=t=>{var s=ma(),l=a(s),i=a(l);c(i,{icon:"mdi:filter-variant",class:"text-primary-500 dark:text-primary-400 text-sm shrink-0"});var m=r(i,4);ee(m,17,()=>e(ft)(),H=>H.key,(H,R)=>{var U=pa(),B=a(U);c(B,{get icon(){return e(R).icon},class:"text-xs"});var n=r(B,2),y=a(n),D=r(n,2),Y=a(D);c(Y,{icon:"mdi:close",class:"text-[10px]"}),S(()=>o(y,e(R).label)),O("click",D,function(...Z){e(R).clear?.apply(this,Z)}),p(H,U)});var _=r(m,2),A=a(_);S(()=>o(A,`${e(j).length??""} resultado${e(j).length!==1?"s":""}`)),p(t,s)};G(ot,t=>{e(ke)&&!e(I)&&t(Ft)})}var it=r(ot,2);{var At=t=>{aa(t,{type:"cards"})},Lt=t=>{var s=ha(),l=Ie(s),i=a(l),m=a(i),_=a(m),A=a(_);c(A,{icon:"mdi:account-group",class:"text-primary-600 dark:text-primary-400 text-xl"});var H=r(_,2);{var R=f=>{var v=xa();p(f,v)};G(H,f=>{e(ke)&&f(R)})}var U=r(m,2),B=a(U),n=r(i,2),y=a(n),D=a(y),Y=a(D);c(Y,{icon:"mdi:text-box-multiple",class:"text-violet-600 dark:text-violet-400 text-xl"});var Z=r(D,2),$e=a(Z),q=r(y,2),xe=a(q),se=r(n,2),J=a(se),K=a(J),T=a(K);c(T,{icon:"mdi:book-open-variant",class:"text-emerald-600 dark:text-emerald-400 text-xl"});var N=r(K,2);{var Q=f=>{var v=ua(),g=a(v);S(()=>{Ne(v,"title",`Más frecuente: ${e(M).asignaturaTop.nombre??""}`),o(g,`Top: ${e(M).asignaturaTop.cantidad??""}`)}),p(f,v)};G(N,f=>{e(M).asignaturaTop&&f(Q)})}var ue=r(J,2),fe=a(ue),re=r(se,2),le=a(re),oe=a(le),ie=a(oe);c(ie,{icon:"mdi:account-tie",class:"text-amber-600 dark:text-amber-400 text-xl"});var ne=r(oe,2),ge=a(ne),be=r(le,2),he=a(be),_e=r(l,2);{var de=f=>{var v=ga(),g=a(v),P=a(g);c(P,{icon:"mdi:chart-bar",class:"text-slate-400 text-base"});var C=r(g,2);ee(C,21,()=>e(Re)(),ve,(V,L,ce)=>{var Se=fa(),ze=a(Se),Ot=a(ze),nt=r(ze,2),Ge=a(nt),dt=r(Ge,2),Ht=a(dt),Rt=r(nt,2),Ut=a(Rt);S(()=>{Ne(ze,"title",e(L).nombre),o(Ot,e(L).nombre),W(Ge,1,`h-full rounded-full transition-all duration-700 ease-out ${Je[ce%Je.length]??""}`,"svelte-m6227o"),vt(Ge,`width: ${e(L).porcentaje??""}%`),W(dt,1,`absolute inset-0 flex items-center justify-end pr-2 text-[10px] font-bold ${e(L).porcentaje>50?"text-white":"text-slate-500 dark:text-slate-300"}`,"svelte-m6227o"),o(Ht,e(L).cantidad),o(Ut,`${e(L).porcentaje??""}%`)}),p(V,Se)}),p(f,v)},je=w(()=>e(Re)().length>0);G(_e,f=>{e(je)&&f(de)})}var De=r(_e,2);{var ye=f=>{var v=ba(),g=a(v);c(g,{icon:"mdi:bell-ring-outline",class:"text-amber-500 text-lg"});var P=r(g,2),C=r(a(P)),V=r(C),L=a(V);S((ce,Se)=>{o(C,` ${ce??""} `),o(L,`(${Se??""} días)`)},[()=>Be(e(M).fechaProxima.toISOString()),()=>Ce(e(M).fechaProxima.toISOString())]),p(f,v)};G(De,f=>{e(M).fechaProxima&&f(ye)})}S(()=>{o(B,e(M).totalEstudiantes),o($e,`${e(M).promedioPlanesPorEstudiante??""}/est.`),o(xe,e(M).totalPlanes),o(fe,e(M).totalAsignaturas),o(ge,`${e(M).totalGrupos??""} grupos`),o(he,e(M).totalDocentes)}),p(t,s)};G(it,t=>{e(I)?t(At):e(E).length>0&&t(Lt,1)})}var zt=r(it,2);{var Gt=t=>{var s=ya(),l=a(s),i=a(l),m=r(a(i),3);W(m,0,"fill-white dark:fill-slate-700 svelte-m6227o",null,{},{"stroke-slate-200":!0});var _=r(l,4),A=a(_);{var H=n=>{var y=pt(`No hay resultados para los filtros actuales. Intenta ajustar los
          criterios de busqueda.`);p(n,y)},R=n=>{var y=pt(`Aun no hay planes de mejoramiento registrados. Usa el formulario para
          crear el primero.`);p(n,y)};G(A,n=>{e(ke)?n(H):n(R,!1)})}var U=r(_,2);{var B=n=>{var y=_a(),D=a(y);c(D,{icon:"mdi:filter-remove",class:"text-base"}),O("click",y,Ue),p(n,y)};G(U,n=>{e(ke)&&n(B)})}p(t,s)},It=t=>{var s=ja(),l=Ie(s),i=a(l),m=r(a(i)),_=a(m),A=r(m),H=r(A),R=a(H),U=r(l,2);ee(U,21,()=>e(ae),ve,(B,n,y)=>{const D=w(()=>e(n).records.length),Y=w(()=>e(n).records.reduce((v,g)=>{const P=Ce(g.fecha_limite);return P<v?P:v},1/0)),Z=w(()=>e(Y)<0?"rose":e(Y)<=3?"amber":e(Y)<=7?"blue":"emerald"),$e=w(()=>`border-t-${e(Z)}-500`);var q=$a(),xe=a(q);{var se=v=>{var g=ka(),P=r(a(g),4),C=a(P);c(C,{icon:"mdi:alert",class:"text-[13px] relative z-10"});var V=r(P,2),L=a(V);S(()=>{Ne(g,"title",`Este estudiante tiene ${e(D)??""} planes de mejoramiento`),o(L,`${e(D)??""} planes`)}),p(v,g)};G(xe,v=>{e(D)>2&&v(se)})}var J=r(xe,2),K=a(J),T=a(K),N=r(K,2),Q=a(N),ue=a(Q),fe=r(Q,2),re=a(fe);c(re,{icon:"mdi:google-classroom",class:"text-xs"});var le=r(re),oe=r(J,2);ee(oe,21,()=>e(n).records,ve,(v,g)=>{const P=w(()=>Ve(e(g).fecha_limite));var C=wa(),V=a(C),L=r(V,2),ce=a(L);S(()=>{W(C,1,`inline-flex items-center gap-1 text-[10px] font-medium px-2 py-1 rounded-lg border ${e(P).color??""}`,"svelte-m6227o"),W(V,1,`w-1.5 h-1.5 rounded-full ${e(P).dot??""} shrink-0`,"svelte-m6227o"),o(ce,e(g).asignatura)}),p(v,C)});var ie=r(oe,2),ne=a(ie),ge=a(ne);c(ge,{icon:"mdi:text-box-multiple",class:"text-xs text-slate-400 dark:text-slate-500"});var be=r(ge,2),he=a(be),_e=r(ne,2),de=a(_e);c(de,{icon:"mdi:calendar-clock",class:"text-[11px]"});var je=r(de),De=r(ie,2),ye=a(De),f=a(ye);c(f,{icon:"mdi:eye-outline",class:"text-sm inline -mt-0.5"}),S((v,g,P,C)=>{W(q,1,`student-card card border-t-4 ${e($e)??""} text-left cursor-pointer group stagger-item`,"svelte-m6227o"),vt(q,`animation-delay: ${v??""}ms`),W(K,1,`w-11 h-11 rounded-xl bg-gradient-to-br ${g??""} flex items-center justify-center text-white font-bold text-xs shadow-md shrink-0`,"svelte-m6227o"),o(T,P),o(ue,e(n).estudiante),o(le,` Grupo ${e(n).grupo??""}`),o(he,`${e(D)??""}
                ${e(D)===1?"plan":"planes"}`),o(je,` ${C??""}`)},[()=>Math.min(y*60,600),()=>Ye(e(n).estudiante),()=>Ze(e(n).estudiante),()=>qe(e(n).records[0].fecha_limite)]),O("click",q,()=>gt(e(n))),p(B,q)}),S(()=>{o(_,e(ae).length),o(A,` estudiante${e(ae).length!==1?"s":""} con `),o(R,e(j).length)}),p(t,s)};G(zt,t=>{!e(I)&&e(ae).length===0?t(Gt):e(I)||t(It,1)})}var Mt=r(Qe,2);{var Nt=t=>{var s=Pa(),l=a(s),i=a(l),m=a(i),_=a(m),A=a(_),H=a(A),R=r(A,2),U=a(R),B=a(U),n=r(U,2),y=a(n);c(y,{icon:"mdi:google-classroom",class:"text-sm"});var D=r(y),Y=r(D,2),Z=r(_,2),$e=a(Z);c($e,{icon:"mdi:close",class:"text-lg"});var q=r(m,2);ee(q,21,()=>e(u).records,ve,(T,N)=>{const Q=w(()=>Ve(e(N).fecha_limite));var ue=Da(),fe=a(ue),re=a(fe),le=a(re);c(le,{icon:"mdi:book-open-variant",class:"text-sm text-primary-500"});var oe=r(le),ie=r(re,2),ne=a(ie);c(ne,{icon:"mdi:account-tie",class:"text-sm text-slate-400"});var ge=r(ne),be=r(ie,2),he=a(be),_e=r(he),de=r(fe,2),je=a(de),De=r(de,2),ye=a(De),f=a(ye);c(f,{icon:"mdi:calendar-clock",class:"text-xs"});var v=r(f,2),g=a(v),P=r(ye,2),C=a(P);c(C,{icon:"mdi:clock-outline",class:"text-xs"});var V=r(C);S((L,ce)=>{o(oe,` ${e(N).asignatura??""}`),o(ge,` ${e(N).docente??""}`),W(be,1,`inline-flex items-center gap-1 text-[11px] font-medium border px-2 py-0.5 rounded-full ${e(Q).color??""}`,"svelte-m6227o"),W(he,1,`w-1.5 h-1.5 rounded-full ${e(Q).dot??""}`,"svelte-m6227o"),o(_e,` ${e(Q).label??""}`),o(je,e(N).plan),o(g,L),o(V,` Registro: ${ce??""}`)},[()=>Be(e(N).fecha_limite),()=>qe(e(N).fecha_registro)]),p(T,ue)});var xe=r(q,2),se=a(xe),J=r(se,2),K=a(J);c(K,{icon:"mdi:file-pdf-box",class:"text-sm"}),Jt(s,T=>bt?.(T)),S((T,N)=>{W(A,1,`w-12 h-12 rounded-xl bg-gradient-to-br ${T??""} flex items-center justify-center text-white font-bold text-sm shadow-md shrink-0`,"svelte-m6227o"),o(H,N),o(B,e(u).estudiante),o(D,` Grupo ${e(u).grupo??""} `),o(Y,` ${e(u).records.length??""}
                ${e(u).records.length===1?"plan":"planes"}`)},[()=>Ye(e(u).estudiante),()=>Ze(e(u).estudiante)]),O("click",l,we),O("keydown",l,T=>T.key==="Escape"&&we()),O("click",i,T=>T.stopPropagation()),O("keydown",i,T=>T.stopPropagation()),O("click",Z,we),O("click",se,we),O("click",J,()=>_t(e(u).records[0])),p(t,s)};G(Mt,t=>{e(u)&&t(Nt)})}Yt(Tt,()=>e(b),t=>k(b,t)),Me(Ee,()=>e($),t=>k($,t)),Me(Fe,()=>e(h),t=>k(h,t)),Me(lt,()=>e(F),t=>k(F,t)),p(d,Ke),Zt()}Kt(["click","keydown"]);export{za as default};
