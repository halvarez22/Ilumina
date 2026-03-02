<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$testData = [
    [
        'SKU' => 'ACBOARD2L20WNW',
        'Precio' => 323.51
    ],
    [
        'SKU' => 'ACBOARD2L20WW',
        'Precio' => 323.51
    ],
    [
        'SKU' => 'ACBOARD50NW',
        'Precio' => 236.40
    ]
];

echo json_encode($testData);
?> 