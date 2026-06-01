/**
 * horarioGenerator.ts — Generador automático de horarios escolares (TS puro, sin dependencias).
 *
 * Entrada: lista de CargaItem (docente↔área↔grupo↔#horas) derivada de horarios.json.
 * Salida: matriz por grupo (grupo→día→Celda[7]) + bloques DESC/PEDAG por docente + reporte
 * de no-asignados y conflictos.
 *
 * Reglas DURAS (de info_horas.json — todos los días tienen Hora 1..7):
 *  - Docente SIN horas extra: solo puede ocupar Hora 1..6 (índices 0..5) todos los días
 *    (máx 30 franjas/sem). NO lleva DESC ni PEDAG.
 *  - Docente CON horas extra (>0): puede ocupar Hora 1..7 (índices 0..6) los 5 días. Lleva
 *    bloque DESC + PEDAG contiguo de 2h (1 DESC + 1 PEDAG seguidos, mismo día).
 *  - Sin choque docente (no 2 grupos misma hora/día) ni choque grupo (no 2 docentes misma hora/día).
 *
 * Preferencias BLANDAS: una materia con 2h/día se coloca contigua; repartir misma materia
 * entre días distintos.
 *
 * Algoritmo: greedy + backtracking con heurística MRV (Minimum Remaining Values).
 */

import { getGrupoFromSlot, DIAS } from "./coberturaUtils";

// ---------- Tipos públicos ----------

export interface CargaItem {
  docente: string;
  area: string;
  grupo: string;
  horas: number;
}

export interface GenDocenteCfg {
  horasExtra: number; // > 0 ⇒ con extra (Hora 1..7 + DESC/PEDAG); 0 ⇒ Hora 1..6, sin DESC/PEDAG
}

export interface GenConfig {
  horasDia: number; // 7
  dias: readonly string[]; // DIAS de coberturaUtils
  cfgDocentes: Record<string, GenDocenteCfg>;
  maxIteraciones?: number; // tope determinista (default 300_000)
  timeoutMs?: number; // red de seguridad (default 5000) — Date.now solo aquí, módulo no-Svelte
}

export interface GenCelda {
  materia: string;
  docente: string;
}

export type GenHorario = Record<string, Record<string, GenCelda[]>>; // grupo -> dia -> Celda[7]

export type MotivoNoAsignado =
  | "sin_hueco"
  | "choque_irresoluble"
  | "descpedag_sin_par"
  | "tope_horario"
  | "timeout";

export interface NoAsignado {
  docente: string;
  area: string; // "" para el bloque DESC/PEDAG
  grupo: string; // "" para DESC/PEDAG
  horasFaltantes: number;
  motivo: MotivoNoAsignado;
}

export interface GenConflictoLite {
  tipo: "carga_excedida" | "grupo_sobre_lleno" | "descpedag_faltante";
  docente?: string;
  grupo?: string;
  mensaje: string;
}

export interface GenResult {
  horario: GenHorario;
  descPedag: Record<string, { dia: string; hora: number }[]>; // pista DESC/PEDAG por docente
  noAsignados: NoAsignado[];
  conflictos: GenConflictoLite[];
  ok: boolean;
  iteraciones: number;
  ms: number;
}

export interface HorarioDocenteFila {
  docente: string;
  lunes: string[];
  martes: string[];
  miercoles: string[];
  jueves: string[];
  viernes: string[];
}

// ---------- Helpers ----------

const AREA_DESCPEDAG = "__DESCPEDAG__";
const HORAS_SIN_EXTRA = 6; // índices válidos 0..5
const DESC_PEDAG_HORAS = 2;

/**
 * Materia robusta: quita el grupo final (3-4 dígitos). NO usa getMateriaFromSlot de
 * coberturaUtils porque ese tiene el regex \d{4} (falla con grupos de 3 dígitos como 601).
 */
export function materiaDeSlot(slot: string): string {
  if (!slot) return "";
  return slot.replace(/\s*\d{3,4}$/, "").trim();
}

