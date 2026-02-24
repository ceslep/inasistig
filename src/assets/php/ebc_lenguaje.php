<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Estructura base solicitada
$datos = [];

// -----------------------------------------------------------------------------
// CICLO 1: GRADOS 1° A 3°
// Fuente: Estándares Básicos de Competencias del Lenguaje (MEN 2006)
// Factores: Producción, Comprensión, Literatura, Medios, Ética
// -----------------------------------------------------------------------------
$estandares_1_3 = [
    // Producción Textual
    "Produzco textos escritos que responden a diversas necesidades comunicativas.",
    "Produzco textos orales que responden a distintos propósitos comunicativos.",
    // Comprensión e Interpretación Textual
    "Comprendo textos que tienen diferentes formatos y finalidades.",
    // Literatura
    "Comprendo textos literarios para propiciar el desarrollo de mi capacidad creativa y lúdica.",
    // Medios de Comunicación y Otros Sistemas Simbólicos
    "Reconozco los medios de comunicación masiva y caracterizo la información que difunden.",
    "Comprendo la información que circula a través de algunos sistemas de comunicación no verbal.",
    // Ética de la Comunicación
    "Identifico los principales elementos y roles de la comunicación para enriquecer procesos comunicativos auténticos."
];

// -----------------------------------------------------------------------------
// CICLO 2: GRADOS 4° A 5°
// Fuente: Estándares Básicos de Competencias del Lenguaje (MEN 2006)
// -----------------------------------------------------------------------------
$estandares_4_5 = [
    // Producción Textual
    "Produzco textos orales, en situaciones comunicativas que permiten evidenciar el uso significativo de la entonación y la pertinencia articulatoria.",
    "Produzco textos escritos que responden a diversas necesidades comunicativas y que siguen un procedimiento estratégico para su elaboración.",
    // Comprensión e Interpretación Textual
    "Comprendo diversos tipos de texto, utilizando algunas estrategias de búsqueda, organización y almacenamiento de la información.",
    // Literatura
    "Elaboro hipótesis de lectura acerca de las relaciones entre los elementos constitutivos de un texto literario, y entre éste y el contexto.",
    // Medios de Comunicación y Otros Sistemas Simbólicos
    "Caracterizo los medios de comunicación masiva y selecciono la información que emiten, para utilizarla en la creación de nuevos textos.",
    "Caracterizo el funcionamiento de algunos códigos no verbales con miras a su uso en situaciones comunicativas auténticas.",
    // Ética de la Comunicación
    "Conozco y analizo los elementos, roles, relaciones y reglas básicas de la comunicación, para inferir las intenciones y expectativas de mis interlocutores y hacer más eficaces mis procesos comunicativos."
];

// -----------------------------------------------------------------------------
// CICLO 3: GRADOS 6° A 7°
// Fuente: Estándares Básicos de Competencias del Lenguaje (MEN 2006)
// -----------------------------------------------------------------------------
$estandares_6_7 = [
    // Producción Textual
    "Conozco y utilizo algunas estrategias argumentativas que posibilitan la construcción de textos orales en situaciones comunicativas auténticas.",
    "Produzco textos escritos que responden a necesidades específicas de comunicación, a procedimientos sistemáticos de elaboración y establezco nexos intertextuales y extratextuales.",
    // Comprensión e Interpretación Textual
    "Comprendo e interpreto diversos tipos de texto, para establecer sus relaciones internas y su clasificación en una tipología textual.",
    // Literatura
    "Reconozco la tradición oral como fuente de la conformación y desarrollo de la literatura.",
    "Comprendo obras literarias de diferentes géneros, propiciando así el desarrollo de mi capacidad crítica y creativa.",
    // Medios de Comunicación y Otros Sistemas Simbólicos
    "Caracterizo los medios de comunicación masiva y selecciono la información que emiten para clasificarla y almacenarla.",
    "Relaciono de manera intertextual obras que emplean el lenguaje no verbal y obras que emplean el lenguaje verbal.",
    // Ética de la Comunicación
    "Reconozco, en situaciones comunicativas auténticas, la diversidad y el encuentro de culturas, con el fin de afianzar mis actitudes de respeto y tolerancia."
];

