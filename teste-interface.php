<?php
/**
 * Teste rápido do sistema de configurações de interface
 */

// Carrega configurações e autoloader
require __DIR__ . '/vendor/autoload.php';

use Joabe\Buscaprecos\Controller\ConfiguracaoController;

// Tenta carregar configurações de interface
try {
    
    echo "<h1>Teste do Sistema de Configurações de Interface</h1>";
    
    // Testa conexão com banco
    $pdo = new PDO(
        'mysql:host=localhost;dbname=algorise_db;charset=utf8mb4',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p>✅ Conexão com banco de dados OK</p>";
    
    // Testa se existe a tabela configuracoes
    $result = $pdo->query("SHOW TABLES LIKE 'configuracoes'")->fetch();
    if ($result) {
        echo "<p>✅ Tabela 'configuracoes' existe</p>";
        
        // Conta registros de interface
        $count = $pdo->query("SELECT COUNT(*) FROM configuracoes WHERE categoria = 'interface'")->fetchColumn();
        echo "<p>📊 Total de configurações de interface: {$count}</p>";
        
        // Lista algumas configurações
        $configs = $pdo->query("SELECT chave, valor FROM configuracoes WHERE categoria = 'interface' LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        echo "<h3>Algumas configurações de interface:</h3>";
        echo "<ul>";
        foreach ($configs as $config) {
            echo "<li><strong>{$config['chave']}:</strong> {$config['valor']}</li>";
        }
        echo "</ul>";
        
    } else {
        echo "<p>❌ Tabela 'configuracoes' não existe</p>";
    }
    
    // Testa o método do controller
    $configsInterface = ConfiguracaoController::getConfiguracoesPorCategoria('interface');
    echo "<h3>Configurações via Controller:</h3>";
    echo "<ul>";
    foreach (array_slice($configsInterface, 0, 5, true) as $chave => $valor) {
        echo "<li><strong>{$chave}:</strong> {$valor}</li>";
    }
    echo "</ul>";
    
    echo "<h3>CSS Dinâmico Gerado:</h3>";
    $nomeSystem = $configsInterface['interface_nome_sistema'] ?? 'Algorise';
    $corPrimaria = $configsInterface['interface_cor_primaria'] ?? '#0d6efd';
    $corSidebar = $configsInterface['interface_sidebar_cor'] ?? '#212529';
    $larguraSidebar = $configsInterface['interface_sidebar_largura'] ?? '280';
    
    echo "<pre>";
    echo ":root {\n";
    echo "  --bs-primary: {$corPrimaria};\n";
    echo "  --sidebar-bg: {$corSidebar};\n";
    echo "  --sidebar-width: {$larguraSidebar}px;\n";
    echo "}\n";
    echo "</pre>";
    
    echo "<p><strong>Nome do Sistema:</strong> {$nomeSystem}</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<p><a href='/algorise-versao-php-puro/configuracoes/interface'>🎨 Acessar Configurações de Interface</a></p>";
echo "<p><a href='/algorise-versao-php-puro/dashboard'>🏠 Voltar ao Dashboard</a></p>";
?>