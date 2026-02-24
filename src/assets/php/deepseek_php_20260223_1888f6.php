<?php
header('Content-Type: application/json');

$dba_colombia_completo = [
    'metadata' => [
        'fuente' => 'Ministerio de Educación Nacional de Colombia - MEN',
        'descripcion' => 'Derechos Básicos de Aprendizaje - Todos los grados (Transición a 11°)',
        'url_oficial' => 'https://www.colombiaaprende.edu.co/',
        'actualizacion' => '2024',
        'nota' => 'Los DBA establecen los aprendizajes estructurantes para cada grado en Colombia'
    ],

    // EDUCACIÓN PREESCOLAR
    'preescolar' => [
        'nombre' => 'Grado Transición',
        'nivel' => 'Preescolar',
        'dimensiones' => [
            'cognitiva' => [
                'nombre' => 'Dimensión Cognitiva',
                'dba' => [
                    'Reconoce y nombra letras, números y colores básicos',
                    'Comprende conceptos básicos de tamaño, forma y cantidad',
                    'Identifica y resuelve problemas sencillos de su entorno',
                    'Comprende y sigue instrucciones simples de 2 a 3 pasos',
                    'Desarrolla conciencia fonológica identificando sonidos iniciales'
                ]
            ],
            'comunicativa' => [
                'nombre' => 'Dimensión Comunicativa',
                'dba' => [
                    'Expresa ideas y sentimientos mediante lenguaje oral y gestual',
                    'Participa en conversaciones y diálogos respetando turnos',
                    'Comprende mensajes orales en situaciones cotidianas',
                    'Disfruta de la lectura de cuentos y narraciones infantiles',
                    'Realiza trazos y grafías con intención comunicativa'
                ]
            ],
            'corporal' => [
                'nombre' => 'Dimensión Corporal',
                'dba' => [
                    'Desarrolla coordinación motora gruesa (correr, saltar, lanzar)',
                    'Desarrolla coordinación motora fina (recortar, rasgar, ensartar)',
                    'Reconoce y cuida su cuerpo practicando hábitos de higiene',
                    'Participa en juegos y actividades físicas siguiendo reglas básicas'
                ]
            ],
            'artistica' => [
                'nombre' => 'Dimensión Estética y Creativa',
                'dba' => [
                    'Explora diferentes materiales para expresión plástica (pintura, modelado)',
                    'Participa en actividades musicales explorando ritmos y canciones',
                    'Representa situaciones cotidianas mediante juego dramático',
                    'Expresa creativamente sus vivencias a través del arte'
                ]
            ],
            'socioemocional' => [
                'nombre' => 'Dimensión Socioemocional',
                'dba' => [
                    'Reconoce y expresa emociones básicas de manera adecuada',
                    'Establece relaciones positivas con pares y adultos',
                    'Participa en actividades grupales colaborando con otros niños',
                    'Desarrolla habilidades de autocontrol y resolución pacífica de conflictos',
                    'Muestra respeto y empatía hacia los demás'
                ]
            ],
            'etica_valores' => [
                'nombre' => 'Dimensión Ética y Valórica',
                'dba' => [
                    'Comprende valores y normas básicas de convivencia en el aula',
                    'Reconoce y respeta la diversidad cultural y las diferencias',
                    'Desarrolla habilidades para compartir, cooperar y ayudar a otros'
                ]
            ]
        ]
    ],

    // CIENCIAS NATURALES - GRADOS 1° A 11°
    'ciencias_naturales' => [
        'nombre' => 'Ciencias Naturales y Educación Ambiental',
        'area' => 'Ciencias Naturales',
        'niveles' => 'Básica Primaria, Básica Secundaria y Media',
        'grados' => [
            '1' => [
                'dba' => [
                    'Identifica y describe las partes básicas de plantas y animales de su entorno',
                    'Comprende los conceptos básicos de tiempo y clima (día soleado, lluvioso)',
                    'Reconoce y clasifica objetos según sus propiedades (color, forma, tamaño)',
                    'Diferencia seres vivos de objetos inertes en su entorno',
                    'Reconoce la importancia del agua, el aire y el suelo para la vida'
                ]
            ],
            '2' => [
                'dba' => [
                    'Explora los diferentes estados de la materia (sólido, líquido, gas) en contextos cotidianos',
                    'Comprende las fases de la Luna y conceptos básicos del sistema solar',
                    'Identifica y describe las características de los seres vivos y su hábitat',
                    'Reconoce cambios en los seres vivos durante su ciclo de vida',
                    'Clasifica animales según sus características (mamíferos, aves, peces, insectos)'
                ]
            ],
            '3' => [
                'dba' => [
                    'Comprende las fuerzas básicas y cómo afectan a los objetos (gravedad, fricción, imanes)',
                    'Conoce la diversidad de plantas y animales en diferentes ecosistemas colombianos',
                    'Identifica los cambios en el tiempo y el clima a lo largo del año (estaciones en Colombia)',
                    'Explica relaciones simples entre las características de los seres vivos y su ambiente',
                    'Reconoce la importancia de los recursos naturales y su conservación'
                ]
            ],
            '4' => [
                'dba' => [
                    'Explora conceptos básicos de electricidad (circuitos simples) y magnetismo',
                    'Comprende las cadenas alimenticias y las interacciones entre seres vivos en ecosistemas',
                    'Estudia la Tierra, su forma, movimientos y fenómenos geológicos básicos (sismos, volcanes)',
                    'Diferencia entre ecosistemas terrestres y acuáticos en Colombia',
                    'Identifica adaptaciones de los seres vivos al medio ambiente'
                ]
            ],
            '5' => [
                'dba' => [
                    'Profundiza en la comprensión de la energía y sus formas (térmica, luminosa, sonora, cinética)',
                    'Explora la diversidad de la vida y clasificación de seres vivos en grupos taxonómicos básicos',
                    'Estudia los recursos naturales, su uso sostenible y la importancia de su conservación',
                    'Comprende la relación entre los seres humanos y el ambiente (impacto ambiental)',
                    'Explica fenómenos naturales como el ciclo del agua y su importancia'
                ]
            ],
            '6' => [
                'dba' => [
                    'Comprende la estructura y función básica de la célula como unidad fundamental de los seres vivos',
                    'Explica los niveles de organización interna de los seres vivos (célula, tejido, órgano, sistema)',
                    'Analiza las relaciones entre los sistemas del cuerpo humano y su funcionamiento coordinado',
                    'Reconoce los procesos de nutrición en diferentes organismos (autótrofos y heterótrofos)',
                    'Clasifica los seres vivos según criterios taxonómicos básicos'
                ]
            ],
            '7' => [
                'dba' => [
                    'Comprende los procesos de reproducción en seres vivos (sexual y asexual) y su importancia',
                    'Explica la relación entre los sistemas del cuerpo humano (circulatorio, respiratorio, digestivo)',
                    'Reconoce las formas de energía, sus transformaciones y aplicaciones en la vida cotidiana',
                    'Describe los recursos naturales, su clasificación y el impacto de su explotación',
                    'Analiza la dinámica de poblaciones en ecosistemas y factores que afectan su equilibrio'
                ]
            ],
            '8' => [
                'dba' => [
                    'Comprende los principios básicos de la herencia biológica y los mecanismos de evolución',
                    'Explica el funcionamiento del sistema nervioso y endocrino en la coordinación del organismo',
                    'Reconoce los tipos de movimiento y las causas que los producen (fuerzas, leyes de Newton)',
                    'Describe los ciclos biogeoquímicos (carbono, nitrógeno, agua) y su importancia ecosistémica',
                    'Analiza problemáticas ambientales locales y globales y propone soluciones'
                ]
            ],
            '9' => [
                'dba' => [
                    'Comprende los mecanismos de evolución (selección natural, deriva génica) y evidencia fósil',
                    'Explica la relación entre organismo, ambiente y adaptación como resultado evolutivo',
                    'Reconoce las leyes del movimiento y las aplica a situaciones cotidianas',
                    'Describe la estructura del universo, sistema solar y fenómenos astronómicos básicos',
                    'Analiza la sostenibilidad ambiental y el desarrollo sostenible'
                ]
            ],
            '10' => [
                'dba' => [
                    'Comprende procesos bioquímicos celulares (respiración, fotosíntesis, síntesis de proteínas)',
                    'Explica el funcionamiento integrado de los sistemas del cuerpo humano (homeostasis)',
                    'Reconoce las leyes de la termodinámica y sus aplicaciones en sistemas biológicos y físicos',
                    'Describe la dinámica de los ecosistemas (flujo de energía, ciclos de materia, sucesión ecológica)',
                    'Analiza la biotecnología, sus aplicaciones en salud, agricultura y medio ambiente, y sus implicaciones éticas'
                ]
            ],
            '11' => [
                'dba' => [
                    'Comprende procesos metabólicos a nivel celular y molecular (enzimas, metabolismo energético)',
                    'Explica mecanismos genéticos (ADN, ARN, código genético, mutaciones) y sus implicaciones',
                    'Reconoce las teorías sobre el origen del universo y la evolución química de la vida',
                    'Describe problemáticas ambientales globales (cambio climático, pérdida de biodiversidad) y estrategias de mitigación',
                    'Analiza el impacto social, ético y ambiental de los desarrollos científicos y tecnológicos'
                ]
            ]
        ]
    ],

    // MATEMÁTICAS - GRADOS 1° A 11°
    'matematicas' => [
        'nombre' => 'Matemáticas',
        'area' => 'Matemáticas',
        'niveles' => 'Básica Primaria, Básica Secundaria y Media',
        'grados' => [
            '1' => [
                'dba' => [
                    'Reconoce y escribe números del 1 al 100 en contextos cotidianos',
                    'Comprende y utiliza conceptos básicos de suma y resta con números hasta 20',
                    'Identifica patrones numéricos y geométricos simples en secuencias',
                    'Reconoce figuras geométricas básicas (círculo, cuadrado, triángulo, rectángulo)',
                    'Realiza mediciones con unidades no convencionales (pasos, cuartas, tapas)',
                    'Clasifica objetos según atributos medibles (color, forma, tamaño)'
                ]
            ],
            '2' => [
                'dba' => [
                    'Suma y resta números de dos y tres dígitos con y sin reagrupación',
                    'Comprende y aplica conceptos básicos de multiplicación como suma repetida',
                    'Resuelve problemas matemáticos simples que involucran suma, resta y multiplicación',
                    'Reconoce el valor posicional de los números hasta 999',
                    'Lee e interpreta información en tablas de conteo y gráficos de barras sencillos',
                    'Identifica y nombra figuras geométricas en dos y tres dimensiones'
                ]
            ],
            '3' => [
                'dba' => [
                    'Multiplica y divide números de uno y dos dígitos (tablas hasta el 10)',
                    'Trabaja con fracciones simples (medios, tercios, cuartos) y decimales básicos (décimos)',
                    'Resuelve problemas matemáticos que involucran medidas de longitud, peso y capacidad',
                    'Reconoce y clasifica figuras geométricas según sus características (lados, vértices, ángulos)',
                    'Calcula perímetros de figuras planas usando unidades convencionales',
                    'Organiza y analiza datos en tablas y gráficos'
                ]
            ],
            '4' => [
                'dba' => [
                    'Realiza multiplicaciones y divisiones con números de varios dígitos',
                    'Comprende y aplica conceptos de fracciones y decimales en situaciones cotidianas',
                    'Resuelve problemas que involucran proporciones simples y porcentajes básicos (25%, 50%)',
                    'Identifica y traza líneas paralelas, perpendiculares y ángulos en figuras geométricas',
                    'Calcula áreas de rectángulos y cuadrados usando fórmulas básicas',
                    'Interpreta información estadística en gráficos de líneas y circulares'
                ]
            ],
            '5' => [
                'dba' => [
                    'Multiplica y divide números de varios dígitos con fluidez',
                    'Trabaja con fracciones, decimales y porcentajes en contextos variados',
                    'Resuelve problemas más avanzados que involucran proporciones, razones y porcentajes',
                    'Reconoce y construye figuras simétricas y realiza transformaciones geométricas (traslación, rotación)',
                    'Calcula áreas de triángulos, paralelogramos y polígonos regulares',
                    'Interpreta medidas de tendencia central (media, mediana, moda) en conjuntos de datos'
                ]
            ],
            '6' => [
                'dba' => [
                    'Resuelve problemas con números naturales, enteros y racionales en contextos variados',
                    'Interpreta y representa relaciones de proporcionalidad directa e inversa',
                    'Reconoce propiedades de figuras bidimensionales y tridimensionales (polígonos, poliedros)',
                    'Realiza conversiones entre unidades de medida en diferentes sistemas',
                    'Interpreta medidas de tendencia central y de dispersión básica',
                    'Utiliza el plano cartesiano para localizar puntos y representar figuras'
                ]
            ],
            '7' => [
                'dba' => [
                    'Resuelve problemas con números racionales y sus operaciones en diferentes contextos',
                    'Interpreta variaciones de proporcionalidad directa, inversa y porcentajes',
                    'Reconoce transformaciones geométricas en el plano (reflexión, traslación, rotación, homotecia)',
                    'Calcula áreas y volúmenes de prismas y pirámides',
                    'Analiza datos usando medidas de tendencia central y rango',
                    'Representa situaciones mediante expresiones algebraicas simples'
                ]
            ],
            '8' => [
                'dba' => [
                    'Resuelve problemas con números reales y sus propiedades (racionales e irracionales)',
                    'Interpreta funciones lineales y cuadráticas y sus representaciones gráficas',
                    'Reconoce y aplica teoremas geométricos (Pitágoras, Tales) en situaciones problema',
                    'Calcula áreas y volúmenes de cuerpos redondos (cilindros, conos, esferas)',
                    'Interpreta distribuciones de datos estadísticos y diagramas de caja',
                    'Resuelve ecuaciones lineales con una incógnita'
                ]
            ],
            '9' => [
                'dba' => [
                    'Resuelve problemas con números reales y expresiones algebraicas (polinomios)',
                    'Interpreta funciones polinómicas, exponenciales y logarítmicas',
                    'Aplica relaciones métricas en triángulos (trigonometría básica: seno, coseno, tangente)',
                    'Calcula perímetros y áreas de figuras circulares (circunferencia, círculo, sector circular)',
                    'Analiza correlación entre variables estadísticas y rectas de regresión',
                    'Resuelve sistemas de ecuaciones lineales 2x2'
                ]
            ],
            '10' => [
                'dba' => [
                    'Resuelve problemas con funciones trigonométricas (seno, coseno, tangente) y sus identidades básicas',
                    'Interpreta conceptos de límites y continuidad de funciones',
                    'Aplica razones trigonométricas en la solución de problemas de triángulos oblicuángulos',
                    'Comprende conceptos básicos de cálculo diferencial (derivada como razón de cambio)',
                    'Analiza distribuciones de probabilidad discretas y continuas básicas',
                    'Utiliza el plano cartesiano para representar cónicas (circunferencia, parábola)'
                ]
            ],
            '11' => [
                'dba' => [
                    'Resuelve problemas con funciones exponenciales, logarítmicas y trigonométricas inversas',
                    'Interpreta y aplica conceptos de derivada (recta tangente, optimización básica) e integral (área bajo la curva)',
                    'Aplica geometría analítica en situaciones problema (cónicas, lugares geométricos)',
                    'Calcula áreas y volúmenes usando métodos del cálculo integral básico',
                    'Analiza datos usando inferencia estadística (intervalos de confianza, pruebas de hipótesis)',
                    'Aplica conceptos de probabilidad condicional y teorema de Bayes en contextos reales'
                ]
            ]
        ]
    ],

    // CIENCIAS SOCIALES - GRADOS 1° A 11°
    'ciencias_sociales' => [
        'nombre' => 'Ciencias Sociales, Historia, Geografía, Constitución Política y Democracia',
        'area' => 'Ciencias Sociales',
        'niveles' => 'Básica Primaria, Básica Secundaria y Media',
        'grados' => [
            '1' => [
                'dba' => [
                    'Reconoce y nombra lugares y objetos familiares en su entorno cercano (casa, escuela, barrio)',
                    'Comprende las diferencias entre familias y comunidades y sus roles',
                    'Identifica y describe algunas características de su comunidad local',
                    'Reconoce las normas básicas de convivencia en el hogar y la escuela',
                    'Valora las celebraciones y tradiciones de su comunidad'
                ]
            ],
            '2' => [
                'dba' => [
                    'Explora la geografía básica de Colombia, identificando regiones naturales y características',
                    'Comprende la importancia del respeto, la tolerancia y la convivencia en la comunidad',
                    'Identifica roles y responsabilidades de las personas en la sociedad (oficios y profesiones)',
                    'Reconoce símbolos patrios y su significado (bandera, escudo, himno)',
                    'Describe actividades económicas de su comunidad (agricultura, comercio, servicios)'
                ]
            ],
            '3' => [
                'dba' => [
                    'Aprende sobre la historia y cultura de Colombia, incluyendo eventos y personajes importantes',
                    'Comprende la diversidad cultural y étnica del país (grupos indígenas, afrocolombianos, ROM)',
                    'Reconoce y describe características de diferentes comunidades en Colombia',
                    'Identifica los departamentos y capitales de Colombia en un mapa',
                    'Reconoce la importancia del patrimonio cultural material e inmaterial'
                ]
            ],
            '4' => [
                'dba' => [
                    'Profundiza en la geografía colombiana, incluyendo ríos, montañas, valles y ciudades importantes',
                    'Conoce la historia de Colombia desde la época precolombina hasta la Colonia',
                    'Comprende los principios básicos de la democracia, la participación ciudadana y el gobierno escolar',
                    'Reconoce las ramas del poder público (ejecutiva, legislativa, judicial) y sus funciones',
                    'Identifica las características de las regiones naturales de Colombia'
                ]
            ],
            '5' => [
                'dba' => [
                    'Explora las regiones geográficas de Colombia y su diversidad natural, económica y cultural',
                    'Estudia la historia de Colombia desde la Independencia hasta el siglo XX',
                    'Comprende conceptos básicos sobre economía (producción, distribución, consumo), recursos naturales y desarrollo sostenible',
                    'Reconoce los derechos fundamentales de los niños y niñas (Constitución, Convención de Derechos del Niño)',
                    'Analiza la organización territorial de Colombia (departamentos, municipios, distritos)'
                ]
            ],
            '6' => [
                'dba' => [
                    'Comprende el proceso de hominización y el poblamiento de América (teorías, rutas migratorias)',
                    'Identifica las características de las primeras civilizaciones (Mesopotamia, Egipto, China, India)',
                    'Reconoce formas de organización social en la antigüedad (ciudades-Estado, imperios)',
                    'Describe las características físicas de los continentes y océanos del mundo',
                    'Analiza la relación entre el ser humano y el medio ambiente en diferentes contextos históricos'
                ]
            ],
            '7' => [
                'dba' => [
                    'Comprende la época medieval en Europa (feudalismo, cruzadas, cultura medieval)',
                    'Identifica cambios sociales, políticos y económicos en la transición a la modernidad (renacimiento)',
                    'Reconoce la expansión europea y los viajes de exploración (descubrimiento de América)',
                    'Analiza las culturas precolombinas en América (Mayas, Aztecas, Incas, Muiscas, Taironas)',
                    'Describe el encuentro de mundos y sus consecuencias demográficas, sociales y culturales'
                ]
            ],
            '8' => [
                'dba' => [
                    'Comprende el proceso de conquista y colonización en América por parte de los europeos',
                    'Identifica las instituciones coloniales en América (virreinatos, capitanías, resguardos, encomiendas)',
                    'Reconoce las transformaciones territoriales, económicas y sociales durante la colonia',
                    'Describe la economía colonial (minería, hacienda, comercio) y el trabajo forzado',
                    'Analiza el mestizaje cultural, racial y las castas en la sociedad colonial'
                ]
            ],
            '9' => [
                'dba' => [
                    'Comprende los procesos de independencia en América Latina y Colombia (causas internas y externas)',
                    'Identifica los cambios políticos, económicos y sociales del siglo XIX en Colombia',
                    'Reconoce la formación de los estados nacionales en América Latina',
                    'Describe la economía colombiana en el siglo XIX (exportaciones, artesanado, primeras industrias)',
                    'Analiza los movimientos sociales del siglo XIX (artesanos, campesinos, guerras civiles)',
                    'Comprende el proceso de abolición de la esclavitud en Colombia'
                ]
            ],
            '10' => [
                'dba' => [
                    'Comprende el siglo XX en Colombia (Hegemonía Conservadora, República Liberal, Frente Nacional)',
                    'Identifica los principales conflictos políticos y sociales (La Violencia, surgimiento de guerrillas)',
                    'Reconoce los cambios territoriales y demográficos en Colombia (urbanización, migraciones)',
                    'Describe la evolución económica del siglo XX (industrialización, café, apertura económica)',
                    'Analiza los movimientos sociales contemporáneos (obreros, campesinos, indígenas, estudiantes)',
                    'Comprende el proceso constituyente de 1991 y la nueva Constitución Política'
                ]
            ],
            '11' => [
                'dba' => [
                    'Comprende el fenómeno de la globalización y sus efectos económicos, políticos y culturales en Colombia y el mundo',
                    'Identifica los principales problemas sociales contemporáneos (pobreza, desigualdad, desplazamiento, narcotráfico)',
                    'Reconoce los retos ambientales globales (cambio climático, pérdida de biodiversidad) y acuerdos internacionales',
                    'Describe la economía global, los bloques económicos y las relaciones internacionales de Colombia',
                    'Analiza el conflicto armado colombiano, los procesos de paz y la construcción de posconflicto',
                    'Reconoce la diversidad cultural mundial y los derechos humanos en el contexto global'
                ]
            ]
        ]
    ],

    // LENGUAJE - GRADOS 1° A 11°
    'lenguaje' => [
        'nombre' => 'Lengua Castellana',
        'area' => 'Humanidades',
        'niveles' => 'Básica Primaria, Básica Secundaria y Media',
        'grados' => [
            '1' => [
                'dba' => [
                    'Reconoce y nombra letras del alfabeto y sus sonidos (conciencia fonológica)',
                    'Comprende y cuenta historias sencillas (cuentos, fábulas) identificando personajes y eventos',
                    'Reconoce palabras y frases familiares en textos cortos y cotidianos',
                    'Escribe letras, palabras y oraciones simples con intención comunicativa',
                    'Expresa ideas, sentimientos y vivencias mediante dibujos y palabras',
                    'Participa en conversaciones y diálogos respetando turnos'
                ]
            ],
            '2' => [
                'dba' => [
                    'Lee y comprende textos cortos (narrativos, informativos) identificando ideas principales',
                    'Escribe oraciones completas y comprensibles con concordancia básica',
                    'Reconoce y utiliza reglas básicas de ortografía (uso de mayúsculas, punto final)',
                    'Se comunica de manera clara y coherente en situaciones familiares',
                    'Comprende la relación entre imágenes y texto en diferentes formatos',
                    'Disfruta de la lectura de cuentos, poemas y textos de tradición oral'
                ]
            ],
            '3' => [
                'dba' => [
                    'Lee y comprende textos más extensos (cuentos, leyendas, textos expositivos)',
                    'Escribe textos narrativos simples con estructura (inicio, nudo, desenlace)',
                    'Comprende y aplica reglas de acentuación (palabras agudas, graves) y puntuación básica',
                    'Participa en conversaciones y debates sobre temas familiares expresando opiniones',
                    'Identifica diferentes tipos de textos según su función (narrar, informar, explicar)',
                    'Utiliza el diccionario para consultar significados de palabras'
                ]
            ],
            '4' => [
                'dba' => [
                    'Lee y comprende textos variados (literarios, informativos, instruccionales) identificando estructura',
                    'Escribe textos narrativos y expositivos más elaborados con coherencia y cohesión',
                    'Aplica reglas gramaticales (sustantivo, adjetivo, verbo) y de puntuación con mayor precisión',
                    'Expresa opiniones y argumenta en discusiones sobre temas estudiados',
                    'Reconoce las características de los géneros literarios (narrativo, lírico, dramático)',
                    'Realiza resúmenes y esquemas para organizar información de textos'
                ]
            ],
            '5' => [
                'dba' => [
                    'Lee y analiza textos literarios y no literarios de manera crítica, identificando intención comunicativa',
                    'Escribe textos argumentativos y persuasivos sencillos (cartas, opiniones)',
                    'Utiliza recursos literarios básicos en la escritura (comparaciones, personificaciones)',
                    'Participa activamente en debates, exposiciones y presentaciones orales',
                    'Comprende la estructura de la oración (sujeto, predicado, complementos)',
                    'Identifica las variedades lingüísticas (dialectos, sociolectos) en su comunidad'
                ]
            ],
            '6' => [
                'dba' => [
                    'Comprende e interpreta textos literarios de diferentes épocas y culturas (mitos, leyendas, épica)',
                    'Analiza los elementos de la comunicación en diferentes contextos (emisor, receptor, mensaje, canal)',
                    'Produce textos expositivos y argumentativos sencillos con estructura adecuada',
                    'Utiliza estrategias de búsqueda y selección de información en diferentes fuentes',
                    'Reconoce las variedades lingüísticas y la diversidad cultural en su comunidad',
                    'Identifica las características de los medios de comunicación masiva (radio, prensa, televisión)'
                ]
            ],
            '7' => [
                'dba' => [
                    'Interpreta textos literarios de tradición oral y escrita (cuentos, poemas, teatro medieval)',
                    'Analiza críticamente los medios de comunicación masiva y su influencia en la sociedad',
                    'Produce textos críticos y creativos sobre temas de interés social y cultural',
                    'Utiliza técnicas de investigación documental (fichas, citas, referencias básicas)',
                    'Reconoce la evolución de la lengua española y su influencia de otras lenguas',
                    'Comprende el uso de figuras literarias en textos poéticos (metáfora, símil, hipérbole)'
                ]
            ],
            '8' => [
                'dba' => [
                    'Comprende textos literarios de diferentes movimientos estéticos (romanticismo, realismo, modernismo)',
                    'Analiza discursos en diferentes contextos socioculturales (político, religioso, publicitario)',
                    'Produce textos académicos siguiendo normas (informes, reseñas, ensayos cortos)',
                    'Utiliza herramientas digitales para investigar y comunicar información',
                    'Reconoce la relación entre lenguaje, poder y sociedad en diferentes contextos',
                    'Analiza los elementos de la argumentación (tesis, argumentos, conclusiones)'
                ]
            ],
            '9' => [
                'dba' => [
                    'Interpreta textos literarios latinoamericanos (boom latinoamericano, literatura indigenista)',
                    'Analiza el discurso político y social en diferentes contextos históricos y actuales',
                    'Produce ensayos argumentativos con estructura formal y postura crítica',
                    'Utiliza técnicas avanzadas de investigación (planteamiento de problema, marco teórico básico)',
                    'Reconoce la diversidad lingüística en Colombia (lenguas indígenas, criollas, ROM)',
                    'Comprende la intertextualidad y las relaciones entre diferentes textos y discursos'
                ]
            ],
            '10' => [
                'dba' => [
                    'Comprende textos literarios universales (literatura griega, medieval, renacentista, contemporánea)',
                    'Analiza discursos académicos y científicos reconociendo sus características y propósitos',
                    'Produce textos críticos y creativos de mayor complejidad (ensayos, crónicas, reseñas críticas)',
                    'Desarrolla proyectos de investigación con rigor metodológico',
                    'Reconoce las principales teorías lingüísticas contemporáneas (estructuralismo, generativismo, pragmática)',
                    'Analiza la relación entre literatura, historia y sociedad en diferentes contextos'
                ]
            ],
            '11' => [
                'dba' => [
                    'Interpreta textos literarios contemporáneos y posmodernos reconociendo sus características estéticas',
                    'Analiza discursos en diversos campos del saber (científico, filosófico, artístico, político)',
                    'Produce textos argumentativos complejos (artículos de opinión, ensayos académicos) con postura crítica',
                    'Sustenta proyectos de investigación ante comunidades académicas',
                    'Reflexiona sobre el lenguaje como práctica social, cultural e identitaria',
                    'Analiza las transformaciones del lenguaje en la era digital y su impacto en la comunicación'
                ]
            ]
        ]
    ],

    // INGLÉS - GRADOS 1° A 11° (basado en Currículo Sugerido MEN)
    'ingles' => [
        'nombre' => 'Inglés',
        'area' => 'Humanidades - Idioma Extranjero',
        'meta' => 'Nivel B1 (Pre-intermedio) al culminar grado 11° [citation:5]',
        'niveles' => 'Básica Primaria, Básica Secundaria y Media',
        'grados' => [
            '1' => [
                'dba' => [
                    'Comprende y utiliza vocabulario básico (saludos, colores, números 1-10, familia) en situaciones cotidianas',
                    'Se comunica en inglés de manera simple sobre temas familiares usando palabras y frases cortas',
                    'Reconoce y reproduce sonidos y entonación básica del inglés',
                    'Comprende instrucciones sencillas en inglés (stand up, sit down, point to)',
                    'Responde preguntas personales básicas (What\'s your name? How old are you?)'
                ]
            ],
            '2' => [
                'dba' => [
                    'Amplía el vocabulario (partes del cuerpo, animales, comida, ropa) y comprende instrucciones en inglés',
                    'Participa en conversaciones sencillas en inglés sobre temas familiares',
                    'Lee y entiende textos cortos en inglés con apoyo de imágenes',
                    'Escribe palabras y frases cortas relacionadas con temas trabajados',
                    'Describe objetos, animales y personas usando vocabulario básico'
                ]
            ],
            '3' => [
                'dba' => [
                    'Desarrolla habilidades de lectura y comprensión en inglés (textos narrativos cortos)',
                    'Participa en diálogos y conversaciones más complejas sobre temas conocidos',
                    'Utiliza estructuras gramaticales básicas (presente simple, there is/are) en la escritura y el habla',
                    'Escribe párrafos cortos describiendo personas, lugares o actividades',
                    'Comprende la idea principal de canciones y videos cortos en inglés'
                ]
            ],
            '4' => [
                'dba' => [
                    'Lee y comprende textos en inglés de mayor longitud y complejidad (cuentos, descripciones)',
                    'Se expresa de manera más fluida en inglés sobre temas cotidianos (rutinas, gustos, preferencias)',
                    'Escribe párrafos cortos en inglés con estructura básica (inicio, desarrollo, cierre)',
                    'Utiliza tiempos verbales presente y pasado simple en contextos familiares',
                    'Comprende instrucciones escritas y orales para realizar actividades'
                ]
            ],
            '5' => [
                'dba' => [
                    'Lee y analiza textos variados en inglés (cuentos, cartas, textos informativos)',
                    'Mantiene conversaciones en inglés sobre temas conocidos expresando opiniones',
                    'Escribe textos coherentes en inglés aplicando estructuras gramaticales (presente, pasado, futuro simple)',
                    'Comprende y produce descripciones detalladas de personas, lugares y objetos',
                    'Utiliza vocabulario apropiado para expresar necesidades, gustos y sentimientos'
                ]
            ],
            '6' => [
                'dba' => [
                    'Comprende y describe actividades cotidianas, rutinas y hobbies en presente simple',
                    'Intercambia información personal y sobre otros (familia, amigos, mascotas)',
                    'Lee y comprende textos descriptivos y narrativos cortos en inglés',
                    'Escribe textos cortos describiendo personas, lugares y actividades',
                    'Comprende instrucciones y anuncios simples en contextos familiares'
                ]
            ],
            '7' => [
                'dba' => [
                    'Describe experiencias pasadas y eventos usando pasado simple y continuo',
                    'Expresa planes futuros e intenciones utilizando "going to" y "will"',
                    'Compara personas, objetos y lugares usando adjetivos comparativos y superlativos',
                    'Lee y comprende textos más extensos (biografías, cartas, correos electrónicos)',
                    'Participa en conversaciones sobre temas cotidianos y de interés personal'
                ]
            ],
            '8' => [
                'dba' => [
                    'Narra eventos en pasado de manera coherente (biografías, experiencias personales)',
                    'Expresa opiniones, acuerdos y desacuerdos en conversaciones y debates',
                    'Comprende textos auténticos como artículos cortos y publicaciones en redes sociales',
                    'Escribe textos expositivos y argumentativos cortos (opiniones, reseñas)',
                    'Utiliza conectores básicos para enlazar ideas (and, but, because, so)'
                ]
            ],
            '9' => [
                'dba' => [
                    'Comprende y produce textos que expresan hipótesis y condiciones (condicionales tipo 0 y 1)',
                    'Analiza problemas sociales y propone soluciones en inglés (medio ambiente, convivencia)',
                    'Lee textos auténticos (noticias, blogs) identificando ideas principales y detalles',
                    'Participa en debates y discusiones sobre temas de interés juvenil',
                    'Escribe ensayos cortos expresando postura personal sobre temas contemporáneos'
                ]
            ],
            '10' => [
                'dba' => [
                    'Comprende textos académicos y científicos en inglés de nivel básico-intermedio',
                    'Produce presentaciones orales sobre temas académicos y profesionales',
                    'Utiliza estructuras gramaticales complejas (voz pasiva, reported speech)',
                    'Analiza y discute temas globales (globalización, tecnología, medio ambiente)',
                    'Escribe textos argumentativos formales (cartas formales, ensayos)'
                ]
            ],
            '11' => [
                'dba' => [
                    'Alcanza nivel B1 (Pre-intermedio) según Marco Común Europeo [citation:5]',
                    'Comprende textos auténticos de diferentes géneros (literarios, periodísticos, académicos)',
                    'Mantiene conversaciones fluidas sobre temas abstractos y concretos',
                    'Produce textos coherentes y cohesivos sobre temas complejos',
                    'Analiza críticamente discursos y medios de comunicación en inglés',
                    'Prepara y sustenta proyectos de investigación en inglés'
                ]
            ]
        ]
    ],

    // EDUCACIÓN ÉTICA Y VALORES HUMANOS - GRADOS 1° A 11°
    'etica_valores' => [
        'nombre' => 'Educación Ética y Valores Humanos',
        'area' => 'Transversal Obligatoria',
        'base_legal' => 'Ley 115/94, Artículo 23',
        'grados' => generarDBAEtica(),
        'descripcion' => 'Desarrollo integral de la personalidad, formación en valores, derechos humanos y convivencia pacífica'
    ],

    // EDUCACIÓN RELIGIOSA - GRADOS 1° A 11°
    'educacion_religiosa' => [
        'nombre' => 'Educación Religiosa',
        'area' => 'Transversal Obligatoria',
        'base_legal' => 'Ley 115/94, Decreto 4500/2006',
        'grados' => generarDBAReligion(),
        'descripcion' => 'Formación integral, diálogo interreligioso, respeto por la diversidad de creencias'
    ],

    // EDUCACIÓN ARTÍSTICA - GRADOS 1° A 11°
    'educacion_artistica' => [
        'nombre' => 'Educación Artística y Cultural',
        'area' => 'Transversal Obligatoria',
        'base_legal' => 'Ley 115/94, Artículo 23',
        'grados' => generarDBAArtistica(),
        'descripcion' => 'Desarrollo de sensibilidad estética, expresión creativa, apreciación artística y patrimonio cultural'
    ],

    // EDUCACIÓN FÍSICA - GRADOS 1° A 11°
    'educacion_fisica' => [
        'nombre' => 'Educación Física, Recreación y Deporte',
        'area' => 'Transversal Obligatoria',
        'base_legal' => 'Ley 115/94, Artículo 23',
        'grados' => generarDBAFisica(),
        'descripcion' => 'Desarrollo motor, hábitos saludables, trabajo en equipo, deporte formativo'
    ],

    // TECNOLOGÍA E INFORMÁTICA - GRADOS 1° A 11°
    'tecnologia_informatica' => [
        'nombre' => 'Tecnología e Informática',
        'area' => 'Transversal Obligatoria',
        'base_legal' => 'Ley 115/94, Artículo 23',
        'grados' => generarDBATecnologia(),
        'descripcion' => 'Alfabetización digital, pensamiento computacional, uso responsable de tecnología'
    ],

    // CÁTEDRA DE LA PAZ - GRADOS 1° A 11°
    'catedra_paz' => [
        'nombre' => 'Cátedra de la Paz',
        'area' => 'Cátedra Obligatoria',
        'base_legal' => 'Ley 1732/2014, Decreto 1038/2015',
        'grados' => generarDBAPaz(),
        'descripcion' => 'Cultura de paz, memoria histórica, resolución pacífica de conflictos, derechos humanos'
    ],

    // FORMACIÓN CIUDADANA - GRADOS 1° A 11°
    'formacion_ciudadana' => [
        'nombre' => 'Competencias Ciudadanas',
        'area' => 'Competencias Transversales',
        'base_legal' => 'Estándares Básicos de Competencias Ciudadanas - MEN',
        'grados' => generarDBACiudadana(),
        'descripcion' => 'Convivencia pacífica, participación democrática, pluralidad, identidad y valoración de diferencias'
    ],

    // CÁTEDRA AFROCOLOMBIANA - GRADOS 1° A 11°
    'catedra_afrocolombiana' => [
        'nombre' => 'Cátedra de Estudios Afrocolombianos',
        'area' => 'Cátedra Obligatoria',
        'base_legal' => 'Ley 70/1993, Decreto 1122/1998',
        'grados' => generarDBAAfro(),
        'descripcion' => 'Reconocimiento y valoración de los aportes afrocolombianos a la cultura nacional'
    ]
];

