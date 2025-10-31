<?php

namespace Joabe\Buscaprecos\Controller;

use Joabe\Buscaprecos\Core\Router;

class CotacaoPublicaController
{
    /**
     * Exibe o formulário de cotação para o fornecedor.
     */
    public function exibirFormulario($params = [])
    {
        $token = $_GET['token'] ?? null;
        if (!$token) {
            $this->exibirPaginaDeErro("Token de acesso não fornecido. Por favor, utilize o link enviado para o seu e-mail.");
            return;
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
            WHERE lsf.token = ? AND lsf.status = 'pendente'";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$token]);
        $solicitacao = $stmt->fetch();

        if (!$solicitacao) {
            $this->exibirPaginaDeErro("Solicitação de cotação inválida, já respondida ou expirada.");
            return;
        }

        if (new \DateTime() > new \DateTime($solicitacao['prazo_final'])) {
            // Opcional: Atualizar status para Expirado aqui
            $this->exibirPaginaDeErro("O prazo para responder a esta cotação expirou em " . date('d/m/Y', strtotime($solicitacao['prazo_final'])) . ".");
            return;
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
        $arquivos = $_FILES; // Pega os arquivos enviados
        $token = $dados['token'] ?? null;
        $precos = $dados['precos'] ?? [];

        // Validação básica de entrada
        if (!$token || empty($precos) || !isset($arquivos['proposta_anexo']) || $arquivos['proposta_anexo']['error'] !== UPLOAD_ERR_OK) {
            $this->exibirPaginaDeErro("Dados inválidos. É obrigatório preencher a cotação e anexar a proposta em PDF.");
            return;
        }

        $pdo = \getDbConnection();

        // Busca informações da solicitação (código existente)
        $sqlInfo = "SELECT lsf.id, lsf.fornecedor_id, f.razao_social, f.cnpj 
                    FROM lotes_solicitacao_fornecedores lsf
                    JOIN fornecedores f ON lsf.fornecedor_id = f.id
                    WHERE lsf.token = ? AND lsf.status = 'pendente'";
        $stmtInfo = $pdo->prepare($sqlInfo);
        $stmtInfo->execute([$token]);
        $solicitacaoInfo = $stmtInfo->fetch();

        if (!$solicitacaoInfo) {
            $this->exibirPaginaDeErro("Solicitação inválida, já respondida ou expirada.");
            return;
        }
        
        $solicitacaoFornecedorId = $solicitacaoInfo['id'];
        $arquivoAnexo = $arquivos['proposta_anexo'];
        $caminhoAnexo = null;
        $nomeOriginalAnexo = null;

        try {
            $pdo->beginTransaction();

            // 1. Processar e mover o arquivo de anexo
            if ($arquivoAnexo['size'] > 5 * 1024 * 1024) { // 5 MB
                throw new \Exception("O arquivo excede o tamanho máximo de 5MB.");
            }
            if ($arquivoAnexo['type'] !== 'application/pdf') {
                throw new \Exception("O arquivo deve ser do tipo PDF.");
            }

            $nomeOriginalAnexo = $arquivoAnexo['name'];
            $extensao = pathinfo($nomeOriginalAnexo, PATHINFO_EXTENSION);
            $nomeUnico = bin2hex(random_bytes(16)) . '.' . $extensao;
            
            // O nome do objeto no GCS incluirá o diretório de propostas
            $gcsObjectName = 'propostas/' . $nomeUnico;

            // Faz o upload para o GCS
            $caminhoAnexo = \Joabe\Buscaprecos\Core\uploadToGCS($arquivoAnexo['tmp_name'], $gcsObjectName);

            // 2. Atualiza o status da solicitação para 'Respondido' e salva os dados do anexo
            $sqlStatus = "UPDATE lotes_solicitacao_fornecedores 
                        SET status = 'Respondido', data_resposta = NOW(), caminho_anexo = ?, nome_original_anexo = ?
                        WHERE id = ?";
            $stmtStatus = $pdo->prepare($sqlStatus);
            $stmtStatus->execute([$caminhoAnexo, $nomeOriginalAnexo, $solicitacaoFornecedorId]);

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
            $this->exibirPaginaDeErro("Ocorreu um erro interno ao salvar sua cotação. Detalhe: " . $e->getMessage());
            return;
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
    private function exibirPaginaDeErro($mensagem) {
        http_response_code(400);
        $paginaConteudo = __DIR__ . '/../View/publico/erro.php';
        ob_start();
        require __DIR__ . '/../View/layout/public.php';
        $view = ob_get_clean();
        echo $view;
    }
}