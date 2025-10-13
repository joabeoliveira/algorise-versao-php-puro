<?php
// Script para criar usuário admin automaticamente
require_once __DIR__ . '/../src/settings-php-puro.php';

try {
    $pdo = getDbConnection();
    
    echo "Conectado ao banco de dados...\n";
    
    // Criar hash para a senha admin123
    $senhaHash = password_hash('admin123', PASSWORD_DEFAULT);
    echo "Hash criado: " . $senhaHash . "\n";
    
    // Inserir ou atualizar usuário admin
    $sql = "INSERT INTO usuarios (nome, email, senha, role) VALUES 
            ('Administrador', 'admin@algorise.com', ?, 'admin') 
            ON DUPLICATE KEY UPDATE 
            senha=?, nome='Administrador', role='admin'";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$senhaHash, $senhaHash]);
    
    if ($result) {
        echo "✓ Usuário admin criado/atualizado com sucesso!\n";
        
        // Verificar se foi criado
        $stmt = $pdo->prepare("SELECT id, nome, email, role FROM usuarios WHERE email = ?");
        $stmt->execute(['admin@algorise.com']);
        $usuario = $stmt->fetch();
        
        if ($usuario) {
            echo "✓ Verificação: Usuário encontrado - ID: " . $usuario['id'] . ", Nome: " . $usuario['nome'] . "\n";
        }
    } else {
        echo "✗ Erro ao criar usuário admin\n";
    }
    
} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage() . "\n";
}
?>