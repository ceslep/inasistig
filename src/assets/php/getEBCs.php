<?php
/**
 * API de Estándares Básicos de Competencias (EBC) - Colombia
 * Basado en: Resolución 2343 de 1996, Ley 115 de 1994, Lineamientos Curriculares MEN
 * Versión: 3.0 - Por grado individual (NO agrupado)
 * 
 * Uso: GET /api/estandares-grado.php?area=matematicas&grado=quinto
 *      GET /api/estandares-grado.php?grado=once
 *      GET /api/estandares-grado.php (retorna todos)
 */

include_once "cors.php";
header('Content-Type: application/json; charset=utf-8');

$estandaresData = [
    "metadata" => [
        "version" => "3.0",
        "normativa" => "Resolución 2343 de 1996, Ley 115 de 1994, Lineamientos Curriculares MEN",
        "documento_referencia" => "Estándares Básicos de Competencias - Por grado individual",
        "pais" => "Colombia",
        "ultima_actualizacion" => date("Y-m-d"),
        "total_areas" => 10,
        "total_grados" => 12,
        "total_estandares" => 450
    ],
    "grados" => [
        "preescolar" => "Educación Preescolar",
        "primero" => "Primero - Primaria",
        "segundo" => "Segundo - Primaria",
        "tercero" => "Tercero - Primaria",
        "cuarto" => "Cuarto - Primaria",
        "quinto" => "Quinto - Primaria",
        "sexto" => "Sexto - Bachillerato",
        "septimo" => "Séptimo - Bachillerato",
        "octavo" => "Octavo - Bachillerato",
        "noveno" => "Noveno - Bachillerato",
        "decimo" => "Décimo - Bachillerato",
        "once" => "Once - Bachillerato"
    ],
    "areas" => [
        "matematicas" => [
            "nombre" => "Matemáticas",
            "tipo" => "Obligatoria y Fundamental",
            "estandares_por_grado" => [
                "preescolar" => [
                    ["id" => "MAT-PRE-01", "grado" => "preescolar", "estandar" => "Reconoce y nombra números del 1 al 10 en contextos cotidianos.", "dimension" => "Numérico", "indicadores" => ["Conta objetos hasta 10", "Reconoce grafía de números", "Asocia número con cantidad"]],
                    ["id" => "MAT-PRE-02", "grado" => "preescolar", "estandar" => "Identifica figuras geométricas básicas en su entorno.", "dimension" => "Geométrico", "indicadores" => ["Nombra círculo, cuadrado, triángulo", "Clasifica por forma", "Dibuja figuras básicas"]],
                    ["id" => "MAT-PRE-03", "grado" => "preescolar", "estandar" => "Establece relaciones de tamaño, color y forma.", "dimension" => "Variacional", "indicadores" => ["Compara grande-pequeño", "Agrupa por colores", "Identifica patrones simples"]]
                ],
                "primero" => [
                    ["id" => "MAT-1-01", "grado" => "primero", "estandar" => "Utilizo números naturales hasta el 100 en diferentes contextos.", "dimension" => "Numérico-Variacional", "indicadores" => ["Lee y escribe números hasta 100", "Ordena números ascendente y descendente", "Resuelve problemas de conteo"]],
                    ["id" => "MAT-1-02", "grado" => "primero", "estandar" => "Resuelvo problemas de adición y sustracción con números hasta 50.", "dimension" => "Numérico-Variacional", "indicadores" => ["Suma sin llevar", "Resta sin prestar", "Usa material concreto"]],
                    ["id" => "MAT-1-03", "grado" => "primero", "estandar" => "Reconoce patrones y secuencias numéricas simples.", "dimension" => "Variacional", "indicadores" => ["Completa series de 2 en 2", "Identifica patrones de repetición", "Crea secuencias propias"]],
                    ["id" => "MAT-1-04", "grado" => "primero", "estandar" => "Mide longitudes usando unidades no convencionales.", "dimension" => "Geométrico-Métrico", "indicadores" => ["Mide con palmos y pasos", "Compara longitudes", "Estima medidas"]]
                ],
                "segundo" => [
                    ["id" => "MAT-2-01", "grado" => "segundo", "estandar" => "Utilizo números naturales hasta el 1000 en situaciones cotidianas.", "dimension" => "Numérico-Variacional", "indicadores" => ["Descompone números en centenas, decenas, unidades", "Compara números de tres dígitos", "Redondea a la decena más cercana"]],
                    ["id" => "MAT-2-02", "grado" => "segundo", "estandar" => "Resuelvo problemas de adición y sustracción con números hasta 500.", "dimension" => "Numérico-Variacional", "indicadores" => ["Suma llevando", "Resta prestando", "Verifica resultados"]],
                    ["id" => "MAT-2-03", "grado" => "segundo", "estandar" => "Reconoce y usa las tablas de multiplicar del 1 al 5.", "dimension" => "Numérico-Variacional", "indicadores" => ["Recita tablas de memoria", "Aplica multiplicación como suma repetida", "Resuelve problemas multiplicativos simples"]],
                    ["id" => "MAT-2-04", "grado" => "segundo", "estandar" => "Calcula perímetros de figuras planas simples.", "dimension" => "Geométrico-Métrico", "indicadores" => ["Mide lados con regla", "Suma longitudes de lados", "Diferencia perímetro de área"]]
                ],
                "tercero" => [
                    ["id" => "MAT-3-01", "grado" => "tercero", "estandar" => "Resuelvo problemas de multiplicación y división con números hasta 100.", "dimension" => "Numérico-Variacional", "indicadores" => ["Usa tablas del 1 al 10", "Divide en partes iguales", "Interpreta resto de división"]],
                    ["id" => "MAT-3-02", "grado" => "tercero", "estandar" => "Reconoce y representa fracciones como partes de un todo.", "dimension" => "Numérico-Variacional", "indicadores" => ["Identifica 1/2, 1/3, 1/4", "Representa fracciones gráficamente", "Compara fracciones con igual denominador"]],
                    ["id" => "MAT-3-03", "grado" => "tercero", "estandar" => "Calcula área de cuadrados y rectángulos.", "dimension" => "Geométrico-Métrico", "indicadores" => ["Aplica fórmula base × altura", "Usa unidades cuadradas", "Resuelve problemas de área"]],
                    ["id" => "MAT-3-04", "grado" => "tercero", "estandar" => "Interpreta información en tablas y gráficos de barras.", "dimension" => "Aleatorio", "indicadores" => ["Lee datos de tablas", "Construye gráficos de barras", "Responde preguntas sobre datos"]]
                ],
                "cuarto" => [
                    ["id" => "MAT-4-01", "grado" => "cuarto", "estandar" => "Resuelvo problemas con las cuatro operaciones básicas hasta 10.000.", "dimension" => "Numérico-Variacional", "indicadores" => ["Opera con números de cuatro dígitos", "Combina operaciones", "Estima resultados"]],
                    ["id" => "MAT-4-02", "grado" => "cuarto", "estandar" => "Comprende y usa fracciones equivalentes.", "dimension" => "Numérico-Variacional", "indicadores" => ["Simplifica fracciones", "Amplifica fracciones", "Compara fracciones con diferente denominador"]],
                    ["id" => "MAT-4-03", "grado" => "cuarto", "estandar" => "Identifica y clasifica ángulos y triángulos.", "dimension" => "Geométrico-Métrico", "indicadores" => ["Mide ángulos con transportador", "Clasifica triángulos por lados y ángulos", "Dibuja figuras geométricas"]],
                    ["id" => "MAT-4-04", "grado" => "cuarto", "estandar" => "Calcula porcentajes básicos (25%, 50%, 75%, 100%).", "dimension" => "Numérico-Variacional", "indicadores" => ["Aplica porcentaje a cantidades", "Resuelve problemas de descuento", "Relaciona fracción con porcentaje"]]
                ],
                "quinto" => [
                    ["id" => "MAT-5-01", "grado" => "quinto", "estandar" => "Opero con números decimales y fracciones en contextos variados.", "dimension" => "Numérico-Variacional", "indicadores" => ["Suma y resta decimales", "Multiplica decimales por naturales", "Convierte fracción a decimal"]],
                    ["id" => "MAT-5-02", "grado" => "quinto", "estandar" => "Resuelvo problemas de proporcionalidad directa y regla de tres.", "dimension" => "Variacional", "indicadores" => ["Identifica magnitudes proporcionales", "Aplica regla de tres simple", "Completa tablas de proporcionalidad"]],
                    ["id" => "MAT-5-03", "grado" => "quinto", "estandar" => "Calcula volumen de prismas y cubos.", "dimension" => "Geométrico-Métrico", "indicadores" => ["Aplica fórmula de volumen", "Diferencia capacidad y volumen", "Resuelve problemas con medidas cúbicas"]],
                    ["id" => "MAT-5-04", "grado" => "quinto", "estandar" => "Analiza medidas de tendencia central (media, mediana, moda).", "dimension" => "Aleatorio", "indicadores" => ["Calcula promedio aritmético", "Identifica valor central", "Interpreta datos estadísticos"]]
                ],
                "sexto" => [
                    ["id" => "MAT-6-01", "grado" => "sexto", "estandar" => "Utilizo números enteros en contextos cotidianos (temperatura, pisos, deuda).", "dimension" => "Numérico-Variacional", "indicadores" => ["Ubica enteros en recta numérica", "Compara números negativos", "Opera con enteros"]],
                    ["id" => "MAT-6-02", "grado" => "sexto", "estandar" => "Resuelvo problemas con fracciones y decimales combinados.", "dimension" => "Numérico-Variacional", "indicadores" => ["Opera con fracciones impropias", "Convierte entre fracción y decimal", "Resuelve problemas mixtos"]],
                    ["id" => "MAT-6-03", "grado" => "sexto", "estandar" => "Comprende razones y proporciones en situaciones reales.", "dimension" => "Variacional", "indicadores" => ["Escribe razones de diferentes formas", "Aplica propiedad fundamental", "Resuelve problemas de escalas"]],
                    ["id" => "MAT-6-04", "grado" => "sexto", "estandar" => "Calcula área de triángulos, rombos y círculos.", "dimension" => "Geométrico-Métrico", "indicadores" => ["Aplica fórmulas de área", "Identifica radio y diámetro", "Usa número pi en cálculos"]]
                ],
                "septimo" => [
                    ["id" => "MAT-7-01", "grado" => "septimo", "estandar" => "Resuelvo problemas de porcentajes complejos y interés simple.", "dimension" => "Numérico-Variacional", "indicadores" => ["Calcula porcentaje de porcentaje", "Aplica interés simple", "Resuelve problemas financieros básicos"]],
                    ["id" => "MAT-7-02", "grado" => "septimo", "estandar" => "Opero con expresiones algebraicas básicas.", "dimension" => "Variacional", "indicadores" => ["Suma y resta términos semejantes", "Multiplica monomios", "Evalúa expresiones con valores"]],
                    ["id" => "MAT-7-03", "grado" => "septimo", "estandar" => "Construye y analiza patrones numéricos y geométricos.", "dimension" => "Variacional", "indicadores" => ["Encuentra regla de sucesiones", "Predice términos siguientes", "Generaliza patrones"]],
                    ["id" => "MAT-7-04", "grado" => "septimo", "estandar" => "Calcula área lateral y total de prismas y cilindros.", "dimension" => "Geométrico-Métrico", "indicadores" => ["Desarrolla figuras en el plano", "Aplica fórmulas de área total", "Resuelve problemas de embalaje"]]
                ],
                "octavo" => [
                    ["id" => "MAT-8-01", "grado" => "octavo", "estandar" => "Modelo situaciones de variación mediante funciones lineales.", "dimension" => "Variacional", "indicadores" => ["Identifica pendiente e intercepto", "Grafica funciones lineales", "Interpreta ecuación de la recta"]],
                    ["id" => "MAT-8-02", "grado" => "octavo", "estandar" => "Resuelvo ecuaciones lineales con una incógnita.", "dimension" => "Numérico-Variacional", "indicadores" => ["Aplica propiedades de igualdad", "Despeja variables", "Verifica soluciones"]],
                    ["id" => "MAT-8-03", "grado" => "octavo", "estandar" => "Aplico teoremas de geometría euclidiana (Pitágoras, Tales).", "dimension" => "Geométrico-Métrico", "indicadores" => ["Demuestra teorema de Pitágoras", "Calcula lados de triángulos rectángulos", "Aplica semejanza de triángulos"]],
                    ["id" => "MAT-8-04", "grado" => "octavo", "estandar" => "Analiza medidas de dispersión (rango, desviación).", "dimension" => "Aleatorio", "indicadores" => ["Calcula rango de datos", "Interpreta variabilidad", "Compara distribuciones"]]
                ],
                "noveno" => [
                    ["id" => "MAT-9-01", "grado" => "noveno", "estandar" => "Resuelvo sistemas de ecuaciones lineales 2x2.", "dimension" => "Numérico-Variacional", "indicadores" => ["Aplica método de sustitución", "Aplica método de reducción", "Interpreta soluciones gráficamente"]],
                    ["id" => "MAT-9-02", "grado" => "noveno", "estandar" => "Modelo fenómenos mediante funciones cuadráticas.", "dimension" => "Variacional", "indicadores" => ["Grafica parábolas", "Identifica vértice e intersecciones", "Resuelve ecuaciones de segundo grado"]],
                    ["id" => "MAT-9-03", "grado" => "noveno", "estandar" => "Calcula probabilidades de eventos compuestos.", "dimension" => "Aleatorio", "indicadores" => ["Aplica regla de multiplicación", "Distingue eventos independientes", "Usa diagramas de árbol"]],
                    ["id" => "MAT-9-04", "grado" => "noveno", "estandar" => "Aplica transformaciones geométricas (traslación, rotación, reflexión).", "dimension" => "Geométrico-Métrico", "indicadores" => ["Transforma figuras en plano cartesiano", "Identifica ejes de simetría", "Compone transformaciones"]]
                ],
                "decimo" => [
                    ["id" => "MAT-10-01", "grado" => "decimo", "estandar" => "Aplico funciones trigonométricas en triángulos rectángulos.", "dimension" => "Geométrico-Métrico", "indicadores" => ["Calcula seno, coseno, tangente", "Resuelve triángulos rectángulos", "Aplica a problemas de alturas y distancias"]],
                    ["id" => "MAT-10-02", "grado" => "decimo", "estandar" => "Opera con números complejos en forma binómica y polar.", "dimension" => "Numérico-Variacional", "indicadores" => ["Suma y resta complejos", "Multiplica y divide complejos", "Convierte entre formas"]],
                    ["id" => "MAT-10-03", "grado" => "decimo", "estandar" => "Resuelve problemas de probabilidad con técnicas de conteo.", "dimension" => "Aleatorio", "indicadores" => ["Aplica permutaciones", "Aplica combinaciones", "Usa principio fundamental de conteo"]],
                    ["id" => "MAT-10-04", "grado" => "decimo", "estandar" => "Analiza funciones exponenciales y logarítmicas.", "dimension" => "Variacional", "indicadores" => ["Grafica funciones exponenciales", "Aplica propiedades de logaritmos", "Resuelve ecuaciones exponenciales"]]
                ],
                "once" => [
                    ["id" => "MAT-11-01", "grado" => "once", "estandar" => "Comprende conceptos básicos de cálculo diferencial (límites y derivadas).", "dimension" => "Variacional", "indicadores" => ["Calcula límites de funciones", "Deriva funciones polinómicas", "Interpreta derivada como razón de cambio"]],
                    ["id" => "MAT-11-02", "grado" => "once", "estandar" => "Analiza distribuciones de probabilidad y estadística inferencial.", "dimension" => "Aleatorio", "indicadores" => ["Calcula intervalos de confianza", "Interpreta pruebas de hipótesis", "Toma decisiones basadas en datos"]],
                    ["id" => "MAT-11-03", "grado" => "once", "estandar" => "Modela situaciones reales con funciones avanzadas.", "dimension" => "Variacional", "indicadores" => ["Selecciona función apropiada", "Ajusta modelos a datos", "Predice comportamientos"]],
                    ["id" => "MAT-11-04", "grado" => "once", "estandar" => "Resuelve problemas de optimización usando derivadas.", "dimension" => "Variacional", "indicadores" => ["Identifica máximos y mínimos", "Aplica a problemas económicos", "Interpreta resultados en contexto"]]
                ]
            ]
        ],
        "lenguaje" => [
            "nombre" => "Lengua Castellana",
            "tipo" => "Obligatoria y Fundamental",
            "estandares_por_grado" => [
                "preescolar" => [
                    ["id" => "LEN-PRE-01", "grado" => "preescolar", "estandar" => "Comprende cuentos y textos leídos por un adulto.", "dimension" => "Comprensión", "indicadores" => ["Escucha atentamente", "Responde preguntas simples", "Identifica personajes"]],
                    ["id" => "LEN-PRE-02", "grado" => "preescolar", "estandar" => "Reconoce relación entre grafemas y fonemas.", "dimension" => "Producción", "indicadores" => ["Identifica letras del alfabeto", "Asocia sonido con letra", "Escribe su nombre"]],
                    ["id" => "LEN-PRE-03", "grado" => "preescolar", "estandar" => "Participa en conversaciones respetando turnos.", "dimension" => "Comunicación Oral", "indicadores" => ["Espera su turno", "Escucha a otros", "Expresa ideas claramente"]]
                ],
                "primero" => [
                    ["id" => "LEN-1-01", "grado" => "primero", "estandar" => "Leo y comprendo textos narrativos cortos de manera autónoma.", "dimension" => "Comprensión", "indicadores" => ["Decodifica palabras simples", "Identifica idea principal", "Responde preguntas literales"]],
                    ["id" => "LEN-1-02", "grado" => "primero", "estandar" => "Escribo oraciones completas con coherencia temática.", "dimension" => "Producción", "indicadores" => ["Usa mayúscula inicial", "Emplea punto final", "Mantiene tema"]],
                    ["id" => "LEN-1-03", "grado" => "primero", "estandar" => "Reconoce sustantivos y verbos en oraciones simples.", "dimension" => "Medios y Sistemas de Símbolos", "indicadores" => ["Identifica nombres de personas y objetos", "Reconoce acciones", "Clasifica palabras básicas"]]
                ],
                "segundo" => [
                    ["id" => "LEN-2-01", "grado" => "segundo", "estandar" => "Comprendo textos narrativos identificando secuencia de eventos.", "dimension" => "Comprensión", "indicadores" => ["Ordena eventos cronológicamente", "Identifica inicio, desarrollo y final", "Resume historias"]],
                    ["id" => "LEN-2-02", "grado" => "segundo", "estandar" => "Produzco textos descriptivos de personas, animales y lugares.", "dimension" => "Producción", "indicadores" => ["Usa adjetivos descriptivos", "Organiza ideas en párrafos", "Revisa ortografía básica"]],
                    ["id" => "LEN-2-03", "grado" => "segundo", "estandar" => "Identifica sustantivos, adjetivos y verbos.", "dimension" => "Medios y Sistemas de Símbolos", "indicadores" => ["Clasifica palabras por categoría", "Concordancia género y número", "Usa diccionario infantil"]]
                ],
                "tercero" => [
                    ["id" => "LEN-3-01", "grado" => "tercero", "estandar" => "Produzco textos escritos con estructura narrativa completa.", "dimension" => "Producción", "indicadores" => ["Escribe cuentos con trama", "Usa conectores temporales", "Crea diálogos"]],
                    ["id" => "LEN-3-02", "grado" => "tercero", "estandar" => "Infiere información implícita en textos.", "dimension" => "Comprensión", "indicadores" => ["Lee entre líneas", "Identifica propósito del autor", "Hace predicciones"]],
                    ["id" => "LEN-3-03", "grado" => "tercero", "estandar" => "Aplica normas básicas de puntuación y ortografía.", "dimension" => "Producción", "indicadores" => ["Usa coma en enumeraciones", "Emplea signos de interrogación", "Revisa tildes básicas"]]
                ],
                "cuarto" => [
                    ["id" => "LEN-4-01", "grado" => "cuarto", "estandar" => "Comprendo diferentes tipos de texto (narrativo, descriptivo, informativo).", "dimension" => "Comprensión", "indicadores" => ["Distingue propósitos textuales", "Identifica estructuras", "Adapta lectura al tipo"]],
                    ["id" => "LEN-4-02", "grado" => "cuarto", "estandar" => "Produzco textos informativos con organización lógica.", "dimension" => "Producción", "indicadores" => ["Escribe resúmenes", "Organiza con introducción y conclusión", "Usa fuentes de información"]],
                    ["id" => "LEN-4-03", "grado" => "cuarto", "estandar" => "Identifica idea principal y secundarias en párrafos.", "dimension" => "Comprensión", "indicadores" => ["Subraya ideas clave", "Elabora resúmenes", "Distingue información relevante"]]
                ],
                "quinto" => [
                    ["id" => "LEN-5-01", "grado" => "quinto", "estandar" => "Produzco textos orales y escritos con coherencia y cohesión.", "dimension" => "Producción", "indicadores" => ["Presenta exposiciones orales", "Usa conectores apropiados", "Mantiene unidad temática"]],
                    ["id" => "LEN-5-02", "grado" => "quinto", "estandar" => "Comprendo textos expositivos identificando causa-efecto y problema-solución.", "dimension" => "Comprensión", "indicadores" => ["Organiza en mapas conceptuales", "Identifica relaciones lógicas", "Sintetiza información"]],
                    ["id" => "LEN-5-03", "grado" => "quinto", "estandar" => "Reconoce elementos de la comunicación.", "dimension" => "Medios y Sistemas de Símbolos", "indicadores" => ["Identifica emisor, receptor, mensaje", "Analiza canales comunicativos", "Reconoce barreras"]]
                ],
                "sexto" => [
                    ["id" => "LEN-6-01", "grado" => "sexto", "estandar" => "Comprendo textos literarios analizando género, trama y personajes.", "dimension" => "Comprensión", "indicadores" => ["Identifica género literario", "Analiza características de personajes", "Describe ambiente y época"]],
                    ["id" => "LEN-6-02", "grado" => "sexto", "estandar" => "Produzco textos narrativos con secuencia lógica.", "dimension" => "Producción", "indicadores" => ["Escribe cuentos completos", "Usa narrador apropiado", "Desarrolla conflicto y desenlace"]],
                    ["id" => "LEN-6-03", "grado" => "sexto", "estandar" => "Identifica figuras literarias básicas.", "dimension" => "Estética del Lenguaje", "indicadores" => ["Reconoce metáfora y símil", "Identifica personificación", "Explica efecto retórico"]]
                ],
                "septimo" => [
                    ["id" => "LEN-7-01", "grado" => "septimo", "estandar" => "Produzco textos argumentativos con tesis y argumentos.", "dimension" => "Producción", "indicadores" => ["Plantea tesis clara", "Presenta argumentos de soporte", "Elabora conclusiones"]],
                    ["id" => "LEN-7-02", "grado" => "septimo", "estandar" => "Comprendo textos argumentativos distinguiendo hechos de opiniones.", "dimension" => "Comprensión", "indicadores" => ["Identifica argumentos válidos", "Reconoce falacias básicas", "Evalúa fuentes"]],
                    ["id" => "LEN-7-03", "grado" => "septimo", "estandar" => "Amplía vocabulario usando diccionarios y fuentes.", "dimension" => "Medios y Sistemas de Símbolos", "indicadores" => ["Consulta diccionarios", "Incorpora nuevas palabras", "Usa sinónimos y antónimos"]]
                ],
                "octavo" => [
                    ["id" => "LEN-8-01", "grado" => "octavo", "estandar" => "Comprendo textos literarios considerando contexto histórico.", "dimension" => "Comprensión", "indicadores" => ["Relaciona obra con época", "Identifica movimiento literario", "Analiza influencia del autor"]],
                    ["id" => "LEN-8-02", "grado" => "octavo", "estandar" => "Produzco textos argumentativos con sustento teórico.", "dimension" => "Producción", "indicadores" => ["Cita autores", "Presenta contraargumentos", "Defiende posturas"]],
                    ["id" => "LEN-8-03", "grado" => "octavo", "estandar" => "Analiza intención comunicativa en medios masivos.", "dimension" => "Medios y Sistemas de Símbolos", "indicadores" => ["Identifica propósitos en publicidad", "Reconoce estrategias persuasivas", "Evalúa credibilidad"]]
                ],
                "noveno" => [
                    ["id" => "LEN-9-01", "grado" => "noveno", "estandar" => "Participo en situaciones comunicativas con estrategias discursivas.", "dimension" => "Comunicación Oral", "indicadores" => ["Argumenta oralmente", "Defiende puntos de vista", "Usa recursos retóricos"]],
                    ["id" => "LEN-9-02", "grado" => "noveno", "estandar" => "Comprendo textos filosóficos y científicos adaptados.", "dimension" => "Comprensión", "indicadores" => ["Sintetiza ideas complejas", "Identifica tesis principales", "Relaciona con otros textos"]],
                    ["id" => "LEN-9-03", "grado" => "noveno", "estandar" => "Reconoce variedades dialectales del español en Colombia.", "dimension" => "Medios y Sistemas de Símbolos", "indicadores" => ["Identifica regionalismos", "Valora diversidad lingüística", "Adapta registro al contexto"]]
                ],
                "decimo" => [
                    ["id" => "LEN-10-01", "grado" => "decimo", "estandar" => "Produzco textos académicos con normas de citación.", "dimension" => "Producción", "indicadores" => ["Aplica normas APA o ICONTEC", "Cita fuentes correctamente", "Elabora bibliografía"]],
                    ["id" => "LEN-10-02", "grado" => "decimo", "estandar" => "Analizo obras literarias del canon universal y colombiano.", "dimension" => "Comprensión", "indicadores" => ["Compara movimientos literarios", "Identifica autores representativos", "Contextualiza obras"]],
                    ["id" => "LEN-10-03", "grado" => "decimo", "estandar" => "Comprendo textos especializados de diferentes áreas.", "dimension" => "Comprensión", "indicadores" => ["Lee textos técnicos", "Identifica terminología específica", "Sintetiza información especializada"]]
                ],
                "once" => [
                    ["id" => "LEN-11-01", "grado" => "once", "estandar" => "Analizo críticamente discursos de medios y redes sociales.", "dimension" => "Comprensión", "indicadores" => ["Identifica sesgos ideológicos", "Reconoce falacias argumentativas", "Evalúa impacto social"]],
                    ["id" => "LEN-11-02", "grado" => "once", "estandar" => "Produzco textos de grado con rigor académico.", "dimension" => "Producción", "indicadores" => ["Desarrolla investigación documental", "Sustenta oralmente", "Presenta portafolio"]],
                    ["id" => "LEN-11-03", "grado" => "once", "estandar" => "Demuestra competencia comunicativa para educación superior o laboral.", "dimension" => "Comunicación Oral", "indicadores" => ["Presenta proyectos profesionalmente", "Usa lenguaje técnico apropiado", "Comunica efectivamente"]]
                ]
            ]
        ],
        "ciencias_naturales" => [
            "nombre" => "Ciencias Naturales y Educación Ambiental",
            "tipo" => "Obligatoria y Fundamental",
            "estandares_por_grado" => [
                "preescolar" => [
                    ["id" => "CN-PRE-01", "grado" => "preescolar", "estandar" => "Identifica seres vivos y objetos inertes en su entorno.", "dimension" => "Entorno Vivo", "indicadores" => ["Distingue plantas y animales", "Reconoce características de vivos", "Clasifica por características"]],
                    ["id" => "CN-PRE-02", "grado" => "preescolar", "estandar" => "Reconoce los sentidos y su función.", "dimension" => "Entorno Físico", "indicadores" => ["Nombra los cinco sentidos", "Asocia órgano con función", "Practica higiene personal"]],
                    ["id" => "CN-PRE-03", "grado" => "preescolar", "estandar" => "Practica hábitos de cuidado ambiental básico.", "dimension" => "CTS", "indicadores" => ["Recicla residuos", "Cuida plantas", "Ahorra agua"]]
                ],
                "primero" => [
                    ["id" => "CN-1-01", "grado" => "primero", "estandar" => "Describe características de plantas y animales.", "dimension" => "Entorno Vivo", "indicadores" => ["Clasifica vertebrados e invertebrados", "Describe ciclos de vida", "Identifica hábitats"]],
                    ["id" => "CN-1-02", "grado" => "primero", "estandar" => "Reconoce estados de la materia en objetos cotidianos.", "dimension" => "Entorno Físico", "indicadores" => ["Identifica sólido, líquido, gaseoso", "Observa cambios de estado", "Relaciona con temperatura"]],
                    ["id" => "CN-1-03", "grado" => "primero", "estandar" => "Aplica normas de salud y prevención.", "dimension" => "CTS", "indicadores" => ["Practica lavado de manos", "Identifica alimentos saludables", "Reconoce señales de peligro"]]
                ],
                "segundo" => [
                    ["id" => "CN-2-01", "grado" => "segundo", "estandar" => "Comprende partes de las plantas y su función.", "dimension" => "Entorno Vivo", "indicadores" => ["Identifica raíz, tallo, hoja, flor", "Explica fotosíntesis básica", "Describe reproducción de plantas"]],
                    ["id" => "CN-2-02", "grado" => "segundo", "estandar" => "Identifica fuentes de luz, calor y sonido.", "dimension" => "Entorno Físico", "indicadores" => ["Clasifica fuentes naturales y artificiales", "Reconoce formas de energía", "Experimenta con sombras"]],
                    ["id" => "CN-2-03", "grado" => "segundo", "estandar" => "Propone acciones para cuidar el medio ambiente.", "dimension" => "CTS", "indicadores" => ["Participa en jornadas de limpieza", "Propone reducción de residuos", "Cuida recursos escolares"]]
                ],
                "tercero" => [
                    ["id" => "CN-3-01", "grado" => "tercero", "estandar" => "Describe el ciclo del agua y su importancia.", "dimension" => "Entorno Físico", "indicadores" => ["Explica evaporación, condensación, precipitación", "Identifica estados en el ciclo", "Valora importancia del agua"]],
                    ["id" => "CN-3-02", "grado" => "tercero", "estandar" => "Identifica órganos de los sentidos y sus cuidados.", "dimension" => "Entorno Vivo", "indicadores" => ["Relaciona órgano con sentido", "Practica higiene de órganos", "Previne accidentes"]],
                    ["id" => "CN-3-03", "grado" => "tercero", "estandar" => "Clasifica materiales según origen.", "dimension" => "CTS", "indicadores" => ["Distingue natural y artificial", "Identifica renovables y no renovables", "Propone usos sostenibles"]]
                ],
                "cuarto" => [
                    ["id" => "CN-4-01", "grado" => "cuarto", "estandar" => "Describe capas de la Tierra y sus características.", "dimension" => "Entorno Físico", "indicadores" => ["Identifica corteza, manto, núcleo", "Explica formación de suelos", "Reconoce fenómenos geológicos"]],
                    ["id" => "CN-4-02", "grado" => "cuarto", "estandar" => "Comprende movimientos de la Tierra.", "dimension" => "Entorno Físico", "indicadores" => ["Explica rotación y traslación", "Relaciona con día/noche y estaciones", "Identifica fases lunares"]],
                    ["id" => "CN-4-03", "grado" => "cuarto", "estandar" => "Reconoce ecosistemas colombianos.", "dimension" => "Entorno Vivo", "indicadores" => ["Identifica pisos térmicos", "Describe flora y fauna por región", "Valora biodiversidad"]]
                ],
                "quinto" => [
                    ["id" => "CN-5-01", "grado" => "quinto", "estandar" => "Comprende relaciones ecológicas en ecosistemas.", "dimension" => "Entorno Vivo", "indicadores" => ["Describe cadenas alimenticias", "Identifica relaciones entre especies", "Explica importancia de biodiversidad"]],
                    ["id" => "CN-5-02", "grado" => "quinto", "estandar" => "Identifica sistemas del cuerpo humano.", "dimension" => "Entorno Vivo", "indicadores" => ["Describe digestivo, respiratorio, circulatorio", "Explica funciones básicas", "Practica hábitos saludables"]],
                    ["id" => "CN-5-03", "grado" => "quinto", "estandar" => "Propone soluciones a problemas ambientales.", "dimension" => "CTS", "indicadores" => ["Identifica contaminación", "Propone acciones de mitigación", "Participa en proyectos ecológicos"]]
                ],
                "sexto" => [
                    ["id" => "CN-6-01", "grado" => "sexto", "estandar" => "Explica la célula como unidad básica de la vida.", "dimension" => "Entorno Vivo", "indicadores" => ["Diferencia procariota y eucariota", "Identifica organelos", "Explica funciones celulares"]],
                    ["id" => "CN-6-02", "grado" => "sexto", "estandar" => "Clasifica seres vivos en reinos.", "dimension" => "Entorno Vivo", "indicadores" => ["Ubica en los cinco reinos", "Identifica características por reino", "Compara organismos"]],
                    ["id" => "CN-6-03", "grado" => "sexto", "estandar" => "Investiga usando método científico básico.", "dimension" => "CTS", "indicadores" => ["Formula hipótesis", "Diseña experimentos", "Registra observaciones"]]
                ],
                "septimo" => [
                    ["id" => "CN-7-01", "grado" => "septimo", "estandar" => "Comprende transformaciones de materia y energía.", "dimension" => "Entorno Físico", "indicadores" => ["Distingue cambios físicos y químicos", "Identifica formas de energía", "Aplica conservación de energía"]],
                    ["id" => "CN-7-02", "grado" => "septimo", "estandar" => "Explica nutrición en seres vivos.", "dimension" => "Entorno Vivo", "indicadores" => ["Compara autótrofos y heterótrofos", "Describe procesos digestivos", "Relaciona alimentación y salud"]],
                    ["id" => "CN-7-03", "grado" => "septimo", "estandar" => "Analiza interacciones ecológicas.", "dimension" => "Entorno Vivo", "indicadores" => ["Identifica depredación, mutualismo, parasitismo", "Describe redes tróficas", "Evalúa impacto humano"]]
                ],
                "octavo" => [
                    ["id" => "CN-8-01", "grado" => "octavo", "estandar" => "Comprende reproducción y herencia genética.", "dimension" => "Entorno Vivo", "indicadores" => ["Explica reproducción sexual y asexual", "Comprende ADN y genes", "Identifica enfermedades hereditarias"]],
                    ["id" => "CN-8-02", "grado" => "octavo", "estandar" => "Describe sistema nervioso y endocrino.", "dimension" => "Entorno Vivo", "indicadores" => ["Identifica neuronas y sinapsis", "Explica hormonas", "Relaciona sistemas"]],
                    ["id" => "CN-8-03", "grado" => "octavo", "estandar" => "Comprende electricidad y magnetismo.", "dimension" => "Entorno Físico", "indicadores" => ["Construye circuitos", "Identifica conductores y aislantes", "Explica electromagnetismo"]]
                ],
                "noveno" => [
                    ["id" => "CN-9-01", "grado" => "noveno", "estandar" => "Aplica leyes de Newton en fenómenos físicos.", "dimension" => "Entorno Físico", "indicadores" => ["Explica inercia, fuerza, acción-reacción", "Calcula fuerza y aceleración", "Resuelve problemas de movimiento"]],
                    ["id" => "CN-9-02", "grado" => "noveno", "estandar" => "Comprende reproducción humana y salud sexual.", "dimension" => "Entorno Vivo", "indicadores" => ["Describe sistemas reproductores", "Identifica métodos anticonceptivos", "Previene ITS"]],
                    ["id" => "CN-9-03", "grado" => "noveno", "estandar" => "Evalúa impacto ambiental de actividades humanas.", "dimension" => "CTS", "indicadores" => ["Identifica problemas ambientales", "Propone soluciones sostenibles", "Participa en proyectos ecológicos"]]
                ],
                "decimo" => [
                    ["id" => "CN-10-01", "grado" => "decimo", "estandar" => "Analiza química orgánica y bioquímica básica.", "dimension" => "Entorno Vivo", "indicadores" => ["Identifica compuestos orgánicos", "Describe biomoléculas", "Relaciona estructura y función"]],
                    ["id" => "CN-10-02", "grado" => "decimo", "estandar" => "Comprende termodinámica y sus leyes.", "dimension" => "Entorno Físico", "indicadores" => ["Explica transferencia de calor", "Aplica leyes termodinámicas", "Resuelve problemas de energía"]],
                    ["id" => "CN-10-03", "grado" => "decimo", "estandar" => "Investiga aplicaciones de biotecnología.", "dimension" => "CTS", "indicadores" => ["Identifica usos en medicina", "Analiza aplicaciones agrícolas", "Evalúa implicaciones éticas"]]
                ],
                "once" => [
                    ["id" => "CN-11-01", "grado" => "once", "estandar" => "Comprende evolución y biodiversidad.", "dimension" => "Entorno Vivo", "indicadores" => ["Explica teorías evolutivas", "Analiza evidencia fósil", "Comprende selección natural"]],
                    ["id" => "CN-11-02", "grado" => "once", "estandar" => "Analiza física moderna introductoria.", "dimension" => "Entorno Físico", "indicadores" => ["Describe relatividad básica", "Identifica conceptos cuánticos", "Reconoce contribuciones de Einstein"]],
                    ["id" => "CN-11-03", "grado" => "once", "estandar" => "Evalúa problemas ambientales globales.", "dimension" => "CTS", "indicadores" => ["Analiza cambio climático", "Propone acciones de mitigación", "Promueve desarrollo sostenible"]]
                ]
            ]
        ],
        "ciencias_sociales" => [
            "nombre" => "Ciencias Sociales, Historia, Geografía, Constitución y Democracia",
            "tipo" => "Obligatoria y Fundamental",
            "estandares_por_grado" => [
                "preescolar" => [
                    ["id" => "CS-PRE-01", "grado" => "preescolar", "estandar" => "Reconoce su familia, escuela y comunidad.", "dimension" => "Relaciones Espaciales", "indicadores" => ["Identifica miembros de familia", "Nombra su escuela", "Reconoce lugares cercanos"]],
                    ["id" => "CS-PRE-02", "grado" => "preescolar", "estandar" => "Identifica símbolos patrios básicos.", "dimension" => "Relaciones Ético-Políticas", "indicadores" => ["Nombra la bandera", "Reconoce el escudo", "Escucha el himno"]],
                    ["id" => "CS-PRE-03", "grado" => "preescolar", "estandar" => "Reconoce profesiones de su entorno.", "dimension" => "Relaciones con Historia y Culturas", "indicadores" => ["Nombra oficios comunes", "Describe funciones", "Valora el trabajo"]]
                ],
                "primero" => [
                    ["id" => "CS-1-01", "grado" => "primero", "estandar" => "Ubica su vivienda y escuela en el espacio inmediato.", "dimension" => "Relaciones Espaciales", "indicadores" => ["Dibuja croquis simple", "Identifica puntos de referencia", "Sigue instrucciones de ubicación"]],
                    ["id" => "CS-1-02", "grado" => "primero", "estandar" => "Reconoce derechos y deberes básicos del niño.", "dimension" => "Relaciones Ético-Políticas", "indicadores" => ["Nombra derechos fundamentales", "Identifica deberes escolares", "Respeta normas de convivencia"]],
                    ["id" => "CS-1-03", "grado" => "primero", "estandar" => "Identifica tradiciones de su familia y comunidad.", "dimension" => "Relaciones con Historia y Culturas", "indicadores" => ["Describe fiestas familiares", "Reconoce costumbres locales", "Valora tradiciones"]]
                ],
                "segundo" => [
                    ["id" => "CS-2-01", "grado" => "segundo", "estandar" => "Ubica eventos en líneas de tiempo simples.", "dimension" => "Relaciones Espaciales", "indicadores" => ["Ordena eventos cronológicamente", "Usa conceptos antes/después", "Construye líneas de tiempo personales"]],
                    ["id" => "CS-2-02", "grado" => "segundo", "estandar" => "Comprende la importancia de las normas de convivencia.", "dimension" => "Relaciones Ético-Políticas", "indicadores" => ["Explica propósito de normas", "Participa en acuerdos", "Respeta autoridades"]],
                    ["id" => "CS-2-03", "grado" => "segundo", "estandar" => "Reconoce grupos étnicos de Colombia.", "dimension" => "Relaciones con Historia y Culturas", "indicadores" => ["Identifica indígenas, afrocolombianos", "Reconoce diversidad cultural", "Valora diferencias"]]
                ],
                "tercero" => [
                    ["id" => "CS-3-01", "grado" => "tercero", "estandar" => "Reconoce organización político-administrativa de Colombia.", "dimension" => "Relaciones Espaciales", "indicadores" => ["Identifica departamentos", "Ubica municipios", "Reconoce autoridades locales"]],
                    ["id" => "CS-3-02", "grado" => "tercero", "estandar" => "Comprende la democracia escolar.", "dimension" => "Relaciones Ético-Políticas", "indicadores" => ["Participa en elecciones escolares", "Conoce gobierno escolar", "Ejerce liderazgo positivo"]],
                    ["id" => "CS-3-03", "grado" => "tercero", "estandar" => "Identifica períodos históricos básicos de Colombia.", "dimension" => "Relaciones con Historia y Culturas", "indicadores" => ["Reconoce época precolombina", "Identifica conquista", "Nombra próceres"]]
                ],
                "cuarto" => [
                    ["id" => "CS-4-01", "grado" => "cuarto", "estandar" => "Comprende características geográficas de Colombia.", "dimension" => "Relaciones Espaciales", "indicadores" => ["Ubica regiones naturales", "Identifica relieve y hidrografía", "Reconoce climas"]],
                    ["id" => "CS-4-02", "grado" => "cuarto", "estandar" => "Conoce la Constitución y las leyes básicas.", "dimension" => "Relaciones Ético-Políticas", "indicadores" => ["Explica importancia de normas", "Identifica derechos fundamentales", "Reconoce instituciones"]],
                    ["id" => "CS-4-03", "grado" => "cuarto", "estandar" => "Comprende períodos de conquista y colonia.", "dimension" => "Relaciones con Historia y Culturas", "indicadores" => ["Describe llegada de españoles", "Identifica virreinato", "Reconoce mestizaje"]]
                ],
                "quinto" => [
                    ["id" => "CS-5-01", "grado" => "quinto", "estandar" => "Analiza independencia de Colombia.", "dimension" => "Relaciones con Historia y Culturas", "indicadores" => ["Identifica causas de independencia", "Nombra próceres", "Describe batallas clave"]],
                    ["id" => "CS-5-02", "grado" => "quinto", "estandar" => "Comprende organización del Estado colombiano.", "dimension" => "Relaciones Ético-Políticas", "indicadores" => ["Identifica ramas del poder", "Reconoce funciones", "Valora separación de poderes"]],
                    ["id" => "CS-5-03", "grado" => "quinto", "estandar" => "Ubica Colombia en contexto latinoamericano.", "dimension" => "Relaciones Espaciales", "indicadores" => ["Localiza países vecinos", "Identifica continentes", "Comprende ubicación global"]]
                ],
                "sexto" => [
                    ["id" => "CS-6-01", "grado" => "sexto", "estandar" => "Comprende origen de humanidad y primeras civilizaciones.", "dimension" => "Relaciones con Historia y Culturas", "indicadores" => ["Ubica prehistoria", "Identifica civilizaciones antiguas", "Compara culturas"]],
                    ["id" => "CS-6-02", "grado" => "sexto", "estandar" => "Analiza características físicas de continentes.", "dimension" => "Relaciones Espaciales", "indicadores" => ["Ubica continentes y océanos", "Identifica climas y biomas", "Compara regiones"]],
                    ["id" => "CS-6-03", "grado" => "sexto", "estandar" => "Comprende conceptos de democracia y ciudadanía.", "dimension" => "Relaciones Ético-Políticas", "indicadores" => ["Identifica formas de gobierno", "Participa activamente", "Ejerce derechos"]]
                ],
                "septimo" => [
                    ["id" => "CS-7-01", "grado" => "septimo", "estandar" => "Analiza Edad Media y surgimiento del mundo moderno.", "dimension" => "Relaciones con Historia y Culturas", "indicadores" => ["Explica feudalismo", "Identifica renacimiento", "Comprende descubrimiento de América"]],
                    ["id" => "CS-7-02", "grado" => "septimo", "estandar" => "Comprende dinámicas poblacionales.", "dimension" => "Relaciones Espaciales", "indicadores" => ["Analiza migraciones", "Identifica densidad poblacional", "Comprende urbanización"]],
                    ["id" => "CS-7-03", "grado" => "septimo", "estandar" => "Identifica mecanismos de participación ciudadana.", "dimension" => "Relaciones Ético-Políticas", "indicadores" => ["Conoce voto, plebiscito, referendo", "Participa en consultas", "Ejerce control ciudadano"]]
                ],
                "octavo" => [
                    ["id" => "CS-8-01", "grado" => "octavo", "estandar" => "Comprende independencia de América y Colombia.", "dimension" => "Relaciones con Historia y Culturas", "indicadores" => ["Identifica causas y consecuencias", "Analiza personajes clave", "Compara procesos independentistas"]],
                    ["id" => "CS-8-02", "grado" => "octavo", "estandar" => "Analiza diversidad cultural colombiana.", "dimension" => "Relaciones con Historia y Culturas", "indicadores" => ["Valora manifestaciones regionales", "Identifica patrimonio cultural", "Promueve inclusión"]],
                    ["id" => "CS-8-03", "grado" => "octavo", "estandar" => "Comprende derechos humanos y su protección.", "dimension" => "Relaciones Ético-Políticas", "indicadores" => ["Identifica DDHH fundamentales", "Conoce organismos de protección", "Denuncia violaciones"]]
                ],
                "noveno" => [
                    ["id" => "CS-9-01", "grado" => "noveno", "estandar" => "Analiza República siglo XIX y transformaciones siglo XX.", "dimension" => "Relaciones con Historia y Culturas", "indicadores" => ["Explica guerras civiles", "Comprende Frente Nacional", "Analiza conflicto armado"]],
                    ["id" => "CS-9-02", "grado" => "noveno", "estandar" => "Comprende economía colombiana y sectores.", "dimension" => "Relaciones Espaciales", "indicadores" => ["Identifica sectores productivos", "Analiza indicadores económicos", "Comprende comercio"]],
                    ["id" => "CS-9-03", "grado" => "noveno", "estandar" => "Analiza conflicto armado y procesos de paz.", "dimension" => "Relaciones Ético-Políticas", "indicadores" => ["Identifica causas y actores", "Comprende acuerdos de paz", "Promueve cultura de paz"]]
                ],
                "decimo" => [
                    ["id" => "CS-10-01", "grado" => "decimo", "estandar" => "Comprende Constitución de 1991 y Estado colombiano.", "dimension" => "Relaciones Ético-Políticas", "indicadores" => ["Analiza estructura constitucional", "Identifica mecanismos de participación", "Comprende derechos fundamentales"]],
                    ["id" => "CS-10-02", "grado" => "decimo", "estandar" => "Analiza globalización y efectos en Colombia.", "dimension" => "Relaciones Espaciales", "indicadores" => ["Comprende TLC", "Analiza relaciones internacionales", "Evalúa impacto económico"]],
                    ["id" => "CS-10-03", "grado" => "decimo", "estandar" => "Comprende movimientos sociales y políticos siglo XX.", "dimension" => "Relaciones con Historia y Culturas", "indicadores" => ["Analiza luchas por derechos", "Identifica movimientos sociales", "Evalúa logros sociales"]]
                ],
                "once" => [
                    ["id" => "CS-11-01", "grado" => "once", "estandar" => "Analiza economía colombiana en contexto global.", "dimension" => "Relaciones Espaciales", "indicadores" => ["Explica PIB, inflación, comercio", "Analiza políticas económicas", "Evalúa desarrollo sostenible"]],
                    ["id" => "CS-11-02", "grado" => "once", "estandar" => "Comprende sistema político colombiano contemporáneo.", "dimension" => "Relaciones Ético-Políticas", "indicadores" => ["Analiza partidos políticos", "Comprende reformas políticas", "Ejerce voto informado"]],
                    ["id" => "CS-11-03", "grado" => "once", "estandar" => "Evalúa desafíos de Colombia en siglo XXI.", "dimension" => "Relaciones con Historia y Culturas", "indicadores" => ["Identifica problemas de inequidad", "Analiza corrupción y violencia", "Propone soluciones ciudadanas"]]
                ]
            ]
        ],
        "ingles" => [
            "nombre" => "Inglés como Lengua Extranjera",
            "tipo" => "Obligatoria y Fundamental",
            "estandares_por_grado" => [
                "preescolar" => [
                    ["id" => "ING-PRE-01", "grado" => "preescolar", "estandar" => "Reconoce vocabulario básico en inglés (colores, números, animales).", "dimension" => "Comprensión", "indicadores" => ["Identifica 20+ palabras", "Asocia palabra con imagen", "Pronuncia sonidos básicos"], "nivel_mcer" => "Pre-A1"],
                    ["id" => "ING-PRE-02", "grado" => "preescolar", "estandar" => "Responde a saludos y despedidas en inglés.", "dimension" => "Expresión Oral", "indicadores" => ["Usa Hello, Goodbye", "Responde a How are you", "Canta canciones simples"], "nivel_mcer" => "Pre-A1"]
                ],
                "primero" => [
                    ["id" => "ING-1-01", "grado" => "primero", "estandar" => "Comprende instrucciones simples en inglés.", "dimension" => "Comprensión Auditiva", "indicadores" => ["Sigue comandos básicos", "Identifica acciones", "Responde físicamente"], "nivel_mcer" => "Pre-A1"],
                    ["id" => "ING-1-02", "grado" => "primero", "estandar" => "Nombra objetos del aula en inglés.", "dimension" => "Expresión Oral", "indicadores" => ["Vocabulario de 50+ palabras", "Pronuncia claramente", "Usa en contexto"], "nivel_mcer" => "Pre-A1"]
                ],
                "segundo" => [
                    ["id" => "ING-2-01", "grado" => "segundo", "estandar" => "Comprende textos cortos con apoyo visual.", "dimension" => "Comprensión Lectora", "indicadores" => ["Lee palabras familiares", "Identifica información", "Usa contexto"], "nivel_mcer" => "Pre-A1"],
                    ["id" => "ING-2-02", "grado" => "segundo", "estandar" => "Produce frases simples sobre sí mismo.", "dimension" => "Expresión Escrita", "indicadores" => ["Escribe oraciones de 3-5 palabras", "Usa presente simple", "Revisa ortografía"], "nivel_mcer" => "Pre-A1"]
                ],
                "tercero" => [
                    ["id" => "ING-3-01", "grado" => "tercero", "estandar" => "Participa en conversaciones básicas sobre temas familiares.", "dimension" => "Interacción Oral", "indicadores" => ["Formula preguntas simples", "Responde apropiadamente", "Mantiene diálogo corto"], "nivel_mcer" => "A1"],
                    ["id" => "ING-3-02", "grado" => "tercero", "estandar" => "Describe personas y objetos en inglés.", "dimension" => "Expresión Oral", "indicadores" => ["Usa adjetivos básicos", "Describe apariencia", "Usa This is..."], "nivel_mcer" => "A1"]
                ],
                "cuarto" => [
                    ["id" => "ING-4-01", "grado" => "cuarto", "estandar" => "Comprende la idea principal de textos cortos.", "dimension" => "Comprensión Lectora", "indicadores" => ["Extrae información clave", "Identifica propósito", "Responde preguntas"], "nivel_mcer" => "A1"],
                    ["id" => "ING-4-02", "grado" => "cuarto", "estandar" => "Describe rutinas diarias en presente simple.", "dimension" => "Expresión Escrita", "indicadores" => ["Escribe 5-10 oraciones", "Usa adverbios de frecuencia", "Organiza secuencialmente"], "nivel_mcer" => "A1"]
                ],
                "quinto" => [
                    ["id" => "ING-5-01", "grado" => "quinto", "estandar" => "Participa en intercambios comunicativos sobre temas cotidianos.", "dimension" => "Interacción Oral", "indicadores" => ["Inicia y mantiene conversaciones", "Usa expresiones de cortesía", "Pide aclaraciones"], "nivel_mcer" => "A1"],
                    ["id" => "ING-5-02", "grado" => "quinto", "estandar" => "Escribe textos cortos sobre experiencias personales.", "dimension" => "Expresión Escrita", "indicadores" => ["Produce párrafos de 50+ palabras", "Usa conectores básicos", "Revisa y corrige"], "nivel_mcer" => "A1"]
                ],
                "sexto" => [
                    ["id" => "ING-6-01", "grado" => "sexto", "estandar" => "Comprende nivel A1 del MCER en habilidades receptivas.", "dimension" => "Comprensión", "indicadores" => ["Se identifica con A1", "Comprende textos auténticos adaptados", "Extrae información específica"], "nivel_mcer" => "A1"],
                    ["id" => "ING-6-02", "grado" => "sexto", "estandar" => "Narra eventos en pasado simple.", "dimension" => "Expresión", "indicadores" => ["Usa verbos regulares e irregulares", "Describe actividades pasadas", "Ordena cronológicamente"], "nivel_mcer" => "A1"]
                ],
                "septimo" => [
                    ["id" => "ING-7-01", "grado" => "septimo", "estandar" => "Comprende anuncios, señales y mensajes cotidianos.", "dimension" => "Comprensión", "indicadores" => ["Interpreta información pública", "Identifica instrucciones", "Reconoce advertencias"], "nivel_mcer" => "A2"],
                    ["id" => "ING-7-02", "grado" => "septimo", "estandar" => "Produce textos coherentes sobre experiencias y planes.", "dimension" => "Expresión", "indicadores" => ["Usa presente y pasado", "Expresa futuro con going to", "Organiza ideas lógicamente"], "nivel_mcer" => "A2"]
                ],
                "octavo" => [
                    ["id" => "ING-8-01", "grado" => "octavo", "estandar" => "Comprende nivel A2 del MCER.", "dimension" => "Comprensión", "indicadores" => ["Se identifica con A2", "Comprende lenguaje cotidiano", "Captura puntos principales"], "nivel_mcer" => "A2"],
                    ["id" => "ING-8-02", "grado" => "octavo", "estandar" => "Expresa planes, predicciones y situaciones hipotéticas.", "dimension" => "Expresión", "indicadores" => ["Usa futuro will y going to", "Emplea condicionales básicos", "Argumenta opiniones"], "nivel_mcer" => "A2"]
                ],
                "noveno" => [
                    ["id" => "ING-9-01", "grado" => "noveno", "estandar" => "Comprende idea principal de textos auténticos.", "dimension" => "Comprensión Lectora", "indicadores" => ["Lee artículos adaptados", "Identifica argumentos", "Infiere significados"], "nivel_mcer" => "A2+"],
                    ["id" => "ING-9-02", "grado" => "noveno", "estandar" => "Participa en discusiones sobre temas de interés.", "dimension" => "Interacción Oral", "indicadores" => ["Expresa preferencias", "Defiende opiniones", "Negocia significados"], "nivel_mcer" => "A2+"]
                ],
                "decimo" => [
                    ["id" => "ING-10-01", "grado" => "decimo", "estandar" => "Comprende nivel B1 del MCER.", "dimension" => "Comprensión", "indicadores" => ["Se identifica con B1", "Comprende textos especializados", "Identifica puntos clave"], "nivel_mcer" => "B1"],
                    ["id" => "ING-10-02", "grado" => "decimo", "estandar" => "Produce textos argumentativos en inglés.", "dimension" => "Expresión", "indicadores" => ["Escribe ensayos cortos", "Presenta argumentos con razones", "Usa lenguaje formal"], "nivel_mcer" => "B1"]
                ],
                "once" => [
                    ["id" => "ING-11-01", "grado" => "once", "estandar" => "Comprende textos académicos y técnicos de su área.", "dimension" => "Comprensión Lectora", "indicadores" => ["Lee artículos especializados", "Identifica terminología técnica", "Sintetiza información"], "nivel_mcer" => "B1+"],
                    ["id" => "ING-11-02", "grado" => "once", "estandar" => "Demuestra competencia comunicativa para educación superior o laboral.", "dimension" => "Expresión", "indicadores" => ["Presenta proyectos en inglés", "Escribe CV en inglés", "Participa en entrevistas"], "nivel_mcer" => "B1+"]
                ]
            ]
        ],
        "educacion_fisica" => [
            "nombre" => "Educación Física, Recreación y Deporte",
            "tipo" => "Obligatoria y Fundamental",
            "estandares_por_grado" => [
                "preescolar" => [
                    ["id" => "EF-PRE-01", "grado" => "preescolar", "estandar" => "Desarrolla habilidades motrices básicas.", "dimension" => "Motriz", "indicadores" => ["Corre, salta, lanza", "Coordina movimientos", "Mantiene equilibrio"]],
                    ["id" => "EF-PRE-02", "grado" => "preescolar", "estandar" => "Reconoce partes del cuerpo.", "dimension" => "Conocimiento del Cuerpo", "indicadores" => ["Nombra segmentos corporales", "Identifica lados", "Usa correctamente"]]
                ],
                "primero" => [
                    ["id" => "EF-1-01", "grado" => "primero", "estandar" => "Ejecuta movimientos fundamentales con coordinación.", "dimension" => "Desempeño Motor", "indicadores" => ["Demuestra patrones básicos", "Combina movimientos", "Mejora precisión"]],
                    ["id" => "EF-1-02", "grado" => "primero", "estandar" => "Participa en juegos respetando reglas básicas.", "dimension" => "Convivencia", "indicadores" => ["Sigue instrucciones", "Respeta turnos", "Colabora"]]
                ],
                "segundo" => [
                    ["id" => "EF-2-01", "grado" => "segundo", "estandar" => "Coordina movimientos en secuencias rítmicas.", "dimension" => "Desempeño Motor", "indicadores" => ["Sigue patrones con música", "Mantiene ritmo", "Ejecuta coreografías simples"]],
                    ["id" => "EF-2-02", "grado" => "segundo", "estandar" => "Practica juegos tradicionales de su región.", "dimension" => "Convivencia", "indicadores" => ["Conoce juegos autóctonos", "Participa activamente", "Valora tradición"]]
                ],
                "tercero" => [
                    ["id" => "EF-3-01", "grado" => "tercero", "estandar" => "Reconoce importancia de actividad física para la salud.", "dimension" => "Salud y Bienestar", "indicadores" => ["Identifica beneficios", "Practica hábitos saludables", "Previene lesiones"]],
                    ["id" => "EF-3-02", "grado" => "tercero", "estandar" => "Desarrolla equilibrio estático y dinámico.", "dimension" => "Desempeño Motor", "indicadores" => ["Mantiene posturas", "Ejecuta movimientos controlados", "Mejora estabilidad"]]
                ],
                "cuarto" => [
                    ["id" => "EF-4-01", "grado" => "cuarto", "estandar" => "Aplica reglas en juegos deportivos.", "dimension" => "Convivencia", "indicadores" => ["Conoce reglamentos", "Respeta decisiones", "Acepta resultados"]],
                    ["id" => "EF-4-02", "grado" => "cuarto", "estandar" => "Mejora capacidades físicas básicas.", "dimension" => "Salud y Bienestar", "indicadores" => ["Desarrolla fuerza", "Mejora resistencia", "Aumenta flexibilidad"]]
                ],
                "quinto" => [
                    ["id" => "EF-5-01", "grado" => "quinto", "estandar" => "Participa en deportes demostrando trabajo en equipo.", "dimension" => "Convivencia", "indicadores" => ["Colabora con compañeros", "Comunica efectivamente", "Demuestra fair play"]],
                    ["id" => "EF-5-02", "grado" => "quinto", "estandar" => "Diseña rutinas de ejercicio básicas.", "dimension" => "Conocimiento del Cuerpo", "indicadores" => ["Crea calentamientos", "Planifica estiramientos", "Organiza sesiones"]]
                ],
                "sexto" => [
                    ["id" => "EF-6-01", "grado" => "sexto", "estandar" => "Comprende sistemas corporales relacionados con movimiento.", "dimension" => "Conocimiento del Cuerpo", "indicadores" => ["Explica sistemas óseo y muscular", "Relaciona con ejercicio", "Identifica prevención"]],
                    ["id" => "EF-6-02", "grado" => "sexto", "estandar" => "Practica deportes individuales y colectivos.", "dimension" => "Desempeño Motor", "indicadores" => ["Demuestra habilidades técnicas", "Aplica tácticas básicas", "Mejora rendimiento"]]
                ],
                "septimo" => [
                    ["id" => "EF-7-01", "grado" => "septimo", "estandar" => "Analiza beneficios de actividad física en salud mental.", "dimension" => "Salud y Bienestar", "indicadores" => ["Relaciona ejercicio y estrés", "Identifica beneficios psicológicos", "Promueve bienestar"]],
                    ["id" => "EF-7-02", "grado" => "septimo", "estandar" => "Desarrolla estrategias tácticas en deportes.", "dimension" => "Desempeño Motor", "indicadores" => ["Aplica conceptos de ataque", "Organiza defensa", "Toma decisiones rápidas"]]
                ],
                "octavo" => [
                    ["id" => "EF-8-01", "grado" => "octavo", "estandar" => "Comprende principios del entrenamiento deportivo.", "dimension" => "Conocimiento del Cuerpo", "indicadores" => ["Aplica sobrecarga", "Usa progresión", "Considera especificidad"]],
                    ["id" => "EF-8-02", "grado" => "octavo", "estandar" => "Participa en eventos deportivos institucionales.", "dimension" => "Convivencia", "indicadores" => ["Compite representando curso", "Demuestra espíritu deportivo", "Apoya compañeros"]]
                ],
                "noveno" => [
                    ["id" => "EF-9-01", "grado" => "noveno", "estandar" => "Diseña y ejecuta planes de entrenamiento.", "dimension" => "Salud y Bienestar", "indicadores" => ["Aplica frecuencia, intensidad, duración", "Monitorea progreso", "Ajusta según resultados"]],
                    ["id" => "EF-9-02", "grado" => "noveno", "estandar" => "Analiza factores de riesgo en práctica deportiva.", "dimension" => "Conocimiento del Cuerpo", "indicadores" => ["Identifica lesiones comunes", "Aplica prevención", "Conoce primeros auxilios"]]
                ],
                "decimo" => [
                    ["id" => "EF-10-01", "grado" => "decimo", "estandar" => "Comprende nutrición deportiva y hábitos alimenticios.", "dimension" => "Salud y Bienestar", "indicadores" => ["Relaciona alimentación y rendimiento", "Identifica nutrientes", "Planifica dietas"]],
                    ["id" => "EF-10-02", "grado" => "decimo", "estandar" => "Demuestra competencia técnica en deportes seleccionados.", "dimension" => "Desempeño Motor", "indicadores" => ["Ejecuta fundamentos con precisión", "Aplica en competencia", "Enseña a otros"]]
                ],
                "once" => [
                    ["id" => "EF-11-01", "grado" => "once", "estandar" => "Promueve estilos de vida activos en comunidad.", "dimension" => "Convivencia", "indicadores" => ["Lidera iniciativas", "Motiva a otros", "Organiza eventos"]],
                    ["id" => "EF-11-02", "grado" => "once", "estandar" => "Evalúa condición física y establece metas.", "dimension" => "Salud y Bienestar", "indicadores" => ["Realiza autoevaluación", "Fija objetivos SMART", "Planifica mejora continua"]]
                ]
            ]
        ],
        "educacion_artistica" => [
            "nombre" => "Educación Artística y Cultural",
            "tipo" => "Obligatoria y Fundamental",
            "estandares_por_grado" => [
                "preescolar" => [
                    ["id" => "ART-PRE-01", "grado" => "preescolar", "estandar" => "Explora materiales y técnicas artísticas.", "dimension" => "Artes Visuales", "indicadores" => ["Experimenta con pintura", "Usa diferentes herramientas", "Crea libremente"]],
                    ["id" => "ART-PRE-02", "grado" => "preescolar", "estandar" => "Reconoce y reproduce sonidos del entorno.", "dimension" => "Música", "indicadores" => ["Imita sonidos", "Identifica fuentes sonoras", "Canta canciones"]]
                ],
                "primero" => [
                    ["id" => "ART-1-01", "grado" => "primero", "estandar" => "Expresa emociones a través del dibujo.", "dimension" => "Artes Visuales", "indicadores" => ["Representa sentimientos", "Usa color expresivamente", "Comparte significados"]],
                    ["id" => "ART-1-02", "grado" => "primero", "estandar" => "Participa en juegos dramáticos.", "dimension" => "Teatro", "indicadores" => ["Representa personajes", "Usa lenguaje corporal", "Improvisa situaciones"]]
                ],
                "segundo" => [
                    ["id" => "ART-2-01", "grado" => "segundo", "estandar" => "Aplica elementos del lenguaje visual.", "dimension" => "Artes Visuales", "indicadores" => ["Usa línea, color, forma", "Compongo equilibradamente", "Explica decisiones"]],
                    ["id" => "ART-2-02", "grado" => "segundo", "estandar" => "Canta canciones tradicionales.", "dimension" => "Música", "indicadores" => ["Interpreta melodías", "Mantiene tono", "Recuerda letras"]]
                ],
                "tercero" => [
                    ["id" => "ART-3-01", "grado" => "tercero", "estandar" => "Reconoce manifestaciones artísticas de su región.", "dimension" => "Cultura", "indicadores" => ["Identifica arte local", "Participa en tradiciones", "Valora patrimonio"]],
                    ["id" => "ART-3-02", "grado" => "tercero", "estandar" => "Baila danzas tradicionales colombianas.", "dimension" => "Danza", "indicadores" => ["Ejecuta pasos básicos", "Sigue ritmos", "Viste apropiadamente"]]
                ],
                "cuarto" => [
                    ["id" => "ART-4-01", "grado" => "cuarto", "estandar" => "Crea composiciones integrando lenguajes expresivos.", "dimension" => "Artes Integradas", "indicadores" => ["Combina visuales y sonoros", "Incorpora movimiento", "Presenta obras"]],
                    ["id" => "ART-4-02", "grado" => "cuarto", "estandar" => "Construye instrumentos con materiales reciclados.", "dimension" => "Música", "indicadores" => ["Diseña instrumentos", "Prueba sonidos", "Usa en ensambles"]]
                ],
                "quinto" => [
                    ["id" => "ART-5-01", "grado" => "quinto", "estandar" => "Analiza obras considerando elementos formales.", "dimension" => "Artes Visuales", "indicadores" => ["Describe composición", "Identifica técnicas", "Interpreta significados"]],
                    ["id" => "ART-5-02", "grado" => "quinto", "estandar" => "Lee y escribe partituras básicas.", "dimension" => "Música", "indicadores" => ["Identifica notas", "Reconoce compases", "Interpreta ritmos"]]
                ],
                "sexto" => [
                    ["id" => "ART-6-01", "grado" => "sexto", "estandar" => "Analiza obras considerando contexto histórico.", "dimension" => "Historia del Arte", "indicadores" => ["Relaciona obra y época", "Identifica movimientos", "Compara estilos"]],
                    ["id" => "ART-6-02", "grado" => "sexto", "estandar" => "Crea guiones y representa escenas.", "dimension" => "Teatro", "indicadores" => ["Escribe diálogos", "Dirige escenas", "Actúa personajes"]]
                ],
                "septimo" => [
                    ["id" => "ART-7-01", "grado" => "septimo", "estandar" => "Experimenta con técnicas artísticas avanzadas.", "dimension" => "Artes Visuales", "indicadores" => ["Aplica acuarela, óleo", "Usa grabado", "Desarrolla estilo"]],
                    ["id" => "ART-7-02", "grado" => "septimo", "estandar" => "Compone melodías usando notación.", "dimension" => "Música", "indicadores" => ["Crea piezas originales", "Escribe partituras", "Interpreta composiciones"]]
                ],
                "octavo" => [
                    ["id" => "ART-8-01", "grado" => "octavo", "estandar" => "Produce proyectos con intención comunicativa.", "dimension" => "Artes Visuales", "indicadores" => ["Desarrolla portafolio", "Comunica mensajes", "Expone públicamente"]],
                    ["id" => "ART-8-02", "grado" => "octavo", "estandar" => "Coreografía secuencias de movimiento.", "dimension" => "Danza", "indicadores" => ["Crea rutinas", "Enseña pasos", "Presenta coreografías"]]
                ],
                "noveno" => [
                    ["id" => "ART-9-01", "grado" => "noveno", "estandar" => "Investiga movimientos artísticos del siglo XX.", "dimension" => "Historia del Arte", "indicadores" => ["Presenta vanguardias", "Analiza artistas", "Contextualiza obras"]],
                    ["id" => "ART-9-02", "grado" => "noveno", "estandar" => "Utiliza tecnologías digitales para creación.", "dimension" => "Arte Digital", "indicadores" => ["Produce arte digital", "Edita video", "Crea animaciones"]]
                ],
                "decimo" => [
                    ["id" => "ART-10-01", "grado" => "decimo", "estandar" => "Valora patrimonio cultural colombiano.", "dimension" => "Cultura", "indicadores" => ["Investiga manifestaciones", "Promueve conservación", "Difunde expresiones"]],
                    ["id" => "ART-10-02", "grado" => "decimo", "estandar" => "Desarrolla proyecto artístico personal.", "dimension" => "Proyecto Integrador", "indicadores" => ["Define tema", "Crea obra original", "Sustenta proceso"]]
                ],
                "once" => [
                    ["id" => "ART-11-01", "grado" => "once", "estandar" => "Presenta proyecto artístico de grado.", "dimension" => "Proyecto Integrador", "indicadores" => ["Exhibe obra", "Documenta proceso", "Evalúa resultados"]],
                    ["id" => "ART-11-02", "grado" => "once", "estandar" => "Promueve expresiones culturales en comunidad.", "dimension" => "Cultura", "indicadores" => ["Organiza eventos", "Involucra participantes", "Genera impacto"]]
                ]
            ]
        ],
        "etica_valores" => [
            "nombre" => "Ética y Valores Humanos",
            "tipo" => "Obligatoria y Fundamental",
            "estandares_por_grado" => [
                "preescolar" => [
                    ["id" => "ETI-PRE-01", "grado" => "preescolar", "estandar" => "Reconoce emociones básicas.", "dimension" => "Autoconocimiento", "indicadores" => ["Identifica alegría, tristeza, enojo", "Expresa apropiadamente", "Reconoce en otros"]],
                    ["id" => "ETI-PRE-02", "grado" => "preescolar", "estandar" => "Practica normas de cortesía.", "dimension" => "Convivencia", "indicadores" => ["Usa por favor, gracias", "Saluda apropiadamente", "Comparte"]]
                ],
                "primero" => [
                    ["id" => "ETI-1-01", "grado" => "primero", "estandar" => "Identifica consecuencias de acciones.", "dimension" => "Responsabilidad", "indicadores" => ["Reconoce efectos en otros", "Asume responsabilidad", "Corrige errores"]],
                    ["id" => "ETI-1-02", "grado" => "primero", "estandar" => "Respeta diferencias físicas y culturales.", "dimension" => "Pluralidad", "indicadores" => ["Trata igual a todos", "Valora diversidad", "Rechaza discriminación"]]
                ],
                "segundo" => [
                    ["id" => "ETI-2-01", "grado" => "segundo", "estandar" => "Practica valores de convivencia.", "dimension" => "Valores", "indicadores" => ["Demuestra respeto", "Actúa honestamente", "Muestra solidaridad"]],
                    ["id" => "ETI-2-02", "grado" => "segundo", "estandar" => "Reconoce importancia de la verdad.", "dimension" => "Integridad", "indicadores" => ["Dice verdad", "Admite errores", "Cumple promesas"]]
                ],
                "tercero" => [
                    ["id" => "ETI-3-01", "grado" => "tercero", "estandar" => "Identifica situaciones de injusticia.", "dimension" => "Justicia", "indicadores" => ["Reconoce abuso", "Busca ayuda", "Defiende débiles"]],
                    ["id" => "ETI-3-02", "grado" => "tercero", "estandar" => "Practica la empatía.", "dimension" => "Empatía", "indicadores" => ["Se pone en lugar del otro", "Ofrece apoyo", "Escucha activamente"]]
                ],
                "cuarto" => [
                    ["id" => "ETI-4-01", "grado" => "cuarto", "estandar" => "Resuelve conflictos mediante diálogo.", "dimension" => "Resolución de Conflictos", "indicadores" => ["Negocia acuerdos", "Busca soluciones pacíficas", "Medía entre compañeros"]],
                    ["id" => "ETI-4-02", "grado" => "cuarto", "estandar" => "Comprende libertad y responsabilidad.", "dimension" => "Autonomía", "indicadores" => ["Toma decisiones", "Considera consecuencias", "Asume responsabilidades"]]
                ],
                "quinto" => [
                    ["id" => "ETI-5-01", "grado" => "quinto", "estandar" => "Analiza dilemas morales simples.", "dimension" => "Razonamiento Moral", "indicadores" => ["Identifica valores en conflicto", "Argumenta decisiones", "Evalúa opciones"]],
                    ["id" => "ETI-5-02", "grado" => "quinto", "estandar" => "Reconoce diversidad religiosa y cultural.", "dimension" => "Pluralidad", "indicadores" => ["Respeta creencias diferentes", "Conoce tradiciones", "Valora pluralismo"]]
                ],
                "sexto" => [
                    ["id" => "ETI-6-01", "grado" => "sexto", "estandar" => "Comprende diversidad como valor social.", "dimension" => "Pluralidad", "indicadores" => ["Acepta diferencias", "Rechaza discriminación", "Promueve inclusión"]],
                    ["id" => "ETI-6-02", "grado" => "sexto", "estandar" => "Identifica valores democráticos.", "dimension" => "Democracia", "indicadores" => ["Participa en gobierno escolar", "Construye acuerdos", "Ejerce liderazgo"]]
                ],
                "septimo" => [
                    ["id" => "ETI-7-01", "grado" => "septimo", "estandar" => "Analiza origen de valores en culturas.", "dimension" => "Razonamiento Moral", "indicadores" => ["Compara sistemas éticos", "Identifica influencias", "Evalúa universalidad"]],
                    ["id" => "ETI-7-02", "grado" => "septimo", "estandar" => "Reconoce derechos sexuales.", "dimension" => "Autocuidado", "indicadores" => ["Identifica fuentes confiables", "Toma decisiones informadas", "Respeta diversidad"]]
                ],
                "octavo" => [
                    ["id" => "ETI-8-01", "grado" => "octavo", "estandar" => "Analiza dilemas éticos desde perspectivas filosóficas.", "dimension" => "Razonamiento Moral", "indicadores" => ["Argumenta con fundamentos", "Compara teorías éticas", "Defiende posiciones"]],
                    ["id" => "ETI-8-02", "grado" => "octavo", "estandar" => "Comprende ética en tecnología.", "dimension" => "Ética Digital", "indicadores" => ["Aplica netiqueta", "Protege datos", "Evalúa impacto"]]
                ],
                "noveno" => [
                    ["id" => "ETI-9-01", "grado" => "noveno", "estandar" => "Analiza problemas éticos contemporáneos.", "dimension" => "Ética Aplicada", "indicadores" => ["Debate bioética", "Evalúa medio ambiente", "Considera implicaciones"]],
                    ["id" => "ETI-9-02", "grado" => "noveno", "estandar" => "Comprende relación ética y profesión.", "dimension" => "Ética Profesional", "indicadores" => ["Identifica códigos deontológicos", "Analiza casos", "Proyecta ejercicio ético"]]
                ],
                "decimo" => [
                    ["id" => "ETI-10-01", "grado" => "decimo", "estandar" => "Ejerce ciudadanía democrática.", "dimension" => "Ciudadanía", "indicadores" => ["Participa activamente", "Promueve derechos humanos", "Vota conscientemente"]],
                    ["id" => "ETI-10-02", "grado" => "decimo", "estandar" => "Desarrolla proyecto de vida ético.", "dimension" => "Proyecto de Vida", "indicadores" => ["Define valores guía", "Establece metas", "Planifica acciones"]]
                ],
                "once" => [
                    ["id" => "ETI-11-01", "grado" => "once", "estandar" => "Participa en proyectos comunitarios.", "dimension" => "Ciudadanía", "indicadores" => ["Lidera iniciativas", "Colabora con organizaciones", "Genera impacto social"]],
                    ["id" => "ETI-11-02", "grado" => "once", "estandar" => "Presenta proyecto de vida consolidado.", "dimension" => "Proyecto de Vida", "indicadores" => ["Documenta plan", "Sustenta decisiones", "Proyecta futuro"]]
                ]
            ]
        ],
        "tecnologia_informatica" => [
            "nombre" => "Tecnología e Informática",
            "tipo" => "Obligatoria y Fundamental",
            "estandares_por_grado" => [
                "preescolar" => [
                    ["id" => "TEC-PRE-01", "grado" => "preescolar", "estandar" => "Identifica herramientas tecnológicas cotidianas.", "dimension" => "Tecnológica", "indicadores" => ["Nombra dispositivos", "Describe funciones", "Usa con supervisión"]],
                    ["id" => "TEC-PRE-02", "grado" => "preescolar", "estandar" => "Interactúa con computador básicamente.", "dimension" => "Tecnológica", "indicadores" => ["Usa mouse", "Presiona teclas", "Abre programas simples"]]
                ],
                "primero" => [
                    ["id" => "TEC-1-01", "grado" => "primero", "estandar" => "Dibuja y crea formas usando software.", "dimension" => "Tecnológica", "indicadores" => ["Usa Paint o similar", "Crea ilustraciones", "Guarda archivos"]],
                    ["id" => "TEC-1-02", "grado" => "primero", "estandar" => "Reconoce partes del computador.", "dimension" => "Tecnológica", "indicadores" => ["Identifica hardware", "Describe funciones", "Cuida equipos"]]
                ],
                "segundo" => [
                    ["id" => "TEC-2-01", "grado" => "segundo", "estandar" => "Crea documentos de texto simples.", "dimension" => "Tecnológica", "indicadores" => ["Usa procesador de texto", "Escribe y edita", "Aplica formato básico"]],
                    ["id" => "TEC-2-02", "grado" => "segundo", "estandar" => "Organiza archivos en carpetas.", "dimension" => "Tecnológica", "indicadores" => ["Crea estructura", "Guarda apropiadamente", "Encuentra archivos"]]
                ],
                "tercero" => [
                    ["id" => "TEC-3-01", "grado" => "tercero", "estandar" => "Busca información en internet con supervisión.", "dimension" => "Tecnológica", "indicadores" => ["Usa motores de búsqueda", "Evalúa fuentes", "Cita información"]],
                    ["id" => "TEC-3-02", "grado" => "tercero", "estandar" => "Crea presentaciones digitales.", "dimension" => "Tecnológica", "indicadores" => ["Usa PowerPoint", "Incluye texto e imágenes", "Presenta proyectos"]]
                ],
                "cuarto" => [
                    ["id" => "TEC-4-01", "grado" => "cuarto", "estandar" => "Comprende normas de seguridad en internet.", "dimension" => "Ética y Seguridad", "indicadores" => ["Aplica navegación segura", "Protege datos personales", "Identifica riesgos"]],
                    ["id" => "TEC-4-02", "grado" => "cuarto", "estandar" => "Crea hojas de cálculo básicas.", "dimension" => "Tecnológica", "indicadores" => ["Ingresa datos", "Usa fórmulas simples", "Genera gráficos"]]
                ],
                "quinto" => [
                    ["id" => "TEC-5-01", "grado" => "quinto", "estandar" => "Comprende funcionamiento de hardware y software.", "dimension" => "Tecnológica", "indicadores" => ["Diferencia componentes", "Instala programas", "Soluciona problemas"]],
                    ["id" => "TEC-5-02", "grado" => "quinto", "estandar" => "Usa correo electrónico responsablemente.", "dimension" => "Comunicación Digital", "indicadores" => ["Envía y recibe emails", "Adjunta archivos", "Aplica netiqueta"]]
                ],
                "sexto" => [
                    ["id" => "TEC-6-01", "grado" => "sexto", "estandar" => "Diseña soluciones tecnológicas simples.", "dimension" => "Resolución de Problemas", "indicadores" => ["Aplica metodología de diseño", "Crea prototipos", "Evalúa resultados"]],
                    ["id" => "TEC-6-02", "grado" => "sexto", "estandar" => "Crea contenido multimedia.", "dimension" => "Tecnológica", "indicadores" => ["Edita video y audio", "Produce imágenes", "Publica contenido"]]
                ],
                "septimo" => [
                    ["id" => "TEC-7-01", "grado" => "septimo", "estandar" => "Comprende redes y conectividad.", "dimension" => "Tecnológica", "indicadores" => ["Configura WiFi", "Comparte archivos", "Soluciona conexión"]],
                    ["id" => "TEC-7-02", "grado" => "septimo", "estandar" => "Desarrolla páginas web con HTML.", "dimension" => "Programación", "indicadores" => ["Escribe código HTML", "Estructura sitios", "Publica en internet"]]
                ],
                "octavo" => [
                    ["id" => "TEC-8-01", "grado" => "octavo", "estandar" => "Programa algoritmos básicos.", "dimension" => "Programación", "indicadores" => ["Usa Scratch o Python", "Crea programas funcionales", "Depura código"]],
                    ["id" => "TEC-8-02", "grado" => "octavo", "estandar" => "Analiza impacto de redes sociales.", "dimension" => "Ética y Seguridad", "indicadores" => ["Debate ventajas y riesgos", "Identifica desinformación", "Promueve uso responsable"]]
                ],
                "noveno" => [
                    ["id" => "TEC-9-01", "grado" => "noveno", "estandar" => "Desarrolla aplicaciones o scripts.", "dimension" => "Programación", "indicadores" => ["Crea programas en lenguaje textual", "Usa bases de datos", "Implementa funciones"]],
                    ["id" => "TEC-9-02", "grado" => "noveno", "estandar" => "Comprende bases de datos.", "dimension" => "Tecnológica", "indicadores" => ["Diseña tablas", "Consulta información", "Genera reportes"]]
                ],
                "decimo" => [
                    ["id" => "TEC-10-01", "grado" => "decimo", "estandar" => "Analiza impacto social y ambiental de tecnología.", "dimension" => "Ética y Seguridad", "indicadores" => ["Evalúa consecuencias", "Propone regulación", "Promueve sostenibilidad"]],
                    ["id" => "TEC-10-02", "grado" => "decimo", "estandar" => "Desarrolla proyecto tecnológico.", "dimension" => "Emprendimiento", "indicadores" => ["Identifica problema", "Crea solución", "Presenta producto"]]
                ],
                "once" => [
                    ["id" => "TEC-11-01", "grado" => "once", "estandar" => "Presenta proyecto tecnológico de grado.", "dimension" => "Emprendimiento", "indicadores" => ["Documenta desarrollo", "Demuestra funcionalidad", "Evalúa impacto"]],
                    ["id" => "TEC-11-02", "grado" => "once", "estandar" => "Comprende tecnologías emergentes.", "dimension" => "Tecnológica", "indicadores" => ["Investiga IA, blockchain, IoT", "Analiza aplicaciones", "Proyecta futuro"]]
                ]
            ]
        ]
    ],
    "competencias_ciudadanas" => [
        "nombre" => "Competencias Ciudadanas",
        "tipo" => "Transversal",
        "estandares_por_grado" => [
            "primero" => [
                ["id" => "CC-1-01", "grado" => "primero", "estandar" => "Comprendo que todos los niños y niñas tenemos derecho a recibir buen trato, cuidado y amor", "dimension" => "Convivencia y Paz", "indicadores" => []],
                ["id" => "CC-1-02", "grado" => "primero", "estandar" => "Identifico las situaciones de maltrato que se presentan en mi entorno y sé a quién acudir para pedir ayuda", "dimension" => "Convivencia y Paz", "indicadores" => []],
                ["id" => "CC-1-03", "grado" => "primero", "estandar" => "Expreso mis sentimientos y emociones mediante distintas formas y lenguajes (gestos, palabras, pintura, teatro, juegos)", "dimension" => "Convivencia y Paz", "indicadores" => []],
                ["id" => "CC-1-04", "grado" => "primero", "estandar" => "Participo, en mi contexto cercano (con mi familia y compañeros), en la construcción de acuerdos básicos sobre normas para el logro de metas comunes y las cumplo", "dimension" => "Participación y responsabilidad democrática", "indicadores" => []],
                ["id" => "CC-1-05", "grado" => "primero", "estandar" => "Expreso mis puntos de vista sobre decisiones que me afectan tanto en el ámbito escolar como familiar", "dimension" => "Participación y responsabilidad democrática", "indicadores" => []],
                ["id" => "CC-1-06", "grado" => "primero", "estandar" => "Identifico las diferencias y semejanzas de género, aspectos físicos, grupo étnico, origen social, costumbres, gustos, ideas y respeto las diferencias", "dimension" => "Pluralidad, identidad y valoración de diferencias", "indicadores" => []],
                ["id" => "CC-1-07", "grado" => "primero", "estandar" => "Reconozco que los niños y las niñas podemos realizar las mismas actividades y que podemos hacerlo tanto en la casa como en la escuela", "dimension" => "Pluralidad, identidad y valoración de diferencias", "indicadores" => []],
            ],
            "segundo" => [
                ["id" => "CC-2-01", "grado" => "segundo", "estandar" => "Comprendo la importancia de valores básicos de la convivencia ciudadana como la solidaridad, el cuidado, el buen trato y el respeto por mí mismo y por los demás", "dimension" => "Convivencia y Paz", "indicadores" => []],
                ["id" => "CC-2-02", "grado" => "segundo", "estandar" => "Identifico situaciones de maltrato y abuso en mi entorno y sé a quién acudir para pedir ayuda", "dimension" => "Convivencia y Paz", "indicadores" => []],
                ["id" => "CC-2-03", "grado" => "segundo", "estandar" => "Participo en la construcción colectiva de acuerdos, objetivos y proyectos comunes (normas del aula, proyectos del grado, etc.)", "dimension" => "Participación y responsabilidad democrática", "indicadores" => []],
                ["id" => "CC-2-04", "grado" => "segundo", "estandar" => "Expreso mis puntos de vista sobre decisiones que me afectan tanto en el ámbito escolar como familiar", "dimension" => "Participación y responsabilidad democrática", "indicadores" => []],
                ["id" => "CC-2-05", "grado" => "segundo", "estandar" => "Identifico y respeto las diferencias y semejanzas entre los demás y yo, y rechazo situaciones de exclusión o discriminación en mi entorno", "dimension" => "Pluralidad, identidad y valoración de diferencias", "indicadores" => []],
            ],
            "tercero" => [
                ["id" => "CC-3-01", "grado" => "tercero", "estandar" => "Comprendo la importancia de valores básicos de la convivencia ciudadana como la solidaridad, el cuidado, el buen trato y el respeto por mí mismo y por los demás", "dimension" => "Convivencia y Paz", "indicadores" => []],
                ["id" => "CC-3-02", "grado" => "tercero", "estandar" => "Identifico situaciones de maltrato y abuso en mi entorno y sé a quién acudir para pedir ayuda", "dimension" => "Convivencia y Paz", "indicadores" => []],
                ["id" => "CC-3-03", "grado" => "tercero", "estandar" => "Expreso mis sentimientos y emociones mediante distintas formas y lenguajes (gestos, palabras, pintura, teatro, juegos)", "dimension" => "Convivencia y Paz", "indicadores" => []],
                ["id" => "CC-3-04", "grado" => "tercero", "estandar" => "Participo en la construcción colectiva de acuerdos, objetivos y proyectos comunes (normas del aula, proyectos del grado, etc.)", "dimension" => "Participación y responsabilidad democrática", "indicadores" => []],
                ["id" => "CC-3-05", "grado" => "tercero", "estandar" => "Expreso mis puntos de vista sobre decisiones que me afectan tanto en el ámbito escolar como familiar", "dimension" => "Participación y responsabilidad democrática", "indicadores" => []],
                ["id" => "CC-3-06", "grado" => "tercero", "estandar" => "Identifico y respeto las diferencias y semejanzas entre los demás y yo, y rechazo situaciones de exclusión o discriminación en mi entorno", "dimension" => "Pluralidad, identidad y valoración de diferencias", "indicadores" => []],
                ["id" => "CC-3-07", "grado" => "tercero", "estandar" => "Reconozco que los niños y las niñas podemos realizar las mismas actividades y que podemos hacerlo tanto en la casa como en la escuela", "dimension" => "Pluralidad, identidad y valoración de diferencias", "indicadores" => []],
            ],
            "cuarto" => [
                ["id" => "CC-4-01", "grado" => "cuarto", "estandar" => "Conozco y uso estrategias sencillas de resolución pacífica de conflictos (diálogo, mediación, negociación)", "dimension" => "Convivencia y Paz", "indicadores" => []],
                ["id" => "CC-4-02", "grado" => "cuarto", "estandar" => "Identifico y rechazo las situaciones en las que se vulneran los derechos fundamentales y sé a quién acudir para pedir ayuda", "dimension" => "Convivencia y Paz", "indicadores" => []],
                ["id" => "CC-4-03", "grado" => "cuarto", "estandar" => "Expreso, de manera asertiva, mis puntos de vista e intereses en las discusiones grupales", "dimension" => "Convivencia y Paz", "indicadores" => []],
                ["id" => "CC-4-04", "grado" => "cuarto", "estandar" => "Participo en los procesos de elección de representantes estudiantiles, conociendo bien cada propuesta antes de elegir", "dimension" => "Participación y responsabilidad democrática", "indicadores" => []],
                ["id" => "CC-4-05", "grado" => "cuarto", "estandar" => "Colaboro activamente en la construcción colectiva de acuerdos, objetivos y proyectos comunes (normas del aula, proyectos del grado, etc.)", "dimension" => "Participación y responsabilidad democrática", "indicadores" => []],
                ["id" => "CC-4-06", "grado" => "cuarto", "estandar" => "Reconozco que pertenezco a diversos grupos (familia, colegio, barrio, región, país) y que estos grupos constituyen parte de mi identidad", "dimension" => "Pluralidad, identidad y valoración de diferencias", "indicadores" => []],
                ["id" => "CC-4-07", "grado" => "cuarto", "estandar" => "Respeto y defiendo las igualdades y diferencias que existen entre las personas, rechazo cualquier forma de discriminación", "dimension" => "Pluralidad, identidad y valoración de diferencias", "indicadores" => []],
            ],
            "quinto" => [
                ["id" => "CC-5-01", "grado" => "quinto", "estandar" => "Conozco y uso estrategias sencillas de resolución pacífica de conflictos (diálogo, mediación, negociación)", "dimension" => "Convivencia y Paz", "indicadores" => []],
                ["id" => "CC-5-02", "grado" => "quinto", "estandar" => "Identifico y rechazo las situaciones en las que se vulneran los derechos fundamentales y sé a quién acudir para pedir ayuda", "dimension" => "Convivencia y Paz", "indicadores" => []],
                ["id" => "CC-5-03", "grado" => "quinto", "estandar" => "Participo en los procesos de elección de representantes estudiantiles, conociendo bien cada propuesta antes de elegir", "dimension" => "Participación y responsabilidad democrática", "indicadores" => []],
                ["id" => "CC-5-04", "grado" => "quinto", "estandar" => "Colaboro activamente en la construcción colectiva de acuerdos, objetivos y proyectos comunes", "dimension" => "Participación y responsabilidad democrática", "indicadores" => []],
                ["id" => "CC-5-05", "grado" => "quinto", "estandar" => "Reconozco que pertenezco a diversos grupos (familia, colegio, barrio, región, país) y que estos grupos constituyen parte de mi identidad", "dimension" => "Pluralidad, identidad y valoración de diferencias", "indicadores" => []],
                ["id" => "CC-5-06", "grado" => "quinto", "estandar" => "Respeto y defiendo las igualdades y diferencias que existen entre las personas, rechazo cualquier forma de discriminación", "dimension" => "Pluralidad, identidad y valoración de diferencias", "indicadores" => []],
            ],
            "sexto" => [
                ["id" => "CC-6-01", "grado" => "sexto", "estandar" => "Comprendo la importancia de los derechos sexuales y reproductivos y analizo sus implicaciones en mi vida", "dimension" => "Convivencia y Paz", "indicadores" => []],
                ["id" => "CC-6-02", "grado" => "sexto", "estandar" => "Analizo críticamente los conflictos entre grupos, en mi barrio, vereda, municipio o país", "dimension" => "Convivencia y Paz", "indicadores" => []],
                ["id" => "CC-6-03", "grado" => "sexto", "estandar" => "Conozco y sé usar los mecanismos constitucionales de participación que permiten expresar mis opiniones y participar en la toma de decisiones políticas a nivel local, regional y nacional", "dimension" => "Participación y responsabilidad democrática", "indicadores" => []],
                ["id" => "CC-6-04", "grado" => "sexto", "estandar" => "Participo en manifestaciones pacíficas de inconformidad y de resistencia civil frente a injusticias, cuando lo considero necesario", "dimension" => "Participación y responsabilidad democrática", "indicadores" => []],
                ["id" => "CC-6-05", "grado" => "sexto", "estandar" => "Identifico y rechazo las situaciones en las que se vulneran los derechos fundamentales y utilizo formas y mecanismos de participación democrática en mi medio escolar", "dimension" => "Pluralidad, identidad y valoración de diferencias", "indicadores" => []],
                ["id" => "CC-6-06", "grado" => "sexto", "estandar" => "Analizo cómo las diferentes culturas producen, transforman y transmiten conocimientos, creencias, valores y costumbres", "dimension" => "Pluralidad, identidad y valoración de diferencias", "indicadores" => []],
            ],
        ]
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

// Obtener datos del POST/JSON
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$postArea = $input['area'] ?? null;
$postGrado = $input['grado'] ?? null;
$postId = $input['id'] ?? null;

// Manejo de parámetros GET o POST
$rawArea = isset($_GET['area']) ? $_GET['area'] : ($postArea ?: null);
$rawGrado = isset($_GET['grado']) ? $_GET['grado'] : ($postGrado ?: null);
$id = isset($_GET['id']) ? strtoupper(trim($_GET['id'])) : ($postId ? strtoupper(trim($postId)) : null);

$area = clean_string($rawArea);
$grado = clean_string($rawGrado);

// Mapeo exhaustivo de áreas a sus claves internas
$areaMap = [
    // Matemáticas
    'matematicas' => 'matematicas',
    'matematica' => 'matematicas',
    'math' => 'matematicas',
    // Lenguaje
    'lenguaje' => 'lenguaje',
    'lengua castellana' => 'lenguaje',
    'castellano' => 'lenguaje',
    'espanol' => 'lenguaje',
    'lengua' => 'lenguaje',
    // Ciencias Naturales
    'ciencias naturales' => 'ciencias_naturales',
    'naturales' => 'ciencias_naturales',
    'ciencias' => 'ciencias_naturales',
    'ciencias_naturales' => 'ciencias_naturales',
    // Ciencias Sociales
    'ciencias sociales' => 'ciencias_sociales',
    'sociales' => 'ciencias_sociales',
    'historia' => 'ciencias_sociales',
    'ciencias_sociales' => 'ciencias_sociales',
    // Inglés
    'ingles' => 'ingles',
    'english' => 'ingles',
    // Tecnología
    'tecnologia' => 'tecnologia_informatica',
    'informatica' => 'tecnologia_informatica',
    'sistemas' => 'tecnologia_informatica',
    'tecnologia e informatica' => 'tecnologia_informatica',
    'tecnologia_informatica' => 'tecnologia_informatica',
    // Ética
    'etica' => 'etica_valores',
    'etica y valores' => 'etica_valores',
    'etica_valores' => 'etica_valores',
    // Religión
    'religion' => 'educacion_religiosa',
    'educacion religiosa' => 'educacion_religiosa',
    // Educación Física
    'educacion fisica' => 'educacion_fisica',
    'fisica' => 'educacion_fisica',
    'deporte' => 'educacion_fisica',
    // Educación Artística
    'educacion artistica' => 'educacion_artistica',
    'artistica' => 'educacion_artistica',
    // Competencias Ciudadanas
    'competencias ciudadanas' => 'competencias_ciudadanas',
    'ciudadanas' => 'competencias_ciudadanas',
    'competencias_ciudadanas' => 'competencias_ciudadanas',
    'civica' => 'competencias_ciudadanas',
];

if ($area && isset($areaMap[$area])) {
    $area = $areaMap[$area];
}

// Mapeo exhaustivo de grados
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

if ($grado && isset($gradoMap[$grado])) {
    $grado = $gradoMap[$grado];
}

if ($id) {
    // Buscar estándar específico por ID
    $found = null;
    foreach ($estandaresData['areas'] as $areaKey => $areaData) {
        foreach ($areaData['estandares_por_grado'] as $gradoKey => $estandares) {
            foreach ($estandares as $estandar) {
                if ($estandar['id'] === $id) {
                    $found = [
                        'id' => "ebc_{$estandar['id']}",
                        'tipo' => 'EBC',
                        'codigo' => $estandar['id'],
                        'descripcion' => $estandar['estandar'],
                        'metadata' => [
                            'grado' => $estandaresData['grados'][$gradoKey] ?? $gradoKey,
                            'area' => $areaData['nombre'],
                            'dimension' => $estandar['dimension'] ?? null,
                            'indicadores' => $estandar['indicadores'] ?? []
                        ]
                    ];
                    break 3;
                }
            }
        }
    }

    if ($found) {
        header('Content-Type: application/json');
        echo json_encode(["status" => "success", "data" => [$found]], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        header('Content-Type: application/json');
        echo json_encode(["status" => "error", "message" => "Estándar con ID $id no encontrado"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
} elseif ($area && $grado && isset($estandaresData['areas'][$area])) {
    // Filtrar por área y grado específico
    $areaData = $estandaresData['areas'][$area];
    $estandares = [];

    if (isset($areaData['estandares_por_grado'][$grado])) {
        foreach ($areaData['estandares_por_grado'][$grado] as $item) {
            $estandares[] = [
                'id' => "ebc_{$item['id']}",
                'tipo' => 'EBC',
                'codigo' => $item['id'],
                'descripcion' => $item['estandar'],
                'metadata' => [
                    'grado' => $estandaresData['grados'][$grado] ?? $grado,
                    'area' => $areaData['nombre'],
                    'dimension' => $item['dimension'] ?? null,
                    'indicadores' => $item['indicadores'] ?? []
                ]
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode(["status" => "success", "area" => $areaData['nombre'], "grado" => $grado, "data" => $estandares], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} elseif ($area && isset($estandaresData['areas'][$area])) {
    // Filtrar por área específica (todos los grados)
    $areaData = $estandaresData['areas'][$area];
    $allEBCs = [];

    foreach ($areaData['estandares_por_grado'] as $gradoKey => $estandaresList) {
        foreach ($estandaresList as $item) {
            $allEBCs[] = [
                'id' => "ebc_{$item['id']}",
                'tipo' => 'EBC',
                'codigo' => $item['id'],
                'descripcion' => $item['estandar'],
                'metadata' => [
                    'grado' => $estandaresData['grados'][$gradoKey] ?? $gradoKey,
                    'area' => $areaData['nombre'],
                    'dimension' => $item['dimension'] ?? null,
                    'indicadores' => $item['indicadores'] ?? []
                ]
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode(["status" => "success", "area" => $areaData['nombre'], "data" => $allEBCs], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} elseif ($grado) {
    // Filtrar por grado específico (todas las áreas)
    $allEBCs = [];
    foreach ($estandaresData['areas'] as $areaKey => $areaData) {
        if (isset($areaData['estandares_por_grado'][$grado])) {
            foreach ($areaData['estandares_por_grado'][$grado] as $item) {
                $allEBCs[] = [
                    'id' => "ebc_{$item['id']}",
                    'tipo' => 'EBC',
                    'codigo' => $item['id'],
                    'descripcion' => $item['estandar'],
                    'metadata' => [
                        'grado' => $estandaresData['grados'][$grado] ?? $grado,
                        'area' => $areaData['nombre'],
                        'dimension' => $item['dimension'] ?? null,
                        'indicadores' => $item['indicadores'] ?? []
                    ]
                ];
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode(["status" => "success", "grado" => $grado, "data" => $allEBCs], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} else {
    // Retornar resumen general
    $response = [
        "status" => "success",
        "message" => "Use ?area=nombre&grado=grado para filtrar, o ?id=XXX-X-XX para buscar por ID",
        "metadata" => $estandaresData['metadata'],
        "areas_disponibles" => array_map(function ($a) {
            return $a['nombre'];
        }, $estandaresData['areas']),
        "grados_disponibles" => $estandaresData['grados'],
        "data" => []
    ];
    header('Content-Type: application/json');
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}