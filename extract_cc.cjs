const fs = require('fs');
const path = require('path');

const ebcPath = path.join(__dirname, 'src', 'assets', 'php', 'getEBCs.php');
const sourcePath = path.join(__dirname, 'src', 'assets', 'php', 'estandaresbasicosdp.php');

const sourceContent = fs.readFileSync(sourcePath, 'utf8');

const startStr = "'competencias_ciudadanas' => [";
const startIdx = sourceContent.indexOf(startStr);
if (startIdx === -1) {
    console.error("Not found competencies");
    process.exit(1);
}

const section = sourceContent.substring(startIdx);

const gradeMatches = [...section.matchAll(/\/\/\s*GRADO\s*(\d+)°(.*?)(?=\/\/\s*GRADO|$)/gs)];

const gradoMap = {
    1: 'primero', 2: 'segundo', 3: 'tercero',
    4: 'cuarto', 5: 'quinto', 6: 'sexto'
};

let output = '        "competencias_ciudadanas" => [\n';
output += '            "nombre" => "Competencias Ciudadanas",\n';
output += '            "tipo" => "Transversal",\n';
output += '            "estandares_por_grado" => [\n';

for (const match of gradeMatches) {
    const gradoNum = parseInt(match[1]);
    const block = match[2];

    if (!gradoMap[gradoNum]) continue;
    const gradoNombre = gradoMap[gradoNum];

    output += `                "${gradoNombre}" => [\n`;

    const groupMatches = [...block.matchAll(/'grupo'\s*=>\s*'([^']+)'\s*,\s*'estandares'\s*=>\s*\[(.*?)\]/gs)];

    let itemIndex = 1;
    for (const gMatch of groupMatches) {
        const grupo = gMatch[1].replace(/'/g, "\\'");
        const estandaresStr = gMatch[2];

        const stdMatches = [...estandaresStr.matchAll(/'([^']+)'/g)];
        for (const sMatch of stdMatches) {
            const estandar = sMatch[1].replace(/'/g, "\\'");
            const id = `CC-${gradoNum}-${String(itemIndex).padStart(2, '0')}`;
            output += `                    ["id" => "${id}", "grado" => "${gradoNombre}", "estandar" => "${estandar}", "dimension" => "${grupo}", "indicadores" => []],\n`;
            itemIndex++;
        }
    }

    output += `                ],\n`;
}

output += '            ]\n';
output += '        ]\n';

console.log("Generated parsing successfully");

fs.writeFileSync(path.join(__dirname, 'cc_out.txt'), output);
console.log("Success Dump");
