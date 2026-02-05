<?php
declare(strict_types=1);

/**
 * Simulador de Incidentes Escolares
 * Componente para plataforma de gestión educativa
 * 
 * @author Senior Full-Stack Developer (EdTech Specialist)
 * @version 1.0.0
 * @license MIT
 */

// Configuración inicial para manejo correcto de UTF-8
header('Content-Type: application/json; charset=utf-8');
ini_set('default_charset', 'UTF-8');

/**
 * Clase para generar incidentes escolares simulados
 */
class SimuladorIncidentesEscolares
{
    private array $incidentes = [];
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
     * Constructor - Inicializa y genera los incidentes
     */
    public function __construct()
    {
        $this->generarIncidentesBase();
        $this->completarIncidentesAleatorios();
    }

    /**
     * Genera los incidentes específicos requeridos
     */
    private function generarIncidentesBase(): void
    {
        $this->incidentes = [
            [
                'id' => $this->generarUUID(),
                'categoria' => 'Administrativo',
                'titulo' => 'Padre solicita hablar con docente',
                'descripcion' => 'Un padre de familia solicita hablar con el docente sin cita previa, interrumpiendo la clase en curso.',
                'impacto' => 3,
                'tiempo_estimado' => 15
            ],
            [
                'id' => $this->generarUUID(),
                'categoria' => 'Emergencia',
                'titulo' => 'Simulacro de desastres',
                'descripcion' => 'Se activa protocolo de evacuación por simulacro de terremoto, requiriendo coordinación de todo el personal.',
                'impacto' => 4,
                'tiempo_estimado' => 30
            ],
            [
                'id' => $this->generarUUID(),
                'categoria' => 'Administrativo',
                'titulo' => 'Izada de bandera',
                'descripcion' => 'Ceremonia cívica matutina con participación de toda la comunidad educativa, requiere logística especial.',
                'impacto' => 2,
                'tiempo_estimado' => 20
            ],
            [
                'id' => $this->generarUUID(),
                'categoria' => 'Infraestructura',
                'titulo' => 'Actividad en auditorio',
                'descripcion' => 'Evento especial programado en auditorio principal, requiere preparación técnica y coordinación de espacios.',
                'impacto' => 3,
                'tiempo_estimado' => 45
            ],
            [
                'id' => $this->generarUUID(),
                'categoria' => 'Infraestructura',
                'titulo' => 'Falta de energía eléctrica',
                'descripcion' => 'Corte de energía eléctrica durante examen, afectando equipos tecnológicos y condiciones de iluminación.',
                'impacto' => 5,
                'tiempo_estimado' => 60
            ]
        ];
    }

