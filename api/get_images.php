<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Ruta a la carpeta de imágenes (desde el directorio api)
$imagesPath = dirname(__DIR__) . '/fotos/Ductus/';

// Verificar que la carpeta existe
if (!is_dir($imagesPath)) {
    http_response_code(404);
    echo json_encode(['error' => 'Carpeta de imágenes no encontrada: ' . $imagesPath]);
    exit;
}

// Obtener todos los archivos JPG
$images = [];
$files = scandir($imagesPath);

foreach ($files as $file) {
    // Solo incluir archivos JPG (case insensitive)
    if (preg_match('/\.jpg$/i', $file)) {
        $images[] = $file;
    }
}

// Ordenar alfabéticamente
sort($images);

// Devolver la lista de imágenes
echo json_encode($images);
?> 