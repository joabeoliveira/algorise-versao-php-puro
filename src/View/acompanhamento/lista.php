<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Acompanhamento de Solicitações de Cotação</h1>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered table-primary">
        <thead class="table-primary">
            <tr>
                <th>Status</th>
                <th>Processo</th>
                <th>Fornecedor</th>
                <th>Data de Envio</th>
                <th>Prazo Final</th>
                <th>Anexo</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($solicitacoes)): ?>
                <tr>
                    <td colspan="6" class="text-center">Nenhuma solicitação de cotação foi enviada ainda.</td>
                </tr>
            <?php endif; ?>

            <?php foreach ($solicitacoes as $solicitacao): ?>
                <tr>
                    <td>
                        <?php
                            $status = $solicitacao['status'];
                            $prazoFinal = new DateTime($solicitacao['prazo_final']);
                            $hoje = new DateTime();
                            $badgeClass = 'bg-secondary';
                            $statusTexto = 'Aguardando Resposta';

                            if ($status === 'Respondido') {
                                $badgeClass = 'bg-success';
                                $statusTexto = 'Respondido em ' . date('d/m/Y', strtotime($solicitacao['data_resposta']));
                            } elseif ($hoje > $prazoFinal) {
                                $badgeClass = 'bg-danger';
                                $statusTexto = 'Prazo Expirado';
                            } else {
                                $badgeClass = 'bg-warning text-dark';
                            }
                        ?>
                        <span class="badge <?= $badgeClass ?>"><?= $statusTexto ?></span>
                    </td>
                    <td><?= htmlspecialchars($solicitacao['nome_processo']) ?></td>
                    <td><?= htmlspecialchars($solicitacao['razao_social']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($solicitacao['data_envio'])) ?></td>
                    <td><?= date('d/m/Y', strtotime($solicitacao['prazo_final'])) ?></td>
                    <td>
                        <?php if (!empty($solicitacao['caminho_anexo'])): ?>
                            <a href="/download.php?file=<?= htmlspecialchars($solicitacao['caminho_anexo']) ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="<?= htmlspecialchars($solicitacao['nome_original_anexo']) ?>">
                                <i class="bi bi-file-earmark-pdf"></i> Baixar
                            </a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="alert alert-info mt-4">
    <strong>Legenda de Status:</strong>
    <ul>
        <li><span class="badge bg-warning text-dark">Aguardando Resposta:</span> A solicitação foi enviada e ainda está dentro do prazo.</li>
        <li><span class="badge bg-success">Respondido:</span> O fornecedor enviou a cotação e o anexo.</li>
        <li><span class="badge bg-danger">Prazo Expirado:</span> O prazo para resposta terminou e o fornecedor não enviou a cotação. <strong>Este é o registro formal de não-resposta.</strong></li>
    </ul>
</div>