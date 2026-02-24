<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Estructura base solicitada
$datos = [];

// -----------------------------------------------------------------------------
// CICLO 1: GRADOS 1° A 3° - ÉTICA Y RELIGIÓN
// Dimensiones: Identidad, Experiencia religiosa, Valores, Comunidad
// -----------------------------------------------------------------------------
$etica_religion_1_3 = [
    // Identidad y dignidad humana
    "Reconozco que soy una persona única e irrepetible, creada a imagen y semejanza de Dios.",
    "Valoro mi cuerpo como don de Dios y cuido de él con responsabilidad.",
    "Identifico mis cualidades y talentos como regalos para servir a los demás.",
    "Reconozco la dignidad de todas las personas sin importar su apariencia, origen o condición.",

    // Experiencia religiosa y sentido de la vida
    "Expreso con palabras, gestos y dibujos mis sentimientos de gratitud hacia Dios.",
    "Identifico en la naturaleza señales del amor y cuidado de Dios por la creación.",
    "Reconozco que la oración es una forma de conversar con Dios y expresar mis necesidades.",
    "Participo en celebraciones y momentos de oración con respeto y atención.",

    // Valores y convivencia
    "Practico valores como el amor, el respeto, la honestidad y la solidaridad en mi vida diaria.",
    "Reconozco que mis acciones tienen consecuencias y asumo responsabilidad por ellas.",
    "Pido disculpas cuando hago daño y perdono a quienes me ofenden.",
    "Ayudo a mis compañeros y compañeras cuando lo necesitan, siguiendo el ejemplo de Jesús.",

    // Comunidad y pertenencia
    "Identifico a mi familia como el primer espacio donde aprendo a amar y ser amado.",
    "Reconozco que la Iglesia es una comunidad de creyentes que se reúnen para celebrar su fe.",
    "Participo activamente en actividades de mi grupo, respetando las normas de convivencia.",
    "Valoro la diversidad de personas y culturas como expresión de la riqueza de la creación."
];

// -----------------------------------------------------------------------------
// CICLO 2: GRADOS 4° A 5° - ÉTICA Y RELIGIÓN
// -----------------------------------------------------------------------------
$etica_religion_4_5 = [
    // Identidad y proyecto de vida
    "Reconozco que mi vida tiene un sentido y un propósito que descubro en relación con Dios y los demás.",
    "Identifico mis fortalezas y debilidades como oportunidades de crecimiento personal.",
    "Valoro la importancia de tomar decisiones responsables acordes con mis valores.",
    "Reconozco que el proyecto de vida se construye día a día con acciones concretas.",

    // Experiencia religiosa y Biblia
    "Identifico en la Biblia relatos que me ayudan a comprender el amor de Dios por la humanidad.",
    "Reconozco a Jesús como modelo de vida que me enseña a amar y servir.",
    "Comprendo que los sacramentos son signos del amor de Dios que fortalecen mi fe.",
    "Participo en la celebración eucarística comprendiendo su significado para la vida cristiana.",

    // Valores éticos y morales
    "Distingo entre el bien y el mal a la luz de los valores del Evangelio.",
    "Practico la justicia, la verdad y la misericordia en mis relaciones con los demás.",
    "Reconozco la importancia de la coherencia entre lo que pienso, digo y hago.",
    "Asumo actitudes de respeto y cuidado hacia el medio ambiente como expresión de mi fe.",

    // Comunidad y servicio
    "Identifico las necesidades de mi comunidad y propongo acciones para contribuir a su bienestar.",
    "Reconozco que el servicio a los demás es una forma de vivir mi fe cristiana.",
    "Participo en proyectos solidarios que promueven la dignidad de las personas.",
    "Valoro el diálogo y el encuentro como caminos para construir una sociedad más fraterna."
];

