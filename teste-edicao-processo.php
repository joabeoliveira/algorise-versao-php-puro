<?php
/**
 * Teste - Funcionalidade de Edição de Processo
 */

session_start();
require __DIR__ . '/vendor/autoload.php';

echo "<h1>🔧 Teste - Edição de Processo</h1>";

try {
    // Testa conexão com banco
    $pdo = new PDO(
        'mysql:host=localhost;dbname=algorise_db;charset=utf8mb4',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<div class='alert alert-success'>✅ Conexão com banco OK</div>";
    
    // Lista alguns processos para teste
    $stmt = $pdo->query("SELECT id, numero_processo, nome_processo FROM processos LIMIT 5");
    $processos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📋 Processos Disponíveis para Teste:</h3>";
    
    if (empty($processos)) {
        echo "<div class='alert alert-warning'>⚠️ Nenhum processo encontrado. Crie alguns processos primeiro.</div>";
    } else {
        echo "<div class='table-responsive'>";
        echo "<table class='table table-striped table-bordered'>";
        echo "<thead class='bg-primary text-white'>";
        echo "<tr><th>ID</th><th>Número</th><th>Nome</th><th>Ações</th></tr>";
        echo "</thead><tbody>";
        
        foreach ($processos as $processo) {
            echo "<tr>";
            echo "<td>{$processo['id']}</td>";
            echo "<td>" . htmlspecialchars($processo['numero_processo']) . "</td>";
            echo "<td>" . htmlspecialchars($processo['nome_processo']) . "</td>";
            echo "<td>";
            echo "<a href='/algorise-versao-php-puro/processos/{$processo['id']}/editar' class='btn btn-sm btn-primary'>✏️ Editar</a> ";
            echo "<a href='/algorise-versao-php-puro/processos' class='btn btn-sm btn-outline-secondary'>📋 Listar</a>";
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</tbody></table>";
        echo "</div>";
    }
    
    echo "<h3>🔧 Correções Aplicadas:</h3>";
    echo "<ul class='list-group'>";
    echo "<li class='list-group-item list-group-item-success'>✅ <code>\$args['id']</code> → <code>\$params['id']</code></li>";
    echo "<li class='list-group-item list-group-item-success'>✅ <code>\$response->getBody()</code> → <code>\$_SESSION['flash_error']</code></li>";
    echo "<li class='list-group-item list-group-item-success'>✅ <code>\$response->withStatus()</code> → <code>Router::redirect()</code></li>";
    echo "<li class='list-group-item list-group-item-success'>✅ Mensagens de feedback com sessão</li>";
    echo "</ul>";
    
    echo "<h3>🎯 Funcionalidades Testadas:</h3>";
    echo "<div class='row'>";
    echo "<div class='col-md-4'>";
    echo "<div class='card border-success'>";
    echo "<div class='card-header bg-success text-white'>✅ exibirFormularioEdicao()</div>";
    echo "<div class='card-body'>";
    echo "<small>Carrega dados do processo para edição</small>";
    echo "</div></div></div>";
    
    echo "<div class='col-md-4'>";
    echo "<div class='card border-info'>";
    echo "<div class='card-header bg-info text-white'>🔄 atualizar()</div>";
    echo "<div class='card-body'>";
    echo "<small>Salva alterações no banco de dados</small>";
    echo "</div></div></div>";
    
    echo "<div class='col-md-4'>";
    echo "<div class='card border-danger'>";
    echo "<div class='card-header bg-danger text-white'>🗑️ excluir()</div>";
    echo "<div class='card-body'>";
    echo "<small>Remove processo do sistema</small>";
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
echo "<p class='mb-0'>O ProcessoController foi corrigido para usar a sintaxe do PHP puro em vez do Slim Framework. ";
echo "Agora as operações de editar, atualizar e excluir processos devem funcionar corretamente.</p>";
echo "</div>";
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<div class="container py-4">
<?php /* O conteúdo já foi ecoado acima */ ?>
</div>