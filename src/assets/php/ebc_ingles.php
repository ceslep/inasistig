<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Estructura base solicitada
$datos = [];

// -----------------------------------------------------------------------------
// NIVEL A1 - PRINCIPIANTE: GRADOS 1° A 3°
// Fuente: Estándares Básicos de Competencias en Lenguas Extranjeras: Inglés - MEN Colombia
// Habilidades: Escucha, Lectura, Escritura, Monólogos, Conversación
// -----------------------------------------------------------------------------
$ingles_1_3 = [
    // Escucha (Listening)
    "Reconozco cuando me hablan en inglés y reacciono de manera verbal y no verbal.",
    "Entiendo cuando me saludan y se despiden de mí.",
    "Sigo instrucciones relacionadas con actividades de clase y recreativas propuestas por mi profesor.",
    "Comprendo canciones, rimas y rondas infantiles, y lo demuestro con gestos y movimientos.",
    "Demuestro comprensión de preguntas sencillas sobre mí, mi familia y mi entorno.",
    "Comprendo descripciones cortas y sencillas de objetos y lugares conocidos.",
    "Identifico a las personas que participan en una conversación.",
    "Sigo la secuencia de un cuento corto apoyado en imágenes.",
    "Entiendo la idea general de una historia contada por mi profesor cuando se apoya en movimientos, gestos y cambios de voz.",
    "Reconozco que hay otras personas como yo que se comunican en inglés.",
    "Comprendo secuencias relacionadas con hábitos y rutinas.",

    // Lectura (Reading)
    "Identifico palabras relacionadas entre sí sobre temas que me son familiares.",
    "Reconozco palabras y frases cortas en inglés en libros, objetos, juguetes, propagandas y lugares de mi escuela.",
    "Relaciono ilustraciones con oraciones simples.",
    "Reconozco y sigo instrucciones sencillas, si están ilustradas.",
    "Puedo predecir una historia a partir del título, las ilustraciones y las palabras clave.",
    "Sigo la secuencia de una historia sencilla.",
    "Utilizo diagramas para organizar la información de cuentos cortos leídos en clase.",
    "Disfruto la lectura como una actividad de esparcimiento que me ayuda a descubrir el mundo.",

    // Escritura (Writing)
    "Copio y transcribo palabras que comprendo y que uso con frecuencia en el salón de clase.",
    "Escribo el nombre de lugares y elementos que reconozco en una ilustración.",
    "Respondo brevemente a las preguntas 'qué, quién, cuándo y dónde', si se refieren a mi familia, mis amigos o mi colegio.",
    "Escribo información personal en formatos sencillos.",
    "Escribo mensajes de invitación y felicitación usando formatos sencillos.",
    "Demuestro conocimiento de las estructuras básicas del inglés.",

    // Monólogos (Spoken Production)
    "Recito y canto rimas, poemas y trabalenguas que comprendo, con ritmo y entonación adecuados.",
    "Expreso mis sentimientos y estados de ánimo.",
    "Menciono lo que me gusta y lo que no me gusta.",
    "Describo lo que estoy haciendo.",
    "Nombro algunas cosas que puedo hacer y que no puedo hacer.",
    "Describo lo que hacen algunos miembros de mi comunidad.",
    "Uso gestos y movimientos corporales para hacerme entender mejor.",
    "Describo algunas características de mí mismo, de otras personas, de animales, de lugares y del clima.",
    "Participo en representaciones cortas; memorizo y comprendo los parlamentos.",

    // Conversación (Spoken Interaction)
    "Respondo a saludos y a despedidas.",
    "Respondo a preguntas sobre cómo me siento.",
    "Uso expresiones cotidianas para expresar mis necesidades inmediatas en el aula.",
    "Utilizo el lenguaje no verbal cuando no puedo responder verbalmente a preguntas sobre mis preferencias.",
    "Expreso e indico necesidades personales básicas relacionadas con el aula.",
    "Respondo a preguntas sobre personas, objetos y lugares de mi entorno.",
    "Pido que me repitan el mensaje cuando no lo comprendo.",
    "Participo activamente en juegos de palabras y rondas.",
    "Refuerzo con gestos lo que digo para hacerme entender."
];

