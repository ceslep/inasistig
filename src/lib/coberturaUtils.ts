export type HorarioDocente = {
  docente: string;
  lunes: string[];
  martes: string[];
  miercoles: string[];
  jueves: string[];
  viernes: string[];
};

export type TipoSlot = "clase" | "desc" | "pedag" | "libre" | "libre_ausencia";

export type SlotInfo = {
  hora: number;
  docente: string;
  slot: string;
  tipo: TipoSlot;
  grupoAusente?: string;
  docenteAusente?: string;
  motivoAusencia?: string;
};

export type Ausencia = { tipo: "docente"; nombre: string; motivo: string } | { tipo: "grupo"; nombre: string; horaInicio?: number; motivo: string };

export type CoberturaSugerida = {
  hora: number;
  docenteAusente: string;
  grupoAusente: string;
  docenteCubre: string;
  grupoACubrir: string;
  aprobada: boolean;
  violation?: string;
  posiblesCobradores: string[];
  motivoAusencia: string;
};

export type SesionCobertura = {
  dia: string;
  fecha: string;
  ausencias: Ausencia[];
  coberturas: CoberturaSugerida[];
  step: number;
};

export type CoberturaHistorica = {
  fecha: string;
  dia_semana: string;
  hora: number;
  docente_ausente: string;
  grupo_ausente: string;
  docente_cubre: string;
  grupo_a_cubrir: string;
  estado: string;
  motivo: string;
};

export const DIAS = ["lunes", "martes", "miercoles", "jueves", "viernes"] as const;
export const DIAS_ABREV = ["LUN", "MAR", "MIE", "JUE", "VIE"];
export const ROLES_SIN_LIMITE = ["ORIENTACION", "ORIENTADOR", "COORDINADOR", "BIBLIOTECA"];

export function getGruposDisponibles(horarios: HorarioDocente[]): string[] {
  const grupos = new Set<string>();
  for (const h of horarios) {
    for (const dia of DIAS) {
      for (const slot of h[dia]) {
        if (slot && slot !== "DESC" && slot !== "PEDAG" && slot !== "DEESC") {
const match = slot.match(/(\d{3,4})$/);
          if (match) grupos.add(match[1]);
        }
      }
    }
  }
  return Array.from(grupos).sort();
}

export function getDocentesList(horarios: HorarioDocente[]): string[] {
  return horarios.map((h) => h.docente).sort();
}

export function getDiaFromFecha(fecha: string): string {
  const date = new Date(fecha + "T00:00:00");
  const day = date.getDay();
  if (day === 0 || day === 6) return "";
  const map: Record<number, string> = { 1: "lunes", 2: "martes", 3: "miercoles", 4: "jueves", 5: "viernes" };
  return map[day] || "";
}

export function getSlotsDelDia(dia: string, horarios: HorarioDocente[]): SlotInfo[] {
  const slots: SlotInfo[] = [];
  for (const h of horarios) {
    const jornada = h[dia as keyof HorarioDocente] as string[];
    for (let hora = 0; hora < jornada.length; hora++) {
      const slot = jornada[hora];
      slots.push({
        hora,
        docente: h.docente,
        slot,
        tipo: clasificarSlot(slot),
      });
    }
  }
  return slots;
}

export function clasificarSlot(slot: string): TipoSlot {
  if (!slot) return "libre";
  if (slot === "DESC" || slot === "DEESC") return "desc";
  if (slot === "PEDAG") return "pedag";
  return "clase";
}

export function getGrupoFromSlot(slot: string): string {
  if (!slot) return "";
  const match = slot.match(/(\d{3,4})$/);
  return match ? match[1] : "";
}

export function getMateriaFromSlot(slot: string): string {
  if (!slot) return "";
  return slot.replace(/\s*\d{4}$/, "").trim();
}

