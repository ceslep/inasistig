const fs = require('fs');
const path = require('path');

const filePath = path.join('c:', 'Users', 'cesar', 'inasistig', 'src', 'assets', 'php', 'getDBAs.php');

try {
    let content = fs.readFileSync(filePath, 'utf8');

    // 1. Fix areaMap mappings
    // Replace the problematic mappings to match data keys
    content = content.replace(/'etica' => 'etica'/g, "'etica' => 'etica_valores'");
    content = content.replace(/'ética' => 'etica'/g, "'ética' => 'etica_valores'");
    content = content.replace(/'tecnologia' => 'tecnologia'/g, "'tecnologia' => 'tecnologia_informatica'");
    content = content.replace(/'tecnología' => 'tecnologia'/g, "'tecnología' => 'tecnologia_informatica'");
    content = content.replace(/'religion' => 'religion'/g, "'religion' => 'educacion_religiosa'");
    content = content.replace(/'religión' => 'religion'/g, "'religión' => 'educacion_religiosa'");
    content = content.replace(/'ambiental' => 'ambiental'/g, "'ambiental' => 'educacion_ambiental'");
    content = content.replace(/'sexual' => 'sexual'/g, "'sexual' => 'educacion_sexual'");
    content = content.replace(/'economia' => 'economia'/g, "'economia' => 'educacion_economica'");

    // 2. Fix parameter overwrite
    // We need to find the block near the end that resets $area and $grado
    const oldBlockRegex = /\/\/ Manejo de parámetros GET para filtrar\s+\$area = isset\(\$_GET\['area'\]\) \? strtolower\(\$_GET\['area'\]\) : null;\s+\$grado = isset\(\$_GET\['grado'\]\) \? strtolower\(\$_GET\['grado'\]\) : null;\s+\$id = isset\(\$_GET\['id'\]\) \? strtoupper\(\$_GET\['id'\]\) : null;/;

    const newBlock = `// Manejo de parámetros adicionales
$id = isset($_GET['id']) ? strtoupper($_GET['id']) : ($input['id'] ?? null);
// $area y $grado ya fueron normalizados y mapeados al inicio`;

    if (oldBlockRegex.test(content)) {
        content = content.replace(oldBlockRegex, newBlock);
        console.log('Successfully replaced the overwriting block.');
    } else {
        console.warn('Could not find the overwriting block with the expected regex. Trying a simpler match...');
        // Fallback search for the specific lines
        const searchLines = [
            "// Manejo de parámetros GET para filtrar",
            "$area = isset($_GET['area']) ? strtolower($_GET['area']) : null;",
            "$grado = isset($_GET['grado']) ? strtolower($_GET['grado']) : null;",
            "$id = isset($_GET['id']) ? strtoupper($_GET['id']) : null;"
        ];

        let found = true;
        let modifiedContent = content;
        for (const line of searchLines) {
            if (!content.includes(line)) {
                found = false;
                console.log(`Missing line: ${line}`);
                break;
            }
        }

        if (found) {
            const fullMatch = searchLines.join('\r\n');
            const fullMatchLF = searchLines.join('\n');
            if (content.includes(fullMatch)) {
                modifiedContent = content.replace(fullMatch, newBlock);
            } else if (content.includes(fullMatchLF)) {
                modifiedContent = content.replace(fullMatchLF, newBlock);
            }
            content = modifiedContent;
            console.log('Successfully replaced the overwriting block using line matching.');
        } else {
            console.error('FATAL: Overwriting block not found.');
        }
    }

    fs.writeFileSync(filePath, content, 'utf8');
    console.log('File successfully updated.');

} catch (err) {
    console.error('Error:', err);
    process.exit(1);
}
