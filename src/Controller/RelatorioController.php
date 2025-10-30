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
        
        // Primeiro verifica se a tabela existe e quais colunas tem
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE 'notas_tecnicas'");
            if ($stmt->rowCount() === 0) {
                // Se a tabela não existe, retorna array vazio
                $relatorios = [];
            } else {
                // Verifica quais colunas de data existem
                $stmt = $pdo->query("DESCRIBE notas_tecnicas");
                $columns = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                
                // Determina qual coluna de data usar para ordenação
                $dateColumn = 'id'; // fallback para id se não houver coluna de data
                if (in_array('gerada_em', $columns)) {
                    $dateColumn = 'nt.gerada_em';
                } elseif (in_array('created_at', $columns)) {
                    $dateColumn = 'nt.created_at';
                } elseif (in_array('data_criacao', $columns)) {
                    $dateColumn = 'nt.data_criacao';
                } elseif (in_array('data_geracao', $columns)) {
                    $dateColumn = 'nt.data_geracao';
                } else {
                    $dateColumn = 'nt.id';
                }
                
                $stmt = $pdo->query("
                    SELECT 
                        nt.*,
                        COALESCE(p.nome_processo, '') as nome_processo, 
                        COALESCE(p.numero_processo, '') as numero_processo,
                        COALESCE(cr.titulo, '') as titulo_cotacao,
                        COALESCE(nt.gerada_por, 'Sistema') as gerada_por
                    FROM notas_tecnicas nt
                    LEFT JOIN processos p ON nt.processo_id = p.id
                    LEFT JOIN cotacoes_rapidas cr ON nt.cotacao_rapida_id = cr.id
                    ORDER BY $dateColumn DESC
                ");
                $relatorios = $stmt->fetchAll();
            }
        } catch (\Exception $e) {
            // Debug temporário - registra erro no log
            error_log("Erro no RelatorioController::listar: " . $e->getMessage());
            // Se houver erro, retorna array vazio e não quebra a página
            $relatorios = [];
        }

        // A view espera variável $notas, não $relatorios
        $notas = $relatorios;
        
        $tituloPagina = "Relatórios Gerados";
        $paginaConteudo = __DIR__ . '/../View/relatorios/lista.php';

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
        try {
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

            // Carregar configurações da empresa e interface para aplicar nos relatórios
            $configsEmpresa = ConfiguracaoController::getConfiguracoesPorCategoria('empresa');
            $configsInterface = ConfiguracaoController::getConfiguracoesPorCategoria('interface');

            // Busca itens do processo
            $stmtItens = $pdo->prepare("
                SELECT i.*, 
                       COUNT(p.id) as total_precos,
                       MIN(p.valor) as menor_preco,
                       MAX(p.valor) as maior_preco,
                       AVG(p.valor) as preco_medio
                FROM itens i
                LEFT JOIN precos_coletados p ON i.id = p.item_id AND p.status_analise = 'considerado'
                WHERE i.processo_id = ?
                GROUP BY i.id
                ORDER BY i.numero_item
            ");
            $stmtItens->execute([$processo_id]);
            $itens = $stmtItens->fetchAll(\PDO::FETCH_ASSOC);

            // Busca preços para cada item
            foreach ($itens as &$item) {
                $stmtPrecos = $pdo->prepare("
                    SELECT p.*, p.fornecedor_nome, p.fornecedor_cnpj
                    FROM precos_coletados p
                    WHERE p.item_id = ? AND p.status_analise = 'considerado'
                    ORDER BY p.valor ASC
                ");
                $stmtPrecos->execute([$item['id']]);
                $item['precos'] = $stmtPrecos->fetchAll(\PDO::FETCH_ASSOC);
            }

            // Monta dados para o template de relatório
            $dadosRelatorio = [
                'title' => "Nota Técnica Nº {$numero_nota}/{$ano_nota}",
                'subtitle' => "Processo: {$processo['numero_processo']} - {$processo['nome_processo']}",
                'header' => true,
                'sections' => [],
                // Dados da empresa para cabeçalho do relatório
                'empresa' => [
                    'nome' => $configsEmpresa['empresa_nome'] ?? 'Empresa',
                    'cnpj' => $configsEmpresa['empresa_cnpj'] ?? '',
                    'endereco' => $configsEmpresa['empresa_endereco'] ?? '',
                    'cidade' => $configsEmpresa['empresa_cidade'] ?? '',
                    'estado' => $configsEmpresa['empresa_estado'] ?? '',
                    'cep' => $configsEmpresa['empresa_cep'] ?? '',
                    'telefone' => $configsEmpresa['empresa_telefone'] ?? '',
                    'email' => $configsEmpresa['empresa_email'] ?? ''
                ],
                // Logo da empresa
                'logo_path' => $configsInterface['interface_logo_path'] ?? null
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
                    <p>Foram pesquisados " . count($itens) . " itens junto a fornecedores especializados no ramo de atividade.</p>
                "
            ];

            // Seção 3: Resumo dos Itens
            if (!empty($itens)) {
                $tabelaItens = [
                    'headers' => ['Item', 'Descrição', 'Unidade', 'Quantidade', 'Cotações', 'Menor Preço', 'Preço Médio'],
                    'data' => []
                ];

                foreach ($itens as $item) {
                    $descricao = $item['descricao'] ?? '';
                    $tabelaItens['data'][] = [
                        $item['numero_item'],
                        substr($descricao, 0, 80) . (strlen($descricao) > 80 ? '...' : ''),
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
                $descricao = $item['descricao'] ?? 'Descrição não informada';
                $catmat = $item['catmat_catser'] ?? 'N/A';
                $conteudoItem = "
                    <h4>Item {$item['numero_item']}</h4>
                    <p><strong>Descrição:</strong> {$descricao}</p>
                    <p><strong>Código CATMAT:</strong> {$catmat}</p>
                    <p><strong>Unidade:</strong> {$item['unidade_medida']}</p>
                    <p><strong>Quantidade:</strong> " . number_format($item['quantidade'], 0, ',', '.') . "</p>
                ";

                if (!empty($item['precos'])) {
                    $tabelaPrecos = [
                        'headers' => ['Fornecedor', 'CNPJ', 'Valor Unitário', 'Valor Total'],
                        'data' => []
                    ];

                    foreach ($item['precos'] as $preco) {
                        $valorTotal = $preco['valor'] * $item['quantidade'];
                        $tabelaPrecos['data'][] = [
                            $preco['fornecedor_nome'],
                            formatarString($preco['fornecedor_cnpj'], '##.###.###/####-##'),
                            formatarMoeda($preco['valor']),
                            formatarMoeda($valorTotal)
                        ];
                    }

                    $conteudoItem .= "<h5>Cotações Obtidas:</h5>";
                    $conteudoItem .= self::generateTableHtml($tabelaPrecos);

                    // Análise de preços
                    if (count($item['precos']) > 1) {
                        $menorPreco = min(array_column($item['precos'], 'valor'));
                        $maiorPreco = max(array_column($item['precos'], 'valor'));
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
                $geradoPor = $_SESSION['usuario_nome'] ?? 'Sistema';
                $stmtInsert = $pdo->prepare("
                    INSERT INTO notas_tecnicas (processo_id, numero_nota, ano_nota, tipo, gerada_por, gerada_em)
                    VALUES (?, ?, ?, 'PROCESSO', ?, NOW())
                ");
                $stmtInsert->execute([$processo_id, $numero_nota, $ano_nota, $geradoPor]);
                $nota_existente_id = $pdo->lastInsertId();
            }

            // Adiciona link para voltar à lista
            $pdf->addCss("
                .view-link {
                    position: fixed;
                    top: 10px;
                    right: 10px;
                    background: #28a745;
                    color: white !important;
                    padding: 10px 20px;
                    text-decoration: none;
                    border-radius: 5px;
                    font-size: 14px;
                    font-weight: bold;
                    z-index: 1000;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                    transition: all 0.3s ease;
                }
                .view-link:hover {
                    background: #218838;
                    text-decoration: none;
                    color: white !important;
                    transform: translateY(-1px);
                }
                @media print { .view-link { display: none; } }
            ");

            $linkListaRelatorios = "/relatorios";
            $htmlFinal = str_replace(
                '<body>',
                "<body><a href='{$linkListaRelatorios}' class='view-link no-print'>← Voltar para Lista</a>",
                $pdf->render()
            );

            // Output direto para o navegador
            header('Content-Type: text/html; charset=UTF-8');
            echo $htmlFinal;

        } catch (\Exception $e) {
            error_log("Erro ao gerar relatório: " . $e->getMessage());
            if (function_exists('logarEvento')) {
                logarEvento('error', 'Erro ao gerar relatório: ' . $e->getMessage(), ['processo_id' => $params['id'] ?? 0]);
            }
            $_SESSION['flash_error'] = 'Ocorreu um erro inesperado ao gerar o relatório. Tente novamente mais tarde.';
            
            $processo_id = $params['id'] ?? 0;
            if ($processo_id) {
                Router::redirect('/processos/' . $processo_id);
            } else {
                Router::redirect('/processos');
            }
            return;
        }
    }

    /**
     * Visualiza uma nota técnica existente (regenera em tempo real)
     */
    public function visualizar($params = [])
    {
        try {
            $nota_id = $params['nota_id'] ?? 0;

            $pdo = \getDbConnection();
            // Busca a nota e o seu TIPO
            $stmt = $pdo->prepare("SELECT id, processo_id, cotacao_rapida_id, tipo FROM notas_tecnicas WHERE id = ?");
            $stmt->execute([$nota_id]);
            $nota = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Log para debug
            error_log("DEBUG visualizar() - nota_id: {$nota_id}, dados: " . json_encode($nota));

            if (!$nota) {
                $_SESSION['flash_error'] = 'Relatório não encontrado.';
                Router::redirect('/relatorios');
                return;
            }

            // Redireciona com base no tipo da nota (case-insensitive)
            $redirectUrl = '/relatorios'; // URL padrão de fallback
            $tipoNormalizado = strtoupper($nota['tipo'] ?? '');

            if ($tipoNormalizado === 'PROCESSO' && !empty($nota['processo_id'])) {
                // Se for de PROCESSO, redireciona para a rota de relatório de processo
                $redirectUrl = "/processos/{$nota['processo_id']}/relatorio?nota_id={$nota_id}";
                error_log("DEBUG: Redirecionando para PROCESSO: {$redirectUrl}");
            } elseif ($tipoNormalizado === 'COTACAO_RAPIDA' && !empty($nota['cotacao_rapida_id'])) {
                // Se for de COTAÇÃO RÁPIDA, redireciona para a nova rota
                $redirectUrl = "/relatorios/nota-tecnica-rapida?id={$nota_id}";
                error_log("DEBUG: Redirecionando para COTACAO_RAPIDA: {$redirectUrl}");
            } else {
                error_log("DEBUG: Nenhuma condição atendida. tipo={$nota['tipo']}, tipoNormalizado={$tipoNormalizado}, cotacao_rapida_id={$nota['cotacao_rapida_id']}");
            }

            Router::redirect($redirectUrl);

        } catch (\Exception $e) {
            error_log("Erro ao visualizar relatório: " . $e->getMessage());
            if (function_exists('logarEvento')) {
                logarEvento('error', 'Erro ao visualizar relatório: ' . $e->getMessage(), ['nota_id' => $params['nota_id'] ?? 0]);
            }
            $_SESSION['flash_error'] = 'Ocorreu um erro ao tentar visualizar o relatório.';
            Router::redirect('/relatorios');
        }
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

    public function gerarRelatorioCotacaoRapida($params = [])
    {
        try {
            $queryParams = Router::getQueryData();
            $nota_id = $queryParams['id'] ?? 0;
    
            if (!$nota_id) {
                throw new \Exception("ID da nota técnica não fornecido.");
            }
    
            $pdo = \getDbConnection();
    
            // 1. Buscar dados da Nota Técnica e da Cotação Rápida
            $stmtNota = $pdo->prepare("
                SELECT 
                    nt.id, nt.numero_nota, nt.ano_nota, nt.gerada_por, nt.gerada_em,
                    cr.titulo as cotacao_titulo, cr.criada_em as cotacao_criada_em
                FROM notas_tecnicas nt
                JOIN cotacoes_rapidas cr ON nt.cotacao_rapida_id = cr.id
                WHERE nt.id = ? AND nt.tipo = 'COTACAO_RAPIDA'
            ");
            $stmtNota->execute([$nota_id]);
            $nota = $stmtNota->fetch(\PDO::FETCH_ASSOC);
    
            if (!$nota) {
                throw new \Exception("Nota técnica de cotação rápida não encontrada.");
            }
    
            // 2. Buscar Itens e Preços associados
            $stmtItens = $pdo->prepare("
                SELECT 
                    cri.id, cri.catmat_catser, cri.descricao_pesquisa, cri.quantidade, cri.estatisticas_json
                FROM cotacoes_rapidas_itens cri
                WHERE cri.cotacao_rapida_id = (SELECT cotacao_rapida_id FROM notas_tecnicas WHERE id = ?)
            ");
            $stmtItens->execute([$nota_id]);
            $itens = $stmtItens->fetchAll(\PDO::FETCH_ASSOC);
    
            foreach ($itens as &$item) {
                $stmtPrecos = $pdo->prepare("
                    SELECT * FROM cotacoes_rapidas_precos
                    WHERE cotacao_rapida_item_id = ? AND considerado = 1
                    ORDER BY preco_unitario ASC
                ");
                $stmtPrecos->execute([$item['id']]);
                $item['precos'] = $stmtPrecos->fetchAll(\PDO::FETCH_ASSOC);
            }
    
            // 3. Montar a estrutura do relatório (similar ao gerarRelatorio)
            $configsEmpresa = ConfiguracaoController::getConfiguracoesPorCategoria('empresa');
            $configsInterface = ConfiguracaoController::getConfiguracoesPorCategoria('interface');
    
            $dadosRelatorio = [
                'title' => "Nota Técnica de Pesquisa de Preços Nº {$nota['numero_nota']}/{$nota['ano_nota']}",
                'subtitle' => "Referente a: {$nota['cotacao_titulo']}",
                'header' => true,
                'sections' => [],
                'empresa' => [
                    'nome' => $configsEmpresa['empresa_nome'] ?? 'Empresa',
                    'logo_path' => $configsInterface['interface_logo_path'] ?? null
                ]
            ];
    
            // Seção 1: Objeto
            $dadosRelatorio['sections'][] = [
                'title' => '1. OBJETO',
                'content' => "<p>A presente nota técnica visa registrar a pesquisa de preços para a aquisição de bens/serviços, conforme descrito no título: <strong>{$nota['cotacao_titulo']}</strong>.</p>"
            ];
    
            // Seção 2: Metodologia
            $dadosRelatorio['sections'][] = [
                'title' => '2. METODOLOGIA',
                'content' => "<p>A pesquisa de preços foi realizada utilizando os parâmetros da Instrução Normativa SEGES/ME Nº 65, de 7 de julho de 2021, com foco nos incisos I e II do art. 5º, que correspondem a preços do Painel de Preços e contratações similares de outros entes públicos.</p>"
            ];
    
            // Seção 3: Detalhamento por Item
            foreach ($itens as $index => $item) {
                $conteudoItem = "
                    <h4>Item " . ($index + 1) . ": {$item['descricao_pesquisa']}</h4>
                    <p><strong>Código CATMAT/CATSER:</strong> {$item['catmat_catser']}</p>
                    <p><strong>Quantidade:</strong> {$item['quantidade']}</p>
                ";
    
                if (!empty($item['precos'])) {
                    $tabelaPrecos = [
                        'headers' => ['Fonte', 'Origem (UASG/Fornecedor)', 'Data', 'Valor Unitário'],
                        'data' => []
                    ];
                    foreach ($item['precos'] as $preco) {
                        $tabelaPrecos['data'][] = [
                            $preco['fonte_pesquisa'],
                            $preco['fornecedor_nome'],
                            formatarData($preco['data_resultado']),
                            formatarMoeda($preco['preco_unitario'])
                        ];
                    }
                    $conteudoItem .= self::generateTableHtml($tabelaPrecos);
    
                    // Análise de preços
                    $estatisticas = json_decode($item['estatisticas_json'], true);
                    if ($estatisticas && $estatisticas['total'] > 0) {
                        $conteudoItem .= "
                            <div class='info'>
                                <h5>Análise Estatística:</h5>
                                <p>Menor preço: " . formatarMoeda($estatisticas['minimo']) . "</p>
                                <p>Maior preço: " . formatarMoeda($estatisticas['maximo']) . "</p>
                                <p>Preço médio: " . formatarMoeda($estatisticas['media']) . "</p>
                                <p>Mediana: " . formatarMoeda($estatisticas['mediana']) . "</p>
                            </div>
                        ";
                    }
                } else {
                    $conteudoItem .= "<p><em>Nenhuma cotação considerada para este item.</em></p>";
                }
    
                $dadosRelatorio['sections'][] = [
                    'title' => '',
                    'content' => $conteudoItem
                ];
            }
    
            // Seção 4: Conclusão
            $dadosRelatorio['sections'][] = [
                'title' => '3. CONCLUSÃO',
                'content' => "<p>Com base nos dados coletados, os preços de referência para os itens são considerados adequados e conformes com os valores de mercado, servindo como base para a estimativa de valor da futura contratação.</p>"
            ];
    
            // Rodapé
            $dataGeracao = !empty($nota['gerada_em']) ? formatarDataHora(strtotime($nota['gerada_em'])) : 'Data não registrada';
            $dadosRelatorio['footer'] = "
                <div class='signature-block'>
                    <div class='signature-line'>
                        {$nota['gerada_por']}<br>
                        Agente Responsável pela Pesquisa
                    </div>
                </div>
                <p class='small text-center'>
                    Documento gerado automaticamente pelo Sistema Algorise em {$dataGeracao}
                </p>
            ";
    
            // 4. Gerar e exibir o PDF
            $pdf = Pdf::createReport($dadosRelatorio);
            $htmlFinal = $pdf->render();
    
            header('Content-Type: text/html; charset=UTF-8');
            echo $htmlFinal;
    
        } catch (\Exception $e) {
            // error_log("Erro ao gerar relatório de cotação rápida: " . $e->getMessage());
            // $_SESSION['flash_error'] = 'Ocorreu um erro ao gerar o relatório: ' . $e->getMessage();
            
            // COMENTE O REDIRECIONAMENTO:
            // Router::redirect('/dashboard'); 

            // E AGORA, IMPRIMA O ERRO DIRETAMENTE NA TELA:
            echo "<h1>Erro Fatal ao Renderizar Relatório</h1>";
            echo "<p>A página não pôde ser exibida pelo seguinte motivo:</p>";
            echo "<pre style='background: #f0f0f0; border: 1px solid #ccc; padding: 10px; border-radius: 5px;'>";
            echo "<strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "\n\n";
            echo "<strong>Arquivo:</strong> " . $e->getFile() . "\n";
            echo "<strong>Linha:</strong> " . $e->getLine() . "\n\n";
            echo "<strong>Stack Trace:</strong>\n" . htmlspecialchars($e->getTraceAsString());
            echo "</pre>";
            exit; // Para a execução aqui para que o erro seja visível
        }
    }
}