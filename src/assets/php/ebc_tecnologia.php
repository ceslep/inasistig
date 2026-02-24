<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Estructura base solicitada
$datos = [];

// -----------------------------------------------------------------------------
// CICLO 1: GRADOS 1° A 3° - TECNOLOGÍA E INFORMÁTICA
// Componentes: Naturaleza, Apropiación, Solución de problemas, Tecnología y sociedad
// -----------------------------------------------------------------------------
$tecnologia_1_3 = [
    // Naturaleza y conocimiento de la tecnología
    "Identifico y describo la importancia de algunos artefactos en el desarrollo de actividades cotidianas de mi entorno y el de mis antepasados.",
    "Explico la utilidad de objetos tecnológicos para la realización de actividades humanas (red para la pesca; rueda para el transporte...).",
    "Identifico artefactos que se utilizan hoy y que no se utilizaban en épocas pasadas.",
    "Describo semejanzas y diferencias que existen entre los productos de la tecnología que utilizaban mis bisabuelos, abuelos y padres con los que yo utilizo (transporte, comunicación, alimentación y hábitat).",
    "Reconozco herramientas que, como extensión de partes de mi cuerpo, me ayudan a realizar tareas de transformación de materiales.",
    "Establezco semejanzas y diferencias entre artefactos y elementos naturales.",
    "Identifico la tecnología que me rodea y explico la importancia que tiene para desarrollar actividades en mi barrio, casa, colegio y parque.",

    // Apropiación y uso de la tecnología
    "Identifico algunos artefactos, productos y procesos de mi entorno cotidiano, explico algunos aspectos de su funcionamiento y los utilizo en forma segura y apropiada.",
    "Identifico artefactos que se utilizan en mi entorno para satisfacer necesidades cotidianas (deportes, arte, entretenimiento, salud, estudio, alimentación...).",
    "Utilizo apropiadamente algunos de los artefactos de mi entorno en tareas cotidianas (aseo diario, comunicarme, desplazarme).",
    "Clasifico y describo artefactos de mi entorno según algunos criterios (uso, material, forma...).",
    "Describo procesos sencillos (artesanales e industriales) para la obtención de productos en mi entorno.",
    "Reconozco y utilizo algunos símbolos y señales de la vida cotidiana, particularmente los relacionados con la seguridad (tránsito, basuras, advertencias).",
    "Reconozco la computadora como recurso de trabajo y comunicación y la utilizo en diferentes actividades.",
    "Doy cuenta de mi esquema de vacunación y reconozco su utilidad e importancia en la preservación de mi salud.",
    "Identifico, explico y tengo en cuenta algunas características de los productos de uso cotidiano (alimentos, empaques, componentes, fecha de vencimiento, condiciones de almacenamiento y seguridad).",
    "Identifico y utilizo eficientemente diferentes fuentes de recursos naturales de mi entorno y doy cuenta de algunos momentos de su transformación (potabilización del agua, generación de energía).",
    "Manejo adecuadamente herramientas de uso cotidiano para transformar materiales con algún propósito (recortar, pegar, construir, pintar, ensamblar).",
    "Identifico y explico los riesgos al utilizar algunas herramientas y artefactos empleados en la vida cotidiana y los utilizo teniendo en cuenta normas de seguridad.",

    // Solución de problemas con tecnología
    "Identifico productos tecnológicos, en particular artefactos, para solucionar problemas de la vida cotidiana.",
    "Identifico características de algunos artefactos y productos tecnológicos utilizados en el entorno cercano para satisfacer necesidades.",
    "Identifico situaciones de mi entorno que afectan mi salud y propongo soluciones prácticas que involucran la utilización de artefactos.",
    "Selecciono entre diversos artefactos disponibles los más adecuados para realizar tareas cotidianas en el hogar y la escuela, teniendo en cuenta sus restricciones y condiciones de utilización.",
    "Detecto fallas en el funcionamiento de algunos artefactos, actúo de manera segura frente a ellas e informo a los adultos mis observaciones.",
    "Planteo cambios en el diseño de algunos artefactos de mi entorno en relación con su función y seguridad.",
    "Indago cómo están construidos y cómo funcionan algunos artefactos de uso cotidiano.",
    "Explico la forma y el funcionamiento de artefactos por medio de dibujos.",
    "Ensamblo artefactos y dispositivos sencillos siguiendo instrucciones gráficas.",

    // Tecnología y sociedad
    "Exploro mi entorno cotidiano y reconozco la presencia de elementos naturales y de artefactos elaborados con la intención de mejorar las condiciones de vida.",
    "Manifiesto interés por temas relacionados con la tecnología a través de preguntas e intercambio de ideas.",
    "Reconozco que el uso de materiales ha cambiado a través de la historia y que este cambio ha tenido efectos en los estilos de vida y en el desarrollo de la sociedad.",
    "Diferencio en mi entorno productos naturales de productos creados por el ser humano.",
    "Selecciono y utilizo materiales y artefactos de mi entorno para satisfacer mis necesidades (ropa según el clima).",
    "Identifico causas y consecuencias derivadas del uso de artefactos tecnológicos en mi entorno.",
    "Entiendo cómo mis acciones sobre el medio ambiente afectan a otros y las de los otros me afectan.",
    "Identifico materiales caseros y partes de artefactos en desuso para construir objetos que me ayudan a satisfacer mis necesidades y contribuir con la preservación del medio ambiente.",
    "Participo en equipos de trabajo para diseñar, elaborar y evaluar proyectos tecnológicos en los que expreso mis ideas, sentimientos y emociones."
];

