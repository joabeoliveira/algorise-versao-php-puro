<?php

namespace Joabe\Buscaprecos\Controller;

use Joabe\Buscaprecos\Core\Router;

// 'USE' STATEMENTS PARA AS BIBLIOTECAS NECESSÁRIAS
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;


class CotacaoRapidaController
{
    /**
     * Exibe o formulário principal da Cotação Rápida.
     */
    public function exibirFormulario($params = [])
    {
        $tituloPagina = "Cotação Rápida";
        $paginaConteudo = __DIR__ . '/../View/cotacao_rapida/formulario.php';

        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();

        echo $view;
    }

    /**
     * Busca preços nas APIs do governo (Incisos I e II) e retorna os resultados em JSON.
     */
    public function buscarPrecos($params = [])
{
    $dados = \Joabe\Buscaprecos\Core\Router::getPostData();
    $catmats = $dados['catmat'] ?? [];
    $descricoes = $dados['descricao'] ?? [];
    $regioes = $dados['regiao'] ?? [];
    $quantidades = $dados['quantidade'] ?? [];

    if (empty($catmats) || empty($catmats[0])) {
        return $response->withJson(['erro' => 'Pelo menos um código CATMAT/CATSER é obrigatório.'], 400);
    }

    $resultadosPorItem = [];

    foreach ($catmats as $index => $catmat) {
        if (empty($catmat)) continue;

        $quantidadeItem = (int)($quantidades[$index] ?? 1);
        if ($quantidadeItem <= 0) $quantidadeItem = 1;
        
        $descricaoItem = $descricoes[$index] ?: "Item CATMAT " . $catmat;
        $regiaoItem = $regioes[$index] ?? null;

        // Busca nas fontes
        $resultadosPainel = $this->buscarApiComprasGov($catmat);
        foreach ($resultadosPainel as &$res) { $res['fonte_pesquisa'] = 'Painel de Preços (Inciso I)'; }
        
        $resultadosSimilares = $regiaoItem ? $this->buscarApiComprasGov($catmat, ['estado' => $regiaoItem]) : [];
        foreach ($resultadosSimilares as &$res) { $res['fonte_pesquisa'] = 'Contratação Similar (Inciso II)'; }

        $precosColetados = array_merge($resultadosPainel, $resultadosSimilares);

        $resultadosPorItem[$catmat] = [
            'descricao' => $descricaoItem,
            'quantidade' => $quantidadeItem,
            // A CORREÇÃO ESTÁ AQUI: Calcula estatísticas sobre o 'precoUnitario'
            'estatisticas' => $this->calcularEstatisticas($precosColetados, 'precoUnitario'),
            'precos' => $precosColetados
        ];
    }

    if (empty($resultadosPorItem)) {
        return $response->withJson(['mensagem' => 'Nenhum preço encontrado para os CATMATs informados.']);
    }
    
    return $response->withJson(['resultados_por_item' => $resultadosPorItem]);
}

