<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Estructura base solicitada
$datos = [];

// -----------------------------------------------------------------------------
// CICLO 1: GRADOS 1° A 3° - MATEMÁTICAS (DBA)
// Pensamientos: Numérico, Espacial, Métrico, Aleatorio, Variacional
// -----------------------------------------------------------------------------
$dba_matematicas_1_3 = [
    // Pensamiento Numérico
    "Reconozco significados del número en diferentes contextos (medición, conteo, comparación, codificación, localización entre otros).",
    "Describo, comparo y cuantifico situaciones con números, en diferentes contextos y con diversas representaciones.",
    "Uso representaciones concretas y pictóricas para explicar el valor de posición en el sistema de numeración decimal.",
    "Reconozco propiedades de los números (ser par, ser impar, etc.) y relaciones entre ellos en diferentes contextos.",
    "Resuelvo y formulo problemas en situaciones aditivas de composición y de transformación.",

    // Pensamiento Espacial
    "Diferencio atributos y propiedades de objetos tridimensionales.",
    "Reconozco nociones de horizontalidad, verticalidad, paralelismo y perpendicularidad en distintos contextos.",
    "Represento el espacio circundante para establecer relaciones espaciales.",
    "Reconozco y aplico traslaciones y giros sobre una figura.",
    "Reconozco congruencia y semejanza entre figuras (ampliar, reducir).",

    // Pensamiento Métrico
    "Reconozco en los objetos propiedades o atributos que se puedan medir (longitud, área, volumen, capacidad, peso y masa).",
    "Comparo y ordeno objetos respecto a atributos medibles.",
    "Realizo y describo procesos de medición con patrones arbitrarios y algunos estandarizados.",
    "Realizo estimaciones de medidas requeridas en la resolución de problemas.",

    // Pensamiento Aleatorio
    "Clasifico y organizo datos de acuerdo a cualidades y atributos y los presento en tablas.",
    "Interpreto cualitativamente datos referidos a situaciones del entorno escolar.",
    "Represento datos relativos a mi entorno usando objetos concretos, pictogramas y diagramas de barras.",
    "Explico desde mi experiencia la posibilidad o imposibilidad de ocurrencia de eventos cotidianos.",

    // Pensamiento Variacional
    "Reconozco y describo regularidades y patrones en distintos contextos (numérico, geométrico, musical, entre otros).",
    "Describo cualitativamente situaciones de cambio y variación utilizando el lenguaje natural, dibujos y gráficas.",
    "Construyo secuencias numéricas y geométricas utilizando propiedades de los números y de las figuras geométricas."
];

// -----------------------------------------------------------------------------
// CICLO 2: GRADOS 4° A 5° - MATEMÁTICAS (DBA)
// -----------------------------------------------------------------------------
$dba_matematicas_4_5 = [
    // Pensamiento Numérico
    "Interpreto las fracciones en diferentes contextos: situaciones de medición, relaciones parte todo, cociente, razones y proporciones.",
    "Utilizo la notación decimal para expresar fracciones en diferentes contextos y relaciono estas dos notaciones con la de los porcentajes.",
    "Justifico el valor de posición en el sistema de numeración decimal en relación con el conteo recurrente de unidades.",
    "Resuelvo y formulo problemas cuya estrategia de solución requiera de las relaciones y propiedades de los números naturales y sus operaciones.",
    "Resuelvo y formulo problemas en situaciones de proporcionalidad directa, inversa y producto de medidas.",

    // Pensamiento Espacial
    "Comparo y clasifico objetos tridimensionales de acuerdo con componentes (caras, lados) y propiedades.",
    "Comparo y clasifico figuras bidimensionales de acuerdo con sus componentes (ángulos, vértices) y características.",
    "Identifico, represento y utilizo ángulos en giros, aberturas, inclinaciones, figuras, puntas y esquinas.",
    "Utilizo sistemas de coordenadas para especificar localizaciones y describir relaciones espaciales.",
    "Identifico y justifico relaciones de congruencia y semejanza entre figuras.",

    // Pensamiento Métrico
    "Diferencio y ordeno, en objetos y eventos, propiedades o atributos que se puedan medir (longitudes, distancias, áreas, volúmenes, pesos, masa, duración, amplitud de ángulos).",
    "Selecciono unidades, tanto convencionales como estandarizadas, apropiadas para diferentes mediciones.",
    "Utilizo y justifico el uso de la estimación para resolver problemas utilizando rangos de variación.",
    "Utilizo diferentes procedimientos de cálculo para hallar el área de la superficie exterior y el volumen de algunos cuerpos sólidos.",

    // Pensamiento Aleatorio
    "Represento datos usando tablas y gráficas (pictogramas, gráficas de barras, diagramas de líneas, diagramas circulares).",
    "Comparo diferentes representaciones del mismo conjunto de datos.",
    "Interpreto información presentada en tablas y gráficas.",
    "Conjeturo y pongo a prueba predicciones acerca de la posibilidad de ocurrencia de eventos.",
    "Uso e interpreto la media (o promedio) y la mediana y comparo lo que indican.",

    // Pensamiento Variacional
    "Describo e interpreto variaciones representadas en gráficos.",
    "Predigo patrones de variación en una secuencia numérica, geométrica o gráfica.",
    "Represento y relaciono patrones numéricos con tablas y reglas verbales.",
    "Analizo y explico relaciones de dependencia entre cantidades que varían en el tiempo con cierta regularidad.",
    "Construyo igualdades y desigualdades numéricas como representación de relaciones entre distintos datos."
];

