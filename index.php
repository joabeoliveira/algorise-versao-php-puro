<?php
/**
 * Redirecionamento Automático para a Pasta Public
 * 
 * Este arquivo redireciona automaticamente para a pasta public/
 * onde está o verdadeiro ponto de entrada da aplicação.
 */

// Verifica se estamos acessando pela raiz
if (basename($_SERVER['SCRIPT_NAME']) === 'index.php' && dirname($_SERVER['SCRIPT_NAME']) !== '/public') {
    // Constrói a URL para a pasta public
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['REQUEST_URI']);
    
    // Remove barras extras
    $path = rtrim($path, '/');
    
    // Monta a URL de redirecionamento
    $redirectUrl = $protocol . '://' . $host . $path . '/public/';
    
    // Redireciona permanentemente
    header("Location: $redirectUrl", true, 301);
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecionando... - Algorise</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .container {
            text-align: center;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }
        .logo {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .spinner {
            margin: 1rem auto;
            width: 40px;
            height: 40px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .message {
            margin-top: 1rem;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">🚀</div>
        <h2>Algorise</h2>
        <div class="spinner"></div>
        <p class="message">Redirecionando para a aplicação...</p>
        <small>Se não foi redirecionado automaticamente, <a href="public/" style="color: #fff;">clique aqui</a></small>
    </div>
    
    <script>
        // Redirecionamento JavaScript como fallback
        setTimeout(function() {
            if (!document.hidden) {
                window.location.href = 'public/';
            }
        }, 2000);
    </script>
</body>
</html>