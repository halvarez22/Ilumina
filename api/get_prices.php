<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Ruta al archivo JSON de precios
$jsonFile = dirname(__DIR__) . '/precios/precios.json';

// Verificar que el archivo existe
if (!file_exists($jsonFile)) {
    http_response_code(404);
    echo json_encode(['error' => 'Archivo de precios no encontrado: ' . $jsonFile]);
    exit;
}

try {
    // Leer el archivo JSON
    $jsonContent = file_get_contents($jsonFile);
    if ($jsonContent === false) {
        throw new Exception('No se pudo leer el archivo JSON');
    }
    
    $data = json_decode($jsonContent, true);
    if ($data === null) {
        throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
    }
    
    // Devolver solo los precios en el formato esperado por el frontend
    if (isset($data['prices'])) {
        $prices = [];
        foreach ($data['prices'] as $sku => $price) {
            $prices[] = [
                'SKU' => $sku,
                'Precio' => $price
            ];
        }
        echo json_encode($prices);
    } else {
        echo json_encode([]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al leer el archivo de precios',
        'details' => $e->getMessage()
    ]);
}
?> 