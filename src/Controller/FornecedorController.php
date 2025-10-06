<?php

namespace Joabe\Buscaprecos\Controller;

use Joabe\Buscaprecos\Core\Router;
use Joabe\Buscaprecos\Core\Spreadsheet;

class FornecedorController
{
    /**
     * Lista todos os fornecedores
     */
    public function listar($params = [])
    {
        $pdo = \getDbConnection();
        $stmt = $pdo->query("SELECT * FROM fornecedores ORDER BY razao_social ASC");
        $fornecedores = $stmt->fetchAll();

        $tituloPagina = "Fornecedores Cadastrados";
        $paginaConteudo = __DIR__ . '/../View/fornecedores/lista.php';

        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();
        echo $view;
    }

    /**
     * Exibe o formulário para novo fornecedor
     */
    public function exibirFormulario($params = [])
    {
        $tituloPagina = "Novo Fornecedor";
        $paginaConteudo = __DIR__ . '/../View/fornecedores/formulario.php';

        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();
        echo $view;
    }

    /**
     * Cria um novo fornecedor
     */
    public function criar($params = [])
    {
        $dados = Router::getPostData();

        // Limpa CNPJ e telefone
        $cnpjLimpo = preg_replace('/\D/', '', $dados['cnpj']);
        $telefoneLimpo = preg_replace('/\D/', '', $dados['telefone'] ?? '');

        // Validações
        if (empty($dados['razao_social']) || empty($dados['cnpj']) || empty($dados['email'])) {
            $_SESSION['flash_error'] = 'Razão social, CNPJ e e-mail são obrigatórios.';
            Router::redirect('/fornecedores/novo');
            return;
        }

        if (!validarCnpj($cnpjLimpo)) {
            $_SESSION['flash_error'] = 'CNPJ inválido.';
            Router::redirect('/fornecedores/novo');
            return;
        }

        if (!validarEmail($dados['email'])) {
            $_SESSION['flash_error'] = 'E-mail inválido.';
            Router::redirect('/fornecedores/novo');
            return;
        }

        $sql = "INSERT INTO fornecedores (razao_social, cnpj, email, endereco, telefone, ramo_atividade) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $pdo = \getDbConnection();
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $dados['razao_social'],
                $cnpjLimpo,
                $dados['email'],
                $dados['endereco'] ?? null,
                $telefoneLimpo ?: null,
                $dados['ramo_atividade'] ?? null
            ]);
            
            $_SESSION['flash_success'] = 'Fornecedor cadastrado com sucesso!';
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) { // Violação de chave única
                $_SESSION['flash_error'] = 'CNPJ ou e-mail já cadastrado.';
            } else {
                $_SESSION['flash_error'] = 'Erro ao cadastrar fornecedor.';
            }
            Router::redirect('/fornecedores/novo');
            return;
        }

        Router::redirect('/fornecedores');
    }

    /**
     * Exibe formulário de edição
     */
    public function exibirFormularioEdicao($params = [])
    {
        $id = $params['id'] ?? 0;

        $pdo = \getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM fornecedores WHERE id = ?");
        $stmt->execute([$id]);
        $fornecedor = $stmt->fetch();

        if (!$fornecedor) {
            $_SESSION['flash_error'] = 'Fornecedor não encontrado.';
            Router::redirect('/fornecedores');
            return;
        }

        $tituloPagina = "Editar Fornecedor";
        $paginaConteudo = __DIR__ . '/../View/fornecedores/formulario_edicao.php';

        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();
        echo $view;
    }

    /**
     * Atualiza um fornecedor existente
     */
    public function atualizar($params = [])
    {
        $id = $params['id'] ?? 0;
        $dados = Router::getPostData();

        // Limpa CNPJ e telefone
        $cnpjLimpo = preg_replace('/\D/', '', $dados['cnpj']);
        $telefoneLimpo = preg_replace('/\D/', '', $dados['telefone'] ?? '');

        // Validações
        if (empty($dados['razao_social']) || empty($dados['cnpj']) || empty($dados['email'])) {
            $_SESSION['flash_error'] = 'Razão social, CNPJ e e-mail são obrigatórios.';
            Router::redirect("/fornecedores/{$id}/editar");
            return;
        }

        if (!validarCnpj($cnpjLimpo)) {
            $_SESSION['flash_error'] = 'CNPJ inválido.';
            Router::redirect("/fornecedores/{$id}/editar");
            return;
        }

        if (!validarEmail($dados['email'])) {
            $_SESSION['flash_error'] = 'E-mail inválido.';
            Router::redirect("/fornecedores/{$id}/editar");
            return;
        }

        $sql = "UPDATE fornecedores SET 
                    razao_social = ?, 
                    cnpj = ?, 
                    email = ?, 
                    endereco = ?, 
                    telefone = ?, 
                    ramo_atividade = ? 
                WHERE id = ?";
        
        $pdo = \getDbConnection();
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $dados['razao_social'],
                $cnpjLimpo,
                $dados['email'],
                $dados['endereco'] ?? null,
                $telefoneLimpo ?: null,
                $dados['ramo_atividade'] ?? null,
                $id
            ]);
            
            $_SESSION['flash_success'] = 'Fornecedor atualizado com sucesso!';
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                $_SESSION['flash_error'] = 'CNPJ ou e-mail já cadastrado para outro fornecedor.';
            } else {
                $_SESSION['flash_error'] = 'Erro ao atualizar fornecedor.';
            }
            Router::redirect("/fornecedores/{$id}/editar");
            return;
        }

        Router::redirect('/fornecedores');
    }

    /**
     * Exclui um fornecedor
     */
    public function excluir($params = [])
    {
        $id = $params['id'] ?? 0;

        $pdo = \getDbConnection();
        
        // Verifica se o fornecedor tem preços vinculados
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM precos WHERE fornecedor_id = ?");
        $stmt->execute([$id]);
        $resultado = $stmt->fetch();

        if ($resultado['total'] > 0) {
            $_SESSION['flash_error'] = 'Não é possível excluir: fornecedor possui preços cadastrados.';
            Router::redirect('/fornecedores');
            return;
        }

        // Exclui o fornecedor
        $stmt = $pdo->prepare("DELETE FROM fornecedores WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['flash_success'] = 'Fornecedor excluído com sucesso!';
        Router::redirect('/fornecedores');
    }

    /**
     * Exibe formulário de importação de planilha
     */
    public function exibirFormularioImportacao($params = [])
    {
        $tituloPagina = "Importar Fornecedores";
        $paginaConteudo = __DIR__ . '/../View/fornecedores/importar.php';

        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();
        echo $view;
    }

    /**
     * Processa a importação de fornecedores via planilha
     */
    public function processarImportacao($params = [])
    {
        if (!isset($_FILES['planilha']) || $_FILES['planilha']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Erro no upload do arquivo.';
            Router::redirect('/fornecedores/importar');
            return;
        }

        // Processa o upload
        $uploadResult = Spreadsheet::processUpload($_FILES['planilha'], ['csv', 'xlsx', 'xls']);
        
        if (!$uploadResult['success']) {
            $_SESSION['flash_error'] = implode(', ', $uploadResult['errors']);
            Router::redirect('/fornecedores/importar');
            return;
        }

        try {
            // Carrega a planilha
            if ($uploadResult['extension'] === 'csv') {
                $spreadsheet = Spreadsheet::loadFromCsv($uploadResult['path']);
            } else {
                // Para Excel, tenta converter para CSV primeiro
                $spreadsheet = Spreadsheet::loadFromExcel($uploadResult['path']);
            }

            $dados = $spreadsheet->getData();
            $headers = $spreadsheet->getHeaders();

            // Valida headers obrigatórios
            $headersObrigatorios = ['razao_social', 'cnpj', 'email'];
            $headersEncontrados = array_map('strtolower', $headers);
            
            foreach ($headersObrigatorios as $header) {
                if (!in_array(strtolower($header), $headersEncontrados)) {
                    $_SESSION['flash_error'] = "Coluna obrigatória não encontrada: {$header}";
                    Router::redirect('/fornecedores/importar');
                    return;
                }
            }

            // Processa os dados
            $pdo = \getDbConnection();
            $sucessos = 0;
            $erros = 0;
            $errosDetalhes = [];

            $stmt = $pdo->prepare("
                INSERT INTO fornecedores (razao_social, cnpj, email, endereco, telefone, ramo_atividade)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                razao_social = VALUES(razao_social),
                email = VALUES(email),
                endereco = VALUES(endereco),
                telefone = VALUES(telefone),
                ramo_atividade = VALUES(ramo_atividade)
            ");

            foreach ($dados as $linha => $fornecedor) {
                try {
                    // Mapeia os campos (case-insensitive)
                    $razaoSocial = '';
                    $cnpj = '';
                    $email = '';
                    $endereco = '';
                    $telefone = '';
                    $ramoAtividade = '';

                    foreach ($fornecedor as $campo => $valor) {
                        $campoLower = strtolower($campo);
                        switch ($campoLower) {
                            case 'razao_social':
                            case 'razão_social':
                            case 'empresa':
                            case 'nome':
                                $razaoSocial = trim($valor);
                                break;
                            case 'cnpj':
                                $cnpj = preg_replace('/\D/', '', $valor);
                                break;
                            case 'email':
                            case 'e-mail':
                                $email = trim($valor);
                                break;
                            case 'endereco':
                            case 'endereço':
                            case 'address':
                                $endereco = trim($valor);
                                break;
                            case 'telefone':
                            case 'phone':
                            case 'tel':
                                $telefone = preg_replace('/\D/', '', $valor);
                                break;
                            case 'ramo_atividade':
                            case 'ramo':
                            case 'atividade':
                                $ramoAtividade = trim($valor);
                                break;
                        }
                    }

                    // Validações
                    if (empty($razaoSocial) || empty($cnpj) || empty($email)) {
                        $erros++;
                        $errosDetalhes[] = "Linha " . ($linha + 2) . ": Dados obrigatórios em branco";
                        continue;
                    }

                    if (!validarCnpj($cnpj)) {
                        $erros++;
                        $errosDetalhes[] = "Linha " . ($linha + 2) . ": CNPJ inválido ({$cnpj})";
                        continue;
                    }

                    if (!validarEmail($email)) {
                        $erros++;
                        $errosDetalhes[] = "Linha " . ($linha + 2) . ": E-mail inválido ({$email})";
                        continue;
                    }

                    // Insere/atualiza no banco
                    $stmt->execute([
                        $razaoSocial,
                        $cnpj,
                        $email,
                        $endereco ?: null,
                        $telefone ?: null,
                        $ramoAtividade ?: null
                    ]);

                    $sucessos++;

                } catch (\Exception $e) {
                    $erros++;
                    $errosDetalhes[] = "Linha " . ($linha + 2) . ": " . $e->getMessage();
                }
            }

            // Remove arquivo temporário
            unlink($uploadResult['path']);

            // Monta mensagem de resultado
            $mensagem = "Importação concluída! {$sucessos} fornecedores processados";
            if ($erros > 0) {
                $mensagem .= ", {$erros} erros encontrados";
                if (count($errosDetalhes) <= 5) {
                    $mensagem .= ": " . implode('; ', $errosDetalhes);
                } else {
                    $mensagem .= ". Primeiros erros: " . implode('; ', array_slice($errosDetalhes, 0, 3));
                }
            }

            if ($erros > 0 && $sucessos === 0) {
                $_SESSION['flash_error'] = $mensagem;
            } else {
                $_SESSION['flash_success'] = $mensagem;
            }

        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao processar planilha: ' . $e->getMessage();
        }

        Router::redirect('/fornecedores');
    }

    /**
     * Gera modelo de planilha para download
     */
    public function gerarModeloPlanilha($params = [])
    {
        $headers = [
            'razao_social',
            'cnpj', 
            'email',
            'endereco',
            'telefone',
            'ramo_atividade'
        ];

        $dadosExemplo = [
            [
                'Empresa Exemplo LTDA',
                '12.345.678/0001-90',
                'contato@empresa.com.br',
                'Rua das Flores, 123 - Centro - Cidade/UF',
                '(11) 99999-9999',
                'Comércio de Materiais'
            ]
        ];

        $tempFile = Spreadsheet::generateTemplate($headers, $dadosExemplo);

        // Headers para download
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="modelo_fornecedores.csv"');
        header('Content-Length: ' . filesize($tempFile));

        // Output do arquivo
        readfile($tempFile);

        // Remove arquivo temporário
        unlink($tempFile);
    }

    /**
     * API: Lista fornecedores em formato JSON
     */
    public function listarJson($params = [])
    {
        $pdo = \getDbConnection();
        $stmt = $pdo->query("
            SELECT id, razao_social, cnpj, email, telefone, ramo_atividade 
            FROM fornecedores 
            ORDER BY razao_social ASC
        ");
        $fornecedores = $stmt->fetchAll();

        // Formata CNPJ para exibição
        foreach ($fornecedores as &$fornecedor) {
            $fornecedor['cnpj_formatado'] = formatarString($fornecedor['cnpj'], '##.###.###/####-##');
            $fornecedor['telefone_formatado'] = $fornecedor['telefone'] ? 
                formatarString($fornecedor['telefone'], '(##) #####-####') : '';
        }

        Router::json($fornecedores);
    }

    /**
     * API: Lista ramos de atividade únicos
     */
    public function listarRamosAtividade($params = [])
    {
        $pdo = \getDbConnection();
        $stmt = $pdo->query("
            SELECT DISTINCT ramo_atividade 
            FROM fornecedores 
            WHERE ramo_atividade IS NOT NULL AND ramo_atividade != ''
            ORDER BY ramo_atividade ASC
        ");
        $ramos = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        Router::json($ramos);
    }

    /**
     * API: Lista fornecedores por ramo de atividade
     */
    public function listarPorRamo($params = [])
    {
        $queryParams = Router::getQueryData();
        $ramo = $queryParams['ramo'] ?? '';

        $pdo = \getDbConnection();
        
        if ($ramo === 'todos' || empty($ramo)) {
            // Busca todos os fornecedores
            $stmt = $pdo->prepare("
                SELECT id, razao_social, cnpj, email 
                FROM fornecedores 
                ORDER BY razao_social ASC
            ");
            $stmt->execute();
        } else {
            // Busca por ramo específico
            $stmt = $pdo->prepare("
                SELECT id, razao_social, cnpj, email 
                FROM fornecedores 
                WHERE ramo_atividade LIKE ?
                ORDER BY razao_social ASC
            ");
            $stmt->execute(["%{$ramo}%"]);
        }
        $fornecedores = $stmt->fetchAll();

        Router::json($fornecedores);
    }
}