// -----------------------------------------------------------------------------
// CICLO 3: GRADOS 6° A 7° - MATEMÁTICAS (DBA)
// -----------------------------------------------------------------------------
$dba_matematicas_6_7 = [
    // Pensamiento Numérico
    "Utilizo números racionales, en sus distintas expresiones (fracciones, razones, decimales o porcentajes) para resolver problemas en contextos de medida.",
    "Justifico la extensión de la representación polinomial decimal usual de los números naturales a la representación decimal usual de los números racionales.",
    "Reconozco y generalizo propiedades de las relaciones entre números racionales y de las operaciones entre ellos en diferentes contextos.",
    "Resuelvo y formulo problemas utilizando propiedades básicas de la teoría de números.",
    "Justifico procedimientos aritméticos utilizando las relaciones y propiedades de las operaciones.",

    // Pensamiento Espacial
    "Represento objetos tridimensionales desde diferentes posiciones y vistas.",
    "Identifico y describo figuras y cuerpos generados por cortes rectos y transversales de objetos tridimensionales.",
    "Clasifico polígonos en relación con sus propiedades.",
    "Predigo y comparo los resultados de aplicar transformaciones rígidas (traslaciones, rotaciones, reflexiones) y homotecias sobre figuras bidimensionales.",
    "Resuelvo y formulo problemas que involucren relaciones y propiedades de semejanza y congruencia usando representaciones visuales.",

    // Pensamiento Métrico
    "Utilizo técnicas y herramientas para la construcción de figuras planas y cuerpos con medidas dadas.",
    "Resuelvo y formulo problemas que involucren factores escalares (diseño de maquetas, mapas).",
    "Calculo áreas y volúmenes a través de composición y descomposición de figuras y cuerpos.",
    "Identifico relaciones entre distintas unidades utilizadas para medir cantidades de la misma magnitud.",

    // Pensamiento Aleatorio
    "Comparo e interpreto datos provenientes de diversas fuentes (prensa, revistas, televisión, experimentos, consultas, entrevistas).",
    "Interpreto, produzco y comparo representaciones gráficas adecuadas para presentar diversos tipos de datos.",
    "Uso medidas de tendencia central (media, mediana, moda) para interpretar comportamiento de un conjunto de datos.",
    "Uso modelos (diagramas de árbol, por ejemplo) para discutir y predecir posibilidad de ocurrencia de un evento.",
    "Conjeturo acerca del resultado de un experimento aleatorio usando proporcionalidad y nociones básicas de probabilidad.",

    // Pensamiento Variacional
    "Describo y represento situaciones de variación relacionando diferentes representaciones (diagramas, expresiones verbales generalizadas y tablas).",
    "Reconozco el conjunto de valores de cada una de las cantidades variables ligadas entre sí en situaciones concretas de cambio.",
    "Analizo las propiedades de correlación positiva y negativa entre variables, de variación lineal o de proporcionalidad directa y de proporcionalidad inversa.",
    "Utilizo métodos informales (ensayo y error, complementación) en la solución de ecuaciones.",
    "Identifico las características de las diversas gráficas cartesianas en relación con la situación que representan."
];

