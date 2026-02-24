<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Estructura base solicitada
$datos = [];

// -----------------------------------------------------------------------------
// CICLO 1: GRADOS 1° A 3° - EDUCACIÓN FÍSICA, RECREACIÓN Y DEPORTE
// Dimensiones: Corporeidad, Motricidad, Lúdica, Salud, Convivencia
// -----------------------------------------------------------------------------
$educacion_fisica_1_3 = [
    // Corporeidad y motricidad
    "Reconozco mi cuerpo y sus posibilidades de movimiento en diferentes situaciones lúdicas.",
    "Exploro diferentes formas de desplazamiento, giros, saltos y equilibrios en espacios variados.",
    "Coordinar movimientos básicos como correr, saltar, lanzar y recibir en actividades recreativas.",
    "Desarrollo habilidades motrices básicas a través del juego y la exploración corporal.",
    "Identifico las partes de mi cuerpo y sus funciones en la realización de actividades físicas.",

    // Expresión corporal y creatividad
    "Expreso emociones y sentimientos a través del movimiento corporal y el juego simbólico.",
    "Participo en actividades rítmicas y expresivas que integran música y movimiento.",
    "Improviso secuencias de movimiento a partir de estímulos sonoros, visuales o verbales.",

    // Lúdica y recreación
    "Disfruto del juego como medio para relacionarme con mis compañeros y compañeras.",
    "Participo activamente en juegos tradicionales, populares y cooperativos.",
    "Respeto las reglas básicas de los juegos y actividades recreativas en las que participo.",
    "Valoro el juego limpio y el respeto por los demás en las actividades lúdicas.",

    // Salud y cuidado del cuerpo
    "Identifico hábitos básicos de higiene y cuidado personal antes y después de la actividad física.",
    "Reconozco la importancia de la hidratación y la alimentación saludable para mi bienestar.",
    "Practico ejercicios de calentamiento y relajación como parte de mis rutinas de actividad física.",
    "Identifico señales de fatiga o malestar durante la actividad física y sé cuándo detenerme.",

    // Convivencia y valores
    "Comparto materiales y espacios con mis compañeros durante las actividades físicas.",
    "Acepto con deportividad los resultados de juegos y competencias recreativas.",
    "Colaboro con mis compañeros en actividades grupales y juegos cooperativos.",
    "Respeto las diferencias físicas y de habilidad motriz entre mis compañeros."
];

// -----------------------------------------------------------------------------
// CICLO 2: GRADOS 4° A 5° - EDUCACIÓN FÍSICA, RECREACIÓN Y DEPORTE
// -----------------------------------------------------------------------------
$educacion_fisica_4_5 = [
    // Corporeidad y motricidad
    "Mejoro la coordinación y el control corporal en actividades que requieren precisión y equilibrio.",
    "Desarrollo habilidades motrices específicas como patear, driblar, encestar y golpear.",
    "Aplico principios básicos de técnica en gestos deportivos elementales.",
    "Adapto mis movimientos a diferentes ritmos, velocidades y direcciones en actividades físicas.",
    "Planifico y ejecuto secuencias de movimiento con mayor complejidad y propósito.",

    // Expresión corporal y creatividad
    "Creo y reproduzco secuencias de movimiento que expresan ideas, emociones o narrativas.",
    "Participo en actividades de expresión corporal que integran elementos teatrales y dancísticos.",
    "Utilizo el lenguaje corporal como medio de comunicación no verbal en actividades grupales.",

    // Lúdica y recreación
    "Participo en juegos predeportivos que desarrollan habilidades específicas para deportes básicos.",
    "Diseño y propongo variantes de juegos tradicionales adaptándolos a diferentes contextos.",
    "Valoro la recreación como medio para el descanso activo y el bienestar integral.",
    "Organizo y dirijo actividades lúdicas sencillas con mis compañeros.",

    // Salud y cuidado del cuerpo
    "Practico rutinas de calentamiento, parte principal y vuelta a la calma en actividades físicas.",
    "Identifico los beneficios de la actividad física regular para mi salud física y mental.",
    "Aplico medidas básicas de prevención de lesiones durante la práctica de actividad física.",
    "Reconozco la importancia del descanso y la recuperación después del ejercicio físico.",

    // Convivencia y valores
    "Practico el juego limpio y el respeto a las reglas en actividades deportivas y recreativas.",
    "Asumo roles diferentes en juegos y actividades grupales (líder, seguidor, árbitro).",
    "Manejo constructivamente la frustración y el éxito en situaciones competitivas.",
    "Promuevo la inclusión y participación de todos mis compañeros en actividades físicas."
];

