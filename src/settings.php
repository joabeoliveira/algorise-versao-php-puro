<?php
/**
 * Configurações do Sistema - Versão PHP Puro
 * Mantém apenas as dependências essenciais
 */

// Habilita a exibição de todos os erros (bom para desenvolvimento)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define timezone padrão
date_default_timezone_set('America/Sao_Paulo');

// Carrega o autoloader do Composer (apenas para phpdotenv)
require __DIR__ . '/../vendor/autoload.php';

// Carrega as variáveis de ambiente do arquivo .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
try {
    $dotenv->load();
} catch (Exception $e) {
    // Se não conseguir carregar o .env, usa valores padrão ou falha graciosamente
    if ($_ENV['APP_ENV'] ?? 'production' !== 'production') {
        echo "Aviso: Arquivo .env não encontrado. Configure as variáveis de ambiente.\n";
    }
}

/**
 * Função para retornar uma instância da conexão PDO com o banco de dados.
 * @return PDO
 */
function getDbConnection(): PDO
{
    // Configurações do banco - usa .env se disponível, senão valores padrão
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_DATABASE'] ?? 'buscaprecos';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASSWORD'] ?? '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lança exceções em caso de erro
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retorna os resultados como arrays associativos
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Usa 'prepared statements' reais
    ];

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        // Em um ambiente de produção, você logaria este erro em vez de exibi-lo
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
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
 * Função para debug (apenas em desenvolvimento)
 */
function debug($data): void
{
    if (($_ENV['APP_ENV'] ?? 'production') === 'development') {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
    }
}

/**
 * Função para logs simples
 */
function logarEvento($nivel, $mensagem, $contexto = []): void
{
    $logFile = __DIR__ . '/../storage/logs/app-' . date('Y-m-d') . '.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $contextoStr = empty($contexto) ? '' : ' ' . json_encode($contexto);
    $logEntry = "[$timestamp] $nivel: $mensagem$contextoStr\n";
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
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
 */
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
}

/**
 * Headers de segurança básicos (apenas se for requisição web)
 */
if (php_sapi_name() !== 'cli' && !headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

/**
 * Retorna as configurações do banco de dados
 * Para compatibilidade com arquivos que esperam um array de configuração
 */
return [
    'db' => [
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'dbname' => $_ENV['DB_DATABASE'] ?? 'algorise',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'pass' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => 'utf8mb4'
    ],
    'app' => [
        'name' => APP_NAME,
        'version' => APP_VERSION,
        'env' => APP_ENV
    ]
];