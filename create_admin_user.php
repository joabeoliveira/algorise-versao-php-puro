<?php
/**
 * Script para criar usuário administrador
 * Uso: php create_admin_user.php
 */

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/src/settings-php-puro.php';

try {
    $pdo = getDbConnection();
    
    $email = 'admin@admin.com';
    $senha = '114211Jo';
    $nome = 'Admin';
    $tipo = 'admin';
    
    // Hash da senha
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    
    // Verificar se o usuário já existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuarioExistente = $stmt->fetch();
    
    if ($usuarioExistente) {
        // Atualizar usuário existente
        $stmt = $pdo->prepare("
            UPDATE usuarios 
            SET nome = ?, senha = ?, tipo = ?, ativo = 1, updated_at = NOW()
            WHERE email = ?
        ");
        $stmt->execute([$nome, $senhaHash, $tipo, $email]);
        echo "✅ Usuário atualizado com sucesso!\n";
    } else {
        // Criar novo usuário
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nome, email, senha, tipo, ativo, created_at, updated_at)
            VALUES (?, ?, ?, ?, 1, NOW(), NOW())
        ");
        $stmt->execute([$nome, $email, $senhaHash, $tipo]);
        echo "✅ Usuário criado com sucesso!\n";
    }
    
    echo "\nDetalhes do usuário:\n";
    echo "Email: $email\n";
    echo "Nome: $nome\n";
    echo "Tipo: $tipo\n";
    echo "Senha: (hashada com sucesso)\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
