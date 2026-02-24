<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Estructura base solicitada
$datos = [];

// -----------------------------------------------------------------------------
// CICLO 1: GRADOS 1° A 3° - COMPETENCIAS CIUDADANAS
// Dimensiones: Convivencia y Paz, Participación y Responsabilidad Democrática, Pluralidad
// -----------------------------------------------------------------------------
$ciudadanas_1_3 = [
    // Convivencia y Paz
    "Comprendo la importancia de valores básicos de la convivencia ciudadana como la solidaridad, el cuidado, el buen trato y el respeto por mí mismo y por los demás, y los practico en mi contexto cercano.",
    "Comprendo que todos los niños y niñas tenemos derecho a recibir buen trato, cuidado y amor.",
    "Reconozco las emociones básicas (alegría, tristeza, rabia, temor) en mí y en las otras personas.",
    "Expreso mis sentimientos y emociones mediante distintas formas y lenguajes (gestos, palabras, pintura, teatro, juegos, etc.).",
    "Reconozco que las acciones se relacionan con las emociones y que puedo aprender a manejar mis emociones para no hacer daño a otras personas.",
    "Comprendo que mis acciones pueden afectar a la gente cercana y que las acciones de la gente cercana pueden afectarme a mí.",
    "Comprendo que nada justifica el maltrato de niñas y niños y que todo maltrato se puede evitar.",
    "Identifico las situaciones de maltrato que se dan en mi entorno y sé a quiénes acudir para pedir ayuda y protección.",
    "Puedo diferenciar las expresiones verdaderas de cariño de aquellas que pueden maltratarme.",
    "Hago cosas que ayuden a aliviar el malestar de personas cercanas; manifiesto satisfacción al preocuparme por sus necesidades.",
    "Comprendo que las normas ayudan a promover el buen trato y evitar el maltrato en el juego y en la vida escolar.",
    "Identifico cómo me siento yo o las personas cercanas cuando no recibimos buen trato y expreso empatía.",
    "Conozco y respeto las reglas básicas del diálogo, como el uso de la palabra y el respeto por la palabra de la otra persona.",
    "Conozco y uso estrategias sencillas de resolución pacífica de conflictos.",
    "Conozco las señales y las normas básicas de tránsito para desplazarme con seguridad.",
    "Me preocupo porque los animales, las plantas y los recursos del medio ambiente reciban buen trato.",
    // Participación y Responsabilidad Democrática
    "Participo, en mi contexto cercano (con mi familia y compañeros), en la construcción de acuerdos básicos sobre normas para el logro de metas comunes y las cumplo.",
    "Expreso mis ideas, sentimientos e intereses en el salón y escucho respetuosamente los de los demás miembros del grupo.",
    "Manifiesto mi punto de vista cuando se toman decisiones colectivas en la casa y en la vida escolar.",
    "Reconozco que emociones como el temor o la rabia pueden afectar mi participación en clase.",
    "Manifiesto desagrado cuando a mí o a alguien del salón no nos escuchan o no nos toman en cuenta y lo expreso... sin agredir.",
    "Comprendo qué es una norma y qué es un acuerdo.",
    "Entiendo el sentido de las acciones reparadoras, es decir, de las acciones que buscan enmendar el daño causado cuando incumplo normas o acuerdos.",
    "Colaboro activamente para el logro de metas comunes en mi salón y reconozco la importancia que tienen las normas para lograr esas metas.",
    "Participo en los procesos de elección de representantes estudiantiles, conociendo bien cada propuesta antes de elegir.",
    // Pluralidad, Identidad y Valoración de las Diferencias
    "Identifico y respeto las diferencias y semejanzas entre los demás y yo, y rechazo situaciones de exclusión o discriminación en mi familia, con mis amigas y amigos y en mi salón.",
    "Identifico las diferencias y semejanzas de género, aspectos físicos, grupo étnico, origen social, costumbres, gustos, ideas y tantas otras que hay entre las demás personas y yo.",
    "Reconozco y acepto la existencia de grupos con diversas características de etnia, edad, género, oficio, lugar, situación socioeconómica, etc.",
    "Valoro las semejanzas y diferencias de gente cercana.",
    "Identifico las ocasiones en que mis amigos/as o yo hemos hecho sentir mal a alguien excluyéndolo, burlándonos o poniéndole apodos ofensivos.",
    "Manifiesto desagrado cuando me excluyen o excluyen a alguien por su género, etnia, condición social y características físicas, y lo digo respetuosamente.",
    "Comparo cómo me siento cuando me discriminan o me excluyen... y cómo, cuando me aceptan. Así puedo explicar por qué es importante aceptar a las personas."
];