// -----------------------------------------------------------------------------
// NIVEL A2.1 - BÁSICO 1: GRADOS 4° A 5°
// -----------------------------------------------------------------------------
$ingles_4_5 = [
    // Escucha
    "Comprendo información básica sobre temas relacionados con mis actividades cotidianas y con mi entorno.",
    "Comprendo preguntas y expresiones orales que se refieren a mí, a mi familia, mis amigos y mi entorno.",
    "Comprendo mensajes cortos y simples relacionados con mi entorno y mis intereses personales y académicos.",
    "Comprendo y sigo instrucciones puntuales cuando éstas se presentan en forma clara y con vocabulario conocido.",
    "Comprendo una descripción oral sobre una situación, persona, lugar u objeto.",
    "Identifico el tema general y los detalles relevantes en conversaciones, informaciones radiales o exposiciones orales.",
    "Comprendo la idea general en una descripción y en una narración.",

    // Lectura
    "Comprendo instrucciones escritas para llevar a cabo actividades cotidianas, personales y académicas.",
    "Comprendo textos literarios, académicos y de interés general, escritos con un lenguaje sencillo.",
    "Puedo extraer información general y específica de un texto corto y escrito en un lenguaje sencillo.",
    "Comprendo relaciones establecidas por palabras como and (adición), but (contraste), first, second... (orden temporal), en enunciados sencillos.",
    "Valoro la lectura como un hábito importante de enriquecimiento personal y académico.",
    "Identifico el significado adecuado de las palabras en el diccionario según el contexto.",
    "Aplico estrategias de lectura relacionadas con el propósito de la misma.",
    "Identifico en textos sencillos, elementos culturales como costumbres y celebraciones.",
    "Identifico la acción, los personajes y el entorno en textos narrativos.",

    // Escritura
    "Describo con frases cortas personas, lugares, objetos o hechos relacionados con temas y situaciones que me son familiares.",
    "Escribo mensajes cortos y con diferentes propósitos relacionados con situaciones, objetos o personas de mi entorno inmediato.",
    "Completo información personal básica en formatos y documentos sencillos.",
    "Escribo un texto corto relativo a mí, a mi familia, mis amigos, mi entorno o sobre hechos que me son familiares.",
    "Escribo textos cortos en los que expreso contraste, adición, causa y efecto entre ideas.",
    "Utilizo vocabulario adecuado para darle coherencia a mis escritos.",
    "Describo con oraciones simples a una persona, lugar u objeto que me son familiares aunque, si lo requiero, me apoyo en apuntes o en mi profesor.",

    // Monólogos
    "Doy instrucciones orales sencillas en situaciones escolares, familiares y de mi entorno cercano.",
    "Establezco comparaciones entre personajes, lugares y objetos.",
    "Expreso de manera sencilla lo que me gusta y me disgusta respecto a algo.",
    "Narro o describo de forma sencilla hechos y actividades que me son familiares.",
    "Hago exposiciones muy breves, de contenido predecible y aprendido.",
    "Describo con oraciones simples mi rutina diaria y la de otras personas.",
    "Respondo con frases cortas a preguntas sencillas sobre temas que me son familiares.",

    // Conversación
    "Solicito explicaciones sobre situaciones puntuales en mi escuela, mi familia y mi entorno cercano.",
    "Participo en situaciones comunicativas cotidianas tales como pedir favores, disculparme y agradecer.",
    "Utilizo códigos no verbales como gestos y entonación, entre otros.",
    "Formulo preguntas sencillas sobre temas que me son familiares apoyándome en gestos y repetición.",
    "Hago propuestas a mis compañeros sobre qué hacer, dónde, cuándo o cómo.",
    "Inicio, mantengo y cierro una conversación sencilla sobre un tema conocido."
];

