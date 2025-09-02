<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Histórico de Relatórios Gerados</h1>
</div>

<div class="table-responsive mt-4">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>Número da Nota</th>
                <th>Origem / Título</th>
                <th>Gerada Por</th>
                <th>Data de Geração</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($notas)): ?>
                <tr>
                    <td colspan="5" class="text-center">Nenhum relatório foi gerado ainda.</td>
                </tr>
            <?php endif; ?>

            <?php foreach ($notas as $nota): ?>
                <tr>
                    <td>
                        <strong><?= sprintf('%04d', $nota['numero_nota']) ?>/<?= $nota['ano_nota'] ?></strong>
                    </td>
                    <td>
                        <?php if ($nota['tipo'] === 'COTACAO_RAPIDA'): ?>
                            <div>
                                <span class="badge bg-info">Cotação Rápida</span>
                            </div>
                            <div class="small text-muted"><?= htmlspecialchars($nota['titulo_cotacao']) ?></div>
                        <?php else: ?>
                            <div>
                                <span class="badge bg-secondary">Processo</span>
                            </div>
                            <div class="small text-muted"><?= htmlspecialchars($nota['numero_processo']) ?> - <?= htmlspecialchars($nota['nome_processo']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($nota['gerada_por']) ?></td>
                    <td><?= date('d/m/Y H:i:s', strtotime($nota['gerada_em'])) ?></td>
                    <td>
                        <a href="/relatorios/<?= $nota['id'] ?>/visualizar" target="_blank" class="btn btn-sm btn-outline-secondary" title="Visualizar Relatório">
                            <i class="bi bi-file-earmark-pdf"></i> Visualizar
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>