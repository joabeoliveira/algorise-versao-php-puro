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
            echo "Processo não encontrado.";
            http_response_code(404);
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
    $processo_id = $params['processo_id'];
    $dados = \Joabe\Buscaprecos\Core\Router::getPostData();
    $pdo = \getDbConnection();
    $redirectUrl = "/processos/{$processo_id}/itens"; // URL de redirecionamento padrão

    // Validação de duplicidade
    $catmat = !empty($dados['catmat_catser']) ? $dados['catmat_catser'] : null;
    $sqlVerifica = "SELECT COUNT(*) FROM itens WHERE processo_id = ? AND (numero_item = ? OR (catmat_catser IS NOT NULL AND catmat_catser != '' AND catmat_catser = ?))";
    $stmtVerifica = $pdo->prepare($sqlVerifica);
    $stmtVerifica->execute([$processo_id, $dados['numero_item'], $catmat]);

    if ($stmtVerifica->fetchColumn() > 0) {
        // ERRO: Item duplicado. Salva a mensagem de erro e os dados do formulário na sessão.
        $_SESSION['flash'] = [
            'tipo' => 'danger',
            'mensagem' => 'Erro: Já existe um item com este Número ou CATMAT.',
            'dados_formulario' => $dados
        ];
        \Joabe\Buscaprecos\Core\Router::redirect($redirectUrl);
    }

    // Se passou na validação, executa o INSERT
    $sql = "INSERT INTO itens (processo_id, numero_item, catmat_catser, descricao, unidade_medida, quantidade) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $processo_id, $dados['numero_item'], $catmat,
        $dados['descricao'], $dados['unidade_medida'],
        $dados['quantidade']
    ]);

    // SUCESSO: Salva a mensagem de sucesso na sessão.
    $_SESSION['flash'] = [
        'tipo' => 'success',
        'mensagem' => 'Item adicionado com sucesso!'
    ];

    \Joabe\Buscaprecos\Core\Router::redirect($redirectUrl);
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
        echo "Processo ou item não encontrado.";
        http_response_code(404);
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
    $pdo = \getDbConnection();

    // --- INÍCIO DA VALIDAÇÃO ANTI-DUPLICIDADE NA EDIÇÃO ---
    $catmat = !empty($dados['catmat_catser']) ? $dados['catmat_catser'] : null;

    // A query agora inclui "AND id != ?" para ignorar o próprio item na verificação
    // e também verifica se o catmat não está vazio antes de comparar.
    $sqlVerifica = "SELECT COUNT(*) FROM itens WHERE processo_id = ? AND (numero_item = ? OR (catmat_catser IS NOT NULL AND catmat_catser = ?)) AND id != ?";
    $stmtVerifica = $pdo->prepare($sqlVerifica);
    $stmtVerifica->execute([$processo_id, $dados['numero_item'], $catmat, $item_id]);
    $count = $stmtVerifica->fetchColumn();

    if ($count > 0) {
        // Se encontrou duplicado, redireciona de volta com uma mensagem de erro
        $redirectUrl = "/processos/{$processo_id}/itens?erro=duplicado";
        \Joabe\Buscaprecos\Core\Router::redirect($redirectUrl);
    }
    // --- FIM DA VALIDAÇÃO ---

    // Se passou na validação, continua com o UPDATE
    $sql = "UPDATE itens SET 
                numero_item = ?, 
                catmat_catser = ?, 
                descricao = ?, 
                unidade_medida = ?, 
                quantidade = ? 
            WHERE id = ? AND processo_id = ?";
    
    $stmt = $pdo->prepare($sql);
    
    $stmt->execute([
        $dados['numero_item'],
        $catmat, // Usa a variável tratada
        $dados['descricao'],
        $dados['unidade_medida'],
        $dados['quantidade'],
        $item_id,
        $processo_id
    ]);
    // Redireciona de volta para a lista de itens do processo
    \Joabe\Buscaprecos\Core\Router::redirect('/processos/{$processo_id}/itens'); return;

}

    // NOVO MÉTODO: Processa a exclusão de um item
    public function excluir($params = [])
{
    $processo_id = $params['processo_id'];
    $item_id = $params['item_id'];
    $pdo = \getDbConnection();

    // Prepara e executa a query de exclusão
    $sql = "DELETE FROM itens WHERE id = ? AND processo_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$item_id, $processo_id]);

    // Redireciona de volta para a lista de itens do processo
    \Joabe\Buscaprecos\Core\Router::redirect("/processos/{$processo_id}/itens");

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
    }

    try {
        $spreadsheet = IOFactory::load($arquivoPlanilha['tmp_name']);
        $sheet = $spreadsheet->getActiveSheet();
        
        $linhasParaImportar = [];
        $errosValidacao = [];

        // FASE 1: PRÉ-VALIDAÇÃO (Lê todas as linhas antes de tocar no banco)
        foreach ($sheet->getRowIterator(2) as $row) {
            $numLinha = $row->getRowIndex();
            $cells = [];
            foreach ($row->getCellIterator('A', 'E') as $cell) {
                $cells[] = $cell->getValue();
            }

            // --- INÍCIO DA ALTERAÇÃO ---
            $numeroItem = filter_var($cells[0] ?? 0, FILTER_VALIDATE_INT);
            $catmat     = preg_replace('/\D/', '', trim($cells[1] ?? ''));
            $descricao  = trim($cells[2] ?? '');
            $unidade    = trim($cells[3] ?? '');
            $quantidade = filter_var($cells[4] ?? 0, FILTER_VALIDATE_INT);

            if (empty($numeroItem) && empty($catmat) && empty($descricao)) { continue; } // Ignora linha totalmente vazia

            // Validação rigorosa
            if ($numeroItem === false || $numeroItem <= 0 || empty($catmat) || empty($descricao) || empty($unidade) || $quantidade === false || $quantidade <= 0) {
                $errosValidacao[] = $numLinha;
            } else {
                $linhasParaImportar[] = [
                    'numero_item'   => $numeroItem,
                    'catmat_catser' => $catmat,
                    'descricao'     => $descricao,
                    'unidade_medida'=> $unidade,
                    'quantidade'    => $quantidade,
                ];
            }
        }
        // --- FIM DA ALTERAÇÃO ---

        if (!empty($errosValidacao)) {
            $mensagemErro = "A importação foi cancelada. Todos os campos são obrigatórios. Verifique as seguintes linhas na sua planilha: " . implode(', ', $errosValidacao);
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => $mensagemErro];
            \Joabe\Buscaprecos\Core\Router::redirect($redirectUrl);
        }

        // FASE 2: IMPORTAÇÃO NO BANCO DE DADOS
        $pdo = \getDbConnection();
        // --- INÍCIO DA ALTERAÇÃO NO SQL ---
        $sql = "INSERT INTO itens (numero_item, catmat_catser, descricao, unidade_medida, quantidade, processo_id) VALUES (?, ?, ?, ?, ?, ?)";
        // --- FIM DA ALTERAÇÃO NO SQL ---
        $stmt = $pdo->prepare($sql);
        
        $pdo->beginTransaction();
        foreach ($linhasParaImportar as $item) {
            // --- INÍCIO DA ALTERAÇÃO NO EXECUTE ---
            $stmt->execute([
                $item['numero_item'],
                $item['catmat_catser'],
                $item['descricao'],
                $item['unidade_medida'],
                $item['quantidade'],
                $processo_id
            ]);
            // --- FIM DA ALTERAÇÃO NO EXECUTE ---
        }
        $pdo->commit();

    } catch (\Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) { $pdo->rollBack(); }
        $_SESSION['flash'] = ['tipo' => 'danger', 'mensagem' => 'Erro crítico ao processar a planilha: ' . $e->getMessage()];
        \Joabe\Buscaprecos\Core\Router::redirect($redirectUrl);
    }
    
    $_SESSION['flash'] = ['tipo' => 'success', 'mensagem' => "Importação concluída! " . count($linhasParaImportar) . " itens foram adicionados com sucesso ao processo."];
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