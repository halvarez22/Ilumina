<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Incluir el script original
ob_start();
include 'get_real_products.php';
$output = ob_get_clean();

// Verificar si hay errores
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'success' => false,
        'error' => 'Error de JSON: ' . json_last_error_msg(),
        'raw_output' => $output,
        'output_length' => strlen($output)
    ]);
} else {
    echo json_encode([
        'success' => true,
        'message' => 'Script ejecutado correctamente',
        'output_length' => strlen($output),
        'first_100_chars' => substr($output, 0, 100)
    ]);
}
?> 