// FUNCIONES GENERADORAS DE DBA PARA ÁREAS TRANSVERSALES

function generarDBAEtica()
{
    $dbas = [];
    $grados = range(1, 11);

    $descripciones = [
        1 => [
            'Reconoce y valora su propio cuerpo como base de su identidad personal',
            'Identifica emociones básicas (alegría, tristeza, miedo, ira) en sí mismo y en los demás',
            'Practica normas básicas de convivencia en el aula (saludar, pedir permiso, dar las gracias)',
            'Valora la amistad y el respeto en las relaciones con sus compañeros',
            'Reconoce la importancia de decir la verdad y ser honesto'
        ],
        2 => [
            'Comprende la importancia de las normas para la convivencia armónica en el aula y la familia',
            'Identifica y expresa sus emociones de manera adecuada en diferentes situaciones',
            'Reconoce la diversidad como valor en el aula (diferentes gustos, habilidades, procedencias)',
            'Practica la escucha activa y el diálogo para resolver diferencias',
            'Valora la honestidad en las relaciones cotidianas con compañeros y adultos'
        ],
        3 => [
            'Reconoce la importancia de la autonomía y la responsabilidad en sus acciones cotidianas',
            'Identifica situaciones de injusticia en su entorno y propone alternativas de solución',
            'Practica la empatía y la solidaridad con compañeros que enfrentan dificultades',
            'Participa en la construcción colectiva de acuerdos de convivencia en el aula',
            'Reconoce el valor del perdón y la reconciliación en las relaciones interpersonales'
        ],
        4 => [
            'Comprende la relación entre derechos y deberes en la vida escolar y familiar',
            'Reflexiona sobre las consecuencias de sus acciones en los demás y en el entorno',
            'Practica el diálogo como herramienta fundamental para resolver conflictos',
            'Reconoce la importancia del cuidado de sí mismo, de los otros y del ambiente',
            'Valora la diversidad de opiniones como oportunidad de aprendizaje'
        ],
        5 => [
            'Analiza críticamente situaciones cotidianas donde se vulneran derechos fundamentales',
            'Desarrolla habilidades para la toma de decisiones éticas en contextos escolares',
            'Practica la resolución pacífica de conflictos mediante la negociación y el consenso',
            'Reconoce la importancia de la justicia y la equidad en las relaciones sociales',
            'Participa activamente en iniciativas de servicio y ayuda a la comunidad'
        ],
        6 => [
            'Comprende conceptos fundamentales de ética: libertad, responsabilidad, dignidad, justicia',
            'Reflexiona sobre la relación entre desarrollo moral y construcción de proyecto de vida',
            'Analiza dilemas éticos cotidianos y propone alternativas fundamentadas',
            'Reconoce la importancia de los derechos humanos como marco para la convivencia',
            'Desarrolla habilidades para la autoevaluación y el mejoramiento personal'
        ],
        7 => [
            'Analiza la relación entre ética, moral y normas sociales en diferentes contextos',
            'Reflexiona sobre la influencia del entorno familiar, social y cultural en la formación de valores',
            'Practica la empatía y la compasión como fundamentos de la acción ética',
            'Reconoce la importancia de la coherencia entre el discurso y la acción',
            'Participa en debates sobre temas éticos de actualidad (medio ambiente, tecnología, redes sociales)'
        ],
        8 => [
            'Comprende las principales teorías éticas (utilitarismo, deontología, ética de virtudes)',
            'Analiza críticamente situaciones de corrupción, injusticia y exclusión en la sociedad',
            'Reflexiona sobre la relación entre desarrollo personal, bien común y justicia social',
            'Desarrolla habilidades para argumentar éticamente en discusiones y debates',
            'Reconoce la importancia de la memoria histórica y la no repetición de violencias'
        ],
        9 => [
            'Analiza la relación entre ética, política y ciudadanía en contextos democráticos',
            'Reflexiona sobre los desafíos éticos contemporáneos (biotecnología, inteligencia artificial, medio ambiente)',
            'Desarrolla pensamiento crítico frente a discursos discriminatorios y excluyentes',
            'Participa en iniciativas de transformación social desde principios éticos',
            'Comprende la importancia del liderazgo ético y el servicio a los demás'
        ],
        10 => [
            'Profundiza en el estudio de problemas éticos fundamentales (vida, muerte, libertad, justicia)',
            'Analiza la relación entre ética, ciencia y tecnología en la sociedad contemporánea',
            'Reflexiona sobre el sentido de la vida y la construcción de proyectos existenciales',
            'Desarrolla habilidades para el diálogo intercultural e interreligioso desde el respeto',
            'Reconoce la importancia de la ética profesional en la elección vocacional'
        ],
        11 => [
            'Sistematiza y aplica principios éticos en la construcción de su proyecto de vida',
            'Analiza críticamente problemas éticos globales (pobreza, desigualdad, cambio climático, conflictos)',
            'Reflexiona sobre el papel de la educación ética en la construcción de paz y democracia',
            'Desarrolla compromiso activo con la defensa de los derechos humanos y la dignidad',
            'Prepara su transición a la vida universitaria o laboral con sólidos fundamentos éticos'
        ]
    ];

    foreach ($grados as $grado) {
        $dbas[$grado] = [
            'dba' => $descripciones[$grado],
            'competencias' => [
                'Autoconocimiento y autorregulación',
                'Empatía y cuidado de otros',
                'Pensamiento crítico y ético',
                'Participación y responsabilidad democrática',
                'Resolución pacífica de conflictos'
            ]
        ];
    }

    return $dbas;
}