/** Cantidad de horas válidas por día para un docente según tenga o no horas extra. */
function franjasValidasCount(horasExtra: number, horasDia: number): number {
  return horasExtra > 0 ? horasDia : Math.min(HORAS_SIN_EXTRA, horasDia);
}

function tieneExtra(cfg: GenConfig, docente: string): boolean {
  return (cfg.cfgDocentes[docente]?.horasExtra ?? 0) > 0;
}

/** Deriva la carga académica (docente↔área↔grupo↔horas) desde el formato por-docente. */
export function derivarCarga(
  filas: HorarioDocenteFila[],
  dias: readonly string[] = DIAS,
): CargaItem[] {
  const acc = new Map<string, CargaItem>();
  for (const fila of filas) {
    for (const dia of dias) {
      const jornada = (fila as unknown as Record<string, string[]>)[dia] ?? [];
      for (const slot of jornada) {
        if (!slot || slot === "DESC" || slot === "PEDAG" || slot === "DEESC") continue;
        const grupo = getGrupoFromSlot(slot);
        const area = materiaDeSlot(slot);
        if (!grupo || !area) continue;
        const key = `${fila.docente}|${area}|${grupo}`;
        const item = acc.get(key);
        if (item) item.horas += 1;
        else acc.set(key, { docente: fila.docente, area, grupo, horas: 1 });
      }
    }
  }
  return [...acc.values()].sort(
    (a, b) =>
      a.docente.localeCompare(b.docente, "es") ||
      a.area.localeCompare(b.area, "es") ||
      a.grupo.localeCompare(b.grupo),
  );
}

/** Totales por docente: horas de clase + DESC/PEDAG (2 si tiene extra) vs tope de franjas. */
export function totalesPorDocente(
  carga: CargaItem[],
  cfg: GenConfig,
): Record<string, { horasClase: number; descPedag: number; total: number; tope: number }> {
  const out: Record<string, { horasClase: number; descPedag: number; total: number; tope: number }> = {};
  const docentes = new Set<string>(carga.map((c) => c.docente));
  for (const d of Object.keys(cfg.cfgDocentes)) docentes.add(d);

  for (const d of docentes) {
    const horasClase = carga.filter((c) => c.docente === d).reduce((s, c) => s + c.horas, 0);
    const conExtra = tieneExtra(cfg, d);
    const descPedag = conExtra ? DESC_PEDAG_HORAS : 0;
    const tope = franjasValidasCount(conExtra ? 1 : 0, cfg.horasDia) * cfg.dias.length;
    out[d] = { horasClase, descPedag, total: horasClase + descPedag, tope };
  }
  return out;
}

/** Validación previa (sin generar): docentes que exceden su tope, grupos sobre-llenos. */
export function validarCarga(carga: CargaItem[], cfg: GenConfig): GenConflictoLite[] {
  const conflictos: GenConflictoLite[] = [];
  const totales = totalesPorDocente(carga, cfg);
  for (const [docente, t] of Object.entries(totales)) {
    if (t.total > t.tope) {
      conflictos.push({
        tipo: "carga_excedida",
        docente,
        mensaje: `${docente}: ${t.total}h (${t.horasClase} clase${t.descPedag ? " + 2 DESC/PEDAG" : ""}) supera el tope de ${t.tope} franjas`,
      });
    }
  }

  const capacidadGrupo = cfg.horasDia * cfg.dias.length;
  const horasPorGrupo = new Map<string, number>();
  for (const c of carga) horasPorGrupo.set(c.grupo, (horasPorGrupo.get(c.grupo) ?? 0) + c.horas);
  for (const [grupo, h] of horasPorGrupo) {
    if (h > capacidadGrupo) {
      conflictos.push({
        tipo: "grupo_sobre_lleno",
        grupo,
        mensaje: `Grupo ${grupo}: ${h}h supera la capacidad de ${capacidadGrupo} franjas`,
      });
    }
  }
  return conflictos;
}

// ---------- Generador ----------

interface Leccion {
  docente: string;
  area: string; // AREA_DESCPEDAG para el bloque duro
  grupo: string; // "" para DESC/PEDAG
  tam: 1 | 2;
  dura: boolean; // true ⇒ DESC/PEDAG, no degradar
}

