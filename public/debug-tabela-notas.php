<?php
/**
 * Debug - Verificar estrutura da tabela notas_tecnicas
 */

// Configurações de exibição de erros
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Debug - Estrutura da Tabela notas_tecnicas</h1>";

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
    
    // Verifica se a tabela existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'notas_tecnicas'");
    if ($stmt->rowCount() === 0) {
        echo "<p>❌ <strong>Tabela 'notas_tecnicas' não existe!</strong></p>";
        
        // Lista todas as tabelas
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<h3>📋 Tabelas disponíveis:</h3>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
        
    } else {
        echo "<p>✅ <strong>Tabela 'notas_tecnicas' encontrada!</strong></p>";
        
        // Mostra a estrutura da tabela
        $stmt = $pdo->query("DESCRIBE notas_tecnicas");
        $columns = $stmt->fetchAll();
        
        echo "<h3>📋 Estrutura da tabela notas_tecnicas:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
        echo "<thead>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th style='padding: 10px;'>Campo</th>";
        echo "<th style='padding: 10px;'>Tipo</th>";
        echo "<th style='padding: 10px;'>Null</th>";
        echo "<th style='padding: 10px;'>Key</th>";
        echo "<th style='padding: 10px;'>Default</th>";
        echo "<th style='padding: 10px;'>Extra</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td style='padding: 10px;'><strong>{$col['Field']}</strong></td>";
            echo "<td style='padding: 10px;'>{$col['Type']}</td>";
            echo "<td style='padding: 10px;'>{$col['Null']}</td>";
            echo "<td style='padding: 10px;'>{$col['Key']}</td>";
            echo "<td style='padding: 10px;'>{$col['Default']}</td>";
            echo "<td style='padding: 10px;'>{$col['Extra']}</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        
        // Conta registros
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM notas_tecnicas");
        $count = $stmt->fetch()['total'];
        echo "<p><strong>📊 Total de registros:</strong> $count</p>";
        
        if ($count > 0) {
            // Mostra alguns registros de exemplo
            $stmt = $pdo->query("SELECT * FROM notas_tecnicas LIMIT 3");
            $examples = $stmt->fetchAll();
            
            echo "<h3>📄 Exemplos de registros:</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
            echo "<thead><tr style='background: #f0f0f0;'>";
            foreach (array_keys($examples[0]) as $col) {
                echo "<th style='padding: 10px;'>$col</th>";
            }
            echo "</tr></thead>";
            echo "<tbody>";
            foreach ($examples as $row) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td style='padding: 10px;'>" . htmlspecialchars($value ?? '') . "</td>";
                }
                echo "</tr>";
            }
            echo "</tbody></table>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Erro:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='./'>← Voltar para o sistema</a></p>";
?>