// -----------------------------------------------------------------------------
// CICLO 4: GRADOS 8° A 9°
// Fuente: Estándares Básicos de Competencias del Lenguaje (MEN 2006)
// -----------------------------------------------------------------------------
$estandares_8_9 = [
    // Producción Textual
    "Produzco textos orales de tipo argumentativo para exponer mis ideas y llegar a acuerdos en los que prime el respeto por mi interlocutor y la valoración de los contextos comunicativos.",
    "Produzco textos escritos que evidencian el conocimiento que he alcanzado acerca del funcionamiento de la lengua en situaciones de comunicación y el uso de las estrategias de producción textual.",
    // Comprensión e Interpretación Textual
    "Comprendo e interpreto textos, teniendo en cuenta el funcionamiento de la lengua en situaciones de comunicación, el uso de estrategias de lectura y el papel del interlocutor y del contexto.",
    // Literatura
    "Determino en las obras literarias latinoamericanas, elementos textuales que dan cuenta de sus características estéticas, históricas y sociológicas, cuando sea pertinente.",
    // Medios de Comunicación y Otros Sistemas Simbólicos
    "Retomo crítica y selectivamente la información que circula a través de los medios de comunicación masiva, para confrontarla con la que proviene de otras fuentes.",
    "Comprendo los factores sociales y culturales que determinan algunas manifestaciones del lenguaje no verbal.",
    // Ética de la Comunicación
    "Reflexiono en forma crítica acerca de los actos comunicativos y explico los componentes del proceso de comunicación, con énfasis en los agentes, los discursos, los contextos y el funcionamiento de la lengua, en tanto sistema de signos, símbolos y reglas de uso."
];

// -----------------------------------------------------------------------------
// CICLO 5: GRADOS 10° A 11°
// Fuente: Estándares Básicos de Competencias del Lenguaje (MEN 2006)
// -----------------------------------------------------------------------------
$estandares_10_11 = [
    // Producción Textual
    "Produzco textos argumentativos que evidencian mi conocimiento de la lengua y el control sobre el uso que hago de ella en contextos comunicativos orales y escritos.",
    // Comprensión e Interpretación Textual
    "Comprendo e interpreto textos con actitud crítica y capacidad argumentativa.",
    // Literatura
    "Analizo crítica y creativamente diferentes manifestaciones literarias del contexto universal.",
    // Medios de Comunicación y Otros Sistemas Simbólicos
    "Interpreto en forma crítica la información difundida por los medios de comunicación masiva.",
    "Retomo críticamente los lenguajes no verbales para desarrollar procesos comunicativos intencionados.",
    // Ética de la Comunicación
    "Expreso respeto por la diversidad cultural y social del mundo contemporáneo, en las situaciones comunicativas en las que intervengo."
];

// Generación del array por grados independientes (1 a 11)
for ($i = 1; $i <= 11; $i++) {
    $grupo = [];

    if ($i <= 3) {
        $grupo = $estandares_1_3;
    } elseif ($i <= 5) {
        $grupo = $estandares_4_5;
    } elseif ($i <= 7) {
        $grupo = $estandares_6_7;
    } elseif ($i <= 9) {
        $grupo = $estandares_8_9;
    } else {
        $grupo = $estandares_10_11;
    }

    $datos[] = [
        "grado" => $i,
        "asignatura" => "lenguaje",
        "dbas" => $grupo
    ];
}

// Salida JSON
echo json_encode([
    "status" => "success",
    "source" => "Estándares Básicos de Competencias del Lenguaje (MEN 2006)",
    "nota" => "El documento provisto contiene EBC de Lenguaje, mapeados aquí como DBAs para compatibilidad del sistema. Se incluyen los enunciados identificadores de los 5 factores.",
    "data" => $datos
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>