interface Pos {
  dia: string;
  hora: number; // hora inicial; si tam=2, ocupa hora y hora+1
}

interface Estado {
  ocupDoc: Set<string>; // `${docente}|${dia}|${hora}`
  ocupGrupo: Set<string>; // `${grupo}|${dia}|${hora}`
  horario: GenHorario;
  descPedag: Record<string, { dia: string; hora: number }[]>;
  // cuenta de la materia (docente|area|grupo) ya colocada por día — para preferencia blanda
  diasUsadosPorMateria: Map<string, Set<string>>;
}

function keyDoc(d: string, dia: string, h: number): string {
  return `${d}|${dia}|${h}`;
}
function keyGrupo(g: string, dia: string, h: number): string {
  return `${g}|${dia}|${h}`;
}
function keyMateria(l: Leccion): string {
  return `${l.docente}|${l.area}|${l.grupo}`;
}

function asegurarGrupoEstado(estado: Estado, grupo: string, dias: readonly string[], horasDia: number) {
  if (estado.horario[grupo]) return;
  const fila: Record<string, GenCelda[]> = {};
  for (const d of dias) fila[d] = Array.from({ length: horasDia }, () => ({ materia: "", docente: "" }));
  estado.horario[grupo] = fila;
}

function construirLecciones(carga: CargaItem[], cfg: GenConfig): Leccion[] {
  const lecciones: Leccion[] = [];
  for (const item of carga) {
    if (item.horas <= 0) continue;
    let restantes = item.horas;
    const bloques2 = Math.floor(restantes / 2);
    for (let i = 0; i < bloques2; i++) {
      lecciones.push({ docente: item.docente, area: item.area, grupo: item.grupo, tam: 2, dura: false });
      restantes -= 2;
    }
    if (restantes === 1) {
      lecciones.push({ docente: item.docente, area: item.area, grupo: item.grupo, tam: 1, dura: false });
    }
  }
  // Bloque DESC+PEDAG contiguo solo para docentes CON horas extra
  const docentesConCarga = new Set(carga.map((c) => c.docente));
  for (const docente of docentesConCarga) {
    if (tieneExtra(cfg, docente)) {
      lecciones.push({ docente, area: AREA_DESCPEDAG, grupo: "", tam: 2, dura: true });
    }
  }
  return lecciones;
}

/** Posiciones factibles para una lección dado el estado actual (filtradas por reglas duras). */
function posicionesFactibles(l: Leccion, estado: Estado, cfg: GenConfig): Pos[] {
  const out: Pos[] = [];
  const maxHora = franjasValidasCount(cfg.cfgDocentes[l.docente]?.horasExtra ?? 0, cfg.horasDia);
  for (const dia of cfg.dias) {
    for (let h = 0; h < maxHora; h++) {
      if (l.tam === 2) {
        if (h + 1 >= maxHora) continue; // el par debe caber dentro del tope
        if (estado.ocupDoc.has(keyDoc(l.docente, dia, h))) continue;
        if (estado.ocupDoc.has(keyDoc(l.docente, dia, h + 1))) continue;
        if (!l.dura) {
          // bloque de clase: el grupo también debe estar libre en ambas
          if (estado.ocupGrupo.has(keyGrupo(l.grupo, dia, h))) continue;
          if (estado.ocupGrupo.has(keyGrupo(l.grupo, dia, h + 1))) continue;
        }
        out.push({ dia, hora: h });
      } else {
        if (estado.ocupDoc.has(keyDoc(l.docente, dia, h))) continue;
        if (estado.ocupGrupo.has(keyGrupo(l.grupo, dia, h))) continue;
        out.push({ dia, hora: h });
      }
    }
  }
  return out;
}

/** Orden de candidatos por preferencia blanda: penalizar día ya usado por esta materia. */
function ordenarCandidatos(l: Leccion, candidatos: Pos[], estado: Estado): Pos[] {
  if (l.dura) return candidatos;
  const usados = estado.diasUsadosPorMateria.get(keyMateria(l));
  return [...candidatos].sort((a, b) => {
    const pa = usados?.has(a.dia) ? 1 : 0;
    const pb = usados?.has(b.dia) ? 1 : 0;
    return pa - pb;
  });
}

