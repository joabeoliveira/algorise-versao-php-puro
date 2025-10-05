<?php
/**
 * Debug - Visualizar usuários cadastrados
 */

// Configurações de exibição de erros
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>👥 Usuários Cadastrados no Sistema</h1>";

try {
    // Carrega configurações
    $settings = require __DIR__ . '/../src/settings.php';
    
    // Conecta ao banco
    $host = $settings['db']['host'];
    $dbname = $settings['db']['dbname'];
    $username = $settings['db']['user'];
    $password = $settings['db']['pass'];
    
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Busca usuários
    $stmt = $pdo->query("SELECT id, nome, email, role, ativo, created_at FROM usuarios ORDER BY id");
    $usuarios = $stmt->fetchAll();
    
    if (empty($usuarios)) {
        echo "<p>❌ <strong>Nenhum usuário encontrado!</strong></p>";
        echo "<p>Você precisa criar um usuário para acessar o sistema.</p>";
        echo "<h3>Para criar um usuário:</h3>";
        echo "<ol>";
        echo "<li>Execute o SQL abaixo no phpMyAdmin ou MySQL:</li>";
        echo "</ol>";
        
        echo "<div style='background: #f4f4f4; padding: 10px; border: 1px solid #ddd; margin: 10px 0;'>";
        echo "<code>";
        echo "INSERT INTO usuarios (nome, email, password_hash, role, ativo) VALUES <br>";
        echo "('Admin', 'admin@algorise.com', '" . password_hash('123456', PASSWORD_DEFAULT) . "', 'admin', 1);";
        echo "</code>";
        echo "</div>";
        
        echo "<p><strong>Credenciais:</strong></p>";
        echo "<ul>";
        echo "<li><strong>Email:</strong> admin@algorise.com</li>";
        echo "<li><strong>Senha:</strong> 123456</li>";
        echo "</ul>";
        
    } else {
        echo "<p>✅ <strong>" . count($usuarios) . " usuário(s) encontrado(s)</strong></p>";
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
        echo "<thead>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th style='padding: 10px;'>ID</th>";
        echo "<th style='padding: 10px;'>Nome</th>";
        echo "<th style='padding: 10px;'>Email</th>";
        echo "<th style='padding: 10px;'>Role</th>";
        echo "<th style='padding: 10px;'>Ativo</th>";
        echo "<th style='padding: 10px;'>Criado em</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        
        foreach ($usuarios as $user) {
            $ativo = $user['ativo'] ? '✅ Sim' : '❌ Não';
            $role = ucfirst($user['role']);
            echo "<tr>";
            echo "<td style='padding: 10px; text-align: center;'>{$user['id']}</td>";
            echo "<td style='padding: 10px;'>{$user['nome']}</td>";
            echo "<td style='padding: 10px;'>{$user['email']}</td>";
            echo "<td style='padding: 10px; text-align: center;'>{$role}</td>";
            echo "<td style='padding: 10px; text-align: center;'>{$ativo}</td>";
            echo "<td style='padding: 10px; text-align: center;'>{$user['created_at']}</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        
        echo "<h3>🔐 Teste de Login</h3>";
        echo "<p>Use as credenciais acima para fazer login no sistema:</p>";
        echo "<p><a href='./login-simples.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Testar Login</a></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Erro:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='./teste-ambiente.php'>← Voltar ao teste de ambiente</a></p>";
echo "<p><a href='./'>← Ir para a aplicação</a></p>";
?>