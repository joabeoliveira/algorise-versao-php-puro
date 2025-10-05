<?php

namespace Joabe\Buscaprecos\Core;

/**
 * Router simples em PHP puro
 * Substitui o Slim Framework com funcionalidade básica de roteamento
 */
class Router
{
    private array $routes = [];
    private array $middlewares = [];
    
    public function __construct()
    {
        // Inicia a sessão se não estiver ativa
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Adiciona uma rota GET
     */
    public function get(string $path, $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }
    
    /**
     * Adiciona uma rota POST
     */
    public function post(string $path, $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }
    
    /**
     * Adiciona middleware global
     */
    public function addMiddleware(callable $middleware): void
    {
        $this->middlewares[] = $middleware;
    }
    
    /**
     * Cria um grupo de rotas com middleware específico
     */
    public function group(string $prefix, callable $callback, callable $middleware = null): void
    {
        $originalRoutes = count($this->routes);
        
        // Executa o callback para adicionar as rotas do grupo
        $callback($this);
        
        // Se há middleware, aplica às rotas recém-adicionadas
        if ($middleware) {
            for ($i = $originalRoutes; $i < count($this->routes); $i++) {
                $this->routes[$i]['group_middleware'] = $middleware;
            }
        }
    }
    
    /**
     * Processa a requisição atual
     */
    public function run(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        
        // Executa middlewares globais
        foreach ($this->middlewares as $middleware) {
            $result = $middleware();
            if ($result === false) {
                return; // Middleware interceptou a requisição
            }
        }
        
        // Procura pela rota correspondente
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchRoute($route['path'], $path, $params)) {
                
                // Executa middleware específico do grupo se existir
                if (isset($route['group_middleware'])) {
                    $result = $route['group_middleware']();
                    if ($result === false) {
                        return; // Middleware do grupo interceptou
                    }
                }
                
                // Executa o handler da rota
                $this->executeHandler($route['handler'], $params);
                return;
            }
        }
        
        // Rota não encontrada
        $this->notFound();
    }
    
    /**
     * Adiciona uma rota ao array de rotas
     */
    private function addRoute(string $method, string $path, $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }
    
    /**
     * Verifica se uma rota corresponde ao caminho atual
     */
    private function matchRoute(string $routePath, string $currentPath, &$params = []): bool
    {
        $params = [];
        
        // Converte o padrão da rota em regex
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';
        
        if (preg_match($pattern, $currentPath, $matches)) {
            // Extrai os parâmetros da URL
            preg_match_all('/\{([^}]+)\}/', $routePath, $paramNames);
            
            for ($i = 1; $i < count($matches); $i++) {
                $paramName = $paramNames[1][$i - 1];
                $params[$paramName] = $matches[$i];
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Executa o handler de uma rota
     */
    private function executeHandler($handler, array $params): void
    {
        if (is_callable($handler)) {
            // Se for uma função anônima
            $handler($params);
        } elseif (is_array($handler) && count($handler) === 2) {
            // Se for [Controller::class, 'metodo']
            [$class, $method] = $handler;
            
            if (class_exists($class)) {
                $controller = new $class();
                if (method_exists($controller, $method)) {
                    $controller->$method($params);
                } else {
                    $this->error("Método $method não encontrado na classe $class");
                }
            } else {
                $this->error("Classe $class não encontrada");
            }
        } else {
            $this->error("Handler inválido");
        }
    }
    
    /**
     * Redireciona para uma URL
     */
    public static function redirect(string $url, int $code = 302): void
    {
        header("Location: $url", true, $code);
        exit;
    }
    
    /**
     * Retorna dados em JSON
     */
    public static function json(array $data, int $code = 200): void
    {
        header('Content-Type: application/json', true, $code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Pega dados do POST
     */
    public static function getPostData(): array
    {
        return $_POST ?? [];
    }
    
    /**
     * Pega dados do GET
     */
    public static function getQueryData(): array
    {
        return $_GET ?? [];
    }
    
    /**
     * Página não encontrada
     */
    private function notFound(): void
    {
        http_response_code(404);
        
        // Se for uma requisição Ajax ou API, retorna JSON
        $contentType = $_SERVER['HTTP_ACCEPT'] ?? '';
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        $isApi = str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/');
        
        if ($isAjax || $isApi || str_contains($contentType, 'application/json')) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => 'Endpoint não encontrado'
            ]);
        } else {
            echo "404 - Página não encontrada";
        }
    }
    
    /**
     * Erro interno
     */
    private function error(string $message): void
    {
        http_response_code(500);
        echo "Erro: $message";
    }
}