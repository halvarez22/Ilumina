<?php
echo "=== SIMULACIÓN COMPLETA DEL FLUJO FRONTEND ===\n\n";

// 1. Simular ImageMappingService.initialize()
echo "1. INICIALIZANDO IMAGEMAPPINGSERVICE:\n";
$imagesPath = dirname(__DIR__) . '/fotos/Ductus/';
$imageCache = [];

if (is_dir($imagesPath)) {
    $files = scandir($imagesPath);
    foreach ($files as $file) {
        if (preg_match('/\.jpg$/i', $file)) {
            $nameWithoutExt = str_replace('.JPG', '', $file);
            $nameWithoutExt = str_replace('.jpg', '', $nameWithoutExt);
            
            if (strlen($nameWithoutExt) >= 3) {
                $imageCache[$nameWithoutExt] = $file;
            }
        }
    }
    echo "✅ Mapeo creado con " . count($imageCache) . " imágenes\n";
} else {
    echo "❌ ERROR: Carpeta de imágenes no encontrada\n";
    exit(1);
}

// 2. Simular getProducts() - obtener productos de test_products.php
echo "\n2. OBTENIENDO PRODUCTOS:\n";
$testProductsUrl = 'http://localhost/api/test_products.php';
$products = [];

try {
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'ignore_errors' => true
        ]
    ]);
    
    $response = file_get_contents($testProductsUrl, false, $context);
    if ($response !== false) {
        $data = json_decode($response, true);
        if (isset($data['Inventario']) && is_array($data['Inventario'])) {
            $products = $data['Inventario'];
            echo "✅ Productos obtenidos: " . count($products) . "\n";
        } else {
            echo "❌ ERROR: Formato de respuesta inválido\n";
        }
    } else {
        echo "❌ ERROR: No se pudo conectar con test_products.php\n";
        echo "Intentando simular productos localmente...\n";
        
        // Simular productos localmente si no hay servidor web
        $products = [
            [
                'SKU' => 'ACBOARD2L20WNW',
                'Titulo' => 'Lámpara Colgante ACBOARD2L20WNW',
                'Descripcion' => 'Elegante lámpara colgante con acabado en oro cepillado',
                'Stock' => '15',
                'Cve_sat' => 'Lámparas Colgantes',
                'Img1' => 'https://picsum.photos/seed/ACBOARD2L20WNW/600/400'
            ],
            [
                'SKU' => 'ACCAB4WTLRGB',
                'Titulo' => 'Aplique ACCAB4WTLRGB',
                'Descripcion' => 'Moderno aplique de pared con luz LED RGB',
                'Stock' => '22',
                'Cve_sat' => 'Apliques',
                'Img1' => 'https://picsum.photos/seed/ACCAB4WTLRGB/600/400'
            ],
            [
                'SKU' => 'ACCON9MM',
                'Titulo' => 'Conexión ACCON9MM',
                'Descripcion' => 'Conexión de 9mm para tiras LED',
                'Stock' => '50',
                'Cve_sat' => 'Conexiones',
                'Img1' => 'https://picsum.photos/seed/ACCON9MM/600/400'
            ]
        ];
        echo "✅ Productos simulados: " . count($products) . "\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

// 3. Simular el mapeo de productos con imágenes
echo "\n3. MAPEANDO PRODUCTOS CON IMÁGENES:\n";
$mappedProducts = [];

foreach ($products as $item) {
    $sku = $item['SKU'] ?? 'NO_SKU';
    
    // Simular ImageMappingService.getImageUrlWithFallback()
    $localImage = isset($imageCache[$sku]) ? $imageCache[$sku] : null;
    $localImageUrl = $localImage ? "/api/serve_image.php?image=" . urlencode($localImage) : null;
    $fallbackUrl = $item['Img1'] ?? "https://picsum.photos/seed/$sku/600/400";
    $finalImageUrl = $localImageUrl ?: $fallbackUrl;
    
    $mappedProducts[] = [
        'id' => $sku,
        'name' => $item['Titulo'] ?? $item['Descripcion'] ?? "Producto $sku",
        'description' => $item['Descripcion'] ?? 'Sin descripción disponible',
        'price' => 0.00,
        'imageUrl' => $finalImageUrl,
        'category' => $item['Cve_sat'] ?? 'Sin categoría',
        'stock' => intval($item['Stock'] ?? 0),
        'hasLocalImage' => $localImageUrl !== null,
        'localImageFile' => $localImage
    ];
    
    echo "Producto: " . ($item['Titulo'] ?? $sku) . "\n";
    echo "  SKU: $sku\n";
    echo "  Imagen local: " . ($localImage ? "✅ $localImage" : "❌ No encontrada") . "\n";
    echo "  URL final: $finalImageUrl\n";
    echo "  Es local: " . ($localImageUrl !== null ? "✅ SÍ" : "❌ NO") . "\n";
    echo "\n";
}

// 4. Resumen final
echo "4. RESUMEN FINAL:\n";
$localImages = 0;
$fallbackImages = 0;

foreach ($mappedProducts as $product) {
    if ($product['hasLocalImage']) {
        $localImages++;
    } else {
        $fallbackImages++;
    }
}

echo "Total productos: " . count($mappedProducts) . "\n";
echo "Imágenes locales: $localImages\n";
echo "Imágenes de fallback: $fallbackImages\n";
echo "Porcentaje de éxito: " . round(($localImages / count($mappedProducts)) * 100, 2) . "%\n";

if ($localImages === 0) {
    echo "\n🚨 PROBLEMA DETECTADO: No se están cargando imágenes locales\n";
    echo "Posibles causas:\n";
    echo "  1. El servidor web no está corriendo\n";
    echo "  2. Las rutas de las APIs no son accesibles\n";
    echo "  3. Los SKUs no coinciden con los nombres de archivo\n";
    echo "  4. Problema de CORS o permisos\n";
} else {
    echo "\n✅ SISTEMA FUNCIONANDO: Se están cargando imágenes locales\n";
}

echo "\n=== FIN DE LA SIMULACIÓN ===\n";
?> 