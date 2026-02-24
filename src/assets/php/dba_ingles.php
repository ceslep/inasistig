<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Estructura base solicitada
$datos = [];

// -----------------------------------------------------------------------------
// GRADO TRANSICIÓN - PRE-A1: Reconocimiento y familiarización
// Enfoque: Escucha, reconocimiento visual, interacción básica
// -----------------------------------------------------------------------------
$english_transicion = [
    // Listening (Comprensión auditiva)
    "Reconozco saludos y despedidas básicas en inglés (hello, goodbye, good morning).",
    "Identifico instrucciones sencillas acompañadas de gestos y apoyos visuales (stand up, sit down, clap your hands).",
    "Reconozco vocabulario básico de mi entorno inmediato (colores, números del 1 al 10, partes del cuerpo, animales).",
    "Respondo físicamente a comandos simples en inglés (Total Physical Response).",

    // Speaking (Producción oral)
    "Repito palabras y frases cortas en inglés con pronunciación aproximada.",
    "Participo en canciones, rimas y juegos verbales en inglés.",
    "Expreso necesidades básicas usando palabras clave en inglés (water, bathroom, help).",

    // Reading (Comprensión lectora)
    "Reconozco palabras escritas en inglés que he escuchado previamente.",
    "Asocio imágenes con palabras escritas en inglés de mi vocabulario básico.",
    "Identifico letras del alfabeto inglés en contextos significativos.",

    // Writing (Producción escrita)
    "Copio palabras y frases cortas en inglés con apoyo visual.",
    "Escribo mi nombre y palabras familiares en inglés con modelo.",

    // Interculturalidad
    "Reconozco que existen otras lenguas diferentes al español.",
    "Muestro interés y curiosidad por aprender palabras en inglés."
];

// -----------------------------------------------------------------------------
// GRADO 1° - A1.1: Inicio de la comunicación básica
// -----------------------------------------------------------------------------
$english_1 = [
    // Listening
    "Comprendo preguntas sencillas sobre información personal (What's your name? How old are you?).",
    "Identifico vocabulario relacionado con mi familia, escuela y objetos del aula.",
    "Sigo instrucciones de dos pasos relacionadas con actividades del salón de clase.",
    "Reconozco sonidos y patrones rítmicos característicos del inglés.",

    // Speaking
    "Me presento y presento a otros usando frases básicas en inglés (My name is..., This is...).",
    "Describo personas y objetos usando adjetivos simples (big, small, happy, sad).",
    "Participo en diálogos guiados sobre temas familiares (gustos, preferencias, rutinas).",
    "Canto canciones y recito rimas en inglés con pronunciación inteligible.",

    // Reading
    "Leo y comprendo palabras y frases cortas acompañadas de imágenes.",
    "Identifico el propósito de textos sencillos (listas, etiquetas, carteles).",
    "Predigo el contenido de un texto a partir de imágenes y títulos.",

    // Writing
    "Escribo palabras y frases cortas para describir imágenes o situaciones familiares.",
    "Completo información personal básica en formatos sencillos en inglés.",
    "Uso mayúsculas y puntos en la escritura de oraciones simples.",

    // Interculturalidad
    "Reconozco costumbres básicas de países donde se habla inglés.",
    "Comparo aspectos de mi cultura con los de otras culturas angloparlantes."
];

