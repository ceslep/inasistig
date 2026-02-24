<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Estructura base solicitada
$datos = [];

// -----------------------------------------------------------------------------
// CICLO 1: GRADOS 1° A 3° - LENGUAJE (DBA)
// Factores: Producción textual, Comprensión textual, Literatura, Medios, Ética
// -----------------------------------------------------------------------------
$dba_lenguaje_1_3 = [
    // Producción textual
    "Produzco textos escritos que responden a diversas necesidades comunicativas.",
    "Produzco textos orales que responden a distintos propósitos comunicativos.",
    "Utilizo, de acuerdo con el contexto, un vocabulario adecuado para expresar mis ideas.",
    "Expreso en forma clara mis ideas y sentimientos, según lo amerite la situación comunicativa.",
    "Utilizo la entonación y los matices afectivos de voz para alcanzar mi propósito en diferentes situaciones comunicativas.",
    "Describo personas, objetos, lugares, etc., en forma detallada.",
    "Describo eventos de manera secuencial.",
    "Elaboro instrucciones que evidencian secuencias lógicas en la realización de acciones.",
    "Expongo y defiendo mis ideas en función de la situación comunicativa.",

    // Comprensión e interpretación textual
    "Leo diferentes clases de textos: manuales, tarjetas, afiches, cartas, periódicos, etc.",
    "Reconozco la función social de los diversos tipos de textos que leo.",
    "Identifico la silueta o el formato de los textos que leo.",
    "Elaboro hipótesis acerca del sentido global de los textos, antes y durante el proceso de lectura.",
    "Identifico el propósito comunicativo y la idea global de un texto.",
    "Elaboro resúmenes y esquemas que dan cuenta del sentido de un texto.",
    "Comparo textos de acuerdo con sus formatos, temáticas y funciones.",

    // Literatura
    "Leo fábulas, cuentos, poemas, relatos mitológicos, leyendas, o cualquier otro texto literario.",
    "Elaboro y socializo hipótesis predictivas acerca del contenido de los textos.",
    "Identifico maneras de cómo se formula el inicio y el final de algunas narraciones.",
    "Diferencio poemas, cuentos y obras de teatro.",
    "Recreo relatos y cuentos cambiando personajes, ambientes, hechos y épocas.",
    "Participo en la elaboración de guiones para teatro de títeres.",

    // Medios de comunicación y otros sistemas simbólicos
    "Identifico los diversos medios de comunicación masiva con los que interactúo.",
    "Caracterizo algunos medios de comunicación: radio, televisión, prensa, entre otros.",
    "Comento mis programas favoritos de televisión o radio.",
    "Identifico la información que emiten los medios de comunicación masiva y la forma de presentarla.",
    "Establezco diferencias y semejanzas entre noticieros, telenovelas, anuncios comerciales, dibujos animados, caricaturas, entre otros.",
    "Utilizo los medios de comunicación masiva para adquirir información e incorporarla de manera significativa a mis esquemas de conocimiento.",
    "Entiendo el lenguaje empleado en historietas y otros tipos de textos con imágenes fijas.",
    "Expongo oralmente lo que me dicen mensajes cifrados en pictogramas, jeroglíficos, etc.",
    "Reconozco la temática de caricaturas, tiras cómicas, historietas, anuncios publicitarios y otros medios de expresión gráfica.",

    // Ética de la comunicación
    "Reconozco los principales elementos constitutivos de un proceso de comunicación: interlocutores, código, canal, texto y situación comunicativa.",
    "Establezco semejanzas y diferencias entre quien produce el texto y quien lo interpreta.",
    "Identifico en situaciones comunicativas reales los roles de quien produce y de quien interpreta un texto.",
    "Identifico la intención de quien produce un texto."
];

