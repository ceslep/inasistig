<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Estructura base solicitada
$datos = [];

// -----------------------------------------------------------------------------
// CICLO 1: GRADOS 1° A 3° - EDUCACIÓN ARTÍSTICA
// Dimensiones: Sensibilidad, Apreciación estética, Comunicación, Creación, Contextualización
// -----------------------------------------------------------------------------
$artistica_1_3 = [
    // Sensibilidad y percepción sensorial
    "Exploro y experimento con los sentidos para descubrir texturas, colores, sonidos y movimientos en mi entorno.",
    "Identifico y nombro emociones que me generan diferentes manifestaciones artísticas.",
    "Reconozco que el arte me permite expresar lo que siento y pienso de maneras creativas.",

    // Apreciación estética
    "Observo con atención obras artísticas y describo lo que veo, escucho o siento.",
    "Reconozco que existen diferentes formas de hacer arte: pintura, música, danza, teatro, entre otras.",
    "Disfruto de manifestaciones artísticas propias de mi cultura y de otras culturas.",

    // Comunicación artística
    "Utilizo mi cuerpo, la voz y materiales sencillos para comunicar ideas y emociones.",
    "Participo en actividades artísticas grupales respetando los turnos y aportes de mis compañeros.",
    "Comparto mis creaciones artísticas y escucho con respeto las de los demás.",

    // Creación y expresión
    "Creo dibujos, collages, sonidos y movimientos a partir de mi imaginación y experiencias.",
    "Experimento con diferentes materiales y técnicas artísticas para explorar nuevas formas de expresión.",
    "Transformo materiales de mi entorno en producciones artísticas sencillas.",

    // Contextualización cultural
    "Reconozco que el arte hace parte de la vida de las personas y las comunidades.",
    "Identifico manifestaciones artísticas de mi región y valoro su importancia cultural.",
    "Participo en celebraciones y festividades donde se expresan manifestaciones artísticas de mi comunidad."
];

// -----------------------------------------------------------------------------
// CICLO 2: GRADOS 4° A 5° - EDUCACIÓN ARTÍSTICA
// -----------------------------------------------------------------------------
$artistica_4_5 = [
    // Sensibilidad y percepción sensorial
    "Desarrollo mi capacidad de observación para identificar elementos estéticos en el arte y en la naturaleza.",
    "Reconozco cómo los elementos del lenguaje artístico (línea, color, forma, sonido, ritmo) generan diferentes efectos expresivos.",
    "Identifico y expreso las emociones que me provocan distintas manifestaciones artísticas.",

    // Apreciación estética
    "Analizo obras artísticas identificando sus elementos, técnicas y posibles intenciones expresivas.",
    "Comparo diferentes manifestaciones artísticas reconociendo sus características distintivas.",
    "Valoro la diversidad de expresiones artísticas como manifestación de la creatividad humana.",

    // Comunicación artística
    "Utilizo diferentes lenguajes artísticos para comunicar ideas, emociones y experiencias personales.",
    "Participo en procesos de creación colectiva aportando ideas y respetando las propuestas de otros.",
    "Presento mis producciones artísticas explicando las decisiones que tomé en el proceso creativo.",

    // Creación y expresión
    "Diseño y produzco obras artísticas utilizando diferentes materiales, técnicas y lenguajes.",
    "Experimento con la combinación de elementos artísticos para generar nuevas propuestas expresivas.",
    "Transformo ideas y emociones en producciones artísticas con intención comunicativa.",

    // Contextualización cultural
    "Reconozco manifestaciones artísticas de diferentes épocas y culturas y su relación con el contexto histórico.",
    "Identifico el papel del arte en la construcción de identidad cultural de las comunidades.",
    "Valoro y promuevo las expresiones artísticas tradicionales de mi región y país."
];

