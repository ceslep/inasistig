<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$datos = [];

// -----------------------------------------------------------------------------
// CICLO 1: GRADOS 1° A 3° - CIENCIAS SOCIALES
// -----------------------------------------------------------------------------
$sociales_1_3 = [
    "Identifico y describo algunas características socioculturales de comunidades a las que pertenezco y de otras diferentes.",
    "Identifico y describo cambios y aspectos que se mantienen en mí y en las organizaciones de mi entorno.",
    "Reconozco en mi entorno cercano las huellas que dejaron las comunidades que lo ocuparon en el pasado.",
    "Me ubico en el entorno físico y de representación (mapas y planos) utilizando referentes espaciales.",
    "Establezco relaciones entre los espacios físicos que ocupo y sus representaciones.",
    "Identifico y describo las características de un paisaje natural y de un paisaje cultural.",
    "Comparo actividades económicas que se llevan a cabo en diferentes entornos.",
    "Identifico y describo características y funciones básicas de organizaciones sociales y políticas de mi entorno (familia, colegio, barrio).",
    "Identifico mis derechos y deberes y los de otras personas en las comunidades a las que pertenezco.",
    "Reconozco normas que rigen algunas comunidades y distingo aquellas en cuya construcción puedo participar."
];

// -----------------------------------------------------------------------------
// CICLO 2: GRADOS 4° A 5° - CIENCIAS SOCIALES
// -----------------------------------------------------------------------------
$sociales_4_5 = [
    "Identifico y explico fenómenos sociales y económicos que permitieron el paso del nomadismo al sedentarismo.",
    "Identifico y describo características sociales, políticas, económicas y culturales de las primeras organizaciones humanas.",
    "Identifico, describo y comparo algunas características de las comunidades prehispanicas de Colombia y América.",
    "Me ubico en el entorno físico utilizando referentes espaciales (izquierda, derecha, puntos cardinales).",
    "Identifico y describo características de las diferentes regiones naturales del mundo.",
    "Clasifico y describo diferentes actividades económicas en diferentes sectores económicos.",
    "Identifico y describo algunas características de las organizaciones político-administrativas colombianas en diferentes épocas.",
    "Conozco los Derechos de los Niños e identifico instituciones que velan por su cumplimiento.",
    "Explico el impacto de algunos hechos históricos en la formación limítrofe del territorio colombiano."
];

// -----------------------------------------------------------------------------
// CICLO 3: GRADOS 6° A 7° - CIENCIAS SOCIALES
// -----------------------------------------------------------------------------
$sociales_6_7 = [
    "Describo características de la organización social, política o económica en algunas culturas y épocas.",
    "Comparo diferentes culturas con la sociedad colombiana actual y propongo explicaciones para semejanzas y diferencias.",
    "Reconozco que la división entre un período histórico y otro es un intento por caracterizar los hechos históricos.",
    "Utilizo coordenadas, convenciones y escalas para trabajar con mapas y planos de representación.",
    "Localizo diversas culturas en el espacio geográfico y reconozco las principales características físicas de su entorno.",
    "Identifico sistemas de producción en diferentes culturas y períodos históricos.",
    "Reconozco y describo diferentes formas que ha asumido la democracia a través de la historia.",
    "Comparo entre sí algunos sistemas políticos estudiados y a la vez con el sistema político colombiano.",
    "Identifico variaciones en el significado del concepto de ciudadanía en diversas culturas a través del tiempo."
];

// -----------------------------------------------------------------------------
// CICLO 4: GRADOS 8° A 9° - CIENCIAS SOCIALES
// -----------------------------------------------------------------------------
$sociales_8_9 = [
    "Explico las principales características de algunas revoluciones de los siglos XVIII y XIX.",
    "Explico la influencia de estas revoluciones en procesos sociales, políticos y económicos posteriores en Colombia y América Latina.",
    "Analizo algunas de las condiciones que dieron origen a los procesos de independencia de los pueblos americanos.",
    "Describo las principales características físicas de los diversos ecosistemas.",
    "Explico la manera como el medio ambiente influye en el tipo de organización social y económica.",
    "Comparo las causas de algunas olas de migración y desplazamiento humano en nuestro territorio.",
    "Comparo los mecanismos de participación ciudadana contemplados en las constituciones políticas de 1886 y 1991.",
    "Identifico y explico algunos de los principales procesos políticos del siglo XIX en Colombia.",
    "Reconozco en el pago de los impuestos una forma importante de solidaridad ciudadana."
];

// -----------------------------------------------------------------------------
// CICLO 5: GRADOS 10° A 11° - CIENCIAS SOCIALES
// -----------------------------------------------------------------------------
$sociales_10_11 = [
    "Explico el origen del régimen bipartidista en Colombia.",
    "Analizo el periodo conocido como 'la Violencia' y establezco relaciones con las formas actuales de violencia.",
    "Identifico las causas, características y consecuencias del Frente Nacional.",
    "Analizo desde el punto de vista político, económico, social y cultural algunos de los hechos históricos mundiales sobresalientes del siglo XX.",
    "Identifico y analizo las diferentes formas del orden mundial en el siglo XX (Guerra Fría, globalización).",
    "Analizo el impacto de estos modelos en la región.",
    "Explico y evalúo el impacto del desarrollo industrial y tecnológico sobre el medio ambiente y el ser humano.",
    "Analizo el paso de un sistema democrático representativo a un sistema democrático participativo en Colombia.",
    "Identifico las organizaciones internacionales que surgieron a lo largo del siglo XX y evalúo el impacto de su gestión."
];

// Generación del array por grados independientes (1 a 11)
for ($i = 1; $i <= 11; $i++) {
    $grupo = [];

    if ($i <= 3) {
        $grupo = $sociales_1_3;
    } elseif ($i <= 5) {
        $grupo = $sociales_4_5;
    } elseif ($i <= 7) {
        $grupo = $sociales_6_7;
    } elseif ($i <= 9) {
        $grupo = $sociales_8_9;
    } else {
        $grupo = $sociales_10_11;
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
    "source" => "Estándares Básicos de Competencias en Ciencias Sociales (MEN)",
    "nota" => "El documento provisto contiene EBC, mapeados aquí como DBAs para compatibilidad del sistema.",
    "data" => $datos
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>