// -----------------------------------------------------------------------------
// CICLO 2: GRADOS 4° A 5° - TECNOLOGÍA E INFORMÁTICA
// -----------------------------------------------------------------------------
$tecnologia_4_5 = [
    // Naturaleza y conocimiento de la tecnología
    "Reconozco objetos producidos por el hombre, explico su desarrollo histórico, sus efectos en la sociedad, su proceso de producción y la relación con los recursos naturales involucrados.",
    "Explico la evolución que han tenido algunos artefactos desde los tiempos prehispánicos hasta nuestros días.",
    "Reconozco que los artefactos son productos que pueden ser mejorados permanentemente y aunque algunos parecen distintos cumplen la misma función.",
    "Diferencio objetos producidos en procesos tecnológicos de objetos naturales.",
    "Reconozco invenciones e innovaciones que han aportado al desarrollo del país.",
    "Reconozco que los avances en ciencias naturales y matemáticas inciden en desarrollos tecnológicos.",
    "Identifico el efecto del desarrollo de las ciencias y las matemáticas en los productos tecnológicos.",
    "Muestro la diferencia entre un artefacto y un proceso mediante ejemplos.",
    "Identifico recursos naturales que son utilizados por la tecnología y explico la importancia de su conservación.",
    "Identifico algunas fuentes y tipos de energía y explico cómo se transforman.",
    "Identifico y doy ejemplos de artefactos que involucran tecnologías de la información en su funcionamiento.",

    // Apropiación y uso de la tecnología
    "Describo y explico las características y el funcionamiento de algunos artefactos, productos, procesos y sistemas de mi entorno y los uso en forma segura y apropiada.",
    "Sigo instrucciones sobre el uso adecuado de artefactos y procesos que están en manuales y otros documentos.",
    "Utilizo de forma segura diferentes artefactos y procesos tecnológicos existentes en mi entorno teniendo en cuenta, entre otros, recomendaciones técnicas y aspectos ergonómicos.",
    "Describo y clasifico artefactos existentes en mi entorno de acuerdo con características tales como materiales, forma, función, funcionamiento y fuentes de energía, entre otras.",
    "Describo y utilizo adecuadamente las tecnologías de la información y la comunicación disponibles en mi entorno para el desarrollo de diversas actividades (comunicación, entretenimiento, aprendizaje, búsqueda y validación de información, investigación...).",
    "Utilizo criterios de selección para escoger productos que respondan a mis necesidades (fecha de vencimiento, condiciones de manipulación y de almacenamiento, componentes, efectos sobre la salud y el ambiente).",
    "Empleo con seguridad artefactos y procesos para mantener y conservar productos.",
    "Describo el funcionamiento y las características de artefactos, procesos y sistemas tecnológicos usando diferentes formas de representación (esquemas, dibujos, diagramas).",
    "Ensamblo artefactos y dispositivos sencillos siguiendo instrucciones de texto o esquemáticas.",
    "Utilizo herramientas manuales para realizar de manera segura procesos de medición, trazado, corte, doblado y unión de materiales para construir modelos y maquetas.",

    // Solución de problemas con tecnología
    "Describo y analizo las ventajas y desventajas de la utilización de artefactos y procesos y los empleo para solucionar problemas de la vida cotidiana.",
    "Identifico y describo características, dificultades, deficiencias o riesgos asociados con el empleo de artefactos y procesos en la solución de problemas.",
    "Identifico y comparo ventajas y desventajas de distintas soluciones tecnológicas a un mismo problema.",
    "Identifico fallas sencillas en un artefacto o proceso, actúo en forma segura frente a estas fallas y realizo propuestas de reparación.",
    "Frente a un problema propongo varias soluciones posibles indicando cómo llegué a ellas, sus ventajas y las dificultades de cada una.",
    "Establezco relaciones de correspondencia entre los artefactos y las tallas de los usuarios.",
    "Detecto deficiencias en el diseño de algunos productos tecnológicos y propongo diversas mejoras.",
    "Diseño y construyo soluciones tecnológicas expresadas en maquetas o modelos que funcionan y cumplen con propósitos previamente establecidos.",
    "Participo con mis compañeros en la definición de roles y responsabilidades en el desarrollo de proyectos en tecnología.",
    "Frente a nuevos problemas formulo analogías o adaptaciones de soluciones existentes.",
    "Describo con esquemas, dibujos y textos instrucciones de ensamble de artefactos.",
    "Reconozco y tengo en cuenta los momentos del proceso de diseño al desarrollar soluciones.",
    "Describo y argumento mis propuestas y decisiones para la solución de problemas.",
    "Diseño, construyo, adapto y reparo artefactos sencillos reutilizando materiales caseros para satisfacer mis deseos personales y contribuir a la preservación del medio ambiente.",

    // Tecnología y sociedad
    "Identifico, describo y analizo situaciones en las que se evidencian los efectos sociales y ambientales de las manifestaciones tecnológicas.",
    "Conozco los bienes y servicios que ofrece mi comunidad y velo por su cuidado y buen uso valorando sus beneficios sociales.",
    "Reconozco la importancia de las normas en la prevención de enfermedades y accidentes, promuevo su cumplimiento.",
    "Accedo y utilizo diferentes fuentes de información y medios de comunicación para sustentar mis ideas, ampliar mi perspectiva crítica y tomar decisiones frente a dilemas tecnológicos.",
    "Relaciono costumbres culturales con características del entorno y de uso de diversos artefactos (materiales de construcción en la vivienda, tipos de alimentación).",
    "Identifico instituciones y autoridades a las que puedo acudir para pedir protección de bienes y servicios de mi comunidad.",
    "Identifico el potencial de uso de los recursos naturales en relación con la obtención de energía.",
    "Describo y analizo los efectos que tienen los avances tecnológicos para la salud (uso adecuado de antibióticos en el tratamiento de infecciones).",
    "Analizo y discuto los cambios producidos en el suelo como consecuencia de la acción humana (en agricultura, el uso de pesticidas).",
    "Participo en discusiones que involucran predicciones sobre posibles consecuencias relacionadas con el uso de artefactos y procesos tecnológicos en mi entorno y argumento mis planteamientos.",
    "Me involucro en proyectos tecnológicos relacionados con el buen uso de los recursos naturales y la adecuada disposición de los residuos del entorno en que vivo.",
    "Cumplo con las normas de seguridad, organización y limpieza en los sitios de trabajo y cuido las herramientas y materiales que en ellos se encuentran.",
    "Diferencio los intereses del que fabrica, vende o compra un producto, bien o servicio y me intereso por obtener garantía de calidad.",
    "Describo el impacto que produce en el medio ambiente la utilización de algunos tipos de energía."
];

