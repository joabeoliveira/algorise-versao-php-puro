// A LINHA MAIS IMPORTANTE: garante que o código abaixo só rode
// depois que todo o HTML da página foi carregado pelo navegador.
document.addEventListener('DOMContentLoaded', () => {

    // I. Configuração do Cliente Supabase
    const supabaseUrl = 'https://abuowxogoiqzbmnvszys.supabase.co';
    const supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImFidW93eG9nb2lxemJtbnZzenlzIiwicm9sZSI6ImFub24iLCJpYXQiOjE3MjA0NjI5MDQsImV4cCI6MjAzNjAzODkwNH0.pB_CYcdoJJz3GW7Ojh6Ms1TgTh4xs4-_G1hOMgOLQoM';
    
    // Verificar se o Supabase está disponível
    if (typeof window.supabase === 'undefined') {
        console.error('Supabase client não carregado. Verifique se o script está incluído na página.');
        return;
    }
    
    const supabase = window.supabase.createClient(supabaseUrl, supabaseKey);

    // II. Seleção dos Elementos do DOM
    const inputBuscaModal = document.getElementById('inputBuscaModal');
    const listaSugestoes = document.getElementById('listaSugestoes');
    const btnLimpar = document.getElementById('btnLimpar');
    const inputCatmatPrincipal = document.getElementById('catmat_input');
    const inputDescricaoPrincipal = document.getElementById('descricao_input');
    const modalElement = document.getElementById('modalBuscaCatmat');
    
    // Apenas cria a instância do Modal se o elemento existir na página
    let modalInstance = null;
    if (modalElement) {
        modalInstance = new bootstrap.Modal(modalElement);
    }

    // III. Funções e Lógica de Busca
    if (btnLimpar) {
        btnLimpar.addEventListener('click', () => {
            inputBuscaModal.value = '';
            listaSugestoes.innerHTML = '';
            inputBuscaModal.focus();
        });
    }

    // Função para processar a query e extrair termos com operadores
    function processarQuery(query) {
        // Remove espaços extras e divide em termos
        const termos = query.trim().split(/\s+/);
        
        // Separa termos normais e termos com operador +
        const termosNormais = [];
        const termosAnd = [];
        
        for (let i = 0; i < termos.length; i++) {
            if (termos[i] === '+' && i < termos.length - 1) {
                // Pega o próximo termo como parte de AND
                termosAnd.push(termos[i + 1]);
                i++; // Pula o próximo termo pois já foi processado
            } else if (termos[i] !== '+') {
                termosNormais.push(termos[i]);
            }
        }
        
        return {
            termosNormais,
            termosAnd
        };
    }

    async function buscarSugestoes(query) {
        if (query.length < 2) {
            listaSugestoes.innerHTML = '';
            return;
        }

        // Processa a query para extrair termos com operadores
        const { termosNormais, termosAnd } = processarQuery(query);
        
        // Se não houver termos normais (apenas operadores), não faz a busca
        if (termosNormais.length === 0) {
            listaSugestoes.innerHTML = '<li class="list-group-item">Digite pelo menos um termo de busca.</li>';
            return;
        }

        // Monta o texto de busca para a função RPC
        const textoBusca = termosNormais.join(' ');
        
        // Chama a função RPC no Supabase
        const { data, error } = await supabase.rpc('buscar_itens_similares', {
            texto_busca: textoBusca,
            limite: 20,
            offset_val: 0,
        });

        if (error) {
            console.error('Erro na busca:', error);
            listaSugestoes.innerHTML = '<li class="list-group-item text-danger">Erro na busca.</li>';
            return;
        }

        if (!data || data.length === 0) {
            listaSugestoes.innerHTML = '<li class="list-group-item">Nenhuma sugestão encontrada.</li>';
            return;
        }

        // Filtra os resultados se houver termos AND
        let resultadosFiltrados = data;
        if (termosAnd.length > 0) {
            resultadosFiltrados = data.filter(item => {
                const descricaoLower = item.descricao.toLowerCase();
                return termosAnd.every(termo => 
                    descricaoLower.includes(termo.toLowerCase())
                );
            });
        }

        if (resultadosFiltrados.length === 0) {
            listaSugestoes.innerHTML = '<li class="list-group-item">Nenhum item encontrado com todos os termos especificados.</li>';
            return;
        }

        listaSugestoes.innerHTML = '';
        resultadosFiltrados.forEach(item => {
            const li = document.createElement('li');
            li.classList.add('list-group-item', 'd-flex', 'flex-column');
            li.style.cursor = 'pointer';
            li.innerHTML = `
                <div class="item-code">${item.codigo_catmat}</div>
                <div class="item-desc">${item.descricao}</div>
            `;

            li.addEventListener('click', () => {
                if (inputCatmatPrincipal) {
                    inputCatmatPrincipal.value = item.codigo_catmat;
                }
                if (inputDescricaoPrincipal) {
                    inputDescricaoPrincipal.value = item.descricao;
                }

                inputBuscaModal.value = '';
                listaSugestoes.innerHTML = '';

                if (modalInstance) {
                    modalInstance.hide();
                }
            });

            listaSugestoes.appendChild(li);
        });
    }

    if (inputBuscaModal) {
        let timeoutId;
        inputBuscaModal.addEventListener('input', () => {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                buscarSugestoes(inputBuscaModal.value);
            }, 300);
        });
    }
// Fim do bloco 'DOMContentLoaded'
});