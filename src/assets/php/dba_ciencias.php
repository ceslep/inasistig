<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Estructura base solicitada
$datos = [];

// -----------------------------------------------------------------------------
// CICLO 1: GRADOS 1° A 3° - CIENCIAS NATURALES (DBA)
// Enfoque: Indagación, seres vivos, materia y energía, entorno
// -----------------------------------------------------------------------------
$dba_naturales_1_3 = [
    // Indagación científica
    "Formulo preguntas sobre fenómenos naturales de mi entorno y propongo posibles respuestas basadas en mis observaciones.",
    "Registro mis observaciones utilizando dibujos, palabras y números de manera organizada.",
    "Comparo mis predicciones con los resultados de mis exploraciones y explico las diferencias.",

    // Seres vivos y ecosistemas
    "Identifico características de seres vivos y las relaciono con las condiciones de su entorno.",
    "Describo ciclos de vida de plantas y animales y los relaciono con su hábitat.",
    "Reconozco que los seres vivos tienen necesidades básicas (agua, aire, alimento, refugio) y las relaciono con su supervivencia.",

    // Materia y energía
    "Identifico diferentes estados de la materia en objetos de mi entorno y describo cambios sencillos entre ellos.",
    "Reconozco fuentes de luz, calor y sonido en mi entorno y describo sus efectos en los seres vivos.",
    "Identifico fuerzas que producen movimiento en objetos y seres vivos de mi entorno.",

    // Tierra y universo
    "Describo el movimiento aparente del Sol, la Luna y las estrellas y lo relaciono con el paso del tiempo.",
    "Identifico características del suelo, el agua y el aire de mi entorno y su importancia para los seres vivos.",

    // Ciencia, tecnología y sociedad
    "Reconozco que la ciencia y la tecnología se usan para resolver problemas cotidianos y mejorar la calidad de vida.",
    "Identifico prácticas cotidianas que contribuyen al cuidado del medio ambiente y las promuevo en mi entorno."
];

// -----------------------------------------------------------------------------
// CICLO 2: GRADOS 4° A 5° - CIENCIAS NATURALES (DBA)
// -----------------------------------------------------------------------------
$dba_naturales_4_5 = [
    // Indagación científica
    "Formulo preguntas investigables sobre fenómenos naturales y diseño procedimientos sencillos para responderlas.",
    "Registro y organizo datos de mis investigaciones utilizando tablas, gráficas y esquemas.",
    "Extraigo conclusiones de mis investigaciones y las comparo con el conocimiento científico establecido.",

    // Seres vivos y ecosistemas
    "Clasifico seres vivos en grupos taxonómicos según características observables y explico los criterios utilizados.",
    "Describo relaciones alimentarias en ecosistemas y explico el flujo de energía en cadenas tróficas.",
    "Identifico adaptaciones de seres vivos a diferentes ecosistemas y explico su función para la supervivencia.",

    // Materia y energía
    "Clasifico materiales según sus propiedades físicas y químicas y explico sus usos en la vida cotidiana.",
    "Describo transformaciones de energía en fenómenos naturales y artefactos tecnológicos de mi entorno.",
    "Explico cambios de estado de la materia relacionándolos con la transferencia de energía térmica.",

    // Tierra y universo
    "Describo características de las capas de la Tierra y explico su importancia para el sostenimiento de la vida.",
    "Relaciono el movimiento de rotación y traslación de la Tierra con fenómenos como el día, la noche y las estaciones.",
    "Identifico recursos naturales renovables y no renovables y propongo estrategias para su uso responsable.",

    // Ciencia, tecnología y sociedad
    "Explico cómo los avances científicos y tecnológicos han transformado la vida de las personas y el medio ambiente.",
    "Analizo críticamente el impacto de las actividades humanas en los ecosistemas y propongo alternativas de mitigación.",
    "Argumento la importancia de aplicar medidas de prevención y cuidado de la salud basadas en conocimiento científico."
];

