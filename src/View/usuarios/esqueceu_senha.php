<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: #f0f2f5; } .form-card { max-width: 450px; width: 100%; } </style>
</head>
<body>
    <div class="card form-card shadow-sm">
        <div class="card-body p-5">
            <h3 class="card-title text-center mb-2">Recuperar Senha</h3>
            <p class="text-muted text-center mb-4">Digite seu e-mail para receber as instruções de redefinição.</p>
            
            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="alert alert-info"><?= $_SESSION['flash_message']; ?></div>
                <?php unset($_SESSION['flash_message']); ?>
            <?php endif; ?>

            <form action="/esqueceu-senha" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail cadastrado</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Enviar Link de Recuperação</button>
                </div>
                 <div class="text-center mt-3">
                    <a href="/login">Voltar para o Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>