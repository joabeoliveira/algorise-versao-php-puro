<?php
// Login simples sem sistema de rotas para testar

session_start();

// Carrega configurações básicas
try {
    require_once __DIR__ . '/../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();
} catch (Exception $e) {
    die("Erro na configuração: " . $e->getMessage());
}

// Função simples de conexão
function getSimpleDbConnection() {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_DATABASE'] ?? 'buscaprecos';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASSWORD'] ?? '';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        return null; // Retorna null se não conseguir conectar
    }
}

// Processa login
if ($_POST) {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    if ($email && $senha) {
        $pdo = getSimpleDbConnection();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
                $stmt->execute([$email]);
                $usuario = $stmt->fetch();
                
                if ($usuario && password_verify($senha, $usuario['senha'])) {
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['usuario_nome'] = $usuario['nome'];
                    echo "<div style='background:#d4edda; padding:10px; margin:10px 0; border-radius:5px;'>
                            ✅ Login realizado com sucesso! Bem-vindo, {$usuario['nome']}!
                          </div>";
                } else {
                    echo "<div style='background:#f8d7da; padding:10px; margin:10px 0; border-radius:5px;'>
                            ❌ Email ou senha incorretos.
                          </div>";
                }
            } catch (Exception $e) {
                echo "<div style='background:#fff3cd; padding:10px; margin:10px 0; border-radius:5px;'>
                        ⚠️ Erro na consulta: {$e->getMessage()}
                      </div>";
            }
        } else {
            echo "<div style='background:#fff3cd; padding:10px; margin:10px 0; border-radius:5px;'>
                    ⚠️ Não foi possível conectar ao banco de dados
                  </div>";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Simples - Teste</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 400px; 
            margin: 50px auto; 
            padding: 20px; 
            background: #f5f5f5; 
        }
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>🔑 Login Simples - Teste</h2>
        
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <div style='background:#d4edda; padding:15px; border-radius:5px; margin:10px 0;'>
                <h3>✅ Você está logado!</h3>
                <p>ID: <?= $_SESSION['usuario_id'] ?></p>
                <p>Nome: <?= $_SESSION['usuario_nome'] ?></p>
                <a href="?logout=1" style="color:#dc3545;">🚪 Sair</a>
            </div>
            
            <?php 
            // Logout simples
            if (isset($_GET['logout'])) {
                session_destroy();
                header('Location: /login-simples.php');
                exit;
            }
            ?>
            
        <?php else: ?>
            <form method="post">
                <input type="email" name="email" placeholder="Seu e-mail" required>
                <input type="password" name="senha" placeholder="Sua senha" required>
                <button type="submit">Entrar</button>
            </form>
            
            <p style="font-size:12px; color:#666; margin-top:20px;">
                💡 <strong>Dica:</strong> Se você tem o sistema antigo funcionando, use as mesmas credenciais de login.
            </p>
        <?php endif; ?>
        
        <hr style="margin:20px 0;">
        <p><a href="/debug.php">🔍 Voltar ao Debug</a></p>
        <p><a href="/sistema.php">🏠 Sistema Principal</a></p>
    </div>
</body>
</html>