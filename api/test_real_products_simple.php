<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Función para limpiar precio
function cleanPrice($price) {
    $price = str_replace(['$', ',', ' '], '', $price);
    $price = trim($price);
    return floatval($price);
}

try {
    // Leer archivo de precios
    $pricesFile = '../precios/Precios Venta Página.csv';
    $categoriesFile = '../sku_categoria/sku_categoria.csv';
    
    if (!file_exists($pricesFile)) {
        throw new Exception("Archivo de precios no encontrado: $pricesFile");
    }
    
    if (!file_exists($categoriesFile)) {
        throw new Exception("Archivo de categorías no encontrado: $categoriesFile");
    }
    
    // Leer solo los primeros 5 precios para prueba
    $prices = [];
    $handle = fopen($pricesFile, 'r');
    if ($handle) {
        // Saltar header
        fgetcsv($handle, 0, ',', '"', '\\');
        
        $count = 0;
        while (($data = fgetcsv($handle, 0, ',', '"', '\\')) !== false && $count < 5) {
            if (count($data) >= 2) {
                $sku = trim($data[0]);
                $price = cleanPrice($data[1]);
                
                if (!empty($sku) && $price > 0) {
                    $prices[$sku] = $price;
                    $count++;
                }
            }
        }
        fclose($handle);
    }
    
    // Generar productos de prueba
    $products = [];
    $productId = 1;
    
    foreach ($prices as $sku => $price) {
        $products[] = [
            'id' => $productId++,
            'sku' => $sku,
            'name' => $sku,
            'description' => 'Producto de prueba - ' . $sku,
            'price' => $price,
            'category' => 'Test',
            'image' => $sku . '.JPG',
            'stock' => 10,
            'rating' => 4.5,
            'reviews' => 10
        ];
    }
    
    echo json_encode([
        'success' => true,
        'products' => $products,
        'total' => count($products),
        'debug' => [
            'prices_count' => count($prices),
            'sample_prices' => array_slice($prices, 0, 3, true),
            'prices_file_exists' => file_exists($pricesFile),
            'categories_file_exists' => file_exists($categoriesFile),
            'prices_file_path' => realpath($pricesFile),
            'categories_file_path' => realpath($categoriesFile)
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?> 