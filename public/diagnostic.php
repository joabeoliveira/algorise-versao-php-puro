<?php

// Ativa a exibição de todos os erros para este script
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=utf-8');

echo "INICIANDO DIAGNÓSTICO DO AMBIENTE DE PRODUÇÃO...\n";
echo "===================================================\n\n";

// --- VERIFICAÇÃO 1: Arquivo de Configurações Essenciais ---
echo "PASSO 1: Verificando arquivo de configurações (settings-php-puro.php)...";
$settings_path = __DIR__ . '/../src/settings-php-puro.php';

if (file_exists($settings_path)) {
    echo "  [SUCESSO] O arquivo 'src/settings-php-puro.php' foi encontrado.\n";
    require_once $settings_path;
} else {
    echo "  [FALHA CRÍTICA] O arquivo 'src/settings-php-puro.php' NÃO foi encontrado! O deploy está incompleto.\n";
    exit;
}

// --- VERIFICAÇÃO 2: Arquivo de Funções Helper ---
echo "\nPASSO 2: Verificando o novo arquivo de helpers (helpers.php)...";
$helpers_path = __DIR__ . '/../src/Core/helpers.php';

if (file_exists($helpers_path)) {
    echo "  [SUCESSO] O arquivo 'src/Core/helpers.php' foi encontrado.\n";
    require_once $helpers_path;
} else {
    echo "  [FALHA CRÍTICA] O arquivo 'src/Core/helpers.php' NÃO foi encontrado! Esta é a causa provável do erro.\n";
    exit;
}

// --- VERIFICAÇÃO 3: Existência das Funções ---
echo "\nPASSO 3: Verificando se as funções de formatação foram carregadas...";
if (function_exists('formatarMoeda')) {
    echo "  [SUCESSO] A função formatarMoeda() existe.\n";
} else {
    echo "  [FALHA] A função formatarMoeda() NÃO existe.\n";
}
if (function_exists('formatarData')) {
    echo "  [SUCESSO] A função formatarData() existe.\n";
} else {
    echo "  [FALHA] A função formatarData() NÃO existe.\n";
}
if (function_exists('formatarDataHora')) {
    echo "  [SUCESSO] A função formatarDataHora() existe.\n";
} else {
    echo "  [FALHA] A função formatarDataHora() NÃO existe.\n";
}

// --- VERIFICAÇÃO 4: Conexão com o Banco de Dados ---
echo "\nPASSO 4: Tentando conectar ao banco de dados...";
if (function_exists('getDbConnection')) {
    try {
        $pdo = getDbConnection();
        if ($pdo) {
            echo "  [SUCESSO] Conexão com o banco de dados estabelecida com sucesso!\n";
            // Tenta uma query simples
            $stmt = $pdo->query('SELECT 1');
            if ($stmt) {
                echo "  [SUCESSO] Query de teste executada com sucesso.\n";
            } else {
                echo "  [FALHA] Conectou, mas não conseguiu executar uma query de teste.\n";
            }
        } else {
            echo "  [FALHA] A função getDbConnection() retornou um valor nulo.\n";
        }
    } catch (Exception $e) {
        echo "  [FALHA CRÍTICA] A conexão com o banco de dados falhou!\n";
        echo "      ERRO: " . $e->getMessage() . "\n";
    }
} else {
    echo "  [FALHA] A função getDbConnection() não foi encontrada.\n";
}

echo "\n===================================================\n";
echo "DIAGNÓSTICO CONCLUÍDO.\n";

?>