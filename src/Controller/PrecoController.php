<?php
namespace Joabe\Buscaprecos\Controller;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PrecoController
{
    public function exibirPainel($request, $response, $args)
    {
        // ... (código do método exibirPainel - sem alterações)
        $processo_id = $args['processo_id'];
        $item_id = $args['item_id'];
        $pdo = \getDbConnection();
        $stmtProcesso = $pdo->prepare("SELECT * FROM processos WHERE id = ?");
        $stmtProcesso->execute([$processo_id]);
        $processo = $stmtProcesso->fetch();
        $stmtItem = $pdo->prepare("SELECT * FROM itens WHERE id = ? AND processo_id = ?");
        $stmtItem->execute([$item_id, $processo_id]);
        $item = $stmtItem->fetch();
        if (!$processo || !$item) {
            $response->getBody()->write("Erro 404: Processo ou Item não encontrado.");
            return $response->withStatus(404);
        }
        $stmtPrecos = $pdo->prepare("SELECT * FROM precos_coletados WHERE item_id = ? ORDER BY criado_em DESC");
        $stmtPrecos->execute([$item_id]);
        $precos = $stmtPrecos->fetchAll();
        $tituloPagina = "Painel de Pesquisa de Preços";
        $paginaConteudo = __DIR__ . '/../View/precos/painel.php';
        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();
        $response->getBody()->write($view);
        return $response;
    }


    // NOVO MÉTODO: Salva uma nova cotação de preço no banco
    public function criar($request, $response, $args)
    {
        $processo_id = $args['processo_id'];
        $item_id = $args['item_id'];
        $dados = $request->getParsedBody();
        $redirectUrl = "/processos/{$processo_id}/itens/{$item_id}/pesquisar";

        if (empty($dados['data_coleta'])) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Erro: A data da coleta é obrigatória.'];
            return $response->withHeader('Location', $redirectUrl)->withStatus(302);
        }

        $fonte = $dados['fonte'];
        $dataColeta = new \DateTime($dados['data_coleta']);
        $dataAtual = new \DateTime();
        
