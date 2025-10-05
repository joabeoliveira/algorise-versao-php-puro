<?php

namespace Joabe\Buscaprecos\Core;

/**
 * Classe simples para trabalhar com planilhas CSV/Excel
 * Substitui PhpOffice/PhpSpreadsheet com funcionalidades básicas
 */
class Spreadsheet
{
    private array $data = [];
    private array $headers = [];
    
    /**
     * Carrega dados de um arquivo CSV
     */
    public static function loadFromCsv(string $filepath, string $delimiter = ','): self
    {
        $spreadsheet = new self();
        
        if (!file_exists($filepath)) {
            throw new \Exception("Arquivo não encontrado: $filepath");
        }
        
        $handle = fopen($filepath, 'r');
        if (!$handle) {
            throw new \Exception("Não foi possível abrir o arquivo: $filepath");
        }
        
        $isFirstRow = true;
        while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
            if ($isFirstRow) {
                $spreadsheet->headers = $row;
                $isFirstRow = false;
            } else {
                $spreadsheet->data[] = array_combine($spreadsheet->headers, $row);
            }
        }
        
        fclose($handle);
        return $spreadsheet;
    }
    
    /**
     * Carrega dados de um array
     */
    public static function loadFromArray(array $data, array $headers = null): self
    {
        $spreadsheet = new self();
        
        if ($headers) {
            $spreadsheet->headers = $headers;
            $spreadsheet->data = $data;
        } else {
            // Assume que a primeira linha são os headers
            $spreadsheet->headers = array_shift($data);
            foreach ($data as $row) {
                $spreadsheet->data[] = array_combine($spreadsheet->headers, $row);
            }
        }
        
        return $spreadsheet;
    }
    
    /**
     * Carrega dados básicos de Excel (formato simples - CSV salvo como .xlsx)
     */
    public static function loadFromExcel(string $filepath): self
    {
        // Para Excel simples, vamos tentar converter para CSV primeiro
        // Isso funciona para casos básicos, sem formatação complexa
        
        if (!file_exists($filepath)) {
            throw new \Exception("Arquivo não encontrado: $filepath");
        }
        
        // Verifica se é realmente um arquivo Excel
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        if (!in_array($extension, ['xlsx', 'xls'])) {
            throw new \Exception("Formato de arquivo não suportado: $extension");
        }
        
        // Para arquivos Excel simples, tenta ler como CSV
        // (funciona se o arquivo foi salvo como CSV com extensão .xlsx)
        try {
            return self::loadFromCsv($filepath);
        } catch (\Exception $e) {
            throw new \Exception("Não foi possível processar o arquivo Excel. Converta para CSV primeiro.");
        }
    }
    
    /**
     * Obtém todos os dados
     */
    public function getData(): array
    {
        return $this->data;
    }
    
    /**
     * Obtém os headers
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
    
    /**
     * Obtém uma linha específica
     */
    public function getRow(int $index): ?array
    {
        return $this->data[$index] ?? null;
    }
    
    /**
     * Obtém uma coluna específica
     */
    public function getColumn(string $header): array
    {
        $column = [];
        foreach ($this->data as $row) {
            $column[] = $row[$header] ?? null;
        }
        return $column;
    }
    
    /**
     * Adiciona uma linha
     */
    public function addRow(array $row): void
    {
        if (!empty($this->headers)) {
            $this->data[] = array_combine($this->headers, $row);
        } else {
            $this->data[] = $row;
        }
    }
    
    /**
     * Define os headers
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }
    
    /**
     * Filtra dados por uma condição
     */
    public function filter(callable $callback): array
    {
        return array_filter($this->data, $callback);
    }
    
    /**
     * Salva como CSV
     */
    public function saveToCsv(string $filepath, string $delimiter = ','): bool
    {
        $handle = fopen($filepath, 'w');
        if (!$handle) {
            return false;
        }
        
        // Escreve headers
        if (!empty($this->headers)) {
            fputcsv($handle, $this->headers, $delimiter);
        }
        
        // Escreve dados
        foreach ($this->data as $row) {
            if (!empty($this->headers)) {
                // Garante que a ordem das colunas está correta
                $orderedRow = [];
                foreach ($this->headers as $header) {
                    $orderedRow[] = $row[$header] ?? '';
                }
                fputcsv($handle, $orderedRow, $delimiter);
            } else {
                fputcsv($handle, $row, $delimiter);
            }
        }
        
        fclose($handle);
        return true;
    }
    
    /**
     * Converte para HTML table
     */
    public function toHtmlTable(array $attributes = []): string
    {
        $attrString = '';
        foreach ($attributes as $key => $value) {
            $attrString .= " $key=\"$value\"";
        }
        
        $html = "<table$attrString>";
        
        // Headers
        if (!empty($this->headers)) {
            $html .= "<thead><tr>";
            foreach ($this->headers as $header) {
                $html .= "<th>" . htmlspecialchars($header) . "</th>";
            }
            $html .= "</tr></thead>";
        }
        
        // Dados
        $html .= "<tbody>";
        foreach ($this->data as $row) {
            $html .= "<tr>";
            if (!empty($this->headers)) {
                foreach ($this->headers as $header) {
                    $html .= "<td>" . htmlspecialchars($row[$header] ?? '') . "</td>";
                }
            } else {
                foreach ($row as $cell) {
                    $html .= "<td>" . htmlspecialchars($cell) . "</td>";
                }
            }
            $html .= "</tr>";
        }
        $html .= "</tbody></table>";
        
        return $html;
    }
    
    /**
     * Gera um modelo de planilha CSV para download
     */
    public static function generateTemplate(array $headers, array $sampleData = []): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'template_');
        $handle = fopen($tempFile, 'w');
        
        // Escreve headers
        fputcsv($handle, $headers);
        
        // Escreve dados de exemplo se fornecidos
        foreach ($sampleData as $row) {
            fputcsv($handle, $row);
        }
        
        fclose($handle);
        return $tempFile;
    }
    
    /**
     * Valida se um arquivo tem o formato esperado
     */
    public static function validateFile(string $filepath, array $requiredHeaders): array
    {
        $errors = [];
        
        if (!file_exists($filepath)) {
            $errors[] = "Arquivo não encontrado";
            return $errors;
        }
        
        try {
            $spreadsheet = self::loadFromCsv($filepath);
            $headers = $spreadsheet->getHeaders();
            
            // Verifica se todos os headers obrigatórios estão presentes
            foreach ($requiredHeaders as $required) {
                if (!in_array($required, $headers)) {
                    $errors[] = "Coluna obrigatória não encontrada: $required";
                }
            }
            
            // Verifica se há dados
            if (empty($spreadsheet->getData())) {
                $errors[] = "Arquivo não contém dados";
            }
            
        } catch (\Exception $e) {
            $errors[] = "Erro ao ler arquivo: " . $e->getMessage();
        }
        
        return $errors;
    }
    
    /**
     * Processa upload de arquivo de planilha
     */
    public static function processUpload(array $fileData, array $allowedTypes = ['csv', 'xlsx', 'xls']): array
    {
        $errors = [];
        
        // Verifica se houve erro no upload
        if ($fileData['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Erro no upload do arquivo";
            return ['success' => false, 'errors' => $errors];
        }
        
        // Verifica o tipo do arquivo
        $extension = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedTypes)) {
            $errors[] = "Tipo de arquivo não permitido. Use: " . implode(', ', $allowedTypes);
            return ['success' => false, 'errors' => $errors];
        }
        
        // Verifica o tamanho (máximo 5MB)
        if ($fileData['size'] > 5 * 1024 * 1024) {
            $errors[] = "Arquivo muito grande. Máximo 5MB.";
            return ['success' => false, 'errors' => $errors];
        }
        
        // Move o arquivo para local temporário
        $tempPath = sys_get_temp_dir() . '/' . uniqid() . '.' . $extension;
        if (!move_uploaded_file($fileData['tmp_name'], $tempPath)) {
            $errors[] = "Erro ao processar arquivo";
            return ['success' => false, 'errors' => $errors];
        }
        
        return [
            'success' => true,
            'path' => $tempPath,
            'extension' => $extension,
            'name' => $fileData['name']
        ];
    }
}