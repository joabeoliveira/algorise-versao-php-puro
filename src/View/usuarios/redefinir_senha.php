<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><title>Redefinir Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: #f0f2f5; } .form-card { max-width: 450px; width: 100%; } </style>
</head>
<body>
    <div class="card form-card shadow-sm">
        <div class="card-body p-5">
            <h3 class="card-title text-center mb-4">Crie sua Nova Senha</h3>
            
            <?php if (isset($erro)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                <a href="/login">Voltar para o Login</a>
            <?php else: ?>
                <form action="/redefinir-senha" method="POST">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                    <div class="mb-3">
                        <label for="senha" class="form-label">Nova Senha</label>
                        <input type="password" class="form-control" id="senha" name="senha" required>
                    </div>
                    <div class="mb-3">
                        <label for="senha_confirm" class="form-label">Confirme a Nova Senha</label>
                        <input type="password" class="form-control" id="senha_confirm" name="senha_confirm" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Redefinir Senha</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>