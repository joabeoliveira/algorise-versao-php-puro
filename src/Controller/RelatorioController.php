<?php

namespace Joabe\Buscaprecos\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;

class RelatorioController
{
    public function gerarRelatorio($request, $response, $args)
    {
        $processo_id = $args['id'];
        $params = $request->getQueryParams();
        $nota_existente_id = $params['nota_id'] ?? null; // Pega o ID da nota da URL, se existir

        $pdo = \getDbConnection();

        // =======================================================
        //     INÍCIO DA CORREÇÃO: LÓGICA CONDICIONAL DE NUMERAÇÃO
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
        
        // Se não encontrou uma nota existente (ou se não foi passado um id), gera uma nova
        if (!$isRegeneration) {
            // MODO CRIAÇÃO: Gera um novo número sequencial
            $ano_nota = date('Y');
            $stmtNum = $pdo->prepare("SELECT MAX(numero_nota) FROM notas_tecnicas WHERE ano_nota = ?");
            $stmtNum->execute([$ano_nota]);
            $ultimoNumero = $stmtNum->fetchColumn();
            $numero_nota = $ultimoNumero ? $ultimoNumero + 1 : 1;
        }
        // =======================================================
        //                      FIM DA CORREÇÃO
        // =======================================================

        // O resto da busca de dados permanece igual
        $stmtProcesso = $pdo->prepare("SELECT * FROM processos WHERE id = ?");
        $stmtProcesso->execute([$processo_id]);
        $dadosProcesso = $stmtProcesso->fetch(\PDO::FETCH_ASSOC);

        // ... (toda a lógica de busca de itens, preços, fontes, etc., permanece a mesma)
        $sqlItens = "SELECT i.*, pc.id as preco_id, pc.fonte, pc.valor, pc.data_coleta, pc.fornecedor_nome, pc.status_analise, pc.justificativa_descarte FROM itens i LEFT JOIN precos_coletados pc ON i.id = pc.item_id WHERE i.processo_id = ? ORDER BY i.numero_item ASC, pc.valor ASC";
        $stmtItens = $pdo->prepare($sqlItens);
        $stmtItens->execute([$processo_id]);
        $resultados = $stmtItens->fetchAll(\PDO::FETCH_ASSOC);
        $dadosItens = []; $precosDesconsiderados = [];
        foreach ($resultados as $linha) {
            $itemId = $linha['id'];
            if (!isset($dadosItens[$itemId])) { $dadosItens[$itemId] = $linha; $dadosItens[$itemId]['precos'] = []; }
            if ($linha['preco_id']) { $dadosItens[$itemId]['precos'][] = $linha; if ($linha['status_analise'] === 'desconsiderado') { $precosDesconsiderados[] = $linha; } }
        }
        $sqlSolicitacoes = "SELECT f.razao_social, lsf.status, ls.justificativa_fornecedores FROM lotes_solicitacao ls JOIN lotes_solicitacao_fornecedores lsf ON ls.id = lsf.lote_solicitacao_id JOIN fornecedores f ON lsf.fornecedor_id = f.id WHERE ls.processo_id = ?";
        $stmtSolicitacoes = $pdo->prepare($sqlSolicitacoes);
        $stmtSolicitacoes->execute([$processo_id]);
        $dadosSolicitacoes = $stmtSolicitacoes->fetchAll(\PDO::FETCH_ASSOC);
        $fontesUtilizadasBruto = [];
        foreach ($dadosItens as $item) { foreach ($item['precos'] as $preco) { $fontesUtilizadasBruto[] = $preco['fonte']; } }
        $mapaFontes = ['Painel de Preços' => 'I', 'Contratação Similar' => 'II', 'Site Especializado' => 'III', 'Pesquisa com Fornecedor' => 'IV', 'Nota Fiscal' => 'V'];
        $incisosUtilizados = [];
        foreach (array_unique($fontesUtilizadasBruto) as $fonte) { if (isset($mapaFontes[$fonte])) { $incisosUtilizados[] = $mapaFontes[$fonte]; } }
        sort($incisosUtilizados);
        $priorizouOficiais = in_array('I', $incisosUtilizados) || in_array('II', $incisosUtilizados);
        $dadosFontes = ['fontes_utilizadas' => $incisosUtilizados, 'priorizou_oficiais' => $priorizouOficiais];

        // GERA O PDF
        ob_start();
        // Passa o número e ano corretos para a view
        $novoNumero = $numero_nota; 
        $anoAtual = $ano_nota;
        require __DIR__ . '/../View/relatorios/nota_tecnica.php';
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // --- SALVA O REGISTRO APENAS SE FOR UMA NOTA TÉCNICA NOVA ---
        if (!$isRegeneration) {
            $stmtInsert = $pdo->prepare("INSERT INTO notas_tecnicas (numero_nota, ano_nota, processo_id, gerada_por) VALUES (?, ?, ?, ?)");
            $stmtInsert->execute([$novoNumero, $anoAtual, $processo_id, $dadosProcesso['agente_responsavel']]);
        }

        $dompdf->stream("Nota_Tecnica_{$novoNumero}_{$anoAtual}.pdf", ["Attachment" => false]);
        return $response;
    }


