<?php
/**
 * ai_proxy.php - Proxy para OpenRouter API
 * Mantiene la API key segura en el servidor
 */

include_once("cors.php");
header('Content-Type: application/json; charset=utf-8');

// ⚠️ CONFIGURA TU API KEY AQUÍ (NUNCA en el frontend)
$OPENROUTER_API_KEY = '';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    echo json_encode(['error' => ['message' => 'Método no permitido', 'code' => 405]]);
    exit();
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['messages'])) {
    echo json_encode(['error' => ['message' => 'Datos incompletos', 'code' => 400]]);
    exit();
}

$payload = json_encode([
    'model' => $data['model'] ?? 'openrouter/free',
    'max_tokens' => $data['max_tokens'] ?? 800,
    'messages' => $data['messages'],
]);

$ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $OPENROUTER_API_KEY,
        'Content-Type: application/json',
        'HTTP-Referer: https://app.iedeoccidente.com',
        'X-Title: Inasistig',
    ],
    CURLOPT_TIMEOUT => 60,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    http_response_code(500);
    echo json_encode(['error' => ['message' => 'Error de conexión: ' . $error, 'code' => 500]]);
    exit();
}

http_response_code($httpCode);
echo $response;