// -----------------------------------------------------------------------------
// CICLO 3: GRADOS 6° A 7° - ÉTICA Y RELIGIÓN
// -----------------------------------------------------------------------------
$etica_religion_6_7 = [
    // Identidad y dimensión trascendente
    "Reconozco la dimensión espiritual del ser humano como fuente de sentido y esperanza.",
    "Analizo críticamente los modelos de vida que propone la cultura actual a la luz del Evangelio.",
    "Valoro la importancia de la autoestima y el autocuidado como expresión del amor propio.",
    "Identifico en mi experiencia personal momentos de encuentro con lo trascendente.",

    // Experiencia religiosa y revelación
    "Comprendo que la Revelación de Dios se realiza a través de la historia de la salvación.",
    "Analizo los principales relatos del Antiguo Testamento como expresión de la alianza de Dios con su pueblo.",
    "Reconozco en Jesús el cumplimiento de las promesas de Dios y el modelo perfecto de humanidad.",
    "Identifico el papel del Espíritu Santo en la vida de la Iglesia y en mi crecimiento en la fe.",

    // Ética y discernimiento moral
    "Aplico criterios éticos cristianos para analizar situaciones de la vida cotidiana.",
    "Distingo entre libertad y libertinaje, reconociendo la responsabilidad que implica ser libre.",
    "Analizo dilemas morales a la luz de la doctrina social de la Iglesia.",
    "Reconozco la importancia de la conciencia moral bien formada para tomar decisiones responsables.",

    // Comunidad eclesial y compromiso social
    "Identifico la misión de la Iglesia como continuadora de la obra de Jesús en el mundo.",
    "Reconozco la importancia de la participación en la vida de la comunidad eclesial.",
    "Analizo críticamente las situaciones de injusticia social desde la perspectiva del Evangelio.",
    "Propongo acciones concretas para contribuir a la construcción de una sociedad más justa y solidaria."
];

// -----------------------------------------------------------------------------
// CICLO 4: GRADOS 8° A 9° - ÉTICA Y RELIGIÓN
// -----------------------------------------------------------------------------
$etica_religion_8_9 = [
    // Identidad, vocación y proyecto de vida
    "Analizo críticamente los factores que influyen en la construcción de mi identidad personal.",
    "Reconozco la vocación como llamado de Dios a realizar mi proyecto de vida en servicio a los demás.",
    "Valoro la importancia del discernimiento vocacional como proceso de escucha y respuesta a Dios.",
    "Integro los valores cristianos en la elaboración de mi proyecto de vida personal y profesional.",

    // Experiencia religiosa y teología moral
    "Comprendo los fundamentos de la moral cristiana a partir de las enseñanzas de Jesús.",
    "Analizo los principales mandamientos de la Ley de Dios como expresión del amor a Dios y al prójimo.",
    "Reconozco la importancia de los sacramentos de la iniciación cristiana en el crecimiento de la fe.",
    "Identifico en la tradición de la Iglesia fuentes de sabiduría para enfrentar los desafíos de la vida.",

    // Ética social y doctrina social de la Iglesia
    "Analizo críticamente las estructuras de pecado que generan injusticia y exclusión social.",
    "Reconozco los principios de la doctrina social de la Iglesia: dignidad humana, bien común, solidaridad y subsidiariedad.",
    "Aplico criterios de la moral social para evaluar políticas públicas y prácticas sociales.",
    "Propongo alternativas éticas para transformar realidades de desigualdad y violencia.",

    // Diálogo interreligioso y cultura de paz
    "Reconozco el valor del diálogo interreligioso como camino de encuentro y respeto mutuo.",
    "Analizo críticamente los fundamentalismos y extremismos que amenazan la convivencia pacífica.",
    "Valoro la diversidad religiosa y cultural como expresión de la riqueza de la humanidad.",
    "Promuevo actitudes de tolerancia, respeto y cooperación en mi entorno escolar y social."
];

