<?php
// Lógica para lidar com mensagens flash e dados de formulário em caso de erro
$mensagemFlash = $_SESSION['flash'] ?? null;
$dadosFormulario = [];
$abrirFormulario = false;
if ($mensagemFlash && !empty($mensagemFlash['dados_formulario'])) {
    $dadosFormulario = $mensagemFlash['dados_formulario'];
    $abrirFormulario = true;
}
unset($_SESSION['flash']);

// Garante que as variáveis existam para evitar erros
$processo = $processo ?? ['id' => 0, 'nome_processo' => 'Processo Inválido'];
$itens = $itens ?? [];
$totalItens = $totalItens ?? count($itens); // Garante que $totalItens sempre tenha um valor
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="/processos" class="btn btn-sm btn-outline-secondary mb-2">Voltar para Processos</a>
        <h1>Itens do Processo: <small class="text-muted"><?= htmlspecialchars($processo['nome_processo']) ?></small></h1>
    </div>
</div>

<?php if ($totalItens == 0): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle-fill"></i>
        <strong>Dica:</strong> Como este processo ainda não possui itens, você pode usar a opção "Importar via Planilha" para adicioná-los em massa.
    </div>
<?php endif; ?>

<?php if ($mensagemFlash): ?>
    <div class="alert alert-<?= htmlspecialchars($mensagemFlash['tipo']) ?> mt-3 alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($mensagemFlash['mensagem']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<h4 class="mt-4">Itens Cadastrados</h4>
<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered table-primary align-middle">
        <thead class="table-primary">
            <tr>
                <th>Nº</th>
                <th style="width: 50%;">Descrição</th>
                <th>CATMAT/CATSER</th>
                <th>Qtd.</th>
                <th>Unid. Medida</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($itens as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['numero_item'] ?? '') ?></td>
                    <td><?= htmlspecialchars($item['descricao'] ?? '') ?></td>
                    <td><?= htmlspecialchars($item['catmat_catser'] ?? '') ?></td>
                    <td><?= htmlspecialchars($item['quantidade'] ?? '') ?></td>
                    <td><?= htmlspecialchars($item['unidade_medida'] ?? '') ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="/processos/<?= $processo['id'] ?>/itens/<?= $item['id'] ?>/editar" class="btn btn-sm btn-primary" title="Editar Item"><i class="bi bi-pencil-square"></i></a>
                            <a href="/processos/<?= $processo['id'] ?>/itens/<?= $item['id'] ?>/pesquisar" class="btn btn-sm btn-info text-white" title="Pesquisar Preços"><i class="bi bi-search"></i></a>
                            <form action="/processos/<?= $processo['id'] ?>/itens/<?= $item['id'] ?>/excluir" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este item?');">
                                <button type="submit" class="btn btn-sm btn-danger" title="Excluir Item"><i class="bi bi-trash-fill"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($itens)): ?>
                <tr>
                    <td colspan="6" class="text-center">Nenhum item cadastrado para este processo.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<hr>

<div class="d-flex justify-content-end my-3 gap-2 flex-wrap">
    <a href="/processos/<?= $processo['id'] ?>/analise" class="btn btn-warning">
        <i class="bi bi-clipboard-data"></i> Mesa de Análise Geral
    </a>
    <button class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#modalSolicitacaoLote">
        <i class="bi bi-envelope-paper"></i> Solicitar Cotação para Fornecedores
    </button>
    
    <?php if ($totalItens == 0): ?>
        <a href="/processos/<?= $processo['id'] ?>/itens/importar" class="btn btn-info text-white">
            <i class="bi bi-file-earmark-spreadsheet"></i> Importar via Planilha
        </a>
    <?php endif; ?>

    <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#formularioAdicionarItem" aria-expanded="false" aria-controls="formularioAdicionarItem">
        <i class="bi bi-plus-lg"></i> Adicionar Novo Item
    </button>
