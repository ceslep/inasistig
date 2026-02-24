<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Estructura base solicitada
$datos = [];

// -----------------------------------------------------------------------------
// CICLO 1: GRADOS 1° A 3°
// Fuente: Estándares Básicos de Competencias (MEN 2006) - Paginas 80-81 del documento
// -----------------------------------------------------------------------------
$estandares_1_3 = [
    // Pensamiento Numérico
    "Reconozco significados del número en diferentes contextos (medición, conteo, comparación, codificación, localización entre otros).",
    "Describo, comparo y cuantifico situaciones con números, en diferentes contextos y con diversas representaciones.",
    "Describo situaciones que requieren el uso de medidas relativas.",
    "Describo situaciones de medición utilizando fracciones comunes.",
    "Uso representaciones –principalmente concretas y pictóricas– para explicar el valor de posición en el sistema de numeración decimal.",
    "Uso representaciones –principalmente concretas y pictóricas– para realizar equivalencias de un número en las diferentes unidades del sistema decimal.",
    "Reconozco propiedades de los números (ser par, ser impar, etc.) y relaciones entre ellos (ser mayor que, ser menor que, ser múltiplo de, ser divisible por, etc.) en diferentes contextos.",
    "Resuelvo y formulo problemas en situaciones aditivas de composición y de transformación.",
    "Resuelvo y formulo problemas en situaciones de variación proporcional.",
    "Uso diversas estrategias de cálculo (especialmente cálculo mental) y de estimación para resolver problemas en situaciones aditivas y multiplicativas.",
    "Identifico, si a la luz de los datos de un problema, los resultados obtenidos son o no razonables.",
    "Identifico regularidades y propiedades de los números utilizando diferentes instrumentos de cálculo (calculadoras, ábacos, bloques multibase, etc.).",
    // Pensamiento Espacial
    "Diferencio atributos y propiedades de objetos tridimensionales.",
    "Dibujo y describo cuerpos o figuras tridimensionales en distintas posiciones y tamaños.",
    "Reconozco nociones de horizontalidad, verticalidad, paralelismo y perpendicularidad en distintos contextos y su condición relativa con respecto a diferentes sistemas de referencia.",
    "Represento el espacio circundante para establecer relaciones espaciales.",
    "Reconozco y aplico traslaciones y giros sobre una figura.",
    "Reconozco y valoro simetrías en distintos aspectos del arte y el diseño.",
    "Reconozco congruencia y semejanza entre figuras (ampliar, reducir).",
    "Realizo construcciones y diseños utilizando cuerpos y figuras geométricas tridimensionales y dibujos o figuras geométricas bidimensionales.",
    "Desarrollo habilidades para relacionar dirección, distancia y posición en el espacio.",
    // Pensamiento Métrico
    "Reconozco en los objetos propiedades o atributos que se puedan medir (longitud, área, volumen, capacidad, peso y masa) y, en los eventos, su duración.",
    "Comparo y ordeno objetos respecto a atributos medibles.",
    "Realizo y describo procesos de medición con patrones arbitrarios y algunos estandarizados, de acuerdo al contexto.",
    "Analizo y explico sobre la pertinencia de patrones e instrumentos en procesos de medición.",
    "Realizo estimaciones de medidas requeridas en la resolución de problemas relativos particularmente a la vida social, económica y de las ciencias.",
    "Reconozco el uso de las magnitudes y sus unidades de medida en situaciones aditivas y multiplicativas.",
    // Pensamiento Aleatorio
    "Clasifico y organizo datos de acuerdo a cualidades y atributos y los presento en tablas.",
    "Interpreto cualitativamente datos referidos a situaciones del entorno escolar.",
    "Describo situaciones o eventos a partir de un conjunto de datos.",
    "Represento datos relativos a mi entorno usando objetos concretos, pictogramas y diagramas de barras.",
    "Identifico regularidades y tendencias en un conjunto de datos.",
    "Explico –desde mi experiencia– la posibilidad o imposibilidad de ocurrencia de eventos cotidianos.",
    "Predigo si la posibilidad de ocurrencia de un evento es mayor que la de otro.",
    "Resuelvo y formulo preguntas que requieran para su solución coleccionar y analizar datos del entorno próximo.",
    // Pensamiento Variacional
    "Reconozco y describo regularidades y patrones en distintos contextos (numérico, geométrico, musical, entre otros).",
    "Describo cualitativamente situaciones de cambio y variación utilizando el lenguaje natural, dibujos y gráficas.",
    "Reconozco y genero equivalencias entre expresiones numéricas y describo cómo cambian los símbolos aunque el valor siga igual.",
    "Construyo secuencias numéricas y geométricas utilizando propiedades de los números y de las figuras geométricas."
];

