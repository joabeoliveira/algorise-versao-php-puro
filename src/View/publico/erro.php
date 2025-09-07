<div class="alert alert-danger text-center">
    <h4 class="alert-heading">Ocorreu um Erro</h4>
    <p class="mb-0"><?= htmlspecialchars($mensagem ?? 'Não foi possível processar sua solicitação.') ?></p>
</div>
<div class="text-center mt-3">
    <a href="javascript:history.back()" class="btn btn-secondary">Voltar</a>
</div>