// -----------------------------------------------------------------------------
// CICLO 3: GRADOS 6° A 7° - TECNOLOGÍA E INFORMÁTICA
// -----------------------------------------------------------------------------
$tecnologia_6_7 = [
    // Naturaleza y conocimiento de la tecnología
    "Analizo y explico la evolución y vinculación que los procesos técnicos han tenido en la fabricación de artefactos y productos que permiten al hombre transformar el entorno y resolver problemas.",
    "Analizo y explico razones por las cuales la evolución de técnicas, procesos, herramientas y materiales han mejorado la fabricación de artefactos y sistemas tecnológicos a lo largo de la historia.",
    "Señalo y explico técnicas y conceptos de otras disciplinas que se han empleado para la generación y evolución de sistemas tecnológicos (alimentación, servicios públicos, salud, transporte).",
    "Explico y ejemplifico cómo en el diseño y uso de artefactos, procesos o sistemas tecnológicos existen principios que los sustentan.",
    "Reconozco en cualquier artefacto (silla, herramientas, zapatos, computadora, celular, televisor, chalupa, remo, cuchara) conceptos científicos y técnicos que permitieron su creación.",
    "Ilustro con ejemplos el compromiso que existe entre diferentes factores en los desarrollos tecnológicos (peso, costo, resistencia, material...).",
    "Identifico innovaciones e inventos trascendentales, los ubico y explico en su contexto histórico y reconozco cómo cambiaron la sociedad.",
    "Explico con ejemplos el concepto de sistema, indico sus componentes y relaciones de causa efecto.",
    "Describo la aplicación de la realimentación en el funcionamiento automático de algunos sistemas.",
    "Explico y doy ejemplos en relación con la transformación entre diferentes tipos de energías.",

    // Apropiación y uso de la tecnología
    "Analizo y explico las características y funcionamiento de algunos artefactos, productos, procesos y sistemas tecnológicos y los utilizo en forma segura y apropiada.",
    "Analizo y aplico las normas de seguridad y ergonomía que se deben tener en cuenta para el uso de algunos artefactos, procesos y sistemas tecnológicos (transporte, recursos energéticos, medicamentos, antibióticos, alimentos, productos de aseo, equipos eléctricos).",
    "Analizo y explico la contribución y el impacto de artefactos, procesos y sistemas tecnológicos en la solución de problemas y satisfacción de necesidades (salud, alimentación, transporte).",
    "Frente a una necesidad o problema selecciono la mejor alternativa de solución entre diferentes productos, artefactos, procesos y sistemas tecnológicos teniendo en cuenta sus características generales, funcionamiento e impacto en el entorno (eficiencia, seguridad, consumo, costo).",
    "Utilizo las tecnologías de la información y la comunicación para apoyar mis procesos de aprendizaje y actividades personales.",
    "En las actividades de aprendizaje busco, selecciono y valido información utilizando diferentes medios tecnológicos.",
    "Utilizo editores de texto y gráficos para elaborar mis trabajos.",
    "Utilizo herramientas y equipos de manera segura para construir modelos, maquetas y prototipos.",
    "Interpreto gráficos, bocetos y planos que requiero para el uso y la elaboración de artefactos y productos, así como para el reconocimiento de ciertos procesos y sistemas tecnológicos.",
    "Realizo representaciones gráficas tridimensionales, en perspectivas isométricas, de ideas y diseños a mano alzada o con herramientas informáticas que indiquen dimensiones, formas y otras especificaciones necesarias para la comprensión de la representación.",
    "Ensamblo artefactos y dispositivos apoyándome en instrucciones de texto o esquemáticas.",
    "Utilizo instrumentos para medir diferentes dimensiones físicas, interpreto y represento los resultados.",

    // Solución de problemas con tecnología
    "Selecciono, adapto y utilizo artefactos, procesos y sistemas tecnológicos sencillos en la solución de problemas en diferentes contextos.",
    "Identifico y formulo problemas propios del entorno susceptibles de ser resueltos a través de soluciones tecnológicas y reconozco las causas que los originan.",
    "Evalúo, clasifico y selecciono soluciones tecnológicas y establezco el cumplimiento de los propósitos de su diseño en cuanto a formas, función, funcionamiento, materiales y fuentes de energía, entre otros aspectos.",
    "Detecto fallas en algunos artefactos, procesos y sistemas tecnológicos siguiendo procedimientos de prueba y descarte y propongo estrategias de solución.",
    "Identifico la influencia de factores ambientales, sociales, culturales, económicos en la solución de problemas.",
    "Realizo registros antropométricos y valoraciones ergonómicas como parte del proceso de elaboración de soluciones tecnológicas e incluyo consideraciones respecto a la seguridad, el medio ambiente y el contexto cultural y socioeconómico.",
    "Adelanto procesos sencillos de innovación en mi entorno como solución a deficiencias detectadas en productos, procesos y sistemas tecnológicos.",
    "Utilizo las tecnologías de la información y la comunicación para recolectar, seleccionar, organizar y procesar información para la solución de problemas.",
    "Identifico restricciones y especificaciones en los problemas que se quieren resolver.",
    "Trabajo en equipo para la generación de soluciones tecnológicas.",
    "Adapto soluciones tecnológicas a nuevos contextos y problemas.",
    "Utilizo información textual y gráfica para comprender y explicar cómo funcionan, usan, producen y mantienen algunos artefactos y procesos.",
    "Explico y argumento con base en experimentación, evidencias y razonamiento lógico mis propuestas y decisiones en el diseño de soluciones tecnológicas.",

    // Tecnología y sociedad
    "Analizo y explico la relación que existe entre la transformación de los recursos naturales y el desarrollo tecnológico así como su impacto sobre el medio ambiente, la salud y la sociedad.",
    "Me intereso por las tradiciones y valores de mi comunidad y participo en la gestión de iniciativas a favor del medio ambiente, la salud y la cultura (jornadas de recolección de materiales reciclables, vacunación, bazares, festivales...).",
    "Desarrollo habilidades para acceder y manejar fuentes de información que me permitan tomar decisiones razonadas y resolver problemas tecnológicos cotidianos.",
    "Indago sobre posibles soluciones para preservar el ambiente de acuerdo con normas y regulaciones.",
    "Explico el proceso de transformación de los recursos naturales en productos y sistemas tecnológicos y analizo sus ventajas y desventajas (un basurero, una represa).",
    "Reconozco y analizo la importancia que tienen las manifestaciones tecnológicas en ámbitos como el trabajo, la educación, la salud, el transporte, el medio ambiente, la cultura y la recreación, entre otros.",
    "Analizo las ventajas y limitaciones de algunos recursos tecnológicos y evalúo su potencial para satisfacer las necesidades personales y sociales en el entorno familiar, escolar y local.",
    "Exploro diversos recursos energéticos y evalúo su impacto sobre el medio ambiente y las posibilidades de desarrollo para las comunidades.",
    "Evalúo las ventajas y desventajas antes de adquirir y utilizar artefactos y productos tecnológicos.",
    "Participo en discusiones que inviten a reflexionar en torno al uso racional de algunos artefactos tecnológicos.",
    "Reconozco y divulgo los derechos que tienen las comunidades para acceder a bienes y servicios (el acceso a recursos energéticos, hídricos).",
    "Acepto, defiendo y promuevo comportamientos legales relacionados con el empleo de los recursos tecnológicos."
];

