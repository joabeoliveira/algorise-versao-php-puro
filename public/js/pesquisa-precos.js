document.addEventListener('DOMContentLoaded', () => {
    const btnBuscarPainel = document.getElementById('btnBuscarPainel');
    const loadingPainel = document.getElementById('loadingPainel');
    const tabelaResultados = document.getElementById('tabelaResultadosPainel');
    const btnAdicionarSelecionados = document.getElementById('btnAdicionarSelecionados');
    
    let modalResultados = null;
    const modalElement = document.getElementById('modalResultadosPainel');
    if (modalElement) {
        modalResultados = new bootstrap.Modal(modalElement);
    }

    if (!btnBuscarPainel) return;

    // Função para buscar no Painel de Preços (sem alterações)
    btnBuscarPainel.addEventListener('click', async () => {
        loadingPainel.style.display = 'block';
        btnBuscarPainel.disabled = true;

        try {
            const catmat = btnBuscarPainel.dataset.catmat;
            const response = await fetch('/api/painel-de-precos', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ catmat: catmat })
            });
            const data = await response.json();
            
            tabelaResultados.innerHTML = ''; 

            if (data.resultado && data.resultado.length > 0) {
                data.resultado.forEach(item => {
                    const tr = document.createElement('tr');
                    const itemDataString = JSON.stringify(item).replace(/'/g, "&apos;");

                    tr.innerHTML = `
                        <td><input type="checkbox" class="form-check-input cota-checkbox" data-item='${itemDataString}'></td>
                        <td>R$ ${parseFloat(item.precoUnitario).toFixed(2).replace('.', ',')}</td>
                        <td>${item.siglaUnidadeFornecimento || 'N/A'}</td>
                        <td>${item.capacidadeUnidadeFornecimento > 0 ? `${item.capacidadeUnidadeFornecimento} ${item.siglaUnidadeMedida}` : 'N/A'}</td>
                        <td>${item.nomeFornecedor || 'N/A'}</td>
                        <td>${new Date(item.dataResultado).toLocaleDateString('pt-BR')}</td>
                    `;
                    tabelaResultados.appendChild(tr);
                });
            } else {
                tabelaResultados.innerHTML = '<tr><td colspan="6" class="text-center">Nenhum resultado encontrado.</td></tr>';
            }
            modalResultados.show();
        } catch (error) {
            console.error('Erro:', error);
            alert('Ocorreu um erro ao buscar os dados.');
        } finally {
            loadingPainel.style.display = 'none';
            btnBuscarPainel.disabled = false;
        }
    });

    // =======================================================
    //   INÍCIO DA LÓGICA ATUALIZADA PARA ENVIAR EM LOTE
    // =======================================================
    btnAdicionarSelecionados.addEventListener('click', async () => {
        const checkboxes = document.querySelectorAll('.cota-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Por favor, selecione pelo menos uma cotação.');
            return;
        }

        // 1. Coleta todos os dados em um array
        const precosParaSalvar = [];
        checkboxes.forEach(box => {
            const itemData = JSON.parse(box.dataset.item);
            let unidadeCompleta = itemData.siglaUnidadeFornecimento || '';
            if (itemData.capacidadeUnidadeFornecimento > 0 && itemData.siglaUnidadeMedida) {
                unidadeCompleta += ` c/ ${itemData.capacidadeUnidadeFornecimento} ${itemData.siglaUnidadeMedida}`;
            }

            precosParaSalvar.push({
                fonte: 'Painel de Preços',
                valor: parseFloat(itemData.precoUnitario).toFixed(2),
                unidade_medida: unidadeCompleta,
                data_coleta: itemData.dataResultado.split('T')[0],
                fornecedor_nome: itemData.nomeFornecedor,
                fornecedor_cnpj: itemData.niFornecedor,
                link_evidencia: null
            });
        });

        // Desabilita o botão para evitar cliques duplos
        btnAdicionarSelecionados.disabled = true;
        btnAdicionarSelecionados.textContent = 'Salvando...';

        try {
            // 2. Envia o array para a nova API
            const url = '/api' + window.location.pathname.replace('/pesquisar', '/precos/lote');
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(precosParaSalvar)
            });

            if (!response.ok) {
                throw new Error('Falha ao salvar os dados no servidor.');
            }

            // 3. Se tudo deu certo, recarrega a página para mostrar os novos dados
            modalResultados.hide();
            window.location.reload();

        } catch (error) {
            console.error('Erro ao salvar em lote:', error);
            alert('Ocorreu um erro ao salvar as cotações. Tente novamente.');
            btnAdicionarSelecionados.disabled = false;
            btnAdicionarSelecionados.textContent = 'Adicionar Cotações Selecionadas';
        }
    });
});