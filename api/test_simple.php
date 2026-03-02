<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Datos de prueba muy simples
$testData = [
    'success' => true,
    'message' => 'Test successful',
    'products' => [
        [
            'id' => 1,
            'name' => 'Test Product',
            'price' => 100
        ]
    ]
];

// Intentar generar JSON
$jsonResponse = json_encode($testData);

if ($jsonResponse === false) {
    $error = json_last_error_msg();
    echo json_encode([
        'success' => false,
        'error' => 'JSON encode failed: ' . $error
    ]);
} else {
    echo $jsonResponse;
}
?> 