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
    
    $debug = [
        'prices_file_exists' => file_exists($pricesFile),
        'categories_file_exists' => file_exists($categoriesFile),
        'prices_file_path' => realpath($pricesFile),
        'categories_file_path' => realpath($categoriesFile),
        'prices_count' => 0,
        'categories_count' => 0,
        'products_generated' => 0
    ];
    
    if (!file_exists($pricesFile)) {
        throw new Exception("Archivo de precios no encontrado: $pricesFile");
    }
    
    if (!file_exists($categoriesFile)) {
        throw new Exception("Archivo de categorías no encontrado: $categoriesFile");
    }
    
    // Leer precios
    $prices = [];
    $handle = fopen($pricesFile, 'r');
    if ($handle) {
        // Saltar header
        fgetcsv($handle, 0, ',', '"', '\\');
        
        while (($data = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if (count($data) >= 2) {
                $sku = trim($data[0]);
                $price = cleanPrice($data[1]);
                
                if (!empty($sku) && $price > 0) {
                    $prices[$sku] = $price;
                }
            }
        }
        fclose($handle);
    }
    
    $debug['prices_count'] = count($prices);
    
    // Leer categorías
    $categories = [];
    $handle = fopen($categoriesFile, 'r');
    if ($handle) {
        // Saltar header
        fgetcsv($handle, 0, ',', '"', '\\');
        
        while (($data = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if (count($data) >= 3) {
                $sku = trim($data[0]);
                $category = trim($data[2]);
                
                if (!empty($sku) && !empty($category) && $category !== 'NO') {
                    $categories[$sku] = $category;
                }
            }
        }
        fclose($handle);
    }
    
    $debug['categories_count'] = count($categories);
    
    // Generar productos (solo los primeros 10 para debug)
    $products = [];
    $productId = 1;
    $count = 0;
    
    foreach ($prices as $sku => $price) {
        if ($count >= 10) break; // Solo los primeros 10
        
        $category = isset($categories[$sku]) ? $categories[$sku] : 'Sin categoría';
        
        // Solo incluir productos con categorías válidas
        if ($category !== 'Varios' && $category !== 'NO') {
            $products[] = [
                'id' => $productId++,
                'sku' => $sku,
                'name' => $sku,
                'description' => 'Producto - ' . $sku,
                'price' => $price,
                'category' => $category,
                'image' => $sku . '.JPG',
                'stock' => 10,
                'rating' => 4.5,
                'reviews' => 10
            ];
            $count++;
        }
    }
    
    $debug['products_generated'] = count($products);
    $debug['sample_products'] = array_slice($products, 0, 3);
    
    echo json_encode([
        'success' => true,
        'products' => $products,
        'total' => count($products),
        'debug' => $debug
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