// -----------------------------------------------------------------------------
// CICLO 3: GRADOS 6° A 7° - EDUCACIÓN ARTÍSTICA
// -----------------------------------------------------------------------------
$artistica_6_7 = [
    // Sensibilidad y percepción sensorial
    "Desarrollo una mirada crítica y sensible frente a las manifestaciones artísticas del entorno.",
    "Analizo cómo los elementos del lenguaje artístico se articulan para generar significados y efectos expresivos.",
    "Reflexiono sobre la relación entre emoción, percepción y creación en los procesos artísticos.",

    // Apreciación estética
    "Interpreto obras artísticas considerando sus elementos formales, contexto de producción y posibles lecturas.",
    "Comparo y contrasto diferentes manifestaciones artísticas identificando sus características estéticas y culturales.",
    "Argumento mis apreciaciones estéticas fundamentándolas en conocimientos sobre el lenguaje artístico.",

    // Comunicación artística
    "Utilizo lenguajes artísticos específicos para comunicar ideas complejas y posturas personales.",
    "Participo en procesos de creación colaborativa asumiendo roles y responsabilidades en el equipo.",
    "Socializo mis producciones artísticas estableciendo diálogos críticos con la audiencia.",

    // Creación y expresión
    "Diseño y desarrollo proyectos artísticos que integran diferentes lenguajes y técnicas.",
    "Experimento con procesos creativos que combinan tradición e innovación en la expresión artística.",
    "Produzco obras artísticas con intención estética, comunicativa y/o crítica.",

    // Contextualización cultural
    "Analizo manifestaciones artísticas en relación con su contexto histórico, social y cultural.",
    "Reconozco la diversidad de expresiones artísticas como patrimonio cultural de la humanidad.",
    "Valoro críticamente el papel del arte en la transformación social y la construcción de identidad."
];

// -----------------------------------------------------------------------------
// CICLO 4: GRADOS 8° A 9° - EDUCACIÓN ARTÍSTICA
// -----------------------------------------------------------------------------
$artistica_8_9 = [
    // Sensibilidad y percepción sensorial
    "Desarrollo una sensibilidad estética informada que me permite apreciar la complejidad de las manifestaciones artísticas.",
    "Analizo críticamente cómo los elementos del lenguaje artístico construyen significados en diferentes contextos.",
    "Reflexiono sobre la relación entre arte, emoción, conocimiento y transformación personal.",

    // Apreciación estética
    "Interpreto obras artísticas desde múltiples perspectivas, considerando aspectos formales, conceptuales y contextuales.",
    "Establezco relaciones entre diferentes manifestaciones artísticas identificando influencias, tendencias y rupturas.",
    "Argumento juicios estéticos fundamentados en conocimientos teóricos y experiencia sensible.",

    // Comunicación artística
    "Utilizo lenguajes artísticos con dominio técnico y expresivo para comunicar ideas, posturas y emociones complejas.",
    "Lidero o participo activamente en procesos de creación colectiva con intención artística y social.",
    "Socializo y defiendo mis producciones artísticas en espacios de diálogo crítico y construcción de sentido.",

    // Creación y expresión
    "Diseño y ejecuto proyectos artísticos que integran investigación, experimentación y producción con coherencia conceptual.",
    "Innovar en procesos creativos combinando técnicas tradicionales y contemporáneas con intención expresiva.",
    "Produzco obras artísticas con rigor técnico, profundidad conceptual y relevancia estética.",

    // Contextualización cultural
    "Analizo críticamente manifestaciones artísticas en relación con procesos históricos, políticos y culturales.",
    "Reconozco y valoro la diversidad artística como expresión de la riqueza cultural y la creatividad humana.",
    "Reflexiono sobre el papel del arte en la construcción de identidades, la memoria colectiva y la transformación social."
];

