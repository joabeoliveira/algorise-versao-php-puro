<?php
/**
 * Ponto de entrada principal - Versão PHP Puro
 * Substitui o Slim Framework por um sistema de roteamento simples
 */

// Carrega configurações e autoloader primeiro
require __DIR__ . '/../vendor/autoload.php';
$settings = require __DIR__ . '/../src/settings-php-puro.php';

require __DIR__ . '/../src/Core/helpers.php';

// Função para garantir que o usuário admin existe
function garantirUsuarioAdmin() {
    try {
        $pdo = \getDbConnection();
        
        // Verifica se já existe usuário admin
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute(['admin@algorise.com']);
        
        if (!$stmt->fetch()) {
            // Criar usuário admin se não existir
            $senhaHash = password_hash('admin123', PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (nome, email, senha, role) VALUES 
                    ('Administrador', 'admin@algorise.com', ?, 'admin')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$senhaHash]);
            error_log("Usuário admin criado automaticamente");
        }
    } catch (Exception $e) {
        error_log("Erro ao criar usuário admin: " . $e->getMessage());
    }
}

// Garantir que usuário admin existe (importante para primeira execução no Cloud)
garantirUsuarioAdmin();

// Inicia a sessão após carregar configurações
session_start();

// Importa as classes necessárias
use Joabe\Buscaprecos\Core\Router;
use Joabe\Buscaprecos\Controller\ProcessoController;
use Joabe\Buscaprecos\Controller\ItemController;
use Joabe\Buscaprecos\Controller\PrecoController;
use Joabe\Buscaprecos\Controller\DashboardController;
use Joabe\Buscaprecos\Controller\FornecedorController;
use Joabe\Buscaprecos\Controller\AnaliseController;
use Joabe\Buscaprecos\Controller\AcompanhamentoController;
use Joabe\Buscaprecos\Controller\RelatorioController;
use Joabe\Buscaprecos\Controller\CotacaoRapidaController;
use Joabe\Buscaprecos\Controller\UsuarioController;
use Joabe\Buscaprecos\Controller\CotacaoPublicaController;
use Joabe\Buscaprecos\Controller\ConfiguracaoController;
use Joabe\Buscaprecos\Controller\CatmatController;

// Cria o router
$router = new Router();

// Middleware de Autenticação Global
$router->addMiddleware(function() {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $publicRoutes = ['/login', '/esqueceu-senha', '/redefinir-senha', '/status', '/fix-db-schema', '/diagnostico-db', '/teste-crud', '/download.php'];
    $isPublic = in_array($path, $publicRoutes) || 
                str_starts_with($path, '/cotacao/responder') || 
                str_starts_with($path, '/download-proposta/') ||
                str_starts_with($path, '/api/catmat/');

    if (!isset($_SESSION['usuario_id']) && !$isPublic) {
        Router::redirect('/login');
        return false;
    }
    return true;
});

// Middleware para verificação de permissões de admin
$adminMiddleware = function() {
    if (!isset($_SESSION['usuario_role']) || $_SESSION['usuario_role'] !== 'admin') {
        Router::redirect('/dashboard');
        return false;
    }
    return true;
};

// =====================================
// ROTAS PÚBLICAS
// =====================================

// Download de PDFs do Google Cloud Storage
$router->get('/download.php', function() {
    require __DIR__ . '/download.php';
});

// Download de propostas 
$router->get('/download-proposta/{nome_arquivo}', [UsuarioController::class, 'downloadProposta']);

// Status de saúde simples (público)
$router->get('/status', function() {
    // Executa o script existente de status e encerra
    require __DIR__ . '/status.php';
});

// Fix DB schema (REMOVER APÓS USO!)
$router->get('/fix-db-schema', function() {
    require __DIR__ . '/fix-db-schema.php';
});

// Diagnóstico DB (REMOVER APÓS USO!)
$router->get('/diagnostico-db', function() {
    require __DIR__ . '/diagnostico-db.php';
});

// Teste CRUD (REMOVER APÓS USO!)
$router->get('/teste-crud', function() {
    require __DIR__ . '/teste-crud.php';
});

// Rota de Diagnóstico Temporária
$router->get('/diagnostic', function() {
    require __DIR__ . '/diagnostic.php';
});

// Login
$router->get('/login', function() {
    $controller = new UsuarioController();
    $controller->exibirFormularioLogin();
});
$router->post('/login', function() {
    $controller = new UsuarioController();
    $controller->processarLogin();
});

// Recuperação de senha
$router->get('/esqueceu-senha', [UsuarioController::class, 'exibirFormularioEsqueceuSenha']);
$router->post('/esqueceu-senha', [UsuarioController::class, 'solicitarRedefinicao']);
$router->get('/redefinir-senha', [UsuarioController::class, 'exibirFormularioRedefinir']);
$router->post('/redefinir-senha', [UsuarioController::class, 'processarRedefinicao']);

