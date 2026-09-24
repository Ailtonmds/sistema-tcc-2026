<?php
/**
 * Listar categorias
 */

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/verificar_sessao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

try {
    $stmt = $pdo->query('SELECT id, nome, descricao, ativo, criado_em FROM categorias ORDER BY nome');
    $categorias = $stmt->fetchAll();

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Categorias listadas com sucesso.',
        'dados' => $categorias
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao listar categorias.']);
}