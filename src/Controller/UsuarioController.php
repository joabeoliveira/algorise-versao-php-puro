<?php

namespace Joabe\Buscaprecos\Controller;

use Joabe\Buscaprecos\Core\Router;
use Joabe\Buscaprecos\Core\Mail;

class UsuarioController
{
    /**
     * Exibe o formulário de login
     */
    public function exibirFormularioLogin($params = [])
    {
        ob_start();
        require __DIR__ . '/../View/usuarios/login.php';
        $view = ob_get_clean();
        echo $view;
    }

    /**
     * Processa o login do usuário
     */
    public function processarLogin($params = [])
    {
        try {
            if (session_status() === \PHP_SESSION_NONE) {
                session_start();
            }
            $dados = Router::getPostData();
            $email = $dados['email'] ?? '';
            $senha = $dados['senha'] ?? '';

            $pdo = \getDbConnection();
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($senha, $usuario['senha'])) {
                // Evita fixação de sessão ao logar
                if (function_exists('session_regenerate_id')) {
                    @session_regenerate_id(true);
                }
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_role'] = $usuario['role'];
                Router::redirect('/dashboard');
                return;
            }

            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'E-mail ou senha inválidos.'];
            Router::redirect('/login');

        } catch (\Exception $e) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Ocorreu um erro inesperado. Tente novamente.'];
            Router::redirect('/login');
        }
    }

    /**
     * Processa o logout do usuário
     */
    public function processarLogout($params = [])
    {
        if (session_status() === PHP_SESSION_NONE) { 
            session_start(); 
        }
        
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $cookieParams = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000, 
                $cookieParams["path"],
                $cookieParams["domain"],
                $cookieParams["secure"],
                $cookieParams["httponly"]
            );
        }
        
        session_destroy();
        Router::redirect('/login');
    }

    /**
     * Exibe o formulário "Esqueceu a senha"
     */
    public function exibirFormularioEsqueceuSenha($params = [])
    {
        ob_start();
        require __DIR__ . '/../View/usuarios/esqueceu_senha.php';
        $view = ob_get_clean();
        echo $view;
    }

    /**
     * Processa a solicitação de redefinição de senha
     */
    public function solicitarRedefinicao($params = [])
    {
        $dados = Router::getPostData();
        $email = $dados['email'] ?? '';

        $pdo = \getDbConnection();
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);

        // Se o e-mail existir no banco, tenta enviar o link
        if ($stmt->fetch()) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = password_hash($token, PASSWORD_DEFAULT);
            
            $sql = "REPLACE INTO password_resets (email, token, created_at) VALUES (?, ?, NOW())";
            $stmtToken = $pdo->prepare($sql);
            $stmtToken->execute([$email, $tokenHash]);
            
            $resetLink = "http://{$_SERVER['HTTP_HOST']}/redefinir-senha?token={$token}&email=" . urlencode($email);
            
            // Usa o novo sistema de email
            try {
                $emailBody = "
                <h2>Redefinição de Senha - Algorise</h2>
                <p>Você solicitou a redefinição de sua senha.</p>
                <p>Clique no link abaixo para redefinir:</p>
                <p><a href='{$resetLink}'>Redefinir Senha</a></p>
                <p>Se você não solicitou esta redefinição, ignore este email.</p>
                <p>Este link expira em 1 hora.</p>
                ";

                $emailEnviado = Mail::sendWithEnvConfig(
                    $email,
                    'Redefinição de Senha - Algorise',
                    $emailBody,
                    true // HTML
                );

                if ($emailEnviado) {
                    $_SESSION['flash'] = ['tipo' => 'success', 'mensagem' => 'Link de redefinição enviado para seu e-mail.'];
                } else {
                    $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Erro ao enviar e-mail. Tente novamente.'];
                }

            } catch (\Exception $e) {
                $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Erro ao enviar e-mail. Tente novamente.'];
            }
        } else {
            // Sempre mostra a mesma mensagem (segurança)
            $_SESSION['flash'] = ['tipo' => 'success', 'mensagem' => 'Se o e-mail estiver cadastrado, você receberá as instruções.'];
        }

        Router::redirect('/login');
    }

    /**
     * Exibe o formulário de redefinição de senha
     */
    public function exibirFormularioRedefinir($params = [])
    {
        $queryParams = Router::getQueryData();
        $token = $queryParams['token'] ?? '';
        $email = $queryParams['email'] ?? '';

        if (empty($token) || empty($email)) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Link inválido ou expirado.'];
            Router::redirect('/login');
            return;
        }

        // Verifica se o token é válido e não expirado
        $pdo = \getDbConnection();
        $stmt = $pdo->prepare(" 
            SELECT token FROM password_resets 
            WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute([$email]);
        $resetData = $stmt->fetch();

        if (!$resetData || !password_verify($token, $resetData['token'])) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Link inválido ou expirado.'];
            Router::redirect('/login');
            return;
        }

        // Exibe o formulário
        ob_start();
        require __DIR__ . '/../View/usuarios/redefinir_senha.php';
        $view = ob_get_clean();
        echo $view;
    }

    /**
     * Processa a redefinição de senha
     */
    public function processarRedefinicao($params = [])
    {
        $dados = Router::getPostData();
        $queryParams = Router::getQueryData();
        
        $token = $queryParams['token'] ?? '';
        $email = $queryParams['email'] ?? '';
        $novaSenha = $dados['nova_senha'] ?? '';
        $confirmarSenha = $dados['confirmar_senha'] ?? '';

        if (empty($token) || empty($email) || empty($novaSenha)) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Dados incompletos.'];
            Router::redirect('/login');
            return;
        }

        if ($novaSenha !== $confirmarSenha) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'As senhas não coincidem.'];
            Router::redirect("/redefinir-senha?token={$token}&email=" . urlencode($email));
            return;
        }

        if (strlen($novaSenha) < 6) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'A senha deve ter pelo menos 6 caracteres.'];
            Router::redirect("/redefinir-senha?token={$token}&email=" . urlencode($email));
            return;
        }

        $pdo = \getDbConnection();

        // Verifica o token novamente
        $stmt = $pdo->prepare(" 
            SELECT token FROM password_resets 
            WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute([$email]);
        $resetData = $stmt->fetch();

        if (!$resetData || !password_verify($token, $resetData['token'])) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Link inválido ou expirado.'];
            Router::redirect('/login');
            return;
        }

        // Atualiza a senha
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE email = ?");
        $stmt->execute([$senhaHash, $email]);

        // Remove o token usado
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->execute([$email]);

        $_SESSION['flash'] = ['tipo' => 'success', 'mensagem' => 'Senha redefinida com sucesso! Faça login com sua nova senha.'];
        Router::redirect('/login');
    }

    /**
     * Lista todos os usuários (apenas admin)
     */
    public function listar($params = [])
    {
        try {
            $pdo = \getDbConnection();
            
            // SQL DIRETO - sem DatabaseHelper (usando criado_em correto)
            $stmt = $pdo->prepare("SELECT id, nome, email, role, ativo, criado_em FROM usuarios ORDER BY nome");
            $stmt->execute();
            $usuarios = $stmt->fetchAll();

            $tituloPagina = "Gerenciar Usuários";
            $paginaConteudo = __DIR__ . '/../View/usuarios/lista.php';
            
            ob_start();
            require __DIR__ . '/../View/layout/main.php';
            $view = ob_get_clean();
            echo $view;
            
        } catch (\Exception $e) {
            echo "<!DOCTYPE html>
            <html>
            <head>
                <title>Usuários - Erro</title>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; background: #f8f9fa; }
                    .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; }
                    .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
                </style>
            </head>
            <body>
                <h1>Gerenciar Usuários</h1>
                <div class='error'>
                    <h3>Erro ao carregar usuários</h3>
                    <p>Ocorreu um erro ao acessar os dados dos usuários.</p>
                </div>
                <a href='/dashboard' class='btn'>Voltar ao Dashboard</a>
            </body>
            </html>";
        }
    }

    /**
     * Exibe formulário de criação de usuário
     */
    public function exibirFormularioCriacao($params = [])
    {
        $tituloPagina = "Novo Usuário";
        $paginaConteudo = __DIR__ . '/../View/usuarios/formulario.php';
        
        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();
        echo $view;
    }

    /**
     * Cria um novo usuário
     */
    public function criar($params = [])
    {
        $dados = Router::getPostData();
        
        // Validações básicas
        if (empty($dados['nome']) || empty($dados['email']) || empty($dados['senha'])) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Nome, email e senha são obrigatórios.'];
            Router::redirect('/usuarios/novo');
            return;
        }

        $pdo = \getDbConnection();
        
        try {
            $senhaHash = password_hash($dados['senha'], PASSWORD_DEFAULT);
            
            // SQL DIRETO - sem complicações
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, role) VALUES (?, ?, ?, 'admin')");
            $result = $stmt->execute([
                $dados['nome'], 
                $dados['email'], 
                $senhaHash
            ]);

            if ($result) {
                $_SESSION['flash'] = ['tipo' => 'success', 'mensagem' => 'Usuário criado com sucesso!'];
                Router::redirect('/usuarios');
            } else {
                $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Erro ao criar usuário.'];
                Router::redirect('/usuarios/novo');
            }
            
        } catch (\PDOException $e) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Erro no banco de dados. Tente novamente.'];
            Router::redirect('/usuarios/novo');
        } catch (\Exception $e) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Erro interno. Tente novamente.'];
            Router::redirect('/usuarios/novo');
        }
    }    /**
     * Exibe formulário de edição de usuário
     */
    public function exibirFormularioEdicao($params = [])
    {
        $id = $params['id'] ?? 0;

        $pdo = \getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $usuario = $stmt->fetch();

        if (!$usuario) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Usuário não encontrado.'];
            Router::redirect('/usuarios');
            return;
        }

        $tituloPagina = "Editar Usuário";
        $paginaConteudo = __DIR__ . '/../View/usuarios/formulario_edicao.php';
        
        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();
        echo $view;
    }

    /**
     * Atualiza um usuário existente
     */
    public function atualizar($params = [])
    {
        $id = $params['id'] ?? 0;
        $dados = Router::getPostData();
        
        error_log("=== ATUALIZAR USUÁRIO ===");
        error_log("ID: $id");
        error_log("Dados recebidos: " . json_encode($dados));

        // Validações
        if (empty($dados['nome']) || empty($dados['email'])) {
            error_log("Validação falhou: nome ou email vazio");
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Nome e e-mail são obrigatórios.'];
            Router::redirect("/usuarios/{$id}/editar");
            return;
        }

        if (!validarEmail($dados['email'])) {
            error_log("Validação falhou: email inválido");
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'E-mail inválido.'];
            Router::redirect("/usuarios/{$id}/editar");
            return;
        }

        // Corrigir role: banco aceita 'admin' e 'usuario' (não 'user')
        $role = in_array($dados['role'] ?? '', ['admin', 'usuario']) ? $dados['role'] : 'usuario';
        $pdo = \getDbConnection();

        try {
            // Se uma nova senha foi fornecida
            if (!empty($dados['senha'])) {
                if (strlen($dados['senha']) < 6) {
                    $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'A senha deve ter pelo menos 6 caracteres.'];
                    Router::redirect("/usuarios/{$id}/editar");
                    return;
                }
                
                $senhaHash = password_hash($dados['senha'], PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios SET nome = ?, email = ?, senha = ?, role = ?, atualizado_em = NOW() WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $params = [$dados['nome'], $dados['email'], $senhaHash, $role, $id];
                error_log("SQL (com senha): $sql");
                error_log("Params: " . json_encode($params));
                $stmt->execute($params);
            } else {
                // Atualiza sem alterar a senha
                $sql = "UPDATE usuarios SET nome = ?, email = ?, role = ?, atualizado_em = NOW() WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $params = [$dados['nome'], $dados['email'], $role, $id];
                error_log("SQL (sem senha): $sql");
                error_log("Params: " . json_encode($params));
                $stmt->execute($params);
            }
            
            error_log("UPDATE sucesso! Linhas afetadas: " . $stmt->rowCount());
            $_SESSION['flash'] = ['tipo' => 'success', 'mensagem' => 'Usuário atualizado com sucesso!'];
        } catch (\PDOException $e) {
            error_log("ERRO ao atualizar usuário: " . $e->getMessage());
            if ($e->getCode() == 23000) {
                $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'E-mail já cadastrado para outro usuário.'];
            } else {
                $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Erro ao atualizar usuário. Tente novamente.'];
            }
            Router::redirect("/usuarios/{$id}/editar");
            return;
        }

        Router::redirect('/usuarios');
    }

    /**
     * Exclui um usuário
     */
    public function excluir($params = [])
    {
        $id = $params['id'] ?? 0;

        // Impede que o usuário se auto-exclua
        if ($id == $_SESSION['usuario_id']) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Você não pode excluir seu próprio usuário.'];
            Router::redirect('/usuarios');
            return;
        }

        $pdo = \getDbConnection();
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['flash'] = ['tipo' => 'success', 'mensagem' => 'Usuário excluído com sucesso!'];
        Router::redirect('/usuarios');
    }

    /**
     * Download de proposta de fornecedor
     */
    public function downloadProposta($params = [])
    {
        $nomeArquivo = $params['nome_arquivo'] ?? '';
        
        if (empty($nomeArquivo)) {
            http_response_code(404);
            echo 'Nome do arquivo não fornecido.';
            return;
        }
        
        $caminhoCompleto = __DIR__ . '/../../storage/propostas/' . $nomeArquivo;
        
        if (!file_exists($caminhoCompleto) || !preg_match('/^[a-f0-9]+\.pdf$/', $nomeArquivo)) {
            http_response_code(404);
            echo 'Arquivo não encontrado ou inválido.';
            return;
        }
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $nomeArquivo . '"');
        readfile($caminhoCompleto);
    }
}