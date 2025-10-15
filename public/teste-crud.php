<?php
/**
 * Teste de INSERT no banco - Diagnóstico
 * REMOVER APÓS USO!
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/settings-php-puro.php';

header('Content-Type: text/plain; charset=utf-8');

echo "========================================\n";
echo "TESTE DE INSERT NO BANCO\n";
echo "========================================\n\n";

try {
    $pdo = getDbConnection();
    echo "✓ Conectado ao banco de dados\n\n";
    
    // 1. Testar INSERT em processos
    echo "1. Testando INSERT em PROCESSOS...\n";
    try {
        $sql = "INSERT INTO processos (numero_processo, nome_processo, orgao, status, tipo_contratacao, data_criacao) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            'TESTE-' . time(),
            'Processo de Teste',
            'Órgão Teste',
            'planejamento',
            'pregao_eletronico'
        ]);
        
        if ($result) {
            $id = $pdo->lastInsertId();
            echo "   ✓ INSERT bem-sucedido! ID: $id\n";
            
            // Verificar se foi salvo
            $stmt = $pdo->prepare("SELECT * FROM processos WHERE id = ?");
            $stmt->execute([$id]);
            $processo = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "   ✓ Verificação: " . json_encode($processo, JSON_PRETTY_PRINT) . "\n\n";
            
            // Limpar teste
            $pdo->exec("DELETE FROM processos WHERE id = $id");
            echo "   ✓ Registro de teste removido\n\n";
        }
    } catch (PDOException $e) {
        echo "   ✗ ERRO: " . $e->getMessage() . "\n";
        echo "   SQL State: " . $e->getCode() . "\n\n";
    }
    
    // 2. Testar INSERT em fornecedores
    echo "2. Testando INSERT em FORNECEDORES...\n";
    try {
        $sql = "INSERT INTO fornecedores (razao_social, cnpj, email, telefone, ramo_atividade, ativo, data_criacao) 
                VALUES (?, ?, ?, ?, ?, 1, NOW())";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            'Fornecedor Teste ' . time(),
            '00.000.000/0000-00',
            'teste@teste.com',
            '(00) 0000-0000',
            'Teste'
        ]);
        
        if ($result) {
            $id = $pdo->lastInsertId();
            echo "   ✓ INSERT bem-sucedido! ID: $id\n";
            
            // Limpar teste
            $pdo->exec("DELETE FROM fornecedores WHERE id = $id");
            echo "   ✓ Registro de teste removido\n\n";
        }
    } catch (PDOException $e) {
        echo "   ✗ ERRO: " . $e->getMessage() . "\n";
        echo "   SQL State: " . $e->getCode() . "\n\n";
    }
    
    // 3. Testar UPDATE em usuários
    echo "3. Testando UPDATE em USUÁRIOS...\n";
    try {
        // Buscar um usuário existente
        $stmt = $pdo->query("SELECT id FROM usuarios LIMIT 1");
        $usuario = $stmt->fetch();
        
        if ($usuario) {
            $id = $usuario['id'];
            $sql = "UPDATE usuarios SET atualizado_em = NOW() WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([$id]);
            
            if ($result) {
                echo "   ✓ UPDATE bem-sucedido! Linhas afetadas: " . $stmt->rowCount() . "\n\n";
            }
        } else {
            echo "   ℹ Nenhum usuário encontrado para testar UPDATE\n\n";
        }
    } catch (PDOException $e) {
        echo "   ✗ ERRO: " . $e->getMessage() . "\n";
        echo "   SQL State: " . $e->getCode() . "\n\n";
    }
    
    echo "========================================\n";
    echo "✓ TESTES CONCLUÍDOS\n";
    echo "========================================\n";
    
} catch (Exception $e) {
    echo "✗ ERRO FATAL: " . $e->getMessage() . "\n";
}