export function aplicarAusencias(
  slots: SlotInfo[],
  ausencias: Ausencia[],
  horarios: HorarioDocente[]
): SlotInfo[] {
  const slotsConAusencia = slots.map((s) => {
    let tipo = s.tipo;
    let grupoAusente = "";
    let docenteAusente = "";
    let motivoAusencia = "";

    // 1) Aplicar ausencias de docente — slot necesita cubrimiento
    for (const ausencia of ausencias) {
      if (ausencia.tipo === "docente" && ausencia.nombre === s.docente) {
        if (s.tipo === "clase") {
          tipo = "libre_ausencia";
          docenteAusente = s.docente;
          motivoAusencia = ausencia.motivo;
        }
      }
    }

    // 2) Aplicar ausencias de grupo — slot del docente queda LIBRE (no necesita cubrirse).
    //    Si su clase es con grupo ausente, su hora pasa a libre. Si además era libre_ausencia
    //    por docente ausente, también queda libre (no hay grupo a cubrir).
    for (const ausencia of ausencias) {
      if (ausencia.tipo === "grupo") {
        const grupoSlot = getGrupoFromSlot(s.slot);
        if (grupoSlot === ausencia.nombre && s.tipo === "clase") {
          const horaMin = ausencia.horaInicio ?? 1;
          if (s.hora >= horaMin - 1) {
            // Grupo no asiste — no hay clase que cubrir. Slot queda libre.
            tipo = "libre";
            grupoAusente = "";
            docenteAusente = "";
            motivoAusencia = "";
          }
        }
      }
    }

    return { ...s, tipo, grupoAusente, docenteAusente, motivoAusencia };
  });

  return slotsConAusencia;
}

export function encontrarCoberturasPosibles(
  slots: SlotInfo[],
  docenteCubre: string
): SlotInfo[] {
  return slots.filter((s) => s.docente === docenteCubre && s.tipo === "libre");
}

/**
 * Determina si el slot original del docente a `hora` en `dia` era una clase
 * con un grupo que está ausente desde su horaInicio cumplida. En ese caso,
 * el slot equivale a tiempo libre del docente — sus cubrimientos en esas
 * horas NO cuentan contra el límite 1h/día (martes scenario).
 */
export function esHoraLibrePorGrupoAusente(
  docente: string,
  hora: number,
  dia: string,
  horarios: HorarioDocente[],
  ausenciasGrupo: { grupo: string; horaInicio: number }[]
): boolean {
  if (!ausenciasGrupo.length) return false;
  const h = horarios.find((x) => x.docente === docente);
  if (!h) return false;
  const jornada = h[dia as keyof HorarioDocente] as string[];
  const slotRaw = jornada?.[hora];
  if (!slotRaw) return false;
  const grupo = getGrupoFromSlot(slotRaw);
  if (!grupo) return false;
  return ausenciasGrupo.some((g) => g.grupo === grupo && hora >= (g.horaInicio ?? 1) - 1);
}

export function construirCargaDiariaHistorica(
  coberturasPrevias: CoberturaHistorica[],
  fechaActual: string
): Map<string, number> {
  const carga = new Map<string, number>();
  for (const cp of coberturasPrevias) {
    if (cp.estado !== "aprobado") continue;
    if (cp.fecha !== fechaActual) continue;
    const doc = cp.docente_cubre;
    if (!doc) continue;
    if (ROLES_SIN_LIMITE.some((r) => doc.includes(r))) continue;
    carga.set(doc, (carga.get(doc) || 0) + 1);
  }
  return carga;
}

export function construirCargaDiariaSesion(
  coberturas: CoberturaSugerida[],
  indiceExcluir: number,
  docenteNuevo: string,
  cargaInicial?: Map<string, number>,
  ctx?: {
    dia: string;
    horarios: HorarioDocente[];
    ausenciasGrupo: { grupo: string; horaInicio: number }[];
  }
): Map<string, number> {
  const carga = new Map<string, number>(cargaInicial ?? []);
  const esLibre = (docente: string, hora: number): boolean => {
    if (!ctx) return false;
    return esHoraLibrePorGrupoAusente(docente, hora, ctx.dia, ctx.horarios, ctx.ausenciasGrupo);
  };
  for (let i = 0; i < coberturas.length; i++) {
    const c = coberturas[i];
    if (!c.docenteCubre) continue;
    if (ROLES_SIN_LIMITE.some((r) => c.docenteCubre.includes(r))) continue;
    if (i === indiceExcluir) {
      if (docenteNuevo && !ROLES_SIN_LIMITE.some((r) => docenteNuevo.includes(r))) {
        if (!esLibre(docenteNuevo, c.hora)) {
          carga.set(docenteNuevo, (carga.get(docenteNuevo) || 0) + 1);
        }
      }
      continue;
    }
    if (c.aprobada || c.docenteCubre) {
      if (!esLibre(c.docenteCubre, c.hora)) {
        carga.set(c.docenteCubre, (carga.get(c.docenteCubre) || 0) + 1);
      }
    }
  }
  return carga;
}

