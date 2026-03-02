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
    
    // Procesar solo las primeras 3 líneas
    for ($i = 0; $i < 3; $i++) {
        $data = fgetcsv($handle, 0, ',', '"', '\\');
        if ($data && count($data) >= 2) {
            $sku = trim($data[0]);
            $priceStr = trim($data[1]);
            $priceStr_clean = str_replace(['$', ',', ' '], '', $priceStr);
            $price = floatval($priceStr_clean);
            
            $result['processed'][] = [
                'original_sku' => $data[0],
                'original_price' => $data[1],
                'cleaned_sku' => $sku,
                'cleaned_price_str' => $priceStr_clean,
                'final_price' => $price,
                'is_valid' => (!empty($sku) && $price > 0)
            ];
            
            if (!empty($sku) && $price > 0) {
                $result['prices'][] = [
                    'SKU' => $sku,
                    'Precio' => $price
                ];
            }
        }
    }
    
    fclose($handle);
    
    echo json_encode($result);
} else {
    echo json_encode(['error' => 'No se pudo abrir el archivo']);
}
?> 