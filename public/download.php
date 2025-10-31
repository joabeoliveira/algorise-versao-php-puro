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

    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object($objectName);

    if (!$object->exists()) {
        http_response_code(404);
        echo 'Arquivo não encontrado no repositório.';
        exit;
    }

    $stream = $object->downloadAsStream();

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($fileName) . '"');

    echo $stream->getContents();

} catch (\Exception $e) {
    http_response_code(500);
    echo 'Erro ao baixar o arquivo: ' . $e->getMessage();
    error_log('[GCS Download] ' . $e->getMessage());
}