// -----------------------------------------------------------------------------
// CICLO 2: GRADOS 4° A 5°
// Fuente: Estándares Básicos de Competencias (MEN 2006) - Paginas 82-83 del documento
// -----------------------------------------------------------------------------
$estandares_4_5 = [
    // Pensamiento Numérico
    "Interpreto las fracciones en diferentes contextos: situaciones de medición, relaciones parte todo, cociente, razones y proporciones.",
    "Identifico y uso medidas relativas en distintos contextos.",
    "Utilizo la notación decimal para expresar fracciones en diferentes contextos y relaciono estas dos notaciones con la de los porcentajes.",
    "Justifico el valor de posición en el sistema de numeración decimal en relación con el conteo recurrente de unidades.",
    "Resuelvo y formulo problemas cuya estrategia de solución requiera de las relaciones y propiedades de los números naturales y sus operaciones.",
    "Resuelvo y formulo problemas en situaciones aditivas de composición, transformación, comparación e igualación.",
    "Resuelvo y formulo problemas en situaciones de proporcionalidad directa, inversa y producto de medidas.",
    "Identifico la potenciación y la radicación en contextos matemáticos y no matemáticos.",
    "Modelo situaciones de dependencia mediante la proporcionalidad directa e inversa.",
    "Uso diversas estrategias de cálculo y de estimación para resolver problemas en situaciones aditivas y multiplicativas.",
    "Identifico, en el contexto de una situación, la necesidad de un cálculo exacto o aproximado y lo razonable de los resultados obtenidos.",
    "Justifico regularidades y propiedades de los números, sus relaciones y operaciones.",
    // Pensamiento Espacial
    "Comparo y clasifico objetos tridimensionales de acuerdo con componentes (caras, lados) y propiedades.",
    "Comparo y clasifico figuras bidimensionales de acuerdo con sus componentes (ángulos, vértices) y características.",
    "Identifico, represento y utilizo ángulos en giros, aberturas, inclinaciones, figuras, puntas y esquinas en situaciones estáticas y dinámicas.",
    "Utilizo sistemas de coordenadas para especificar localizaciones y describir relaciones espaciales.",
    "Identifico y justifico relaciones de congruencia y semejanza entre figuras.",
    "Construyo y descompongo figuras y sólidos a partir de condiciones dadas.",
    "Conjeturo y verifico los resultados de aplicar transformaciones a figuras en el plano para construir diseños.",
    "Construyo objetos tridimensionales a partir de representaciones bidimensionales y puedo realizar el proceso contrario en contextos de arte, diseño y arquitectura.",
    // Pensamiento Métrico
    "Diferencio y ordeno, en objetos y eventos, propiedades o atributos que se puedan medir (longitudes, distancias, áreas de superficies, volúmenes de cuerpos sólidos, volúmenes de líquidos y capacidades de recipientes; pesos y masa de cuerpos sólidos; duración de eventos o procesos; amplitud de ángulos).",
    "Selecciono unidades, tanto convencionales como estandarizadas, apropiadas para diferentes mediciones.",
    "Utilizo y justifico el uso de la estimación para resolver problemas relativos a la vida social, económica y de las ciencias, utilizando rangos de variación.",
    "Utilizo diferentes procedimientos de cálculo para hallar el área de la superficie exterior y el volumen de algunos cuerpos sólidos.",
    "Justifico relaciones de dependencia del área y volumen, respecto a las dimensiones de figuras y sólidos.",
    "Reconozco el uso de algunas magnitudes (longitud, área, volumen, capacidad, peso y masa, duración, rapidez, temperatura) y de algunas de las unidades que se usan para medir cantidades de la magnitud respectiva en situaciones aditivas y multiplicativas.",
    "Describo y argumento relaciones entre el perímetro y el área de figuras diferentes, cuando se fija una de estas medidas.",
    // Pensamiento Aleatorio
    "Represento datos usando tablas y gráficas (pictogramas, gráficas de barras, diagramas de líneas, diagramas circulares).",
    "Comparo diferentes representaciones del mismo conjunto de datos.",
    "Interpreto información presentada en tablas y gráficas (pictogramas, gráficas de barras, diagramas de líneas, diagramas circulares).",
    "Conjeturo y pongo a prueba predicciones acerca de la posibilidad de ocurrencia de eventos.",
    "Describo la manera como parecen distribuirse los distintos datos de un conjunto de ellos y la comparo con la manera como se distribuyen en otros conjuntos de datos.",
    "Uso e interpreto la media (o promedio) y la mediana y comparo lo que indican.",
    "Resuelvo y formulo problemas a partir de un conjunto de datos provenientes de observaciones, consultas o experimentos.",
    // Pensamiento Variacional
    "Describo e interpreto variaciones representadas en gráficos.",
    "Predigo patrones de variación en una secuencia numérica, geométrica o gráfica.",
    "Represento y relaciono patrones numéricos con tablas y reglas verbales.",
    "Analizo y explico relaciones de dependencia entre cantidades que varían en el tiempo con cierta regularidad en situaciones económicas, sociales y de las ciencias naturales.",
    "Construyo igualdades y desigualdades numéricas como representación de relaciones entre distintos datos."
];

