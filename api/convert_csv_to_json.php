<?php
// Script para convertir CSV a JSON
$csvFile = dirname(__DIR__) . '/precios/Precios Venta Página.csv';
$jsonFile = dirname(__DIR__) . '/precios/precios.json';

if (!file_exists($csvFile)) {
    echo "Error: Archivo CSV no encontrado: $csvFile\n";
    exit(1);
}

echo "Convirtiendo CSV a JSON...\n";

$prices = [];
$processedRows = 0;
$validRows = 0;

if (($handle = fopen($csvFile, "r")) !== FALSE) {
    // Leer la primera línea como headers
    $headers = fgetcsv($handle, 0, ',', '"', '\\');
    echo "Headers encontrados: " . implode(', ', $headers) . "\n";
    
    if ($headers && count($headers) >= 2) {
        // Leer todas las líneas de datos
        while (($data = fgetcsv($handle, 0, ',', '"', '\\')) !== FALSE) {
            $processedRows++;
            
            if (count($data) >= 2) {
                $sku = trim($data[0]); // Primera columna es CODIGO
                $priceStr = trim($data[1]); // Segunda columna es PRECIO IVA INCLUIDO
                
                // Limpiar el precio (remover $, comas y espacios)
                $priceStr = str_replace(['$', ',', ' '], '', $priceStr);
                $price = floatval($priceStr);
                
                if (!empty($sku) && $price > 0) {
                    $prices[$sku] = $price; // Usar SKU como clave para búsqueda rápida
                    $validRows++;
                }
            }
            
            // Mostrar progreso cada 1000 filas
            if ($processedRows % 1000 == 0) {
                echo "Procesadas $processedRows filas, $validRows productos válidos...\n";
            }
        }
    }
    fclose($handle);
}

echo "Total filas procesadas: $processedRows\n";
echo "Total productos válidos: $validRows\n";

// Guardar como JSON
$jsonData = [
    'metadata' => [
        'total_products' => $validRows,
        'processed_rows' => $processedRows,
        'created_at' => date('Y-m-d H:i:s'),
        'source_file' => basename($csvFile)
    ],
    'prices' => $prices
];

$jsonString = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

if (file_put_contents($jsonFile, $jsonString)) {
    echo "Archivo JSON creado exitosamente: $jsonFile\n";
    echo "Tamaño del archivo: " . number_format(filesize($jsonFile)) . " bytes\n";
} else {
    echo "Error al guardar el archivo JSON\n";
    exit(1);
}

echo "¡Conversión completada!\n";
?> 