<?php
// Script para verificar estrutura de todas as tabelas
require_once __DIR__ . '/../src/settings-php-puro.php';

try {
    $pdo = getDbConnection();
    
    echo "=== VERIFICAÇÃO COMPLETA DA ESTRUTURA DO BANCO ===\n\n";
    
    // Listar todas as tabelas
    $stmt = $pdo->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tabelas as $tabela) {
        echo "📋 TABELA: $tabela\n";
        echo str_repeat("-", 50) . "\n";
        
        try {
            $stmt = $pdo->query("DESCRIBE $tabela");
            $colunas = $stmt->fetchAll();
            
            foreach ($colunas as $coluna) {
                $nullable = $coluna['Null'] === 'YES' ? 'NULL' : 'NOT NULL';
                $default = $coluna['Default'] !== null ? "DEFAULT '{$coluna['Default']}'" : '';
                $extra = $coluna['Extra'] ? "({$coluna['Extra']})" : '';
                
                echo sprintf("  %-20s %-15s %-8s %-20s %s\n", 
                    $coluna['Field'], 
                    $coluna['Type'], 
                    $nullable, 
                    $default,
                    $extra
                );
            }
            
            // Contar registros
            $count = $pdo->query("SELECT COUNT(*) FROM $tabela")->fetchColumn();
            echo "  📊 Registros: $count\n";
            
        } catch (Exception $e) {
            echo "  ❌ Erro: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    echo "=== TESTES DE CONSULTAS CRÍTICAS ===\n\n";
    
    // Testar consultas que são usadas frequentemente
    $consultas = [
        'usuarios' => "SELECT id, nome, email, role FROM usuarios LIMIT 3",
        'processos' => "SELECT id, nome, status FROM processos LIMIT 3",
        'fornecedores' => "SELECT id, razao_social FROM fornecedores LIMIT 3",
        'itens' => "SELECT id, nome FROM itens LIMIT 3",
        'cotacoes_rapidas' => "SELECT id, numero_processo FROM cotacoes_rapidas LIMIT 3"
    ];
    
    foreach ($consultas as $tabela => $sql) {
        echo "🔍 Testando $tabela:\n";
        try {
            $stmt = $pdo->query($sql);
            $resultados = $stmt->fetchAll();
            echo "  ✅ Sucesso - " . count($resultados) . " registros encontrados\n";
            
            if (count($resultados) > 0) {
                $primeiro = $resultados[0];
                echo "  📝 Exemplo: " . json_encode($primeiro, JSON_UNESCAPED_UNICODE) . "\n";
            }
        } catch (Exception $e) {
            echo "  ❌ Erro: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro geral: " . $e->getMessage() . "\n";
}
?>