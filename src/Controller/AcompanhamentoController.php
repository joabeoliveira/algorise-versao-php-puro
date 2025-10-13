<?php

namespace Joabe\Buscaprecos\Controller;

use Joabe\Buscaprecos\Core\Router;

class AcompanhamentoController
{
    public function exibir($params = [])
    {
        try {
            $pdo = \getDbConnection();
            
            // Verificar se as tabelas necessárias existem
            $solicitacoes = [];
            
            try {
                // Verificar se a tabela solicitacoes_cotacao existe (estrutura mais simples)
                $stmt = $pdo->query("SHOW TABLES LIKE 'solicitacoes_cotacao'");
                if ($stmt->rowCount() > 0) {
                    $sql = "SELECT 
                                sc.id,
                                sc.processo_id,
                                sc.fornecedor_id,
                                sc.status,
                                sc.data_envio,
                                sc.prazo_resposta,
                                sc.data_resposta,
                                p.nome as nome_processo,
                                f.razao_social
                            FROM solicitacoes_cotacao sc
                            LEFT JOIN processos p ON sc.processo_id = p.id
                            LEFT JOIN fornecedores f ON sc.fornecedor_id = f.id
                            ORDER BY sc.data_envio DESC";
                    
                    $stmt = $pdo->query($sql);
                    $solicitacoes = $stmt->fetchAll();
                } else {
                    // Fallback: usar dados de cotações rápidas se disponível
                    $stmt = $pdo->query("SHOW TABLES LIKE 'cotacoes_rapidas'");
                    if ($stmt->rowCount() > 0) {
                        $sql = "SELECT 
                                    cr.id,
                                    cr.numero_processo as nome_processo,
                                    cr.orgao as razao_social,
                                    'Cotação Rápida' as status,
                                    cr.data_criacao as data_envio,
                                    NULL as prazo_resposta,
                                    cr.data_atualizacao as data_resposta
                                FROM cotacoes_rapidas cr
                                ORDER BY cr.data_criacao DESC
                                LIMIT 50";
                        
                        $stmt = $pdo->query($sql);
                        $solicitacoes = $stmt->fetchAll();
                    }
                }
            } catch (\Exception $e) {
                error_log("Erro na consulta de acompanhamento: " . $e->getMessage());
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