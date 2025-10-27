<?php

namespace Joabe\Buscaprecos\Controller;

use Joabe\Buscaprecos\Core\Router;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PrecoController
{
    public function exibirPainel($params = [])
    {
        try {
            $processo_id = $params['processo_id'] ?? 0;
            $item_id = $params['item_id'] ?? 0;
            $pdo = \getDbConnection();

            $stmtProcesso = $pdo->prepare("SELECT * FROM processos WHERE id = ?");
            $stmtProcesso->execute([$processo_id]);
            $processo = $stmtProcesso->fetch();

            $stmtItem = $pdo->prepare("SELECT * FROM itens WHERE id = ? AND processo_id = ?");
            $stmtItem->execute([$item_id, $processo_id]);
            $item = $stmtItem->fetch();

            if (!$processo || !$item) {
                http_response_code(404);
                echo "Erro 404: Processo ou Item não encontrado.";
                return;
            }

            $stmtPrecos = $pdo->prepare("SELECT * FROM precos_coletados WHERE item_id = ?");
            $stmtPrecos->execute([$item_id]);
            $precos = $stmtPrecos->fetchAll();

            $tituloPagina = "Painel de Pesquisa de Preços";
            $paginaConteudo = __DIR__ . '/../View/precos/painel.php';
            ob_start();
            require __DIR__ . '/../View/layout/main.php';
            $view = ob_get_clean();
            echo $view;

        } catch (\PDOException $e) {
            error_log("Erro ao exibir painel de preços: " . $e->getMessage());
            echo "<p>Erro ao carregar o painel de preços. Tente novamente mais tarde.</p>";
        }
    }


    // NOVO MÉTODO: Salva uma nova cotação de preço no banco
    public function criar($params = [])
    {
        $processo_id = $params['processo_id'] ?? 0;
        $item_id = $params['item_id'] ?? 0;
        $dados = \Joabe\Buscaprecos\Core\Router::getPostData();
        $redirectUrl = "/processos/{$processo_id}/itens/{$item_id}/pesquisar";

        if (empty($dados['data_coleta'])) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Erro: A data da coleta é obrigatória.'];
            header("Location: {$redirectUrl}");
            exit;
        }

        $fonte = $dados['fonte'];
        $dataColeta = new \DateTime($dados['data_coleta']);
        $dataAtual = new \DateTime();
        
        if ($dataColeta > $dataAtual) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Erro: A data da coleta não pode ser no futuro.'];
            header("Location: {$redirectUrl}");
            exit;
        }

        $intervalo = $dataAtual->diff($dataColeta);
        $mesesDiferenca = $intervalo->y * 12 + $intervalo->m;
        $erroPrazo = null;

        if (($fonte === 'Site Especializado' || $fonte === 'Pesquisa com Fornecedor') && $mesesDiferenca >= 6) {
            $erroPrazo = 'Erro de Validação: Para "Site Especializado" ou "Pesquisa com Fornecedor", a data não pode ser superior a 6 meses.';
        }

        if (($fonte === 'Contratação Similar' || $fonte === 'Nota Fiscal') && $mesesDiferenca >= 12) {
            $erroPrazo = 'Erro de Validação: Para "Contratação Similar" ou "Nota Fiscal", a data não pode ser superior a 1 ano.';
        }

        if ($erroPrazo) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => $erroPrazo];
            header("Location: {$redirectUrl}");
            exit;
        }

        try {
            $sql = "INSERT INTO precos_coletados (item_id, fonte, valor, unidade_medida, data_coleta, fornecedor_nome, fornecedor_cnpj, link_evidencia) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $pdo = \getDbConnection();
            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                $item_id, $dados['fonte'], $dados['valor'], $dados['unidade_medida'],
                $dados['data_coleta'], $dados['fornecedor_nome'] ?: null, $dados['fornecedor_cnpj'] ?: null, $dados['link_evidencia'] ?: null
            ]);

            $_SESSION['flash'] = ['tipo' => 'success', 'mensagem' => 'Cotação manual adicionada com sucesso!'];

        } catch (\PDOException $e) {
            error_log("Erro ao criar cotação de preço: " . $e->getMessage());
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Erro ao salvar a cotação: ' . $e->getMessage()];
        }

        header("Location: {$redirectUrl}");
        exit;
    }

    public function buscarPainelDePrecos($params = [])
    {
        $dados = \Joabe\Buscaprecos\Core\Router::getPostData();
        $catmat = $dados['catmat'] ?? null;

        if (!$catmat) {
            \Joabe\Buscaprecos\Core\Router::json(['erro' => 'CATMAT não fornecido'], 400);
            return;
        }

        // --- CORREÇÃO APLICADA AQUI ---
        // Monta a URL completa e correta da API do governo, incluindo todos os parâmetros.
        $url = "https://dadosabertos.compras.gov.br/modulo-pesquisa-preco/1_consultarMaterial?pagina=1&tamanhoPagina=20&codigoItemCatalogo={$catmat}&dataResultado=true";

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n",
                'timeout' => 30
            ]
        ]);

        try {
            $response = file_get_contents($url, false, $context);
            
            if ($response === false) {
                \Joabe\Buscaprecos\Core\Router::json(['erro' => 'Falha ao consultar a API do Painel de Preços.'], 500);
                return;
            }
            
            header('Content-Type: application/json');
            echo $response;

        } catch (\Exception $e) {
            // Log do erro para depuração
            error_log($e->getMessage()); 
            
            \Joabe\Buscaprecos\Core\Router::json(['erro' => 'Falha ao consultar a API do Painel de Preços.'], 500);
        }
    }

    public function excluir($params = [])
    {
        $processo_id = $params['processo_id'] ?? 0;
        $item_id = $params['item_id'] ?? 0;
        $preco_id = $params['preco_id'] ?? 0;
        $redirectUrl = "/processos/{$processo_id}/itens/{$item_id}/pesquisar";

        try {
            $pdo = \getDbConnection();
            $sql = "DELETE FROM precos_coletados WHERE id = ? AND item_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$preco_id, $item_id]);

            $_SESSION['flash'] = ['tipo' => 'success', 'mensagem' => 'Preço excluído com sucesso!'];

        } catch (\PDOException $e) {
            error_log("Erro ao excluir preço: " . $e->getMessage());
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Erro ao excluir o preço: ' . $e->getMessage()];
        }

        header("Location: {$redirectUrl}");
        exit;
    }

    /**
     * Cria múltiplas cotações de preço de uma vez (em lote).
     */
    public function criarLote($params = [])
    {
        $item_id = $params['item_id'] ?? 0;
        $precos = \Joabe\Buscaprecos\Core\Router::getPostData(); // Recebe o array de preços do frontend

        if (empty($precos) || !is_array($precos)) {
            \Joabe\Buscaprecos\Core\Router::json(['status' => 'error', 'message' => 'Nenhum preço fornecido.'], 400);
            return;
        }

        $pdo = \getDbConnection();
        
        $sql = "INSERT INTO precos_coletados 
                    (item_id, fonte, valor, unidade_medida, data_coleta, fornecedor_nome, fornecedor_cnpj, link_evidencia) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);

        try {
            $pdo->beginTransaction();
            foreach ($precos as $preco) {
                $stmt->execute([
                    $item_id,
                    $preco['fonte'],
                    $preco['valor'],
                    $preco['unidade_medida'],
                    $preco['data_coleta'],
                    $preco['fornecedor_nome'] ?: null,
                    $preco['fornecedor_cnpj'] ?: null,
                    $preco['link_evidencia'] ?: null
                ]);
            }
            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            error_log("Falha ao salvar os preços: " . $e->getMessage());
            \Joabe\Buscaprecos\Core\Router::json(['status' => 'error', 'message' => 'Falha ao salvar os preços.'], 500);
            return;
        }

        \Joabe\Buscaprecos\Core\Router::json(['status' => 'success', 'message' => 'Cotações salvas com sucesso.']);
    }

    /**
     * Busca contratações similares na API de dados abertos,
     * seja por região (automático) ou por UASGs específicas.
     */
    public function pesquisarContratacoesSimilares($params = [])
    {
        $item_id = $params['item_id'] ?? 0;
        $dadosCorpo = \Joabe\Buscaprecos\Core\Router::getPostData();
        $uasgsSugeridas = $dadosCorpo['uasgs'] ?? [];

        $pdo = \getDbConnection();
        
        // Busca o item e seu processo pai para obter o CATMAT e a Região
        $stmtItem = $pdo->prepare("SELECT i.catmat_catser, p.regiao FROM itens i JOIN processos p ON i.processo_id = p.id WHERE i.id = ?");
        $stmtItem->execute([$item_id]);
        $itemInfo = $stmtItem->fetch();

        if (!$itemInfo || empty($itemInfo['catmat_catser'])) {
            \Joabe\Buscaprecos\Core\Router::json(['erro' => 'Item, CATMAT ou Região não encontrados.'], 404);
            return;
        }

        $catmat = $itemInfo['catmat_catser'];
        $regiao = $itemInfo['regiao'];
        
        $resultadosFinais = ['resultado' => []];

        try {
            if (!empty($uasgsSugeridas)) {
                // Modo 1: Busca por UASGs específicas fornecidas pelo usuário
                foreach ($uasgsSugeridas as $uasg) {
                    if (empty($uasg)) continue;
                    $url = "https://dadosabertos.compras.gov.br/modulo-pesquisa-preco/1_consultarMaterial?codigoItemCatalogo={$catmat}&codigoUasg={$uasg}&dataResultado=true&tamanhoPagina=10";
                    
                    $context = stream_context_create([
                        'http' => [
                            'method' => 'GET',
                            'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n",
                            'timeout' => 30
                        ]
                    ]);
                    
                    $response = file_get_contents($url, false, $context);
                    if ($response !== false) {
                        $dados = json_decode($response, true);
                        if (!empty($dados['resultado'])) {
                            $resultadosFinais['resultado'] = array_merge($resultadosFinais['resultado'], $dados['resultado']);
                        }
                    }
                }
            } else {
                // Modo 2: Busca automática pela região do processo
                $url = "https://dadosabertos.compras.gov.br/modulo-pesquisa-preco/1_consultarMaterial?codigoItemCatalogo={$catmat}&estado={$regiao}&dataResultado=true&tamanhoPagina=20";
                
                $context = stream_context_create([
                    'http' => [
                        'method' => 'GET',
                        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n",
                        'timeout' => 30
                    ]
                ]);
                
                $response = file_get_contents($url, false, $context);
                if ($response !== false) {
                    $dados = json_decode($response, true);
                    if (!empty($dados['resultado'])) {
                        $resultadosFinais['resultado'] = $dados['resultado'];
                    }
                }
            }
        } catch (\Exception $e) {
            error_log("Erro na API de Contratos Similares: " . $e->getMessage());
            \Joabe\Buscaprecos\Core\Router::json(['erro' => 'Falha ao consultar a API externa.'], 502);
            return;
        }

        \Joabe\Buscaprecos\Core\Router::json($resultadosFinais);
    }

     
     /**
     * Cria uma solicitação em lote para múltiplos itens e fornecedores,
     * e dispara um e-mail individual e com token único para cada fornecedor.
     */
    public function enviarSolicitacaoLote($params = [])
    {
        // Log de entrada
        error_log("MÉTODO CHAMADO: enviarSolicitacaoLote");
        ini_set('display_errors', 0);

        $processo_id = $params['processo_id'] ?? 0;
        $dados = \Joabe\Buscaprecos\Core\Router::getPostData();

        error_log("Dados recebidos: " . json_encode($dados));

        $itemIds = $dados['item_ids'] ?? [];
        $fornecedorIds = $dados['fornecedor_ids'] ?? [];
        $prazoDias = (int)($dados['prazo_dias'] ?? 5);
        $condicoesContratuais = $dados['condicoes_contratuais'] ?? '';
        $justificativaFornecedores = $dados['justificativa_fornecedores'] ?? '';

        if (empty($itemIds) || empty($fornecedorIds) || empty($justificativaFornecedores)) {
            \Joabe\Buscaprecos\Core\Router::json([
                'status' => 'error',
                'message' => 'É necessário selecionar itens, fornecedores e preencher a justificativa.'
            ], 400);
            return;
        }

        $pdo = \getDbConnection();

        try {
            $pdo->beginTransaction();

            $prazoFinal = (new \DateTime())->add(new \DateInterval("P{$prazoDias}D"))->format('Y-m-d');
            $sqlLote = "INSERT INTO lotes_solicitacao (processo_id, prazo_final, justificativa_fornecedores, condicoes_contratuais) VALUES (?, ?, ?, ?)";
            $stmtLote = $pdo->prepare($sqlLote);
            $stmtLote->execute([$processo_id, $prazoFinal, $justificativaFornecedores, $condicoesContratuais]);
            $loteId = $pdo->lastInsertId();

            $sqlItem = "INSERT INTO lotes_solicitacao_itens (lote_solicitacao_id, item_id) VALUES (?, ?)";
            $stmtItem = $pdo->prepare($sqlItem);
            foreach ($itemIds as $itemId) {
                $stmtItem->execute([$loteId, $itemId]);
            }

            $sqlFornecedor = "INSERT INTO lotes_solicitacao_fornecedores (lote_solicitacao_id, fornecedor_id, token) VALUES (?, ?, ?)";
            $stmtFornecedor = $pdo->prepare($sqlFornecedor);
            $tokensPorFornecedorId = [];
            foreach ($fornecedorIds as $fornecedorId) {
                $token = bin2hex(random_bytes(32));
                $stmtFornecedor->execute([$loteId, $fornecedorId, $token]);
                $tokensPorFornecedorId[$fornecedorId] = $token;
            }

            $listaFornecedores = $this->getDadosFornecedores($pdo, $fornecedorIds);
            $itensHtml = $this->getItensHtml($pdo, $itemIds);
            $errosEnvio = [];
            
            $blocoCondicoes = !empty($condicoesContratuais)
                ? "<hr><p><strong>Condições da Contratação:</strong></p><p style=\"white-space: pre-wrap;\">" . htmlspecialchars($condicoesContratuais) . "</p><hr>"
                : '';

            foreach ($listaFornecedores as $fornecedor) {
                // --- INÍCIO DA CORREÇÃO ---
                // Valida o e-mail do fornecedor antes de tentar o envio
                if (empty($fornecedor['email']) || !filter_var($fornecedor['email'], FILTER_VALIDATE_EMAIL)) {
                    $erroDetalhado = "Fornecedor '{$fornecedor['razao_social']}' (ID: {$fornecedor['id']}) está com e-mail inválido ou em branco.";
                    error_log($erroDetalhado);
                    $errosEnvio[] = $erroDetalhado;
                    continue; // Pula para o próximo fornecedor
                }
                // --- FIM DA CORREÇÃO ---

                $tokenUnico = $tokensPorFornecedorId[$fornecedor['id']];
                $linkResposta = "https://{$_SERVER['HTTP_HOST']}/cotacao/responder?token={$tokenUnico}";
                
                $mail = new PHPMailer(true);
                try {
                    $host = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
                    $username = $_ENV['MAIL_USERNAME'] ?? '';
                    $password = $_ENV['MAIL_PASSWORD'] ?? '';
                    $port = $_ENV['MAIL_PORT'] ?? 587;
                    
                    $mail->isSMTP();
                    $mail->Host       = $host;
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $username;
                    $mail->Password   = $password;
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = $port;
                    $mail->CharSet    = 'UTF-8';
                    $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@sistema.com', $_ENV['MAIL_FROM_NAME'] ?? 'Sistema');
                    $mail->addAddress($fornecedor['email'], $fornecedor['razao_social']);
                    $mail->isHTML(true);
                    $mail->Subject = 'Solicitação de Cotação de Preços';
                    $mail->Body = "<h1>Solicitação de Cotação</h1><p>Prezado(a) Fornecedor(a) <strong>" . htmlspecialchars($fornecedor['razao_social']) . "</strong>,</p><p>Estamos realizando uma pesquisa de preços para os seguintes itens:</p>{$itensHtml}{$blocoCondicoes}<p>Para nos enviar sua proposta, por favor, acesse o seu link exclusivo abaixo. O prazo para resposta é até o dia <strong>" . date('d/m/Y', strtotime($prazoFinal)) . "</strong>.</p><p style=\"text-align:center; margin: 20px 0;\"><a href='{$linkResposta}' style='padding: 12px 20px; background-color: #0d6efd; color: white; text-decoration: none; border-radius: 5px; font-size: 16px;'>Clique Aqui para Cotar</a></p><p>Se não for possível clicar no botão, copie e cole o link a seguir no seu navegador: {$linkResposta}</p><p>Atenciosamente,<br>Equipe de Cotações</p>";
                    $mail->AltBody = "Para cotar os itens, por favor, copie e cole o seguinte link no seu navegador: {$linkResposta}";
                    
                    $mail->send();

                } catch (Exception $e) {
                    $erroDetalhado = "Não foi possível enviar para {$fornecedor['email']}. Erro: {$mail->ErrorInfo}";
                    error_log("Erro ao enviar e-mail: " . $erroDetalhado);
                    $errosEnvio[] = $erroDetalhado;
                }
            }

            // --- INÍCIO DA CORREÇÃO ---
            // Verifica se houve erros e desfaz a transação se necessário
            if (!empty($errosEnvio)) {
                $pdo->rollBack(); // Desfaz as inserções no banco
                \Joabe\Buscaprecos\Core\Router::json([
                    'status' => 'warning',
                    // Mensagem mais clara para o usuário
                    'message' => 'Nenhuma solicitação foi salva pois um ou mais e-mails de fornecedores são inválidos ou falharam no envio.',
                    'details' => $errosEnvio
                ]);
                return;
            }
            // --- FIM DA CORREÇÃO ---

            $pdo->commit(); // Confirma a transação apenas se todos os e-mails foram enviados

        } catch (\Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Erro ao enviar solicitação em lote: " . $e->getMessage());
            \Joabe\Buscaprecos\Core\Router::json([
                'status' => 'error',
                'message' => 'Falha grave ao processar a solicitação: ' . $e->getMessage()
            ], 500);
            return;
        }

        \Joabe\Buscaprecos\Core\Router::json([
            'status' => 'success',
            'message' => 'Solicitações enviadas com sucesso!'
        ]);
    }

    // Métodos auxiliares
    private function getDadosFornecedores(\PDO $pdo, array $fornecedorIds): array
    {
        if (empty($fornecedorIds)) return [];
        try {
            $placeholders = implode(',', array_fill(0, count($fornecedorIds), '?'));
            $stmt = $pdo->prepare("SELECT id, razao_social, email FROM fornecedores WHERE id IN ($placeholders)");
            $stmt->execute($fornecedorIds);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar dados de fornecedores: " . $e->getMessage());
            return [];
        }
    }

    private function getItensHtml(\PDO $pdo, array $itemIds): string
    {
        if (empty($itemIds)) return '';
        try {
            $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
            $stmt = $pdo->prepare("SELECT descricao FROM itens WHERE id IN ($placeholders)");
            $stmt->execute($itemIds);
            $listaItensDesc = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            return '<ul><li>' . implode('</li><li>', array_map('htmlspecialchars', $listaItensDesc)) . '</li></ul>';
        } catch (\PDOException $e) {
            error_log("Erro ao buscar HTML de itens: " . $e->getMessage());
            return '';
        }
    }

    public function exibirAnalise($params = [])
    {
        try {
            $processo_id = $params['processo_id'] ?? 0;
            $item_id = $params['item_id'] ?? 0;
            $pdo = \getDbConnection();

            $stmtProcesso = $pdo->prepare("SELECT * FROM processos WHERE id = ?");
            $stmtProcesso->execute([$processo_id]);
            $processo = $stmtProcesso->fetch();

            $stmtItem = $pdo->prepare("SELECT * FROM itens WHERE id = ?");
            $stmtItem->execute([$item_id]);
            $item = $stmtItem->fetch();
            
            $stmtPrecos = $pdo->prepare("SELECT * FROM precos_coletados WHERE item_id = ? ORDER BY valor ASC");
            $stmtPrecos->execute([$item_id]);
            $precos = $stmtPrecos->fetchAll();

            // Filtra apenas os preços "considerados" para as estatísticas
            $precosConsiderados = array_filter($precos, fn($p) => $p['status_analise'] === 'considerado');
            
            $estatisticas = ['total' => 0, 'minimo' => 0, 'maximo' => 0, 'media' => 0, 'mediana' => 0];

            if (!empty($precosConsiderados)) {
                $valores = array_column($precosConsiderados, 'valor');
                sort($valores);
                $count = count($valores);
                
                $estatisticas['total'] = $count;
                $estatisticas['minimo'] = $valores[0];
                $estatisticas['maximo'] = $valores[$count - 1];
                $estatisticas['media'] = array_sum($valores) / $count;
                
                $meio = floor(($count - 1) / 2);
                if ($count % 2) { 
                    $estatisticas['mediana'] = $valores[$meio];
                } else { 
                    $estatisticas['mediana'] = ($valores[$meio] + $valores[$meio + 1]) / 2.0;
                }
            }
            
            $tituloPagina = "Mesa de Análise de Preços";
            $paginaConteudo = __DIR__ . '/../View/analise/mesa.php';
            
            ob_start();
            require __DIR__ . '/../View/layout/main.php';
            $view = ob_get_clean();

            echo $view;

        } catch (\PDOException $e) {
            error_log("Erro ao exibir análise de preços: " . $e->getMessage());
            echo "<p>Erro ao carregar a análise de preços. Tente novamente mais tarde.</p>";
        }
    }

    public function desconsiderarPreco($params = [])
    {
        $redirectUrl = "/processos/{$params['processo_id']}/analise";
        try {
            $dados = \Joabe\Buscaprecos\Core\Router::getPostData();
            $justificativa = $dados['justificativa_descarte'];

            $sql = "UPDATE precos_coletados SET status_analise = 'desconsiderado', justificativa_descarte = ? WHERE id = ?";
            $pdo = \getDbConnection();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$justificativa, $params['preco_id']]);

            $_SESSION['flash_success'] = 'Preço desconsiderado com sucesso.';

        } catch (\PDOException $e) {
            error_log("Erro ao desconsiderar preço: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Erro ao atualizar o preço.';
        }

        header("Location: {$redirectUrl}");
        exit;
    }

    public function reconsiderarPreco($params = [])
    {
        $redirectUrl = "/processos/{$params['processo_id']}/analise";
        try {
            $sql = "UPDATE precos_coletados SET status_analise = 'considerado', justificativa_descarte = NULL WHERE id = ?";
            $pdo = \getDbConnection();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$params['preco_id']]);

            $_SESSION['flash_success'] = 'Preço reconsiderado com sucesso.';

        } catch (\PDOException $e) {
            error_log("Erro ao reconsiderar preço: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Erro ao atualizar o preço.';
        }

        header("Location: {$redirectUrl}");
        exit;
    }

}