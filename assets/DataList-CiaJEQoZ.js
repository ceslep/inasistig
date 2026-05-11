import{p as Gt,s as ie,b as It,o as Nt,j as C,e as Mt,$ as Ot,f as Se,aU as d,k as r,i as T,m as e,A as ne,t as w,r as o,g as v,y as a,B as ge,a9 as Ht,am as tt,h as Rt,N as $,v as z,q as U,l as at,n as Ae,bo as Ut,x,J as st,d as qt}from"./svelte-4ubRlOph.js";import{p as Vt,c as Bt}from"./App-CpTA2LK1.js";import{e as Zt}from"./index-DbMD6_ar.js";import{S as Jt}from"./SkeletonLoader-Dz53Ou7s.js";import"./pdf-wbV6RFuj.js";import"./ui-CHfsb5jC.js";function rt(p){try{return new Date(p).toLocaleDateString("es-CO",{year:"numeric",month:"long",day:"numeric"})}catch{return p}}function Kt(p){return`
  <div class="header">
    <div class="header-left">
      ${p?`<img src="${p}" alt="Escudo" class="escudo">`:""}
      <div>
        <p class="inst-name">INSTITUCIÓN EDUCATIVA OFICIAL <strong>INSTITUTO GUÁTICA</strong></p>
        <p class="inst-detail">Res. 002879 del 13/Dic/2017 · NIT 891.401.438-5 · DANE 166318000537</p>
      </div>
    </div>
    <div class="header-right">
      <p class="title-text">ACTA DE ENTREGA</p>
      <p class="title-sub">Plan de Mejoramiento · ${Vt} · ${Bt}</p>
    </div>
  </div>
  `}function Wt(p,be,B,q){const Y=rt(p.fecha_limite),j=rt(new Date().toISOString().split("T")[0]);return`
  <div class="page-item">
    ${Kt(be)}

    <table class="info-table">
      <tr>
        <td class="info-cell"><span class="lbl">Estudiante</span><span class="val bold">${p.estudiante||""}</span></td>
        <td class="info-cell"><span class="lbl">Grupo</span><span class="val">${p.grupo||""}</span></td>
        <td class="info-cell"><span class="lbl">Asignatura</span><span class="val">${p.asignatura||""}</span></td>
      </tr>
      <tr>
        <td class="info-cell"><span class="lbl">Docente</span><span class="val">${p.docente||""}</span></td>
        <td class="info-cell"><span class="lbl">Fecha de Entrega</span><span class="val">${j}</span></td>
        <td class="info-cell"><span class="lbl">Fecha Límite</span><span class="val bold">${Y}</span></td>
      </tr>
    </table>

    <div class="plan-section">
      <p class="plan-title">Plan de Mejoramiento / Refuerzo Académico</p>
      <div class="plan-content">${p.plan||""}</div>
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
  ${q?"":'<div class="page-break"></div>'}
  `}function lt(p,be){Array.isArray(p)||(p=[p]);const B=p[0]?.estudiante||"",q=p.map((P,G)=>Wt(P,be,!0,G===p.length-1)).join(""),Y=`<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Plan de Mejoramiento - ${B}</title>
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
  ${q}

  <script>
    window.onload = function() {
      window.print();
    };
  <\/script>
</body>
</html>`,j=window.open("","_blank");j.document.write(Y),j.document.close()}var Yt=x('<button class="text-xs font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 flex items-center gap-1 transition-colors svelte-m6227o"><!> Limpiar filtros</button>'),Qt=x('<button class="btn-danger text-xs flex items-center gap-1 svelte-m6227o"><!> </button>'),Xt=x('<option class="svelte-m6227o"> </option>'),ea=x('<option class="svelte-m6227o"> </option>'),ta=x('<span class="filter-chip svelte-m6227o"><!> <span class="truncate max-w-[150px] svelte-m6227o"> </span> <button class="w-4 h-4 rounded-full hover:bg-primary-200 dark:hover:bg-primary-700 flex items-center justify-center transition-colors ml-0.5 svelte-m6227o"><!></button></span>'),aa=x('<div class="sticky-filter-bar svelte-m6227o"><div class="flex items-center gap-2 flex-wrap svelte-m6227o"><!> <span class="text-xs font-medium text-slate-500 dark:text-slate-400 shrink-0 svelte-m6227o">Filtros activos:</span> <!> <span class="text-xs text-slate-400 dark:text-slate-500 ml-auto shrink-0 svelte-m6227o"> </span></div></div>'),sa=x('<span class="text-[10px] font-medium text-primary-500 bg-primary-50 dark:bg-primary-900/50 px-1.5 py-0.5 rounded-md svelte-m6227o">Filtrado</span>'),ra=x('<span class="text-[10px] font-medium text-emerald-500 bg-emerald-50 dark:bg-emerald-900/50 px-1.5 py-0.5 rounded-md truncate max-w-[80px] svelte-m6227o"> </span>'),la=x('<div class="flex items-center gap-3 svelte-m6227o"><span class="text-xs text-slate-600 dark:text-slate-300 w-28 sm:w-40 truncate font-medium svelte-m6227o"> </span> <div class="flex-1 h-6 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden relative svelte-m6227o"><div></div> <span> </span></div> <span class="text-[10px] text-slate-400 dark:text-slate-500 w-8 text-right font-medium svelte-m6227o"> </span></div>'),oa=x('<div class="card p-4 sm:p-5 stagger-item svelte-m6227o" style="animation-delay: 320ms"><div class="flex items-center gap-2 mb-3 svelte-m6227o"><!> <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider svelte-m6227o">Distribución por asignatura</h3> <span class="text-[10px] text-slate-400 dark:text-slate-500 ml-auto svelte-m6227o">Top 5</span></div> <div class="space-y-2.5 svelte-m6227o"></div></div>'),ia=x('<div class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/50 dark:to-orange-950/50 border border-amber-200/60 dark:border-amber-800/60 stagger-item svelte-m6227o" style="animation-delay: 400ms"><!> <p class="text-xs text-amber-700 dark:text-amber-300 svelte-m6227o"><span class="font-semibold svelte-m6227o">Próxima fecha límite:</span> <span class="text-amber-500 dark:text-amber-400 ml-1 svelte-m6227o"> </span></p></div>'),na=x('<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 svelte-m6227o"><div class="stat-card group stagger-item svelte-m6227o" style="animation-delay: 0ms"><div class="flex items-center justify-between mb-3 svelte-m6227o"><div class="w-10 h-10 rounded-xl bg-primary-100 dark:bg-primary-900/50 flex items-center justify-center transition-transform group-hover:scale-110 svelte-m6227o"><!></div> <!></div> <p class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 tracking-tight svelte-m6227o"> </p> <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 svelte-m6227o">Estudiantes</p></div> <div class="stat-card group stagger-item svelte-m6227o" style="animation-delay: 80ms"><div class="flex items-center justify-between mb-3 svelte-m6227o"><div class="w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-900/50 flex items-center justify-center transition-transform group-hover:scale-110 svelte-m6227o"><!></div> <span class="text-[10px] font-medium text-slate-400 dark:text-slate-500 svelte-m6227o"> </span></div> <p class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 tracking-tight svelte-m6227o"> </p> <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 svelte-m6227o">Planes de mejoramiento</p></div> <div class="stat-card group stagger-item svelte-m6227o" style="animation-delay: 160ms"><div class="flex items-center justify-between mb-3 svelte-m6227o"><div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center transition-transform group-hover:scale-110 svelte-m6227o"><!></div> <!></div> <p class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 tracking-tight svelte-m6227o"> </p> <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 svelte-m6227o">Asignaturas</p></div> <div class="stat-card group stagger-item svelte-m6227o" style="animation-delay: 240ms"><div class="flex items-center justify-between mb-3 svelte-m6227o"><div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center transition-transform group-hover:scale-110 svelte-m6227o"><!></div> <span class="text-[10px] font-medium text-slate-400 dark:text-slate-500 svelte-m6227o"> </span></div> <p class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 tracking-tight svelte-m6227o"> </p> <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 svelte-m6227o">Docentes</p></div></div> <!> <!>',1),da=x('<button class="mt-4 inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/30 hover:bg-primary-100 dark:hover:bg-primary-900/50 rounded-xl transition-colors svelte-m6227o"><!> Limpiar filtros</button>'),ca=x('<div class="card p-12 text-center stagger-item svelte-m6227o"><div class="mx-auto mb-6 w-48 h-48 svelte-m6227o"><svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full svelte-m6227o"><circle cx="100" cy="100" r="90" class="fill-slate-100 dark:fill-slate-800 svelte-m6227o"></circle><rect x="45" y="75" width="110" height="80" rx="8" class="fill-slate-200 dark:fill-slate-700 svelte-m6227o"></rect><path d="M45 83C45 78.5817 48.5817 75 53 75H80L90 65H137C141.418 65 145 68.5817 145 73V75H45V83Z" class="fill-slate-300 dark:fill-slate-600 svelte-m6227o"></path><rect x="40" y="85" width="120" height="70" rx="6" stroke-width="1.5"></rect><circle cx="100" cy="115" r="18" class="stroke-primary-400 dark:stroke-primary-500 svelte-m6227o" stroke-width="3" fill="none"></circle><line x1="113" y1="128" x2="125" y2="140" class="stroke-primary-400 dark:stroke-primary-500 svelte-m6227o" stroke-width="3" stroke-linecap="round"></line><text x="95" y="122" class="fill-primary-400 dark:fill-primary-500 svelte-m6227o" font-size="20" font-weight="bold" font-family="sans-serif">?</text><circle cx="50" cy="55" r="3" class="fill-primary-200 dark:fill-primary-700 svelte-m6227o" opacity="0.6"></circle><circle cx="160" cy="60" r="4" class="fill-violet-200 dark:fill-violet-700 svelte-m6227o" opacity="0.6"></circle><circle cx="35" cy="150" r="3" class="fill-emerald-200 dark:fill-emerald-700 svelte-m6227o" opacity="0.6"></circle><circle cx="170" cy="145" r="5" class="fill-amber-200 dark:fill-amber-700 svelte-m6227o" opacity="0.5"></circle></svg></div> <p class="text-slate-600 dark:text-slate-300 font-semibold text-lg mb-1 svelte-m6227o">No se encontraron registros</p> <p class="text-sm text-slate-400 dark:text-slate-500 max-w-sm mx-auto svelte-m6227o"><!></p> <!></div>'),va=x('<div class="alert-badge svelte-m6227o"><span class="alert-ring svelte-m6227o"></span> <span class="alert-ring alert-ring-2 svelte-m6227o"></span> <span class="alert-icon-wrap svelte-m6227o"><!></span> <span class="alert-label svelte-m6227o"> </span></div>'),pa=x('<span><span></span> <span class="truncate max-w-[120px] svelte-m6227o"> </span></span>'),ma=x(`<button><!> <div class="flex items-start gap-3 mb-3 svelte-m6227o"><div> </div> <div class="flex-1 min-w-0 svelte-m6227o"><h3 class="text-[13px] font-bold text-slate-800 dark:text-slate-100 leading-tight line-clamp-2 svelte-m6227o"> </h3> <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5 flex items-center gap-1 svelte-m6227o"><!> </p></div></div> <div class="flex flex-wrap gap-1.5 mb-3 svelte-m6227o"></div> <div class="mt-auto flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-700 svelte-m6227o"><div class="flex items-center gap-1.5 svelte-m6227o"><!> <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 svelte-m6227o"> </span></div> <span class="text-[10px] text-slate-400 dark:text-slate-500 flex items-center gap-1 svelte-m6227o"><!> </span></div> <div class="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none flex items-center justify-center bg-slate-900/5 dark:bg-slate-100/5 svelte-m6227o"><span class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm text-xs font-semibold text-primary-600 dark:text-primary-400 px-3 py-1.5 rounded-full shadow-lg svelte-m6227o"><!> Ver
              detalle</span></div></button>`),xa=x('<div class="flex items-center justify-between px-1 svelte-m6227o"><p class="text-xs text-slate-400 dark:text-slate-500 svelte-m6227o">Mostrando <span class="font-semibold text-slate-600 dark:text-slate-300 svelte-m6227o"> </span> <span class="font-semibold text-slate-600 dark:text-slate-300 svelte-m6227o"> </span> planes</p></div> <div class="student-grid svelte-m6227o"></div>',1),ua=x('<div class="rounded-xl border border-slate-100 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 p-4 space-y-3 svelte-m6227o"><div class="flex flex-wrap items-center gap-x-3 gap-y-1.5 svelte-m6227o"><span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 svelte-m6227o"><!> </span> <span class="inline-flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 svelte-m6227o"><!> </span> <span><span></span> </span></div> <div class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line svelte-m6227o"> </div> <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-slate-400 dark:text-slate-500 svelte-m6227o"><span class="flex items-center gap-1 svelte-m6227o"><!> Límite: <span class="font-medium text-slate-500 dark:text-slate-400 svelte-m6227o"> </span></span> <span class="flex items-center gap-1 svelte-m6227o"><!> </span></div></div>'),fa=x('<div class="svelte-m6227o"><div class="modal-backdrop svelte-m6227o" role="dialog" aria-modal="true" aria-label="Detalle del estudiante" tabindex="-1"><div class="modal-content svelte-m6227o"><div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700 shrink-0 svelte-m6227o"><div class="flex items-center gap-3 svelte-m6227o"><div> </div> <div class="svelte-m6227o"><h3 class="text-base font-bold text-slate-800 dark:text-slate-100 svelte-m6227o"> </h3> <p class="text-xs text-slate-400 dark:text-slate-500 flex items-center gap-1.5 svelte-m6227o"><!> <span class="text-slate-200 dark:text-slate-600 svelte-m6227o">|</span> </p></div></div> <button class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition-colors text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 svelte-m6227o" aria-label="Cerrar"><!></button></div> <div class="p-5 space-y-3 overflow-y-auto modal-body svelte-m6227o"></div> <div class="flex justify-end gap-2 p-5 border-t border-slate-100 dark:border-slate-700 shrink-0 svelte-m6227o"><button class="px-4 py-2 text-xs font-medium text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors svelte-m6227o">Cerrar</button> <button class="btn-danger svelte-m6227o"><!> Generar PDF</button></div></div></div></div>'),ga=x('<div class="space-y-5 svelte-m6227o"><div class="card p-5 sm:p-6 svelte-m6227o"><div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5 svelte-m6227o"><div class="flex items-center gap-2 svelte-m6227o"><div class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900/50 flex items-center justify-center svelte-m6227o"><!></div> <h2 class="text-base font-bold text-slate-800 dark:text-slate-100 svelte-m6227o">Registros</h2></div> <div class="flex items-center gap-2 svelte-m6227o"><!> <!></div></div> <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 svelte-m6227o"><div class="relative svelte-m6227o"><!> <input type="text" placeholder="Buscar en todos los campos..." class="field-input pl-10 svelte-m6227o"/></div> <select class="field-input svelte-m6227o"><option class="svelte-m6227o">Todos los grupos</option><!></select> <select class="field-input svelte-m6227o"><option class="svelte-m6227o">Todos los docentes</option><!></select></div></div> <!> <!> <!></div> <!>',1);function $a(p,be){Gt(be,!0);let B=ie(It([])),q=ie(!0),Y=ie(""),j=ie(""),P=ie(""),G=ie(""),E=ie(null),ot=$(()=>[...new Set(e(B).map(t=>t.grupo))].sort((t,s)=>Number(t)-Number(s))),it=$(()=>[...new Set(e(B).map(t=>t.docente))].sort()),b=$(()=>e(B).filter(t=>{const s=!e(j)||Object.values(t).some(m=>String(m).toLowerCase().includes(e(j).toLowerCase())),l=!e(P)||t.grupo===e(P),i=!e(G)||t.docente===e(G);return s&&l&&i})),Q=$(()=>Object.values(e(b).reduce((t,s)=>(t[s.estudiante]||(t[s.estudiante]={estudiante:s.estudiante,grupo:s.grupo,records:[]}),t[s.estudiante].records.push(s),t),{})).sort((t,s)=>t.estudiante.localeCompare(s.estudiante))),S=$(()=>({totalEstudiantes:e(Q).length,totalPlanes:e(b).length,totalAsignaturas:[...new Set(e(b).map(t=>t.asignatura))].length,totalDocentes:[...new Set(e(b).map(t=>t.docente))].length,totalGrupos:[...new Set(e(b).map(t=>t.grupo))].length,promedioPlanesPorEstudiante:e(Q).length>0?(e(b).length/e(Q).length).toFixed(1):"0",asignaturaTop:(()=>{const t={};e(b).forEach(l=>{t[l.asignatura]=(t[l.asignatura]||0)+1});const s=Object.entries(t).sort((l,i)=>i[1]-l[1]);return s[0]?{nombre:s[0][0],cantidad:s[0][1]}:null})(),fechaProxima:(()=>{const t=new Date;return t.setHours(0,0,0,0),e(b).map(l=>new Date(l.fecha_limite)).filter(l=>l>=t).sort((l,i)=>l-i)[0]||null})()})),Fe=$(()=>()=>{const t={};e(b).forEach(l=>{t[l.asignatura]=(t[l.asignatura]||0)+1});const s=e(b).length||1;return Object.entries(t).sort((l,i)=>i[1]-l[1]).slice(0,5).map(([l,i])=>({nombre:l,cantidad:i,porcentaje:Math.round(i/s*100)}))});function Le(){C(j,""),C(P,""),C(G,"")}let he=$(()=>e(j)||e(P)||e(G)),nt=$(()=>()=>{const t=[];return e(j)&&t.push({key:"search",label:`"${e(j)}"`,icon:"mdi:magnify",clear:()=>C(j,"")}),e(P)&&t.push({key:"grupo",label:`Grupo ${e(P)}`,icon:"mdi:google-classroom",clear:()=>C(P,"")}),e(G)&&t.push({key:"docente",label:e(G),icon:"mdi:account-tie",clear:()=>C(G,"")}),t});Nt(async()=>{await Promise.allSettled([(async()=>{try{const t=await fetch("https://app.iedeoccidente.com/gs/getgsartirec.php");C(B,await t.json(),!0)}catch(t){console.error("Error fetching data:",t)}finally{C(q,!1)}})(),(async()=>{try{const s=await(await fetch(Zt)).blob(),l=new FileReader;l.onloadend=()=>{C(Y,l.result,!0)},l.readAsDataURL(s)}catch{}})()])});function ze(t){try{return new Date(t).toLocaleDateString("es-CO",{year:"numeric",month:"long",day:"numeric"})}catch{return t}}function Ge(t){try{return new Date(t).toLocaleDateString("es-CO",{month:"short",day:"numeric"})}catch{return t}}function je(t){const s=new Date;s.setHours(0,0,0,0);const l=new Date(t);return l.setHours(0,0,0,0),Math.ceil((l-s)/(1e3*60*60*24))}function Ie(t){const s=je(t);return s<0?{label:"Vencido",color:"text-rose-600 bg-rose-50 dark:bg-rose-950 border-rose-200 dark:border-rose-800",icon:"mdi:alert-circle",dot:"bg-rose-500"}:s<=3?{label:`${s}d restante${s!==1?"s":""}`,color:"text-amber-600 bg-amber-50 dark:bg-amber-950 border-amber-200 dark:border-amber-800",icon:"mdi:clock-alert",dot:"bg-amber-500"}:s<=7?{label:`${s} días`,color:"text-blue-600 bg-blue-50 dark:bg-blue-950 border-blue-200 dark:border-blue-800",icon:"mdi:calendar-clock",dot:"bg-blue-500"}:{label:`${s} días`,color:"text-emerald-600 bg-emerald-50 dark:bg-emerald-950 border-emerald-200 dark:border-emerald-800",icon:"mdi:calendar-check",dot:"bg-emerald-500"}}function dt(t){C(E,t,!0)}function _e(){C(E,null)}function ct(t){return document.body.appendChild(t),{destroy(){t.parentNode&&t.parentNode.removeChild(t)}}}function vt(t){t.key==="Escape"&&e(E)&&_e()}const Ne=["from-primary-400 to-primary-600","from-violet-400 to-violet-600","from-emerald-400 to-emerald-600","from-amber-400 to-amber-600","from-rose-400 to-rose-600","from-cyan-400 to-cyan-600","from-fuchsia-400 to-fuchsia-600","from-teal-400 to-teal-600"];function Me(t){let s=0;for(let l=0;l<t.length;l++)s=t.charCodeAt(l)+((s<<5)-s);return Ne[Math.abs(s)%Ne.length]}function Oe(t){return t.split(" ").slice(0,2).map(s=>s[0]).join("").toUpperCase()}function pt(t){const s=e(b).filter(l=>l.estudiante===t.estudiante);lt(s,e(Y))}function mt(){const t=[...e(b)].sort((s,l)=>s.estudiante.localeCompare(l.estudiante));lt(t,e(Y))}const He=["bg-primary-500","bg-violet-500","bg-emerald-500","bg-amber-500","bg-rose-500"];var Re=ga();Mt("keydown",Ot,vt);var Ue=Se(Re),qe=a(Ue),Ve=a(qe),Be=a(Ve),xt=a(Be),ut=a(xt);d(ut,{icon:"mdi:format-list-bulleted",class:"text-primary-600 dark:text-primary-400 text-lg"});var ft=r(Be,2),Ze=a(ft);{var gt=t=>{var s=Yt(),l=a(s);d(l,{icon:"mdi:filter-remove",class:"text-sm"}),z("click",s,Le),v(t,s)};T(Ze,t=>{e(he)&&t(gt)})}var bt=r(Ze,2);{var ht=t=>{var s=Qt(),l=a(s);d(l,{icon:"mdi:file-pdf-box",class:"text-sm"});var i=r(l);w(()=>o(i,` PDF Grupo ${e(P)??""}`)),z("click",s,mt),v(t,s)};T(bt,t=>{e(P)&&e(b).length>0&&t(ht)})}var _t=r(Ve,2),Je=a(_t),Ke=a(Je);d(Ke,{icon:"mdi:magnify",class:"absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base"});var yt=r(Ke,2),De=r(Je,2),Ce=a(De);Ce.value=Ce.__value="";var kt=r(Ce);ne(kt,17,()=>e(ot),ge,(t,s)=>{var l=Xt(),i=a(l),m={};w(()=>{o(i,`Grupo ${e(s)??""}`),m!==(m=e(s))&&(l.value=(l.__value=e(s))??"")}),v(t,l)});var We=r(De,2),Pe=a(We);Pe.value=Pe.__value="";var wt=r(Pe);ne(wt,17,()=>e(it),ge,(t,s)=>{var l=ea(),i=a(l),m={};w(()=>{o(i,e(s)),m!==(m=e(s))&&(l.value=(l.__value=e(s))??"")}),v(t,l)});var Ye=r(qe,2);{var $t=t=>{var s=aa(),l=a(s),i=a(l);d(i,{icon:"mdi:filter-variant",class:"text-primary-500 dark:text-primary-400 text-sm shrink-0"});var m=r(i,4);ne(m,17,()=>e(nt)(),I=>I.key,(I,N)=>{var M=ta(),O=a(M);d(O,{get icon(){return e(N).icon},class:"text-xs"});var n=r(O,2),g=a(n),h=r(n,2),V=a(h);d(V,{icon:"mdi:close",class:"text-[10px]"}),w(()=>o(g,e(N).label)),z("click",h,function(...Z){e(N).clear?.apply(this,Z)}),v(I,M)});var A=r(m,2),F=a(A);w(()=>o(F,`${e(b).length??""} resultado${e(b).length!==1?"s":""}`)),v(t,s)};T(Ye,t=>{e(he)&&!e(q)&&t($t)})}var Qe=r(Ye,2);{var jt=t=>{Jt(t,{type:"cards"})},Dt=t=>{var s=na(),l=Se(s),i=a(l),m=a(i),A=a(m),F=a(A);d(F,{icon:"mdi:account-group",class:"text-primary-600 dark:text-primary-400 text-xl"});var I=r(A,2);{var N=u=>{var c=sa();v(u,c)};T(I,u=>{e(he)&&u(N)})}var M=r(m,2),O=a(M),n=r(i,2),g=a(n),h=a(g),V=a(h);d(V,{icon:"mdi:text-box-multiple",class:"text-violet-600 dark:text-violet-400 text-xl"});var Z=r(h,2),ye=a(Z),H=r(g,2),de=a(H),X=r(n,2),J=a(X),K=a(J),y=a(K);d(y,{icon:"mdi:book-open-variant",class:"text-emerald-600 dark:text-emerald-400 text-xl"});var L=r(K,2);{var W=u=>{var c=ra(),f=a(c);w(()=>{Ae(c,"title",`Más frecuente: ${e(S).asignaturaTop.nombre??""}`),o(f,`Top: ${e(S).asignaturaTop.cantidad??""}`)}),v(u,c)};T(L,u=>{e(S).asignaturaTop&&u(W)})}var ce=r(J,2),ve=a(ce),ee=r(X,2),te=a(ee),ae=a(te),se=a(ae);d(se,{icon:"mdi:account-tie",class:"text-amber-600 dark:text-amber-400 text-xl"});var re=r(ae,2),pe=a(re),me=r(te,2),xe=a(me),ue=r(l,2);{var le=u=>{var c=oa(),f=a(c),_=a(f);d(_,{icon:"mdi:chart-bar",class:"text-slate-400 text-base"});var k=r(f,2);ne(k,21,()=>e(Fe)(),ge,(R,D,oe)=>{var $e=la(),Te=a($e),At=a(Te),Xe=r(Te,2),Ee=a(Xe),et=r(Ee,2),Ft=a(et),Lt=r(Xe,2),zt=a(Lt);w(()=>{Ae(Te,"title",e(D).nombre),o(At,e(D).nombre),U(Ee,1,`h-full rounded-full transition-all duration-700 ease-out ${He[oe%He.length]??""}`,"svelte-m6227o"),at(Ee,`width: ${e(D).porcentaje??""}%`),U(et,1,`absolute inset-0 flex items-center justify-end pr-2 text-[10px] font-bold ${e(D).porcentaje>50?"text-white":"text-slate-500 dark:text-slate-300"}`,"svelte-m6227o"),o(Ft,e(D).cantidad),o(zt,`${e(D).porcentaje??""}%`)}),v(R,$e)}),v(u,c)},ke=$(()=>e(Fe)().length>0);T(ue,u=>{e(ke)&&u(le)})}var we=r(ue,2);{var fe=u=>{var c=ia(),f=a(c);d(f,{icon:"mdi:bell-ring-outline",class:"text-amber-500 text-lg"});var _=r(f,2),k=r(a(_)),R=r(k),D=a(R);w((oe,$e)=>{o(k,` ${oe??""} `),o(D,`(${$e??""} días)`)},[()=>ze(e(S).fechaProxima.toISOString()),()=>je(e(S).fechaProxima.toISOString())]),v(u,c)};T(we,u=>{e(S).fechaProxima&&u(fe)})}w(()=>{o(O,e(S).totalEstudiantes),o(ye,`${e(S).promedioPlanesPorEstudiante??""}/est.`),o(de,e(S).totalPlanes),o(ve,e(S).totalAsignaturas),o(pe,`${e(S).totalGrupos??""} grupos`),o(xe,e(S).totalDocentes)}),v(t,s)};T(Qe,t=>{e(q)?t(jt):e(B).length>0&&t(Dt,1)})}var Ct=r(Qe,2);{var Pt=t=>{var s=ca(),l=a(s),i=a(l),m=r(a(i),3);U(m,0,"fill-white dark:fill-slate-700 svelte-m6227o",null,{},{"stroke-slate-200":!0});var A=r(l,4),F=a(A);{var I=n=>{var g=st(`No hay resultados para los filtros actuales. Intenta ajustar los
          criterios de busqueda.`);v(n,g)},N=n=>{var g=st(`Aun no hay planes de mejoramiento registrados. Usa el formulario para
          crear el primero.`);v(n,g)};T(F,n=>{e(he)?n(I):n(N,!1)})}var M=r(A,2);{var O=n=>{var g=da(),h=a(g);d(h,{icon:"mdi:filter-remove",class:"text-base"}),z("click",g,Le),v(n,g)};T(M,n=>{e(he)&&n(O)})}v(t,s)},Tt=t=>{var s=xa(),l=Se(s),i=a(l),m=r(a(i)),A=a(m),F=r(m),I=r(F),N=a(I),M=r(l,2);ne(M,21,()=>e(Q),ge,(O,n,g)=>{const h=$(()=>e(n).records.length),V=$(()=>e(n).records.reduce((c,f)=>{const _=je(f.fecha_limite);return _<c?_:c},1/0)),Z=$(()=>e(V)<0?"rose":e(V)<=3?"amber":e(V)<=7?"blue":"emerald"),ye=$(()=>`border-t-${e(Z)}-500`);var H=ma(),de=a(H);{var X=c=>{var f=va(),_=r(a(f),4),k=a(_);d(k,{icon:"mdi:alert",class:"text-[13px] relative z-10"});var R=r(_,2),D=a(R);w(()=>{Ae(f,"title",`Este estudiante tiene ${e(h)??""} planes de mejoramiento`),o(D,`${e(h)??""} planes`)}),v(c,f)};T(de,c=>{e(h)>2&&c(X)})}var J=r(de,2),K=a(J),y=a(K),L=r(K,2),W=a(L),ce=a(W),ve=r(W,2),ee=a(ve);d(ee,{icon:"mdi:google-classroom",class:"text-xs"});var te=r(ee),ae=r(J,2);ne(ae,21,()=>e(n).records,ge,(c,f)=>{const _=$(()=>Ie(e(f).fecha_limite));var k=pa(),R=a(k),D=r(R,2),oe=a(D);w(()=>{U(k,1,`inline-flex items-center gap-1 text-[10px] font-medium px-2 py-1 rounded-lg border ${e(_).color??""}`,"svelte-m6227o"),U(R,1,`w-1.5 h-1.5 rounded-full ${e(_).dot??""} shrink-0`,"svelte-m6227o"),o(oe,e(f).asignatura)}),v(c,k)});var se=r(ae,2),re=a(se),pe=a(re);d(pe,{icon:"mdi:text-box-multiple",class:"text-xs text-slate-400 dark:text-slate-500"});var me=r(pe,2),xe=a(me),ue=r(re,2),le=a(ue);d(le,{icon:"mdi:calendar-clock",class:"text-[11px]"});var ke=r(le),we=r(se,2),fe=a(we),u=a(fe);d(u,{icon:"mdi:eye-outline",class:"text-sm inline -mt-0.5"}),w((c,f,_,k)=>{U(H,1,`student-card card border-t-4 ${e(ye)??""} text-left cursor-pointer group stagger-item`,"svelte-m6227o"),at(H,`animation-delay: ${c??""}ms`),U(K,1,`w-11 h-11 rounded-xl bg-gradient-to-br ${f??""} flex items-center justify-center text-white font-bold text-xs shadow-md shrink-0`,"svelte-m6227o"),o(y,_),o(ce,e(n).estudiante),o(te,` Grupo ${e(n).grupo??""}`),o(xe,`${e(h)??""}
                ${e(h)===1?"plan":"planes"}`),o(ke,` ${k??""}`)},[()=>Math.min(g*60,600),()=>Me(e(n).estudiante),()=>Oe(e(n).estudiante),()=>Ge(e(n).records[0].fecha_limite)]),z("click",H,()=>dt(e(n))),v(O,H)}),w(()=>{o(A,e(Q).length),o(F,` estudiante${e(Q).length!==1?"s":""} con `),o(N,e(b).length)}),v(t,s)};T(Ct,t=>{!e(q)&&e(Q).length===0?t(Pt):e(q)||t(Tt,1)})}var Et=r(Ue,2);{var St=t=>{var s=fa(),l=a(s),i=a(l),m=a(i),A=a(m),F=a(A),I=a(F),N=r(F,2),M=a(N),O=a(M),n=r(M,2),g=a(n);d(g,{icon:"mdi:google-classroom",class:"text-sm"});var h=r(g),V=r(h,2),Z=r(A,2),ye=a(Z);d(ye,{icon:"mdi:close",class:"text-lg"});var H=r(m,2);ne(H,21,()=>e(E).records,ge,(y,L)=>{const W=$(()=>Ie(e(L).fecha_limite));var ce=ua(),ve=a(ce),ee=a(ve),te=a(ee);d(te,{icon:"mdi:book-open-variant",class:"text-sm text-primary-500"});var ae=r(te),se=r(ee,2),re=a(se);d(re,{icon:"mdi:account-tie",class:"text-sm text-slate-400"});var pe=r(re),me=r(se,2),xe=a(me),ue=r(xe),le=r(ve,2),ke=a(le),we=r(le,2),fe=a(we),u=a(fe);d(u,{icon:"mdi:calendar-clock",class:"text-xs"});var c=r(u,2),f=a(c),_=r(fe,2),k=a(_);d(k,{icon:"mdi:clock-outline",class:"text-xs"});var R=r(k);w((D,oe)=>{o(ae,` ${e(L).asignatura??""}`),o(pe,` ${e(L).docente??""}`),U(me,1,`inline-flex items-center gap-1 text-[11px] font-medium border px-2 py-0.5 rounded-full ${e(W).color??""}`,"svelte-m6227o"),U(xe,1,`w-1.5 h-1.5 rounded-full ${e(W).dot??""}`,"svelte-m6227o"),o(ue,` ${e(W).label??""}`),o(ke,e(L).plan),o(f,D),o(R,` Registro: ${oe??""}`)},[()=>ze(e(L).fecha_limite),()=>Ge(e(L).fecha_registro)]),v(y,ce)});var de=r(H,2),X=a(de),J=r(X,2),K=a(J);d(K,{icon:"mdi:file-pdf-box",class:"text-sm"}),Ut(s,y=>ct?.(y)),w((y,L)=>{U(F,1,`w-12 h-12 rounded-xl bg-gradient-to-br ${y??""} flex items-center justify-center text-white font-bold text-sm shadow-md shrink-0`,"svelte-m6227o"),o(I,L),o(O,e(E).estudiante),o(h,` Grupo ${e(E).grupo??""} `),o(V,` ${e(E).records.length??""}
                ${e(E).records.length===1?"plan":"planes"}`)},[()=>Me(e(E).estudiante),()=>Oe(e(E).estudiante)]),z("click",l,_e),z("keydown",l,y=>y.key==="Escape"&&_e()),z("click",i,y=>y.stopPropagation()),z("keydown",i,y=>y.stopPropagation()),z("click",Z,_e),z("click",X,_e),z("click",J,()=>pt(e(E).records[0])),v(t,s)};T(Et,t=>{e(E)&&t(St)})}Ht(yt,()=>e(j),t=>C(j,t)),tt(De,()=>e(P),t=>C(P,t)),tt(We,()=>e(G),t=>C(G,t)),v(p,Re),Rt()}qt(["click","keydown"]);export{$a as default};
