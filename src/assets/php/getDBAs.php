<?php
/**
 * API Completa de Derechos Básicos de Aprendizaje (DBA) - Colombia
 * Basado en: Resolución 0256 de 2016 - Ministerio de Educación Nacional
 * Versión: 2.0 - Completa y Ampliada
 * 
 * Uso: POST /api/getDBAs.php con payload {area: "matematicas", grado: "quinto"}
 */

include_once "cors.php";

// Función para normalizar strings (quitar acentos, espacios extra, etc.)
function clean_string($str)
{
    if (!$str)
        return null;
    $str = strtolower(trim($str));
    $search = ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'];
    $replace = ['a', 'e', 'i', 'o', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'n'];
    $str = str_replace($search, $replace, $str);
    return preg_replace('/[^a-z0-9_ ]/', '', $str);
}

// Obtener datos del POST
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$postArea = $input['area'] ?? null;
$postGrado = $input['grado'] ?? null;

// Obtener parámetros
$rawArea = isset($_GET['area']) ? $_GET['area'] : ($postArea ?: null);
$rawGrado = isset($_GET['grado']) ? $_GET['grado'] : ($postGrado ?: null);

$area = clean_string($rawArea);
$grado = clean_string($rawGrado);

// Mapeo de áreas/materias
$areaMap = [
    'matematicas' => 'matematicas',
    'matematica' => 'matematicas',
    'math' => 'matematicas',
    'lenguaje' => 'lenguaje',
    'lengua castellana' => 'lenguaje',
    'castellano' => 'lenguaje',
    'espanol' => 'lenguaje',
    'lengua' => 'lenguaje',
    'ciencias naturales' => 'ciencias_naturales',
    'naturales' => 'ciencias_naturales',
    'ciencias' => 'ciencias_naturales',
    'ciencias_naturales' => 'ciencias_naturales',
    'ciencias sociales' => 'ciencias_sociales',
    'sociales' => 'ciencias_sociales',
    'historia' => 'ciencias_sociales',
    'ciencias_sociales' => 'ciencias_sociales',
    'ingles' => 'ingles',
    'english' => 'ingles',
    'educacion fisica' => 'educacion_fisica',
    'fisica' => 'educacion_fisica',
    'deporte' => 'educacion_fisica',
    'educacion artistica' => 'educacion_artistica',
    'artistica' => 'educacion_artistica',
    'etica' => 'etica_valores',
    'etica y valores' => 'etica_valores',
    'etica_valores' => 'etica_valores',
    'tecnologia' => 'tecnologia_informatica',
    'informatica' => 'tecnologia_informatica',
    'sistemas' => 'tecnologia_informatica',
    'tecnologia e informatica' => 'tecnologia_informatica',
    'tecnologia_informatica' => 'tecnologia_informatica',
    'religion' => 'educacion_religiosa',
    'educacion religiosa' => 'educacion_religiosa',
    'ambiental' => 'educacion_ambiental',
    'sexual' => 'educacion_sexual',
    'economia' => 'educacion_economica',
    'paz' => 'catedra_paz',
    'catedra_paz' => 'catedra_paz',
    'ciudadana' => 'formacion_ciudadana',
    'formacion_ciudadana' => 'formacion_ciudadana',
    'afro' => 'catedra_afrocolombiana',
    'catedra_afrocolombiana' => 'catedra_afrocolombiana',
    'preescolar' => 'preescolar',
];
$area = isset($areaMap[$area]) ? $areaMap[$area] : $area;

// Mapeo de grados
$gradoMap = [
    'preescolar' => 'preescolar',
    'transicion' => 'preescolar',
    'cero' => 'preescolar',
    '0' => 'preescolar',
    'primero' => 'primero',
    '1' => 'primero',
    'uno' => 'primero',
    'segundo' => 'segundo',
    '2' => 'segundo',
    'dos' => 'segundo',
    'tercero' => 'tercero',
    '3' => 'tercero',
    'tres' => 'tercero',
    'cuarto' => 'cuarto',
    '4' => 'cuarto',
    'cuatrc' => 'cuarto',
    'quinto' => 'quinto',
    '5' => 'quinto',
    'cinco' => 'quinto',
    'sexto' => 'sexto',
    '6' => 'sexto',
    'seis' => 'sexto',
    'septimo' => 'septimo',
    '7' => 'septimo',
    'siete' => 'septimo',
    'octavo' => 'octavo',
    '8' => 'octavo',
    'ocho' => 'octavo',
    'noveno' => 'noveno',
    '9' => 'noveno',
    'nueve' => 'noveno',
    'decimo' => 'decimo',
    '10' => 'decimo',
    'diez' => 'decimo',
    'once' => 'once',
    '11' => 'once',
];
$grado = isset($gradoMap[$grado]) ? $gradoMap[$grado] : $grado;



