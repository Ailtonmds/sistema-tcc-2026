<?php
/**
 * Excluir categoria
 * Não permite exclusão se houver produtos vinculados
 */

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/verificar_sessao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if ($input === null) {
    $input = $_POST;
}

$id = (int) ($input['id'] ?? 0);

if ($id <= 0) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'ID é obrigatório.']);
    exit;
}

try {
    // Verificar se categoria existe
    $stmt = $pdo->prepare('SELECT id FROM categorias WHERE id = :id');
    $stmt->execute(['id' => $id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Categoria não encontrada.']);
        exit;
    }

    // Verificar se há produtos usando esta categoria
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM produtos WHERE categoria_id = :id');
    $stmt->execute(['id' => $id]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        http_response_code(409);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => "Não é possível excluir: existem {$count} produto(s) vinculado(s) a esta categoria."
        ]);
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM categorias WHERE id = :id');
    $stmt->execute(['id' => $id]);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Categoria excluída com sucesso.'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao excluir categoria.']);
}