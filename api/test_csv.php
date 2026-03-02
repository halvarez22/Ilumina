<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$csvFile = dirname(__DIR__) . '/sku_categoria/sku_categoria.csv';

if (!file_exists($csvFile)) {
    echo json_encode(['error' => 'CSV no encontrado: ' . $csvFile]);
    exit;
}

$handle = fopen($csvFile, 'r');
if (!$handle) {
    echo json_encode(['error' => 'No se pudo abrir el CSV']);
    exit;
}

$headers = fgetcsv($handle);
$firstRow = fgetcsv($handle);
fclose($handle);

echo json_encode([
    'success' => true,
    'headers' => $headers,
    'firstRow' => $firstRow,
    'filePath' => $csvFile
]);
?> 