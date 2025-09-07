<style>
    /* Estilos para a visualização normal do formulário na tela */
    .form-container { max-width: 900px; margin: auto; }
    .card-header { font-weight: bold; }
    .campo-destacado { background-color: #fffde7 !important; border-left: 4px solid #ffc107 !important; }
    .campo-destacado:focus { box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25) !important; }
    label .bi-pencil-fill { color: #ffc107; margin-left: 5px; font-size: 0.9em; }

    /* Estilos que formatam a página para parecer um documento ao imprimir */
    @media print {
        /* Esconde elementos indesejados (botões, header principal, etc.) */
        .no-print, body > main > .card > .card-header { 
            display: none !important; 
        }

        /* Expande o conteúdo para ocupar a folha */
        body, body > main, .card-body, .form-container {
            background-color: #fff !important;
            padding: 0 !important; margin: 0 !important;
            max-width: 100% !important;
        }
        .card { border: none !important; box-shadow: none !important; }
        
        /* Formata os inputs para parecerem texto plano */
        .form-control, .form-select {
            border: none !important; box-shadow: none !important; padding: 0 !important;
            background-color: transparent !important; color: #000;
        }
        label { font-weight: bold; }

        /* Garante que a assinatura não seja cortada */
        #bloco-assinatura { page-break-inside: avoid; }
        .table { font-size: 10pt; }
        h3, h6 { margin-top: 1rem; }
    }
</style>

<div class="form-container">
    <form action="/cotacao/responder" method="POST" enctype="multipart/form-data" id="form-cotacao">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
        
        <div class="text-center">
            <h3 class="mb-1">PROPOSTA COMERCIAL</h3>
            <p class="text-muted"><strong>Processo de Referência:</strong> <?= htmlspecialchars($solicitacao['nome_processo'] ?? 'N/A') ?></p>
            <p class="text-center text-danger no-print"><strong>Prazo para resposta: <?= isset($solicitacao['prazo_final']) ? date('d/m/Y', strtotime($solicitacao['prazo_final'])) : 'N/A' ?></strong></p>
            
        </div>
        
        <hr>

        <div class="card mb-4">
    <div class="card-header">1. Dados do Proponente (Confirme ou Altere se Necessário)</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8 mb-2"><label class="form-label">Razão Social</label><input type="text" class="form-control" name="proponente[razao_social]" value="<?= htmlspecialchars($solicitacao['razao_social'] ?? '') ?>" required></div>
            <div class="col-md-4 mb-2"><label class="form-label">CNPJ</label><input type="text" class="form-control" id="form_cnpj" name="proponente[cnpj]" value="<?= htmlspecialchars($solicitacao['cnpj'] ?? '') ?>" required></div>
        </div>
        <div class="mb-2"><label class="form-label">Endereço Completo</label><input type="text" class="form-control" name="proponente[endereco]" value="<?= htmlspecialchars($solicitacao['endereco'] ?? '') ?>" required></div>
        <div class="row">
            <div class="col-md-6 mb-2"><label class="form-label">E-mail</label><input type="email" class="form-control" name="proponente[email]" value="<?= htmlspecialchars($solicitacao['email'] ?? '') ?>" required></div>
            <div class="col-md-6 mb-2"><label class="form-label">Telefone</label><input type="tel" class="form-control" id="form_telefone" name="proponente[telefone]" value="<?= htmlspecialchars($solicitacao['telefone'] ?? '') ?>" required></div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-2"><label class="form-label">Validade (dias)</label><input type="number" class="form-control" name="proponente[validade_proposta]" value="60" required></div>
            <div class="col-md-6 mb-2"><label class="form-label">Data de Emissão</label><input type="date" class="form-control" name="proponente[data_emissao]" value="<?= date('Y-m-d') ?>" required></div>
        </div>
        <div class="row">
            <div class="col-md-8 mb-2"><label class="form-label">Nome do Responsável<i class="bi bi-pencil-fill no-print"></i></label><input type="text" class="form-control campo-destacado" id="form_responsavel_nome" name="proponente[responsavel_nome]" required></div>
            <div class="col-md-4 mb-2"><label class="form-label">CPF do Responsável<i class="bi bi-pencil-fill no-print"></i></label><input type="text" class="form-control campo-destacado" id="form_responsavel_cpf" name="proponente[responsavel_cpf]" required></div>
        </div>
    </div>
    </div>

        <div class="card mb-4">
            <div class="card-header">2. Itens para Cotação</div>
            <div class="card-body">
                <p class="text-muted no-print">Preencha o valor unitário para os itens que deseja cotar.</p>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th style="width: 40%;">Descrição do Item</th>
                                <th class="text-center">Unid.</th>
                                <th class="text-center">Qtd.</th>
                                <th>Preço Unit. (R$)</th>
                                <th>Valor Total (R$)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itens ?? [] as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['descricao']) ?></td>
                            
                            <td class="text-center">
                                <input type="text" class="form-control text-center" value="<?= htmlspecialchars($item['unidade_medida']) ?>" readonly>
                            </td>

                            <td class="text-center">
                                <input type="text" class="form-control text-center" id="qtd_<?= $item['id'] ?>" value="<?= htmlspecialchars($item['quantidade']) ?>" readonly>
                            </td>
                            <td>
                                <input type="number" step="0.01" class="form-control campo-destacado preco-unitario text-end" name="precos[<?= $item['id'] ?>][valor]" data-item-id="<?= $item['id'] ?>">
                                
                                <input type="hidden" name="precos[<?= $item['id'] ?>][unidade_medida]" value="<?= htmlspecialchars($item['unidade_medida']) ?>">
                                </td>            
                            <td>
                                <input type="text" class="form-control valor-total text-end" readonly>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div id="bloco-assinatura" class="mt-5 pt-5 text-center">
             <div class="row"><div class="col-6" style="margin: auto;"><hr><p class="mb-0"><strong>Assinatura do Responsável</strong></p><p class="text-muted" id="assinatura-nome"></p></div></div>
        </div>

        <div class="no-print">
            <hr>
            <div class="card mb-4 border-danger">
                <div class="card-header bg-danger text-white">3. Anexar Proposta Formal</div>
                <div class="card-body">
                    <p>Use o botão "Visualizar..." para gerar e salvar o PDF. Em seguida, assine-o e anexe o arquivo finalizado aqui.</p>
                    <input class="form-control" type="file" name="proposta_anexo" accept=".pdf" required>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-secondary" id="btn-imprimir-proposta"><i class="bi bi-printer"></i> Visualizar Proposta (Salvar como PDF)</button>
                <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-send"></i> Enviar Cotação</button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-cotacao');
    if (!form) return;

    // LÓGICA DE CÁLCULO AUTOMÁTICO
    form.addEventListener('input', function(e) {
        if (e.target.classList.contains('preco-unitario')) {
            const itemId = e.target.dataset.itemId;
            const quantidade = parseFloat(document.getElementById(`qtd_${itemId}`).value) || 0;
            const precoUnitario = parseFloat(e.target.value) || 0;
            const total = quantidade * precoUnitario;

            // =======================================================
            //     INÍCIO DA CORREÇÃO: PROCURAR 'tr' EM VEZ DE '.row'
            // =======================================================
            const campoTotal = e.target.closest('tr').querySelector('.valor-total');
            // =======================================================
            //                      FIM DA CORREÇÃO
            // =======================================================

            campoTotal.value = total > 0 ? total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) : '';
        }
    });

    // LÓGICA DE IMPRESSÃO DO NAVEGADOR (permanece igual)
    const btnImprimir = document.getElementById('btn-imprimir-proposta');
    if (btnImprimir) {
        btnImprimir.addEventListener('click', function() {
            const nomeResponsavelInput = document.getElementById('form_responsavel_nome');
            if (!nomeResponsavelInput.value) {
                alert('Por favor, preencha o "Nome do Responsável" antes de visualizar a impressão.');
                nomeResponsavelInput.focus();
                return;
            }
            document.getElementById('assinatura-nome').textContent = nomeResponsavelInput.value;
            window.print();
        });
    }
});
</script>