/**
 * Sistema de Busca CATMAT - JavaScript
 * Implementa funcionalidades de busca inteligente com operadores
 */

class CatmatSearch {
    constructor() {
        this.initializeElements();
        this.attachEventListeners();
        this.loadHistorico();
    }

    // Inicializar elementos DOM
    initializeElements() {
        this.buscaInput = document.getElementById('buscaCatmat');
        this.btnBuscar = document.getElementById('btnBuscar');
        this.btnLimpar = document.getElementById('btnLimpar');
        this.btnHistorico = document.getElementById('btnHistorico');
        this.sugestoesList = document.getElementById('sugestoesList');
        this.loadingContainer = document.getElementById('loadingBusca');
        this.resultadosContainer = document.getElementById('resultadosContainer');
        this.semResultadosContainer = document.getElementById('semResultados');
        this.listaResultados = document.getElementById('listaResultados');
        this.totalResultados = document.getElementById('totalResultados');
        
        // Filtros
        this.filtroMaterial = document.getElementById('filtroMaterial');
        this.filtroCategoria = document.getElementById('filtroCategoria');
        this.filtroAplicacao = document.getElementById('filtroAplicacao');
        this.filtroOrdem = document.getElementById('filtroOrdem');
        
        // Estado da busca
        this.currentPage = 1;
        this.resultsPerPage = 10;
        this.lastQuery = '';
        this.searchHistory = this.getStoredHistory();
    }