    // Listar Relatórios Gerados
    // ===============================================
    public function listar($request, $response, $args)
    {
        $pdo = \getDbConnection();
        // Query atualizada para buscar dados de ambos os tipos de relatório
        $stmt = $pdo->query(
            "SELECT 
                nt.id, nt.numero_nota, nt.ano_nota, nt.tipo, nt.gerada_em, nt.gerada_por,
                p.numero_processo, p.nome_processo,
                cr.titulo as titulo_cotacao
            FROM notas_tecnicas nt
            LEFT JOIN processos p ON nt.processo_id = p.id
            LEFT JOIN cotacoes_rapidas cr ON nt.cotacao_rapida_id = cr.id
            ORDER BY nt.ano_nota DESC, nt.numero_nota DESC"
        );
        $notas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $tituloPagina = "Histórico de Relatórios Gerados";
        $paginaConteudo = __DIR__ . '/../View/relatorios/lista.php';
        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();
        $response->getBody()->write($view);
        return $response;
    }
    // Exibir Formulário de Pesquisa



    public function visualizar($request, $response, $args)
{
    $nota_id = $args['nota_id'];
    $pdo = \getDbConnection();
    $stmtNota = $pdo->prepare("SELECT * FROM notas_tecnicas WHERE id = ?");
    $stmtNota->execute([$nota_id]);
    $dadosNota = $stmtNota->fetch(\PDO::FETCH_ASSOC);

    if (!$dadosNota) {
        $response->getBody()->write("Relatório não encontrado.");
        return $response->withStatus(404);
    }
    
    $html = '';
    
    // Se o relatório for de COTAÇÃO RÁPIDA
    if ($dadosNota['tipo'] === 'COTACAO_RAPIDA') {
        $cotacao_rapida_id = $dadosNota['cotacao_rapida_id'];
        
        $stmtCotacao = $pdo->prepare("SELECT * FROM cotacoes_rapidas WHERE id = ?");
        $stmtCotacao->execute([$cotacao_rapida_id]);
        $dadosCotacao = $stmtCotacao->fetch(\PDO::FETCH_ASSOC);

        $stmtItens = $pdo->prepare("SELECT * FROM cotacoes_rapidas_itens WHERE cotacao_rapida_id = ? ORDER BY id ASC");
        $stmtItens->execute([$cotacao_rapida_id]);
        $dadosItens = $stmtItens->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($dadosItens as $key => $item) {
            $stmtPrecos = $pdo->prepare("SELECT * FROM cotacoes_rapidas_precos WHERE cotacao_rapida_item_id = ? ORDER BY preco_unitario ASC");
            $stmtPrecos->execute([$item['id']]);
            $dadosItens[$key]['precos'] = $stmtPrecos->fetchAll(\PDO::FETCH_ASSOC);
        }

        ob_start();
        // Renderiza o template específico para cotação rápida
        require __DIR__ . '/../View/relatorios/nota_tecnica_rapida.php';
        $html = ob_get_clean();

    } else { // --- INÍCIO DA CORREÇÃO: Lógica para relatório de PROCESSO COMPLETO ---
        
        $processo_id = $dadosNota['processo_id'];

        // 1. Busca os dados gerais do processo
        $stmtProcesso = $pdo->prepare("SELECT * FROM processos WHERE id = ?");
        $stmtProcesso->execute([$processo_id]);
        $dadosProcesso = $stmtProcesso->fetch(\PDO::FETCH_ASSOC);

        // 2. Busca os itens e todos os seus preços coletados
        $sqlItens = "SELECT i.*, pc.id as preco_id, pc.fonte, pc.valor, pc.data_coleta, pc.fornecedor_nome, pc.status_analise, pc.justificativa_descarte FROM itens i LEFT JOIN precos_coletados pc ON i.id = pc.item_id WHERE i.processo_id = ? ORDER BY i.numero_item ASC, pc.valor ASC";
        $stmtItens = $pdo->prepare($sqlItens);
        $stmtItens->execute([$processo_id]);
        $resultados = $stmtItens->fetchAll(\PDO::FETCH_ASSOC);

        // Agrupa os preços por item
        $dadosItens = [];
        $precosDesconsiderados = [];
        foreach ($resultados as $linha) {
            $itemId = $linha['id'];
            if (!isset($dadosItens[$itemId])) {
                $dadosItens[$itemId] = $linha;
                $dadosItens[$itemId]['precos'] = [];
            }
            if ($linha['preco_id']) {
                $dadosItens[$itemId]['precos'][] = $linha;
                if ($linha['status_analise'] === 'desconsiderado') {
                    $precosDesconsiderados[] = $linha;
                }
            }
        }

        // 3. Busca informações de solicitações enviadas a fornecedores
        $sqlSolicitacoes = "SELECT f.razao_social, lsf.status, ls.justificativa_fornecedores FROM lotes_solicitacao ls JOIN lotes_solicitacao_fornecedores lsf ON ls.id = lsf.lote_solicitacao_id JOIN fornecedores f ON lsf.fornecedor_id = f.id WHERE ls.processo_id = ?";
        $stmtSolicitacoes = $pdo->prepare($sqlSolicitacoes);
        $stmtSolicitacoes->execute([$processo_id]);
        $dadosSolicitacoes = $stmtSolicitacoes->fetchAll(\PDO::FETCH_ASSOC);

        // 4. Determina as fontes e incisos utilizados
        $fontesUtilizadasBruto = [];
        foreach ($dadosItens as $item) {
            foreach ($item['precos'] as $preco) {
                $fontesUtilizadasBruto[] = $preco['fonte'];
            }
        }
        $mapaFontes = ['Painel de Preços' => 'I', 'Contratação Similar' => 'II', 'Site Especializado' => 'III', 'Pesquisa com Fornecedor' => 'IV', 'Nota Fiscal' => 'V'];
        $incisosUtilizados = [];
        foreach (array_unique($fontesUtilizadasBruto) as $fonte) {
            if (isset($mapaFontes[$fonte])) {
                $incisosUtilizados[] = $mapaFontes[$fonte];
            }
        }
        sort($incisosUtilizados);
        $priorizouOficiais = in_array('I', $incisosUtilizados) || in_array('II', $incisosUtilizados);
        $dadosFontes = ['fontes_utilizadas' => $incisosUtilizados, 'priorizou_oficiais' => $priorizouOficiais];

        // 5. Prepara as variáveis para o template da nota técnica
        $novoNumero = $dadosNota['numero_nota'];
        $anoAtual = $dadosNota['ano_nota'];

        ob_start();
        // Renderiza o template principal da nota técnica do processo
        require __DIR__ . '/../View/relatorios/nota_tecnica.php';
        $html = ob_get_clean();

    } // --- FIM DA CORREÇÃO ---

    // Gera o PDF com o HTML renderizado
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Exibe o PDF no navegador
    $dompdf->stream("Relatorio_{$dadosNota['numero_nota']}_{$dadosNota['ano_nota']}.pdf", ["Attachment" => false]);
    return $response;
}


}