<?php
/**
 * Logout - Encerra a sessão do usuário
 */

require_once __DIR__ . '/../config/config.php';

// Destruir sessão
session_unset();
session_destroy();

// Retornar resposta JSON
echo json_encode([
    'sucesso' => true,
    'mensagem' => 'Logout realizado com sucesso.'
]);