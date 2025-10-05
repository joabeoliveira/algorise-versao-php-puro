<?php
/**
 * Criar banco manualmente - Estrutura completa
 */

echo "🛠️ CRIANDO BANCO MANUALMENTE...<br><br>";

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'buscaprecos';

try {
    // Conectar
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✅ Conectado ao MySQL<br>";
    
    // Dropar e recriar banco
    echo "🗑️ Removendo banco antigo...<br>";
    $pdo->exec("DROP DATABASE IF EXISTS `$database`");
    
    echo "📁 Criando banco novo...<br>";
    $pdo->exec("CREATE DATABASE `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$database`");
    
    // Criar tabela usuarios
    echo "👥 Criando tabela usuarios...<br>";
    $pdo->exec("
        CREATE TABLE `usuarios` (
            `id` int NOT NULL AUTO_INCREMENT,
            `nome` varchar(255) NOT NULL,
            `email` varchar(255) NOT NULL,
            `senha` varchar(255) NOT NULL,
            `role` enum('admin','user') NOT NULL DEFAULT 'user',
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Inserir usuários
    echo "🔑 Criando usuários padrão...<br>";
    
    // Senha padrão: 123456
    $senhaHash = password_hash('123456', PASSWORD_DEFAULT);
    
    $pdo->exec("
        INSERT INTO `usuarios` (`nome`, `email`, `senha`, `role`) VALUES
        ('Joabe Antonio de Oliveira', 'joabeantonio@gmail.com', '$senhaHash', 'admin'),
        ('Joabe Oliveira', 'joabeoliveiradev@gmail.com', '$senhaHash', 'user'),
        ('Admin Sistema', 'admin@sistema.com', '$senhaHash', 'admin')
    ");
    
    // Criar tabela processos
    echo "📋 Criando tabela processos...<br>";
    $pdo->exec("
        CREATE TABLE `processos` (
            `id` int NOT NULL AUTO_INCREMENT,
            `numero_processo` varchar(50) NOT NULL,
            `objeto` text NOT NULL,
            `modalidade` varchar(100) NOT NULL,
            `tipo_contratacao` varchar(100) NOT NULL,
            `status` enum('rascunho','em_andamento','concluido','cancelado') DEFAULT 'rascunho',
            `data_abertura` date NULL,
            `data_encerramento` date NULL,
            `valor_estimado` decimal(15,2) NULL,
            `orgao` varchar(255) NULL,
            `agente_responsavel` varchar(255) NULL,
            `observacoes` text NULL,
            `usuario_id` int NOT NULL,
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `usuario_id` (`usuario_id`),
            FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Criar tabela fornecedores
    echo "🏢 Criando tabela fornecedores...<br>";
    $pdo->exec("
        CREATE TABLE `fornecedores` (
            `id` int NOT NULL AUTO_INCREMENT,
            `nome` varchar(255) NOT NULL,
            `cnpj` varchar(18) NULL,
            `email` varchar(255) NULL,
            `telefone` varchar(20) NULL,
            `endereco` text NULL,
            `ramo_atividade` varchar(255) NULL,
            `contato_nome` varchar(255) NULL,
            `contato_cargo` varchar(255) NULL,
            `status` enum('ativo','inativo') DEFAULT 'ativo',
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `cnpj` (`cnpj`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Criar tabela itens
    echo "📦 Criando tabela itens...<br>";
    $pdo->exec("
        CREATE TABLE `itens` (
            `id` int NOT NULL AUTO_INCREMENT,
            `processo_id` int NOT NULL,
            `descricao` text NOT NULL,
            `unidade` varchar(50) NOT NULL,
            `quantidade` decimal(10,2) NOT NULL,
            `valor_unitario_estimado` decimal(15,2) NULL,
            `valor_total_estimado` decimal(15,2) NULL,
            `catmat` varchar(20) NULL,
            `marca_referencia` varchar(255) NULL,
            `observacoes` text NULL,
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `processo_id` (`processo_id`),
            FOREIGN KEY (`processo_id`) REFERENCES `processos` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Criar tabela precos
    echo "💰 Criando tabela precos...<br>";
    $pdo->exec("
        CREATE TABLE `precos` (
            `id` int NOT NULL AUTO_INCREMENT,
            `item_id` int NOT NULL,
            `fornecedor_id` int NOT NULL,
            `valor_unitario` decimal(15,2) NOT NULL,
            `valor_total` decimal(15,2) NOT NULL,
            `prazo_entrega` int NULL,
            `validade_proposta` date NULL,
            `observacoes` text NULL,
            `status` enum('pendente','aprovado','rejeitado') DEFAULT 'pendente',
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `item_id` (`item_id`),
            KEY `fornecedor_id` (`fornecedor_id`),
            FOREIGN KEY (`item_id`) REFERENCES `itens` (`id`) ON DELETE CASCADE,
            FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Criar tabela cotacoes_rapidas
    echo "⚡ Criando tabela cotacoes_rapidas...<br>";
    $pdo->exec("
        CREATE TABLE `cotacoes_rapidas` (
            `id` int NOT NULL AUTO_INCREMENT,
            `usuario_id` int NOT NULL,
            `descricao_item` text NOT NULL,
            `quantidade` decimal(10,2) NOT NULL,
            `unidade` varchar(50) NOT NULL,
            `observacoes` text NULL,
            `status` enum('pendente','processado','finalizado') DEFAULT 'pendente',
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `usuario_id` (`usuario_id`),
            FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Inserir dados de teste
    echo "🎲 Inserindo dados de teste...<br>";
    
    // Fornecedor de teste
    $pdo->exec("
        INSERT INTO `fornecedores` (`nome`, `cnpj`, `email`, `telefone`, `ramo_atividade`, `status`) VALUES
        ('Fornecedor Teste Ltda', '12.345.678/0001-90', 'contato@teste.com', '(11) 99999-9999', 'Comércio Geral', 'ativo')
    ");
    
    // Processo de teste
    $pdo->exec("
        INSERT INTO `processos` (`numero_processo`, `objeto`, `modalidade`, `tipo_contratacao`, `status`, `orgao`, `usuario_id`) VALUES
        ('001/2025', 'Aquisição de Material de Escritório', 'Pregão Eletrônico', 'Menor Preço', 'em_andamento', 'Prefeitura Municipal', 1)
    ");
    
    // Item de teste
    $pdo->exec("
        INSERT INTO `itens` (`processo_id`, `descricao`, `unidade`, `quantidade`, `valor_unitario_estimado`, `valor_total_estimado`) VALUES
        (1, 'Papel A4 75g - Resma 500 folhas', 'RESMA', 100, 25.50, 2550.00)
    ");
    
    // Verificar resultado
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $userCount = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<br><h2>🎉 BANCO CRIADO COM SUCESSO!</h2>";
    echo "<p>📊 <strong>" . count($tables) . "</strong> tabelas criadas</p>";
    echo "<p>👥 <strong>$userCount</strong> usuários criados</p>";
    
    echo "<h3>🔑 CREDENCIAIS PARA LOGIN:</h3>";
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 10px; margin: 20px 0; border: 2px solid #4caf50;'>";
    echo "<strong style='color: #d32f2f;'>🔴 ADMIN:</strong><br>";
    echo "📧 Email: <code>joabeantonio@gmail.com</code><br>";
    echo "🔐 Senha: <code>123456</code><br><br>";
    
    echo "<strong style='color: #1976d2;'>🔵 USER:</strong><br>";
    echo "📧 Email: <code>joabeoliveiradev@gmail.com</code><br>";
    echo "🔐 Senha: <code>123456</code><br><br>";
    
    echo "<strong style='color: #ff9800;'>🟠 ADMIN SISTEMA:</strong><br>";
    echo "📧 Email: <code>admin@sistema.com</code><br>";
    echo "🔐 Senha: <code>123456</code><br>";
    echo "</div>";
    
    echo "<h3>📋 TABELAS CRIADAS:</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>✅ $table</li>";
    }
    echo "</ul>";
    
    echo "<div style='text-align: center; margin: 30px 0;'>";
    echo "<a href='/' style='background: #4caf50; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: bold;'>";
    echo "🚀 ENTRAR NO SISTEMA";
    echo "</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "❌ <strong>ERRO:</strong> " . $e->getMessage() . "<br>";
    echo "<p>Verifique se o MySQL está rodando no XAMPP Control Panel.</p>";
}
?>