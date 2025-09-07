<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Cotações</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style> body { background-color: #f8f9fa; } </style>
</head>
<body>
    <main class="container mt-5">
        <div class="card">
            <div class="card-header fs-4 text-center">
                Busca Preços AI - Portal de Cotações
            </div>
            <div class="card-body p-4">
                <?php 
                    if (isset($paginaConteudo) && file_exists($paginaConteudo)) {
                        include $paginaConteudo;
                    } else {
                        echo "<h1>Erro: Conteúdo não encontrado.</h1>";
                    }
                ?>
            </div>
        </div>
    </main>

    <!-- Script principal (deve conter apenas o essencial) -->
    <script>
    // Função básica para cálculo de totais (será sobrescrita se definida no formulário)
    function calcularTotais() {
        console.log('Função de cálculo disponível');
    }

    </script>
    <script src="https://unpkg.com/imask"></script>
    <script src="/js/masks.js"></script> </body>

</body>
</html>