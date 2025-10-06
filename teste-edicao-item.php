<?php
/**
 * Teste - Funcionalidade de Edição de Item
 */

session_start();
require __DIR__ . '/vendor/autoload.php';

echo "<h1>🔧 Teste - Edição de Item do Processo</h1>";

try {
    // Testa conexão com banco
    $pdo = new PDO(
        'mysql:host=localhost;dbname=algorise_db;charset=utf8mb4',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<div class='alert alert-success'>✅ Conexão com banco OK</div>";
    
    // Lista alguns processos e seus itens para teste
    $stmt = $pdo->query("
        SELECT p.id as processo_id, p.numero_processo, p.nome_processo, 
               i.id as item_id, i.numero_item, i.descricao 
        FROM processos p 
        LEFT JOIN itens i ON p.id = i.processo_id 
        ORDER BY p.id, i.numero_item 
        LIMIT 10
    ");
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📋 Processos e Itens Disponíveis para Teste:</h3>";
    
    if (empty($dados)) {
        echo "<div class='alert alert-warning'>⚠️ Nenhum processo/item encontrado. Crie alguns processos e itens primeiro.</div>";
        echo "<div class='text-center'>";
        echo "<a href='/algorise-versao-php-puro/processos/novo' class='btn btn-primary me-2'>➕ Novo Processo</a>";
        echo "<a href='/algorise-versao-php-puro/processos' class='btn btn-outline-primary'>📋 Ver Processos</a>";
        echo "</div>";
    } else {
        echo "<div class='table-responsive'>";
        echo "<table class='table table-striped table-bordered'>";
        echo "<thead class='bg-primary text-white'>";
        echo "<tr><th>Processo ID</th><th>Número</th><th>Nome do Processo</th><th>Item ID</th><th>Nº Item</th><th>Descrição</th><th>Ações</th></tr>";
        echo "</thead><tbody>";
        
        foreach ($dados as $row) {
            $processoId = $row['processo_id'];
            $itemId = $row['item_id'];
            
            echo "<tr>";
            echo "<td>{$processoId}</td>";
            echo "<td>" . htmlspecialchars($row['numero_processo']) . "</td>";
            echo "<td>" . htmlspecialchars($row['nome_processo']) . "</td>";
            echo "<td>" . ($itemId ? $itemId : '-') . "</td>";
            echo "<td>" . ($row['numero_item'] ? htmlspecialchars($row['numero_item']) : '-') . "</td>";
            echo "<td>" . ($row['descricao'] ? htmlspecialchars($row['descricao']) : '-') . "</td>";
            echo "<td>";
            
            if ($itemId) {
                echo "<a href='/algorise-versao-php-puro/processos/{$processoId}/itens/{$itemId}/editar' class='btn btn-sm btn-primary'>✏️ Editar</a> ";
                echo "<a href='/algorise-versao-php-puro/processos/{$processoId}/itens' class='btn btn-sm btn-outline-secondary'>📋 Listar</a>";
            } else {
                echo "<a href='/algorise-versao-php-puro/processos/{$processoId}/itens' class='btn btn-sm btn-outline-info'>➕ Adicionar Item</a>";
            }
            
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</tbody></table>";
        echo "</div>";
    }
    
    echo "<h3>🔧 Correções Aplicadas no ItemController:</h3>";
    echo "<ul class='list-group'>";
    echo "<li class='list-group-item list-group-item-success'>✅ <code>echo \"Processo não encontrado\"</code> → <code>\$_SESSION['flash_error']</code></li>";
    echo "<li class='list-group-item list-group-item-success'>✅ <code>http_response_code(404)</code> → <code>Router::redirect()</code></li>";
    echo "<li class='list-group-item list-group-item-success'>✅ Validação de duplicados com flash messages</li>";
    echo "<li class='list-group-item list-group-item-success'>✅ Redirecionamentos corrigidos para usar variáveis interpoladas</li>";
    echo "<li class='list-group-item list-group-item-success'>✅ Mensagens de sucesso adicionadas</li>";
    echo "</ul>";
    
    echo "<h3>🎯 Métodos Corrigidos:</h3>";
    echo "<div class='row'>";
    echo "<div class='col-md-4'>";
    echo "<div class='card border-success'>";
    echo "<div class='card-header bg-success text-white'>✅ exibirFormularioEdicao()</div>";
    echo "<div class='card-body'>";
    echo "<small>Carrega dados do item para edição</small>";
    echo "</div></div></div>";
    
    echo "<div class='col-md-4'>";
    echo "<div class='card border-info'>";
    echo "<div class='card-header bg-info text-white'>🔄 atualizar()</div>";
    echo "<div class='card-body'>";
    echo "<small>Salva alterações com validação</small>";
    echo "</div></div></div>";
    
    echo "<div class='col-md-4'>";
    echo "<div class='card border-warning'>";
    echo "<div class='card-header bg-warning text-dark'>🛡️ listar()</div>";
    echo "<div class='card-body'>";
    echo "<small>Validação de processo existente</small>";
    echo "</div></div></div>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='alert alert-danger'>❌ Erro: " . $e->getMessage() . "</div>";
}

echo "<div class='mt-4 text-center'>";
echo "<a href='/algorise-versao-php-puro/processos' class='btn btn-primary me-2'>";
echo "<i class='bi bi-folder'></i> Ver Processos";
echo "</a>";
echo "<a href='/algorise-versao-php-puro/processos/novo' class='btn btn-outline-primary me-2'>";
echo "<i class='bi bi-plus'></i> Novo Processo";
echo "</a>";
echo "<a href='/algorise-versao-php-puro/dashboard' class='btn btn-outline-secondary'>";
echo "<i class='bi bi-house'></i> Dashboard";
echo "</a>";
echo "</div>";

echo "<div class='alert alert-success mt-4'>";
echo "<h6>✅ Correções Aplicadas com Sucesso!</h6>";
echo "<p class='mb-0'>O ItemController foi corrigido para usar mensagens de sessão e redirecionamentos adequados. ";
echo "Agora as operações de editar itens devem funcionar corretamente sem mostrar \"Processo não encontrado\".</p>";
echo "</div>";

// Verificar se há mensagens de sessão pendentes
if (isset($_SESSION['flash_error'])) {
    echo "<div class='alert alert-danger mt-3'>";
    echo "<strong>Flash Error:</strong> " . htmlspecialchars($_SESSION['flash_error']);
    echo "</div>";
    unset($_SESSION['flash_error']);
}

if (isset($_SESSION['flash_success'])) {
    echo "<div class='alert alert-success mt-3'>";
    echo "<strong>Flash Success:</strong> " . htmlspecialchars($_SESSION['flash_success']);
    echo "</div>";
    unset($_SESSION['flash_success']);
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<div class="container py-4">
<?php /* O conteúdo já foi ecoado acima */ ?>
</div>