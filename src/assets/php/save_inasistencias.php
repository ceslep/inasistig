<?php
/**
 * save_inasistencias.php - Guardado de inasistencias en Google Sheets
 * 
 * Recibe los datos de inasistencias y los guarda en Google Sheets.
 * Espera un arreglo de valores con la estructura:
 * [timestamp, docente, fecha, horas, asignatura, tipo_registro, grupo, observaciones, estudiante, observaciones_estudiante]
 */

require __DIR__ . '/vendor/autoload.php';

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;

// Configuración de archivos
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

    // Leer datos del cuerpo de la solicitud
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON inválido.');
    }

    if (!isset($data['inasistencias']) || !is_array($data['inasistencias'])) {
        throw new Exception('Datos incompletos. Se espera el campo "inasistencias" como arreglo.');
    }

    if (!isset($data['spreadsheetId'])) {
        throw new Exception('Se requiere el spreadsheetId.');
    }

    // Parámetros dinámicos
    $spreadsheetId = $data['spreadsheetId'];
    $worksheetTitle = $data['worksheetTitle'] ?? date('Y-m');
    $range = $worksheetTitle . '!A1:Z1000'; // Rango amplio

    // Inicializar cliente de Google
    $client = new Client();
    $client->setApplicationName('Control de Inasistencias');
    $client->setScopes([Sheets::SPREADSHEETS]);

    if (!file_exists(SERVICE_ACCOUNT_KEY_FILE)) {
        throw new Exception('Archivo de credenciales no encontrado.');
    }
    $client->setAuthConfig(SERVICE_ACCOUNT_KEY_FILE);
    $service = new Sheets($client);

    $inasistenciasData = $data['inasistencias'];
    $totalInasistencias = count($inasistenciasData);

    if ($totalInasistencias === 0) {
        throw new Exception('No hay inasistencias para registrar.');
    }

    // Validar que cada inasistencia tenga los campos requeridos
    $expectedFields = 9; // timestamp, docente, fecha, horas, asignatura, tipo_registro, grupo, estudiante, observaciones

    foreach ($inasistenciasData as $index => $inasistencia) {
        if (!is_array($inasistencia) || count($inasistencia) !== $expectedFields) {
            throw new Exception("Inasistencia en índice $index debe tener exactamente $expectedFields campos.");
        }

        // Validar campos clave (docente, fecha, horas, estudiante deben estar presentes)
        foreach ([1, 2, 3, 7] as $fieldIndex) { // docente, fecha, horas, estudiante
            if (!isset($inasistencia[$fieldIndex]) || trim((string) $inasistencia[$fieldIndex]) === '') {
                throw new Exception("Campo requerido en posición $fieldIndex está vacío en inasistencia $index.");
            }
        }
    }

    error_log("DEBUG: Payload válido con $totalInasistencias inasistencias.");
    
    // Log detallado de los datos recibidos
    foreach ($inasistenciasData as $index => $inasistencia) {
        error_log("DEBUG: Inasistencia $index - Estudiante: " . ($inasistencia[8] ?? 'NO DEFINIDO'));
        error_log("DEBUG: Inasistencia $index - Datos completos: " . json_encode($inasistencia));
    }

    // Preparar todos los datos para insertar
    $allRowsToInsert = [];
    $currentTimestamp = date('Y-m-d H:i:s');

    foreach ($inasistenciasData as $index => $inasistencia) {
        // Asegurar que el timestamp esté presente (índice 0)
        if (!isset($inasistencia[0]) || empty($inasistencia[0])) {
            $inasistencia[0] = $currentTimestamp;
        }
        
        $allRowsToInsert[] = $inasistencia;
    }

    // Leer la hoja para encontrar la siguiente fila vacía
    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    $allValues = $response->getValues() ?: [];

    $nextRow = count($allValues) + 1; // Siguiente fila vacía
    if ($nextRow < 2)
        $nextRow = 2; // Saltar encabezado si la hoja está vacía

    // Insertar todas las inasistencias en batch
    $insertRange = $worksheetTitle . "!A{$nextRow}:I" . ($nextRow + $totalInasistencias - 1);
    $body = new ValueRange(['values' => $allRowsToInsert]);
    $params = ['valueInputOption' => 'RAW'];

    $service->spreadsheets_values->update($spreadsheetId, $insertRange, $body, $params);

    echo json_encode([
        'success' => true,
        'message' => "Se registraron exitosamente $totalInasistencias inasistencia(s).",
        'total' => $totalInasistencias,
        'rows' => [
            'start' => $nextRow,
            'end' => $nextRow + $totalInasistencias - 1
        ],
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