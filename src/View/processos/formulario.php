<div class="container mt-4">
    <h1>Adicionar Novo Processo</h1>

    <form action="/processos" method="POST" class="mt-4">
        <div class="mb-3">
            <label for="numero_processo" class="form-label">Número do Processo</label>
            <input type="text" class="form-control" id="numero_processo" name="numero_processo" required>
        </div>
        <div class="mb-3">
            <label for="nome_processo" class="form-label">Nome do Processo</label>
            <input type="text" class="form-control" id="nome_processo" name="nome_processo" required>
        </div>

        <div class="mb-3">
            <label for="agente_responsavel" class="form-label">Agente Responsável</label>
            <input type="text" class="form-control" id="agente_responsavel" name="agente_responsavel" required>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="agente_matricula" class="form-label">Matrícula do Agente</label>
                <input type="text" class="form-control" id="agente_matricula" name="agente_matricula" value="<?= htmlspecialchars($processo['agente_matricula'] ?? '') ?>">
            </div>
            <div class="col-md-3 mb-3">
                <label for="uasg" class="form-label">Código UASG</label>
                <input type="text" class="form-control" id="uasg" name="uasg" required>
            </div>
            <div class="col-md-3 mb-3">
                <label for="regiao" class="form-label">Região (UF)</label>
                <select class="form-select" id="regiao" name="regiao" required>
                    <option value="" selected disabled>Selecione</option>
                    <?php $estados = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO']; ?>
                    <?php foreach ($estados as $uf): ?>
                        <option value="<?= $uf ?>"><?= $uf ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="mb-3">
            <label for="tipo_contratacao" class="form-label">Tipo de Contratação</label>
            <select class="form-select" id="tipo_contratacao" name="tipo_contratacao" required>
                <option value="Pregão Eletrônico">Pregão Eletrônico</option>
                <option value="Dispensa de Licitação">Dispensa de Licitação</option>
                <option value="Inexigibilidade">Inexigibilidade</option>
                <option value="Compra Direta">Compra Direta (Pequeno Valor)</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status" required>
                <option value="Em Elaboração">Em Elaboração</option>
                <option value="Pesquisa em Andamento">Pesquisa em Andamento</option>
                <option value="Finalizado">Finalizado</option>
                <option value="Cancelado">Cancelado</option>
            </select>
        </div>

        <a href="/processos" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Salvar Processo</button>
    </form>
</div>