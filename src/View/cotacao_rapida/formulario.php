<style>
    .preco-row.desconsiderado {
        opacity: 0.5;
        text-decoration: line-through;
        background-color: #f8f9fa;
    }
    .preco-row.desconsiderado .badge {
        opacity: 0.65;
    }
</style>

<h1 class="mb-4">Cotação Rápida (Incisos I e II da IN 65/2021)</h1>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <label for="nomeResponsavel" class="form-label"><strong>Nome do Responsável pela Pesquisa</strong></label>
        <input type="text" class="form-control" id="nomeResponsavel" placeholder="Digite seu nome completo..." required>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header"><h5 class="mb-0">Itens para Pesquisa</h5></div>
    <div class="card-body">
        <form id="formCotacaoRapida">
            <div id="itens-container">
                <div class="row item-row mb-3 align-items-center">
                    <div class="col-md-3">
                        <label class="form-label"><strong>Código CATMAT</strong></label>
                        <input type="number" class="form-control catmat-input" name="catmat[]" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Descrição do Item (Opcional)</label>
                        <input type="text" class="form-control descricao-input" name="descricao[]">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label"><strong>Quantidade</strong></label>
                        <input type="number" class="form-control" name="quantidade[]" value="1" min="1" required>
                    </div>
                    <div class="col-md-2">
                    <label class="form-label">Região (UF)</label>
                        <select class="form-select" name="regiao[]">
                            <option value="" selected>Todas</option>
                            <?php $estados = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO']; ?>
                            <?php foreach ($estados as $uf): ?><option value="<?= $uf ?>"><?= $uf ?></option><?php endforeach; ?>
                        </select>
                        </div>
                        <div class="col-md-1 pt-4 text-center">
                            <button type="button" class="btn btn-danger btn-sm remove-item" title="Remover este item"><i class="bi bi-trash"></i></button>  
                        </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-3">
    <div>
        <button type="button" class="btn btn-success" id="btn-adicionar-item"><i class="bi bi-plus-lg"></i> Adicionar outro item</button>
        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#modalImportarItens">
            <i class="bi bi-file-earmark-spreadsheet"></i> Importar via Planilha
        </button>
        </div>
    <button type="submit" class="btn btn-primary btn-lg px-4"><i class="bi bi-search"></i> Buscar Preços</button>
</div>

            <div class="modal fade" id="modalImportarItens" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Importar Itens da Planilha</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted">A planilha deve conter o CATMAT (coluna A), a Descrição (coluna B) e a Quantidade (coluna C).</p>
                            <div class="mb-3">
                                <a href="/cotacao-rapida/modelo-planilha" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-file-earmark-spreadsheet"></i> Baixar modelo da planilha
                                </a>
                            </div>
                            <label for="inputPlanilha" class="form-label"><strong>Selecione o arquivo:</strong></label>
                            <input type="file" id="inputPlanilha" class="form-control" accept=".xlsx, .xls, .csv">
                            <div id="loadingImport" class="spinner-border spinner-border-sm mt-2" style="display: none;"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" id="btnConfirmarImportacao">Importar e Preencher</button>
                        </div>
                    </div>
                </div>
            </div>


        </form>
    </div>
</div>

