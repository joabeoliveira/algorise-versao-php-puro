<?php
/**
 * Script para importar banco de dados
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🗄️ Importação do Banco de Dados</h1>";

// Configurações do banco
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'buscaprecos';

try {
    // 1. Conectar ao MySQL (sem banco específico)
    echo "<p>1. Conectando ao MySQL...</p>";
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "<p>✅ Conexão estabelecida</p>";
    
    // 2. Criar banco se não existir
    echo "<p>2. Criando banco de dados...</p>";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p>✅ Banco '$database' criado/verificado</p>";
    
    // 3. Selecionar o banco
    $pdo->exec("USE `$database`");
    echo "<p>✅ Banco '$database' selecionado</p>";
    
    // 4. Ler arquivo SQL
    echo "<p>3. Lendo arquivo SQL...</p>";
    $sqlFile = __DIR__ . '/backup_saas.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("Arquivo backup_saas.sql não encontrado!");
    }
    
    $sql = file_get_contents($sqlFile);
    echo "<p>✅ Arquivo lido (" . number_format(strlen($sql)) . " caracteres)</p>";
    
    // 5. Limpar comandos que podem causar erro
    $sql = preg_replace('/\/\*M!.*?\*\//', '', $sql); // Remove comandos específicos do MariaDB
    $sql = preg_replace('/--.*$/m', '', $sql); // Remove comentários
    $sql = str_replace('saas_compras', $database, $sql); // Substitui nome do banco
    
    // 6. Dividir e executar comandos SQL
    echo "<p>4. Executando comandos SQL...</p>";
    $commands = array_filter(array_map('trim', explode(';', $sql)));
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($commands as $command) {
        if (empty($command) || substr($command, 0, 2) === '--' || substr($command, 0, 2) === '/*') {
            continue;
        }
        
        try {
            $pdo->exec($command);
            $successCount++;
        } catch (Exception $e) {
            $errorCount++;
            if ($errorCount <= 5) { // Mostra apenas os primeiros 5 erros
                echo "<p>⚠️ Erro: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    echo "<p>✅ <strong>Importação concluída!</strong></p>";
    echo "<p>📊 <strong>$successCount</strong> comandos executados com sucesso</p>";
    if ($errorCount > 0) {
        echo "<p>⚠️ <strong>$errorCount</strong> comandos com erro (podem ser normais)</p>";
    }
    
    // 7. Verificar tabelas criadas
    echo "<p>5. Verificando tabelas criadas...</p>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p>✅ <strong>" . count($tables) . "</strong> tabelas encontradas:</p>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // 8. Verificar usuários
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
        $userCount = $stmt->fetch()['total'];
        echo "<p>👥 <strong>$userCount</strong> usuários encontrados na tabela</p>";
        
        if ($userCount > 0) {
            $stmt = $pdo->query("SELECT id, nome, email, role FROM usuarios LIMIT 5");
            $users = $stmt->fetchAll();
            echo "<p><strong>Primeiros usuários:</strong></p>";
            echo "<ul>";
            foreach ($users as $user) {
                echo "<li>{$user['nome']} ({$user['email']}) - {$user['role']}</li>";
            }
            echo "</ul>";
        }
    } catch (Exception $e) {
        echo "<p>⚠️ Erro ao verificar usuários: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ <strong>Erro:</strong> " . $e->getMessage() . "</p>";
    echo "<p>Verifique se o MySQL/MariaDB está rodando.</p>";
}

echo "<hr>";
echo "<p><a href='/'>← Voltar para o sistema</a></p>";
?>