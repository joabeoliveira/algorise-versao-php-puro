<?php
/**
 * Script para criar pacote de implantação para EasyPanel
 * Este script cria um arquivo ZIP com apenas os arquivos necessários
 * para implantar o projeto Busca Preços no EasyPanel
 */

echo "Criando pacote de implantação para EasyPanel...\n";

// Criar arquivo ZIP
$zip = new ZipArchive();
$nomeArquivoZip = 'buscaprecos_easypanel.zip';

if ($zip->open($nomeArquivoZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    // Arquivos e diretórios necessários
    $arquivosNecessarios = [
        // Arquivos de configuração
        'docker-compose.yml',
        '.env',
        'composer.json',
        'composer.lock',
        'criar_usuario.php',
        'README.md',
    ];
    
    // Adicionar arquivos
    foreach ($arquivosNecessarios as $arquivo) {
        if (file_exists($arquivo)) {
            $zip->addFile($arquivo, $arquivo);
            echo "Adicionado: $arquivo\n";
        } else {
            echo "Arquivo não encontrado: $arquivo\n";
        }
    }
    
    // Adicionar diretórios e seus conteúdos
    $diretorios = ['docker', 'public', 'src'];
    
    foreach ($diretorios as $diretorio) {
        if (is_dir($diretorio)) {
            adicionarDiretorioAoZip($zip, $diretorio, $diretorio);
        } else {
            echo "Diretório não encontrado: $diretorio\n";
        }
    }
    
    $zip->close();
    echo "\nArquivo ZIP criado com sucesso: $nomeArquivoZip\n";
    
    // Verificar tamanho do arquivo
    if (file_exists($nomeArquivoZip)) {
        echo "Tamanho: " . round(filesize($nomeArquivoZip) / 1024, 2) . " KB\n";
    }
} else {
    echo "Erro ao criar arquivo ZIP!\n";
}

/**
 * Função para adicionar diretório e seu conteúdo ao ZIP
 */
function adicionarDiretorioAoZip($zip, $diretorio, $caminhoBase) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($diretorio, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $item) {
        $caminhoRelativo = str_replace('\\', '/', $item->getPathname());
        $caminhoNoZip = str_replace('\\', '/', substr($caminhoRelativo, strlen($caminhoBase) - strlen($diretorio)));
        
        if ($item->isFile()) {
            $zip->addFile($caminhoRelativo, $caminhoNoZip);
            echo "Adicionado: $caminhoNoZip\n";
        }
    }
}
?>