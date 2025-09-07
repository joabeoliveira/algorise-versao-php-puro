document.addEventListener('DOMContentLoaded', () => {
    // Seletores dos elementos do modal de busca em órgãos
    const modalElement = document.getElementById('modalBuscaOrgaos');
    if (!modalElement) return; // Se o modal não existe na página, não faz nada.

    const btnExecutarBuscaUasg = document.getElementById('btnExecutarBuscaUasg');
    const btnExecutarBuscaRegiao = document.getElementById('btnExecutarBuscaRegiao');
    const loadingOrgaos = document.getElementById('loadingOrgaos');
    const resultadosContainer = document.getElementById('resultadosOrgaosContainer');
    const tabelaResultados = document.getElementById('tabelaResultadosOrgaos');
    const btnAdicionarSelecionados = document.getElementById('btnAdicionarSelecionadosOrgaos');
    
    const uasgInputs = [
        document.getElementById('uasgInput1'),
        document.getElementById('uasgInput2'),
        document.getElementById('uasgInput3')
    ];
    
    // URL da nossa API interna
    const apiUrl = window.location.pathname.replace('/pesquisar', '/pesquisar-orgaos');

    // Função genérica para executar a busca
    const executarBusca = async (payload) => {
        loadingOrgaos.style.display = 'block';
        resultadosContainer.style.display = 'none';
        tabelaResultados.innerHTML = '';
        btnExecutarBuscaUasg.disabled = true;
        btnExecutarBuscaRegiao.disabled = true;

        try {
            const response = await fetch('/api' + apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (!response.ok) throw new Error('Falha na resposta do servidor.');

            const data = await response.json();

            if (data.resultado && data.resultado.length > 0) {
                data.resultado.forEach(item => {
                    const tr = document.createElement('tr');
                    const itemDataString = JSON.stringify(item).replace(/'/g, "&apos;");
                    
                    let unidadeCompleta = item.siglaUnidadeFornecimento || 'N/A';
                    if (item.capacidadeUnidadeFornecimento > 0 && item.siglaUnidadeMedida) {
                        unidadeCompleta += ` c/ ${item.capacidadeUnidadeFornecimento} ${item.siglaUnidadeMedida}`;
                    }

                    tr.innerHTML = `
                        <td><input type="checkbox" class="form-check-input cota-checkbox-orgaos" data-item='${itemDataString}'></td>
                        <td>R$ ${parseFloat(item.precoUnitario).toFixed(2).replace('.', ',')}</td>
                        <td>${unidadeCompleta}</td>
                        <td>${item.nomeUasg || 'N/A'}</td> <td>${new Date(item.dataResultado).toLocaleDateString('pt-BR')}</td>
                    `;
                    tabelaResultados.appendChild(tr);
                });
                resultadosContainer.style.display = 'block';
            } else {
                tabelaResultados.innerHTML = '<tr><td colspan="6" class="text-center">Nenhum resultado encontrado.</td></tr>';
                resultadosContainer.style.display = 'block';
            }

        } catch (error) {
            console.error('Erro na busca por órgãos:', error);
            alert('Ocorreu um erro ao buscar os dados.');
        } finally {
            loadingOrgaos.style.display = 'none';
            btnExecutarBuscaUasg.disabled = false;
            btnExecutarBuscaRegiao.disabled = false;
        }
    };

    // Event listeners para os botões de busca
    btnExecutarBuscaUasg.addEventListener('click', () => {
        const uasgs = uasgInputs.map(input => input.value.trim()).filter(val => val);
        if (uasgs.length === 0) {
            alert('Por favor, informe pelo menos um código de UASG.');
            return;
        }
        executarBusca({ uasgs: uasgs });
    });

    btnExecutarBuscaRegiao.addEventListener('click', () => {
        executarBusca({}); // Envia payload vazio para indicar busca por região
    });

    // Event listener para adicionar as cotações selecionadas
    btnAdicionarSelecionados.addEventListener('click', async () => {
        const checkboxes = document.querySelectorAll('.cota-checkbox-orgaos:checked');
        if (checkboxes.length === 0) {
            alert('Por favor, selecione pelo menos uma cotação.');
            return;
        }

        const precosParaSalvar = [];
        checkboxes.forEach(box => {
            const itemData = JSON.parse(box.dataset.item);
            let unidadeCompleta = itemData.siglaUnidadeFornecimento || '';
            if (itemData.capacidadeUnidadeFornecimento > 0 && itemData.siglaUnidadeMedida) {
                unidadeCompleta += ` c/ ${itemData.capacidadeUnidadeFornecimento} ${itemData.siglaUnidadeMedida}`;
            }

            precosParaSalvar.push({
                fonte: 'Contratação Similar',
                valor: parseFloat(itemData.precoUnitario).toFixed(2),
                unidade_medida: unidadeCompleta,
                data_coleta: itemData.dataResultado.split('T')[0],
                fornecedor_nome: itemData.nomeUasg,
                fornecedor_cnpj: itemData.niFornecedor,
                link_evidencia: null
            });
        });

        btnAdicionarSelecionados.disabled = true;
        btnAdicionarSelecionados.textContent = 'Salvando...';

        try {
            const urlLote = '/api' + window.location.pathname.replace('/pesquisar', '/precos/lote');
            const response = await fetch(urlLote, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(precosParaSalvar)
            });

            if (!response.ok) throw new Error('Falha ao salvar os dados no servidor.');
            
            // Recarrega a página para mostrar os novos dados
            window.location.reload();

        } catch (error) {
            console.error('Erro ao salvar cotações de órgãos:', error);
            alert('Ocorreu um erro ao salvar as cotações. Tente novamente.');
            btnAdicionarSelecionados.disabled = false;
            btnAdicionarSelecionados.textContent = 'Adicionar Cotações Selecionadas';
        }
    });
});