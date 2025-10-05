<?php
/**
 * Script para adaptar todos os controllers para PHP puro
 */

echo "🔧 Adaptando Controllers para PHP Puro...\n\n";

$controllersPath = __DIR__ . '/src/Controller/';
$controllers = [
    'ProcessoController.php',
    'ItemController.php', 
    'PrecoController.php',
    'AnaliseController.php',
    'AcompanhamentoController.php',
    'CotacaoRapidaController.php',
    'CotacaoPublicaController.php'
];

foreach ($controllers as $controller) {
    $filePath = $controllersPath . $controller;
    
    if (!file_exists($filePath)) {
        echo "❌ $controller não encontrado\n";
        continue;
    }
    
    echo "🔄 Processando $controller...\n";
    
    $content = file_get_contents($filePath);
    
    // Substituições básicas
    $content = preg_replace(
        '/public function (\w+)\(\$request, \$response, \$args\)/',
        'public function $1($params = [])',
        $content
    );
    
    $content = preg_replace(
        '/\$dados = \$request->getParsedBody\(\);/',
        '$dados = \\Joabe\\Buscaprecos\\Core\\Router::getPostData();',
        $content
    );
    
    $content = preg_replace(
        '/return \$response->withHeader\([\'"]Location[\'"],\s*[\'"]([^\'"]+)[\'"]\)->withStatus\(\d+\);/',
        '\\Joabe\\Buscaprecos\\Core\\Router::redirect(\'$1\'); return;',
        $content
    );
    
    $content = preg_replace(
        '/\$response->getBody\(\)->write\(\$view\);\s*return \$response;/',
        'echo $view;',
        $content
    );
    
    // Adiciona use statement se não existir
    if (!strpos($content, 'use Joabe\\Buscaprecos\\Core\\Router;')) {
        $content = str_replace(
            '<?php',
            "<?php\n\nuse Joabe\\Buscaprecos\\Core\\Router;",
            $content
        );
    }
    
    // Salva o arquivo
    if (file_put_contents($filePath, $content)) {
        echo "✅ $controller adaptado com sucesso!\n";
    } else {
        echo "❌ Erro ao salvar $controller\n";
    }
}

echo "\n🎉 Adaptação concluída!\n";
?>