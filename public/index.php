<?php
session_start();
require __DIR__ . '/../src/settings.php';

// 1. Inclusão dos Controllers (Use statements)
use Slim\Factory\AppFactory;
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


// 2. Criação da Aplicação e Middlewares
$app = AppFactory::create();
$app->addBodyParsingMiddleware();

// Middleware de Autenticação
$authMiddleware = function ($request, $handler) use ($app) {
    $path = $request->getUri()->getPath();
    $publicRoutes = ['/login']; // Apenas a rota /login é pública
    $isPublic = in_array($path, $publicRoutes) || str_starts_with($path, '/cotacao/responder');

    if (!isset($_SESSION['usuario_id']) && !$isPublic) {
        $response = $app->getResponseFactory()->createResponse();
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
    return $handler->handle($request);
};
$app->add($authMiddleware);


// Middleware de Verificação de Permissões
$adminAuthMiddleware = function ($request, $handler) use ($app) {
    // Se o usuário não for admin, redireciona para o dashboard
    if (!isset($_SESSION['usuario_role']) || $_SESSION['usuario_role'] !== 'admin') {
        $response = $app->getResponseFactory()->createResponse();
        // Opcional: Adicionar uma mensagem de erro para o usuário
        // $_SESSION['flash_error'] = 'Você não tem permissão para acessar esta página.';
        return $response->withHeader('Location', '/dashboard')->withStatus(302);
    }
    // Se for admin, permite que a requisição continue
    return $handler->handle($request);
};


// 3. Definição de TODAS as Rotas
// Rotas Públicas (Login e Resposta de Cotação)
$app->get('/login', [UsuarioController::class, 'exibirFormularioLogin']);
$app->post('/login', [UsuarioController::class, 'processarLogin']);

// Rota para exibir o formulário de redefinição de senha
$app->get('/esqueceu-senha', [UsuarioController::class, 'exibirFormularioEsqueceuSenha']);
$app->post('/esqueceu-senha', [UsuarioController::class, 'solicitarRedefinicao']);

// Rota para processar a redefinição de senha
$app->get('/redefinir-senha', [UsuarioController::class, 'exibirFormularioRedefinir']);
$app->post('/redefinir-senha', [UsuarioController::class, 'processarRedefinicao']);

$app->get('/cotacao/responder', [\Joabe\Buscaprecos\Controller\CotacaoPublicaController::class, 'exibirFormulario']);
$app->post('/cotacao/responder', [\Joabe\Buscaprecos\Controller\CotacaoPublicaController::class, 'salvarResposta']);

// Rotas Protegidas
$app->get('/logout', [UsuarioController::class, 'processarLogout']);
$app->get('/', function ($request, $response) {
    return $response->withHeader('Location', '/dashboard')->withStatus(302);
});
$app->get('/dashboard', [DashboardController::class, 'exibir']);

// Processos
$app->get('/processos', [ProcessoController::class, 'listar']);
$app->get('/processos/novo', [ProcessoController::class, 'exibirFormulario']);
$app->post('/processos', [ProcessoController::class, 'criar']);
$app->get('/processos/{id}/editar', [ProcessoController::class, 'exibirFormularioEdicao']);
$app->post('/processos/{id}/editar', [ProcessoController::class, 'atualizar']);
$app->post('/processos/{id}/excluir', [ProcessoController::class, 'excluir']);
$app->get('/processos/{processo_id}/analise', [AnaliseController::class, 'exibirAnaliseProcesso']);
$app->post('/processos/{id}/salvar-justificativas', [AnaliseController::class, 'salvarJustificativasProcesso']);

// Itens
$app->get('/processos/{processo_id}/itens', [ItemController::class, 'listar']);
$app->post('/processos/{processo_id}/itens', [ItemController::class, 'criar']);
$app->get('/processos/{processo_id}/itens/{item_id}/editar', [ItemController::class, 'exibirFormularioEdicao']);
$app->post('/processos/{processo_id}/itens/{item_id}/editar', [ItemController::class, 'atualizar']);
$app->post('/processos/{processo_id}/itens/{item_id}/excluir', [ItemController::class, 'excluir']);
$app->get('/processos/{processo_id}/itens/importar', [ItemController::class, 'exibirFormularioImportacao']);
$app->post('/processos/{processo_id}/itens/importar', [ItemController::class, 'processarImportacao']);
$app->get('/processos/{processo_id}/itens/modelo-planilha', [ItemController::class, 'gerarModeloPlanilha']);
$app->post('/processos/{processo_id}/itens/{item_id}/analise/salvar', [AnaliseController::class, 'salvarAnaliseItem']);


// Preços e Cotações
$app->get('/processos/{processo_id}/itens/{item_id}/pesquisar', [PrecoController::class, 'exibirPainel']);
$app->post('/processos/{processo_id}/itens/{item_id}/precos', [PrecoController::class, 'criar']);
$app->post('/processos/{processo_id}/itens/{item_id}/precos/{preco_id}/excluir', [PrecoController::class, 'excluir']);
$app->post('/processos/{processo_id}/itens/{item_id}/precos/{preco_id}/desconsiderar', [PrecoController::class, 'desconsiderarPreco']);
$app->post('/processos/{processo_id}/itens/{item_id}/precos/{preco_id}/reconsiderar', [PrecoController::class, 'reconsiderarPreco']);
$app->post('/api/processos/{processo_id}/solicitacao-lote', [PrecoController::class, 'enviarSolicitacaoLote']);

// Fornecedores
$app->get('/fornecedores', [FornecedorController::class, 'listar']);
$app->get('/fornecedores/novo', [FornecedorController::class, 'exibirFormulario']);
$app->post('/fornecedores', [FornecedorController::class, 'criar']);
$app->get('/fornecedores/{id}/editar', [FornecedorController::class, 'exibirFormularioEdicao']);
$app->post('/fornecedores/{id}/editar', [FornecedorController::class, 'atualizar']);
$app->post('/fornecedores/{id}/excluir', [FornecedorController::class, 'excluir']);
$app->get('/fornecedores/importar', [FornecedorController::class, 'exibirFormularioImportacao']);
$app->post('/fornecedores/importar', [FornecedorController::class, 'processarImportacao']);
$app->get('/fornecedores/modelo-planilha', [FornecedorController::class, 'gerarModeloPlanilha']);

// Cotação Rápida
$app->get('/cotacao-rapida', [CotacaoRapidaController::class, 'exibirFormulario']);
$app->get('/cotacao-rapida/modelo-planilha', [CotacaoRapidaController::class, 'gerarModeloPlanilha']);

// Relatórios
$app->get('/acompanhamento', [AcompanhamentoController::class, 'exibir']);
$app->get('/relatorios', [RelatorioController::class, 'listar']);
$app->get('/processos/{id}/relatorio', [RelatorioController::class, 'gerarRelatorio']);
$app->get('/relatorios/{nota_id}/visualizar', [RelatorioController::class, 'visualizar']);
$app->get('/download-proposta/{nome_arquivo}', function ($request, $response, $args) {
    $nomeArquivo = $args['nome_arquivo'];
    $caminhoCompleto = __DIR__ . '/../storage/propostas/' . $nomeArquivo;
    if (!file_exists($caminhoCompleto) || !preg_match('/^[a-f0-9]+\.pdf$/', $nomeArquivo)) {
        $response->getBody()->write('Arquivo não encontrado ou inválido.');
        return $response->withStatus(404);
    }
    $response = $response->withHeader('Content-Type', 'application/pdf');
    $response = $response->withHeader('Content-Disposition', 'inline; filename="' . $nomeArquivo . '"');
    $response->getBody()->write(file_get_contents($caminhoCompleto));
    return $response;
});

// APIs (JSON)
$app->post('/api/painel-de-precos', [PrecoController::class, 'buscarPainelDePrecos']);
$app->post('/api/processos/{processo_id}/itens/{item_id}/precos/lote', [PrecoController::class, 'criarLote']);
$app->post('/api/processos/{processo_id}/itens/{item_id}/pesquisar-orgaos', [PrecoController::class, 'pesquisarContratacoesSimilares']);
$app->get('/api/fornecedores', [FornecedorController::class, 'listarJson']);
$app->get('/api/fornecedores/ramos-atividade', [FornecedorController::class, 'listarRamosAtividade']);
$app->get('/api/fornecedores/por-ramo', [FornecedorController::class, 'listarPorRamo']);
$app->post('/api/cotacao-rapida/buscar', [CotacaoRapidaController::class, 'buscarPrecos']);
$app->post('/api/cotacao-rapida/salvar-relatorio', [CotacaoRapidaController::class, 'salvarAnalise']);


// 4. Execução da Aplicação (DEVE SER A ÚLTIMA LINHA)

// Rotas para Gerenciamento de Usuários
// --- INÍCIO DO GRUPO DE ROTAS PROTEGIDAS PARA ADMIN ---
$app->group('/usuarios', function ($group) {
    $group->get('', [\Joabe\Buscaprecos\Controller\UsuarioController::class, 'listar']);
    $group->get('/novo', [\Joabe\Buscaprecos\Controller\UsuarioController::class, 'exibirFormularioCriacao']);
    $group->post('/novo', [\Joabe\Buscaprecos\Controller\UsuarioController::class, 'criar']);
    $group->get('/{id}/editar', [\Joabe\Buscaprecos\Controller\UsuarioController::class, 'exibirFormularioEdicao']);
    $group->post('/{id}/editar', [\Joabe\Buscaprecos\Controller\UsuarioController::class, 'atualizar']);
    $group->post('/{id}/excluir', [\Joabe\Buscaprecos\Controller\UsuarioController::class, 'excluir']);
})->add($adminAuthMiddleware); // Aplica o "porteiro" de admin a todo o grupo
// --- FIM DO GRUPO DE ROTAS PROTEGIDAS ---

// NOVO: Rota para o Relatório de Gestão (em desenvolvimento)
$app->get('/relatorio-gestao', function ($request, $response, $args) {
    $tituloPagina = "Relatório de Gestão";
    $paginaConteudo = __DIR__ . '/../src/View/em_desenvolvimento.php';
    ob_start();
    require __DIR__ . '/../src/View/layout/main.php';
    $view = ob_get_clean();
    $response->getBody()->write($view);
    return $response;
});

$app->run();