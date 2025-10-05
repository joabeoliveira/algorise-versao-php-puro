<?php
// Teste ultra-simples do PHP puro

echo "<h1>🚀 Teste Básico PHP Puro</h1>";

// Teste 1: Autoloader
try {
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "<p>✅ Autoloader carregado</p>";
} catch (Exception $e) {
    echo "<p>❌ Erro autoloader: " . $e->getMessage() . "</p>";
}

// Teste 2: Dotenv
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad(); // usa safeLoad para não dar erro se já carregou
    echo "<p>✅ Dotenv carregado</p>";
} catch (Exception $e) {
    echo "<p>❌ Erro dotenv: " . $e->getMessage() . "</p>";
}

// Teste 3: Classes próprias
try {
    if (class_exists('Joabe\\Buscaprecos\\Core\\Router')) {
        echo "<p>✅ Router encontrado</p>";
    } else {
        echo "<p>❌ Router não encontrado</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erro Router: " . $e->getMessage() . "</p>";
}

echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Current Time: " . date('Y-m-d H:i:s') . "</p>";

// Teste da página principal
echo '<p><a href="/" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🏠 Testar Página Principal</a></p>';
?>