<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$debug = [];

try {
    // Verificar archivos
    $pricesFile = '../precios/Precios Venta Página.csv';
    $categoriesFile = '../sku_categoria/sku_categoria.csv';
    
    $debug['files'] = [
        'prices_file' => $pricesFile,
        'prices_exists' => file_exists($pricesFile),
        'categories_file' => $categoriesFile,
        'categories_exists' => file_exists($categoriesFile)
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
        $header = fgetcsv($handle, 0, ',', '"', '\\');
        $debug['prices_header'] = $header;
        
        $count = 0;
        while (($data = fgetcsv($handle, 0, ',', '"', '\\')) !== false && $count < 5) {
            if (count($data) >= 2) {
                $sku = trim($data[0]);
                $price = floatval(str_replace(['$', ','], '', $data[1]));
                
                if (!empty($sku) && $price > 0) {
                    $prices[$sku] = $price;
                    $count++;
                }
            }
        }
        fclose($handle);
    }
    
    $debug['prices_count'] = count($prices);
    $debug['prices_sample'] = array_slice($prices, 0, 5, true);
    
    // Leer categorías
    $categories = [];
    $handle = fopen($categoriesFile, 'r');
    if ($handle) {
        // Saltar header
        $header = fgetcsv($handle, 0, ',', '"', '\\');
        $debug['categories_header'] = $header;
        
        $count = 0;
        while (($data = fgetcsv($handle, 0, ',', '"', '\\')) !== false && $count < 5) {
            if (count($data) >= 3) {
                $sku = trim($data[0]);
                $category = trim($data[2]);
                
                if (!empty($sku) && !empty($category) && $category !== 'NO') {
                    $categories[$sku] = $category;
                    $count++;
                }
            }
        }
        fclose($handle);
    }
    
    $debug['categories_count'] = count($categories);
    $debug['categories_sample'] = array_slice($categories, 0, 5, true);
    
    // Generar productos
    $products = [];
    $productId = 1;
    
    foreach ($prices as $sku => $price) {
        $category = isset($categories[$sku]) ? $categories[$sku] : 'Sin categoría';
        
        // Solo incluir productos con categorías válidas
        if ($category !== 'Varios' && $category !== 'NO') {
            $products[] = [
                'id' => $productId++,
                'sku' => $sku,
                'name' => $sku,
                'description' => 'Producto de iluminación LED - ' . $sku,
                'price' => $price,
                'category' => $category,
                'image' => $sku . '.JPG',
                'stock' => rand(10, 100),
                'rating' => round(rand(35, 50) / 10, 1),
                'reviews' => rand(5, 50)
            ];
        }
    }
    
    $debug['products_count'] = count($products);
    $debug['products_sample'] = array_slice($products, 0, 3);
    
    echo json_encode([
        'success' => true,
        'debug' => $debug,
        'products' => $products,
        'total' => count($products)
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => $debug
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?> 