        if ($dataColeta > $dataAtual) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Erro: A data da coleta não pode ser no futuro.'];
            return $response->withHeader('Location', $redirectUrl)->withStatus(302);
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
            return $response->withHeader('Location', $redirectUrl)->withStatus(302);
        }

        $sql = "INSERT INTO precos_coletados (item_id, fonte, valor, unidade_medida, data_coleta, fornecedor_nome, fornecedor_cnpj, link_evidencia) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $pdo = \getDbConnection();
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            $item_id, $dados['fonte'], $dados['valor'], $dados['unidade_medida'],
            $dados['data_coleta'], $dados['fornecedor_nome'] ?: null, $dados['fornecedor_cnpj'] ?: null, $dados['link_evidencia'] ?: null
        ]);

        $_SESSION['flash'] = ['tipo' => 'success', 'mensagem' => 'Cotação manual adicionada com sucesso!'];
        return $response->withHeader('Location', $redirectUrl)->withStatus(302);
    }

    public function buscarPainelDePrecos($request, $response, $args)
{
    $dados = $request->getParsedBody();
    $catmat = $dados['catmat'] ?? null;

    if (!$catmat) {
        $response->getBody()->write(json_encode(['erro' => 'CATMAT não fornecido']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // --- CORREÇÃO APLICADA AQUI ---
    // Monta a URL completa e correta da API do governo, incluindo todos os parâmetros.
    $url = "https://dadosabertos.compras.gov.br/modulo-pesquisa-preco/1_consultarMaterial?pagina=1&tamanhoPagina=20&codigoItemCatalogo={$catmat}&dataResultado=true";

    $client = new \GuzzleHttp\Client([
        'verify' => false // Adicionado para ignorar problemas de certificado SSL que podem ocorrer em ambientes locais
    ]);

    try {
        $apiResponse = $client->request('GET', $url);
        $body = $apiResponse->getBody()->getContents();
        
        $response->getBody()->write($body);
        return $response->withHeader('Content-Type', 'application/json');

    } catch (\GuzzleHttp\Exception\GuzzleException $e) {
        // Log do erro para depuração
        error_log($e->getMessage()); 
        
        $response->getBody()->write(json_encode(['erro' => 'Falha ao consultar a API do Painel de Preços.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
}

    public function excluir($request, $response, $args)
    {
        $processo_id = $args['processo_id'];
        $item_id = $args['item_id'];
        $preco_id = $args['preco_id'];

        $pdo = \getDbConnection();
        
        // Prepara e executa a query de exclusão
        $sql = "DELETE FROM precos_coletados WHERE id = ? AND item_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$preco_id, $item_id]);

        // Redireciona o usuário de volta para a página de pesquisa
        $redirectUrl = "/processos/{$processo_id}/itens/{$item_id}/pesquisar";
        return $response->withHeader('Location', $redirectUrl)->withStatus(302);
    }

    /**
     * Cria múltiplas cotações de preço de uma vez (em lote).
     */
    public function criarLote($request, $response, $args)
    {
        $item_id = $args['item_id'];
        $precos = $request->getParsedBody(); // Recebe o array de preços do frontend

        if (empty($precos) || !is_array($precos)) {
            return $response->withJson(['status' => 'error', 'message' => 'Nenhum preço fornecido.'], 400);
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
            // Em um app real, logaríamos o erro $e->getMessage()
            return $response->withJson(['status' => 'error', 'message' => 'Falha ao salvar os preços.'], 500);
        }

        return $response->withJson(['status' => 'success', 'message' => 'Cotações salvas com sucesso.']);
    }

    /**
     * Busca contratações similares na API de dados abertos,
     * seja por região (automático) ou por UASGs específicas.
     */
    public function pesquisarContratacoesSimilares($request, $response, $args)
    {
        $item_id = $args['item_id'];
        $dadosCorpo = $request->getParsedBody();
        $uasgsSugeridas = $dadosCorpo['uasgs'] ?? [];

        $pdo = \getDbConnection();
        
        // Busca o item e seu processo pai para obter o CATMAT e a Região
        $stmtItem = $pdo->prepare("SELECT i.catmat_catser, p.regiao FROM itens i JOIN processos p ON i.processo_id = p.id WHERE i.id = ?");
        $stmtItem->execute([$item_id]);
        $itemInfo = $stmtItem->fetch();

        if (!$itemInfo || empty($itemInfo['catmat_catser'])) {
            return $response->withJson(['erro' => 'Item, CATMAT ou Região não encontrados.'], 404);
        }

        $catmat = $itemInfo['catmat_catser'];
        $regiao = $itemInfo['regiao'];
        
        $client = new \GuzzleHttp\Client(['verify' => false]);
        $resultadosFinais = ['resultado' => []];

        try {
            if (!empty($uasgsSugeridas)) {
                // Modo 1: Busca por UASGs específicas fornecidas pelo usuário
                foreach ($uasgsSugeridas as $uasg) {
                    if (empty($uasg)) continue;
                    $url = "https://dadosabertos.compras.gov.br/modulo-pesquisa-preco/1_consultarMaterial?codigoItemCatalogo={$catmat}&codigoUasg={$uasg}&dataResultado=true&tamanhoPagina=10";
                    $apiResponse = $client->request('GET', $url);
                    $dados = json_decode($apiResponse->getBody()->getContents(), true);
                    if (!empty($dados['resultado'])) {
                        $resultadosFinais['resultado'] = array_merge($resultadosFinais['resultado'], $dados['resultado']);
                    }
                }
            } else {
                // Modo 2: Busca automática pela região do processo
                $url = "https://dadosabertos.compras.gov.br/modulo-pesquisa-preco/1_consultarMaterial?codigoItemCatalogo={$catmat}&estado={$regiao}&dataResultado=true&tamanhoPagina=20";
                $apiResponse = $client->request('GET', $url);
                $dados = json_decode($apiResponse->getBody()->getContents(), true);
                if (!empty($dados['resultado'])) {
                    $resultadosFinais['resultado'] = $dados['resultado'];
                }
            }
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            error_log("Erro na API de Contratos Similares: " . $e->getMessage());
            return $response->withJson(['erro' => 'Falha ao consultar a API externa.'], 502);
        }

        return $response->withJson($resultadosFinais);
    }

     
     /**
     * Cria uma solicitação em lote para múltiplos itens e fornecedores,
     * e dispara um e-mail individual e com token único para cada fornecedor.
     */
    public function enviarSolicitacaoLote($request, $response, $args)
    {



        $processo_id = $args['processo_id'];
        $dados = $request->getParsedBody();

        // =======================================================
    //          INÍCIO DO CÓDIGO DE DEPURAÇÃO (TEMPORÁRIO)
    // =======================================================
    $log_message = "Timestamp: " . date("Y-m-d H:i:s") . "\n";
    $log_message .= "Dados Recebidos: " . print_r($dados, true) . "\n---\n";
    file_put_contents(__DIR__ . '/../../debug.log', $log_message, FILE_APPEND);
    // =======================================================
    //                  FIM DO CÓDIGO DE DEPURAÇÃO
    // =======================================================

    $processo_id = $args['processo_id'];
    $itemIds = $dados['item_ids'] ?? [];
    $fornecedorIds = $dados['fornecedor_ids'] ?? [];

    

        $itemIds = $dados['item_ids'] ?? [];
        $fornecedorIds = $dados['fornecedor_ids'] ?? [];
        $prazoDias = (int)($dados['prazo_dias'] ?? 5);
        $condicoesContratuais = $dados['condicoes_contratuais'] ?? '';
        $justificativaFornecedores = $dados['justificativa_fornecedores'] ?? '';

        if (empty($itemIds) || empty($fornecedorIds) || empty($justificativaFornecedores)) {
            return $response->withJson(['status' => 'error', 'message' => 'É necessário selecionar itens, fornecedores e preencher a justificativa.'], 400);
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
            $blocoCondicoes = '';
            if (!empty($condicoesContratuais)) {
                $blocoCondicoes = "<hr><p><strong>Condições da Contratação:</strong></p><p style=\"white-space: pre-wrap;\">" . htmlspecialchars($condicoesContratuais) . "</p><hr>";
            }

            foreach ($listaFornecedores as $fornecedor) {
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = $_ENV['MAIL_HOST'];
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $_ENV['MAIL_USERNAME'];
                    $mail->Password   = $_ENV['MAIL_PASSWORD'];
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = $_ENV['MAIL_PORT'];
                    $mail->CharSet    = 'UTF-8';
                    $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
                    $mail->addAddress($fornecedor['email'], $fornecedor['razao_social']);
                    $mail->isHTML(true);
                    $mail->Subject = 'Solicitação de Cotação de Preços';
                    $tokenUnico = $tokensPorFornecedorId[$fornecedor['id']];
                    $linkResposta = "http://{$_SERVER['HTTP_HOST']}/cotacao/responder?token={$tokenUnico}";
                    $mail->Body = "<h1>Solicitação de Cotação</h1><p>Prezado(a) Fornecedor(a) <strong>" . htmlspecialchars($fornecedor['razao_social']) . "</strong>,</p><p>Estamos realizando uma pesquisa de preços para os seguintes itens:</p>{$itensHtml}{$blocoCondicoes}<p>Para nos enviar sua proposta, por favor, acesse o seu link exclusivo abaixo. O prazo para resposta é até o dia <strong>" . date('d/m/Y', strtotime($prazoFinal)) . "</strong>.</p><p style=\"text-align:center; margin: 20px 0;\"><a href='{$linkResposta}' style='padding: 12px 20px; background-color: #0d6efd; color: white; text-decoration: none; border-radius: 5px; font-size: 16px;'>Clique Aqui para Cotar</a></p><p>Se não for possível clicar no botão, copie e cole o link a seguir no seu navegador: {$linkResposta}</p><p>Atenciosamente,<br>Equipe de Cotações</p>";
                    $mail->AltBody = "Para cotar os itens, por favor, copie e cole o seguinte link no seu navegador: {$linkResposta}";
                    $mail->send();
                } catch (Exception $e) {
                    $errosEnvio[] = "Não foi possível enviar para {$fornecedor['email']}. Erro: {$mail->ErrorInfo}";
                }
            }

            if (!empty($errosEnvio)) {
                return $response->withJson(['status' => 'warning', 'message' => 'Solicitações salvas, mas alguns e-mails não puderam ser enviados.', 'details' => $errosEnvio]);
            }
            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            error_log("Erro ao enviar solicitação em lote: " . $e->getMessage());
            return $response->withJson(['status' => 'error', 'message' => 'Falha ao processar a solicitação. Detalhe: ' . $e->getMessage()], 500);
        }

        return $response->withJson(['status' => 'success', 'message' => 'Solicitações enviadas com sucesso!']);
    }

// Adicione estes dois métodos auxiliares dentro da classe PrecoController para manter o código organizado
private function getDadosFornecedores(\PDO $pdo, array $fornecedorIds): array
    {
        $placeholders = implode(',', array_fill(0, count($fornecedorIds), '?'));
        $stmt = $pdo->prepare("SELECT id, razao_social, email FROM fornecedores WHERE id IN ($placeholders)");
        $stmt->execute($fornecedorIds);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

private function getItensHtml(\PDO $pdo, array $itemIds): string
    {
        $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
        $stmt = $pdo->prepare("SELECT descricao FROM itens WHERE id IN ($placeholders)");
        $stmt->execute($itemIds);
        $listaItensDesc = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        return '<ul><li>' . implode('</li><li>', array_map('htmlspecialchars', $listaItensDesc)) . '</li></ul>';
    }

    public function exibirAnalise($request, $response, $args)
    {
        $processo_id = $args['processo_id'];
        $item_id = $args['item_id'];
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

        $response->getBody()->write($view);
        return $response;
    }

    public function desconsiderarPreco($request, $response, $args)
    {
        $dados = $request->getParsedBody();
        $justificativa = $dados['justificativa_descarte'];

        $sql = "UPDATE precos_coletados SET status_analise = 'desconsiderado', justificativa_descarte = ? WHERE id = ?";
        $pdo = \getDbConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$justificativa, $args['preco_id']]);

        $redirectUrl = "/processos/{$args['processo_id']}/analise";
        return $response->withHeader('Location', $redirectUrl)->withStatus(302);
    }

    public function reconsiderarPreco($request, $response, $args)
    {
        $sql = "UPDATE precos_coletados SET status_analise = 'considerado', justificativa_descarte = NULL WHERE id = ?";
        $pdo = \getDbConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$args['preco_id']]);

        $redirectUrl = "/processos/{$args['processo_id']}/analise";
        return $response->withHeader('Location', $redirectUrl)->withStatus(302);
    }

}