const gradoMap = {
    1: "primero", 2: "segundo", 3: "tercero", 4: "cuarto", 5: "quinto",
    6: "sexto", 7: "septimo", 8: "octavo", 9: "noveno", 10: "decimo", 11: "once"
};

const preescolarData = [
    { dim: "Cognitiva", dbas: ["Reconoce y nombra letras, números y colores básicos", "Comprende conceptos básicos de tamaño, forma y cantidad", "Identifica y resuelve problemas sencillos de su entorno", "Comprende y sigue instrucciones simples de 2 a 3 pasos", "Desarrolla conciencia fonológica identificando sonidos iniciales"] },
    { dim: "Comunicativa", dbas: ["Expresa ideas y sentimientos mediante lenguaje oral y gestual", "Participa en conversaciones y diálogos respetando turnos", "Comprende mensajes orales en situaciones cotidianas", "Disfruta de la lectura de cuentos y narraciones infantiles", "Realiza trazos y grafías con intención comunicativa"] },
    { dim: "Corporal", dbas: ["Desarrolla coordinación motora gruesa (correr, saltar, lanzar)", "Desarrolla coordinación motora fina (recortar, rasgar, ensartar)", "Reconoce y cuida su cuerpo practicando hábitos de higiene", "Participa en juegos y actividades físicas siguiendo reglas básicas"] },
    { dim: "Estética y Creativa", dbas: ["Explora diferentes materiales para expresión plástica (pintura, modelado)", "Participa en actividades musicales explorando ritmos y canciones", "Representa situaciones cotidianas mediante juego dramático", "Expresa creativamente sus vivencias a través del arte"] },
    { dim: "Socioemocional", dbas: ["Reconoce y expresa emociones básicas de manera adecuada", "Establece relaciones positivas con pares y adultos", "Participa en actividades grupales colaborando con otros niños", "Desarrolla habilidades de autocontrol y resolución pacífica de conflictos", "Muestra respeto y empatía hacia los demás"] },
    { dim: "Ética y Valórica", dbas: ["Comprende valores y normas básicas de convivencia en el aula", "Reconoce y respeta la diversidad cultural y las diferencias", "Desarrolla habilidades para compartir, cooperar y ayudar a otros"] }
];

function formatPreescolar() {
    let out = `        "preescolar" => [\n            "nombre" => "Preescolar",\n            "categoria" => "Transición",\n            "dbas" => [\n`;
    let id = 1;
    preescolarData.forEach(dim => {
        dim.dbas.forEach(desc => {
            out += `                ["id" => "PRE-${String(id++).padStart(2, '0')}", "grado" => "transicion", "descripcion" => "${desc}", "evidencia" => "", "dimension" => "${dim.dim}"],\n`;
        });
    });
    out = out.replace(/,\n$/, "\n");
    out += `            ]\n        ],\n`;
    return out;
}