// -----------------------------------------------------------------------------
// CICLO 3: GRADOS 6° A 7° - EDUCACIÓN FÍSICA, RECREACIÓN Y DEPORTE
// -----------------------------------------------------------------------------
$educacion_fisica_6_7 = [
    // Corporeidad y motricidad
    "Refino habilidades motrices específicas en diferentes deportes y actividades físicas.",
    "Aplico principios biomecánicos básicos para mejorar la eficiencia de mis movimientos.",
    "Desarrollo capacidades físicas básicas: fuerza, resistencia, flexibilidad y velocidad.",
    "Adapto técnicas deportivas a diferentes situaciones de juego y competencia.",
    "Evalúo y ajusto mi desempeño motriz a partir de la retroalimentación recibida.",

    // Expresión corporal y creatividad
    "Diseño coreografías o secuencias expresivas que integran elementos rítmicos y espaciales.",
    "Utilizo la expresión corporal como medio para comunicar mensajes o conceptos grupales.",
    "Participo en proyectos artísticos que integran movimiento, música y narrativa.",

    // Lúdica y recreación
    "Participo en torneos y encuentros deportivos escolares aplicando reglas y estrategias básicas.",
    "Organizo actividades recreativas que promueven la participación activa de diversos grupos.",
    "Valoro la recreación como estrategia para el manejo del estrés y el equilibrio emocional.",
    "Promuevo estilos de vida activos a través de la práctica regular de actividades recreativas.",

    // Salud y cuidado del cuerpo
    "Diseño y aplico planes básicos de acondicionamiento físico acordes a mis características.",
    "Identifico factores de riesgo asociados a la práctica de actividad física y los prevengo.",
    "Aplico principios básicos de nutrición e hidratación para optimizar mi desempeño físico.",
    "Reconozco la relación entre actividad física, salud mental y bienestar emocional.",

    // Convivencia y valores
    "Practico el fair play y respeto las decisiones de árbitros y compañeros en competencias.",
    "Asumo responsabilidades en la organización y desarrollo de actividades deportivas escolares.",
    "Manejo asertivamente conflictos que surgen en situaciones competitivas o de juego.",
    "Promuevo la equidad de género y la inclusión en actividades físicas y deportivas."
];

// -----------------------------------------------------------------------------
// CICLO 4: GRADOS 8° A 9° - EDUCACIÓN FÍSICA, RECREACIÓN Y DEPORTE
// -----------------------------------------------------------------------------
$educacion_fisica_8_9 = [
    // Corporeidad y motricidad
    "Perfecciono técnicas deportivas específicas aplicando principios tácticos y estratégicos.",
    "Desarrollo capacidades físicas condicionales y coordinativas para optimizar mi rendimiento.",
    "Analizo y ajusto mi técnica motriz a partir de la observación y el análisis del movimiento.",
    "Aplico principios de entrenamiento básico para mejorar mis capacidades físicas.",
    "Evalúo mi progreso motriz y establezco metas realistas de mejora personal.",

    // Expresión corporal y creatividad
    "Creo propuestas expresivas que integran elementos de danza, teatro y movimiento.",
    "Utilizo el lenguaje corporal y la expresión artística para comunicar ideas sociales o culturales.",
    "Participo en proyectos interdisciplinares que vinculan educación física con otras áreas.",

    // Lúdica y recreación
    "Diseño y ejecuto proyectos recreativos que promueven la participación comunitaria.",
    "Aplico estrategias de animación y dirección de grupos en actividades lúdico-deportivas.",
    "Valoro la recreación como derecho fundamental y estrategia de desarrollo comunitario.",
    "Promuevo prácticas recreativas sostenibles y respetuosas con el medio ambiente.",

    // Salud y cuidado del cuerpo
    "Diseño programas básicos de actividad física considerando principios de individualidad y progresión.",
    "Identifico y prevengo lesiones deportivas aplicando conocimientos básicos de primeros auxilios.",
    "Aplico principios de periodización básica en planes de entrenamiento personal.",
    "Analizo críticamente mensajes publicitarios sobre cuerpos, salud y actividad física.",

    // Convivencia y valores
    "Promuevo valores deportivos como respeto, solidaridad y responsabilidad en competencias.",
    "Asumo roles de liderazgo positivo en equipos y grupos de actividad física.",
    "Manejo constructivamente la presión competitiva y las expectativas de desempeño.",
    "Promuevo la inclusión de personas con diferentes capacidades en actividades físicas."
];

