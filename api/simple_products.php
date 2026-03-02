<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Productos de prueba simples
$products = [
    [
        'id' => 1,
        'sku' => 'ACBOARD2L20WNW',
        'name' => 'ACBOARD2L20WNW',
        'description' => 'Producto de iluminación LED - ACBOARD2L20WNW',
        'price' => 150.00,
        'category' => 'Lamparas',
        'image' => 'ACBOARD2L20WNW.JPG',
        'stock' => 50,
        'rating' => 4.5,
        'reviews' => 25
    ],
    [
        'id' => 2,
        'sku' => 'ACBOARD2L20WW',
        'name' => 'ACBOARD2L20WW',
        'description' => 'Producto de iluminación LED - ACBOARD2L20WW',
        'price' => 160.00,
        'category' => 'Lamparas',
        'image' => 'ACBOARD2L20WW.JPG',
        'stock' => 45,
        'rating' => 4.3,
        'reviews' => 18
    ],
    [
        'id' => 3,
        'sku' => 'ACBOARD2L20WWW',
        'name' => 'ACBOARD2L20WWW',
        'description' => 'Producto de iluminación LED - ACBOARD2L20WWW',
        'price' => 170.00,
        'category' => 'Lamparas',
        'image' => 'ACBOARD2L20WWW.JPG',
        'stock' => 40,
        'rating' => 4.7,
        'reviews' => 32
    ]
];

echo json_encode([
    'success' => true,
    'products' => $products,
    'total' => count($products)
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?> 