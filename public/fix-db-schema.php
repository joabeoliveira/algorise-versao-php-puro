<?php
/**
 * Endpoint temporário para corrigir schema do banco
 * REMOVER APÓS USO!
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/settings-php-puro.php';

header('Content-Type: text/plain; charset=utf-8');

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
    
    // 3. Adicionar coluna 'data_criacao' na tabela cotacoes_rapidas (se existir)
    echo "3. Verificando coluna 'data_criacao' na tabela 'cotacoes_rapidas'...\n";
    try {
        $pdo->exec("ALTER TABLE cotacoes_rapidas ADD COLUMN data_criacao TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP");
        echo "   ✓ Coluna 'data_criacao' adicionada com sucesso!\n\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "   ℹ Coluna 'data_criacao' já existe\n\n";
        } elseif (strpos($e->getMessage(), "doesn't exist") !== false) {
            echo "   ℹ Tabela 'cotacoes_rapidas' não existe (OK)\n\n";
        } else {
            echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
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
    echo "✓ CORREÇÕES CONCLUÍDAS!\n";
    echo "========================================\n\n";
    
    echo "PRÓXIMO PASSO: Teste acessar /processos, /fornecedores e /usuarios\n";
    
} catch (Exception $e) {
    echo "✗ ERRO FATAL: " . $e->getMessage() . "\n";
}
