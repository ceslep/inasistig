<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Estructura base solicitada
$datos = [];

// -----------------------------------------------------------------------------
// CICLO 1: GRADOS 1° A 3° - CIENCIAS SOCIALES (DBA)
// Enfoque: Reconocimiento del entorno, identidad, normas básicas, relaciones sociales
// -----------------------------------------------------------------------------
$dba_sociales_1_3 = [
    // Reconocimiento de sí mismo y del entorno
    "Me reconozco como un ser único e irrepetible, con características propias que me diferencian de los demás.",
    "Identifico y describo características físicas, sociales y culturales de mi familia, mi escuela y mi comunidad.",
    "Reconozco que pertenezco a diferentes grupos sociales (familia, escuela, barrio) y valoro su importancia.",

    // Relaciones espaciales y temporales básicas
    "Me ubico en el espacio utilizando referentes espaciales básicos (arriba, abajo, cerca, lejos, derecha, izquierda).",
    "Reconozco secuencias temporales básicas (antes, ahora, después) en mi vida cotidiana y en mi entorno.",
    "Identifico cambios y permanencias en mi vida personal y en mi entorno cercano a través del tiempo.",

    // Normas y convivencia
    "Identifico normas básicas de convivencia en mi familia, escuela y comunidad, y comprendo su propósito.",
    "Reconozco mis derechos y deberes como niño o niña y los relaciono con los de otras personas.",
    "Practico valores como el respeto, la solidaridad y la honestidad en mis relaciones cotidianas.",

    // Reconocimiento de la diversidad
    "Reconozco y respeto las diferencias y semejanzas entre las personas de mi entorno (género, etnia, cultura, capacidades).",
    "Identifico manifestaciones culturales de mi comunidad (fiestas, tradiciones, comidas) y valoro su importancia.",
    "Reconozco que en Colombia convivimos personas de diversas etnias y culturas y valoro esta diversidad.",

    // Relaciones con el entorno natural
    "Identifico características del paisaje natural y cultural de mi entorno y reconozco su importancia.",
    "Reconozco la relación entre las actividades humanas y el cuidado del medio ambiente en mi comunidad.",
    "Participo en acciones sencillas para el cuidado de mi entorno escolar y comunitario."
];

// -----------------------------------------------------------------------------
// CICLO 2: GRADOS 4° A 5° - CIENCIAS SOCIALES (DBA)
// -----------------------------------------------------------------------------
$dba_sociales_4_5 = [
    // Identidad y pertenencia cultural
    "Reconozco mi identidad personal y cultural, y la relaciono con la de mi comunidad y región.",
    "Identifico y describo características sociales, políticas, económicas y culturales de comunidades prehispánicas de Colombia y América.",
    "Reconozco la influencia de las culturas indígenas, africanas y europeas en la conformación de la identidad colombiana.",

    // Relaciones espaciales y representación
    "Utilizo coordenadas, escalas y convenciones para ubicar fenómenos históricos y culturales en mapas y planos.",
    "Identifico y describo características de las diferentes regiones naturales de Colombia y el mundo.",
    "Establezco relaciones entre la ubicación geográfica y las características climáticas, económicas y culturales de diferentes lugares.",

    // Organización social y política básica
    "Identifico y describo características y funciones básicas de organizaciones sociales y políticas de mi entorno (familia, escuela, municipio).",
    "Comparo formas de organización social y política de diferentes épocas y culturas con las actuales.",
    "Reconozco la importancia de las normas y los acuerdos para la convivencia en diferentes grupos sociales.",

    // Economía y actividades productivas
    "Comparo actividades económicas que se llevan a cabo en diferentes entornos y reconozco su impacto en las comunidades.",
    "Identifico diferentes sectores económicos (agrícola, ganadero, industrial, comercial) y su relación con el desarrollo de las comunidades.",
    "Reconozco la importancia del trabajo y el intercambio de bienes y servicios para el desarrollo social.",

    // Derechos, deberes y participación
    "Identifico mis derechos y deberes como niño o niña y los relaciono con los Derechos Humanos y la Constitución.",
    "Participo en la construcción de normas para la convivencia en los grupos a los que pertenezco.",
    "Reconozco mecanismos básicos de participación ciudadana en mi escuela y comunidad.",

    // Memoria histórica y patrimonio
    "Identifico huellas del pasado en mi entorno (monumentos, tradiciones, relatos) y reconozco su valor histórico.",
    "Reconozco la importancia de preservar el patrimonio cultural y natural de mi comunidad y país.",
    "Valoro los aportes de diferentes grupos culturales al desarrollo histórico y cultural de Colombia."
];

