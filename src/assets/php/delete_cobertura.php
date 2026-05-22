<?php
/**
 * delete_cobertura.php - Eliminar una cobertura específica
 *
 * Elimina una fila de Google Sheets basándose en fecha + hora + docente_cubre
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

    $spreadsheetId = $data['spreadsheetId'] ?? '1N-94FYW5kvGmOcJ4CCqQRWC71guFLxlXltlM7GvDQDw';
    $worksheetTitle = $data['worksheetTitle'] ?? 'historial';
    $fecha = $data['fecha'] ?? '';
    $hora = $data['hora'] ?? '';
    $docenteCubre = $data['docente_cubre'] ?? '';

    if (!$fecha || $hora === '' || !$docenteCubre) {
        throw new Exception('Se requiere fecha, hora y docente_cubre.');
    }

    $client = new Client();
    $client->setApplicationName('Coberturas');
    $client->setScopes([Sheets::SPREADSHEETS]);

    if (!file_exists(SERVICE_ACCOUNT_KEY_FILE)) {
        throw new Exception('Archivo de credenciales no encontrado.');
    }
    $client->setAuthConfig(SERVICE_ACCOUNT_KEY_FILE);
    $service = new Sheets($client);

    $range = $worksheetTitle . '!A1:Z5000';
    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    $allValues = $response->getValues() ?: [];

    $rowToDelete = -1;
    foreach ($allValues as $idx => $row) {
        if (count($row) >= 6 &&
            trim((string) $row[0]) === trim($fecha) &&
            trim((string) $row[2]) === trim((string) $hora) &&
            trim((string) $row[5]) === trim($docenteCubre)) {
            $rowToDelete = $idx + 1;
            break;
        }
    }

    if ($rowToDelete === -1) {
        throw new Exception('Registro no encontrado.');
    }

    $deleteRange = $worksheetTitle . '!A' . $rowToDelete . ':H' . $rowToDelete;
    $body = new ValueRange(['values' => [[]]]);
    $params = ['valueInputOption' => 'RAW'];
    $service->spreadsheets_values->update($spreadsheetId, $deleteRange, $body, $params);

    echo json_encode([
        'success' => true,
        'message' => 'Cobertura eliminada.'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}