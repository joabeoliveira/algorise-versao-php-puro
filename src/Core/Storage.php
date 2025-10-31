<?php

namespace Joabe\Buscaprecos\Core;

use Google\Cloud\Storage\StorageClient;

/**
 * Upload de arquivos para o Google Cloud Storage.
 *
 * @param string $sourcePath O caminho do arquivo local temporário (ex: $_FILES['file']['tmp_name']).
 * @param string $objectName O nome do objeto de destino no bucket (ex: 'propostas/arquivo.pdf').
 * @return string O nome do objeto no GCS.
 * @throws \Exception Se o upload falhar.
 */
function uploadToGCS(string $sourcePath, string $objectName): string
{
    $bucketName = getenv('STORAGE_BUCKET');
    error_log("[GCS] Usando bucket: " . ($bucketName ? $bucketName : 'não definido'));

    if (!$bucketName) {
        throw new \Exception("A variável de ambiente STORAGE_BUCKET não está definida.");
    }

    try {
        $storage = new StorageClient();
        $bucket = $storage->bucket($bucketName);
        $file = fopen($sourcePath, 'r');

        $object = $bucket->upload($file, [
            'name' => $objectName
        ]);

        // Log para depuração
        error_log("[GCS] Upload bem-sucedido para gs://{$bucketName}/{$objectName}");

        return $objectName;

    } catch (\Exception $e) {
        error_log("[GCS] Falha no upload: " . $e->getMessage());
        throw new \Exception("Falha ao fazer upload do arquivo para o Cloud Storage.", 0, $e);
    }
}
