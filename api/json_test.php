<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$data = ['test' => 'value'];
$json = json_encode($data);

echo $json;
?> 