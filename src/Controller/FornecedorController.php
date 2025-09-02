<?php

namespace Joabe\Buscaprecos\Controller;

// 'USE' para importar arquivos em massa para fornecedores
use PhpOffice\PhpSpreadsheet\IOFactory;
use Slim\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Cell\DataType; 

class FornecedorController
{
    public function listar($request, $response, $args)
    {
        $pdo = \getDbConnection();
        $stmt = $pdo->query("SELECT * FROM fornecedores ORDER BY razao_social ASC");
        $fornecedores = $stmt->fetchAll();

        $tituloPagina = "Fornecedores Cadastrados";
        $paginaConteudo = __DIR__ . '/../View/fornecedores/lista.php';

        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();

        $response->getBody()->write($view);
        return $response;
    }

    public function exibirFormulario($request, $response, $args)
    {
        $tituloPagina = "Novo Fornecedor";
        $paginaConteudo = __DIR__ . '/../View/fornecedores/formulario.php';

        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();

        $response->getBody()->write($view);
        return $response;
    }

    public function criar($request, $response, $args)
    {
        $dados = $request->getParsedBody();

        // --- INÍCIO DA CORREÇÃO ---
        $cnpjLimpo = preg_replace('/\D/', '', $dados['cnpj']);
        $telefoneLimpo = preg_replace('/\D/', '', $dados['telefone'] ?? '');
        // --- FIM DA CORREÇÃO ---
        // Verifica se o CNPJ já está cadastrado        
        $sql = "INSERT INTO fornecedores (razao_social, cnpj, email, endereco, telefone, ramo_atividade) VALUES (?, ?, ?, ?, ?, ?)";
    
    $pdo = \getDbConnection();
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([
            $dados['razao_social'],
            $dados['cnpj'],
            $dados['email'],
            $dados['endereco'] ?? null, // Salva o novo campo de endereço
            $dados['telefone'] ?? null,
            $dados['ramo_atividade'] ?? null
        ]);
        } catch (\PDOException $e) {
            // Tratar erro de CNPJ duplicado
            if ($e->getCode() == 23000) { 
                // Você pode implementar uma flash message de erro aqui
                die("Erro: O CNPJ informado já está cadastrado.");
            }
            throw $e;
        }

