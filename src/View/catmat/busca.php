<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Cabeçalho da página -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="bi bi-search me-2"></i>Consulta CATMAT</h2>
                    <p class="text-muted mb-0">Sistema de busca inteligente com operadores para encontrar o CATMAT ideal</p>
                </div>
                <div>
                    <a href="/processos" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar aos Processos
                    </a>
                </div>
            </div>

            <!-- Área de Busca Principal -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-search me-2"></i>Busca Inteligente</h5>
                        </div>
                        <div class="card-body">
                            <!-- Campo de busca principal -->
                            <div class="mb-4">
                                <label for="buscaCatmat" class="form-label">
                                    <strong>Digite sua busca:</strong>
                                </label>
                                <div class="position-relative">
                                    <input type="text" 
                                           class="form-control form-control-lg" 
                                           id="buscaCatmat" 
                                           placeholder="Ex: SERINGA + 20ML + DESCARTÁVEL"
                                           autocomplete="off">
                                    <div class="position-absolute top-100 start-0 w-100 bg-white border rounded shadow-sm d-none" 
                                         id="sugestoesList" 
                                         style="z-index: 1050; max-height: 200px; overflow-y: auto;">
                                        <!-- Sugestões aparecerão aqui -->
                                    </div>
                                </div>
                                <div class="form-text">
                                    💡 <strong>Dica:</strong> Use operadores para refinar sua busca (veja exemplos abaixo)
                                </div>
                            </div>

                            <!-- Guia de Operadores -->
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary"><i class="bi bi-info-circle me-1"></i>Operadores de Busca:</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <tbody>
                                                <tr>
                                                    <td><code>+</code></td>
                                                    <td><strong>E/AND</strong></td>
                                                    <td><span class="text-muted">Combina termos</span></td>
                                                </tr>
                                                <tr>
                                                    <td><code>-</code></td>
                                                    <td><strong>EXCLUIR</strong></td>
                                                    <td><span class="text-muted">Remove resultados</span></td>
                                                </tr>
                                                <tr>
                                                    <td><code>|</code></td>
                                                    <td><strong>OU/OR</strong></td>
                                                    <td><span class="text-muted">Alternativas</span></td>
                                                </tr>
                                                <tr>
                                                    <td><code>" "</code></td>
                                                    <td><strong>FRASE EXATA</strong></td>
                                                    <td><span class="text-muted">Busca literal</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-success"><i class="bi bi-lightbulb me-1"></i>Exemplos Práticos:</h6>
                                    <div class="example-searches">
                                        <div class="mb-2">
                                            <button class="btn btn-outline-success btn-sm example-btn" 
                                                    data-search="SERINGA + 20ML + DESCARTÁVEL">
                                                <code>SERINGA + 20ML + DESCARTÁVEL</code>
                                            </button>
                                            <br><small class="text-muted">Seringas de 20ML descartáveis</small>
                                        </div>
                                        <div class="mb-2">
                                            <button class="btn btn-outline-success btn-sm example-btn" 
                                                    data-search="PAPEL + A4 | OFÍCIO">
                                                <code>PAPEL + A4 | OFÍCIO</code>
                                            </button>
                                            <br><small class="text-muted">Papel A4 OU ofício</small>
                                        </div>
                                        <div class="mb-2">
                                            <button class="btn btn-outline-success btn-sm example-btn" 
                                                    data-search="CANETA - GEL">
                                                <code>CANETA - GEL</code>
                                            </button>
                                            <br><small class="text-muted">Canetas exceto as de gel</small>
                                        </div>
                                        <div class="mb-2">
                                            <button class="btn btn-outline-success btn-sm example-btn" 
                                                    data-search='"BICO CATETER 14FR"'>
                                                <code>"BICO CATETER 14FR"</code>
                                            </button>
                                            <br><small class="text-muted">Frase exata</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botões de Ação -->
                            <div class="d-flex gap-2 mt-4">
                                <button type="button" class="btn btn-primary" id="btnBuscar">
                                    <i class="bi bi-search me-1"></i>Buscar CATMATs
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="btnLimpar">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Limpar
                                </button>
                                <button type="button" class="btn btn-outline-info" id="btnHistorico">
                                    <i class="bi bi-clock-history me-1"></i>Histórico
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros Avançados (Colapsável) -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header" data-bs-toggle="collapse" data-bs-target="#filtrosAvancados" style="cursor: pointer;">
                            <h6 class="mb-0">
                                <i class="bi bi-funnel me-2"></i>Filtros Avançados 
                                <small class="text-muted">(clique para expandir)</small>
                            </h6>
                        </div>
                        <div class="collapse" id="filtrosAvancados">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Material:</label>
                                        <select class="form-select" id="filtroMaterial">
                                            <option value="">Todos os materiais</option>
                                            <option value="AÇO">Aço</option>
                                            <option value="PLÁSTICO">Plástico</option>
                                            <option value="PAPEL">Papel</option>
                                            <option value="METAL">Metal</option>
                                        </select>
                                        <div class="form-text">Filtra por material de fabricação</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Categoria:</label>
                                        <select class="form-select" id="filtroCategoria">
                                            <option value="">Todas as categorias</option>
                                            <option value="PEÇAS">Peças/Acessórios</option>
                                            <option value="MUNIÇÃO">Munição</option>
                                            <option value="MEDICAMENTO">Medicamentos</option>
                                        </select>
                                        <div class="form-text">Categoria principal do item</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Aplicação:</label>
                                        <input type="text" class="form-control" id="filtroAplicacao" 
                                               placeholder="Ex: OBUSEIRO M56">
                                        <div class="form-text">Para que é usado o item</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Ordenar por:</label>
                                        <select class="form-select" id="filtroOrdem">
                                            <option value="relevancia">Relevância</option>
                                            <option value="codigo">Código CATMAT</option>
                                            <option value="descricao">Descrição A-Z</option>
                                        </select>
                                        <div class="form-text">Como organizar os resultados</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading -->
            <div class="text-center d-none" id="loadingBusca">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Buscando...</span>
                </div>
                <p class="mt-2 text-muted">Pesquisando CATMATs...</p>
            </div>

            <!-- Área de Resultados -->
            <div class="row" id="resultadosContainer" style="display: none;">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Resultados da Busca</h5>
                            <div>
                                <span id="totalResultados" class="badge bg-light text-dark">0 resultados</span>
                                <button class="btn btn-sm btn-outline-light ms-2" id="btnExportar">
                                    <i class="bi bi-download"></i> Exportar
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <!-- Lista de Resultados -->
                            <div id="listaResultados">
                                <!-- Resultados aparecerão aqui via JavaScript -->
                            </div>

                            <!-- Paginação -->
                            <div class="d-flex justify-content-between align-items-center p-3 border-top">
                                <div>
                                    <small class="text-muted" id="infoResultados"></small>
                                </div>
                                <nav>
                                    <ul class="pagination pagination-sm mb-0" id="paginacao">
                                        <!-- Paginação será gerada via JavaScript -->
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Área vazia (quando não há resultados) -->
            <div class="text-center py-5 d-none" id="semResultados">
                <i class="bi bi-search display-1 text-muted"></i>
                <h4 class="text-muted mt-3">Nenhum CATMAT encontrado</h4>
                <p class="text-muted">Tente usar termos diferentes ou verifique os operadores de busca.</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Adicionar a Processo -->
