<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$datos = [];

// -----------------------------------------------------------------------------
// CICLO 1: GRADOS 1° A 3° - CIENCIAS NATURALES
// -----------------------------------------------------------------------------
$naturales_1_3 = [
    "Describo características de seres vivos y objetos inertes, establezco semejanzas y diferencias entre ellos y los clasifico.",
    "Propongo y verifico necesidades de los seres vivos.",
    "Observo y describo cambios en mi desarrollo y en el de otros seres vivos.",
    "Identifico y describo la flora, la fauna, el agua y el suelo de mi entorno.",
    "Identifico diferentes estados físicos de la materia (el agua, por ejemplo) y verifico causas para cambios de estado.",
    "Identifico y comparo fuentes de luz, calor y sonido y su efecto sobre diferentes seres vivos.",
    "Identifico tipos de movimiento en seres vivos y objetos, y las fuerzas que los producen.",
    "Construyo circuitos eléctricos simples con pilas.",
    "Diferencio objetos naturales de objetos creados por el ser humano.",
    "Identifico necesidades de cuidado de mi cuerpo y el de otras personas."
];

// -----------------------------------------------------------------------------
// CICLO 2: GRADOS 4° A 5° - CIENCIAS NATURALES
// -----------------------------------------------------------------------------
$naturales_4_5 = [
    "Explico la importancia de la célula como unidad básica de los seres vivos.",
    "Identifico los niveles de organización celular de los seres vivos.",
    "Clasifico seres vivos en diversos grupos taxonómicos (plantas, animales, microorganismos).",
    "Analizo el ecosistema que me rodea y lo comparo con otros.",
    "Identifico adaptaciones de los seres vivos, teniendo en cuenta las características de los ecosistemas.",
    "Describo y verifico el efecto de la transferencia de energía térmica en los cambios de estado de algunas sustancias.",
    "Propongo y verifico diferentes métodos de separación de mezclas.",
    "Describo los principales elementos del sistema solar y establezco relaciones de tamaño, movimiento y posición.",
    "Identifico máquinas simples en objetos cotidianos y describo su utilidad.",
    "Establezco relaciones entre el efecto invernadero, la lluvia ácida y el debilitamiento de la capa de ozono con la contaminación atmosférica."
];

// -----------------------------------------------------------------------------
// CICLO 3: GRADOS 6° A 7° - CIENCIAS NATURALES
// -----------------------------------------------------------------------------
$naturales_6_7 = [
    "Explico la estructura de la célula y las funciones básicas de sus componentes.",
    "Clasifico organismos en grupos taxonómicos de acuerdo con las características de sus células.",
    "Explico las funciones de los seres vivos a partir de las relaciones entre diferentes sistemas de órganos.",
    "Caracterizo ecosistemas y analizo el equilibrio dinámico entre sus poblaciones.",
    "Justifico la importancia del agua en el sostenimiento de la vida.",
    "Clasifico y verifico las propiedades de la materia.",
    "Explico el desarrollo de modelos de organización de los elementos químicos.",
    "Explico y utilizo la tabla periódica como herramienta para predecir procesos químicos.",
    "Relaciono energía y movimiento.",
    "Identifico recursos renovables y no renovables y los peligros a los que están expuestos."
];

// -----------------------------------------------------------------------------
// CICLO 4: GRADOS 8° A 9° - CIENCIAS NATURALES
// -----------------------------------------------------------------------------
$naturales_8_9 = [
    "Reconozco la importancia del modelo de la doble hélice para la explicación del almacenamiento y transmisión del material hereditario.",
    "Establezco relaciones entre los genes, las proteínas y las funciones celulares.",
    "Justifico la importancia de la reproducción sexual en el mantenimiento de la variabilidad.",
    "Comparo diferentes teorías sobre el origen de las especies.",
    "Verifico las diferencias entre cambios químicos y mezclas.",
    "Establezco relaciones cuantitativas entre los componentes de una solución.",
    "Comparo los modelos que sustentan la definición ácido-base.",
    "Establezco relaciones entre frecuencia, amplitud, velocidad de propagación y longitud de onda en diversos tipos de ondas mecánicas.",
    "Reconozco y diferencio modelos para explicar la naturaleza y el comportamiento de la luz.",
    "Argumento las ventajas y desventajas de la manipulación genética."
];

// -----------------------------------------------------------------------------
// CICLO 5: GRADOS 10° A 11° - CIENCIAS NATURALES
// -----------------------------------------------------------------------------
$naturales_10_11 = [
    "Explico la relación entre el ADN, el ambiente y la diversidad de los seres vivos.",
    "Establezco relaciones entre mutación, selección natural y herencia.",
    "Explico las relaciones entre materia y energía en las cadenas alimentarias.",
    "Argumento la importancia de la fotosíntesis como un proceso de conversión de energía necesaria para organismos aerobios.",
    "Explico la estructura de los átomos a partir de diferentes teorías.",
    "Explico la obtención de energía nuclear a partir de la alteración de la estructura del átomo.",
    "Uso la tabla periódica para determinar propiedades físicas y químicas de los elementos.",
    "Modelo matemáticamente el movimiento de objetos cotidianos a partir de las fuerzas que actúan sobre ellos.",
    "Establezco relaciones entre campo gravitacional y electrostático y entre campo eléctrico y magnético.",
    "Analizo el potencial de los recursos naturales en la obtención de energía para diferentes usos."
];

// Generación del array por grados independientes (1 a 11)
for ($i = 1; $i <= 11; $i++) {
    $grupo = [];

    if ($i <= 3) {
        $grupo = $naturales_1_3;
    } elseif ($i <= 5) {
        $grupo = $naturales_4_5;
    } elseif ($i <= 7) {
        $grupo = $naturales_6_7;
    } elseif ($i <= 9) {
        $grupo = $naturales_8_9;
    } else {
        $grupo = $naturales_10_11;
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
    "source" => "Estándares Básicos de Competencias en Ciencias Naturales (MEN)",
    "nota" => "El documento provisto contiene EBC, mapeados aquí como DBAs para compatibilidad del sistema.",
    "data" => $datos
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>