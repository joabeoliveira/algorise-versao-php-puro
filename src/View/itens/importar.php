<?php
// Garante que a variável $processo exista para evitar erros.
$processo = $processo ?? ['id' => 0, 'nome_processo' => 'Processo Inválido'];
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-0">Importar Itens em Massa</h1>
            <p class="text-muted">Para o Processo: <strong><?= htmlspecialchars($processo['nome_processo']) ?></strong></p>
        </div>
        <a href="/processos/<?= $processo['id'] ?>/itens" class="btn btn-secondary">
            <i class="bi bi-x-lg"></i> Cancelar e Voltar
        </a>
    </div>

    <div class="alert alert-danger">
        <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> Atenção: Formato Obrigatório</h5>
        <p>A planilha deve seguir estritamente o formato abaixo. **A importação será cancelada se o código CATMAT/CATSER estiver ausente em qualquer linha.**</p>
        <hr>
        <ol class="mb-0">
            <li><strong>Coluna A:</strong> Nº do Item (Obrigatório)</li>
            <li><strong>Coluna B:</strong> CATMAT/CATSER (Obrigatório)</li>
            <li><strong>Coluna C:</strong> Descrição Completa do Item (Obrigatório)</li>
            <li><strong>Coluna D:</strong> Unidade de Medida (Obrigatório)</li>
            <li><strong>Coluna E:</strong> Quantidade (Obrigatório)</li>
        </ol>
    </div>

    <div class="alert alert-secondary">
        <p class="mb-2">
            <i class="bi bi-lightbulb-fill"></i>
            <strong>Dica:</strong> Para encontrar os códigos e as descrições padronizadas, consulte o Catálogo de Materiais (CATMAT) oficial do Governo Federal.
        </p>
        <a href="https://www.gov.br/compras/pt-br/acesso-a-informacao/consulta-detalhada/planilha-catmat-catser/catmat.xlsx" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-cloud-download"></i> Baixar Planilha do CATMAT
        </a>
        <a href="/processos/<?= $processo['id'] ?>/itens/modelo-planilha" class="btn btn-sm btn-outline-success">
            <i class="bi bi-file-earmark-spreadsheet"></i> Baixar Modelo de Importação
        </a>
    </div>
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?> alert-dismissible fade show" role="alert">
            <?= nl2br(htmlspecialchars($_SESSION['flash']['mensagem'])) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <form action="/processos/<?= $processo['id'] ?>/itens/importar" method="POST" enctype="multipart/form-data" class="mt-4 card p-4">
        <div class="mb-3">
            <label for="arquivo_planilha" class="form-label fs-5"><strong>Selecione o arquivo da sua planilha</strong></label>
            <input type="file" class="form-control form-control-lg" id="arquivo_planilha" name="arquivo_planilha" accept=".xlsx, .xls, .csv" required>
        </div>
        <button type="submit" class="btn btn-primary btn-lg mt-2">
            <i class="bi bi-upload"></i> Iniciar Importação de Itens
        </button>
    </form>
</div>