const pazData = {
    1: ['Reconoce la paz como valor fundamental en la convivencia cotidiana', 'Practica formas pacíficas de resolver conflictos en el aula (diálogo, negociación, acuerdos)', 'Identifica la violencia como algo dañino para todos en sus diferentes formas', 'Valora la amistad, el compañerismo y la ayuda mutua como expresiones de paz', 'Participa en la construcción colectiva de acuerdos de convivencia en el aula'],
    2: ['Comprende la importancia del perdón y la reconciliación en las relaciones interpersonales', 'Identifica formas de violencia en su entorno (física, verbal, exclusión) y cómo evitarlas', 'Propone soluciones pacíficas a conflictos cotidianos en la escuela y familia', 'Valora la diversidad como oportunidad de aprendizaje y enriquecimiento mutuo', 'Participa en proyectos colaborativos que promueven la convivencia pacífica'],
    3: ['Reconoce la importancia de los derechos humanos para la convivencia pacífica', 'Identifica mecanismos de participación democrática en el aula y la escuela', 'Practica la escucha activa y la empatía en situaciones de conflicto', 'Valora la mediación como estrategia para resolver diferencias', 'Participa en campañas escolares de promoción de la paz y la no violencia'],
    4: ['Comprende el conflicto como oportunidad de aprendizaje y transformación positiva', 'Analiza causas y consecuencias de conflictos en diferentes contextos', 'Desarrolla habilidades para la negociación y el consenso en grupos', 'Reconoce la importancia de la memoria histórica para la construcción de paz', 'Participa en iniciativas de servicio comunitario que promueven la reconciliación'],
    5: ['Analiza la relación entre justicia, equidad y construcción de paz', 'Comprende conceptos básicos sobre conflicto armado en Colombia', 'Reconoce el papel de las víctimas en los procesos de reconciliación', 'Valora experiencias de construcción de paz en comunidades locales', 'Participa en proyectos escolares de memoria histórica y cultura de paz'],
    6: ['Comprende el concepto de paz positiva (no solo ausencia de guerra, sino justicia social)', 'Analiza causas estructurales de los conflictos (desigualdad, exclusión, discriminación)', 'Reconoce experiencias históricas de construcción de paz en Colombia', 'Desarrolla habilidades para el análisis crítico de conflictos sociales', 'Participa en iniciativas juveniles de promoción de derechos humanos'],
    7: ['Analiza el conflicto armado colombiano: causas, actores, etapas y consecuencias', 'Comprende los procesos de paz en Colombia (acuerdos, negociaciones, desmovilizaciones)', 'Reconoce el papel de organizaciones sociales en la construcción de paz', 'Valora la importancia de la justicia transicional y los derechos de las víctimas', 'Participa en ejercicios de memoria histórica en su comunidad'],
    8: ['Analiza el Acuerdo de Paz con las FARC-EP (2016): contenidos, implementación y desafíos', 'Comprende conceptos de justicia restaurativa, verdad, reparación y no repetición', 'Reconoce experiencias de reconciliación en territorios afectados por el conflicto', 'Analiza críticamente discursos sobre paz y violencia en medios de comunicación', 'Participa en proyectos pedagógicos sobre cultura de paz y reconciliación'],
    9: ['Analiza los desafíos actuales para la construcción de paz en Colombia (violencias emergentes, narcotráfico, desigualdad)', 'Comprende la relación entre paz, desarrollo sostenible y construcción de territorios', 'Reconoce el papel de jóvenes, mujeres y comunidades étnicas en procesos de paz', 'Analiza experiencias internacionales de construcción de paz posconflicto', 'Desarrolla propuestas de incidencia para la paz desde entornos escolares'],
    10: ['Profundiza en teorías de paz y conflicto desde diferentes perspectivas disciplinares', 'Analiza la relación entre memoria histórica, verdad y reconciliación en Colombia', 'Comprende los desafíos de la implementación de acuerdos de paz en contextos locales', 'Reconoce experiencias de resistencia civil y construcción de paz desde bases', 'Participa en investigaciones escolares sobre memoria y construcción de paz'],
    11: ['Sistematiza una comprensión integral del conflicto y la paz en Colombia', 'Analiza críticamente el momento actual del posconflicto y sus perspectivas', 'Reflexiona sobre su papel como ciudadano en la construcción de paz duradera', 'Desarrolla propuestas de intervención para la paz en su comunidad', 'Prepara su transición a la vida adulta con compromiso activo por la paz y la reconciliación']
};

function formatArea(prefix, name, data, extraKey, extraVal) {
    let out = `        "${name}" => [\n            "nombre" => "${name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}",\n            "categoria" => "Transversal",\n            "dbas" => [\n`;
    for (const [grade, lines] of Object.entries(data)) {
        lines.forEach((line, i) => {
            out += `                ["id" => "${prefix}-${grade}-${String(i + 1).padStart(2, '0')}", "grado" => "${gradoMap[grade]}", "descripcion" => "${line}", "evidencia" => "", "${extraKey}" => "${extraVal}"],\n`;
        });
    }
    out = out.replace(/,\n$/, "\n");
    out += `            ]\n        ],\n`;
    return out;
}

const finalOut = formatPreescolar() + formatArea("PAZ", "catedra_paz", pazData, "eje", "Cultura de Paz");
require('fs').writeFileSync('block.txt', finalOut);
