<?php
/**
 * arjson.php - Guardado y obtención de temas JSON del docente
 * 
 * Maneja dos operaciones:
 * - POST: Recibe JSON y lo guarda en archivo
 * - GET: Devuelve el contenido del archivo JSON
 * 
 * URL: https://app.iedeoccidente.com/ig/arjson.php
 */

include_once("cors.php");

// Directorio donde se guardarán los archivos JSON
$uploadDir = __DIR__ . '/arjson/';

// Crear directorio si no existe
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // === GUARDAR JSON ===
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON inválido: ' . json_last_error_msg());
        }

        if (!isset($data['filename']) || empty($data['filename'])) {
            throw new Exception('Se requiere el nombre del archivo (filename)');
        }

        if (!isset($data['data']) || !is_array($data['data'])) {
            throw new Exception('Se requieren los datos (data) como array');
        }

        // Sanitizar nombre de archivo
        $filename = basename($data['filename']);
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);

        // Asegurar extensión .json
        if (pathinfo($filename, PATHINFO_EXTENSION) !== 'json') {
            $filename .= '.json';
        }

        $filepath = $uploadDir . $filename;

        // Guardar archivo
        $jsonContent = json_encode($data['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $result = file_put_contents($filepath, $jsonContent);

        if ($result === false) {
            throw new Exception('Error al guardar el archivo');
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Archivo guardado correctamente',
            'filename' => $filename
        ]);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // === OBTENER JSON ===
        // Obtener nombre de archivo de la URL
        $requestUri = $_SERVER['REQUEST_URI'];
        $path = parse_url($requestUri, PHP_URL_PATH);
        
        // Extraer nombre de archivo de la ruta
        $filename = basename($path);
        
        if (empty($filename) || $filename === 'arjson.php') {
            // Listar archivos disponibles
            $files = glob($uploadDir . '*.json');
            $fileList = array_map(function($f) {
                return basename($f);
            }, $files);
            
            echo json_encode([
                'status' => 'success',
                'files' => $fileList
            ]);
            exit();
        }

        // Sanitizar nombre de archivo
        $filename = basename($filename);
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+\.json$/', $filename)) {
            throw new Exception('Nombre de archivo inválido');
        }

        $filepath = $uploadDir . $filename;

        if (!file_exists($filepath)) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Archivo no encontrado'
            ]);
            exit();
        }

        $content = file_get_contents($filepath);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Error al leer el archivo JSON');
        }

        echo json_encode($data);

    } else {
        throw new Exception('Método no permitido. Use GET o POST');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