// -----------------------------------------------------------------------------
// CICLO 4: GRADOS 8° A 9° - TECNOLOGÍA E INFORMÁTICA
// -----------------------------------------------------------------------------
$tecnologia_8_9 = [
    // Naturaleza y conocimiento de la tecnología
    "Analizo y explico la manera como el hombre, en diversas culturas y regiones del mundo, ha empleado conocimientos científicos y tecnológicos para desarrollar artefactos, procesos y sistemas que buscan resolver problemas y que han transformado el entorno.",
    "Explico la evolución tecnológica y establezco relaciones entre ésta y algunos eventos históricos.",
    "Comparo algunas de las tecnologías empleadas en el pasado con las del presente y explico sus cambios y posibles tendencias.",
    "Identifico y analizo inventos e innovaciones que han marcado hitos en el desarrollo tecnológico.",
    "Reconozco que la evolución de las ciencias permite optimizar algunas de las soluciones tecnológicas existentes.",
    "Explico el funcionamiento de sistemas tecnológicos utilizando conceptos de sistema, subsistemas, entradas, salidas, procesos y recursos.",
    "Explico conceptos propios del conocimiento tecnológico tales como tecnología, procesos, productos, servicios, artefactos, herramientas, materiales, técnica, fabricación y producción.",
    "Identifico artefactos que contienen sistemas de control con realimentación.",
    "Analizo la importancia y el papel que juegan las patentes y los derechos de autor en el desarrollo tecnológico.",
    "Explico la transformación entre diferentes tipos de energías e identifico algunos efectos que produce en el medio ambiente esta transformación.",
    "Ilustro el significado e importancia de la calidad en la producción de artefactos tecnológicos.",
    "Identifico artefactos basados en tecnología digital y reconozco y explico la utilización del sistema binario en estas tecnologías.",
    "Indico las componentes de un sistema informático, exploro las funciones de cada componente y relaciones entre estas, evalúo sus especificaciones y capacidades en relación con las necesidades del usuario.",

    // Apropiación y uso de la tecnología
    "Analizo y explico los principios científicos y leyes en las que se basa el funcionamiento de artefactos, productos, servicios, procesos y sistemas tecnológicos de mi entorno y los utilizo en forma eficiente y segura.",
    "Identifico principios científicos aplicados en el funcionamiento de algunos artefactos, productos, servicios, procesos y sistemas tecnológicos.",
    "Identifico y analizo interacciones entre diferentes sistemas tecnológicos (alimentación y salud, transporte y comunicación).",
    "Reconozco y explico las tecnologías más adecuadas para usarlas en mi hábitat dependiendo de las características y condiciones de mi entorno.",
    "Utilizo responsable y eficientemente fuentes de energía y recursos naturales.",
    "Defiendo con argumentos (evidencias, razonamiento lógico, experimentación) la selección de una manifestación tecnológica para resolver una necesidad o problema.",
    "Utilizo eficientemente la tecnología en el aprendizaje de otras disciplinas (artes, educación física, matemáticas, ciencias).",
    "Utilizo responsable y autónomamente las tecnologías de la información y la comunicación (TIC) para aprender, investigar y comunicarme con otros en el mundo.",
    "Desarrollo ayudas multimedia e hipermedia como apoyo a mi proceso de aprendizaje y de comunicación.",
    "Tomo decisiones argumentadas frente a la utilización de ciertos productos tecnológicos teniendo en cuenta el impacto en el medio ambiente, la sociedad y la salud (alcohol, alimentos, artefactos, medicamentos...).",
    "Reconozco la importancia del mantenimiento de artefactos tecnológicos utilizados en la vida cotidiana (bicicleta, aparatos eléctricos, artefactos de información y comunicación, elementos de laboratorio, herramientas).",
    "Utilizo elementos de protección y normas de seguridad para la manipulación de herramientas y equipos de manera segura para construir modelos, maquetas y prototipos.",
    "Ensamblo sistemas siguiendo instrucciones escritas o esquemáticas.",
    "Utilizo instrumentos tecnológicos para realizar mediciones e identifico algunas fuentes de error en estas mediciones.",

    // Solución de problemas con tecnología
    "Identifico, formulo y resuelvo problemas apropiando conocimiento científico y tecnológico, teniendo en cuenta algunas restricciones y condiciones; reconozco y comparo las diferentes soluciones.",
    "Identifico y formulo problemas propios del entorno susceptibles de ser resueltos a través de soluciones tecnológicas.",
    "Comparo distintas soluciones tecnológicas a un mismo problema teniendo en cuenta aspectos relacionados con: sus características, funcionamiento, costos, eficiencia.",
    "Identifico las condiciones y restricciones de utilización de una solución tecnológica y puedo verificar su cumplimiento.",
    "Detecto fallas en sistemas tecnológicos sencillos siguiendo un proceso de prueba y descarte y propongo soluciones.",
    "Reconozco que no hay soluciones perfectas y que las soluciones a un mismo problema pueden ser diversas en función de los criterios utilizados y su ponderación.",
    "Incluyo aspectos relacionados con la seguridad, ergonomía, impacto en el medio ambiente y en la sociedad en la solución de problemas.",
    "Reconozco la importancia de la innovación, la invención, la investigación, el desarrollo y la experimentación en la elaboración de soluciones tecnológicas como factores de la productividad y la competitividad.",
    "Propongo y realizo procesos de mejoramiento de soluciones tecnológicas y argumento con base en la experimentación, evidencias y razonamiento lógico los cambios propuestos.",
    "Propongo soluciones tecnológicas en condiciones de incertidumbre donde parte de la información debe ser obtenida y parcialmente inferida.",
    "Diseño, construyo y pruebo prototipos de artefactos, procesos y sistemas como respuesta a una necesidad o problema teniendo en cuenta restricciones y especificaciones planteadas.",
    "Explico las características de distintos procesos de transformación de materiales y de obtención de materias primas.",
    "Reconozco propiedades físicas y químicas de los materiales empleados en la fabricación de objetos tecnológicos.",
    "Interpreto y represento ideas sobre diseños, innovaciones o protocolos de experimentos mediante el uso de registros, textos, diagramas, figuras, planos, maquetas, modelos y prototipos.",
    "Realizo representaciones gráficas en dos dimensiones de objetos de tres dimensiones utilizando proyecciones y diseños a mano alzada o con ayuda de herramientas informáticas.",
    "Argumento y explico mis propuestas y decisiones en el diseño de soluciones tecnológicas.",

    // Tecnología y sociedad
    "Participo en discusiones y debates sobre las causas y los efectos sociales, económicos y culturales de los desarrollos tecnológicos y actúo en consecuencia de manera ética y responsable.",
    "Manifiesto interés por los desarrollos tecnológicos en la sociedad, la salud y el medio ambiente y explico relaciones de causa y efecto entre ellos.",
    "Busco y utilizo información para explicar las consecuencias sociales y ambientales relacionadas con aplicaciones tecnológicas en diversos ámbitos.",
    "Analizo el costo ambiental de la explotación de recursos, el agotamiento de las fuentes y el problema de las basuras examinando sus consecuencias a largo plazo.",
    "Indago sobre sistemas tecnológicos en diversos ámbitos y explico sus implicaciones para la sociedad (las telecomunicaciones, la salud).",
    "Reconozco que existen diversos puntos de vista e intereses relacionados con la percepción de los problemas y las soluciones tecnológicas y los tomo en cuenta en mis argumentaciones.",
    "Identifico y analizo la influencia de las tecnologías de la información y la comunicación en los cambios culturales, individuales y sociales así como los intereses de grupos sociales en la producción e innovación tecnológica.",
    "Mantengo una actitud analítica y crítica en relación con el uso de determinados productos contaminantes y la disposición adecuada de residuos (las pilas, el plástico).",
    "Propongo alternativas energéticas con menor impacto para el medio ambiente.",
    "Explico el impacto que producen algunos tipos y fuentes de energía.",
    "Reconozco y analizo el uso potencial de los recursos naturales en algunos desarrollos tecnológicos y evalúo las consecuencias de su agotamiento.",
    "Propongo iniciativas de acción en relación con la preservación, implementación o supresión de los bienes y servicios tecnológicos de mi entorno.",
    "Reconozco y valoro la importancia de conocer mis derechos y deberes como ciudadano para participar en decisiones relacionadas con su protección.",
    "Ejerzo mi papel como ciudadano responsable a través del uso adecuado de los sistemas tecnológicos (transporte, ahorro de energía...).",
    "Utilizo responsablemente productos tecnológicos valorando su pertinencia, calidad y efectos potenciales sobre mi salud y el ambiente.",
    "Entiendo la relación que existe entre el consumo doméstico de servicios públicos y la factura de cobro respectiva.",
    "Establezco relaciones entre la práctica de los deportes, las artes, la recreación entre otras y las respectivas normas de seguridad y utilizo los elementos de protección correspondientes (cascos, rodilleras).",
    "Identifico el ciclo de vida de la tecnología y evalúo las consecuencias de su prolongación."
];

