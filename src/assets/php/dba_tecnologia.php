<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Estructura base solicitada
$datos = [];

// -----------------------------------------------------------------------------
// CICLO 1: GRADOS 1° A 3° - TECNOLOGÍA E INFORMÁTICA (DBA)
// Componentes: Naturaleza, Apropiación, Solución problemas, Tecnología-sociedad, 
//              Investigación, Pensamiento computacional, Programación
// -----------------------------------------------------------------------------
$dba_tecnologia_1_3 = [
    // Naturaleza y conocimiento de la tecnología
    "Reconozco artefactos tecnológicos de mi entorno y explico su utilidad para satisfacer necesidades básicas.",
    "Identifico diferencias entre objetos naturales y objetos creados por el ser humano mediante tecnología.",
    "Describo cómo han cambiado algunos artefactos tecnológicos a lo largo del tiempo (teléfono, transporte, comunicación).",

    // Apropiación y uso de la tecnología
    "Utilizo herramientas tecnológicas básicas (computador, tablet, aplicaciones) siguiendo instrucciones de seguridad.",
    "Aplico normas básicas de cuidado y mantenimiento de dispositivos tecnológicos del aula.",
    "Reconozco símbolos y señales de seguridad en el uso de tecnologías (advertencias eléctricas, protección de datos).",

    // Solución de problemas con tecnología
    "Identifico problemas sencillos de mi entorno que pueden resolverse con el uso de artefactos tecnológicos.",
    "Propongo ideas creativas para mejorar artefactos tecnológicos de uso cotidiano en mi hogar o escuela.",
    "Ensamblo y desensamblo componentes tecnológicos sencillos siguiendo instrucciones gráficas.",

    // Tecnología y sociedad
    "Reconozco que el uso de la tecnología afecta mi vida diaria y la de mi comunidad.",
    "Participo en actividades grupales donde uso tecnología para compartir ideas y trabajar en equipo.",
    "Cuido el medio ambiente al usar y desechar correctamente dispositivos tecnológicos (reciclaje, reutilización).",

    // Investigación y gestión de información (Componente nuevo - DBA investigación)
    "Formulo preguntas sencillas sobre temas de mi interés y busco respuestas usando diferentes medios (libros, internet, personas).",
    "Selecciono información básica de fuentes confiables (docente, familia, libros escolares) para responder mis preguntas.",
    "Registro mis hallazgos usando dibujos, palabras clave o grabaciones de voz.",

    // Pensamiento computacional básico
    "Sigo secuencias lógicas de pasos para resolver tareas sencillas (recetas, instrucciones de juego, rutinas).",
    "Identifico patrones repetitivos en actividades cotidianas y los represento con símbolos o dibujos.",
    "Descompongo tareas complejas en pasos más pequeños y sencillos de realizar.",

    // Programación e iniciación algorítmica
    "Doy instrucciones claras y ordenadas para que otros realicen una tarea (juegos de roles, comandos simples).",
    "Utilizo aplicaciones de programación visual básica (bloques, iconos) para crear secuencias sencillas.",
    "Detecto y corrijo errores en secuencias de instrucciones cuando no obtengo el resultado esperado."
];

