<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Nota Técnica - Pesquisa de Preços</title>
    <style>
        @page { margin: 1in; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; }
        h3, h4 { font-family: 'Arial', sans-serif; color: #2E4053; text-align: center; margin: 0; padding: 0; }
        h3 { font-size: 14pt; margin-bottom: 20px; }
        h4 { font-size: 12pt; text-align: left; margin-top: 25px; margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        p, li { text-align: justify; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 9pt; }
        th, td { border: 1px solid #999; padding: 6px; text-align: left; vertical-align: top; }
        th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
        .text-center { text-align: center; }
        .text-danger { color: #c00; }
        .page-break { page-break-after: always; }
        .assinatura { margin-top: 80px; text-align: center; }
    </style>
</head>
<body>
    <h3>NOTA TÉCNICA Nº <?= sprintf('%04d', $novoNumero) ?>/<?= $anoAtual ?></h3>

    <h4>I - OBJETO DA CONTRATAÇÃO</h4>
    <p>O objeto da presente contratação refere-se a <strong><?= htmlspecialchars($dadosProcesso['nome_processo']) ?></strong>, conforme especificações detalhadas nos itens listados neste documento e no Termo de Referência do Processo nº <?= htmlspecialchars($dadosProcesso['numero_processo']) ?>.</p>

    <h4>II - FONTES CONSULTADAS</h4>
    <p>2.1. Para a definição do valor estimado da contratação foram utilizados os parâmetros dos incisos: <strong><?= htmlspecialchars(implode(', ', $dadosFontes['fontes_utilizadas'])) ?></strong> da IN SEGES/ME nº 65/2021.</p>
    
    <?php if ($dadosFontes['priorizou_oficiais'] || empty($dadosProcesso['justificativa_fontes'])): ?>
        <p>2.2. Foram priorizadas as consultas aos sistemas oficiais de governo e/ou às contratações similares feitas pela Administração Pública, em conformidade com o artigo 5º, §1º, da IN SEGES/ME nº 65/2021.</p>
    <?php else: ?>
        <p>2.2. Não foram priorizados os parâmetros do artigo 5º, incisos I e II, da IN SEGES/ME n° 65/2021, pela seguinte razão: "<?= htmlspecialchars($dadosProcesso['justificativa_fontes']) ?>"</p>
    <?php endif; ?>

    <?php if (!empty($dadosSolicitacoes)): ?>
        <p>2.3. Na consulta direta com fornecedores, foi enviada comunicação às seguintes empresas:</p>
        <table>
            <thead><tr><th>Fornecedor</th><th class="text-center">Apresentou resposta?</th><th>Justificativa para escolha</th></tr></thead>
            <tbody>
                <?php foreach ($dadosSolicitacoes as $solicitacao): ?>
                    <tr>
                        <td><?= htmlspecialchars($solicitacao['razao_social']) ?></td>
                        <td class="text-center"><?= ($solicitacao['status'] === 'Respondido' ? 'Sim' : 'Não') ?></td>
                        <td><?= htmlspecialchars($solicitacao['justificativa_fornecedores']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h4>III - SÉRIE DE PREÇOS COLETADOS E ANÁLISE CRÍTICA</h4>
    <?php foreach ($dadosItens as $item): ?>
        <p><strong>Item <?= htmlspecialchars($item['numero_item']) ?>: <?= htmlspecialchars($item['descricao']) ?></strong></p>
        <table>
            <thead><tr><th>Fonte da Pesquisa</th><th>Fornecedor/Órgão</th><th>Data</th><th>Valor (R$)</th><th class="text-center">Status</th><th>Justificativa do Descarte</th></tr></thead>
            <tbody>
                <?php foreach ($item['precos'] as $preco): ?>
                    <tr>
                        <td><?= htmlspecialchars($preco['fonte']) ?></td>
                        <td><?= htmlspecialchars($preco['fornecedor_nome'] ?: 'N/A') ?></td>
                        <td class="text-center"><?= date('d/m/Y', strtotime($preco['data_coleta'])) ?></td>
                        <td class="text-center"><?= number_format($preco['valor'], 2, ',', '.') ?></td>
                        <td class="text-center <?= $preco['status_analise'] === 'desconsiderado' ? 'text-danger' : '' ?>"><?= ucfirst($preco['status_analise']) ?></td>
                        <td><?= htmlspecialchars($preco['justificativa_descarte'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($item['precos'])): ?>
                    <tr><td colspan="6" class="text-center">Nenhum preço coletado para este item.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endforeach; ?>

    <div class="page-break"></div>
    <h3>MEMORIAL DE CÁLCULO E CONCLUSÃO</h3>

    <h4>IV - METODOLOGIA PARA OBTENÇÃO DO PREÇO ESTIMADO</h4>
    <?php foreach ($dadosItens as $item): ?>
        <?php
            $precosValidos = array_filter($item['precos'], fn($p) => $p['status_analise'] === 'considerado');
            $numPrecosValidos = count($precosValidos);
        ?>
        <p><strong>Item <?= htmlspecialchars($item['numero_item']) ?>:</strong></p>
        <ul>
            <li>A obtenção do preço estimado deu-se com base na metodologia de "<strong><?= htmlspecialchars($item['metodologia_estimativa']) ?></strong>", em razão de: "<?= htmlspecialchars($item['justificativa_estimativa']) ?>"</li>
            <?php if ($numPrecosValidos < 3 && !empty($item['justificativa_excepcionalidade'])): ?>
                <li>Não foi possível a obtenção do mínimo de três preços para estimativa, pois: "<?= htmlspecialchars($item['justificativa_excepcionalidade']) ?>"</li>
            <?php endif; ?>
        </ul>
    <?php endforeach; ?>

    <p>4.2. Dentro dos preços coletados, foram desconsiderados aqueles inexequíveis, inconsistentes ou excessivamente elevados, conforme abaixo:</p>
    <?php if (!empty($precosDesconsiderados)): ?>
        <table>
            <thead>
                <tr>
                    <th>Fonte da Pesquisa</th>
                    <th>Preço (R$)</th>
                    <th>Justificativa do Descarte</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($precosDesconsiderados as $preco): ?>
                <tr>
                    <td><?= htmlspecialchars($preco['fonte']) ?> (Item <?= htmlspecialchars($preco['numero_item']) ?>)</td>
                    <td class="text-center"><?= number_format($preco['valor'], 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($preco['justificativa_descarte']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center">Nenhum preço foi desconsiderado nesta pesquisa.</p>
    <p>Não houve preços desconsiderados na presente pesquisa.</p>
<?php endif; ?>

    <h4>V - MEMÓRIA DE CÁLCULO E CONCLUSÃO</h4>
    <p>5.1. O preço estimado da contratação é de <strong>R$ <?= number_format(array_sum(array_map(fn($i) => $i['valor_estimado'] * $i['quantidade'], $dadosItens)), 2, ',', '.') ?></strong>, conforme memória de cálculo abaixo:</p>
    <table>
        <thead><tr><th>Item</th><th>Descrição</th><th>Qtd.</th><th>Valor Unit. Estimado (R$)</th><th>Valor Total Estimado (R$)</th></tr></thead>
        <tbody>
            <?php $valorTotalGeral = 0; ?>
            <?php foreach ($dadosItens as $item): ?>
                <?php
                    $valorTotalItem = $item['valor_estimado'] * $item['quantidade'];
                    $valorTotalGeral += $valorTotalItem;
                ?>
                <tr>
                    <td class="text-center"><?= htmlspecialchars($item['numero_item']) ?></td>
                    <td><?= htmlspecialchars($item['descricao']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($item['quantidade']) ?></td>
                    <td class="text-center"><?= number_format($item['valor_estimado'], 2, ',', '.') ?></td>
                    <td class="text-center"><?= number_format($valorTotalItem, 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="4" style="text-align: right; font-weight: bold;">VALOR TOTAL GERAL DA CONTRATAÇÃO</td>
                <td class="text-center" style="font-weight: bold; background-color: #f2f2f2;"><?= number_format($valorTotalGeral, 2, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>
    <p>5.2. Após a realização de pesquisa de preços em conformidade com a IN SEGES/ME nº 65/2021, certifica-se que o preço estimado para a presente contratação é compatível com os praticados no mercado.</p>

    <h4>VI - IDENTIFICAÇÃO DOS AGENTES RESPONSÁVEIS PELA PESQUISA</h4>
    <p>6.1. A presente pesquisa de preços foi conduzida por: <strong><?= htmlspecialchars($dadosProcesso['agente_responsavel']) ?></strong>, matrícula nº <strong><?= htmlspecialchars($dadosProcesso['agente_matricula'] ?? 'Não informada') ?></strong>.</p>
    
    <div class="assinatura">
        <p>___________________________________________________</p>
        <p><?= htmlspecialchars($dadosProcesso['agente_responsavel']) ?></p>
        <p>Responsável pela Pesquisa de Preços</p>
    </div>
</body>
</html>