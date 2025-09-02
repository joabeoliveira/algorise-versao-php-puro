<?php
namespace Joabe\Buscaprecos\Controller;

class DashboardController
{
    public function exibir($request, $response, $args)
{
    $pdo = \getDbConnection();

    // Consultas para os Gráficos EXISTENTES
    $dadosStatus = $pdo->query("SELECT status, COUNT(*) as total FROM processos GROUP BY status")->fetchAll();
    $dadosTipo = $pdo->query("SELECT tipo_contratacao, COUNT(*) as total FROM processos GROUP BY tipo_contratacao")->fetchAll();
    $dadosAgentes = $pdo->query("SELECT agente_responsavel, COUNT(*) as total FROM processos WHERE agente_responsavel IS NOT NULL AND agente_responsavel != '' GROUP BY agente_responsavel ORDER BY total DESC LIMIT 5")->fetchAll();

    // --- INÍCIO DAS NOVAS CONSULTAS ---

    // 1. Consulta para o Valor Total dos Processos por Mês
    $dadosValorPorMes = $pdo->query(
        "SELECT DATE_FORMAT(data_criacao, '%Y-%m') as mes, SUM(valor_total_estimado) as valor_total
         FROM (
             SELECT p.data_criacao, SUM(i.valor_estimado * i.quantidade) as valor_total_estimado
             FROM processos p
             JOIN itens i ON p.id = i.processo_id
             WHERE p.status = 'Finalizado' AND i.valor_estimado IS NOT NULL
             GROUP BY p.id, p.data_criacao
         ) as subquery
         GROUP BY mes
         ORDER BY mes ASC
         LIMIT 12"
    )->fetchAll();

    // 2. Consulta para a Taxa de Resposta de Fornecedores
    $dadosRespostasFornecedores = $pdo->query(
        "SELECT 
            CASE 
                WHEN status = 'Respondido' THEN 'Respondido'
                WHEN status = 'Enviado' AND prazo_final >= CURDATE() THEN 'Aguardando'
                ELSE 'Prazo Expirado'
            END as status_calculado,
            COUNT(*) as total
        FROM lotes_solicitacao_fornecedores lsf
        JOIN lotes_solicitacao ls ON lsf.lote_solicitacao_id = ls.id
        GROUP BY status_calculado"
    )->fetchAll();

    // 3. Consulta para Processos por Região
    $dadosProcessosPorRegiao = $pdo->query(
        "SELECT regiao, COUNT(*) as total 
        FROM processos 
        WHERE regiao IS NOT NULL AND regiao != ''
        GROUP BY regiao 
        ORDER BY total DESC"
    )->fetchAll();

    // --- FIM DAS NOVAS CONSULTAS ---


    // Consultas para os Cartões de KPI
    $totalProcessos = $pdo->query("SELECT COUNT(*) FROM processos")->fetchColumn();
    $totalEmAndamento = $pdo->query("SELECT COUNT(*) FROM processos WHERE status = 'Pesquisa em Andamento'")->fetchColumn();
    $totalFinalizados = $pdo->query("SELECT COUNT(*) FROM processos WHERE status = 'Finalizado'")->fetchColumn();

    $tituloPagina = "Dashboard";
    $paginaConteudo = __DIR__ . '/../View/dashboard.php';

    ob_start();
    require __DIR__ . '/../View/layout/main.php';
    $view = ob_get_clean();

    $response->getBody()->write($view);
    return $response;
}

}