// -----------------------------------------------------------------------------
// CICLO 3: GRADOS 6° A 7°
// Fuente: Estándares Básicos de Competencias (MEN 2006) - Paginas 84-85 del documento
// -----------------------------------------------------------------------------
$estandares_6_7 = [
    // Pensamiento Numérico
    "Resuelvo y formulo problemas en contextos de medidas relativas y de variaciones en las medidas.",
    "Utilizo números racionales, en sus distintas expresiones (fracciones, razones, decimales o porcentajes) para resolver problemas en contextos de medida.",
    "Justifico la extensión de la representación polinomial decimal usual de los números naturales a la representación decimal usual de los números racionales, utilizando las propiedades del sistema de numeración decimal.",
    "Reconozco y generalizo propiedades de las relaciones entre números racionales (simétrica, transitiva, etc.) y de las operaciones entre ellos (conmutativa, asociativa, etc.) en diferentes contextos.",
    "Resuelvo y formulo problemas utilizando propiedades básicas de la teoría de números, como las de la igualdad, las de las distintas formas de la desigualdad y las de la adición, sustracción, multiplicación, división y potenciación.",
    "Justifico procedimientos aritméticos utilizando las relaciones y propiedades de las operaciones.",
    "Formulo y resuelvo problemas en situaciones aditivas y multiplicativas, en diferentes contextos y dominios numéricos.",
    "Resuelvo y formulo problemas cuya solución requiere de la potenciación o radicación.",
    "Justifico el uso de representaciones y procedimientos en situaciones de proporcionalidad directa e inversa.",
    "Justifico la pertinencia de un cálculo exacto o aproximado en la solución de un problema y lo razonable o no de las respuestas obtenidas.",
    "Establezco conjeturas sobre propiedades y relaciones de los números, utilizando calculadoras o computadores.",
    "Justifico la elección de métodos e instrumentos de cálculo en la resolución de problemas.",
    "Reconozco argumentos combinatorios como herramienta para interpretación de situaciones diversas de conteo.",
    // Pensamiento Espacial
    "Represento objetos tridimensionales desde diferentes posiciones y vistas.",
    "Identifico y describo figuras y cuerpos generados por cortes rectos y transversales de objetos tridimensionales.",
    "Clasifico polígonos en relación con sus propiedades.",
    "Predigo y comparo los resultados de aplicar transformaciones rígidas (traslaciones, rotaciones, reflexiones) y homotecias (ampliaciones y reducciones) sobre figuras bidimensionales en situaciones matemáticas y en el arte.",
    "Resuelvo y formulo problemas que involucren relaciones y propiedades de semejanza y congruencia usando representaciones visuales.",
    "Resuelvo y formulo problemas usando modelos geométricos.",
    "Identifico características de localización de objetos en sistemas de representación cartesiana y geográfica.",
    // Pensamiento Métrico
    "Utilizo técnicas y herramientas para la construcción de figuras planas y cuerpos con medidas dadas.",
    "Resuelvo y formulo problemas que involucren factores escalares (diseño de maquetas, mapas).",
    "Calculo áreas y volúmenes a través de composición y descomposición de figuras y cuerpos.",
    "Identifico relaciones entre distintas unidades utilizadas para medir cantidades de la misma magnitud.",
    "Resuelvo y formulo problemas que requieren técnicas de estimación.",
    // Pensamiento Aleatorio
    "Comparo e interpreto datos provenientes de diversas fuentes (prensa, revistas, televisión, experimentos, consultas, entrevistas).",
    "Reconozco la relación entre un conjunto de datos y su representación.",
    "Interpreto, produzco y comparo representaciones gráficas adecuadas para presentar diversos tipos de datos (diagramas de barras, diagramas circulares).",
    "Uso medidas de tendencia central (media, mediana, moda) para interpretar comportamiento de un conjunto de datos.",
    "Uso modelos (diagramas de árbol, por ejemplo) para discutir y predecir posibilidad de ocurrencia de un evento.",
    "Conjeturo acerca del resultado de un experimento aleatorio usando proporcionalidad y nociones básicas de probabilidad.",
    "Resuelvo y formulo problemas a partir de un conjunto de datos presentados en tablas, diagramas de barras, diagramas circulares.",
    "Predigo y justifico razonamientos y conclusiones usando información estadística.",
    // Pensamiento Variacional
    "Describo y represento situaciones de variación relacionando diferentes representaciones (diagramas, expresiones verbales generalizadas y tablas).",
    "Reconozco el conjunto de valores de cada una de las cantidades variables ligadas entre sí en situaciones concretas de cambio (variación).",
    "Analizo las propiedades de correlación positiva y negativa entre variables, de variación lineal o de proporcionalidad directa y de proporcionalidad inversa en contextos aritméticos y geométricos.",
    "Utilizo métodos informales (ensayo y error, complementación) en la solución de ecuaciones.",
    "Identifico las características de las diversas gráficas cartesianas (de puntos, continuas, formadas por segmentos, etc.) en relación con la situación que representan."
];

