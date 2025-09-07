<?php

// Inclui o arquivo de configurações para poder usar a função de conexão com o banco
require __DIR__ . '/src/settings.php';

echo "--- Ferramenta de Criação de Usuário ---\n";

// Pede os dados do novo usuário no terminal
$nome = readline("Digite o nome completo do usuário: ");
$email = readline("Digite o e-mail do usuário: ");
$senha = readline("Digite a senha do usuário: ");
$role = readline("Digite a permissão ('admin' ou 'user'): ");

// Validação simples
if (empty($nome) || empty($email) || empty($senha) || !in_array($role, ['admin', 'user'])) {
    echo "\nERRO: Todos os campos são obrigatórios e a permissão deve ser 'admin' ou 'user'.\n";
    exit;
}

// Criptografa a senha - a parte mais importante!
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

try {
    // Conecta ao banco e insere o novo usuário
    $pdo = getDbConnection();
    $sql = "INSERT INTO usuarios (nome, email, senha, role) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nome, $email, $senhaHash, $role]);

    echo "\nSUCESSO: Usuário '{$nome}' criado com sucesso!\n";

} catch (PDOException $e) {
    // Trata erros, como e-mail duplicado
    echo "\nERRO AO CRIAR USUÁRIO: " . $e->getMessage() . "\n";
}