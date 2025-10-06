<?php
// Teste direto da API
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/settings.php';

use Joabe\Buscaprecos\Controller\PrecoController;

try {
    $controller = new PrecoController();
    
    // Simula parâmetros
    $params = ['processo_id' => 1];
    
    // Mock do POST data
    $_POST = [
        'item_ids' => [1],
        'fornecedor_ids' => [1], 
        'prazo_dias' => 5,
        'justificativa_fornecedores' => 'teste'
    ];
    
    echo "Testando método...\n";
    $controller->enviarSolicitacaoLote($params);
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString();
}
?>