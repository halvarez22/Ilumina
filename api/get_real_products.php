<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Función para limpiar precio
function cleanPrice($price) {
    $price = str_replace(['$', ',', ' '], '', $price);
    $price = trim($price);
    return floatval($price);
}

// Función para limpiar caracteres UTF-8 problemáticos
function cleanUtf8($string) {
    // Intentar convertir a UTF-8 válido usando iconv si está disponible
    if (function_exists('iconv')) {
        $string = @iconv('UTF-8', 'UTF-8//IGNORE', $string);
    }
    // Remover caracteres no válidos
    $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $string);
    // Limpieza más agresiva - remover caracteres problemáticos adicionales
    $string = preg_replace('/[^\x20-\x7E\xA0-\xFF]/', '', $string);
    // Remover caracteres de control adicionales
    $string = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $string);
    return $string;
}

// Función para generar descripción basada en SKU
function generateDescription($sku) {
    $descriptions = [
        'ACBOARD' => 'Panel LED de alta eficiencia',
        'ACCAB' => 'Cable para tiras LED',
        'ACCON' => 'Conector para tiras LED',
        'ACCOP' => 'Cople para tiras LED',
        'ACDIFI' => 'Difusor para iluminación lineal',
        'ACENDCAP' => 'Tapa final para tiras LED',
        'ACGRAP' => 'Grapa para tiras LED',
        'ACLECH' => 'Lecho para iluminación lineal',
        'ACLED' => 'LED de alta potencia',
        'ACOPL' => 'Opla para tiras LED',
        'ACRDIF' => 'Difusor redondo',
        'ACT' => 'Tira LED de alta calidad',
        'ACTL' => 'Tira LED con protección',
        'AR111' => 'Lámpara LED AR111',
        'BADAGE' => 'Badge decorativo',
        'BASE' => 'Base para iluminación',
        'BASINF' => 'Base inferior',
        'BASLAT' => 'Base lateral',
        'BASUP' => 'Base superior',
        'BELDEN' => 'Cable Belden',
        'CAB' => 'Cable de conexión',
        'CANOTRAC' => 'Canal de tracción',
        'CAS' => 'Caja de conexión',
        'CI' => 'Cilindro LED',
        'CL' => 'Cilindro LED PCB',
        'CLIP' => 'Clip de montaje',
        'CON' => 'Conector',
        'COP' => 'Cople de conexión',
        'CP' => 'Controlador de potencia',
        'DD' => 'Driver de LED',
        'DHPFMV' => 'Driver de alta potencia',
        'DXA' => 'Difusor XA',
        'ECAP' => 'Encapsulado',
        'ENCAP' => 'Encapsulado',
        'ENDCAP' => 'Tapa final',
        'FP' => 'Fuente de poder',
        'G' => 'Gabinete',
        'GRAPA' => 'Grapa de montaje'
    ];
    
    foreach ($descriptions as $prefix => $desc) {
        if (strpos($sku, $prefix) === 0) {
            return $desc . ' - ' . $sku;
        }
    }
    
    return 'Producto de iluminación LED - ' . $sku;
}