// -----------------------------------------------------------------------------
// CICLO 2: GRADOS 4° A 5° - COMPETENCIAS CIUDADANAS
// -----------------------------------------------------------------------------
$ciudadanas_4_5 = [
    // Convivencia y Paz
    "Asumo, de manera pacífica y constructiva, los conflictos cotidianos en mi vida escolar y familiar y contribuyo a la protección de los derechos de las niñas y los niños.",
    "Entiendo que los conflictos son parte de las relaciones, pero que tener conflictos no significa que dejemos de ser amigos o querernos.",
    "Conozco la diferencia entre conflicto y agresión, y comprendo que la agresión (no los conflictos) es lo que puede hacerle daño a las relaciones.",
    "Identifico los puntos de vista de la gente con la que tengo conflictos poniéndome en su lugar.",
    "Identifico las ocasiones en que actúo en contra de los derechos de otras personas y comprendo por qué esas acciones vulneran sus derechos.",
    "Expongo mis posiciones y escucho las posiciones ajenas, en situaciones de conflicto.",
    "Identifico múltiples opciones para manejar mis conflictos y veo las posibles consecuencias de cada opción.",
    "Utilizo mecanismos para manejar mi rabia.",
    "Pido disculpas a quienes he hecho daño (así no haya tenido intención) y logro perdonar cuando me ofenden.",
    "Puedo actuar en forma asertiva (es decir, sin agresión pero con claridad y eficacia) para frenar situaciones de abuso en mi vida escolar.",
    "Reconozco cómo se sienten otras personas cuando son agredidas o se vulneran sus derechos y contribuyo a aliviar su malestar.",
    "Conozco los derechos fundamentales de los niños y las niñas.",
    "Identifico las instituciones y autoridades a las que puedo acudir para pedir la protección y defensa de los derechos de los niños y las niñas y busco apoyo, cuando es necesario.",
    "Reconozco el valor de las normas y los acuerdos para la convivencia en la familia, en el medio escolar y en otras situaciones.",
    "Reconozco que tengo derecho a mi privacidad e intimidad; exijo el respeto a ello.",
    "¡Me cuido a mí mismo! Comprendo que cuidarme y tener hábitos saludables favorece mi bienestar y mis relaciones.",
    "Ayudo a cuidar las plantas, los animales y el medio ambiente en mi entorno cercano.",
    // Participación y Responsabilidad Democrática
    "Participo constructivamente en procesos democráticos en mi salón y en el medio escolar.",
    "Conozco y sé usar los mecanismos de participación estudiantil de mi medio escolar.",
    "Conozco las funciones del gobierno escolar y el manual de convivencia.",
    "Identifico y expreso, con mis propias palabras, las ideas y los deseos de quienes participamos en la toma de decisiones, en el salón y en el medio escolar.",
    "Expreso, en forma asertiva, mis puntos de vista e intereses en las discusiones grupales.",
    "Identifico y manejo mis emociones, como el temor a participar o la rabia, durante las discusiones grupales.",
    "Propongo distintas opciones cuando tomamos decisiones en el salón y en la vida escolar.",
    "Coopero y muestro solidaridad con mis compañeros y mis compañeras; trabajo constructivamente en equipo.",
    "Participo con mis profesores, compañeros y compañeras en proyectos colectivos orientados al bien común y a la solidaridad.",
    "Reconozco la importancia de la creación de obras de todo tipo, tales como las literarias y artísticas y, por ende, la importancia del respeto al derecho de autor.",
    // Pluralidad, Identidad y Valoración de las Diferencias
    "Reconozco y rechazo las situaciones de exclusión o discriminación en mi medio escolar.",
    "Reconozco que todos los niños y las niñas somos personas con el mismo valor y los mismos derechos.",
    "Reconozco lo distintas que somos las personas y comprendo que esas diferencias son oportunidades para construir nuevos conocimientos y relaciones y hacer que la vida sea más interesante y divertida.",
    "Identifico mi origen cultural y reconozco y respeto las semejanzas y diferencias con el origen cultural de otra gente.",
    "Identifico algunas formas de discriminación en mi escuela (por género, religión, etnia, edad, cultura, aspectos económicos o sociales, capacidades o limitaciones individuales) y colaboro con acciones, normas o acuerdos para evitarlas.",
    "Identifico mis sentimientos cuando me excluyen o discriminan y entiendo lo que pueden sentir otras personas en esas mismas situaciones.",
    "Expreso empatía (sentimientos parecidos o compatibles con los de otros) frente a personas excluidas o discriminadas.",
    "Identifico y reflexiono acerca de las consecuencias de la discriminación en las personas y en la convivencia escolar."
];

