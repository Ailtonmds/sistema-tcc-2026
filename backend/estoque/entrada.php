<?php
/**
 * Registrar entrada de estoque
 * Fluxo: Validar produto -> Validar lote -> Validar quantidade -> Atualizar quantidade do lote -> Registrar movimentação
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

$produto_id = (int) ($input['produto_id'] ?? 0);
$lote_id = (int) ($input['lote_id'] ?? 0);
$quantidade = (int) ($input['quantidade'] ?? 0);
$observacao = trim($input['observacao'] ?? '');
$usuario_id = $_SESSION['usuario_id'];

if ($produto_id <= 0 || $lote_id <= 0 || $quantidade <= 0) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Produto, lote e quantidade (maior que zero) são obrigatórios.']);
    exit;
}

try {
    // Verificar se produto existe e está ativo
    $stmt = $pdo->prepare('SELECT id FROM produtos WHERE id = :id AND ativo = 1');
    $stmt->execute(['id' => $produto_id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Produto não encontrado ou inativo.']);
        exit;
    }

    // Verificar se lote existe e está vinculado ao produto
    $stmt = $pdo->prepare('SELECT id FROM produto_lote WHERE produto_id = :produto_id AND lote_id = :lote_id');
    $stmt->execute(['produto_id' => $produto_id, 'lote_id' => $lote_id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Lote não encontrado ou não vinculado a este produto.']);
        exit;
    }

    // Verificar validade do lote
    $stmt = $pdo->prepare('SELECT data_validade FROM lotes WHERE id = :id');
    $stmt->execute(['id' => $lote_id]);
    $lote = $stmt->fetch();
    if ($lote && $lote['data_validade'] < date('Y-m-d')) {
        // Apenas aviso, não bloqueia
    }

    $pdo->beginTransaction();

    // Atualizar quantidade do lote
    $stmt = $pdo->prepare('UPDATE produto_lote SET quantidade = quantidade + :quantidade WHERE produto_id = :produto_id AND lote_id = :lote_id');
    $stmt->execute(['quantidade' => $quantidade, 'produto_id' => $produto_id, 'lote_id' => $lote_id]);

    // Registrar movimentação
    $stmt = $pdo->prepare(
        'INSERT INTO movimentacoes (produto_id, lote_id, tipo, quantidade, usuario_id, observacao) 
         VALUES (:produto_id, :lote_id, "entrada", :quantidade, :usuario_id, :observacao)'
    );
    $stmt->execute([
        'produto_id' => $produto_id,
        'lote_id' => $lote_id,
        'quantidade' => $quantidade,
        'usuario_id' => $usuario_id,
        'observacao' => $observacao
    ]);

    $movimentacao_id = $pdo->lastInsertId();

    $pdo->commit();

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Entrada de estoque registrada com sucesso.',
        'dados' => ['movimentacao_id' => $movimentacao_id]
    ]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao registrar entrada.']);
}