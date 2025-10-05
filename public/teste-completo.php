<?php
/**
 * Arquivo de entrada para testar rotas diretamente
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🚀 Teste do Sistema PHP Puro</h1>";

// 1. Informações da requisição
echo "<h2>📊 Informações da Requisição</h2>";
echo "<strong>URL:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "<br>";
echo "<strong>Método:</strong> " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "<br>";
echo "<strong>Path:</strong> " . parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) . "<br>";

// 2. Testar autoload
echo "<h2>📦 Teste do Autoload</h2>";
require_once __DIR__ . '/../vendor/autoload.php';
echo "✅ Autoloader carregado<br>";

// 3. Testar configurações
echo "<h2>⚙️ Teste das Configurações</h2>";
try {
    $settings = require_once __DIR__ . '/../src/settings.php';
    echo "✅ Settings carregado<br>";
} catch (Exception $e) {
    echo "❌ Erro no settings: " . $e->getMessage() . "<br>";
}

// 4. Testar Router
echo "<h2>🛣️ Teste do Router</h2>";
try {
    $routerClass = 'Joabe\Buscaprecos\Core\Router';
    if (class_exists($routerClass)) {
        $router = new $routerClass();
        echo "✅ Router instanciado<br>";
        
        // Adicionar rota de teste
        $router->get('/teste-rota', function() {
            echo json_encode(['status' => 'sucesso', 'rota' => 'funcionando']);
        });
        
        echo "✅ Rota de teste adicionada<br>";
    } else {
        echo "❌ Classe Router não encontrada<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro no router: " . $e->getMessage() . "<br>";
}

// 5. Testar Controllers
echo "<h2>🎮 Teste dos Controllers</h2>";
try {
    $userController = 'Joabe\Buscaprecos\Controller\UsuarioController';
    $dashController = 'Joabe\Buscaprecos\Controller\DashboardController';
    
    if (class_exists($userController) && class_exists($dashController)) {
        echo "✅ Controllers carregados<br>";
    } else {
        echo "❌ Controllers não encontrados<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro nos controllers: " . $e->getMessage() . "<br>";
}

// 6. Testar Banco
echo "<h2>💾 Teste do Banco de Dados</h2>";
try {
    $pdo = getDbConnection();
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
    $count = $stmt->fetchColumn();
    echo "✅ Conexão OK - $count usuários encontrados<br>";
} catch (Exception $e) {
    echo "❌ Erro no banco: " . $e->getMessage() . "<br>";
}

echo "<h2>🔗 Links de Teste</h2>";
echo "<a href='/' style='margin:5px;padding:10px;background:blue;color:white;text-decoration:none;'>Home</a>";
echo "<a href='/dashboard' style='margin:5px;padding:10px;background:green;color:white;text-decoration:none;'>Dashboard</a>";
echo "<a href='/login' style='margin:5px;padding:10px;background:orange;color:white;text-decoration:none;'>Login</a>";
echo "<a href='/processos' style='margin:5px;padding:10px;background:purple;color:white;text-decoration:none;'>Processos</a>";

echo "<h2>🧪 Executar Router de Teste</h2>";
if (isset($_GET['executar'])) {
    echo "Executando router...<br>";
    try {
        $router->run();
    } catch (Exception $e) {
        echo "❌ Erro na execução: " . $e->getMessage() . "<br>";
    }
} else {
    echo "<a href='?executar=1'>Clique para executar o router</a>";
}
?>