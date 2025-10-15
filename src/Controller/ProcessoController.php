<?php

namespace Joabe\Buscaprecos\Controller;

use Joabe\Buscaprecos\Core\Router;

class ProcessoController
{
    public function listar($params = [])
    {
        try {
            $pdo = \getDbConnection();
            
            // SQL DIRETO - buscar TODAS as colunas necessárias
            $stmt = $pdo->query("SELECT id, numero_processo, orgao, nome_processo, tipo_contratacao, status, agente_responsavel, agente_matricula, uasg, regiao, data_criacao FROM processos ORDER BY data_criacao DESC");
            $processos = $stmt->fetchAll();
            
            // Converter ENUMs snake_case para exibição em PT-BR
            $tipoMap = [
                'pregao_eletronico' => 'Pregão Eletrônico',
                'pregao_presencial' => 'Pregão Presencial',
                'dispensavel' => 'Dispensa de Licitação',
                'inexigivel' => 'Inexigibilidade'
            ];
            
            $statusMap = [
                'planejamento' => 'Em Elaboração',
                'em_andamento' => 'Pesquisa em Andamento',
                'concluido' => 'Finalizado',
                'cancelado' => 'Cancelado'
            ];
            
            foreach ($processos as &$processo) {
                $processo['tipo_contratacao'] = $tipoMap[$processo['tipo_contratacao']] ?? $processo['tipo_contratacao'];
                $processo['status'] = $statusMap[$processo['status']] ?? $processo['status'];
            }

            $tituloPagina = "Lista de Processos";
            $paginaConteudo = __DIR__ . '/../View/processos/lista.php';

            ob_start();
            require __DIR__ . '/../View/layout/main.php';
            $view = ob_get_clean();

            echo $view;
            
        } catch (\Exception $e) {
            error_log("Erro ao listar processos: " . $e->getMessage());
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Ocorreu um erro ao carregar os processos. Tente novamente.'];
            Router::redirect('/dashboard');
        }
    }

    public function exibirFormulario($params = [])
    {
        $tituloPagina = "Novo Processo";
        $paginaConteudo = __DIR__ . '/../View/processos/formulario.php';

        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();

        echo $view;
    }

    public function criar($params = [])
    {
        $dados = \Joabe\Buscaprecos\Core\Router::getPostData();
        
        // Log de debug
        error_log("=== CRIAR PROCESSO ===");
        error_log("Dados recebidos: " . json_encode($dados));

        try {
            $pdo = \getDbConnection();
            
            // Validação básica
            if (empty($dados['numero_processo'])) {
                error_log("Erro: numero_processo vazio");
                $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Número do processo é obrigatório'];
                \Joabe\Buscaprecos\Core\Router::redirect('/processos/novo');
                return;
            }
            
            // Converter valores do formulário para snake_case do ENUM
            $tipoContratacaoMap = [
                'Pregão Eletrônico' => 'pregao_eletronico',
                'Dispensa de Licitação' => 'dispensavel',
                'Inexigibilidade' => 'inexigivel',
                'Compra Direta' => 'dispensavel'
            ];
            
            $statusMap = [
                'Em Elaboração' => 'planejamento',
                'Pesquisa em Andamento' => 'em_andamento',
                'Finalizado' => 'concluido',
                'Cancelado' => 'cancelado'
            ];
            
            $tipoContratacao = $tipoContratacaoMap[$dados['tipo_contratacao'] ?? ''] ?? 'pregao_eletronico';
            $status = $statusMap[$dados['status'] ?? ''] ?? 'planejamento';
            
            // Colunas: numero_processo, orgao, nome_processo, tipo_contratacao, status, agente_responsavel, agente_matricula, uasg, regiao, data_criacao
            $sql = "INSERT INTO processos (numero_processo, orgao, nome_processo, tipo_contratacao, status, agente_responsavel, agente_matricula, uasg, regiao, data_criacao) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            
            $params = [
                $dados['numero_processo'] ?? '',
                $dados['orgao'] ?? '',
                $dados['nome_processo'] ?? $dados['nome'] ?? '',
                $tipoContratacao,
                $status,
                $dados['agente_responsavel'] ?? $_SESSION['usuario_nome'] ?? 'Sistema',
                $dados['agente_matricula'] ?? '',
                $dados['uasg'] ?? '',
                $dados['regiao'] ?? ''
            ];
            
            error_log("SQL: $sql");
            error_log("Params: " . json_encode($params));
            
            $result = $stmt->execute($params);
            $lastId = $pdo->lastInsertId();
            
            error_log("INSERT sucesso! ID: $lastId, Linhas afetadas: " . $stmt->rowCount());
            
            $_SESSION['flash'] = ['tipo' => 'success', 'mensagem' => 'Processo criado com sucesso!'];
            \Joabe\Buscaprecos\Core\Router::redirect('/processos');
            
        } catch (\Exception $e) {
            error_log("ERRO ao criar processo: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $_SESSION['flash'] = [
                'tipo' => 'danger',
                'mensagem' => 'Ocorreu um erro ao criar o processo. Tente novamente.',
                'dados_formulario' => $dados
            ];
            \Joabe\Buscaprecos\Core\Router::redirect('/processos/novo');
        }
    }