// -----------------------------------------------------------------------------
// CICLO 2: GRADOS 4° A 5° - LENGUAJE (DBA)
// -----------------------------------------------------------------------------
$dba_lenguaje_4_5 = [
    // Producción textual
    "Produzco textos orales, en situaciones comunicativas que permiten evidenciar el uso significativo de la entonación y la pertinencia articulatoria.",
    "Produzco textos escritos que responden a diversas necesidades comunicativas y que siguen un procedimiento estratégico para su elaboración.",
    "Organizo mis ideas para producir un texto oral, teniendo en cuenta mi realidad y mis propias experiencias.",
    "Elaboro un plan para la exposición de mis ideas.",
    "Selecciono el léxico apropiado y acomodo mi estilo al plan de exposición así como al contexto comunicativo.",
    "Adecuo la entonación y la pronunciación a las exigencias de las situaciones comunicativas en que participo.",
    "Produzco un texto oral, teniendo en cuenta la entonación, la articulación y la organización de ideas que requiere la situación comunicativa.",
    "Elijo un tema para producir un texto escrito, teniendo en cuenta un propósito, las características del interlocutor y las exigencias del contexto.",
    "Diseño un plan para elaborar un texto informativo.",
    "Produzco la primera versión de un texto informativo, atendiendo a requerimientos formales y conceptuales de la producción escrita en lengua castellana.",
    "Reescribo el texto a partir de las propuestas de corrección formuladas por mis compañeros y por mí.",

    // Comprensión e interpretación textual
    "Leo diversos tipos de texto: descriptivo, informativo, narrativo, explicativo y argumentativo.",
    "Comprendo los aspectos formales y conceptuales al interior de cada texto leído.",
    "Identifico la intención comunicativa de cada uno de los textos leídos.",
    "Determino algunas estrategias para buscar, seleccionar y almacenar información: resúmenes, cuadros sinópticos, mapas conceptuales y fichas.",
    "Establezco diferencias y semejanzas entre las estrategias de búsqueda, selección y almacenamiento de información.",
    "Utilizo estrategias de búsqueda, selección y almacenamiento de información para mis procesos de producción y comprensión textual.",

    // Literatura
    "Leo diversos tipos de texto literario: relatos mitológicos, leyendas, cuentos, fábulas, poemas y obras teatrales.",
    "Reconozco, en los textos literarios que leo, elementos tales como tiempo, espacio, acción, personajes.",
    "Propongo hipótesis predictivas acerca de un texto literario, partiendo de aspectos como título, tipo de texto, época de la producción, etc.",
    "Relaciono las hipótesis predictivas que surgen de los textos que leo, con su contexto y con otros textos, sean literarios o no.",
    "Comparo textos narrativos, líricos y dramáticos, teniendo en cuenta algunos de sus elementos constitutivos.",
    "Elaboro hipótesis de lectura acerca de las relaciones entre los elementos constitutivos de un texto literario, y entre éste y el contexto.",

    // Medios de comunicación y otros sistemas simbólicos
    "Reconozco las características de los diferentes medios de comunicación masiva.",
    "Selecciono y clasifico la información emitida por los diferentes medios de comunicación.",
    "Elaboro planes textuales con la información seleccionada de los medios de comunicación.",
    "Produzco textos orales y escritos con base en planes en los que utilizo la información recogida de los medios.",
    "Socializo, analizo y corrijo los textos producidos con base en la información tomada de los medios de comunicación masiva.",
    "Entiendo las obras no verbales como productos de las comunidades humanas.",
    "Doy cuenta de algunas estrategias empleadas para comunicar a través del lenguaje no verbal.",
    "Explico el sentido que tienen mensajes no verbales en mi contexto: señales de tránsito, indicios, banderas, colores, etc.",
    "Reconozco y uso códigos no verbales en situaciones comunicativas auténticas.",

    // Ética de la comunicación
    "Identifico los elementos constitutivos de la comunicación: interlocutores, código, canal, mensaje y contextos.",
    "Caracterizo los roles desempeñados por los sujetos que participan del proceso comunicativo.",
    "Tengo en cuenta, en mis interacciones comunicativas, principios básicos de la comunicación: reconocimiento del otro en tanto interlocutor válido y respeto por los turnos conversacionales.",
    "Identifico en situaciones comunicativas reales los roles, las intenciones de los interlocutores y el respeto por los principios básicos de la comunicación.",
    "Conozco y analizo los elementos, roles, relaciones y reglas básicas de la comunicación, para inferir las intenciones y expectativas de mis interlocutores y hacer más eficaces mis procesos comunicativos."
];