// -----------------------------------------------------------------------------
// CICLO 4: GRADOS 8° A 9° - MATEMÁTICAS (DBA)
// -----------------------------------------------------------------------------
$dba_matematicas_8_9 = [
    // Pensamiento Numérico
    "Utilizo números reales en sus diferentes representaciones y en diversos contextos.",
    "Resuelvo problemas y simplifico cálculos usando propiedades y relaciones de los números reales y de las relaciones y operaciones entre ellos.",
    "Utilizo la notación científica para representar medidas de cantidades de diferentes magnitudes.",
    "Identifico y utilizo la potenciación, la radicación y la logaritmación para representar situaciones matemáticas y no matemáticas y para resolver problemas.",

    // Pensamiento Espacial
    "Conjeturo y verifico propiedades de congruencias y semejanzas entre figuras bidimensionales y entre objetos tridimensionales en la solución de problemas.",
    "Reconozco y contrasto propiedades y relaciones geométricas utilizadas en demostración de teoremas básicos (Pitágoras y Tales).",
    "Aplico y justifico criterios de congruencias y semejanza entre triángulos en la resolución y formulación de problemas.",
    "Uso representaciones geométricas para resolver y formular problemas en las matemáticas y en otras disciplinas.",

    // Pensamiento Métrico
    "Generalizo procedimientos de cálculo válidos para encontrar el área de regiones planas y el volumen de sólidos.",
    "Selecciono y uso técnicas e instrumentos para medir longitudes, áreas de superficies, volúmenes y ángulos con niveles de precisión apropiados.",
    "Justifico la pertinencia de utilizar unidades de medida estandarizadas en situaciones tomadas de distintas ciencias.",

    // Pensamiento Aleatorio
    "Reconozco cómo diferentes maneras de presentación de información pueden originar distintas interpretaciones.",
    "Interpreto analítica y críticamente información estadística proveniente de diversas fuentes.",
    "Interpreto y utilizo conceptos de media, mediana y moda y explicito sus diferencias en distribuciones de distinta dispersión y asimetría.",
    "Comparo resultados de experimentos aleatorios con los resultados previstos por un modelo matemático probabilístico.",
    "Calculo probabilidad de eventos simples usando métodos diversos (listados, diagramas de árbol, técnicas de conteo).",

    // Pensamiento Variacional
    "Identifico relaciones entre propiedades de las gráficas y propiedades de las ecuaciones algebraicas.",
    "Construyo expresiones algebraicas equivalentes a una expresión algebraica dada.",
    "Uso procesos inductivos y lenguaje algebraico para formular y poner a prueba conjeturas.",
    "Modelo situaciones de variación con funciones polinómicas.",
    "Identifico diferentes métodos para solucionar sistemas de ecuaciones lineales.",
    "Analizo los procesos infinitos que subyacen en las notaciones decimales.",
    "Identifico y utilizo diferentes maneras de definir y medir la pendiente de una curva que representa en el plano cartesiano situaciones de variación."
];

