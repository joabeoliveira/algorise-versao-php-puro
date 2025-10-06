<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Cotação Rápida</title>
    <style>
        /* --- ESTILOS OTIMIZADOS PARA O PDF --- */
        @page {
            /* 1. Margens da página reduzidas */
            margin: 0.7in;
        }
        body { 
            font-family: 'Times New Roman', Times, serif; 
            font-size: 11pt; 
            line-height: 1.4; 
            color: #333;
        }
        h3, h4 { 
            font-family: 'Arial', sans-serif; 
            color: #2E4053; 
            text-align: center; 
            margin: 0; 
            padding: 0;
        }
        h3 { 
            font-size: 14pt; 
            margin-bottom: 20px; 
            text-transform: uppercase;
        }
        h4 { 
            font-size: 12pt; 
            text-align: left; 
            /* 2. Margens entre seções reduzidas */
            margin-top: 20px; 
            margin-bottom: 8px; 
            border-bottom: 1px solid #ccc; 
            padding-bottom: 4px; 
        }
        h5 {
            font-family: 'Arial', sans-serif;
            font-size: 10pt;
            margin-top: 15px;
            margin-bottom: 5px;
        }
        p, li { 
            text-align: justify; 
            margin-top: 0;
            margin-bottom: 8px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
            font-size: 9pt; 
        }
        th, td { 
            border: 1px solid #999; 
            padding: 5px; /* Padding da tabela reduzido */
            text-align: left; 
            vertical-align: top;
        }
        th { 
            background-color: #f2f2f2; 
            font-weight: bold; 
            text-align: center;
        }
        .text-center { text-align: center; }
        .text-danger { color: #c00; font-weight: bold; }
        .assinatura { margin-top: 60px; text-align: center; }

        /* 3. Lógica de quebra de página inteligente */
        .item-analysis-block {
            page-break-inside: avoid;
        }
        /* Estilos para cabeçalho com logo */
        .header-container {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #2E4053;
        }
        .header-logo {
            display: table-cell;
            width: 80px;
            vertical-align: middle;
        }
        .header-logo img {
            max-width: 70px;
            max-height: 70px;
            object-fit: contain;
        }
        .header-info {
            display: table-cell;
            vertical-align: middle;
            padding-left: 20px;
        }
        .header-info h2 {
            font-size: 16pt;
            margin: 0;
            color: #2E4053;
            font-family: 'Arial', sans-serif;
        }
        .header-info p {
            margin: 2px 0;
            font-size: 9pt;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Cabeçalho com logo e informações da empresa -->
    <?php if (!empty($dadosRelatorio['logo_path']) || !empty($dadosRelatorio['empresa']['nome'])): ?>
    <div class="header-container">
        <?php if (!empty($dadosRelatorio['logo_path'])): ?>
        <div class="header-logo">
            <img src="<?= __DIR__ . '/../../public' . htmlspecialchars($dadosRelatorio['logo_path']) ?>" alt="Logo">
        </div>
        <?php endif; ?>
        <div class="header-info">
            <h2><?= htmlspecialchars($dadosRelatorio['empresa']['nome'] ?? 'Empresa') ?></h2>
            <?php if (!empty($dadosRelatorio['empresa']['cnpj'])): ?>
            <p><strong>CNPJ:</strong> <?= htmlspecialchars($dadosRelatorio['empresa']['cnpj']) ?></p>
            <?php endif; ?>
            <?php if (!empty($dadosRelatorio['empresa']['endereco'])): ?>
            <p><?= htmlspecialchars($dadosRelatorio['empresa']['endereco']) ?></p>
            <?php endif; ?>
            <?php if (!empty($dadosRelatorio['empresa']['cidade']) && !empty($dadosRelatorio['empresa']['estado'])): ?>
            <p><?= htmlspecialchars($dadosRelatorio['empresa']['cidade']) ?> - <?= htmlspecialchars($dadosRelatorio['empresa']['estado']) ?></p>
            <?php endif; ?>
            <?php if (!empty($dadosRelatorio['empresa']['telefone']) || !empty($dadosRelatorio['empresa']['email'])): ?>
            <p>
                <?php if (!empty($dadosRelatorio['empresa']['telefone'])): ?>Tel: <?= htmlspecialchars($dadosRelatorio['empresa']['telefone']) ?><?php endif; ?>
                <?php if (!empty($dadosRelatorio['empresa']['email'])): ?> | Email: <?= htmlspecialchars($dadosRelatorio['empresa']['email']) ?><?php endif; ?>
            </p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <h3>Relatório Simplificado de Pesquisa de Preços</h3>
    <h4>NOTA Nº <?= sprintf('%04d', $dadosNota['numero_nota']) ?>/<?= $dadosNota['ano_nota'] ?> (Cotação Rápida)</h4>

    <h4>I - OBJETO</h4>
    <p>O presente relatório demonstra a pesquisa de preços realizada para subsidiar a estimativa de valor para os itens relacionados à pesquisa intitulada "<strong><?= htmlspecialchars($dadosCotacao['titulo']) ?></strong>".</p>

    <h4>II - FONTES CONSULTADAS</h4>
    <p>Para a definição do valor estimado, a pesquisa de preços utilizou exclusivamente os parâmetros dos **Incisos I (Painel de Preços) e II (Contratações similares)**, conforme priorização estabelecida pelo Art. 5º, §1º, da IN SEGES/ME nº 65/2021.</p>

    <?php foreach ($dadosItens as $item): ?>
        <div class="item-analysis-block">
            <h4>Análise do Item: <?= htmlspecialchars($item['catmat_catser']) ?></h4>
            <p><strong>Descrição:</strong> <?= htmlspecialchars($item['descricao_pesquisa']) ?></p>

            <h5>III - SÉRIE DE PREÇOS COLETADOS</h5>
            <table>
                <thead><tr><th>Fonte</th><th>Fornecedor/Órgão</th><th>Data</th><th>Preço Unitário (R$)</th><th class="text-center">Status / Justificativa</th></tr></thead>
                <tbody>
                    <?php if(empty($item['precos'])): ?>
                        <tr><td colspan="5" class="text-center">Nenhum preço foi encontrado para este item.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($item['precos'] as $preco): ?>
                        <tr>
                            <td><?= htmlspecialchars($preco['fonte_pesquisa']) ?></td>
                            <td><?= htmlspecialchars($preco['fornecedor_nome']) ?></td>
                            <td class="text-center"><?= date('d/m/Y', strtotime($preco['data_resultado'])) ?></td>
                            <td class="text-center"><?= number_format($preco['preco_unitario'], 2, ',', '.') ?></td>
                            <td><?= $preco['considerado'] ? 'Considerado' : '<span class="text-danger">Desconsiderado:</span> ' . htmlspecialchars($preco['justificativa_descarte'] ?? 'Justificativa não fornecida.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h5>IV - METODOLOGIA E ESTATÍSTICAS DO PREÇO UNITÁRIO</h5>
            <?php $stats = json_decode($item['estatisticas_json'], true); ?>
            <p>Após a análise crítica, a cesta de preços válida (com <strong><?= $stats['total'] ?></strong> preços) resultou nas seguintes estatísticas:</p>
            <table>
                <thead><tr><th>Valor Médio Unitário</th><th>Valor Mediano Unitário</th><th>Menor Valor Unitário</th></tr></thead>
                <tbody>
                    <tr>
                        <td class="text-center">R$ <?= number_format($stats['media'], 2, ',', '.') ?></td>
                        <td class="text-center">R$ <?= number_format($stats['mediana'], 2, ',', '.') ?></td>
                        <td class="text-center">R$ <?= number_format($stats['minimo'], 2, ',', '.') ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
    <h4>V - QUADRO RESUMO DO VALOR ESTIMADO</h4>
    <p>Com base na mediana dos preços unitários válidos para cada item, obteve-se a seguinte memória de cálculo para o valor total estimado da contratação:</p>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>CATMAT</th>
                <th>Descrição</th>
                <th class="text-center">Qtd.</th>
                <th class="text-center">Valor Apurado (Mediana Unitária)</th>
                <th class="text-center">Valor Total Estimado</th>
            </tr>
        </thead>
        <tbody>
            <?php $valorTotalGeral = 0; $itemCounter = 1; ?>
            <?php foreach ($dadosItens as $item): ?>
                <?php
                    $stats = json_decode($item['estatisticas_json'], true);
                    $valorTotalItem = ($stats['mediana'] ?? 0) * ($item['quantidade'] ?? 1);
                    $valorTotalGeral += $valorTotalItem;
                ?>
                <tr>
                    <td class="text-center"><?= $itemCounter++ ?></td>
                    <td class="text-center"><?= htmlspecialchars($item['catmat_catser']) ?></td>
                    <td><?= htmlspecialchars($item['descricao_pesquisa']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($item['quantidade']) ?></td>
                    <td class="text-center">R$ <?= number_format($stats['mediana'] ?? 0, 2, ',', '.') ?></td>
                    <td class="text-center">R$ <?= number_format($valorTotalItem, 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="5" style="text-align: right; font-weight: bold;">VALOR TOTAL GERAL ESTIMADO</td>
                <td class="text-center" style="font-weight: bold; background-color: #f2f2f2;">R$ <?= number_format($valorTotalGeral, 2, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>

    <h4>VI - IDENTIFICAÇÃO DO RESPONSÁVEL</h4>
    <p>A presente pesquisa de preços foi conduzida por: <strong><?= htmlspecialchars($dadosNota['gerada_por']) ?></strong>.</p>
    <p>Relatório gerado em: <strong><?= date('d/m/Y H:i:s', strtotime($dadosNota['gerada_em'])) ?></strong>.</p>
</body>
</html>