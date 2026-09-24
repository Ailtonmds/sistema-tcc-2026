<?php
/**
 * Excluir (desativar) produto
 * Verifica se há lotes ou movimentações vinculadas antes de excluir fisicamente
 * Se houver, apenas desativa o produto
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
$forcar = (bool) ($input['forcar'] ?? false);

if ($id <= 0) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'ID é obrigatório.']);
    exit;
}

try {
    // Verificar se produto existe
    $stmt = $pdo->prepare('SELECT id, ativo FROM produtos WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $produto = $stmt->fetch();
    
    if (!$produto) {
        http_response_code(404);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Produto não encontrado.']);
        exit;
    }

    // Verificar se há lotes vinculados
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM produto_lote WHERE produto_id = :id');
    $stmt->execute(['id' => $id]);
    $temLotes = $stmt->fetchColumn() > 0;

    // Verificar se há movimentações vinculadas
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM movimentacoes WHERE produto_id = :id');
    $stmt->execute(['id' => $id]);
    $temMovimentacoes = $stmt->fetchColumn() > 0;

    if (($temLotes || $temMovimentacoes) && !$forcar) {
        // Apenas desativar
        $stmt = $pdo->prepare('UPDATE produtos SET ativo = 0 WHERE id = :id');
        $stmt->execute(['id' => $id]);

        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Produto desativado (possui lotes/movimentações vinculados).',
            'dados' => ['acao' => 'desativado']
        ]);
        exit;
    }

    // Exclusão física (apenas se não houver vínculos ou forçar)
    if ($forcar) {
        // Excluir em ordem: produto_lote, depois produto
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare('DELETE FROM produto_lote WHERE produto_id = :id');
        $stmt->execute(['id' => $id]);
        
        $stmt = $pdo->prepare('DELETE FROM produtos WHERE id = :id');
        $stmt->execute(['id' => $id]);
        
        $pdo->commit();

        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Produto excluído permanentemente.',
            'dados' => ['acao' => 'excluido']
        ]);
    } else {
        // Desativar
        $stmt = $pdo->prepare('UPDATE produtos SET ativo = 0 WHERE id = :id');
        $stmt->execute(['id' => $id]);

        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Produto desativado.',
            'dados' => ['acao' => 'desativado']
        ]);
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao excluir produto.']);
}