// -----------------------------------------------------------------------------
// CICLO 3: GRADOS 6° A 7° - COMPETENCIAS CIUDADANAS
// -----------------------------------------------------------------------------
$ciudadanas_6_7 = [
    // Convivencia y Paz
    "Contribuyo, de manera constructiva, a la convivencia en mi medio escolar y en mi comunidad (barrio o vereda).",
    "Conozco procesos y técnicas de mediación de conflictos.",
    "Sirvo de mediador en conflictos entre compañeros y compañeras, cuando me autorizan, fomentando el diálogo y el entendimiento.",
    "Apelo a la mediación escolar, si considero que necesito ayuda para resolver conflictos.",
    "Reconozco el conflicto como una oportunidad para aprender y fortalecer nuestras relaciones.",
    "Identifico las necesidades y los puntos de vista de personas o grupos en una situación de conflicto, en la que no estoy involucrado.",
    "Comprendo que las intenciones de la gente, muchas veces, son mejores de lo que yo inicialmente pensaba; también veo que hay situaciones en las que alguien puede hacerme daño sin intención.",
    "Comprendo que el engaño afecta la confianza entre las personas y reconozco la importancia de recuperar la confianza cuando se ha perdido.",
    "Comprendo la importancia de brindar apoyo a la gente que está en una situación difícil.",
    "Comprendo que todas las familias tienen derecho al trabajo, la salud, la vivienda, la propiedad, la educación y la recreación.",
    "Reflexiono sobre el uso del poder y la autoridad en mi entorno y expreso pacíficamente mi desacuerdo cuando considero que hay injusticias.",
    "Comprendo la importancia de los derechos sexuales y reproductivos y analizo sus implicaciones en mi vida.",
    "Promuevo el respeto a la vida, frente a riesgos como ignorar señales de tránsito, portar armas, conducir a alta velocidad o habiendo consumido alcohol; sé qué medidas tomar para actuar con responsabilidad frente a un accidente.",
    "Comprendo que el espacio público es patrimonio de todos y todas y, por eso, lo cuido y respeto.",
    "Reconozco que los seres vivos y el medio ambiente son un recurso único e irrepetible que merece mi respeto y consideración.",
    // Participación y Responsabilidad Democrática
    "Identifico y rechazo las situaciones en las que se vulneran los derechos fundamentales y utilizo formas y mecanismos de participación democrática en mi medio escolar.",
    "Conozco la Declaración Universal de los Derechos Humanos y su relación con los derechos fundamentales enunciados en la Constitución.",
    "Conozco los mecanismos constitucionales que protegen los derechos fundamentales (como la tutela) y comprendo cómo se aplican.",
    "Analizo el manual de convivencia y las normas de mi institución; las cumplo voluntariamente y participo de manera pacífica en su transformación cuando las considero injustas.",
    "Exijo el cumplimiento de las normas y los acuerdos por parte de las autoridades, de mis compañeros y de mí mismo(a).",
    "Manifiesto indignación (rechazo, dolor, rabia) cuando se vulneran las libertades de las personas y acudo a las autoridades apropiadas.",
    "Analizo cómo mis pensamientos y emociones influyen en mi participación en las decisiones colectivas.",
    "Identifico decisiones colectivas en las que intereses de diferentes personas están en conflicto y propongo alternativas de solución que tengan en cuenta esos intereses.",
    "Preveo las consecuencias que pueden tener, sobre mí y sobre los demás, las diversas alternativas de acción propuestas frente a una decisión colectiva.",
    "Escucho y expreso, con mis palabras, las razones de mis compañeros(as) durante discusiones grupales, incluso cuando no estoy de acuerdo.",
    "Uso mi libertad de expresión y respeto las opiniones ajenas.",
    "Comprendo que el disenso y la discusión constructiva contribuyen al progreso del grupo.",
    "Comprendo la importancia de participar en el gobierno escolar y de hacer seguimiento a sus representantes.",
    // Pluralidad, Identidad y Valoración de las Diferencias
    "Identifico y rechazo las diversas formas de discriminación en mi medio escolar y en mi comunidad, y analizo críticamente las razones que pueden favorecer estas discriminaciones.",
    "Comprendo que, según la Declaración Universal de los Derechos Humanos y la Constitución Nacional, las personas tenemos derecho a no ser discriminadas.",
    "Reconozco que los derechos se basan en la igualdad de los seres humanos, aunque cada uno sea, se exprese y viva de manera diferente.",
    "Reconozco que pertenezco a diversos grupos (familia, colegio, barrio, región, país, etc.) y entiendo que eso hace parte de mi identidad.",
    "Respeto y defiendo las libertades de las personas: libertad de expresión, de conciencia, de pensamiento, de culto y de libre desarrollo de la personalidad.",
    "Comprendo que existen diversas formas de expresar las identidades (por ejemplo, la apariencia física, la expresión artística y verbal, y tantas otras...) y las respeto.",
    "Comprendo que cuando las personas son discriminadas, su autoestima y sus relaciones con los demás se ven afectadas.",
    "Identifico mis emociones ante personas o grupos que tienen intereses o gustos distintos a los míos y pienso cómo eso influye en mi trato hacia ellos.",
    "Analizo de manera crítica mis pensamientos y acciones cuando estoy en una situación de discriminación y establezco si estoy apoyando o impidiendo dicha situación con mis acciones u omisiones.",
    "Actúo con independencia frente a situaciones en las que favorecer a personas excluidas puede afectar mi imagen ante el grupo.",
    "Reconozco que los niños, las niñas, los ancianos y las personas discapacitadas merecen cuidado especial, tanto en espacios públicos como privados."
];