export function getPosiblesCobradoresParaSlot(
  slot: SlotInfo,
  dia: string,
  horarios: HorarioDocente[],
  slotsLibresPorAusencia: SlotInfo[],
  cargaDiariaSesion: Map<string, number>,
  horasCubiertasSemana: Map<string, number>,
  indiceAusencias: Map<string, number>,
  asignacionesSesion: CoberturaSugerida[],
  ausenciasGrupo: { grupo: string; horaInicio: number }[] = []
): { docente: string; indice: number }[] {
  return horarios
    .filter((h) => {
      if (slotsLibresPorAusencia.some((s) => s.docenteAusente === h.docente)) return false;

      const jornada = h[dia as keyof HorarioDocente] as string[];
      const slotDelDocente = jornada[slot.hora];

      // Si slot pertenece a grupo ausente desde horaInicio cumplida, equivale a libre.
      const grupoDelSlot = getGrupoFromSlot(slotDelDocente);
      const slotLiberadoPorGrupo =
        !!grupoDelSlot &&
        ausenciasGrupo.some(
          (g) => g.grupo === grupoDelSlot && slot.hora >= (g.horaInicio ?? 1) - 1
        );

      if (slotDelDocente !== "" && !slotLiberadoPorGrupo) return false;

      if (slot.hora === 6) {
        const allDaysLibre = DIAS.every((d) => h[d as keyof HorarioDocente][6] === "");
        if (allDaysLibre) return false;
      }

      const esSinLimite = ROLES_SIN_LIMITE.some((r) => h.docente.includes(r));

      // Martes scenario: si el slot del candidato a esta hora era una clase con grupo
      // ausente, su hora ya es libre — puede cubrir aunque ya tenga otro cubrimiento.
      const slotLibrePorGrupo = slotLiberadoPorGrupo;

      const yaAsignadoEnSesion = asignacionesSesion.some((c) => c.docenteCubre === h.docente);
      if (yaAsignadoEnSesion && !esSinLimite && !slotLibrePorGrupo) return false;

      const cargaSesion = cargaDiariaSesion.get(h.docente) || 0;
      if (!esSinLimite && !slotLibrePorGrupo && cargaSesion >= 1) return false;

      const horasSemana = horasCubiertasSemana.get(h.docente) || 0;
      if (!esSinLimite && !slotLibrePorGrupo && horasSemana >= 2) return false;

      return true;
    })
    .map((h) => ({
      docente: h.docente,
      indice: indiceAusencias.get(h.docente) || 0,
    }));
}