// -----------------------------------------------------------------------------
// CICLO 5: GRADOS 10° A 11° - ÉTICA Y RELIGIÓN
// -----------------------------------------------------------------------------
$etica_religion_10_11 = [
    // Identidad, sentido de la vida y trascendencia
    "Analizo críticamente las propuestas de sentido de vida que ofrece la cultura contemporánea.",
    "Reconozco la inteligencia espiritual como capacidad para trascender y dar sentido a la existencia.",
    "Valoro la importancia de la reflexión filosófica y teológica en la búsqueda de la verdad.",
    "Integro la dimensión trascendente en la comprensión de mi identidad y proyecto de vida.",

    // Experiencia religiosa y fundamentos de la fe
    "Comprendo los fundamentos racionales de la fe cristiana en diálogo con la ciencia y la filosofía.",
    "Analizo críticamente los desafíos que plantea la secularización a la experiencia religiosa.",
    "Reconozco la importancia de la formación teológica para profundizar en el conocimiento de la fe.",
    "Identifico en la tradición cristiana respuestas a las grandes preguntas sobre el sentido de la vida.",

    // Ética global y compromiso transformador
    "Analizo críticamente los desafíos éticos de la globalización: migración, pobreza, medio ambiente.",
    "Reconozco la responsabilidad ética de las generaciones presentes frente a las futuras.",
    "Aplico principios de la ética cristiana para proponer alternativas de desarrollo sostenible.",
    "Promuevo la participación ciudadana comprometida con la construcción del bien común.",

    // Diálogo fe-cultura y nueva evangelización
    "Analizo críticamente la relación entre fe y cultura en el contexto de la posmodernidad.",
    "Reconozco la importancia de la nueva evangelización como propuesta de sentido para el mundo actual.",
    "Valoro el aporte de la experiencia religiosa a la construcción de una cultura del encuentro y la fraternidad.",
    "Me comprometo con la transformación de la sociedad desde los valores del Evangelio y el compromiso social."
];

// Generación del array por grados independientes (1 a 11)
for ($i = 1; $i <= 11; $i++) {
    $grupo = [];

    if ($i <= 3) {
        $grupo = $etica_religion_1_3;
    } elseif ($i <= 5) {
        $grupo = $etica_religion_4_5;
    } elseif ($i <= 7) {
        $grupo = $etica_religion_6_7;
    } elseif ($i <= 9) {
        $grupo = $etica_religion_8_9;
    } else {
        $grupo = $etica_religion_10_11;
    }

    $datos[] = [
        "grado" => $i,
        "asignatura" => "etica_religion",
        "dbas" => $grupo
    ];
}

// Salida JSON
echo json_encode([
    "status" => "success",
    "source" => "Estándares para la Educación Religiosa Escolar - Conferencia Episcopal de Colombia (2022)",
    "nota" => "Estándares organizados por dimensiones: Identidad y dignidad humana, Experiencia religiosa, Valores éticos, Comunidad y servicio. Mapeados como DBAs para compatibilidad del sistema.",
    "dimensiones" => [
        "identidad_dignidad" => "Reconocimiento de la persona humana como imagen de Dios y sujeto de derechos y deberes.",
        "experiencia_religiosa" => "Comprensión y vivencia de la fe cristiana a través de la Biblia, los sacramentos y la oración.",
        "valores_eticos" => "Aplicación de principios morales cristianos en la toma de decisiones y la convivencia.",
        "comunidad_servicio" => "Compromiso con la construcción de una sociedad más justa, fraterna y solidaria."
    ],
    "enfoques" => [
        "antropologico" => "Comprensión integral del ser humano en sus dimensiones física, psicológica, social y espiritual.",
        "biblico" => "Lectura e interpretación de la Sagrada Escritura como fuente de revelación y sabiduría.",
        "cristologico" => "Jesucristo como modelo perfecto de humanidad y fundamento de la vida cristiana.",
        "eclesiologico" => "La Iglesia como comunidad de fe, sacramento de salvación y servidora de la humanidad.",
        "etico_moral" => "Aplicación de la doctrina moral cristiana a los desafíos personales y sociales."
    ],
    "data" => $datos
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>