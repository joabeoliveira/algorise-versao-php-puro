document.addEventListener('DOMContentLoaded', () => {
    const modalDesconsiderar = document.getElementById('modalDesconsiderar');
    if (modalDesconsiderar) {
        modalDesconsiderar.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const processoId = button.getAttribute('data-processo-id');
            const itemId = button.getAttribute('data-item-id');
            const precoId = button.getAttribute('data-preco-id');
            const form = modalDesconsiderar.querySelector('#formDesconsiderar');
            
            const actionUrl = `/processos/${processoId}/itens/${itemId}/precos/${precoId}/desconsiderar`;
            form.setAttribute('action', actionUrl);
        });
    }
});

// public/js/analise-precos.js

document.addEventListener('DOMContentLoaded', function() {
    // Encontre todos os formulários de análise de item.
    // Você precisará adaptar o seletor ('form.analise-item-form')
    // de acordo com como seus formulários de item são identificados no HTML.
    // Exemplo: se cada item é um div com ID 'item-X' e tem um form dentro,
    // você pode usar document.querySelectorAll('.item-card form').
    const itemAnalysisForms = document.querySelectorAll('.analise-item-form'); // Exemplo de seletor

    itemAnalysisForms.forEach(form => {
        form.addEventListener('submit', async function(event) {
            event.preventDefault(); // Impede o envio padrão do formulário (recarregamento da página)

            const form = event.target;
            const formData = new FormData(form);
            // Converte FormData para um objeto JSON (útil para Slim Framework com `getParsedBody()`)
            const jsonData = {};
            formData.forEach((value, key) => { jsonData[key] = value; });

            // Pega o ID do item da URL da action do formulário, por exemplo:
            // action="/processos/1/itens/5/analise/salvar" -> item_id = 5
            const actionPathParts = form.action.split('/');
            const itemId = actionPathParts[actionPathParts.length - 2]; // Ajuste conforme a estrutura exata da sua URL

            // Desabilitar o botão de salvar e mostrar um indicador de carregamento (opcional)
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Salvando...';
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json' // Importante para o Slim interpretar como JSON
                    },
                    body: JSON.stringify(jsonData)
                });

                const result = await response.json();

                if (result.status === 'success') {
                    // Feedback visual de sucesso:
                    const successMessageDiv = document.createElement('div');
                    successMessageDiv.className = 'alert alert-success alert-dismissible fade show mt-2';
                    successMessageDiv.textContent = result.message;
                    successMessageDiv.setAttribute('role', 'alert');
                    successMessageDiv.innerHTML += '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';

                    // Encontre o local para exibir a mensagem. Ex: perto do form.
                    // Você pode adicionar um div específico para mensagens por item.
                    // Por simplicidade, vou adicionar ao pai do formulário.
                    form.closest('.item-card-container').appendChild(successMessageDiv); // Ajuste o seletor conforme seu HTML

                    // Marcar visualmente o item como analisado (sem recarregar a página)
                    const itemCard = form.closest('.item-card-container'); // O container do item
                    if (itemCard) {
                        itemCard.classList.add('item-analisado'); 
                        
                        const titleElement = itemCard.querySelector('h4'); // Encontra o título do card
                        if (titleElement) {
                            // Garante que não vai adicionar múltiplos badges
                            if (!titleElement.querySelector('.badge')) { 
                                const successBadge = document.createElement('span');
                                successBadge.className = 'badge bg-success ms-2';
                                successBadge.innerHTML = '<i class="bi bi-check-circle-fill"></i> Analisado';
                                titleElement.appendChild(successBadge);
                            }
                        }

                        // Remover qualquer mensagem de erro anterior, se houver
                        const existingError = itemCard.querySelector('.alert-danger');
                        if (existingError) {
                            existingError.remove();
                        }
                    }

                    // Opcional: Remover a mensagem após alguns segundos
                    setTimeout(() => {
                        successMessageDiv.remove();
                    }, 5000);

                } else {
                    // Lidar com erros (ex: validação falhou)
                    const errorMessageDiv = document.createElement('div');
                    errorMessageDiv.className = 'alert alert-danger alert-dismissible fade show mt-2';
                    errorMessageDiv.textContent = result.message || 'Erro ao salvar a análise.';
                    errorMessageDiv.setAttribute('role', 'alert');
                    errorMessageDiv.innerHTML += '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                    form.closest('.item-card-container').appendChild(errorMessageDiv);
                }
            } catch (error) {
                console.error('Erro na submissão AJAX:', error);
                const errorMessageDiv = document.createElement('div');
                errorMessageDiv.className = 'alert alert-danger alert-dismissible fade show mt-2';
                errorMessageDiv.textContent = 'Ocorreu um erro na conexão. Tente novamente.';
                errorMessageDiv.setAttribute('role', 'alert');
                errorMessageDiv.innerHTML += '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                form.closest('.item-card-container').appendChild(errorMessageDiv);
            } finally {
                // Reabilitar o botão de salvar
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Salvar Análise'; // Ou o texto original
                }
            }
        });
    });
});