// -----------------------------------------------------------------------------
// NIVEL A2.2 - BÁSICO 2: GRADOS 6° A 7°
// -----------------------------------------------------------------------------
$ingles_6_7 = [
    // Escucha
    "Sigo las instrucciones dadas en clase para realizar actividades académicas.",
    "Entiendo lo que me dicen el profesor y mis compañeros en interacciones cotidianas dentro del aula, sin necesidad de repetición.",
    "Identifico ideas generales y específicas en textos orales, si tengo conocimiento del tema y del vocabulario utilizado.",
    "Reconozco los elementos de enlace de un texto oral para identificar su secuencia.",
    "Muestro una actitud respetuosa y tolerante al escuchar a otros.",
    "Identifico diferentes roles de los hablantes que participan en conversaciones de temas relacionados con mis intereses.",
    "Utilizo mi conocimiento general del mundo para comprender lo que escucho.",
    "Infi ero información específica a partir de un texto oral.",
    "Identifico la información clave en conversaciones breves tomadas de la vida real, si están acompañadas por imágenes.",
    "Reconozco el propósito de diferentes tipos de textos que presentan mis compañeros en clase.",

    // Lectura
    "Identifico iniciación, nudo y desenlace en una narración.",
    "Reconozco el propósito de una descripción en textos narrativos de mediana extensión.",
    "Identifico puntos a favor y en contra en un texto argumentativo sobre temas con los que estoy familiarizado.",
    "Comprendo relaciones de adición, contraste, orden temporal y espacial y causa-efecto entre enunciados sencillos.",
    "Identifico la recurrencia de ideas en un mismo texto.",
    "Identifico relaciones de significado expresadas en textos sobre temas que me son familiares.",
    "Represento, en forma gráfica, la información que encuentro en textos que comparan y contrastan objetos, animales y personas.",
    "Valoro la lectura como una actividad importante para todas las áreas de mi vida.",
    "Comprendo la información implícita en textos relacionados con temas de mi interés.",
    "Diferencio la estructura organizativa de textos descriptivos, narrativos y argumentativos.",
    "Identifico elementos culturales presentes en textos sencillos.",

    // Escritura
    "Escribo narraciones sobre experiencias personales y hechos a mi alrededor.",
    "Escribo mensajes en diferentes formatos sobre temas de mi interés.",
    "Diligencio efectivamente formatos con información personal.",
    "Contesto, en forma escrita, preguntas relacionadas con textos que he leído.",
    "Produzco textos sencillos con diferentes funciones (describir, narrar, argumentar) sobre temas personales y relacionados con otras asignaturas.",
    "Parafraseo información que leo como parte de mis actividades académicas.",
    "Organizo párrafos coherentes cortos, teniendo en cuenta elementos formales del lenguaje como ortografía y puntuación.",
    "Uso planes representados en mapas o diagramas para desarrollar mis escritos.",
    "Ejemplifico mis puntos de vista sobre los temas que escribo.",
    "Edito mis escritos en clase, teniendo en cuenta reglas de ortografía, adecuación del vocabulario y estructuras gramaticales.",

    // Monólogos
    "Hago presentaciones cortas y ensayadas sobre temas cotidianos y personales.",
    "Narro historias cortas enlazando mis ideas de manera apropiada.",
    "Expreso mi opinión sobre asuntos de interés general para mí y mis compañeros.",
    "Explico y justifico brevemente mis planes y acciones.",
    "Hago descripciones sencillas sobre diversos asuntos cotidianos de mi entorno.",
    "Hago exposiciones ensayadas y breves sobre algún tema académico de mi interés.",
    "Expreso mis opiniones, gustos y preferencias sobre temas que he trabajado en clase, utilizando estrategias para monitorear mi pronunciación.",

    // Conversación
    "Uso un plan para exponer temas relacionados con el entorno académico de otras asignaturas.",
    "Participo en una conversación cuando mi interlocutor me da el tiempo para pensar mis respuestas.",
    "Converso con mis compañeros y mi profesor sobre experiencias pasadas y planes futuros.",
    "Me arriesgo a participar en una conversación con mis compañeros y mi profesor.",
    "Me apoyo en mis conocimientos generales del mundo para participar en una conversación.",
    "Interactúo con mis compañeros y profesor para tomar decisiones sobre temas específicos que conozco.",
    "Uso lenguaje formal o informal en juegos de rol improvisados, según el contexto.",
    "Monitoreo la toma de turnos entre los participantes en discusiones sobre temas preparados con anterioridad.",
    "Demuestro que reconozco elementos de la cultura extranjera y los relaciono con mi cultura."
];

