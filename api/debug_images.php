<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

echo "=== DIAGNÓSTICO DE IMÁGENES ===\n\n";

// 1. Verificar estructura de directorios
echo "1. VERIFICANDO ESTRUCTURA DE DIRECTORIOS:\n";
$currentDir = __DIR__;
$parentDir = dirname(__DIR__);
$imagesPath = $parentDir . '/fotos/Ductus/';

echo "Directorio actual: $currentDir\n";
echo "Directorio padre: $parentDir\n";
echo "Ruta de imágenes: $imagesPath\n";
echo "¿Existe carpeta fotos?: " . (is_dir($parentDir . '/fotos') ? 'SÍ' : 'NO') . "\n";
echo "¿Existe carpeta Ductus?: " . (is_dir($imagesPath) ? 'SÍ' : 'NO') . "\n\n";

// 2. Listar archivos si existe la carpeta
if (is_dir($imagesPath)) {
    echo "2. ARCHIVOS EN LA CARPETA:\n";
    $files = scandir($imagesPath);
    $jpgFiles = [];
    
    foreach ($files as $file) {
        if (preg_match('/\.jpg$/i', $file)) {
            $jpgFiles[] = $file;
        }
    }
    
    echo "Total archivos JPG encontrados: " . count($jpgFiles) . "\n";
    echo "Primeros 10 archivos:\n";
    for ($i = 0; $i < min(10, count($jpgFiles)); $i++) {
        echo "  - " . $jpgFiles[$i] . "\n";
    }
    echo "\n";
    
    // 3. Probar acceso a algunos archivos
    echo "3. PRUEBA DE ACCESO A ARCHIVOS:\n";
    for ($i = 0; $i < min(3, count($jpgFiles)); $i++) {
        $testFile = $imagesPath . $jpgFiles[$i];
        echo "Archivo: " . $jpgFiles[$i] . "\n";
        echo "  Ruta completa: $testFile\n";
        echo "  ¿Existe?: " . (file_exists($testFile) ? 'SÍ' : 'NO') . "\n";
        echo "  ¿Es archivo?: " . (is_file($testFile) ? 'SÍ' : 'NO') . "\n";
        echo "  ¿Es legible?: " . (is_readable($testFile) ? 'SÍ' : 'NO') . "\n";
        echo "  Tamaño: " . (file_exists($testFile) ? filesize($testFile) . " bytes" : "N/A") . "\n";
        echo "\n";
    }
    
    // 4. Probar URLs de serve_image.php
    echo "4. PRUEBA DE URLs DE SERVE_IMAGE.PHP:\n";
    for ($i = 0; $i < min(3, count($jpgFiles)); $i++) {
        $testUrl = "/api/serve_image.php?image=" . urlencode($jpgFiles[$i]);
        echo "URL: $testUrl\n";
        
        // Simular la lógica de serve_image.php
        $imageName = $jpgFiles[$i];
        if (preg_match('/^[A-Za-z0-9._-]+\.JPG$/i', $imageName)) {
            $fullPath = $imagesPath . $imageName;
            if (file_exists($fullPath) && is_file($fullPath)) {
                echo "  ✅ VÁLIDA - Archivo existe y es accesible\n";
            } else {
                echo "  ❌ ERROR - Archivo no existe o no es accesible\n";
            }
        } else {
            echo "  ❌ ERROR - Nombre de archivo no válido\n";
        }
        echo "\n";
    }
    
} else {
    echo "❌ ERROR: La carpeta de imágenes no existe en: $imagesPath\n";
}

// 5. Verificar permisos del servidor web
echo "5. INFORMACIÓN DEL SERVIDOR:\n";
echo "Usuario del servidor: " . (function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : 'No disponible') . "\n";
echo "Permisos de la carpeta padre: " . substr(sprintf('%o', fileperms($parentDir)), -4) . "\n";
if (is_dir($imagesPath)) {
    echo "Permisos de la carpeta imágenes: " . substr(sprintf('%o', fileperms($imagesPath)), -4) . "\n";
}

echo "\n=== FIN DEL DIAGNÓSTICO ===\n";
?> 