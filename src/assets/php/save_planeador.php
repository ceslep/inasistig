<?php
/**
 * save_planeador.php - Guardado de planeaciones en Google Sheets
 * 
 * Recibe los datos de planeación de clases y los guarda en Google Sheets.
 * Estructura de datos esperada:
 * [docente, institution, campus, grade, subject, period, dba[], standard, competency,
 *  has_piar, piar_description, exploration, structuring, practice, transfer,
 *  assessment_moment, eval_criteria, eval_evidence, eval_type, resources]
 */

require __DIR__ . '/vendor/autoload.php';

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;

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
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON inválido.');
    }

    if (!isset($data['datos']) || !is_array($data['datos'])) {
        throw new Exception('Datos incompletos. Se espera el campo "datos" como arreglo.');
    }

    if (!isset($data['spreadsheetId'])) {
        throw new Exception('Se requiere el spreadsheetId.');
    }

    $spreadsheetId = $data['spreadsheetId'];
    $worksheetTitle = $data['worksheetTitle'] ?? 'Planeaciones';
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

    $planeaciones = $data['datos'];

    if (count($planeaciones) === 0) {
        throw new Exception('No hay planeaciones para registrar.');
    }

    // Encabezados de columnas
    $headers = [
        'Timestamp',
        'Docente',
        'Institución',
        'Sede/Jornada',
        'Grado',
        'Asignatura',
        'Período',
        'DBA',
        'Estándar',
        'Competencia',
        'Tiene PIAR',
        'Descripción PIAR',
        'Exploración',
        'Estructuración',
        'Práctica',
        'Transferencia',
        'Valoración',
        'Criterios de Evaluación',
        'Evidencias',
        'Tipo de Evaluación',
        'Recursos'
    ];

    // Leer datos existentes
    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    $allValues = $response->getValues() ?: [];

    // Preservar encabezado si existe
    $hasHeader = count($allValues) > 0 && isset($allValues[0][0]) && strtolower(trim($allValues[0][0])) === 'timestamp';
    $existingData = $hasHeader ? array_slice($allValues, 1) : $allValues;

    // Convertir planeaciones a filas
    $newRows = [];
    $timestamp = date('Y-m-d H:i:s');

    foreach ($planeaciones as $planeacion) {
        $row = [
            $timestamp,
            isset($planeacion['docente']) ? $planeacion['docente'] : '',
            isset($planeacion['institution']) ? $planeacion['institution'] : '',
            isset($planeacion['campus']) ? $planeacion['campus'] : '',
            isset($planeacion['grade']) ? $planeacion['grade'] : '',
            isset($planeacion['subject']) ? $planeacion['subject'] : '',
            isset($planeacion['period']) ? $planeacion['period'] : '',
            isset($planeacion['dba']) ? implode('; ', $planeacion['dba']) : '',
            isset($planeacion['standard']) ? $planeacion['standard'] : '',
            isset($planeacion['competency']) ? $planeacion['competency'] : '',
            isset($planeacion['has_piar']) ? ($planeacion['has_piar'] ? 'Sí' : 'No') : 'No',
            isset($planeacion['piar_description']) ? $planeacion['piar_description'] : '',
            isset($planeacion['exploration']) ? $planeacion['exploration'] : '',
            isset($planeacion['structuring']) ? $planeacion['structuring'] : '',
            isset($planeacion['practice']) ? $planeacion['practice'] : '',
            isset($planeacion['transfer']) ? $planeacion['transfer'] : '',
            isset($planeacion['assessment_moment']) ? $planeacion['assessment_moment'] : '',
            isset($planeacion['eval_criteria']) ? $planeacion['eval_criteria'] : '',
            isset($planeacion['eval_evidence']) ? $planeacion['eval_evidence'] : '',
            isset($planeacion['eval_type']) ? $planeacion['eval_type'] : '',
            isset($planeacion['resources']) ? $planeacion['resources'] : '',
        ];
        $newRows[] = $row;
    }

    // Combinar datos existentes con nuevos
    $finalRows = array_merge($existingData, $newRows);

    // Agregar encabezado al inicio
    if ($hasHeader) {
        array_unshift($finalRows, $headers);
    } else {
        array_unshift($finalRows, $headers);
    }

    // Escribir a Google Sheets
    $totalRows = count($finalRows);
    $updateRange = $worksheetTitle . '!A1:Z' . $totalRows;
    $body = new ValueRange(['values' => $finalRows]);
    $params = ['valueInputOption' => 'RAW'];

    $service->spreadsheets_values->update($spreadsheetId, $updateRange, $body, $params);

    echo json_encode([
        'success' => true,
        'message' => 'Planeación guardada exitosamente.',
        'total' => count($newRows),
        'spreadsheetId' => $spreadsheetId,
        'worksheetTitle' => $worksheetTitle
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
