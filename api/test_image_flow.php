<?php
echo "=== PRUEBA COMPLETA DEL FLUJO DE IMÁGENES ===\n\n";

// 1. Simular la inicialización del ImageMappingService
echo "1. INICIALIZANDO MAPEO DE IMÁGENES:\n";
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

// 2. Simular algunos productos de ejemplo
echo "\n2. PRODUCTOS DE PRUEBA:\n";
$testProducts = [
    ['SKU' => 'ACBOARD2L20WNW', 'name' => 'Producto Test 1'],
    ['SKU' => 'ACCAB4WTLRGB', 'name' => 'Producto Test 2'],
    ['SKU' => 'ACCON9MM', 'name' => 'Producto Test 3'],
    ['SKU' => 'INVALID_SKU', 'name' => 'Producto Sin Imagen'],
];

foreach ($testProducts as $product) {
    $sku = $product['SKU'];
    $hasLocalImage = isset($imageCache[$sku]);
    
    if ($hasLocalImage) {
        $imageUrl = "/api/serve_image.php?image=" . urlencode($imageCache[$sku]);
        echo "  ✅ SKU: $sku → Imagen: " . $imageCache[$sku] . " → URL: $imageUrl\n";
    } else {
        $fallbackUrl = "https://picsum.photos/seed/$sku/600/400";
        echo "  ❌ SKU: $sku → Sin imagen local → Fallback: $fallbackUrl\n";
    }
}

// 3. Probar URLs de serve_image.php
echo "\n3. PRUEBA DE URLs DE SERVE_IMAGE.PHP:\n";
foreach ($testProducts as $product) {
    $sku = $product['SKU'];
    if (isset($imageCache[$sku])) {
        $imageName = $imageCache[$sku];
        $fullPath = $imagesPath . $imageName;
        
        echo "Probando: $imageName\n";
        echo "  Ruta completa: $fullPath\n";
        echo "  ¿Existe?: " . (file_exists($fullPath) ? 'SÍ' : 'NO') . "\n";
        echo "  ¿Es legible?: " . (is_readable($fullPath) ? 'SÍ' : 'NO') . "\n";
        
        // Simular la validación de serve_image.php
        if (preg_match('/^[A-Za-z0-9._-]+\.JPG$/i', $imageName)) {
            echo "  ✅ Nombre válido\n";
        } else {
            echo "  ❌ Nombre inválido\n";
        }
        echo "\n";
    }
}

// 4. Simular el flujo del frontend
echo "4. SIMULACIÓN DEL FLUJO FRONTEND:\n";
echo "Cuando el frontend carga:\n";
echo "  1. ImageMappingService.initialize() se ejecuta\n";
echo "  2. Se hace fetch('/api/get_images.php')\n";
echo "  3. Se crea el mapeo SKU → URL\n";
echo "  4. Para cada producto, se busca la imagen local\n";
echo "  5. Si no existe, se usa fallback\n\n";

// 5. Verificar posibles problemas
echo "5. DIAGNÓSTICO DE PROBLEMAS POTENCIALES:\n";

// Problema 1: Rutas relativas vs absolutas
echo "Problema 1 - Rutas:\n";
echo "  Ruta actual: " . __DIR__ . "\n";
echo "  Ruta de imágenes: $imagesPath\n";
echo "  URL base esperada: /api/serve_image.php\n";

// Problema 2: Headers de respuesta
echo "\nProblema 2 - Headers:\n";
echo "  Content-Type: application/json (para get_images.php)\n";
echo "  Content-Type: image/jpeg (para serve_image.php)\n";
echo "  Access-Control-Allow-Origin: *\n";

// Problema 3: Validación de nombres
echo "\nProblema 3 - Validación:\n";
$testNames = ['ACBOARD2L20WNW.JPG', 'test.jpg', 'invalid.txt'];
foreach ($testNames as $name) {
    $isValid = preg_match('/^[A-Za-z0-9._-]+\.JPG$/i', $name);
    echo "  '$name': " . ($isValid ? '✅ Válido' : '❌ Inválido') . "\n";
}

echo "\n=== FIN DE LA PRUEBA ===\n";
?> 