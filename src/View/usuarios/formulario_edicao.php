<div class="container mt-4">
    <h1>Editar Usuário</h1>

    <form action="/usuarios/<?= htmlspecialchars($usuario['id']) ?>/editar" method="POST" class="mt-4">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome Completo</label>
            <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>
        </div>
        
        <hr>
        <p class="text-muted">Deixe os campos de senha em branco para não alterá-la.</p>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="senha" class="form-label">Nova Senha</label>
                <input type="password" class="form-control" id="senha" name="senha">
            </div>
            <div class="col-md-6 mb-3">
                <label for="senha_confirm" class="form-label">Confirmar Nova Senha</label>
                <input type="password" class="form-control" id="senha_confirm" name="senha_confirm">
            </div>
        </div>
        <hr>

        <div class="mb-3">
            <label for="role" class="form-label">Tipo de Permissão</label>
            <select class="form-select" id="role" name="role">
                <option value="user" <?= $usuario['role'] == 'user' ? 'selected' : '' ?>>Usuário Padrão</option>
                <option value="admin" <?= $usuario['role'] == 'admin' ? 'selected' : '' ?>>Administrador</option>
            </select>
        </div>
        
        <a href="/usuarios" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
    </form>
</div>