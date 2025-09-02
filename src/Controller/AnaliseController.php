<?php

namespace Joabe\Buscaprecos\Controller;

class AnaliseController
{
    /**
     * Exibe a "Mesa de Análise" para todos os itens de um processo.
     */
    public function exibirAnaliseProcesso($request, $response, $args)
    {
        $processo_id = $args['processo_id'];
        $pdo = \getDbConnection();

        // 1. Busca os dados do processo
        $stmtProcesso = $pdo->prepare("SELECT * FROM processos WHERE id = ?");
        $stmtProcesso->execute([$processo_id]);
        $processo = $stmtProcesso->fetch();

        if (!$processo) {
            $response->getBody()->write("Processo não encontrado.");
            return $response->withStatus(404);
        }

        // 2. ÚNICA CONSULTA OTIMIZADA: Busca todos os itens e seus preços de uma vez
        $sql = "SELECT 
                    i.id as item_id, i.numero_item, i.descricao, i.unidade_medida, i.catmat_catser, i.quantidade,
                    i.valor_estimado, i.metodologia_estimativa, i.justificativa_estimativa, i.justificativa_excepcionalidade,
                    i.status_analise, /* <--- GARANTE QUE ESTE CAMPO ESTÁ SENDO BUSCADO */
                    pc.id as preco_id, pc.fonte, pc.valor, pc.data_coleta, pc.fornecedor_nome, 
                    pc.status_analise, pc.justificativa_descarte
                FROM itens i
                LEFT JOIN precos_coletados pc ON i.id = pc.item_id
                WHERE i.processo_id = ?
                ORDER BY i.numero_item ASC, pc.valor ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$processo_id]);
        $resultados = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // 3. Processa os resultados para agrupar os preços por item
        $itensAgrupados = [];
        foreach ($resultados as $linha) {
            $itemId = $linha['item_id'];
            if (!isset($itensAgrupados[$itemId])) {
                $itensAgrupados[$itemId] = [
                    'item' => [
                        'id' => $itemId,
                        'numero_item' => $linha['numero_item'],
                        'descricao' => $linha['descricao'],
                        'unidade_medida' => $linha['unidade_medida'],
                        'catmat_catser' => $linha['catmat_catser'],
                        'quantidade' => $linha['quantidade'],
                        'valor_estimado' => $linha['valor_estimado'],
                        'metodologia_estimativa' => $linha['metodologia_estimativa'],
                        'justificativa_estimativa' => $linha['justificativa_estimativa'],
                        'justificativa_excepcionalidade' => $linha['justificativa_excepcionalidade'],
                        'status_analise' => $linha['status_analise'] /* <--- GARANTE QUE ESTE CAMPO ESTÁ SENDO PASSADO */
                    ],
                    'precos' => []
                ];
            }
            if ($linha['preco_id'] !== null) {
                $itensAgrupados[$itemId]['precos'][] = [
                    'id' => $linha['preco_id'],
                    'fonte' => $linha['fonte'],
                    'valor' => $linha['valor'],
                    'unidade_medida' => $linha['unidade_medida'],
                    'data_coleta' => $linha['data_coleta'],
                    'fornecedor_nome' => $linha['fornecedor_nome'],
                    'status_analise' => $linha['status_analise'],
                    'justificativa_descarte' => $linha['justificativa_descarte']
                ];
            }
        }

        // 4. Calcula as estatísticas e a flag de alerta para cada item
        $itensComAnalise = [];
        foreach ($itensAgrupados as $itemAgrupado) {
            $precosConsiderados = array_filter($itemAgrupado['precos'], fn($p) => $p['status_analise'] === 'considerado');
            $estatisticas = $this->calcularEstatisticas($precosConsiderados);
            
            // =======================================================
            //     INÍCIO DA CORREÇÃO: LÓGICA DO ALERTA ADICIONADA
            // =======================================================
            $itemAgrupado['alerta_amostra'] = ($estatisticas['total'] > 0 && $estatisticas['total'] < 3);
            // =======================================================
            //                      FIM DA CORREÇÃO
            // =======================================================

            $itemAgrupado['estatisticas'] = $estatisticas;
            $itensComAnalise[] = $itemAgrupado;
        }

        // 5. Renderiza a view com os dados processados
        $tituloPagina = "Mesa de Análise Geral do Processo";
        $paginaConteudo = __DIR__ . '/../View/analise/processo.php';
        
        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();

        $response->getBody()->write($view);
        return $response;
    }

    /**
     * Função auxiliar para calcular as estatísticas de um conjunto de preços.
     */
    private function calcularEstatisticas(array $precos): array
    {
        $estatisticas = ['total' => 0, 'minimo' => 0, 'maximo' => 0, 'media' => 0, 'mediana' => 0];
        if (empty($precos)) {
            return $estatisticas;
        }

        $valores = array_column($precos, 'valor');
        sort($valores);
        $count = count($valores);
        
        $estatisticas['total'] = $count;
        $estatisticas['minimo'] = $valores[0];
        $estatisticas['maximo'] = $valores[$count - 1];
        $estatisticas['media'] = array_sum($valores) / $count;
        
        $meio = floor(($count - 1) / 2);
        if ($count % 2) {
            $estatisticas['mediana'] = $valores[$meio];
        } else {
            $estatisticas['mediana'] = ($valores[$meio] + $valores[$meio + 1]) / 2.0;
        }

        return $estatisticas;
    }