// -----------------------------------------------------------------------------
// NIVEL B1.1 - PRE INTERMEDIO 1: GRADOS 8° A 9°
// -----------------------------------------------------------------------------
$ingles_8_9 = [
    // Escucha
    "Entiendo instrucciones para ejecutar acciones cotidianas.",
    "Identifico la idea principal de un texto oral cuando tengo conocimiento previo del tema.",
    "Identifico conectores en una situación de habla para comprender su sentido.",
    "Identifico personas, situaciones, lugares y el tema en conversaciones sencillas.",
    "Identifico el propósito de un texto oral.",
    "Muestro una actitud respetuosa y tolerante cuando escucho a otros.",
    "Utilizo estrategias adecuadas al propósito y al tipo de texto (activación de conocimientos previos, apoyo en el lenguaje corporal y gestual, uso de imágenes) para comprender lo que escucho.",
    "Comprendo el sentido general del texto oral aunque no entienda todas sus palabras.",
    "Me apoyo en el lenguaje corporal y gestual del hablante para comprender mejor lo que dice.",
    "Utilizo las imágenes e información del contexto de habla para comprender mejor lo que escucho.",

    // Lectura
    "Identifico palabras clave dentro del texto que me permiten comprender su sentido general.",
    "Identifico el punto de vista del autor.",
    "Asumo una posición crítica frente al punto de vista del autor.",
    "Identifico los valores de otras culturas y eso me permite construir mi interpretación de su identidad.",
    "Valoro la lectura como un medio para adquirir información de diferentes disciplinas que amplían mi conocimiento.",
    "Utilizo variedad de estrategias de comprensión de lectura adecuadas al propósito y al tipo de texto.",
    "Analizo textos descriptivos, narrativos y argumentativos con el fin de comprender las ideas principales y específicas.",
    "Hago inferencias a partir de la información en un texto.",
    "En un texto identifico los elementos que me permiten apreciar los valores de la cultura angloparlante.",
    "Comprendo variedad de textos informativos provenientes de diferentes fuentes.",

    // Escritura
    "Estructuro mis textos teniendo en cuenta elementos formales del lenguaje como la puntuación, la ortografía, la sintaxis, la coherencia y la cohesión.",
    "Planeo, reviso y edito mis escritos con la ayuda de mis compañeros y del profesor.",
    "Expreso valores de mi cultura a través de los textos que escribo.",
    "Escribo diferentes tipos de textos de mediana longitud y con una estructura sencilla (cartas, notas, mensajes, correos electrónicos, etc.).",
    "Escribo resúmenes e informes que demuestran mi conocimiento sobre temas de otras disciplinas.",
    "Escribo textos de diferentes tipos teniendo en cuenta a mi posible lector.",
    "Valoro la escritura como un medio de expresión de mis ideas y pensamientos, quién soy y qué sé del mundo.",
    "Escribo textos a través de los cuales explico mis preferencias, decisiones o actuaciones.",
    "Escribo textos expositivos sobre temas de mi interés.",

    // Monólogos
    "Narro en forma detallada experiencias, hechos o historias de mi interés y del interés de mi audiencia.",
    "Hago presentaciones orales sobre temas de mi interés y relacionados con el currículo escolar.",
    "Utilizo un vocabulario apropiado para expresar mis ideas con claridad sobre temas del currículo y de mi interés.",
    "Puedo expresarme con la seguridad y confianza propios de mi personalidad.",
    "Utilizo elementos metalingüísticos como gestos y entonación para hacer más comprensible lo que digo.",
    "Sustento mis opiniones, planes y proyectos.",
    "Uso estrategias como el parafraseo para compensar dificultades en la comunicación.",
    "Opino sobre los estilos de vida de la gente de otras culturas, apoyándome en textos escritos y orales previamente estudiados.",

    // Conversación
    "Participo espontáneamente en conversaciones sobre temas de mi interés utilizando un lenguaje claro y sencillo.",
    "Respondo preguntas teniendo en cuenta a mi interlocutor y el contexto.",
    "Utilizo una pronunciación inteligible para lograr una comunicación efectiva.",
    "Uso mis conocimientos previos para participar en una conversación.",
    "Describo en forma oral mis ambiciones, sueños y esperanzas utilizando un lenguaje claro y sencillo.",
    "Uso lenguaje funcional para discutir alternativas, hacer recomendaciones y negociar acuerdos en debates preparados con anterioridad.",
    "Utilizo estrategias que me permiten iniciar, mantener y cerrar una conversación sencilla sobre temas de mi interés, de una forma natural."
];

