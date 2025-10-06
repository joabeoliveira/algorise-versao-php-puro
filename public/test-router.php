<?php
// Teste direto do Router
session_start();
require __DIR__ . '/../vendor/autoload.php';
$settings = require __DIR__ . '/../src/settings.php';

use Joabe\Buscaprecos\Core\Router;

$router = new Router();

// Teste da rota de download
$router->get('/download-proposta/{nome_arquivo}', function($params) {
    echo "TESTE ROUTER - Parâmetros recebidos: " . json_encode($params);
});

echo "Testando rota: GET /download-proposta/6585b7d39ae6049beefaeb11a5a41330.pdf\n";

// Simula a requisição
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/download-proposta/6585b7d39ae6049beefaeb11a5a41330.pdf';

$router->run();
?>