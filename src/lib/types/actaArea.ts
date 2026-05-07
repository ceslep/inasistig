export type Rol = 'coordinador' | 'secretario' | 'miembro'
export type EstadoAcuerdo = 'pendiente' | 'en_curso' | 'cerrado'
export type Voto = 'si' | 'no' | 'abstencion' | 'na'

export interface Participante {
  nombre: string
  rol: Rol
  firma?: string
}

export interface OrdenItem {
  descripcion: string
  responsable: string
  tiempoMin: number
}

export interface DesarrolloItem {
  temaIndex: number
  discusion: string
  decisiones: string
  votacion: Voto
}

export interface AcuerdoItem {
  actividad: string
  responsable: string
  fechaLimite: string
  estado: EstadoAcuerdo
}

export interface ProximaReunion {
  fecha: string
  hora: string
  lugar: string
}

export interface ActaReunion {
  id: string
  institucion: string
  areaAcademica: string
  asignaturas: string[]
  grados: string[]
  fecha: string
  horaInicio: string
  horaFin: string
  lugar: string
  participantes: Participante[]
  ordenDia: OrdenItem[]
  desarrollo: DesarrolloItem[]
  acuerdos: AcuerdoItem[]
  proxima: ProximaReunion
  actaLeidaAprobada: boolean
  firmaCoordinador: string
  firmaSecretario: string
  docenteCreador: string
  createdAt: string
}

export interface ActaPayload {
  spreadsheetId: string
  worksheetTitle: string
  datos: string[][]
}

export const AREAS_ACADEMICAS = [
  'Matemáticas',
  'Lengua Castellana',
  'Inglés',
  'Ciencias Naturales y Educación Ambiental',
  'Ciencias Sociales',
  'Filosofía',
  'Educación Artística',
  'Educación Física, Recreación y Deportes',
  'Tecnología e Informática',
  'Educación Religiosa, Ética y Valores Humanos',
  'Emprendimiento',
  'Cátedra de la Paz',
  'Dirección de Grupo',
  'Coordinación Académica',
] as const

export const GRADOS = ['6°', '7°', '8°', '9°', '10°', '11°'] as const

export const ROLES: { value: Rol; label: string }[] = [
  { value: 'coordinador', label: 'Coordinador de área' },
  { value: 'secretario', label: 'Secretario ad hoc' },
  { value: 'miembro', label: 'Miembro' },
]

export const ESTADOS_ACUERDO: { value: EstadoAcuerdo; label: string }[] = [
  { value: 'pendiente', label: 'Pendiente' },
  { value: 'en_curso', label: 'En curso' },
  { value: 'cerrado', label: 'Cerrado' },
]

export const VOTOS: { value: Voto; label: string }[] = [
  { value: 'na', label: 'No aplica' },
  { value: 'si', label: 'Sí' },
  { value: 'no', label: 'No' },
  { value: 'abstencion', label: 'Abstención' },
]
