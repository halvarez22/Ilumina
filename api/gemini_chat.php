<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'error' => true,
        'message' => 'Método no permitido. Usa POST.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

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

// Groq API key (preferimos GROQ_API_KEY)
$apiKey = getenv('GROQ_API_KEY');
if (!$apiKey) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'GROQ_API_KEY no está configurada. Ponla en .env.local en la raíz del proyecto.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => 'Body JSON inválido.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$history = $data['history'] ?? [];
$message = $data['message'] ?? '';

if (!is_array($history)) $history = [];
if (!is_string($message) || trim($message) === '') {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => 'El campo "message" es requerido.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Construir mensajes estilo OpenAI (Groq compatible) a partir de la historia (role + parts[text]).
$messages = [];
foreach ($history as $item) {
    if (!is_array($item)) continue;
    $role = $item['role'] ?? null;
    $parts = $item['parts'] ?? null;
    if ($role !== 'user' && $role !== 'model') continue;
    if (!is_array($parts)) continue;

    $textFragments = [];
    foreach ($parts as $p) {
        if (!is_array($p)) continue;
        $t = $p['text'] ?? null;
        if (is_string($t) && $t !== '') {
            $textFragments[] = $t;
        }
    }
    if (count($textFragments) === 0) continue;

    $content = implode("\n\n", $textFragments);
    $mappedRole = $role === 'user' ? 'user' : 'assistant';

    $messages[] = [
        'role' => $mappedRole,
        'content' => $content,
    ];
}

// Añadir el último mensaje del usuario
$messages[] = [
    'role' => 'user',
    'content' => $message,
];

// Modelo de Groq. Se puede sobreescribir con GROQ_MODEL.
$model = getenv('GROQ_MODEL');
if (!$model) {
    $model = 'llama-3.3-70b-versatile';
}

$payload = [
    'model' => $model,
    'messages' => $messages,
    'temperature' => 0.9,
    'max_tokens' => 2048,
];

$url = 'https://api.groq.com/openai/v1/chat/completions';
$body = json_encode($payload, JSON_UNESCAPED_UNICODE);

function httpPostJson($url, $jsonBody, $authHeader, &$statusCodeOut) {
    $statusCodeOut = 0;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            $authHeader,
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $resp = curl_exec($ch);
        $statusCodeOut = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $resp;
    }

    $opts = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n" . $authHeader . "\r\n",
            'content' => $jsonBody,
            'ignore_errors' => true,
            'timeout' => 60,
        ]
    ];
    $ctx = stream_context_create($opts);
    $resp = file_get_contents($url, false, $ctx);

    // Intentar extraer status code de headers
    if (isset($http_response_header) && is_array($http_response_header)) {
        if (preg_match('#^HTTP/\d+\.\d+ (\d{3})#', $http_response_header[0], $m)) {
            $statusCodeOut = intval($m[1]);
        }
    }

    return $resp;
}

$authHeader = 'Authorization: Bearer ' . $apiKey;
$status = 0;
$respBody = httpPostJson($url, $body, $authHeader, $status);

if ($respBody === false || $status === 0) {
    http_response_code(503);
    echo json_encode([
        'error' => true,
        'message' => 'No se pudo conectar con Groq.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($status < 200 || $status >= 300) {
    $groqErr = json_decode($respBody, true);
    $userMessage = 'Error desde Groq.';
    if (is_array($groqErr) && isset($groqErr['error']['message'])) {
        $userMessage = $groqErr['error']['message'];
    } elseif (is_array($groqErr) && isset($groqErr['error'])) {
        $userMessage = is_string($groqErr['error']) ? $groqErr['error'] : 'Error desde Groq.';
    }
    http_response_code($status);
    echo json_encode([
        'error' => true,
        'message' => $userMessage,
        'details' => $respBody,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$respJson = json_decode($respBody, true);
if (!is_array($respJson)) {
    http_response_code(502);
    echo json_encode([
        'error' => true,
        'message' => 'Respuesta inválida desde Groq.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$text = '';
if (isset($respJson['choices'][0]['message']['content'])) {
    $text = $respJson['choices'][0]['message']['content'];
}

echo json_encode([
    'text' => $text,
], JSON_UNESCAPED_UNICODE);
exit;
?>
