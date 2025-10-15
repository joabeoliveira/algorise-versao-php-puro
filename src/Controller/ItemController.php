<?php

namespace Joabe\Buscaprecos\Controller;

use Joabe\Buscaprecos\Core\Router;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ItemController
{
    public function listar($params = [])
    {
        $processo_id = $params['processo_id'] ?? 0;
        $pdo = \getDbConnection();

        // 1. Busca os dados do processo pai para exibir o nome na página
        $stmtProcesso = $pdo->prepare("SELECT * FROM processos WHERE id = ?");
        $stmtProcesso->execute([$processo_id]);
        $processo = $stmtProcesso->fetch();

        if (!$processo) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Processo não encontrado.'];
            \Joabe\Buscaprecos\Core\Router::redirect('/processos');
            return;
        }

        // 2. Busca a lista de itens existentes para este processo
        $stmtItens = $pdo->prepare("SELECT * FROM itens WHERE processo_id = ? ORDER BY numero_item ASC");
        $stmtItens->execute([$processo_id]);
        $itens = $stmtItens->fetchAll();

        // 3. Conta o total de itens para a lógica condicional na view
        $stmtCount = $pdo->prepare("SELECT COUNT(id) as total FROM itens WHERE processo_id = ?");
        $stmtCount->execute([$processo_id]);
        $totalItens = $stmtCount->fetchColumn();

        // 4. Prepara as variáveis e renderiza a view
        $tituloPagina = "Itens do Processo: " . htmlspecialchars($processo['nome_processo']);
        $paginaConteudo = __DIR__ . '/../View/itens/lista.php';

        ob_start();
        // As variáveis $processo, $itens, e $totalItens estarão disponíveis na view 'lista.php'
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();

        echo $view;
    }

    // NOVO MÉTODO: Salva o novo item no banco de dados
    public function criar($params = [])
    {
        try {
            $processo_id = $params['processo_id'];
            $dados = \Joabe\Buscaprecos\Core\Router::getPostData();
            $pdo = \getDbConnection();
            $redirectUrl = "/processos/{$processo_id}/itens";

            // SQL DIRETO - sem DatabaseHelper
            
            // Validação básica
            if (empty($dados['descricao'])) {
                $_SESSION['flash'] = [
                    'tipo' => 'danger',
                    'mensagem' => 'A descrição do item é obrigatória.',
                    'dados_formulario' => $dados
                ];
                \Joabe\Buscaprecos\Core\Router::redirect($redirectUrl);
                return;
            }

            // Verificar duplicidade de número do item
            if (!empty($dados['numero_item'])) {
                $sqlVerifica = "SELECT COUNT(*) FROM itens WHERE processo_id = ? AND numero_item = ?";
                $stmtVerifica = $pdo->prepare($sqlVerifica);
                $stmtVerifica->execute([$processo_id, $dados['numero_item']]);

                if ($stmtVerifica->fetchColumn() > 0) {
                    $_SESSION['flash'] = [
                        'tipo' => 'danger',
                        'mensagem' => 'Já existe um item com este número.',
                        'dados_formulario' => $dados
                    ];
                    \Joabe\Buscaprecos\Core\Router::redirect($redirectUrl);
                    return;
                }
            }

            // SQL DIRETO - inserir item (nomes corretos das colunas)
            error_log("=== CRIAR ITEM ===");
            error_log("Dados recebidos: " . json_encode($dados));
            
            $stmt = $pdo->prepare("
                INSERT INTO itens (processo_id, numero_item, catmat_catser, descricao, unidade_medida, quantidade, valor_estimado, data_criacao) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $params = [
                $processo_id,
                $dados['numero_item'] ?? null,
                $dados['catmat'] ?? $dados['catmat_catser'] ?? null,
                $dados['descricao'] ?? '',
                $dados['unidade_medida'] ?? $dados['unidade'] ?? 'UN',
                $dados['quantidade'] ?? 1,
                $dados['valor_estimado'] ?? $dados['valor_unitario'] ?? null
            ];
            
            error_log("Params: " . json_encode($params));
            $stmt->execute($params);
            


            // SUCESSO: Salva a mensagem de sucesso na sessão.
            $_SESSION['flash'] = [
                'tipo' => 'success',
                'mensagem' => 'Item adicionado com sucesso!'
            ];

            \Joabe\Buscaprecos\Core\Router::redirect($redirectUrl);
            
        } catch (\Exception $e) {
            error_log("Erro ao criar item: " . $e->getMessage());
            $_SESSION['flash'] = [
                'tipo' => 'danger',
                'mensagem' => 'Erro ao criar item: ' . $e->getMessage(),
                'dados_formulario' => $dados ?? []
            ];
            \Joabe\Buscaprecos\Core\Router::redirect($redirectUrl);
        }
    }
    public function exibirFormularioEdicao($params = [])
    {
        $processo_id = $params['processo_id'];
    $item_id = $params['item_id'];
    $pdo = \getDbConnection();

    // Busca o processo pai
    $stmtProcesso = $pdo->prepare("SELECT * FROM processos WHERE id = ?");
    $stmtProcesso->execute([$processo_id]);
    $processo = $stmtProcesso->fetch();

    // Busca o item específico
    $stmtItem = $pdo->prepare("SELECT * FROM itens WHERE id = ? AND processo_id = ?");
    $stmtItem->execute([$item_id, $processo_id]);
    $item = $stmtItem->fetch();

    if (!$processo || !$item) {
        $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Processo ou item não encontrado.'];
        \Joabe\Buscaprecos\Core\Router::redirect('/processos');
        return;
    }

    // Prepara as variáveis e chama o layout principal
    $tituloPagina = "Editar Item";
    $paginaConteudo = __DIR__ . '/../View/itens/formulario_edicao.php';

    ob_start();
    require __DIR__ . '/../View/layout/main.php';
    $view = ob_get_clean();

    echo $view;
    }

    // NOVO MÉTODO: Recebe os dados do formulário e atualiza o item no banco
    public function atualizar($params = [])
    {
        $processo_id = $params['processo_id'];
        $item_id = $params['item_id'];
        $dados = \Joabe\Buscaprecos\Core\Router::getPostData();
        $redirectUrl = "/processos/{$processo_id}/itens";
        $editUrl = "/processos/{$processo_id}/itens/{$item_id}/editar";

        try {
            $pdo = \getDbConnection();

            // Validação de duplicidade para numero_item
            if (!empty($dados['numero_item'])) {
                $sqlVerificaNum = "SELECT COUNT(*) FROM itens WHERE processo_id = ? AND numero_item = ? AND id != ?";
                $stmtVerificaNum = $pdo->prepare($sqlVerificaNum);
                $stmtVerificaNum->execute([$processo_id, $dados['numero_item'], $item_id]);
                if ($stmtVerificaNum->fetchColumn() > 0) {
                    $_SESSION['flash'] = [
                        'tipo' => 'danger',
                        'mensagem' => 'Já existe um item com este número neste processo.',
                        'dados_formulario' => $dados
                    ];
                    \Joabe\Buscaprecos\Core\Router::redirect($editUrl);
                    return;
                }
            }

            // Validação de duplicidade para catmat_catser
            if (!empty($dados['catmat_catser'])) {
                $sqlVerificaCatmat = "SELECT COUNT(*) FROM itens WHERE processo_id = ? AND catmat_catser = ? AND id != ?";
                $stmtVerificaCatmat = $pdo->prepare($sqlVerificaCatmat);
                $stmtVerificaCatmat->execute([$processo_id, $dados['catmat_catser'], $item_id]);
                if ($stmtVerificaCatmat->fetchColumn() > 0) {
                    $_SESSION['flash'] = [
                        'tipo' => 'danger',
                        'mensagem' => 'Já existe um item com este CATMAT/CATSER neste processo.',
                        'dados_formulario' => $dados
                    ];
                    \Joabe\Buscaprecos\Core\Router::redirect($editUrl);
                    return;
                }
            }

            // Colunas corretas: numero_item, catmat_catser, descricao, unidade_medida, quantidade, valor_estimado, data_atualizacao
            error_log("=== ATUALIZAR ITEM ===");
            error_log("Item ID: $item_id, Processo ID: $processo_id");
            error_log("Dados: " . json_encode($dados));
            
            $stmt = $pdo->prepare("
                UPDATE itens 
                SET numero_item = ?, catmat_catser = ?, descricao = ?, unidade_medida = ?, quantidade = ?, valor_estimado = ?, data_atualizacao = NOW()
                WHERE id = ? AND processo_id = ?
            ");
            
            $params = [
                $dados['numero_item'] ?? null,
                $dados['catmat'] ?? $dados['catmat_catser'] ?? null,
                $dados['descricao'] ?? '',
                $dados['unidade_medida'] ?? $dados['unidade'] ?? 'UN',
                $dados['quantidade'] ?? 1,
                $dados['valor_estimado'] ?? $dados['valor_unitario'] ?? null,
                $item_id,
                $processo_id
            ];
            
            error_log("Params: " . json_encode($params));
            $stmt->execute($params);

            $_SESSION['flash'] = ['tipo' => 'success', 'mensagem' => 'Item atualizado com sucesso.'];
            \Joabe\Buscaprecos\Core\Router::redirect($redirectUrl);

        } catch (\PDOException $e) {
            error_log("Erro ao atualizar item: " . $e->getMessage());
            $_SESSION['flash'] = [
                'tipo' => 'danger',
                'mensagem' => 'Ocorreu um erro inesperado ao atualizar o item. Tente novamente.',
                'dados_formulario' => $dados
            ];
            \Joabe\Buscaprecos\Core\Router::redirect($editUrl);
        }
    }

    // NOVO MÉTODO: Processa a exclusão de um item
    public function excluir($params = [])
{
    $processo_id = $params['processo_id'];
    $item_id = $params['item_id'];
    $redirectUrl = "/processos/{$processo_id}/itens";

    try {
        $pdo = \getDbConnection();
        $stmt = $pdo->prepare("DELETE FROM itens WHERE id = ? AND processo_id = ?");
        $stmt->execute([$item_id, $processo_id]);

        $_SESSION['flash'] = ['tipo' => 'success', 'mensagem' => 'Item excluído com sucesso.'];

    } catch (\PDOException $e) {
        error_log("Erro ao excluir item: " . $e->getMessage());
        if ($e->getCode() == 23000) { // Foreign key constraint
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Não é possível excluir o item, pois ele possui cotações ou preços associados.'];
        } else {
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Ocorreu um erro inesperado ao excluir o item.'];
        }
    }

    \Joabe\Buscaprecos\Core\Router::redirect($redirectUrl);
}

    //     MÉTODOS PARA IMPORTAÇÃO DE ITENS
    // ===============================================

    public function exibirFormularioImportacao($params = [])
    {
        $processo_id = $params['processo_id'];
        $pdo = \getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM processos WHERE id = ?");
        $stmt->execute([$processo_id]);
        $processo = $stmt->fetch();

        // Validação extra no servidor
        $stmtCount = $pdo->prepare("SELECT COUNT(id) as total FROM itens WHERE processo_id = ?");
        $stmtCount->execute([$processo_id]);
        if ($stmtCount->fetchColumn() > 0) {
            \Joabe\Buscaprecos\Core\Router::redirect("/processos/{$processo_id}/itens");
        }

        $tituloPagina = "Importar Itens";
        $paginaConteudo = __DIR__ . '/../View/itens/importar.php';
        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();
        echo $view;
    }

    public function processarImportacao($params = [])
{
    $processo_id = $params['processo_id'];
    $uploadedFiles = $_FILES;
    $arquivoPlanilha = $uploadedFiles['arquivo_planilha'] ?? null;
    $redirectUrl = "/processos/{$processo_id}/itens/importar";

    if (!$arquivoPlanilha || $arquivoPlanilha['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Erro no upload do arquivo.'];
        \Joabe\Buscaprecos\Core\Router::redirect($redirectUrl);
        return; // Adicionado para garantir que a execução pare
    }

    try {
        $spreadsheet = IOFactory::load($arquivoPlanilha['tmp_name']);
        $sheet = $spreadsheet->getActiveSheet();
        
        $linhasParaImportar = [];
        $errosValidacao = [];

        // FASE 1: PRÉ-VALIDAÇÃO
        foreach ($sheet->getRowIterator(2) as $row) {
            $numLinha = $row->getRowIndex();
            $cells = [];
            foreach ($row->getCellIterator('A', 'E') as $cell) {
                $cells[] = $cell->getValue();
            }

            $numeroItem = filter_var($cells[0] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $catmat     = preg_replace('/\D/', '', trim($cells[1] ?? ''));
            $descricao  = trim($cells[2] ?? '');
            $unidade    = trim($cells[3] ?? '');
            $quantidade = filter_var($cells[4] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            if (empty($numeroItem) && empty($catmat) && empty($descricao)) { continue; } // Ignora linha vazia

            if ($numeroItem === false || empty($catmat) || empty($descricao) || empty($unidade) || $quantidade === false) {
                $errosValidacao[] = $numLinha;
            } else {
                $linhasParaImportar[] = [
                    'numero_item'   => $numeroItem,
                    'catmat_catser' => $catmat,
                    'descricao'     => $descricao,
                    'unidade_medida'=> $unidade,
                    'quantidade'    => $quantidade,
                    'processo_id'   => $processo_id // Adiciona o processo_id aqui
                ];
            }
        }

        if (!empty($errosValidacao)) {
            $mensagemErro = "A importação foi cancelada. Todos os campos são obrigatórios e devem ser válidos. Verifique as seguintes linhas: " . implode(', ', $errosValidacao);
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => $mensagemErro];
            \Joabe\Buscaprecos\Core\Router::redirect($redirectUrl);
            return; // Adicionado para garantir
        }

        if (empty($linhasParaImportar)) {
            $_SESSION['flash'] = ['tipo' => 'info', 'mensagem' => 'Nenhum item válido para importar foi encontrado na planilha.'];
            \Joabe\Buscaprecos\Core\Router::redirect($redirectUrl);
            return; // Adicionado para garantir
        }

        // FASE 2: IMPORTAÇÃO NO BANCO
        $pdo = \getDbConnection();
        
        // Montagem dinâmica do SQL
        $primeiraLinha = $linhasParaImportar[0];
        $colunas = array_keys($primeiraLinha);
        $placeholders = array_fill(0, count($colunas), '?');
        $sql = "INSERT INTO itens (" . implode(', ', $colunas) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $pdo->prepare($sql);
        
        $pdo->beginTransaction();
        foreach ($linhasParaImportar as $item) {
            $stmt->execute(array_values($item));
        }
        $pdo->commit();

    } catch (\Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) { $pdo->rollBack(); }
        error_log("Erro ao processar planilha: " . $e->getMessage()); // Log do erro real
        $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Erro crítico ao processar a planilha. Verifique o formato do arquivo e os dados.'];
        \Joabe\Buscaprecos\Core\Router::redirect($redirectUrl);
        return; // Adicionado para garantir
    }
    
    $_SESSION['flash'] = ['tipo' => 'success', 'mensagem' => "Importação concluída! " . count($linhasParaImportar) . " itens foram adicionados com sucesso."];
    \Joabe\Buscaprecos\Core\Router::redirect("/processos/{$processo_id}/itens");
}

    public function gerarModeloPlanilha($params = [])
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Modelo de Itens');

        // --- INÍCIO DA ALTERAÇÃO ---
        $sheet->setCellValue('A1', 'Nº do Item (Obrigatório, ex: 1, 2, 3)');
        $sheet->setCellValue('B1', 'CATMAT/CATSER (Obrigatório)');
        $sheet->setCellValue('C1', 'Descrição Completa do Item (Obrigatório)');
        $sheet->setCellValue('D1', 'Unidade de Medida (Obrigatório)');
        $sheet->setCellValue('E1', 'Quantidade (Obrigatório, apenas números)');

        $sheet->setCellValue('A2', 1);
        $sheet->setCellValueExplicit('B2', '472839', DataType::TYPE_STRING);
        $sheet->setCellValue('C2', 'CANETA ESFEROGRÁFICA, COR AZUL, PONTA 1.0MM');
        $sheet->setCellValue('D2', 'UN');
        $sheet->setCellValue('E2', 100);
        
        $headerStyle = ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDDDDDD']]];
        $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);
        foreach (range('A', 'E') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    // --- FIM DA ALTERAÇÃO ---

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $fileContent = ob_get_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="modelo_importacao_itens.xlsx"');
        echo $fileContent;
    }
    // FIM DOS MÉTODOS PARA IMPORTAÇÃO DE ITENS


    

    
}