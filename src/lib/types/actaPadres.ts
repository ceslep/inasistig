export type TipoReunionPadres = 'ordinaria' | 'extraordinaria' | 'evaluacion' | 'emergencia'
export type RolParticipante = 'padre' | 'madre' | 'acudiente' | 'docente' | 'coordinador' | 'director' | 'invitado'
export type EstadoCompromiso = 'pendiente' | 'en_proceso' | 'cumplido'

export const TIPOS_REUNION_PADRES: { value: TipoReunionPadres; label: string; descripcion: string }[] = [
  { value: 'ordinaria', label: 'Ordinaria', descripcion: 'Reunión programada del calendario institucional' },
  { value: 'extraordinaria', label: 'Extraordinaria', descripcion: 'Convocada por necesidades especiales' },
  { value: 'evaluacion', label: 'Evaluación', descripcion: 'Socialización de resultados académicos' },
  { value: 'emergencia', label: 'Emergencia', descripcion: 'Situación urgente que requiere atención inmediata' },
]

export const ROLES_PARTICIPANTE: { value: RolParticipante; label: string }[] = [
  { value: 'padre', label: 'Padre' },
  { value: 'madre', label: 'Madre' },
  { value: 'acudiente', label: 'Acudiente' },
  { value: 'docente', label: 'Docente' },
  { value: 'coordinador', label: 'Coordinador' },
  { value: 'director', label: 'Director' },
  { value: 'invitado', label: 'Invitado' },
]

export const TEMAS_AGENDA_PREDEFINIDOS = [
  'Bienvenida y verificación de quórum',
  'Revisión y aprobación del orden del día',
  'Avance académico y comportamental',
  'Cumplimiento de tareas y actividades',
  'Convivencia escolar y resolución de conflictos',
  'Eventos institucionales próximos',
  'Peticiones y sugerencias de los padres',
  'Acuerdos y compromisos',
  'Varios',
]

export const ESTADOS_COMPROMISO: { value: EstadoCompromiso; label: string; color: string }[] = [
  { value: 'pendiente', label: 'Pendiente', color: 'red' },
  { value: 'en_proceso', label: 'En proceso', color: 'amber' },
  { value: 'cumplido', label: 'Cumplido', color: 'emerald' },
]

export const LUGARES_REUNION = [
  { value: 'auditorio', label: 'Auditorio' },
  { value: 'aula_clase', label: 'Aula de clase' },
  { value: 'sala_audiovisual', label: 'Sala audiovisual' },
  { value: 'cancha_deportiva', label: 'Cancha deportiva' },
  { value: 'cafeteria', label: 'Cafetería' },
  { value: 'patio', label: 'Patio' },
  { value: 'oficina_coordinacion', label: 'Oficina de coordinación' },
  { value: 'virtual', label: 'Virtual (videollamada)' },
  { value: 'otro', label: 'Otro' },
]

export interface ParticipanteReunion {
  nombre: string
  rol: RolParticipante
  hijosEnInstitucion: number
  firma?: string
  hijosGrados?: string
}

export interface TemaAgenda {
  nombre: string
  descripcion: string
  tratado: boolean
}

export interface CompromisoReunion {
  actividad: string
  responsable: string
  fechaLimite: string
  estado: EstadoCompromiso
}

export interface FirmaActaReunion {
  nombre: string
  rol: string
  firma?: string
}

export interface ActaReunionPadres {
  id: string
  institucion: string
  tipo: TipoReunionPadres
  fecha: string
  horaInicio: string
  horaFin: string
  lugar: string
  grado?: string
  grupo?: string
  temaPrincipal: string
  participantes: ParticipanteReunion[]
  temasAgenda: TemaAgenda[]
  acuerdos: string
  compromisos: CompromisoReunion[]
  observacionesGenerales: string
  proxFechaReunion?: string
  actaLeidaAprobada: boolean
  firmas: FirmaActaReunion[]
  fotos: string[]
  fotosFirmas: string[]
  docenteCreador: string
  createdAt: string
}

export interface ActaReunionPadresPayload {
  spreadsheetId: string
  worksheetTitle: string
  datos: string[][]
}

export const BASE_LEGAL_TEXT_PADRES =
  'La presente acta se ajusta a lo dispuesto en la Ley 115 de 1994 (artículos 22° y 23°), el Decreto 1286 de 2005 (artículo 23°) y el Decreto 1860 de 1994 (artículos 16 al 20), en lo correspondiente a la participación de la familia en el proceso educativo.'

export const ACTIVIDADES_PREDETERMINADAS_PADRES = [
  { orden: 1, nombre: 'Bienvenida', descripcion: 'Saludo y verificación de quórum', tiempoMin: 5 },
  { orden: 2, nombre: 'Orden del día', descripcion: 'Revisión y aprobación de temas a tratar', tiempoMin: 5 },
  { orden: 3, nombre: 'Exposicion del tema principal', descripcion: 'Presentación del tema por parte del docente o invitado', tiempoMin: 20 },
  { orden: 4, nombre: 'Intervención de padres', descripcion: 'Comentarios, preguntas y aportes de los asistentes', tiempoMin: 15 },
  { orden: 5, nombre: 'Acuerdos', descripcion: 'Registro de compromisos y tareas', tiempoMin: 10 },
  { orden: 6, nombre: 'Cierre', descripcion: 'Resumen y próxima convocatoria', tiempoMin: 5 },
]

export const MAX_FOTOS_PADRES = 4
export const MAX_FOTOS_FIRMAS_PADRES = 4