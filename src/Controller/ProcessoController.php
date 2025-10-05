<?php

use Joabe\Buscaprecos\Core\Router;

namespace Joabe\Buscaprecos\Controller;

class ProcessoController
{
        public function listar($params = [])
    {
        $pdo = \getDbConnection();
        $stmt = $pdo->query("SELECT * FROM processos ORDER BY data_criacao DESC");
        $processos = $stmt->fetchAll();

        // Prepara as variáveis para o layout principal
        $tituloPagina = "Lista de Processos";
        
        // --- A CORREÇÃO ESTÁ AQUI ---
        // Garante que estamos apontando para a view correta da lista de PROCESSOS.
        $paginaConteudo = __DIR__ . '/../View/processos/lista.php';

        // Renderiza o layout principal, que por sua vez incluirá a nossa lista
        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();

        echo $view;
    }

    // --- CORREÇÃO APLICADA AQUI ---
        public function exibirFormulario($params = [])
    {
        // Prepara as variáveis para o layout principal
        $tituloPagina = "Novo Processo";
        // Define o arquivo de conteúdo que o layout principal vai incluir
        $paginaConteudo = __DIR__ . '/../View/processos/formulario.php';

        // Renderiza o layout principal
        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();

        echo $view;
    }
    // --- FIM DA CORREÇÃO ---

    public function criar($params = [])
{
    $dados = \Joabe\Buscaprecos\Core\Router::getPostData();

    $sql = "INSERT INTO processos (numero_processo, nome_processo, tipo_contratacao, status, agente_responsavel, agente_matricula, uasg, regiao) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $pdo = \getDbConnection();
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $dados['numero_processo'],
        $dados['nome_processo'],
        $dados['tipo_contratacao'],
        $dados['status'],
        $dados['agente_responsavel'], 
        $dados['agente_matricula'] ?? null, // Novo campo
        $dados['uasg'],          
        $dados['regiao']
    ]);

    \Joabe\Buscaprecos\Core\Router::redirect('/processos'); return;
}


    public function exibirFormularioEdicao($params = [])
    {
        $id = $args['id'];
        $pdo = \getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM processos WHERE id = ?");
        $stmt->execute([$id]);
        $processo = $stmt->fetch();

        if (!$processo) {
            $response->getBody()->write("Processo não encontrado.");
            return $response->withStatus(404);
        }
        
        // Prepara as variáveis para o layout principal
        $tituloPagina = "Editar Processo";
        $paginaConteudo = __DIR__ . '/../View/processos/formulario_edicao.php';

        // Renderiza o layout principal
        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();

        echo $view;
    }

    // NOVO MÉTODO: Salva as alterações no banco de dados
    public function atualizar($params = [])
{
    $id = $args['id'];
    $dados = \Joabe\Buscaprecos\Core\Router::getPostData();

    $sql = "UPDATE processos SET 
                numero_processo = ?, nome_processo = ?, tipo_contratacao = ?, status = ?, 
                agente_responsavel = ?, agente_matricula = ?, uasg = ?, regiao = ? 
            WHERE id = ?";

    $pdo = \getDbConnection();
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $dados['numero_processo'],
        $dados['nome_processo'],
        $dados['tipo_contratacao'],
        $dados['status'],
        $dados['agente_responsavel'], 
        $dados['agente_matricula'] ?? null, // Novo campo
        $dados['uasg'],
        $dados['regiao'],
        $id
    ]);

    \Joabe\Buscaprecos\Core\Router::redirect('/processos'); return;
}


        // NOVO MÉTODO: Apaga um processo do banco de dados
    public function excluir($params = [])
    {
        $id = $args['id'];

        $sql = "DELETE FROM processos WHERE id = ?";

        $pdo = \getDbConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        // Redireciona para o dashboard após excluir
        \Joabe\Buscaprecos\Core\Router::redirect('/dashboard'); return;
    }
}