// Cotação pública
$router->get('/cotacao/responder', [CotacaoPublicaController::class, 'exibirFormulario']);
$router->post('/cotacao/responder', [CotacaoPublicaController::class, 'salvarResposta']);

// =====================================
// ROTAS PROTEGIDAS
// =====================================

// Páginas principais
$router->get('/', function($params) {
    if (isset($_SESSION['usuario_id'])) {
        Router::redirect('/dashboard');
    } else {
        Router::redirect('/login');
    }
});

$router->get('/logout', [UsuarioController::class, 'processarLogout']);
$router->get('/dashboard', function() {
    $controller = new DashboardController();
    $controller->exibir();
});

// Processos
$router->get('/processos', [ProcessoController::class, 'listar']);
$router->get('/processos/novo', [ProcessoController::class, 'exibirFormulario']);
$router->post('/processos', [ProcessoController::class, 'criar']);
$router->get('/processos/{id}/editar', [ProcessoController::class, 'exibirFormularioEdicao']);
$router->post('/processos/{id}/editar', [ProcessoController::class, 'atualizar']);
$router->post('/processos/{id}/excluir', [ProcessoController::class, 'excluir']);
$router->get('/processos/{processo_id}/analise', [AnaliseController::class, 'exibirAnaliseProcesso']);
$router->post('/processos/{id}/salvar-justificativas', [AnaliseController::class, 'salvarJustificativasProcesso']);

// Itens
$router->get('/processos/{processo_id}/itens', [ItemController::class, 'listar']);
$router->post('/processos/{processo_id}/itens', [ItemController::class, 'criar']);
$router->get('/processos/{processo_id}/itens/{item_id}/editar', [ItemController::class, 'exibirFormularioEdicao']);
$router->post('/processos/{processo_id}/itens/{item_id}/editar', [ItemController::class, 'atualizar']);
$router->post('/processos/{processo_id}/itens/{item_id}/excluir', [ItemController::class, 'excluir']);
$router->get('/processos/{processo_id}/itens/importar', [ItemController::class, 'exibirFormularioImportacao']);
$router->post('/processos/{processo_id}/itens/importar', [ItemController::class, 'processarImportacao']);
$router->get('/processos/{processo_id}/itens/modelo-planilha', [ItemController::class, 'gerarModeloPlanilha']);
$router->post('/processos/{processo_id}/itens/{item_id}/analise/salvar', [AnaliseController::class, 'salvarAnaliseItem']);

// Preços e Cotações
$router->get('/processos/{processo_id}/itens/{item_id}/pesquisar', [PrecoController::class, 'exibirPainel']);
$router->post('/processos/{processo_id}/itens/{item_id}/precos', [PrecoController::class, 'criar']);
$router->post('/processos/{processo_id}/itens/{item_id}/precos/{preco_id}/excluir', [PrecoController::class, 'excluir']);
$router->post('/processos/{processo_id}/itens/{item_id}/precos/{preco_id}/desconsiderar', [PrecoController::class, 'desconsiderarPreco']);
$router->post('/processos/{processo_id}/itens/{item_id}/precos/{preco_id}/reconsiderar', [PrecoController::class, 'reconsiderarPreco']);

// Fornecedores
$router->get('/fornecedores', [FornecedorController::class, 'listar']);
$router->get('/fornecedores/novo', [FornecedorController::class, 'exibirFormulario']);
$router->post('/fornecedores', [FornecedorController::class, 'criar']);
$router->get('/fornecedores/{id}/editar', [FornecedorController::class, 'exibirFormularioEdicao']);
$router->post('/fornecedores/{id}/editar', [FornecedorController::class, 'atualizar']);
$router->post('/fornecedores/{id}/excluir', [FornecedorController::class, 'excluir']);
$router->get('/fornecedores/importar', [FornecedorController::class, 'exibirFormularioImportacao']);
$router->post('/fornecedores/importar', [FornecedorController::class, 'processarImportacao']);
$router->get('/fornecedores/modelo-planilha', [FornecedorController::class, 'gerarModeloPlanilha']);

// Cotação Rápida
$router->get('/cotacao-rapida', [CotacaoRapidaController::class, 'exibirFormulario']);
$router->get('/cotacao-rapida/modelo-planilha', [CotacaoRapidaController::class, 'gerarModeloPlanilha']);

// Busca CATMAT
$router->get('/catmat/busca', [CatmatController::class, 'busca']);
$router->get('/catmat', [CatmatController::class, 'busca']); // Alias para facilitar acesso