// -----------------------------------------------------------------------------
// GRADO 2° - A1.1: Consolidación de comunicación básica
// -----------------------------------------------------------------------------
$english_2 = [
    // Listening
    "Comprendo descripciones breves de personas, lugares y objetos familiares.",
    "Identifico información específica en textos orales sencillos (números, colores, días de la semana).",
    "Distingo entre preguntas y afirmaciones en el habla cotidiana en inglés.",
    "Reconozco la idea principal en cuentos cortos leídos en voz alta.",

    // Speaking
    "Describo mi rutina diaria usando expresiones de tiempo básicas (in the morning, after school).",
    "Expreso gustos y preferencias usando estructuras simples (I like..., I don't like...).",
    "Formulo preguntas sencillas para obtener información básica (Where is...? What is this?).",
    "Participo en conversaciones breves sobre temas de mi interés personal.",

    // Reading
    "Leo cuentos infantiles adaptados y comprendo la secuencia de eventos.",
    "Identifico palabras clave que me ayudan a comprender el sentido global de un texto.",
    "Uso el contexto y las imágenes para inferir el significado de palabras desconocidas.",

    // Writing
    "Escribo oraciones simples para describir personas, lugares y actividades.",
    "Produzco textos cortos con propósito comunicativo claro (invitaciones, mensajes, listas).",
    "Reviso y corrijo mi escritura con apoyo de modelos y retroalimentación.",

    // Interculturalidad
    "Identifico festividades y tradiciones de culturas angloparlantes.",
    "Valoro la diversidad lingüística y cultural a través del aprendizaje del inglés."
];

// -----------------------------------------------------------------------------
// GRADO 3° - A1.2: Desarrollo de comunicación funcional
// -----------------------------------------------------------------------------
$english_3 = [
    // Listening
    "Comprendo instrucciones y explicaciones relacionadas con actividades académicas y recreativas.",
    "Identifico la secuencia temporal en narraciones orales sencillas (first, then, finally).",
    "Reconozco diferentes intenciones comunicativas en textos orales (pedir, informar, invitar).",
    "Comprendo diálogos breves sobre situaciones cotidianas (compras, transporte, escuela).",

    // Speaking
    "Narro experiencias personales y eventos pasados usando conectores temporales básicos.",
    "Expreso opiniones sencillas sobre temas familiares y doy razones breves (I think... because...).",
    "Participo en role-plays simulando situaciones comunicativas reales (tienda, restaurante, consultorio).",
    "Adapto mi lenguaje oral al contexto y al interlocutor (formal/informal).",

    // Reading
    "Leo textos informativos breves y extraigo información específica para cumplir un propósito.",
    "Identifico la estructura básica de diferentes tipos de texto (narrativo, descriptivo, instruccional).",
    "Uso estrategias de lectura (skimming, scanning) para localizar información en textos sencillos.",

    // Writing
    "Escribo párrafos cortos con idea principal y detalles de apoyo sobre temas familiares.",
    "Produzco textos con secuencia lógica usando conectores básicos (and, but, because, then).",
    "Aplico convenciones ortográficas y gramaticales básicas en la producción escrita.",

    // Interculturalidad
    "Analizo similitudes y diferencias entre mi cultura y las de países angloparlantes.",
    "Utilizo el inglés como herramienta para acceder a información de otras culturas."
];

// -----------------------------------------------------------------------------
// GRADO 4° - A1.2/A2.1: Transición hacia comunicación más compleja
// -----------------------------------------------------------------------------
$english_4 = [
    // Listening
    "Comprendo la idea principal y detalles relevantes en textos orales sobre temas conocidos.",
    "Identifico la actitud del hablante (entusiasmo, sorpresa, preocupación) a través de la entonación.",
    "Sigo explicaciones y presentaciones breves sobre temas académicos de otras áreas.",
    "Reconozco vocabulario específico de diferentes campos temáticos (ciencias, artes, deportes).",

    // Speaking
    "Describo procesos y procedimientos usando secuencias lógicas y lenguaje preciso.",
    "Argumento preferencias y decisiones usando conectores de causa-efecto y contraste.",
    "Participo en discusiones grupales expresando acuerdos, desacuerdos y alternativas.",
    "Presento información oralmente usando apoyos visuales y organización clara de ideas.",

    // Reading
    "Leo textos de diferente género (narrativos, informativos, persuasivos) y reconozco sus propósitos.",
    "Infi ero significados de palabras desconocidas a partir del contexto y la estructura morfológica.",
    "Comparo información de diferentes fuentes escritas sobre un mismo tema.",

    // Writing
    "Escribo textos con estructura clara (introducción, desarrollo, conclusión) para diferentes propósitos.",
    "Produzco textos persuasivos sencillos presentando argumentos y ejemplos de apoyo.",
    "Reviso y edito mis escritos considerando coherencia, cohesión y corrección lingüística.",

    // Interculturalidad
    "Analizo críticamente representaciones culturales en textos en inglés.",
    "Utilizo el inglés para investigar y compartir aspectos de mi cultura con audiencias internacionales."
];

