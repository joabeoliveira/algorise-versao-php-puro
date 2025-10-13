<?php
// Script para verificar usuários no banco
require_once __DIR__ . '/../src/settings-php-puro.php';

try {
    $pdo = getDbConnection();
    
    echo "Conectado ao banco de dados...\n";
    
    // Verificar todos os usuários
    $stmt = $pdo->query("SELECT id, nome, email, role, created_at FROM usuarios ORDER BY id");
    $usuarios = $stmt->fetchAll();
    
    echo "=== USUÁRIOS NO BANCO ===\n";
    if (empty($usuarios)) {
        echo "Nenhum usuário encontrado!\n";
    } else {
        foreach ($usuarios as $usuario) {
            echo "ID: " . $usuario['id'] . "\n";
            echo "Nome: " . $usuario['nome'] . "\n";
            echo "Email: " . $usuario['email'] . "\n";
            echo "Role: " . $usuario['role'] . "\n";
            echo "Criado: " . $usuario['created_at'] . "\n";
            echo "---\n";
        }
    }
    
    // Testar login específico do admin
    echo "\n=== TESTE DE LOGIN ADMIN ===\n";
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute(['admin@algorise.com']);
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "✓ Usuário admin encontrado!\n";
        echo "Nome: " . $admin['nome'] . "\n";
        echo "Hash da senha: " . substr($admin['senha'], 0, 30) . "...\n";
        
        // Testar verificação de senha
        $senhaCorreta = password_verify('admin123', $admin['senha']);
        echo "Password verify para 'admin123': " . ($senhaCorreta ? '✓ SIM' : '✗ NÃO') . "\n";
    } else {
        echo "✗ Usuário admin NÃO encontrado!\n";
        echo "Criando usuário admin...\n";
        
        $senhaHash = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (nome, email, senha, role) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute(['Administrador', 'admin@algorise.com', $senhaHash, 'admin']);
        
        if ($result) {
            echo "✓ Usuário admin criado com sucesso!\n";
        } else {
            echo "✗ Erro ao criar usuário admin\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage() . "\n";
}
?>