$dbaData = [
    "metadata" => [
        "version" => "2.0",
        "normativa" => "Resolución 0256 de 2016 - MEN",
        "documento_referencia" => "Derechos Básicos de Aprendizaje - Versión 3",
        "pais" => "Colombia",
        "ultima_actualizacion" => date("Y-m-d"),
        "total_areas" => 16,
        "total_dbas" => 420
    ],
    "areas" => [
        "matematicas" => [
            "nombre" => "Matemáticas",
            "categoria" => "Obligatoria y Fundamental",
            "dbas" => [
                ["id" => "MAT-1-01", "grado" => "primero", "descripcion" => "Comprende que los números se utilizan para contar, ordenar y medir cantidades de objetos.", "evidencia" => "Identifica y usa números naturales hasta el 100 en situaciones cotidianas.", "dimension" => "Numérico-Variacional"],
                ["id" => "MAT-1-02", "grado" => "primero", "descripcion" => "Reconoce patrones y regularidades en secuencias numéricas y geométricas simples.", "evidencia" => "Completa secuencias y identifica patrones de repetición.", "dimension" => "Numérico-Variacional"],
                ["id" => "MAT-1-03", "grado" => "primero", "descripcion" => "Identifica características de objetos bidimensionales y tridimensionales.", "evidencia" => "Clasifica figuras según forma, tamaño y color.", "dimension" => "Geométrico-Métrico"],
                ["id" => "MAT-2-01", "grado" => "segundo", "descripcion" => "Resuelve problemas que involucran adición y sustracción de números naturales hasta 1000.", "evidencia" => "Aplica algoritmos de suma y resta en contextos cotidianos.", "dimension" => "Numérico-Variacional"],
                ["id" => "MAT-2-02", "grado" => "segundo", "descripcion" => "Comprende el sistema de numeración decimal y el valor posicional.", "evidencia" => "Descompone números en centenas, decenas y unidades.", "dimension" => "Numérico-Variacional"],
                ["id" => "MAT-2-03", "grado" => "segundo", "descripcion" => "Mide longitudes, capacidades y pesos usando unidades no convencionales y convencionales.", "evidencia" => "Compara y ordena objetos según su medida.", "dimension" => "Geométrico-Métrico"],
                ["id" => "MAT-3-01", "grado" => "tercero", "descripcion" => "Resuelve problemas de multiplicación y división con números naturales hasta 100.", "evidencia" => "Usa las tablas de multiplicar del 1 al 10.", "dimension" => "Numérico-Variacional"],
                ["id" => "MAT-3-02", "grado" => "tercero", "descripcion" => "Identifica fracciones como partes de un todo en contextos cotidianos.", "evidencia" => "Representa fracciones básicas (1/2, 1/3, 1/4) gráficamente.", "dimension" => "Numérico-Variacional"],
                ["id" => "MAT-3-03", "grado" => "tercero", "descripcion" => "Calcula perímetros y áreas de figuras planas simples.", "evidencia" => "Mide y calcula perímetro de cuadrados y rectángulos.", "dimension" => "Geométrico-Métrico"],
                ["id" => "MAT-4-01", "grado" => "cuarto", "descripcion" => "Resuelve problemas aditivos y multiplicativos con números naturales hasta 10.000.", "evidencia" => "Aplica las cuatro operaciones básicas en contextos variados.", "dimension" => "Numérico-Variacional"],
                ["id" => "MAT-4-02", "grado" => "cuarto", "descripcion" => "Comprende y usa fracciones equivalentes en situaciones de medida y reparto.", "evidencia" => "Compara y ordena fracciones con igual y diferente denominador.", "dimension" => "Numérico-Variacional"],
                ["id" => "MAT-4-03", "grado" => "cuarto", "descripcion" => "Identifica y clasifica ángulos y triángulos según sus características.", "evidencia" => "Dibuja y mide ángulos usando transportador.", "dimension" => "Geométrico-Métrico"],
                ["id" => "MAT-5-01", "grado" => "quinto", "descripcion" => "Resuelve problemas que involucran operaciones con números decimales y fracciones.", "evidencia" => "Aplica operaciones básicas con decimales en contextos de medida y dinero.", "dimension" => "Numérico-Variacional"],
                ["id" => "MAT-5-02", "grado" => "quinto", "descripcion" => "Calcula porcentajes básicos (25%, 50%, 75%, 100%) en situaciones cotidianas.", "evidencia" => "Resuelve problemas de descuento y proporción.", "dimension" => "Numérico-Variacional"],
                ["id" => "MAT-5-03", "grado" => "quinto", "descripcion" => "Interpreta y representa información en tablas y gráficos de barras.", "evidencia" => "Construye y lee gráficos estadísticos simples.", "dimension" => "Aleatorio"],
                ["id" => "MAT-6-01", "grado" => "sexto", "descripcion" => "Comprende y utiliza los números enteros en diferentes contextos (temperatura, pisos, deuda).", "evidencia" => "Ubica números enteros en la recta numérica y realiza operaciones básicas.", "dimension" => "Numérico-Variacional"],
                ["id" => "MAT-6-02", "grado" => "sexto", "descripcion" => "Resuelve problemas que involucran razones y proporciones.", "evidencia" => "Aplica propiedad fundamental de las proporciones.", "dimension" => "Numérico-Variacional"],
                ["id" => "MAT-6-03", "grado" => "sexto", "descripcion" => "Calcula área y volumen de prismas y cilindros.", "evidencia" => "Aplica fórmulas de área y volumen en sólidos geométricos.", "dimension" => "Geométrico-Métrico"],
                ["id" => "MAT-7-01", "grado" => "sexto", "descripcion" => "Resuelve problemas que involucran proporcionalidad directa y porcentajes complejos.", "evidencia" => "Aplica regla de tres simple y compuesta.", "dimension" => "Numérico-Variacional"],
                ["id" => "MAT-7-02", "grado" => "sexto", "descripcion" => "Opera con expresiones algebraicas básicas (suma, resta, multiplicación).", "evidencia" => "Simplifica expresiones algebraicas combinando términos semejantes.", "dimension" => "Numérico-Variacional"],
                ["id" => "MAT-7-03", "grado" => "sexto", "descripcion" => "Construye y analiza patrones numéricos y geométricos.", "evidencia" => "Encuentra la regla general de una sucesión.", "dimension" => "Numérico-Variacional"],
                ["id" => "MAT-8-01", "grado" => "octavo", "descripcion" => "Modela situaciones de variación mediante funciones lineales.", "evidencia" => "Representa gráficamente funciones lineales y analiza su comportamiento.", "dimension" => "Numérico-Variacional"],
                ["id" => "MAT-8-02", "grado" => "octavo", "descripcion" => "Resuelve ecuaciones lineales con una incógnita.", "evidencia" => "Aplica propiedades de igualdad para despejar variables.", "dimension" => "Numérico-Variacional"],
                ["id" => "MAT-8-03", "grado" => "octavo", "descripcion" => "Aplica teoremas de geometría euclidiana (Pitágoras, Tales).", "evidencia" => "Calcula lados de triángulos rectángulos usando Pitágoras.", "dimension" => "Geométrico-Métrico"],
                ["id" => "MAT-9-01", "grado" => "noveno", "descripcion" => "Resuelve problemas que involucran sistemas de ecuaciones lineales 2x2.", "evidencia" => "Aplica métodos de sustitución, igualación y reducción.", "dimension" => "Numérico-Variacional"],
                ["id" => "MAT-9-02", "grado" => "noveno", "descripcion" => "Modela fenómenos de variación mediante funciones cuadráticas.", "evidencia" => "Grafica parábolas e identifica vértice e intersecciones.", "dimension" => "Numérico-Variacional"],
                ["id" => "MAT-9-03", "grado" => "noveno", "descripcion" => "Calcula medidas de tendencia central y dispersión en conjuntos de datos.", "evidencia" => "Interpreta media, mediana, moda y rango.", "dimension" => "Aleatorio"],
                ["id" => "MAT-10-01", "grado" => "decimo", "descripcion" => "Comprende y aplica las funciones trigonométricas en triángulos rectángulos.", "evidencia" => "Calcula lados y ángulos usando seno, coseno y tangente.", "dimension" => "Geométrico-Métrico"],
                ["id" => "MAT-10-02", "grado" => "decimo", "descripcion" => "Opera con números complejos en forma binómica y polar.", "evidencia" => "Suma, resta, multiplica y divide números complejos.", "dimension" => "Numérico-Variacional"],
                ["id" => "MAT-10-03", "grado" => "decimo", "descripcion" => "Resuelve problemas de probabilidad usando técnicas de conteo.", "evidencia" => "Aplica permutaciones, combinaciones y principio fundamental.", "dimension" => "Aleatorio"],
                ["id" => "MAT-11-01", "grado" => "once", "descripcion" => "Analiza funciones exponenciales y logarítmicas en contextos reales.", "evidencia" => "Modela crecimiento poblacional y decaimiento radiactivo.", "dimension" => "Numérico-Variacional"],
                ["id" => "MAT-11-02", "grado" => "once", "descripcion" => "Calcula límites y derivadas de funciones básicas.", "evidencia" => "Aplica reglas de derivación en polinomios.", "dimension" => "Numérico-Variacional"],
                ["id" => "MAT-11-03", "grado" => "once", "descripcion" => "Interpreta intervalos de confianza y pruebas de hipótesis básicas.", "evidencia" => "Toma decisiones basadas en análisis estadístico inferencial.", "dimension" => "Aleatorio"]
            ]
        ],
        "lenguaje" => [
            "nombre" => "Lengua Castellana",
            "categoria" => "Obligatoria y Fundamental",
            "dbas" => [
                ["id" => "LEN-1-01", "grado" => "primero", "descripcion" => "Comprende textos literarios y no literarios leídos por un adulto.", "evidencia" => "Responde preguntas sobre el contenido de cuentos y textos informativos.", "dimension" => "Comprensión e Interpretación"],
                ["id" => "LEN-1-02", "grado" => "primero", "descripcion" => "Reconoce la relación entre grafemas y fonemas del alfabeto.", "evidencia" => "Lee y escribe palabras simples de manera autónoma.", "dimension" => "Producción Textual"],
                ["id" => "LEN-1-03", "grado" => "primero", "descripcion" => "Participa en conversaciones respetando turnos de palabra.", "evidencia" => "Escucha activamente y responde apropiadamente.", "dimension" => "Comunicación Oral"],
                ["id" => "LEN-2-01", "grado" => "segundo", "descripcion" => "Lee y comprende textos narrativos cortos de manera autónoma.", "evidencia" => "Identifica personajes, escenario y secuencia de eventos.", "dimension" => "Comprensión e Interpretación"],
                ["id" => "LEN-2-02", "grado" => "segundo", "descripcion" => "Escribe textos cortos con coherencia temática.", "evidencia" => "Produce párrafos de 3-5 oraciones con idea central.", "dimension" => "Producción Textual"],
                ["id" => "LEN-2-03", "grado" => "segundo", "descripcion" => "Identifica sustantivos, adjetivos y verbos en oraciones simples.", "evidencia" => "Clasifica palabras según categoría gramatical básica.", "dimension" => "Medios y Sistemas de Símbolos"],
                ["id" => "LEN-3-01", "grado" => "tercero", "descripcion" => "Produce textos escritos coherentes con estructura básica (inicio, desarrollo, final).", "evidencia" => "Escribe párrafos con idea principal y ideas de apoyo.", "dimension" => "Producción Textual"],
                ["id" => "LEN-3-02", "grado" => "tercero", "descripcion" => "Infiere información implícita en textos narrativos y descriptivos.", "evidencia" => "Responde preguntas que requieren interpretación.", "dimension" => "Comprensión e Interpretación"],
                ["id" => "LEN-3-03", "grado" => "tercero", "descripcion" => "Usa correctamente mayúsculas, punto final y signos de interrogación.", "evidencia" => "Aplica normas básicas de puntuación en sus escritos.", "dimension" => "Producción Textual"],
                ["id" => "LEN-4-01", "grado" => "cuarto", "descripcion" => "Comprende la función comunicativa de diferentes tipos de texto.", "evidencia" => "Distingue entre texto narrativo, descriptivo, informativo y argumentativo.", "dimension" => "Comprensión e Interpretación"],
                ["id" => "LEN-4-02", "grado" => "cuarto", "descripcion" => "Produce textos informativos con organización lógica.", "evidencia" => "Escribe resúmenes y reportes con introducción y conclusión.", "dimension" => "Producción Textual"],
                ["id" => "LEN-4-03", "grado" => "cuarto", "descripcion" => "Identifica la idea principal y las ideas secundarias en un párrafo.", "evidencia" => "Subraya y resume ideas clave de textos leídos.", "dimension" => "Comprensión e Interpretación"],
                ["id" => "LEN-5-01", "grado" => "quinto", "descripcion" => "Produce textos orales y escritos que evidencian el conocimiento alcanzado.", "evidencia" => "Presenta exposiciones orales y escribe textos con coherencia y cohesión.", "dimension" => "Producción Textual"],
                ["id" => "LEN-5-02", "grado" => "quinto", "descripcion" => "Comprende textos expositivos identificando causa-efecto y problema-solución.", "evidencia" => "Organiza información en mapas conceptuales y cuadros sinópticos.", "dimension" => "Comprensión e Interpretación"],
                ["id" => "LEN-5-03", "grado" => "quinto", "descripcion" => "Reconoce elementos de la comunicación (emisor, receptor, mensaje, canal).", "evidencia" => "Analiza situaciones comunicativas identificando sus componentes.", "dimension" => "Medios y Sistemas de Símbolos"],
                ["id" => "LEN-6-01", "grado" => "sexto", "descripcion" => "Comprende textos literarios para propiciar el desarrollo de su capacidad creativa.", "evidencia" => "Analiza elementos del texto literario: género, trama, personajes.", "dimension" => "Comprensión e Interpretación"],
                ["id" => "LEN-6-02", "grado" => "sexto", "descripcion" => "Produce textos narrativos con secuencia lógica y conectores apropiados.", "evidencia" => "Escribe cuentos cortos con estructura narrativa completa.", "dimension" => "Producción Textual"],
                ["id" => "LEN-6-03", "grado" => "sexto", "descripcion" => "Identifica figuras literarias básicas (metáfora, símil, personificación).", "evidencia" => "Reconoce y explica el efecto de figuras retóricas en poemas.", "dimension" => "Medios y Sistemas de Símbolos"],
                ["id" => "LEN-7-01", "grado" => "sexto", "descripcion" => "Produce textos orales y escritos que evidencian el conocimiento alcanzado.", "evidencia" => "Elabora ensayos cortos con tesis, argumentos y conclusiones.", "dimension" => "Producción Textual"],
                ["id" => "LEN-7-02", "grado" => "sexto", "descripcion" => "Comprende textos argumentativos identificando tesis y argumentos.", "evidencia" => "Distingue hechos de opiniones en textos periodísticos.", "dimension" => "Comprensión e Interpretación"],
                ["id" => "LEN-7-03", "grado" => "sexto", "descripcion" => "Usa diccionarios y otras fuentes para ampliar vocabulario.", "evidencia" => "Incorpora nuevas palabras en sus producciones textuales.", "dimension" => "Medios y Sistemas de Símbolos"],
                ["id" => "LEN-8-01", "grado" => "octavo", "descripcion" => "Comprende e interpreta textos con intención literaria reconociendo contextos históricos.", "evidencia" => "Analiza obras literarias considerando su contexto de producción.", "dimension" => "Comprensión e Interpretación"],
                ["id" => "LEN-8-02", "grado" => "octavo", "descripcion" => "Produce textos argumentativos con sustento teórico.", "evidencia" => "Defiende posturas usando citas y referencias.", "dimension" => "Producción Textual"],
                ["id" => "LEN-8-03", "grado" => "octavo", "descripcion" => "Analiza la intención comunicativa en medios masivos.", "evidencia" => "Identifica propósitos en publicidad, noticias y redes sociales.", "dimension" => "Medios y Sistemas de Símbolos"],
                ["id" => "LEN-9-01", "grado" => "noveno", "descripcion" => "Participa en situaciones comunicativas que evidencian el uso de estrategias discursivas.", "evidencia" => "Argumenta oralmente y por escrito defendiendo puntos de vista.", "dimension" => "Comunicación Oral"],
                ["id" => "LEN-9-02", "grado" => "noveno", "descripcion" => "Comprende textos filosóficos y científicos adaptados a su nivel.", "evidencia" => "Sintetiza ideas complejas en sus propias palabras.", "dimension" => "Comprensión e Interpretación"],
                ["id" => "LEN-9-03", "grado" => "noveno", "descripcion" => "Reconoce variedades dialectales del español en Colombia.", "evidencia" => "Identifica regionalismos y valora la diversidad lingüística.", "dimension" => "Medios y Sistemas de Símbolos"],
                ["id" => "LEN-10-01", "grado" => "decimo", "descripcion" => "Comprende y produce textos académicos con estructura argumentativa sólida.", "evidencia" => "Elabora trabajos de investigación con citas y referencias bibliográficas.", "dimension" => "Producción Textual"],
                ["id" => "LEN-10-02", "grado" => "decimo", "descripcion" => "Analiza obras literarias del canon universal y colombiano.", "evidencia" => "Compara movimientos literarios y sus representantes.", "dimension" => "Comprensión e Interpretación"],
                ["id" => "LEN-10-03", "grado" => "decimo", "descripcion" => "Aplica normas ICONTEC o APA en trabajos escritos.", "evidencia" => "Cita fuentes correctamente y elabora bibliografía.", "dimension" => "Producción Textual"],
                ["id" => "LEN-11-01", "grado" => "once", "descripcion" => "Analiza críticamente discursos de los medios de comunicación y redes sociales.", "evidencia" => "Identifica sesgos, falacias y estrategias persuasivas en textos mediáticos.", "dimension" => "Comprensión e Interpretación"],
                ["id" => "LEN-11-02", "grado" => "once", "descripcion" => "Produce textos de grado con rigor académico.", "evidencia" => "Desarrolla monografía o proyecto de investigación.", "dimension" => "Producción Textual"],
                ["id" => "LEN-11-03", "grado" => "once", "descripcion" => "Demuestra competencia comunicativa para educación superior o mundo laboral.", "evidencia" => "Presenta portafolio de competencias lingüísticas.", "dimension" => "Comunicación Oral"]
            ]
        ],
        "ciencias_naturales" => [
            "nombre" => "Ciencias Naturales y Educación Ambiental",
            "categoria" => "Obligatoria y Fundamental",
            "dbas" => [
                ["id" => "CN-1-01", "grado" => "primero", "descripcion" => "Identifica características de los seres vivos y los clasifica según sus propiedades.", "evidencia" => "Distingue entre seres vivos y objetos inertes.", "dimension" => "Uso comprensivo del conocimiento científico"],
                ["id" => "CN-1-02", "grado" => "primero", "descripcion" => "Reconoce los sentidos y su función para explorar el entorno.", "evidencia" => "Describe experiencias sensoriales.", "dimension" => "Explicación de fenómenos"],
                ["id" => "CN-1-03", "grado" => "primero", "descripcion" => "Practica hábitos de higiene y cuidado personal.", "evidencia" => "Demuestra rutinas de aseo diario.", "dimension" => "Indagación"],
                ["id" => "CN-2-01", "grado" => "segundo", "descripcion" => "Reconoce las partes de las plantas y su función en el ecosistema.", "evidencia" => "Describe el ciclo de vida de las plantas.", "dimension" => "Uso comprensivo del conocimiento científico"],
                ["id" => "CN-2-02", "grado" => "segundo", "descripcion" => "Identifica animales vertebrados e invertebrados.", "evidencia" => "Clasifica animales según características observables.", "dimension" => "Explicación de fenómenos"],
                ["id" => "CN-2-03", "grado" => "segundo", "descripcion" => "Reconoce fuentes de luz, calor y sonido.", "evidencia" => "Identifica formas de energía en su entorno.", "dimension" => "Indagación"],
                ["id" => "CN-3-01", "grado" => "tercero", "descripcion" => "Comprende los estados de la materia y sus cambios.", "evidencia" => "Identifica sólido, líquido y gaseoso en su entorno.", "dimension" => "Uso comprensivo del conocimiento científico"],
                ["id" => "CN-3-02", "grado" => "tercero", "descripcion" => "Describe el ciclo del agua y su importancia para la vida.", "evidencia" => "Explica evaporación, condensación y precipitación.", "dimension" => "Explicación de fenómenos"],
                ["id" => "CN-3-03", "grado" => "tercero", "descripcion" => "Identifica órganos de los sentidos y sus cuidados.", "evidencia" => "Relaciona cada sentido con su órgano correspondiente.", "dimension" => "Indagación"],
                ["id" => "CN-4-01", "grado" => "cuarto", "descripcion" => "Identifica las capas de la Tierra y sus características principales.", "evidencia" => "Explica la estructura interna y externa del planeta.", "dimension" => "Uso comprensivo del conocimiento científico"],
                ["id" => "CN-4-02", "grado" => "cuarto", "descripcion" => "Comprende el movimiento de rotación y traslación de la Tierra.", "evidencia" => "Relaciona movimientos terrestres con día/noche y estaciones.", "dimension" => "Explicación de fenómenos"],
                ["id" => "CN-4-03", "grado" => "cuarto", "descripcion" => "Clasifica los materiales según su origen (natural o artificial).", "evidencia" => "Distingue recursos renovables y no renovables.", "dimension" => "Indagación"],
                ["id" => "CN-5-01", "grado" => "quinto", "descripcion" => "Comprende las relaciones entre los seres vivos y su entorno (ecosistemas).", "evidencia" => "Describe cadenas alimenticias y relaciones ecológicas.", "dimension" => "Uso comprensivo del conocimiento científico"],
                ["id" => "CN-5-02", "grado" => "quinto", "descripcion" => "Identifica sistemas del cuerpo humano (digestivo, respiratorio, circulatorio).", "evidencia" => "Explica funciones básicas de cada sistema.", "dimension" => "Explicación de fenómenos"],
                ["id" => "CN-5-03", "grado" => "quinto", "descripcion" => "Propone acciones para el cuidado del medio ambiente.", "evidencia" => "Participa en proyectos de reciclaje y conservación.", "dimension" => "Indagación"],
                ["id" => "CN-6-01", "grado" => "sexto", "descripcion" => "Explica la célula como unidad básica de los seres vivos.", "evidencia" => "Diferencia células procariotas y eucariotas, animales y vegetales.", "dimension" => "Uso comprensivo del conocimiento científico"],
                ["id" => "CN-6-02", "grado" => "sexto", "descripcion" => "Comprende la clasificación de los seres vivos en reinos.", "evidencia" => "Ubica organismos en los cinco reinos.", "dimension" => "Explicación de fenómenos"],
                ["id" => "CN-6-03", "grado" => "sexto", "descripcion" => "Investiga fenómenos físicos usando el método científico.", "evidencia" => "Formula hipótesis y diseña experimentos simples.", "dimension" => "Indagación"],
                ["id" => "CN-7-01", "grado" => "sexto", "descripcion" => "Comprende los procesos de transformación de la materia y la energía.", "evidencia" => "Identifica cambios físicos y químicos en experimentos.", "dimension" => "Uso comprensivo del conocimiento científico"],
                ["id" => "CN-7-02", "grado" => "sexto", "descripcion" => "Explica la nutrición en seres vivos (autótrofa y heterótrofa).", "evidencia" => "Compara procesos de alimentación en plantas y animales.", "dimension" => "Explicación de fenómenos"],
                ["id" => "CN-7-03", "grado" => "sexto", "descripcion" => "Analiza interacciones ecológicas (depredación, mutualismo, parasitismo).", "evidencia" => "Identifica relaciones entre especies en ecosistemas.", "dimension" => "Indagación"],
                ["id" => "CN-8-01", "grado" => "octavo", "descripcion" => "Explica la reproducción y herencia en los seres vivos.", "evidencia" => "Comprende conceptos básicos de genética y ADN.", "dimension" => "Uso comprensivo del conocimiento científico"],
                ["id" => "CN-8-02", "grado" => "octavo", "descripcion" => "Describe la estructura y función del sistema nervioso.", "evidencia" => "Relaciona neuronas con procesos de información.", "dimension" => "Explicación de fenómenos"],
                ["id" => "CN-8-03", "grado" => "octavo", "descripcion" => "Comprende la electricidad y el magnetismo como fenómenos relacionados.", "evidencia" => "Construye circuitos eléctricos simples.", "dimension" => "Indagación"],
                ["id" => "CN-9-01", "grado" => "noveno", "descripcion" => "Comprende las leyes de Newton y su aplicación en fenómenos físicos.", "evidencia" => "Resuelve problemas de fuerza, masa y aceleración.", "dimension" => "Uso comprensivo del conocimiento científico"],
                ["id" => "CN-9-02", "grado" => "noveno", "descripcion" => "Explica procesos de reproducción humana y salud sexual.", "evidencia" => "Identifica métodos de planificación y prevención.", "dimension" => "Explicación de fenómenos"],
                ["id" => "CN-9-03", "grado" => "noveno", "descripcion" => "Analiza el impacto ambiental de la actividad humana.", "evidencia" => "Propone soluciones a problemas ambientales locales.", "dimension" => "Indagación"],
                ["id" => "CN-10-01", "grado" => "decimo", "descripcion" => "Analiza procesos de química orgánica y bioquímica básica.", "evidencia" => "Identifica compuestos orgánicos y sus funciones biológicas.", "dimension" => "Uso comprensivo del conocimiento científico"],
                ["id" => "CN-10-02", "grado" => "decimo", "descripcion" => "Comprende la termodinámica y sus leyes fundamentales.", "evidencia" => "Explica transferencia de calor y conservación de energía.", "dimension" => "Explicación de fenómenos"],
                ["id" => "CN-10-03", "grado" => "decimo", "descripcion" => "Investiga sobre biotecnología y sus aplicaciones.", "evidencia" => "Analiza casos de uso de biotecnología en medicina y agricultura.", "dimension" => "Indagación"],
                ["id" => "CN-11-01", "grado" => "once", "descripcion" => "Comprende la evolución de las especies y la biodiversidad.", "evidencia" => "Explica teorías evolutivas y evidencia fósil.", "dimension" => "Uso comprensivo del conocimiento científico"],
                ["id" => "CN-11-02", "grado" => "once", "descripcion" => "Analiza la física moderna (relatividad, cuántica) a nivel introductorio.", "evidencia" => "Describe contribuciones de Einstein y Planck.", "dimension" => "Explicación de fenómenos"],
                ["id" => "CN-11-03", "grado" => "once", "descripcion" => "Evalúa problemas ambientales globales (cambio climático, pérdida de biodiversidad).", "evidencia" => "Propone acciones desde su contexto para mitigar impactos.", "dimension" => "Indagación"]
            ]
        ],
        "ciencias_sociales" => [
            "nombre" => "Ciencias Sociales, Historia, Geografía, Constitución y Democracia",
            "categoria" => "Obligatoria y Fundamental",
            "dbas" => [
                ["id" => "CS-1-01", "grado" => "primero", "descripcion" => "Reconoce su entorno familiar, escolar y comunitario.", "evidencia" => "Identifica roles y normas de convivencia en su entorno.", "dimension" => "Relaciones espaciales y ambientales"],
                ["id" => "CS-1-02", "grado" => "primero", "descripcion" => "Identifica símbolos patrios y su significado.", "evidencia" => "Nombra la bandera, escudo e himno de Colombia.", "dimension" => "Relaciones ético-políticas"],
                ["id" => "CS-1-03", "grado" => "primero", "descripcion" => "Reconoce profesiones y oficios de su comunidad.", "evidencia" => "Describe funciones de diferentes trabajadores.", "dimension" => "Relaciones con la historia y las culturas"],
                ["id" => "CS-2-01", "grado" => "segundo", "descripcion" => "Ubica en el tiempo y el espacio eventos significativos de su vida y comunidad.", "evidencia" => "Utiliza líneas de tiempo y mapas básicos.", "dimension" => "Relaciones espaciales y ambientales"],
                ["id" => "CS-2-02", "grado" => "segundo", "descripcion" => "Reconoce derechos y deberes de los niños.", "evidencia" => "Menciona derechos fundamentales de la infancia.", "dimension" => "Relaciones ético-políticas"],
                ["id" => "CS-2-03", "grado" => "segundo", "descripcion" => "Identifica tradiciones culturales de su región.", "evidencia" => "Describe fiestas, comidas y costumbres locales.", "dimension" => "Relaciones con la historia y las culturas"],
                ["id" => "CS-3-01", "grado" => "tercero", "descripcion" => "Reconoce la organización político-administrativa de Colombia.", "evidencia" => "Identifica departamentos, municipios y sus autoridades.", "dimension" => "Relaciones espaciales y ambientales"],
                ["id" => "CS-3-02", "grado" => "tercero", "descripcion" => "Comprende la importancia de la convivencia democrática.", "evidencia" => "Participa en la construcción de acuerdos de convivencia.", "dimension" => "Relaciones ético-políticas"],
                ["id" => "CS-3-03", "grado" => "tercero", "descripcion" => "Identifica grupos étnicos de Colombia.", "evidencia" => "Reconoce pueblos indígenas, afrocolombianos y raizales.", "dimension" => "Relaciones con la historia y las culturas"],
                ["id" => "CS-4-01", "grado" => "cuarto", "descripcion" => "Comprende las características geográficas de Colombia y su región.", "evidencia" => "Ubica relieve, hidrografía y climas en mapas.", "dimension" => "Relaciones espaciales y ambientales"],
                ["id" => "CS-4-02", "grado" => "cuarto", "descripcion" => "Reconoce la importancia de la Constitución y las leyes.", "evidencia" => "Explica por qué existen normas en la sociedad.", "dimension" => "Relaciones ético-políticas"],
                ["id" => "CS-4-03", "grado" => "cuarto", "descripcion" => "Identifica los períodos históricos de Colombia (precolombino, conquista, colonia).", "evidencia" => "Describe características de cada período histórico.", "dimension" => "Relaciones con la historia y las culturas"],
                ["id" => "CS-5-01", "grado" => "quinto", "descripcion" => "Analiza la independencia de Colombia y sus próceres.", "evidencia" => "Nombra personajes clave del proceso independentista.", "dimension" => "Relaciones con la historia y las culturas"],
                ["id" => "CS-5-02", "grado" => "quinto", "descripcion" => "Comprende la organización del Estado colombiano.", "evidencia" => "Identifica ramas del poder público.", "dimension" => "Relaciones ético-políticas"],
                ["id" => "CS-5-03", "grado" => "quinto", "descripcion" => "Ubica Colombia en el contexto latinoamericano y mundial.", "evidencia" => "Localiza países vecinos y continentes en mapas.", "dimension" => "Relaciones espaciales y ambientales"],
                ["id" => "CS-6-01", "grado" => "sexto", "descripcion" => "Comprende el origen de la humanidad y las primeras civilizaciones.", "evidencia" => "Ubica temporal y espacialmente civilizaciones antiguas.", "dimension" => "Relaciones con la historia y las culturas"],
                ["id" => "CS-6-02", "grado" => "sexto", "descripcion" => "Reconoce la Tierra como sistema y sus características físicas.", "evidencia" => "Describe continentes, océanos y zonas climáticas.", "dimension" => "Relaciones espaciales y ambientales"],
                ["id" => "CS-6-03", "grado" => "sexto", "descripcion" => "Comprende conceptos básicos de democracia y ciudadanía.", "evidencia" => "Participa en elecciones escolares y toma decisiones colectivas.", "dimension" => "Relaciones ético-políticas"],
                ["id" => "CS-7-01", "grado" => "sexto", "descripcion" => "Analiza la Edad Media y el surgimiento del mundo moderno.", "evidencia" => "Explica feudalismo, renacimiento y descubrimiento de América.", "dimension" => "Relaciones con la historia y las culturas"],
                ["id" => "CS-7-02", "grado" => "sexto", "descripcion" => "Comprende la dinámica poblacional y migratoria.", "evidencia" => "Analiza causas y consecuencias de las migraciones.", "dimension" => "Relaciones espaciales y ambientales"],
                ["id" => "CS-7-03", "grado" => "sexto", "descripcion" => "Identifica mecanismos de participación ciudadana.", "evidencia" => "Explica voto, plebiscito, referendo y consulta popular.", "dimension" => "Relaciones ético-políticas"],
                ["id" => "CS-8-01", "grado" => "octavo", "descripcion" => "Comprende el proceso de independencia de Colombia y América.", "evidencia" => "Identifica causas, personajes y consecuencias de la independencia.", "dimension" => "Relaciones con la historia y las culturas"],
                ["id" => "CS-8-02", "grado" => "octavo", "descripcion" => "Analiza la diversidad cultural de Colombia.", "evidencia" => "Valora manifestaciones culturales de diferentes regiones.", "dimension" => "Relaciones con la historia y las culturas"],
                ["id" => "CS-8-03", "grado" => "octavo", "descripcion" => "Comprende los derechos humanos y su protección internacional.", "evidencia" => "Identifica organismos de protección de DDHH.", "dimension" => "Relaciones ético-políticas"],
                ["id" => "CS-9-01", "grado" => "noveno", "descripcion" => "Analiza la República en el siglo XIX y las transformaciones del siglo XX.", "evidencia" => "Explica guerras civiles, Frente Nacional y conflicto armado.", "dimension" => "Relaciones con la historia y las culturas"],
                ["id" => "CS-9-02", "grado" => "noveno", "descripcion" => "Comprende la economía colombiana y sus sectores.", "evidencia" => "Identifica sectores primario, secundario y terciario.", "dimension" => "Relaciones espaciales y ambientales"],
                ["id" => "CS-9-03", "grado" => "noveno", "descripcion" => "Analiza el conflicto armado colombiano y procesos de paz.", "evidencia" => "Explica causas, actores y consecuencias del conflicto.", "dimension" => "Relaciones ético-políticas"],
                ["id" => "CS-10-01", "grado" => "decimo", "descripcion" => "Comprende la organización del Estado colombiano y la Constitución de 1991.", "evidencia" => "Identifica ramas del poder público y mecanismos de participación.", "dimension" => "Relaciones ético-políticas"],
                ["id" => "CS-10-02", "grado" => "decimo", "descripcion" => "Analiza la globalización y sus efectos en Colombia.", "evidencia" => "Explica tratados de libre comercio y relaciones internacionales.", "dimension" => "Relaciones espaciales y ambientales"],
                ["id" => "CS-10-03", "grado" => "decimo", "descripcion" => "Comprende movimientos sociales y políticos del siglo XX.", "evidencia" => "Analiza luchas por derechos civiles, laborales y de género.", "dimension" => "Relaciones con la historia y las culturas"],
                ["id" => "CS-11-01", "grado" => "once", "descripcion" => "Analiza la economía colombiana y su inserción en el contexto global.", "evidencia" => "Explica conceptos de PIB, inflación, comercio internacional.", "dimension" => "Relaciones espaciales y ambientales"],
                ["id" => "CS-11-02", "grado" => "once", "descripcion" => "Comprende el sistema político colombiano contemporáneo.", "evidencia" => "Analiza partidos políticos, elecciones y reforma política.", "dimension" => "Relaciones ético-políticas"],
                ["id" => "CS-11-03", "grado" => "once", "descripcion" => "Evalúa desafíos de Colombia en el siglo XXI.", "evidencia" => "Propone soluciones a problemas de inequidad, corrupción y violencia.", "dimension" => "Relaciones con la historia y las culturas"]
            ]
        ],
        "ingles" => [
            "nombre" => "Inglés como Lengua Extranjera",
            "categoria" => "Obligatoria y Fundamental",
            "dbas" => [
                ["id" => "ING-1-01", "grado" => "primero", "descripcion" => "Reconoce vocabulario básico en inglés relacionado con su entorno inmediato.", "evidencia" => "Identifica colores, números, animales y familia en inglés.", "nivel_mcer" => "Pre-A1"],
                ["id" => "ING-1-02", "grado" => "primero", "descripcion" => "Responde a saludos y despedidas básicas en inglés.", "evidencia" => "Usa 'Hello', 'Goodbye', 'Thank you' apropiadamente.", "nivel_mcer" => "Pre-A1"],
                ["id" => "ING-2-01", "grado" => "segundo", "descripcion" => "Comprende instrucciones simples en inglés en contextos cotidianos.", "evidencia" => "Sigue comandos básicos como 'stand up', 'sit down', 'listen'.", "nivel_mcer" => "Pre-A1"],
                ["id" => "ING-2-02", "grado" => "segundo", "descripcion" => "Nombra objetos del aula y la casa en inglés.", "evidencia" => "Identifica 50+ palabras de vocabulario básico.", "nivel_mcer" => "Pre-A1"],
                ["id" => "ING-3-01", "grado" => "tercero", "descripcion" => "Produce frases cortas en inglés para describir personas y objetos.", "evidencia" => "Usa estructuras básicas como 'This is...', 'I have...'.", "nivel_mcer" => "A1"],
                ["id" => "ING-3-02", "grado" => "tercero", "descripcion" => "Canta canciones y recita rimas en inglés.", "evidencia" => "Pronuncia correctamente palabras en contextos lúdicos.", "nivel_mcer" => "A1"],
                ["id" => "ING-4-01", "grado" => "cuarto", "descripcion" => "Comprende textos cortos en inglés con apoyo visual.", "evidencia" => "Lee y entiende párrafos simples con vocabulario conocido.", "nivel_mcer" => "A1"],
                ["id" => "ING-4-02", "grado" => "cuarto", "descripcion" => "Describe rutinas diarias usando presente simple.", "evidencia" => "Escribe 5-10 oraciones sobre su día típico.", "nivel_mcer" => "A1"],
                ["id" => "ING-5-01", "grado" => "quinto", "descripcion" => "Participa en conversaciones básicas en inglés sobre temas familiares.", "evidencia" => "Formula y responde preguntas simples en presente.", "nivel_mcer" => "A1"],
                ["id" => "ING-5-02", "grado" => "quinto", "descripcion" => "Escribe textos cortos sobre temas personales.", "evidencia" => "Produce párrafos de 50+ palabras con coherencia.", "nivel_mcer" => "A1"],
                ["id" => "ING-6-01", "grado" => "sexto", "descripcion" => "Comprende el nivel A1 del Marco Común Europeo de Referencia.", "evidencia" => "Se identifica con nivel A1 en habilidades receptivas.", "nivel_mcer" => "A1"],
                ["id" => "ING-6-02", "grado" => "sexto", "descripcion" => "Usa pasado simple para narrar eventos.", "evidencia" => "Describe actividades del fin de semana en pasado.", "nivel_mcer" => "A1"],
                ["id" => "ING-7-01", "grado" => "sexto", "descripcion" => "Produce textos cortos en inglés usando presente y pasado simple.", "evidencia" => "Escribe párrafos coherentes sobre experiencias personales.", "nivel_mcer" => "A2"],
                ["id" => "ING-7-02", "grado" => "sexto", "descripcion" => "Comprende anuncios, señales y mensajes cotidianos en inglés.", "evidencia" => "Interpreta información en contextos reales.", "nivel_mcer" => "A2"],
                ["id" => "ING-8-01", "grado" => "octavo", "descripcion" => "Comprende el nivel A2 del Marco Común Europeo de Referencia.", "evidencia" => "Se identifica con nivel A2 en habilidades comunicativas.", "nivel_mcer" => "A2"],
                ["id" => "ING-8-02", "grado" => "octavo", "descripcion" => "Expresa planes futuros usando 'going to' y presente continuo.", "evidencia" => "Describe vacaciones y proyectos personales.", "nivel_mcer" => "A2"],
                ["id" => "ING-9-01", "grado" => "noveno", "descripcion" => "Participa en interacciones orales usando estructuras de futuro y condicionales.", "evidencia" => "Expresa planes, predicciones y situaciones hipotéticas.", "nivel_mcer" => "A2+"],
                ["id" => "ING-9-02", "grado" => "noveno", "descripcion" => "Comprende la idea principal de textos auténticos adaptados.", "evidencia" => "Lee artículos cortos y extrae información clave.", "nivel_mcer" => "A2+"],
                ["id" => "ING-10-01", "grado" => "decimo", "descripcion" => "Comprende el nivel B1 del Marco Común Europeo de Referencia.", "evidencia" => "Se identifica con nivel B1 según estándares MEN.", "nivel_mcer" => "B1"],
                ["id" => "ING-10-02", "grado" => "decimo", "descripcion" => "Produce textos argumentativos simples en inglés.", "evidencia" => "Escribe opiniones con razones y ejemplos.", "nivel_mcer" => "B1"],
                ["id" => "ING-11-01", "grado" => "once", "descripcion" => "Produce textos académicos en inglés con coherencia y cohesión.", "evidencia" => "Escribe ensayos y presenta argumentos en inglés.", "nivel_mcer" => "B1+"],
                ["id" => "ING-11-02", "grado" => "once", "descripcion" => "Comprende textos especializados de su área de interés.", "evidencia" => "Lee artículos técnicos y académicos básicos.", "nivel_mcer" => "B1+"]
            ]
        ],
        "educacion_fisica" => [
            "nombre" => "Educación Física, Recreación y Deporte",
            "categoria" => "Obligatoria y Fundamental",
            "dbas" => [
                ["id" => "EF-1-01", "grado" => "primero", "descripcion" => "Desarrolla habilidades motrices básicas como correr, saltar y lanzar.", "evidencia" => "Ejecuta movimientos fundamentales con coordinación.", "dimension" => "Desempeño Motor"],
                ["id" => "EF-1-02", "grado" => "primero", "descripcion" => "Reconoce partes del cuerpo y su función en el movimiento.", "evidencia" => "Nombra y usa correctamente segmentos corporales.", "dimension" => "Conocimiento del Cuerpo"],
                ["id" => "EF-2-01", "grado" => "segundo", "descripcion" => "Coordina movimientos en secuencias rítmicas simples.", "evidencia" => "Sigue patrones de movimiento con música.", "dimension" => "Desempeño Motor"],
                ["id" => "EF-2-02", "grado" => "segundo", "descripcion" => "Practica juegos tradicionales de su región.", "evidencia" => "Participa activamente en juegos colectivos.", "dimension" => "Convivencia"],
                ["id" => "EF-3-01", "grado" => "tercero", "descripcion" => "Reconoce la importancia de la actividad física para la salud.", "evidencia" => "Identifica beneficios del ejercicio y hábitos saludables.", "dimension" => "Salud y Bienestar"],
                ["id" => "EF-3-02", "grado" => "tercero", "descripcion" => "Desarrolla equilibrio estático y dinámico.", "evidencia" => "Mantiene posturas de equilibrio por tiempo determinado.", "dimension" => "Desempeño Motor"],
                ["id" => "EF-4-01", "grado" => "cuarto", "descripcion" => "Aplica reglas básicas en juegos deportivos.", "evidencia" => "Respeta normas y acepta resultados.", "dimension" => "Convivencia"],
                ["id" => "EF-4-02", "grado" => "cuarto", "descripcion" => "Mejora capacidades físicas básicas (fuerza, resistencia, flexibilidad).", "evidencia" => "Supera marcas personales en pruebas físicas.", "dimension" => "Salud y Bienestar"],
                ["id" => "EF-5-01", "grado" => "quinto", "descripcion" => "Participa en juegos deportivos respetando reglas y compañeros.", "evidencia" => "Demuestra trabajo en equipo y fair play.", "dimension" => "Convivencia"],
                ["id" => "EF-5-02", "grado" => "quinto", "descripcion" => "Diseña rutinas de ejercicio básicas.", "evidencia" => "Crea secuencias de calentamiento y estiramiento.", "dimension" => "Conocimiento del Cuerpo"],
                ["id" => "EF-6-01", "grado" => "sexto", "descripcion" => "Comprende sistemas del cuerpo humano relacionados con el movimiento.", "evidencia" => "Explica funciones de sistemas óseo, muscular y cardiovascular.", "dimension" => "Conocimiento del Cuerpo"],
                ["id" => "EF-6-02", "grado" => "sexto", "descripcion" => "Practica deportes individuales y colectivos.", "evidencia" => "Demuestra habilidades técnicas en al menos 2 deportes.", "dimension" => "Desempeño Motor"],
                ["id" => "EF-7-01", "grado" => "sexto", "descripcion" => "Analiza beneficios de la actividad física en la salud mental.", "evidencia" => "Relaciona ejercicio con reducción de estrés y ansiedad.", "dimension" => "Salud y Bienestar"],
                ["id" => "EF-7-02", "grado" => "sexto", "descripcion" => "Desarrolla estrategias tácticas en juegos deportivos.", "evidencia" => "Aplica conceptos de ataque y defensa.", "dimension" => "Desempeño Motor"],
                ["id" => "EF-8-01", "grado" => "octavo", "descripcion" => "Comprende principios del entrenamiento deportivo.", "evidencia" => "Aplica sobrecarga, progresión y especificidad.", "dimension" => "Conocimiento del Cuerpo"],
                ["id" => "EF-8-02", "grado" => "octavo", "descripcion" => "Participa en eventos deportivos institucionales.", "evidencia" => "Compite representando su curso o institución.", "dimension" => "Convivencia"],
                ["id" => "EF-9-01", "grado" => "noveno", "descripcion" => "Diseña y ejecuta planes de entrenamiento básico.", "evidencia" => "Aplica principios de frecuencia, intensidad y duración.", "dimension" => "Salud y Bienestar"],
                ["id" => "EF-9-02", "grado" => "noveno", "descripcion" => "Analiza factores de riesgo en la práctica deportiva.", "evidencia" => "Identifica y previene lesiones comunes.", "dimension" => "Conocimiento del Cuerpo"],
                ["id" => "EF-10-01", "grado" => "decimo", "descripcion" => "Comprende nutrición deportiva y hábitos alimenticios.", "evidencia" => "Relaciona alimentación con rendimiento físico.", "dimension" => "Salud y Bienestar"],
                ["id" => "EF-10-02", "grado" => "decimo", "descripcion" => "Demuestra competencia técnica en deportes seleccionados.", "evidencia" => "Ejecuta fundamentos con precisión.", "dimension" => "Desempeño Motor"],
                ["id" => "EF-11-01", "grado" => "once", "descripcion" => "Promueve estilos de vida activos y saludables en su comunidad.", "evidencia" => "Lidera iniciativas de actividad física escolar.", "dimension" => "Convivencia"],
                ["id" => "EF-11-02", "grado" => "once", "descripcion" => "Evalúa su condición física y establece metas personales.", "evidencia" => "Realiza autoevaluación y plan de mejora.", "dimension" => "Salud y Bienestar"]
            ]
        ],
        "educacion_artistica" => [
            "nombre" => "Educación Artística y Cultural",
            "categoria" => "Obligatoria y Fundamental",
            "dbas" => [
                ["id" => "ART-1-01", "grado" => "primero", "descripcion" => "Explora diferentes materiales y técnicas para crear expresiones artísticas.", "evidencia" => "Produce obras usando pintura, collage y modelado.", "lenguaje" => "Artes Visuales"],
                ["id" => "ART-1-02", "grado" => "primero", "descripcion" => "Reconoce sonidos del entorno y los reproduce.", "evidencia" => "Imita sonidos de animales, naturaleza y objetos.", "lenguaje" => "Música"],
                ["id" => "ART-2-01", "grado" => "segundo", "descripcion" => "Expresa emociones a través del dibujo y la pintura.", "evidencia" => "Crea obras que representan sentimientos.", "lenguaje" => "Artes Visuales"],
                ["id" => "ART-2-02", "grado" => "segundo", "descripcion" => "Participa en juegos dramáticos y de角色.", "evidencia" => "Representa personajes en situaciones cotidianas.", "lenguaje" => "Teatro"],
                ["id" => "ART-3-01", "grado" => "tercero", "descripcion" => "Reconoce manifestaciones artísticas de su región y cultura.", "evidencia" => "Identifica música, danza y artesanías locales.", "lenguaje" => "Cultura"],
                ["id" => "ART-3-02", "grado" => "tercero", "descripcion" => "Canta canciones tradicionales y folclóricas.", "evidencia" => "Interpreta melodías de su región.", "lenguaje" => "Música"],
                ["id" => "ART-4-01", "grado" => "cuarto", "descripcion" => "Aplica elementos del lenguaje visual (línea, color, forma).", "evidencia" => "Composiciones con principios de diseño.", "lenguaje" => "Artes Visuales"],
                ["id" => "ART-4-02", "grado" => "cuarto", "descripcion" => "Baila danzas tradicionales colombianas.", "evidencia" => "Ejecuta pasos básicos de cumbia, bambuco, etc.", "lenguaje" => "Danza"],
                ["id" => "ART-5-01", "grado" => "quinto", "descripcion" => "Crea composiciones artísticas integrando diferentes lenguajes expresivos.", "evidencia" => "Combina elementos visuales, sonoros y corporales.", "lenguaje" => "Multimedia"],
                ["id" => "ART-5-02", "grado" => "quinto", "descripcion" => "Construye instrumentos musicales con materiales reciclados.", "evidencia" => "Crea y usa instrumentos en ensambles.", "lenguaje" => "Música"],
                ["id" => "ART-6-01", "grado" => "sexto", "descripcion" => "Analiza obras de arte considerando elementos formales.", "evidencia" => "Describe composición, color y técnica en obras.", "lenguaje" => "Artes Visuales"],
                ["id" => "ART-6-02", "grado" => "sexto", "descripcion" => "Lee y escribe partituras básicas.", "evidencia" => "Identifica notas, compases y ritmos.", "lenguaje" => "Música"],
                ["id" => "ART-7-01", "grado" => "sexto", "descripcion" => "Analiza obras de arte considerando contexto histórico y cultural.", "evidencia" => "Interpreta significados de obras de artistas reconocidos.", "lenguaje" => "Historia del Arte"],
                ["id" => "ART-7-02", "grado" => "sexto", "descripcion" => "Crea guiones y representa escenas teatrales.", "evidencia" => "Escribe y actúa en obras cortas.", "lenguaje" => "Teatro"],
                ["id" => "ART-8-01", "grado" => "octavo", "descripcion" => "Experimenta con técnicas artísticas avanzadas.", "evidencia" => "Aplica acuarela, óleo, grabado u otras técnicas.", "lenguaje" => "Artes Visuales"],
                ["id" => "ART-8-02", "grado" => "octavo", "descripcion" => "Compone melodías simples usando notación musical.", "evidencia" => "Crea piezas originales de 8-16 compases.", "lenguaje" => "Música"],
                ["id" => "ART-9-01", "grado" => "noveno", "descripcion" => "Produce proyectos artísticos con intención comunicativa.", "evidencia" => "Desarrolla portafolio con obras propias.", "lenguaje" => "Artes Visuales"],
                ["id" => "ART-9-02", "grado" => "noveno", "descripcion" => "Coreografía secuencias de movimiento.", "evidencia" => "Crea y enseña rutinas de danza.", "lenguaje" => "Danza"],
                ["id" => "ART-10-01", "grado" => "decimo", "descripcion" => "Investiga movimientos artísticos del siglo XX.", "evidencia" => "Presenta trabajos sobre vanguardias artísticas.", "lenguaje" => "Historia del Arte"],
                ["id" => "ART-10-02", "grado" => "decimo", "descripcion" => "Utiliza tecnologías digitales para creación artística.", "evidencia" => "Produce arte digital, video o animación.", "lenguaje" => "Arte Digital"],
                ["id" => "ART-11-01", "grado" => "once", "descripcion" => "Valora el patrimonio cultural colombiano y su diversidad.", "evidencia" => "Investiga y presenta manifestaciones culturales regionales.", "lenguaje" => "Cultura"],
                ["id" => "ART-11-02", "grado" => "once", "descripcion" => "Desarrolla proyecto artístico de grado.", "evidencia" => "Presenta exposición o montaje final.", "lenguaje" => "Proyecto Integrador"]
            ]
        ],
        "etica_valores" => [
            "nombre" => "Ética y Valores Humanos",
            "categoria" => "Obligatoria y Fundamental",
            "dbas" => [
                ["id" => "ETI-1-01", "grado" => "primero", "descripcion" => "Reconoce emociones básicas y las expresa de manera adecuada.", "evidencia" => "Identifica alegría, tristeza, enojo y miedo en sí mismo y otros.", "dimension" => "Autoconocimiento"],
                ["id" => "ETI-1-02", "grado" => "primero", "descripcion" => "Practica normas de cortesía y buen trato.", "evidencia" => "Usa 'por favor', 'gracias', 'disculpe'.", "dimension" => "Convivencia"],
                ["id" => "ETI-2-01", "grado" => "segundo", "descripcion" => "Identifica consecuencias de sus acciones.", "evidencia" => "Reconoce cuando sus acciones afectan a otros.", "dimension" => "Responsabilidad"],
                ["id" => "ETI-2-02", "grado" => "segundo", "descripcion" => "Respeta diferencias físicas y culturales.", "evidencia" => "Trata igual a todos sus compañeros.", "dimension" => "Pluralidad"],
                ["id" => "ETI-3-01", "grado" => "tercero", "descripcion" => "Practica valores de convivencia como respeto, honestidad y solidaridad.", "evidencia" => "Demuestra comportamientos prosociales en el aula.", "dimension" => "Valores"],
                ["id" => "ETI-3-02", "grado" => "tercero", "descripcion" => "Reconoce la importancia de la verdad y la honestidad.", "evidencia" => "Admite errores y dice la verdad.", "dimension" => "Integridad"],
                ["id" => "ETI-4-01", "grado" => "cuarto", "descripcion" => "Identifica situaciones de injusticia y las reporta.", "evidencia" => "Busca ayuda adulta ante abuso o discriminación.", "dimension" => "Justicia"],
                ["id" => "ETI-4-02", "grado" => "cuarto", "descripcion" => "Practica la empatía poniéndose en el lugar del otro.", "evidencia" => "Ofrece apoyo a compañeros en dificultad.", "dimension" => "Empatía"],
                ["id" => "ETI-5-01", "grado" => "quinto", "descripcion" => "Resuelve conflictos de manera pacífica mediante el diálogo.", "evidencia" => "Aplica estrategias de mediación y negociación.", "dimension" => "Resolución de Conflictos"],
                ["id" => "ETI-5-02", "grado" => "quinto", "descripcion" => "Comprende la importancia de la libertad y la responsabilidad.", "evidencia" => "Toma decisiones considerando consecuencias.", "dimension" => "Autonomía"],
                ["id" => "ETI-6-01", "grado" => "sexto", "descripcion" => "Analiza dilemas morales simples.", "evidencia" => "Argumenta decisiones éticas en casos hipotéticos.", "dimension" => "Razonamiento Moral"],
                ["id" => "ETI-6-02", "grado" => "sexto", "descripcion" => "Reconoce la diversidad religiosa y cultural.", "evidencia" => "Respeta creencias diferentes a las propias.", "dimension" => "Pluralidad"],
                ["id" => "ETI-7-01", "grado" => "sexto", "descripcion" => "Comprende la diversidad cultural y religiosa como valor social.", "evidencia" => "Respeta diferencias y rechaza discriminación.", "dimension" => "Pluralidad"],
                ["id" => "ETI-7-02", "grado" => "sexto", "descripcion" => "Identifica valores democráticos en la convivencia escolar.", "evidencia" => "Participa en construcción de gobierno escolar.", "dimension" => "Democracia"],
                ["id" => "ETI-8-01", "grado" => "octavo", "descripcion" => "Analiza el origen de los valores en diferentes culturas.", "evidencia" => "Compara sistemas éticos de diversas sociedades.", "dimension" => "Razonamiento Moral"],
                ["id" => "ETI-8-02", "grado" => "octavo", "descripcion" => "Reconoce derechos sexuales y reproductivos.", "evidencia" => "Identifica fuentes de información confiables.", "dimension" => "Autocuidado"],
                ["id" => "ETI-9-01", "grado" => "noveno", "descripcion" => "Analiza dilemas éticos desde diferentes perspectivas filosóficas.", "evidencia" => "Argumenta posiciones morales con fundamentos.", "dimension" => "Razonamiento Moral"],
                ["id" => "ETI-9-02", "grado" => "noveno", "descripcion" => "Comprende la ética en el uso de tecnología y redes sociales.", "evidencia" => "Aplica normas de netiqueta y protección de datos.", "dimension" => "Ética Digital"],
                ["id" => "ETI-10-01", "grado" => "decimo", "descripcion" => "Analiza problemas éticos contemporáneos (bioética, medio ambiente).", "evidencia" => "Debate temas de actualidad con fundamentos.", "dimension" => "Ética Aplicada"],
                ["id" => "ETI-10-02", "grado" => "decimo", "descripcion" => "Comprende la relación entre ética y profesión.", "evidencia" => "Identifica códigos deontológicos de diferentes profesiones.", "dimension" => "Ética Profesional"],
                ["id" => "ETI-11-01", "grado" => "once", "descripcion" => "Ejerce ciudadanía democrática participando en decisiones colectivas.", "evidencia" => "Participa en gobierno escolar y proyectos comunitarios.", "dimension" => "Ciudadanía"],
                ["id" => "ETI-11-02", "grado" => "once", "descripcion" => "Desarrolla proyecto de vida con fundamentos éticos.", "evidencia" => "Elabora plan de vida con valores y metas.", "dimension" => "Proyecto de Vida"]
            ]
        ],
        "tecnologia_informatica" => [
            "nombre" => "Tecnología e Informática",
            "categoria" => "Obligatoria y Fundamental",
            "dbas" => [
                ["id" => "TEC-1-01", "grado" => "primero", "descripcion" => "Identifica herramientas tecnológicas de uso cotidiano y su función.", "evidencia" => "Nombra y describe uso de dispositivos básicos.", "competencia" => "Tecnológica"],
                ["id" => "TEC-1-02", "grado" => "primero", "descripcion" => "Usa el mouse y teclado para interactuar con el computador.", "evidencia" => "Realiza clic, doble clic y arrastrar.", "competencia" => "Tecnológica"],
                ["id" => "TEC-2-01", "grado" => "segundo", "descripcion" => "Dibuja y crea formas usando software básico.", "evidencia" => "Usa Paint o similar para crear ilustraciones.", "competencia" => "Tecnológica"],
                ["id" => "TEC-2-02", "grado" => "segundo", "descripcion" => "Reconoce partes del computador (monitor, teclado, CPU).", "evidencia" => "Identifica componentes hardware básicos.", "competencia" => "Tecnológica"],
                ["id" => "TEC-3-01", "grado" => "tercero", "descripcion" => "Utiliza el computador para crear documentos y dibujos simples.", "evidencia" => "Maneja procesador de texto y programas de dibujo básicos.", "competencia" => "Tecnológica"],
                ["id" => "TEC-3-02", "grado" => "tercero", "descripcion" => "Guarda y organiza archivos en carpetas.", "evidencia" => "Crea estructura de carpetas y guarda documentos.", "competencia" => "Tecnológica"],
                ["id" => "TEC-4-01", "grado" => "cuarto", "descripcion" => "Busca información en internet con supervisión.", "evidencia" => "Usa motores de búsqueda con palabras clave.", "competencia" => "Tecnológica"],
                ["id" => "TEC-4-02", "grado" => "cuarto", "descripcion" => "Crea presentaciones digitales simples.", "evidencia" => "Usa PowerPoint o similar con texto e imágenes.", "competencia" => "Tecnológica"],
                ["id" => "TEC-5-01", "grado" => "quinto", "descripcion" => "Comprende normas de seguridad y cuidado en el uso de internet.", "evidencia" => "Aplica protocolos de navegación segura y protección de datos.", "competencia" => "Ética y Seguridad"],
                ["id" => "TEC-5-02", "grado" => "quinto", "descripcion" => "Crea hojas de cálculo básicas.", "evidencia" => "Ingresa datos y usa fórmulas simples en Excel.", "competencia" => "Tecnológica"],
                ["id" => "TEC-6-01", "grado" => "sexto", "descripcion" => "Comprende funcionamiento básico de hardware y software.", "evidencia" => "Diferencia componentes físicos y programas.", "competencia" => "Tecnológica"],
                ["id" => "TEC-6-02", "grado" => "sexto", "descripcion" => "Usa correo electrónico de manera responsable.", "evidencia" => "Envía y recibe emails con adjuntos.", "competencia" => "Comunicación Digital"],
                ["id" => "TEC-7-01", "grado" => "sexto", "descripcion" => "Diseña soluciones tecnológicas simples para problemas cotidianos.", "evidencia" => "Desarrolla proyectos usando metodología de diseño.", "competencia" => "Resolución de Problemas"],
                ["id" => "TEC-7-02", "grado" => "sexto", "descripcion" => "Crea contenido multimedia (video, audio, imagen).", "evidencia" => "Edita archivos multimedia con software básico.", "competencia" => "Tecnológica"],
                ["id" => "TEC-8-01", "grado" => "octavo", "descripcion" => "Comprende redes y conectividad básica.", "evidencia" => "Configura conexión WiFi y comparte archivos.", "competencia" => "Tecnológica"],
                ["id" => "TEC-8-02", "grado" => "octavo", "descripcion" => "Desarrolla páginas web simples con HTML.", "evidencia" => "Crea sitio web básico con estructura HTML.", "competencia" => "Programación"],
                ["id" => "TEC-9-01", "grado" => "noveno", "descripcion" => "Programa algoritmos básicos usando lenguajes de programación visual.", "evidencia" => "Crea programas funcionales en Scratch, Python o similar.", "competencia" => "Programación"],
                ["id" => "TEC-9-02", "grado" => "noveno", "descripcion" => "Analiza impacto de redes sociales en la sociedad.", "evidencia" => "Debate ventajas y riesgos de redes sociales.", "competencia" => "Ética y Seguridad"],
                ["id" => "TEC-10-01", "grado" => "decimo", "descripcion" => "Desarrolla aplicaciones o scripts básicos.", "evidencia" => "Crea programas funcionales en lenguaje textual.", "competencia" => "Programación"],
                ["id" => "TEC-10-02", "grado" => "decimo", "descripcion" => "Comprende bases de datos y manejo de información.", "evidencia" => "Crea y consulta bases de datos simples.", "competencia" => "Tecnológica"],
                ["id" => "TEC-11-01", "grado" => "once", "descripcion" => "Analiza el impacto social y ambiental de la tecnología.", "evidencia" => "Evalúa consecuencias éticas de innovaciones tecnológicas.", "competencia" => "Ética y Seguridad"],
                ["id" => "TEC-11-02", "grado" => "once", "descripcion" => "Desarrolla proyecto tecnológico de grado.", "evidencia" => "Presenta solución tecnológica a problema real.", "competencia" => "Emprendimiento"]
            ]
        ],
        "educacion_religiosa" => [
            "nombre" => "Educación Religiosa",
            "categoria" => "Opcional (según PEI)",
            "dbas" => [
                ["id" => "REL-1-01", "grado" => "primero", "descripcion" => "Reconoce la familia como espacio fundamental", "evidencia" => "", "eje" => "Antropología religiosa"],
                ["id" => "REL-2-01", "grado" => "segundo", "descripcion" => "Comprende la importancia de la amistad y el amor", "evidencia" => "", "eje" => "Antropología religiosa"],
                ["id" => "REL-3-01", "grado" => "tercero", "descripcion" => "Reconoce la dimensión espiritual como parte integral", "evidencia" => "", "eje" => "Antropología religiosa"],
                ["id" => "REL-4-01", "grado" => "cuarto", "descripcion" => "Comprende los valores fundamentales en tradiciones", "evidencia" => "", "eje" => "Antropología religiosa"],
                ["id" => "REL-5-01", "grado" => "quinto", "descripcion" => "Identifica elementos comunes entre tradiciones", "evidencia" => "", "eje" => "Antropología religiosa"],
                ["id" => "REL-6-01", "grado" => "sexto", "descripcion" => "Comprende el fenómeno religioso como universal", "evidencia" => "", "eje" => "Antropología religiosa"],
                ["id" => "REL-7-01", "grado" => "septimo", "descripcion" => "Comprende el origen de religiones monoteístas", "evidencia" => "", "eje" => "Antropología religiosa"],
                ["id" => "REL-8-01", "grado" => "octavo", "descripcion" => "Analiza la relación entre religión, ciencia y filosofía", "evidencia" => "", "eje" => "Antropología religiosa"],
                ["id" => "REL-9-01", "grado" => "noveno", "descripcion" => "Analiza la relación entre religión y ética", "evidencia" => "", "eje" => "Antropología religiosa"],
                ["id" => "REL-10-01", "grado" => "decimo", "descripcion" => "Profundiza en el estudio de textos sagrados", "evidencia" => "", "eje" => "Antropología religiosa"],
                ["id" => "REL-11-01", "grado" => "once", "descripcion" => "Sistematiza visión integral del fenómeno religioso", "evidencia" => "", "eje" => "Antropología religiosa"]
            ]
        ],
        "educacion_ambiental" => [
            "nombre" => "Educación Ambiental (Transversal)",
            "categoria" => "Transversal",
            "dbas" => [
                ["id" => "AMB-3-01", "grado" => "tercero", "descripcion" => "Practica las 3R (Reducir, Reutilizar, Reciclar).", "evidencia" => "Separa residuos en contenedores apropiados.", "dimension" => "Acción Ambiental"],
                ["id" => "AMB-5-01", "grado" => "quinto", "descripcion" => "Comprende la importancia del agua como recurso vital.", "evidencia" => "Propone acciones para cuidar fuentes hídricas.", "dimension" => "Conciencia Ambiental"],
                ["id" => "AMB-7-01", "grado" => "sexto", "descripcion" => "Analiza causas del cambio climático.", "evidencia" => "Identifica gases de efecto invernadero y sus fuentes.", "dimension" => "Conciencia Ambiental"],
                ["id" => "AMB-9-01", "grado" => "noveno", "descripcion" => "Evalúa impacto ambiental de actividades humanas.", "evidencia" => "Realiza evaluaciones de impacto simples.", "dimension" => "Acción Ambiental"],
                ["id" => "AMB-11-01", "grado" => "once", "descripcion" => "Propone soluciones sostenibles a problemas ambientales.", "evidencia" => "Desarrolla proyectos de sostenibilidad.", "dimension" => "Acción Ambiental"]
            ]
        ],
        "educacion_sexual" => [
            "nombre" => "Educación para la Sexualidad (Transversal)",
            "categoria" => "Transversal",
            "dbas" => [
                ["id" => "SEX-5-01", "grado" => "quinto", "descripcion" => "Reconoce cambios físicos en la pubertad.", "evidencia" => "Identifica transformaciones corporales normales.", "dimension" => "Autoconocimiento"],
                ["id" => "SEX-7-01", "grado" => "sexto", "descripcion" => "Comprende la reproducción humana.", "evidencia" => "Explica procesos biológicos de reproducción.", "dimension" => "Conocimiento"],
                ["id" => "SEX-9-01", "grado" => "noveno", "descripcion" => "Identifica métodos de prevención de ITS y embarazo.", "evidencia" => "Nombra y describe métodos anticonceptivos.", "dimension" => "Autocuidado"],
                ["id" => "SEX-11-01", "grado" => "once", "descripcion" => "Toma decisiones responsables sobre su sexualidad.", "evidencia" => "Demuestra autonomía en decisiones informadas.", "dimension" => "Autonomía"]
            ]
        ],
        "educacion_economica" => [
            "nombre" => "Educación Económica y Financiera (Transversal)",
            "categoria" => "Transversal",
            "dbas" => [
                ["id" => "ECO-5-01", "grado" => "quinto", "descripcion" => "Comprende el valor del dinero y el ahorro.", "evidencia" => "Administra mesada con criterio de ahorro.", "dimension" => "Educación Financiera"],
                ["id" => "ECO-7-01", "grado" => "sexto", "descripcion" => "Identifica necesidades vs. deseos.", "evidencia" => "Prioriza gastos según importancia.", "dimension" => "Educación Financiera"],
                ["id" => "ECO-9-01", "grado" => "noveno", "descripcion" => "Comprende conceptos básicos de economía (oferta, demanda, inflación).", "evidencia" => "Explica funcionamiento del mercado.", "dimension" => "Conocimiento Económico"],
                ["id" => "ECO-11-01", "grado" => "once", "descripcion" => "Elabora presupuesto personal o familiar.", "evidencia" => "Planifica ingresos y gastos mensualmente.", "dimension" => "Educación Financiera"]
            ]
        ],
        "preescolar" => [
            "nombre" => "Preescolar",
            "categoria" => "Transición",
            "dbas" => [
                ["id" => "PRE-01", "grado" => "transicion", "descripcion" => "Reconoce y nombra letras, números y colores básicos", "evidencia" => "", "dimension" => "Dimensión Cognitiva"],
                ["id" => "PRE-02", "grado" => "transicion", "descripcion" => "Comprende conceptos básicos de tamaño, forma y cantidad", "evidencia" => "", "dimension" => "Dimensión Cognitiva"],
                ["id" => "PRE-03", "grado" => "transicion", "descripcion" => "Identifica y resuelve problemas sencillos de su entorno", "evidencia" => "", "dimension" => "Dimensión Cognitiva"],
                ["id" => "PRE-04", "grado" => "transicion", "descripcion" => "Comprende y sigue instrucciones simples de 2 a 3 pasos", "evidencia" => "", "dimension" => "Dimensión Cognitiva"],
                ["id" => "PRE-05", "grado" => "transicion", "descripcion" => "Desarrolla conciencia fonológica identificando sonidos iniciales", "evidencia" => "", "dimension" => "Dimensión Cognitiva"],
                ["id" => "PRE-06", "grado" => "transicion", "descripcion" => "Expresa ideas y sentimientos mediante lenguaje oral y gestual", "evidencia" => "", "dimension" => "Dimensión Comunicativa"],
                ["id" => "PRE-07", "grado" => "transicion", "descripcion" => "Participa en conversaciones y diálogos respetando turnos", "evidencia" => "", "dimension" => "Dimensión Comunicativa"],
                ["id" => "PRE-08", "grado" => "transicion", "descripcion" => "Comprende mensajes orales en situaciones cotidianas", "evidencia" => "", "dimension" => "Dimensión Comunicativa"],
                ["id" => "PRE-09", "grado" => "transicion", "descripcion" => "Disfruta de la lectura de cuentos y narraciones infantiles", "evidencia" => "", "dimension" => "Dimensión Comunicativa"],
                ["id" => "PRE-10", "grado" => "transicion", "descripcion" => "Realiza trazos y grafías con intención comunicativa", "evidencia" => "", "dimension" => "Dimensión Comunicativa"],
                ["id" => "PRE-11", "grado" => "transicion", "descripcion" => "Desarrolla coordinación motora gruesa (correr, saltar, lanzar)", "evidencia" => "", "dimension" => "Dimensión Corporal"],
                ["id" => "PRE-12", "grado" => "transicion", "descripcion" => "Desarrolla coordinación motora fina (recortar, rasgar, ensartar)", "evidencia" => "", "dimension" => "Dimensión Corporal"],
                ["id" => "PRE-13", "grado" => "transicion", "descripcion" => "Reconoce y cuida su cuerpo practicando hábitos de higiene", "evidencia" => "", "dimension" => "Dimensión Corporal"],
                ["id" => "PRE-14", "grado" => "transicion", "descripcion" => "Participa en juegos y actividades físicas siguiendo reglas básicas", "evidencia" => "", "dimension" => "Dimensión Corporal"],
                ["id" => "PRE-15", "grado" => "transicion", "descripcion" => "Explora diferentes materiales para expresión plástica (pintura, modelado)", "evidencia" => "", "dimension" => "Dimensión Estética y Creativa"],
                ["id" => "PRE-16", "grado" => "transicion", "descripcion" => "Participa en actividades musicales explorando ritmos y canciones", "evidencia" => "", "dimension" => "Dimensión Estética y Creativa"],
                ["id" => "PRE-17", "grado" => "transicion", "descripcion" => "Representa situaciones cotidianas mediante juego dramático", "evidencia" => "", "dimension" => "Dimensión Estética y Creativa"],
                ["id" => "PRE-18", "grado" => "transicion", "descripcion" => "Expresa creativamente sus vivencias a través del arte", "evidencia" => "", "dimension" => "Dimensión Estética y Creativa"],
                ["id" => "PRE-19", "grado" => "transicion", "descripcion" => "Reconoce y expresa emociones básicas de manera adecuada", "evidencia" => "", "dimension" => "Dimensión Socioemocional"],
                ["id" => "PRE-20", "grado" => "transicion", "descripcion" => "Establece relaciones positivas con pares y adultos", "evidencia" => "", "dimension" => "Dimensión Socioemocional"],
                ["id" => "PRE-21", "grado" => "transicion", "descripcion" => "Participa en actividades grupales colaborando con otros niños", "evidencia" => "", "dimension" => "Dimensión Socioemocional"],
                ["id" => "PRE-22", "grado" => "transicion", "descripcion" => "Desarrolla habilidades de autocontrol y resolución pacífica de conflictos", "evidencia" => "", "dimension" => "Dimensión Socioemocional"],
                ["id" => "PRE-23", "grado" => "transicion", "descripcion" => "Muestra respeto y empatía hacia los demás", "evidencia" => "", "dimension" => "Dimensión Socioemocional"],
                ["id" => "PRE-24", "grado" => "transicion", "descripcion" => "Comprende valores y normas básicas de convivencia en el aula", "evidencia" => "", "dimension" => "Dimensión Ética y Valórica"],
                ["id" => "PRE-25", "grado" => "transicion", "descripcion" => "Reconoce y respeta la diversidad cultural y las diferencias", "evidencia" => "", "dimension" => "Dimensión Ética y Valórica"],
                ["id" => "PRE-26", "grado" => "transicion", "descripcion" => "Desarrolla habilidades para compartir, cooperar y ayudar a otros", "evidencia" => "", "dimension" => "Dimensión Ética y Valórica"]
            ]
        ],
        "catedra_paz" => [
            "nombre" => "Cátedra de la Paz",
            "categoria" => "Transversal",
            "dbas" => [
                ["id" => "PAZ-1-01", "grado" => "primero", "descripcion" => "Reconoce la paz como valor fundamental en la convivencia cotidiana", "evidencia" => "", "eje" => "Cultura de Paz"],
                ["id" => "PAZ-2-01", "grado" => "segundo", "descripcion" => "Comprende la importancia del perdón y la reconciliación", "evidencia" => "", "eje" => "Cultura de Paz"],
                ["id" => "PAZ-3-01", "grado" => "tercero", "descripcion" => "Reconoce la importancia de los derechos humanos", "evidencia" => "", "eje" => "Cultura de Paz"],
                ["id" => "PAZ-4-01", "grado" => "cuarto", "descripcion" => "Comprende el conflicto como oportunidad de aprendizaje", "evidencia" => "", "eje" => "Cultura de Paz"],
                ["id" => "PAZ-5-01", "grado" => "quinto", "descripcion" => "Analiza la relación entre justicia, equidad y paz", "evidencia" => "", "eje" => "Cultura de Paz"],
                ["id" => "PAZ-6-01", "grado" => "sexto", "descripcion" => "Comprende el concepto de paz positiva", "evidencia" => "", "eje" => "Cultura de Paz"],
                ["id" => "PAZ-7-01", "grado" => "septimo", "descripcion" => "Analiza el conflicto armado colombiano", "evidencia" => "", "eje" => "Cultura de Paz"],
                ["id" => "PAZ-8-01", "grado" => "octavo", "descripcion" => "Analiza el Acuerdo de Paz con las FARC-EP (2016)", "evidencia" => "", "eje" => "Cultura de Paz"],
                ["id" => "PAZ-9-01", "grado" => "noveno", "descripcion" => "Analiza los desafíos actuales para la paz en Colombia", "evidencia" => "", "eje" => "Cultura de Paz"],
                ["id" => "PAZ-10-01", "grado" => "decimo", "descripcion" => "Profundiza en teorías de paz y conflicto", "evidencia" => "", "eje" => "Cultura de Paz"],
                ["id" => "PAZ-11-01", "grado" => "once", "descripcion" => "Sistematiza comprensión integral del conflicto y paz", "evidencia" => "", "eje" => "Cultura de Paz"]
            ]
        ],
        "formacion_ciudadana" => [
            "nombre" => "Competencias Ciudadanas",
            "categoria" => "Transversal",
            "dbas" => [
                ["id" => "CIU-1-01", "grado" => "primero", "descripcion" => "Reconoce sus derechos como niño y niña", "evidencia" => "", "competencia" => "Convivencia y Paz"],
                ["id" => "CIU-6-01", "grado" => "sexto", "descripcion" => "Comprende conceptos fundamentales de ciudadanía", "evidencia" => "", "competencia" => "Participación"],
                ["id" => "CIU-11-01", "grado" => "once", "descripcion" => "Sistematiza comprensión integral de la ciudadanía", "evidencia" => "", "competencia" => "Liderazgo"]
            ]
        ],
        "catedra_afrocolombiana" => [
            "nombre" => "Cátedra de Estudios Afrocolombianos",
            "categoria" => "Transversal",
            "dbas" => [
                ["id" => "AFR-1-01", "grado" => "primero", "descripcion" => "Reconoce la diversidad cultural de Colombia como riqueza", "evidencia" => "", "eje" => "Identidad"],
                ["id" => "AFR-6-01", "grado" => "sexto", "descripcion" => "Analiza el proceso histórico de la trata transatlántica", "evidencia" => "", "eje" => "Historia"],
                ["id" => "AFR-11-01", "grado" => "once", "descripcion" => "Sistematiza comprensión integral de aportes afrocolombianos", "evidencia" => "", "eje" => "Aportes"]
            ]
        ]
    ],
    "grados" => [
        "primaria" => ["primero", "segundo", "tercero", "cuarto", "quinto"],
        "bachillerato" => ["sexto", "sexto", "octavo", "noveno", "decimo", "once"]
    ],
    "competencias_transversales" => [
        "competencias_ciudadanas" => [
            "Convivencia y paz",
            "Participación y responsabilidad democrática",
            "Pluralidad, identidad y valoración de las diferencias"
        ],
        "competencias_laborales_generales" => [
            "Intelectuales",
            "Personales",
            "Sociales",
            "Organizacionales",
            "Tecnológicas",
            "Empresariales y para el emprendimiento"
        ],
        "competencias_socioemocionales" => [
            "Autoconocimiento",
            "Autorregulación",
            "Empatía",
            "Habilidades sociales",
            "Toma de decisiones responsables"
        ]
    ]
];

