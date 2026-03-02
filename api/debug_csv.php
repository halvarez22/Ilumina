<?php
echo "=== DEBUG CSV CATEGORÍAS ===\n\n";

$csvFile = dirname(__DIR__) . '/public/sku_categoria/sku_categoria.csv';

if (!file_exists($csvFile)) {
    echo "❌ ERROR: Archivo CSV no encontrado: $csvFile\n";
    exit(1);
}

echo "✅ Archivo encontrado: $csvFile\n\n";

// Leer las primeras 10 líneas
$handle = fopen($csvFile, 'r');
if ($handle) {
    echo "1. PRIMERAS 10 LÍNEAS:\n";
    $lineCount = 0;
    while (($line = fgets($handle)) !== false && $lineCount < 10) {
        $lineCount++;
        echo "Línea $lineCount: " . trim($line) . "\n";
    }
    fclose($handle);
}

// Analizar headers
echo "\n2. ANÁLISIS DE HEADERS:\n";
$handle = fopen($csvFile, 'r');
if ($handle) {
    $headers = fgetcsv($handle);
    if ($headers) {
        echo "Headers encontrados:\n";
        foreach ($headers as $index => $header) {
            echo "  Columna $index: '$header'\n";
        }
    }
    fclose($handle);
}

// Contar categorías
echo "\n3. ANÁLISIS DE CATEGORÍAS:\n";
$handle = fopen($csvFile, 'r');
if ($handle) {
    $headers = fgetcsv($handle);
    $categories = [];
    $skuCount = 0;
    
    while (($data = fgetcsv($handle)) !== false) {
        if (count($data) >= 3) {
            $sku = trim($data[0]);
            $category = trim($data[2]);
            
            if (!empty($sku) && !empty($category)) {
                $categories[$category] = ($categories[$category] ?? 0) + 1;
                $skuCount++;
            }
        }
    }
    fclose($handle);
    
    echo "Total SKUs: $skuCount\n";
    echo "Categorías encontradas:\n";
    foreach ($categories as $category => $count) {
        echo "  '$category': $count productos\n";
    }
}

echo "\n=== FIN DEBUG CSV ===\n";
?> 