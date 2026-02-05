<?php
declare(strict_types=1);
include_once("cors.php");

/**
 * Base de Datos de Situaciones Escolares
 * Sistema de Gestión para Bachillerato Colombiano
 * 
 * @author César Leandro Patiño Vélez
 * @version 3.0.0
 * @license MIT
 * @context Bachillerato Colombiano - MEN - Lineamientos Curriculares
 */

// Configuración inicial para manejo correcto de UTF-8
header('Content-Type: application/json; charset=utf-8');
ini_set('default_charset', 'UTF-8');

/**
 * Clase para gestionar la base de situaciones escolares
 */
class BaseSituacionesEscolares
{
    private array $categorias = [
        'Administrativo',
        'Emergencia',
        'Académico',
        'Convivencia',
        'Infraestructura',
        'Salud',
        'Tecnológico',
        'Comunicación',
        'Disciplinario',
        'Normalidad'
    ];

    /**
     * Base completa de situaciones por categoría
     * Mínimo 15 por categoría, contextualizadas para Colombia
     */
    private function obtenerBaseSituaciones(): array
    {
        return [
'Normalidad' => [
                ['Actividad programada exitosa', 'La actividad pedagógica planificada se desarrolló sin contratiempos ni interrupciones', 1, 5],
                ['Clase según cronograma', 'La sesión académica se ejecutó completamente según lo programado en el plan de estudios', 1, 5],
                ['Evaluación sin incidentes', 'El examen o prueba se realizó en tiempo y forma sin novedades que afectaran el proceso', 1, 5],
                ['Taller cumplido exitosamente', 'La actividad práctica o taller programado se completó en su totalidad sin dificultades', 1, 5],
                ['Rutina escolar establecida', 'Todas las actividades del día se desarrollaron según la rutina establecida sin alteraciones', 1, 5],
                ['Llegada puntual', 'Estudiantes ingresan a clase a la hora programada sin retrasos', 1, 5],
                ['Saludo matutino', 'Docente recibe estudiantes con protocolo de bienvenida establecido', 1, 5],
                ['Recreo ordenado', 'Estudiantes disfrutan tiempo libre sin incidentes ni conflictos', 1, 10],
                ['Entrega de tareas', 'Alumnos presentan trabajos asignados correctamente y a tiempo', 1, 10],
                ['Asistencia regular', 'Control de asistencia sin novedades ni ausencias', 1, 5],
                ['Participación activa', 'Estudiantes responden preguntas y participan voluntariamente en clase', 1, 15],
                ['Transición entre clases', 'Cambio de asignatura sin contratiempos ni demoras', 1, 5],
                ['Recolección de materiales', 'Estudiantes devuelven recursos didácticos correctamente', 1, 5],
                ['Formación ordenada', 'Estudiantes se alinean para actividades sin problemas', 1, 5],
                ['Escucha atenta', 'Alumnos prestan atención durante explicaciones del docente', 1, 10],
                ['Colaboración grupal', 'Trabajo en equipo desarrollado satisfactoriamente sin conflictos', 1, 20],
                ['Respeto por turnos', 'Estudiantes esperan su turno para hablar o participar', 1, 5],
                ['Cuidado de útiles', 'Alumnos mantienen materiales personales y escolares en buen estado', 1, 5],
                ['Seguimiento de instrucciones', 'Estudiantes ejecutan tareas según indicaciones dadas', 1, 10],
                ['Despedida diaria', 'Cierre de jornada con protocolo establecido y ordenado', 1, 5],
                ['Organización personal', 'Estudiantes mantienen pupitres y espacios personales ordenados', 1, 5],
                ['Compartir materiales', 'Alumnos colaboran prestando útiles escolares cuando es necesario', 1, 5],
                ['Silencio en biblioteca', 'Estudiantes respetan normas de espacio de estudio', 1, 10],
                ['Ayuda voluntaria', 'Alumnos colaboran espontáneamente con limpieza o organización', 1, 10],
                ['Celebración de logros', 'Reconocimiento positivo de avances académicos o personales', 1, 15],
                ['Puntualidad docente', 'Profesor inicia clase en horario establecido', 1, 5],
                ['Ambiente positivo', 'Aula con clima emocional favorable para el aprendizaje', 1, 10],
                ['Resolución autónoma', 'Estudiantes solucionan pequeños problemas sin ayuda adulta', 1, 10],
                ['Motivación evidente', 'Estudiantes muestran interés genuino por aprender', 1, 10],
                ['Comunidad cohesionada', 'Grupo clase demuestra sentido de pertenencia y apoyo mutuo', 1, 15]
            ],
        
            // ========== CATEGORÍA: ADMINISTRATIVO (20 situaciones) ==========
            'Administrativo' => [
                ['Padre solicita hablar con docente', 'Un padre de familia solicita conversar con el docente sin cita previa, interrumpiendo la clase en curso', 3, 15],
                ['Izada de bandera', 'Ceremonia cívica matutina con participación de toda la comunidad educativa, requiere logística especial', 2, 20],
                ['Reunión de padres inesperada', 'Convocatoria urgente de padres de familia por situación disciplinaria o académica', 4, 40],
                ['Solicitud de documentos', 'Padre requiere certificados académicos con urgencia para trámite legal o traslado', 2, 10],
                ['Cambio de horario', 'Modificación repentina del cronograma escolar por evento institucional o festividad local', 3, 25],
                ['Visita de inspectoría', 'Inspección educativa sorpresiva del MEN o Secretaría de Educación requiere documentación inmediata', 4, 50],
                ['Matrícula extemporánea', 'Estudiante nuevo necesita incorporación urgente al sistema por traslado familiar', 2, 20],
                ['Consejo académico extraordinario', 'Reunión urgente de docentes para evaluar casos críticos de estudiantes', 4, 45],
                ['Entrega de boletines', 'Distribución de informes de desempeño a padres de familia con firma de recibido', 3, 30],
                ['Actualización de datos SIE', 'Registro y actualización de información en el Sistema Integrado de Educación', 2, 15],
                ['Solicitud de traslado', 'Familia solicita documento de traslado escolar por cambio de residencia', 2, 10],
                ['Justificación de inasistencia', 'Padre presenta excusa médica o justificativo de ausencia del estudiante', 1, 5],
                ['Autorización de salida', 'Permiso firmado por acudiente para retiro anticipado del estudiante', 1, 5],
                ['Compromiso de convivencia', 'Firma de documento por incumplimiento del manual de convivencia', 3, 20],
                ['Solicitud de constancia', 'Requerimiento de documento para beneficios sociales o programas gubernamentales', 2, 10],
                ['Revisión de matrícula', 'Verificación de documentos pendientes para formalizar inscripción', 2, 15],
                ['Actualización de acudiente', 'Cambio de información de responsable legal del estudiante', 1, 10],
                ['Solicitud de descuento', 'Trámite para beneficios económicos por situación socioeconómica', 3, 25],
                ['Compromiso de pago', 'Acuerdo para regularización de pagos pendientes con institución', 3, 20],
                ['Autorización de actividades', 'Permiso para participación en eventos extracurriculares o salidas pedagógicas', 2, 15]
            ],

            // ========== CATEGORÍA: EMERGENCIA (20 situaciones) ==========
            'Emergencia' => [
                ['Simulacro de desastres', 'Se activa protocolo de evacuación por simulacro de terremoto, requiriendo coordinación de todo el personal', 4, 30],
                ['Estudiante con fiebre alta', 'Alumno presenta síntomas de enfermedad durante clase, requiere atención médica inmediata', 4, 30],
                ['Fuga de gas', 'Alarma de seguridad por posible fuga en cocina escolar o laboratorio', 5, 45],
                ['Incendio menor', 'Principio de incendio en laboratorio de química controlado rápidamente por brigada escolar', 5, 40],
                ['Estudiante desmayado', 'Alumno pierde conocimiento durante actividad física o por condiciones de salud', 4, 25],
                ['Alerta de seguridad', 'Reporte de persona sospechosa en las instalaciones escolares', 5, 35],
                ['Lesión grave', 'Estudiante sufre fractura o herida que requiere traslado a centro médico', 5, 45],
                ['Reacción alérgica severa', 'Estudiante presenta anafilaxia por alimento o picadura de insecto', 5, 30],
                ['Emergencia sísmica real', 'Sismo perceptible activa protocolo de evacuación real', 5, 40],
                ['Inundación', 'Crecimiento de quebrada o inundación afecta instalaciones escolares', 5, 50],
                ['Incidente con arma blanca', 'Estudiante porta objeto punzocortante, requiere intervención inmediata', 5, 35],
                ['Intento de autolesión', 'Estudiante presenta conductas de autoagresión o ideación suicida', 5, 45],
                ['Emergencia médica colectiva', 'Varios estudiantes presentan síntomas similares (intoxicación, enfermedad)', 5, 40],
                ['Incidente de seguridad vial', 'Accidente en las inmediaciones de la institución afecta acceso', 4, 35],
                ['Desaparición de estudiante', 'Menor no se encuentra en instalaciones, se activa protocolo de búsqueda', 5, 45],
                ['Amenaza de bomba', 'Comunicación telefónica o mensaje amenazante requiere evacuación', 5, 50],
                ['Ataque de pánico masivo', 'Varios estudiantes presentan crisis de ansiedad simultánea', 4, 30],
                ['Incidente con sustancias', 'Estudiante presenta síntomas de consumo de psicoactivos', 5, 40],
                ['Emergencia climática', 'Alerta roja por fenómenos naturales (vendaval, granizada, etc.)', 5, 45],
                ['Accidente de transporte', 'Vehículo escolar sufre incidente con estudiantes a bordo', 5, 50]
            ],

            // ========== CATEGORÍA: ACADÉMICO (20 situaciones) ==========
            'Académico' => [
                ['Profesor ausente', 'Docente no asiste sin previo aviso, requiere sustitución inmediata', 4, 20],
                ['Material didáctico insuficiente', 'Falta de recursos para actividad práctica programada', 2, 15],
                ['Evaluación cancelada', 'Examen debe suspenderse por condiciones climáticas adversas o emergencia', 3, 10],
                ['Estudiante con dificultades', 'Alumno requiere atención especializada durante clase por NEE', 3, 25],
                ['Proyecto grupal conflictivo', 'Disputas entre estudiantes afectan desarrollo de trabajo colaborativo', 3, 30],
                ['Plagio detectado', 'Estudiante presenta trabajo con contenido copiado sin citación', 3, 20],
                ['Calificación impugnada', 'Padre o estudiante solicita revisión de nota por presunta injusticia', 3, 25],
                ['Recuperación pendiente', 'Estudiante debe realizar actividades de nivelación por bajo desempeño', 3, 30],
                ['Proyecto incompleto', 'Grupo no entrega trabajo en fecha establecida', 2, 15],
                ['Dificultad con contenidos', 'Estudiantes no comprenden temas clave, requiere reforzamiento', 3, 25],
                ['Falta de tareas', 'Varios estudiantes no presentan actividades asignadas', 2, 10],
                ['Evaluación fraudulenta', 'Estudiante intenta hacer trampa durante examen', 4, 20],
                ['Proyecto destacado', 'Trabajo sobresaliente merece reconocimiento especial', 1, 10],
                ['Participación insuficiente', 'Estudiante no contribuye en actividades grupales', 2, 15],
                ['Avance satisfactorio', 'Estudiantes completan actividades según cronograma', 1, 5],
                ['Dificultad tecnológica', 'Estudiantes no pueden acceder a plataforma virtual de aprendizaje', 3, 20],
                ['Requerimiento de tutoría', 'Estudiante necesita acompañamiento adicional fuera de clase', 3, 30],
                ['Actualización curricular', 'Docente debe adaptar contenidos a nuevos estándares MEN', 3, 25],
                ['Integración de saberes', 'Proyecto interdisciplinario requiere coordinación entre áreas', 3, 35],
                ['Evaluación diferenciada', 'Estudiante con NEE requiere adaptación de instrumentos de valoración', 3, 25]
            ],

            // ========== CATEGORÍA: CONVIVENCIA (20 situaciones) ==========
            'Convivencia' => [
                ['Bullying detectado', 'Situación de acoso escolar requiere intervención inmediata del comité', 5, 45],
                ['Discusión entre estudiantes', 'Conflicto verbal escalando a nivel físico en patio escolar', 4, 20],
                ['Robo de pertenencias', 'Denuncia de hurto de útiles escolares, celulares o dinero entre compañeros', 3, 30],
                ['Lenguaje inapropiado', 'Estudiante usa vocabulario ofensivo hacia docente o compañeros', 3, 15],
                ['Exclusión social', 'Grupo de estudiantes marginando a compañero nuevo o diferente', 3, 25],
                ['Chismografía', 'Difusión de rumores que afectan reputación de estudiantes o docentes', 3, 20],
                ['Discriminación', 'Comentarios o acciones que menosprecian por condición social, étnica o física', 4, 30],
                ['Conflicto cultural', 'Malentendidos por diferencias regionales o de costumbres', 3, 25],
                ['Acoso cibernético', 'Uso de redes sociales para hostigar o humillar a compañeros', 4, 35],
                ['Violencia verbal', 'Insultos, gritos o amenazas entre miembros de la comunidad educativa', 4, 25],
                ['Mediación exitosa', 'Conflicto resuelto mediante diálogo guiado por comité de convivencia', 1, 20],
                ['Reconciliación', 'Estudiantes involucrados en conflicto firman acta de paz', 1, 25],
                ['Inclusión lograda', 'Estudiante marginado es integrado exitosamente al grupo', 1, 15],
                ['Resolución colaborativa', 'Estudiantes resuelven conflicto sin intervención adulta', 1, 10],
                ['Apoyo entre pares', 'Estudiantes se ayudan mutuamente en situaciones difíciles', 1, 10],
                ['Celebración de diversidad', 'Actividad que reconoce y valora diferencias culturales', 1, 20],
                ['Comunicación asertiva', 'Estudiantes expresan necesidades sin agresividad', 1, 10],
                ['Empatía demostrada', 'Estudiante muestra comprensión genuina hacia compañero en dificultad', 1, 10],
                ['Cooperación espontánea', 'Grupo colabora sin ser solicitado para resolver problema', 1, 15],
                ['Respeto por diferencias', 'Estudiantes aceptan y valoran opiniones distintas', 1, 10]
            ],

            // ========== CATEGORÍA: INFRAESTRUCTURA (20 situaciones) ==========
            'Infraestructura' => [
                ['Falta de energía eléctrica', 'Corte de energía eléctrica durante examen, afectando equipos tecnológicos y condiciones de iluminación', 5, 60],
                ['Fuga de agua', 'Tubería rota en baños principales afectando áreas comunes y clases', 4, 40],
                ['Vidrio roto', 'Ventana dañada en aula por accidente durante recreo o vandalismo', 3, 20],
                ['Mantenimiento urgente', 'Sistema eléctrico presenta fallas intermitentes poniendo en riesgo seguridad', 4, 50],
                ['Mobiliario deteriorado', 'Sillas y mesas en mal estado requieren reposición inmediata', 2, 15],
                ['Problema de climatización', 'Aire acondicionado fallando en día de alta temperatura', 3, 30],
                ['Actividad en auditorio', 'Evento especial programado en auditorio principal, requiere preparación técnica', 3, 45],
                ['Baños inservibles', 'Sanitarios fuera de funcionamiento afectando higiene escolar', 4, 35],
                ['Filtración de agua', 'Goteras en techo durante temporada de lluvias', 3, 25],
                ['Piso irregular', 'Baldosas levantadas o desniveles que representan riesgo de caída', 3, 20],
                ['Iluminación deficiente', 'Lámparas fundidas afectando visibilidad en aulas', 2, 15],
                ['Paredes deterioradas', 'Humedad, grietas o pintura descascarada', 2, 20],
                ['Puertas atascadas', 'Acceso restringido por cerraduras o bisagras defectuosas', 3, 25],
                ['Ventilación inadecuada', 'Aulas sin circulación de aire afectando concentración', 2, 15],
                ['Espacio insuficiente', 'Sobrepoblación en aula afectando desarrollo de actividades', 3, 20],
                ['Acceso no adecuado', 'Rampas o pasamanos deficientes para estudiantes con movilidad reducida', 4, 30],
                ['Señalización deficiente', 'Falta de indicadores de emergencia o rutas de evacuación', 3, 25],
                ['Áreas recreativas dañadas', 'Canchas, juegos o zonas verdes en mal estado', 3, 30],
                ['Almacenamiento inadecuado', 'Falta de espacios para guardar materiales y equipos', 2, 15],
                ['Mejora implementada', 'Reparación exitosa de infraestructura con mínimo impacto', 1, 10]
            ],

            // ========== CATEGORÍA: SALUD (20 situaciones) ==========
            'Salud' => [
                ['Reacción alérgica', 'Estudiante presenta alergia a alimento consumido en cafetería o ambiente', 5, 25],
                ['Lesión deportiva', 'Fractura, esguince o contusión durante clase de educación física', 4, 40],
                ['Brotes de enfermedad', 'Varios estudiantes con síntomas gripales simultáneos', 4, 35],
                ['Medicamento olvidado', 'Padre no envía tratamiento médico necesario para condición crónica', 2, 10],
                ['Crisis de ansiedad', 'Estudiante presenta ataque de pánico durante evaluación o evento', 3, 20],
                ['Deshidratación', 'Estudiante presenta mareo por falta de ingesta de líquidos', 3, 15],
                ['Intoxicación alimentaria', 'Malestar gastrointestinal por consumo de alimentos en mal estado', 4, 30],
                ['Picadura de insecto', 'Reacción alérgica o dolor por picadura de abeja, avispa u otro', 3, 20],
                ['Convulsión', 'Estudiante presenta crisis epiléptica requiriendo atención especializada', 5, 35],
                ['Dolor abdominal agudo', 'Estudiante requiere valoración médica por dolor intenso', 4, 25],
                ['Hemorragia nasal', 'Sangrado abundante que requiere atención inmediata', 3, 15],
                ['Corte profundo', 'Herida que requiere curación y posible puntos de sutura', 4, 25],
                ['Quemadura menor', 'Lesión por contacto con superficie caliente o químicos', 3, 20],
                ['Mareo severo', 'Estudiante presenta vértigo o desorientación', 3, 15],
                ['Dolor de cabeza intenso', 'Cefalea que impide desarrollo normal de actividades', 3, 20],
                ['Fatiga extrema', 'Estudiante muestra agotamiento físico o mental evidente', 2, 15],
                ['Problema dental', 'Dolor agudo que requiere atención odontológica urgente', 3, 25],
                ['Reacción a medicamento', 'Efectos secundarios adversos por tratamiento farmacológico', 4, 30],
                ['Desmayo recurrente', 'Estudiante presenta episodios repetidos de pérdida de conciencia', 5, 35],
                ['Bienestar general', 'Estudiantes muestran energía y disposición adecuadas', 1, 5]
            ],

            // ========== CATEGORÍA: TECNOLÓGICO (20 situaciones) ==========
            'Tecnológico' => [
                ['Internet caído', 'Falla en conexión afectando clases virtuales y recursos digitales', 4, 30],
                ['Proyector defectuoso', 'Equipo audiovisual fallando durante presentación importante', 3, 15],
                ['Software educativo', 'Plataforma de aprendizaje presenta errores técnicos o caídas', 3, 25],
                ['Dispositivos perdidos', 'Tablets escolares no localizados después de actividad', 3, 20],
                ['Seguridad informática', 'Intento de acceso no autorizado al sistema escolar', 5, 45],
                ['Computadores inoperables', 'Equipos de laboratorio con fallas de hardware o software', 4, 35],
                ['Plataforma caída', 'Sistema de gestión académica fuera de servicio', 4, 40],
                ['Archivos corruptos', 'Documentos importantes no se pueden abrir o recuperar', 3, 25],
                ['Impresora defectuosa', 'Equipo de impresión fallando durante entrega de materiales', 2, 15],
                ['Conexión lenta', 'Velocidad de internet insuficiente para actividades en línea', 3, 20],
                ['Software desactualizado', 'Programas obsoletos incompatibles con nuevos formatos', 2, 20],
                ['Periféricos dañados', 'Teclados, mouse o monitores en mal estado', 2, 15],
                ['Red Wi-Fi inestable', 'Conexión intermitente afectando trabajo colaborativo', 3, 25],
                ['Licencias vencidas', 'Software educativo sin autorización de uso', 3, 20],
                ['Almacenamiento insuficiente', 'Servidores o dispositivos sin espacio para nuevos archivos', 2, 15],
                ['Ciberacoso detectado', 'Uso indebido de plataformas digitales para hostigamiento', 4, 30],
                ['Fallo en videoconferencia', 'Herramienta de comunicación remota presenta errores', 3, 20],
                ['Backup fallido', 'Copia de seguridad de datos no se realizó correctamente', 4, 35],
                ['Virus informático', 'Malware afecta funcionamiento de equipos escolares', 4, 40],
                ['Tecnología integrada', 'Implementación exitosa de herramientas digitales en clase', 1, 10]
            ],

            // ========== CATEGORÍA: COMUNICACIÓN (20 situaciones) ==========
            'Comunicación' => [
                ['Circular malinterpretada', 'Información enviada a padres genera confusión y quejas', 3, 20],
                ['Redes sociales', 'Publicación inapropiada de estudiante sobre institución', 4, 30],
                ['Llamada amenazante', 'Comunicación telefónica con contenido intimidatorio', 5, 40],
                ['Traducción necesaria', 'Familia requiere servicios de intérprete para comunicación efectiva', 2, 15],
                ['Rumor escolar', 'Información falsa circulando entre estudiantes', 3, 25],
                ['Comunicado urgente', 'Notificación inmediata a padres por emergencia o cambio', 4, 20],
                ['Reunión mal coordinada', 'Cita con padres programada incorrectamente causando conflictos', 2, 15],
                ['Información incompleta', 'Documentos o comunicaciones con datos faltantes', 2, 10],
                ['Canal inadecuado', 'Uso de medio incorrecto para tipo de mensaje (urgente/no urgente)', 2, 10],
                ['Respuesta tardía', 'Demora en contestar solicitudes de padres o estudiantes', 2, 15],
                ['Malentendido cultural', 'Diferencias en interpretación por contexto regional', 3, 20],
                ['Sobrecarga informativa', 'Demasiados comunicados generando saturación', 2, 15],
                ['Filtración de información', 'Datos confidenciales compartidos sin autorización', 4, 30],
                ['Comunicación asertiva', 'Mensaje claro, respetuoso y efectivo entre partes', 1, 10],
                ['Feedback constructivo', 'Retroalimentación que promueve mejora sin desmotivar', 1, 15],
                ['Escucha activa', 'Atención plena y comprensión genuina durante conversaciones', 1, 10],
                ['Diálogo abierto', 'Comunicación bidireccional que fomenta participación', 1, 15],
                ['Claridad en instrucciones', 'Indicaciones precisas que evitan confusiones', 1, 10],
                ['Comunicación oportuna', 'Información entregada en momento adecuado', 1, 10],
                ['Canal apropiado', 'Uso correcto de medio según tipo y urgencia del mensaje', 1, 10]
            ],

            // ========== CATEGORÍA: DISCIPLINARIO (25 situaciones) ==========
            'Disciplinario' => [
                ['Inasistencia reiterada', 'Estudiante acumula faltas injustificadas sin justificación', 4, 30],
                ['Uniforme incompleto', 'Incumplimiento reiterado del código de vestimenta escolar', 2, 10],
                ['Uso de celular prohibido', 'Estudiante graba clase sin autorización del docente', 3, 15],
                ['Salida no autorizada', 'Menor abandona instalaciones sin permiso del coordinador', 5, 30],
                ['Agresión verbal', 'Estudiante insulta a compañero en público durante recreo', 4, 25],
                ['Desacato a autoridad', 'Estudiante desafía instrucciones directas del docente', 4, 20],
                ['Consumo prohibido', 'Estudiante ingiere alimentos o sustancias no permitidas', 4, 25],
                ['Destrucción de propiedad', 'Daño intencional a mobiliario o equipos escolares', 5, 35],
                ['Falsificación de documentos', 'Estudiante presenta excusa médica falsa', 4, 30],
                ['Acoso cibernético', 'Uso de redes sociales para hostigar a compañeros', 5, 40],
                ['Porte de objetos prohibidos', 'Estudiante lleva elementos no permitidos al colegio', 4, 20],
                ['Falta de respeto', 'Comportamiento irrespetuoso hacia personal administrativo', 3, 15],
                ['Trampa en evaluación', 'Estudiante intenta hacer trampa durante examen', 4, 25],
                ['Incumplimiento de normas', 'Estudiante viola reglamento escolar repetidamente', 3, 25],
                ['Alteración del orden', 'Estudiante interrumpe clase constantemente', 3, 15],
                ['Vandalismo', 'Grafitis o daños a paredes y propiedades escolares', 4, 30],
                ['Tenencia de sustancias', 'Estudiante posee alcohol, tabaco o drogas en institución', 5, 45],
                ['Amenazas', 'Comunicación intimidatoria hacia compañeros o docentes', 5, 35],
                ['Extorsión', 'Solicitud de dinero o bienes mediante coerción', 5, 40],
                ['Robo', 'Hurto de pertenencias ajenas dentro de la institución', 4, 35],
                ['Discriminación activa', 'Acciones deliberadas para marginar por condición particular', 4, 30],
                ['Incitación a la violencia', 'Promoción de conductas agresivas entre estudiantes', 5, 35],
                ['Fuga escolar', 'Abandono reiterado de clases sin autorización', 4, 30],
                ['Comportamiento ejemplar', 'Estudiante demuestra conducta modelo consistentemente', 1, 10],
                ['Autorregulación', 'Estudiante corrige conducta inapropiada sin intervención externa', 1, 10]
            ]

        ];
    }

