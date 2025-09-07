<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Fornecedores</h1>
    <div>
        <a href="/fornecedores/importar" class="btn btn-success">
            <i class="bi bi-file-earmark-spreadsheet"></i> Importar Planilha
        </a>
        <a href="/fornecedores/novo" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Adicionar Novo Fornecedor
        </a>
        </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Razão Social</th>
                <th>CNPJ</th>
                <th>E-mail</th>
                <th>Ramo de Atividade</th>
                <th>Ações</th>
            </tr>
        </thead>
        
        <tbody>
            <?php foreach ($fornecedores as $fornecedor): ?>
                <tr>
                    <td><?= htmlspecialchars($fornecedor['razao_social']) ?></td>
                    
                    <td><?= htmlspecialchars(formatarString($fornecedor['cnpj'], '##.###.###/####-##')) ?></td>
                    <td><?= htmlspecialchars($fornecedor['email']) ?></td>
                    <td><?= htmlspecialchars(formatarString($fornecedor['telefone'], '(##) #####-####')) ?></td>
                    <td><?= htmlspecialchars($fornecedor['ramo_atividade']) ?></td>
                    <td>
                        <a href="/fornecedores/<?= $fornecedor['id'] ?>/editar" class="btn btn-sm btn-primary" title="Editar Fornecedor">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <form action="/fornecedores/<?= $fornecedor['id'] ?>/excluir" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir este fornecedor?');">
                            <button type="submit" class="btn btn-sm btn-danger" title="Excluir Fornecedor">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($fornecedores)): ?>
                <tr>
                    <td colspan="6" class="text-center">Nenhum fornecedor encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>