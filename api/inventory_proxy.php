<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

function loadEnvFile($path) {
    if (!is_readable($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$lines) return;
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v);
        $v = trim($v, "\"'");
        if ($k === '') continue;
        if (getenv($k) === false) {
            putenv("$k=$v");
            $_ENV[$k] = $v;
        }
    }
}

// Permite desarrollo local con `.env.local` (NO se debe exponer en producción).
loadEnvFile(__DIR__ . '/../.env.local');
loadEnvFile(__DIR__ . '/../.env');

// --- Configuración vía variables de entorno ---
$apiUrl = getenv('INVENTORY_API_URL') ?: 'https://186.96.31.119:2121/DataSnap/Rest/TILU_ServerMethods/InventarioCompleto';
$username = getenv('INVENTORY_USERNAME') ?: '';
$password = getenv('INVENTORY_PASSWORD') ?: '';

if ($username === '' || $password === '') {
    http_response_code(500);
    echo json_encode([
        "error" => true,
        "message" => "INVENTORY_USERNAME / INVENTORY_PASSWORD no están configuradas en el servidor."
    ]);
    exit;
}

$sslVerifyRaw = getenv('INVENTORY_SSL_VERIFY');
$sslVerify = true;
if ($sslVerifyRaw !== false && $sslVerifyRaw !== '') {
    $parsed = filter_var($sslVerifyRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    if ($parsed !== null) $sslVerify = $parsed;
}

// --- Preparar Contexto de la Solicitud ---
$contextOptions = [
    'http' => [
        'method' => 'GET',
        'header' => "Authorization: Basic " . base64_encode("$username:$password") . "\r\n" .
                    "Accept: application/json, text/plain, */*\r\n" .
                    "Accept-Language: es-ES,es;q=0.9,en;q=0.8\r\n" .
                    "Accept-Encoding: gzip, deflate, br\r\n" .
                    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\n" .
                    "Connection: keep-alive\r\n" .
                    "Cache-Control: no-cache\r\n",
        'ignore_errors' => true, // Para poder leer el cuerpo de la respuesta incluso en errores HTTP
        'timeout' => 120, // Aumentar timeout a 2 minutos
    ],
    'ssl' => [
        'verify_peer' => $sslVerify,
        'verify_peer_name' => $sslVerify,
        // 'cafile' => '/path/to/your/cacert.pem', // Si tienes un CA bundle específico
    ]
];

// --- Realizar la Solicitud a la API Real ---
$context = stream_context_create($contextOptions);
$responseBody = file_get_contents($apiUrl, false, $context);

// --- Obtener el Código de Estado HTTP y Cabeceras de la API Real ---
$responseCode = 500; // Código por defecto si algo falla catastróficamente
$contentType = 'application/json; charset=utf-8'; // Tipo de contenido por defecto

if (isset($http_response_header) && is_array($http_response_header)) {
    // Extraer el código de estado HTTP
    if (preg_match('#^HTTP/\d+\.\d+ (\d{3})#', $http_response_header[0], $matches)) {
        $responseCode = intval($matches[1]);
    }
    // Buscar la cabecera Content-Type de la respuesta original (si existe)
    foreach ($http_response_header as $header) {
        if (stripos($header, 'Content-Type:') === 0) {
            $contentType = substr($header, strlen('Content-Type: '));
            break;
        }
    }
}

// --- Enviar la Respuesta al Frontend ---
// Establecer el código de estado HTTP que el proxy devolverá al frontend
http_response_code($responseCode);

// Establecer la cabecera Content-Type. Es crucial que sea application/json.
header("Content-Type: " . $contentType);
header("Access-Control-Allow-Origin: *"); // Ojo: Para desarrollo. En producción, sé específico: https://tu-frontend-dominio.com

if ($responseBody === false) {
    // Error en file_get_contents (no necesariamente un error HTTP de la API)
    http_response_code(503); // Service Unavailable (o el código que consideres apropiado)
    echo json_encode([
        "error" => true,
        "message" => "Error al conectar con el servicio de inventario.",
        "details" => error_get_last()['message'] ?? 'Detalles no disponibles'
    ]);
} else {
    // Simplemente reenvía el cuerpo de la respuesta de la API real.
    // Asumimos que la API real ya devuelve JSON. Si no, necesitarías decodificarlo y recodificarlo.
    echo $responseBody;
}

exit; // Terminar el script
?>