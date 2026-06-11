import{p as Rt,s as ee,b as ct,o as Ut,j as k,e as Bt,$ as qt,f as Ie,b3 as d,k as r,i as z,m as e,A as te,t as S,r as o,g as v,N as w,y as a,B as pe,af as Vt,au as Ne,h as Wt,v as N,q as W,l as vt,n as Me,bY as Yt,x,J as pt,d as Zt}from"./svelte-DojZHhY2.js";import{d as Jt,g as Pe,c as Oe,i as Kt,p as Qt}from"./App-Bjkxw-hz.js";import{e as Xt}from"./index-DUu3WKg9.js";import{S as ea}from"./SkeletonLoader-BX3XAJDA.js";import"./pdf-DgofWDCs.js";import"./ui-DZFw2LIZ.js";function He(b){try{return new Date(b).toLocaleDateString("es-CO",{year:"numeric",month:"long",day:"numeric"})}catch{return b}}function ta(b,T){return`
  <div class="header">
    <div class="header-left">
      ${b?`<img src="${b}" alt="Escudo" class="escudo">`:""}
      <div>
        <p class="inst-name">INSTITUCIÓN EDUCATIVA OFICIAL <strong>INSTITUTO GUÁTICA</strong></p>
        <p class="inst-detail">Res. 002879 del 13/Dic/2017 · NIT 891.401.438-5 · DANE 166318000537</p>
      </div>
    </div>
    <div class="header-right">
      <p class="title-text">ACTA DE ENTREGA</p>
      <p class="title-sub">Plan de Mejoramiento · ${T} · ${Jt}</p>
    </div>
  </div>
  `}function aa(b,T,M){const O=T[0]?.grupo||"",Y=[...new Set(T.map(h=>h.asignatura))].join(", "),$=[...new Set(T.map(h=>h.docente))].join(", "),m=T[0]?.fecha_limite||"";return`
  <table class="info-table">
    <tr>
      <td class="info-cell">
        <span class="lbl">Estudiante</span>
        <span class="val bold">${b}</span>
      </td>
      <td class="info-cell">
        <span class="lbl">Grupo</span>
        <span class="val">${O}</span>
      </td>
      <td class="info-cell">
        <span class="lbl">Asignatura(s)</span>
        <span class="val">${Y}</span>
      </td>
    </tr>
    <tr>
      <td class="info-cell">
        <span class="lbl">Docente(s)</span>
        <span class="val">${$}</span>
      </td>
      <td class="info-cell">
        <span class="lbl">Fecha de Entrega</span>
        <span class="val">${M}</span>
      </td>
      <td class="info-cell">
        <span class="lbl">Fecha Límite</span>
        <span class="val bold">${He(m)}</span>
      </td>
    </tr>
  </table>
  `}function sa(){return`
  <div class="footer-zone">
    <p class="notice"><strong>Nota:</strong> Este plan debe ser desarrollado por el estudiante en el período indicado con acompañamiento de los padres. Entregar en la fecha límite.</p>
    <div class="firmas">
      <div class="firma"><div class="firma-line"></div><p class="firma-label">Firma del Docente</p></div>
      <div class="firma"><div class="firma-line"></div><p class="firma-label">Firma del Coordinador</p></div>
      <div class="firma"><div class="firma-line"></div><p class="firma-label">Firma del Acudiente</p></div>
    </div>
  </div>
  `}function ra(b,T,M,O){const Y=He(new Date().toISOString().split("T")[0]),$=T.map((m,h)=>`
    <div class="student-record${h===T.length-1?" last":""}">
      <div class="record-header">
        <span class="record-asignatura">${m.asignatura||""}</span>
        <span class="record-meta">${m.docente||""} · Límite: ${He(m.fecha_limite)}</span>
      </div>
      <div class="plan-section">
        <div class="plan-content">${m.plan||""}</div>
      </div>
    </div>
    `).join("");return`
  <div class="page-item">
    ${ta(M,O)}
    ${aa(b,T,Y)}
    ${$}
    ${sa()}
  </div>
  `}function mt(b,T,M){Array.isArray(b)||(b=[b]);const O=b[0]?.estudiante||"",Y=b.reduce((u,me)=>{const ae=me.estudiante||"__unknown__";return u[ae]||(u[ae]=[]),u[ae].push(me),u},{}),$=Object.values(Y);let m="";$.forEach((u,me)=>{const ae=me===$.length-1,Se=u[0]?.estudiante||"";m+=ra(Se,u,T,M),ae||(m+='<div class="page-break"></div>')});const h=`<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Plan de Mejoramiento - ${O}</title>
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
  ${m}

  <script>
    window.onload = function() {
      window.print();
    };
  <\/script>
</body>
</html>`,E=window.open("","_blank");E.document.write(h),E.document.close()}var la=x('<button class="text-xs font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 flex items-center gap-1 transition-colors svelte-m6227o"><!> Limpiar filtros</button>'),oa=x('<button class="btn-danger text-xs flex items-center gap-1 svelte-m6227o"><!> </button>'),ia=x('<option class="svelte-m6227o"> </option>'),na=x('<option class="svelte-m6227o"> </option>'),da=x('<option class="svelte-m6227o"> </option>'),ca=x('<span class="filter-chip svelte-m6227o"><!> <span class="truncate max-w-[150px] svelte-m6227o"> </span> <button class="w-4 h-4 rounded-full hover:bg-primary-200 dark:hover:bg-primary-700 flex items-center justify-center transition-colors ml-0.5 svelte-m6227o"><!></button></span>'),va=x('<div class="sticky-filter-bar svelte-m6227o"><div class="flex items-center gap-2 flex-wrap svelte-m6227o"><!> <span class="text-xs font-medium text-slate-600 dark:text-slate-400 shrink-0 svelte-m6227o">Filtros activos:</span> <!> <span class="text-xs text-slate-500 dark:text-slate-500 ml-auto shrink-0 svelte-m6227o"> </span></div></div>'),pa=x('<span class="text-[10px] font-medium text-primary-500 bg-primary-50 dark:bg-primary-900/50 px-1.5 py-0.5 rounded-md svelte-m6227o">Filtrado</span>'),ma=x('<span class="text-[10px] font-medium text-emerald-500 bg-emerald-50 dark:bg-emerald-900/50 px-1.5 py-0.5 rounded-md truncate max-w-[80px] svelte-m6227o"> </span>'),xa=x('<div class="flex items-center gap-3 svelte-m6227o"><span class="text-xs text-slate-600 dark:text-slate-300 w-28 sm:w-40 truncate font-medium svelte-m6227o"> </span> <div class="flex-1 h-6 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden relative svelte-m6227o"><div></div> <span> </span></div> <span class="text-[10px] text-slate-500 dark:text-slate-500 w-8 text-right font-medium svelte-m6227o"> </span></div>'),ua=x('<div class="card p-4 sm:p-5 stagger-item svelte-m6227o" style="animation-delay: 320ms"><div class="flex items-center gap-2 mb-3 svelte-m6227o"><!> <h3 class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider svelte-m6227o">Distribución por asignatura</h3> <span class="text-[10px] text-slate-500 dark:text-slate-500 ml-auto svelte-m6227o">Top 5</span></div> <div class="space-y-2.5 svelte-m6227o"></div></div>'),fa=x('<div class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/50 dark:to-orange-950/50 border border-amber-200/60 dark:border-amber-800/60 stagger-item svelte-m6227o" style="animation-delay: 400ms"><!> <p class="text-xs text-amber-700 dark:text-amber-300 svelte-m6227o"><span class="font-semibold svelte-m6227o">Próxima fecha límite:</span> <span class="text-amber-500 dark:text-amber-400 ml-1 svelte-m6227o"> </span></p></div>'),ga=x('<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 svelte-m6227o"><div class="stat-card group stagger-item svelte-m6227o" style="animation-delay: 0ms"><div class="flex items-center justify-between mb-3 svelte-m6227o"><div class="w-10 h-10 rounded-xl bg-primary-100 dark:bg-primary-900/50 flex items-center justify-center transition-transform group-hover:scale-110 svelte-m6227o"><!></div> <!></div> <p class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 tracking-tight svelte-m6227o"> </p> <p class="text-xs text-slate-500 dark:text-slate-500 mt-0.5 svelte-m6227o">Estudiantes</p></div> <div class="stat-card group stagger-item svelte-m6227o" style="animation-delay: 80ms"><div class="flex items-center justify-between mb-3 svelte-m6227o"><div class="w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-900/50 flex items-center justify-center transition-transform group-hover:scale-110 svelte-m6227o"><!></div> <span class="text-[10px] font-medium text-slate-500 dark:text-slate-500 svelte-m6227o"> </span></div> <p class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 tracking-tight svelte-m6227o"> </p> <p class="text-xs text-slate-500 dark:text-slate-500 mt-0.5 svelte-m6227o">Planes de mejoramiento</p></div> <div class="stat-card group stagger-item svelte-m6227o" style="animation-delay: 160ms"><div class="flex items-center justify-between mb-3 svelte-m6227o"><div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center transition-transform group-hover:scale-110 svelte-m6227o"><!></div> <!></div> <p class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 tracking-tight svelte-m6227o"> </p> <p class="text-xs text-slate-500 dark:text-slate-500 mt-0.5 svelte-m6227o">Asignaturas</p></div> <div class="stat-card group stagger-item svelte-m6227o" style="animation-delay: 240ms"><div class="flex items-center justify-between mb-3 svelte-m6227o"><div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center transition-transform group-hover:scale-110 svelte-m6227o"><!></div> <span class="text-[10px] font-medium text-slate-500 dark:text-slate-500 svelte-m6227o"> </span></div> <p class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 tracking-tight svelte-m6227o"> </p> <p class="text-xs text-slate-500 dark:text-slate-500 mt-0.5 svelte-m6227o">Docentes</p></div></div> <!> <!>',1),ba=x('<button class="mt-4 inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/30 hover:bg-primary-100 dark:hover:bg-primary-900/50 rounded-xl transition-colors svelte-m6227o"><!> Limpiar filtros</button>'),ha=x('<div class="card p-12 text-center stagger-item svelte-m6227o"><div class="mx-auto mb-6 w-48 h-48 svelte-m6227o"><svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full svelte-m6227o"><circle cx="100" cy="100" r="90" class="fill-slate-100 dark:fill-slate-800 svelte-m6227o"></circle><rect x="45" y="75" width="110" height="80" rx="8" class="fill-slate-200 dark:fill-slate-700 svelte-m6227o"></rect><path d="M45 83C45 78.5817 48.5817 75 53 75H80L90 65H137C141.418 65 145 68.5817 145 73V75H45V83Z" class="fill-slate-300 dark:fill-slate-600 svelte-m6227o"></path><rect x="40" y="85" width="120" height="70" rx="6" stroke-width="1.5"></rect><circle cx="100" cy="115" r="18" class="stroke-primary-400 dark:stroke-primary-500 svelte-m6227o" stroke-width="3" fill="none"></circle><line x1="113" y1="128" x2="125" y2="140" class="stroke-primary-400 dark:stroke-primary-500 svelte-m6227o" stroke-width="3" stroke-linecap="round"></line><text x="95" y="122" class="fill-primary-400 dark:fill-primary-500 svelte-m6227o" font-size="20" font-weight="bold" font-family="sans-serif">?</text><circle cx="50" cy="55" r="3" class="fill-primary-200 dark:fill-primary-700 svelte-m6227o" opacity="0.6"></circle><circle cx="160" cy="60" r="4" class="fill-violet-200 dark:fill-violet-700 svelte-m6227o" opacity="0.6"></circle><circle cx="35" cy="150" r="3" class="fill-emerald-200 dark:fill-emerald-700 svelte-m6227o" opacity="0.6"></circle><circle cx="170" cy="145" r="5" class="fill-amber-200 dark:fill-amber-700 svelte-m6227o" opacity="0.5"></circle></svg></div> <p class="text-slate-600 dark:text-slate-300 font-semibold text-lg mb-1 svelte-m6227o">No se encontraron registros</p> <p class="text-sm text-slate-500 dark:text-slate-500 max-w-sm mx-auto svelte-m6227o"><!></p> <!></div>'),_a=x('<div class="alert-badge svelte-m6227o"><span class="alert-ring svelte-m6227o"></span> <span class="alert-ring alert-ring-2 svelte-m6227o"></span> <span class="alert-icon-wrap svelte-m6227o"><!></span> <span class="alert-label svelte-m6227o"> </span></div>'),ya=x('<span><span></span> <span class="truncate max-w-[120px] svelte-m6227o"> </span></span>'),ka=x(`<button><!> <div class="flex items-start gap-3 mb-3 svelte-m6227o"><div> </div> <div class="flex-1 min-w-0 svelte-m6227o"><h3 class="text-[13px] font-bold text-slate-900 dark:text-slate-100 leading-tight line-clamp-2 svelte-m6227o"> </h3> <p class="text-[11px] text-slate-500 dark:text-slate-500 mt-0.5 flex items-center gap-1 svelte-m6227o"><!> </p></div></div> <div class="flex flex-wrap gap-1.5 mb-3 svelte-m6227o"></div> <div class="mt-auto flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-700 svelte-m6227o"><div class="flex items-center gap-1.5 svelte-m6227o"><!> <span class="text-[11px] font-semibold text-slate-600 dark:text-slate-500 svelte-m6227o"> </span></div> <span class="text-[10px] text-slate-500 dark:text-slate-500 flex items-center gap-1 svelte-m6227o"><!> </span></div> <div class="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none flex items-center justify-center bg-slate-900/5 dark:bg-slate-100/5 svelte-m6227o"><span class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm text-xs font-semibold text-primary-600 dark:text-primary-400 px-3 py-1.5 rounded-full shadow-lg svelte-m6227o"><!> Ver
              detalle</span></div></button>`),wa=x('<div class="flex items-center justify-between px-1 svelte-m6227o"><p class="text-xs text-slate-500 dark:text-slate-500 svelte-m6227o">Mostrando <span class="font-semibold text-slate-600 dark:text-slate-300 svelte-m6227o"> </span> <span class="font-semibold text-slate-600 dark:text-slate-300 svelte-m6227o"> </span> planes</p></div> <div class="student-grid svelte-m6227o"></div>',1),$a=x('<div class="rounded-xl border border-slate-100 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 p-4 space-y-3 svelte-m6227o"><div class="flex flex-wrap items-center gap-x-3 gap-y-1.5 svelte-m6227o"><span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 svelte-m6227o"><!> </span> <span class="inline-flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-400 svelte-m6227o"><!> </span> <span><span></span> </span></div> <div class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line svelte-m6227o"> </div> <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-slate-500 dark:text-slate-500 svelte-m6227o"><span class="flex items-center gap-1 svelte-m6227o"><!> Límite: <span class="font-medium text-slate-600 dark:text-slate-500 svelte-m6227o"> </span></span> <span class="flex items-center gap-1 svelte-m6227o"><!> </span></div></div>'),ja=x('<div class="svelte-m6227o"><div class="modal-backdrop svelte-m6227o" role="dialog" aria-modal="true" aria-label="Detalle del estudiante" tabindex="-1"><div class="modal-content svelte-m6227o"><div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700 shrink-0 svelte-m6227o"><div class="flex items-center gap-3 svelte-m6227o"><div> </div> <div class="svelte-m6227o"><h3 class="text-base font-bold text-slate-800 dark:text-slate-100 svelte-m6227o"> </h3> <p class="text-xs text-slate-500 dark:text-slate-500 flex items-center gap-1.5 svelte-m6227o"><!> <span class="text-slate-400 dark:text-slate-600 svelte-m6227o">|</span> </p></div></div> <button class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition-colors text-slate-500 hover:text-slate-700 dark:hover:text-slate-200 svelte-m6227o" aria-label="Cerrar"><!></button></div> <div class="p-5 space-y-3 overflow-y-auto modal-body svelte-m6227o"></div> <div class="flex justify-end gap-2 p-5 border-t border-slate-100 dark:border-slate-700 shrink-0 svelte-m6227o"><button class="px-4 py-2 text-xs font-medium text-slate-600 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors svelte-m6227o">Cerrar</button> <button class="btn-danger svelte-m6227o"><!> Generar PDF</button></div></div></div></div>'),Da=x('<div class="space-y-5 svelte-m6227o"><div class="card p-5 sm:p-6 svelte-m6227o"><div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5 svelte-m6227o"><div class="flex items-center gap-2 svelte-m6227o"><div class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900/50 flex items-center justify-center svelte-m6227o"><!></div> <h2 class="text-base font-bold text-slate-800 dark:text-slate-100 svelte-m6227o">Registros</h2></div> <div class="flex items-center gap-2 svelte-m6227o"><!> <!></div></div> <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 svelte-m6227o"><div class="relative svelte-m6227o"><!> <input type="text" placeholder="Buscar en todos los campos..." class="field-input pl-10 svelte-m6227o"/></div> <select class="field-input svelte-m6227o"></select> <select class="field-input svelte-m6227o"><option class="svelte-m6227o">Todos los grupos</option><!></select> <select class="field-input svelte-m6227o"><option class="svelte-m6227o">Todos los docentes</option><!></select></div></div> <!> <!> <!></div> <!>',1);function Fa(b,T){Rt(T,!0);let M=ee(ct([])),O=ee(!0),Y=ee(""),$=ee(""),m=ee(""),h=ee(""),E=ee(ct(Pe())),u=ee(null),me=w(()=>[...new Set(e(M).map(t=>t.grupo))].sort((t,s)=>Number(t)-Number(s))),ae=w(()=>[...new Set(e(M).map(t=>t.docente))].sort()),Se=w(()=>Oe(e(E))),j=w(()=>e(M).filter(t=>{const s=!e($)||Object.values(t).some(_=>String(_).toLowerCase().includes(e($).toLowerCase())),l=!e(m)||t.grupo===e(m),i=!e(h)||t.docente===e(h),p=!e(E)||t.periodo===e(E)||t.periodo==="";return s&&l&&i&&p})),se=w(()=>Object.values(e(j).reduce((t,s)=>(t[s.estudiante]||(t[s.estudiante]={estudiante:s.estudiante,grupo:s.grupo,records:[]}),t[s.estudiante].records.push(s),t),{})).sort((t,s)=>t.estudiante.localeCompare(s.estudiante))),G=w(()=>({totalEstudiantes:e(se).length,totalPlanes:e(j).length,totalAsignaturas:[...new Set(e(j).map(t=>t.asignatura))].length,totalDocentes:[...new Set(e(j).map(t=>t.docente))].length,totalGrupos:[...new Set(e(j).map(t=>t.grupo))].length,promedioPlanesPorEstudiante:e(se).length>0?(e(j).length/e(se).length).toFixed(1):"0",asignaturaTop:(()=>{const t={};e(j).forEach(l=>{t[l.asignatura]=(t[l.asignatura]||0)+1});const s=Object.entries(t).sort((l,i)=>i[1]-l[1]);return s[0]?{nombre:s[0][0],cantidad:s[0][1]}:null})(),fechaProxima:(()=>{const t=new Date;return t.setHours(0,0,0,0),e(j).map(l=>new Date(l.fecha_limite)).filter(l=>l>=t).sort((l,i)=>l-i)[0]||null})()})),Re=w(()=>()=>{const t={};e(j).forEach(l=>{t[l.asignatura]=(t[l.asignatura]||0)+1});const s=e(j).length||1;return Object.entries(t).sort((l,i)=>i[1]-l[1]).slice(0,5).map(([l,i])=>({nombre:l,cantidad:i,porcentaje:Math.round(i/s*100)}))});function Ue(){k($,""),k(m,""),k(h,""),k(E,Pe(),!0)}let ke=w(()=>e($)||e(m)||e(h)||e(E)!==Pe()),xt=w(()=>()=>{const t=[];return e($)&&t.push({key:"search",label:`"${e($)}"`,icon:"mdi:magnify",clear:()=>k($,"")}),e(m)&&t.push({key:"grupo",label:`Grupo ${e(m)}`,icon:"mdi:google-classroom",clear:()=>k(m,"")}),e(h)&&t.push({key:"docente",label:e(h),icon:"mdi:account-tie",clear:()=>k(h,"")}),e(E)!==Pe()&&t.push({key:"periodo",label:Oe(e(E)),icon:"mdi:calendar",clear:()=>k(E,Pe(),!0)}),t});Ut(async()=>{await Promise.allSettled([(async()=>{try{const t=await fetch("https://app.iedeoccidente.com/gs/getgsartirec.php");k(M,await t.json(),!0)}catch(t){console.error("Error fetching data:",t)}finally{k(O,!1)}})(),(async()=>{try{const s=await(await fetch(Xt)).blob(),l=new FileReader;l.onloadend=()=>{k(Y,l.result,!0)},l.readAsDataURL(s)}catch{}})()])});function Be(t){try{return new Date(t).toLocaleDateString("es-CO",{year:"numeric",month:"long",day:"numeric"})}catch{return t}}function qe(t){try{return new Date(t).toLocaleDateString("es-CO",{month:"short",day:"numeric"})}catch{return t}}function Le(t){const s=new Date;s.setHours(0,0,0,0);const l=new Date(t);return l.setHours(0,0,0,0),Math.ceil((l-s)/(1e3*60*60*24))}function Ve(t){const s=Le(t);return s<0?{label:"Vencido",color:"text-rose-600 bg-rose-50 dark:bg-rose-950 border-rose-200 dark:border-rose-800",icon:"mdi:alert-circle",dot:"bg-rose-500"}:s<=3?{label:`${s}d restante${s!==1?"s":""}`,color:"text-amber-600 bg-amber-50 dark:bg-amber-950 border-amber-200 dark:border-amber-800",icon:"mdi:clock-alert",dot:"bg-amber-500"}:s<=7?{label:`${s} días`,color:"text-blue-600 bg-blue-50 dark:bg-blue-950 border-blue-200 dark:border-blue-800",icon:"mdi:calendar-clock",dot:"bg-blue-500"}:{label:`${s} días`,color:"text-emerald-600 bg-emerald-50 dark:bg-emerald-950 border-emerald-200 dark:border-emerald-800",icon:"mdi:calendar-check",dot:"bg-emerald-500"}}function ut(t){k(u,t,!0)}function we(){k(u,null)}function ft(t){return document.body.appendChild(t),{destroy(){t.parentNode&&t.parentNode.removeChild(t)}}}function gt(t){t.key==="Escape"&&e(u)&&we()}const We=["from-primary-400 to-primary-600","from-violet-400 to-violet-600","from-emerald-400 to-emerald-600","from-amber-400 to-amber-600","from-rose-400 to-rose-600","from-cyan-400 to-cyan-600","from-fuchsia-400 to-fuchsia-600","from-teal-400 to-teal-600"];function Ye(t){let s=0;for(let l=0;l<t.length;l++)s=t.charCodeAt(l)+((s<<5)-s);return We[Math.abs(s)%We.length]}function Ze(t){return t.split(" ").slice(0,2).map(s=>s[0]).join("").toUpperCase()}function bt(t){const s=e(j).filter(l=>l.estudiante===t.estudiante);mt(s,e(Y),e(Se))}function ht(){const t=[...e(j)].sort((s,l)=>s.estudiante.localeCompare(l.estudiante));mt(t,e(Y),e(Se))}const Je=["bg-primary-500","bg-violet-500","bg-emerald-500","bg-amber-500","bg-rose-500"];var Ke=Da();Bt("keydown",qt,gt);var Qe=Ie(Ke),Xe=a(Qe),et=a(Xe),tt=a(et),_t=a(tt),yt=a(_t);d(yt,{icon:"mdi:format-list-bulleted",class:"text-primary-600 dark:text-primary-400 text-lg"});var kt=r(tt,2),at=a(kt);{var wt=t=>{var s=la(),l=a(s);d(l,{icon:"mdi:filter-remove",class:"text-sm"}),N("click",s,Ue),v(t,s)};z(at,t=>{e(ke)&&t(wt)})}var $t=r(at,2);{var jt=t=>{var s=oa(),l=a(s);d(l,{icon:"mdi:file-pdf-box",class:"text-sm"});var i=r(l);S(()=>o(i,` PDF Grupo ${e(m)??""}`)),N("click",s,ht),v(t,s)};z($t,t=>{e(m)&&e(j).length>0&&t(jt)})}var Dt=r(et,2),st=a(Dt),rt=a(st);d(rt,{icon:"mdi:magnify",class:"absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-base"});var Pt=r(rt,2),Te=r(st,2);te(Te,21,()=>Qt,pe,(t,s)=>{const l=w(()=>Kt(e(s).nombre));var i=ia(),p=a(i),_={};S(F=>{i.disabled=!e(l),o(p,`${F??""} ${e(l)?"":"(Fuera de rango)"}`),_!==(_=e(s).nombre)&&(i.value=(i.__value=e(s).nombre)??"")},[()=>Oe(e(s).nombre)]),v(t,i)});var Ee=r(Te,2),Fe=a(Ee);Fe.value=Fe.__value="";var St=r(Fe);te(St,17,()=>e(me),pe,(t,s)=>{var l=na(),i=a(l),p={};S(()=>{o(i,`Grupo ${e(s)??""}`),p!==(p=e(s))&&(l.value=(l.__value=e(s))??"")}),v(t,l)});var lt=r(Ee,2),Ae=a(lt);Ae.value=Ae.__value="";var Ct=r(Ae);te(Ct,17,()=>e(ae),pe,(t,s)=>{var l=da(),i=a(l),p={};S(()=>{o(i,e(s)),p!==(p=e(s))&&(l.value=(l.__value=e(s))??"")}),v(t,l)});var ot=r(Xe,2);{var Lt=t=>{var s=va(),l=a(s),i=a(l);d(i,{icon:"mdi:filter-variant",class:"text-primary-500 dark:text-primary-400 text-sm shrink-0"});var p=r(i,4);te(p,17,()=>e(xt)(),H=>H.key,(H,R)=>{var U=ca(),B=a(U);d(B,{get icon(){return e(R).icon},class:"text-xs"});var n=r(B,2),y=a(n),D=r(n,2),Z=a(D);d(Z,{icon:"mdi:close",class:"text-[10px]"}),S(()=>o(y,e(R).label)),N("click",D,function(...J){e(R).clear?.apply(this,J)}),v(H,U)});var _=r(p,2),F=a(_);S(()=>o(F,`${e(j).length??""} resultado${e(j).length!==1?"s":""}`)),v(t,s)};z(ot,t=>{e(ke)&&!e(O)&&t(Lt)})}var it=r(ot,2);{var Tt=t=>{ea(t,{type:"cards"})},Et=t=>{var s=ga(),l=Ie(s),i=a(l),p=a(i),_=a(p),F=a(_);d(F,{icon:"mdi:account-group",class:"text-primary-600 dark:text-primary-400 text-xl"});var H=r(_,2);{var R=f=>{var c=pa();v(f,c)};z(H,f=>{e(ke)&&f(R)})}var U=r(p,2),B=a(U),n=r(i,2),y=a(n),D=a(y),Z=a(D);d(Z,{icon:"mdi:text-box-multiple",class:"text-violet-600 dark:text-violet-400 text-xl"});var J=r(D,2),$e=a(J),q=r(y,2),xe=a(q),re=r(n,2),K=a(re),Q=a(K),C=a(Q);d(C,{icon:"mdi:book-open-variant",class:"text-emerald-600 dark:text-emerald-400 text-xl"});var I=r(Q,2);{var X=f=>{var c=ma(),g=a(c);S(()=>{Me(c,"title",`Más frecuente: ${e(G).asignaturaTop.nombre??""}`),o(g,`Top: ${e(G).asignaturaTop.cantidad??""}`)}),v(f,c)};z(I,f=>{e(G).asignaturaTop&&f(X)})}var ue=r(K,2),fe=a(ue),le=r(re,2),oe=a(le),ie=a(oe),ne=a(ie);d(ne,{icon:"mdi:account-tie",class:"text-amber-600 dark:text-amber-400 text-xl"});var de=r(ie,2),ge=a(de),be=r(oe,2),he=a(be),_e=r(l,2);{var ce=f=>{var c=ua(),g=a(c),P=a(g);d(P,{icon:"mdi:chart-bar",class:"text-slate-400 text-base"});var L=r(g,2);te(L,21,()=>e(Re)(),pe,(V,A,ve)=>{var Ce=xa(),ze=a(Ce),Nt=a(ze),nt=r(ze,2),Ge=a(nt),dt=r(Ge,2),Mt=a(dt),Ot=r(nt,2),Ht=a(Ot);S(()=>{Me(ze,"title",e(A).nombre),o(Nt,e(A).nombre),W(Ge,1,`h-full rounded-full transition-all duration-700 ease-out ${Je[ve%Je.length]??""}`,"svelte-m6227o"),vt(Ge,`width: ${e(A).porcentaje??""}%`),W(dt,1,`absolute inset-0 flex items-center justify-end pr-2 text-[10px] font-bold ${e(A).porcentaje>50?"text-white":"text-slate-600 dark:text-slate-300"}`,"svelte-m6227o"),o(Mt,e(A).cantidad),o(Ht,`${e(A).porcentaje??""}%`)}),v(V,Ce)}),v(f,c)},je=w(()=>e(Re)().length>0);z(_e,f=>{e(je)&&f(ce)})}var De=r(_e,2);{var ye=f=>{var c=fa(),g=a(c);d(g,{icon:"mdi:bell-ring-outline",class:"text-amber-500 text-lg"});var P=r(g,2),L=r(a(P)),V=r(L),A=a(V);S((ve,Ce)=>{o(L,` ${ve??""} `),o(A,`(${Ce??""} días)`)},[()=>Be(e(G).fechaProxima.toISOString()),()=>Le(e(G).fechaProxima.toISOString())]),v(f,c)};z(De,f=>{e(G).fechaProxima&&f(ye)})}S(()=>{o(B,e(G).totalEstudiantes),o($e,`${e(G).promedioPlanesPorEstudiante??""}/est.`),o(xe,e(G).totalPlanes),o(fe,e(G).totalAsignaturas),o(ge,`${e(G).totalGrupos??""} grupos`),o(he,e(G).totalDocentes)}),v(t,s)};z(it,t=>{e(O)?t(Tt):e(M).length>0&&t(Et,1)})}var Ft=r(it,2);{var At=t=>{var s=ha(),l=a(s),i=a(l),p=r(a(i),3);W(p,0,"fill-white dark:fill-slate-700 svelte-m6227o",null,{},{"stroke-slate-200":!0});var _=r(l,4),F=a(_);{var H=n=>{var y=pt(`No hay resultados para los filtros actuales. Intenta ajustar los
          criterios de busqueda.`);v(n,y)},R=n=>{var y=pt(`Aun no hay planes de mejoramiento registrados. Usa el formulario para
          crear el primero.`);v(n,y)};z(F,n=>{e(ke)?n(H):n(R,!1)})}var U=r(_,2);{var B=n=>{var y=ba(),D=a(y);d(D,{icon:"mdi:filter-remove",class:"text-base"}),N("click",y,Ue),v(n,y)};z(U,n=>{e(ke)&&n(B)})}v(t,s)},zt=t=>{var s=wa(),l=Ie(s),i=a(l),p=r(a(i)),_=a(p),F=r(p),H=r(F),R=a(H),U=r(l,2);te(U,21,()=>e(se),pe,(B,n,y)=>{const D=w(()=>e(n).records.length),Z=w(()=>e(n).records.reduce((c,g)=>{const P=Le(g.fecha_limite);return P<c?P:c},1/0)),J=w(()=>e(Z)<0?"rose":e(Z)<=3?"amber":e(Z)<=7?"blue":"emerald"),$e=w(()=>`border-t-${e(J)}-500`);var q=ka(),xe=a(q);{var re=c=>{var g=_a(),P=r(a(g),4),L=a(P);d(L,{icon:"mdi:alert",class:"text-[13px] relative z-10"});var V=r(P,2),A=a(V);S(()=>{Me(g,"title",`Este estudiante tiene ${e(D)??""} planes de mejoramiento`),o(A,`${e(D)??""} planes`)}),v(c,g)};z(xe,c=>{e(D)>2&&c(re)})}var K=r(xe,2),Q=a(K),C=a(Q),I=r(Q,2),X=a(I),ue=a(X),fe=r(X,2),le=a(fe);d(le,{icon:"mdi:google-classroom",class:"text-xs"});var oe=r(le),ie=r(K,2);te(ie,21,()=>e(n).records,pe,(c,g)=>{const P=w(()=>Ve(e(g).fecha_limite));var L=ya(),V=a(L),A=r(V,2),ve=a(A);S(()=>{W(L,1,`inline-flex items-center gap-1 text-[10px] font-medium px-2 py-1 rounded-lg border ${e(P).color??""}`,"svelte-m6227o"),W(V,1,`w-1.5 h-1.5 rounded-full ${e(P).dot??""} shrink-0`,"svelte-m6227o"),o(ve,e(g).asignatura)}),v(c,L)});var ne=r(ie,2),de=a(ne),ge=a(de);d(ge,{icon:"mdi:text-box-multiple",class:"text-xs text-slate-600 dark:text-slate-500"});var be=r(ge,2),he=a(be),_e=r(de,2),ce=a(_e);d(ce,{icon:"mdi:calendar-clock",class:"text-[11px]"});var je=r(ce),De=r(ne,2),ye=a(De),f=a(ye);d(f,{icon:"mdi:eye-outline",class:"text-sm inline -mt-0.5"}),S((c,g,P,L)=>{W(q,1,`student-card card border-t-4 ${e($e)??""} text-left cursor-pointer group stagger-item`,"svelte-m6227o"),vt(q,`animation-delay: ${c??""}ms`),W(Q,1,`w-11 h-11 rounded-xl bg-gradient-to-br ${g??""} flex items-center justify-center text-white font-bold text-xs shadow-md shrink-0`,"svelte-m6227o"),o(C,P),o(ue,e(n).estudiante),o(oe,` Grupo ${e(n).grupo??""}`),o(he,`${e(D)??""}
                ${e(D)===1?"plan":"planes"}`),o(je,` ${L??""}`)},[()=>Math.min(y*60,600),()=>Ye(e(n).estudiante),()=>Ze(e(n).estudiante),()=>qe(e(n).records[0].fecha_limite)]),N("click",q,()=>ut(e(n))),v(B,q)}),S(()=>{o(_,e(se).length),o(F,` estudiante${e(se).length!==1?"s":""} con `),o(R,e(j).length)}),v(t,s)};z(Ft,t=>{!e(O)&&e(se).length===0?t(At):e(O)||t(zt,1)})}var Gt=r(Qe,2);{var It=t=>{var s=ja(),l=a(s),i=a(l),p=a(i),_=a(p),F=a(_),H=a(F),R=r(F,2),U=a(R),B=a(U),n=r(U,2),y=a(n);d(y,{icon:"mdi:google-classroom",class:"text-sm"});var D=r(y),Z=r(D,2),J=r(_,2),$e=a(J);d($e,{icon:"mdi:close",class:"text-lg"});var q=r(p,2);te(q,21,()=>e(u).records,pe,(C,I)=>{const X=w(()=>Ve(e(I).fecha_limite));var ue=$a(),fe=a(ue),le=a(fe),oe=a(le);d(oe,{icon:"mdi:book-open-variant",class:"text-sm text-primary-500"});var ie=r(oe),ne=r(le,2),de=a(ne);d(de,{icon:"mdi:account-tie",class:"text-sm text-slate-500"});var ge=r(de),be=r(ne,2),he=a(be),_e=r(he),ce=r(fe,2),je=a(ce),De=r(ce,2),ye=a(De),f=a(ye);d(f,{icon:"mdi:calendar-clock",class:"text-xs"});var c=r(f,2),g=a(c),P=r(ye,2),L=a(P);d(L,{icon:"mdi:clock-outline",class:"text-xs"});var V=r(L);S((A,ve)=>{o(ie,` ${e(I).asignatura??""}`),o(ge,` ${e(I).docente??""}`),W(be,1,`inline-flex items-center gap-1 text-[11px] font-medium border px-2 py-0.5 rounded-full ${e(X).color??""}`,"svelte-m6227o"),W(he,1,`w-1.5 h-1.5 rounded-full ${e(X).dot??""}`,"svelte-m6227o"),o(_e,` ${e(X).label??""}`),o(je,e(I).plan),o(g,A),o(V,` Registro: ${ve??""}`)},[()=>Be(e(I).fecha_limite),()=>qe(e(I).fecha_registro)]),v(C,ue)});var xe=r(q,2),re=a(xe),K=r(re,2),Q=a(K);d(Q,{icon:"mdi:file-pdf-box",class:"text-sm"}),Yt(s,C=>ft?.(C)),S((C,I)=>{W(F,1,`w-12 h-12 rounded-xl bg-gradient-to-br ${C??""} flex items-center justify-center text-white font-bold text-sm shadow-md shrink-0`,"svelte-m6227o"),o(H,I),o(B,e(u).estudiante),o(D,` Grupo ${e(u).grupo??""} `),o(Z,` ${e(u).records.length??""}
                ${e(u).records.length===1?"plan":"planes"}`)},[()=>Ye(e(u).estudiante),()=>Ze(e(u).estudiante)]),N("click",l,we),N("keydown",l,C=>C.key==="Escape"&&we()),N("click",i,C=>C.stopPropagation()),N("keydown",i,C=>C.stopPropagation()),N("click",J,we),N("click",re,we),N("click",K,()=>bt(e(u).records[0])),v(t,s)};z(Gt,t=>{e(u)&&t(It)})}Vt(Pt,()=>e($),t=>k($,t)),Ne(Te,()=>e(E),t=>k(E,t)),Ne(Ee,()=>e(m),t=>k(m,t)),Ne(lt,()=>e(h),t=>k(h,t)),v(b,Ke),Wt()}Zt(["click","keydown"]);export{Fa as default};
