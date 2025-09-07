<?php

namespace Joabe\Buscaprecos\Controller;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class UsuarioController
{
    // ... (outros métodos como exibirFormularioLogin, processarLogin, etc. permanecem iguais)
    public function exibirFormularioLogin($request, $response, $args)
    {
        ob_start();
        require __DIR__ . '/../View/usuarios/login.php';
        $view = ob_get_clean();
        $response->getBody()->write($view);
        return $response;
    }
    public function processarLogin($request, $response, $args)
    {
        $dados = $request->getParsedBody();
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
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }
        $_SESSION['flash_error'] = 'E-mail ou senha inválidos.';
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
    public function processarLogout($request, $response, $args)
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
    public function exibirFormularioEsqueceuSenha($request, $response, $args)
    {
        ob_start();
        require __DIR__ . '/../View/usuarios/esqueceu_senha.php';
        $view = ob_get_clean();
        $response->getBody()->write($view);
        return $response;
    }

    // --- MÉTODO solicitarRedefinicao CORRIGIDO ---
    public function solicitarRedefinicao($request, $response, $args)
    {
        $dados = $request->getParsedBody();
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
            
            $mail = new PHPMailer(true);
            try {
                // Ativar o modo de depuração do PHPMailer (ajuda a encontrar o erro)
                // $mail->SMTPDebug = \PHPMailer\SMTP::DEBUG_SERVER;

                $mail->isSMTP();
                $mail->Host       = $_ENV['MAIL_HOST'];
                $mail->SMTPAuth   = true;
                $mail->Username   = $_ENV['MAIL_USERNAME'];
                $mail->Password   = $_ENV['MAIL_PASSWORD'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = (int)$_ENV['MAIL_PORT'];
                $mail->CharSet    = 'UTF-8';
                $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Redefinição de Senha - Busca Preços AI';
                $mail->Body    = "Olá,<br><br>Você solicitou a redefinição de sua senha. Clique no link abaixo para criar uma nova senha:<br><br><a href='{$resetLink}'>{$resetLink}</a><br><br>Este link é válido por uma hora.";
                
                $mail->send();

                // Define a mensagem de SUCESSO e PARA a execução
                $_SESSION['flash_message'] = 'Se o seu e-mail estiver cadastrado, um link de recuperação foi enviado.';
                return $response->withHeader('Location', '/esqueceu-senha')->withStatus(302);

            } catch (Exception $e) {
                error_log("PHPMailer Error: " . $mail->ErrorInfo);
                // Define a mensagem de ERRO e PARA a execução
                $_SESSION['flash_message'] = 'ERRO: Não foi possível enviar o e-mail. Verifique suas credenciais no arquivo .env e a conexão. Detalhe técnico: ' . $mail->ErrorInfo;
                return $response->withHeader('Location', '/esqueceu-senha')->withStatus(302);
            }
        }
        
        // Se o e-mail não existir, exibe a mesma mensagem padrão e para a execução
        $_SESSION['flash_message'] = 'Se o seu e-mail estiver cadastrado, um link de recuperação foi enviado.';
        return $response->withHeader('Location', '/esqueceu-senha')->withStatus(302);
    }
    