// -----------------------------------------------------------------------------
// CICLO 2: GRADOS 4° A 5° - TECNOLOGÍA E INFORMÁTICA (DBA)
// Enfoque especial: Búsqueda rigurosa de información (DBA central grado 5°)
// -----------------------------------------------------------------------------
$dba_tecnologia_4_5 = [
    // Naturaleza y conocimiento de la tecnología
    "Explico la evolución de artefactos tecnológicos y su impacto en la transformación de la sociedad.",
    "Comparo diferentes tecnologías que cumplen la misma función y analizo sus ventajas y desventajas.",
    "Identifico conceptos científicos y técnicos que sustentan el funcionamiento de artefactos cotidianos.",

    // Apropiación y uso de la tecnología
    "Utilizo eficientemente herramientas tecnológicas para realizar tareas académicas y personales.",
    "Aplico criterios de selección para elegir productos tecnológicos según mis necesidades (calidad, costo, impacto).",
    "Configuro y personalizo dispositivos y aplicaciones según mis preferencias y requerimientos de uso.",

    // Solución de problemas con tecnología
    "Diseño soluciones tecnológicas sencillas para problemas de mi entorno usando materiales y herramientas disponibles.",
    "Evalúo diferentes alternativas de solución tecnológica considerando restricciones de tiempo, costo y recursos.",
    "Documentó el proceso de diseño y construcción de mis soluciones tecnológicas mediante esquemas y descripciones.",

    // Tecnología y sociedad
    "Analizo críticamente el impacto social y ambiental del uso de tecnologías en mi comunidad.",
    "Participo en debates sobre el uso responsable de la tecnología y propongo normas de convivencia digital.",
    "Promuevo prácticas de consumo tecnológico responsable y sostenible en mi entorno escolar y familiar.",

    // Investigación y gestión de información (DBA CENTRAL - Grado 5°)
    "Utilizo diferentes estrategias de búsqueda y selección de información que me permitan profundizar en el rigor académico de mis requerimientos académicos.",
    "Formulo preguntas de investigación claras y específicas utilizando conectores y estructuras de búsqueda apropiadas.",
    "Evalúo la credibilidad y confiabilidad de las fuentes de información (autor, fecha, institución, propósito).",
    "Aplico criterios de selección para filtrar información relevante según mi propósito académico (palabras clave, operadores de búsqueda).",
    "Organizo la información recopilada usando esquemas, mapas conceptuales o bases de datos sencillas.",
    "Cito adecuadamente las fuentes de información utilizadas respetando los derechos de autor y la propiedad intelectual.",

    // Pensamiento computacional
    "Represento procesos y algoritmos mediante diagramas de flujo, pseudocódigo o bloques de programación.",
    "Identifico y aplico estructuras de control básicas (secuencia, selección, repetición) en la resolución de problemas.",
    "Abstraigo características esenciales de objetos o situaciones para crear modelos simplificados.",

    // Programación e iniciación algorítmica
    "Diseño y ejecuto programas sencillos usando entornos de programación visual por bloques.",
    "Depuro y optimizo código identificando y corrigiendo errores lógicos y de sintaxis.",
    "Comparto y colaboro en proyectos de programación utilizando repositorios básicos o plataformas educativas."
];

// -----------------------------------------------------------------------------
// CICLO 3: GRADOS 6° A 7° - TECNOLOGÍA E INFORMÁTICA (DBA)
// -----------------------------------------------------------------------------
$dba_tecnologia_6_7 = [
    // Naturaleza y conocimiento de la tecnología
    "Analizo la interrelación entre ciencia, tecnología y sociedad en el desarrollo histórico de artefactos y sistemas.",
    "Explico principios científicos que fundamentan el funcionamiento de sistemas tecnológicos complejos.",
    "Evalúo el ciclo de vida de productos tecnológicos desde su diseño hasta su disposición final.",

    // Apropiación y uso de la tecnología
    "Selecciono y utilizo tecnologías apropiadas para optimizar procesos de aprendizaje y productividad personal.",
    "Aplico normas de ergonomía, seguridad digital y protección de datos en el uso de tecnologías.",
    "Integro diferentes herramientas tecnológicas para desarrollar proyectos interdisciplinares.",

    // Solución de problemas con tecnología
    "Aplico metodologías de diseño tecnológico (design thinking, ingeniería inversa) para resolver problemas complejos.",
    "Prototipo soluciones tecnológicas iterativamente incorporando retroalimentación y pruebas de usabilidad.",
    "Comunico mis propuestas de solución tecnológica usando representaciones técnicas (planos, diagramas, modelos 3D).",

    // Tecnología y sociedad
    "Argumento posturas éticas frente a dilemas tecnológicos (privacidad, inteligencia artificial, automatización).",
    "Analizo críticamente cómo las tecnologías de información influyen en la construcción de identidad y relaciones sociales.",
    "Promuevo iniciativas de inclusión digital y acceso equitativo a tecnologías en mi comunidad.",

    // Investigación y gestión de información avanzada
    "Diseño estrategias de búsqueda avanzada utilizando operadores booleanos, filtros especializados y bases de datos académicas.",
    "Sintetiza información de múltiples fuentes contrastando perspectivas y evaluando sesgos metodológicos.",
    "Elabora revisiones bibliográficas estructuradas que fundamentan académicamente mis investigaciones.",
    "Utiliza gestores de referencias bibliográficas para organizar y citar fuentes según normas académicas (APA, IEEE).",

    // Pensamiento computacional intermedio
    "Modela problemas del mundo real utilizando estructuras de datos básicas (listas, pilas, colas, árboles).",
    "Analiza la eficiencia de algoritmos considerando complejidad temporal y espacial en contextos prácticos.",
    "Aplica técnicas de descomposición, reconocimiento de patrones y generalización en el diseño de soluciones.",

    // Programación y desarrollo de software
    "Desarrolla aplicaciones sencillas usando lenguajes de programación textual (Python, JavaScript, Scratch avanzado).",
    "Implementa estructuras de control y funciones para crear programas modulares y reutilizables.",
    "Colabora en proyectos de software utilizando prácticas básicas de control de versiones (Git, GitHub educativo)."
];