// -----------------------------------------------------------------------------
// CICLO 3: GRADOS 6° A 7° - LENGUAJE (DBA)
// -----------------------------------------------------------------------------
$dba_lenguaje_6_7 = [
    // Producción textual
    "Conozco y utilizo algunas estrategias argumentativas que posibilitan la construcción de textos orales en situaciones comunicativas auténticas.",
    "Produzco textos escritos que responden a necesidades específicas de comunicación, a procedimientos sistemáticos de elaboración y establezco nexos intertextuales y extratextuales.",
    "Defino una temática para la elaboración de un texto oral con fines argumentativos.",
    "Formulo una hipótesis para demostrarla en un texto oral con fines argumentativos.",
    "Llevo a cabo procedimientos de búsqueda, selección y almacenamiento de información acerca de la temática que voy a tratar en un texto con fines argumentativos.",
    "Elaboro un plan textual, jerarquizando la información que he obtenido de fuentes diversas.",
    "Caracterizo estrategias argumentativas de tipo descriptivo.",
    "Utilizo estrategias descriptivas para producir un texto oral con fines argumentativos.",
    "Defino una temática para la producción de un texto narrativo.",
    "Llevo a cabo procedimientos de búsqueda, selección y almacenamiento de información acerca de la temática que voy a tratar en mi texto narrativo.",
    "Elaboro un plan textual, organizando la información en secuencias lógicas.",
    "Produzco una primera versión del texto narrativo teniendo en cuenta personajes, espacio, tiempos y vínculos con otros textos y con mi entorno.",
    "Reescribo un texto, teniendo en cuenta aspectos de coherencia y cohesión.",

    // Comprensión e interpretación textual
    "Comprendo e interpreto diversos tipos de texto, para establecer sus relaciones internas y su clasificación en una tipología textual.",
    "Reconozco las características de los diversos tipos de texto que leo.",
    "Propongo hipótesis de interpretación para cada uno de los tipos de texto que he leído.",
    "Identifico las principales características formales del texto: formato de presentación, títulos, graficación, capítulos, organización, etc.",
    "Comparo el contenido de los diferentes tipos de texto que he leído.",
    "Relaciono la forma y el contenido de los textos que leo y muestro cómo se influyen mutuamente.",
    "Establezco relaciones de semejanza y diferencia entre los diversos tipos de texto que he leído.",

    // Literatura
    "Reconozco la tradición oral como fuente de la conformación y desarrollo de la literatura.",
    "Comprendo obras literarias de diferentes géneros, propiciando así el desarrollo de mi capacidad crítica y creativa.",
    "Interpreto y clasifico textos provenientes de la tradición oral tales como coplas, leyendas, relatos mitológicos, canciones, proverbios, refranes, parábolas, entre otros.",
    "Caracterizo rasgos específicos que consolidan la tradición oral, como: origen, autoría colectiva, función social, uso del lenguaje, evolución, recurrencias temáticas, etc.",
    "Identifico en la tradición oral el origen de los géneros literarios fundamentales: lírico, narrativo y dramático.",
    "Establezco relaciones entre los textos provenientes de la tradición oral y otros textos en cuanto a temas, personajes, lenguaje, entre otros aspectos.",
    "Leo obras literarias de género narrativo, lírico y dramático, de diversa temática, época y región.",
    "Comprendo elementos constitutivos de obras literarias, tales como tiempo, espacio, función de los personajes, lenguaje, atmósferas, diálogos, escenas, entre otros.",
    "Reconozco en las obras literarias procedimientos narrativos, líricos y dramáticos.",
    "Comparo los procedimientos narrativos, líricos o dramáticos empleados en la literatura que permiten estudiarla por géneros.",
    "Formulo hipótesis de comprensión acerca de las obras literarias que leo teniendo en cuenta género, temática, época y región.",

    // Medios de comunicación y otros sistemas simbólicos
    "Caracterizo los medios de comunicación masiva y selecciono la información que emiten para clasificarla y almacenarla.",
    "Relaciono de manera intertextual obras que emplean el lenguaje no verbal y obras que emplean el lenguaje verbal.",
    "Reconozco las características de los principales medios de comunicación masiva.",
    "Selecciono y clasifico la información emitida por los medios de comunicación masiva.",
    "Recopilo en fichas, mapas, gráficos y cuadros la información que he obtenido de los medios de comunicación masiva.",
    "Organizo la información recopilada y la almaceno de tal forma que la pueda consultar cuando lo requiera.",
    "Caracterizo obras no verbales mediante producciones verbales.",
    "Cotejo obras no verbales con las descripciones y explicaciones que se han formulado acerca de dichas obras.",
    "Comparo el sentido que tiene el uso del espacio y de los movimientos corporales en situaciones comunicativas cotidianas, con el sentido que tienen en obras artísticas.",
    "Propongo hipótesis de interpretación de espectáculos teatrales, obras pictóricas, escultóricas, arquitectónicas, entre otras.",

    // Ética de la comunicación
    "Reconozco, en situaciones comunicativas auténticas, la diversidad y el encuentro de culturas, con el fin de afianzar mis actitudes de respeto y tolerancia.",
    "Caracterizo el contexto cultural del otro y lo comparo con el mío.",
    "Identifico en situaciones comunicativas auténticas algunas variantes lingüísticas de mi entorno, generadas por ubicación geográfica, diferencia social o generacional, profesión, oficio, entre otras.",
    "Evidencio que las variantes lingüísticas encierran una visión particular del mundo.",
    "Reconozco que las variantes lingüísticas y culturales no impiden respetar al otro como interlocutor válido."
];

