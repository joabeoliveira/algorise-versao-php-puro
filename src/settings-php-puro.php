<?php
/**
 * Configurações do Sistema - Versão PHP Puro
 * Mantém apenas as dependências essenciais
 */

// Configurações de erro baseadas no ambiente
if (isset($_SERVER['GAE_ENV']) || ($_ENV['APP_ENV'] ?? 'development') === 'production') {
    // Produção - ocultar erros
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 1);
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
} else {
    // Desenvolvimento - mostrar todos os erros
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Define timezone padrão
date_default_timezone_set('America/Sao_Paulo');

// Carrega o autoloader do Composer (apenas para phpdotenv)
require __DIR__ . '/../vendor/autoload.php';
// Importa helper de segredos se disponível
use Joabe\Buscaprecos\Core\Secrets;

// Carrega as variáveis de ambiente do arquivo .env (apenas em desenvolvimento)
// No Google App Engine, as variáveis vêm do app.yaml
if (!isset($_SERVER['GAE_ENV']) && !isset($_SERVER['GOOGLE_CLOUD_PROJECT'])) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    try {
        $dotenv->load();
    } catch (Exception $e) {
        // Se não conseguir carregar o .env, usa valores padrão ou falha graciosamente
        if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
            error_log("Aviso: Arquivo .env não encontrado. Configure as variáveis de ambiente.");
        }
    }
}

/**
 * Função para retornar uma instância da conexão PDO com o banco de dados.
 * Funciona automaticamente tanto no desenvolvimento local quanto no Google Cloud.
 * @return PDO
 */
function getDbConnection(): PDO
{
    // Configurações do banco baseadas no ambiente
    $dbname = $_ENV['DB_DATABASE'] ?? 'algorise';
    $user = $_ENV['DB_USER'] ?? 'root';
    // Permite configurar o nome do segredo via env (padrão: db-password)
    $dbPasswordSecret = $_ENV['DB_PASSWORD_SECRET'] ?? 'db-password';
    // Em produção, busca senha segura no Secret Manager; local usa .env
    $pass = isProduction()
        ? (class_exists(Secrets::class) ? (Secrets::get($dbPasswordSecret, 'DB_PASSWORD') ?? ($_ENV['DB_PASSWORD'] ?? '')) : ($_ENV['DB_PASSWORD'] ?? ''))
        : ($_ENV['DB_PASSWORD'] ?? '');
    $charset = 'utf8mb4';
    
    // Log diagnóstico: apenas indica presença de senha (sem expor valor)
    try {
        logarEvento('info', 'DB password presente? ' . (!empty($pass) ? 'sim' : 'nao') . ' | segredo: ' . $dbPasswordSecret);
    } catch (\Throwable $t) {
        // ignora
    }
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        if (isAppEngine()) {
            // Google App Engine - usar Cloud SQL via Unix Socket
            $connectionName = $_ENV['CLOUD_SQL_CONNECTION_NAME'] ?? '';
            if (empty($connectionName)) {
                logarEvento('error', 'A variável de ambiente CLOUD_SQL_CONNECTION_NAME não está definida no App Engine.');
                throw new \PDOException('Configuração de banco de dados incompleta para o ambiente de produção.', 500);
            }
            $socketDir = $_ENV['DB_SOCKET_DIR'] ?? '/cloudsql';
            $dsn = "mysql:unix_socket=$socketDir/$connectionName;dbname=$dbname;charset=$charset";
            
            logarEvento('info', "Conectando ao Cloud SQL via Unix Socket: $socketDir/$connectionName");
            
        } elseif (isProduction()) {
            // Produção não-AppEngine (Cloud Run, Compute Engine, etc)
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $port = $_ENV['DB_PORT'] ?? 3306;
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
            
            logarEvento('info', "Conectando ao MySQL em produção: $host:$port");
            
        } else {
            // Desenvolvimento local (XAMPP, Docker, etc)
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $port = $_ENV['DB_PORT'] ?? 3306;
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
            
            // Em desenvolvimento, permite conexão sem senha
            if (empty($pass) && $user === 'root') {
                logarEvento('debug', "Conectando ao MySQL local (XAMPP): $host:$port");
            }
        }

        $pdo = new PDO($dsn, $user, $pass, $options);
        
        logarEvento('info', "Conexão com banco estabelecida com sucesso - Ambiente: " . getEnvironment());
        return $pdo;
        
    } catch (\PDOException $e) {
        $errorMsg = "Erro de conexão com banco de dados: " . $e->getMessage();
        logarEvento('error', $errorMsg);
        
        if (isDevelopment()) {
            // Em desenvolvimento, mostra o erro detalhado
            throw new \PDOException($errorMsg, (int)$e->getCode());
        } else {
            // Em produção, erro genérico por segurança
            throw new \PDOException('Erro de conexão com banco de dados', 500);
        }
    }
}

/**
 * Aplica uma máscara a uma string.
 * Ex: formatarString("11222333000199", "##.###.###/####-##")
 *
 * @param string $string A string de entrada (apenas dígitos).
 * @param string $mascara A máscara a ser aplicada, usando '#' como placeholder.
 * @return string A string formatada.
 */