function generarDBAReligion()
{
    $dbas = [];
    $grados = range(1, 11);

    $descripciones = [
        1 => [
            'Reconoce la familia como espacio fundamental para el desarrollo humano y espiritual',
            'Identifica valores religiosos y morales presentes en su entorno familiar y escolar',
            'Respeta las diferentes manifestaciones religiosas que observa en su comunidad',
            'Comprende el sentido de las celebraciones religiosas en su contexto cultural',
            'Valora la vida como don recibido y expresa gratitud'
        ],
        2 => [
            'Comprende la importancia de la amistad y el amor como valores fundamentales en la comunidad',
            'Identifica relatos religiosos de su tradición cultural (narraciones bíblicas, parábolas)',
            'Reconoce la naturaleza como creación y expresa cuidado por el medio ambiente',
            'Valora los momentos de encuentro comunitario y celebración como espacios de fraternidad',
            'Respeta las diferencias en creencias religiosas de sus compañeros'
        ],
        3 => [
            'Reconoce la dimensión espiritual como parte integral del ser humano',
            'Identifica figuras religiosas significativas en diferentes tradiciones (fundadores, profetas, líderes)',
            'Comprende el significado de símbolos religiosos en distintas culturas',
            'Practica la solidaridad y el servicio como expresión de valores religiosos',
            'Reflexiona sobre preguntas existenciales básicas (origen, sentido, destino)'
        ],
        4 => [
            'Comprende los valores fundamentales presentes en diferentes tradiciones religiosas',
            'Analiza relatos sagrados de diversas culturas reconociendo enseñanzas universales',
            'Reconoce la importancia del diálogo interreligioso para la convivencia pacífica',
            'Valora las expresiones artísticas religiosas como patrimonio cultural',
            'Reflexiona sobre la relación entre fe, cultura y vida cotidiana'
        ],
        5 => [
            'Identifica elementos comunes y diferencias entre las principales tradiciones religiosas',
            'Comprende el papel de la religión en la construcción de identidad personal y cultural',
            'Analiza la relación entre valores religiosos y derechos humanos',
            'Participa respetuosamente en diálogos sobre creencias y convicciones personales',
            'Reflexiona sobre el aporte de las religiones a la construcción de paz'
        ],
        6 => [
            'Comprende el fenómeno religioso como dimensión humana universal a lo largo de la historia',
            'Analiza las expresiones religiosas en las primeras civilizaciones (Mesopotamia, Egipto, Grecia, Roma)',
            'Reconoce la influencia de la religión en la organización social y cultural',
            'Identifica elementos de las religiones orientales (hinduismo, budismo, taoísmo)',
            'Valora la diversidad religiosa como riqueza cultural de la humanidad'
        ],
        7 => [
            'Comprende el origen y desarrollo de las religiones monoteístas (judaísmo, cristianismo, islam)',
            'Analiza el papel de la religión en la configuración de la cultura occidental',
            'Reconoce la influencia del cristianismo en la historia de Colombia y América Latina',
            'Identifica elementos de diálogo y conflicto entre religiones a lo largo de la historia',
            'Reflexiona sobre la libertad religiosa como derecho fundamental'
        ],
        8 => [
            'Analiza la relación entre religión, ciencia y filosofía en diferentes épocas históricas',
            'Comprende el proceso de secularización y su impacto en la sociedad contemporánea',
            'Reconoce la presencia de lo religioso en manifestaciones culturales actuales (arte, música, cine)',
            'Analiza críticamente fundamentalismos religiosos y su impacto social',
            'Valora el ecumenismo y el diálogo interreligioso como caminos de paz'
        ],
        9 => [
            'Analiza la relación entre religión, ética y construcción de sentido en la modernidad',
            'Comprende las nuevas expresiones de espiritualidad en la sociedad contemporánea',
            'Reconoce el aporte de las religiones a la defensa de los derechos humanos y la paz',
            'Reflexiona sobre preguntas existenciales desde diferentes perspectivas religiosas y no religiosas',
            'Participa en iniciativas interreligiosas de servicio comunitario'
        ],
        10 => [
            'Profundiza en el estudio de textos sagrados desde perspectivas histórico-críticas',
            'Analiza la relación entre religión, política y sociedad en contextos contemporáneos',
            'Comprende el fenómeno de la globalización y su impacto en las tradiciones religiosas',
            'Reflexiona sobre desafíos éticos contemporáneos desde perspectivas religiosas',
            'Desarrolla habilidades para el acompañamiento espiritual y la consejería'
        ],
        11 => [
            'Sistematiza una visión integral del fenómeno religioso en la historia humana',
            'Analiza críticamente el papel de la religión en conflictos y procesos de reconciliación',
            'Reflexiona sobre la construcción de sentido y proyecto de vida desde dimensiones espirituales',
            'Reconoce el pluralismo religioso como realidad social y principio democrático',
            'Prepara su transición a la vida adulta con apertura respetuosa a diversas cosmovisiones'
        ]
    ];

    foreach ($grados as $grado) {
        $dbas[$grado] = [
            'dba' => $descripciones[$grado],
            'ejes' => [
                'Antropología religiosa',
                'Hecho religioso y cultura',
                'Diálogo interreligioso',
                'Valores y sentido de vida',
                'Religión y sociedad'
            ]
        ];
    }

    return $dbas;
}