// Función para determinar categoría de imagen
function getImageCategory($sku) {
    $categories = [
        'ACBOARD' => 'Lamparas',
        'ACCAB' => 'Tiras de Leds',
        'ACCON' => 'Tiras de Leds',
        'ACCOP' => 'Tiras de Leds',
        'ACDIFI' => 'Iluminacion Lineal',
        'ACENDCAP' => 'Tiras de Leds',
        'ACGRAP' => 'Tiras de Leds',
        'ACLECH' => 'Iluminacion Lineal',
        'ACLED' => 'Iluminacion Lineal',
        'ACOPL' => 'Tiras de Leds',
        'ACRDIF' => 'Iluminacion Lineal',
        'ACT' => 'Tiras de Leds',
        'ACTL' => 'Tiras de Leds',
        'AR111' => 'Lamparas',
        'BADAGE' => 'Varios',
        'BASE' => 'Linea Europea / Decorativa',
        'BASINF' => 'Rieles',
        'BASLAT' => 'Rieles',
        'BASUP' => 'Varios',
        'BELDEN' => 'Drivers y Controladores',
        'CAB' => 'Tiras de Leds',
        'CANOTRAC' => 'Rieles',
        'CAS' => 'Varios',
        'CI' => 'Varios',
        'CL' => 'Varios',
        'CLIP' => 'Varios',
        'CON' => 'Drivers y Controladores',
        'COP' => 'Rieles',
        'CP' => 'Drivers y Controladores',
        'DD' => 'Drivers y Controladores',
        'DHPFMV' => 'Drivers y Controladores',
        'DXA' => 'Iluminacion Lineal',
        'ECAP' => 'Tiras de Leds',
        'ENCAP' => 'Tiras de Leds',
        'ENDCAP' => 'Tiras de Leds',
        'FP' => 'Drivers y Controladores',
        'G' => 'Varios',
        'GRAPA' => 'Rieles'
    ];
    
    foreach ($categories as $prefix => $category) {
        if (strpos($sku, $prefix) === 0) {
            return $category;
        }
    }
    
    return 'Varios';
}

try {
    $debug = filter_var(getenv('APP_DEBUG') ?: '0', FILTER_VALIDATE_BOOLEAN);

    // Leer archivo de precios
    $pricesFile = '../precios/Precios Venta Página.csv';
    $categoriesFile = '../sku_categoria/sku_categoria.csv';
    
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
                $sku = cleanUtf8(trim($data[0]));
                $price = cleanPrice($data[1]);
                
                if (!empty($sku) && $price > 0) {
                    $prices[$sku] = $price;
                }
            }
        }
        fclose($handle);
    }
    
    // Leer categorías
    $categories = [];
    $handle = fopen($categoriesFile, 'r');
    if ($handle) {
        // Saltar header
        fgetcsv($handle, 0, ',', '"', '\\');
        
        while (($data = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if (count($data) >= 3) {
                $sku = cleanUtf8(trim($data[0]));
                $category = cleanUtf8(trim($data[2]));
                
                if (!empty($sku) && !empty($category) && $category !== 'NO') {
                    $categories[$sku] = $category;
                }
            }
        }
        fclose($handle);
    }
    
    // Generar productos
    $products = [];
    $productId = 1;
    
    foreach ($prices as $sku => $price) {
        $category = isset($categories[$sku]) ? $categories[$sku] : 'Sin categoría';
        
        // Solo incluir productos con categorías válidas (excluir "Varios" y "NO")
        if ($category !== 'Varios' && $category !== 'NO') {
            $products[] = [
                'id' => $productId++,
                'sku' => cleanUtf8($sku),
                'name' => cleanUtf8($sku),
                'description' => cleanUtf8(generateDescription($sku)),
                'price' => $price,
                'category' => cleanUtf8($category),
                'image' => cleanUtf8($sku) . '.JPG',
                'stock' => rand(10, 100),
                'rating' => round(rand(35, 50) / 10, 1),
                'reviews' => rand(5, 50)
            ];
        }
    }
    
    // Ordenar por categoría y luego por SKU
    usort($products, function($a, $b) {
        if ($a['category'] === $b['category']) {
            return strcmp($a['sku'], $b['sku']);
        }
        return strcmp($a['category'], $b['category']);
    });
    
    // Restaurar: devolver todos los productos reales, excluyendo los problemáticos
    $badProducts = [];
    $validProducts = [];
    foreach ($products as $i => $product) {
        $test = json_encode($product, JSON_UNESCAPED_UNICODE);
        if ($test === false) {
            $badProducts[] = [
                'index' => $i,
                'error' => json_last_error_msg(),
                'product' => $product
            ];
        } else {
            $validProducts[] = $product;
        }
    }
    if (!empty($badProducts)) {
        if ($debug) {
            file_put_contents('debug_bad_products.txt', print_r($badProducts, true));
        }
    }
    $dataToEncode = [
        'success' => true,
        'products' => $validProducts,
        'total' => count($validProducts)
    ];
    $jsonResponse = json_encode($dataToEncode, JSON_UNESCAPED_UNICODE);
    echo $jsonResponse;
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?> 