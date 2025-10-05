<?php
// Servidor simples para debug - sem sistema de rotas complexo

echo "🔍 Debug do Sistema PHP Puro<br><br>";

// 1. Verificar se o autoloader funciona
try {
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "✅ Autoloader OK<br>";
} catch (Exception $e) {
    echo "❌ Autoloader Error: " . $e->getMessage() . "<br>";
}

// 2. Verificar se o .env funciona
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();
    echo "✅ Dotenv OK<br>";
} catch (Exception $e) {
    echo "❌ Dotenv Error: " . $e->getMessage() . "<br>";
}

// 3. Verificar configurações básicas
echo "PHP Version: " . phpversion() . "<br>";
echo "Current Time: " . date('Y-m-d H:i:s') . "<br>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";

// 4. Verificar se as classes existem
$classes = [
    'Joabe\\Buscaprecos\\Core\\Router',
    'Joabe\\Buscaprecos\\Core\\Http', 
    'Joabe\\Buscaprecos\\Core\\Mail'
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "✅ Classe $class OK<br>";
    } else {
        echo "❌ Classe $class não encontrada<br>";
    }
}

echo "<br><hr><br>";

// 5. Menu de navegação simples
echo "<h3>🔗 Links de Teste:</h3>";
echo '<a href="/debug.php" style="display:block; margin:5px 0;">🔄 Recarregar Debug</a>';
echo '<a href="/sistema.php" style="display:block; margin:5px 0;">🏠 Sistema Principal</a>';
echo '<a href="/login-simples.php" style="display:block; margin:5px 0;">🔑 Login Simples</a>';

?>