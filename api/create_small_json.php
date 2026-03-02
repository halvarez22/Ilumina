<?php
// Script para crear un JSON pequeño de prueba
$jsonFile = dirname(__DIR__) . '/precios/precios.json';

echo "Creando archivo JSON pequeño de prueba...\n";

// Crear datos de prueba con solo 10 productos
$prices = [
    'ACBOARD2L20WNW' => 323.51,
    'ACBOARD2L20WW' => 323.51,
    'ACBOARD2L20WWW' => 323.51,
    'ACBOARD50NW' => 236.40,
    'ACBOARD50W' => 236.40,
    'ACBOARD50WW' => 236.40,
    'ACCAB4WTLRGB' => 75.41,
    'ACCABTLCCT' => 60.33,
    'ACCON9MM' => 87.10,
    'ACCONCCT127V' => 1286.90
];

$jsonData = [
    'total_products' => count($prices),
    'prices' => $prices
];

echo "Datos a guardar:\n";
echo json_encode($jsonData, JSON_PRETTY_PRINT) . "\n";

$jsonString = json_encode($jsonData);
if ($jsonString === false) {
    echo "Error al codificar JSON: " . json_last_error_msg() . "\n";
    exit(1);
}

echo "JSON codificado exitosamente. Longitud: " . strlen($jsonString) . " caracteres\n";

$result = file_put_contents($jsonFile, $jsonString);
if ($result === false) {
    echo "Error al guardar archivo\n";
    exit(1);
}

echo "Archivo guardado exitosamente: $jsonFile\n";
echo "Tamaño del archivo: " . filesize($jsonFile) . " bytes\n";
echo "¡Completado!\n";
?> 