// -----------------------------------------------------------------------------
// CICLO 3: GRADOS 6° A 7° - CIENCIAS SOCIALES (DBA)
// -----------------------------------------------------------------------------
$dba_sociales_6_7 = [
    // Comprensión histórica y análisis de procesos
    "Analizo características de la organización social, política y económica en diferentes culturas y épocas históricas.",
    "Establezco relaciones entre eventos históricos, sus causas, consecuencias e incidencia en la vida de diferentes agentes.",
    "Comparo diferentes culturas con la sociedad colombiana actual y propongo explicaciones para semejanzas y diferencias.",

    // Pensamiento espacial y territorial
    "Localizo diversas culturas en el espacio geográfico y analizo relaciones entre entorno físico y desarrollo cultural.",
    "Utilizo herramientas cartográficas (mapas, planos, sistemas de información geográfica) para analizar fenómenos sociales.",
    "Analizo procesos de transformación del territorio colombiano y sus implicaciones sociales, económicas y políticas.",

    // Sistemas políticos y ciudadanía
    "Comparo entre sí algunos sistemas políticos estudiados y los relaciono con el sistema político colombiano.",
    "Identifico variaciones en el significado del concepto de ciudadanía en diversas culturas a través del tiempo.",
    "Analizo mecanismos de participación ciudadana y su importancia para la construcción democrática.",

    // Economía y desarrollo
    "Comparo organizaciones económicas de diferentes culturas y períodos históricos con las de la actualidad.",
    "Analizo relaciones entre recursos naturales, actividades productivas y desarrollo socioeconómico de las comunidades.",
    "Identifico factores económicos, sociales y políticos que generan cooperación o conflicto en las organizaciones sociales.",

    // Diversidad cultural y derechos humanos
    "Reconozco y valoro la diversidad étnica y cultural de Colombia y su aporte a la identidad nacional.",
    "Analizo situaciones de discriminación y exclusión social y propongo alternativas para promover la equidad.",
    "Argumento la importancia de respetar y promover los Derechos Humanos en contextos diversos.",

    // Relaciones sociedad-naturaleza
    "Analizo la manera como diferentes comunidades se han relacionado con el medio ambiente a lo largo de la historia.",
    "Identifico impactos de las actividades humanas en el medio ambiente y propongo estrategias de manejo responsable.",
    "Reconozco la importancia del desarrollo sostenible para el bienestar de las generaciones presentes y futuras."
];

// -----------------------------------------------------------------------------
// CICLO 4: GRADOS 8° A 9° - CIENCIAS SOCIALES (DBA)
// -----------------------------------------------------------------------------
$dba_sociales_8_9 = [
    // Análisis crítico de procesos históricos
    "Analizo críticamente procesos históricos que han configurado la sociedad colombiana y latinoamericana.",
    "Establezco relaciones entre revoluciones, independencias y procesos de modernización con la situación actual de Colombia.",
    "Identifico corrientes de pensamiento político, económico y cultural y su influencia en el desarrollo social.",

    // Territorio, región y globalización
    "Analizo procesos de integración regional y globalización y sus implicaciones para Colombia y América Latina.",
    "Identifico factores que han generado migraciones y desplazamientos humanos y sus consecuencias sociales y culturales.",
    "Analizo relaciones entre organización territorial, poder político y desarrollo socioeconómico en diferentes escalas.",

    // Democracia, Estado y participación
    "Analizo características del Estado Social de Derecho y su importancia para garantizar derechos ciudadanos.",
    "Evalúo mecanismos de participación ciudadana contemplados en la Constitución y su aplicación en contextos reales.",
    "Identifico roles y responsabilidades de diferentes actores políticos en la construcción democrática.",

    // Economía, desarrollo y desigualdad
    "Analizo modelos económicos implementados en Colombia y América Latina y sus impactos sociales y ambientales.",
    "Identifico causas estructurales de la desigualdad social y propongo alternativas para promover la equidad.",
    "Evalúo relaciones entre desarrollo económico, bienestar social y sostenibilidad ambiental.",

    // Derechos humanos, diversidad y conflicto
    "Analizo críticamente situaciones de vulneración de Derechos Humanos en contextos históricos y actuales.",
    "Argumento posturas éticas frente a dilemas relacionados con diversidad cultural, género y equidad social.",
    "Analizo causas y consecuencias del conflicto armado en Colombia y valoro iniciativas de construcción de paz.",

    // Sociedad, ciencia y tecnología
    "Analizo impactos sociales, culturales y ambientales de avances científicos y tecnológicos en diferentes épocas.",
    "Evalúo críticamente el papel de los medios de comunicación en la formación de opinión pública y cultura.",
    "Argumento posturas responsables frente al uso de la tecnología y la información en la sociedad contemporánea."
];

