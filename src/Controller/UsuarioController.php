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
        $dados = Router::getPostData();
        $email = $dados['email'] ?? '';
        $senha = $dados['senha'] ?? '';

        $pdo = \getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_role'] = $usuario['role'];
            Router::redirect('/dashboard');
            return;
        }

        $_SESSION['flash_error'] = 'E-mail ou senha inválidos.';
        Router::redirect('/login');
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
                    $_SESSION['flash_success'] = 'Link de redefinição enviado para seu e-mail.';
                } else {
                    $_SESSION['flash_error'] = 'Erro ao enviar e-mail. Tente novamente.';
                }

            } catch (\Exception $e) {
                $_SESSION['flash_error'] = 'Erro no envio do e-mail: ' . $e->getMessage();
            }
        } else {
            // Sempre mostra a mesma mensagem (segurança)
            $_SESSION['flash_success'] = 'Se o e-mail estiver cadastrado, você receberá as instruções.';
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
            $_SESSION['flash_error'] = 'Link inválido ou expirado.';
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
            $_SESSION['flash_error'] = 'Link inválido ou expirado.';
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
            $_SESSION['flash_error'] = 'Dados incompletos.';
            Router::redirect('/login');
            return;
        }

        if ($novaSenha !== $confirmarSenha) {
            $_SESSION['flash_error'] = 'As senhas não coincidem.';
            Router::redirect("/redefinir-senha?token={$token}&email=" . urlencode($email));
            return;
        }

        if (strlen($novaSenha) < 6) {
            $_SESSION['flash_error'] = 'A senha deve ter pelo menos 6 caracteres.';
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
            $_SESSION['flash_error'] = 'Link inválido ou expirado.';
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

        $_SESSION['flash_success'] = 'Senha redefinida com sucesso! Faça login com sua nova senha.';
        Router::redirect('/login');
    }

    /**
     * Lista todos os usuários (apenas admin)
     */
    public function listar($params = [])
    {
        $pdo = \getDbConnection();
        $stmt = $pdo->prepare("SELECT id, nome, email, role, created_at FROM usuarios ORDER BY nome");
        $stmt->execute();
        $usuarios = $stmt->fetchAll();

        $tituloPagina = "Gerenciar Usuários";
        $paginaConteudo = __DIR__ . '/../View/usuarios/listar.php';
        
        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();
        echo $view;
    }

    /**
     * Exibe formulário de criação de usuário
     */
    public function exibirFormularioCriacao($params = [])
    {
        $tituloPagina = "Novo Usuário";
        $paginaConteudo = __DIR__ . '/../View/usuarios/criar.php';
        
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

        // Validações
        if (empty($dados['nome']) || empty($dados['email']) || empty($dados['senha'])) {
            $_SESSION['flash_error'] = 'Todos os campos são obrigatórios.';
            Router::redirect('/usuarios/novo');
            return;
        }

        if (!validarEmail($dados['email'])) {
            $_SESSION['flash_error'] = 'E-mail inválido.';
            Router::redirect('/usuarios/novo');
            return;
        }

        if (strlen($dados['senha']) < 6) {
            $_SESSION['flash_error'] = 'A senha deve ter pelo menos 6 caracteres.';
            Router::redirect('/usuarios/novo');
            return;
        }

        $role = in_array($dados['role'] ?? '', ['admin', 'user']) ? $dados['role'] : 'user';
        $senhaHash = password_hash($dados['senha'], PASSWORD_DEFAULT);

        $pdo = \getDbConnection();

        try {
            $stmt = $pdo->prepare("
                INSERT INTO usuarios (nome, email, senha, role) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$dados['nome'], $dados['email'], $senhaHash, $role]);
            
            $_SESSION['flash_success'] = 'Usuário criado com sucesso!';
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) { // Erro de violação de chave única
                $_SESSION['flash_error'] = 'E-mail já cadastrado.';
            } else {
                $_SESSION['flash_error'] = 'Erro ao criar usuário.';
            }
            Router::redirect('/usuarios/novo');
            return;
        }

        Router::redirect('/usuarios');
    }

    /**
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
            $_SESSION['flash_error'] = 'Usuário não encontrado.';
            Router::redirect('/usuarios');
            return;
        }

        $tituloPagina = "Editar Usuário";
        $paginaConteudo = __DIR__ . '/../View/usuarios/editar.php';
        
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

        // Validações
        if (empty($dados['nome']) || empty($dados['email'])) {
            $_SESSION['flash_error'] = 'Nome e e-mail são obrigatórios.';
            Router::redirect("/usuarios/{$id}/editar");
            return;
        }

        if (!validarEmail($dados['email'])) {
            $_SESSION['flash_error'] = 'E-mail inválido.';
            Router::redirect("/usuarios/{$id}/editar");
            return;
        }

        $role = in_array($dados['role'] ?? '', ['admin', 'user']) ? $dados['role'] : 'user';
        $pdo = \getDbConnection();

        try {
            // Se uma nova senha foi fornecida
            if (!empty($dados['senha'])) {
                if (strlen($dados['senha']) < 6) {
                    $_SESSION['flash_error'] = 'A senha deve ter pelo menos 6 caracteres.';
                    Router::redirect("/usuarios/{$id}/editar");
                    return;
                }
                
                $senhaHash = password_hash($dados['senha'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    UPDATE usuarios SET nome = ?, email = ?, senha = ?, role = ? WHERE id = ?
                ");
                $stmt->execute([$dados['nome'], $dados['email'], $senhaHash, $role, $id]);
            } else {
                // Atualiza sem alterar a senha
                $stmt = $pdo->prepare("
                    UPDATE usuarios SET nome = ?, email = ?, role = ? WHERE id = ?
                ");
                $stmt->execute([$dados['nome'], $dados['email'], $role, $id]);
            }
            
            $_SESSION['flash_success'] = 'Usuário atualizado com sucesso!';
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                $_SESSION['flash_error'] = 'E-mail já cadastrado para outro usuário.';
            } else {
                $_SESSION['flash_error'] = 'Erro ao atualizar usuário.';
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
            $_SESSION['flash_error'] = 'Você não pode excluir seu próprio usuário.';
            Router::redirect('/usuarios');
            return;
        }

        $pdo = \getDbConnection();
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['flash_success'] = 'Usuário excluído com sucesso!';
        Router::redirect('/usuarios');
    }
}