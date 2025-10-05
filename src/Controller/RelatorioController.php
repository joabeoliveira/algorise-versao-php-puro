<?php

namespace Joabe\Buscaprecos\Controller;

use Joabe\Buscaprecos\Core\Router;
use Joabe\Buscaprecos\Core\Pdf;

class RelatorioController
{
    /**
     * Lista todos os relatórios (notas técnicas)
     */
    public function listar($params = [])
    {
        $pdo = \getDbConnection();
        $stmt = $pdo->query("
            SELECT nt.*, p.nome_processo, p.numero_processo
            FROM notas_tecnicas nt
            JOIN processos p ON nt.processo_id = p.id
            ORDER BY nt.created_at DESC
        ");
        $relatorios = $stmt->fetchAll();

        $tituloPagina = "Relatórios Gerados";
        $paginaConteudo = __DIR__ . '/../View/relatorios/listar.php';

        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();
        echo $view;
    }

    /**
     * Gera relatório (nota técnica) para um processo
     */
    public function gerarRelatorio($params = [])
    {
        $processo_id = $params['id'] ?? 0;
        $queryParams = Router::getQueryData();
        $nota_existente_id = $queryParams['nota_id'] ?? null;

        $pdo = \getDbConnection();

        // =======================================================
        //     LÓGICA CONDICIONAL DE NUMERAÇÃO
        // =======================================================
        $numero_nota = 0;
        $ano_nota = 0;
        $isRegeneration = false;

        if ($nota_existente_id) {
            // MODO VISUALIZAÇÃO: Busca os dados da nota existente
            $stmtExistente = $pdo->prepare("SELECT numero_nota, ano_nota FROM notas_tecnicas WHERE id = ?");
            $stmtExistente->execute([$nota_existente_id]);
            $nota = $stmtExistente->fetch(\PDO::FETCH_ASSOC);
            if ($nota) {
                $numero_nota = $nota['numero_nota'];
                $ano_nota = $nota['ano_nota'];
                $isRegeneration = true;
            }
        }
        
        // Se não encontrou uma nota existente, gera uma nova
        if (!$isRegeneration) {
            // MODO CRIAÇÃO: Gera um novo número sequencial
            $ano_nota = date('Y');
            $stmtNum = $pdo->prepare("SELECT MAX(numero_nota) FROM notas_tecnicas WHERE ano_nota = ?");
            $stmtNum->execute([$ano_nota]);
            $ultimoNumero = $stmtNum->fetchColumn();
            $numero_nota = $ultimoNumero ? $ultimoNumero + 1 : 1;
        }

        // Busca dados do processo
        $stmtProcesso = $pdo->prepare("SELECT * FROM processos WHERE id = ?");
        $stmtProcesso->execute([$processo_id]);
        $processo = $stmtProcesso->fetch(\PDO::FETCH_ASSOC);

        if (!$processo) {
            $_SESSION['flash_error'] = 'Processo não encontrado.';
            Router::redirect('/processos');
            return;
        }

        // Busca itens do processo
        $stmtItens = $pdo->prepare("
            SELECT i.*, 
                   COUNT(p.id) as total_precos,
                   MIN(p.valor_unitario) as menor_preco,
                   MAX(p.valor_unitario) as maior_preco,
                   AVG(p.valor_unitario) as preco_medio
            FROM itens i
            LEFT JOIN precos p ON i.id = p.item_id AND p.desconsiderado = 0
            WHERE i.processo_id = ?
            GROUP BY i.id
            ORDER BY i.numero_item
        ");
        $stmtItens->execute([$processo_id]);
        $itens = $stmtItens->fetchAll(\PDO::FETCH_ASSOC);

        // Busca preços para cada item
        foreach ($itens as &$item) {
            $stmtPrecos = $pdo->prepare("
                SELECT p.*, f.razao_social as fornecedor_nome, f.cnpj as fornecedor_cnpj
                FROM precos p
                JOIN fornecedores f ON p.fornecedor_id = f.id
                WHERE p.item_id = ? AND p.desconsiderado = 0
                ORDER BY p.valor_unitario ASC
            ");
            $stmtPrecos->execute([$item['id']]);
            $item['precos'] = $stmtPrecos->fetchAll(\PDO::FETCH_ASSOC);
        }

        // Monta dados para o template de relatório
        $dadosRelatorio = [
            'title' => "Nota Técnica Nº {$numero_nota}/{$ano_nota}",
            'subtitle' => "Processo: {$processo['numero_processo']} - {$processo['nome_processo']}",
            'header' => true,
            'sections' => []
        ];

        // Seção 1: Dados do Processo
        $dadosRelatorio['sections'][] = [
            'title' => '1. IDENTIFICAÇÃO DO PROCESSO',
            'content' => "
                <p><strong>Número:</strong> {$processo['numero_processo']}</p>
                <p><strong>Objeto:</strong> {$processo['nome_processo']}</p>
                <p><strong>Modalidade:</strong> " . ucfirst(str_replace('_', ' ', $processo['tipo_contratacao'])) . "</p>
                <p><strong>Responsável:</strong> {$processo['agente_responsavel']}</p>
                <p><strong>UASG:</strong> {$processo['uasg']}</p>
            "
        ];

        // Seção 2: Metodologia
        $dadosRelatorio['sections'][] = [
            'title' => '2. METODOLOGIA',
            'content' => "
                <p>A pesquisa de preços foi realizada conforme determina a Instrução Normativa SEGES/MP nº 65/2021, 
                utilizando sistema informatizado que consolida cotações de fornecedores do mercado.</p>
                <p>Foram pesquisados {count($itens)} itens junto a fornecedores especializados no ramo de atividade.</p>
            "
        ];

        // Seção 3: Resumo dos Itens
        if (!empty($itens)) {
            $tabelaItens = [
                'headers' => ['Item', 'Descrição', 'Unidade', 'Quantidade', 'Cotações', 'Menor Preço', 'Preço Médio'],
                'data' => []
            ];

            foreach ($itens as $item) {
                $tabelaItens['data'][] = [
                    $item['numero_item'],
                    substr($item['descricao_detalhada'], 0, 80) . (strlen($item['descricao_detalhada']) > 80 ? '...' : ''),
                    $item['unidade_medida'],
                    number_format($item['quantidade'], 0, ',', '.'),
                    $item['total_precos'],
                    $item['menor_preco'] ? formatarMoeda($item['menor_preco']) : 'N/A',
                    $item['preco_medio'] ? formatarMoeda($item['preco_medio']) : 'N/A'
                ];
            }

            $dadosRelatorio['sections'][] = [
                'title' => '3. RESUMO DOS ITENS PESQUISADOS',
                'table' => $tabelaItens
            ];
        }

        // Seção 4: Detalhamento por Item
        foreach ($itens as $item) {
            $conteudoItem = "
                <h4>Item {$item['numero_item']}</h4>
                <p><strong>Descrição:</strong> {$item['descricao_detalhada']}</p>
                <p><strong>Código CATMAT:</strong> {$item['codigo_catmat']}</p>
                <p><strong>Unidade:</strong> {$item['unidade_medida']}</p>
                <p><strong>Quantidade:</strong> " . number_format($item['quantidade'], 0, ',', '.') . "</p>
            ";

            if (!empty($item['precos'])) {
                $tabelaPrecos = [
                    'headers' => ['Fornecedor', 'CNPJ', 'Valor Unitário', 'Valor Total'],
                    'data' => []
                ];

                foreach ($item['precos'] as $preco) {
                    $valorTotal = $preco['valor_unitario'] * $item['quantidade'];
                    $tabelaPrecos['data'][] = [
                        $preco['fornecedor_nome'],
                        formatarString($preco['fornecedor_cnpj'], '##.###.###/####-##'),
                        formatarMoeda($preco['valor_unitario']),
                        formatarMoeda($valorTotal)
                    ];
                }

                $conteudoItem .= "<h5>Cotações Obtidas:</h5>";
                $conteudoItem .= self::generateTableHtml($tabelaPrecos);

                // Análise de preços
                if (count($item['precos']) > 1) {
                    $menorPreco = min(array_column($item['precos'], 'valor_unitario'));
                    $maiorPreco = max(array_column($item['precos'], 'valor_unitario'));
                    $variacao = (($maiorPreco - $menorPreco) / $menorPreco) * 100;

                    $conteudoItem .= "
                        <div class='info'>
                            <h5>Análise:</h5>
                            <p>Menor preço: " . formatarMoeda($menorPreco) . "</p>
                            <p>Maior preço: " . formatarMoeda($maiorPreco) . "</p>
                            <p>Variação: " . number_format($variacao, 2, ',', '.') . "%</p>
                        </div>
                    ";
                }
            } else {
                $conteudoItem .= "<p><em>Nenhuma cotação obtida para este item.</em></p>";
            }

            $dadosRelatorio['sections'][] = [
                'title' => '',
                'content' => $conteudoItem
            ];
        }

        // Seção 5: Conclusão
        $dadosRelatorio['sections'][] = [
            'title' => '4. CONCLUSÃO',
            'content' => "
                <p>A presente pesquisa de preços foi realizada de acordo com as normas vigentes, 
                obtendo-se cotações suficientes para demonstrar a adequação dos valores praticados no mercado.</p>
                <p>Os preços coletados serão utilizados como referência para a contratação, 
                observando-se sempre o princípio da economicidade e as demais disposições legais aplicáveis.</p>
            "
        ];

        // Rodapé
        $dadosRelatorio['footer'] = "
            <div class='signature-block'>
                <div class='signature-line'>
                    {$processo['agente_responsavel']}<br>
                    Agente Responsável pela Pesquisa
                </div>
            </div>
            <p class='small text-center'>
                Documento gerado automaticamente pelo Sistema Algorise em " . formatarDataHora(time()) . "
            </p>
        ";

        // Gera o PDF
        $pdf = Pdf::createReport($dadosRelatorio);

        // Salva no banco de dados se for uma nova nota
        if (!$isRegeneration) {
            try {
                $stmtInsert = $pdo->prepare("
                    INSERT INTO notas_tecnicas (processo_id, numero_nota, ano_nota, conteudo_html, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmtInsert->execute([$processo_id, $numero_nota, $ano_nota, $pdf->render()]);
                $nota_existente_id = $pdo->lastInsertId();
            } catch (\Exception $e) {
                logarEvento('error', 'Erro ao salvar nota técnica: ' . $e->getMessage());
            }
        }

        // Adiciona link para visualização posterior
        $pdf->addCss("
            .view-link {
                position: fixed;
                top: 10px;
                right: 10px;
                background: #007bff;
                color: white;
                padding: 8px 15px;
                text-decoration: none;
                border-radius: 4px;
                font-size: 12px;
                z-index: 1000;
            }
            @media print { .view-link { display: none; } }
        ");

        $linkVisualizacao = "/relatorios/{$nota_existente_id}/visualizar";
        $htmlFinal = str_replace(
            '<body>',
            "<body><a href='{$linkVisualizacao}' class='view-link no-print'>← Voltar para Lista</a>",
            $pdf->render()
        );

        // Output direto para o navegador
        header('Content-Type: text/html; charset=UTF-8');
        echo $htmlFinal;
    }

    /**
     * Visualiza uma nota técnica existente
     */
    public function visualizar($params = [])
    {
        $nota_id = $params['nota_id'] ?? 0;

        $pdo = \getDbConnection();
        $stmt = $pdo->prepare("
            SELECT nt.*, p.nome_processo, p.numero_processo
            FROM notas_tecnicas nt
            JOIN processos p ON nt.processo_id = p.id
            WHERE nt.id = ?
        ");
        $stmt->execute([$nota_id]);
        $nota = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$nota) {
            $_SESSION['flash_error'] = 'Relatório não encontrado.';
            Router::redirect('/relatorios');
            return;
        }

        // Adiciona link para regenerar o relatório
        $linkRegeneracao = "/processos/{$nota['processo_id']}/relatorio?nota_id={$nota_id}";
        $htmlFinal = str_replace(
            '<body>',
            "<body>
                <div class='no-print' style='position: fixed; top: 10px; right: 10px; z-index: 1000;'>
                    <a href='/relatorios' class='btn btn-secondary btn-sm'>← Lista</a>
                    <a href='{$linkRegeneracao}' class='btn btn-primary btn-sm'>Regenerar</a>
                </div>",
            $nota['conteudo_html']
        );

        // Output direto
        header('Content-Type: text/html; charset=UTF-8');
        echo $htmlFinal;
    }

    /**
     * Método auxiliar para gerar tabela HTML
     */
    private static function generateTableHtml($tableData)
    {
        if (empty($tableData)) {
            return '';
        }
        
        $html = '<table>';
        
        // Headers
        if (isset($tableData['headers'])) {
            $html .= '<thead><tr>';
            foreach ($tableData['headers'] as $header) {
                $html .= '<th>' . htmlspecialchars($header) . '</th>';
            }
            $html .= '</tr></thead>';
        }
        
        // Dados
        if (isset($tableData['data'])) {
            $html .= '<tbody>';
            foreach ($tableData['data'] as $row) {
                $html .= '<tr>';
                foreach ($row as $cell) {
                    $html .= '<td>' . htmlspecialchars($cell) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody>';
        }
        
        $html .= '</table>';
        return $html;
    }
}