// -----------------------------------------------------------------------------
// GRADO 5° - A2.1: Comunicación funcional consolidada
// -----------------------------------------------------------------------------
$english_5 = [
    // Listening
    "Comprendo textos orales de mayor extensión sobre temas académicos y de interés personal.",
    "Identifico relaciones entre ideas en presentaciones y conversaciones (causa-efecto, problema-solución).",
    "Reconozco diferentes registros de lenguaje y los asocio con contextos comunicativos específicos.",
    "Sigo instrucciones complejas de múltiples pasos para realizar tareas académicas.",

    // Speaking
    "Desarrollo presentaciones orales organizadas sobre temas investigados previamente.",
    "Participo en debates sencillos presentando posturas claras y respondiendo a contraargumentos.",
    "Adapto mi discurso oral al propósito, audiencia y contexto comunicativo.",
    "Utilizo estrategias de compensación (paráfrasis, gestos, preguntas de clarificación) cuando encuentro dificultades.",

    // Reading
    "Leo textos auténticos adaptados y aplico estrategias de comprensión profunda (predicción, inferencia, síntesis).",
    "Analizo la intención del autor y el punto de vista en textos de diferente género.",
    "Evalúo la credibilidad y relevancia de fuentes de información escritas.",

    // Writing
    "Produzco textos de diferentes géneros (ensayos breves, reportes, cartas) con coherencia y cohesión.",
    "Utilizo variedad de estructuras gramaticales y vocabulario preciso para expresar ideas complejas.",
    "Aplico procesos de escritura (planificación, borrador, revisión, edición) para mejorar la calidad de mis textos.",

    // Interculturalidad
    "Reflexiono críticamente sobre estereotipos culturales y promuevo el respeto por la diversidad.",
    "Utilizo el inglés como herramienta para la colaboración y el intercambio en contextos globales."
];

// -----------------------------------------------------------------------------
// GRADOS 6° A 7° - A2.1/A2.2: Desarrollo de autonomía comunicativa (Proyección)
// -----------------------------------------------------------------------------
$english_6_7 = [
    // Listening & Speaking
    "Comprendo y participo en conversaciones sobre temas abstractos y académicos.",
    "Expreso opiniones fundamentadas y argumento posturas en discusiones formales e informales.",
    "Adapto mi registro lingüístico a diferentes contextos y audiencias.",

    // Reading & Writing
    "Leo textos auténticos de diferentes géneros y aplico estrategias de análisis crítico.",
    "Produzco textos argumentativos y expositivos con estructura clara y desarrollo coherente de ideas.",
    "Utilizo fuentes diversas para investigar y sintetizar información en producciones escritas.",

    // Interculturalidad
    "Analizo perspectivas culturales diversas y desarrollo empatía intercultural.",
    "Utilizo el inglés para participar en proyectos colaborativos con hablantes de otras lenguas."
];

// -----------------------------------------------------------------------------
// GRADOS 8° A 9° - B1.1: Comunicación independiente (Proyección)
// -----------------------------------------------------------------------------
$english_8_9 = [
    // Listening & Speaking
    "Comprendo textos orales complejos sobre temas concretos y abstractos en diferentes registros.",
    "Participo con fluidez en interacciones espontáneas sobre temas de interés personal y académico.",
    "Desarrollo presentaciones detalladas y respondo preguntas imprevistas con estrategias de compensación.",

    // Reading & Writing
    "Leo textos extensos y analizo perspectivas múltiples, intenciones del autor y recursos retóricos.",
    "Produzco textos claros y detallados sobre diversos temas, integrando fuentes y argumentando posturas.",
    "Aplico convenciones académicas en la producción escrita (citas, referencias, estructura formal).",

    // Interculturalidad
    "Evalúo críticamente representaciones culturales y mediáticas en contextos globales.",
    "Utilizo el inglés para investigar, colaborar y contribuir en comunidades de práctica internacionales."
];