// -----------------------------------------------------------------------------
// CICLO 3: GRADOS 6° A 7° - CIENCIAS NATURALES (DBA)
// -----------------------------------------------------------------------------
$dba_naturales_6_7 = [
    // Indagación científica
    "Diseño investigaciones científicas identificando variables, controles y procedimientos para responder preguntas específicas.",
    "Analizo datos cuantitativos y cualitativos utilizando herramientas matemáticas y representaciones gráficas.",
    "Evalúo la validez de mis conclusiones considerando fuentes de error y limitaciones metodológicas.",

    // Seres vivos: célula y funciones vitales
    "Explico la célula como unidad estructural y funcional de los seres vivos y describo las funciones de sus componentes.",
    "Comparo procesos de nutrición, respiración y reproducción en diferentes grupos de organismos.",
    "Relaciono la estructura de órganos y sistemas con las funciones que cumplen en los seres vivos.",

    // Genética y evolución
    "Explico la herencia de características en los seres vivos a partir de la transmisión de información genética.",
    "Relaciono la variabilidad genética con los procesos de selección natural y evolución de las especies.",

    // Ecosistemas y biodiversidad
    "Analizo el equilibrio dinámico de los ecosistemas considerando factores bióticos y abióticos.",
    "Evalúo el impacto de la acción humana en la biodiversidad y propongo estrategias de conservación.",

    // Materia y transformaciones
    "Explico la estructura atómica de la materia y relaciono las propiedades de los elementos con su organización en la tabla periódica.",
    "Clasifico cambios de la materia en físicos y químicos y explico las evidencias que los diferencian.",
    "Describo reacciones químicas sencillas y represento sus ecuaciones balanceadas.",

    // Fuerza, movimiento y energía
    "Explico el movimiento de los cuerpos aplicando las leyes de Newton y representando fuerzas mediante diagramas.",
    "Relaciono diferentes formas de energía y describo transformaciones entre ellas en sistemas naturales y tecnológicos.",
    "Aplico el principio de conservación de la energía para analizar fenómenos físicos cotidianos.",

    // Tierra y universo
    "Explico la dinámica de la corteza terrestre y su relación con fenómenos como sismos, vulcanismo y formación de montañas.",
    "Describo el sistema solar y explico fenómenos astronómicos como eclipses y fases lunares.",

    // Ciencia, tecnología y sociedad
    "Analizo críticamente aplicaciones de la ciencia y la tecnología en la salud, la industria y el medio ambiente.",
    "Argumento posturas éticas frente a dilemas científico-tecnológicos considerando evidencias y valores sociales.",
    "Promuevo el uso responsable del conocimiento científico para el desarrollo sostenible y el bienestar colectivo."
];

// -----------------------------------------------------------------------------
// CICLO 4: GRADOS 8° A 9° - CIENCIAS NATURALES (DBA)
// -----------------------------------------------------------------------------
$dba_naturales_8_9 = [
    // Indagación científica avanzada
    "Formulo hipótesis comprobables basadas en marcos teóricos y diseño experimentos controlados para validarlas.",
    "Utilizo modelos científicos para representar fenómenos complejos y predecir comportamientos bajo diferentes condiciones.",
    "Comunico resultados de investigaciones científicas utilizando lenguaje técnico apropiado y formatos académicos.",

    // Biología molecular y celular
    "Explico los procesos de síntesis de proteínas y su relación con la expresión de características en los organismos.",
    "Analizo la regulación de funciones celulares y su relación con la homeostasis en organismos multicelulares.",
    "Comparo mecanismos de división celular (mitosis y meiosis) y explico su importancia en el crecimiento y la reproducción.",

    // Genética y biotecnología
    "Aplico principios de genética mendeliana para predecir patrones de herencia en cruces genéticos.",
    "Analizo aplicaciones de la biotecnología en medicina, agricultura e industria, evaluando sus beneficios y riesgos éticos.",
    "Argumento posturas frente a la manipulación genética considerando aspectos científicos, éticos y sociales.",

    // Ecología y sostenibilidad
    "Analizo ciclos biogeoquímicos y su relación con el flujo de materia y energía en los ecosistemas.",
    "Evalúo estrategias de manejo sostenible de recursos naturales considerando dimensiones ecológicas, económicas y sociales.",
    "Diseño propuestas de intervención para mitigar problemas ambientales locales basadas en conocimiento científico.",

    // Química de soluciones y reacciones
    "Calculo concentraciones de soluciones y aplico estequiometría para predecir cantidades de reactivos y productos.",
    "Explico factores que afectan la velocidad de reacciones químicas y su aplicación en procesos industriales.",
    "Clasifico sustancias según su comportamiento ácido-base y explico su importancia en sistemas biológicos y ambientales.",

    // Física: ondas, electricidad y magnetismo
    "Describo propiedades de ondas mecánicas y electromagnéticas y explico sus aplicaciones en tecnologías de comunicación.",
    "Analizo circuitos eléctricos aplicando leyes de Ohm y Kirchhoff para predecir comportamiento de componentes.",
    "Explico la relación entre electricidad y magnetismo y sus aplicaciones en generación de energía y dispositivos tecnológicos.",

    // Tierra, clima y cambio global
    "Analizo factores que influyen en el clima y evalúo evidencias del cambio climático antropogénico.",
    "Explico procesos geológicos a escala temporal y su relación con la distribución de recursos y riesgos naturales.",

    // Ciencia, tecnología y sociedad avanzada
    "Evalúo críticamente la producción y validación del conocimiento científico considerando contextos históricos y culturales.",
    "Analizo la relación entre ciencia, tecnología y poder en la toma de decisiones sobre políticas públicas.",
    "Promuevo la alfabetización científica como herramienta para la participación ciudadana informada y responsable."
];

