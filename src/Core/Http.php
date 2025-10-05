<?php

namespace Joabe\Buscaprecos\Core;

/**
 * Classe simples para trabalhar com requisições HTTP
 * Substitui o Guzzle HTTP com funcionalidades básicas
 */
class Http
{
    /**
     * Faz uma requisição GET
     */
    public static function get(string $url, array $headers = []): array
    {
        return self::request('GET', $url, null, $headers);
    }
    
    /**
     * Faz uma requisição POST
     */
    public static function post(string $url, $data = null, array $headers = []): array
    {
        return self::request('POST', $url, $data, $headers);
    }
    
    /**
     * Faz uma requisição PUT
     */
    public static function put(string $url, $data = null, array $headers = []): array
    {
        return self::request('PUT', $url, $data, $headers);
    }
    
    /**
     * Faz uma requisição DELETE
     */
    public static function delete(string $url, array $headers = []): array
    {
        return self::request('DELETE', $url, null, $headers);
    }
    
    /**
     * Método principal para fazer requisições HTTP
     */
    private static function request(string $method, string $url, $data = null, array $headers = []): array
    {
        // Configuração padrão do contexto
        $context = [
            'http' => [
                'method' => $method,
                'timeout' => 30,
                'ignore_errors' => true,
                'header' => self::formatHeaders($headers)
            ]
        ];
        
        // Adiciona dados para POST/PUT
        if (in_array($method, ['POST', 'PUT']) && $data !== null) {
            if (is_array($data)) {
                $context['http']['content'] = http_build_query($data);
                $context['http']['header'] .= "Content-Type: application/x-www-form-urlencoded\r\n";
            } elseif (is_string($data)) {
                $context['http']['content'] = $data;
            }
        }
        
        // Cria o contexto e faz a requisição
        $stream_context = stream_context_create($context);
        $response = @file_get_contents($url, false, $stream_context);
        
        // Processa a resposta
        if ($response === false) {
            return [
                'success' => false,
                'error' => 'Erro ao fazer a requisição',
                'status_code' => 0,
                'data' => null
            ];
        }
        
        // Extrai o código de status dos headers
        $status_code = 200;
        if (isset($http_response_header)) {
            preg_match('/HTTP\/\d\.\d\s+(\d+)/', $http_response_header[0], $matches);
            if (isset($matches[1])) {
                $status_code = (int) $matches[1];
            }
        }
        
        // Tenta decodificar JSON se possível
        $decoded = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $data = $decoded;
        } else {
            $data = $response;
        }
        
        return [
            'success' => $status_code >= 200 && $status_code < 300,
            'status_code' => $status_code,
            'data' => $data,
            'raw' => $response,
            'headers' => $http_response_header ?? []
        ];
    }
    
    /**
     * Formata os headers para o contexto
     */
    private static function formatHeaders(array $headers): string
    {
        $formatted = "";
        foreach ($headers as $key => $value) {
            $formatted .= "$key: $value\r\n";
        }
        return $formatted;
    }
    
    /**
     * Método auxiliar para fazer requisições com cURL (alternativa mais robusta)
     */
    public static function curlRequest(string $method, string $url, $data = null, array $headers = []): array
    {
        $ch = curl_init();
        
        // Configurações básicas
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => self::formatCurlHeaders($headers)
        ]);
        
        // Adiciona dados para POST/PUT
        if (in_array($method, ['POST', 'PUT']) && $data !== null) {
            if (is_array($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        }
        
        // Executa a requisição
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Processa erro
        if ($response === false || !empty($error)) {
            return [
                'success' => false,
                'error' => $error ?: 'Erro desconhecido',
                'status_code' => $status_code,
                'data' => null
            ];
        }
        
        // Tenta decodificar JSON
        $decoded = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $data = $decoded;
        } else {
            $data = $response;
        }
        
        return [
            'success' => $status_code >= 200 && $status_code < 300,
            'status_code' => $status_code,
            'data' => $data,
            'raw' => $response
        ];
    }
    
    /**
     * Formata headers para cURL
     */
    private static function formatCurlHeaders(array $headers): array
    {
        $formatted = [];
        foreach ($headers as $key => $value) {
            $formatted[] = "$key: $value";
        }
        return $formatted;
    }
}