    public function exibirFormularioEdicao($params = [])
    {
        $id = $params['id'] ?? 0;
        $pdo = \getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM processos WHERE id = ?");
        $stmt->execute([$id]);
        $processo = $stmt->fetch();

        if (!$processo) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Processo não encontrado.'];
            Router::redirect('/processos');
            return;
        }
        
        $tituloPagina = "Editar Processo";
        $paginaConteudo = __DIR__ . '/../View/processos/formulario_edicao.php';

        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();

        echo $view;
    }

    public function atualizar($params = [])
    {
        $id = $params['id'] ?? 0;
        $dados = \Joabe\Buscaprecos\Core\Router::getPostData();
        $redirectUrl = "/processos/$id/editar";

        try {
            $pdo = \getDbConnection();
            
            // Converter valores do formulário para snake_case do ENUM
            $tipoContratacaoMap = [
                'Pregão Eletrônico' => 'pregao_eletronico',
                'Dispensa de Licitação' => 'dispensavel',
                'Inexigibilidade' => 'inexigivel',
                'Compra Direta' => 'dispensavel'
            ];
            
            $statusMap = [
                'Em Elaboração' => 'planejamento',
                'Pesquisa em Andamento' => 'em_andamento',
                'Finalizado' => 'concluido',
                'Cancelado' => 'cancelado'
            ];
            
            $tipoContratacao = $tipoContratacaoMap[$dados['tipo_contratacao'] ?? ''] ?? 'pregao_eletronico';
            $status = $statusMap[$dados['status'] ?? ''] ?? 'planejamento';
            
            error_log("=== ATUALIZAR PROCESSO ===");
            error_log("ID: $id");
            error_log("Tipo Original: " . ($dados['tipo_contratacao'] ?? '') . " -> Convertido: $tipoContratacao");
            error_log("Status Original: " . ($dados['status'] ?? '') . " -> Convertido: $status");
            
            $stmt = $pdo->prepare("
                UPDATE processos 
                SET numero_processo = ?, orgao = ?, nome_processo = ?, tipo_contratacao = ?, status = ?, agente_responsavel = ?, agente_matricula = ?, uasg = ?, regiao = ?, data_atualizacao = NOW()
                WHERE id = ?
            ");
            
            $params = [
                $dados['numero_processo'] ?? '',
                $dados['orgao'] ?? '',
                $dados['nome_processo'] ?? $dados['nome'] ?? '',
                $tipoContratacao,
                $status,
                $dados['agente_responsavel'] ?? $_SESSION['usuario_nome'] ?? 'Sistema',
                $dados['agente_matricula'] ?? '',
                $dados['uasg'] ?? '',
                $dados['regiao'] ?? '',
                $id
            ];
            
            error_log("Params: " . json_encode($params));
            $stmt->execute($params);
            
            $_SESSION['flash'] = ['tipo' => 'success', 'mensagem' => 'Processo atualizado com sucesso!'];
            \Joabe\Buscaprecos\Core\Router::redirect('/processos');
            
        } catch (\Exception $e) {
            error_log("Erro ao atualizar processo: " . $e->getMessage());
            $_SESSION['flash'] = [
                'tipo' => 'danger',
                'mensagem' => 'Ocorreu um erro ao atualizar o processo. Tente novamente.',
                'dados_formulario' => $dados
            ];
            \Joabe\Buscaprecos\Core\Router::redirect($redirectUrl);
        }
    }

    public function excluir($params = [])
    {
        $id = $params['id'] ?? 0;

        try {
            $pdo = \getDbConnection();
            $stmt = $pdo->prepare("DELETE FROM processos WHERE id = ?");
            $stmt->execute([$id]);

            $_SESSION['flash'] = ['tipo' => 'success', 'mensagem' => 'Processo excluído com sucesso.'];
            
        } catch (\PDOException $e) {
            error_log("Erro ao excluir processo: " . $e->getMessage());

            if ($e->getCode() == 23000) { // Foreign key constraint
                $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Não é possível excluir o processo, pois ele possui itens ou cotações associadas.'];
            } else {
                $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Ocorreu um erro inesperado ao excluir o processo.'];
            }
        }

        Router::redirect('/processos');
    }
}