<?php

namespace Joabe\Buscaprecos\Controller;

use Joabe\Buscaprecos\Core\Router;

class AcompanhamentoController
{
    public function exibir($params = [])
    {
        try {
            $pdo = \getDbConnection();
            
            $solicitacoes = [];
            
            try {
                $sql = "SELECT 
                            lsf.id,
                            ls.processo_id,
                            lsf.fornecedor_id,
                            lsf.status,
                            lsf.data_criacao as data_envio,
                            ls.prazo_final,
                            lsf.data_resposta,
                            p.nome_processo,
                            f.razao_social,
                            lsf.caminho_anexo,
                            lsf.nome_original_anexo
                        FROM lotes_solicitacao_fornecedores lsf
                        JOIN lotes_solicitacao ls ON lsf.lote_solicitacao_id = ls.id
                        JOIN processos p ON ls.processo_id = p.id
                        JOIN fornecedores f ON lsf.fornecedor_id = f.id
                        ORDER BY lsf.data_criacao DESC";
                
                $stmt = $pdo->query($sql);
                $solicitacoes = $stmt->fetchAll();

            } catch (\Exception $e) {
                error_log("Erro na consulta de acompanhamento: " . $e->getMessage());
                // Deixa $solicitacoes como um array vazio para não quebrar a view
                $solicitacoes = []; 
            }

            $tituloPagina = "Acompanhamento de Solicitações";
            $paginaConteudo = __DIR__ . '/../View/acompanhamento/lista.php';

            ob_start();
            require __DIR__ . '/../View/layout/main.php';
            $view = ob_get_clean();

            echo $view;
            
        } catch (\Exception $e) {
            error_log("Erro geral no acompanhamento: " . $e->getMessage());
            echo "<!DOCTYPE html>
            <html>
            <head>
                <title>Acompanhamento - Erro</title>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; background: #f8f9fa; }
                    .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; }
                    .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
                </style>
            </head>
            <body>
                <h1>Acompanhamento</h1>
                <div class='error'>
                    <h3>Erro interno</h3>
                    <p>Erro ao carregar dados de acompanhamento. Verifique se as tabelas necessárias estão criadas.</p>
                </div>
                <a href='/dashboard' class='btn'>Voltar ao Dashboard</a>
            </body>
            </html>";
        }
    }
}