// -----------------------------------------------------------------------------
// CICLO 4: GRADOS 8° A 9°
// Fuente: Estándares Básicos de Competencias (MEN 2006) - Paginas 86-87 del documento
// -----------------------------------------------------------------------------
$estandares_8_9 = [
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
    "Interpreto analítica y críticamente información estadística proveniente de diversas fuentes (prensa, revistas, televisión, experimentos, consultas, entrevistas).",
    "Interpreto y utilizo conceptos de media, mediana y moda y explicito sus diferencias en distribuciones de distinta dispersión y asimetría.",
    "Selecciono y uso algunos métodos estadísticos adecuados al tipo de problema, de información y al nivel de la escala en la que esta se representa (nominal, ordinal, de intervalo o de razón).",
    "Comparo resultados de experimentos aleatorios con los resultados previstos por un modelo matemático probabilístico.",
    "Resuelvo y formulo problemas seleccionando información relevante en conjuntos de datos provenientes de fuentes diversas (prensa, revistas, televisión, experimentos, consultas, entrevistas).",
    "Reconozco tendencias que se presentan en conjuntos de variables relacionadas.",
    "Calculo probabilidad de eventos simples usando métodos diversos (listados, diagramas de árbol, técnicas de conteo).",
    "Uso conceptos básicos de probabilidad (espacio muestral, evento, independencia, etc.).",
    // Pensamiento Variacional
    "Identifico relaciones entre propiedades de las gráficas y propiedades de las ecuaciones algebraicas.",
    "Construyo expresiones algebraicas equivalentes a una expresión algebraica dada.",
    "Uso procesos inductivos y lenguaje algebraico para formular y poner a prueba conjeturas.",
    "Modelo situaciones de variación con funciones polinómicas.",
    "Identifico diferentes métodos para solucionar sistemas de ecuaciones lineales.",
    "Analizo los procesos infinitos que subyacen en las notaciones decimales.",
    "Identifico y utilizo diferentes maneras de definir y medir la pendiente de una curva que representa en el plano cartesiano situaciones de variación.",
    "Identifico la relación entre los cambios en los parámetros de la representación algebraica de una familia de funciones y los cambios en las gráficas que las representan.",
    "Analizo en representaciones gráficas cartesianas los comportamientos de cambio de funciones específicas pertenecientes a familias de funciones polinómicas, racionales, exponenciales y logarítmicas."
];

