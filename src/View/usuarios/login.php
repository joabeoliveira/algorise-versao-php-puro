<?php
// Inicia a sessão se ainda não estiver iniciada, para poder acessar a variável de erro
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Algorise</title>
    
    <?php
    // Headers removidos - CSP estava bloqueando tudo
    ?>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            
            /* --- Início da Modificação --- */
            background-image: url('/img/background-login3.jpg'); /* Caminho para a sua imagem */
            background-size: cover; /* Faz a imagem cobrir toda a tela */
            background-position: center; /* Centraliza a imagem */
            background-repeat: no-repeat; /* Evita que a imagem se repita */
            /* --- Fim da Modificação --- */
        }
        .login-card {
            max-width: 420px;
            width: 100%;
            border: none;
            border-radius: 0.75rem;
            
            /* Efeito opcional para destacar o formulário do fundo */
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px); /* Efeito de desfoque no fundo do card */
        }
    </style>
</head>
<body>
    <div class="card login-card shadow">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <i class="bi bi-graph-up-arrow" style="font-size: 3rem; color: #0d6efd;"></i>
                <h3 class="card-title mt-2">Algorise</h3>
                <p class="text-muted">Por favor, insira suas credenciais para continuar.</p>
            </div>
            
            <?php if (isset($_SESSION['flash'])): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($_SESSION['flash']); ?>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <form action="/login" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" required autofocus>
                </div>

                <div class="mb-4">
                    <label for="senha" class="form-label">Senha</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="senha" name="senha" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Entrar</button>
                </div>
            </form>
            <div class="text-center mt-3">
                <a href="/esqueceu-senha">Esqueci minha senha</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#senha');
            const eyeIcon = togglePassword.querySelector('i');

            togglePassword.addEventListener('click', function (e) {
                // Alterna o tipo do input entre 'password' e 'text'
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                
                // Alterna o ícone do botão
                eyeIcon.classList.toggle('bi-eye');
                eyeIcon.classList.toggle('bi-eye-slash');
            });
        });
    </script>
    </body>
</html>