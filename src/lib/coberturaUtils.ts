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
export const ROLES_SIN_LIMITE = ["ORIENTADOR", "COORDINADOR", "BIBLIOTECA"];

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

    for (const ausencia of ausencias) {
      if (ausencia.tipo === "docente" && ausencia.nombre === s.docente) {
        if (s.tipo === "clase") {
          tipo = "libre_ausencia";
          docenteAusente = s.docente;
          motivoAusencia = ausencia.motivo;
        }
      } else if (ausencia.tipo === "grupo") {
        const grupoSlot = getGrupoFromSlot(s.slot);
        if (grupoSlot === ausencia.nombre && s.tipo === "clase") {
          const horaMin = ausencia.horaInicio ?? 1;
          if (s.hora >= horaMin - 1) {
            tipo = "libre_ausencia";
            grupoAusente = ausencia.nombre;
            docenteAusente = "";
            motivoAusencia = ausencia.motivo || "GRUPO AUSENTE";
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

export function asignarAutomaticamente(
  slotsLibresPorAusencia: SlotInfo[],
  horarios: HorarioDocente[],
  coberturasPrevias: CoberturaHistorica[],
  dia: string,
  fechaActual: string
): CoberturaSugerida[] {
  const coberturas: CoberturaSugerida[] = [];
  const cargaDiariaSesion = new Map<string, number>();
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

  const slotsDisponibles = [...slotsLibresPorAusencia];

  for (const slot of slotsDisponibles) {
    if (slot.tipo !== "libre_ausencia") continue;

    const posiblesCobradores = horarios
      .filter((h) => {
        if (slotsLibresPorAusencia.some((s) => s.docenteAusente === h.docente)) return false;

        const jornada = h[dia as keyof HorarioDocente] as string[];
        const slotDelDocente = jornada[slot.hora];

        if (slotDelDocente !== "") return false;

        if (slot.hora === 6) {
          const allDaysLibre = DIAS.every((d) => h[d as keyof HorarioDocente][6] === "");
          if (allDaysLibre) return false;
        }

        const esSinLimite = ROLES_SIN_LIMITE.some(r => h.docente.includes(r));

        const yaAsignadoEnSesion = coberturas.some((c) => c.docenteCubre === h.docente);
        if (yaAsignadoEnSesion && !esSinLimite) return false;

        const cargaSesion = cargaDiariaSesion.get(h.docente) || 0;
        if (!esSinLimite && cargaSesion >= 1) return false;

        const horasSemana = horasCubiertasSemana.get(h.docente) || 0;
        if (!esSinLimite && horasSemana >= 2) return false;

        return true;
      })
      .map((h) => ({
        docente: h.docente,
        indice: indiceAusencias.get(h.docente) || 0,
      }));

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
          if (jornada[slot.hora] !== "") return false;
          if (slot.hora === 6) {
            const allDaysLibre = DIAS.every((d) => h[d as keyof HorarioDocente][6] === "");
            if (allDaysLibre) return false;
          }
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
      aprobada: false,
      violation,
      posiblesCobradores: posiblesCobradores.map((c) => c.docente),
      motivoAusencia: slot.motivoAusencia || "",
    });

    if (mejorCobrador && !violation) {
      cargaDiariaSesion.set(mejorCobrador, (cargaDiariaSesion.get(mejorCobrador) || 0) + 1);
      horasCubiertasSemana.set(mejorCobrador, (horasCubiertasSemana.get(mejorCobrador) || 0) + 1);
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
    .filter(([_, data]) => data.count >= 2)
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