// -----------------------------------------------------------------------------
// CICLO 5: GRADOS 10° A 11° - EDUCACIÓN FÍSICA, RECREACIÓN Y DEPORTE
// -----------------------------------------------------------------------------
$educacion_fisica_10_11 = [
    // Corporeidad y motricidad
    "Optimizo mi desempeño motriz aplicando conocimientos avanzados de técnica y táctica deportiva.",
    "Desarrollo planes de entrenamiento personal considerando objetivos específicos y contextos.",
    "Analizo críticamente mi práctica motriz y propongo estrategias de mejora continua.",
    "Aplico principios de biomecánica y fisiología para optimizar técnicas deportivas.",
    "Evalúo y transfiero habilidades motrices a diferentes contextos deportivos y recreativos.",

    // Expresión corporal y creatividad
    "Diseño propuestas artístico-expresivas que abordan temáticas sociales, culturales o personales.",
    "Utilizo la expresión corporal como herramienta de comunicación, transformación y reflexión social.",
    "Integro elementos de diferentes lenguajes artísticos en propuestas corporales innovadoras.",

    // Lúdica y recreación
    "Diseño y gestiona proyectos recreativos con impacto social, ambiental o comunitario.",
    "Aplica metodologías de animación sociocultural para promover participación activa.",
    "Valora la recreación como derecho humano y estrategia de desarrollo humano integral.",
    "Promueve prácticas recreativas inclusivas, sostenibles y transformadoras.",

    // Salud y cuidado del cuerpo
    "Diseña programas de actividad física considerando principios científicos y contextos específicos.",
    "Aplica conocimientos de prevención, primeros auxilios y rehabilitación básica en actividad física.",
    "Analiza críticamente modelos corporales, estéticos y de salud promovidos por medios y cultura.",
    "Promueve estilos de vida saludables basados en evidencia científica y respeto por la diversidad corporal.",

    // Convivencia y valores
    "Promueve una cultura deportiva basada en valores éticos, inclusión y respeto por la diversidad.",
    "Asume roles de liderazgo transformador en organizaciones deportivas y recreativas.",
    "Maneja asertivamente situaciones de conflicto, presión o desigualdad en contextos deportivos.",
    "Promueve la equidad, inclusión y justicia social en prácticas físicas, deportivas y recreativas."
];

// Generación del array por grados independientes (1 a 11)
for ($i = 1; $i <= 11; $i++) {
    $grupo = [];

    if ($i <= 3) {
        $grupo = $educacion_fisica_1_3;
    } elseif ($i <= 5) {
        $grupo = $educacion_fisica_4_5;
    } elseif ($i <= 7) {
        $grupo = $educacion_fisica_6_7;
    } elseif ($i <= 9) {
        $grupo = $educacion_fisica_8_9;
    } else {
        $grupo = $educacion_fisica_10_11;
    }

    $datos[] = [
        "grado" => $i,
        "asignatura" => "educacion_fisica",
        "dbas" => $grupo
    ];
}

// Salida JSON
echo json_encode([
    "status" => "success",
    "source" => "Estándares Básicos de Competencias en Educación Física, Recreación y Deporte - Ministerio de Educación Nacional de Colombia",
    "nota" => "Estándares organizados por cinco dimensiones: Corporeidad y motricidad, Expresión corporal, Lúdica y recreación, Salud y cuidado del cuerpo, Convivencia y valores. Mapeados como DBAs para compatibilidad del sistema.",
    "dimensiones" => [
        "corporeidad_motricidad" => "Desarrollo de habilidades motrices, coordinación y control corporal en diferentes contextos.",
        "expresion_corporal" => "Uso del cuerpo como medio de expresión, comunicación y creación artística.",
        "ludica_recreacion" => "Valoración del juego y la recreación como medios de desarrollo integral y bienestar.",
        "salud_cuidado" => "Promoción de hábitos saludables y prevención de riesgos en la práctica de actividad física.",
        "convivencia_valores" => "Fomento de valores deportivos, inclusión y convivencia pacífica en contextos lúdico-deportivos."
    ],
    "enfoques" => [
        "pedagogico" => "Enfoque constructivista que parte de las experiencias motrices previas del estudiante.",
        "saludable" => "Promoción de estilos de vida activos y saludables como eje transversal.",
        "inclusivo" => "Garantía de participación equitativa de todos los estudiantes independientemente de sus capacidades.",
        "ludico" => "El juego como estrategia pedagógica fundamental para el aprendizaje significativo.",
        "social" => "La actividad física como medio para el desarrollo de competencias ciudadanas y convivencia."
    ],
    "data" => $datos
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>