export function asignarAutomaticamente(
  slotsLibresPorAusencia: SlotInfo[],
  horarios: HorarioDocente[],
  coberturasPrevias: CoberturaHistorica[],
  dia: string,
  fechaActual: string,
  ausenciasGrupo: { grupo: string; horaInicio: number }[] = []
): CoberturaSugerida[] {
  const coberturas: CoberturaSugerida[] = [];
  const cargaDiariaSesion = construirCargaDiariaHistorica(coberturasPrevias, fechaActual);
  const horasCubiertasSemana = new Map<string, number>();
  const semanaActual = getSemanaDelAno(fechaActual);

  const hoy = new Date(fechaActual + "T00:00:00");
  const hace14dias = new Date(hoy);
  hace14dias.setDate(hoy.getDate() - 14);
  const hace7dias = new Date(hoy);
  hace7dias.setDate(hoy.getDate() - 7);

  for (const cp of coberturasPrevias) {
    if (cp.estado !== "aprobado") continue;
    const cpSemana = getSemanaDelAno(cp.fecha);
    if (cpSemana !== semanaActual) continue;
    const doc = cp.docente_cubre;
    horasCubiertasSemana.set(doc, (horasCubiertasSemana.get(doc) || 0) + 1);
  }

  const indiceAusencias = new Map<string, number>();
  for (const cp of coberturasPrevias) {
    const cpFecha = new Date(cp.fecha + "T00:00:00");
    if (cpFecha < hace14dias || cpFecha >= hace7dias) continue;
    if (cp.docente_ausente) {
      indiceAusencias.set(cp.docente_ausente, (indiceAusencias.get(cp.docente_ausente) || 0) + 1);
    }
  }

  // B5: Dedup slots por (hora, docenteAusente|docente, grupoAusente)
  const slotsVistos = new Set<string>();
  const slotsDisponibles: SlotInfo[] = [];
  for (const s of slotsLibresPorAusencia) {
    const key = `${s.hora}-${s.docenteAusente || s.docente}-${s.grupoAusente || ""}`;
    if (slotsVistos.has(key)) continue;
    slotsVistos.add(key);
    slotsDisponibles.push(s);
  }
  console.log("asignarAutomaticamente: slotsDisponibles.length =", slotsDisponibles.length);
  const tipoCounts: Record<string, number> = {};
  for (const s of slotsDisponibles) {
    tipoCounts[s.tipo] = (tipoCounts[s.tipo] || 0) + 1;
  }
  console.log("asignarAutomaticamente: tipoCounts =", tipoCounts);

  for (const slot of slotsDisponibles) {
    if (slot.tipo !== "libre_ausencia") continue;

    const posiblesCobradores = getPosiblesCobradoresParaSlot(
      slot,
      dia,
      horarios,
      slotsLibresPorAusencia,
      cargaDiariaSesion,
      horasCubiertasSemana,
      indiceAusencias,
      coberturas,
      ausenciasGrupo
    );

    let mejorCobrador = "";
    let violation = "";

    if (posiblesCobradores.length > 0) {
      posiblesCobradores.sort((a, b) => b.indice - a.indice);
      mejorCobrador = posiblesCobradores[0].docente;
    } else {
      const sinLimite = horarios
        .filter((h) => {
          if (slotsLibresPorAusencia.some((s) => s.docenteAusente === h.docente)) return false;
          const jornada = h[dia as keyof HorarioDocente] as string[];
          const slotRaw = jornada[slot.hora];
          const grupoDelSlot = getGrupoFromSlot(slotRaw);
          const slotLiberadoPorGrupo =
            !!grupoDelSlot &&
            ausenciasGrupo.some(
              (g) => g.grupo === grupoDelSlot && slot.hora >= (g.horaInicio ?? 1) - 1
            );
          if (slotRaw !== "" && !slotLiberadoPorGrupo) return false;
          if (slot.hora === 6) {
            const allDaysLibre = DIAS.every((d) => h[d as keyof HorarioDocente][6] === "");
            if (allDaysLibre) return false;
          }
          // no reasignar mismo docente — máximo 1h/día
          // excepción: si en esta hora el slot del docente era de un grupo ausente,
          // su hora es libre y puede cubrir adicionalmente.
          const esSinLimite = ROLES_SIN_LIMITE.some((r) => h.docente.includes(r));
          if (!esSinLimite && !slotLiberadoPorGrupo && coberturas.some((c) => c.docenteCubre === h.docente)) return false;
          return true;
        })
        .map((h) => h.docente);

      if (sinLimite.length > 0) {
        violation = "⚠️ Límite diario/semanal alcanzado";
        mejorCobrador = sinLimite[0];
      } else {
        const sinLimiteTotal = ROLES_SIN_LIMITE.filter(r =>
          !slotsLibresPorAusencia.some((s) => s.docenteAusente?.includes(r))
        );
        if (sinLimiteTotal.length > 0) {
          violation = "";
          mejorCobrador = sinLimiteTotal[0];
        }
      }
    }

    if (!mejorCobrador) {
      violation = posiblesCobradores.length === 0 ? "⚠️ Sin docentes disponibles" : "⚠️ Todos ocupados";
    }

    coberturas.push({
      hora: slot.hora,
      docenteAusente: slot.docenteAusente || slot.docente,
      grupoAusente: slot.grupoAusente || getGrupoFromSlot(slot.slot),
      docenteCubre: mejorCobrador,
      grupoACubrir: slot.grupoAusente || (slot.slot ? getGrupoFromSlot(slot.slot) : slot.docente.split(" ").pop() || ""),
      aprobada: true,
      violation,
      posiblesCobradores: posiblesCobradores.map((c) => c.docente),
      motivoAusencia: slot.motivoAusencia || "",
    });

    if (mejorCobrador) {
      const esSinLimite = ROLES_SIN_LIMITE.some((r) => mejorCobrador.includes(r));
      const slotLibre = esHoraLibrePorGrupoAusente(
        mejorCobrador,
        slot.hora,
        dia,
        horarios,
        ausenciasGrupo
      );
      if (!esSinLimite && !slotLibre) {
        cargaDiariaSesion.set(mejorCobrador, (cargaDiariaSesion.get(mejorCobrador) || 0) + 1);
        horasCubiertasSemana.set(mejorCobrador, (horasCubiertasSemana.get(mejorCobrador) || 0) + 1);
      }
    }
  }

  return coberturas;
}

