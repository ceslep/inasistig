<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Estructura base solicitada
$datos = [];

// -----------------------------------------------------------------------------
// CICLO 1: GRADOS 1° A 3° - RELIGIÓN (ERE)
// Dimensiones: Antropológica, Bíblica, Cristológica, Eclesiológica, Ético-moral
// -----------------------------------------------------------------------------
$religion_1_3 = [
    // Dimensión antropológica: El ser humano y su sentido de trascendencia
    "Reconozco que soy una persona única e irrepetible, creada a imagen y semejanza de Dios.",
    "Valoro mi cuerpo como don de Dios y cuido de él con responsabilidad.",
    "Identifico mis cualidades y talentos como regalos para servir a los demás.",
    "Reconozco la dignidad de todas las personas sin importar su apariencia, origen o condición.",

    // Dimensión bíblica: La Sagrada Escritura como fuente de revelación
    "Identifico relatos bíblicos sencillos que me ayudan a comprender el amor de Dios por la humanidad.",
    "Reconozco en la Biblia historias que me enseñan valores como el amor, la justicia y la solidaridad.",
    "Participo en momentos de oración y celebración de la fe con respeto y atención.",

    // Dimensión cristológica: Jesucristo como modelo de vida
    "Conozco a Jesús como amigo que me enseña a amar y servir a los demás.",
    "Identifico en la vida de Jesús ejemplos de bondad, perdón y servicio que puedo imitar.",
    "Reconozco que Jesús nos invita a vivir en paz y a cuidar de los más necesitados.",

    // Dimensión eclesiológica: La Iglesia como comunidad de fe
    "Identifico a mi familia como el primer espacio donde aprendo a vivir la fe.",
    "Reconozco que la Iglesia es una comunidad de creyentes que se reúnen para celebrar su fe.",
    "Participo activamente en actividades de mi grupo, respetando las normas de convivencia.",

    // Dimensión ético-moral: Valores y compromiso social
    "Practico valores como el amor, el respeto, la honestidad y la solidaridad en mi vida diaria.",
    "Reconozco que mis acciones tienen consecuencias y asumo responsabilidad por ellas.",
    "Pido disculpas cuando hago daño y perdono a quienes me ofenden.",
    "Ayudo a mis compañeros y compañeras cuando lo necesitan, siguiendo el ejemplo de Jesús."
];

// -----------------------------------------------------------------------------
// CICLO 2: GRADOS 4° A 5° - RELIGIÓN (ERE)
// -----------------------------------------------------------------------------
$religion_4_5 = [
    // Dimensión antropológica
    "Reconozco que mi vida tiene un sentido y un propósito que descubro en relación con Dios y los demás.",
    "Identifico mis fortalezas y debilidades como oportunidades de crecimiento personal.",
    "Valoro la importancia de tomar decisiones responsables acordes con mis valores cristianos.",
    "Reconozco que el proyecto de vida se construye día a día con acciones concretas de fe.",

    // Dimensión bíblica
    "Identifico en la Biblia relatos del Antiguo y Nuevo Testamento que me ayudan a comprender la historia de la salvación.",
    "Reconozco a Jesús como modelo de vida que me enseña a amar, perdonar y servir.",
    "Comprendo que los sacramentos son signos del amor de Dios que fortalecen mi fe.",
    "Participo en la celebración eucarística comprendiendo su significado para la vida cristiana.",

    // Dimensión cristológica
    "Analizo las enseñanzas principales de Jesús y las relaciono con situaciones de mi vida cotidiana.",
    "Identifico en los milagros y parábolas de Jesús mensajes de esperanza, justicia y amor.",
    "Reconozco en la Pasión, Muerte y Resurrección de Jesús el fundamento de mi fe.",

    // Dimensión eclesiológica
    "Identifico las características y misión de la Iglesia como comunidad de creyentes.",
    "Reconozco la importancia de la participación en la vida de la comunidad eclesial.",
    "Valoro el testimonio de los santos y mártires como modelos de vida cristiana.",

    // Dimensión ético-moral
    "Distingo entre el bien y el mal a la luz de los valores del Evangelio.",
    "Practico la justicia, la verdad y la misericordia en mis relaciones con los demás.",
    "Reconozco la importancia de la coherencia entre lo que pienso, digo y hago.",
    "Asumo actitudes de respeto y cuidado hacia el medio ambiente como expresión de mi fe."
];

