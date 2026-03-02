<?php
// Configuración de seguridad
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Obtener el nombre de la imagen del parámetro
$imageName = $_GET['image'] ?? '';

// Validar el nombre de la imagen (solo permitir caracteres seguros)
if (!preg_match('/^[A-Za-z0-9._-]+\.JPG$/i', $imageName)) {
    http_response_code(400);
    echo 'Nombre de imagen inválido';
    exit;
}

// Ruta base de las imágenes (desde el directorio api)
$imagesPath = dirname(__DIR__) . '/fotos/Ductus/';
$fullPath = $imagesPath . $imageName;

// Verificar que el archivo existe y está dentro del directorio permitido
if (!file_exists($fullPath) || !is_file($fullPath)) {
    http_response_code(404);
    echo 'Imagen no encontrada';
    exit;
}

// Verificar que la ruta real está dentro del directorio permitido (prevención de directory traversal)
$realPath = realpath($fullPath);
$allowedPath = realpath($imagesPath);

if ($realPath === false || strpos($realPath, $allowedPath) !== 0) {
    http_response_code(403);
    echo 'Acceso denegado';
    exit;
}

// Obtener información del archivo
$fileInfo = pathinfo($fullPath);
$extension = strtolower($fileInfo['extension']);

// Verificar que es una imagen JPG
if ($extension !== 'jpg' && $extension !== 'jpeg') {
    http_response_code(400);
    echo 'Tipo de archivo no permitido';
    exit;
}

// Configurar headers para la imagen
header('Content-Type: image/jpeg');
header('Cache-Control: public, max-age=31536000'); // Cache por 1 año
header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));

// Servir la imagen
readfile($fullPath);
?> 