function generarDBAArtistica()
{
    $dbas = [];
    $grados = range(1, 11);

    $descripciones = [
        1 => [
            'Explora las posibilidades expresivas de su cuerpo a través del movimiento y el gesto',
            'Reconoce colores primarios y secundarios en su entorno y en producciones artísticas',
            'Disfruta de canciones, rondas infantiles y juegos musicales tradicionales',
            'Representa gráficamente su entorno familiar y escolar usando diferentes materiales',
            'Explora materiales reciclables y naturales para crear expresiones artísticas'
        ],
        2 => [
            'Combina colores para crear nuevas tonalidades en sus producciones plásticas',
            'Representa situaciones cotidianas y fantásticas mediante dibujos y pinturas',
            'Explora ritmos musicales básicos utilizando el cuerpo, la voz e instrumentos sencillos',
            'Crea figuras tridimensionales con diferentes técnicas (modelado, ensamblaje, construcción)',
            'Participa en representaciones teatrales grupales sencillas (juegos de roles, títeres)'
        ],
        3 => [
            'Reconoce obras artísticas de su cultura local (pintura, música, danza, teatro)',
            'Expresa emociones, ideas y vivencias a través de diferentes lenguajes artísticos',
            'Explora técnicas mixtas en la creación plástica (collage, estampado, frottage)',
            'Participa en montajes escénicos sencillos expresando personajes y situaciones',
            'Reconoce elementos del lenguaje musical (ritmo, melodía, intensidad, timbre)'
        ],
        4 => [
            'Comprende la importancia del patrimonio artístico y cultural de Colombia',
            'Desarrolla habilidades técnicas en al menos un lenguaje artístico (plástica, música, danza, teatro)',
            'Analiza elementos formales en obras de arte (composición, color, textura, movimiento)',
            'Participa en procesos creativos colectivos respetando aportes de los demás',
            'Reconoce manifestaciones artísticas de diferentes regiones colombianas'
        ],
        5 => [
            'Analiza la relación entre arte, cultura y sociedad en diferentes contextos',
            'Desarrolla proyectos artísticos individuales y colectivos con intención comunicativa',
            'Reconoce estilos y movimientos artísticos en la historia del arte',
            'Valora la diversidad de expresiones artísticas en su comunidad y el país',
            'Participa en eventos artísticos y culturales como creador o espectador crítico'
        ],
        6 => [
            'Comprende el arte como forma de conocimiento y expresión humana a lo largo de la historia',
            'Analiza elementos del lenguaje visual en diferentes manifestaciones artísticas',
            'Explora técnicas artísticas tradicionales y contemporáneas en diversos lenguajes',
            'Desarrolla habilidades para la apreciación estética y el análisis crítico de obras',
            'Participa en procesos creativos que integran diferentes lenguajes artísticos'
        ],
        7 => [
            'Analiza el arte precolombino y colonial en América y Colombia',
            'Comprende la relación entre arte, identidad cultural y memoria histórica',
            'Desarrolla proyectos artísticos que expresan visiones personales del entorno',
            'Explora manifestaciones artísticas contemporáneas (performance, instalación, arte digital)',
            'Reconoce el papel del arte en la transformación social y comunitaria'
        ],
        8 => [
            'Analiza movimientos artísticos modernos y contemporáneos en Colombia y Latinoamérica',
            'Comprende la relación entre arte, política y sociedad en el siglo XX',
            'Desarrolla portafolio artístico personal con producción original',
            'Participa en montajes y exposiciones colectivas asumiendo roles diversos',
            'Reflexiona críticamente sobre la industria cultural y el mercado del arte'
        ],
        9 => [
            'Analiza tendencias artísticas globales y su relación con contextos locales',
            'Comprende el arte como campo profesional y sus diferentes ocupaciones',
            'Desarrolla proyectos artísticos interdisciplinarios con fundamentación conceptual',
            'Investiga manifestaciones artísticas emergentes en la cultura juvenil',
            'Participa en procesos de gestión cultural comunitaria'
        ],
        10 => [
            'Profundiza en el estudio de estéticas contemporáneas y filosofía del arte',
            'Analiza la relación entre arte, nuevos medios y tecnología digital',
            'Desarrolla proyectos artísticos con mayor complejidad técnica y conceptual',
            'Investiga sobre artistas colombianos contemporáneos y sus propuestas',
            'Prepara portafolio para estudios superiores en artes o carreras afines'
        ],
        11 => [
            'Sistematiza una comprensión integral del arte en la cultura contemporánea',
            'Analiza críticamente el papel del arte en la construcción de memoria y paz en Colombia',
            'Desarrolla proyecto artístico de grado con sustentación teórico-práctica',
            'Reflexiona sobre el papel del arte en su proyecto de vida y vocación',
            'Participa activamente en la vida cultural de su comunidad como agente creativo'
        ]
    ];

    foreach ($grados as $grado) {
        $dbas[$grado] = [
            'dba' => $descripciones[$grado],
            'lenguajes' => ['Artes plásticas', 'Música', 'Danza', 'Teatro', 'Audiovisuales']
        ];
    }

    return $dbas;
}

