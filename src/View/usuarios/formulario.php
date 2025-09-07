<div class="container mt-4">
    <h1>Adicionar Novo Usuário</h1>

    <form action="/usuarios/novo" method="POST" class="mt-4">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome Completo</label>
            <input type="text" class="form-control" id="nome" name="nome" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="senha" class="form-label">Senha</label>
                <input type="password" class="form-control" id="senha" name="senha" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="senha_confirm" class="form-label">Confirmar Senha</label>
                <input type="password" class="form-control" id="senha_confirm" name="senha_confirm" required>
            </div>
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Tipo de Permissão</label>
            <select class="form-select" id="role" name="role">
                <option value="user" selected>Usuário Padrão</option>
                <option value="admin">Administrador</option>
            </select>
        </div>
        
        <a href="/usuarios" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Salvar Usuário</button>
    </form>
</div>