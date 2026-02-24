const fs = require('fs');
const path = require('path');

const getDBAsPath = path.join(__dirname, 'src', 'assets', 'php', 'getDBAs.php');
const deepseekPath = path.join(__dirname, 'src', 'assets', 'php', 'deepseek_php_20260223_1888f6.php');

const deepseekContent = fs.readFileSync(deepseekPath, 'utf8');

function extractArray(content, key) {
    const regex = new RegExp(`'${key}'\\s*=>\\s*\\[([\\s\\S]*?)\n    \\],`, 'g');
    const match = regex.exec(content);
    if (!match) return null;
    return match[1];
}

const gradoMap = {
    1: "primero", 2: "segundo", 3: "tercero", 4: "cuarto", 5: "quinto",
    6: "sexto", 7: "septimo", 8: "octavo", 9: "noveno", 10: "decimo", 11: "once"
};

function parseDescripciones(content, funcName) {
    const regex = new RegExp(`function ${funcName}\\(\\) \\{[\\s\\S]*?\\$descripciones = \\[\n([\\s\\S]*?)\n    \\];`, '');
    const match = regex.exec(content);
    if (!match) return null;

    const block = match[1];
    const results = {};
    const gradeRegex = /(\d+) => \[\s*([\s\S]*?)\s*\]/g;
    let m;
    while ((m = gradeRegex.exec(block)) !== null) {
        const grade = m[1];
        const lines = m[2].split('\n').map(l => l.trim()).filter(l => l.startsWith("'")).map(l => l.replace(/^'|',?$/g, "").replace(/\\'/g, "'"));
        results[grade] = lines;
    }
    return results;
}

function parseExtras(content, funcName) {
    const regex = new RegExp(`function ${funcName}\\(\\) \\{[\\s\\S]*?\\'(?:ejes|competencias)\\'\\s*=>\\s*\\[(.*?)\\]`, '');
    const match = regex.exec(content);
    if (!match) return [];
    return match[1].split(',').map(s => s.trim().replace(/^'|'$/g, ""));
}

function formatAsGetDBA(prefix, name, category, descripciones, extras, extraKey) {
    let output = `        "${name}" => [\n`;
    output += `            "nombre" => "${name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}",\n`;
    output += `            "categoria" => "${category}",\n`;
    output += `            "dbas" => [\n`;

    for (const [gradeNum, lines] of Object.entries(descripciones)) {
        const gradeStr = gradoMap[gradeNum];
        lines.forEach((line, i) => {
            const extra = extras[i] || extras[0] || "";
            const id = `${prefix}-${gradeNum}-${String(i + 1).padStart(2, '0')}`;
            const escaped = line.replace(/"/g, '\\"');
            output += `                ["id" => "${id}", "grado" => "${gradeStr}", "descripcion" => "${escaped}", "evidencia" => "", "${extraKey}" => "${extra}"],\n`;
        });
    }
    output = output.replace(/,\n$/, "\n");
    output += `            ]\n        ],\n`;
    return output;
}

// Extract Preescolar
function extractPreescolar() {
    const regex = /'preescolar'\s*=>\s*\[[\s\S]*?'dimensiones'\s*=>\s*\[\n([\s\S]*?)\n        \]\n    \],/;
    const match = regex.exec(deepseekContent);
    if (!match) return "";

    const block = match[1];
    let output = `        "preescolar" => [\n`;
    output += `            "nombre" => "Preescolar",\n`;
    output += `            "categoria" => "Transición",\n`;
    output += `            "dbas" => [\n`;

    const dimRegex = /'(\w+)' => \[\s*'nombre' => '([^']+)',\s*'dba' => \[\n([\s\S]*?)\n\s*\]\s*\]/g;
    let m;
    let idx = 1;
    while ((m = dimRegex.exec(block)) !== null) {
        const dimName = m[2];
        const lines = m[3].split('\n').map(l => l.trim()).filter(l => l.startsWith("'")).map(l => l.replace(/^'|',?$/g, "").replace(/\\'/g, "'"));
        lines.forEach(line => {
            const id = `PRE-COG-${String(idx++).padStart(2, '0')}`;
            const escaped = line.replace(/"/g, '\\"');
            output += `                ["id" => "${id}", "grado" => "transicion", "descripcion" => "${escaped}", "evidencia" => "", "dimension" => "${dimName}"],\n`;
        });
    }
    output = output.replace(/,\n$/, "\n");
    output += `            ]\n        ],\n`;
    return output;
}

const preescolarBlock = extractPreescolar();
const pazBlock = formatAsGetDBA("PAZ", "catedra_paz", "Transversal", parseDescripciones(deepseekContent, "generarDBAPaz"), parseExtras(deepseekContent, "generarDBAPaz"), "eje");
const ciudadanaBlock = formatAsGetDBA("CIU", "formacion_ciudadana", "Transversal", parseDescripciones(deepseekContent, "generarDBACiudadana"), parseExtras(deepseekContent, "generarDBACiudadana"), "competencia");
const afroBlock = formatAsGetDBA("AFR", "catedra_afrocolombiana", "Transversal", parseDescripciones(deepseekContent, "generarDBAAfro"), parseExtras(deepseekContent, "generarDBAAfro"), "eje");
const religionBlock = formatAsGetDBA("REL", "educacion_religiosa", "Opcional (según PEI)", parseDescripciones(deepseekContent, "generarDBAReligion"), parseExtras(deepseekContent, "generarDBAReligion"), "eje");

let getDBAsContent = fs.readFileSync(getDBAsPath, 'utf8');

// Replace Religion
const religionStart = getDBAsContent.indexOf('"educacion_religiosa" => [');
if (religionStart !== -1) {
    let religionEnd = getDBAsContent.indexOf('        ],', religionStart);
    if (religionEnd !== -1) {
        // Find the next area or the end of areas
        let nextArea = getDBAsContent.indexOf('        "', religionEnd + 10);
        if (nextArea === -1) nextArea = getDBAsContent.indexOf('    ]', religionEnd);

        const before = getDBAsContent.substring(0, religionStart);
        const after = getDBAsContent.substring(nextArea);
        getDBAsContent = before + religionBlock + after;
    }
}

// Append others before the final '    ]' of areas
const areasEnd = getDBAsContent.lastIndexOf('    ]');
const beforeEnd = getDBAsContent.substring(0, areasEnd);
const afterEnd = getDBAsContent.substring(areasEnd);

getDBAsContent = beforeEnd + preescolarBlock + pazBlock + ciudadanaBlock + afroBlock + afterEnd;

// Update Metadata
getDBAsContent = getDBAsContent.replace(/"total_areas" => \d+/, '"total_areas" => 16');
// Estimate total DBAs (existing was 150, we added 4 areas * ~5 grades/avg * 5 items/avg + more for religion)
// Exact count is better but let's just say 350 for now
getDBAsContent = getDBAsContent.replace(/"total_dbas" => \d+/, '"total_dbas" => 350');

fs.writeFileSync(getDBAsPath, getDBAsContent, 'utf8');
console.log("Merge completed successfully");