    // ... (resto dos métodos: exibirFormularioRedefinir, processarRedefinicao, validarToken) ...
    public function exibirFormularioRedefinir($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $token = $params['token'] ?? '';
        $email = $params['email'] ?? '';
        list($isValid, $erro) = $this->validarToken($email, $token);
        ob_start();
        require __DIR__ . '/../View/usuarios/redefinir_senha.php';
        $view = ob_get_clean();
        $response->getBody()->write($view);
        return $response;
    }
    public function processarRedefinicao($request, $response, $args)
    {
        $dados = $request->getParsedBody();
        $token = $dados['token'] ?? '';
        $email = $dados['email'] ?? '';
        $senha = $dados['senha'] ?? '';
        $senhaConfirm = $dados['senha_confirm'] ?? '';
        list($isValid, $erro) = $this->validarToken($email, $token);
        if (!$isValid) {
            $_SESSION['flash_error'] = $erro;
            return $response->withHeader('Location', '/login')->withStatus(302);
        }
        if ($senha !== $senhaConfirm || empty($senha)) {
            $viewData = ['token' => $token, 'email' => $email, 'erro' => 'As senhas não conferem ou estão em branco.'];
            ob_start();
            extract($viewData);
            require __DIR__ . '/../View/usuarios/redefinir_senha.php';
            $view = ob_get_clean();
            $response->getBody()->write($view);
            return $response;
        }
        $novaSenhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $pdo = \getDbConnection();
        $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE email = ?");
        $stmt->execute([$novaSenhaHash, $email]);
        $stmtDel = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmtDel->execute([$email]);
        $_SESSION['flash_success'] = 'Sua senha foi redefinida com sucesso! Você já pode fazer o login.';
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
    private function validarToken($email, $token): array
    {
        if (empty($email) || empty($token)) { return [false, 'Link inválido ou incompleto.']; }
        $pdo = \getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE email = ?");
        $stmt->execute([$email]);
        $resetRequest = $stmt->fetch();
        if (!$resetRequest || !password_verify($token, $resetRequest['token'])) {
            return [false, 'Token inválido ou não encontrado. Por favor, solicite um novo link.'];
        }
        if (time() - strtotime($resetRequest['created_at']) > 3600) { // 1 hora de validade
            return [false, 'Token expirado. Por favor, solicite um novo link.'];
        }
        return [true, null];
    }

    // --- INÍCIO DA FUNCIONALIDADE DE GERENCIAMENTO DE USUÁRIOS ---

    /**
     * Lista todos os usuários cadastrados.
     */
    public function listar($request, $response, $args)
    {
        $pdo = \getDbConnection();
        $stmt = $pdo->query("SELECT id, nome, email, role FROM usuarios ORDER BY nome ASC");
        $usuarios = $stmt->fetchAll();

        $tituloPagina = "Gerenciamento de Usuários";
        // Aponta para a nova view que vamos criar
        $paginaConteudo = __DIR__ . '/../View/usuarios/lista.php';

        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();
        $response->getBody()->write($view);
        return $response;
    }

    /**
     * Exibe o formulário para criar um novo usuário.
     */
    public function exibirFormularioCriacao($request, $response, $args)
    {
        $tituloPagina = "Adicionar Novo Usuário";
        $paginaConteudo = __DIR__ . '/../View/usuarios/formulario.php'; // View de formulário que vamos criar

        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();
        $response->getBody()->write($view);
        return $response;
    }

    /**
     * Processa os dados do formulário e cria um novo usuário no banco.
     */
    public function criar($request, $response, $args)
    {
        $dados = $request->getParsedBody();

        // Validação básica
        if (empty($dados['nome']) || empty($dados['email']) || empty($dados['senha'])) {
            // Em uma aplicação real, usaríamos flash messages para erros
            die("Nome, e-mail e senha são obrigatórios.");
        }
        if ($dados['senha'] !== $dados['senha_confirm']) {
            die("As senhas não conferem.");
        }

        $senhaHash = password_hash($dados['senha'], PASSWORD_DEFAULT);
        $role = $dados['role'] ?? 'user';

        $sql = "INSERT INTO usuarios (nome, email, senha, role) VALUES (?, ?, ?, ?)";
        $pdo = \getDbConnection();
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$dados['nome'], $dados['email'], $senhaHash, $role]);
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) { // Erro de violação de chave única (e-mail duplicado)
                die("Erro: O e-mail informado já está cadastrado.");
            }
            throw $e;
        }

        return $response->withHeader('Location', '/usuarios')->withStatus(302);
    }

    /**
     * Exclui um usuário do banco de dados.
     */
    public function excluir($request, $response, $args)
    {
        $id = $args['id'];

        // Impede que o usuário se auto-exclua
        if ($id == $_SESSION['usuario_id']) {
            die("Você não pode excluir o seu próprio usuário.");
        }

        $pdo = \getDbConnection();
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);

        return $response->withHeader('Location', '/usuarios')->withStatus(302);
    }

    /**
     * Exibe o formulário de edição para um usuário específico.
     */
    public function exibirFormularioEdicao($request, $response, $args)
    {
        $id = $args['id'];
        $pdo = \getDbConnection();
        $stmt = $pdo->prepare("SELECT id, nome, email, role FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $usuario = $stmt->fetch();

        if (!$usuario) {
            // Lidar com usuário não encontrado
            return $response->withHeader('Location', '/usuarios')->withStatus(404);
        }

        $tituloPagina = "Editar Usuário";
        // Aponta para a nova view de edição que vamos criar
        $paginaConteudo = __DIR__ . '/../View/usuarios/formulario_edicao.php';

        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();
        $response->getBody()->write($view);
        return $response;
    }

    /**
     * Processa os dados do formulário e atualiza um usuário no banco.
     */
    public function atualizar($request, $response, $args)
    {
        $id = $args['id'];
        $dados = $request->getParsedBody();

        $nome = $dados['nome'];
        $email = $dados['email'];
        $role = $dados['role'];
        $senha = $dados['senha'];

        $sql = "UPDATE usuarios SET nome = ?, email = ?, role = ?";
        $params = [$nome, $email, $role];

        // Se o campo de senha não estiver vazio, atualiza a senha
        if (!empty($senha)) {
            if ($senha !== $dados['senha_confirm']) {
                die("As senhas não conferem.");
            }
            $sql .= ", senha = ?";
            $params[] = password_hash($senha, PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $pdo = \getDbConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $response->withHeader('Location', '/usuarios')->withStatus(302);
    }

    // --- FIM DA FUNCIONALIDADE DE GERENCIAMENTO DE USUÁRIOS ---


}