<?php
/**
 * save_acta_padres.php - Guardado de actas de reunión de padres de familia en Google Sheets
 *
 * Recibe los datos del acta de reunión de padres y los guarda en Google Sheets.
 * Estructura del row:
 * [timestamp, id, docente, institucion, fecha, hora_inicio, hora_fin, lugar,
 *  tipo, grado, grupo, tema_principal, participantes_json, temas_agenda_json,
 *  acuerdos, compromisos_json, observaciones, prox_fecha_reunion,
 *  acta_leida_aprobada, firmas_json, fotos_urls, firmas_fotos_urls, created_at]
 *
 * Basado en save_acta_izada.php - usa servicio de Google Sheets con service account.
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
        throw new Exception('JSON inválido: ' . json_last_error_msg());
    }

    if (!isset($data['datos']) || !is_array($data['datos'])) {
        throw new Exception('Datos incompletos. Se espera el campo "datos" como arreglo.');
    }

    if (!isset($data['spreadsheetId'])) {
        throw new Exception('Se requiere el spreadsheetId.');
    }

    $spreadsheetId = $data['spreadsheetId'];
    $worksheetTitle = $data['worksheetTitle'] ?? 'ActaPadres';
    $range = $worksheetTitle . '!A1:Z20000';

    // Inicializar cliente de Google
    $client = new Client();
    $client->setApplicationName('Acta de Reunión de Padres');
    $client->setScopes([Sheets::SPREADSHEETS]);

    if (!file_exists(SERVICE_ACCOUNT_KEY_FILE)) {
        throw new Exception('Archivo de credenciales de servicio no encontrado.');
    }
    $client->setAuthConfig(SERVICE_ACCOUNT_KEY_FILE);
    $service = new Sheets($client);

    $nuevasActas = $data['datos'];
    $totalActas = count($nuevasActas);

    if ($totalActas === 0) {
        throw new Exception('No hay actas para registrar.');
    }

    // Campos esperados por fila
    $expectedFields = 21;

    foreach ($nuevasActas as $index => $acta) {
        if (!is_array($acta) || count($acta) < 10) {
            throw new Exception("Acta en índice $index tiene formato inválido.");
        }
    }

    error_log("DEBUG save_acta_padres: Payload válido con $totalActas actas.");

    // Función para generar clave única de acta
    // Clave: docente|fecha|grado|tipo
    function generateActaPadresKey($row) {
        $docente = isset($row[2]) ? trim((string)$row[2]) : '';
        $fecha = isset($row[4]) ? trim((string)$row[4]) : '';
        $grados = isset($row[9]) ? trim((string)$row[9]) : '';
        $tipo = isset($row[8]) ? trim((string)$row[8]) : '';
        return strtolower($docente . '|' . $fecha . '|' . $grados . '|' . $tipo);
    }

    // Leer la hoja completa
    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    $allValues = $response->getValues() ?: [];

    // Preservar encabezado si existe
    $hasHeader = count($allValues) > 0 && isset($allValues[0][0]) && strtolower(trim($allValues[0][0])) !== 'timestamp';
    $headerRow = $hasHeader ? array_shift($allValues) : null;

    // Si no hay encabezado, crear uno
    if (!$hasHeader) {
        $headerRow = [
            'Timestamp',
            'ID',
            'Docente',
            'Institución',
            'Fecha',
            'Hora inicio',
            'Hora fin',
            'Lugar',
            'Tipo',
            'Grado',
            'Grupo',
            'Tema principal',
            'Participantes (JSON)',
            'Temas Agenda (JSON)',
            'Acuerdos',
            'Compromisos (JSON)',
            'Observaciones',
            'Próxima fecha reunión',
            'Acta leída y aprobada',
            'Firmas (JSON)',
            'Fotos URLs',
            'Firmas Fotos URLs',
            'Creado',
        ];
    }

    // Construir mapa de registros existentes
    $existingRecords = [];
    foreach ($allValues as $rowIndex => $row) {
        if (count($row) >= 10) {
            $key = generateActaPadresKey($row);
            $existingRecords[$key] = $row;
        }
    }

    error_log("DEBUG save_acta_padres: Registros existentes: " . count($existingRecords));

    // Procesar los nuevos registros
    foreach ($nuevasActas as $acta) {
        $key = generateActaPadresKey($acta);
        $existingRecords[$key] = $acta;
        error_log("DEBUG save_acta_padres: Guardando Acta key=$key");
    }

    // Reconstruir array final
    $finalRows = [];
    if ($headerRow) {
        $finalRows[] = $headerRow;
    }
    foreach ($existingRecords as $key => $row) {
        $finalRows[] = $row;
    }

    $totalFinal = count($finalRows);
    $totalRegistros = $totalFinal - 1;

    // Escribir todos los datos de vuelta
    $updateRange = $worksheetTitle . '!A1:Z' . $totalFinal;
    $body = new ValueRange(['values' => $finalRows]);
    $params = ['valueInputOption' => 'RAW'];

    $service->spreadsheets_values->update($spreadsheetId, $updateRange, $body, $params);

    echo json_encode([
        'success' => true,
        'message' => "Se procesaron $totalRegistros acta(s) de padres exitosamente.",
        'total' => $totalRegistros,
        'spreadsheetId' => $spreadsheetId,
        'worksheetTitle' => $worksheetTitle,
        'mode' => 'replace'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("ERROR save_acta_padres: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>