// -----------------------------------------------------------------------------
// CICLO 3: GRADOS 6° A 7° - RELIGIÓN (ERE)
// -----------------------------------------------------------------------------
$religion_6_7 = [
    // Dimensión antropológica
    "Reconozco la dimensión espiritual del ser humano como fuente de sentido y esperanza.",
    "Analizo críticamente los modelos de vida que propone la cultura actual a la luz del Evangelio.",
    "Valoro la importancia de la autoestima y el autocuidado como expresión del amor propio.",
    "Identifico en mi experiencia personal momentos de encuentro con lo trascendente.",

    // Dimensión bíblica
    "Comprendo que la Revelación de Dios se realiza a través de la historia de la salvación.",
    "Analizo los principales relatos del Antiguo Testamento como expresión de la alianza de Dios con su pueblo.",
    "Reconozco en Jesús el cumplimiento de las promesas de Dios y el modelo perfecto de humanidad.",
    "Identifico el papel del Espíritu Santo en la vida de la Iglesia y en mi crecimiento en la fe.",

    // Dimensión cristológica
    "Analizo la persona y misión de Jesucristo a la luz de los Evangelios y la tradición de la Iglesia.",
    "Identifico en las bienaventuranzas un programa de vida cristiana para transformar la sociedad.",
    "Reconozco en la Eucaristía el memorial de la Pascua de Cristo y fuente de vida para la comunidad.",

    // Dimensión eclesiológica
    "Identifico la misión de la Iglesia como continuadora de la obra de Jesús en el mundo.",
    "Reconozco la importancia de los sacramentos como encuentros con Cristo en la vida de la Iglesia.",
    "Analizo críticamente las situaciones de injusticia social desde la perspectiva del Evangelio.",

    // Dimensión ético-moral
    "Aplico criterios éticos cristianos para analizar situaciones de la vida cotidiana.",
    "Distingo entre libertad y libertinaje, reconociendo la responsabilidad que implica ser libre.",
    "Analizo dilemas morales a la luz de la doctrina social de la Iglesia.",
    "Reconozco la importancia de la conciencia moral bien formada para tomar decisiones responsables."
];

// -----------------------------------------------------------------------------
// CICLO 4: GRADOS 8° A 9° - RELIGIÓN (ERE)
// -----------------------------------------------------------------------------
$religion_8_9 = [
    // Dimensión antropológica
    "Analizo críticamente los factores que influyen en la construcción de mi identidad personal desde una perspectiva cristiana.",
    "Reconozco la vocación como llamado de Dios a realizar mi proyecto de vida en servicio a los demás.",
    "Valoro la importancia del discernimiento vocacional como proceso de escucha y respuesta a Dios.",
    "Integro los valores cristianos en la elaboración de mi proyecto de vida personal y profesional.",

    // Dimensión bíblica
    "Comprendo los fundamentos de la moral cristiana a partir de las enseñanzas de Jesús y la tradición de la Iglesia.",
    "Analizo los principales mandamientos de la Ley de Dios como expresión del amor a Dios y al prójimo.",
    "Reconozco la importancia de los sacramentos de la iniciación cristiana en el crecimiento de la fe.",
    "Identifico en la tradición de la Iglesia fuentes de sabiduría para enfrentar los desafíos de la vida.",

    // Dimensión cristológica
    "Analizo la relación entre la divinidad y humanidad de Jesucristo y su relevancia para la vida cristiana.",
    "Identifico en la resurrección de Jesús el fundamento de la esperanza cristiana ante el sufrimiento y la muerte.",
    "Reconozco en el Espíritu Santo la fuerza que transforma y renueva la vida de la Iglesia y de cada creyente.",

    // Dimensión eclesiológica
    "Analizo críticamente las estructuras de pecado que generan injusticia y exclusión social.",
    "Reconozco los principios de la doctrina social de la Iglesia: dignidad humana, bien común, solidaridad y subsidiariedad.",
    "Aplico criterios de la moral social para evaluar políticas públicas y prácticas sociales.",
    "Propongo alternativas éticas para transformar realidades de desigualdad y violencia.",

    // Dimensión ético-moral
    "Reconozco el valor del diálogo interreligioso como camino de encuentro y respeto mutuo.",
    "Analizo críticamente los fundamentalismos y extremismos que amenazan la convivencia pacífica.",
    "Valoro la diversidad religiosa y cultural como expresión de la riqueza de la humanidad.",
    "Promuevo actitudes de tolerancia, respeto y cooperación en mi entorno escolar y social."
];

