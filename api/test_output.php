<?php
// Capturar la salida del script original
ob_start();
include 'get_real_products.php';
$output = ob_get_clean();

// Verificar si hay errores de JSON
$jsonError = json_last_error();
$jsonErrorMessage = json_last_error_msg();

// Intentar decodificar la salida
$decoded = json_decode($output, true);

echo json_encode([
    'success' => true,
    'output_length' => strlen($output),
    'json_error' => $jsonError,
    'json_error_message' => $jsonErrorMessage,
    'output_preview' => substr($output, 0, 200),
    'decoded_success' => $decoded !== null,
    'decoded_keys' => $decoded ? array_keys($decoded) : null
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?> 