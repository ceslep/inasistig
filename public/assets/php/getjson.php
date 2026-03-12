<?php
/**
 * getjson.php - Serve JSON file for temas del docente
 * 
 * URL: https://app.iedeoccidente.com/ig/getjson.php?file={filename}
 */

include_once("cors.php");

// Directorio donde están los archivos JSON
$jsonDir = __DIR__ . '/arjson/';

// Obtener nombre de archivo de la query string
$filename = isset($_GET['file']) ? $_GET['file'] : '';

if (empty($filename)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Parámetro file es requerido']);
    exit();
}

// Sanitizar nombre de archivo
$filename = basename($filename);
if (!preg_match('/^[a-zA-Z0-9_\-\.]+\.json$/', $filename)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Nombre de archivo inválido']);
    exit();
}

$filepath = $jsonDir . $filename;

if (!file_exists($filepath)) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Archivo no encontrado']);
    exit();
}

// Servir el archivo JSON directamente
$content = file_get_contents($filepath);
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
echo $content;