// -----------------------------------------------------------------------------
// CICLO 4: GRADOS 8° A 9° - TECNOLOGÍA E INFORMÁTICA (DBA)
// -----------------------------------------------------------------------------
$dba_tecnologia_8_9 = [
    // Naturaleza y conocimiento de la tecnología
    "Evalúa críticamente cómo los paradigmas tecnológicos transforman modelos económicos, políticos y culturales.",
    "Analiza la evolución de sistemas tecnológicos complejos y su relación con avances científicos interdisciplinares.",
    "Argumenta sobre implicaciones epistemológicas de la tecnología en la construcción del conocimiento.",

    // Apropiación y uso de la tecnología
    "Gestiona proyectos tecnológicos integrando herramientas colaborativas, metodologías ágiles y evaluación de impacto.",
    "Aplica principios de diseño centrado en el usuario para desarrollar experiencias tecnológicas accesibles e inclusivas.",
    "Evalúa la sostenibilidad técnica, económica y ambiental de soluciones tecnológicas antes de su implementación.",

    // Solución de problemas con tecnología
    "Diseña arquitecturas de solución tecnológica considerando escalabilidad, interoperabilidad y mantenimiento.",
    "Aplica pruebas sistemáticas (unitarias, de integración, de usuario) para validar la calidad de soluciones tecnológicas.",
    "Documenta técnicamente procesos de desarrollo facilitando la transferencia de conocimiento y la replicabilidad.",

    // Tecnología y sociedad
    "Debate críticamente sobre gobernanza tecnológica, regulación de datos y derechos digitales en contextos democráticos.",
    "Analiza cómo las tecnologías emergentes (IA, blockchain, IoT) reconfiguran relaciones de poder y equidad social.",
    "Promueve alfabetización digital crítica que empodere a comunidades para participar en decisiones tecnológicas.",

    // Investigación y gestión de información experta
    "Diseña protocolos de investigación tecnológica que integren métodos cuantitativos, cualitativos y mixtos.",
    "Evalúa críticamente la calidad metodológica de estudios tecnológicos considerando validez, confiabilidad y sesgos.",
    "Sintetiza hallazgos de investigación en revisiones sistemáticas o meta-análisis que orienten decisiones informadas.",
    "Comunica resultados de investigación tecnológica adaptando lenguaje y formatos a audiencias diversas (académica, técnica, pública).",

    // Pensamiento computacional avanzado
    "Modela sistemas complejos utilizando paradigmas de programación orientada a objetos, funcional o concurrente.",
    "Evalúa trade-offs en el diseño de algoritmos considerando rendimiento, mantenibilidad y usabilidad.",
    "Aplica técnicas de inteligencia artificial básica (clasificación, clustering, regresión) para resolver problemas del mundo real.",

    // Programación y desarrollo profesional
    "Desarrolla aplicaciones web o móviles siguiendo estándares de calidad, seguridad y experiencia de usuario.",
    "Implementa prácticas de desarrollo colaborativo (code review, integración continua, documentación técnica).",
    "Evalúa y selecciona frameworks, librerías y herramientas tecnológicas según criterios técnicos y de proyecto."
];

