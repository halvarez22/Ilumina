<?php
// Script simple para convertir CSV a JSON
$csvFile = dirname(__DIR__) . '/precios/Precios Venta Página.csv';
$jsonFile = dirname(__DIR__) . '/precios/precios.json';

if (!file_exists($csvFile)) {
    echo "Error: Archivo CSV no encontrado: $csvFile\n";
    exit(1);
}

echo "Convirtiendo CSV a JSON...\n";

$prices = [];

if (($handle = fopen($csvFile, "r")) !== FALSE) {
    // Leer la primera línea como headers
    $headers = fgetcsv($handle, 0, ',', '"', '\\');
    echo "Headers encontrados: " . implode(', ', $headers) . "\n";
    
    if ($headers && count($headers) >= 2) {
        $processedRows = 0;
        $validRows = 0;
        
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

// Crear estructura de datos más simple
$jsonData = [
    'total_products' => count($prices),
    'prices' => $prices
];

echo "Intentando guardar archivo JSON...\n";

// Intentar guardar con diferentes métodos
$success = false;

// Método 1: file_put_contents
$jsonString = json_encode($jsonData);
if ($jsonString !== false) {
    $result = file_put_contents($jsonFile, $jsonString);
    if ($result !== false) {
        echo "Archivo JSON guardado exitosamente con file_put_contents\n";
        $success = true;
    } else {
        echo "Error con file_put_contents\n";
    }
} else {
    echo "Error al codificar JSON\n";
}

// Método 2: fopen/fwrite si el primero falló
if (!$success) {
    echo "Intentando con fopen/fwrite...\n";
    $handle = fopen($jsonFile, 'w');
    if ($handle) {
        $result = fwrite($handle, $jsonString);
        fclose($handle);
        if ($result !== false) {
            echo "Archivo JSON guardado exitosamente con fopen/fwrite\n";
            $success = true;
        } else {
            echo "Error con fopen/fwrite\n";
        }
    } else {
        echo "No se pudo abrir el archivo para escritura\n";
    }
}

if ($success) {
    echo "Archivo JSON creado: $jsonFile\n";
    echo "Tamaño del archivo: " . number_format(filesize($jsonFile)) . " bytes\n";
    echo "¡Conversión completada!\n";
} else {
    echo "No se pudo guardar el archivo JSON\n";
    exit(1);
}
?> 