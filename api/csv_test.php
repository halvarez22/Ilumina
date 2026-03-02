<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$csvFile = dirname(__DIR__) . '/precios/Precios Venta Página.csv';

if (!file_exists($csvFile)) {
    echo json_encode(['error' => 'Archivo no encontrado']);
    exit;
}

$handle = fopen($csvFile, "r");
if ($handle) {
    $result = [];
    
    // Leer headers
    $headers = fgetcsv($handle, 0, ',', '"', '\\');
    $result['headers'] = $headers;
    
    // Leer solo las primeras 3 líneas de datos
    for ($i = 0; $i < 3; $i++) {
        $data = fgetcsv($handle, 0, ',', '"', '\\');
        if ($data) {
            $result['data'][] = $data;
        }
    }
    
    fclose($handle);
    
    echo json_encode($result);
} else {
    echo json_encode(['error' => 'No se pudo abrir el archivo']);
}
?> 