// -----------------------------------------------------------------------------
// CICLO 5: GRADOS 10° A 11° - TECNOLOGÍA E INFORMÁTICA (DBA)
// -----------------------------------------------------------------------------
$dba_tecnologia_10_11 = [
    // Naturaleza y conocimiento de la tecnología
    "Analiza críticamente cómo los paradigmas tecnológicos contemporáneos redefinen conceptos de identidad, privacidad y agencia humana.",
    "Evalúa la interdependencia entre innovación tecnológica, sostenibilidad ambiental y justicia social en escenarios globales.",
    "Argumenta posturas fundamentadas sobre ética tecnológica considerando marcos filosóficos, legales y culturales diversos.",

    // Apropiación y uso de la tecnología
    "Lidera proyectos de transformación digital que integren visión estratégica, gestión del cambio y evaluación de impacto social.",
    "Diseña ecosistemas tecnológicos que promuevan innovación responsable, inclusión digital y desarrollo humano integral.",
    "Evalúa críticamente modelos de negocio tecnológicos considerando externalidades, equidad y sostenibilidad a largo plazo.",

    // Solución de problemas con tecnología
    "Diseña arquitecturas de solución tecnológica escalables que respondan a requisitos funcionales, no funcionales y de evolución.",
    "Aplica metodologías de investigación-acción o diseño basado en evidencia para iterar soluciones tecnológicas en contextos reales.",
    "Comunica propuestas tecnológicas complejas adaptando argumentos técnicos, económicos y sociales a stakeholders diversos.",

    // Tecnología y sociedad
    "Debate críticamente sobre gobernanza algorítmica, sesgos en IA y derechos digitales en sociedades democráticas.",
    "Analiza cómo las tecnologías disruptivas reconfiguran mercados laborales, educación y participación ciudadana.",
    "Promueve políticas públicas tecnológicas que equilibren innovación, regulación y protección de derechos fundamentales.",

    // Investigación y gestión de información de alto nivel
    "Diseña investigaciones tecnológicas que integren marcos teóricos robustos, métodos rigurosos y análisis crítico de resultados.",
    "Evalúa la calidad epistemológica de investigaciones tecnológicas considerando paradigmas, métodos y límites del conocimiento.",
    "Sintetiza conocimiento tecnológico en revisiones críticas, meta-análisis o propuestas de agenda de investigación futura.",
    "Comunica hallazgos de investigación tecnológica en formatos académicos, técnicos y de divulgación pública con rigor y accesibilidad.",

    // Pensamiento computacional experto
    "Modela problemas complejos utilizando múltiples paradigmas de programación y estructuras de datos avanzadas.",
    "Evalúa y optimiza algoritmos considerando complejidad computacional, escalabilidad y adaptabilidad a contextos cambiantes.",
    "Aplica técnicas de aprendizaje automático, procesamiento de datos o computación distribuida para resolver desafíos tecnológicos reales.",

    // Programación y desarrollo de sistemas
    "Desarrolla sistemas software complejos aplicando patrones de diseño, principios de arquitectura y prácticas de ingeniería de software.",
    "Lidera equipos de desarrollo utilizando metodologías ágiles, gestión de calidad y prácticas de DevOps.",
    "Evalúa y selecciona tecnologías emergentes considerando madurez técnica, ecosistema de soporte y alineación estratégica."
];

// Generación del array por grados independientes (1 a 11)
for ($i = 1; $i <= 11; $i++) {
    $grupo = [];

    if ($i <= 3) {
        $grupo = $dba_tecnologia_1_3;
    } elseif ($i <= 5) {
        $grupo = $dba_tecnologia_4_5;
    } elseif ($i <= 7) {
        $grupo = $dba_tecnologia_6_7;
    } elseif ($i <= 9) {
        $grupo = $dba_tecnologia_8_9;
    } else {
        $grupo = $dba_tecnologia_10_11;
    }

    $datos[] = [
        "grado" => $i,
        "asignatura" => "tecnologia_informatica",
        "dbas" => $grupo
    ];
}

// Salida JSON
echo json_encode([
    "status" => "success",
    "source" => "Derechos Básicos de Aprendizaje (DBA) - Tecnología e Informática - Universidad de Nariño (2023) / MEN Colombia",
    "nota" => "DBA organizados por componentes tradicionales del MEN más componentes emergentes: Investigación/gestión de información, Pensamiento computacional, Programación. Estructura compatible con sistema de planeación inasistig.",
    "componentes_tradicionales" => [
        "naturaleza_conocimiento" => "Comprensión de la tecnología como construcción humana, su evolución y fundamentos científicos.",
        "apropiacion_uso" => "Utilización competente, segura y crítica de herramientas y sistemas tecnológicos.",
        "solucion_problemas" => "Aplicación de metodologías de diseño y desarrollo tecnológico para resolver necesidades reales.",
        "tecnologia_sociedad" => "Reflexión ética y crítica sobre el impacto social, ambiental y cultural de la tecnología."
    ],
    "componentes_emergentes" => [
        "investigacion_informacion" => "Búsqueda, selección, evaluación y gestión rigurosa de información con fines académicos (DBA central grado 5°).",
        "pensamiento_computacional" => "Descomposición, abstracción, reconocimiento de patrones y diseño algorítmico para resolver problemas.",
        "programacion_desarrollo" => "Creación, depuración y colaboración en el desarrollo de software y soluciones digitales."
    ],
    "enfoques_pedagogicos" => [
        "constructivista" => "Aprendizaje activo basado en proyectos, indagación y construcción de conocimiento significativo.",
        "transversal" => "Integración de tecnología con otras áreas del currículo para resolver problemas complejos.",
        "critico_reflexivo" => "Desarrollo de posturas éticas y argumentadas frente a dilemas tecnológicos contemporáneos.",
        "colaborativo" => "Trabajo en equipo, comunicación efectiva y gestión de proyectos tecnológicos compartidos.",
        "innovador" => "Fomento de creatividad, experimentación y emprendimiento tecnológico con impacto social."
    ],
    "data" => $datos
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>