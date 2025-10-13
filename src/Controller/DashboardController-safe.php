<?php
namespace Joabe\Buscaprecos\Controller;

use Joabe\Buscaprecos\Core\Router;

class DashboardController
{
    public function exibir($params = [])
    {
        try {
            $pdo = \getDbConnection();

            // Dados básicos que sempre funcionam
            $dadosStatus = [];
            $dadosTipo = [];
            $dadosAgentes = [];
            $dadosValorPorMes = [];
            $dadosRespostasFornecedores = [];
            $dadosProcessosPorRegiao = [];

            // Verificar se a tabela processos existe
            $tabelasExistentes = [];
            try {
                $stmt = $pdo->query("SHOW TABLES");
                $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $tabelasExistentes = $tabelas;
            } catch (Exception $e) {
                error_log("Erro ao listar tabelas: " . $e->getMessage());
            }

            // Só executa consultas se as tabelas existirem
            if (in_array('processos', $tabelasExistentes)) {
                try {
                    $dadosStatus = $pdo->query("SELECT status, COUNT(*) as total FROM processos GROUP BY status")->fetchAll();
                } catch (Exception $e) {
                    error_log("Erro na consulta de status: " . $e->getMessage());
                }

                try {
                    $dadosTipo = $pdo->query("SELECT tipo_contratacao, COUNT(*) as total FROM processos GROUP BY tipo_contratacao")->fetchAll();
                } catch (Exception $e) {
                    error_log("Erro na consulta de tipo: " . $e->getMessage());
                }

                try {
                    $dadosAgentes = $pdo->query("SELECT agente_responsavel, COUNT(*) as total FROM processos WHERE agente_responsavel IS NOT NULL AND agente_responsavel != '' GROUP BY agente_responsavel ORDER BY total DESC LIMIT 5")->fetchAll();
                } catch (Exception $e) {
                    error_log("Erro na consulta de agentes: " . $e->getMessage());
                }

                if (in_array('itens', $tabelasExistentes)) {
                    try {
                        $dadosValorPorMes = $pdo->query(
                            "SELECT DATE_FORMAT(data_criacao, '%Y-%m') as mes, SUM(valor_total_estimado) as valor_total
                             FROM (
                                 SELECT p.data_criacao, SUM(COALESCE(i.valor_estimado, 0) * COALESCE(i.quantidade, 1)) as valor_total_estimado
                                 FROM processos p
                                 LEFT JOIN itens i ON p.id = i.processo_id
                                 WHERE p.status = 'Finalizado'
                                 GROUP BY p.id, p.data_criacao
                             ) as subquery
                             WHERE valor_total_estimado > 0
                             GROUP BY mes
                             ORDER BY mes ASC
                             LIMIT 12"
                        )->fetchAll();
                    } catch (Exception $e) {
                        error_log("Erro na consulta de valor por mês: " . $e->getMessage());
                    }
                }

                if (in_array('lotes_solicitacao_fornecedores', $tabelasExistentes) && in_array('lotes_solicitacao', $tabelasExistentes)) {
                    try {
                        $dadosRespostasFornecedores = $pdo->query(
                            "SELECT 
                                CASE 
                                    WHEN lsf.status = 'Respondido' THEN 'Respondido'
                                    WHEN lsf.status = 'Enviado' AND ls.prazo_final >= CURDATE() THEN 'Aguardando'
                                    ELSE 'Prazo Expirado'
                                END as status_calculado,
                                COUNT(*) as total
                            FROM lotes_solicitacao_fornecedores lsf
                            JOIN lotes_solicitacao ls ON lsf.lote_solicitacao_id = ls.id
                            GROUP BY status_calculado"
                        )->fetchAll();
                    } catch (Exception $e) {
                        error_log("Erro na consulta de respostas: " . $e->getMessage());
                    }
                }

                try {
                    $dadosProcessosPorRegiao = $pdo->query(
                        "SELECT COALESCE(regiao, 'Não informado') as regiao, COUNT(*) as total 
                        FROM processos 
                        GROUP BY regiao 
                        ORDER BY total DESC"
                    )->fetchAll();
                } catch (Exception $e) {
                    error_log("Erro na consulta de região: " . $e->getMessage());
                }
            }

            // KPIs básicos
            $totalProcessos = 0;
            $totalEmAndamento = 0;
            $totalFinalizados = 0;

            if (in_array('processos', $tabelasExistentes)) {
                try {
                    $totalProcessos = $pdo->query("SELECT COUNT(*) FROM processos")->fetchColumn();
                    $totalEmAndamento = $pdo->query("SELECT COUNT(*) FROM processos WHERE status = 'Pesquisa em Andamento'")->fetchColumn();
                    $totalFinalizados = $pdo->query("SELECT COUNT(*) FROM processos WHERE status = 'Finalizado'")->fetchColumn();
                } catch (Exception $e) {
                    error_log("Erro nas consultas de KPI: " . $e->getMessage());
                }
            }

            $tituloPagina = "Dashboard";
            $paginaConteudo = __DIR__ . '/../View/dashboard.php';

            ob_start();
            require __DIR__ . '/../View/layout/main.php';
            $view = ob_get_clean();
            
            echo $view;
            
        } catch (Exception $e) {
            error_log("Erro geral no dashboard: " . $e->getMessage());
            
            // Em caso de erro, exibir página de erro simples
            echo "<!DOCTYPE html>
            <html>
            <head>
                <title>Dashboard - Erro</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; }
                </style>
            </head>
            <body>
                <h1>Dashboard</h1>
                <div class='error'>
                    <h3>Erro interno do servidor</h3>
                    <p>Ocorreu um erro ao carregar o dashboard. Os logs contêm mais informações.</p>
                    <p>Por favor, verifique se o banco de dados está configurado corretamente.</p>
                    <p><a href='/debug-dashboard.php'>Debug do Dashboard</a> | <a href='/verificar-usuarios.php'>Verificar Usuários</a></p>
                </div>
            </body>
            </html>";
        }
    }
}
?>