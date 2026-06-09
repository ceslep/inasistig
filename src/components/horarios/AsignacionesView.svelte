<script lang="ts">
  import type { CoberturaSugerida, SugerenciaGrupo, HorarioDocente, CoberturaHistorica } from "../../lib/coberturaUtils";
  import { formatoDia, formatoHora, ROLES_SIN_LIMITE, esHoraLibrePorGrupoAusente, normalizarDocente, MARCADOR_LIBRE } from "../../lib/coberturaUtils";
  import horariosData from "../../lib/horarios.json";
  import Swal from "sweetalert2";
  import { coberturaSheetsService } from "../../services/coberturaSheetsService";

  let {
    diaSeleccionado,
    fechaSeleccionada,
    coberturasSugeridas,
    gruposSugeridosAAusentar,
    coberturasHistoricas = [],
    gruposAusentes = [],
    loading,
    onToggle,
    onCambiarDocenteCubre,
    onAgregarGrupoAusente,
    onGuardar,
    onBack,
    onOpenGruposModal,
    onLiberarGrupoDesdeHora,
    onAprobarTodo,
    horariosEfectivos,
  }: {
    diaSeleccionado: string;
    fechaSeleccionada: string;
    coberturasSugeridas: CoberturaSugerida[];
    gruposSugeridosAAusentar: SugerenciaGrupo[];
    coberturasHistoricas?: CoberturaHistorica[];
    gruposAusentes?: { grupo: string; horaInicio: number }[];
    loading: boolean;
    onToggle: (index: number) => void;
    onCambiarDocenteCubre: (index: number, docente: string) => void;
    onAgregarGrupoAusente: (grupo: string) => void;
    onGuardar: () => void;
    onBack: () => void;
    onOpenGruposModal?: () => void;
    onLiberarGrupoDesdeHora?: (grupo: string, hora: number, docenteAusente: string) => void;
    onAprobarTodo: () => void;
    horariosEfectivos?: HorarioDocente[];
  } = $props();

  async function exportarJSON() {
    const today = new Date();
    const currentYear = today.getFullYear();
    const currentMonth = today.getMonth();
    let selectedYear = currentYear;
    let selectedMonth = currentMonth;
    let selectedDay = today.getDate();

    const MESES = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    const DIAS_SEMANA = ['L','M','X','J','V','S','D'];

    function buildCalendar(month: number, year: number, selDay: number) {
      const firstDay = new Date(year, month, 1);
      const lastDay = new Date(year, month + 1, 0);
      const startDay = (firstDay.getDay() + 6) % 7;
      const totalDays = lastDay.getDate();

      let html = `
        <div style="font-family: inherit; max-width: 320px; margin: 0 auto;">
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <button id="cal-prev" type="button" style="background: rgb(var(--bg-secondary)); border: none; border-radius: 8px; width: 36px; height: 36px; cursor: pointer; color: rgb(var(--text-primary)); font-size: 18px; display: flex; align-items: center; justify-content: center;">‹</button>
            <span id="cal-title" style="font-weight: 700; font-size: 16px; color: rgb(var(--text-primary));">${MESES[month]} ${year}</span>
            <button id="cal-next" type="button" style="background: rgb(var(--bg-secondary)); border: none; border-radius: 8px; width: 36px; height: 36px; cursor: pointer; color: rgb(var(--text-primary)); font-size: 18px; display: flex; align-items: center; justify-content: center;">›</button>
          </div>
          <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; margin-bottom: 8px;">
            ${DIAS_SEMANA.map(d => `<div style="text-align: center; font-size: 11px; font-weight: 700; color: rgb(var(--text-muted)); padding: 4px 0;">${d}</div>`).join('')}
          </div>
          <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px;" id="cal-grid">
      `;

      let dayCount = 1;
      for (let i = 0; i < 6; i++) {
        for (let j = 0; j < 7; j++) {
          const idx = i * 7 + j;
          if (idx < startDay || dayCount > totalDays) {
            html += `<div></div>`;
          } else {
            const isSelected = dayCount === selDay;
            const isToday = dayCount === today.getDate() && month === today.getMonth() && year === today.getFullYear();
            const bgColor = isSelected ? 'rgb(var(--accent-primary))' : isToday ? 'rgba(99,102,241,0.2)' : 'rgb(var(--bg-secondary))';
            const textColor = isSelected ? 'white' : 'rgb(var(--text-primary))';
            html += `<button type="button" class="cal-day" data-day="${dayCount}" style="width: 100%; aspect-ratio: 1; border: none; border-radius: 8px; background: ${bgColor}; color: ${textColor}; font-size: 14px; font-weight: ${isToday && !isSelected ? '600' : '400'}; cursor: pointer; transition: all 0.15s;">${dayCount}</button>`;
            dayCount++;
          }
        }
      }
      html += `</div></div>`;
      return html;
    }

    function getSelectedDateStr() {
      const m = String(selectedMonth + 1).padStart(2, '0');
      const d = String(selectedDay).padStart(2, '0');
      return `${selectedYear}-${m}-${d}`;
    }

    const result = await Swal.fire({
      title: 'Exportar Coberturas a JSON',
      html: buildCalendar(selectedMonth, selectedYear, selectedDay),
      confirmButtonText: 'Exportar',
      confirmButtonColor: '#6366f1',
      showCancelButton: true,
      cancelButtonText: 'Cancelar',
      cancelButtonColor: '#ef4444',
      background: 'rgb(var(--bg-primary))',
      color: 'rgb(var(--text-primary))',
      didOpen: () => {
        const popup = Swal.getPopup()!;
        popup.addEventListener('click', (e: Event) => {
          const target = e.target as HTMLElement;
          if (target.id === 'cal-prev') {
            selectedMonth--;
            if (selectedMonth < 0) { selectedMonth = 11; selectedYear--; }
            Swal.getHtmlContainer()!.innerHTML = buildCalendar(selectedMonth, selectedYear, selectedDay);
          } else if (target.id === 'cal-next') {
            selectedMonth++;
            if (selectedMonth > 11) { selectedMonth = 0; selectedYear++; }
            Swal.getHtmlContainer()!.innerHTML = buildCalendar(selectedMonth, selectedYear, selectedDay);
          } else if (target.classList.contains('cal-day')) {
            selectedDay = parseInt(target.getAttribute('data-day') || '1');
            Swal.getHtmlContainer()!.innerHTML = buildCalendar(selectedMonth, selectedYear, selectedDay);
          }
        });
      },
      preConfirm: () => getSelectedDateStr()
    });

    if (result.isConfirmed && result.value) {
      const fechaExport = result.value as string;

      let data: any;
      let source: 'gsheet' | 'sugerido' = 'sugerido';

      try {
        Swal.fire({
          title: 'Consultando...',
          text: 'Buscando coberturas guardadas en Google Sheets...',
          background: 'rgb(var(--bg-primary))',
          color: 'rgb(var(--text-primary))',
          showConfirmButton: false,
          didOpen: () => Swal.showLoading()
        });

        const todasCoberturas = await coberturaSheetsService.getCoberturas();
        const coberturasFecha = todasCoberturas.filter(c => c.fecha === fechaExport);

        Swal.close();

        if (coberturasFecha.length > 0) {
          source = 'gsheet';
          data = {
            fecha: fechaExport,
            dia: diaSeleccionado,
            exportado: new Date().toISOString(),
            origen: 'google_sheets',
            totalCoberturas: coberturasFecha.length,
            coberturas: coberturasFecha.map(c => ({
              hora: c.hora,
              ausente: c.docente_ausente,
              grupoAusente: c.grupo_ausente,
              cubre: c.docente_cubre,
              grupoACubrir: c.grupo_a_cubrir,
              aprobado: c.estado === 'aprobado',
              motivo: c.motivo,
              estado: c.estado
            }))
          };
        } else {
          data = {
            fecha: fechaExport,
            dia: diaSeleccionado,
            exportado: new Date().toISOString(),
            origen: 'sugerido',
            totalCoberturas: coberturasSugeridas.length,
            coberturas: coberturasSugeridas.map(c => ({
              hora: c.hora,
              ausente: c.docenteAusente,
              grupoAusente: c.grupoAusente,
              cubre: c.docenteCubre,
              grupoACubrir: c.grupoACubrir,
              aprobado: c.aprobada,
              motivo: c.motivoAusencia,
              violacion: c.violation || null
            }))
          };
        }
      } catch {
        Swal.close();
        data = {
          fecha: fechaExport,
          dia: diaSeleccionado,
          exportado: new Date().toISOString(),
          origen: 'sugerido',
          totalCoberturas: coberturasSugeridas.length,
          coberturas: coberturasSugeridas.map(c => ({
            hora: c.hora,
            ausente: c.docenteAusente,
            grupoAusente: c.grupoAusente,
            cubre: c.docenteCubre,
            grupoACubrir: c.grupoACubrir,
            aprobado: c.aprobada,
            motivo: c.motivoAusencia,
            violacion: c.violation || null
          }))
        };
      }

      const blob = new Blob([JSON.stringify(data, null, 2)], { type: "application/json" });
      const url = URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url;
      a.download = `coberturas_${fechaExport}.json`;
      a.click();
      URL.revokeObjectURL(url);

      await Swal.fire({
        title: 'Exportación exitosa',
        text: source === 'gsheet'
          ? `Se exportaron ${data.totalCoberturas} coberturas guardadas en Google Sheets.`
          : `No había coberturas guardadas para ${fechaExport}. Se exportó la sugerencia actual (${data.totalCoberturas} coberturas).`,
        icon: 'success',
        confirmButtonText: 'OK',
        confirmButtonColor: '#6366f1',
        background: 'rgb(var(--bg-primary))',
        color: 'rgb(var(--text-primary))'
      });
    }
  }

  // Horario con adelantos aplicados (cae al import crudo si no se pasa).
  const horariosVista = $derived((horariosEfectivos ?? (horariosData as HorarioDocente[])));

  // Conteo de ocurrencias por docente en sesión (excluyendo roles sin límite,
  // horas que originalmente eran del grupo liberado, e "IGNORAR").
  const conteoSesion = $derived.by(() => {
    const m = new Map<string, number>();
    for (const c of coberturasSugeridas) {
      const d = normalizarDocente(c.docenteCubre);
      if (!d || d === "IGNORAR") continue;
      if (ROLES_SIN_LIMITE.some((r) => d.includes(r))) continue;
      if (esHoraLibrePorGrupoAusente(d, c.hora, diaSeleccionado, horariosVista, gruposAusentes)) {
        continue;
      }
      m.set(d, (m.get(d) || 0) + 1);
    }
    return m;
  });

  // Conteo histórico mismo día (excluye roles sin límite e "IGNORAR")
  const conteoHistorico = $derived.by(() => {
    const m = new Map<string, number>();
    for (const cp of coberturasHistoricas) {
      if (cp.estado !== "aprobado") continue;
      if (cp.fecha !== fechaSeleccionada) continue;
      const d = normalizarDocente(cp.docente_cubre);
      if (!d || d === "IGNORAR") continue;
      if (ROLES_SIN_LIMITE.some((r) => d.includes(r))) continue;
      m.set(d, (m.get(d) || 0) + 1);
    }
    return m;
  });

  // Estilo para options con histórico/sesión previa (no asignados automáticamente).
  // Retorna inline-style para <option>. Roles sin límite e "IGNORAR" se ignoran.
  // Tope semanal: máx 2 (histórico + sesión). El aviso aparece solo al SUPERAR 2
  // (3er cubrimiento). 1 histórico + 1 sesión = 2 → válido, sin alerta.
  function estiloOptionDocente(docente: string, autoAsignado: string): string {
    const d = normalizarDocente(docente);
    if (!d || d === "IGNORAR") return "";
    if (ROLES_SIN_LIMITE.some((r) => d.includes(r))) return "";
    if (d === normalizarDocente(autoAsignado)) return ""; // el auto-asignado se respeta
    const s = conteoSesion.get(d) || 0;
    const h = conteoHistorico.get(d) || 0;
    // Solo resaltar fuerte cuando agregar a este docente superaría el tope (total ya >= 2).
    if (s + h >= 2) {
      return "background-color: #ef4444; color: #ffffff; font-weight: 700;";
    }
    if (h >= 1) {
      // Tiene 1 histórico esta semana (informativo, naranja).
      return "background-color: #f97316; color: #ffffff; font-weight: 700;";
    }
    if (s >= 1) {
      // Tiene 1 en sesión (informativo, azul).
      return "background-color: #3b82f6; color: #ffffff; font-weight: 700;";
    }
    return "";
  }

  function getIndicadorWarning(docente: string): string {
    const d = normalizarDocente(docente);
    if (!d || d === "IGNORAR") return "";
    if (ROLES_SIN_LIMITE.some((r) => d.includes(r))) return "";
    const s = conteoSesion.get(d) || 0;
    const h = conteoHistorico.get(d) || 0;
    if (s + h >= 2) return "🔴"; // alcanzó el tope (otra cobertura lo superaría)
    if (h >= 1) return "🟠";
    if (s >= 1) return "🔵";
    return "";
  }

  function esDuplicado(docente: string, hora: number): { dup: boolean; sesion: number; historico: number; porGrupoLiberado: boolean } {
    const d = normalizarDocente(docente);
    if (!d || d === "IGNORAR") return { dup: false, sesion: 0, historico: 0, porGrupoLiberado: false };
    if (ROLES_SIN_LIMITE.some((r) => d.includes(r))) return { dup: false, sesion: 0, historico: 0, porGrupoLiberado: false };
    const horaEsLibrePorGrupo = esHoraLibrePorGrupoAusente(d, hora, diaSeleccionado, horariosVista, gruposAusentes);
    const s = conteoSesion.get(d) || 0;
    const h = conteoHistorico.get(d) || 0;
    // Si esta hora es libre por grupo liberado, no marcamos como duplicado real.
    if (horaEsLibrePorGrupo) {
      // Repite pero válido — aviso informativo si hay otros covers reales del mismo docente.
      const repiteReal = s >= 1 || h >= 1;
      return { dup: false, sesion: s, historico: h, porGrupoLiberado: repiteReal };
    }
    // Violación solo al SUPERAR el tope semanal de 2 (histórico + sesión).
    return { dup: s + h > 2, sesion: s, historico: h, porGrupoLiberado: false };
  }

  // ¿La cobertura proviene de una hora libre propietaria del docente que cubre?
  // (su slot en horarios.json a esa hora es "" y NO es por grupo liberado).
  function esCoberturaEnHoraLibre(docente: string, hora: number): boolean {
    const d = normalizarDocente(docente);
    if (!d || d === "IGNORAR") return false;
    if (ROLES_SIN_LIMITE.some((r) => d.includes(r))) return false;
    const horario = horariosVista.find((h) => h.docente === d);
    if (!horario) return false;
    const slot = (horario[diaSeleccionado as keyof HorarioDocente] as string[])?.[hora];
    return slot === "" || slot === undefined;
  }

  // Nombre a mostrar para el docente que cubre, con marcador "@" si la cobertura
  // sale de su hora libre. Si ya viene marcado (persistido), respeta sin duplicar.
  function nombreCubreMostrar(docente: string, hora: number): string {
    if (!docente || docente === "IGNORAR") return docente;
    const base = normalizarDocente(docente);
    if (ROLES_SIN_LIMITE.some((r) => base.includes(r))) return base;
    return esCoberturaEnHoraLibre(base, hora) ? base + MARCADOR_LIBRE : base;
  }

  // Intercepta el cambio manual de docente en el <select>. Si el docente elegido
  // excede el límite (1h/día o 2h/semana — esDuplicado.dup), muestra un modal de
  // aviso fuerte con sus últimas 4 coberturas (fecha desc) y pide confirmación.
  // No cambia la lógica de negocio: solo decide si llamar onCambiarDocenteCubre.
  async function confirmarCambioDocente(index: number, docente: string, hora: number, target: HTMLSelectElement, valorPrevio: string) {
    if (!docente || docente === "IGNORAR" || ROLES_SIN_LIMITE.some((r) => docente.includes(r))) {
      onCambiarDocenteCubre(index, docente);
      return;
    }

    const info = esDuplicado(docente, hora);
    if (!info.dup) {
      onCambiarDocenteCubre(index, docente);
      return;
    }

    const escapar = (t: string) =>
      String(t ?? "").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

    const ultimas = coberturasHistoricas
      .filter((c) => normalizarDocente(c.docente_cubre) === normalizarDocente(docente))
      .sort((a, b) => b.fecha.localeCompare(a.fecha) || b.hora - a.hora)
      .slice(0, 4);

    const filas = ultimas
      .map(
        (c) => `
        <tr>
          <td style="padding:6px; border-bottom:1px solid rgb(var(--border-primary)); white-space:nowrap;">${escapar(c.fecha)}</td>
          <td style="padding:6px; border-bottom:1px solid rgb(var(--border-primary)); text-align:center; font-weight:bold; color:rgb(var(--accent-primary));">${formatoHora(c.hora)}</td>
          <td style="padding:6px; border-bottom:1px solid rgb(var(--border-primary));">${escapar(c.docente_ausente)}</td>
          <td style="padding:6px; border-bottom:1px solid rgb(var(--border-primary)); text-align:center;">${escapar(c.grupo_a_cubrir || c.grupo_ausente || "-")}</td>
        </tr>`
      )
      .join("");

    const detalle = `${info.sesion > 0 ? `${info.sesion} hoy en esta sesión` : ""}${info.sesion > 0 && info.historico > 0 ? " · " : ""}${info.historico > 0 ? `${info.historico}h registradas esta semana` : ""}`;

    const tablaHtml = ultimas.length
      ? `<p style="font-size:13px; color:rgb(var(--text-secondary)); margin:10px 0 6px 0;">Últimas ${ultimas.length} cobertura(s) — de la más reciente a la más antigua:</p>
         <table style="width:100%; font-size:12px; border-collapse:collapse;">
           <thead>
             <tr style="background-color:rgb(var(--bg-secondary));">
               <th style="padding:6px; text-align:left; font-weight:bold; color:rgb(var(--text-secondary));">Fecha</th>
               <th style="padding:6px; text-align:center; font-weight:bold; color:rgb(var(--text-secondary));">Hora</th>
               <th style="padding:6px; text-align:left; font-weight:bold; color:rgb(var(--text-secondary));">Ausente</th>
               <th style="padding:6px; text-align:center; font-weight:bold; color:rgb(var(--text-secondary));">Grupo</th>
             </tr>
           </thead>
           <tbody>${filas}</tbody>
         </table>`
      : `<p style="font-size:13px; color:rgb(var(--text-secondary)); margin:10px 0 0 0;">Sin coberturas en el historial.</p>`;

    const res = await Swal.fire({
      icon: "warning",
      title: "Límite de coberturas",
      html: `<div style="text-align:left;"><p style="margin:0; color:rgb(var(--text-primary));"><strong>${escapar(docente)}</strong> ya supera el límite recomendado (máx 1h/día, 2h/semana).${detalle ? `<br><span style="font-size:13px; color:rgb(var(--text-secondary));">${detalle}.</span>` : ""}</p>${tablaHtml}</div>`,
      showCancelButton: true,
      confirmButtonText: "Sí, asignar de todas formas",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#ef4444",
      width: "560px",
    });

    if (res.isConfirmed) {
      onCambiarDocenteCubre(index, docente);
    } else {
      target.value = valorPrevio;
    }
  }

  let seleccionadas = $state(0);
  let violaciones = $state(0);

  // Paleta contrastada por docente ausente — bg suave + borde fuerte
  const PALETA_AUSENTE: { bg: string; border: string; text: string }[] = [
    { bg: "rgba(59, 130, 246, 0.10)", border: "#3b82f6", text: "#1e40af" },   // azul
    { bg: "rgba(16, 185, 129, 0.10)", border: "#10b981", text: "#065f46" },   // verde
    { bg: "rgba(168, 85, 247, 0.10)", border: "#a855f7", text: "#6b21a8" },   // morado
    { bg: "rgba(249, 115, 22, 0.10)", border: "#f97316", text: "#9a3412" },   // naranja
    { bg: "rgba(236, 72, 153, 0.10)", border: "#ec4899", text: "#9d174d" },   // rosa
    { bg: "rgba(14, 165, 233, 0.10)", border: "#0ea5e9", text: "#075985" },   // cian
    { bg: "rgba(234, 179, 8, 0.10)",  border: "#eab308", text: "#854d0e" },   // amarillo
    { bg: "rgba(20, 184, 166, 0.10)", border: "#14b8a6", text: "#115e59" },   // teal
    { bg: "rgba(244, 63, 94, 0.10)",  border: "#f43f5e", text: "#9f1239" },   // rojo
    { bg: "rgba(132, 204, 22, 0.10)", border: "#84cc16", text: "#3f6212" },   // lima
  ];

  const colorPorAusente = $derived.by(() => {
    const m = new Map<string, { bg: string; border: string; text: string }>();
    const unicos: string[] = [];
    for (const c of coberturasSugeridas) {
      if (!c.docenteAusente) continue;
      if (!unicos.includes(c.docenteAusente)) unicos.push(c.docenteAusente);
    }
    unicos.forEach((doc, idx) => {
      m.set(doc, PALETA_AUSENTE[idx % PALETA_AUSENTE.length]);
    });
    return m;
  });

  const dias = ["lunes", "martes", "miercoles", "jueves", "viernes"] as const;
  const diasAbreviado = ["LUN", "MAR", "MIE", "JUE", "VIE"];

  $effect(() => {
    seleccionadas = coberturasSugeridas.filter((c) => c.aprobada).length;
    violaciones = coberturasSugeridas.filter((c) => c.violation).length;
  });

  function getClaseSlot(contenido: string): { bg: string; text: string; border: string } {
    if (!contenido) return { bg: "bg-white dark:bg-zinc-800", text: "text-zinc-300 dark:text-zinc-500", border: "border-2 border-dashed border-zinc-300 dark:border-zinc-600" };
    if (contenido === "DESC" || contenido === "PEDAG" || contenido === "DEESC") return { bg: "bg-orange-200 dark:bg-orange-800", text: "text-orange-800 dark:text-orange-200", border: "border border-orange-300 dark:border-orange-600" };
    return { bg: "bg-emerald-200 dark:bg-emerald-800", text: "text-emerald-800 dark:text-emerald-200", border: "border border-emerald-300 dark:border-emerald-600" };
  }

  function formatearMateria(contenido: string): string {
    if (!contenido) return "LIBRE";
    if (contenido === "DESC" || contenido === "PEDAG" || contenido === "DEESC") return contenido;
    return contenido.replace(/\n/g, " ");
  }

  function abrirHistorialDocente(nombre: string) {
    if (!nombre) return;
    const escapar = (t: string) =>
      String(t ?? "").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

    const nombreLimpio = normalizarDocente(nombre);
    const delDocente = coberturasHistoricas
      .filter((c) => normalizarDocente(c.docente_cubre) === nombreLimpio)
      .sort((a, b) => b.fecha.localeCompare(a.fecha) || b.hora - a.hora);

    if (delDocente.length === 0) {
      Swal.fire({
        icon: "info",
        title: nombre,
        text: "Sin coberturas en el historial.",
        confirmButtonText: "Cerrar",
      });
      return;
    }

    const filas = delDocente
      .map(
        (c) => `
        <tr>
          <td style="padding:6px; border-bottom:1px solid rgb(var(--border-primary)); white-space:nowrap;">${escapar(c.fecha)}</td>
          <td style="padding:6px; border-bottom:1px solid rgb(var(--border-primary)); text-align:center; font-weight:bold; color:rgb(var(--accent-primary));">${formatoHora(c.hora)}</td>
          <td style="padding:6px; border-bottom:1px solid rgb(var(--border-primary));">${escapar(c.docente_ausente)}</td>
          <td style="padding:6px; border-bottom:1px solid rgb(var(--border-primary)); text-align:center;">${escapar(c.grupo_a_cubrir || c.grupo_ausente || "-")}</td>
          <td style="padding:6px; border-bottom:1px solid rgb(var(--border-primary)); font-style:italic; color:rgb(var(--text-secondary));">${escapar(c.motivo || "-")}</td>
        </tr>`
      )
      .join("");

    const tableHtml = `
      <p style="font-size:13px; color:rgb(var(--text-secondary)); margin:0 0 8px 0;">${delDocente.length} cobertura(s) — ordenadas de la más reciente a la más antigua.</p>
      <table style="width:100%; font-size:12px; border-collapse:collapse;">
        <thead>
          <tr style="background-color:rgb(var(--bg-secondary));">
            <th style="padding:6px; text-align:left; font-weight:bold; color:rgb(var(--text-secondary));">Fecha</th>
            <th style="padding:6px; text-align:center; font-weight:bold; color:rgb(var(--text-secondary));">Hora</th>
            <th style="padding:6px; text-align:left; font-weight:bold; color:rgb(var(--text-secondary));">Ausente</th>
            <th style="padding:6px; text-align:center; font-weight:bold; color:rgb(var(--text-secondary));">Grupo</th>
            <th style="padding:6px; text-align:left; font-weight:bold; color:rgb(var(--text-secondary));">Motivo</th>
          </tr>
        </thead>
        <tbody>${filas}</tbody>
      </table>`;

    Swal.fire({
      title: `Historial de ${nombre}`,
      html: tableHtml,
      confirmButtonText: "Cerrar",
      width: "650px",
    });
  }

  function abrirHorarioDocente(nombre: string) {
    const nombreLimpio = normalizarDocente(nombre);
    if (ROLES_SIN_LIMITE.some((r) => nombreLimpio.includes(r))) {
      Swal.fire({
        icon: "info",
        title: nombreLimpio,
        text: "Rol administrativo sin horario fijo de clases.",
        confirmButtonText: "Cerrar",
      });
      return;
    }
    const horario = horariosVista.find((h) => h.docente === nombreLimpio);
    if (!horario) {
      Swal.fire("Error", `No se encontró el horario para ${nombreLimpio}`, "error");
      return;
    }

    let tableHtml = `
      <table style="width:100%; font-size:12px; border-collapse: collapse;">
        <thead>
          <tr style="background-color: #f3f4f6;">
            <th style="padding:8px; text-align:center; font-weight:bold; color:rgb(var(--text-secondary));">HORA</th>
            ${diasAbreviado.map((d) => `<th style="padding:8px; text-align:center; font-weight:bold; color:rgb(var(--text-secondary));">${d}</th>`).join("")}
          </tr>
        </thead>
        <tbody>
          ${Array(7)
            .fill(0)
            .map(
              (_, horaIdx) => `
            <tr>
              <td style="padding:6px; text-align:center; font-weight:bold; background-color:rgb(var(--bg-secondary)); color:rgb(var(--text-secondary));">${horaIdx + 1}</td>
              ${dias
                .map((dia) => {
                  const slot = horario[dia][horaIdx];
                  const estilo = getClaseSlot(slot);
                  return `<td style="padding:4px; text-align:center;">
                  <div class="px-2 py-2 rounded-lg text-xs font-bold min-h-[2.5rem] flex items-center justify-center ${estilo.bg} ${estilo.text} ${estilo.border}">
                    ${formatearMateria(slot)}
                  </div>
                </td>`;
                })
                .join("")}
            </tr>
          `
            )
            .join("")}
        </tbody>
      </table>
    `;

    Swal.fire({
      title: `Horario de ${nombreLimpio}`,
      html: tableHtml,
      confirmButtonText: "Cerrar",
      width: "650px",
    });
  }
