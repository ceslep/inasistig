<?php
/**
 * API Router - DBA and EBC Menu
 * Returns DBA or EBC data based on materia and grado parameters
 * 
 * Usage: 
 *   POST /api/dba_ebc.php with { tipo: "DBA", materia: "matematicas", grado: "noveno" }
 *   GET  /api/dba_ebc.php?tipo=EBC&materia=matematicas&grado=9
 */

include_once "cors.php";
header('Content-Type: application/json; charset=utf-8');

// ============================================
// Helper Functions
// ============================================

function clean_string($str) {
    if (!$str) return null;
    $str = strtolower(trim($str));
    $search = ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'];
    $replace = ['a', 'e', 'i', 'o', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'n'];
    $str = str_replace($search, $replace, $str);
    return preg_replace('/[^a-z0-9_ ]/', '', $str);
}

function normalize_grado($grado) {
    $map = [
        'preescolar' => 'preescolar', 'transicion' => 'preescolar', '0' => 'preescolar',
        'primero' => 'primero', '1' => 'primero', 'uno' => 'primero',
        'segundo' => 'segundo', '2' => 'segundo', 'dos' => 'segundo',
        'tercero' => 'tercero', '3' => 'tercero', 'tres' => 'tercero',
        'cuarto' => 'cuarto', '4' => 'cuarto', 'cuatro' => 'cuarto',
        'quinto' => 'quinto', '5' => 'quinto', 'cinco' => 'quinto',
        'sexto' => 'sexto', '6' => 'sexto', 'seis' => 'sexto',
        'septimo' => 'septimo', '7' => 'septimo', 'siete' => 'septimo',
        'octavo' => 'octavo', '8' => 'octavo', 'ocho' => 'octavo',
        'noveno' => 'noveno', '9' => 'noveno', 'nueve' => 'noveno',
        'decimo' => 'decimo', '10' => 'decimo', 'diez' => 'decimo',
        'once' => 'once', '11' => 'once', 'onceavo' => 'once',
    ];
    return $map[$grado] ?? $grado;
}

function get_file_prefix($materia) {
    $map = [
        'matematicas' => 'matematicas',
        'matematica' => 'matematicas',
        'math' => 'matematicas',
        'lenguaje' => 'lenguaje',
        'lengua castellana' => 'lenguaje',
        'castellano' => 'lenguaje',
        'espanol' => 'lenguaje',
        'ciencias naturales' => 'ciencias',
        'naturales' => 'ciencias',
        'ciencias' => 'ciencias',
        'ciencias_naturales' => 'ciencias',
        'sociales' => 'sociales',
        'ciencias sociales' => 'sociales',
        'historia' => 'sociales',
        'ciencias_sociales' => 'sociales',
        'ingles' => 'ingles',
        'english' => 'ingles',
        'educacion fisica' => 'edu_fisica',
        'fisica' => 'edu_fisica',
        'deporte' => 'edu_fisica',
        'educacion artistica' => 'artistica',
        'artistica' => 'artistica',
        'arte' => 'artistica',
        'etica' => 'religion',
        'religion' => 'religion',
        'etica y valores' => 'religion',
        'educacion religiosa' => 'religion',
        'tecnologia' => 'tecnologia',
        'informatica' => 'tecnologia',
        'sistemas' => 'tecnologia',
        'tecnologia e informatica' => 'tecnologia',
        'convivencia' => 'convivencia',
        'ciudadana' => 'convivencia',
        'formacion ciudadana' => 'convivencia',
    ];
    return $map[$materia] ?? $materia;
}

// ============================================
// Main Logic
// ============================================

// Get parameters from POST or GET
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$postTipo = $input['tipo'] ?? null;
$postMateria = $input['materia'] ?? null;
$postGrado = $input['grado'] ?? null;

$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : ($postTipo ?: null);
$materia = isset($_GET['materia']) ? $_GET['materia'] : ($postMateria ?: null);
$grado = isset($_GET['grado']) ? $_GET['grado'] : ($postGrado ?: null);

// Normalize inputs
$tipo = clean_string($tipo) ?? 'dba';
$materia = clean_string($materia);
$grado = normalize_grado(clean_string($grado));

// Validate materia
if (!$materia) {
    echo json_encode([
        "status" => "error",
        "message" => "Parámetro 'materia' es requerido"
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Validate grado
if (!$grado) {
    echo json_encode([
        "status" => "error",
        "message" => "Parámetro 'grado' es requerido"
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Determine file path
$prefix = get_file_prefix($materia);
$tipoUpper = strtoupper($tipo);

$allowedTypes = ['DBA', 'EBC'];
if (!in_array($tipoUpper, $allowedTypes)) {
    echo json_encode([
        "status" => "error",
        "message" => "Tipo inválido. Use 'DBA' o 'EBC'"
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$filename = strtolower($tipo) . '_' . $prefix . '.php';
$filepath = __DIR__ . '/' . $filename;

// Check if file exists
if (!file_exists($filepath)) {
    $availableFiles = array_merge(
        glob(__DIR__ . '/dba_*.php'),
        glob(__DIR__ . '/ebc_*.php')
    );
    $availableFiles = array_map('basename', $availableFiles);
    
    echo json_encode([
        "status" => "error",
        "message" => "Archivo no encontrado: $filename",
        "requested" => ["tipo" => $tipoUpper, "materia" => $materia, "grado" => $grado],
        "available_files" => $availableFiles,
        "hint" => "Verifique que el archivo existe para la materia: $materia"
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Include the file and capture output
ob_start();
include $filepath;
$file_content = ob_get_clean();

// Parse the JSON output from included file
$data = json_decode($file_content, true);

if (!$data) {
    echo json_encode([
        "status" => "error",
        "message" => "Error al parsear el archivo: $filename",
        "raw_content" => substr($file_content, 0, 500)
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Filter by grado if data is array
if (isset($data['data']) && is_array($data['data'])) {
    $filtered = array_filter($data['data'], function($item) use ($grado) {
        $itemGrado = isset($item['grado']) ? normalize_grado($item['grado']) : '';
        return $itemGrado === $grado;
    });
    $data['data'] = array_values($filtered);
    $data['filtered_by'] = [
        "tipo" => $tipoUpper,
        "materia" => $materia,
        "grado" => $grado
    ];
}

// Add request metadata
if (!isset($data['metadata'])) {
    $data['metadata'] = [];
}
$data['metadata']['request'] = [
    "tipo" => $tipoUpper,
    "materia" => $materia,
    "grado" => $grado,
    "source_file" => $filename,
    "timestamp" => date("Y-m-d H:i:s")
];

// Ensure status
if (!isset($data['status'])) {
    $data['status'] = 'success';
}

// Return response
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