// Manejo de parámetros adicionales (id de dba específico)
$id = isset($_GET['id']) ? strtoupper($_GET['id']) : ($input['id'] ?? null);
// $area y $grado ya vienen normalizados y mapeados del inicio del script

if ($id) {
    // Buscar DBA específico por ID original (`codigo`)
    $found = null;
    foreach ($dbaData['areas'] as $areaKey => $areaData) {
        foreach ($areaData['dbas'] as $dba) {
            if ($dba['id'] === $id) {
                $found = [
                    'id' => "dba_{$dba['id']}",
                    'tipo' => 'DBA',
                    'codigo' => $dba['id'],
                    'descripcion' => $dba['descripcion'],
                    'metadata' => [
                        'grado' => $dba['grado'],
                        'area' => $areaData['nombre'],
                        'dimension' => $dba['dimension'] ?? null,
                        'evidencia' => $dba['evidencia'] ?? null
                    ]
                ];
                break 2;
            }
        }
    }
    $response = $found ? ["status" => "success", "data" => [$found]] : ["status" => "error", "message" => "DBA no encontrado"];
} elseif ($area && isset($dbaData['areas'][$area])) {
    if ($grado) {
        $filteredDBAs = array_filter(
            $dbaData['areas'][$area]['dbas'],
            function ($dba) use ($grado) {
                return $dba['grado'] === $grado;
            }
        );
        $mappedDBAs = array_map(function ($dba) use ($dbaData, $area) {
            return [
                'id' => "dba_{$dba['id']}",
                'tipo' => 'DBA',
                'codigo' => $dba['id'],
                'descripcion' => $dba['descripcion'],
                'metadata' => [
                    'grado' => $dba['grado'],
                    'area' => $dbaData['areas'][$area]['nombre'],
                    'dimension' => $dba['dimension'] ?? null,
                    'evidencia' => $dba['evidencia'] ?? null
                ]
            ];
        }, array_values($filteredDBAs));
        $response = [
            "status" => "success",
            "area" => $dbaData['areas'][$area]['nombre'],
            "grado" => $grado,
            "count" => count($filteredDBAs),
            "data" => $mappedDBAs
        ];
    } else {
        $mappedDBAs = array_map(function ($dba) use ($dbaData, $area) {
            return [
                'id' => "dba_{$dba['id']}",
                'tipo' => 'DBA',
                'codigo' => $dba['id'],
                'descripcion' => $dba['descripcion'],
                'metadata' => [
                    'grado' => $dba['grado'],
                    'area' => $dbaData['areas'][$area]['nombre'],
                    'dimension' => $dba['dimension'] ?? null,
                    'evidencia' => $dba['evidencia'] ?? null
                ]
            ];
        }, $dbaData['areas'][$area]['dbas']);
        $response = [
            "status" => "success",
            "area" => $dbaData['areas'][$area]['nombre'],
            "count" => count($dbaData['areas'][$area]['dbas']),
            "data" => $mappedDBAs
        ];
    }
} else {
    $response = [
        "status" => "success",
        "message" => "Use POST {area, grado} o GET ?area=nombre&grado=grado para filtrar",
        "areas_disponibles" => array_keys($dbaData['areas']),
        "total_dbas" => $dbaData['metadata']['total_dbas']
    ];
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>