function formatarString($string, $mascara)
{
    $string = preg_replace('/\D/', '', $string); // Remove tudo que não for dígito
    $tamanhoString = strlen($string);
    $posicaoString = 0;
    $resultado = '';

    for ($i = 0; $i < strlen($mascara); $i++) {
        if ($posicaoString >= $tamanhoString) {
            break; // Não há mais dígitos na string
        }

        if ($mascara[$i] === '#') {
            $resultado .= $string[$posicaoString];
            $posicaoString++;
        } else {
            $resultado .= $mascara[$i];
        }
    }

    return $resultado;
}

/**
 * Formata um valor monetário
 */
function formatarMoeda($valor): string
{
    return 'R$ ' . number_format((float)$valor, 2, ',', '.');
}

/**
 * Formata uma data para o padrão brasileiro
 */
function formatarData($data): string
{
    if (empty($data)) return '';
    
    $timestamp = is_numeric($data) ? $data : strtotime($data);
    return date('d/m/Y', $timestamp);
}

/**
 * Formata uma data e hora para o padrão brasileiro
 */
function formatarDataHora($dataHora): string
{
    if (empty($dataHora)) return '';
    
    $timestamp = is_numeric($dataHora) ? $dataHora : strtotime($dataHora);
    return date('d/m/Y H:i', $timestamp);
}

/**
 * Função para sanitizar entrada do usuário
 */
