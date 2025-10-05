<?php
/**
 * Teste de carregamento do sistema
 */

// Log de debug
function debug_log($message) {
    echo "[DEBUG] $message<br>\n";
    flush();
}

debug_log("Iniciando teste do sistema...");

// 1. Configurações básicas
ini_set('display_errors', 1);
error_reporting(E_ALL);
debug_log("Configurações de erro definidas");

// 2. Sessão
session_start();
debug_log("Sessão iniciada");

// 3. Autoloader
debug_log("Carregando autoloader...");
try {
    require __DIR__ . '/../vendor/autoload.php';
    debug_log("✅ Autoloader carregado");
} catch (Exception $e) {
    debug_log("❌ Erro no autoloader: " . $e->getMessage());
    exit;
}

// 4. Settings
debug_log("Carregando configurações...");
try {
    $settings = require __DIR__ . '/../src/settings.php';
    debug_log("✅ Configurações carregadas");
} catch (Exception $e) {
    debug_log("❌ Erro nas configurações: " . $e->getMessage());
    exit;
}

// 5. Router
debug_log("Testando Router...");
try {
    $router = new \Joabe\Buscaprecos\Core\Router();
    debug_log("✅ Router instanciado");
    
    // Adicionar rota básica
    $router->get('/', function() {
        echo json_encode(['status' => 'ok', 'message' => 'Rota funcionando']);
    });
    
    $router->get('/test', function() {
        echo json_encode(['status' => 'ok', 'message' => 'Rota de teste funcionando']);
    });
    
    debug_log("✅ Rotas adicionadas");
    
} catch (Exception $e) {
    debug_log("❌ Erro no Router: " . $e->getMessage());
    exit;
}

// 6. Controllers
debug_log("Testando Controllers...");
try {
    $userController = new \Joabe\Buscaprecos\Controller\UsuarioController();
    debug_log("✅ UsuarioController instanciado");
} catch (Exception $e) {
    debug_log("❌ Erro no UsuarioController: " . $e->getMessage());
}

// 7. Teste de execução do router
debug_log("URL atual: " . ($_SERVER['REQUEST_URI'] ?? 'N/A'));
debug_log("Método: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A'));

echo "<hr>";
echo "<h3>Sistema carregado com sucesso!</h3>";
echo "<a href='/'>Home</a> | ";
echo "<a href='/test'>Teste</a> | ";
echo "<a href='/dashboard'>Dashboard</a>";

echo "<hr>";
echo "<h4>Executar Router:</h4>";
if (isset($_GET['run'])) {
    debug_log("Executando router...");
    try {
        $router->run();
    } catch (Exception $e) {
        debug_log("❌ Erro na execução do router: " . $e->getMessage());
    }
} else {
    echo "<a href='?run=1'>Clique para executar o router</a>";
}
?>