</script>

<div class="p-4 sm:p-6 rounded-2xl border" style="border-color: rgb(var(--border-primary)); background-color: rgb(var(--card-bg));">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-lg font-bold" style="color: rgb(var(--text-primary));">
      Step 3 — Asignaciones Sugeridas
    </h2>
    <button onclick={onBack} class="text-sm px-3 py-1 rounded-lg" style="color: rgb(var(--text-secondary));">
      ← Atrás
    </button>
  </div>

  <div class="mb-4 p-4 rounded-xl grid grid-cols-1 sm:grid-cols-3 gap-4 text-center" style="background-color: rgb(var(--bg-secondary));">
    <div>
      <p class="text-2xl font-bold" style="color: rgb(var(--accent-primary));">{formatoDia(diaSeleccionado)}</p>
      <p class="text-xs" style="color: rgb(var(--text-secondary));">{fechaSeleccionada}</p>
    </div>
    <div>
      <p class="text-2xl font-bold" style="color: rgb(var(--accent-primary));">{seleccionadas}</p>
      <p class="text-xs" style="color: rgb(var(--text-secondary));">Coberturas seleccionadas</p>
    </div>
    <div>
      <p class="text-2xl font-bold" style="color: {violaciones > 0 ? '#ef4444' : 'rgb(var(--accent-primary))'};">{violaciones}</p>
      <p class="text-xs" style="color: rgb(var(--text-secondary));">Con violación de reglas</p>
    </div>
  </div>
    <div class="flex flex-wrap justify-end gap-2 mb-2">
      <button
        onclick={onAprobarTodo}
        class="px-3 py-1.5 rounded-lg font-medium transition-all text-xs"
        style="background-color: rgb(var(--accent-primary)); color: white;"
      >
        ✓ APROBAR TODO
      </button>
      {#if onOpenGruposModal}
        <button
          onclick={onOpenGruposModal}
          class="px-3 py-1.5 rounded-lg font-medium transition-all text-xs"
          style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--accent-primary)); border: 1px solid rgb(var(--accent-primary));"
        >
          + LIBERAR GRUPOS
        </button>
      {/if}
      <button
        onclick={exportarJSON}
        class="px-3 py-1.5 rounded-lg font-medium transition-all text-xs"
        style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--accent-primary)); border: 1px solid rgb(var(--accent-primary));"
      >
        ↓ EXPORTAR JSON
      </button>
    </div>

  {#if gruposSugeridosAAusentar.length > 0}
    <div class="mb-4 p-4 rounded-xl border" style="border-color: rgb(var(--accent-primary)); background-color: rgba(99, 102, 241, 0.05);">
      <p class="text-sm font-bold mb-2" style="color: rgb(var(--accent-primary));">💡 Grupos sugeridos para ausentar (reducir coberturas)</p>
      <p class="text-xs mb-3" style="color: rgb(var(--text-secondary));">Estos grupos generan 2 o más horas libres. Ausentarlos reduce la carga de coberturas.</p>
      <div class="flex gap-2 flex-wrap">
        {#each gruposSugeridosAAusentar as sug}
          <button
            onclick={() => onAgregarGrupoAusente(sug.grupo)}
            class="px-3 py-2 rounded-lg text-sm font-medium transition-all flex items-center gap-2"
            style="background-color: rgb(var(--accent-primary)); color: white;"
          >
            <span>Grupo {sug.grupo}</span>
            <span class="text-xs opacity-75">({sug.horasAfectadas} horas)</span>
          </button>
        {/each}
      </div>
    </div>
  {/if}

  {#if coberturasSugeridas.length === 0}
    <div class="text-center py-8">
      <p class="text-zinc-500">No hay coberturas sugeridas para el día seleccionado.</p>
    </div>
  {:else}
    <!-- Tabla (escritorio ≥1024px) -->
    <div class="hidden lg:block overflow-x-auto mb-6">
      <table class="w-full text-sm" style="border-collapse: collapse;">
        <thead>
          <tr style="background-color: rgb(var(--bg-secondary));">
            <th class="p-3 text-center font-bold uppercase tracking-wider" style="color: rgb(var(--text-primary));">Hora</th>
            <th class="p-3 text-center font-bold uppercase tracking-wider" style="color: rgb(var(--text-primary));">Ausente</th>
            <th class="p-3 text-center font-bold uppercase tracking-wider w-[40%] min-w-[320px]" style="color: rgb(var(--text-primary));">Cubre</th>
            <th class="p-3 text-center font-bold uppercase tracking-wider" style="color: rgb(var(--text-primary));">Grupo a cubrir</th>
            <th class="p-3 text-center font-bold uppercase tracking-wider" style="color: rgb(var(--text-primary));">Estado</th>
            <th class="p-3 text-center font-bold uppercase tracking-wider" style="color: rgb(var(--text-primary));">Aprobar</th>
          </tr>
        </thead>
        <tbody>
          {#each coberturasSugeridas as cov, i}
            {@const esViolacion = !!cov.violation}
            {@const checked = cov.aprobada && !esViolacion}
            {@const dupInfo = esDuplicado(cov.docenteCubre, cov.hora)}
            {@const color = colorPorAusente.get(cov.docenteAusente) ?? { bg: "transparent", border: "transparent", text: "rgb(var(--accent-primary))" }}
            <tr
              class="transition-colors"
              style="border-color: rgb(var(--border-primary)); background-color: {esViolacion ? 'rgba(239,68,68,0.05)' : color.bg}; border-left: 4px solid {color.border};"
            >
              <td class="p-3 text-center font-bold border-t" style="border-color: rgb(var(--border-primary)); color: rgb(var(--text-primary));">
                {formatoHora(cov.hora)}
              </td>
              <td class="p-3 text-center border-t" style="border-color: rgb(var(--border-primary));">
                <button
                  onclick={() => abrirHorarioDocente(cov.docenteAusente)}
                  class="font-bold hover:underline cursor-pointer px-2 py-1 rounded"
                  style="color: {color.text}; background-color: {color.bg}; border: 1.5px solid {color.border};"
                  title="Ver horario semanal"
                >
                  {cov.docenteAusente}
                </button>
              </td>
              <td class="p-3 text-center border-t" style="border-color: rgb(var(--border-primary));">
                <div class="flex items-center gap-2 w-full">
                  <select
                    value={cov.docenteCubre || ""}
                    onchange={(e) => confirmarCambioDocente(i, e.currentTarget.value, cov.hora, e.currentTarget, cov.docenteCubre || "")}
                    class="flex-1 min-w-0 px-3 py-2 rounded-lg text-base font-semibold border transition-all {dupInfo.dup ? 'cobertura-blink-dup' : ''}"
                    style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--accent-primary)); border-color: {dupInfo.dup ? '#ef4444' : dupInfo.porGrupoLiberado ? '#eab308' : 'rgb(var(--border-primary))'}; border-width: {dupInfo.dup || dupInfo.porGrupoLiberado ? '2px' : '1px'};"
                  >
                    {#if cov.posiblesCobradores.length > 0}
                      <optgroup label="Docentes disponibles">
                        {#each cov.posiblesCobradores as docente}
                          {@const indic = getIndicadorWarning(docente)}
                          {@const warningText = (() => { const s = conteoSesion.get(docente) || 0; const h = conteoHistorico.get(docente) || 0; if (s >= 1 && h >= 1) return " ¡YA USADO!";
if (h >= 1) return " ¡HOY YA CUBRIÓ!";
if (s >= 1) return " Cubre otra o actual";
return ""; })()}
                          <option value={docente} style={estiloOptionDocente(docente, cov.docenteCubre)}>{indic}{docente}{warningText}</option>
                        {/each}
                      </optgroup>
                    {/if}
                    <optgroup label="Roles institucionales">
                      <option value="ORIENTACION">ORIENTACION</option>
                      <option value="COORDINADOR">COORDINADOR</option>
                      <option value="BIBLIOTECA">BIBLIOTECA</option>
                      <option value="AUDITORIO">AUDITORIO</option>
                    </optgroup>
                    <optgroup label="Otros">
                      <option value="IGNORAR">IGNORAR</option>
                    </optgroup>
                  </select>
                  {#if cov.docenteCubre && cov.docenteCubre !== "IGNORAR"}
                    <button
                      type="button"
                      onclick={() => abrirHistorialDocente(cov.docenteCubre)}
                      class="shrink-0 w-7 h-7 rounded-lg flex items-center justify-center transition-all hover:opacity-80"
                      style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--accent-primary)); border: 1px solid rgb(var(--border-primary));"
                      title="Ver historial de coberturas de {cov.docenteCubre}"
                      aria-label="Ver historial de {cov.docenteCubre}"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3v5h5"/><path d="M3.05 13A9 9 0 1 0 6 5.3L3 8"/><path d="M12 7v5l4 2"/></svg>
                    </button>
                  {/if}
                  {#if esCoberturaEnHoraLibre(cov.docenteCubre, cov.hora)}
                    <span class="shrink-0 text-xs font-bold px-2 py-1 rounded" style="background-color: rgb(var(--accent-primary)); color: white;" title="Cubre en su hora libre ({normalizarDocente(cov.docenteCubre)}{MARCADOR_LIBRE})">@</span>
                  {/if}
                </div>
                {#if dupInfo.dup}
                  <div class="mt-2 px-2 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1" style="background-color: #fee2e2; color: #991b1b; border: 1px solid #ef4444;">
                    <span class="text-base">🚨</span>
                    <span>REPITE{dupInfo.sesion > 1 ? ` ${dupInfo.sesion}× HOY` : ""}{dupInfo.historico > 0 ? ` · ${dupInfo.historico}h HIST` : ""}</span>
                  </div>
                {:else if dupInfo.porGrupoLiberado}
                  <div class="mt-2 px-2 py-1.5 rounded-lg text-xs font-medium flex items-center gap-1" style="background-color: #dbeafe; color: #1e40af; border: 1px solid #3b82f6;">
                    <span class="text-base">ℹ️</span>
                    <span>Repite válido — grupo liberado</span>
                  </div>
                {/if}
              </td>
              <td class="p-3 text-center border-t" style="border-color: rgb(var(--border-primary));">
                {#if cov.docenteCubre}
                  <button
                    onclick={() => abrirHorarioDocente(cov.docenteCubre)}
                    class="font-medium hover:underline cursor-pointer"
                    style="color: rgb(var(--accent-primary));"
                    title="Ver horario semanal de {cov.docenteCubre}"
                  >
                    {cov.grupoACubrir}
                  </button>
                {:else}
                  <span style="color: rgb(var(--text-secondary));">{cov.grupoACubrir}</span>
                {/if}
              </td>
              <td class="p-3 text-center border-t" style="border-color: rgb(var(--border-primary));">
                {#if esViolacion}
                  <span class="px-2 py-1 rounded text-xs font-bold bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-200">
                    {cov.violation}
                  </span>
                {:else if cov.aprobada}
                  <span class="px-2 py-1 rounded text-xs font-bold bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-200">
                    ✓ Aprobada
                  </span>
                {/if}
                {#if cov.porGrupoAusente}
                  <span class="ml-1 text-xs font-medium" style="color: rgb(var(--accent-primary));" title="Cubre hora libre por ausencia de grupo">
                    ↻ grado liberado
                  </span>
                {/if}
                {#if !esViolacion && !cov.aprobada}
                  <span class="px-2 py-1 rounded text-xs font-bold bg-zinc-100 dark:bg-zinc-700 text-zinc-500 dark:text-zinc-400">
                    Pendiente
                  </span>
                {/if}
                {#if cov.grupoAusente
                    && cov.hora >= 4
                    && !cov.porGrupoAusente
                    && (!cov.docenteCubre || cov.docenteCubre === "IGNORAR" || !!cov.violation)
                    && onLiberarGrupoDesdeHora}
                  <span class="text-xs" style="color: rgb(var(--text-secondary));">|</span>
                  <button
                    onclick={() => onLiberarGrupoDesdeHora(cov.grupoAusente, cov.hora, cov.docenteAusente)}
                    class="ml-2 px-3 py-1 rounded text-xs font-bold transition-all border-2"
                    style="background-color: #ef4444; color: white; border-color: #b91c1c;"
                  >
                    🗑️ Liberar {cov.grupoAusente} (hora {cov.hora + 1})
                  </button>
                {/if}
              </td>
              <td class="p-3 text-center border-t" style="border-color: rgb(var(--border-primary));">
                <button
                  onclick={() => onToggle(i)}
                  class="w-11 h-11 rounded-full flex items-center justify-center transition-all mx-auto text-lg font-bold"
                  style="background-color: {cov.aprobada ? 'rgb(var(--accent-primary))' : 'rgb(var(--bg-secondary))'}; color: {cov.aprobada ? 'white' : 'rgb(var(--text-secondary))'};"
                >
                  {cov.aprobada ? "✓" : ""}
                </button>
              </td>
            </tr>
          {/each}
        </tbody>
      </table>
    </div>

    <!-- Tarjetas por cobertura (móvil / tablet <1024px) -->
    <div class="lg:hidden space-y-3 mb-6">
      {#each coberturasSugeridas as cov, i}
        {@const esViolacion = !!cov.violation}
        {@const dupInfo = esDuplicado(cov.docenteCubre, cov.hora)}
        {@const color = colorPorAusente.get(cov.docenteAusente) ?? { bg: "transparent", border: "rgb(var(--border-primary))", text: "rgb(var(--accent-primary))" }}
        {@const indicSel = cov.docenteCubre ? getIndicadorWarning(cov.docenteCubre) : ""}
        <div
          class="rounded-2xl border p-3.5 shadow-sm"
          style="border-color: {esViolacion ? '#ef4444' : 'rgb(var(--border-primary))'}; background-color: rgb(var(--card-bg)); border-left: 5px solid {esViolacion ? '#ef4444' : color.border};"
        >
          <!-- Cabecera: hora + grupo + aprobar -->
          <div class="flex items-center justify-between gap-3 mb-3.5">
            <div class="flex items-center gap-2.5 min-w-0">
              <span
                class="shrink-0 inline-flex items-center justify-center min-w-[2.75rem] h-11 px-2 rounded-xl text-base font-extrabold tabular-nums"
                style="background-color: rgb(var(--accent-primary)); color: white;"
              >
                {formatoHora(cov.hora)}
              </span>
              <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-widest leading-none mb-0.5" style="color: rgb(var(--text-secondary));">Grupo</p>
                <p class="text-base font-bold leading-tight truncate" style="color: rgb(var(--text-primary));">{cov.grupoACubrir}</p>
              </div>
            </div>
            <button
              onclick={() => onToggle(i)}
              class="shrink-0 w-12 h-12 rounded-full flex items-center justify-center transition-all text-xl font-bold"
              style="background-color: {cov.aprobada ? 'rgb(var(--accent-primary))' : 'transparent'}; color: {cov.aprobada ? 'white' : 'rgb(var(--text-secondary))'}; border: 2px solid {cov.aprobada ? 'rgb(var(--accent-primary))' : 'rgb(var(--border-primary))'};"
              aria-label={cov.aprobada ? "Quitar aprobación" : "Aprobar cobertura"}
            >
              {cov.aprobada ? "✓" : "○"}
            </button>
          </div>

          <!-- Ausente -->
          <button
            onclick={() => abrirHorarioDocente(cov.docenteAusente)}
            class="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-left transition-all hover:opacity-90"
            style="background-color: {color.bg}; border: 1.5px solid {color.border};"
            title="Ver horario semanal"
          >
            <span class="shrink-0 text-[9px] font-extrabold uppercase tracking-wider px-1.5 py-0.5 rounded" style="background-color: {color.border}; color: white;">Ausente</span>
            <span class="flex-1 min-w-0 text-sm font-bold break-words" style="color: {color.text};">{cov.docenteAusente}</span>
            {#if cov.grupoAusente}
              <span class="shrink-0 text-xs font-semibold opacity-80" style="color: {color.text};">{cov.grupoAusente}</span>
            {/if}
          </button>

          <!-- Conector -->
          <div class="flex items-center gap-2 my-2 px-1">
            <span class="text-base leading-none" style="color: rgb(var(--text-secondary));">↓</span>
            <span class="text-[10px] font-bold uppercase tracking-widest" style="color: rgb(var(--text-secondary));">Cubierto por</span>
            <span class="flex-1 h-px" style="background-color: rgb(var(--border-primary));"></span>
          </div>

          <!-- Cubre -->
          <div>
            <div class="flex items-center gap-2">
              <select
                value={cov.docenteCubre || ""}
                onchange={(e) => confirmarCambioDocente(i, e.currentTarget.value, cov.hora, e.currentTarget, cov.docenteCubre || "")}
                class="flex-1 min-w-0 px-2.5 py-2.5 rounded-xl text-sm leading-tight font-semibold border transition-all min-h-[46px] {dupInfo.dup ? 'cobertura-blink-dup' : ''}"
                style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--accent-primary)); border-color: {dupInfo.dup ? '#ef4444' : dupInfo.porGrupoLiberado ? '#eab308' : 'rgb(var(--border-primary))'}; border-width: {dupInfo.dup || dupInfo.porGrupoLiberado ? '2px' : '1px'};"
              >
                {#if cov.posiblesCobradores.length > 0}
                  <optgroup label="Docentes disponibles">
                    {#each cov.posiblesCobradores as docente}
                      {@const indic = getIndicadorWarning(docente)}
                      {@const warningText = (() => { const s = conteoSesion.get(docente) || 0; const h = conteoHistorico.get(docente) || 0; if (s >= 1 && h >= 1) return " ¡YA USADO!";
if (h >= 1) return " ¡HOY YA CUBRIÓ!";
if (s >= 1) return " Cubre otra o actual";
return ""; })()}
                      <option value={docente} style={estiloOptionDocente(docente, cov.docenteCubre)}>{indic}{docente}{warningText}</option>
                    {/each}
                  </optgroup>
                {/if}
                <optgroup label="Roles institucionales">
                  <option value="ORIENTACION">ORIENTACION</option>
                  <option value="COORDINADOR">COORDINADOR</option>
                  <option value="BIBLIOTECA">BIBLIOTECA</option>
                  <option value="AUDITORIO">AUDITORIO</option>
                </optgroup>
                <optgroup label="Otros">
                  <option value="IGNORAR">IGNORAR</option>
                </optgroup>
              </select>
              {#if cov.docenteCubre && cov.docenteCubre !== "IGNORAR"}
                <button
                  type="button"
                  onclick={() => abrirHistorialDocente(cov.docenteCubre)}
                  class="shrink-0 w-11 h-11 rounded-xl flex items-center justify-center transition-all hover:opacity-80"
                  style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--accent-primary)); border: 1px solid rgb(var(--border-primary));"
                  title="Ver historial de coberturas de {cov.docenteCubre}"
                  aria-label="Ver historial de {cov.docenteCubre}"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3v5h5"/><path d="M3.05 13A9 9 0 1 0 6 5.3L3 8"/><path d="M12 7v5l4 2"/></svg>
                </button>
              {/if}
            </div>
            {#if cov.docenteCubre}
              <div
                class="mt-2 flex items-start gap-2 px-2.5 py-2 rounded-xl"
                style="background-color: rgb(var(--bg-secondary)); border: 1px solid rgb(var(--border-primary));"
              >
                {#if indicSel}<span class="shrink-0 text-lg leading-none">{indicSel}</span>{/if}
                <span class="flex-1 text-base font-bold break-words leading-snug" style="color: rgb(var(--text-primary));">{nombreCubreMostrar(cov.docenteCubre, cov.hora)}</span>
                {#if esCoberturaEnHoraLibre(cov.docenteCubre, cov.hora)}
                  <span class="shrink-0 text-[10px] font-bold px-1.5 py-0.5 rounded" style="background-color: rgb(var(--accent-primary)); color: white;" title="Cubre en su hora libre">hora libre</span>
                {/if}
              </div>
            {/if}
            {#if dupInfo.dup}
              <div class="mt-2 px-2.5 py-1.5 rounded-xl text-xs font-bold flex items-center gap-1.5" style="background-color: #fee2e2; color: #991b1b; border: 1px solid #ef4444;">
                <span class="text-base">🚨</span>
                <span>REPITE{dupInfo.sesion > 1 ? ` ${dupInfo.sesion}× HOY` : ""}{dupInfo.historico > 0 ? ` · ${dupInfo.historico}h HIST` : ""}</span>
              </div>
            {:else if dupInfo.porGrupoLiberado}
              <div class="mt-2 px-2.5 py-1.5 rounded-xl text-xs font-medium flex items-center gap-1.5" style="background-color: #dbeafe; color: #1e40af; border: 1px solid #3b82f6;">
                <span class="text-base">ℹ️</span>
                <span>Repite válido — grupo liberado</span>
              </div>
            {/if}
          </div>

          <!-- Estado -->
          {#if esViolacion || cov.aprobada || cov.porGrupoAusente || (!esViolacion && !cov.aprobada) || (cov.grupoAusente && cov.hora >= 4 && !cov.porGrupoAusente && (!cov.docenteCubre || cov.docenteCubre === "IGNORAR" || !!cov.violation) && onLiberarGrupoDesdeHora)}
            <div class="mt-3 pt-3 flex flex-wrap items-center gap-2" style="border-top: 1px dashed rgb(var(--border-primary));">
              {#if esViolacion}
                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-200">
                  {cov.violation}
                </span>
              {:else if cov.aprobada}
                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-200">
                  ✓ Aprobada
                </span>
              {/if}
              {#if cov.porGrupoAusente}
                <span class="text-xs font-medium" style="color: rgb(var(--accent-primary));" title="Cubre hora libre por ausencia de grupo">
                  ↻ grado liberado
                </span>
              {/if}
              {#if !esViolacion && !cov.aprobada}
                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-zinc-100 dark:bg-zinc-700 text-zinc-500 dark:text-zinc-400">
                  Pendiente
                </span>
              {/if}
              {#if cov.grupoAusente
                  && cov.hora >= 4
                  && !cov.porGrupoAusente
                  && (!cov.docenteCubre || cov.docenteCubre === "IGNORAR" || !!cov.violation)
                  && onLiberarGrupoDesdeHora}
                <button
                  onclick={() => onLiberarGrupoDesdeHora(cov.grupoAusente, cov.hora, cov.docenteAusente)}
                  class="px-3 py-2 rounded-lg text-xs font-bold transition-all border-2 w-full sm:w-auto"
                  style="background-color: #ef4444; color: white; border-color: #b91c1c;"
                >
                  🗑️ Liberar {cov.grupoAusente} (hora {cov.hora + 1})
                </button>
              {/if}
            </div>
          {/if}
        </div>
      {/each}
    </div>

    <div class="mt-4 mb-4 p-3 rounded-xl flex flex-wrap gap-3 text-xs items-center" style="background-color: rgb(var(--bg-secondary)); border: 1px solid rgb(var(--border-primary));">
      <span class="font-semibold mr-2" style="color: rgb(var(--text-primary));">Alertas en select:</span>
      <span class="px-3 py-1.5 rounded-lg font-bold flex items-center gap-1" style="background-color: #3b82f6; color: white;">🔵 Cubre otra o actual</span>
      <span class="px-3 py-1.5 rounded-lg font-bold flex items-center gap-1 blink-warning" style="background-color: #f97316; color: white;">🟠 Ya cubrió hoy</span>
      <span class="px-3 py-1.5 rounded-lg font-bold flex items-center gap-1 blink-warning" style="background-color: #ef4444; color: white;">🔴 Sesión + histórico</span>
    </div>
  {/if}

  <div class="flex flex-col sm:flex-row gap-3">
    <button
      onclick={onBack}
      class="flex-1 py-3 rounded-xl font-medium transition-all min-h-[52px]"
      style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border: 1px solid rgb(var(--border-primary));"
    >
      ← Volver al análisis
    </button>
    <button
      id="tour-step3-guardar"
      onclick={onGuardar}
      disabled={loading || seleccionadas === 0}
      class="flex-1 py-3 rounded-xl font-bold text-white transition-all disabled:opacity-50 flex items-center justify-center gap-2 min-h-[52px]"
      style="background-color: rgb(var(--accent-primary)); opacity: {loading ? 0.7 : 1};"
    >
      {#if loading}
        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Guardando...
      {:else}
        Guardar {seleccionadas} cobertura(s)
      {/if}
    </button>
  </div>
</div>

<style>
  @keyframes cobertura-blink {
    0%, 100% {
      box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
      border-color: #ef4444;
    }
    50% {
      box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
      border-color: #fca5a5;
    }
  }
  .cobertura-blink-dup {
    animation: cobertura-blink 1s ease-in-out infinite;
  }
  @keyframes blink-warning {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
  }
  .blink-warning {
    animation: blink-warning 1s ease-in-out infinite;
  }
</style>