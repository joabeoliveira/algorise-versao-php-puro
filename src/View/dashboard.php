<h1 class="mb-4">Dashboard Geral</h1>

<div class="row mb-4">
    <div class="col-lg-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Total de Processos</h5>
                        <p class="card-text fs-2 fw-bold"><?= $totalProcessos ?></p>
                    </div>
                    <i class="bi bi-folder-fill opacity-50" style="font-size: 3rem;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Em Andamento</h5>
                        <p class="card-text fs-2 fw-bold"><?= $totalEmAndamento ?></p>
                    </div>
                    <i class="bi bi-hourglass-split opacity-50" style="font-size: 3rem;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Finalizados</h5>
                        <p class="card-text fs-2 fw-bold"><?= $totalFinalizados ?></p>
                    </div>
                    <i class="bi bi-check-circle-fill opacity-50" style="font-size: 3rem;"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">Processos por Status</div>
            <div class="card-body"><canvas id="graficoStatus" data-dados='<?= json_encode($dadosStatus) ?>'></canvas></div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">Processos por Tipo</div>
            <div class="card-body"><canvas id="graficoTipo" data-dados='<?= json_encode($dadosTipo) ?>'></canvas></div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">Top 5 Agentes por Processos</div>
            <div class="card-body"><canvas id="graficoAgentes" data-dados='<?= json_encode($dadosAgentes) ?>'></canvas></div>
        </div>
    </div>
</div>



<div class="row mt-4">
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header">Valor Total Estimado por Mês (Processos Finalizados)</div>
            <div class="card-body">
                <canvas id="graficoValorPorMes" data-dados='<?= json_encode($dadosValorPorMes) ?>'></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">Taxa de Resposta de Fornecedores</div>
            <div class="card-body">
                <canvas id="graficoRespostasFornecedores" data-dados='<?= json_encode($dadosRespostasFornecedores) ?>'></canvas>
            </div>
        </div>
    </div>
</div>
<div class="row mt-4">
    <div class="col-12 mb-4">
        <div class="card h-100">
            <div class="card-header">Distribuição de Processos por Região (UF)</div>
            <div class="card-body">
                <canvas id="graficoProcessosPorRegiao" data-dados='<?= json_encode($dadosProcessosPorRegiao) ?>'></canvas>
            </div>
        </div>
    </div>
</div>