// -----------------------------------------------------------------------------
// CICLO 4: GRADOS 8° A 9° - COMPETENCIAS CIUDADANAS
// -----------------------------------------------------------------------------
$ciudadanas_8_9 = [
    // Convivencia y Paz
    "Construyo relaciones pacíficas que contribuyen a la convivencia cotidiana en mi comunidad y municipio.",
    "Entiendo la importancia de mantener expresiones de afecto y cuidado mutuo con mis familiares, amigos, amigas y pareja, a pesar de las diferencias, disgustos o conflictos.",
    "Comprendo que los conflictos ocurren en las relaciones, incluyendo las de pareja, y que se pueden manejar de manera constructiva si nos escuchamos y comprendemos los puntos de vista del otro.",
    "Identifico y supero emociones, como el resentimiento y el odio, para poder perdonar y reconciliarme con quienes he tenido conflictos.",
    "Utilizo mecanismos constructivos para encauzar mi rabia y enfrentar mis conflictos.",
    "Preveo las consecuencias, a corto y largo plazo, de mis acciones y evito aquellas que pueden causarme sufrimiento o hacérselo a otras personas, cercanas o lejanas.",
    "Conozco y utilizo estrategias creativas para solucionar conflictos.",
    "Analizo críticamente los conflictos entre grupos, en mi barrio, vereda, municipio o país.",
    "Analizo, de manera crítica, los discursos que legitiman la violencia.",
    "Identifico dilemas de la vida, en los que distintos derechos o distintos valores entran en conflicto y analizo posibles opciones de solución, considerando los aspectos positivos y negativos de cada una.",
    "Argumento y debato sobre dilemas de la vida cotidiana en los que distintos derechos o distintos valores entran en conflicto; reconozco los mejores argumentos, así no coincidan con los míos.",
    "Construyo, celebro, mantengo y reparo acuerdos entre grupos.",
    // Participación y Responsabilidad Democrática
    "Participo o lidero iniciativas democráticas en mi medio escolar o en mi comunidad, con criterios de justicia, solidaridad y equidad, y en defensa de los derechos civiles y políticos.",
    "Comprendo las características del Estado de Derecho y del Estado Social de Derecho y su importancia para garantizar los derechos ciudadanos.",
    "Identifico y analizo las situaciones en las que se vulneran los derechos civiles y políticos.",
    "Conozco, analizo y uso los mecanismos de participación ciudadana.",
    "Identifico los sentimientos, necesidades y puntos de vista de aquellos a los que se les han violado derechos civiles y políticos y propongo acciones no violentas para impedirlo.",
    "Analizo críticamente mi participación en situaciones en las que se vulneran o respetan los derechos e identifico cómo dicha participación contribuye a mejorar o empeorar la situación.",
    "Cuestiono y analizo los argumentos de quienes limitan las libertades de las personas.",
    "Analizo críticamente la información de los medios de comunicación.",
    "Hago seguimiento a las acciones que desarrollan los representantes escolares y protesto pacíficamente cuando no cumplen sus funciones o abusan de su poder.",
    "Comprendo que los mecanismos de participación permiten decisiones y, aunque no esté de acuerdo con ellas, sé que me rigen.",
    "Conozco y uso estrategias creativas para generar opciones frente a decisiones colectivas.",
    "Participo en la planeación y ejecución de acciones que contribuyen a aliviar la situación de personas en desventaja.",
    // Pluralidad, Identidad y Valoración de las Diferencias
    "Rechazo las situaciones de discriminación y exclusión social en el país; comprendo sus posibles causas y las consecuencias negativas para la sociedad.",
    "Comprendo el significado y la importancia de vivir en una nación multiétnica y pluricultural.",
    "Comprendo los conceptos de prejuicio y estereotipo y su relación con la exclusión, la discriminación y la intolerancia a la diferencia.",
    "Comprendo que la discriminación y la exclusión pueden tener consecuencias sociales negativas como la desintegración de las relaciones entre personas o grupos, la pobreza o la violencia.",
    "Respeto propuestas éticas y políticas de diferentes culturas, grupos sociales y políticos, y comprendo que es legítimo disentir.",
    "Conozco y respeto los derechos de aquellos grupos a los que históricamente se les han vulnerado (mujeres, grupos étnicos minoritarios, homosexuales, etc.).",
    "Comprendo que la orientación sexual hace parte del libre desarrollo de la personalidad y rechazo cualquier discriminación al respecto.",
    "Analizo mis prácticas cotidianas e identifico cómo mis acciones u omisiones pueden contribuir a la discriminación.",
    "Manifiesto indignación (rechazo, dolor, rabia) frente a cualquier discriminación o situación que vulnere los derechos; apoyo iniciativas para prevenir dichas situaciones.",
    "Identifico dilemas relacionados con problemas de exclusión y analizo alternativas de solución, considerando los aspectos positivos y negativos de cada opción.",
    "Argumento y debato dilemas relacionados con exclusión y reconozco los mejores argumentos, así no coincidan con los míos."
];

