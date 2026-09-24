<?php

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    http_response_code(404);
    exit;
}
/**
 * Configurações gerais do backend
 */

// URL base da API (ajustar conforme necessário)
define('API_BASE_URL', 'http://localhost/sistema-tcc-2026/backend');

// Configurações de sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');

// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Constantes para alertas
define('DIAS_ALERTA_VALIDADE', 30); // Dias antes do vencimento para alertar

// Headers padrão para JSON
header('Content-Type: application/json; charset=utf-8');

set_exception_handler(function (Throwable $exception): void {
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
    }

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro interno ao processar a requisição.'
    ]);
});