<div class="modal fade" id="modalAdicionarProcesso" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adicionar CATMAT ao Processo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">CATMAT Selecionado:</label>
                    <div class="p-2 bg-light rounded">
                        <strong id="catmatSelecionado"></strong><br>
                        <small class="text-muted" id="descricaoSelecionada"></small>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="processoDestino" class="form-label">Selecione o Processo:</label>
                    <select class="form-select" id="processoDestino">
                        <option value="">Carregando processos...</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label for="quantidadeItem" class="form-label">Quantidade:</label>
                        <input type="number" class="form-control" id="quantidadeItem" value="1" min="1">
                    </div>
                    <div class="col-md-6">
                        <label for="unidadeItem" class="form-label">Unidade:</label>
                        <input type="text" class="form-control" id="unidadeItem" placeholder="UN, KG, M..." value="UN">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarAdicao">
                    <i class="bi bi-plus-circle me-1"></i>Adicionar ao Processo
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.example-searches .btn {
    text-align: left;
    word-break: break-all;
}
.example-searches code {
    background: transparent;
    color: inherit;
}
#sugestoesList {
    border-top: none !important;
}
.sugestao-item {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
}
.sugestao-item:hover {
    background-color: #f8f9fa;
}
.resultado-catmat {
    transition: all 0.2s ease;
}
.resultado-catmat:hover {
    background-color: #f8f9fa;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const buscaInput = document.getElementById('buscaCatmat');
    const btnBuscar = document.getElementById('btnBuscar');
    const btnLimpar = document.getElementById('btnLimpar');
    const btnHistorico = document.getElementById('btnHistorico');
    const sugestoesList = document.getElementById('sugestoesList');
    
    // Verificação de segurança
    if (!buscaInput) {
        console.error('Elemento buscaCatmat não encontrado');
        return;
    }
    
    // Event listeners para busca
    buscaInput.addEventListener('input', debounce(function() {
        const termo = this.value.trim();
        if (termo.length >= 2) {
            buscarSugestoes(termo);
        } else {
            ocultarSugestoes();
        }
    }, 300));

    // Busca ao pressionar Enter
    buscaInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            realizarBusca();
        }
    });

    // Botão de buscar
    btnBuscar.addEventListener('click', realizarBusca);

    // Botão limpar
    btnLimpar.addEventListener('click', function() {
        buscaInput.value = '';
        limparResultados();
        ocultarSugestoes();
    });

    // Exemplos clicáveis
    document.querySelectorAll('.example-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const searchExample = this.dataset.search;
            buscaInput.value = searchExample;
            realizarBusca();
        });
    });

    // Função de busca principal
    async function realizarBusca() {
        const query = buscaInput.value?.trim();
        if (!query) {
            showAlert('warning', 'Digite algo para buscar!');
            return;
        }

        mostrarLoading();
        ocultarSugestoes();

        try {
            const response = await fetch('/api/catmat/pesquisar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ query: query })
            });
            
            const data = await response.json();
            
            if (data.success) {
                exibirResultados(data.data);
            } else {
                showAlert('error', data.message || 'Erro na busca');
            }
        } catch (error) {
            console.error('Erro:', error);
            showAlert('error', 'Erro ao realizar busca');
        } finally {
            ocultarLoading();
        }
    }

    // Função para buscar sugestões
    async function buscarSugestoes(termo) {
        try {
            const response = await fetch(`/api/catmat/sugestoes?q=${encodeURIComponent(termo)}`);
            const data = await response.json();
            
            if (Array.isArray(data)) {
                exibirSugestoes(data);
            } else {
                exibirSugestoes([]);
            }
        } catch (error) {
            console.error('Erro nas sugestões:', error);
            exibirSugestoes([]);
        }
    }

    // Exibir sugestões
    function exibirSugestoes(sugestoes) {
        sugestoesList.innerHTML = '';
        
        if (!sugestoes || sugestoes.length === 0) {
            ocultarSugestoes();
            return;
        }
        
        sugestoes.forEach(sugestao => {
            const item = document.createElement('div');
            item.className = 'sugestao-item';
            
            // Sugestao pode ser string ou objeto
            const texto = typeof sugestao === 'string' ? sugestao : (sugestao.texto || sugestao);
            item.textContent = texto;
            
            item.addEventListener('click', function() {
                buscaInput.value = texto;
                ocultarSugestoes();
                realizarBusca();
            });
            sugestoesList.appendChild(item);
        });
        
        sugestoesList.classList.remove('d-none');
    }

    // Ocultar sugestões
    function ocultarSugestoes() {
        sugestoesList.classList.add('d-none');
    }

    // Exibir resultados
    function exibirResultados(dados) {
        const container = document.getElementById('listaResultados');
        const totalElement = document.getElementById('totalResultados');
        
        totalElement.textContent = `${dados.total} resultado(s)`;
        
        let html = '';
        dados.results.forEach(item => {
            html += `
                <div class="resultado-catmat p-3 border-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-1">
                                <span class="badge bg-primary me-2">${item.catmat}</span>
                                <span class="badge bg-success ms-1">${item.relevancia}%</span>
                            </h6>
                            <p class="mb-0 text-muted">${item.descricao}</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn btn-outline-primary btn-sm me-1" onclick="verDetalhes('${item.catmat}')">
                                <i class="bi bi-eye"></i> Ver Detalhes
                            </button>
                            <button class="btn btn-primary btn-sm" onclick="adicionarAoProcesso('${item.catmat}', '${item.descricao}')">
                                <i class="bi bi-plus-circle"></i> Adicionar
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
        document.getElementById('resultadosContainer').style.display = 'block';
    }

    // Mostrar loading
    function mostrarLoading() {
        document.getElementById('loadingBusca').classList.remove('d-none');
        document.getElementById('resultadosContainer').style.display = 'none';
        document.getElementById('semResultados').classList.add('d-none');
    }

    // Ocultar loading
    function ocultarLoading() {
        document.getElementById('loadingBusca').classList.add('d-none');
    }

    // Limpar resultados
    function limparResultados() {
        document.getElementById('resultadosContainer').style.display = 'none';
        document.getElementById('semResultados').classList.add('d-none');
    }

    // Função debounce
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Função para mostrar alertas
    function showAlert(type, message) {
        // Implementar sistema de alertas
        alert(message); // Temporário
    }

    // Ocultar sugestões ao clicar fora
    document.addEventListener('click', function(e) {
        if (!buscaInput.contains(e.target) && !sugestoesList.contains(e.target)) {
            ocultarSugestoes();
        }
    });
});

// Funções globais para os botões dos resultados
function verDetalhes(catmat) {
    alert(`Ver detalhes do CATMAT: ${catmat}`);
    // Implementar modal ou página de detalhes
}

async function adicionarAoProcesso(catmat, descricao) {
    document.getElementById('catmatSelecionado').textContent = catmat;
    document.getElementById('descricaoSelecionada').textContent = descricao;
    
    // Carregar lista de processos REAL
    const processoSelect = document.getElementById('processoDestino');
    processoSelect.innerHTML = '<option value="">Carregando processos...</option>';
    
    try {
        const response = await fetch('/api/catmat/processos');
        const processos = await response.json();
        
        processoSelect.innerHTML = '<option value="">Selecione um processo</option>';
        
        if (processos && processos.length > 0) {
            processos.forEach(processo => {
                const option = document.createElement('option');
                option.value = processo.id;
                option.textContent = processo.nome;
                processoSelect.appendChild(option);
            });
        } else {
            processoSelect.innerHTML = '<option value="">Nenhum processo disponível</option>';
        }
    } catch (error) {
        console.error('Erro ao carregar processos:', error);
        processoSelect.innerHTML = '<option value="">Erro ao carregar processos</option>';
    }
    
    const modal = new bootstrap.Modal(document.getElementById('modalAdicionarProcesso'));
    modal.show();
}
</script>