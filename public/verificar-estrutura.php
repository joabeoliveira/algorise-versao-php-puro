<?php
// Script para verificar estrutura da tabela usuarios
require_once __DIR__ . '/../src/settings-php-puro.php';

try {
    $pdo = getDbConnection();
    
    echo "Conectado ao banco de dados...\n";
    
    // Verificar estrutura da tabela
    $stmt = $pdo->query("DESCRIBE usuarios");
    $colunas = $stmt->fetchAll();
    
    echo "Estrutura da tabela usuarios:\n";
    foreach ($colunas as $coluna) {
        echo "- " . $coluna['Field'] . " (" . $coluna['Type'] . ")\n";
    }
    
    // Verificar se já existe usuário admin
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute(['admin@algorise.com']);
    $usuario = $stmt->fetch();
    
    if ($usuario) {
        echo "\nUsuário admin existente:\n";
        foreach ($usuario as $key => $value) {
            if (!is_numeric($key)) {
                echo "- $key: $value\n";
            }
        }
    } else {
        echo "\nNenhum usuário admin encontrado.\n";
    }
    
} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage() . "\n";
}
?>