function generarDBAFisica()
{
    $dbas = [];
    $grados = range(1, 11);

    $descripciones = [
        1 => [
            'Reconoce las partes de su cuerpo y sus funciones básicas en reposo y movimiento',
            'Explora movimientos locomotores básicos (caminar, correr, saltar, gatear, rodar)',
            'Participa en juegos individuales y colectivos respetando reglas simples',
            'Practica hábitos de higiene corporal después de la actividad física',
            'Desarrolla coordinación visomotora básica (lanzar, atrapar, patear)'
        ],
        2 => [
            'Controla movimientos locomotores en diferentes direcciones, velocidades y niveles',
            'Desarrolla equilibrio estático y dinámico en diferentes posiciones y desplazamientos',
            'Participa en juegos tradicionales colombianos (rondas, canciones, juegos de patio)',
            'Practica ejercicios de flexibilidad básica respetando límites corporales',
            'Reconoce la respiración y el ritmo cardíaco durante la actividad física'
        ],
        3 => [
            'Combina patrones básicos de movimiento en secuencias y circuitos',
            'Participa en juegos predeportivos que desarrollan habilidades motrices específicas',
            'Practica ejercicios de fuerza básica utilizando el propio peso corporal',
            'Reconoce la importancia de la actividad física para la salud y el bienestar',
            'Trabaja en equipo durante juegos cooperativos valorando el aporte de todos'
        ],
        4 => [
            'Aplica habilidades motrices específicas en situaciones de juego y deporte escolar',
            'Participa en actividades rítmicas y expresivas (bailes, coreografías simples)',
            'Practica fundamentos básicos de deportes individuales (atletismo, natación) y colectivos',
            'Comprende reglas básicas de juegos y deportes y las aplica en la práctica',
            'Valora la actividad física como espacio de integración y disfrute'
        ],
        5 => [
            'Desarrolla capacidades físicas condicionales (resistencia, fuerza, velocidad, flexibilidad)',
            'Participa en competencias deportivas escolares con espíritu deportivo',
            'Practica actividades en entornos naturales valorando el cuidado ambiental',
            'Reconoce la relación entre actividad física, alimentación saludable y bienestar',
            'Analiza su propio desempeño motor identificando fortalezas y aspectos a mejorar'
        ],
        6 => [
            'Comprende los sistemas del cuerpo humano involucrados en la actividad física',
            'Desarrolla habilidades motrices específicas en diferentes deportes',
            'Participa en la organización y desarrollo de eventos deportivos escolares',
            'Practica ejercicios de calentamiento y vuelta a la calma de manera autónoma',
            'Reconoce la importancia del juego limpio y el respeto a rivales y árbitros'
        ],
        7 => [
            'Analiza los principios del entrenamiento deportivo básico',
            'Desarrolla capacidades coordinativas (equilibrio, ritmo, reacción, orientación)',
            'Participa en deportes de conjunto aplicando fundamentos tácticos básicos',
            'Practica actividades físicas para la reducción del estrés y la ansiedad',
            'Valora la diversidad de habilidades y capacidades en la clase de educación física'
        ],
        8 => [
            'Comprende la fisiología del ejercicio y los sistemas energéticos',
            'Desarrolla planes básicos de entrenamiento para mejorar capacidades físicas',
            'Participa en deportes alternativos y actividades recreativas diversas',
            'Analiza la relación entre actividad física, prevención de enfermedades y calidad de vida',
            'Reconoce la importancia de la nutrición deportiva en el rendimiento'
        ],
        9 => [
            'Analiza los principios biomecánicos aplicados al movimiento humano',
            'Desarrolla habilidades para el liderazgo y la organización de actividades deportivas',
            'Participa en actividades físicas de alta exigencia con responsabilidad y autocuidado',
            'Reflexiona sobre la influencia social del deporte (profesionalismo, doping, violencia)',
            'Explora diferentes manifestaciones de la cultura física (fitness, yoga, artes marciales)'
        ],
        10 => [
            'Comprende la psicología del deporte y su influencia en el rendimiento',
            'Desarrolla proyectos de promoción de actividad física en la comunidad escolar',
            'Analiza críticamente la industria del deporte y el espectáculo',
            'Practica actividades físicas para el mantenimiento de la salud a lo largo de la vida',
            'Reconoce el deporte como herramienta de inclusión social y construcción de paz'
        ],
        11 => [
            'Sistematiza conocimientos sobre ciencias de la actividad física y el deporte',
            'Desarrolla programa personal de acondicionamiento físico para proyecto de vida',
            'Analiza tendencias contemporáneas en actividad física y bienestar',
            'Reflexiona sobre oportunidades profesionales en el campo de la educación física y el deporte',
            'Promueve estilos de vida activos y saludables en su comunidad'
        ]
    ];

    foreach ($grados as $grado) {
        $dbas[$grado] = [
            'dba' => $descripciones[$grado],
            'componentes' => ['Motricidad', 'Salud y calidad de vida', 'Juego y deporte', 'Expresión corporal', 'Gestión y emprendimiento']
        ];
    }

    return $dbas;
}

