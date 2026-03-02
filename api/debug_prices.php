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
    $debug = [];
    
    // Leer primera línea
    $headers = fgetcsv($handle, 0, ',', '"', '\\');
    $debug['headers'] = $headers;
    $debug['headers_count'] = count($headers);
    
    // Leer primeras 5 líneas de datos
    $sampleData = [];
    for ($i = 0; $i < 5; $i++) {
        $data = fgetcsv($handle, 0, ',', '"', '\\');
        if ($data) {
            $sampleData[] = $data;
        }
    }
    $debug['sample_data'] = $sampleData;
    
    // Procesar una línea de ejemplo
    if (!empty($sampleData[0])) {
        $sku = trim($sampleData[0][0]);
        $priceStr = trim($sampleData[0][1]);
        $priceStr_clean = str_replace(['$', ',', ' '], '', $priceStr);
        $price = floatval($priceStr_clean);
        
        $debug['example_processing'] = [
            'original_sku' => $sampleData[0][0],
            'original_price' => $sampleData[0][1],
            'cleaned_price' => $priceStr_clean,
            'final_price' => $price,
            'is_valid' => (!empty($sku) && $price > 0)
        ];
    }
    
    fclose($handle);
    
    echo json_encode($debug);
} else {
    echo json_encode(['error' => 'No se pudo abrir el archivo']);
}
?> 