    // Anexar event listeners
    attachEventListeners() {
        // Campo de busca
        this.buscaInput.addEventListener('input', this.debounce(() => {
            this.handleInputChange();
        }, 300));

        this.buscaInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.performSearch();
            }
        });

        // Botões principais
        this.btnBuscar.addEventListener('click', () => this.performSearch());
        this.btnLimpar.addEventListener('click', () => this.clearSearch());
        this.btnHistorico.addEventListener('click', () => this.showHistorico());

        // Exemplos de busca
        document.querySelectorAll('.example-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const searchExample = e.target.closest('.example-btn').dataset.search;
                this.buscaInput.value = searchExample;
                this.performSearch();
            });
        });

        // Ocultar sugestões ao clicar fora
        document.addEventListener('click', (e) => {
            if (!this.buscaInput.contains(e.target) && !this.sugestoesList.contains(e.target)) {
                this.hideSugestoes();
            }
        });

        // Filtros avançados
        [this.filtroMaterial, this.filtroCategoria, this.filtroAplicacao, this.filtroOrdem].forEach(filter => {
            if (filter) {
                filter.addEventListener('change', () => {
                    if (this.lastQuery) {
                        this.performSearch();
                    }
                });
            }
        });
    }

    // Manipular mudanças no campo de entrada
    handleInputChange() {
        const query = this.buscaInput.value.trim();
        
        if (query.length >= 2) {
            this.loadSugestoes(query);
        } else {
            this.hideSugestoes();
        }
    }

    // Carregar sugestões
    async loadSugestoes(query) {
        try {
            const response = await fetch(`/api/catmat/sugestoes?q=${encodeURIComponent(query)}`);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            const sugestoes = await response.json();
            
            this.displaySugestoes(sugestoes);
        } catch (error) {
            console.error('Erro ao carregar sugestões:', error);
        }
    }

    // Exibir sugestões
    displaySugestoes(sugestoes) {
        this.sugestoesList.innerHTML = '';
        
        if (!sugestoes || sugestoes.length === 0) {
            this.hideSugestoes();
            return;
        }

        // Se sugestoes é um array direto, usa ele; senão pega a propriedade 'sugestoes'
        const listaSugestoes = Array.isArray(sugestoes) ? sugestoes : (sugestoes.sugestoes || []);

        listaSugestoes.forEach(sugestao => {
            const item = document.createElement('div');
            item.className = 'sugestao-item';
            
            // Sugestao pode ser string ou objeto
            const texto = typeof sugestao === 'string' ? sugestao : (sugestao.texto || sugestao);
            const tipo = typeof sugestao === 'object' ? (sugestao.tipo || 'Termo') : 'Termo';
            
            item.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <span>${this.highlightMatch(texto, this.buscaInput.value)}</span>
                    <small class="text-muted">${tipo}</small>
                </div>
            `;
            
            item.addEventListener('click', () => {
                this.buscaInput.value = texto;
                this.hideSugestoes();
                this.performSearch();
            });
            
            this.sugestoesList.appendChild(item);
        });
        
        this.sugestoesList.classList.remove('d-none');
    }

    // Destacar texto correspondente
    highlightMatch(text, query) {
        const regex = new RegExp(`(${this.escapeRegex(query)})`, 'gi');
        return text.replace(regex, '<strong>$1</strong>');
    }

    // Escape de regex
    escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    // Ocultar sugestões
    hideSugestoes() {
        this.sugestoesList.classList.add('d-none');
    }

    // Realizar busca
    async performSearch(page = 1) {
        const query = this.buscaInput.value.trim();
        
        if (!query) {
            this.showAlert('warning', 'Digite algo para buscar!');
            return;
        }

        this.currentPage = page;
        this.lastQuery = query;
        this.addToHistory(query);

        this.showLoading();
        this.hideSugestoes();

        try {
            const searchData = this.buildSearchData(query, page);
            
            const response = await fetch('/api/catmat/pesquisar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(searchData)
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const results = await response.json();
            
            if (results.success) {
                this.displayResults(results.data);
            } else {
                this.showError(results.message || 'Erro na busca');
            }
            
        } catch (error) {
            console.error('Erro na busca:', error);
            this.showError('Erro ao realizar busca. Tente novamente.');
        } finally {
            this.hideLoading();
        }
    }

    // Construir dados da busca
    buildSearchData(query, page) {
        return {
            query: query,
            page: page,
            limit: this.resultsPerPage,
            filters: {
                material: this.filtroMaterial?.value || '',
                categoria: this.filtroCategoria?.value || '',
                aplicacao: this.filtroAplicacao?.value || '',
                ordem: this.filtroOrdem?.value || 'relevancia'
            }
        };
    }

    // Exibir resultados
    displayResults(data) {
        const { results, total, pagination } = data;
        
        this.totalResultados.textContent = `${total} resultado(s)`;
        
        // Oculta sempre a mensagem "sem resultados" primeiro
        this.semResultadosContainer.classList.add('d-none');
        
        if (!results || results.length === 0) {
            this.showNoResults();
            return;
        }

        let html = '';
        results.forEach(item => {
            html += this.renderResultItem(item);
        });
        
        this.listaResultados.innerHTML = html;
        this.renderPagination(pagination);
        
        // Mostra resultados e oculta área vazia
        this.resultadosContainer.style.display = 'block';
        this.semResultadosContainer.classList.add('d-none');
        
        // Scroll para os resultados
        this.resultadosContainer.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
    }

    // Renderizar item de resultado
    renderResultItem(item) {
        const relevancia = item.relevancia || 0;
        const relevanciaBadge = relevancia >= 90 ? 'bg-success' : 
                              relevancia >= 70 ? 'bg-warning' : 'bg-secondary';
        
        return `
            <div class="resultado-catmat fade-in" data-catmat="${item.catmat}">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="mb-2">
                            <span class="catmat-codigo me-2">${item.catmat}</span>
                            <span class="relevancia-badge ${relevanciaBadge}">${relevancia}% relevância</span>
                        </div>
                        <div class="resultado-descricao">
                            ${this.highlightSearchTerms(item.descricao, this.lastQuery)}
                        </div>
                        ${item.material ? `<small class="text-muted"><strong>Material:</strong> ${item.material}</small><br>` : ''}
                        ${item.categoria ? `<small class="text-muted"><strong>Categoria:</strong> ${item.categoria}</small>` : ''}
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="btn-group-vertical btn-group-sm gap-1" role="group">
                            <button class="btn btn-outline-primary-custom" onclick="catmatSearch.viewDetails('${item.catmat}')">
                                <i class="bi bi-eye"></i> Ver Detalhes
                            </button>
                            <button class="btn btn-primary-gradient" onclick="catmatSearch.addToProcess('${item.catmat}', '${this.escapeHtml(item.descricao)}')">
                                <i class="bi bi-plus-circle"></i> Adicionar ao Processo
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Destacar termos de busca no resultado
    highlightSearchTerms(text, query) {
        // Parse da query para identificar operadores
        const terms = this.parseSearchQuery(query);
        let highlightedText = text;
        
        terms.required.forEach(term => {
            const regex = new RegExp(`(${this.escapeRegex(term)})`, 'gi');
            highlightedText = highlightedText.replace(regex, '<mark class="bg-warning">$1</mark>');
        });
        
        return highlightedText;
    }

    // Parse da query de busca
    parseSearchQuery(query) {
        const terms = {
            required: [],
            excluded: [],
            optional: [],
            exact: []
        };
        
        // Expressões regulares para diferentes operadores
        const patterns = {
            exact: /"([^"]+)"/g,
            excluded: /-(\w+)/g,
            optional: /\|(\w+)/g,
            required: /\+?(\w+)/g
        };
        
        // Extrair frases exatas
        let match;
        while ((match = patterns.exact.exec(query)) !== null) {
            terms.exact.push(match[1]);
        }
        
        // Remover frases exatas da query para processar o resto
        let cleanQuery = query.replace(patterns.exact, '');
        
        // Extrair termos excluídos
        while ((match = patterns.excluded.exec(cleanQuery)) !== null) {
            terms.excluded.push(match[1]);
        }
        
        // Extrair termos opcionais
        while ((match = patterns.optional.exec(cleanQuery)) !== null) {
            terms.optional.push(match[1]);
        }
        
        // Extrair termos obrigatórios
        cleanQuery = cleanQuery.replace(patterns.excluded, '').replace(patterns.optional, '');
        const requiredMatches = cleanQuery.match(/\+?(\w+)/g);
        if (requiredMatches) {
            terms.required.push(...requiredMatches.map(t => t.replace(/^\+/, '')));
        }
        
        return terms;
    }

    // Renderizar paginação
    renderPagination(pagination) {
        if (!pagination || pagination.totalPages <= 1) {
            return;
        }
        
        const paginationElement = document.getElementById('paginacao');
        if (!paginationElement) return;
        
        let html = '';
        
        // Botão anterior
        const prevDisabled = pagination.currentPage === 1 ? 'disabled' : '';
        html += `<li class="page-item ${prevDisabled}">
            <a class="page-link" href="#" onclick="catmatSearch.goToPage(${pagination.currentPage - 1})">Anterior</a>
        </li>`;
        
        // Páginas
        const startPage = Math.max(1, pagination.currentPage - 2);
        const endPage = Math.min(pagination.totalPages, pagination.currentPage + 2);
        
        for (let i = startPage; i <= endPage; i++) {
            const active = i === pagination.currentPage ? 'active' : '';
            html += `<li class="page-item ${active}">
                <a class="page-link" href="#" onclick="catmatSearch.goToPage(${i})">${i}</a>
            </li>`;
        }
        
        // Botão próximo
        const nextDisabled = pagination.currentPage === pagination.totalPages ? 'disabled' : '';
        html += `<li class="page-item ${nextDisabled}">
            <a class="page-link" href="#" onclick="catmatSearch.goToPage(${pagination.currentPage + 1})">Próximo</a>
        </li>`;
        
        paginationElement.innerHTML = html;
        
        // Info dos resultados
        const infoElement = document.getElementById('infoResultados');
        if (infoElement) {
            const start = ((pagination.currentPage - 1) * this.resultsPerPage) + 1;
            const end = Math.min(start + this.resultsPerPage - 1, pagination.totalItems);
            infoElement.textContent = `Mostrando ${start}-${end} de ${pagination.totalItems} resultados`;
        }
    }

    // Ir para página específica
    goToPage(page) {
        if (page < 1 || !this.lastQuery) return;
        this.performSearch(page);
    }

    // Ver detalhes do CATMAT
    viewDetails(catmat) {
        // Implementar modal ou página de detalhes
        this.showAlert('info', `Detalhes do CATMAT ${catmat} - Em desenvolvimento`);
    }

    // Adicionar CATMAT ao processo
    addToProcess(catmat, descricao) {
        document.getElementById('catmatSelecionado').textContent = catmat;
        document.getElementById('descricaoSelecionada').textContent = descricao;
        
        // Carregar lista de processos
        this.loadProcessos();
        
        const modal = new bootstrap.Modal(document.getElementById('modalAdicionarProcesso'));
        modal.show();
    }

    // Carregar lista de processos
    async loadProcessos() {
        try {
            console.log('Carregando processos reais da API...');
            
            const response = await fetch('/api/catmat/processos');
            if (!response.ok) {
                throw new Error('Erro ao carregar processos');
            }
            
            const processos = await response.json();
            console.log('Processos carregados:', processos);
            
            const select = document.getElementById('processoDestino');
            select.innerHTML = '<option value="">Selecione um processo</option>';
            
            processos.forEach(processo => {
                const option = document.createElement('option');
                option.value = processo.id;
                option.textContent = processo.nome;
                select.appendChild(option);
            });
            
            console.log(`${processos.length} processo(s) adicionado(s) ao select`);
            
        } catch (error) {
            console.error('Erro ao carregar processos:', error);
            
            // Fallback em caso de erro
            const select = document.getElementById('processoDestino');
            select.innerHTML = '<option value="">Erro ao carregar processos</option>';
        }
    }

    // Mostrar loading
    showLoading() {
        this.loadingContainer.classList.remove('d-none');
        this.resultadosContainer.style.display = 'none';
        this.semResultadosContainer.classList.add('d-none');
    }

    // Ocultar loading
    hideLoading() {
        this.loadingContainer.classList.add('d-none');
    }

    // Mostrar quando não há resultados
    showNoResults() {
        this.resultadosContainer.style.display = 'none';
        this.semResultadosContainer.classList.remove('d-none');
    }

    // Limpar busca
    clearSearch() {
        this.buscaInput.value = '';
        this.lastQuery = '';
        this.hideSugestoes();
        this.resultadosContainer.style.display = 'none';
        this.semResultadosContainer.classList.add('d-none'); // Garante que fica oculto
        
        // Resetar filtros
        [this.filtroMaterial, this.filtroCategoria, this.filtroAplicacao].forEach(filter => {
            if (filter) filter.value = '';
        });
        if (this.filtroOrdem) this.filtroOrdem.value = 'relevancia';
    }

    // Mostrar histórico
    showHistorico() {
        if (this.searchHistory.length === 0) {
            this.showAlert('info', 'Nenhuma busca no histórico');
            return;
        }
        
        let html = '<div class="list-group">';
        this.searchHistory.slice(0, 10).forEach((item, index) => {
            html += `
                <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" 
                        onclick="catmatSearch.useHistoryItem('${this.escapeHtml(item.query)}')">
                    <span>${item.query}</span>
                    <small class="text-muted">${item.date}</small>
                </button>
            `;
        });
        html += '</div>';
        
        this.showModal('Histórico de Buscas', html);
    }

    // Usar item do histórico
    useHistoryItem(query) {
        this.buscaInput.value = query;
        this.performSearch();
        this.closeModal();
    }

    // Gerenciar histórico
    addToHistory(query) {
        const historyItem = {
            query: query,
            date: new Date().toLocaleString('pt-BR')
        };
        
        // Remove duplicatas
        this.searchHistory = this.searchHistory.filter(item => item.query !== query);
        
        // Adiciona no início
        this.searchHistory.unshift(historyItem);
        
        // Limita a 50 itens
        if (this.searchHistory.length > 50) {
            this.searchHistory = this.searchHistory.slice(0, 50);
        }
        
        this.saveHistory();
    }

    getStoredHistory() {
        try {
            return JSON.parse(localStorage.getItem('catmat_search_history') || '[]');
        } catch {
            return [];
        }
    }

    saveHistory() {
        try {
            localStorage.setItem('catmat_search_history', JSON.stringify(this.searchHistory));
        } catch (error) {
            console.warn('Erro ao salvar histórico:', error);
        }
    }

    loadHistorico() {
        this.searchHistory = this.getStoredHistory();
    }

    // Utilities
    debounce(func, wait) {
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

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    showAlert(type, message) {
        // Sistema simples de alertas - pode ser melhorado
        const alertClass = {
            'success': 'alert-success',
            'warning': 'alert-warning', 
            'error': 'alert-danger',
            'info': 'alert-info'
        }[type] || 'alert-info';
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    showError(message) {
        this.showAlert('error', message);
        this.showNoResults();
    }

    showModal(title, content) {
        // Sistema simples de modal - pode ser melhorado
        const modalHtml = `
            <div class="modal fade" id="dynamicModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ${content}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('dynamicModal'));
        modal.show();
        
        // Remove modal após fechar
        document.getElementById('dynamicModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }

    closeModal() {
        const modal = document.getElementById('dynamicModal');
        if (modal) {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        }
    }
}

// Inicializar quando a página carregar
let catmatSearch;

document.addEventListener('DOMContentLoaded', function() {
    catmatSearch = new CatmatSearch();
    
    // Configurar modal de adicionar ao processo
    const btnConfirmarAdicao = document.getElementById('btnConfirmarAdicao');
    if (btnConfirmarAdicao) {
        btnConfirmarAdicao.addEventListener('click', async function() {
            const catmat = document.getElementById('catmatSelecionado').textContent;
            const descricao = document.getElementById('descricaoSelecionada').textContent;
            const processo = document.getElementById('processoDestino').value;
            const quantidade = document.getElementById('quantidadeItem').value;
            const unidade = document.getElementById('unidadeItem').value;
            
            if (!processo) {
                catmatSearch.showAlert('warning', 'Selecione um processo');
                return;
            }
            
            // Desabilita botão durante processamento
            btnConfirmarAdicao.disabled = true;
            btnConfirmarAdicao.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Adicionando...';
            
            try {
                const response = await fetch('/api/catmat/adicionar-item', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        processo_id: processo,
                        catmat: catmat,
                        descricao: descricao,
                        quantidade: quantidade,
                        unidade: unidade
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    catmatSearch.showAlert('success', result.message);
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalAdicionarProcesso'));
                    modal.hide();
                } else {
                    catmatSearch.showAlert('error', result.message || 'Erro ao adicionar item');
                }
            } catch (error) {
                console.error('Erro:', error);
                catmatSearch.showAlert('error', 'Erro de conexão. Tente novamente.');
            } finally {
                // Restaura botão
                btnConfirmarAdicao.disabled = false;
                btnConfirmarAdicao.innerHTML = '<i class="bi bi-plus-circle me-1"></i>Adicionar ao Processo';
            }
        });
    }
});