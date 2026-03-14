<?php
/**
 * save_planeador.php - Guardado de planeaciones en Google Sheets
 * 
 * Recibe los datos de planeación de clases y los guarda en Google Sheets.
 * Estructura: 49 columnas con formato JSON para arrays.
 */

include_once("cors.php");

require __DIR__ . '/vendor/autoload.php';

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;

const SERVICE_ACCOUNT_KEY_FILE = __DIR__ . '/assets/serviceaccount.json';

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
    $range = $worksheetTitle . '!A1:AW1000';

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

    // 49 Encabezados de columnas
    $headers = [
        'id',
        'fecha_creacion',
        'docente',
        'institution',
        'campus',
        'grade',
        'subject',
        'period',
        'dba',
        'standard',
        'dba_manual',
        'competency',
        'has_piar',
        'piar_description',
        'learning_objectives',
        'competencias',
        'indicadores_logro',
        'exploration',
        'exploration_activities',
        'tiempo_exploracion',
        'structuring',
        'structuring_activities',
        'tiempo_estructuracion',
        'practice',
        'practice_activities',
        'tiempo_practica',
        'transfer',
        'transfer_activities',
        'tiempo_transferencia',
        'assessment_moment',
        'assessment_activities',
        'tiempo_valoracion',
        'eval_type',
        'eval_modalidades',
        'eval_instrumentos',
        'eval_criterios',
        'eval_evidencias',
        'eval_criteria',
        'eval_evidence',
        'eval_ponderacion_conceptos',
        'eval_ponderacion_procedimientos',
        'eval_ponderacion_actitudes',
        'eval_descripcion_auto',
        'resources',
        'planeacion_tipo',
        'periodo_academico',
        'fecha_inicio',
        'fecha_fin',
        'firma_docente',
    ];

    // Leer datos existentes
    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    $allValues = $response->getValues() ?: [];

    // Preservar encabezado si existe
    $hasHeader = count($allValues) > 0 && isset($allValues[0][0]) && strtolower(trim($allValues[0][0])) === 'id';
    $existingData = $hasHeader ? array_slice($allValues, 1) : $allValues;

    // Convertir planeaciones a filas
    $newRows = [];

    foreach ($planeaciones as $planeacion) {
        $row = [
            // A: id
            isset($planeacion['id']) ? $planeacion['id'] : uniqid('PL_'),
            // B: fecha_creacion
            isset($planeacion['fecha_creacion']) ? $planeacion['fecha_creacion'] : date('Y-m-d'),
            // C: docente
            isset($planeacion['docente']) ? $planeacion['docente'] : '',
            // D: institution
            isset($planeacion['institution']) ? $planeacion['institution'] : '',
            // E: campus
            isset($planeacion['campus']) ? $planeacion['campus'] : '',
            // F: grade
            isset($planeacion['grade']) ? $planeacion['grade'] : '',
            // G: subject
            isset($planeacion['subject']) ? $planeacion['subject'] : '',
            // H: period
            isset($planeacion['period']) ? $planeacion['period'] : '',
            // I: dba (JSON array)
            isset($planeacion['dba']) ? json_encode($planeacion['dba'], JSON_UNESCAPED_UNICODE) : '[]',
            // J: standard (JSON array)
            isset($planeacion['standard']) ? json_encode($planeacion['standard'], JSON_UNESCAPED_UNICODE) : '[]',
            // K: dba_manual (estándares manuales cuando no hay normativa)
            isset($planeacion['dba_manual']) ? $planeacion['dba_manual'] : '',
            // L: competency
            isset($planeacion['competency']) ? $planeacion['competency'] : '',
            // L: has_piar
            isset($planeacion['has_piar']) ? ($planeacion['has_piar'] ? 'true' : 'false') : 'false',
            // M: piar_description
            isset($planeacion['piar_description']) ? $planeacion['piar_description'] : '',
            // N: learning_objectives
            isset($planeacion['learning_objectives']) ? $planeacion['learning_objectives'] : '',
            // O: competencias
            isset($planeacion['competencias']) ? $planeacion['competencias'] : '',
            // P: indicadores_logro
            isset($planeacion['indicadores_logro']) ? $planeacion['indicadores_logro'] : '',
            // Q: exploration
            isset($planeacion['exploration']) ? $planeacion['exploration'] : '',
            // R: exploration_activities (JSON array)
            isset($planeacion['exploration_activities']) ? json_encode($planeacion['exploration_activities'], JSON_UNESCAPED_UNICODE) : '[]',
            // S: tiempo_exploracion
            isset($planeacion['tiempo_exploracion']) ? $planeacion['tiempo_exploracion'] : 10,
            // T: structuring
            isset($planeacion['structuring']) ? $planeacion['structuring'] : '',
            // U: structuring_activities (JSON array)
            isset($planeacion['structuring_activities']) ? json_encode($planeacion['structuring_activities'], JSON_UNESCAPED_UNICODE) : '[]',
            // V: tiempo_estructuracion
            isset($planeacion['tiempo_estructuracion']) ? $planeacion['tiempo_estructuracion'] : 20,
            // W: practice
            isset($planeacion['practice']) ? $planeacion['practice'] : '',
            // X: practice_activities (JSON array)
            isset($planeacion['practice_activities']) ? json_encode($planeacion['practice_activities'], JSON_UNESCAPED_UNICODE) : '[]',
            // Y: tiempo_practica
            isset($planeacion['tiempo_practica']) ? $planeacion['tiempo_practica'] : 25,
            // Z: transfer
            isset($planeacion['transfer']) ? $planeacion['transfer'] : '',
            // AA: transfer_activities (JSON array)
            isset($planeacion['transfer_activities']) ? json_encode($planeacion['transfer_activities'], JSON_UNESCAPED_UNICODE) : '[]',
            // AB: tiempo_transferencia
            isset($planeacion['tiempo_transferencia']) ? $planeacion['tiempo_transferencia'] : 15,
            // AC: assessment_moment
            isset($planeacion['assessment_moment']) ? $planeacion['assessment_moment'] : '',
            // AD: assessment_activities (JSON array)
            isset($planeacion['assessment_activities']) ? json_encode($planeacion['assessment_activities'], JSON_UNESCAPED_UNICODE) : '[]',
            // AE: tiempo_valoracion
            isset($planeacion['tiempo_valoracion']) ? $planeacion['tiempo_valoracion'] : 10,
            // AF: eval_type
            isset($planeacion['eval_type']) ? $planeacion['eval_type'] : 'Formativa',
            // AG: eval_modalidades (JSON array)
            isset($planeacion['eval_modalidades']) ? json_encode($planeacion['eval_modalidades'], JSON_UNESCAPED_UNICODE) : '[]',
            // AH: eval_instrumentos (JSON array)
            isset($planeacion['eval_instrumentos']) ? json_encode($planeacion['eval_instrumentos'], JSON_UNESCAPED_UNICODE) : '[]',
            // AI: eval_criterios (JSON array)
            isset($planeacion['eval_criterios']) ? json_encode($planeacion['eval_criterios'], JSON_UNESCAPED_UNICODE) : '[]',
            // AJ: eval_evidencias (JSON array)
            isset($planeacion['eval_evidencias']) ? json_encode($planeacion['eval_evidencias'], JSON_UNESCAPED_UNICODE) : '[]',
            // AK: eval_criteria
            isset($planeacion['eval_criteria']) ? $planeacion['eval_criteria'] : '',
            // AL: eval_evidence
            isset($planeacion['eval_evidence']) ? $planeacion['eval_evidence'] : '',
            // AM: eval_ponderacion_conceptos
            isset($planeacion['eval_ponderacion_conceptos']) ? $planeacion['eval_ponderacion_conceptos'] : 30,
            // AN: eval_ponderacion_procedimientos
            isset($planeacion['eval_ponderacion_procedimientos']) ? $planeacion['eval_ponderacion_procedimientos'] : 40,
            // AO: eval_ponderacion_actitudes
            isset($planeacion['eval_ponderacion_actitudes']) ? $planeacion['eval_ponderacion_actitudes'] : 30,
            // AP: eval_descripcion_auto
            isset($planeacion['eval_descripcion_auto']) ? $planeacion['eval_descripcion_auto'] : '',
            // AQ: resources
            isset($planeacion['resources']) ? $planeacion['resources'] : '',
            // AR: planeacion_tipo
            isset($planeacion['planeacion_tipo']) ? $planeacion['planeacion_tipo'] : '',
            // AS: periodo_academico
            isset($planeacion['periodo_academico']) ? $planeacion['periodo_academico'] : '',
            // AT: fecha_inicio
            isset($planeacion['fecha_inicio']) ? $planeacion['fecha_inicio'] : '',
            // AU: fecha_fin
            isset($planeacion['fecha_fin']) ? $planeacion['fecha_fin'] : '',
            // AV: firma_docente
            isset($planeacion['firma_docente']) ? $planeacion['firma_docente'] : '',
        ];
        $newRows[] = $row;
    }

    // Combinar datos existentes con nuevos
    $finalRows = array_merge($existingData, $newRows);

    // Agregar encabezado al inicio
    array_unshift($finalRows, $headers);

    // Escribir a Google Sheets
    $totalRows = count($finalRows);
    $updateRange = $worksheetTitle . '!A1:AW' . $totalRows;
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
