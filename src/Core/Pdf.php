<?php

namespace Joabe\Buscaprecos\Core;

/**
 * Sistema simples de geração de PDF em PHP puro
 * Substitui DomPDF com funcionalidades básicas usando HTML/CSS
 */
class Pdf
{
    private string $html = '';
    private array $styles = [];
    private string $title = '';
    private array $metadata = [];
    
    public function __construct()
    {
        $this->addDefaultStyles();
    }
    
    /**
     * Define o título do documento
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }
    
    /**
     * Adiciona metadados
     */
    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }
    
    /**
     * Define o conteúdo HTML
     */
    public function setHtml(string $html): self
    {
        $this->html = $html;
        return $this;
    }
    
    /**
     * Adiciona CSS customizado
     */
    public function addCss(string $css): self
    {
        $this->styles[] = $css;
        return $this;
    }
    
    /**
     * Renderiza HTML completo para impressão/PDF
     */
    public function render(): string
    {
        $allStyles = implode("\n", $this->styles);
        
        $fullHtml = <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$this->title}</title>
    <style>
        {$allStyles}
    </style>
</head>
<body>
    {$this->html}
</body>
</html>
HTML;
        
        return $fullHtml;
    }
    
    /**
     * Salva como arquivo HTML (pronto para conversão)
     */
    public function saveAsHtml(string $filepath): bool
    {
        $html = $this->render();
        return file_put_contents($filepath, $html) !== false;
    }
    
    /**
     * Gera output direto no navegador para impressão
     */
    public function output(string $filename = 'documento.pdf'): void
    {
        // Headers para forçar download ou exibição
        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Disposition: inline; filename="' . $filename . '.html"');
        
        echo $this->render();
    }
    
    /**
     * Converte para PDF usando wkhtmltopdf (se disponível)
     */
    public function convertToPdf(string $outputPath): bool
    {
        // Verifica se wkhtmltopdf está disponível
        $wkhtmltopdf = $this->findWkhtmltopdf();
        
        if (!$wkhtmltopdf) {
            throw new \Exception('wkhtmltopdf não encontrado. Instale ou use saveAsHtml()');
        }
        
        // Cria arquivo HTML temporário
        $tempHtml = tempnam(sys_get_temp_dir(), 'pdf_') . '.html';
        $this->saveAsHtml($tempHtml);
        
        // Comando wkhtmltopdf
        $command = sprintf(
            '%s --page-size A4 --margin-top 0.75in --margin-right 0.75in --margin-bottom 0.75in --margin-left 0.75in --encoding UTF-8 %s %s',
            escapeshellarg($wkhtmltopdf),
            escapeshellarg($tempHtml),
            escapeshellarg($outputPath)
        );
        
        // Executa comando
        exec($command, $output, $returnVar);
        
        // Remove arquivo temporário
        unlink($tempHtml);
        
        return $returnVar === 0;
    }
    
    /**
     * Procura pelo wkhtmltopdf no sistema
     */
    private function findWkhtmltopdf(): ?string
    {
        $possiblePaths = [
            '/usr/local/bin/wkhtmltopdf',
            '/usr/bin/wkhtmltopdf',
            'wkhtmltopdf', // No PATH
            'C:\\Program Files\\wkhtmltopdf\\bin\\wkhtmltopdf.exe', // Windows
        ];
        
        foreach ($possiblePaths as $path) {
            if (is_executable($path)) {
                return $path;
            }
        }
        
        return null;
    }
    
    /**
     * Adiciona estilos padrão para documentos
     */
    private function addDefaultStyles(): void
    {
        $defaultCss = <<<CSS
        /* Reset básico */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Corpo do documento */
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 12pt;
            line-height: 1.4;
            color: #333;
            background: white;
            margin: 0;
            padding: 20px;
        }

        /* Títulos */
        h1 {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }

        h2 {
            font-size: 14pt;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            color: #2c3e50;
            border-bottom: 1px solid #bdc3c7;
            padding-bottom: 5px;
        }

        h3 {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 8px;
            color: #34495e;
        }

        /* Parágrafos */
        p {
            margin-bottom: 10px;
            text-align: justify;
        }

        /* Tabelas */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10pt;
        }

        th, td {
            border: 1px solid #bdc3c7;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #ecf0f1;
            font-weight: bold;
            color: #2c3e50;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        /* Listas */
        ul, ol {
            margin: 10px 0;
            padding-left: 20px;
        }

        li {
            margin-bottom: 5px;
        }

        /* Classe utilitárias */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-justify { text-align: justify; }
        .bold { font-weight: bold; }
        .italic { font-style: italic; }
        .small { font-size: 10pt; }
        .large { font-size: 14pt; }

        /* Quebra de página */
        .page-break {
            page-break-before: always;
        }

        /* Cabeçalho e rodapé */
        .header {
            border-bottom: 1px solid #bdc3c7;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .footer {
            border-top: 1px solid #bdc3c7;
            padding-top: 15px;
            margin-top: 20px;
            font-size: 10pt;
            text-align: center;
            color: #7f8c8d;
        }

        /* Assinatura */
        .signature-block {
            margin-top: 40px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #333;
            width: 300px;
            margin: 0 auto;
            padding-top: 5px;
        }

        /* Estilos de impressão */
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            
            .no-print {
                display: none;
            }
        }

        /* Destaque */
        .highlight {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }

        .warning {
            background-color: #f8d7da;
            border: 1px solid #f1556c;
            color: #721c24;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }

        .info {
            background-color: #d1ecf1;
            border: 1px solid #b8daff;
            color: #0c5460;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
CSS;
        
        $this->styles[] = $defaultCss;
    }
    
    /**
     * Método estático para geração rápida
     */
    public static function createFromHtml(string $html, string $title = ''): self
    {
        $pdf = new self();
        $pdf->setTitle($title);
        $pdf->setHtml($html);
        return $pdf;
    }
    
    /**
     * Cria PDF de relatório com template padrão
     */
    public static function createReport(array $data): self
    {
        $pdf = new self();
        $pdf->setTitle($data['title'] ?? 'Relatório');
        
        $html = '';
        
        // Cabeçalho
        if (isset($data['header'])) {
            $html .= '<div class="header">';
            $html .= '<h1>' . htmlspecialchars($data['title'] ?? 'Relatório') . '</h1>';
            if (isset($data['subtitle'])) {
                $html .= '<p class="text-center"><em>' . htmlspecialchars($data['subtitle']) . '</em></p>';
            }
            $html .= '<p class="text-right small">Gerado em: ' . date('d/m/Y H:i') . '</p>';
            $html .= '</div>';
        }
        
        // Conteúdo
        if (isset($data['sections'])) {
            foreach ($data['sections'] as $section) {
                if (isset($section['title'])) {
                    $html .= '<h2>' . htmlspecialchars($section['title']) . '</h2>';
                }
                
                if (isset($section['content'])) {
                    $html .= $section['content'];
                }
                
                if (isset($section['table'])) {
                    $html .= self::generateTable($section['table']);
                }
            }
        }
        
        // Rodapé
        if (isset($data['footer'])) {
            $html .= '<div class="footer">';
            $html .= $data['footer'];
            $html .= '</div>';
        }
        
        $pdf->setHtml($html);
        return $pdf;
    }
    
    /**
     * Gera tabela HTML a partir de dados
     */
    private static function generateTable(array $tableData): string
    {
        if (empty($tableData)) {
            return '';
        }
        
        $html = '<table>';
        
        // Headers
        if (isset($tableData['headers'])) {
            $html .= '<thead><tr>';
            foreach ($tableData['headers'] as $header) {
                $html .= '<th>' . htmlspecialchars($header) . '</th>';
            }
            $html .= '</tr></thead>';
        }
        
        // Dados
        if (isset($tableData['data'])) {
            $html .= '<tbody>';
            foreach ($tableData['data'] as $row) {
                $html .= '<tr>';
                foreach ($row as $cell) {
                    $html .= '<td>' . htmlspecialchars($cell) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody>';
        }
        
        $html .= '</table>';
        return $html;
    }
}