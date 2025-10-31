<?php

// Garante que as funções não sejam redeclaradas
if (!function_exists('formatarMoeda')) {
    /**
     * Formata um valor numérico para o padrão monetário BRL (R$).
     */
    function formatarMoeda($valor) {
        $valorFloat = floatval($valor);
        return 'R$ ' . number_format($valorFloat, 2, ',', '.');
    }
}

if (!function_exists('formatarData')) {
    /**
     * Formata uma data (string 'Y-m-d' ou timestamp) para 'd/m/Y'.
     */
    function formatarData($data) {
        if (empty($data)) {
            return 'N/A';
        }
        $timestamp = strtotime($data);
        if ($timestamp === false) {
            return 'Data Inválida';
        }
        return date('d/m/Y', $timestamp);
    }
}

if (!function_exists('formatarDataHora')) {
    /**
     * Formata um timestamp Unix para 'd/m/Y H:i:s'.
     */
    function formatarDataHora($timestamp) {
        if (empty($timestamp) || !is_numeric($timestamp)) {
            return 'N/A';
        }
        return date('d/m/Y H:i:s', (int)$timestamp);
    }
}

// Adicione aqui outras funções globais que seu sistema possa precisar
// (Como a 'formatarString' que é usada em outro método seu)

require_once __DIR__ . '/Storage.php';