// -----------------------------------------------------------------------------
// CICLO 5: GRADOS 10° A 11°
// Fuente: Estándares Básicos de Competencias (MEN 2006) - Paginas 88-89 del documento
// -----------------------------------------------------------------------------
$estandares_10_11 = [
    // Pensamiento Numérico
    "Analizo representaciones decimales de los números reales para diferenciar entre racionales e irracionales.",
    "Reconozco la densidad e incompletitud de los números racionales a través de métodos numéricos, geométricos y algebraicos.",
    "Comparo y contrasto las propiedades de los números (naturales, enteros, racionales y reales) y las de sus relaciones y operaciones para construir, manejar y utilizar apropiadamente los distintos sistemas numéricos.",
    "Utilizo argumentos de la teoría de números para justificar relaciones que involucran números naturales.",
    "Establezco relaciones y diferencias entre diferentes notaciones de números reales para decidir sobre su uso en una situación dada.",
    // Pensamiento Espacial
    "Identifico en forma visual, gráfica y algebraica algunas propiedades de las curvas que se observan en los bordes obtenidos por cortes longitudinales, diagonales y transversales en un cilindro y en un cono.",
    "Identifico características de localización de objetos geométricos en sistemas de representación cartesiana y otros (polares, cilíndricos y esféricos) y en particular de las curvas y figuras cónicas.",
    "Resuelvo problemas en los que se usen las propiedades geométricas de figuras cónicas por medio de transformaciones de las representaciones algebraicas de esas figuras.",
    "Uso argumentos geométricos para resolver y formular problemas en contextos matemáticos y en otras ciencias.",
    "Describo y modelo fenómenos periódicos del mundo real usando relaciones y funciones trigonométricas.",
    "Reconozco y describo curvas y o lugares geométricos.",
    // Pensamiento Métrico
    "Diseño estrategias para abordar situaciones de medición que requieran grados de precisión específicos.",
    "Resuelvo y formulo problemas que involucren magnitudes cuyos valores medios se suelen definir indirectamente como razones entre valores de otras magnitudes, como la velocidad media, la aceleración media y la densidad media.",
    "Justifico resultados obtenidos mediante procesos de aproximación sucesiva, rangos de variación y límites en situaciones de medición.",
    // Pensamiento Aleatorio
    "Interpreto y comparo resultados de estudios con información estadística provenientes de medios de comunicación.",
    "Justifico o refuto inferencias basadas en razonamientos estadísticos a partir de resultados de estudios publicados en los medios o diseñados en el ámbito escolar.",
    "Diseño experimentos aleatorios (de las ciencias físicas, naturales o sociales) para estudiar un problema o pregunta.",
    "Describo tendencias que se observan en conjuntos de variables relacionadas.",
    "Interpreto nociones básicas relacionadas con el manejo de información como población, muestra, variable aleatoria, distribución de frecuencias, parámetros y estadígrafos).",
    "Uso comprensivamente algunas medidas de centralización, localización, dispersión y correlación (percentiles, cuartiles, centralidad, distancia, rango, varianza, covarianza y normalidad).",
    "Interpreto conceptos de probabilidad condicional e independencia de eventos.",
    "Resuelvo y planteo problemas usando conceptos básicos de conteo y probabilidad (combinaciones, permutaciones, espacio muestral, muestreo aleatorio, muestreo con remplazo).",
    "Propongo inferencias a partir del estudio de muestras probabilísticas.",
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
        "asignatura" => "matematicas",
        "dbas" => $grupo
    ];
}

// Salida JSON
echo json_encode([
    "status" => "success",
    "source" => "Estándares Básicos de Competencias (MEN 2006)",
    "nota" => "El documento provisto contiene EBC, mapeados aquí como DBAs para compatibilidad del sistema.",
    "data" => $datos
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>