export function getSemanaDelAno(fecha: string): number {
  const date = new Date(fecha + "T00:00:00");
  const start = new Date(date.getFullYear(), 0, 1);
  const diff = date.getTime() - start.getTime();
  const oneWeek = 604800000;
  return Math.floor(diff / oneWeek);
}

export function getHorasLibresDelDia(slots: SlotInfo[]): SlotInfo[] {
  return slots.filter((s) => s.tipo === "libre" || s.tipo === "libre_ausencia");
}

export function getSlotsLibresPorAusencia(slots: SlotInfo[]): SlotInfo[] {
  return slots.filter((s) => s.tipo === "libre_ausencia");
}

export function getSlotsPorHora(slots: SlotInfo[], hora: number): SlotInfo[] {
  return slots.filter((s) => s.hora === hora);
}

export function formatoHora(hora: number): string {
  return `Hora ${hora + 1}`;
}

export function formatoDia(dia: string): string {
  const map: Record<string, string> = {
    lunes: "Lunes",
    martes: "Martes",
    miercoles: "Miércoles",
    jueves: "Jueves",
    viernes: "Viernes",
  };
  return map[dia] || dia;
}

export type SugerenciaGrupo = {
  grupo: string;
  horasAfectadas: number;
  docenteAfectado: string;
};

export type AnalisisGrupos = {
  gruposSugeridos: SugerenciaGrupo[];
  gruposYaAusentes: string[];
  gruposDisponibles: string[];
};

export function analizarGruposAAusentar(
  slotsLibresPorAusencia: SlotInfo[],
  coberturasSugeridas: CoberturaSugerida[],
  gruposYaAusentes: string[],
  horarios: HorarioDocente[],
  dia: string
): SugerenciaGrupo[] {
  const horasPorGrupo = new Map<string, { count: number; docente: string }>();

  for (const slot of slotsLibresPorAusencia) {
    if (slot.hora >= 4) continue;
    const grupo = slot.grupoAusente || getGrupoFromSlot(slot.slot);
    if (!grupo || gruposYaAusentes.includes(grupo)) continue;

    const existing = horasPorGrupo.get(grupo) || { count: 0, docente: slot.docente };
    existing.count += 1;
    horasPorGrupo.set(grupo, existing);
  }

  const suggestions = Array.from(horasPorGrupo.entries())
    .filter(([_, data]) => data.count >= 4)
    .map(([grupo, data]) => ({
      grupo,
      horasAfectadas: data.count,
      docenteAfectado: data.docente,
    }))
    .sort((a, b) => b.horasAfectadas - a.horasAfectadas)
    .slice(0, 3);

  return suggestions;
}

export function resumenCoberturas(coberturas: CoberturaSugerida[]): {
  total: number;
  automáticas: number;
  manuales: number;
  violaciones: number;
} {
  return {
    total: coberturas.length,
    automáticas: coberturas.filter((c) => c.aprobada && !c.violation).length,
    manuales: coberturas.filter((c) => !c.aprobada && !c.violation).length,
    violaciones: coberturas.filter((c) => c.violation).length,
  };
}