function sanitizar($input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validação simples de email
 */
function validarEmail($email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validação simples de CNPJ
 */
function validarCnpj($cnpj): bool
{
    $cnpj = preg_replace('/\D/', '', $cnpj);
    
    if (strlen($cnpj) != 14) return false;
    
    // Verifica sequências iguais
    if (preg_match('/(\d)\1{13}/', $cnpj)) return false;
    
    // Validação dos dígitos verificadores
    for ($t = 12; $t < 14; $t++) {
        $d = 0;
        $c = 0;
        for ($m = $t - 7; $m >= 2; $m--, $c++) {
            $d += $cnpj[$c] * $m;
        }
        for ($m = 9; $m >= 2; $m--, $c++) {
            $d += $cnpj[$c] * $m;
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cnpj[$c] != $d) {
            return false;
        }
    }
    
    return true;
}

/**
 * Gera um token aleatório
 */
function gerarToken($length = 32): string
{
    return bin2hex(random_bytes($length));
}

/**
 * Retorna o caminho de storage baseado no ambiente
 */
function getStoragePath($subpath = ''): string
{
    if (isAppEngine()) {
        // Google App Engine - usar Cloud Storage
        $bucket = $_ENV['STORAGE_BUCKET'] ?? 'algorise-storage';
        $basePath = "gs://$bucket/";
    } elseif (isProduction()) {
        // Produção não-AppEngine - pasta do servidor
        $basePath = $_ENV['STORAGE_PATH'] ?? '/var/www/storage/';
    } else {
        // Desenvolvimento local - pasta relativa
        $basePath = __DIR__ . '/../storage/';
    }
    
    return $basePath . ltrim($subpath, '/');
}

/**
 * Retorna URL pública para arquivos
 */
function getPublicStorageUrl($filepath): string
{
    if (isAppEngine()) {
        // Google App Engine - URL do Cloud Storage
        $bucket = $_ENV['STORAGE_BUCKET'] ?? 'algorise-storage';
        return "https://storage.googleapis.com/$bucket/" . ltrim($filepath, '/');
    } else {
        // Desenvolvimento e produção tradicional - URL local
        $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost:8080';
        return $baseUrl . '/storage/' . ltrim($filepath, '/');
    }
}

/**
 * Salva arquivo no storage apropriado
 */
function salvarArquivo($conteudo, $caminho): bool
{
    $fullPath = getStoragePath($caminho);
    
    try {
        if (isAppEngine()) {
            // Google Cloud Storage
            $context = stream_context_create();
            return file_put_contents($fullPath, $conteudo, false, $context) !== false;
        } else {
            // Sistema de arquivos local
            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            return file_put_contents($fullPath, $conteudo) !== false;
        }
    } catch (Exception $e) {
        errorLog("Erro ao salvar arquivo $caminho: " . $e->getMessage());
        return false;
    }
}

/**
 * Lê arquivo do storage
 */
function lerArquivo($caminho): string|false
{
    $fullPath = getStoragePath($caminho);
    
    try {
        return file_get_contents($fullPath);
    } catch (Exception $e) {
        errorLog("Erro ao ler arquivo $caminho: " . $e->getMessage());
        return false;
    }
}

/**
 * Detecta se está rodando em produção (Google Cloud)
 */
function isProduction(): bool
{
    return isset($_SERVER['GAE_ENV']) || 
           isset($_SERVER['GOOGLE_CLOUD_PROJECT']) ||
           ($_ENV['APP_ENV'] ?? 'development') === 'production';
}

/**
 * Detecta se está rodando no Google App Engine
 */
function isAppEngine(): bool
{
    return isset($_SERVER['GAE_ENV']);
}

/**
 * Detecta se está rodando em desenvolvimento local
 */
function isDevelopment(): bool
{
    return !isProduction();
}

/**
 * Retorna o ambiente atual
 */
function getEnvironment(): string
{
    if (isAppEngine()) return 'gcp-appengine';
    if (isProduction()) return 'production';
    return 'development';
}

/**
 * Função para debug (apenas em desenvolvimento)
 */
function debug($data): void
{
    if (isDevelopment()) {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
    }
}

/**
 * Função para logs híbrida - funciona no desenvolvimento e produção
 */
function logarEvento($nivel, $mensagem, $contexto = []): void
{
    $timestamp = date('Y-m-d H:i:s');
    $contextoStr = empty($contexto) ? '' : ' ' . json_encode($contexto);
    $environment = getEnvironment();
    $logMessage = "[$timestamp] [$environment] $nivel: $mensagem$contextoStr";
    
    if (isAppEngine()) {
        // Google App Engine - usar error_log que vai para Cloud Logging
        error_log($logMessage);
        
    } elseif (isProduction()) {
        // Produção não-AppEngine - usar syslog
        openlog('algorise', LOG_PID | LOG_PERROR, LOG_USER);
        $priority = match(strtolower($nivel)) {
            'debug' => LOG_DEBUG,
            'info' => LOG_INFO,
            'warning' => LOG_WARNING,
            'error' => LOG_ERR,
            default => LOG_INFO
        };
        syslog($priority, $logMessage);
        closelog();
        
    } else {
        // Desenvolvimento local - arquivo de log
        $logFile = __DIR__ . '/../storage/logs/app-' . date('Y-m-d') . '.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logEntry = $logMessage . "\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // Em desenvolvimento, também mostra no erro do PHP para debug
        if (strtolower($nivel) === 'error' || strtolower($nivel) === 'warning') {
            error_log($logMessage);
        }
    }
}

/**
 * Função para log de debug (apenas em desenvolvimento)
 */
function debugLog($mensagem, $contexto = []): void
{
    if (isDevelopment()) {
        logarEvento('debug', $mensagem, $contexto);
    }
}

/**
 * Função para log de erro sempre (todos os ambientes)
 */
function errorLog($mensagem, $contexto = []): void
{
    logarEvento('error', $mensagem, $contexto);
}

/**
 * Configurações globais da aplicação
 */
define('APP_NAME', $_ENV['APP_NAME'] ?? 'Algorise');
define('APP_VERSION', '2.0.0-php-puro');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');

/**
 * Configurações de upload
 */
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['csv', 'xlsx', 'xls', 'pdf']);

/**
 * Configurações de sessão mais seguras
 * Importante: estas diretivas precisam ser definidas antes de qualquer session_start
 */
ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');

// Detecta HTTPS considerando proxies do App Engine
$isHttps = (
    (isset($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) === 'on') ||
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') ||
    (isset($_SERVER['HTTP_X_APPENGINE_HTTPS']) && strtolower((string)$_SERVER['HTTP_X_APPENGINE_HTTPS']) === 'on')
);

$useSecure = isProduction() || $isHttps;
ini_set('session.cookie_secure', $useSecure ? '1' : '0');

// SameSite moderno para evitar bloqueio de cookie no redirect
if (PHP_VERSION_ID >= 70300) {
    ini_set('session.cookie_samesite', 'Lax');
}

// Em App Engine/produção, garantir save_path gravável
if (isProduction()) {
    $savePath = sys_get_temp_dir(); // geralmente /tmp
    if (is_writable($savePath)) {
        ini_set('session.save_path', $savePath);
    }
}

/**
 * Headers de segurança condicionais baseados no ambiente
 */
function aplicarHeadersSeguranca(): void
{
    if (headers_sent()) {
        return;
    }
    
    // Headers básicos para todos os ambientes
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    
    if (isProduction()) {
        // Headers de segurança para produção
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        $csp = "default-src 'self';" .
               " script-src 'self' 'unsafe-inline' blob: https://cdn.jsdelivr.net https://unpkg.com https://cdnjs.cloudflare.com;" .
               " style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com;" .
               " font-src 'self' data: https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.gstatic.com;" .
               " img-src 'self' data: https:;" .
               " connect-src 'self' https://cdn.jsdelivr.net https://unpkg.com https://abuowxogoiqzbmnvszys.supabase.co;" .
               " form-action 'self';";
        header("Content-Security-Policy: " . $csp);
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        
        // Cache control para produção
        if (strpos($_SERVER['REQUEST_URI'] ?? '', '/css/') !== false || 
            strpos($_SERVER['REQUEST_URI'] ?? '', '/js/') !== false ||
            strpos($_SERVER['REQUEST_URI'] ?? '', '/catmat-search/') !== false) {
            header('Cache-Control: public, max-age=31536000'); // 1 ano para assets
        }
        
    } else {
        // Headers para desenvolvimento
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
}

// Aplicar headers automaticamente
aplicarHeadersSeguranca();