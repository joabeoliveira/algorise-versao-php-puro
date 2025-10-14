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

    /**
     * Obtém um segredo pelo nome lógico. Em produção (GCP), lê do Secret Manager.
     * Em desenvolvimento/local, usa $_ENV.
     *
     * Exemplos de nomes: 'db-password', 'mail-username', 'mail-password', 'supabase-anon-key'.
     */
    public static function get(string $name, ?string $defaultEnvKey = null): ?string
    {
        // Cache rápido
        if (isset(self::$cache[$name])) {
            return self::$cache[$name];
        }

        // Fallback: chave de ambiente equivalente (ex: DB_PASSWORD)
        $envKey = $defaultEnvKey ?? strtoupper(str_replace(['-', '.'], ['_', '_'], $name));

        // Ambiente local/desenvolvimento: usa .env
        if (!self::isGcpRuntime()) {
            $value = $_ENV[$envKey] ?? null;
            return self::$cache[$name] = $value;
        }

        // Em GCP, tentar Secret Manager
        try {
            $client = self::getClient();
            if (!$client) {
                // Se não conseguiu client (ex: lib não disponível), cai para env
                return self::$cache[$name] = ($_ENV[$envKey] ?? null);
            }

            $projectId = getenv('GOOGLE_CLOUD_PROJECT') ?: ($_ENV['GOOGLE_CLOUD_PROJECT'] ?? null);
            if (!$projectId) {
                // Como último recurso, pega do appspot
                $projectId = getenv('GCLOUD_PROJECT') ?: null;
            }
            if (!$projectId) {
                if (function_exists('error_log')) {
                    error_log("[Secrets] ProjectId não resolvido ao buscar '{$name}'");
                }
                return self::$cache[$name] = ($_ENV[$envKey] ?? null);
            }

            $version = 'latest';
            $secretName = $client->secretVersionName($projectId, $name, $version);
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
            if (function_exists('error_log')) {
                error_log("[Secrets] Segredo '{$name}' obtido. Vazio? " . (empty($value) ? 'sim' : 'nao'));
            }
            return self::$cache[$name] = $value ?: ($_ENV[$envKey] ?? null);
        } catch (\Throwable $e) {
            // Em caso de erro, fallback silencioso para env
            if (function_exists('error_log')) {
                error_log('[Secrets] Falha ao acessar Secret Manager: ' . $e->getMessage());
            }
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
}
