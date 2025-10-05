<?php
/**
 * Teste de Conexão com Banco - XAMPP
 */

// Configurações de exibição de erros
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 Teste de Configuração XAMPP</h1>";

echo "<h2>1. Testando arquivo .env</h2>";

// Carrega o .env
if (file_exists(__DIR__ . '/../.env')) {
    $envContent = file_get_contents(__DIR__ . '/../.env');
    echo "✅ Arquivo .env encontrado<br>";
    
    // Extrai configurações do banco
    preg_match('/DB_HOST=(.*)/', $envContent, $hostMatch);
    preg_match('/DB_DATABASE=(.*)/', $envContent, $dbMatch);
    preg_match('/DB_USER=(.*)/', $envContent, $userMatch);
    preg_match('/DB_PASSWORD=(.*)/', $envContent, $passMatch);
    
    $host = trim($hostMatch[1] ?? '');
    $dbname = trim($dbMatch[1] ?? '');
    $username = trim($userMatch[1] ?? '');
    $password = trim($passMatch[1] ?? '');
    
    echo "📝 Configurações encontradas:<br>";
    echo "- Host: '$host'<br>";
    echo "- Database: '$dbname'<br>";
    echo "- User: '$username'<br>";
    echo "- Password: " . ($password ? "'***'" : "'(vazio)'") . "<br><br>";
    
} else {
    echo "❌ Arquivo .env não encontrado<br>";
    exit;
}

echo "<h2>2. Testando Conexão MySQL</h2>";

try {
    $dsn = "mysql:host=$host;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✅ Conexão com MySQL estabelecida<br>";
    
    // Verifica se o banco existe
    $stmt = $pdo->query("SHOW DATABASES LIKE '$dbname'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Banco '$dbname' encontrado<br>";
        
        // Conecta ao banco específico
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        // Lista tabelas
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "✅ " . count($tables) . " tabelas encontradas: " . implode(', ', $tables) . "<br>";
            
            // Verifica usuários
            if (in_array('usuarios', $tables)) {
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
                $count = $stmt->fetch()['total'];
                echo "👥 $count usuários cadastrados<br>";
            }
        } else {
            echo "⚠️ Nenhuma tabela encontrada. Execute o arquivo SQL para criar as tabelas.<br>";
        }
        
    } else {
        echo "❌ Banco '$dbname' não encontrado<br>";
        echo "📝 <strong>Para criar o banco:</strong><br>";
        echo "1. Abra o phpMyAdmin (http://localhost/phpmyadmin)<br>";
        echo "2. Clique em 'Novo' na lateral esquerda<br>";
        echo "3. Digite 'algorise' como nome do banco<br>";
        echo "4. Clique em 'Criar'<br>";
        echo "5. Importe o arquivo SQL do projeto<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Erro na conexão: " . $e->getMessage() . "<br>";
    
    echo "<h3>🔧 Possíveis Soluções:</h3>";
    echo "<ol>";
    echo "<li><strong>XAMPP não iniciado:</strong> Verifique se Apache e MySQL estão rodando no XAMPP Control Panel</li>";
    echo "<li><strong>Senha do MySQL:</strong> Se você definiu uma senha para o root, atualize DB_PASSWORD no .env</li>";
    echo "<li><strong>Porta diferente:</strong> Se MySQL estiver em porta diferente de 3306, atualize DB_PORT</li>";
    echo "</ol>";
}

echo "<h2>3. Verificação do phpMyAdmin</h2>";
echo "<p>📊 Acesse o phpMyAdmin: <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></p>";
echo "<p>Use as credenciais:</p>";
echo "<ul>";
echo "<li><strong>Usuário:</strong> $username</li>";
echo "<li><strong>Senha:</strong> " . ($password ? $password : "(deixe vazio)") . "</li>";
echo "</ul>";

echo "<h2>4. Status dos Serviços XAMPP</h2>";
echo "<p>Certifique-se de que os seguintes serviços estão rodando no XAMPP:</p>";
echo "<ul>";
echo "<li>✅ Apache (para servir o PHP)</li>";
echo "<li>✅ MySQL (para o banco de dados)</li>";
echo "</ul>";

echo "<hr>";
echo "<h3>🎯 Próximo Passo</h3>";
echo "<p>Se tudo estiver OK acima, <a href='./'>teste a aplicação principal</a></p>";
?>