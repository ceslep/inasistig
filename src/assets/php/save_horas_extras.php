<?php
/**
 * save_horas_extras.php - Guardado de horas extras en Google Sheets
 *
 * Campos:
 *   fecha, DIA, MES, AÑO, docente, HORA DE ENTRADA, HORA DE SALIDA,
 *   GRADO ATENDIDO, ASIGNATURA, ACTIVIDAD, HORAS EXTRAS,
 *   FIRMA DEL DOCENTE, OBSERVACIONES O NOVEDADES
 */

require __DIR__ . '/vendor/autoload.php';

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;

const SERVICE_ACCOUNT_KEY_FILE = __DIR__ . '/assets/serviceaccount.json';

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

    if (!isset($data['spreadsheetId'])) {
        throw new Exception('Se requiere el spreadsheetId.');
    }

    $spreadsheetId = $data['spreadsheetId'];
    $worksheetTitle = $data['worksheetTitle'] ?? 'extras';
    $range = $worksheetTitle . '!A1:M5000';

    $client = new Client();
    $client->setApplicationName('Horas Extras');
    $client->setScopes([Sheets::SPREADSHEETS]);

    if (!file_exists(SERVICE_ACCOUNT_KEY_FILE)) {
        throw new Exception('Archivo de credenciales no encontrado.');
    }
    $client->setAuthConfig(SERVICE_ACCOUNT_KEY_FILE);
    $service = new Sheets($client);

    if (!isset($data['values']) || !is_array($data['values'])) {
        throw new Exception('Se requieren los valores a guardar.');
    }

    $values = $data['values'];
    $rowIndex = $data['rowIndex'] ?? null;

    $headers = [
        'FECHA', 'DIA', 'MES', 'AÑO', 'DOCENTE', 'HORA DE ENTRADA',
        'HORA DE SALIDA', 'GRADO ATENDIDO', 'ASIGNATURA', 'ACTIVIDAD',
        'HORAS EXTRAS', 'FIRMA DEL DOCENTE', 'OBSERVACIONES O NOVEDADES'
    ];

    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    $allValues = $response->getValues() ?: [];
    $nextRow = count($allValues) + 1;

    if ($nextRow <= 1) {
        $headerRange = $worksheetTitle . '!A1:M1';
        $headerBody = new ValueRange(['values' => [$headers]]);
        $service->spreadsheets_values->update($spreadsheetId, $headerRange, $headerBody, ['valueInputOption' => 'RAW']);
        $nextRow = 2;
    }

    $lastCol = 'M';
    if ($rowIndex !== null) {
        $insertRange = $worksheetTitle . "!A{$rowIndex}:{$lastCol}{$rowIndex}";
        $body = new ValueRange(['values' => [$values]]);
        $params = ['valueInputOption' => 'RAW'];
        $service->spreadsheets_values->update($spreadsheetId, $insertRange, $body, $params);
        echo json_encode([
            'success' => true,
            'message' => 'Registro actualizado exitosamente.',
            'rowIndex' => $rowIndex,
            'updated' => true
        ]);
    } else {
        $insertRange = $worksheetTitle . "!A{$nextRow}:{$lastCol}{$nextRow}";
        $body = new ValueRange(['values' => [$values]]);
        $params = ['valueInputOption' => 'RAW'];
        $service->spreadsheets_values->update($spreadsheetId, $insertRange, $body, $params);
        echo json_encode([
            'success' => true,
            'message' => 'Registro guardado exitosamente.',
            'rowIndex' => $nextRow,
            'updated' => false
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>