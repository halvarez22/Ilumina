<?php
// Configuración de MIME types para archivos TypeScript y React
function setMimeTypes() {
    $mimeTypes = [
        '.js' => 'application/javascript',
        '.jsx' => 'application/javascript',
        '.ts' => 'application/javascript',
        '.tsx' => 'application/javascript',
        '.css' => 'text/css',
        '.json' => 'application/json',
        '.html' => 'text/html',
        '.svg' => 'image/svg+xml',
        '.png' => 'image/png',
        '.jpg' => 'image/jpeg',
        '.jpeg' => 'image/jpeg',
        '.gif' => 'image/gif',
        '.webp' => 'image/webp',
        '.woff' => 'font/woff',
        '.woff2' => 'font/woff2',
        '.ttf' => 'font/ttf',
        '.otf' => 'font/otf',
        '.mp3' => 'audio/mpeg',
        '.mp4' => 'video/mp4'
    ];
    
    foreach ($mimeTypes as $extension => $mimeType) {
        if (function_exists('mime_content_type')) {
            // Configurar MIME types si la función está disponible
            putenv("MIME_TYPE_$extension=$mimeType");
        }
    }
}

// Ejecutar la configuración
setMimeTypes();
?> 