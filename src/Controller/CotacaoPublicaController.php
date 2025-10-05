<?php

use Joabe\Buscaprecos\Core\Router;

namespace Joabe\Buscaprecos\Controller;

class CotacaoPublicaController
{
    /**
     * Exibe o formulário de cotação para o fornecedor.
     */
    public function exibirFormulario($params = [])
    {
        $token = $request->getQueryParams()['token'] ?? null;
        if (!$token) {
            return $this->exibirPaginaDeErro($response, "Token de acesso não fornecido. Por favor, utilize o link enviado para o seu e-mail.");
        }

        $pdo = \getDbConnection();
        
        // Valida o token e busca as informações da solicitação
        $sql = "SELECT 
                p.nome_processo,
                l.prazo_final,
                f.razao_social,
                f.cnpj,
                f.endereco,
                f.email,
                f.telefone,
                lsf.status
            FROM lotes_solicitacao_fornecedores lsf
            JOIN lotes_solicitacao l ON lsf.lote_solicitacao_id = l.id
            JOIN processos p ON l.processo_id = p.id
            JOIN fornecedores f ON lsf.fornecedor_id = f.id
            WHERE lsf.token = ? AND lsf.status = 'Enviado'";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$token]);
        $solicitacao = $stmt->fetch();

        if (!$solicitacao) {
            return $this->exibirPaginaDeErro($response, "Solicitação de cotação inválida ou não encontrada.");
        }

        if ($solicitacao['status'] === 'Respondido') {
            return $this->exibirPaginaDeErro($response, "Esta cotação já foi respondida. Obrigado!");
        }

        if (new \DateTime() > new \DateTime($solicitacao['prazo_final'])) {
            // Opcional: Atualizar status para Expirado aqui
            return $this->exibirPaginaDeErro($response, "O prazo para responder a esta cotação expirou em " . date('d/m/Y', strtotime($solicitacao['prazo_final'])) . ".");
        }

        // Busca os itens associados a esta solicitação
        $sqlItens = "SELECT i.id, i.descricao, i.unidade_medida, i.quantidade
                     FROM lotes_solicitacao_itens lsi
                     JOIN itens i ON lsi.item_id = i.id
                     JOIN lotes_solicitacao_fornecedores lsf ON lsi.lote_solicitacao_id = lsf.lote_solicitacao_id
                     WHERE lsf.token = ?";

        $stmtItens = $pdo->prepare($sqlItens);
        $stmtItens->execute([$token]);
        $itens = $stmtItens->fetchAll();
        
        $paginaConteudo = __DIR__ . '/../View/publico/formulario-cotacao.php';

        // Renderiza o layout principal, passando as variáveis necessárias
        ob_start();
        require __DIR__ . '/../View/layout/public.php'; 
        $view = ob_get_clean();

        echo $view;
    }

