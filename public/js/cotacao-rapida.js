document.addEventListener('DOMContentLoaded', () => {
    // Garante que o código só rode se estivermos na página de cotação rápida
    const form = document.getElementById('formCotacaoRapida');
    if (!form) return;

    // --- SELETORES DE ELEMENTOS DA PÁGINA ---
    const itensContainer = document.getElementById('itens-container');
    const btnAdicionarItem = document.getElementById('btn-adicionar-item');
    const areaResultados = document.getElementById('areaResultados');
    const loading = document.getElementById('loading');
    const boxResultados = document.getElementById('boxResultados');
    const areaSalvarRelatorio = document.getElementById('areaSalvarRelatorio');
    const btnSalvarRelatorio = document.getElementById('btnSalvarRelatorio');
    const tituloCotacaoInput = document.getElementById('tituloCotacao');
    const nomeResponsavelInput = document.getElementById('nomeResponsavel');
    const modalImportarElement = document.getElementById('modalImportarItens');
    const btnConfirmarImportacao = document.getElementById('btnConfirmarImportacao');
    const inputPlanilha = document.getElementById('inputPlanilha');
    const loadingImport = document.getElementById('loadingImport');

    let resultadosGlobais = {}; 

    // --- LÓGICA PARA ADICIONAR/REMOVER LINHAS DE ITENS ---
    btnAdicionarItem.addEventListener('click', () => {
        const primeiraLinha = itensContainer.querySelector('.item-row');
        // Se não houver nenhuma linha, não faz nada (evita erro se o import limpar tudo)
        if (!primeiraLinha) {
            // Cria uma nova linha a partir de um template invisível se necessário
            const template = document.getElementById('item-row-template');
            if (template) {
                itensContainer.innerHTML = template.innerHTML;
            }
            return;
        };
        const novaLinha = primeiraLinha.cloneNode(true);
        novaLinha.querySelectorAll('input, select').forEach(el => {
            if (el.name !== 'quantidade[]') el.value = ''; else if (el.name === 'quantidade[]') el.value = '1';
        });
        const colRemover = novaLinha.querySelector('.col-md-1');
        colRemover.innerHTML = '<button type="button" class="btn btn-danger btn-remover-item" title="Remover este item"><i class="bi bi-trash-fill"></i></button>';
        itensContainer.appendChild(novaLinha);
    });
    itensContainer.addEventListener('click', (e) => {
        if (e.target.closest('.btn-remover-item')) {
            e.target.closest('.item-row').remove();
        }
    });

    // --- LÓGICA DE IMPORTAÇÃO DE PLANILHA ---
    if (modalImportarElement) {
        const modalImportar = new bootstrap.Modal(modalImportarElement);
        const btnConfirmarImportacao = document.getElementById('btnConfirmarImportacao');
        const inputPlanilha = document.getElementById('inputPlanilha');
        const loadingImport = document.getElementById('loadingImport');

        btnConfirmarImportacao.addEventListener('click', () => {
            if (inputPlanilha.files.length === 0) {
                alert('Por favor, selecione um arquivo de planilha.');
                return;
            }
            loadingImport.style.display = 'block';
            
            readXlsxFile(inputPlanilha.files[0]).then((rows) => {
                const template = document.getElementById('item-row-template');
                if (!template) {
                    alert('Erro crítico: O template de item não foi encontrado na página.');
                    return;
                }

                itensContainer.innerHTML = ''; // Limpa as linhas que já existem
                rows.shift(); // Remove o cabeçalho da planilha

                if (rows.length === 0) {
                    alert('A planilha está vazia ou não contém dados válidos.');
                    // Adiciona uma linha em branco para o usuário começar
                    itensContainer.appendChild(template.content.cloneNode(true));
                    return;
                }
                
                rows.forEach(row => {
                    if (!row[0]) return; // Pula linhas em branco

                    // Clona o conteúdo do template para criar uma nova linha
                    const novaLinhaFragment = template.content.cloneNode(true);
                    const novaLinha = novaLinhaFragment.querySelector('.item-row');
                    
                    // Seleciona os inputs da nova linha para preencher
                    const inputCatmat = novaLinha.querySelector('input[name="catmat[]"]');
                    const inputDesc = novaLinha.querySelector('input[name="descricao[]"]');
                    const inputQtd = novaLinha.querySelector('input[name="quantidade[]"]');
                    
                    if (inputCatmat && row[0]) inputCatmat.value = row[0]; // Coluna A: Nº do Item
                    if (inputDesc && row[1]) inputDesc.value = row[1]; // Coluna B: CATMAT
                    if (inputQtd && row[2]) inputQtd.value = row[2]; // Coluna C: Quantidade

                    // Adiciona o botão de remover
                    const colRemover = novaLinha.querySelector('.col-md-1');
                    colRemover.innerHTML = '<button type="button" class="btn btn-danger btn-remover-item" title="Remover este item"><i class="bi bi-trash-fill"></i></button>';
                    
                    itensContainer.appendChild(novaLinha);
                });
                
                modalImportar.hide();
            }).catch(error => {
                alert('Erro ao ler a planilha: ' + error.message);
            }).finally(() => {
                loadingImport.style.display = 'none';
                inputPlanilha.value = '';
            });
        });
    }

    // --- LÓGICA PRINCIPAL DA BUSCA DE PREÇOS ---
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        areaResultados.style.display = 'block';
        boxResultados.innerHTML = '';
        loading.style.display = 'block';
        boxResultados.style.display = 'none';
        areaSalvarRelatorio.style.display = 'none';
        resultadosGlobais = {};
        const formData = new FormData(form);
        const dados = { catmat: formData.getAll('catmat[]'), descricao: formData.getAll('descricao[]'), regiao: formData.getAll('regiao[]'), quantidade: formData.getAll('quantidade[]') };
        try {
            const response = await fetch('/api/cotacao-rapida/buscar', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(dados) });
            const data = await response.json();
            if (!response.ok) { throw new Error(data.erro || 'Falha na comunicação.'); }
            if (data.mensagem || !data.resultados_por_item) {
                boxResultados.innerHTML = `<div class="alert alert-warning">${data.mensagem || 'Nenhum resultado retornado.'}</div>`;
            } else {
                resultadosGlobais = data.resultados_por_item;
                renderizarResultados();
                areaSalvarRelatorio.style.display = 'block';
            }
        } catch (error) {
            boxResultados.innerHTML = `<div class="alert alert-danger"><strong>Erro:</strong> ${error.message}</div>`;
        } finally {
            loading.style.display = 'none';
            boxResultados.style.display = 'block';
        }
    });

    // --- LÓGICA PARA ANÁLISE INTERATIVA (DESCARTE DE PREÇOS) ---
    boxResultados.addEventListener('change', (e) => {
        if (e.target.classList.contains('cota-checkbox')) {
            const tr = e.target.closest('tr');
            const card = e.target.closest('.card');
            if (!card) return;
            const catmat = card.dataset.catmat;
            
            if (!e.target.checked) {
                const justificativa = prompt("Por favor, justifique por que este preço está sendo desconsiderado (ex: inexequível, excessivamente elevado).");
                if (!justificativa || !justificativa.trim()) {
                    e.target.checked = true;
                    return;
                }
                tr.dataset.justificativa = justificativa;
            } else {
                tr.dataset.justificativa = '';
            }
            tr.classList.toggle('desconsiderado', !e.target.checked);
            recalcularEstatisticasParaItem(catmat, true);
        }
    });

    // --- LÓGICA PARA SALVAR A COTAÇÃO E GERAR O RELATÓRIO ---
    btnSalvarRelatorio.addEventListener('click', async () => {
        const titulo = tituloCotacaoInput.value;
        const responsavel = nomeResponsavelInput.value;
        if (!titulo.trim() || !responsavel.trim()) {
            alert('Por favor, preencha o Nome do Responsável e o Título da Cotação.');
            return;
        }

        const dadosParaSalvar = { titulo: titulo, responsavel: responsavel, itens: {} };
        for (const catmat in resultadosGlobais) {
            const cardDoItem = document.querySelector(`.card[data-catmat="${catmat}"]`);
            const checkboxes = cardDoItem.querySelectorAll('.cota-checkbox');
            const precosComStatus = resultadosGlobais[catmat].precos.map((preco, index) => {
                const tr = checkboxes[index].closest('tr');
                return { ...preco, considerado: checkboxes[index].checked, justificativa_descarte: tr.dataset.justificativa || '' };
            });
            const statsFinais = recalcularEstatisticasParaItem(catmat, false);
            
            let descricaoOficial = resultadosGlobais[catmat].descricao;
            if(resultadosGlobais[catmat].precos.length > 0 && resultadosGlobais[catmat].precos[0].descricaoItem){
                descricaoOficial = resultadosGlobais[catmat].precos[0].descricaoItem;
            }

            dadosParaSalvar.itens[catmat] = {
                descricao: descricaoOficial,
                quantidade: resultadosGlobais[catmat].quantidade,
                precos: precosComStatus,
                estatisticas: statsFinais
            };
        }

        btnSalvarRelatorio.disabled = true;
        btnSalvarRelatorio.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Salvando...';
        try {
            const response = await fetch('/api/cotacao-rapida/salvar-relatorio', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(dadosParaSalvar)
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.erro || 'Erro do servidor ao salvar.');
            alert('Cotação salva com sucesso! O relatório será aberto em uma nova aba.');
            window.open(`/relatorios/${data.nota_tecnica_id}/visualizar`, '_blank');
        } catch (error) {
            alert('Erro ao salvar: ' + error.message);
        } finally {
            btnSalvarRelatorio.disabled = false;
            btnSalvarRelatorio.innerHTML = '<i class="bi bi-check-circle-fill"></i> Salvar e Gerar PDF';
        }
    });

    // --- FUNÇÕES AUXILIARES DE RENDERIZAÇÃO E CÁLCULO ---
    function renderizarResultados() {
        boxResultados.innerHTML = '<h2>Resultados da Pesquisa de Preços Unitários</h2>';
        for (const catmat in resultadosGlobais) {
            const item = resultadosGlobais[catmat];
            const card = document.createElement('div'); card.className = 'card mb-4'; card.dataset.catmat = catmat;
            let descricaoExibida = item.descricao;
            if (item.precos && item.precos.length > 0 && item.precos[0].descricaoItem) { descricaoExibida = item.precos[0].descricaoItem; }
            card.innerHTML = `<div class="card-header bg-light"><h4 class="mb-0">Item: ${descricaoExibida} (CATMAT: ${catmat}) | Quantidade Solicitada: ${item.quantidade}</h4></div><div class="card-body"></div>`;
            const cardBody = card.querySelector('.card-body');
            const statsContainer = document.createElement('div'); statsContainer.className = 'estatisticas-container'; cardBody.appendChild(statsContainer);
            const summaryContainer = document.createElement('div'); summaryContainer.className = 'summary-container'; cardBody.appendChild(summaryContainer);
            if (item.precos && item.precos.length > 0) {
                const tableContainer = document.createElement('div'); tableContainer.className = 'table-responsive';
                tableContainer.innerHTML = renderizarTabela(item.precos);
                cardBody.insertBefore(tableContainer, summaryContainer);
            } else { cardBody.insertBefore(document.createElement('div'), summaryContainer).innerHTML = `<div class="alert alert-secondary mt-3">Nenhum preço encontrado para este item.</div>`; }
            boxResultados.appendChild(card);
            recalcularEstatisticasParaItem(catmat, true);
        }
    }
    function renderizarEstatisticas(stats) {
        if (!stats || stats.total === 0) return '<div class="row mb-4 g-3"><div class="col-12"><div class="alert alert-warning">Nenhum preço válido selecionado para calcular as estatísticas.</div></div></div>';
        return `<div class="row mb-4 g-3"><div class="col"><div class="card text-center h-100"><div class="card-header">Preços Válidos</div><div class="card-body"><h5 class="card-title">${stats.total}</h5></div></div></div><div class="col"><div class="card text-center h-100"><div class="card-header">Menor Preço Unit.</div><div class="card-body"><h5 class="card-title">${formatarMoeda(stats.minimo)}</h5></div></div></div><div class="col"><div class="card text-center h-100"><div class="card-header">Preço Médio Unit.</div><div class="card-body"><h5 class="card-title">${formatarMoeda(stats.media)}</h5></div></div></div><div class="col"><div class="card text-center h-100"><div class="card-header">Preço Mediano Unit.</div><div class="card-body"><h5 class="card-title">${formatarMoeda(stats.mediana)}</h5></div></div></div></div>`;
    }
    function renderizarTabela(precos) {
        return `<table class="table table-bordered table-hover align-middle"><thead class="table-light"><tr><th class="text-center" style="width: 5%;">✓</th><th>Fonte</th><th>Fornecedor/Órgão</th><th>Data</th><th>Preço Unitário</th></tr></thead><tbody>
            ${precos.map((p, index) => `<tr class="preco-row"><td class="text-center"><input class="form-check-input cota-checkbox" type="checkbox" data-preco-index="${index}" checked></td><td><span class="badge ${(p.fonte_pesquisa || '').includes('Inciso I') ? 'bg-success' : 'bg-info'}">${p.fonte_pesquisa || 'N/A'}</span></td><td>${p.nomeUasg || p.nomeFornecedor || 'N/A'}</td><td>${p.dataResultado ? new Date(p.dataResultado).toLocaleDateString('pt-BR') : 'N/A'}</td><td>${formatarMoeda(p.precoUnitario)}</td></tr>`).join('')}
        </tbody></table>`;
    }
    function renderizarResumoFinal(stats, quantidade) {
        if (!stats || stats.total === 0) return '';
        const custoMediano = stats.mediana * quantidade;
        const custoMedio = stats.media * quantidade;
        return `<div class="alert alert-success mt-4"><h4 class="alert-heading">Resumo do Custo Total Estimado</h4><p>Com base na amostra de preços unitários válidos, o custo total estimado para <strong>${quantidade}</strong> unidade(s) deste item é:</p><ul><li>Custo Total com base na <strong>Mediana</strong>: <strong>${formatarMoeda(custoMediano)}</strong></li><li>Custo Total com base na <strong>Média</strong>: <strong>${formatarMoeda(custoMedio)}</strong></li></ul><hr><p class="mb-0 small">A Mediana é geralmente a medida mais indicada por ser menos sensível a valores extremos.</p></div>`;
    }
    function recalcularEstatisticasParaItem(catmat, atualizarUI) {
        const itemOriginal = resultadosGlobais[catmat];
        const cardDoItem = document.querySelector(`.card[data-catmat="${catmat}"]`);
        if (!itemOriginal || !cardDoItem) return null;
        const precosConsiderados = [];
        cardDoItem.querySelectorAll('.cota-checkbox:checked').forEach(box => {
            precosConsiderados.push(itemOriginal.precos[box.dataset.precoIndex]);
        });
        const valores = precosConsiderados.map(p => p.precoUnitario).filter(v => v !== null && v !== undefined);
        const novasEstatisticas = {
            total: valores.length, minimo: valores.length > 0 ? Math.min(...valores) : 0, maximo: valores.length > 0 ? Math.max(...valores) : 0,
            media: valores.length > 0 ? valores.reduce((a, b) => parseFloat(a) + parseFloat(b), 0) / valores.length : 0, mediana: calcularMediana(valores)
        };
        if (atualizarUI) {
            cardDoItem.querySelector('.estatisticas-container').innerHTML = renderizarEstatisticas(novasEstatisticas);
            cardDoItem.querySelector('.summary-container').innerHTML = renderizarResumoFinal(novasEstatisticas, itemOriginal.quantidade);
        }
        return novasEstatisticas;
    }
    function calcularMediana(valores) {
        if (valores.length === 0) return 0;
        const sorted = [...valores].map(v => parseFloat(v)).sort((a, b) => a - b);
        const mid = Math.floor(sorted.length / 2);
        return sorted.length % 2 !== 0 ? sorted[mid] : (sorted[mid - 1] + sorted[mid]) / 2.0;
    }
    function formatarMoeda(valor) {
        const numero = parseFloat(valor);
        if (isNaN(numero)) return 'R$ 0,00';
        return numero.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }
});