// -----------------------------------------------------------------------------
// GRADOS 10° A 11° - B1.2: Consolidación de competencia comunicativa (Proyección)
// -----------------------------------------------------------------------------
$english_10_11 = [
    // Listening & Speaking
    "Comprendo discursos complejos y técnicos en mi área de interés y especialización.",
    "Participo activamente en debates y negociaciones, adaptando estrategias discursivas al contexto.",
    "Desarrollo presentaciones profesionales con recursos multimedia y manejo de audiencias diversas.",

    // Reading & Writing
    "Leo y analizo textos académicos y profesionales, evaluando argumentos, metodologías y conclusiones.",
    "Produzco textos especializados con rigor académico, coherencia argumentativa y estilo apropiado.",
    "Sintetizo información de múltiples fuentes para generar conocimiento nuevo y propuestas innovadoras.",

    // Interculturalidad
    "Actúo como mediador intercultural facilitando la comunicación y colaboración entre comunidades diversas.",
    "Utilizo el inglés para el desarrollo profesional, la investigación y la ciudadanía global responsable."
];

// Generación del array por grados independientes (1 a 11)
for ($i = 1; $i <= 11; $i++) {
    $grupo = [];

    if ($i == 1) {
        $grupo = $english_1;
    } elseif ($i == 2) {
        $grupo = $english_2;
    } elseif ($i == 3) {
        $grupo = $english_3;
    } elseif ($i == 4) {
        $grupo = $english_4;
    } elseif ($i == 5) {
        $grupo = $english_5;
    } elseif ($i <= 7) {
        $grupo = $english_6_7;
    } elseif ($i <= 9) {
        $grupo = $english_8_9;
    } else {
        $grupo = $english_10_11;
    }

    $datos[] = [
        "grado" => $i,
        "asignatura" => "english",
        "dbas" => $grupo
    ];
}

// Salida JSON
echo json_encode([
    "status" => "success",
    "source" => "Derechos Básicos de Aprendizaje (DBA) - Inglés: Transición y Primaria - Ministerio de Educación Nacional de Colombia",
    "nota" => "DBA organizados por niveles del Marco Común Europeo de Referencia (Pre-A1, A1.1, A1.2, A2.1, A2.2, B1.1, B1.2) y por habilidades comunicativas: Listening, Speaking, Reading, Writing, Intercultural Competence. Estructura compatible con sistema de planeación inasistig.",
    "niveles_mcer" => [
        "Pre-A1" => "Reconocimiento y familiarización con el idioma.",
        "A1.1" => "Comunicación básica en contextos familiares y predecibles.",
        "A1.2" => "Comunicación funcional para satisfacer necesidades inmediatas.",
        "A2.1" => "Interacción sencilla en situaciones cotidianas y académicas básicas.",
        "A2.2" => "Comunicación más autónoma en contextos conocidos.",
        "B1.1" => "Comunicación independiente en temas concretos y abstractos.",
        "B1.2" => "Consolidación de competencia para uso académico y profesional."
    ],
    "habilidades_comunicativas" => [
        "listening" => "Comprensión auditiva: procesar y comprender mensajes orales.",
        "speaking" => "Producción oral: expresar ideas de forma clara y apropiada.",
        "reading" => "Comprensión lectora: interpretar y analizar textos escritos.",
        "writing" => "Producción escrita: comunicar ideas por escrito con coherencia.",
        "intercultural" => "Competencia intercultural: interactuar respetuosamente en contextos diversos."
    ],
    "enfoques_pedagogicos" => [
        "comunicativo" => "Enfoque centrado en el uso real del idioma para fines comunicativos.",
        "accion_orientado" => "Aprendizaje basado en tareas y proyectos con propósito auténtico.",
        "integrado" => "Articulación de las cuatro habilidades con la competencia intercultural.",
        "progresivo" => "Desarrollo gradual de competencias según niveles de complejidad.",
        "contextualizado" => "Aprendizaje situado en contextos significativos para el estudiante."
    ],
    "data" => $datos
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>