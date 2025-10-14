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
    $dbSecretName = $_ENV['DB_PASSWORD_SECRET'] ?? 'db-password';
    $hasDbSecret = class_exists(Secrets::class) && (Secrets::get($dbSecretName) !== null);
    $secretsDiag = method_exists(Secrets::class, 'getLastInfo') ? Secrets::getLastInfo() : null;
    $data['secrets'] = [
        'db-password-secret-name' => $dbSecretName,
        'db-password' => $hasDbSecret ? 'ok' : 'not-found',
        'diag' => $secretsDiag ? [
            'runtime' => $secretsDiag['runtime'] ?? null,
            'projectId' => $secretsDiag['projectId'] ?? null,
            'clientClassExists' => $secretsDiag['clientClassExists'] ?? null,
            'clientUsed' => $secretsDiag['clientUsed'] ?? null,
            'clientEmpty' => $secretsDiag['clientEmpty'] ?? null,
            'clientError' => $secretsDiag['clientError'] ?? null,
            'restTried' => $secretsDiag['restTried'] ?? null,
            'restTokenOk' => $secretsDiag['restTokenOk'] ?? null,
            'restError' => $secretsDiag['restError'] ?? null,
            'result' => $secretsDiag['result'] ?? null,
        ] : null,
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