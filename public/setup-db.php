<?php
/**
 * Importação rápida do banco - Via PHP sem MySQL externo
 */

echo "🚀 IMPORTANDO BANCO DE DADOS...<br><br>";

// Configurações
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'buscaprecos';

// Tentar diferentes portas
$ports = [3306, 3307, 33060];
$connected = false;
$pdo = null;

foreach ($ports as $port) {
    try {
        echo "Tentando conectar na porta $port...<br>";
        $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        echo "✅ Conectado na porta $port!<br>";
        $connected = true;
        break;
    } catch (Exception $e) {
        echo "❌ Porta $port falhou: " . $e->getMessage() . "<br>";
    }
}

if (!$connected) {
    echo "<h2>❌ MYSQL NÃO ESTÁ RODANDO</h2>";
    echo "<p>Para resolver:</p>";
    echo "<ol>";
    echo "<li>Abra o XAMPP Control Panel</li>";
    echo "<li>Clique em 'Start' no MySQL</li>";
    echo "<li>Volte aqui e recarregue a página</li>";
    echo "</ol>";
    echo "<p><a href='javascript:location.reload()'>🔄 Tentar novamente</a></p>";
    exit;
}

try {
    // Criar banco
    echo "<br>Criando banco '$database'...<br>";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$database`");
    echo "✅ Banco criado/selecionado<br>";
    
    // Ler e executar SQL
    echo "<br>Lendo SQL...<br>";
    $sql = file_get_contents(__DIR__ . '/../backup_saas.sql');
    
    // Limpar comandos problemáticos
    $sql = str_replace('saas_compras', $database, $sql);
    
    // Remove todos os comandos MySQL específicos
    $sql = preg_replace('/\/\*M!.*?\*\/\s*-?\s*/s', '', $sql);
    $sql = preg_replace('/\/\*!.*?\*\/\s*/s', '', $sql); 
    $sql = preg_replace('/--.*$/m', '', $sql);
    
    // Remove comandos SET específicos
    $sql = preg_replace('/SET\s+@\w+.*?;/i', '', $sql);
    $sql = preg_replace('/SET\s+\w+.*?;/i', '', $sql);
    
    // Remove LOCK/UNLOCK
    $sql = str_replace(['LOCK TABLES', 'UNLOCK TABLES'], ['-- LOCK TABLES', '-- UNLOCK TABLES'], $sql);
    
    // Remove ALTER TABLE DISABLE/ENABLE KEYS
    $sql = preg_replace('/ALTER TABLE .* DISABLE KEYS.*;/', '', $sql);
    $sql = preg_replace('/ALTER TABLE .* ENABLE KEYS.*;/', '', $sql);
    
    // Executar por blocos
    echo "Executando comandos...<br>";
    $commands = array_filter(explode(';', $sql));
    $count = 0;
    $errors = [];
    
    foreach ($commands as $cmd) {
        $cmd = trim($cmd);
        if (empty($cmd) || substr($cmd, 0, 2) === '--' || substr($cmd, 0, 2) === '/*') continue;
        
        try {
            $pdo->exec($cmd);
            $count++;
            
            // Log importantes
            if (stripos($cmd, 'CREATE TABLE') !== false) {
                $table = preg_match('/CREATE TABLE\s+`?(\w+)`?/i', $cmd, $matches);
                if ($table) {
                    echo "✅ Tabela '{$matches[1]}' criada<br>";
                }
            }
        } catch (Exception $e) {
            $errors[] = "Erro: " . $e->getMessage() . " (Comando: " . substr($cmd, 0, 100) . "...)";
        }
    }
    
    echo "✅ $count comandos executados<br>";
    
    if (!empty($errors) && count($errors) <= 5) {
        echo "<br><strong>Erros encontrados:</strong><br>";
        foreach ($errors as $error) {
            echo "⚠️ $error<br>";
        }
    }
    
    echo "✅ $count comandos executados<br>";
    
    // Verificar resultado
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $userCount = $stmt->fetch()['total'];
    
    echo "<br><h2>🎉 BANCO IMPORTADO COM SUCESSO!</h2>";
    echo "<p>👥 <strong>$userCount usuários</strong> importados</p>";
    
    if ($userCount > 0) {
        $stmt = $pdo->query("SELECT nome, email, role FROM usuarios");
        $users = $stmt->fetchAll();
        
        echo "<h3>📋 USUÁRIOS DISPONÍVEIS:</h3>";
        echo "<ul style='font-size: 16px; line-height: 1.8;'>";
        foreach ($users as $user) {
            $badge = $user['role'] === 'admin' ? '🔴 ADMIN' : '🔵 USER';
            echo "<li><strong>{$user['nome']}</strong> - {$user['email']} $badge</li>";
        }
        echo "</ul>";
        
        echo "<h3>🔑 CREDENCIAIS DE TESTE:</h3>";
        echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
        echo "<strong>Admin:</strong> joabeantonio@gmail.com<br>";
        echo "<strong>User:</strong> joabeoliveiradev@gmail.com<br>";
        echo "<strong>Senha:</strong> (as senhas estão hasheadas no banco)<br>";
        echo "</div>";
        
        echo "<p style='font-size: 18px; margin: 20px 0;'>";
        echo "✅ <strong>Sistema pronto!</strong> ";
        echo "<a href='/' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 ENTRAR NO SISTEMA</a>";
        echo "</p>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>