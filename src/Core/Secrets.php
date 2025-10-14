<?php

namespace Joabe\Buscaprecos\Core;

/**
 * Helper para obter segredos do Google Secret Manager com cache em memória
 * e fallback para variáveis de ambiente locais (.env).
 */
class Secrets
{
    private static array $cache = [];
    private static bool $clientInitTried = false;
    /** @var object|null */
    private static $client = null;
    private static array $lastInfo = [];

    /**
     * Obtém um segredo pelo nome lógico. Em produção (GCP), lê do Secret Manager.
     * Em desenvolvimento/local, usa $_ENV.
     *
     * Exemplos de nomes: 'db-password', 'mail-username', 'mail-password', 'supabase-anon-key'.
     */
    public static function get(string $name, ?string $defaultEnvKey = null): ?string
    {
        self::$lastInfo = [
            'name' => $name,
            'runtime' => self::isGcpRuntime(),
            'projectId' => null,
            'clientClassExists' => class_exists('\\Google\\Cloud\\SecretManager\\V1\\SecretManagerServiceClient'),
            'clientUsed' => false,
            'clientError' => null,
            'clientEmpty' => null,
            'restTried' => false,
            'restError' => null,
            'restTokenOk' => null,
            'result' => null,
        ];
        // Cache rápido
        if (isset(self::$cache[$name])) {
            return self::$cache[$name];
        }

        // Fallback: chave de ambiente equivalente (ex: DB_PASSWORD)
        $envKey = $defaultEnvKey ?? strtoupper(str_replace(['-', '.'], ['_', '_'], $name));

        // Ambiente local/desenvolvimento: usa .env
        if (!self::isGcpRuntime()) {
            $value = $_ENV[$envKey] ?? null;
            self::$lastInfo['result'] = $value !== null ? 'env' : 'not-found';
            return self::$cache[$name] = $value;
        }

        // Em GCP, tentar Secret Manager
        try {
            $client = self::getClient();
            if (!$client) {
                // Se não conseguiu client (ex: lib não disponível), cai para env
                self::$lastInfo['clientUsed'] = false;
                self::$lastInfo['result'] = isset($_ENV[$envKey]) ? 'env' : 'not-found';
                return self::$cache[$name] = ($_ENV[$envKey] ?? null);
            }

            // Permite override de projeto via env para secrets cross-project
            $projectOverride = getenv('SECRET_MANAGER_PROJECT') ?: (getenv('SECRETS_PROJECT') ?: ($_ENV['SECRET_MANAGER_PROJECT'] ?? ($_ENV['SECRETS_PROJECT'] ?? null)));
            $projectId = $projectOverride ?: (getenv('GOOGLE_CLOUD_PROJECT') ?: ($_ENV['GOOGLE_CLOUD_PROJECT'] ?? null));
            if (!$projectId) {
                // Como último recurso, pega do appspot
                $projectId = getenv('GCLOUD_PROJECT') ?: null;
            }
            // Tenta extrair do GAE_APPLICATION (formato e~projeto)
            if (!$projectId && isset($_SERVER['GAE_APPLICATION'])) {
                $parts = explode('~', (string)$_SERVER['GAE_APPLICATION']);
                if (count($parts) === 2 && !empty($parts[1])) {
                    $projectId = $parts[1];
                }
            }
            // Último fallback: metadata server
            if (!$projectId) {
                $projectId = self::getProjectIdFromMetadata();
            }
            self::$lastInfo['projectId'] = $projectId;
            if (!$projectId) {
                if (function_exists('error_log')) {
                    error_log("[Secrets] ProjectId não resolvido ao buscar '{$name}'");
                }
                self::$lastInfo['result'] = isset($_ENV[$envKey]) ? 'env' : 'not-found';
                return self::$cache[$name] = ($_ENV[$envKey] ?? null);
            }
            $version = 'latest';
            // Suporta nome totalmente qualificado: projects/{p}/secrets/{s}[/versions/{v}]
            if (str_starts_with($name, 'projects/')) {
                $secretName = str_contains($name, '/versions/') ? $name : ($name . '/versions/' . $version);
            } else {
                $secretName = $client->secretVersionName($projectId, $name, $version);
            }
            if (function_exists('error_log')) {
                error_log("[Secrets] Buscando segredo '{$name}' no projeto '{$projectId}' (latest)");
            }
            $response = $client->accessSecretVersion($secretName);
            // Em PHP, getData() retorna o conteúdo do segredo como string (bytes)
            $payload = $response->getPayload()->getData();
            $value = $payload !== null ? (string)$payload : null;
            // Normaliza string
            if ($value !== null) {
                $value = trim($value);
            }
            self::$lastInfo['clientUsed'] = true;
            self::$lastInfo['clientEmpty'] = empty($value);
            if (function_exists('error_log')) {
                error_log("[Secrets] Segredo '{$name}' obtido. Vazio? " . (empty($value) ? 'sim' : 'nao'));
            }
            if (!empty($value)) {
                self::$lastInfo['result'] = 'client';
                return self::$cache[$name] = $value;
            }
            // Se veio vazio, tenta fallback REST
            $restValue = self::fetchSecretViaRest($projectId, $name);
            if (function_exists('error_log')) {
                error_log("[Secrets] Fallback REST para '{$name}'. Sucesso? " . (!empty($restValue) ? 'sim' : 'nao'));
            }
            self::$lastInfo['restTried'] = true;
            if (!empty($restValue)) {
                self::$lastInfo['result'] = 'rest';
                return self::$cache[$name] = $restValue;
            }
            self::$lastInfo['result'] = isset($_ENV[$envKey]) ? 'env' : 'not-found';
            return self::$cache[$name] = ($_ENV[$envKey] ?? null);
        } catch (\Throwable $e) {
            // Em caso de erro, fallback silencioso para env
            if (function_exists('error_log')) {
                error_log('[Secrets] Falha ao acessar Secret Manager: ' . $e->getMessage());
            }
            self::$lastInfo['clientError'] = $e->getMessage();
            // Tenta REST antes de desistir
            try {
                $projectId = self::getProjectIdFromMetadata();
                if ($projectId) {
                    $restValue = self::fetchSecretViaRest($projectId, $name);
                    if (function_exists('error_log')) {
                        error_log("[Secrets] Fallback REST após exceção para '{$name}'. Sucesso? " . (!empty($restValue) ? 'sim' : 'nao'));
                    }
                    self::$lastInfo['restTried'] = true;
                    if (!empty($restValue)) {
                        self::$lastInfo['result'] = 'rest';
                        return self::$cache[$name] = $restValue;
                    }
                }
            } catch (\Throwable $t) {
                self::$lastInfo['restError'] = $t->getMessage();
            }
            self::$lastInfo['result'] = isset($_ENV[$envKey]) ? 'env' : 'not-found';
            return self::$cache[$name] = ($_ENV[$envKey] ?? null);
        }
    }

