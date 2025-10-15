<?php
/**
 * Script para aplicar correções no schema do banco de dados
 * Execute: php fix-schema.php
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/settings-php-puro.php';

echo "========================================\n";
echo "CORREÇÃO DE SCHEMA DO BANCO DE DADOS\n";
echo "========================================\n\n";

try {
    $pdo = getDbConnection();
    echo "✓ Conectado ao banco de dados\n\n";
    
    // 1. Adicionar coluna 'orgao' na tabela processos
    echo "1. Verificando coluna 'orgao' na tabela 'processos'...\n";
    try {
        $pdo->exec("ALTER TABLE processos ADD COLUMN orgao VARCHAR(255) NULL AFTER numero_processo");
        echo "   ✓ Coluna 'orgao' adicionada com sucesso!\n\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "   ℹ Coluna 'orgao' já existe\n\n";
        } else {
            echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
        }
    }
    
    // 2. Adicionar coluna 'valor_estimado' na tabela itens
    echo "2. Verificando coluna 'valor_estimado' na tabela 'itens'...\n";
    try {
        $pdo->exec("ALTER TABLE itens ADD COLUMN valor_estimado DECIMAL(15,2) NULL AFTER quantidade");
        echo "   ✓ Coluna 'valor_estimado' adicionada com sucesso!\n\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "   ℹ Coluna 'valor_estimado' já existe\n\n";
        } else {
            echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
        }
    }
    
    // 3. Adicionar coluna 'data_criacao' na tabela cotacoes_rapidas
    echo "3. Verificando coluna 'data_criacao' na tabela 'cotacoes_rapidas'...\n";
    try {
        $pdo->exec("ALTER TABLE cotacoes_rapidas ADD COLUMN data_criacao TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP");
        echo "   ✓ Coluna 'data_criacao' adicionada com sucesso!\n\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "   ℹ Coluna 'data_criacao' já existe\n\n";
        } else {
            // Se a tabela não existir, criar ela
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                echo "   ℹ Tabela 'cotacoes_rapidas' não existe (será criada quando necessário)\n\n";
            } else {
                echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
            }
        }
    }
    
    // 4. Adicionar coluna 'ativo' na tabela fornecedores
    echo "4. Verificando coluna 'ativo' na tabela 'fornecedores'...\n";
    try {
        $pdo->exec("ALTER TABLE fornecedores ADD COLUMN ativo BOOLEAN DEFAULT TRUE");
        echo "   ✓ Coluna 'ativo' adicionada com sucesso!\n\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "   ℹ Coluna 'ativo' já existe\n\n";
        } else {
            echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
        }
    }
    
    echo "========================================\n";
    echo "VERIFICAÇÃO DA ESTRUTURA\n";
    echo "========================================\n\n";
    
    // Verificar estrutura das tabelas
    $tabelas = ['processos', 'itens', 'fornecedores'];
    
    foreach ($tabelas as $tabela) {
        echo "Tabela: $tabela\n";
        $stmt = $pdo->query("SHOW COLUMNS FROM $tabela");
        $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "  Colunas: " . implode(', ', $colunas) . "\n\n";
    }
    
    echo "========================================\n";
    echo "✓ CORREÇÕES CONCLUÍDAS!\n";
    echo "========================================\n";
    
} catch (Exception $e) {
    echo "✗ ERRO FATAL: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
