<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Simular productos reales con SKUs que existen en las imágenes y categorías del CSV
$mockProducts = [
    // Lámparas
    [
        'SKU' => 'ACBOARD2L20WNW',
        'Titulo' => 'Lámpara Colgante ACBOARD2L20WNW',
        'Descripcion' => 'Elegante lámpara colgante con acabado en oro cepillado',
        'Stock' => '15',
        'Cve_sat' => 'Lamparas',
        'Img1' => 'https://picsum.photos/seed/ACBOARD2L20WNW/600/400'
    ],
    [
        'SKU' => 'ACBOARD2L20WW',
        'Titulo' => 'Lámpara Colgante ACBOARD2L20WW',
        'Descripcion' => 'Lámpara colgante moderna con luz cálida',
        'Stock' => '12',
        'Cve_sat' => 'Lamparas',
        'Img1' => 'https://picsum.photos/seed/ACBOARD2L20WW/600/400'
    ],
    [
        'SKU' => 'ACBOARD50NW',
        'Titulo' => 'Lámpara de Techo ACBOARD50NW',
        'Descripcion' => 'Lámpara de techo moderna con luz natural',
        'Stock' => '8',
        'Cve_sat' => 'Lamparas',
        'Img1' => 'https://picsum.photos/seed/ACBOARD50NW/600/400'
    ],
    
    // Tiras de Leds
    [
        'SKU' => 'ACCAB4WTLRGB',
        'Titulo' => 'Tira LED RGB ACCAB4WTLRGB',
        'Descripcion' => 'Tira LED RGB de 4W con control remoto',
        'Stock' => '25',
        'Cve_sat' => 'Tiras de Leds',
        'Img1' => 'https://picsum.photos/seed/ACCAB4WTLRGB/600/400'
    ],
    [
        'SKU' => 'ACCABTLCCT',
        'Titulo' => 'Tira LED CCT ACCABTLCCT',
        'Descripcion' => 'Tira LED con temperatura de color ajustable',
        'Stock' => '30',
        'Cve_sat' => 'Tiras de Leds',
        'Img1' => 'https://picsum.photos/seed/ACCABTLCCT/600/400'
    ],
    [
        'SKU' => 'ACCON9MM',
        'Titulo' => 'Conexión 9MM ACCON9MM',
        'Descripcion' => 'Conexión de 9mm para tiras LED',
        'Stock' => '50',
        'Cve_sat' => 'Tiras de Leds',
        'Img1' => 'https://picsum.photos/seed/ACCON9MM/600/400'
    ],
    [
        'SKU' => 'ACCONTL28355050',
        'Titulo' => 'Tira LED 2835 5050 ACCONTL28355050',
        'Descripcion' => 'Tira LED con chips 2835 y 5050',
        'Stock' => '20',
        'Cve_sat' => 'Tiras de Leds',
        'Img1' => 'https://picsum.photos/seed/ACCONTL28355050/600/400'
    ],
    
    // Iluminación Lineal
    [
        'SKU' => 'ACDIFILUDXA1812',
        'Titulo' => 'Difusor Lineal ACDIFILUDXA1812',
        'Descripcion' => 'Difusor lineal para iluminación LED',
        'Stock' => '18',
        'Cve_sat' => 'Iluminacion Lineal',
        'Img1' => 'https://picsum.photos/seed/ACDIFILUDXA1812/600/400'
    ],
    [
        'SKU' => 'ACLECHDXAP01',
        'Titulo' => 'Lámpara Lineal ACLECHDXAP01',
        'Descripcion' => 'Lámpara lineal empotrable',
        'Stock' => '22',
        'Cve_sat' => 'Iluminacion Lineal',
        'Img1' => 'https://picsum.photos/seed/ACLECHDXAP01/600/400'
    ],
    [
        'SKU' => 'ACLECHDXAP02',
        'Titulo' => 'Lámpara Lineal ACLECHDXAP02',
        'Descripcion' => 'Lámpara lineal empotrable versión 2',
        'Stock' => '15',
        'Cve_sat' => 'Iluminacion Lineal',
        'Img1' => 'https://picsum.photos/seed/ACLECHDXAP02/600/400'
    ],
    
    // Drivers y Controladores
    [
        'SKU' => 'ACCONCCT127V',
        'Titulo' => 'Driver CCT 127V ACCONCCT127V',
        'Descripcion' => 'Driver para control de temperatura de color',
        'Stock' => '10',
        'Cve_sat' => 'Drivers y Controladores',
        'Img1' => 'https://picsum.photos/seed/ACCONCCT127V/600/400'
    ],
    [
        'SKU' => 'CP100W',
        'Titulo' => 'Driver 100W CP100W',
        'Descripcion' => 'Driver LED de 100W',
        'Stock' => '8',
        'Cve_sat' => 'Drivers y Controladores',
        'Img1' => 'https://picsum.photos/seed/CP100W/600/400'
    ],
    [
        'SKU' => 'CP150W',
        'Titulo' => 'Driver 150W CP150W',
        'Descripcion' => 'Driver LED de 150W',
        'Stock' => '6',
        'Cve_sat' => 'Drivers y Controladores',
        'Img1' => 'https://picsum.photos/seed/CP150W/600/400'
    ],
    
    // Rieles
    [
        'SKU' => 'BASINFMICROTB',
        'Titulo' => 'Riel Micro TB BASINFMICROTB',
        'Descripcion' => 'Riel micro para iluminación',
        'Stock' => '35',
        'Cve_sat' => 'Rieles',
        'Img1' => 'https://picsum.photos/seed/BASINFMICROTB/600/400'
    ],
    [
        'SKU' => 'CANOTRACPC100B',
        'Titulo' => 'Riel PC 100B CANOTRACPC100B',
        'Descripcion' => 'Riel PC de 100mm',
        'Stock' => '28',
        'Cve_sat' => 'Rieles',
        'Img1' => 'https://picsum.photos/seed/CANOTRACPC100B/600/400'
    ],
    [
        'SKU' => 'COP1015B90FR',
        'Titulo' => 'Cople 90° COP1015B90FR',
        'Descripcion' => 'Cople de 90 grados para rieles',
        'Stock' => '40',
        'Cve_sat' => 'Rieles',
        'Img1' => 'https://picsum.photos/seed/COP1015B90FR/600/400'
    ],
    
    // Línea Europea / Decorativa
    [
        'SKU' => 'BASE90283X1000D',
        'Titulo' => 'Base Europea BASE90283X1000D',
        'Descripcion' => 'Base decorativa de línea europea',
        'Stock' => '12',
        'Cve_sat' => 'Linea Europea / Decorativa',
        'Img1' => 'https://picsum.photos/seed/BASE90283X1000D/600/400'
    ],
    [
        'SKU' => 'BASE90284X1000D',
        'Titulo' => 'Base Europea BASE90284X1000D',
        'Descripcion' => 'Base decorativa de línea europea versión 2',
        'Stock' => '10',
        'Cve_sat' => 'Linea Europea / Decorativa',
        'Img1' => 'https://picsum.photos/seed/BASE90284X1000D/600/400'
    ],
    
    // Iluminación Modular
    [
        'SKU' => 'CONINTB150CMBB',
        'Titulo' => 'Conexión Modular CONINTB150CMBB',
        'Descripcion' => 'Conexión para iluminación modular',
        'Stock' => '25',
        'Cve_sat' => 'Iluminacion Modular',
        'Img1' => 'https://picsum.photos/seed/CONINTB150CMBB/600/400'
    ],
    
    // Producto sin imagen (para probar fallback)
    [
        'SKU' => 'INVALID_SKU',
        'Titulo' => 'Producto Sin Imagen',
        'Descripcion' => 'Este producto no tiene imagen local',
        'Stock' => '10',
        'Cve_sat' => 'Varios',
        'Img1' => 'https://picsum.photos/seed/INVALID_SKU/600/400'
    ]
];

$response = [
    'Inventario' => $mockProducts
];

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?> 