// Relatórios e Acompanhamento
$router->get('/acompanhamento', [AcompanhamentoController::class, 'exibir']);
$router->get('/relatorios', [RelatorioController::class, 'listar']);
$router->get('/processos/{id}/relatorio', [RelatorioController::class, 'gerarRelatorio']);
$router->get('/relatorios/{nota_id}/visualizar', [RelatorioController::class, 'visualizar']);

// =====================================
// APIs (JSON)
// =====================================

$router->post('/api/painel-de-precos', [PrecoController::class, 'buscarPainelDePrecos']);
$router->post('/api/processos/{processo_id}/itens/{item_id}/precos/lote', [PrecoController::class, 'criarLote']);
$router->post('/api/processos/{processo_id}/itens/{item_id}/pesquisar-orgaos', [PrecoController::class, 'pesquisarContratacoesSimilares']);
$router->post('/api/processos/{processo_id}/solicitacao-lote', [PrecoController::class, 'enviarSolicitacaoLote']);
$router->get('/api/fornecedores', [FornecedorController::class, 'listarJson']);
$router->get('/api/fornecedores/ramos-atividade', [FornecedorController::class, 'listarRamosAtividade']);
$router->get('/api/fornecedores/por-ramo', [FornecedorController::class, 'listarPorRamo']);
$router->post('/api/cotacao-rapida/buscar', [CotacaoRapidaController::class, 'buscarPrecos']);
$router->post('/api/cotacao-rapida/salvar-relatorio', [CotacaoRapidaController::class, 'salvarAnalise']);
$router->get('/relatorios/nota-tecnica-rapida', [RelatorioController::class, 'gerarRelatorioCotacaoRapida']);


// APIs para busca CATMAT
$router->post('/api/catmat/pesquisar', [CatmatController::class, 'pesquisar']);
$router->get('/api/catmat/sugestoes', [CatmatController::class, 'sugestoes']);
$router->get('/api/catmat/processos', [CatmatController::class, 'listarProcessos']);
$router->post('/api/catmat/adicionar-item', [CatmatController::class, 'adicionarItem']);

// =====================================
// ROTAS ADMINISTRATIVAS (APENAS ADMIN)
// =====================================

$router->group('/usuarios', function($router) {
    $router->get('', [UsuarioController::class, 'listar']);
    $router->get('/novo', [UsuarioController::class, 'exibirFormularioCriacao']);
    $router->post('/novo', [UsuarioController::class, 'criar']);
    $router->get('/{id}/editar', [UsuarioController::class, 'exibirFormularioEdicao']);
    $router->post('/{id}/editar', [UsuarioController::class, 'atualizar']);
    $router->post('/{id}/excluir', [UsuarioController::class, 'excluir']);
}, $adminMiddleware);

// Configurações do sistema (apenas para admins)
$router->group('/configuracoes', function($router) {
    $router->get('', [ConfiguracaoController::class, 'index']);
    $router->get('/geral', [ConfiguracaoController::class, 'index']); // Alias para página principal
    $router->post('/atualizar', [ConfiguracaoController::class, 'atualizar']);
    
    // Configurações de email
    $router->get('/email', [ConfiguracaoController::class, 'emailConfig']);
    $router->post('/email/atualizar', [ConfiguracaoController::class, 'atualizarEmail']);
    $router->post('/email/testar', [ConfiguracaoController::class, 'testarEmail']);
    
    // Configurações de interface
    $router->get('/interface', [ConfiguracaoController::class, 'interfaceConfig']);
    $router->post('/interface/atualizar', [ConfiguracaoController::class, 'atualizarInterface']);
    $router->post('/interface/upload', [ConfiguracaoController::class, 'uploadLogo']);
}, $adminMiddleware);

// Relatório de gestão (em desenvolvimento)
$router->get('/relatorio-gestao', function($params) {
    $tituloPagina = "Relatório de Gestão";
    $paginaConteudo = __DIR__ . '/../src/View/em_desenvolvimento.php';
    ob_start();
    require __DIR__ . '/../src/View/layout/main.php';
    $view = ob_get_clean();
    echo $view;
});

// =====================================
// EXECUTA O ROUTER
// =====================================

try {
    $router->run();
} catch (Exception $e) {
    // Em produção, você pode logar o erro e mostrar uma página de erro amigável
    http_response_code(500);
    echo "Erro interno do servidor";
    
    // Em desenvolvimento, mostra o erro
    if ($_ENV['APP_ENV'] === 'development') {
        echo "<pre>Erro: " . $e->getMessage() . "\n";
        echo "Arquivo: " . $e->getFile() . ":" . $e->getLine() . "</pre>";
    }
}