// -----------------------------------------------------------------------------
// CICLO 5: GRADOS 10° A 11° - COMPETENCIAS CIUDADANAS
// -----------------------------------------------------------------------------
$ciudadanas_10_11 = [
    // Convivencia y Paz
    "Participo constructivamente en iniciativas o proyectos a favor de la no-violencia en el nivel local o global.",
    "Contribuyo a que los conflictos entre personas y entre grupos se manejen de manera pacífica y constructiva mediante la aplicación de estrategias basadas en el diálogo y la negociación.",
    "Utilizo distintas formas de expresión para promover y defender los derechos humanos en mi contexto escolar y comunitario.",
    "Analizo críticamente las decisiones, acciones u omisiones que se toman en el ámbito nacional o internacional y que pueden generar conflictos o afectar los derechos humanos.",
    "Analizo críticamente la situación de los derechos humanos en Colombia y en el mundo y propongo alternativas para su promoción y defensa.",
    "Manifiesto indignación (dolor, rabia, rechazo) de manera pacífica ante el sufrimiento de grupos o naciones que están involucradas en confrontaciones violentas.",
    "Valoro positivamente las normas constitucionales que hacen posible la preservación de las diferencias culturales y políticas, y que regulan nuestra convivencia.",
    "Comprendo que, para garantizar la convivencia, el Estado debe contar con el monopolio de la administración de justicia y del uso de la fuerza, y que la sociedad civil debe hacerle seguimiento crítico, para evitar abusos.",
    "Conozco las instancias y sé usar los mecanismos jurídicos ordinarios y alternativos para la resolución pacífica de conflictos: justicia ordinaria, jueces de paz, centros de conciliación, comisarías de familia; negociación, mediación, arbitramento.",
    "Identifico dilemas de la vida en las que entran en conflicto el bien general y el bien particular; analizo opciones de solución, considerando sus aspectos positivos y negativos.",
    "Argumento y debato sobre dilemas de la vida en los que entran en conflicto el bien general y el bien particular, reconociendo los mejores argumentos, así sean distintos a los míos.",
    "Conozco y respeto las normas de tránsito.",
    "Comprendo la importancia de la defensa del medio ambiente, tanto en el nivel local como global, y participo en iniciativas a su favor.",
    // Participación y Responsabilidad Democrática
    "Conozco y sé usar los mecanismos constitucionales de participación que permiten expresar mis opiniones y participar en la toma de decisiones políticas tanto a nivel local como a nivel nacional.",
    "Comprendo que en un Estado de Derecho las personas podemos participar en la creación o transformación de las leyes y que éstas se aplican a todos y todas por igual.",
    "Conozco los principios básicos del Derecho Internacional Humanitario (por ejemplo, la protección a la sociedad civil en un conflicto armado).",
    "Analizo críticamente el sentido de las leyes y comprendo la importancia de cumplirlas, así no comparta alguna de ellas.",
    "Analizo críticamente y debato con argumentos y evidencias sobre hechos ocurridos a nivel local, nacional y mundial, y comprendo las consecuencias que estos pueden tener sobre mi propia vida.",
    "Expreso empatía ante grupos o personas cuyos derechos han sido vulnerados (por ejemplo, en situaciones de desplazamiento) y propongo acciones solidarias para con ellos.",
    "Participo en manifestaciones pacíficas de rechazo o solidaridad ante situaciones de desventaja social, económica o de salud que vive la gente de mi región o mi país.",
    "Participo en iniciativas políticas democráticas en mi medio escolar o localidad.",
    "Comprendo qué es un bien público y participo en acciones que velan por su buen uso, tanto en la comunidad escolar, como en mi municipio.",
    "Comprendo que cuando se actúa en forma corrupta y se usan los bienes públicos para beneficio personal, se afectan todos los miembros de la sociedad.",
    // Pluralidad, Identidad y Valoración de las Diferencias
    "Expreso rechazo ante toda forma de discriminación o exclusión social y hago uso de los mecanismos democráticos para la superación de la discriminación y el respeto a la diversidad.",
    "Construyo una posición crítica frente a las situaciones de discriminación y exclusión social que resultan de las relaciones desiguales entre personas, culturas y naciones.",
    "Reconozco las situaciones de discriminación y exclusión más agudas que se presentan ahora, o se presentaron en el pasado, tanto en el orden nacional como en el internacional; las relaciono con las discriminaciones que observo en mi vida cotidiana.",
    "Comprendo que el respeto por la diferencia no significa aceptar que otras personas o grupos vulneren derechos humanos o normas constitucionales.",
    "Identifico prejuicios, estereotipos y emociones que me dificultan sentir empatía por algunas personas o grupos y exploro caminos para superarlos.",
    "Identifico y analizo dilemas de la vida en los que los valores de distintas culturas o grupos sociales entran en conflicto y exploro distintas opciones de solución, considerando sus aspectos positivos y negativos.",
    "Argumento y debato dilemas de la vida en los que los valores de distintas culturas o grupos sociales entran en conflicto; reconozco los mejores argumentos, así no coincidan con los míos."
];

// Generación del array por grados independientes (1 a 11)
for ($i = 1; $i <= 11; $i++) {
    $grupo = [];

    if ($i <= 3) {
        $grupo = $ciudadanas_1_3;
    } elseif ($i <= 5) {
        $grupo = $ciudadanas_4_5;
    } elseif ($i <= 7) {
        $grupo = $ciudadanas_6_7;
    } elseif ($i <= 9) {
        $grupo = $ciudadanas_8_9;
    } else {
        $grupo = $ciudadanas_10_11;
    }

    $datos[] = [
        "grado" => $i,
        "asignatura" => "competencias_ciudadanas",
        "dbas" => $grupo
    ];
}

// Salida JSON
echo json_encode([
    "status" => "success",
    "source" => "Estándares Básicos de Competencias Ciudadanas (MEN)",
    "nota" => "Estándares organizados por las tres dimensiones: Convivencia y Paz, Participación Democrática, Pluralidad. Mapeados como DBAs para compatibilidad del sistema.",
    "data" => $datos
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>