// -----------------------------------------------------------------------------
// CICLO 5: GRADOS 10° A 11° - EDUCACIÓN ARTÍSTICA
// -----------------------------------------------------------------------------
$artistica_10_11 = [
    // Sensibilidad y percepción sensorial
    "Desarrollo una sensibilidad estética crítica que me permite apreciar, interpretar y valorar la complejidad del arte contemporáneo.",
    "Analizo cómo los lenguajes artísticos construyen significados en diálogo con contextos culturales, políticos y sociales.",
    "Reflexiono sobre la relación entre arte, conocimiento, emoción y transformación personal y colectiva.",

    // Apreciación estética
    "Interpreto obras artísticas desde perspectivas teóricas, históricas y críticas, estableciendo conexiones con el contexto actual.",
    "Establezco relaciones complejas entre manifestaciones artísticas de diferentes épocas, culturas y disciplinas.",
    "Argumento juicios estéticos fundamentados en marcos teóricos, experiencia sensible y reflexión crítica.",

    // Comunicación artística
    "Utilizo lenguajes artísticos con dominio técnico, expresivo y conceptual para comunicar ideas, posturas y propuestas transformadoras.",
    "Lidero procesos de creación colectiva con intención artística, social y/o política, gestionando equipos y recursos.",
    "Socializo y debate mis producciones artísticas en espacios académicos, culturales y comunitarios con rigor y apertura.",

    // Creación y expresión
    "Diseño y ejecuto proyectos artísticos de autoría que integran investigación, experimentación, producción y difusión con coherencia conceptual.",
    "Innovar en procesos creativos combinando técnicas, tecnologías y lenguajes con intención expresiva y crítica.",
    "Produzco obras artísticas con rigor técnico, profundidad conceptual, relevancia estética y proyección social.",

    // Contextualización cultural
    "Analizo críticamente manifestaciones artísticas en relación con procesos globales, locales y personales de transformación cultural.",
    "Reconozco, valoro y promuevo la diversidad artística como expresión de la riqueza cultural, la creatividad humana y la construcción de paz.",
    "Reflexiono y actúo sobre el papel del arte en la construcción de identidades, la memoria histórica, la justicia social y la sostenibilidad."
];

// Generación del array por grados independientes (1 a 11)
for ($i = 1; $i <= 11; $i++) {
    $grupo = [];

    if ($i <= 3) {
        $grupo = $artistica_1_3;
    } elseif ($i <= 5) {
        $grupo = $artistica_4_5;
    } elseif ($i <= 7) {
        $grupo = $artistica_6_7;
    } elseif ($i <= 9) {
        $grupo = $artistica_8_9;
    } else {
        $grupo = $artistica_10_11;
    }

    $datos[] = [
        "grado" => $i,
        "asignatura" => "educacion_artistica",
        "dbas" => $grupo
    ];
}

// Salida JSON
echo json_encode([
    "status" => "success",
    "source" => "Estándares Básicos de Competencias en Educación Artística - Ministerio de Educación Nacional de Colombia",
    "nota" => "Estándares organizados por cinco dimensiones: Sensibilidad, Apreciación estética, Comunicación artística, Creación y expresión, Contextualización cultural. Mapeados como DBAs para compatibilidad del sistema.",
    "dimensiones" => [
        "sensibilidad" => "Desarrollo de la capacidad de percibir, sentir y conectar emocionalmente con manifestaciones artísticas.",
        "apreciacion_estetica" => "Capacidad de observar, analizar, interpretar y valorar obras y manifestaciones artísticas.",
        "comunicacion_artistica" => "Uso de lenguajes artísticos para expresar, compartir y dialogar sobre ideas y emociones.",
        "creacion_expresion" => "Capacidad de producir obras artísticas con intención expresiva, estética y comunicativa.",
        "contextualizacion" => "Comprensión del arte en relación con contextos históricos, culturales y sociales."
    ],
    "enfoques" => [
        "integral" => "Formación que articula sensibilidad, conocimiento, creación y reflexión crítica.",
        "cultural" => "Reconocimiento y valoración de la diversidad artística como patrimonio humano.",
        "creativo" => "Fomento de la imaginación, experimentación e innovación en procesos expresivos.",
        "social" => "Comprensión del arte como herramienta de transformación personal y colectiva.",
        "critico" => "Desarrollo de capacidad de análisis, interpretación y juicio fundamentado sobre el arte."
    ],
    "data" => $datos
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>