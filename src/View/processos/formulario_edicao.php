<div class="container mt-4">
    <h1>Editar Processo</h1>

    <form action="/processos/<?= htmlspecialchars($processo['id']) ?>/editar" method="POST" class="mt-4">
        <div class="mb-3">
            <label for="numero_processo" class="form-label">Número do Processo</label>
            <input type="text" class="form-control" id="numero_processo" name="numero_processo" value="<?= htmlspecialchars($processo['numero_processo']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="nome_processo" class="form-label">Nome do Processo</label>
            <input type="text" class="form-control" id="nome_processo" name="nome_processo" value="<?= htmlspecialchars($processo['nome_processo']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="agente_responsavel" class="form-label">Agente Responsável</label>
            <input type="text" class="form-control" id="agente_responsavel" name="agente_responsavel" value="<?= htmlspecialchars($processo['agente_responsavel'] ?? '') ?>" required>
        </div>

        <div class="row">
           <div class="col-md-6 mb-3">
                <label for="agente_matricula" class="form-label">Matrícula do Agente</label>
                <input type="text" class="form-control" id="agente_matricula" name="agente_matricula" value="<?= htmlspecialchars($processo['agente_matricula'] ?? '') ?>">
            </div>
            <div class="col-md-3 mb-3">
                <label for="uasg" class="form-label">Código UASG</label>
                <input type="text" class="form-control" id="uasg" name="uasg" value="<?= htmlspecialchars($processo['uasg'] ?? '') ?>" required>
            </div>
            <div class="col-md-3 mb-3">
                <label for="regiao" class="form-label">Região (UF)</label>
                <select class="form-select" id="regiao" name="regiao" required>
                    <option value="" disabled>Selecione</option>
                    <?php $estados = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO']; ?>
                    <?php foreach ($estados as $uf): ?>
                        <option value="<?= $uf ?>" <?= ($processo['regiao'] ?? '') == $uf ? 'selected' : '' ?>><?= $uf ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="mb-3">
            <label for="tipo_contratacao" class="form-label">Tipo de Contratação</label>
            <select class="form-select" id="tipo_contratacao" name="tipo_contratacao" required>
                <option value="Pregão Eletrônico" <?= $processo['tipo_contratacao'] == 'Pregão Eletrônico' ? 'selected' : '' ?>>Pregão Eletrônico</option>
                <option value="Dispensa de Licitação" <?= $processo['tipo_contratacao'] == 'Dispensa de Licitação' ? 'selected' : '' ?>>Dispensa de Licitação</option>
                <option value="Inexigibilidade" <?= $processo['tipo_contratacao'] == 'Inexigibilidade' ? 'selected' : '' ?>>Inexigibilidade</option>
                <option value="Compra Direta" <?= $processo['tipo_contratacao'] == 'Compra Direta' ? 'selected' : '' ?>>Compra Direta (Pequeno Valor)</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status" required>
                <option value="Em Elaboração" <?= $processo['status'] == 'Em Elaboração' ? 'selected' : '' ?>>Em Elaboração</option>
                <option value="Pesquisa em Andamento" <?= $processo['status'] == 'Pesquisa em Andamento' ? 'selected' : '' ?>>Pesquisa em Andamento</option>
                <option value="Finalizado" <?= $processo['status'] == 'Finalizado' ? 'selected' : '' ?>>Finalizado</option>
                <option value="Cancelado" <?= $processo['status'] == 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
            </select>
        </div>

        <a href="/processos" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
    </form>
</div>