<div id="areaResultados" class="mt-5" style="display: none;">
    
    <div id="loading" class="text-center" style="display: none;">
        <div class="spinner-border text-primary" role="status" style="height: 3rem; width: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 fs-5">Buscando... Isso pode levar alguns segundos.</p>
    </div>

    <div id="boxResultados"></div>

    <div id="areaSalvarRelatorio" class="mt-4 card shadow-sm" style="display: none;">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Salvar Análise e Gerar Relatório</h5>
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-8">
                    <label for="tituloCotacao" class="form-label"><strong>Título para esta Cotação Rápida</strong></label>
                    <input type="text" id="tituloCotacao" class="form-control" placeholder="Ex: Cotação de notebooks para o Depto. de TI" required>
                    <div class="form-text">Este título ajudará a identificar a cotação no histórico de relatórios.</div>
                </div>
                <div class="col-md-4 text-end">
                    <button type="button" class="btn btn-success btn-lg" id="btnSalvarRelatorio">
                        <i class="bi bi-check-circle-fill"></i> Salvar e Gerar PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<template id="item-row-template">
    <div class="row item-row mb-3 align-items-center">
        <div class="col-md-3">
            <label class="form-label"><strong>Código CATMAT</strong></label>
            <input type="number" class="form-control catmat-input" name="catmat[]" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Descrição do Item (Opcional)</label>
            <input type="text" class="form-control descricao-input" name="descricao[]">
        </div>
        <div class="col-md-2">
            <label class="form-label"><strong>Quantidade</strong></label>
            <input type="number" class="form-control" name="quantidade[]" value="1" min="1" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Região (UF)</label>
            <select class="form-select" name="regiao[]">
                <option value="" selected>Todas</option>
                <?php $estados = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO']; ?>
                <?php foreach ($estados as $uf): ?><option value="<?= $uf ?>"><?= $uf ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-1 pt-4 text-center">
            </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Verifica se já existe uma instância do Supabase
    if (window.supabaseClientInstance) {
        console.warn('Instância do Supabase já existe. Reutilizando...');
    } else {
        // Configuração única do Supabase
        const supabaseUrl = 'https://abuowxogoiqzbmnvszys.supabase.co';
        const supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImFidW93eG9nb2lxemJtbnZzenlzIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDkyNTcwNTcsImV4cCI6MjA2NDgzMzA1N30.t6b1vtcZhGfOfibwdWKLDUJq2BoRegH5s6P5_OvRwz8';
        window.supabaseClientInstance = window.supabase.createClient(supabaseUrl, supabaseKey);
    }

    const supabase = window.supabaseClientInstance;
    const itensContainer = document.getElementById('itens-container');
    if (!itensContainer) return;

    // Objeto para controlar as buscas
    const buscaControl = {
        timeout: null,
        lastRequest: null,
        delay: 1500, // Aumentamos o delay para 1.5 segundos
        minLength: 3 // Número mínimo de caracteres para buscar
    };

    const buscarDescricao = async (catmatInput) => {
        const catmatValue = catmatInput.value.trim();
        const itemRow = catmatInput.closest('.item-row');
        const descricaoInput = itemRow.querySelector('.descricao-input');

        // Verifica se o valor é válido
        if (!catmatValue || catmatValue.length < buscaControl.minLength) {
            descricaoInput.value = '';
            return;
        }

        // Cancela a requisição anterior se ainda estiver pendente
        if (buscaControl.lastRequest) {
            buscaControl.lastRequest.abort();
        }

        descricaoInput.value = 'Buscando...';
        
        try {
            // Cria um AbortController para poder cancelar a requisição
            const controller = new AbortController();
            buscaControl.lastRequest = controller;
            
            const { data, error } = await supabase
                .from('catalogo_materiais')
                .select('descricao')
                .eq('codigo_catmat', catmatValue)
                .limit(1)
                .abortSignal(controller.signal)
                .single();

            buscaControl.lastRequest = null;

            if (error) {
                if (error.code === 'PGRST116') { // Nenhum resultado encontrado
                    descricaoInput.value = 'Código não encontrado';
                } else if (error.name !== 'AbortError') {
                    throw error;
                }
            } else if (data) {
                descricaoInput.value = data.descricao;
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Erro ao buscar descrição:', error);
                descricaoInput.value = 'Erro na busca';
            }
        }
    };

    // Evento de input com debounce
    itensContainer.addEventListener('input', (event) => {
        if (!event.target.classList.contains('catmat-input')) {
            return;
        }

        const catmatInput = event.target;
        const itemRow = catmatInput.closest('.item-row');
        const descricaoInput = itemRow.querySelector('.descricao-input');

        // Limpa o timeout anterior
        clearTimeout(buscaControl.timeout);

        // Limpa a descrição se o campo estiver vazio
        if (catmatInput.value.trim() === '') {
            descricaoInput.value = '';
            return;
        }

        // Configura um novo timeout com delay maior
        buscaControl.timeout = setTimeout(() => {
            buscarDescricao(catmatInput);
        }, buscaControl.delay);
    });

    // Limpeza ao sair da página
    window.addEventListener('beforeunload', () => {
        clearTimeout(buscaControl.timeout);
        if (buscaControl.lastRequest) {
            buscaControl.lastRequest.abort();
        }
    });
});
</script>