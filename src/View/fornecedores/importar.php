<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Importar Fornecedores em Massa</h1>
        <a href="/fornecedores" class="btn btn-secondary">Voltar para a Lista</a>
    </div>

    <div class="alert alert-info">
        <h5 class="alert-heading">Instruções para Importação</h5>
        <p>A planilha deve seguir um formato específico para que a importação funcione corretamente. As colunas devem estar na seguinte ordem:</p>
        <ol>
            <li><strong>Coluna A:</strong> Razão Social (Obrigatório)</li>
            <li><strong>Coluna B:</strong> CNPJ (Obrigatório, sem pontos ou traços)</li>
            <li><strong>Coluna C:</strong> E-mail (Obrigatório)</li>
            <li><strong>Coluna D:</strong> Endereço Completo</li>
            <li><strong>Coluna E:</strong> Telefone</li>
            <li><strong>Coluna F:</strong> Ramo de Atividade (Ramos separados por vírgula)</li>
        </ol>
        <p class="mb-0">A primeira linha da planilha será ignorada, pois é considerada o cabeçalho. 
            <a href="/fornecedores/modelo-planilha">Clique aqui para baixar um modelo de exemplo.</a>
        </p>
    </div>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?> alert-dismissible fade show" role="alert">
            <?= nl2br(htmlspecialchars($_SESSION['flash']['mensagem'])) // nl2br para exibir quebras de linha ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <form action="/fornecedores/importar" method="POST" enctype="multipart/form-data" class="mt-4 card p-4">
        <div class="mb-3">
            <label for="arquivo_planilha" class="form-label"><strong>Selecione o arquivo da planilha (.xlsx, .xls, .csv)</strong></label>
            <input type="file" class="form-control" id="arquivo_planilha" name="arquivo_planilha" accept=".xlsx, .xls, .csv" required>
        </div>
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-upload"></i> Iniciar Importação
        </button>
    </form>
</div>