// -----------------------------------------------------------------------------
// CICLO 5: GRADOS 10° A 11° - TECNOLOGÍA E INFORMÁTICA
// -----------------------------------------------------------------------------
$tecnologia_10_11 = [
    // Naturaleza y conocimiento de la tecnología
    "Interpreto la tecnología y sus manifestaciones (artefactos, procesos, productos, servicios y sistemas) como elaboración cultural que ha evolucionado a través del tiempo para cubrir necesidades, mejorar condiciones de vida y solucionar problemas.",
    "Explico cómo la tecnología ha evolucionado en sus diferentes manifestaciones (artefactos, productos, servicios, procesos y sistemas) y la manera cómo éstas han influido en los cambios estructurales de la sociedad y su cultura a lo largo de la historia.",
    "Explico la influencia recíproca en la evolución de la tecnología, la sociedad y la cultura.",
    "Analizo críticamente la evolución cada vez más rápida de algunos sistemas tecnológicos y explico sus relaciones con las ciencias y la técnica.",
    "Reconozco y explico cómo procesos creativos de innovación, investigación, desarrollo y experimentación guiados por objetivos producen avances tecnológicos.",
    "Identifico y analizo ejemplos exitosos y no exitosos de transferencia tecnológica en la solución de problemas y necesidades.",
    "Analizo la interacción entre el desarrollo tecnológico y los avances en la ciencia, la técnica y las matemáticas.",
    "Analizo críticamente el desarrollo científico y tecnológico del país identificando factores que intervienen en él mismo.",
    "Analizo sistemas tecnológicos utilizando conceptos de sistema, subsistemas, entradas, salidas, procesos y recursos.",
    "Propongo ejemplos donde son utilizados conceptos propios del conocimiento tecnológico tales como tecnología, procesos, productos, servicios, artefactos, herramientas, materiales, técnica, fabricación, producción, restricciones, compromisos y control de calidad.",
    "Utilizo procesos de optimización en el desarrollo de algunos productos, procesos y servicios y resalto, analizo y justifico las decisiones tomadas sobre las relaciones entre los factores involucrados (tiempo, material, peso, costo, resistencia).",
    "Identifico sistemas de control basados en realimentación en artefactos y procesos y explico su funcionamiento y efecto.",
    "Realizo y explico transformaciones entre algunos tipos de energía, explico los efectos de estas transformaciones.",
    "Argumento con ejemplos la importancia de la medición en la vida cotidiana y el papel que juega la metrología.",
    "Verifico la calibración de algunos instrumentos de medición de algunos instrumentos sencillos.",
    "Explico el significado de la calidad en la producción de artefactos tecnológicos y explico su importancia.",

    // Apropiación y uso de la tecnología
    "Selecciono y utilizo eficientemente en el ámbito personal y social artefactos, productos, servicios, procesos y sistemas tecnológicos teniendo en cuenta su funcionamiento, potencialidades y limitaciones.",
    "Analizo y explico los objetivos, las limitaciones y posibilidades de algunos sistemas tecnológicos (transporte, comunicaciones, hábitat, producción industrial, agropecuaria y comercial).",
    "Analizo y explico los principios de obtención de algunos productos y generación de algunos servicios (alimentación, salud, vestuario, transporte, vivienda...).",
    "Frente a un problema o necesidad selecciono entre diferentes opciones tecnológicas de solución utilizando argumentos basados en criterios de calidad, eficiencia, relación beneficio costo, impacto.",
    "Identifico las interacciones que se dan entre diversas tecnologías y sus aplicaciones en ámbitos diversos (TICS, robótica, transporte, alimentación, agrícola).",
    "Diseño y aplico planes sistemáticos de mantenimiento de artefactos tecnológicos utilizados en la vida cotidiana (bicicleta, aparatos eléctricos, equipos de información y comunicación, elementos de laboratorio, herramientas).",
    "Investigo y documento algunos procesos de producción y manufactura de productos, analizo críticamente las tecnologías utilizadas, la calidad obtenida y el impacto sobre el entorno.",
    "Utilizo eficientemente la tecnología en el aprendizaje y la producción en otras disciplinas (artes, educación física, matemáticas, ciencias).",
    "Promuevo la utilización responsable y eficiente de fuentes de energía y recursos naturales.",
    "Reconozco algunas tendencias tecnológicas en productos y servicios (TICS, transporte, alimentación).",
    "Utilizo tecnologías de la información y la comunicación para mejorar la productividad, eficiencia, calidad y gestión en mis actividades personales, laborales y sociales y en la realización de proyectos colaborativos.",
    "Utilizo adecuadamente herramientas informáticas de uso común para la búsqueda y procesamiento de información y la comunicación de ideas (hoja de cálculo, editor de página Internet, editores de texto y gráficos, buscadores, correo electrónico, conversación en línea, comercio electrónico...).",
    "Actúo teniendo en cuenta normas de seguridad industrial y utilizo elementos de protección en ambientes de trabajo y de producción.",
    "Utilizo e interpreto manuales, instrucciones, diagramas, esquemas para el montaje de algunos artefactos, dispositivos y sistemas tecnológicos.",
    "Utilizo herramientas y equipos en la construcción de modelos, maquetas o prototipos aplicando normas de seguridad.",
    "Trabajo en equipo en la realización de proyectos tecnológicos involucrando herramientas tecnológicas de comunicación.",
    "Selecciono y utilizo según los requerimientos instrumentos tecnológicos para medir, interpreto los resultados, los analizo y estimo el error en estas medidas.",
    "Integro componentes y pongo en marcha sistemas informáticos personales siguiendo manuales e instrucciones.",
    "Selecciono entre sistemas y servicios informáticos de uso personal atendiendo a sus características y las necesidades del usuario.",

    // Solución de problemas con tecnología
    "Identifico, formulo y resuelvo problemas a través de la apropiación de conocimiento científico y tecnológico utilizando diferentes estrategias y evalúo rigurosa y sistemáticamente las soluciones teniendo en cuenta las condiciones, restricciones y especificaciones del problema planteado.",
    "Identifico y formulo problemas propios del entorno y susceptibles de ser resueltos a través de soluciones tecnológicas.",
    "Evalúo y selecciono con argumentos basados en experimentación, evidencias y razonamiento lógico mis propuestas y decisiones en torno al diseño.",
    "Identifico cuál es el problema o necesidad que originó el desarrollo de una tecnología, artefacto o sistema tecnológico y valoro el impacto derivado de su desarrollo.",
    "Identifico las condiciones, especificaciones y restricciones de diseño utilizadas en una solución tecnológica y puedo verificar su cumplimiento.",
    "Detecto, describo y formulo hipótesis sobre fallas en sistemas tecnológicos sencillos siguiendo un proceso de prueba y descarte riguroso y propongo estrategias para repararlas.",
    "Propongo, analizo y comparo diferentes soluciones a un mismo problema explicando su origen, ventajas y dificultades.",
    "Tengo en cuenta aspectos relacionados con la antropometría, la ergonomía, la seguridad, el medio ambiente y el contexto cultural y socioeconómico al momento de solucionar problemas con tecnología.",
    "Optimizo soluciones tecnológicas a través de estrategias de innovación, investigación, desarrollo y experimentación y argumento los criterios y la ponderación de los factores utilizados.",
    "Propongo soluciones tecnológicas en condiciones de incertidumbre donde la información puede ser incierta, errónea o incompleta.",
    "Diseño, construyo y pruebo prototipos de artefactos, procesos y sistemas como respuesta a necesidades o problemas teniendo en cuenta restricciones y especificaciones planteadas.",
    "Describo las características de distintos procesos de producción de productos tecnológicos en diversos contextos.",
    "Reconozco que cada solución tecnológica – artefacto, sistema o proceso – puede ser mejorado y que su desarrollo siempre está condicionado por restricciones humanas, técnicas, científicas, económicas, de tiempo o de cualquier otra índole.",
    "Transfiero tecnologías adaptando soluciones tecnológicas a nuevos contextos y problemas.",
    "Propongo y evalúo la utilización de tecnología para mejorar la productividad en la pequeña empresa.",
    "Interpreto y represento ideas sobre diseños, innovaciones o protocolos de experimentos mediante el uso de registros, textos, diagramas, figuras, planos constructivos, maquetas, modelos y prototipos empleando para ello, cuando sea posible, herramientas informáticas.",
    "Realizo planos y diseños manualmente o con ayuda de herramientas informáticas.",

    // Tecnología y sociedad
    "Analizo las implicaciones éticas, sociales y ambientales de las manifestaciones tecnológicas del mundo en que vivo, evalúo críticamente los alcances, limitaciones y beneficios de éstas y tomo decisiones responsables relacionadas con sus aplicaciones.",
    "Busco, proceso y utilizo información apropiada para plantear soluciones a problemas sociales y ambientales relacionados con las aplicaciones e innovaciones tecnológicas en diferentes contextos.",
    "Discuto sobre el impacto de los desarrollos tecnológicos incluida la biotecnología en la medicina, la agricultura y la industria.",
    "Analizo y describo factores culturales y tecnológicos que inciden en la sexualidad y las terapias reproductivas.",
    "Participo en discusiones relacionadas con las aplicaciones e innovaciones tecnológicas sobre la salud, tomo postura y argumento mis intervenciones (por ejemplo, sobre los efectos de diversos artefactos, procesos y sistemas sobre la salud).",
    "Evalúo los procesos productivos de diversos artefactos y sistemas tecnológicos teniendo en cuenta sus efectos sobre el medio ambiente y las comunidades implicadas (el manejo de desechos industriales y basuras).",
    "Analizo el potencial de los recursos naturales y de los nuevos materiales utilizados en la producción tecnológica en diferentes contextos.",
    "Selecciono y utilizo los servicios que me brindan las Tecnologías de la Información y la Comunicación atendiendo a criterios de responsabilidad y calidad.",
    "Analizo críticamente las relaciones entre la tecnología, la ciencia y la técnica y explico algunas de sus potencialidades y limitaciones.",
    "Escojo productos apropiados para utilización y consumo humano reconozco sus componentes y el efecto de ellos en la salud (alimentación, transporte, vestuario...).",
    "Analizo proyectos tecnológicos en desarrollo y debato en mi comunidad el impacto de su posible implementación.",
    "Identifico e indago problemas que afectan directamente a mi comunidad como consecuencia de la implementación o el retiro de bienes y servicios tecnológicos y propongo acciones encaminadas a buscar soluciones sostenibles dentro un contexto participativo.",
    "Conozco y ejerzo mis derechos y deberes ciudadanos en un mundo influenciado por la tecnología y utilizo los procedimientos adecuados para exigir su cumplimiento.",
    "Tomo decisiones relacionadas con las implicaciones sociales y ambientales de la tecnología comunico los criterios básicos que utilicé o las razones que me condujeron a tomarlas.",
    "Selecciono fuentes y tipos de energía teniendo en cuenta entre otros aspectos ambientales.",
    "Diseño y desarrollo estrategias de trabajo en equipo que contribuyan a la protección de mis derechos y los de mi comunidad (campañas de promoción y divulgación de derechos humanos de la juventud).",
    "Evalúo las implicaciones para la sociedad de la protección a la propiedad intelectual en el desarrollo y la utilización de la tecnología.",
    "Tengo en cuenta el ciclo de vida de las tecnologías para su uso, analizo y evalúo el impacto en el medio ambiente de la disposición final una vez ha terminado su vida útil."
];

