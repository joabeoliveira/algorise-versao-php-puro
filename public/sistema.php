<?php
// Sistema principal simplificado - apenas para testar se funciona

session_start();

// Importa as classes necessárias
use Joabe\Buscaprecos\Core\Router;

try {
    require_once __DIR__ . '/../src/settings.php';
    
    // Cria o router
    $router = new Router();
    
    // Adiciona algumas rotas básicas para teste
    $router->get('/teste-rota', function($params) {
        echo "<h1>✅ Rota funcionando!</h1>";
        echo "<p>Parâmetros: " . print_r($params, true) . "</p>";
    });
    
    // Executa o sistema
    $router->run();
    
} catch (Exception $e) {
    echo "<h1>❌ Erro no Sistema</h1>";
    echo "<p>Erro: " . $e->getMessage() . "</p>";
    echo "<p>Arquivo: " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<hr>";
    echo "<p><a href='/debug.php'>🔍 Ir para Debug</a></p>";
    echo "<p><a href='/login-simples.php'>🔑 Login Simples</a></p>";
}
?>