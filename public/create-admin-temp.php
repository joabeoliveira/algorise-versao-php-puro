<?php
/**
 * Endpoint temporário para criar usuário administrador
 * DELETE ESTE ARQUIVO APÓS USAR!
 */

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/settings-php-puro.php';

// Proteção básica - token simples
$token = $_GET['token'] ?? '';
if ($token !== 'create-admin-now-114211') {
    http_response_code(403);
    die('Access denied');
}

header('Content-Type: application/json');

try {
    $pdo = getDbConnection();
    
    $email = 'joabeantonio@gmail.com';
    $senha = '114211Jo@';
    $nome = 'Joabe Antonio';
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
        $mensagem = "Usuário atualizado com sucesso!";
    } else {
        // Criar novo usuário
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nome, email, senha, tipo, ativo, created_at, updated_at)
            VALUES (?, ?, ?, ?, 1, NOW(), NOW())
        ");
        $stmt->execute([$nome, $email, $senhaHash, $tipo]);
        $mensagem = "Usuário criado com sucesso!";
    }
    
    echo json_encode([
        'success' => true,
        'message' => $mensagem,
        'user' => [
            'email' => $email,
            'nome' => $nome,
            'tipo' => $tipo
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
