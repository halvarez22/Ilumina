<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Ruta al archivo CSV de precios
$csvFile = dirname(__DIR__) . '/precios/Precios Venta Página.csv';

// Verificar que el archivo existe
if (!file_exists($csvFile)) {
    http_response_code(404);
    echo json_encode(['error' => 'Archivo de precios no encontrado: ' . $csvFile]);
    exit;
}

function readCSVFile($filePath) {
    $prices = [];
    $lineCount = 0;
    
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        // Leer la primera línea como headers
        $headers = fgetcsv($handle, 0, ',', '"', '\\');
        
        if ($headers && count($headers) >= 2) {
            // Leer solo las primeras 10 líneas de datos
            while (($data = fgetcsv($handle, 0, ',', '"', '\\')) !== FALSE && $lineCount < 10) {
                $lineCount++;
                
                if (count($data) >= 2) {
                    $sku = trim($data[0]); // Primera columna es CODIGO
                    $priceStr = trim($data[1]); // Segunda columna es PRECIO IVA INCLUIDO
                    
                    // Limpiar el precio (remover $, comas y espacios)
                    $priceStr = str_replace(['$', ',', ' '], '', $priceStr);
                    $price = floatval($priceStr);
                    
                    if (!empty($sku) && $price > 0) {
                        $prices[] = [
                            'SKU' => $sku,
                            'Precio' => $price
                        ];
                    }
                }
            }
        }
        fclose($handle);
    }
    
    return $prices;
}

try {
    $prices = readCSVFile($csvFile);
    echo json_encode($prices);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al leer el archivo de precios',
        'details' => $e->getMessage()
    ]);
}
?> 