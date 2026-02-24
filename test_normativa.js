const { execSync } = require('child_process');
const path = require('path');
const fs = require('fs');

const PHP_DIR = path.join(__dirname, 'src', 'assets', 'php');

function runPhp(file, getParams = {}, postData = null) {
    const filePath = path.join(PHP_DIR, file);
    const queryString = Object.entries(getParams).map(([k, v]) => `${k}=${v}`).join('&');

    // We mock the environment for PHP CLI to simulate Web behavior
    let phpScript = `
        $_GET = [];
        ${Object.entries(getParams).map(([k, v]) => `$_GET['${k}'] = '${v}';`).join('\n')}
        $_SERVER['REQUEST_METHOD'] = '${postData ? 'POST' : 'GET'}';
        
        // Mock php://input
        function mock_input($data) {
             $tmp = tmpfile();
             fwrite($tmp, $data);
             fseek($tmp, 0);
             return $tmp;
        }
        
        // This is tricky in CLI, but for these simple scripts 
        // we can just define variables if the script uses them or use a wrapper.
        // Actually, easier to use a temporary wrapper file.
    `;

    const wrapperPath = path.join(__dirname, `wrapper_${file}`);
    const postJson = postData ? JSON.stringify(postData).replace(/'/g, "\\'") : '';

    const wrapperContent = `<?php
$_GET = json_decode('${JSON.stringify(getParams)}', true);
$_SERVER['REQUEST_METHOD'] = '${postData ? 'POST' : 'GET'}';
// We override file_get_contents for the specific call to php://input if needed, 
// but it's easier to just mock the $input variable if we can or use a stream filter.
// For our scripts: $input = json_decode(file_get_contents('php://input'), true);
// We'll use a data:// protocol or similar if PHP supports it, or just a temp file.
$postData = '${postJson}';
if ($postData) {
    $tmp = tempnam(sys_get_temp_dir(), 'php_input');
    file_put_contents($tmp, $postData);
    // This is hard to mock globally without extensions. 
    // Let's just modify the script temporarily to use a variable or use a clever trick.
}

// Simple approach: Replace file_get_contents('php://input') with a literal
$content = file_get_contents('${filePath.replace(/\\/g, '/')}');
if ($postData) {
    $content = str_replace("file_get_contents('php://input')", "'$postData'", $content);
}
// Strip include 'cors.php' as it might fail in CLI if not found
$content = str_replace("include_once \\"cors.php\\";", "", $content);
$content = str_replace("include_once 'cors.php';", "", $content);

eval('?>' . $content);
`;

    fs.writeFileSync(wrapperPath, wrapperContent);
    try {
        const output = execSync(`php ${wrapperPath}`, { encoding: 'utf8' });
        return JSON.parse(output);
    } catch (e) {
        console.error(`Error running ${file}:`, e.message);
        return { error: e.message, output: e.stdout };
    } finally {
        if (fs.existsSync(wrapperPath)) fs.unlinkSync(wrapperPath);
    }
}

console.log("--- TESTING getEBCs.php ---");

// Test 1: GET Area and Grade
const res1 = runPhp('getEBCs.php', { area: 'matematicas', grado: 'primero' });
console.log("Test 1 (GET Matemáticas Primero):", res1.status === 'success' && res1.count > 0 ? "PASSED ✅" : "FAILED ❌");
if (res1.data) console.log(`   Found ${res1.count} items. Sample ID: ${res1.data[0].id}`);

// Test 2: POST Area and Grade
const res2 = runPhp('getEBCs.php', {}, { area: 'lenguaje', grado: 'segundo' });
console.log("Test 2 (POST Lenguaje Segundo):", res2.status === 'success' && res2.count > 0 ? "PASSED ✅" : "FAILED ❌");
if (res2.data) console.log(`   Found ${res2.count} items. Sample ID: ${res2.data[0].id}`);

// Test 3: GET Area (All grades)
const res3 = runPhp('getEBCs.php', { area: 'ciencias_naturales' });
console.log("Test 3 (GET Ciencias Naturales All):", res3.status === 'success' && res3.count > 0 ? "PASSED ✅" : "FAILED ❌");

console.log("\n--- TESTING getDBAs.php ---");

// Test 4: GET Area and Grade
const res4 = runPhp('getDBAs.php', { area: 'matematicas', grado: 'quinto' });
console.log("Test 4 (GET Matemáticas Quinto):", res4.status === 'success' && res4.count > 0 ? "PASSED ✅" : "FAILED ❌");

// Test 5: POST Area and Grade
const res5 = runPhp('getDBAs.php', {}, { area: 'ingles', grado: 'sexto' });
console.log("Test 5 (POST Inglés Sexto):", res5.status === 'success' && res5.count > 0 ? "PASSED ✅" : "FAILED ❌");

console.log("\nSummary Complete.");