    /**
     * Genera UUID v4 para identificadores únicos
     */
    private function generarUUID(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Obtiene todas las situaciones organizadas por categoría
     */
    public function obtenerSituacionesPorCategoria(): array
    {
        $base = $this->obtenerBaseSituaciones();
        $resultado = [];

        foreach ($base as $categoria => $situaciones) {
            $resultado[$categoria] = array_map(function($sit) use ($categoria) {
                return [
                    'id' => $this->generarUUID(),
                    'categoria' => $categoria,
                    'titulo' => $sit[0],
                    'descripcion' => $sit[1],
                    'impacto' => $sit[2],
                    'tiempo_estimado' => $sit[3]
                ];
            }, $situaciones);
        }

        return $resultado;
    }

    /**
     * Obtiene todas las situaciones en un solo array plano
     */
    public function obtenerTodasSituaciones(): array
    {
        $base = $this->obtenerBaseSituaciones();
        $resultado = [];

        foreach ($base as $categoria => $situaciones) {
            foreach ($situaciones as $sit) {
                $resultado[] = [
                    'id' => $this->generarUUID(),
                    'categoria' => $categoria,
                    'titulo' => $sit[0],
                    'descripcion' => $sit[1],
                    'impacto' => $sit[2],
                    'tiempo_estimado' => $sit[3]
                ];
            }
        }

        return $resultado;
    }

    /**
     * Obtiene estadísticas de la base de situaciones
     */
    public function obtenerEstadisticas(): array
    {
        $base = $this->obtenerBaseSituaciones();
        $total = 0;
        $estadisticas = [];

        foreach ($base as $categoria => $situaciones) {
            $total += count($situaciones);
            $estadisticas[$categoria] = [
                'cantidad' => count($situaciones),
                'impacto_promedio' => array_sum(array_column($situaciones, 2)) / count($situaciones),
                'tiempo_promedio' => array_sum(array_column($situaciones, 3)) / count($situaciones)
            ];
        }

        return [
            'total_situaciones' => $total,
            'categorias' => count($base),
            'detalle' => $estadisticas,
            'ultima_actualizacion' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Exporta situaciones como JSON
     */
    public function exportarJSON(): string
    {
        return json_encode(
            [
                'success' => true,
                'situaciones' => $this->obtenerSituacionesPorCategoria(),
                'metadata' => [
                    'total_situaciones' => count($this->obtenerTodasSituaciones()),
                    'categorias' => $this->categorias,
                    'version' => '3.0.0',
                    'contexto' => 'Bachillerato Colombiano - MEN - Lineamientos Curriculares',
                    'fecha_generacion' => date('Y-m-d H:i:s'),
                    'region' => 'Eje Cafetero - Colombia'
                ]
            ],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Exporta situaciones planas como JSON
     */
    public function exportarJSONPlano(): string
    {
        return json_encode(
            [
                'success' => true,
                'situaciones' => $this->obtenerTodasSituaciones(),
                'metadata' => [
                    'total_situaciones' => count($this->obtenerTodasSituaciones()),
                    'version' => '3.0.0',
                    'tipo' => 'plano'
                ]
            ],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Exporta estadísticas como JSON
     */
    public function exportarEstadisticasJSON(): string
    {
        return json_encode(
            [
                'success' => true,
                'estadisticas' => $this->obtenerEstadisticas(),
                'metadata' => [
                    'version' => '3.0.0',
                    'tipo' => 'estadisticas'
                ]
            ],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }
}

// Procesar parámetros de la solicitud
$tipo = $_GET['tipo'] ?? 'categorias';
$categoria = $_GET['categoria'] ?? null;

try {
    $base = new BaseSituacionesEscolares();
    
    switch ($tipo) {
        case 'plano':
            echo $base->exportarJSONPlano();
            break;
        case 'estadisticas':
            echo $base->exportarEstadisticasJSON();
            break;
        case 'categorias':
        default:
            echo $base->exportarJSON();
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener base de situaciones',
        'mensaje' => $e->getMessage(),
        'version' => '3.0.0'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>