// -----------------------------------------------------------------------------
// CICLO 5: GRADOS 10° A 11° - CIENCIAS SOCIALES (DBA)
// -----------------------------------------------------------------------------
$dba_sociales_10_11 = [
    // Pensamiento histórico complejo y prospectiva
    "Analizo críticamente procesos históricos mundiales del siglo XX y su incidencia en la situación actual de Colombia.",
    "Evalúo diferentes interpretaciones de hechos históricos y construyo posturas argumentadas basadas en evidencia.",
    "Proyecta escenarios futuros posibles a partir del análisis de tendencias históricas, sociales y políticas.",

    // Geopolítica, globalización y desarrollo
    "Analiza críticamente el orden mundial contemporáneo, bloques económicos y relaciones de poder entre Estados.",
    "Evalúa impactos de la globalización en dimensiones económicas, políticas, sociales y culturales de Colombia.",
    "Propone alternativas de desarrollo que integren criterios de sostenibilidad, equidad y soberanía nacional.",

    // Democracia deliberativa y acción ciudadana
    "Analiza críticamente el funcionamiento de las instituciones democráticas y propone mecanismos para fortalecerlas.",
    "Diseña y argumenta propuestas de participación ciudadana para abordar problemas sociales y políticos relevantes.",
    "Evalúa críticamente discursos políticos y mediáticos, identificando intereses, sesgos y estrategias persuasivas.",

    // Economía política y justicia social
    "Analiza críticamente relaciones entre modelos económicos, distribución de la riqueza y justicia social.",
    "Evalúa políticas públicas desde criterios de eficiencia, equidad y sostenibilidad.",
    "Propone alternativas económicas y sociales que promuevan el desarrollo humano integral y la reducción de desigualdades.",

    // Derechos humanos, paz y memoria histórica
    "Analiza críticamente la situación de los Derechos Humanos en Colombia y el mundo, y propone estrategias de promoción y defensa.",
    "Argumenta posturas éticas y políticas frente a dilemas relacionados con memoria, verdad, justicia y reparación.",
    "Diseña iniciativas pedagógicas y sociales para la construcción de paz y la reconciliación en contextos diversos.",

    // Sociedad del conocimiento y responsabilidad global
    "Analiza críticamente impactos de las tecnologías de la información y la comunicación en la sociedad contemporánea.",
    "Evalúa responsabilidades éticas y políticas frente a desafíos globales como cambio climático, migraciones y desigualdad.",
    "Propone acciones individuales y colectivas para promover una ciudadanía global responsable y solidaria."
];

// Generación del array por grados independientes (1 a 11)
for ($i = 1; $i <= 11; $i++) {
    $grupo = [];

    if ($i <= 3) {
        $grupo = $dba_sociales_1_3;
    } elseif ($i <= 5) {
        $grupo = $dba_sociales_4_5;
    } elseif ($i <= 7) {
        $grupo = $dba_sociales_6_7;
    } elseif ($i <= 9) {
        $grupo = $dba_sociales_8_9;
    } else {
        $grupo = $dba_sociales_10_11;
    }

    $datos[] = [
        "grado" => $i,
        "asignatura" => "ciencias_sociales",
        "dbas" => $grupo
    ];
}

// Salida JSON
echo json_encode([
    "status" => "success",
    "source" => "Derechos Básicos de Aprendizaje (DBA) - Ciencias Sociales - Ministerio de Educación Nacional de Colombia",
    "nota" => "DBA organizados por ciclos de desarrollo cognitivo y progresión en competencias sociales y ciudadanas. Estructura compatible con sistema de planeación inasistig.",
    "enfoques_pedagogicos" => [
        "historico" => "Comprensión de procesos históricos y su incidencia en la sociedad actual.",
        "espacial" => "Análisis de relaciones entre territorio, sociedad y cultura.",
        "ciudadano" => "Formación para la participación democrática y el ejercicio responsable de la ciudadanía.",
        "critico" => "Desarrollo de pensamiento crítico para analizar realidades sociales complejas.",
        "etico" => "Promoción de valores democráticos, derechos humanos y construcción de paz."
    ],
    "competencias_transversales" => [
        "pensamiento_sistemico" => "Capacidad para analizar relaciones complejas entre factores sociales, políticos, económicos y culturales.",
        "argumentacion" => "Habilidad para sustentar posturas con evidencia, razonamiento lógico y respeto por perspectivas diversas.",
        "empatia_historica" => "Capacidad para comprender perspectivas de actores históricos y contemporáneos en sus contextos.",
        "accion_ciudadana" => "Disposición y capacidad para participar constructivamente en la transformación social.",
        "responsabilidad_global" => "Conciencia de interdependencia global y compromiso con desafíos comunes de la humanidad."
    ],
    "data" => $datos
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>