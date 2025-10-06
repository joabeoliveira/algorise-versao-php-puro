<?php
// Teste simples de JSON
header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

echo json_encode([
    'status' => 'success',
    'received_data' => $data,
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'não definido',
    'method' => $_SERVER['REQUEST_METHOD']
]);
?>