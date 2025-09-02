document.addEventListener('DOMContentLoaded', () => {
    const modalElement = document.getElementById('modalSolicitacaoLote');
    if (!modalElement) return;

    const ramoSelect = document.getElementById('ramoAtividadeSelect');
    const listaFornecedoresDiv = document.getElementById('listaFornecedoresLote');
    const btnEnviarLote = document.getElementById('btnEnviarLote');
    const loadingDiv = document.getElementById('loadingLote');
    const checkTodosItens = document.getElementById('checkTodosItens');

    // Listener para o checkbox "Selecionar Todos os Itens"
    if(checkTodosItens) {
        checkTodosItens.addEventListener('change', () => {
            const isChecked = checkTodosItens.checked;
            const itemCheckboxes = modalElement.querySelectorAll('.item-lote-check');
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });
        });
    }

    // Carrega os ramos de atividade quando o modal é aberto
    modalElement.addEventListener('show.bs.modal', async () => {
        try {
            const response = await fetch('/api/fornecedores/ramos-atividade');
            if (!response.ok) throw new Error('Falha ao carregar ramos de atividade.');
            
            const ramos = await response.json();
            ramoSelect.innerHTML = '<option value="todos" selected>-- Carregar Todos --</option>'; // Reset
            ramos.forEach(ramo => {
                const option = document.createElement('option');
                option.value = ramo;
                option.textContent = ramo;
                ramoSelect.appendChild(option);
            });
            
            // Carrega a lista inicial com todos os fornecedores
            await carregarFornecedores('todos');
        } catch (error) {
            console.error('Erro ao buscar ramos de atividade:', error);
            listaFornecedoresDiv.innerHTML = '<p class="text-center text-danger">Erro ao carregar filtros.</p>';
        }
    });

    // Filtra os fornecedores ao mudar o ramo
    ramoSelect.addEventListener('change', () => {
        carregarFornecedores(ramoSelect.value);
    });

    // Função para carregar e exibir os fornecedores
    async function carregarFornecedores(ramo) {
        listaFornecedoresDiv.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm"></div></div>';
        try {
            const response = await fetch(`/api/fornecedores/por-ramo?ramo=${encodeURIComponent(ramo)}`);
            if (!response.ok) throw new Error('Falha ao carregar fornecedores.');

            const fornecedores = await response.json();
            
            if (fornecedores.length > 0) {
                const table = document.createElement('table');
                table.className = 'table table-sm table-hover';
                
                let headerHtml = `
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;"><input type="checkbox" class="form-check-input" id="checkTodosFornecedores"></th>
                            <th>Selecionar Todos os Fornecedores</th>
                        </tr>
                    </thead>`;

                let rowsHtml = '<tbody>';
                fornecedores.forEach(f => {
                    rowsHtml += `<tr>
                        <td style="width: 5%;"><input type="checkbox" class="form-check-input fornecedor-lote-check" value="${f.id}"></td>
                        <td>${f.razao_social}</td>
                    </tr>`;
                });
                rowsHtml += '</tbody>';
                
                table.innerHTML = headerHtml + rowsHtml;
                listaFornecedoresDiv.innerHTML = '';
                listaFornecedoresDiv.appendChild(table);

                // Adiciona o listener para o novo checkbox "Selecionar Todos os Fornecedores"
                document.getElementById('checkTodosFornecedores').addEventListener('change', (e) => {
                    const isChecked = e.target.checked;
                    listaFornecedoresDiv.querySelectorAll('.fornecedor-lote-check').forEach(checkbox => {
                        checkbox.checked = isChecked;
                    });
                });

            } else {
                listaFornecedoresDiv.innerHTML = '<p class="text-center text-muted">Nenhum fornecedor encontrado para este ramo.</p>';
            }
        } catch (error) {
            console.error('Erro ao carregar fornecedores:', error);
            listaFornecedoresDiv.innerHTML = '<p class="text-center text-danger">Erro ao carregar a lista de fornecedores.</p>';
        }
    }

    // Envia o lote para o backend
    btnEnviarLote.addEventListener('click', async () => {
        const itemCheckboxes = document.querySelectorAll('.item-lote-check:checked');
        const fornecedorCheckboxes = document.querySelectorAll('.fornecedor-lote-check:checked');
        
        if (itemCheckboxes.length === 0 || fornecedorCheckboxes.length === 0) {
            alert('Por favor, selecione pelo menos um item e um fornecedor.');
            return;
        }

        const itemIds = Array.from(itemCheckboxes).map(cb => cb.value);
        const fornecedorIds = Array.from(fornecedorCheckboxes).map(cb => cb.value);
        
        // =======================================================
        //          INÍCIO DA CORREÇÃO: LER NOVOS CAMPOS
        // =======================================================
        const prazoDias = document.getElementById('prazo_dias_lote').value;
        const condicoesContratuais = document.getElementById('condicoes_contratuais_lote').value;
        const justificativaFornecedores = document.getElementById('justificativa_fornecedores_lote').value;

        // Adiciona uma validação no frontend para evitar requisições desnecessárias
        if (!justificativaFornecedores.trim()) {
            alert('A justificativa da escolha dos fornecedores é obrigatória.');
            return;
        }
        // =======================================================
        //                      FIM DA CORREÇÃO
        // =======================================================

        const pathParts = window.location.pathname.split('/');
        const processoId = pathParts[2];
        const url = `/api/processos/${processoId}/solicitacao-lote`;

        loadingDiv.style.display = 'block';
        btnEnviarLote.disabled = true;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                // =======================================================
                //     INÍCIO DA CORREÇÃO: ENVIAR DADOS COMPLETOS
                // =======================================================
                body: JSON.stringify({
                    item_ids: itemIds,
                    fornecedor_ids: fornecedorIds,
                    prazo_dias: prazoDias,
                    condicoes_contratuais: condicoesContratuais,
                    justificativa_fornecedores: justificativaFornecedores
                })
                // =======================================================
                //                    FIM DA CORREÇÃO
                // =======================================================
            });

            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Erro no servidor');

            alert(result.message);
            // Recarrega a página para limpar o formulário e refletir as mudanças.
            window.location.reload();

        } catch (error) {
            alert('Erro: ' + error.message);
        } finally {
            loadingDiv.style.display = 'none';
            btnEnviarLote.disabled = false;
        }
    });

});