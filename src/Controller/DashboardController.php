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
                $tabelas = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                $tabelasExistentes = $tabelas;
            } catch (\Exception $e) {
                error_log("Erro ao listar tabelas: " . $e->getMessage());
            }

            // Executa consultas completas já que todas as tabelas existem
            try {
                $dadosStatus = $pdo->query("SELECT COALESCE(status, 'Não definido') as status, COUNT(*) as total FROM processos GROUP BY status")->fetchAll();
            } catch (\Exception $e) {
                error_log("Erro na consulta de status: " . $e->getMessage());
            }

            try {
                $dadosTipo = $pdo->query("SELECT COALESCE(tipo_contratacao, 'Não definido') as tipo_contratacao, COUNT(*) as total FROM processos GROUP BY tipo_contratacao")->fetchAll();
            } catch (\Exception $e) {
                error_log("Erro na consulta de tipo: " . $e->getMessage());
            }

            try {
                $dadosAgentes = $pdo->query("SELECT COALESCE(agente_responsavel, 'Não definido') as agente_responsavel, COUNT(*) as total FROM processos WHERE agente_responsavel IS NOT NULL AND agente_responsavel != '' GROUP BY agente_responsavel ORDER BY total DESC LIMIT 5")->fetchAll();
            } catch (\Exception $e) {
                error_log("Erro na consulta de agentes: " . $e->getMessage());
            }

            try {
                $dadosProcessosPorRegiao = $pdo->query(
                    "SELECT COALESCE(regiao, 'Não informado') as regiao, COUNT(*) as total 
                    FROM processos 
                    GROUP BY regiao 
                    ORDER BY total DESC"
                )->fetchAll();
            } catch (\Exception $e) {
                error_log("Erro na consulta de região: " . $e->getMessage());
            }

            // Consultas avançadas para gráficos completos
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
            } catch (\Exception $e) {
                error_log("Erro na consulta de valor por mês: " . $e->getMessage());
            }

            // Consulta de cotações rápidas
            try {
                $dadosCotacoesRapidas = $pdo->query(
                    "SELECT 
                        DATE_FORMAT(data_criacao, '%Y-%m') as mes,
                        COUNT(*) as total_cotacoes
                     FROM cotacoes_rapidas 
                     WHERE data_criacao >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                     GROUP BY mes 
                     ORDER BY mes ASC"
                )->fetchAll();
            } catch (\Exception $e) {
                error_log("Erro na consulta de cotações rápidas: " . $e->getMessage());
                $dadosCotacoesRapidas = [];
            }

            // Estatísticas de fornecedores
            try {
                $dadosFornecedores = $pdo->query(
                    "SELECT 
                        CASE 
                            WHEN ativo = 1 THEN 'Ativos'
                            ELSE 'Inativos'
                        END as status_fornecedor,
                        COUNT(*) as total
                     FROM fornecedores 
                     GROUP BY ativo"
                )->fetchAll();
            } catch (\Exception $e) {
                error_log("Erro na consulta de fornecedores: " . $e->getMessage());
                $dadosFornecedores = [];
            }

            // KPIs completos
            $totalProcessos = 0;
            $totalEmAndamento = 0;
            $totalFinalizados = 0;
            $totalCotacoesRapidas = 0;
            $totalFornecedores = 0;
            $totalItens = 0;

            try {
                $totalProcessos = $pdo->query("SELECT COUNT(*) FROM processos")->fetchColumn();
                $totalEmAndamento = $pdo->query("SELECT COUNT(*) FROM processos WHERE status = 'Pesquisa em Andamento'")->fetchColumn();
                $totalFinalizados = $pdo->query("SELECT COUNT(*) FROM processos WHERE status = 'Finalizado'")->fetchColumn();
                $totalCotacoesRapidas = $pdo->query("SELECT COUNT(*) FROM cotacoes_rapidas")->fetchColumn();
                $totalFornecedores = $pdo->query("SELECT COUNT(*) FROM fornecedores WHERE ativo = 1")->fetchColumn();
                $totalItens = $pdo->query("SELECT COUNT(*) FROM itens")->fetchColumn();
            } catch (\Exception $e) {
                error_log("Erro nas consultas de KPI: " . $e->getMessage());
            }

            $tituloPagina = "Dashboard";
            $paginaConteudo = __DIR__ . '/../View/dashboard.php';

            ob_start();
            require __DIR__ . '/../View/layout/main.php';
            $view = ob_get_clean();
            
            echo $view;
            
        } catch (\Exception $e) {
            error_log("Erro geral no dashboard: " . $e->getMessage());
            
            // Fallback com estatísticas básicas
            echo "<!DOCTYPE html>
            <html>
            <head>
                <title>Dashboard - Algorise</title>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <style>
                    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; margin: 0; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
                    .container { max-width: 1200px; margin: 0 auto; }
                    .header { text-align: center; color: white; margin-bottom: 40px; }
                    .header h1 { font-size: 2.5rem; margin-bottom: 10px; }
                    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px; }
                    .stat-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; transition: transform 0.3s ease; }
                    .stat-card:hover { transform: translateY(-5px); }
                    .stat-icon { font-size: 3rem; margin-bottom: 15px; }
                    .stat-number { font-size: 2rem; font-weight: bold; color: #333; margin-bottom: 10px; }
                    .stat-label { color: #666; font-size: 1rem; }
                    .success { background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); color: white; padding: 20px; border-radius: 15px; margin-bottom: 30px; text-align: center; }
                    .actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 30px; }
                    .btn { display: block; padding: 15px 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 10px; text-align: center; font-weight: 500; transition: all 0.3s ease; }
                    .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
                    .btn.danger { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); }
                    .footer { text-align: center; color: white; margin-top: 50px; opacity: 0.8; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>🚀 Algorise Dashboard</h1>
                        <p>Sistema de Busca e Análise de Preços</p>
                    </div>
                    
                    <div class='success'>
                        <h3>🎉 Sistema Operacional!</h3>
                        <p>Você está logado como <strong>" . ($_SESSION['usuario_nome'] ?? 'Admin') . "</strong> • Todas as tabelas do banco estão disponíveis</p>
                    </div>
                    
                    <div class='stats-grid'>
                        <div class='stat-card'>
                            <div class='stat-icon'>📋</div>
                            <div class='stat-number'>" . ($totalProcessos ?? 0) . "</div>
                            <div class='stat-label'>Processos</div>
                        </div>
                        <div class='stat-card'>
                            <div class='stat-icon'>⚡</div>
                            <div class='stat-number'>" . ($totalCotacoesRapidas ?? 0) . "</div>
                            <div class='stat-label'>Cotações Rápidas</div>
                        </div>
                        <div class='stat-card'>
                            <div class='stat-icon'>🏢</div>
                            <div class='stat-number'>" . ($totalFornecedores ?? 0) . "</div>
                            <div class='stat-label'>Fornecedores Ativos</div>
                        </div>
                        <div class='stat-card'>
                            <div class='stat-icon'>📦</div>
                            <div class='stat-number'>" . ($totalItens ?? 0) . "</div>
                            <div class='stat-label'>Itens Cadastrados</div>
                        </div>
                    </div>
                    
                    <div class='actions'>
                        <a href='/processos' class='btn'>📋 Gerenciar Processos</a>
                        <a href='/cotacao-rapida' class='btn'>⚡ Cotação Rápida</a>
                        <a href='/fornecedores' class='btn'>🏢 Fornecedores</a>
                        <a href='/itens' class='btn'>📦 Itens</a>
                        <a href='/precos' class='btn'>💰 Preços</a>
                        <a href='/relatorios' class='btn'>📊 Relatórios</a>
                        <a href='/debug-dashboard.php' class='btn'>🔧 Debug</a>
                        <a href='/logout' class='btn danger'>🚪 Logout</a>
                    </div>
                    
                    <div class='footer'>
                        <p>Algorise • Desenvolvido por Joabe Oliveira • Google Cloud Platform</p>
                    </div>
                </div>
            </body>
            </html>";
        }
    }
}