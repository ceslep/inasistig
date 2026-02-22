<?php
/**
 * get_planeador.php - Obtención de planeaciones desde Google Sheets
 * 
 * Permite obtener las planeaciones guardadas con filtros opcionales.
 * Filtros soportados: docente, grado, materia
 */

require __DIR__ . '/vendor/autoload.php';

use Google\Client;
use Google\Service\Sheets;

// Configuración
const SERVICE_ACCOUNT_KEY_FILE = __DIR__ . '/assets/serviceaccount.json';

// CORS y Cabeceras
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido. Use POST.');
    }

    $input = file_get_contents('php://input');
    $filtros = json_decode($input, true) ?: [];

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON inválido.');
    }

    // Spreadsheet ID - usar variable de entorno o valor por defecto
    $spreadsheetId = '1 EXAMPLE ID'; // TODO: Reemplazar con ID real
    $worksheetTitle = 'Planeaciones';
    $range = $worksheetTitle . '!A1:Z1000';

    // Inicializar Google Client
    $client = new Client();
    $client->setApplicationName('Planeador de Clases');
    $client->setScopes([Sheets::SPREADSHEETS]);

    if (!file_exists(SERVICE_ACCOUNT_KEY_FILE)) {
        throw new Exception('Archivo de credenciales no encontrado.');
    }
    $client->setAuthConfig(SERVICE_ACCOUNT_KEY_FILE);
    $service = new Sheets($client);

    // Obtener datos
    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    $allValues = $response->getValues() ?: [];

    if (count($allValues) === 0) {
        echo json_encode([
            'success' => true,
            'data' => []
        ]);
        exit();
    }

    // Verificar si hay encabezado
    $hasHeader = isset($allValues[0][0]) && strtolower(trim($allValues[0][0])) === 'timestamp';
    $startRow = $hasHeader ? 1 : 0;

    if ($startRow >= count($allValues)) {
        echo json_encode([
            'success' => true,
            'data' => []
        ]);
        exit();
    }

    // Mapear encabezados
    $headers = $hasHeader ? $allValues[0] : [
        'Timestamp', 'Docente', 'Institución', 'Sede/Jornada', 'Grado',
        'Asignatura', 'Período', 'DBA', 'Estándar', 'Competencia',
        'Tiene PIAR', 'Descripción PIAR', 'Exploración', 'Estructuración',
        'Práctica', 'Transferencia', 'Valoración', 'Criterios de Evaluación',
        'Evidencias', 'Tipo de Evaluación', 'Recursos'
    ];

    // Convertir filas a objetos
    $planeaciones = [];
    for ($i = $startRow; $i < count($allValues); $i++) {
        $row = $allValues[$i];
        $planeacion = [
            'timestamp' => isset($row[0]) ? $row[0] : '',
            'docente' => isset($row[1]) ? $row[1] : '',
            'institution' => isset($row[2]) ? $row[2] : '',
            'campus' => isset($row[3]) ? $row[3] : '',
            'grade' => isset($row[4]) ? $row[4] : '',
            'subject' => isset($row[5]) ? $row[5] : '',
            'period' => isset($row[6]) ? $row[6] : '',
            'dba' => isset($row[7]) ? explode('; ', $row[7]) : [],
            'standard' => isset($row[8]) ? $row[8] : '',
            'competency' => isset($row[9]) ? $row[9] : '',
            'has_piar' => isset($row[10]) && $row[10] === 'Sí',
            'piar_description' => isset($row[11]) ? $row[11] : '',
            'exploration' => isset($row[12]) ? $row[12] : '',
            'structuring' => isset($row[13]) ? $row[13] : '',
            'practice' => isset($row[14]) ? $row[14] : '',
            'transfer' => isset($row[15]) ? $row[15] : '',
            'assessment_moment' => isset($row[16]) ? $row[16] : '',
            'eval_criteria' => isset($row[17]) ? $row[17] : '',
            'eval_evidence' => isset($row[18]) ? $row[18] : '',
            'eval_type' => isset($row[19]) ? $row[19] : '',
            'resources' => isset($row[20]) ? $row[20] : '',
        ];
        $planeaciones[] = $planeacion;
    }

    // Aplicar filtros
    if (!empty($filtros['docente'])) {
        $planeaciones = array_filter($planeaciones, fn($p) => 
            stripos($p['docente'], $filtros['docente']) !== false
        );
    }

    if (!empty($filtros['grado'])) {
        $planeaciones = array_filter($planeaciones, fn($p) => 
            stripos($p['grade'], $filtros['grado']) !== false
        );
    }

    if (!empty($filtros['materia'])) {
        $planeaciones = array_filter($planeaciones, fn($p) => 
            stripos($p['subject'], $filtros['materia']) !== false
        );
    }

    // Reindexar array
    $planeaciones = array_values($planeaciones);

    echo json_encode([
        'success' => true,
        'data' => $planeaciones,
        'total' => count($planeaciones)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
