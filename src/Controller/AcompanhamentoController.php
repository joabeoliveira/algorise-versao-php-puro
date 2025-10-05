<?php

use Joabe\Buscaprecos\Core\Router;

namespace Joabe\Buscaprecos\Controller;

class AcompanhamentoController
{
    public function exibir($params = [])
    {
        $pdo = \getDbConnection();
        
        $sql = "SELECT 
                    p.nome_processo,
                    f.razao_social,
                    lsf.status,
                    ls.prazo_final,
                    lsf.data_envio,
                    lsf.data_resposta,
                    lsf.caminho_anexo,
                    lsf.nome_original_anexo
                FROM lotes_solicitacao_fornecedores lsf
                JOIN lotes_solicitacao ls ON lsf.lote_solicitacao_id = ls.id
                JOIN processos p ON ls.processo_id = p.id
                JOIN fornecedores f ON lsf.fornecedor_id = f.id
                ORDER BY lsf.data_envio DESC";
        
        $stmt = $pdo->query($sql);
        $solicitacoes = $stmt->fetchAll();

        $tituloPagina = "Acompanhamento de Solicitações";
        $paginaConteudo = __DIR__ . '/../View/acompanhamento/lista.php'; // Aponta para a nova view

        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();

        echo $view;
    }
}