    /**
     * Salva a resposta de cotação do fornecedor.
     */
    public function salvarResposta($params = [])
    {
        $dados = \Joabe\Buscaprecos\Core\Router::getPostData();
        $arquivos = $request->getUploadedFiles(); // Pega os arquivos enviados
        $token = $dados['token'] ?? null;
        $precos = $dados['precos'] ?? [];

        // Validação básica de entrada
        if (!$token || empty($precos) || !isset($arquivos['proposta_anexo']) || $arquivos['proposta_anexo']->getError() !== UPLOAD_ERR_OK) {
            return $this->exibirPaginaDeErro($response, "Dados inválidos. É obrigatório preencher a cotação e anexar a proposta em PDF.");
        }

        $pdo = \getDbConnection();

        // Busca informações da solicitação (código existente)
        $sqlInfo = "SELECT lsf.id, lsf.fornecedor_id, f.razao_social, f.cnpj 
                    FROM lotes_solicitacao_fornecedores lsf
                    JOIN fornecedores f ON lsf.fornecedor_id = f.id
                    WHERE lsf.token = ? AND lsf.status = 'Enviado'";
        $stmtInfo = $pdo->prepare($sqlInfo);
        $stmtInfo->execute([$token]);
        $solicitacaoInfo = $stmtInfo->fetch();

        if (!$solicitacaoInfo) {
            return $this->exibirPaginaDeErro($response, "Solicitação inválida, já respondida ou expirada.");
        }
        
        $solicitacaoFornecedorId = $solicitacaoInfo['id'];
        $arquivoAnexo = $arquivos['proposta_anexo'];
        $caminhoAnexo = null;
        $nomeOriginalAnexo = null;

        try {
            $pdo->beginTransaction();

            // 1. Processar e mover o arquivo de anexo
            $diretorioUpload = __DIR__ . '/../../storage/propostas/';
            if (!is_dir($diretorioUpload)) {
                mkdir($diretorioUpload, 0775, true);
            }

            // Validações de segurança do arquivo
            if ($arquivoAnexo->getSize() > 5 * 1024 * 1024) { // 5 MB
                throw new \Exception("O arquivo excede o tamanho máximo de 5MB.");
            }
            if ($arquivoAnexo->getClientMediaType() !== 'application/pdf') {
                throw new \Exception("O arquivo deve ser do tipo PDF.");
            }

            $nomeOriginalAnexo = $arquivoAnexo->getClientFilename();
            $extensao = pathinfo($nomeOriginalAnexo, PATHINFO_EXTENSION);
            $nomeUnico = bin2hex(random_bytes(16)) . '.' . $extensao;
            $caminhoAnexo = $diretorioUpload . $nomeUnico;

            $arquivoAnexo->moveTo($caminhoAnexo);


            // 2. Atualiza o status da solicitação para 'Respondido' e salva os dados do anexo
            $sqlStatus = "UPDATE lotes_solicitacao_fornecedores 
                        SET status = 'Respondido', data_resposta = NOW(), caminho_anexo = ?, nome_original_anexo = ?
                        WHERE id = ?";
            $stmtStatus = $pdo->prepare($sqlStatus);
            $stmtStatus->execute([$nomeUnico, $nomeOriginalAnexo, $solicitacaoFornecedorId]);

            // 3. Insere os preços cotados na tabela precos_coletados (código existente)
            $sqlPreco = "INSERT INTO precos_coletados (item_id, fonte, valor, unidade_medida, data_coleta, fornecedor_nome, fornecedor_cnpj) 
                        VALUES (?, ?, ?, ?, NOW(), ?, ?)";
            $stmtPreco = $pdo->prepare($sqlPreco);

            foreach ($precos as $itemId => $dadosPreco) {
                if (!empty($dadosPreco['valor'])) {
                    $stmtPreco->execute([
                        $itemId,
                        'Pesquisa com Fornecedor',
                        $dadosPreco['valor'],
                        $dadosPreco['unidade_medida'],
                        $solicitacaoInfo['razao_social'],
                        $solicitacaoInfo['cnpj']
                    ]);
                }
            }
            
            $pdo->commit();

        } catch (\Exception $e) {
            $pdo->rollBack();
            // Apaga o arquivo se a transação falhou
            if ($caminhoAnexo && file_exists($caminhoAnexo)) {
                unlink($caminhoAnexo);
            }
            error_log("Erro ao salvar cotação: " . $e->getMessage());
            return $this->exibirPaginaDeErro($response, "Ocorreu um erro interno ao salvar sua cotação. Detalhe: " . $e->getMessage());
        }
        
        // Exibe a página de sucesso (código existente)
        $paginaConteudo = __DIR__ . '/../View/publico/sucesso.php';
        ob_start();
        require __DIR__ . '/../View/layout/public.php';
        $view = ob_get_clean();
        echo $view;
    }

    /**
     * Helper para exibir uma página de erro genérica.
     */
    private function exibirPaginaDeErro($response, $mensagem) {
        $paginaConteudo = __DIR__ . '/../View/publico/erro.php';
        ob_start();
        require __DIR__ . '/../View/layout/public.php';
        $view = ob_get_clean();
        $response->getBody()->write($view);
        return $response->withStatus(400);
    }
}