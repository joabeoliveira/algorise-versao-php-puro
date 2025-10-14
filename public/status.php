<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/settings-php-puro.php';

use Joabe\Buscaprecos\Core\Secrets;

header('Content-Type: application/json');

$data = [
    'app' => [
        'name' => APP_NAME,
        'version' => APP_VERSION,
        'env' => getEnvironment(),
    ],
    'php' => [
        'version' => PHP_VERSION,
        'extensions' => [
            'pdo_mysql' => extension_loaded('pdo_mysql'),
        ],
    ],
];

// Valida acesso a Secret Manager (não mostra valores)
try {
    $hasDbSecret = class_exists(Secrets::class) && (Secrets::get('db-password') !== null);
    $data['secrets'] = [
        'db-password' => $hasDbSecret ? 'ok' : 'not-found',
    ];
} catch (Throwable $e) {
    $data['secrets_error'] = $e->getMessage();
}

// Teste rápido de conexão (sem vazar erro sensível)
try {
    $pdo = getDbConnection();
    $data['database'] = 'ok';
} catch (Throwable $e) {
    $data['database'] = 'error';
}

echo json_encode($data);