function aplicar(l: Leccion, pos: Pos, estado: Estado, cfg: GenConfig) {
  const horas = l.tam === 2 ? [pos.hora, pos.hora + 1] : [pos.hora];
  for (const h of horas) estado.ocupDoc.add(keyDoc(l.docente, pos.dia, h));

  if (l.dura) {
    // DESC en la primera hora, PEDAG en la segunda — no ocupa grupo
    (estado.descPedag[l.docente] ??= []).push(
      { dia: pos.dia, hora: pos.hora },
      { dia: pos.dia, hora: pos.hora + 1 },
    );
    return;
  }

  asegurarGrupoEstado(estado, l.grupo, cfg.dias, cfg.horasDia);
  for (const h of horas) {
    estado.ocupGrupo.add(keyGrupo(l.grupo, pos.dia, h));
    estado.horario[l.grupo][pos.dia][h] = { materia: l.area, docente: l.docente };
  }
  (estado.diasUsadosPorMateria.get(keyMateria(l)) ??
    estado.diasUsadosPorMateria.set(keyMateria(l), new Set()).get(keyMateria(l))!).add(pos.dia);
}

function revertir(l: Leccion, pos: Pos, estado: Estado) {
  const horas = l.tam === 2 ? [pos.hora, pos.hora + 1] : [pos.hora];
  for (const h of horas) estado.ocupDoc.delete(keyDoc(l.docente, pos.dia, h));

  if (l.dura) {
    const arr = estado.descPedag[l.docente];
    if (arr) {
      // quitar las dos entradas recién añadidas para esta posición
      estado.descPedag[l.docente] = arr.filter(
        (e) => !(e.dia === pos.dia && (e.hora === pos.hora || e.hora === pos.hora + 1)),
      );
    }
    return;
  }

  for (const h of horas) {
    estado.ocupGrupo.delete(keyGrupo(l.grupo, pos.dia, h));
    if (estado.horario[l.grupo]) estado.horario[l.grupo][pos.dia][h] = { materia: "", docente: "" };
  }
  // Nota: no removemos el día de diasUsadosPorMateria (heurística blanda, aproximación aceptable).
}

class TimeoutError extends Error {}

export function generarAutomatico(carga: CargaItem[], cfg: GenConfig): GenResult {
  const t0 = Date.now();
  const maxIter = cfg.maxIteraciones ?? 300_000;
  const timeoutMs = cfg.timeoutMs ?? 5000;

  const estado: Estado = {
    ocupDoc: new Set(),
    ocupGrupo: new Set(),
    horario: {},
    descPedag: {},
    diasUsadosPorMateria: new Map(),
  };

  // MRV estático: ordenar por #posiciones factibles iniciales (ascendente); duras primero.
  const lecciones = construirLecciones(carga, cfg);
  const baseFactibles = new Map<Leccion, number>();
  for (const l of lecciones) baseFactibles.set(l, posicionesFactibles(l, estado, cfg).length);
  lecciones.sort((a, b) => {
    if (a.dura !== b.dura) return a.dura ? -1 : 1; // duras primero
    return (baseFactibles.get(a) ?? 0) - (baseFactibles.get(b) ?? 0);
  });

  const noAsignados: NoAsignado[] = [];
  let iter = 0;

  const colocar = (i: number): boolean => {
    if (i >= lecciones.length) return true;
    if (iter > maxIter || Date.now() - t0 > timeoutMs) throw new TimeoutError();
    iter++;

    const l = lecciones[i];
    const candidatos = ordenarCandidatos(l, posicionesFactibles(l, estado, cfg), estado);

    for (const pos of candidatos) {
      aplicar(l, pos, estado, cfg);
      if (colocar(i + 1)) return true;
      revertir(l, pos, estado);
    }

    // Sin candidato viable
    if (l.dura) return false; // DESC/PEDAG: no degradar, forzar backtrack del nivel previo
    if (l.tam === 2) {
      // Degradar a dos lecciones de 1h y reintentar desde este índice
      lecciones.splice(i, 1, { ...l, tam: 1 }, { ...l, tam: 1 });
      return colocar(i);
    }
    return false;
  };

  let completo = false;
  try {
    completo = colocar(0);
  } catch (e) {
    if (!(e instanceof TimeoutError)) throw e;
    completo = false;
  }

  if (!completo) {
    // Modo parcial: greedy sin backtracking sobre lo que falte, acumular no-asignados.
    colocarGreedyParcial(lecciones, estado, cfg, noAsignados);
  }

  const conflictos = validarPostGeneracion(estado, cfg, noAsignados);

  return {
    horario: estado.horario,
    descPedag: estado.descPedag,
    noAsignados,
    conflictos,
    ok: noAsignados.length === 0,
    iteraciones: iter,
    ms: Date.now() - t0,
  };
}

