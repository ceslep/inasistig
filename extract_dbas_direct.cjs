const fs = require('fs');
const path = require('path');

const sourcePath = path.join(__dirname, 'src', 'assets', 'php', 'deepseek_php_20260223_1888f6.php');
const content = fs.readFileSync(sourcePath, 'utf8');

let finalOutput = "";

const gradoMap = {
    1: 'primero', 2: 'segundo', 3: 'tercero', 4: 'cuarto',
    5: 'quinto', 6: 'sexto', 7: 'septimo', 8: 'octavo',
    9: 'noveno', 10: 'decimo', 11: 'once'
};

function extractCategory(funcName, prefix, shortName, longName, category, extraField) {
    const fnRegex = new RegExp(`function ${funcName}\\(\\) \\{[\\s\\S]*?\\$descripciones = \\[\n([\\s\\S]*?)\n    \\];\n[\\s\\S]*?\\'(?:ejes|competencias|componentes|lenguajes)\\'\\s*=>\\s*\\[(.*?)\\]`, '');
    const match = fnRegex.exec(content);
    if (!match) {
        console.error("Failed to match " + funcName);
        return "";
    }

    const descBlock = match[1];
    const extrasBlock = match[2];

    const extras = [...extrasBlock.matchAll(/'([^']+)'/g)].map(m => m[1]);

    let output = `        "${shortName}" => [\n`;
    output += `            "nombre" => "${longName}",\n`;
    output += `            "categoria" => "${category}",\n`;
    output += `            "dbas" => [\n`;

    const gradeMatches = [...descBlock.matchAll(/(\d+)\s*=>\s*\[\s*([\s\S]*?)\s*\](?=,|    \])/g)];

    for (const gMatch of gradeMatches) {
        const gradoNum = gMatch[1];
        const itemsStr = gMatch[2];
        const items = [...itemsStr.matchAll(/'([^']+)'/g)].map(m => m[1]);

        let i = 1;
        for (const str of items) {
            const extra = extras[i - 1] || extras[0] || "";
            const id = `${prefix}-${gradoNum}-${String(i).padStart(2, '0')}`;
            const cleaned = str.replace(/"/g, '\\"');
            output += `                ["id" => "${id}", "grado" => "${gradoMap[gradoNum]}", "descripcion" => "${cleaned}", "evidencia" => "", "${extraField}" => "${extra}"],\n`;
            i++;
        }
    }

    output += `            ]\n        ],\n`;
    return output;
}

// 1. Paz
finalOutput += extractCategory("generarDBAPaz", "PAZ", "catedra_paz", "Cátedra de la Paz", "Transversal", "eje");

// 2. Ciudadana
finalOutput += extractCategory("generarDBACiudadana", "CIU", "formacion_ciudadana", "Competencias Ciudadanas", "Transversal", "competencia");

// 3. Afro
finalOutput += extractCategory("generarDBAAfro", "AFR", "catedra_afrocolombiana", "Cátedra de Estudios Afrocolombianos", "Transversal", "eje");

// 4. Religion
finalOutput += extractCategory("generarDBAReligion", "REL", "educacion_religiosa", "Educación Religiosa", "Opcional (según PEI)", "eje");

// 5. Preescolar
const preeRegex = /'preescolar'\s*=>\s*\[[\s\S]*?'dimensiones'\s*=>\s*\[\n([\s\S]*?)\n        \]\n    \],/g;
const preeMatch = preeRegex.exec(content);
if (preeMatch) {
    let output = `        "preescolar" => [\n`;
    output += `            "nombre" => "Preescolar",\n`;
    output += `            "categoria" => "Transición",\n`;
    output += `            "dbas" => [\n`;

    const block = preeMatch[1];

    const dimMatches = [...block.matchAll(/'(\w+)'\s*=>\s*\[\s*'nombre'\s*=>\s*'([^']+)',\s*'dba'\s*=>\s*\[([\s\S]*?)\]\s*\]/g)];

    let globalIdx = 1;
    for (const dMatch of dimMatches) {
        const dimName = dMatch[2];
        const itemsStr = dMatch[3];
        const items = [...itemsStr.matchAll(/'([^']+)'/g)].map(m => m[1]);

        for (const str of items) {
            const id = `PRE-COG-${String(globalIdx).padStart(2, '0')}`;
            const cleaned = str.replace(/"/g, '\\"');
            output += `                ["id" => "${id}", "grado" => "transicion", "descripcion" => "${cleaned}", "evidencia" => "", "dimension" => "${dimName}"],\n`;
            globalIdx++;
        }
    }

    output += `            ]\n        ],\n`;
    finalOutput = output + finalOutput;
} else {
    console.error("Failed to match preescolar");
}

fs.writeFileSync(path.join(__dirname, 'extracted_dbas.txt'), finalOutput);
console.log("Extraction complete!");
