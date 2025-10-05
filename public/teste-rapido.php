<?php
echo "<h1>🎯 Teste Específico - Pasta Public</h1>";
echo "<p><strong>Arquivo:</strong> " . __FILE__ . "</p>";
echo "<p><strong>URL atual:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>";

echo "<hr>";
echo "<h2>🔗 URLs para testar:</h2>";
echo "<ul>";
echo "<li><a href='./index.php'>📄 index.php (principal)</a></li>";
echo "<li><a href='./teste-ambiente.php'>🧪 teste-ambiente.php</a></li>";
echo "<li><a href='./diagnostico.php'>🔍 diagnostico.php</a></li>";
echo "<li><a href='./login-simples.php'>🔐 login-simples.php</a></li>";
echo "</ul>";

echo "<hr>";
echo "<h2>📁 Arquivos na pasta public:</h2>";
$files = scandir(__DIR__);
echo "<ul>";
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..' && substr($file, -4) === '.php') {
        echo "<li><a href='./$file'>$file</a></li>";
    }
}
echo "</ul>";
?>