        return $response->withHeader('Location', '/fornecedores')->withStatus(302);
    }

    /* Retorna a lista de fornecedores em formato JSON para a API. */

    public function listarJson($request, $response, $args)
    {
        $pdo = \getDbConnection();
        $stmt = $pdo->query("SELECT id, razao_social, ramo_atividade FROM fornecedores ORDER BY razao_social ASC");
        $fornecedores = $stmt->fetchAll();

        return $response->withJson($fornecedores);
    }

    public function listarRamosAtividade($request, $response, $args)
    {
        $pdo = \getDbConnection();
        $stmt = $pdo->query("SELECT DISTINCT ramo_atividade FROM fornecedores WHERE ramo_atividade IS NOT NULL AND ramo_atividade != ''");
        $ramosDb = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $ramosUnicos = [];
        foreach ($ramosDb as $ramoString) {
            $ramos = array_map('trim', explode(',', $ramoString));
            $ramosUnicos = array_merge($ramosUnicos, $ramos);
        }

        $ramosUnicos = array_unique(array_filter($ramosUnicos));
        sort($ramosUnicos);

        return $response->withJson($ramosUnicos);
    }

    /**
     * Lista fornecedores, opcionalmente filtrando por ramo de atividade.
     */
    public function listarPorRamo($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $ramo = $params['ramo'] ?? 'todos';

        $pdo = \getDbConnection();
        $sql = "SELECT id, razao_social FROM fornecedores";

        if ($ramo !== 'todos') {
            $sql .= " WHERE ramo_atividade LIKE ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(["%{$ramo}%"]);
        } else {
            $stmt = $pdo->query($sql);
        }
        
        $fornecedores = $stmt->fetchAll();
        return $response->withJson($fornecedores);
    }

    /**
     * Exibe o formulário de edição para um fornecedor específico.
     */

    public function exibirFormularioEdicao($request, $response, $args)
    {
        $id = $args['id'];
        $pdo = \getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM fornecedores WHERE id = ?");
        $stmt->execute([$id]);
        $fornecedor = $stmt->fetch();

        if (!$fornecedor) {
            // Idealmente, redirecionar ou mostrar uma página de erro
            $response->getBody()->write("Fornecedor não encontrado.");
            return $response->withStatus(404);
        }

        $tituloPagina = "Editar Fornecedor";
        $paginaConteudo = __DIR__ . '/../View/fornecedores/formulario_edicao.php';

        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();

        $response->getBody()->write($view);
        return $response;
    }

    /**
     * Salva as alterações de um fornecedor no banco de dados.
     */
    public function atualizar($request, $response, $args)
    {
        $id = $args['id'];
        $dados = $request->getParsedBody();

        $cnpjLimpo = preg_replace('/\D/', '', $dados['cnpj']);
        $telefoneLimpo = preg_replace('/\D/', '', $dados['telefone'] ?? '');

        $sql = "UPDATE fornecedores SET 
                    razao_social = ?, 
                    cnpj = ?, 
                    email = ?, 
                    endereco = ?, 
                    telefone = ?, 
                    ramo_atividade = ? 
                WHERE id = ?";
        
        $pdo = \getDbConnection();
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            $dados['razao_social'],
            $dados['cnpj'],
            $dados['email'],
            $dados['endereco'] ?? null,
            $dados['telefone'] ?? null,
            $dados['ramo_atividade'] ?? null,
            $id
        ]);

        return $response->withHeader('Location', '/fornecedores')->withStatus(302);
    }

    /**
     * Exclui um fornecedor do banco de dados.
     */
    public function excluir($request, $response, $args)
    {
        $id = $args['id'];
        $pdo = \getDbConnection();
        
        // Cuidado: Em uma aplicação real, você pode querer verificar se o fornecedor
        // não está associado a nenhuma cotação antes de excluir.
        $stmt = $pdo->prepare("DELETE FROM fornecedores WHERE id = ?");
        $stmt->execute([$id]);

        return $response->withHeader('Location', '/fornecedores')->withStatus(302);
    }

    /**
     * Exibe o formulário para importação de fornecedores.
     */
    public function exibirFormularioImportacao($request, $response, $args)
    {
        $tituloPagina = "Importar Fornecedores";
        $paginaConteudo = __DIR__ . '/../View/fornecedores/importar.php';

        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();

        $response->getBody()->write($view);
        return $response;
    }

    /**
     * Processa a planilha enviada para importar fornecedores em massa.
     */
    public function processarImportacao($request, $response, $args)
    {
        $uploadedFiles = $request->getUploadedFiles();
        $arquivoPlanilha = $uploadedFiles['arquivo_planilha'] ?? null;

        // 1. Validação inicial do upload
        if (!$arquivoPlanilha || $arquivoPlanilha->getError() !== UPLOAD_ERR_OK) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Erro no upload do arquivo. Verifique se o arquivo foi selecionado e tente novamente.'];
            return $response->withHeader('Location', '/fornecedores/importar')->withStatus(302);
        }

        $pdo = \getDbConnection();
        $sql = "INSERT INTO fornecedores (razao_social, cnpj, email, endereco, telefone, ramo_atividade) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        $sucesso = 0;
        $erros = 0;
        $linhasComErro = [];

        try {
            // 2. Carrega a planilha usando a biblioteca PhpSpreadsheet
            $spreadsheet = IOFactory::load($arquivoPlanilha->getStream()->getMetadata('uri'));
            $sheet = $spreadsheet->getActiveSheet();
            
            $pdo->beginTransaction();

            // 3. Itera sobre cada linha da planilha, começando da segunda (para pular o cabeçalho)
            foreach ($sheet->getRowIterator(2) as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false); // Garante que células vazias sejam lidas
                $cells = [];
                foreach ($cellIterator as $cell) {
                    $cells[] = $cell->getValue();
                }

                // 4. Mapeia as células para as variáveis, limpando os dados
                $razaoSocial = trim($cells[0] ?? '');
                $cnpj        = preg_replace('/\D/', '', trim($cells[1] ?? '')); // Remove caracteres não numéricos do CNPJ
                $email       = trim($cells[2] ?? '');
                
                // Validação mínima para os campos obrigatórios
                if (empty($razaoSocial) || empty($cnpj) || empty($email)) {
                    $erros++;
                    $linhasComErro[] = $row->getRowIndex();
                    continue; // Pula esta linha e vai para a próxima
                }

                $endereco    = trim($cells[3] ?? null);
                $telefone    = preg_replace('/\D/', '', trim($cells[4] ?? '')); // Remove caracteres não numéricos do Telefone
                $ramo        = trim($cells[5] ?? null);

                // 5. Tenta inserir no banco de dados
                try {
                    $stmt->execute([$razaoSocial, $cnpj, $email, $endereco, $telefone, $ramo]);
                    $sucesso++;
                } catch (\PDOException $e) {
                    // Captura erros como CNPJ duplicado e conta como erro
                    $erros++;
                    $linhasComErro[] = $row->getRowIndex();
                }
            }
            
            $pdo->commit(); // Confirma a transação se tudo correu bem

        } catch (\Exception $e) {
            // Desfaz a transação em caso de erro na leitura da planilha
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Erro ao processar a planilha: ' . $e->getMessage()];
            return $response->withHeader('Location', '/fornecedores/importar')->withStatus(302);
        }
        
        // 6. Prepara a mensagem de feedback para o usuário
        $mensagem = "Importação concluída!\n\nFornecedores adicionados com sucesso: {$sucesso}";
        if ($erros > 0) {
            $mensagem .= "\nLinhas com erro ou ignoradas (ex: CNPJ duplicado): {$erros}";
            $mensagem .= "\n(Referente às linhas da planilha: " . implode(', ', $linhasComErro) . ")";
        }

        $_SESSION['flash'] = ['tipo' => ($erros > 0 ? 'warning' : 'success'), 'mensagem' => $mensagem];
        return $response->withHeader('Location', '/fornecedores/importar')->withStatus(302);
    }

    /**
     * Gera e força o download de uma planilha .xlsx de modelo para importação.
     */
    public function gerarModeloPlanilha($request, $response, $args)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Modelo de Fornecedores');

        // Define o cabeçalho
        $sheet->setCellValue('A1', 'Razão Social (Obrigatório)');
        $sheet->setCellValue('B1', 'CNPJ (Obrigatório, apenas números)');
        $sheet->setCellValue('C1', 'E-mail (Obrigatório)');
        $sheet->setCellValue('D1', 'Endereço Completo');
        $sheet->setCellValue('E1', 'Telefone');
        $sheet->setCellValue('F1', 'Ramo de Atividade (separado por vírgula)');

        // Adiciona um exemplo na linha 2 para guiar o usuário
        $sheet->setCellValue('A2', 'Empresa Exemplo LTDA');
        $sheet->setCellValueExplicit('B2','11222333000199',DataType::TYPE_STRING);
        $sheet->setCellValue('C2', 'contato@empresaexemplo.com.br');
        $sheet->setCellValue('D2', 'Rua das Flores, 123, Centro, São Paulo - SP');
        $sheet->setCellValue('E2', '11987654321');
        $sheet->setCellValue('F2', 'Material de escritório, Informática');

        // Aplica estilo ao cabeçalho
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFDDDDDD'],
            ],
        ];
        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

        // Ajusta a largura das colunas automaticamente
        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // =======================================================
        //     INÍCIO DA CORREÇÃO: LÓGICA DE SAÍDA SIMPLIFICADA
        // =======================================================
        $writer = new Xlsx($spreadsheet);
        
        // Inicia o buffer de saída para capturar o arquivo gerado
        ob_start();
        $writer->save('php://output');
        $fileContent = ob_get_clean();

        // Escreve o conteúdo capturado no corpo da resposta
        $response->getBody()->write($fileContent);

        // Retorna a resposta com os cabeçalhos corretos para forçar o download
        return $response
            ->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->withHeader('Content-Disposition', 'attachment;filename="modelo_importacao_fornecedores.xlsx"');
        // =======================================================
        //                      FIM DA CORREÇÃO
        // =======================================================
    }
    
}