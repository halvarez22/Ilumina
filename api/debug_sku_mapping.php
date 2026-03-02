<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

echo "=== DIAGNÓSTICO DE MAPEO SKU ===\n\n";

// 1. Obtener lista de imágenes
$imagesPath = dirname(__DIR__) . '/fotos/Ductus/';
$images = [];

if (is_dir($imagesPath)) {
    $files = scandir($imagesPath);
    foreach ($files as $file) {
        if (preg_match('/\.jpg$/i', $file)) {
            $images[] = $file;
        }
    }
}

echo "1. ANÁLISIS DE NOMBRES DE ARCHIVO:\n";
echo "Total imágenes encontradas: " . count($images) . "\n\n";

// 2. Extraer SKUs de nombres de archivo
$skuMapping = [];
foreach ($images as $image) {
    $nameWithoutExt = str_replace('.JPG', '', $image);
    $nameWithoutExt = str_replace('.jpg', '', $nameWithoutExt);
    
    if (strlen($nameWithoutExt) >= 3) {
        $skuMapping[$nameWithoutExt] = $image;
    }
}

echo "2. SKUs EXTRAÍDOS:\n";
echo "Total SKUs mapeados: " . count($skuMapping) . "\n";
echo "Primeros 10 SKUs:\n";
$count = 0;
foreach ($skuMapping as $sku => $filename) {
    if ($count < 10) {
        echo "  SKU: $sku → Archivo: $filename\n";
        $count++;
    } else {
        break;
    }
}
echo "\n";

// 3. Obtener productos de la API
echo "3. PRODUCTOS DE LA API:\n";
try {
    $inventoryResponse = file_get_contents('http://localhost/api/inventory_proxy.php');
    if ($inventoryResponse !== false) {
        $inventoryData = json_decode($inventoryResponse, true);
        
        if (isset($inventoryData['Inventario']) && is_array($inventoryData['Inventario'])) {
            $products = $inventoryData['Inventario'];
            echo "Total productos en inventario: " . count($products) . "\n\n";
            
            echo "4. ANÁLISIS DE COINCIDENCIAS SKU:\n";
            $matches = 0;
            $noMatches = 0;
            
            foreach ($products as $product) {
                $sku = $product['SKU'] ?? 'NO_SKU';
                $hasImage = isset($skuMapping[$sku]);
                
                if ($hasImage) {
                    $matches++;
                    if ($matches <= 5) {
                        echo "  ✅ SKU: $sku → Imagen: " . $skuMapping[$sku] . "\n";
                    }
                } else {
                    $noMatches++;
                    if ($noMatches <= 5) {
                        echo "  ❌ SKU: $sku → Sin imagen local\n";
                    }
                }
            }
            
            echo "\nResumen de coincidencias:\n";
            echo "  Coincidencias: $matches\n";
            echo "  Sin coincidencia: $noMatches\n";
            echo "  Porcentaje de éxito: " . round(($matches / count($products)) * 100, 2) . "%\n";
            
        } else {
            echo "❌ ERROR: No se pudo obtener el inventario\n";
        }
    } else {
        echo "❌ ERROR: No se pudo conectar con inventory_proxy.php\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DEL DIAGNÓSTICO SKU ===\n";
?> 