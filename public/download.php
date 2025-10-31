<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Google\Cloud\Storage\StorageClient;

try {
    $bucketName = getenv('STORAGE_BUCKET');
    $fileName = $_GET['file'] ?? '';

    if (empty($bucketName) || empty($fileName)) {
        http_response_code(400);
        echo 'Configuração inválida ou nome de arquivo não fornecido.';
        exit;
    }

    $objectName = $fileName;

    // Log detalhado para depuração
    error_log("[GCS Download] Tentando baixar: Bucket: " . $bucketName . ", Objeto: " . $objectName);

    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object($objectName);

    if (!$object->exists()) {
        error_log("[GCS Download] Arquivo NÃO encontrado: gs://{$bucketName}/{$objectName}");
        http_response_code(404);
        echo 'Arquivo não encontrado no repositório.';
        exit;
    }

    error_log("[GCS Download] Arquivo encontrado: gs://{$bucketName}/{$objectName}");

    $stream = $object->downloadAsStream();

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($fileName) . '"');
    
    error_log("[GCS Download] Headers enviados, iniciando stream...");

    echo $stream->getContents();
    
    error_log("[GCS Download] Download concluído com sucesso");

} catch (\Exception $e) {
    http_response_code(500);
    error_log('[GCS Download] ERRO: ' . $e->getMessage() . ' | Classe: ' . get_class($e));
    echo 'Erro ao baixar o arquivo: ' . $e->getMessage();
}