</div>
<div class="collapse mb-4 <?= $abrirFormulario ? 'show' : '' ?>" id="formularioAdicionarItem">
    <div class="card shadow-sm border-light-subtle">
        <div class="card-header bg-light"><h4 class="mb-0">Adicionar Novo Item ao Processo</h4></div>
        <div class="card-body">
            <form action="/processos/<?= $processo['id'] ?>/itens" method="POST" class="mt-2">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="numero_item" class="form-label">Nº do Item</label>
                        <input type="number" class="form-control" id="numero_item" name="numero_item" required value="<?= htmlspecialchars($dadosFormulario['numero_item'] ?? '') ?>">
                    </div>
                    <div class="col-md-9 mb-3">
                        <label for="catmat_input" class="form-label">Código CATMAT/CATSER</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="catmat_input" name="catmat_catser" value="<?= htmlspecialchars($dadosFormulario['catmat_catser'] ?? '') ?>">
                            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#modalBuscaCatmat"><i class="bi bi-search"></i> Buscar</button>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="descricao_input" class="form-label">Descrição Detalhada do Item</label>
                    <textarea class="form-control" id="descricao_input" name="descricao" rows="3" required><?= htmlspecialchars($dadosFormulario['descricao'] ?? '') ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="quantidade" class="form-label">Quantidade</label>
                        <input type="number" class="form-control" id="quantidade" name="quantidade" required value="<?= htmlspecialchars($dadosFormulario['quantidade'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="unidade_medida" class="form-label">Unidade de Medida</label>
                        <input type="text" class="form-control" id="unidade_medida" name="unidade_medida" required value="<?= htmlspecialchars($dadosFormulario['unidade_medida'] ?? '') ?>">
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#formularioAdicionarItem">Cancelar</button>
                    <button type="submit" class="btn btn-success">Salvar Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalBuscaCatmat" tabindex="-1" aria-labelledby="modalBuscaCatmatLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalBuscaCatmatLabel">Busca Inteligente de Materiais</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                <div class="input-group mb-3"><input type="text" id="inputBuscaModal" class="form-control" placeholder="Digite o nome ou código do material..." autocomplete="off"><button class="btn btn-outline-secondary" type="button" id="btnLimpar"><i class="bi bi-x-lg"></i></button></div>
                <ul id="listaSugestoes" class="list-group"></ul>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSolicitacaoLote" tabindex="-1" aria-labelledby="modalSolicitacaoLoteLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalSolicitacaoLoteLabel">Enviar Solicitação de Cotação em Lote</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                <div class="mb-4">
                    <h6>1. Selecione os Itens para Cotar</h6>
                    <div class="table-responsive" style="max-height: 200px;">
                        <table class="table table-sm table-hover">
                            <thead class="table-primary"><tr><th style="width: 5%;"><input class="form-check-input" type="checkbox" id="checkTodosItens"></th><th><label for="checkTodosItens" style="cursor: pointer;" class="mb-0 text-white">Selecionar Todos os Itens</label></th></tr></thead>
                            <tbody>
                                <?php foreach($itens as $item): ?>
                                <tr><td><input type="checkbox" class="form-check-input item-lote-check" value="<?= $item['id'] ?>"></td><td><?= htmlspecialchars($item['numero_item']) ?> - <?= htmlspecialchars($item['descricao']) ?></td></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="mb-4">
                    <h6>2. Selecione os Fornecedores Destinatários</h6>
                    <div class="input-group"><label class="input-group-text" for="ramoAtividadeSelect">Filtrar por Ramo</label><select class="form-select" id="ramoAtividadeSelect"><option value="todos" selected>-- Carregar Todos --</option></select></div>
                    <div id="listaFornecedoresLote" class="table-responsive mt-2" style="max-height: 200px;"></div>
                </div>
                <div class="mb-3">
                    <h6>3. Detalhe as Condições e Justificativas</h6>
                    <div class="mb-3"><label for="condicoes_contratuais_lote" class="form-label">Condições da Contratação (Art. 4º)</label><textarea class="form-control" id="condicoes_contratuais_lote" rows="3" placeholder="Ex: Prazo de entrega de 15 dias, garantia de 12 meses, local de entrega..."></textarea><div class="form-text">Esta informação será incluída no e-mail enviado aos fornecedores.</div></div>
                    <div class="mb-3"><label for="justificativa_fornecedores_lote" class="form-label">Justificativa da Escolha dos Fornecedores (Art. 3º, VIII)</label><textarea class="form-control" id="justificativa_fornecedores_lote" rows="3" placeholder="Justifique por que os fornecedores selecionados são adequados para esta cotação..." required></textarea></div>
                </div>
                <div class="mb-3">
                    <h6>4. Defina o Prazo para Resposta</h6>
                    <label for="prazo_dias_lote" class="form-label">Prazo (em dias)</label>
                    <input type="number" class="form-control" id="prazo_dias_lote" value="5" min="1">
                </div>
            </div>
            <div class="modal-footer">
                <div id="loadingLote" class="spinner-border text-primary me-auto" role="status" style="display: none;"></div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnEnviarLote">Enviar Solicitações</button>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const catmatInput = document.getElementById('catmat_input');
    const descricaoInput = document.getElementById('descricao_input');
    
    if (!catmatInput || !descricaoInput) {
        return;
    }
    
    let timeoutId;
    
    catmatInput.addEventListener('input', () => {
        clearTimeout(timeoutId);
        
        timeoutId = setTimeout(() => {
            const codigo = catmatInput.value.trim();
            if (codigo.length > 0) {
                buscarDescricaoPorCodigo(codigo);
            } else {
                descricaoInput.value = '';
            }
        }, 500);
    });
    
    async function buscarDescricaoPorCodigo(codigo) {
        try {
            // Busca na API local que consulta a tabela catmat do Cloud SQL
            const response = await fetch('/api/catmat/pesquisar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ query: codigo })
            });

            const result = await response.json();

            if (!result.success || !result.data || !result.data.results) {
                descricaoInput.value = '';
                return;
            }

            const resultados = result.data.results;
            
            // Procura por correspondência exata do código
            const itemExato = resultados.find(item => item.catmat === codigo);
            
            if (itemExato) {
                descricaoInput.value = itemExato.descricao;
            } else if (resultados.length > 0) {
                // Se não encontrou exato, usa o primeiro resultado
                descricaoInput.value = resultados[0].descricao;
            }

        } catch (error) {
            console.error('Erro ao buscar descrição:', error);
        }
    }
});
</script>

<script src="/public/catmat-search/search.js"></script>