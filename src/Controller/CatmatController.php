<?php

namespace Joabe\Buscaprecos\Controller;

use Joabe\Buscaprecos\Core\Router;

class CatmatController
{
    /**
     * Exibe a página principal de busca CATMAT
     */
    public function busca($params = [])
    {
        // Prepara as variáveis para o layout principal
        $tituloPagina = "Consulta CATMAT - Busca Inteligente";
        $paginaConteudo = __DIR__ . '/../View/catmat/busca.php';
        
        // CSS e JS específicos para esta página
        $cssExtra = '<link rel="stylesheet" href="/css/catmat-search.css">';
        $jsExtra = '<script src="/js/catmat-search.js" defer></script>';

        // Renderiza o layout principal
        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();
        echo $view;
    }

    /**
     * API para pesquisa via AJAX - usa diretamente o Supabase como referência
     */
    public function pesquisar($params = [])
    {
        $inputData = json_decode(file_get_contents('php://input'), true);
        $query = $inputData['query'] ?? '';
        
        if (empty($query)) {
            Router::json(['success' => false, 'message' => 'Query é obrigatória']);
            return;
        }

        try {
            error_log("BUSCA CATMAT: Query recebida = " . $query);
            
            // Usa a mesma lógica da referência que funciona
            $supabaseUrl = 'https://abuowxogoiqzbmnvszys.supabase.co';
            $supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImFidW93eG9nb2lxemJtbnZzenlzIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDkyNTcwNTcsImV4cCI6MjA2NDgzMzA1N30.t6b1vtcZhGfOfibwdWKLDUJq2BoRegH5s6P5_OvRwz8';
            
            // Processa termos separados por +
            $termos = array_map('trim', explode('+', $query));
            $termos = array_filter($termos, function($termo) {
                return !empty($termo);
            });
            
            error_log("Termos processados: " . print_r($termos, true));
            
            // Se há múltiplos termos, usa função específica para múltiplos termos
            if (count($termos) > 1) {
                // Usa função do Supabase para múltiplos termos (como na referência)
                $postData = [
                    'termos' => $termos,
                    'limite' => 100,
                    'offset_val' => 0
                ];
                $rpcFunction = 'buscar_itens_multiplos_termos';
            } else {
                // Busca simples
                $postData = [
                    'texto_busca' => $termos[0],
                    'limite' => 100,
                    'offset_val' => 0
                ];
                $rpcFunction = 'buscar_itens_similares';
            }
            
            $headers = [
                'Content-Type: application/json',
                'apikey: ' . $supabaseKey,
                'Authorization: Bearer ' . $supabaseKey
            ];
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => implode("\r\n", $headers),
                    'content' => json_encode($postData)
                ]
            ]);
            
            error_log("Chamando: " . $rpcFunction . " | Dados: " . json_encode($postData));
            
            $response = file_get_contents($supabaseUrl . '/rest/v1/rpc/' . $rpcFunction, false, $context);
            $data = json_decode($response, true);
            
            error_log("Resposta Supabase: " . ($data ? count($data) . " itens" : "ERRO/VAZIO"));
            
            if (!$data) {
                error_log("ERRO: Nenhum dado retornado do Supabase");
                Router::json(['success' => true, 'data' => ['results' => [], 'total' => 0]]);
                return;
            }
            
            // Se há múltiplos termos e a função específica falhou, filtra manualmente
            if (count($termos) > 1 && $rpcFunction === 'buscar_itens_similares') {
                error_log("Aplicando filtro manual para termos: " . implode(', ', $termos));
                $data = array_filter($data, function($item) use ($termos) {
                    $descricao = strtolower($item['descricao']);
                    foreach ($termos as $termo) {
                        $termoLimpo = strtolower(trim($termo));
                        if (empty($termoLimpo)) continue;
                        if (!str_contains($descricao, $termoLimpo)) {
                            return false;
                        }
                    }
                    return true;
                });
                error_log("Após filtro manual: " . count($data) . " resultados");
            }
            
            // Converte para formato esperado
            $results = [];
            foreach ($data as $item) {
                $results[] = [
                    'catmat' => $item['codigo_catmat'],
                    'descricao' => $item['descricao'],
                    'relevancia' => 85 // Valor fixo por enquanto
                ];
            }
            
            // Limita a 80 resultados finais para mostrar mais opções
            $resultadosLimitados = array_slice($results, 0, 80);
            
            error_log("RESULTADO FINAL: " . count($resultadosLimitados) . " resultados para query: " . $query);
            
            Router::json([
                'success' => true,
                'data' => [
                    'results' => $resultadosLimitados,
                    'total' => count($resultadosLimitados),
                    'pagination' => [
                        'currentPage' => 1,
                        'totalPages' => 1,
                        'totalItems' => count($resultadosLimitados),
                        'itemsPerPage' => 80
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro na busca CATMAT: " . $e->getMessage());
            Router::json(['success' => false, 'message' => 'Erro interno']);
        }
    }

    /**
     * API para sugestões de auto-complete com integração Supabase
     */
    public function sugestoes($params = [])
    {
        $termo = $_GET['q'] ?? $_GET['termo'] ?? '';
        
        if (strlen($termo) < 2) {
            Router::json(['sugestoes' => []]);
            return;
        }

        try {
            // Busca sugestões no Supabase usando a função RPC existente
            $supabaseUrl = 'https://abuowxogoiqzbmnvszys.supabase.co';
            $supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImFidW93eG9nb2lxemJtbnZzenlzIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDkyNTcwNTcsImV4cCI6MjA2NDgzMzA1N30.t6b1vtcZhGfOfibwdWKLDUJq2BoRegH5s6P5_OvRwz8';
            
            // Prepara dados para a chamada RPC
            $postData = [
                'texto_busca' => $termo,
                'limite' => 15,
                'offset_val' => 0
            ];
            
            $headers = [
                'Content-Type: application/json',
                'apikey: ' . $supabaseKey,
                'Authorization: Bearer ' . $supabaseKey
            ];
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => implode("\r\n", $headers),
                    'content' => json_encode($postData)
                ]
            ]);
            
            $response = file_get_contents($supabaseUrl . '/rest/v1/rpc/buscar_itens_similares', false, $context);
            $data = json_decode($response, true);
            
            if ($data) {
                // Formata as sugestões para o formato esperado
                $sugestoes = [];
                foreach ($data as $item) {
                    $sugestoes[] = [
                        'texto' => $item['codigo_catmat'] . ' - ' . substr($item['descricao'], 0, 80) . '...',
                        'tipo' => 'CATMAT'
                    ];
                }
                
                // Adiciona sugestões de operadores se o termo não os contém
                if (!str_contains($termo, '+') && !str_contains($termo, '-') && !str_contains($termo, '|')) {
                    $sugestoes[] = ['texto' => $termo . ' + DESCARTÁVEL', 'tipo' => 'Operador'];
                    $sugestoes[] = ['texto' => $termo . ' + PLÁSTICO', 'tipo' => 'Operador'];
                    $sugestoes[] = ['texto' => $termo . ' - REUTILIZÁVEL', 'tipo' => 'Operador'];
                }
                
                Router::json($sugestoes);
            } else {
                Router::json(['sugestoes' => []]);
            }
            
        } catch (\Exception $e) {
            error_log("Erro ao buscar sugestões: " . $e->getMessage());
            Router::json(['sugestoes' => []]);
        }
    }

    /**
     * Lista processos disponíveis para adicionar itens
     */
    public function listarProcessos($params = [])
    {
        try {
            $pdo = \getDbConnection();
            
            $stmt = $pdo->prepare("
                SELECT id, numero_processo, nome_processo, status 
                FROM processos 
                ORDER BY data_criacao DESC 
                LIMIT 20
            ");
            
            $stmt->execute();
            $processos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $processosFormatados = [];
            foreach ($processos as $processo) {
                $processosFormatados[] = [
                    'id' => $processo['id'],
                    'nome' => $processo['numero_processo'] . ' - ' . $processo['nome_processo'],
                    'status' => $processo['status']
                ];
            }
            
            Router::json($processosFormatados);
            
        } catch (\Exception $e) {
            error_log("Erro ao listar processos: " . $e->getMessage());
            Router::json([]);
        }
    }

    /**
     * Adiciona item CATMAT a um processo
     */
    public function adicionarItem($params = [])
    {
        $inputData = json_decode(file_get_contents('php://input'), true);
        
        $processoId = $inputData['processo_id'] ?? '';
        $catmat = $inputData['catmat'] ?? '';
        $descricao = $inputData['descricao'] ?? '';
        $quantidade = (int)($inputData['quantidade'] ?? 1);
        $unidade = $inputData['unidade'] ?? 'UN';
        
        if (empty($processoId) || empty($catmat)) {
            Router::json(['success' => false, 'message' => 'Dados obrigatórios não informados']);
            return;
        }
        
        try {
            $pdo = \getDbConnection();
            
            // Verifica se o processo existe
            $stmt = $pdo->prepare("SELECT id, numero_processo FROM processos WHERE id = ?");
            $stmt->execute([$processoId]);
            $processo = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$processo) {
                Router::json(['success' => false, 'message' => 'Processo não encontrado']);
                return;
            }
            
            // Gera próximo número do item
            $stmt = $pdo->prepare("SELECT COALESCE(MAX(numero_item), 0) + 1 as proximo_numero FROM itens WHERE processo_id = ?");
            $stmt->execute([$processoId]);
            $proximoNumero = $stmt->fetchColumn();
            
            // Insere o item
            $stmt = $pdo->prepare("
                INSERT INTO itens (processo_id, numero_item, catmat_catser, descricao, unidade_medida, quantidade)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $processoId,
                $proximoNumero,
                $catmat,
                $descricao,
                $unidade,
                $quantidade
            ]);
            
            $itemId = $pdo->lastInsertId();
            
            Router::json([
                'success' => true, 
                'message' => "CATMAT {$catmat} adicionado ao processo {$processo['numero_processo']}", 
                'item_id' => $itemId
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro ao adicionar item: " . $e->getMessage());
            Router::json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }

    /**
     * Processa operadores de busca da query
     */
    private function processarOperadores($query)
    {
        $termos = [
            'obrigatorios' => [],
            'excluidos' => [],
            'opcionais' => [],
            'frases_exatas' => []
        ];
        
        // Extrair frases exatas primeiro
        preg_match_all('/"([^"]+)"/', $query, $matches);
        if (!empty($matches[1])) {
            $termos['frases_exatas'] = $matches[1];
            $query = preg_replace('/"[^"]+"/', '', $query);
        }
        
        // Dividir em tokens
        $tokens = preg_split('/\s+/', trim($query));
        
        $i = 0;
        while ($i < count($tokens)) {
            $token = $tokens[$i];
            
            if ($token === '+' && isset($tokens[$i + 1])) {
                $termos['obrigatorios'][] = $tokens[$i + 1];
                $i += 2;
            } elseif ($token === '-' && isset($tokens[$i + 1])) {
                $termos['excluidos'][] = $tokens[$i + 1];
                $i += 2;
            } elseif ($token === '|' && isset($tokens[$i + 1])) {
                $termos['opcionais'][] = $tokens[$i + 1];
                $i += 2;
            } elseif (!empty($token) && !in_array($token, ['+', '-', '|'])) {
                $termos['obrigatorios'][] = $token;
                $i++;
            } else {
                $i++;
            }
        }
        
        return $termos;
    }

    /**
     * Busca diretamente no Supabase sem processar operadores primeiro
     */
    private function buscarDiretamenteNoSupabase($query, $page = 1, $limit = 60)
    {
        $supabaseUrl = 'https://abuowxogoiqzbmnvszys.supabase.co';
        $supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImFidW93eG9nb2lxemJtbnZzenlzIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDkyNTcwNTcsImV4cCI6MjA2NDgzMzA1N30.t6b1vtcZhGfOfibwdWKLDUJq2BoRegH5s6P5_OvRwz8';
        
        // Extrai apenas os termos principais da query, removendo operadores
        $termosLimpos = $this->extrairTermosPrincipais($query);
        
        if (empty($termosLimpos)) {
            return [];
        }
        
        // Usa o primeiro termo mais relevante para a busca inicial
        $termoPrincipal = $termosLimpos[0];
        
        $postData = [
            'texto_busca' => $termoPrincipal,
            'limite' => $limit,
            'offset_val' => 0
        ];
        
        $headers = [
            'Content-Type: application/json',
            'apikey: ' . $supabaseKey,
            'Authorization: Bearer ' . $supabaseKey
        ];
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => json_encode($postData),
                'timeout' => 30
            ]
        ]);
        
        try {
            $response = file_get_contents($supabaseUrl . '/rest/v1/rpc/buscar_itens_similares', false, $context);
            
            if ($response === false) {
                error_log("Erro ao conectar com Supabase");
                return [];
            }
            
            $data = json_decode($response, true);
            
            if (!$data || !is_array($data)) {
                error_log("Resposta inválida do Supabase: " . $response);
                return [];
            }
            
            // Converte para formato padronizado
            $resultados = [];
            foreach ($data as $item) {
                $resultados[] = [
                    'catmat' => $item['codigo_catmat'] ?? '',
                    'descricao' => $item['descricao'] ?? '',
                    'material' => $this->extrairMaterial($item['descricao'] ?? ''),
                    'categoria' => $this->extrairCategoria($item['descricao'] ?? '')
                ];
            }
            
            return $resultados;
            
        } catch (\Exception $e) {
            error_log("Erro na busca Supabase: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Extrai termos principais da query removendo operadores
     */
    private function extrairTermosPrincipais($query)
    {
        // Remove aspas e operadores, divide por espaços
        $queryLimpa = preg_replace('/[+\-|"]/', ' ', $query);
        $termos = array_filter(array_map('trim', explode(' ', $queryLimpa)));
        
        // Remove termos muito pequenos
        return array_filter($termos, function($termo) {
            return strlen($termo) >= 2;
        });
    }

    /**
     * Aplica operadores de busca aos resultados já obtidos
     */
    private function aplicarOperadoresDeBusca($resultados, $query)
    {
        $termosProcessados = $this->processarOperadores($query);
        
        return $this->filtrarPorOperadores($resultados, $termosProcessados);
    }

    /**
     * Ordena resultados conforme critério especificado
     */
    private function ordenarResultados(&$resultados, $criterio = 'relevancia')
    {
        switch ($criterio) {
            case 'codigo':
                usort($resultados, function($a, $b) {
                    return strcmp($a['catmat'], $b['catmat']);
                });
                break;
            case 'descricao':
                usort($resultados, function($a, $b) {
                    return strcmp($a['descricao'], $b['descricao']);
                });
                break;
            case 'relevancia':
            default:
                usort($resultados, function($a, $b) {
                    return ($b['relevancia'] ?? 0) <=> ($a['relevancia'] ?? 0);
                });
                break;
        }
    }

    /**
     * Busca dados no Supabase usando a função RPC existente
     */
    private function buscarNoSupabase($termosProcessados, $page = 1, $limit = 20)
    {
        $supabaseUrl = 'https://abuowxogoiqzbmnvszys.supabase.co';
        $supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImFidW93eG9nb2lxemJtbnZzenlzIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDkyNTcwNTcsImV4cCI6MjA2NDgzMzA1N30.t6b1vtcZhGfOfibwdWKLDUJq2BoRegH5s6P5_OvRwz8';
        
        // Combina termos obrigatórios e frases exatas para busca principal
        $termoBusca = implode(' ', array_merge(
            $termosProcessados['obrigatorios'], 
            $termosProcessados['frases_exatas']
        ));
        
        if (empty($termoBusca)) {
            return [];
        }
        
        $postData = [
            'texto_busca' => $termoBusca,
            'limite' => $limit * 3, // Busca mais para depois filtrar
            'offset_val' => 0
        ];
        
        $headers = [
            'Content-Type: application/json',
            'apikey: ' . $supabaseKey,
            'Authorization: Bearer ' . $supabaseKey
        ];
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => json_encode($postData)
            ]
        ]);
        
        $response = file_get_contents($supabaseUrl . '/rest/v1/rpc/buscar_itens_similares', false, $context);
        $data = json_decode($response, true);
        
        if (!$data) {
            return [];
        }
        
        // Aplica filtros de operadores
        return $this->filtrarPorOperadores($data, $termosProcessados);
    }

    /**
     * Aplica filtros baseados nos operadores de busca
     */
    private function filtrarPorOperadores($resultados, $termos)
    {
        $filtrados = [];
        
        foreach ($resultados as $item) {
            $descricao = strtolower($item['descricao']);
            $incluir = true;
            
            // 1. Verifica se TODOS os termos obrigatórios estão presentes
            foreach ($termos['obrigatorios'] as $obrigatorio) {
                if (!str_contains($descricao, strtolower($obrigatorio))) {
                    $incluir = false;
                    break;
                }
            }
            
            if (!$incluir) continue;
            
            // 2. Verifica termos excluídos
            foreach ($termos['excluidos'] as $excluido) {
                if (str_contains($descricao, strtolower($excluido))) {
                    $incluir = false;
                    break;
                }
            }
            
            if (!$incluir) continue;
            
            // 3. Verifica frases exatas
            foreach ($termos['frases_exatas'] as $frase) {
                if (!str_contains($descricao, strtolower($frase))) {
                    $incluir = false;
                    break;
                }
            }
            
            if (!$incluir) continue;
            
            // 4. Se há termos opcionais, verifica se pelo menos um existe
            if (!empty($termos['opcionais'])) {
                $temOpcional = false;
                foreach ($termos['opcionais'] as $opcional) {
                    if (str_contains($descricao, strtolower($opcional))) {
                        $temOpcional = true;
                        break;
                    }
                }
                if (!$temOpcional) continue;
            }
            
            // Monta resultado filtrado
            $filtrados[] = [
                'catmat' => $item['catmat'],
                'descricao' => $item['descricao'],
                'material' => $item['material'],
                'categoria' => $item['categoria']
            ];
        }
        
        return $filtrados;
    }

    /**
     * Aplica filtros adicionais da interface
     */
    private function aplicarFiltros($resultados, $filtros)
    {
        if (empty($filtros)) return $resultados;
        
        return array_filter($resultados, function($item) use ($filtros) {
            // Filtro por material
            if (!empty($filtros['material']) && $item['material'] !== $filtros['material']) {
                return false;
            }
            
            // Filtro por categoria
            if (!empty($filtros['categoria']) && $item['categoria'] !== $filtros['categoria']) {
                return false;
            }
            
            // Filtro por aplicação
            if (!empty($filtros['aplicacao'])) {
                $descricao = strtolower($item['descricao']);
                $aplicacao = strtolower($filtros['aplicacao']);
                if (!str_contains($descricao, $aplicacao)) {
                    return false;
                }
            }
            
            return true;
        });
    }

    /**
     * Calcula relevância baseada na correspondência dos termos
     */
    private function calcularRelevancia($descricao, $queryOriginal)
    {
        $descricaoLower = strtolower($descricao);
        $queryLower = strtolower($queryOriginal);
        
        // Remove operadores para calcular correspondência
        $termosQuery = preg_split('/[+\-|"\s]+/', $queryLower, -1, PREG_SPLIT_NO_EMPTY);
        
        $pontuacao = 0;
        $totalTermos = count($termosQuery);
        
        if ($totalTermos === 0) return 30;
        
        foreach ($termosQuery as $termo) {
            if (strlen($termo) < 2) continue; // Ignora termos muito pequenos
            
            // Pontuação baseada na posição e tipo de correspondência
            if (str_starts_with($descricaoLower, $termo)) {
                // Termo no início da descrição = maior relevância
                $pontuacao += 40;
            } elseif (str_contains($descricaoLower, $termo)) {
                // Termo encontrado em qualquer posição
                $pontuacao += 25;
            }
            
            // Bonus para correspondências de palavras completas
            if (preg_match('/\b' . preg_quote($termo) . '\b/', $descricaoLower)) {
                $pontuacao += 15;
            }
        }
        
        // Normaliza a pontuação
        $relevanciaFinal = min(98, max(5, round($pontuacao * 100 / ($totalTermos * 80))));
        
        return $relevanciaFinal;
    }

    /**
     * Extrai material da descrição
     */
    private function extrairMaterial($descricao)
    {
        $materiais = ['AÇO', 'PLÁSTICO', 'PAPEL', 'METAL', 'MADEIRA', 'TECIDO', 'BORRACHA'];
        
        foreach ($materiais as $material) {
            if (str_contains(strtoupper($descricao), $material)) {
                return $material;
            }
        }
        
        return '';
    }

    /**
     * Extrai categoria da descrição
     */
    private function extrairCategoria($descricao)
    {
        $categorias = [
            'PEÇAS' => ['PEÇAS', 'ACESSÓRIOS', 'COMPONENTE'],
            'MUNIÇÃO' => ['MUNIÇÃO', 'PROJETIL', 'CARTUCHO'],
            'MEDICAMENTO' => ['MEDICAMENTO', 'REMÉDIO', 'DROGA'],
            'EQUIPAMENTO' => ['EQUIPAMENTO', 'APARELHO', 'DISPOSITIVO']
        ];
        
        $descricaoUpper = strtoupper($descricao);
        
        foreach ($categorias as $categoria => $termos) {
            foreach ($termos as $termo) {
                if (str_contains($descricaoUpper, $termo)) {
                    return $categoria;
                }
            }
        }
        
        return '';
    }
}
?>