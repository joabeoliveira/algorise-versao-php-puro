<div class="container mt-4">
    <h1>Editar Fornecedor</h1>

    <form action="/fornecedores/<?= htmlspecialchars($fornecedor['id']) ?>/editar" method="POST" class="mt-4">
        <div class="mb-3">
            <label for="razao_social" class="form-label">Razão Social</label>
            <input type="text" class="form-control" id="razao_social" name="razao_social" value="<?= htmlspecialchars($fornecedor['razao_social']) ?>" required>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="cnpj" class="form-label">CNPJ</label>
                <input type="text" class="form-control" id="cnpj" name="cnpj" value="<?= htmlspecialchars($fornecedor['cnpj']) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="telefone" class="form-label">Telefone</label>
                <input type="text" class="form-control" id="telefone" name="telefone" value="<?= htmlspecialchars($fornecedor['telefone']) ?>">
            </div>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">E-mail para Cotações</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($fornecedor['email']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="endereco" class="form-label">Endereço Completo</label>
            <input type="text" class="form-control" id="endereco" name="endereco" value="<?= htmlspecialchars($fornecedor['endereco'] ?? '') ?>" placeholder="Ex: Rua Exemplo, 123, Bairro, Cidade - UF, CEP">
        </div>
        <div class="mb-3">
            <label for="ramo_atividade" class="form-label">Ramo de Atividade (separe por vírgulas)</label>
            <input type="text" class="form-control" id="ramo_atividade" name="ramo_atividade" value="<?= htmlspecialchars($fornecedor['ramo_atividade']) ?>" placeholder="Ex: material de escritório, limpeza, TI">
        </div>
        
        <a href="/fornecedores" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
    </form>
</div>