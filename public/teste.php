<?php
// Teste básico do sistema PHP puro
use Joabe\Buscaprecos\Core\Router;
use Joabe\Buscaprecos\Core\Http;
use Joabe\Buscaprecos\Core\Mail;

echo "<h1>🔍 Teste do Sistema PHP Puro</h1>";

echo "<h2>1. Testando Autoloader</h2>";
try {
    require __DIR__ . '/../src/settings.php';
    echo "✅ settings.php carregado com sucesso<br>";
} catch (Exception $e) {
    echo "❌ Erro no settings.php: " . $e->getMessage() . "<br>";
}

echo "<h2>2. Testando Classes Core</h2>";
try {
    $router = new Router();
    echo "✅ Router instanciado<br>";
} catch (Exception $e) {
    echo "❌ Erro no Router: " . $e->getMessage() . "<br>";
}

try {
    $response = Http::get('https://httpbin.org/json');
    if ($response['success']) {
        echo "✅ Http funcionando<br>";
    } else {
        echo "⚠️ Http com problemas<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro no Http: " . $e->getMessage() . "<br>";
}

try {
    $mail = new Mail();
    echo "✅ Mail instanciado<br>";
} catch (Exception $e) {
    echo "❌ Erro no Mail: " . $e->getMessage() . "<br>";
}

echo "<h2>3. Testando Conexão com Banco</h2>";
try {
    $pdo = getDbConnection();
    echo "✅ Conexão com banco estabelecida<br>";
    
    // Testa uma query simples
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    if ($result['test'] == 1) {
        echo "✅ Query de teste funcionou<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro no banco: " . $e->getMessage() . "<br>";
    echo "💡 Configuração atual: HOST=" . ($_ENV['DB_HOST'] ?? 'não definido') . "<br>";
}

echo "<h2>4. Testando Sessões</h2>";
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['test'] = 'funcionando';
    echo "✅ Sessões funcionando<br>";
} catch (Exception $e) {
    echo "❌ Erro nas sessões: " . $e->getMessage() . "<br>";
}

echo "<h2>5. Informações do Sistema</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Timezone: " . date_default_timezone_get() . "<br>";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "<br>";

echo "<hr>";
echo "<p><strong>Se todos os testes passaram, o sistema está pronto!</strong></p>";
echo '<p><a href="/login">➡️ Ir para Login</a></p>';
echo '<p><a href="/">🏠 Página Inicial</a></p>';
?>