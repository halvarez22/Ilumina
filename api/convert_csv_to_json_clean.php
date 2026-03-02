<?php
// Script para convertir CSV a JSON limpiando caracteres problemáticos
$csvFile = dirname(__DIR__) . '/precios/Precios Venta Página.csv';
$jsonFile = dirname(__DIR__) . '/precios/precios.json';

if (!file_exists($csvFile)) {
    echo "Error: Archivo CSV no encontrado: $csvFile\n";
    exit(1);
}

echo "Convirtiendo CSV a JSON (limpiando caracteres)...\n";

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
                
                // Limpiar caracteres problemáticos
                $sku = preg_replace('/[^\x20-\x7E]/', '', $sku); // Solo caracteres ASCII imprimibles
                $priceStr = preg_replace('/[^\x20-\x7E]/', '', $priceStr);
                
                // Limpiar el precio (remover $, comas y espacios)
                $priceStr = str_replace(['$', ',', ' '], '', $priceStr);
                $price = floatval($priceStr);
                
                if (!empty($sku) && $price > 0) {
                    $prices[$sku] = $price; // Usar SKU como clave para búsqueda rápida
                    $validRows++;
                }
            }
            
            // Mostrar progreso cada 500 filas
            if ($processedRows % 500 == 0) {
                echo "Procesadas $processedRows filas, $validRows productos válidos...\n";
            }
        }
        
        echo "Total filas procesadas: $processedRows\n";
        echo "Total productos válidos: $validRows\n";
    }
    fclose($handle);
}

// Crear estructura de datos
$jsonData = [
    'total_products' => count($prices),
    'prices' => $prices
];

echo "Codificando JSON...\n";

// Verificar que no hay errores de codificación
$jsonString = json_encode($jsonData, JSON_UNESCAPED_UNICODE);
if ($jsonString === false) {
    echo "Error al codificar JSON: " . json_last_error_msg() . "\n";
    exit(1);
}

echo "JSON codificado exitosamente. Longitud: " . number_format(strlen($jsonString)) . " caracteres\n";

// Guardar archivo
echo "Guardando archivo...\n";
$result = file_put_contents($jsonFile, $jsonString);
if ($result === false) {
    echo "Error al guardar archivo\n";
    exit(1);
}

echo "Archivo JSON creado exitosamente: $jsonFile\n";
echo "Tamaño del archivo: " . number_format(filesize($jsonFile)) . " bytes\n";
echo "¡Conversión completada!\n";

// Verificar que se puede leer correctamente
echo "Verificando archivo...\n";
$testContent = file_get_contents($jsonFile);
$testData = json_decode($testContent, true);
if ($testData && isset($testData['total_products'])) {
    echo "Verificación exitosa: " . $testData['total_products'] . " productos en el archivo JSON\n";
} else {
    echo "Error en la verificación del archivo\n";
}
?> 