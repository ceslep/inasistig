import type { HorarioDocente, CoberturaHistorica, Ausencia } from "../coberturaUtils";

export const horariosTest: HorarioDocente[] = [
  {
    docente: "Ana",
    lunes: ["MAT-101", "", "FIS-201", "", "", "", ""],
    martes: ["", "MAT-101", "", "QUIM-101", "", "", ""],
    miercoles: ["FIS-201", "", "", "MAT-101", "", "", ""],
    jueves: ["", "", "MAT-101", "", "QUIM-101", "", ""],
    viernes: ["", "FIS-201", "", "", "MAT-101", "", ""],
  },
  {
    docente: "Carlos",
    lunes: ["", "FIS-201", "", "MAT-101", "", "", ""],
    martes: ["MAT-101", "", "", "", "FIS-201", "", ""],
    miercoles: ["", "QUIM-101", "", "", "MAT-101", "", ""],
    jueves: ["FIS-201", "", "", "", "", "QUIM-101", ""],
    viernes: ["", "", "MAT-101", "", "", "", "FIS-201"],
  },
  {
    docente: "María",
    lunes: ["QUIM-101", "", "", "FIS-201", "", "", ""],
    martes: ["", "", "QUIM-101", "", "MAT-101", "", ""],
    miercoles: ["MAT-101", "", "FIS-201", "", "", "", ""],
    jueves: ["", "MAT-101", "", "QUIM-101", "", "", ""],
    viernes: ["FIS-201", "", "", "", "", "MAT-101", ""],
  },
];

export const horariosTestConHora7Libre: HorarioDocente[] = [
  {
    docente: "Ana",
    lunes: ["MAT-101", "", "FIS-201", "", "", "", ""],
    martes: ["", "MAT-101", "", "QUIM-101", "", "", ""],
    miercoles: ["FIS-201", "", "", "MAT-101", "", "", ""],
    jueves: ["", "", "MAT-101", "", "QUIM-101", "", ""],
    viernes: ["", "FIS-201", "", "", "MAT-101", "", ""],
  },
  {
    docente: "Carlos",
    lunes: ["", "FIS-201", "", "MAT-101", "", "", ""],
    martes: ["MAT-101", "", "", "", "FIS-201", "", ""],
    miercoles: ["", "QUIM-101", "", "", "MAT-101", "", ""],
    jueves: ["FIS-201", "", "", "", "", "QUIM-101", ""],
    viernes: ["", "", "MAT-101", "", "", "", ""],
  },
  {
    docente: "María",
    lunes: ["QUIM-101", "", "", "FIS-201", "", "", ""],
    martes: ["", "", "QUIM-101", "", "MAT-101", "", ""],
    miercoles: ["MAT-101", "", "FIS-201", "", "", "", ""],
    jueves: ["", "MAT-101", "", "QUIM-101", "", "", ""],
    viernes: ["FIS-201", "", "", "", "", "MAT-101", ""],
  },
];

export const coberturasHistoricasTest: CoberturaHistorica[] = [
  { fecha: "2026-05-11", dia_semana: "lunes", hora: 1, docente_ausente: "Ana", grupo_ausente: "", docente_cubre: "Carlos", grupo_a_cubrir: "101", estado: "aprobado", motivo: "MEDICO" },
  { fecha: "2026-05-11", dia_semana: "lunes", hora: 2, docente_ausente: "Ana", grupo_ausente: "", docente_cubre: "María", grupo_a_cubrir: "201", estado: "aprobado", motivo: "MEDICO" },
  { fecha: "2026-05-12", dia_semana: "martes", hora: 0, docente_ausente: "Carlos", grupo_ausente: "", docente_cubre: "Ana", grupo_a_cubrir: "101", estado: "aprobado", motivo: "FAMILIAR" },
];

export const coberturasHistoricasSemanaCompleta: CoberturaHistorica[] = [
  { fecha: "2026-05-18", dia_semana: "lunes", hora: 0, docente_ausente: "Ana", grupo_ausente: "", docente_cubre: "Carlos", grupo_a_cubrir: "101", estado: "aprobado", motivo: "MEDICO" },
  { fecha: "2026-05-18", dia_semana: "lunes", hora: 1, docente_ausente: "Ana", grupo_ausente: "", docente_cubre: "María", grupo_a_cubrir: "201", estado: "aprobado", motivo: "MEDICO" },
];

export const ausenciaDocenteAna: Ausencia[] = [
  { tipo: "docente", nombre: "Ana", motivo: "MEDICO" },
];

export const ausenciaDocenteCarlos: Ausencia[] = [
  { tipo: "docente", nombre: "Carlos", motivo: "FAMILIAR" },
];

export const ausenciaGrupo101: Ausencia[] = [
  { tipo: "grupo", nombre: "101", horaInicio: 5, motivo: "GRUPO AUSENTE" },
];

export const ausenciaMixtha: Ausencia[] = [
  { tipo: "docente", nombre: "Ana", motivo: "MEDICO" },
  { tipo: "docente", nombre: "Carlos", motivo: "FAMILIAR" },
  { tipo: "grupo", nombre: "101", horaInicio: 5, motivo: "GRUPO AUSENTE" },
];
