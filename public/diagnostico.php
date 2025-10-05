<?php
/**
 * Diagnóstico de URLs e Roteamento
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Diagnóstico de URLs - XAMPP</h1>";

echo "<h2>1. Informações do Servidor</h2>";
echo "<ul>";
echo "<li><strong>Servidor:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Não definido') . "</li>";
echo "<li><strong>PHP:</strong> " . phpversion() . "</li>";
echo "<li><strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Não definido') . "</li>";
echo "<li><strong>Script Name:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'Não definido') . "</li>";
echo "<li><strong>Request URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'Não definido') . "</li>";
echo "</ul>";

echo "<h2>2. Testando URLs</h2>";

$baseUrl = 'http://localhost/algorise-versao-php-puro/public';
$urls = [
    'Raiz do projeto' => $baseUrl,
    'Index direto' => $baseUrl . '/index.php',
    'Test PHP' => $baseUrl . '/test.php',
    'Teste ambiente' => $baseUrl . '/teste-ambiente.php',
    'Diagnóstico (este arquivo)' => $baseUrl . '/diagnostico.php',
];

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<thead><tr style='background: #f0f0f0;'><th style='padding: 10px;'>Descrição</th><th style='padding: 10px;'>URL</th><th style='padding: 10px;'>Teste</th></tr></thead>";

foreach ($urls as $desc => $url) {
    echo "<tr>";
    echo "<td style='padding: 10px;'>$desc</td>";
    echo "<td style='padding: 10px;'><code>$url</code></td>";
    echo "<td style='padding: 10px;'><a href='$url' target='_blank'>🔗 Testar</a></td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>3. Verificações do Apache</h2>";

// Verificar se mod_rewrite está ativo
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    if (in_array('mod_rewrite', $modules)) {
        echo "✅ <strong>mod_rewrite:</strong> Ativo<br>";
    } else {
        echo "❌ <strong>mod_rewrite:</strong> Inativo<br>";
    }
} else {
    echo "⚠️ <strong>apache_get_modules():</strong> Função não disponível<br>";
}

// Verificar .htaccess
if (file_exists(__DIR__ . '/.htaccess')) {
    echo "✅ <strong>.htaccess:</strong> Encontrado<br>";
    echo "<details style='margin: 10px 0;'>";
    echo "<summary>Ver conteúdo do .htaccess</summary>";
    echo "<pre style='background: #f4f4f4; padding: 10px; margin: 10px 0;'>";
    echo htmlspecialchars(file_get_contents(__DIR__ . '/.htaccess'));
    echo "</pre>";
    echo "</details>";
} else {
    echo "❌ <strong>.htaccess:</strong> Não encontrado<br>";
}

echo "<h2>4. Estrutura de Arquivos</h2>";
$files = scandir(__DIR__);
echo "<p><strong>Arquivos no diretório public:</strong></p>";
echo "<ul>";
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        $isDir = is_dir(__DIR__ . '/' . $file) ? '📁' : '📄';
        echo "<li>$isDir $file</li>";
    }
}
echo "</ul>";

echo "<h2>5. Soluções Alternativas</h2>";

echo "<div style='background: #e7f3ff; padding: 15px; margin: 20px 0; border-left: 4px solid #2196F3;'>";
echo "<h3>💡 URLs que devem funcionar:</h3>";
echo "<ul>";
echo "<li><strong>Aplicação principal:</strong> <a href='$baseUrl/index.php'>$baseUrl/index.php</a></li>";
echo "<li><strong>Login direto:</strong> <a href='$baseUrl/login-simples.php'>$baseUrl/login-simples.php</a></li>";
echo "<li><strong>Sistema simples:</strong> <a href='$baseUrl/sistema.php'>$baseUrl/sistema.php</a></li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #fff3cd; padding: 15px; margin: 20px 0; border-left: 4px solid #ffc107;'>";
echo "<h3>⚠️ Se ainda não funcionar:</h3>";
echo "<ol>";
echo "<li><strong>Reinicie o Apache</strong> no XAMPP Control Panel</li>";
echo "<li><strong>Verifique se mod_rewrite está ativo</strong> em httpd.conf</li>";
echo "<li><strong>Use URLs diretas</strong> com .php no final</li>";
echo "<li><strong>Teste sem .htaccess</strong> temporariamente</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p><strong>Arquivo atual:</strong> " . __FILE__ . "</p>";
echo "<p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>