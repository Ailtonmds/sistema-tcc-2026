<?php
/**
 * Excluir lote
 * Não permite exclusão se houver movimentações vinculadas
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
    // Verificar se lote existe
    $stmt = $pdo->prepare('SELECT id FROM lotes WHERE id = :id');
    $stmt->execute(['id' => $id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Lote não encontrado.']);
        exit;
    }

    // Verificar se há movimentações vinculadas
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM movimentacoes WHERE lote_id = :id');
    $stmt->execute(['id' => $id]);
    $temMovimentacoes = $stmt->fetchColumn() > 0;

    if ($temMovimentacoes) {
        http_response_code(409);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Não é possível excluir: existem movimentações vinculadas a este lote.'
        ]);
        exit;
    }

    // Verificar se há vínculos em produto_lote
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM produto_lote WHERE lote_id = :id');
    $stmt->execute(['id' => $id]);
    $temVinculos = $stmt->fetchColumn() > 0;

    $pdo->beginTransaction();

    if ($temVinculos) {
        // Remover vínculos primeiro
        $stmt = $pdo->prepare('DELETE FROM produto_lote WHERE lote_id = :id');
        $stmt->execute(['id' => $id]);
    }

    // Excluir lote
    $stmt = $pdo->prepare('DELETE FROM lotes WHERE id = :id');
    $stmt->execute(['id' => $id]);

    $pdo->commit();

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Lote excluído com sucesso.'
    ]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao excluir lote.']);
}