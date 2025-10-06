<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Lista de Processos</h1>
    <a href="/processos/novo" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Adicionar Novo Processo
    </a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered table-primary">
        <thead class="table-primary">
            <tr>
                <th>Nº do Processo</th>
                <th>Nome</th>
                <th>Agente Responsável</th>
                <th>Região</th>
                <th>UASG</th>
                <th>Tipo</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($processos as $processo): ?>
                <tr>
                    <td><?= htmlspecialchars($processo['numero_processo']) ?></td>
                    <td><?= htmlspecialchars($processo['nome_processo']) ?></td>
                    <td><?= htmlspecialchars($processo['agente_responsavel'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($processo['regiao'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($processo['uasg'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($processo['tipo_contratacao']) ?></td>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($processo['status']) ?></span></td>
                    <td>
                        <a href="/processos/<?= $processo['id'] ?>/itens" class="btn btn-sm btn-success" title="Ver Itens">
                            <i class="bi bi-list-ul"></i>
                        </a>
                        <a href="/processos/<?= $processo['id'] ?>/editar" class="btn btn-sm btn-primary" title="Editar Processo">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <form action="/processos/<?= $processo['id'] ?>/excluir" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir este processo? Todos os itens e preços coletados serão perdidos.');">
                            <button type="submit" class="btn btn-sm btn-danger" title="Excluir Processo">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($processos)): ?>
                <tr>
                    <td colspan="8" class="text-center">Nenhum processo encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>