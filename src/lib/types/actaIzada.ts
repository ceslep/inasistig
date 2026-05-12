export type TipoIzada = 'semanal' | 'especial' | 'patriotica'
export type MomentoDia = 'matutino' | 'vespertino'
export type Grade = '6°' | '7°' | '8°' | '9°' | '10°' | '11°'
export type HimnoTipo = 'nacional' | 'departamental' | 'municipal' | 'institucional'

export const LUGARES_IZADA = [
  { value: 'auditorio', label: 'Auditorio' },
  { value: 'aula_clase', label: 'Aula de clase' },
  { value: 'canchas_deportivas', label: 'Canchas deportivas' },
  { value: 'patio_principal', label: 'Patio principal' },
  { value: 'espacio_abierto', label: 'Espacio abierto' },
  { value: 'otros', label: 'Otros' },
] as const

export const GRADOS_BACHILLERATO_MULTI = [
  { value: '6°', label: '6° Sexto' },
  { value: '7°', label: '7° Séptimo' },
  { value: '8°', label: '8° Octavo' },
  { value: '9°', label: '9° Noveno' },
  { value: '10°', label: '10° Décimo' },
  { value: '11°', label: '11° Undécimo' },
] as const

export const AREAS_IZADA = [
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

export const HIMNOS = [
  { value: 'nacional', label: 'Himno Nacional de Colombia', abrev: 'HN' },
  { value: 'departamental', label: 'Himno del Departamento de Risaralda', abrev: 'HR' },
  { value: 'municipal', label: 'Himno del Municipio de Guática', abrev: 'HM' },
  { value: 'institucional', label: 'Himno de la I.E. Instituto Guática', abrev: 'HI' },
] as const

export const MAX_FOTOS_IZADA = 4

export interface ParticipanteActa {
  nombre: string
  rol: 'docente' | 'estudiante' | 'coordinador' | 'director'
  cantidad?: number
}

export interface ItemDesarrollo {
  orden: number
  actividad: string
  descripcion: string
  responsable: string
  tiempoMin: number
}

export interface Conclusion {
  texto: string
  cumplida: boolean
}

export interface Compromiso {
  actividad: string
  responsable: string
  fechaLimite: string
  estado: 'pendiente' | 'en_curso' | 'cumplido'
}

export interface FirmaActa {
  nombre: string
  rol: string
  firma?: string
}

export interface ActaIzada {
  id: string
  institucion: string
  tipo: TipoIzada
  momento: MomentoDia
  fecha: string
  horaInicio: string
  horaFin: string
  lugar: string
  grados: string[]
  grupos: string[]
  areaAcademica: string
  areasAcademicas: string[]
  temaPrincipal: string
  subtema?: string
  participantes: ParticipanteActa[]
  desarrollo: ItemDesarrollo[]
  conclusiones: Conclusion[]
  compromisos: Compromiso[]
  lema?: string
  himno?: boolean
  himnosRisaralda: string[]
  promesa?: boolean
  discurso?: boolean
  minutoSilencio?: boolean
  actaLeidaAprobada: boolean
  firmas: FirmaActa[]
  fotos: string[]
  docenteCreador: string
  createdAt: string
}

export interface ActaIzadaPayload {
  spreadsheetId: string
  worksheetTitle: string
  datos: string[][]
}

export const TIPOS_IZADA: { value: TipoIzada; label: string; descripcion: string }[] = [
  { value: 'semanal', label: 'Semanal', descripcion: 'Izada rutinaria del lunes' },
  { value: 'especial', label: 'Especial', descripcion: 'Fecha patria o evento institucional' },
  { value: 'patriotica', label: 'Patriótica', descripcion: 'Celebración cívica especial' },
]

export const MOMENTOS_DIA: { value: MomentoDia; label: string }[] = [
  { value: 'matutino', label: 'Matutina' },
  { value: 'vespertino', label: 'Vespertina' },
]

export const GRADOS_BACHILLERATO: { value: Grade; label: string }[] = [
  { value: '6°', label: 'Sexto' },
  { value: '7°', label: 'Séptimo' },
  { value: '8°', label: 'Octavo' },
  { value: '9°', label: 'Noveno' },
  { value: '10°', label: 'Décimo' },
  { value: '11°', label: 'Undécimo' },
]

export const ACTIVIDADES_PREDETERMINADAS = [
  { orden: 1, actividad: 'Formación general', descripcion: 'Organización de estudiantes en formación', responsable: 'Docente titular', tiempoMin: 5 },
  { orden: 2, actividad: 'izar_bandera', descripcion: 'izar el pabellón nacional', responsable: 'Estudiante designado', tiempoMin: 3 },
  { orden: 3, actividad: 'Himno Nacional', descripcion: 'Canto colectivo del himno nacional', responsable: 'Todos', tiempoMin: 3 },
  { orden: 4, actividad: 'Promesa a la Bandera', descripcion: 'Juramento patriótico', responsable: 'Estudiante líder', tiempoMin: 3 },
  { orden: 5, actividad: 'Palabras alusivas', descripcion: 'Discurso o reflexión sobre el tema', responsable: 'Docente o invitado', tiempoMin: 10 },
  { orden: 6, actividad: 'Minuto de silencio', descripcion: 'Ruego y reflexión', responsable: 'Todos', tiempoMin: 1 },
  { orden: 7, actividad: 'Cierre', descripcion: 'Indicaciones y despedida', responsable: 'Docente titular', tiempoMin: 5 },
]

export const TEMAS_IZADA_COMUNES = [
  'Cívico-patriótico',
  'Día de la Constitución',
  'Día de la Raza',
  'Independencia Nacional',
  'Batalla de Boyacá',
  'Héroes de la Patria',
  'Día del idioma',
  'Semana de la paz',
  'Día del estudiante',
  'Día del maestro',
  'Día de la familia',
  'Navidad y tradiciones',
  'Año Nuevo',
  'Derechos humanos',
  'Día de la mujer',
  'Día del trabajo',
  'Combate naval de Riohacha',
  'Batalla de Carabobo',
  'Independencia de Cartagena',
  'Gesta de los libertadores',
  'Patria y soberanía',
  'Identidad nacional',
  'Valor histórico regional',
  'Fomento a la lectura',
  'Cuidado del medio ambiente',
  'Convivencia escolar',
]

export const BASE_LEGAL_TEXT = 'La presente acta se ajusta a lo dispuesto en la Ley 115 de 1994 (artículos 5°, 22° y 23°) y el Decreto 1860 de 1994 (artículos 36 al 40) en lo correspondiente a ceremonies cívicas y formación patriótica.'