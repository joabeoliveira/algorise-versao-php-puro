<?php
/**
 * Teste completo do ambiente de desenvolvimento
 */

// Configurações de exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🚀 Teste do Ambiente - Algorise PHP Puro</h1>";

// Carregamento do autoloader
echo "<h2>1. Testando Autoloader</h2>";
try {
    require __DIR__ . '/../vendor/autoload.php';
    echo "✅ Autoloader carregado com sucesso<br>";
} catch (Exception $e) {
    echo "❌ Erro no autoloader: " . $e->getMessage() . "<br>";
}

// Carregamento das configurações
echo "<h2>2. Testando Configurações</h2>";
try {
    $settings = require __DIR__ . '/../src/settings.php';
    echo "✅ Configurações carregadas<br>";
    echo "📝 Configurações de banco:<br>";
    echo "- Host: " . ($settings['db']['host'] ?? 'não definido') . "<br>";
    echo "- Database: " . ($settings['db']['dbname'] ?? 'não definido') . "<br>";
    echo "- User: " . ($settings['db']['user'] ?? 'não definido') . "<br>";
} catch (Exception $e) {
    echo "❌ Erro ao carregar configurações: " . $e->getMessage() . "<br>";
}

// Teste de conexão com MySQL
echo "<h2>3. Testando Conexão MySQL</h2>";
try {
    $host = $settings['db']['host'] ?? '127.0.0.1';
    $dbname = $settings['db']['dbname'] ?? 'algorise';
    $username = $settings['db']['user'] ?? 'joabe';
    $password = $settings['db']['pass'] ?? '114211';
    
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "✅ Conexão MySQL estabelecida com sucesso<br>";
    
    // Testar se as tabelas existem
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "📋 Tabelas encontradas: " . count($tables) . "<br>";
    echo "🗂️ Lista de tabelas: " . implode(', ', $tables) . "<br>";
    
    // Verificar se existe usuário na tabela
    if (in_array('usuarios', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
        $count = $stmt->fetch()['total'];
        echo "👥 Usuários cadastrados: $count<br>";
        
        if ($count > 0) {
            $stmt = $pdo->query("SELECT id, nome, email FROM usuarios LIMIT 1");
            $user = $stmt->fetch();
            echo "👤 Exemplo de usuário: {$user['nome']} ({$user['email']})<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erro na conexão MySQL: " . $e->getMessage() . "<br>";
}

// Teste das classes do sistema
echo "<h2>4. Testando Classes do Sistema</h2>";
$classes = [
    'Joabe\Buscaprecos\Core\Router',
    'Joabe\Buscaprecos\Controller\DashboardController',
    'Joabe\Buscaprecos\Controller\UsuarioController'
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "✅ Classe $class carregada<br>";
    } else {
        echo "❌ Classe $class não encontrada<br>";
    }
}

// Informações do PHP
echo "<h2>5. Informações do Ambiente PHP</h2>";
echo "📌 Versão PHP: " . phpversion() . "<br>";
echo "📌 Extensões necessárias:<br>";

$extensoes = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'curl'];
foreach ($extensoes as $ext) {
    if (extension_loaded($ext)) {
        echo "  ✅ $ext<br>";
    } else {
        echo "  ❌ $ext (não instalada)<br>";
    }
}

// Informações de diretórios
echo "<h2>6. Verificação de Diretórios</h2>";
$dirs = [
    'Vendor' => __DIR__ . '/../vendor',
    'Source' => __DIR__ . '/../src',
    'Views' => __DIR__ . '/../src/View',
    'Controllers' => __DIR__ . '/../src/Controller',
    'Storage' => __DIR__ . '/../storage',
    'Propostas' => __DIR__ . '/../storage/propostas'
];

foreach ($dirs as $name => $dir) {
    if (is_dir($dir)) {
        echo "✅ $name: $dir<br>";
    } else {
        echo "❌ $name: $dir (não encontrado)<br>";
    }
}

echo "<hr>";
echo "<h2>🎯 Próximos Passos</h2>";
echo "<p>Se todos os testes acima passaram, você pode:</p>";
echo "<ol>";
echo "<li>Acessar <a href='./index.php'>a aplicação principal</a></li>";
echo "<li>Fazer <a href='./login-simples.php'>login no sistema</a></li>";
echo "<li>Ver o <a href='./debug-sistema.php'>debug do sistema</a></li>";
echo "</ol>";

echo "<p><strong>URL do sistema:</strong> <a href='http://localhost/algorise-versao-php-puro/public/'>http://localhost/algorise-versao-php-puro/public/</a></p>";
?>