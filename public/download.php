<?php
/**
 * Download direto de propostas - Solução temporária
 * URL: /download.php?file=nome_do_arquivo.pdf
 */

$nomeArquivo = $_GET['file'] ?? '';

if (empty($nomeArquivo)) {
    http_response_code(404);
    echo 'Nome do arquivo não fornecido.';
    exit;
}

$caminhoCompleto = __DIR__ . '/../storage/propostas/' . $nomeArquivo;

if (!file_exists($caminhoCompleto) || !preg_match('/^[a-f0-9]+\.pdf$/', $nomeArquivo)) {
    http_response_code(404);
    echo 'Arquivo não encontrado ou inválido.';
    exit;
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $nomeArquivo . '"');
readfile($caminhoCompleto);
?>