/** Greedy de respaldo: coloca cada lección en el primer hueco; lo que no cabe → noAsignados. */
function colocarGreedyParcial(
  lecciones: Leccion[],
  estado: Estado,
  cfg: GenConfig,
  noAsignados: NoAsignado[],
) {
  // Empezar de cero para evitar estado parcial inconsistente del backtracking fallido.
  estado.ocupDoc.clear();
  estado.ocupGrupo.clear();
  estado.horario = {};
  estado.descPedag = {};
  estado.diasUsadosPorMateria.clear();

  // Ordenar: duras primero, luego bloques de 2, luego 1h.
  const orden = [...lecciones].sort((a, b) => {
    if (a.dura !== b.dura) return a.dura ? -1 : 1;
    return b.tam - a.tam;
  });

  for (const l of orden) {
    const candidatos = ordenarCandidatos(l, posicionesFactibles(l, estado, cfg), estado);
    if (candidatos.length > 0) {
      aplicar(l, candidatos[0], estado, cfg);
    } else if (l.tam === 2 && !l.dura) {
      // intentar como dos sueltas
      const a: Leccion = { ...l, tam: 1 };
      let colocadas = 0;
      for (let k = 0; k < 2; k++) {
        const cs = posicionesFactibles(a, estado, cfg);
        if (cs.length > 0) {
          aplicar(a, cs[0], estado, cfg);
          colocadas++;
        }
      }
      if (colocadas < 2) {
        noAsignados.push({
          docente: l.docente,
          area: l.area,
          grupo: l.grupo,
          horasFaltantes: 2 - colocadas,
          motivo: "sin_hueco",
        });
      }
    } else {
      noAsignados.push({
        docente: l.docente,
        area: l.dura ? "" : l.area,
        grupo: l.dura ? "" : l.grupo,
        horasFaltantes: l.tam,
        motivo: l.dura ? "descpedag_sin_par" : "sin_hueco",
      });
    }
  }
}

function validarPostGeneracion(
  estado: Estado,
  cfg: GenConfig,
  noAsignados: NoAsignado[],
): GenConflictoLite[] {
  const conflictos: GenConflictoLite[] = [];
  // DESC/PEDAG faltante: docente con extra que no obtuvo su par
  for (const docente of Object.keys(cfg.cfgDocentes)) {
    if (tieneExtra(cfg, docente)) {
      const arr = estado.descPedag[docente];
      if (!arr || arr.length < DESC_PEDAG_HORAS) {
        // Solo reportar si el docente aparece en el horario (tiene clases asignadas)
        const apareceEnHorario = Object.values(estado.horario).some((fila) =>
          cfg.dias.some((d) => fila[d]?.some((c) => c.docente === docente)),
        );
        if (apareceEnHorario) {
          conflictos.push({
            tipo: "descpedag_faltante",
            docente,
            mensaje: `${docente}: no se pudo ubicar el bloque DESC+PEDAG contiguo`,
          });
        }
      }
    }
  }
  // Sumar también los descpedag_sin_par de noAsignados
  for (const na of noAsignados) {
    if (na.motivo === "descpedag_sin_par") {
      conflictos.push({
        tipo: "descpedag_faltante",
        docente: na.docente,
        mensaje: `${na.docente}: bloque DESC+PEDAG sin par contiguo disponible`,
      });
    }
  }
  return conflictos;
}
