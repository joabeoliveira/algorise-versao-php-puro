<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Gerenciamento de Usuários</h1>
    <a href="/usuarios/novo" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Adicionar Novo Usuário
    </a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered table-primary">
        <thead class="table-primary">
            <tr>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Permissão</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?= htmlspecialchars($usuario['nome']) ?></td>
                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                    <td>
                        <span class="badge <?= $usuario['role'] == 'admin' ? 'bg-success' : 'bg-secondary' ?>">
                            <?= htmlspecialchars(ucfirst($usuario['role'])) ?>
                        </span>
                    </td>
                    <td>
                        <a href="/usuarios/<?= $usuario['id'] ?>/editar" class="btn btn-sm btn-primary" title="Editar Usuário">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <?php if ($usuario['id'] != $_SESSION['usuario_id']): ?>
                        <form action="/usuarios/<?= $usuario['id'] ?>/excluir" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');">
                            <button type="submit" class="btn btn-sm btn-danger" title="Excluir Usuário">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>