// -----------------------------------------------------------------------------
// CICLO 4: GRADOS 8° A 9° - LENGUAJE (DBA)
// -----------------------------------------------------------------------------
$dba_lenguaje_8_9 = [
    // Producción textual
    "Produzco textos orales de tipo argumentativo para exponer mis ideas y llegar a acuerdos en los que prime el respeto por mi interlocutor y la valoración de los contextos comunicativos.",
    "Produzco textos escritos que evidencian el conocimiento que he alcanzado acerca del funcionamiento de la lengua en situaciones de comunicación y el uso de las estrategias de producción textual.",
    "Organizo previamente las ideas que deseo exponer y me documento para sustentarlas.",
    "Identifico y valoro los aportes de mi interlocutor y del contexto en el que expongo mis ideas.",
    "Caracterizo y utilizo estrategias descriptivas y explicativas para argumentar mis ideas, valorando y respetando las normas básicas de la comunicación.",
    "Utilizo el discurso oral para establecer acuerdos a partir del reconocimiento de los argumentos de mis interlocutores y la fuerza de mis propios argumentos.",
    "Diseño un plan textual para la presentación de mis ideas, pensamientos y saberes en los contextos en que así lo requiera.",
    "Utilizo un texto explicativo para la presentación de mis ideas, pensamientos y saberes, de acuerdo con las características de mi interlocutor y con la intención que persigo al producir el texto.",
    "Identifico estrategias que garantizan coherencia, cohesión y pertinencia del texto.",
    "Tengo en cuenta reglas sintácticas, semánticas y pragmáticas para la producción de un texto.",
    "Elaboro una primera versión de un texto explicativo atendiendo a los requerimientos estructurales, conceptuales y lingüísticos.",
    "Reescribo el texto, a partir de mi propia valoración y del efecto causado por éste en mis interlocutores.",

    // Comprensión e interpretación textual
    "Comprendo e interpreto textos, teniendo en cuenta el funcionamiento de la lengua en situaciones de comunicación, el uso de estrategias de lectura y el papel del interlocutor y del contexto.",
    "Elaboro hipótesis de lectura de diferentes textos, a partir de la revisión de sus características como: forma de presentación, títulos, graficación y manejo de la lengua.",
    "Comprendo el sentido global de cada uno de los textos que leo, la intención de quien lo produce y las características del contexto en el que se produce.",
    "Caracterizo los textos de acuerdo con la intención comunicativa de quien los produce.",
    "Analizo los aspectos textuales, conceptuales y formales de cada uno de los textos que leo.",
    "Infi ero otros sentidos en cada uno de los textos que leo, relacionándolos con su sentido global y con el contexto en el cual se han producido.",

    // Literatura
    "Determino en las obras literarias latinoamericanas, elementos textuales que dan cuenta de sus características estéticas, históricas y sociológicas, cuando sea pertinente.",
    "Conozco y caracterizo producciones literarias de la tradición oral latinoamericana.",
    "Leo con sentido crítico obras literarias de autores latinoamericanos.",
    "Establezco relaciones entre obras literarias latinoamericanas, procedentes de fuentes escritas y orales.",
    "Caracterizo los principales momentos de la literatura latinoamericana, atendiendo a particularidades temporales, geográficas, de género, de autor, etc.",
    "Identifico los recursos del lenguaje empleados por autores latinoamericanos de diferentes épocas y los comparo con los empleados por autores de otros contextos temporales y espaciales.",

    // Medios de comunicación y otros sistemas simbólicos
    "Retomo crítica y selectivamente la información que circula a través de los medios de comunicación masiva, para confrontarla con la que proviene de otras fuentes.",
    "Comprendo los factores sociales y culturales que determinan algunas manifestaciones del lenguaje no verbal.",
    "Caracterizo los medios de comunicación masiva a partir de aspectos como: de qué manera difunden la información, cuál es su cobertura y alcance, y a qué tipo de audiencia se dirigen.",
    "Diferencio los medios de comunicación masiva de acuerdo con sus características formales y conceptuales.",
    "Utilizo estrategias para la búsqueda, organización, almacenamiento y recuperación de información que circula en diferentes medios de comunicación masiva.",
    "Selecciono la información obtenida a través de los medios masivos, para satisfacer mis necesidades comunicativas.",
    "Establezco relaciones entre la información seleccionada en los medios de difusión masiva y la contrasto críticamente con la que recojo de los contextos en los cuales intervengo.",
    "Determino características, funciones e intenciones de los discursos que circulan a través de los medios de comunicación masiva.",
    "Interpreto elementos políticos, culturales e ideológicos que están presentes en la información que difunden los medios masivos y adopto una posición crítica frente a ellos.",
    "Caracterizo diversas manifestaciones del lenguaje no verbal: música, pintura, escultura, arquitectura, mapas y tatuajes, entre otras.",
    "Identifico rasgos culturales y sociales en diversas manifestaciones del lenguaje no verbal.",
    "Relaciono manifestaciones artísticas no verbales con las personas y las comunidades humanas que las produjeron.",
    "Interpreto manifestaciones artísticas no verbales y las relaciono con otras producciones humanas.",

    // Ética de la comunicación
    "Reflexiono en forma crítica acerca de los actos comunicativos y explico los componentes del proceso de comunicación, con énfasis en los agentes, los discursos, los contextos y el funcionamiento de la lengua.",
    "Reconozco el lenguaje como capacidad humana que configura múltiples sistemas simbólicos y posibilita los procesos de significar y comunicar.",
    "Entiendo la lengua como uno de los sistemas simbólicos producto del lenguaje y la caracterizo en sus aspectos convencionales y arbitrarios.",
    "Explico el proceso de comunicación y doy cuenta de los aspectos e individuos que intervienen en su dinámica.",
    "Comprendo el concepto de coherencia y distingo entre coherencia local y global, en textos míos o de mis compañeros.",
    "Valoro, entiendo y adopto los aportes de la ortografía para la comprensión y producción de textos."
];