// Generación del array por grados independientes (1 a 11)
for ($i = 1; $i <= 11; $i++) {
    $grupo = [];

    if ($i <= 3) {
        $grupo = $tecnologia_1_3;
    } elseif ($i <= 5) {
        $grupo = $tecnologia_4_5;
    } elseif ($i <= 7) {
        $grupo = $tecnologia_6_7;
    } elseif ($i <= 9) {
        $grupo = $tecnologia_8_9;
    } else {
        $grupo = $tecnologia_10_11;
    }

    $datos[] = [
        "grado" => $i,
        "asignatura" => "tecnologia_informatica",
        "dbas" => $grupo
    ];
}

// Salida JSON
echo json_encode([
    "status" => "success",
    "source" => "Estándares Básicos de Competencias en Tecnología e Informática (MEN 2006)",
    "nota" => "Estándares organizados por cuatro componentes: Naturaleza y conocimiento de la tecnología, Apropiación y uso de la tecnología, Solución de problemas con tecnología, Tecnología y sociedad. Mapeados como DBAs para compatibilidad del sistema.",
    "componentes" => [
        "naturaleza_conocimiento" => "Valora el dominio básico de conceptos fundamentales de la tecnología y su evolución histórica y cultural.",
        "apropiacion_uso" => "Valora la utilización adecuada, pertinente y crítica de la tecnología con fines de optimización y productividad.",
        "solucion_problemas" => "Valora el dominio en la identificación, formulación y solución de problemas con tecnología.",
        "tecnologia_sociedad" => "Valora actitudes hacia la tecnología, valoración social y participación social ética y responsable."
    ],
    "data" => $datos
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>