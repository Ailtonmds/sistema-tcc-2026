<?php
/**
 * Editar lote
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
$produto_id = (int) ($input['produto_id'] ?? 0);
$numero_lote = trim($input['numero_lote'] ?? '');
$data_fabricacao = $input['data_fabricacao'] ?? null;
$data_validade = $input['data_validade'] ?? '';
$quantidade = (int) ($input['quantidade'] ?? 0);

if ($id <= 0 || $produto_id <= 0 || $numero_lote === '' || $data_validade === '' || $quantidade <= 0) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'ID, produto, número do lote, validade e quantidade são obrigatórios.']);
    exit;
}

if (!DateTime::createFromFormat('Y-m-d', $data_validade)) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Data de validade inválida.']);
    exit;
}

if ($data_fabricacao !== null && $data_fabricacao !== '' && !DateTime::createFromFormat('Y-m-d', $data_fabricacao)) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Data de fabricação inválida.']);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT id FROM produtos WHERE id = :id AND ativo = 1 FOR UPDATE');
    $stmt->execute(['id' => $produto_id]);
    if (!$stmt->fetch()) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Produto não encontrado ou inativo.']);
        exit;
    }

    // Verificar se lote existe
    $stmt = $pdo->prepare('SELECT id FROM lotes WHERE id = :id FOR UPDATE');
    $stmt->execute(['id' => $id]);
    if (!$stmt->fetch()) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Lote não encontrado.']);
        exit;
    }

    // Verificar se número do lote já existe em outro lote
    $stmt = $pdo->prepare('SELECT id FROM lotes WHERE numero_lote = :numero_lote AND id != :id FOR UPDATE');
    $stmt->execute(['numero_lote' => $numero_lote, 'id' => $id]);
    if ($stmt->fetch()) {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Já existe outro lote com este número.']);
        exit;
    }

    $stmt = $pdo->prepare(
        'UPDATE lotes SET numero_lote = :numero_lote, data_fabricacao = :data_fabricacao, data_validade = :data_validade WHERE id = :id'
    );
    $stmt->execute([
        'numero_lote' => $numero_lote,
        'data_fabricacao' => $data_fabricacao ?: null,
        'data_validade' => $data_validade,
        'id' => $id
    ]);

    $stmt = $pdo->prepare(
        'SELECT id, produto_id FROM produto_lote WHERE lote_id = :lote_id AND produto_id = :produto_id LIMIT 1 FOR UPDATE'
    );
    $stmt->execute(['lote_id' => $id, 'produto_id' => $produto_id]);
    $vinculo = $stmt->fetch();

    if (!$vinculo) {
        $stmt = $pdo->prepare(
            'SELECT id, produto_id FROM produto_lote WHERE lote_id = :lote_id ORDER BY id LIMIT 1 FOR UPDATE'
        );
        $stmt->execute(['lote_id' => $id]);
        $vinculo = $stmt->fetch();
    }

    if ($vinculo) {
        $stmt = $pdo->prepare(
            'UPDATE produto_lote SET produto_id = :produto_id, quantidade = :quantidade WHERE id = :id'
        );
        $stmt->execute([
            'produto_id' => $produto_id,
            'quantidade' => $quantidade,
            'id' => $vinculo['id']
        ]);
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO produto_lote (produto_id, lote_id, quantidade) VALUES (:produto_id, :lote_id, :quantidade)'
        );
        $stmt->execute([
            'produto_id' => $produto_id,
            'lote_id' => $id,
            'quantidade' => $quantidade
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Lote atualizado com sucesso.',
        'dados' => ['id' => $id]
    ]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao atualizar lote.']);
}