function generarDBATecnologia()
{
    $dbas = [];
    $grados = range(1, 11);

    $descripciones = [
        1 => [
            'Reconoce artefactos tecnológicos de su entorno cotidiano (electrodomésticos, herramientas)',
            'Identifica partes básicas del computador y sus funciones (pantalla, teclado, ratón)',
            'Explora programas educativos sencillos (dibujo, juegos, presentaciones)',
            'Comprende la función de los artefactos tecnológicos en casa y escuela',
            'Practica normas básicas de seguridad y cuidado en el uso de artefactos'
        ],
        2 => [
            'Diferencia artefactos tecnológicos según su uso y función (comunicación, transporte, hogar)',
            'Utiliza programas de dibujo y escritura básicos para crear producciones simples',
            'Reconoce la evolución de artefactos tecnológicos (teléfono, televisor, computador)',
            'Navega en páginas web educativas con supervisión y orientación',
            'Comprende la importancia de la energía eléctrica para el funcionamiento de artefactos'
        ],
        3 => [
            'Identifica materiales según su origen (natural, artificial) y propiedades básicas',
            'Utiliza herramientas tecnológicas simples para crear, organizar y presentar información',
            'Reconoce procesos de fabricación de objetos tecnológicos cotidianos',
            'Comprende la importancia de las comunicaciones (teléfono, internet, correo)',
            'Practica normas de seguridad y comportamiento en el uso de internet'
        ],
        4 => [
            'Analiza la relación entre necesidades humanas y creación de artefactos tecnológicos',
            'Utiliza procesadores de texto, hojas de cálculo y presentaciones para proyectos escolares',
            'Reconoce fuentes de energía y su transformación en diferentes artefactos',
            'Comprende el concepto de sistema tecnológico y sus componentes',
            'Participa en proyectos colaborativos utilizando herramientas digitales'
        ],
        5 => [
            'Analiza ventajas y desventajas de diferentes soluciones tecnológicas a problemas cotidianos',
            'Utiliza internet para buscar, seleccionar y organizar información de manera crítica',
            'Reconoce el impacto ambiental de la producción y uso de artefactos tecnológicos',
            'Comprende conceptos básicos de programación (algoritmos, secuencias, instrucciones)',
            'Desarrolla proyectos tecnológicos sencillos siguiendo metodología de diseño'
        ],
        6 => [
            'Comprende la evolución histórica de la tecnología y su impacto en la sociedad',
            'Utiliza herramientas ofimáticas para la producción y presentación de trabajos académicos',
            'Reconoce los componentes de un sistema informático (hardware, software, redes)',
            'Practica ciudadanía digital responsable (netiqueta, privacidad, seguridad)',
            'Desarrolla algoritmos sencillos para resolver problemas cotidianos'
        ],
        7 => [
            'Analiza la relación entre ciencia, tecnología y sociedad en contextos específicos',
            'Utiliza hojas de cálculo para organizar datos, realizar cálculos y generar gráficos',
            'Comprende conceptos básicos de redes informáticas e internet',
            'Reconoce los derechos de autor y licencias de software en entornos digitales',
            'Desarrolla proyectos tecnológicos con enfoque de diseño thinking'
        ],
        8 => [
            'Analiza los principios de funcionamiento de sistemas tecnológicos complejos',
            'Utiliza herramientas multimedia para crear contenidos digitales (audio, video, animación)',
            'Comprende fundamentos de programación estructurada y lógica computacional',
            'Reconoce riesgos y oportunidades de las tecnologías emergentes (IA, IoT, Big Data)',
            'Desarrolla habilidades para la solución de problemas usando pensamiento computacional'
        ],
        9 => [
            'Analiza críticamente el papel de la tecnología en el desarrollo económico y social',
            'Utiliza herramientas para creación de contenidos web (blogs, sitios, redes)',
            'Comprende fundamentos de electrónica básica y robótica',
            'Reconoce implicaciones éticas, sociales y ambientales de la innovación tecnológica',
            'Desarrolla proyectos tecnológicos con impacto social en su comunidad'
        ],
        10 => [
            'Analiza tendencias tecnológicas globales y su impacto en el mundo del trabajo',
            'Utiliza software especializado según intereses vocacionales (diseño, programación, análisis)',
            'Comprende fundamentos de inteligencia artificial y aprendizaje automático',
            'Reconoce la importancia de la ciberseguridad y protección de datos personales',
            'Desarrolla habilidades emprendedoras basadas en tecnología'
        ],
        11 => [
            'Sistematiza una visión crítica sobre el papel de la tecnología en la sociedad contemporánea',
            'Domina herramientas digitales avanzadas para su proyecto de vida (educación superior, trabajo)',
            'Analiza las transformaciones sociales, culturales y éticas de la revolución digital',
            'Reflexiona sobre oportunidades profesionales en campos tecnológicos',
            'Desarrolla proyecto tecnológico de grado con potencial de emprendimiento o impacto social'
        ]
    ];

    foreach ($grados as $grado) {
        $dbas[$grado] = [
            'dba' => $descripciones[$grado],
            'componentes' => ['Naturaleza y evolución tecnológica', 'Apropiación y uso de tecnología', 'Solución de problemas con tecnología', 'Tecnología y sociedad']
        ];
    }

    return $dbas;
}