// -----------------------------------------------------------------------------
// NIVEL B1.2 - PRE INTERMEDIO 2: GRADOS 10° A 11°
// -----------------------------------------------------------------------------
$ingles_10_11 = [
    // Escucha (Avanzado A2+/B1)
    "Comprendo el contenido esencial de textos orales sobre temas concretos y abstractos, incluyendo discusiones técnicas en mi campo de especialización.",
    "Sigo conversaciones extensas y argumentaciones complejas siempre que el tema sea relativamente familiar.",
    "Comprendo la mayoría de las noticias, programas de televisión y películas cuando el habla es clara y en un registro estándar.",
    "Identifico información específica en anuncios, conferencias y entrevistas sobre temas de mi interés académico o profesional.",
    "Reconozco actitudes, opiniones y estados de ánimo en conversaciones y debates.",

    // Lectura
    "Leo con autonomía textos extensos y complejos relacionados con mis intereses académicos, profesionales y personales.",
    "Comprendo artículos y reportes sobre temas contemporáneos en los que los autores adoptan posturas o puntos de vista específicos.",
    "Analizo textos literarios contemporáneos en prosa, identificando temas, personajes y recursos estilísticos.",
    "Evalúo críticamente la información de diferentes fuentes escritas, identificando sesgos y propósitos del autor.",
    "Sintetizo información de múltiples fuentes para construir argumentos propios sobre temas de interés.",
    "Utilizo estrategias de lectura avanzada como skimming, scanning e inferencia contextual para optimizar la comprensión.",

    // Escritura
    "Produzco textos claros y bien estructurados sobre temas complejos, demostrando control de mecanismos de organización, articulación y cohesión.",
    "Escribo ensayos argumentativos y expositivos con introducción, desarrollo y conclusión coherentes.",
    "Redacto informes, reseñas y propuestas siguiendo convenciones del género y del registro apropiado.",
    "Parafraseo y cita fuentes académicas siguiendo normas básicas de integridad académica.",
    "Reviso y edito mis propios textos y los de mis pares, aplicando criterios de claridad, coherencia y corrección lingüística.",
    "Adapto mi estilo de escritura al propósito comunicativo y al lector potencial.",

    // Monólogos
    "Realizo presentaciones orales claras y detalladas sobre una amplia gama de temas relacionados con mis intereses.",
    "Desarrollo argumentos de manera sistemática, destacando puntos relevantes y brindando ejemplos pertinentes.",
    "Manejo con fluidez temas abstractos, hipotéticos y de carácter cultural o profesional.",
    "Utilizo recursos retóricos y estrategias discursivas para mantener la atención y persuadir a mi audiencia.",
    "Manejo adecuadamente la pronunciación, entonación y ritmo para facilitar la comprensión en contextos formales.",

    // Conversación
    "Participo con fluidez y espontaneidad en conversaciones y debates sobre temas generales, académicos y profesionales.",
    "Negocio significados, aclaro malentendidos y reformulo ideas para asegurar la comprensión mutua.",
    "Manejo con naturalidad registros formales e informales según el contexto y el interlocutor.",
    "Expreso y defiendo opiniones personales sobre temas controvertidos, reconociendo y respetando puntos de vista alternativos.",
    "Utilizo estrategias de cortesía, turnos de palabra y lenguaje corporal para gestionar interacciones grupales efectivas.",
    "Demuestro conciencia intercultural al interactuar con hablantes de diferentes variedades del inglés y de otras culturas."
];

// Generación del array por grados independientes (1 a 11)
for ($i = 1; $i <= 11; $i++) {
    $grupo = [];

    if ($i <= 3) {
        $grupo = $ingles_1_3;
    } elseif ($i <= 5) {
        $grupo = $ingles_4_5;
    } elseif ($i <= 7) {
        $grupo = $ingles_6_7;
    } elseif ($i <= 9) {
        $grupo = $ingles_8_9;
    } else {
        $grupo = $ingles_10_11;
    }

    $datos[] = [
        "grado" => $i,
        "asignatura" => "english",
        "dbas" => $grupo
    ];
}

// Salida JSON
echo json_encode([
    "status" => "success",
    "source" => "Estándares Básicos de Competencias en Lenguas Extranjeras: Inglés - Ministerio de Educación Nacional de Colombia",
    "nota" => "Estándares organizados por niveles del Marco Común Europeo (A1, A2.1, A2.2, B1.1, B1.2) y por habilidades comunicativas: Listening, Reading, Writing, Spoken Production, Spoken Interaction. Mapeados como DBAs para compatibilidad del sistema.",
    "competency_codes" => [
        "1" => "Competencia lingüística",
        "2" => "Competencia pragmática",
        "3" => "Competencia sociolingüística"
    ],
    "data" => $datos
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>