    /**
     * Função auxiliar para chamar a API de Dados Abertos.
     */
    private function buscarApiComprasGov(string $catmat, array $filtros = []): array
    {
        $baseUrl = "https://dadosabertos.compras.gov.br/modulo-pesquisa-preco/1_consultarMaterial";
        $parametros = [
            'codigoItemCatalogo' => $catmat,
            'dataResultado' => 'true',
            'tamanhoPagina' => 10 // Busca um número maior de resultados
        ];

        if (!empty($filtros['estado'])) {
            $parametros['estado'] = $filtros['estado'];
        }

        $url = $baseUrl . '?' . http_build_query($parametros);

        $client = new \GuzzleHttp\Client(['verify' => false]);
        try {
            $apiResponse = $client->request('GET', $url);
            $dados = json_decode($apiResponse->getBody()->getContents(), true);
            return $dados['resultado'] ?? [];
        } catch (\Exception $e) {
            // Em caso de erro na API, retorna um array vazio para não quebrar a aplicação
            error_log("Erro na API de Cotação Rápida: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Função auxiliar para calcular estatísticas (copiada de AnaliseController).
     */
    private function calcularEstatisticas(array $dados, string $coluna): array
{
    $estatisticas = ['total' => 0, 'minimo' => 0, 'maximo' => 0, 'media' => 0, 'mediana' => 0];
    if (empty($dados)) {
        return $estatisticas;
    }

    $valores = array_column($dados, $coluna);
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


   public function salvarAnalise($params = [])
    {
        $dados = \Joabe\Buscaprecos\Core\Router::getPostData();
        $titulo = $dados['titulo'] ?? 'Cotação Rápida sem título';
        // --- INÍCIO DA CORREÇÃO ---
        $responsavel = $dados['responsavel'] ?? 'Usuário do Sistema'; // Pega o responsável do JS
        // --- FIM DA CORREÇÃO ---
        $itens = $dados['itens'] ?? [];

        if (empty($titulo) || empty($itens)) {
            return $response->withJson(['erro' => 'Dados insuficientes para salvar.'], 400);
        }

        $pdo = \getDbConnection();
        try {
            $pdo->beginTransaction();

            $stmtCotacao = $pdo->prepare("INSERT INTO cotacoes_rapidas (titulo, criada_por) VALUES (?, ?)");
            $stmtCotacao->execute([$titulo, $responsavel]); // Salva o nome correto
            $cotacaoRapidaId = $pdo->lastInsertId();

            $stmtItem = $pdo->prepare("INSERT INTO cotacoes_rapidas_itens (cotacao_rapida_id, catmat_catser, descricao_pesquisa, quantidade, estatisticas_json) VALUES (?, ?, ?, ?, ?)");

            $stmtPreco = $pdo->prepare("INSERT INTO cotacoes_rapidas_precos (cotacao_rapida_item_id, fonte_pesquisa, fornecedor_nome, data_resultado, preco_unitario, considerado, justificativa_descarte) VALUES (?, ?, ?, ?, ?, ?, ?)");

            foreach ($itens as $catmat => $itemData) {
                $estatisticasString = json_encode($itemData['estatisticas']);
                $stmtItem->execute([ $cotacaoRapidaId, $catmat, $itemData['descricao'], $itemData['quantidade'], $estatisticasString ]);
                $cotacaoRapidaItemId = $pdo->lastInsertId();

                if (!empty($itemData['precos'])) {
                    foreach ($itemData['precos'] as $preco) {
                        $dataResultadoFormatada = !empty($preco['dataResultado']) ? date('Y-m-d', strtotime($preco['dataResultado'])) : null;
                        $consideradoInt = ($preco['considerado'] === true) ? 1 : 0;
                        $stmtPreco->execute([
                            $cotacaoRapidaItemId, 
                            $preco['fonte_pesquisa'],
                            $preco['nomeUasg'] ?? 
                            ($preco['nomeFornecedor'] ?? 'N/A'),
                            $dataResultadoFormatada, 
                            $preco['precoUnitario'],
                            $consideradoInt, $preco['justificativa_descarte'] ?? null
                        ]);
                    }
                }
            }
            
            $anoAtual = date('Y');
            $stmtNum = $pdo->prepare("SELECT MAX(numero_nota) FROM notas_tecnicas WHERE ano_nota = ?");
            $stmtNum->execute([$anoAtual]);
            $novoNumero = ($stmtNum->fetchColumn() ?: 0) + 1;

            $stmtNota = $pdo->prepare("INSERT INTO notas_tecnicas (numero_nota, ano_nota, processo_id, cotacao_rapida_id, tipo, gerada_por) VALUES (?, ?, NULL, ?, 'COTACAO_RAPIDA', ?)");
            $stmtNota->execute([$novoNumero, $anoAtual, $cotacaoRapidaId, $responsavel]); // Salva o nome correto
            $notaId = $pdo->lastInsertId();

            $pdo->commit();
            
            return $response->withJson(['status' => 'sucesso', 'nota_tecnica_id' => $notaId]);

        } catch (\Exception $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            error_log("Erro ao salvar cotação rápida: " . $e->getMessage());
            return $response->withJson(['erro' => 'Erro Interno no Servidor: ' . $e->getMessage()], 500);
        }
    }


    public function gerarModeloPlanilha($params = [])
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Modelo de Itens');

        // --- INÍCIO DA ALTERAÇÃO ---
        $sheet->setCellValue('A1', 'CATMAT/CATSER (Obrigatório)');
        $sheet->setCellValue('B1', 'Descrição (Opcional)');
        $sheet->setCellValue('C1', 'Quantidade (Obrigatório)'); // Nova Coluna

        $sheet->setCellValueExplicit('A2', '461828', DataType::TYPE_STRING);
        $sheet->setCellValue('B2', 'Notebook para escritório');
        $sheet->setCellValue('C2', 10); // Quantidade de exemplo
        
        $headerStyle = ['font' => ['bold' => true]];
        $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        // --- FIM DA ALTERAÇÃO ---

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $fileContent = ob_get_clean();
        $response->getBody()->write($fileContent);

        return $response
            ->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->withHeader('Content-Disposition', 'attachment;filename="modelo_importacao_cotacao_rapida.xlsx"');
    }

}