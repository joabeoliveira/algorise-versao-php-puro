document.addEventListener('DOMContentLoaded', () => {

    // Configuração - Usa banco de dados local via API
    console.log('[CATMAT Search] Usando API local do banco de dados');
    
    // Seleção dos Elementos do DOM
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

    // Funções e Lógica de Busca
    if (btnLimpar) {
        btnLimpar.addEventListener('click', () => {
            inputBuscaModal.value = '';
            listaSugestoes.innerHTML = '';
            inputBuscaModal.focus();
        });
    }

    async function buscarSugestoes(query) {
        if (query.length < 2) {
            listaSugestoes.innerHTML = '';
            return;
        }

        try {
            // Chama a API local para buscar itens CATMAT
            const response = await fetch('/api/catmat/pesquisar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ query: query })
            });

            const result = await response.json();

            if (!result.success || !result.data || !result.data.results) {
                listaSugestoes.innerHTML = '<li class="list-group-item">Nenhuma sugestão encontrada.</li>';
                return;
            }

            const resultados = result.data.results;

            if (resultados.length === 0) {
                listaSugestoes.innerHTML = '<li class="list-group-item">Nenhuma sugestão encontrada.</li>';
                return;
            }

            listaSugestoes.innerHTML = '';
            resultados.forEach(item => {
                const li = document.createElement('li');
                li.classList.add('list-group-item', 'd-flex', 'flex-column');
                li.style.cursor = 'pointer';
                li.innerHTML = `
                    <div class="item-code">${item.catmat}</div>
                    <div class="item-desc">${item.descricao}</div>
                `;

                li.addEventListener('click', () => {
                    if (inputCatmatPrincipal) {
                        inputCatmatPrincipal.value = item.catmat;
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
        } catch (error) {
            console.error('Erro na busca:', error);
            listaSugestoes.innerHTML = '<li class="list-group-item text-danger">Erro na busca.</li>';
        }
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
});