// -----------------------------------------------------------------------------
// CICLO 5: GRADOS 10° A 11° - MATEMÁTICAS (DBA)
// -----------------------------------------------------------------------------
$dba_matematicas_10_11 = [
    // Pensamiento Numérico
    "Analizo representaciones decimales de los números reales para diferenciar entre racionales e irracionales.",
    "Reconozco la densidad e incompletitud de los números racionales a través de métodos numéricos, geométricos y algebraicos.",
    "Comparo y contrasto las propiedades de los números (naturales, enteros, racionales y reales) y las de sus relaciones y operaciones para construir, manejar y utilizar apropiadamente los distintos sistemas numéricos.",
    "Utilizo argumentos de la teoría de números para justificar relaciones que involucran números naturales.",

    // Pensamiento Espacial
    "Identifico en forma visual, gráfica y algebraica algunas propiedades de las curvas que se observan en los bordes obtenidos por cortes longitudinales, diagonales y transversales en un cilindro y en un cono.",
    "Identifico características de localización de objetos geométricos en sistemas de representación cartesiana y otros (polares, cilíndricos y esféricos) y en particular de las curvas y figuras cónicas.",
    "Resuelvo problemas en los que se usen las propiedades geométricas de figuras cónicas por medio de transformaciones de las representaciones algebraicas de esas figuras.",
    "Uso argumentos geométricos para resolver y formular problemas en contextos matemáticos y en otras ciencias.",
    "Describo y modelo fenómenos periódicos del mundo real usando relaciones y funciones trigonométricas.",

    // Pensamiento Métrico
    "Diseño estrategias para abordar situaciones de medición que requieran grados de precisión específicos.",
    "Resuelvo y formulo problemas que involucren magnitudes cuyos valores medios se suelen definir indirectamente como razones entre valores de otras magnitudes, como la velocidad media, la aceleración media y la densidad media.",
    "Justifico resultados obtenidos mediante procesos de aproximación sucesiva, rangos de variación y límites en situaciones de medición.",

    // Pensamiento Aleatorio
    "Interpreto y comparo resultados de estudios con información estadística provenientes de medios de comunicación.",
    "Justifico o refuto inferencias basadas en razonamientos estadísticos a partir de resultados de estudios publicados en los medios o diseñados en el ámbito escolar.",
    "Diseño experimentos aleatorios (de las ciencias físicas, naturales o sociales) para estudiar un problema o pregunta.",
    "Interpreto nociones básicas relacionadas con el manejo de información como población, muestra, variable aleatoria, distribución de frecuencias, parámetros y estadígrafos.",
    "Uso comprensivamente algunas medidas de centralización, localización, dispersión y correlación (percentiles, cuartiles, centralidad, distancia, rango, varianza, covarianza y normalidad).",
    "Interpreto conceptos de probabilidad condicional e independencia de eventos.",

    // Pensamiento Variacional
    "Utilizo las técnicas de aproximación en procesos infinitos numéricos.",
    "Interpreto la noción de derivada como razón de cambio y como valor de la pendiente de la tangente a una curva y desarrollo métodos para hallar las derivadas de algunas funciones básicas en contextos matemáticos y no matemáticos.",
    "Analizo las relaciones y propiedades entre las expresiones algebraicas y las gráficas de funciones polinómicas y racionales y de sus derivadas.",
    "Modelo situaciones de variación periódica con funciones trigonométricas e interpreto y utilizo sus derivadas."
];

// Generación del array por grados independientes (1 a 11)
for ($i = 1; $i <= 11; $i++) {
    $grupo = [];

    if ($i <= 3) {
        $grupo = $dba_matematicas_1_3;
    } elseif ($i <= 5) {
        $grupo = $dba_matematicas_4_5;
    } elseif ($i <= 7) {
        $grupo = $dba_matematicas_6_7;
    } elseif ($i <= 9) {
        $grupo = $dba_matematicas_8_9;
    } else {
        $grupo = $dba_matematicas_10_11;
    }

    $datos[] = [
        "grado" => $i,
        "asignatura" => "matematicas",
        "dbas" => $grupo
    ];
}

// Salida JSON
echo json_encode([
    "status" => "success",
    "source" => "Derechos Básicos de Aprendizaje (DBA) - Matemáticas - Ministerio de Educación Nacional de Colombia",
    "nota" => "DBA organizados por cinco tipos de pensamiento matemático: Numérico, Espacial, Métrico, Aleatorio y Variacional. Estructura compatible con sistema de planeación inasistig.",
    "tipos_pensamiento" => [
        "numerico" => "Comprensión del uso y significados de los números, operaciones y relaciones entre ellos en diversos contextos.",
        "espacial" => "Construcción y manipulación de representaciones mentales de objetos del espacio, sus relaciones y transformaciones.",
        "metrico" => "Comprensión general sobre magnitudes, cantidades, su medición y uso flexible de sistemas métricos.",
        "aleatorio" => "Toma de decisiones en situaciones de incertidumbre, azar y riesgo, apoyado en probabilidad y estadística.",
        "variacional" => "Reconocimiento, descripción, modelación y representación de la variación y el cambio en diferentes contextos."
    ],
    "procesos_generales" => [
        "formulacion_resolucion_problemas" => "Plantear, transformar y resolver problemas a partir de situaciones cotidianas, científicas y matemáticas.",
        "modelacion" => "Construir modelos que representen la realidad para hacerla más comprensible y predecir comportamientos.",
        "comunicacion" => "Expresar y comunicar ideas matemáticas utilizando diferentes lenguajes y registros de representación.",
        "razonamiento" => "Desarrollar argumentos, justificar conjeturas, probar y refutar utilizando el pensamiento lógico.",
        "procedimientos_algoritmos" => "Dominar procedimientos y algoritmos matemáticos y conocer cómo, cuándo y por qué usarlos de manera flexible."
    ],
    "data" => $datos
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>