function generarDBAPaz()
{
    $dbas = [];
    $grados = range(1, 11);

    $descripciones = [
        1 => [
            'Reconoce la paz como valor fundamental en la convivencia cotidiana',
            'Practica formas pacíficas de resolver conflictos en el aula (diálogo, negociación, acuerdos)',
            'Identifica la violencia como algo dañino para todos en sus diferentes formas',
            'Valora la amistad, el compañerismo y la ayuda mutua como expresiones de paz',
            'Participa en la construcción colectiva de acuerdos de convivencia en el aula'
        ],
        2 => [
            'Comprende la importancia del perdón y la reconciliación en las relaciones interpersonales',
            'Identifica formas de violencia en su entorno (física, verbal, exclusión) y cómo evitarlas',
            'Propone soluciones pacíficas a conflictos cotidianos en la escuela y familia',
            'Valora la diversidad como oportunidad de aprendizaje y enriquecimiento mutuo',
            'Participa en proyectos colaborativos que promueven la convivencia pacífica'
        ],
        3 => [
            'Reconoce la importancia de los derechos humanos para la convivencia pacífica',
            'Identifica mecanismos de participación democrática en el aula y la escuela',
            'Practica la escucha activa y la empatía en situaciones de conflicto',
            'Valora la mediación como estrategia para resolver diferencias',
            'Participa en campañas escolares de promoción de la paz y la no violencia'
        ],
        4 => [
            'Comprende el conflicto como oportunidad de aprendizaje y transformación positiva',
            'Analiza causas y consecuencias de conflictos en diferentes contextos',
            'Desarrolla habilidades para la negociación y el consenso en grupos',
            'Reconoce la importancia de la memoria histórica para la construcción de paz',
            'Participa en iniciativas de servicio comunitario que promueven la reconciliación'
        ],
        5 => [
            'Analiza la relación entre justicia, equidad y construcción de paz',
            'Comprende conceptos básicos sobre conflicto armado en Colombia',
            'Reconoce el papel de las víctimas en los procesos de reconciliación',
            'Valora experiencias de construcción de paz en comunidades locales',
            'Participa en proyectos escolares de memoria histórica y cultura de paz'
        ],
        6 => [
            'Comprende el concepto de paz positiva (no solo ausencia de guerra, sino justicia social)',
            'Analiza causas estructurales de los conflictos (desigualdad, exclusión, discriminación)',
            'Reconoce experiencias históricas de construcción de paz en Colombia',
            'Desarrolla habilidades para el análisis crítico de conflictos sociales',
            'Participa en iniciativas juveniles de promoción de derechos humanos'
        ],
        7 => [
            'Analiza el conflicto armado colombiano: causas, actores, etapas y consecuencias',
            'Comprende los procesos de paz en Colombia (acuerdos, negociaciones, desmovilizaciones)',
            'Reconoce el papel de organizaciones sociales en la construcción de paz',
            'Valora la importancia de la justicia transicional y los derechos de las víctimas',
            'Participa en ejercicios de memoria histórica en su comunidad'
        ],
        8 => [
            'Analiza el Acuerdo de Paz con las FARC-EP (2016): contenidos, implementación y desafíos',
            'Comprende conceptos de justicia restaurativa, verdad, reparación y no repetición',
            'Reconoce experiencias de reconciliación en territorios afectados por el conflicto',
            'Analiza críticamente discursos sobre paz y violencia en medios de comunicación',
            'Participa en proyectos pedagógicos sobre cultura de paz y reconciliación'
        ],
        9 => [
            'Analiza los desafíos actuales para la construcción de paz en Colombia (violencias emergentes, narcotráfico, desigualdad)',
            'Comprende la relación entre paz, desarrollo sostenible y construcción de territorios',
            'Reconoce el papel de jóvenes, mujeres y comunidades étnicas en procesos de paz',
            'Analiza experiencias internacionales de construcción de paz posconflicto',
            'Desarrolla propuestas de incidencia para la paz desde entornos escolares'
        ],
        10 => [
            'Profundiza en teorías de paz y conflicto desde diferentes perspectivas disciplinares',
            'Analiza la relación entre memoria histórica, verdad y reconciliación en Colombia',
            'Comprende los desafíos de la implementación de acuerdos de paz en contextos locales',
            'Reconoce experiencias de resistencia civil y construcción de paz desde bases',
            'Participa en investigaciones escolares sobre memoria y construcción de paz'
        ],
        11 => [
            'Sistematiza una comprensión integral del conflicto y la paz en Colombia',
            'Analiza críticamente el momento actual del posconflicto y sus perspectivas',
            'Reflexiona sobre su papel como ciudadano en la construcción de paz duradera',
            'Desarrolla propuestas de intervención para la paz en su comunidad',
            'Prepara su transición a la vida adulta con compromiso activo por la paz y la reconciliación'
        ]
    ];

    foreach ($grados as $grado) {
        $dbas[$grado] = [
            'dba' => $descripciones[$grado],
            'ejes' => ['Cultura de paz', 'Educación para la paz', 'Desarrollo sostenible', 'Participación democrática', 'Memoria histórica y reconciliación']
        ];
    }

    return $dbas;
}