// -----------------------------------------------------------------------------
// CICLO 5: GRADOS 10° A 11° - LENGUAJE (DBA)
// -----------------------------------------------------------------------------
$dba_lenguaje_10_11 = [
    // Producción textual
    "Produzco textos argumentativos que evidencian mi conocimiento de la lengua y el control sobre el uso que hago de ella en contextos comunicativos orales y escritos.",
    "Comprendo el valor del lenguaje en los procesos de construcción del conocimiento.",
    "Desarrollo procesos de autocontrol y corrección lingüística en mi producción de textos orales y escritos.",
    "Caracterizo y utilizo estrategias descriptivas, explicativas y analógicas en mi producción de textos orales y escritos.",
    "Evidencio en mis producciones textuales el conocimiento de los diferentes niveles de la lengua y el control sobre el uso que hago de ellos en contextos comunicativos.",
    "Produzco ensayos de carácter argumentativo en los que desarrollo mis ideas con rigor y atendiendo a las características propias del género.",

    // Comprensión e interpretación textual
    "Comprendo e interpreto textos con actitud crítica y capacidad argumentativa.",
    "Elaboro hipótesis de interpretación atendiendo a la intención comunicativa y al sentido global del texto que leo.",
    "Relaciono el significado de los textos que leo con los contextos sociales, culturales y políticos en los cuales se han producido.",
    "Diseño un esquema de interpretación, teniendo en cuenta al tipo de texto, tema, interlocutor e intención comunicativa.",
    "Construyo reseñas críticas acerca de los textos que leo.",
    "Asumo una actitud crítica frente a los textos que leo y elaboro, y frente a otros tipos de texto: explicativos, descriptivos y narrativos.",

    // Literatura
    "Analizo crítica y creativamente diferentes manifestaciones literarias del contexto universal.",
    "Leo textos literarios de diversa índole, género, temática y origen.",
    "Identifico en obras de la literatura universal el lenguaje, las características formales, las épocas y escuelas, estilos, tendencias, temáticas, géneros y autores, entre otros aspectos.",
    "Comprendo en los textos que leo las dimensiones éticas, estéticas, filosóficas, entre otras, que se evidencian en ellos.",
    "Comparo textos de diversos autores, temas, épocas y culturas, y utilizo recursos de la teoría literaria para enriquecer su interpretación.",

    // Medios de comunicación y otros sistemas simbólicos
    "Interpreto en forma crítica la información difundida por los medios de comunicación masiva.",
    "Retomo críticamente los lenguajes no verbales para desarrollar procesos comunicativos intencionados.",
    "Comprendo el papel que cumplen los medios de comunicación masiva en el contexto social, cultural, económico y político de las sociedades contemporáneas.",
    "Infi ero las implicaciones de los medios de comunicación masiva en la conformación de los contextos sociales, culturales, políticos, etc., del país.",
    "Analizo los mecanismos ideológicos que subyacen a la estructura de los medios de información masiva.",
    "Asumo una posición crítica frente a los elementos ideológicos presentes en dichos medios, y analizo su incidencia en la sociedad actual.",
    "Doy cuenta del uso del lenguaje verbal o no verbal en manifestaciones humanas como los grafiti, la publicidad, los símbolos patrios, las canciones, los caligramas, entre otros.",
    "Analizo las implicaciones culturales, sociales e ideológicas de manifestaciones humanas como los grafiti, la publicidad, los símbolos patrios, las canciones, los caligramas, entre otros.",
    "Explico cómo los códigos verbales y no verbales se articulan para generar sentido en obras cinematográficas, canciones y caligramas, entre otras.",
    "Produzco textos, empleando lenguaje verbal o no verbal, para exponer mis ideas o para recrear realidades, con sentido crítico.",

    // Ética de la comunicación
    "Expreso respeto por la diversidad cultural y social del mundo contemporáneo, en las situaciones comunicativas en las que intervengo.",
    "Identifico, caracterizo y valoro diferentes grupos humanos teniendo en cuenta aspectos étnicos, lingüísticos, sociales y culturales, entre otros, del mundo contemporáneo.",
    "Respeto la diversidad de criterios y posiciones ideológicas que surgen en los grupos humanos.",
    "Utilizo el diálogo y la argumentación para superar enfrentamientos y posiciones antagónicas.",
    "Comprendo que en la relación intercultural con las comunidades indígenas y afrocolombianas deben primar el respeto y la igualdad, lo que propiciará el acercamiento socio-cultural entre todos los colombianos.",
    "Argumento, en forma oral y escrita, acerca de temas y problemáticas que puedan ser objeto de intolerancia, segregación, señalamientos, etc."
];

