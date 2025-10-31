<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Google\Cloud\Storage\StorageClient;

try {
    error_log("[GCS Download] Iniciando script de download");
    
    $bucketName = getenv('STORAGE_BUCKET');
    $fileName = $_GET['file'] ?? '';

    error_log("[GCS Download] Bucket: " . ($bucketName ?: 'NÃO DEFINIDO') . " | Arquivo: " . ($fileName ?: 'NÃO FORNECIDO'));

    if (empty($bucketName) || empty($fileName)) {
        http_response_code(400);
        error_log('[GCS Download] ERRO 400: Bucket ou arquivo vazio');
        echo 'Configuração inválida ou nome de arquivo não fornecido.';
        exit;
    }

    $objectName = $fileName;

    // Log detalhado para depuração
    error_log("[GCS Download] Tentando baixar: Bucket: " . $bucketName . ", Objeto: " . $objectName);

    $storage = new StorageClient();
    error_log("[GCS Download] StorageClient inicializado");
    
    $bucket = $storage->bucket($bucketName);
    error_log("[GCS Download] Bucket acessado");
    
    $object = $bucket->object($objectName);
    error_log("[GCS Download] Objeto criado");

    if (!$object->exists()) {
        error_log("[GCS Download] Arquivo NÃO encontrado: gs://{$bucketName}/{$objectName}");
        http_response_code(404);
        echo 'Arquivo não encontrado no repositório.';
        exit;
    }

    error_log("[GCS Download] Arquivo encontrado: gs://{$bucketName}/{$objectName}");

    $stream = $object->downloadAsStream();
    error_log("[GCS Download] Stream obtido");

    // Limpar buffers antes de enviar headers
    if (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    error_log("[GCS Download] Headers enviados, iniciando stream...");

    echo $stream->getContents();
    
    error_log("[GCS Download] Download concluído com sucesso");

} catch (\Exception $e) {
    http_response_code(500);
    error_log('[GCS Download] ERRO: ' . $e->getMessage() . ' | Stack: ' . $e->getTraceAsString());
    echo 'Erro ao baixar o arquivo: ' . $e->getMessage();
}