    private static function isGcpRuntime(): bool
    {
        return isset($_SERVER['GAE_ENV']) || getenv('GOOGLE_CLOUD_PROJECT') || isset($_SERVER['K_SERVICE']);
    }

    /** @return object|null */
    private static function getClient(): ?object
    {
        if (self::$client !== null || self::$clientInitTried) {
            return self::$client;
        }
        self::$clientInitTried = true;
        try {
            $class = '\\Google\\Cloud\\SecretManager\\V1\\SecretManagerServiceClient';
            if (class_exists($class)) {
                self::$client = new $class();
            } else {
                self::$client = null;
            }
        } catch (\Throwable $e) {
            self::$client = null;
        }
        return self::$client;
    }

    private static function getProjectIdFromMetadata(): ?string
    {
        // Primeiro tenta variável específica do App Engine
        if (isset($_SERVER['GAE_APPLICATION'])) {
            $parts = explode('~', (string)$_SERVER['GAE_APPLICATION']);
            if (count($parts) === 2 && !empty($parts[1])) {
                return $parts[1];
            }
        }
        // Tenta metadata server
        $url = 'http://metadata.google.internal/computeMetadata/v1/project/project-id';
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Metadata-Flavor: Google\r\n",
                'timeout' => 2,
            ]
        ]);
        try {
            $result = @file_get_contents($url, false, $ctx);
            if ($result !== false) {
                return trim($result);
            }
        } catch (\Throwable $e) {
            // ignore
        }
        return null;
    }

    private static function fetchAccessToken(): ?string
    {
        $url = 'http://metadata.google.internal/computeMetadata/v1/instance/service-accounts/default/token';
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Metadata-Flavor: Google\r\n",
                'timeout' => 2,
            ]
        ]);
        try {
            $json = @file_get_contents($url, false, $ctx);
            if ($json === false) return null;
            $data = json_decode($json, true);
            $token = $data['access_token'] ?? null;
            self::$lastInfo['restTokenOk'] = $token ? true : false;
            return $token;
        } catch (\Throwable $e) {
             self::$lastInfo['restTokenOk'] = false;
            return null;
        }
    }

    private static function fetchSecretViaRest(string $projectId, string $name): ?string
    {
        $token = self::fetchAccessToken();
        if (!$token) return null;
        $url = sprintf('https://secretmanager.googleapis.com/v1/projects/%s/secrets/%s/versions/latest:access', urlencode($projectId), urlencode($name));
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Authorization: Bearer {$token}\r\nAccept: application/json\r\n",
                'timeout' => 5,
            ]
        ]);
        try {
            $json = @file_get_contents($url, false, $ctx);
            if ($json === false) return null;
            $data = json_decode($json, true);
            $b64 = $data['payload']['data'] ?? null;
            if (!$b64) return null;
            $value = base64_decode($b64, true);
            return $value !== false ? trim($value) : null;
        } catch (\Throwable $e) {
            self::$lastInfo['restError'] = $e->getMessage();
            return null;
        }
    }

    public static function getLastInfo(): array
    {
        return self::$lastInfo;
    }
}
