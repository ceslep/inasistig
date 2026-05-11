export type Rol = 'coordinador' | 'secretario' | 'miembro'
export type EstadoAcuerdo = 'pendiente' | 'en_curso' | 'cerrado'
export type Voto = 'si' | 'no' | 'abstencion' | 'na'

export interface Participante {
  nombre: string
  rol: Rol
  firma?: string
}

export interface OrdenItem {
  id?: string
  descripcion: string
  responsable: string
  tiempoMin: number
  temaPredefinidoId?: string
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

export interface TemaPredefinido {
  id: string
  label: string
  tiempo: number
  categoria: string
}

export const TEMAS_ORDEN_DIA: Record<string, TemaPredefinido[]> = {
  basico: [
    { id: 'quorum', label: 'Verificación de quórum', tiempo: 5, categoria: 'basico' },
    { id: 'aprobacion_agenda', label: 'Aprobación del orden del día', tiempo: 5, categoria: 'basico' },
    { id: 'acta_anterior', label: 'Revisión y aprobación de acta anterior', tiempo: 10, categoria: 'basico' },
    { id: 'lectura_aprobacion', label: 'Lectura y aprobación del acta', tiempo: 5, categoria: 'basico' },
  ],
  academico: [
    { id: 'resultados', label: 'Análisis de resultados académicos', tiempo: 20, categoria: 'academico' },
    { id: 'planeacion', label: 'Revisión de planeación curricular', tiempo: 15, categoria: 'academico' },
    { id: 'estrategias', label: 'Evaluación de estrategias pedagógicas', tiempo: 15, categoria: 'academico' },
    { id: 'contenidos', label: 'Revisión de contenidos y estándares', tiempo: 10, categoria: 'academico' },
    { id: 'recursos', label: 'Revisión de recursos y materiales educativos', tiempo: 10, categoria: 'academico' },
    { id: 'actividades', label: 'Programación de actividades académicas', tiempo: 10, categoria: 'academico' },
    { id: 'proyectos', label: 'Proyectos pedagógicos transversales', tiempo: 15, categoria: 'academico' },
  ],
  institucional: [
    { id: 'seguimiento_acuerdos', label: 'Seguimiento a acuerdos anteriores', tiempo: 15, categoria: 'institucional' },
    { id: 'coordinacion_areas', label: 'Coordinación con otras áreas', tiempo: 10, categoria: 'institucional' },
    { id: 'pei', label: 'Vinculación con PEI y Plan de Mejoramiento', tiempo: 10, categoria: 'institucional' },
    { id: 'convivencia', label: 'Revisión de acuerdos del manual de convivencia', tiempo: 10, categoria: 'institucional' },
    { id: 'casos_especiales', label: 'Análisis de casos de estudiantes', tiempo: 15, categoria: 'institucional' },
  ],
  formacion: [
    { id: 'capacitaciones', label: 'Socialización de formaciones/capacitaciones', tiempo: 15, categoria: 'formacion' },
    { id: 'innovacion', label: 'Propuestas de innovación educativa', tiempo: 15, categoria: 'formacion' },
    { id: 'experiencias', label: 'Análisis de experiencias exitosas', tiempo: 10, categoria: 'formacion' },
  ],
  administrativo: [
    { id: 'evaluaciones', label: 'Programación de evaluaciones', tiempo: 10, categoria: 'administrativo' },
    { id: 'horarios', label: 'Coordinación de horarios y espacios', tiempo: 10, categoria: 'administrativo' },
    { id: 'padres', label: 'Coordinación de reuniones con padres', tiempo: 10, categoria: 'administrativo' },
    { id: 'recursos_compra', label: 'Gestión de recursos y compras', tiempo: 10, categoria: 'administrativo' },
    { id: 'informes', label: 'Consolidación de informes', tiempo: 10, categoria: 'administrativo' },
  ],
}

export const PLANTILLAS_ACUERDOS: { value: string; label: string }[] = [
  { value: 'Elaborar/actualizar planeación didáctica para [asignatura/grado]', label: 'Elaborar/actualizar planeación didáctica' },
  { value: 'Revisar y ajustar contenidos del currículo', label: 'Revisar y ajustar contenidos del currículo' },
  { value: 'Diseñar instrumentos de evaluación formativa', label: 'Diseñar instrumentos de evaluación formativa' },
  { value: 'Preparar materiales y recursos didácticos', label: 'Preparar materiales y recursos didácticos' },
  { value: 'Socializar estrategias pedagógicas en próxima sesión', label: 'Socializar estrategias pedagógicas' },
  { value: 'Realizar seguimiento a estudiantes en riesgo académico', label: 'Realizar seguimiento a estudiantes en riesgo' },
  { value: 'Aplicar plan de apoyo a estudiantes con bajo rendimiento', label: 'Aplicar plan de apoyo a estudiantes' },
  { value: 'Documentar casos especiales para revisión', label: 'Documentar casos especiales para revisión' },
  { value: 'Generar informes de avance por grado', label: 'Generar informes de avance por grado' },
  { value: 'Registrar casos en plataforma institucional', label: 'Registrar casos en plataforma institucional' },
  { value: 'Coordinar actividades culturales con [área/proyecto]', label: 'Coordinar actividades culturales' },
  { value: 'Programar reunión con padres de familia', label: 'Programar reunión con padres de familia' },
  { value: 'Gestionar recursos ante Dirección', label: 'Gestionar recursos ante Dirección' },
  { value: 'Consolidar información para informes institucionales', label: 'Consolidar información para informes' },
  { value: 'Coordinar uso de laboratorio/taller/sala', label: 'Coordinar uso de laboratorio/taller/sala' },
  { value: 'Aplicar rúbricas de evaluación acordadas', label: 'Aplicar rúbricas de evaluación acordadas' },
  { value: 'Estandarizar criterios de evaluación por asignatura', label: 'Estandarizar criterios de evaluación' },
  { value: 'Registrar resultados en plataforma/SIEE', label: 'Registrar resultados en plataforma/SIEE' },
  { value: 'Revisar criterios de promoción', label: 'Revisar criterios de promoción' },
  { value: 'Preparar proceso de recuperación', label: 'Preparar proceso de recuperación' },
  { value: 'Gestionar compra de materiales/recursos', label: 'Gestionar compra de materiales/recursos' },
  { value: 'Actualizar documentación del área', label: 'Actualizar documentación del área' },
  { value: 'Consolidar informes mensuales/trimestrales', label: 'Consolidar informes mensuales/trimestrales' },
  { value: 'Archivar documentos del área', label: 'Archivar documentos del área' },
  { value: 'Programar evaluaciones en calendario', label: 'Programar evaluaciones en calendario' },
  { value: 'Asistir a capacitación sobre [tema]', label: 'Asistir a capacitación' },
  { value: 'Socializar conocimientos en próxima reunión de área', label: 'Socializar conocimientos en reunión' },
  { value: 'Elaborar memoria de aprendizaje del taller', label: 'Elaborar memoria de aprendizaje' },
  { value: 'Gestionar inscripción a evento de formación', label: 'Gestionar inscripción a evento' },
]

export const CATEGORIAS_TEMAS: Record<string, string> = {
  basico: 'Básico',
  academico: 'Académico',
  institucional: 'Institucional',
  formacion: 'Formación',
  administrativo: 'Administrativo',
}

export const CATEGORIAS_ACUERDOS: Record<string, string> = {
  planeacion: 'Planeación',
  seguimiento: 'Seguimiento',
  coordinacion: 'Coordinación',
  evaluacion: 'Evaluación',
  administrativo: 'Administrativo',
  formacion: 'Formación',
}