// Generación del array por grados independientes (1 a 11)
for ($i = 1; $i <= 11; $i++) {
    $grupo = [];

    if ($i <= 3) {
        $grupo = $dba_lenguaje_1_3;
    } elseif ($i <= 5) {
        $grupo = $dba_lenguaje_4_5;
    } elseif ($i <= 7) {
        $grupo = $dba_lenguaje_6_7;
    } elseif ($i <= 9) {
        $grupo = $dba_lenguaje_8_9;
    } else {
        $grupo = $dba_lenguaje_10_11;
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
    "source" => "Derechos Básicos de Aprendizaje (DBA) - Lenguaje - Ministerio de Educación Nacional de Colombia",
    "nota" => "DBA organizados por cinco factores: Producción textual, Comprensión e interpretación textual, Literatura, Medios de comunicación y otros sistemas simbólicos, Ética de la comunicación. Estructura compatible con sistema de planeación inasistig.",
    "factores" => [
        "produccion_textual" => "Capacidad para producir textos orales y escritos que respondan a diversas necesidades comunicativas con coherencia, cohesión y pertinencia.",
        "comprension_interpretacion" => "Habilidad para leer, comprender e interpretar diversos tipos de textos estableciendo relaciones internas y externas.",
        "literatura" => "Apreciación, comprensión y disfrute de obras literarias como expresión estética y cultural.",
        "medios_sistemas_simbolicos" => "Uso crítico y creativo de los medios de comunicación y otros sistemas simbólicos para comprender y producir mensajes.",
        "etica_comunicacion" => "Actitud ética en los procesos comunicativos: respeto, reconocimiento del otro, diálogo y construcción de acuerdos."
    ],
    "enfoques_pedagogicos" => [
        "semiotico" => "El lenguaje como sistema de signos que permite significar y comunicar.",
        "pragmatico" => "El uso del lenguaje en contextos comunicativos reales con propósitos específicos.",
        "critico" => "Análisis reflexivo de los discursos y sus implicaciones ideológicas y sociales.",
        "estetico" => "Valoración de la dimensión artística y creativa del lenguaje.",
        "etico" => "Formación en valores comunicativos: respeto, tolerancia, diálogo y responsabilidad."
    ],
    "data" => $datos
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>