<?php
// cors.php - Manejo de CORS centralizado

// Permitir cualquier origen
header('Access-Control-Allow-Origin: *');

// Métodos permitidos
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

// Cabeceras permitidas
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Manejar solicitud preflight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