// -----------------------------------------------------------------------------
// CICLO 5: GRADOS 10° A 11° - CIENCIAS NATURALES (DBA)
// -----------------------------------------------------------------------------
$dba_naturales_10_11 = [
    // Indagación científica autónoma
    "Diseño y ejecuto proyectos de investigación científica integrando marcos teóricos, metodologías rigurosas y análisis crítico de resultados.",
    "Evalúo la calidad de fuentes de información científica y argumento posturas basadas en evidencia empírica y razonamiento lógico.",
    "Comunico hallazgos científicos a diversas audiencias adaptando lenguaje, formatos y estrategias según el propósito y contexto.",

    // Biología integradora y sistemas complejos
    "Analizo interacciones entre niveles de organización biológica (moléculas, células, organismos, ecosistemas) para explicar fenómenos complejos.",
    "Evalúo mecanismos de regulación homeostática y su relación con la salud, la enfermedad y la adaptación evolutiva.",
    "Argumento posturas frente a dilemas bioéticos considerando dimensiones científicas, éticas, legales y sociales.",

    // Genómica y biotecnología avanzada
    "Analizo aplicaciones de la genómica y la biotecnología en medicina personalizada, conservación y producción sostenible.",
    "Evalúo implicaciones éticas, legales y sociales de tecnologías emergentes como edición genética y biología sintética.",
    "Diseño propuestas de regulación y gobernanza para el desarrollo responsable de biotecnologías.",

    // Ecología global y sostenibilidad
    "Analizo dinámicas de sistemas socio-ecológicos y evalúo estrategias de adaptación y mitigación frente al cambio global.",
    "Diseño modelos de desarrollo sostenible integrando conocimientos científicos, perspectivas locales y criterios de justicia intergeneracional.",
    "Promuevo acciones colectivas para la conservación de la biodiversidad y la transición hacia economías circulares.",

    // Química analítica y de materiales
    "Aplico principios de termodinámica y cinética química para analizar y optimizar procesos industriales y ambientales.",
    "Diseño y caracteriza materiales con propiedades específicas para aplicaciones tecnológicas y sostenibles.",
    "Evalúo impactos ambientales de procesos químicos y propone alternativas de química verde y economía circular.",

    // Física moderna y tecnologías emergentes
    "Analizo fundamentos de física cuántica y relatividad y sus aplicaciones en tecnologías como computación cuántica y GPS.",
    "Evalúo potencial y limitaciones de energías renovables y tecnologías de almacenamiento para la transición energética.",
    "Argumento posturas sobre desarrollo tecnológico considerando criterios de eficiencia, equidad y sostenibilidad.",

    // Epistemología y ética de la ciencia
    "Analizo críticamente procesos de producción, validación y comunicación del conocimiento científico en contextos contemporáneos.",
    "Evalúo la relación entre ciencia, tecnología y sociedad considerando dimensiones de poder, equidad y justicia epistémica.",
    "Promuevo una cultura científica crítica, reflexiva y comprometida con el bien común y los derechos humanos."
];

// Generación del array por grados independientes (1 a 11)
for ($i = 1; $i <= 11; $i++) {
    $grupo = [];

    if ($i <= 3) {
        $grupo = $dba_naturales_1_3;
    } elseif ($i <= 5) {
        $grupo = $dba_naturales_4_5;
    } elseif ($i <= 7) {
        $grupo = $dba_naturales_6_7;
    } elseif ($i <= 9) {
        $grupo = $dba_naturales_8_9;
    } else {
        $grupo = $dba_naturales_10_11;
    }

    $datos[] = [
        "grado" => $i,
        "asignatura" => "ciencias_naturales",
        "dbas" => $grupo
    ];
}

// Salida JSON
echo json_encode([
    "status" => "success",
    "source" => "Derechos Básicos de Aprendizaje (DBA) - Ciencias Naturales - Ministerio de Educación Nacional de Colombia",
    "nota" => "DBA organizados por ciclos de desarrollo cognitivo y progresión en competencias científicas. Estructura compatible con sistema de planeación inasistig.",
    "enfoques_pedagogicos" => [
        "indagacion" => "Desarrollo de habilidades para formular preguntas, diseñar investigaciones y analizar evidencias.",
        "conceptual" => "Comprensión profunda de conceptos científicos fundamentales y sus interrelaciones.",
        "procedimental" => "Dominio de métodos y técnicas propias del quehacer científico.",
        "actitudinal" => "Fomento de actitudes críticas, éticas y comprometidas con la sostenibilidad.",
        "contextual" => "Vinculación del conocimiento científico con problemas reales y contextos locales y globales."
    ],
    "competencias_transversales" => [
        "pensamiento_critico" => "Capacidad para analizar, evaluar y argumentar con base en evidencia científica.",
        "resolucion_problemas" => "Aplicación del método científico para abordar desafíos complejos.",
        "comunicacion_cientifica" => "Habilidad para comunicar ideas científicas de manera clara y rigurosa.",
        "colaboracion" => "Trabajo en equipo para generar conocimiento y soluciones colectivas.",
        "ciudadania_cientifica" => "Participación informada y responsable en debates sobre ciencia y tecnología."
    ],
    "data" => $datos
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>