function generarDBACiudadana()
{
    $dbas = [];
    $grados = range(1, 11);

    $descripciones = [
        1 => [
            'Reconoce sus derechos como niño y niña en la familia, escuela y comunidad',
            'Identifica deberes y responsabilidades en el aula y el hogar',
            'Participa en decisiones del curso mediante mecanismos democráticos básicos',
            'Reconoce los símbolos patrios y su significado (bandera, escudo, himno)',
            'Practica normas básicas de convivencia escolar respetando a compañeros y adultos'
        ],
        2 => [
            'Comprende la importancia de participar en el gobierno escolar',
            'Reconoce las funciones del personero estudiantil y representantes de curso',
            'Practica la democracia en elecciones del aula y actividades colectivas',
            'Identifica derechos fundamentales de los niños (salud, educación, protección, identidad)',
            'Participa en proyectos de servicio comunitario sencillos'
        ],
        3 => [
            'Comprende la función de las autoridades en su comunidad (alcalde, policía, profesores, padres)',
            'Identifica mecanismos de participación ciudadana a su alcance (elecciones, sugerencias)',
            'Reconoce la importancia de las normas para la convivencia social',
            'Valora la diversidad cultural, étnica y social en su entorno escolar',
            'Participa en iniciativas de cuidado del espacio público y el medio ambiente'
        ],
        4 => [
            'Reconoce la Constitución Política como norma de normas en Colombia',
            'Identifica las ramas del poder público (ejecutiva, legislativa, judicial) y sus funciones básicas',
            'Comprende la organización territorial de Colombia (departamentos, municipios, distritos)',
            'Analiza la importancia del voto como mecanismo de participación democrática',
            'Participa en el gobierno escolar asumiendo roles de representación'
        ],
        5 => [
            'Analiza la relación entre democracia, participación ciudadana y bien común',
            'Comprende los derechos humanos como fundamento de la convivencia democrática',
            'Reconoce mecanismos de protección de derechos (tutela, derechos de petición)',
            'Analiza situaciones de discriminación y exclusión en su entorno',
            'Participa activamente en proyectos ciudadanos en la escuela y comunidad'
        ],
        6 => [
            'Comprende conceptos fundamentales de ciudadanía: derechos, deberes, participación, bien común',
            'Analiza la evolución histórica de la ciudadanía en Colombia',
            'Reconoce mecanismos de participación ciudadana (voto, plebiscito, referendo, consulta popular)',
            'Identifica formas de organización social (juntas de acción comunal, ONG, sindicatos)',
            'Participa en debates sobre temas de interés público en la escuela'
        ],
        7 => [
            'Analiza la relación entre Estado, sociedad civil y ciudadanía en democracia',
            'Comprende la estructura del Estado colombiano y sus funciones',
            'Reconoce los organismos de control (Procuraduría, Contraloría, Defensoría del Pueblo)',
            'Analiza problemáticas sociales locales y propone alternativas de solución',
            'Participa en iniciativas de control social a la gestión pública'
        ],
        8 => [
            'Analiza críticamente fenómenos como corrupción, clientelismo y populismo',
            'Comprende la importancia de la transparencia y el acceso a la información pública',
            'Reconoce experiencias de participación ciudadana y control social en Colombia',
            'Analiza el papel de los medios de comunicación en la formación de opinión pública',
            'Desarrolla habilidades para la incidencia política desde entornos juveniles'
        ],
        9 => [
            'Analiza la relación entre ciudadanía, movimientos sociales y cambio social',
            'Comprende el concepto de ciudadanía global y responsabilidades frente a problemas mundiales',
            'Reconoce experiencias de participación política juvenil en Colombia',
            'Analiza críticamente discursos políticos y propuestas de campañas electorales',
            'Participa en iniciativas de movilización y acción colectiva por causas sociales'
        ],
        10 => [
            'Profundiza en teorías políticas contemporáneas y modelos de democracia',
            'Analiza el sistema político colombiano: partidos, elecciones, reformas políticas',
            'Comprende la relación entre ciudadanía, derechos humanos y construcción de paz',
            'Reconoce desafíos de la democracia colombiana (abstencionismo, polarización, desconfianza)',
            'Participa en ejercicios de simulación de instancias de participación (concejos, asambleas)'
        ],
        11 => [
            'Sistematiza una comprensión integral de la ciudadanía en contextos democráticos contemporáneos',
            'Analiza críticamente el momento político colombiano y sus perspectivas',
            'Reflexiona sobre su papel como ciudadano en la construcción de sociedad',
            'Desarrolla habilidades para el liderazgo político y social con principios éticos',
            'Prepara su transición a la vida adulta con compromiso ciudadano activo'
        ]
    ];

    foreach ($grados as $grado) {
        $dbas[$grado] = [
            'dba' => $descripciones[$grado],
            'competencias' => ['Convivencia y paz', 'Participación y responsabilidad democrática', 'Pluralidad, identidad y valoración de diferencias']
        ];
    }

    return $dbas;
}

function generarDBAAfro()
{
    $dbas = [];
    $grados = range(1, 11);

    $descripciones = [
        1 => [
            'Reconoce la diversidad cultural de Colombia como riqueza de la nación',
            'Identifica tradiciones afrocolombianas presentes en su comunidad (música, cuentos, comidas)',
            'Valora los aportes de las culturas afrocolombianas a la identidad nacional',
            'Disfruta de música, danzas y juegos tradicionales afrocolombianos',
            'Reconoce personajes afrocolombianos significativos en la historia y cultura'
        ],
        2 => [
            'Conoce cuentos, mitos y leyendas de la tradición oral afrocolombiana',
            'Identifica comidas típicas, instrumentos musicales y expresiones culturales de origen afrocolombiano',
            'Reconoce la presencia histórica y actual de comunidades afrocolombianas en Colombia',
            'Valora la diversidad de tradiciones culturales afrocolombianas en diferentes regiones',
            'Respeta las tradiciones y costumbres de personas y comunidades afrocolombianas'
        ],
        3 => [
            'Comprende la historia de la esclavización de personas africanas en América y Colombia',
            'Reconoce procesos de resistencia y libertad de comunidades afrodescendientes (palenques, cimarronaje)',
            'Identifica el legado cultural africano en Colombia (música, danza, gastronomía, religiosidad)',
            'Valora la importancia de San Basilio de Palenque como patrimonio cultural',
            'Reconoce la diversidad de comunidades afrocolombianas en el Pacífico, Caribe y otras regiones'
        ],
        4 => [
            'Analiza el proceso de abolición de la esclavitud en Colombia (1851) y sus limitaciones',
            'Comprende la organización social y territorial de comunidades afrocolombianas',
            'Reconoce los aportes afrocolombianos a la ciencia, literatura, arte y deporte',
            'Identifica expresiones musicales afrocolombianas (currulao, mapalé, cumbia, abozao)',
            'Valora el patrimonio cultural inmaterial afrocolombiano'
        ],
        5 => [
            'Analiza la Ley 70 de 1993 y los derechos territoriales de comunidades afrocolombianas',
            'Comprende conceptos de etnoeducación y cátedra de estudios afrocolombianos',
            'Reconoce problemáticas actuales de comunidades afrocolombianas (discriminación, desplazamiento, pobreza)',
            'Identifica líderes y lideresas afrocolombianas contemporáneas',
            'Participa en celebraciones y conmemoraciones de la cultura afrocolombiana'
        ],
        6 => [
            'Analiza el proceso histórico de la trata transatlántica y sus consecuencias demográficas',
            'Comprende la formación de palenques como espacios de libertad y resistencia',
            'Reconoce la diversidad cultural de África antes de la colonización europea',
            'Identifica aportes africanos a la ciencia, tecnología y conocimiento universal',
            'Valora la conexión cultural entre África y la diáspora afrodescendiente'
        ],
        7 => [
            'Analiza la configuración de la sociedad colonial y el lugar de afrodescendientes en castas',
            'Comprende procesos de mestizaje y sus expresiones culturales',
            'Reconoce expresiones religiosas de origen africano en Colombia',
            'Identifica el papel de afrodescendientes en procesos de independencia',
            'Analiza representaciones de afrodescendientes en el arte colonial'
        ],
        8 => [
            'Analiza la construcción de la nación colombiana y la invisibilización de aportes afrodescendientes',
            'Comprende procesos organizativos afrocolombianos en el siglo XX',
            'Reconoce el movimiento social afrocolombiano y sus demandas',
            'Identifica el racismo estructural y sus manifestaciones contemporáneas',
            'Analiza representaciones de afrodescendientes en medios de comunicación'
        ],
        9 => [
            'Analiza la Constitución de 1991 y el reconocimiento de derechos étnicos',
            'Comprende el concepto de territorio colectivo en comunidades afrocolombianas',
            'Reconoce problemáticas territoriales actuales (minería, megaproyectos, conflicto armado)',
            'Identifica organizaciones y líderes del movimiento afrocolombiano contemporáneo',
            'Analiza políticas públicas para comunidades afrocolombianas'
        ],
        10 => [
            'Profundiza en estudios afrodiaspóricos y pensamiento decolonial',
            'Analiza el racismo epistémico y la producción de conocimiento desde perspectivas afrodescendientes',
            'Comprende las relaciones entre raza, clase y género en contextos latinoamericanos',
            'Reconoce intelectuales y pensadores afrocolombianos contemporáneos',
            'Analiza expresiones artísticas afrodescendientes contemporáneas'
        ],
        11 => [
            'Sistematiza una comprensión integral de los aportes afrocolombianos a la nación',
            'Analiza críticamente el racismo y la discriminación racial en Colombia',
            'Reflexiona sobre acciones afirmativas y reparación histórica',
            'Desarrolla propuestas para la promoción de la equidad racial en su entorno',
            'Prepara su transición a la vida adulta con compromiso antirracista'
        ]
    ];

    foreach ($grados as $grado) {
        $dbas[$grado] = [
            'dba' => $descripciones[$grado],
            'ejes' => ['Historia y cultura afrocolombiana', 'Identidad y diversidad', 'Derechos étnicos y territoriales', 'Racismo y discriminación', 'Aportes a la nación']
        ];
    }

    return $dbas;
}

// FUNCIONES AUXILIARES PARA ACCEDER A LOS DATOS

function obtenerDBA($materia, $grado)
{
    global $dba_colombia_completo;

    if ($materia === 'preescolar') {
        return $dba_colombia_completo['preescolar']['dimensiones'];
    }

    if (isset($dba_colombia_completo[$materia]['grados'][$grado])) {
        return $dba_colombia_completo[$materia]['grados'][$grado]['dba'];
    }

    return "DBA no encontrados para la materia $materia y grado $grado";
}

function listarMaterias()
{
    global $dba_colombia_completo;
    $materias = [];

    foreach ($dba_colombia_completo as $key => $materia) {
        if ($key !== 'metadata') {
            $materias[] = [
                'codigo' => $key,
                'nombre' => $materia['nombre'],
                'area' => $materia['area'] ?? $materia['nivel'] ?? 'General'
            ];
        }
    }

    return $materias;
}

// Convertir a JSON y mostrar
echo json_encode($dba_colombia_completo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// Opcional: Guardar en archivo
file_put_contents('dba_colombia_completo_hasta_once.json', json_encode($dba_colombia_completo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Función para mostrar resumen en consola
function mostrarResumen()
{
    global $dba_colombia_completo;

    echo "=== DBA COLOMBIA COMPLETO (TRANSICIÓN A 11°) ===\n\n";
    echo "Fuente: Ministerio de Educación Nacional - ColombiaAprende [citation:8]\n";
    echo "Actualización: " . date('Y-m-d') . "\n\n";

    echo "MATERIAS INCLUIDAS:\n";
    foreach ($dba_colombia_completo as $key => $materia) {
        if ($key !== 'metadata') {
            $grados = isset($materia['grados']) ? count($materia['grados']) : 0;
            $dimensiones = isset($materia['dimensiones']) ? count($materia['dimensiones']) : 0;
            echo "- {$materia['nombre']}: ";
            if ($grados > 0)
                echo "$grados grados (1° a 11°)";
            if ($dimensiones > 0)
                echo "$dimensiones dimensiones (Transición)";
            echo "\n";
        }
    }
}

// Ejecutar resumen si se llama desde consola
if (php_sapi_name() === 'cli') {
    mostrarResumen();
}

?>