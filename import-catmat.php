<?php
/**
 * Script para importar dados do CSV para a tabela `catmat`.
 *
 * Uso: php import-catmat.php
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/settings-php-puro.php';

// --- Configurações ---
$csvFilePath = __DIR__ . '/catmat_cloud_sql.csv';
$tableName = 'catmat';

// --- Verificações Iniciais ---
if (!file_exists($csvFilePath)) {
    echo "ERRO: Arquivo CSV não encontrado em: " . $csvFilePath . "\n";
    exit(1);
}

echo "========================================\n";
echo "INICIANDO IMPORTAÇÃO PARA A TABELA `catmat`\n";
echo "========================================\n";
echo "Arquivo CSV: " . $csvFilePath . "\n";

try {
    $pdo = getDbConnection();
    echo "✓ Conectado ao banco de dados com sucesso.\n";

    // --- Limpar a tabela antes de importar (Opcional) ---
    // Descomente a linha abaixo se quiser limpar a tabela antes de cada importação
    $pdo->exec("TRUNCATE TABLE {$tableName}");
    echo "✓ Tabela `{$tableName}` foi limpa (TRUNCATE).\n";

    // --- Comando SQL para Importação ---
    // A primeira coluna do CSV (id_catmat) é ignorada e carregada em uma variável dummy (@id_catmat_dummy)
    // O banco de dados irá gerar o ID automaticamente.
    // O caminho do arquivo precisa ser escapado e colocado diretamente na query.
    $escapedCsvFilePath = str_replace('\\', '/', $csvFilePath); // Usar barras normais para compatibilidade
    
    $sql = "
        LOAD DATA LOCAL INFILE '{$escapedCsvFilePath}'
        INTO TABLE catmat
        CHARACTER SET utf8mb4
        FIELDS TERMINATED BY ','
        ENCLOSED BY '\"'
        LINES TERMINATED BY '\r\n'
        IGNORE 1 ROWS
        (
            @id_catmat_dummy,
            codigo_do_grupo,
            nome_do_grupo,
            codigo_da_classe,
            nome_da_classe,
            codigo_do_pdm,
            nome_do_pdm,
            codigo_do_item,
            descricao_do_item,
            codigo_ncm
        )
    ";

    $stmt = $pdo->prepare($sql);

    echo "Executando a importação... Isso pode levar alguns minutos.\n";

    $startTime = microtime(true);
    $stmt->execute();
    $endTime = microtime(true);

    $rowCount = $stmt->rowCount();
    $duration = round($endTime - $startTime, 2);

    echo "========================================\n";
    echo "✓ IMPORTAÇÃO CONCLUÍDA!\n";
    echo "========================================\n";
    echo "Registros inseridos: " . $rowCount . "\n";
    echo "Tempo de execução: " . $duration . " segundos.\n";

} catch (PDOException $e) {
    echo "\n\n========================================\n";
    echo "✗ ERRO DURANTE A IMPORTAÇÃO!\n";
    echo "========================================\n";
    echo "Mensagem: " . $e->getMessage() . "\n";
    
    if (strpos($e->getMessage(), 'secure-file-priv') !== false) {
        echo "\n--- DICA ---\n";
        echo "O erro `secure-file-priv` indica uma restrição do MySQL. O script tenta usar `LOAD DATA LOCAL INFILE` para contornar isso, mas seu servidor pode não estar configurado para permitir.\n";
        echo "Verifique a variável `local_infile` no seu MySQL com o comando: SHOW GLOBAL VARIABLES LIKE 'local_infile';\n";
        echo "Se estiver 'OFF', você precisa habilitá-la no seu arquivo de configuração `my.ini` ou `my.cnf`.\n";
    }
    
    exit(1);
} catch (Exception $e) {
    echo "\n\n========================================\n";
    echo "✗ ERRO FATAL!\n";
    echo "========================================\n";
    echo "Mensagem: " . $e->getMessage() . "\n";
    exit(1);
}
