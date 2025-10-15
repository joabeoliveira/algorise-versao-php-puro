<?php
/**
 * Diagnóstico de estrutura do banco
 * REMOVER APÓS USO!
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/settings-php-puro.php';

header('Content-Type: text/plain; charset=utf-8');

echo "========================================\n";
echo "DIAGNÓSTICO DE ESTRUTURA DO BANCO\n";
echo "========================================\n\n";

try {
    $pdo = getDbConnection();
    echo "✓ Conectado ao banco de dados\n\n";
    
    $tabelas = ['usuarios', 'processos', 'itens', 'fornecedores', 'precos'];
    
    foreach ($tabelas as $tabela) {
        echo "TABELA: $tabela\n";
        echo str_repeat("-", 50) . "\n";
        
        try {
            $stmt = $pdo->query("DESCRIBE $tabela");
            $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($colunas as $coluna) {
                echo sprintf("  %-30s %s\n", $coluna['Field'], $coluna['Type']);
            }
            echo "\n";
            
        } catch (PDOException $e) {
            echo "  ✗ Tabela não existe ou erro: " . $e->getMessage() . "\n\n";
        }
    }
    
    echo "========================================\n";
    echo "FIM DO DIAGNÓSTICO\n";
    echo "========================================\n";
    
} catch (Exception $e) {
    echo "✗ ERRO: " . $e->getMessage() . "\n";
}