// Em src/Controller/AnaliseController.php

public function salvarAnaliseItem($request, $response, $args)
{
    // Adiciona este cabeçalho para garantir que a resposta seja sempre JSON
    $response = $response->withHeader('Content-Type', 'application/json');
    $pdo = \getDbConnection();

    try {
        $item_id = $args['item_id'];
        $dados = $request->getParsedBody();

        if (!isset($dados['metodologia_estimativa']) || !isset($dados['justificativa_estimativa'])) {
            return $response->withJson([
                'status' => 'error',
                'message' => 'Dados do formulário incompletos. Faltando metodologia ou justificativa.'
            ], 400);
        }

        $metodologia = $dados['metodologia_estimativa'];
        $justificativa = $dados['justificativa_estimativa'];
        $justificativaExcepcionalidade = $dados['justificativa_excepcionalidade'] ?? null;
        $valorEstimado = 0;

        if ($metodologia === 'Manual') {
            $valorEstimado = (float)($dados['valor_manual'] ?? 0);
        } else {
            $stmtPrecos = $pdo->prepare("SELECT valor FROM precos_coletados WHERE item_id = ? AND status_analise = 'considerado'");
            $stmtPrecos->execute([$item_id]);
            $precosConsiderados = $stmtPrecos->fetchAll(\PDO::FETCH_ASSOC);
            $estatisticas = $this->calcularEstatisticas($precosConsiderados);
            
            if ($estatisticas['total'] > 0) {
                switch ($metodologia) {
                    case 'Média':
                        $valorEstimado = $estatisticas['media'];
                        break;
                    case 'Mediana':
                        $valorEstimado = $estatisticas['mediana'];
                        break;
                    case 'Menor Valor':
                        $valorEstimado = $estatisticas['minimo'];
                        break;
                }
            }
        }
        
        $sql = "UPDATE itens SET 
                    valor_estimado = ?, 
                    metodologia_estimativa = ?, 
                    justificativa_estimativa = ?, 
                    justificativa_excepcionalidade = ?,
                    status_analise = 'analisado'
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            $valorEstimado, 
            $metodologia, 
            $justificativa, 
            $justificativaExcepcionalidade, 
            $item_id
        ]);

        return $response->withJson([
            'status' => 'success',
            'message' => 'Análise do item salva com sucesso!',
            'item_id' => $item_id,
            'new_status' => 'analisado'
        ]);

    } catch (\Throwable $e) {
        error_log("ERRO FATAL EM salvarAnaliseItem: " . $e->getMessage());
        return $response->withJson([
            'status' => 'error',
            'message' => 'ERRO INTERNO NO SERVIDOR: ' . $e->getMessage()
        ], 500);
    }
}

/**
     * Salva as justificativas gerais do processo, como a justificativa
     * pela não utilização de fontes prioritárias.
     */
    public function salvarJustificativasProcesso($request, $response, $args)
    {
        // 1. Pega o ID do processo vindo da URL
        $processo_id = $args['id'];
        
        // 2. Pega os dados que foram enviados pelo formulário
        $dados = $request->getParsedBody();
        $justificativaFontes = $dados['justificativa_fontes'] ?? null;

        // 3. Prepara a query SQL para atualizar a tabela 'processos'
        $pdo = \getDbConnection();
        $sql = "UPDATE processos SET justificativa_fontes = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        
        // 4. Executa a query, salvando a justificativa no banco
        $stmt->execute([$justificativaFontes, $processo_id]);

        // 5. Cria uma mensagem de sucesso para o usuário
        $_SESSION['flash'] = [
            'tipo' => 'success',
            'mensagem' => 'Justificativa do processo salva com sucesso!'
        ];

        // 6. Redireciona o usuário de volta para a página de análise
        $redirectUrl = "/processos/{$processo_id}/analise";
        return $response->withHeader('Location', $redirectUrl)->withStatus(302);
    }

}