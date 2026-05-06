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