// -----------------------------------------------------------------------------
// CICLO 5: GRADOS 10° A 11° - RELIGIÓN (ERE)
// -----------------------------------------------------------------------------
$religion_10_11 = [
    // Dimensión antropológica
    "Analizo críticamente las propuestas de sentido de vida que ofrece la cultura contemporánea desde una perspectiva cristiana.",
    "Reconozco la inteligencia espiritual como capacidad para trascender y dar sentido a la existencia.",
    "Valoro la importancia de la reflexión filosófica y teológica en la búsqueda de la verdad.",
    "Integro la dimensión trascendente en la comprensión de mi identidad y proyecto de vida.",

    // Dimensión bíblica
    "Comprendo los fundamentos racionales de la fe cristiana en diálogo con la ciencia y la filosofía.",
    "Analizo críticamente los desafíos que plantea la secularización a la experiencia religiosa.",
    "Reconozco la importancia de la formación teológica para profundizar en el conocimiento de la fe.",
    "Identifico en la tradición cristiana respuestas a las grandes preguntas sobre el sentido de la vida.",

    // Dimensión cristológica
    "Analizo la relación entre fe y razón en la comprensión del misterio de Jesucristo.",
    "Identifico en la teología de la cruz y la resurrección claves para interpretar el sufrimiento y la esperanza.",
    "Reconozco en la pneumatología cristiana fundamentos para la renovación personal y comunitaria.",

    // Dimensión eclesiológica
    "Analizo críticamente los desafíos éticos de la globalización: migración, pobreza, medio ambiente.",
    "Reconozco la responsabilidad ética de las generaciones presentes frente a las futuras.",
    "Aplico principios de la ética cristiana para proponer alternativas de desarrollo sostenible.",
    "Promuevo la participación ciudadana comprometida con la construcción del bien común.",

    // Dimensión ético-moral
    "Analizo críticamente la relación entre fe y cultura en el contexto de la posmodernidad.",
    "Reconozco la importancia de la nueva evangelización como propuesta de sentido para el mundo actual.",
    "Valoro el aporte de la experiencia religiosa a la construcción de una cultura del encuentro y la fraternidad.",
    "Me comprometo con la transformación de la sociedad desde los valores del Evangelio y el compromiso social."
];

// Generación del array por grados independientes (1 a 11)
for ($i = 1; $i <= 11; $i++) {
    $grupo = [];

    if ($i <= 3) {
        $grupo = $religion_1_3;
    } elseif ($i <= 5) {
        $grupo = $religion_4_5;
    } elseif ($i <= 7) {
        $grupo = $religion_6_7;
    } elseif ($i <= 9) {
        $grupo = $religion_8_9;
    } else {
        $grupo = $religion_10_11;
    }

    $datos[] = [
        "grado" => $i,
        "asignatura" => "religion",
        "dbas" => $grupo
    ];
}

// Salida JSON
echo json_encode([
    "status" => "success",
    "source" => "Estándares Básicos de Competencias para Educación Religiosa Escolar (ERE) - Conferencia Episcopal de Colombia / MEN",
    "nota" => "Estándares organizados por cinco dimensiones: Antropológica, Bíblica, Cristológica, Eclesiológica y Ético-moral. Mapeados como DBAs para compatibilidad del sistema inasistig.",
    "dimensiones_ere" => [
        "antropologica" => "Comprensión del ser humano como criatura de Dios, dotado de dignidad, libertad y sentido de trascendencia.",
        "biblica" => "Conocimiento y valoración de la Sagrada Escritura como fuente de revelación, sabiduría y orientación para la vida.",
        "cristologica" => "Jesucristo como centro de la fe cristiana, modelo de vida y fundamento de la esperanza y la transformación personal y social.",
        "eclesiologica" => "La Iglesia como comunidad de fe, sacramento de salvación y servidora de la humanidad en el mundo.",
        "etico_moral" => "Aplicación de principios morales cristianos en la toma de decisiones, la convivencia y el compromiso con la justicia social."
    ],
    "enfoques_pedagogicos" => [
        "kerygmatico" => "Anuncio gozoso del Evangelio como propuesta de sentido y transformación de vida.",
        "mistagogico" => "Iniciación progresiva en los misterios de la fe a través de la experiencia litúrgica y comunitaria.",
        "testemunhal" => "El testimonio de vida como estrategia pedagógica fundamental para la transmisión de la fe.",
        "dialogico" => "Diálogo respetuoso entre fe, razón, cultura y ciencia como camino de crecimiento integral.",
        "comprometido" => "Formación para el compromiso social y la transformación de la realidad desde los valores del Evangelio."
    ],
    "articulacion_normativa" => [
        "constitucion_politica" => "Art. 68: Libertad de enseñanza y respeto a la conciencia moral de padres y estudiantes.",
        "ley_115_1994" => "Art. 23: La Educación Religiosa Escolar como área obligatoria y fundamental en Educación Básica y Media.",
        "decreto_4500_2006" => "Reglamentación de la ERE en coordinación con las autoridades religiosas competentes.",
        "conferencia_episcopal" => "Lineamientos para la Educación Religiosa Escolar en Colombia (2022)."
    ],
    "data" => $datos
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>