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
    $debug = [];
    $processedRows = 0;
    $validRows = 0;
    
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        // Leer la primera línea como headers
        $headers = fgetcsv($handle, 0, ',', '"', '\\');
        $debug['headers'] = $headers;
        
        if ($headers && count($headers) >= 2) {
            // Leer el resto de las líneas como datos
            while (($data = fgetcsv($handle, 0, ',', '"', '\\')) !== FALSE) {
                $processedRows++;
                
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
                        $validRows++;
                        
                        // Solo mostrar los primeros 3 para debug
                        if ($validRows <= 3) {
                            $debug['sample_products'][] = [
                                'sku' => $sku,
                                'original_price' => $data[1],
                                'cleaned_price' => $priceStr,
                                'final_price' => $price
                            ];
                        }
                    }
                }
            }
        }
        fclose($handle);
    }
    
    $debug['stats'] = [
        'processed_rows' => $processedRows,
        'valid_rows' => $validRows,
        'total_products' => count($prices)
    ];
    
    return ['prices' => $prices, 'debug' => $debug];
}

try {
    $result = readCSVFile($csvFile);
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al leer el archivo de precios',
        'details' => $e->getMessage()
    ]);
}
?> 