    /**
     * Completa con incidentes aleatorios variados
     */
    private function completarIncidentesAleatorios(): void
    {
        $incidentesAdicionales = [
            // Incidentes Administrativos
            ['Administrativo', 'Reunión de padres inesperada', 'Convocatoria urgente de padres de familia por situación disciplinaria', 4, 40],
            ['Administrativo', 'Solicitud de documentos', 'Padre requiere certificados académicos con urgencia para trámite legal', 2, 10],
            ['Administrativo', 'Cambio de horario', 'Modificación repentina del cronograma escolar por evento institucional', 3, 25],
            ['Administrativo', 'Visita de inspectoría', 'Inspección educativa sorpresiva requiere documentación inmediata', 4, 50],
            ['Administrativo', 'Matrícula extemporánea', 'Estudiante nuevo necesita incorporación urgente al sistema', 2, 20],
            
            // Incidentes de Emergencia
            ['Emergencia', 'Estudiante con fiebre alta', 'Alumno presenta síntomas de enfermedad durante clase, requiere atención médica', 4, 30],
            ['Emergencia', 'Fuga de gas', 'Alarma de seguridad por posible fuga en cocina escolar', 5, 45],
            ['Emergencia', 'Incendio menor', 'Principio de incendio en laboratorio de química controlado rápidamente', 5, 40],
            ['Emergencia', 'Estudiante desmayado', 'Alumno pierde conocimiento durante actividad física', 4, 25],
            ['Emergencia', 'Alerta de seguridad', 'Reporte de persona sospechosa en las instalaciones escolares', 5, 35],
            
            // Incidentes Académicos
            ['Académico', 'Profesor ausente', 'Docente no asiste sin previo aviso, requiere sustitución inmediata', 4, 20],
            ['Académico', 'Material didáctico insuficiente', 'Falta de recursos para actividad práctica programada', 2, 15],
            ['Académico', 'Evaluación cancelada', 'Examen debe suspenderse por condiciones climáticas adversas', 3, 10],
            ['Académico', 'Estudiante con dificultades', 'Alumno requiere atención especializada durante clase', 3, 25],
            ['Académico', 'Proyecto grupal conflictivo', 'Disputas entre estudiantes afectan desarrollo de trabajo colaborativo', 3, 30],
            
            // Incidentes de Convivencia
            ['Convivencia', 'Bullying detectado', 'Situación de acoso escolar requiere intervención inmediata', 5, 45],
            ['Convivencia', 'Discusión entre estudiantes', 'Conflicto verbal escalando a nivel físico en patio escolar', 4, 20],
            ['Convivencia', 'Robo de pertenencias', 'Denuncia de hurto de útiles escolares entre compañeros', 3, 30],
            ['Convivencia', 'Lenguaje inapropiado', 'Estudiante usa vocabulario ofensivo hacia docente', 3, 15],
            ['Convivencia', 'Exclusión social', 'Grupo de estudiantes marginando a compañero nuevo', 3, 25],
            
            // Incidentes de Infraestructura
            ['Infraestructura', 'Fuga de agua', 'Tubería rota en baños principales afectando áreas comunes', 4, 40],
            ['Infraestructura', 'Vidrio roto', 'Ventana dañada en aula por accidente durante recreo', 3, 20],
            ['Infraestructura', 'Mantenimiento urgente', 'Sistema eléctrico presenta fallas intermitentes', 4, 50],
            ['Infraestructura', 'Mobiliario deteriorado', 'Sillas y mesas en mal estado requieren reposición', 2, 15],
            ['Infraestructura', 'Problema de climatización', 'Aire acondicionado fallando en día de alta temperatura', 3, 30],
            
            // Incidentes de Salud
            ['Salud', 'Reacción alérgica', 'Estudiante presenta alergia a alimento consumido en cafetería', 5, 25],
            ['Salud', 'Lesión deportiva', 'Fractura durante clase de educación física', 4, 40],
            ['Salud', 'Brotes de enfermedad', 'Varios estudiantes con síntomas gripales simultáneos', 4, 35],
            ['Salud', 'Medicamento olvidado', 'Padre no envía tratamiento médico necesario', 2, 10],
            ['Salud', 'Crisis de ansiedad', 'Estudiante presenta ataque de pánico durante evaluación', 3, 20],
            
            // Incidentes Tecnológicos
            ['Tecnológico', 'Internet caído', 'Falla en conexión afectando clases virtuales y recursos digitales', 4, 30],
            ['Tecnológico', 'Proyector defectuoso', 'Equipo audiovisual fallando durante presentación importante', 3, 15],
            ['Tecnológico', 'Software educativo', 'Plataforma de aprendizaje presenta errores técnicos', 3, 25],
            ['Tecnológico', 'Dispositivos perdidos', 'Tablets escolares no localizados después de actividad', 3, 20],
            ['Tecnológico', 'Seguridad informática', 'Intento de acceso no autorizado al sistema escolar', 5, 45],
            
            // Incidentes de Comunicación
            ['Comunicación', 'Circular malinterpretada', 'Información enviada a padres genera confusión y quejas', 3, 20],
            ['Comunicación', 'Redes sociales', 'Publicación inapropiada de estudiante sobre institución', 4, 30],
            ['Comunicación', 'Llamada amenazante', 'Comunicación telefónica con contenido intimidatorio', 5, 40],
            ['Comunicación', 'Traducción necesaria', 'Familia requiere servicios de intérprete para comunicación efectiva', 2, 15],
            ['Comunicación', 'Rumor escolar', 'Información falsa circulando entre estudiantes', 3, 25],
            
            // Incidentes Variados
            ['Administrativo', 'Transporte escolar', 'Bus presenta fallas mecánicas con estudiantes a bordo', 4, 35],
            ['Emergencia', 'Condiciones climáticas', 'Tormenta eléctrica severa requiere evacuación preventiva', 5, 40],
            ['Académico', 'Competencia externa', 'Estudiantes clasificados requieren preparación intensiva', 3, 30],
            ['Convivencia', 'Celebración cultural', 'Evento diverso requiere sensibilidad y adaptación curricular', 2, 25],
            ['Infraestructura', 'Acceso limitado', 'Ascensor fuera de servicio afectando estudiantes con movilidad reducida', 4, 30],
            ['Salud', 'Alimentación escolar', 'Queja sobre calidad de servicio de comedores', 3, 20],
            ['Tecnológico', 'Respaldo de datos', 'Sistema de backup fallando con información crítica', 5, 50],
            ['Comunicación', 'Reunión virtual', 'Problemas técnicos impiden participación de padres remotos', 3, 25],
            ['Administrativo', 'Certificación docente', 'Renovación urgente de credenciales profesionales', 2, 15],
            ['Emergencia', 'Primeros auxilios', 'Entrenamiento necesario para personal no capacitado', 3, 40]
        ];

        foreach ($incidentesAdicionales as $incidente) {
            $this->incidentes[] = [
                'id' => $this->generarUUID(),
                'categoria' => $incidente[0],
                'titulo' => $incidente[1],
                'descripcion' => $incidente[2],
                'impacto' => $incidente[3],
                'tiempo_estimado' => $incidente[4]
            ];
        }
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
     * Obtiene todos los incidentes generados
     */
    public function obtenerIncidentes(): array
    {
        return $this->incidentes;
    }

    /**
     * Exporta incidentes como JSON agrupado (Estructura exam.json)
     */
    public function exportarJSON(): string
    {
        $agrupados = [];
        foreach ($this->categorias as $cat) {
            $agrupados[$cat] = [];
        }

        foreach ($this->incidentes as $incidente) {
            $agrupados[$incidente['categoria']][] = $incidente;
        }

        $resultado = [
            'success' => true,
            'situaciones' => $agrupados,
            'metadata' => [
                'total_situaciones' => count($this->incidentes),
                'categorias' => $this->categorias,
                'version' => '3.0.0',
                'contexto' => 'Bachillerato Colombiano - MEN - Lineamientos Curriculares',
                'fecha_generacion' => date('Y-m-d H:i:s'),
                'region' => 'Eje Cafetero - Colombia'
            ]
        ];

        return json_encode(
            $resultado,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }
}

// Instanciar y ejecutar el simulador
try {
    $simulador = new SimuladorIncidentesEscolares();
    echo $simulador->exportarJSON();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al generar simulación',
        'mensaje' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>