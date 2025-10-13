<?php

namespace Joabe\Buscaprecos\Controller;

use Joabe\Buscaprecos\Core\Router;

class ProcessoController
{
    public function listar($params = [])
    {
        try {
            $pdo = \getDbConnection();
            
            // SQL DIRETO - sem DatabaseHelper
            $stmt = $pdo->query("SELECT id, numero_processo, orgao, status, created_at, valor_estimado FROM processos ORDER BY created_at DESC");
            $processos = $stmt->fetchAll();

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

        try {
            $pdo = \getDbConnection();
            
            // SQL DIRETO - campos fixos
            $stmt = $pdo->prepare("
                INSERT INTO processos (numero_processo, nome_processo, orgao, status, valor_estimado, tipo_contratacao, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $dados['numero_processo'] ?? '',
                $dados['nome_processo'] ?? $dados['nome'] ?? '',
                $dados['orgao'] ?? '',
                $dados['status'] ?? 'Rascunho',
                $dados['valor_estimado'] ?? 0,
                $dados['tipo_contratacao'] ?? ''
            ]);
            
            $_SESSION['flash'] = ['tipo' => 'success', 'mensagem' => 'Processo criado com sucesso!'];
            \Joabe\Buscaprecos\Core\Router::redirect('/processos');
            
        } catch (\Exception $e) {
            error_log("Erro ao criar processo: " . $e->getMessage());
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
            
            // SQL DIRETO - atualização simples
            $stmt = $pdo->prepare("
                UPDATE processos 
                SET numero_processo = ?, nome_processo = ?, orgao = ?, status = ?, valor_estimado = ?, tipo_contratacao = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $dados['numero_processo'] ?? '',
                $dados['nome_processo'] ?? $dados['nome'] ?? '',
                $dados['orgao'] ?? '',
                $dados['status'] ?? '',
                $dados['valor_estimado'] ?? 0,
                $dados['tipo_contratacao'] ?? '',
                $id
            ]);
            
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