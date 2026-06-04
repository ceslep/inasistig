export interface Festivo {
  fecha: string;
  nombre: string;
  dia_semana: string;
  tipo: string;
}

export const festivos: Festivo[] = [
  {
    fecha: "2026-01-01",
    nombre: "Año Nuevo",
    dia_semana: "Jue",
    tipo: "Fijo",
  },
  {
    fecha: "2026-01-12",
    nombre: "Día de los Reyes Magos",
    dia_semana: "Lun",
    tipo: "Ley Emiliani",
  },
  {
    fecha: "2026-03-23",
    nombre: "Día de San José",
    dia_semana: "Lun",
    tipo: "Ley Emiliani",
  },
  {
    fecha: "2026-04-02",
    nombre: "Jueves Santo",
    dia_semana: "Jue",
    tipo: "Religioso (Variable)",
  },
  {
    fecha: "2026-04-03",
    nombre: "Viernes Santo",
    dia_semana: "Vie",
    tipo: "Religioso (Variable)",
  },
  {
    fecha: "2026-05-01",
    nombre: "Día del Trabajo",
    dia_semana: "Vie",
    tipo: "Fijo",
  },
  {
    fecha: "2026-05-18",
    nombre: "Ascensión del Señor",
    dia_semana: "Lun",
    tipo: "Ley Emiliani",
  },
  {
    fecha: "2026-06-08",
    nombre: "Corpus Christi",
    dia_semana: "Lun",
    tipo: "Ley Emiliani",
  },
  {
    fecha: "2026-06-15",
    nombre: "Sagrado Corazón de Jesús",
    dia_semana: "Lun",
    tipo: "Ley Emiliani",
  },
  {
    fecha: "2026-06-29",
    nombre: "San Pedro y San Pablo",
    dia_semana: "Lun",
    tipo: "Ley Emiliani",
  },
  {
    fecha: "2026-07-20",
    nombre: "Día de la Independencia",
    dia_semana: "Lun",
    tipo: "Fijo",
  },
  {
    fecha: "2026-08-07",
    nombre: "Batalla de Boyacá",
    dia_semana: "Vie",
    tipo: "Fijo",
  },
  {
    fecha: "2026-08-17",
    nombre: "La Asunción de la Virgen",
    dia_semana: "Lun",
    tipo: "Ley Emiliani",
  },
  {
    fecha: "2026-10-12",
    nombre: "Día de la Raza",
    dia_semana: "Lun",
    tipo: "Ley Emiliani",
  },
  {
    fecha: "2026-11-02",
    nombre: "Todos los Santos",
    dia_semana: "Lun",
    tipo: "Ley Emiliani",
  },
  {
    fecha: "2026-11-16",
    nombre: "Independencia de Cartagena",
    dia_semana: "Lun",
    tipo: "Ley Emiliani",
  },
  {
    fecha: "2026-12-08",
    nombre: "Inmaculada Concepción",
    dia_semana: "Mar",
    tipo: "Fijo",
  },
  {
    fecha: "2026-12-25",
    nombre: "Navidad",
    dia_semana: "Vie",
    tipo: "Fijo",
  },
];

export function esFestivo(fecha: string, lista: Festivo[] = festivos): boolean {
  return lista.some((f) => f.fecha === fecha);
}

function formatearFecha(fecha: Date): string {
  const y = fecha.getFullYear();
  const m = String(fecha.getMonth() + 1).padStart(2, "0");
  const d = String(fecha.getDate()).padStart(2, "0");
  return `${y}-${m}-${d}`;
}

export interface ResolucionFecha {
  /** Fecha final seleccionada (YYYY-MM-DD), ya saltando festivos. */
  fecha: string;
  /** Día de la semana real de `fecha` (lunes..viernes). "" si cae fuera de L-V. */
  dia: string;
  /** true si el día originalmente solicitado era festivo y se reubicó. */
  eraFestivo: boolean;
  /** Nombre del festivo original, si aplica. */
  nombreFestivo: string;
}

const NOMBRES_DIA: Record<number, string> = {
  1: "lunes",
  2: "martes",
  3: "miercoles",
  4: "jueves",
  5: "viernes",
};

/**
 * Resuelve la fecha para un día de la semana (diaIdx 0=lunes..4=viernes) relativo
 * a `hoyStr`. Reglas:
 * - Si ese día ya pasó esta semana, usa el de la próxima semana (rollover por día).
 * - Si la fecha resultante es festivo, salta al siguiente día hábil y marca eraFestivo.
 */
export function resolverFechaDia(
  hoyStr: string,
  diaIdx: number,
  lista: Festivo[] = festivos
): ResolucionFecha {
  const hoy = new Date(hoyStr + "T00:00:00");
  const diaSemanaHoy = hoy.getDay();
  const lunes = new Date(hoy);
  lunes.setDate(hoy.getDate() - (diaSemanaHoy === 0 ? 6 : diaSemanaHoy - 1));
  const hoySinHora = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());

  const d = new Date(lunes);
  d.setDate(lunes.getDate() + diaIdx);
  // Rollover por día: si ese día ya pasó esta semana, usar el de la próxima.
  if (d < hoySinHora) {
    d.setDate(d.getDate() + 7);
  }

  const fechaSolicitada = formatearFecha(d);
  const festivo = lista.find((f) => f.fecha === fechaSolicitada);

  if (!festivo) {
    return { fecha: fechaSolicitada, dia: NOMBRES_DIA[d.getDay()] ?? "", eraFestivo: false, nombreFestivo: "" };
  }

  // Día festivo: reubicar al siguiente día hábil.
  const fechaFinal = siguienteDiaHabil(fechaSolicitada, lista);
  const dFinal = new Date(fechaFinal + "T00:00:00");
  return {
    fecha: fechaFinal,
    dia: NOMBRES_DIA[dFinal.getDay()] ?? "",
    eraFestivo: true,
    nombreFestivo: festivo.nombre,
  };
}

export function siguienteDiaHabil(fechaStr: string, lista: Festivo[] = festivos): string {
  const fecha = new Date(fechaStr + "T00:00:00");
  fecha.setDate(fecha.getDate() + 1);
  while (true) {
    const dayOfWeek = fecha.getDay();
    if (dayOfWeek === 0) {
      fecha.setDate(fecha.getDate() + 1);
      continue;
    }
    if (dayOfWeek === 6) {
      fecha.setDate(fecha.getDate() + 2);
      continue;
    }
    const y = fecha.getFullYear();
    const m = String(fecha.getMonth() + 1).padStart(2, "0");
    const d = String(fecha.getDate()).padStart(2, "0");
    const fechaFormateada = `${y}-${m}-${d}`;
    if (!esFestivo(fechaFormateada, lista)) {
      return fechaFormateada;
    }
    fecha.setDate(fecha.getDate() + 1);
  }
}