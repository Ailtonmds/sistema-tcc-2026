<?php
/**
 * Listar usuários
 */

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/verificar_sessao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

exigir_nivel_acesso(['admin']);

try {
    $stmt = $pdo->query('SELECT id, nome, usuario, nivel_acesso, ativo, criado_em FROM usuarios ORDER BY nome');
    $usuarios = $stmt->fetchAll();

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Usuários listados com sucesso.',
        'dados' => $usuarios
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao listar usuários.']);
}