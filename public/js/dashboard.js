document.addEventListener('DOMContentLoaded', () => {
    // Gráfico de Status (Pizza)
    const canvasStatus = document.getElementById('graficoStatus');
    if (canvasStatus) {
        const dadosStatus = JSON.parse(canvasStatus.dataset.dados);
        new Chart(canvasStatus, {
            type: 'doughnut',
            data: {
                labels: dadosStatus.map(d => d.status),
                datasets: [{
                    label: 'Processos',
                    data: dadosStatus.map(d => d.total),
                    backgroundColor: ['#6c757d', '#ffc107', '#198754', '#dc3545', '#0dcaf0'],
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    // Gráfico de Tipo (Barras)
    const canvasTipo = document.getElementById('graficoTipo');
    if (canvasTipo) {
        const dadosTipo = JSON.parse(canvasTipo.dataset.dados);
        new Chart(canvasTipo, {
            type: 'bar',
            data: {
                labels: dadosTipo.map(d => d.tipo_contratacao),
                datasets: [{
                    label: 'Total de Processos',
                    data: dadosTipo.map(d => d.total),
                    backgroundColor: '#0d6efd',
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    // Gráfico por Agentes (Barras Horizontais)
    const canvasAgentes = document.getElementById('graficoAgentes');
    if (canvasAgentes) {
        const dadosAgentes = JSON.parse(canvasAgentes.dataset.dados);
        new Chart(canvasAgentes, {
            type: 'bar',
            data: {
                labels: dadosAgentes.map(d => d.agente_responsavel),
                datasets: [{
                    label: 'Nº de Processos',
                    data: dadosAgentes.map(d => d.total),
                    backgroundColor: '#fd7e14',
                }]
            },
            options: {
                indexAxis: 'y', // Isso transforma o gráfico em barras horizontais
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    // --- INÍCIO DOS NOVOS GRÁFICOS ---

    // Gráfico de Valor por Mês (Linha)
    const canvasValorMes = document.getElementById('graficoValorPorMes');
    if (canvasValorMes) {
        const dadosValor = JSON.parse(canvasValorMes.dataset.dados);
        new Chart(canvasValorMes, {
            type: 'line',
            data: {
                labels: dadosValor.map(d => d.mes),
                datasets: [{
                    label: 'Valor Total R$',
                    data: dadosValor.map(d => d.valor_total),
                    backgroundColor: 'rgba(25, 135, 84, 0.2)',
                    borderColor: 'rgba(25, 135, 84, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value, index, values) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            }
                        }
                    }
                }
            }
        });
    }

    // Gráfico de Resposta de Fornecedores (Pizza)
    const canvasRespostas = document.getElementById('graficoRespostasFornecedores');
    if (canvasRespostas) {
        const dadosRespostas = JSON.parse(canvasRespostas.dataset.dados);
        new Chart(canvasRespostas, {
            type: 'pie',
            data: {
                labels: dadosRespostas.map(d => d.status_calculado),
                datasets: [{
                    label: 'Solicitações',
                    data: dadosRespostas.map(d => d.total),
                    backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    // Gráfico de Processos por Região (Barras Horizontais)
    const canvasRegiao = document.getElementById('graficoProcessosPorRegiao');
    if (canvasRegiao) {
        const dadosRegiao = JSON.parse(canvasRegiao.dataset.dados);
        new Chart(canvasRegiao, {
            type: 'bar',
            data: {
                labels: dadosRegiao.map(d => d.regiao),
                datasets: [{
                    label: 'Nº de Processos',
                    data: dadosRegiao.map(d => d.total),
                    backgroundColor: '#0dcaf0',
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    // --- FIM DOS NOVOS GRÁFICOS ---

});