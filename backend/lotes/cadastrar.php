<?php
/**
 * Cadastrar lote e vincular a produto
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
$numero_lote = trim($input['numero_lote'] ?? '');
$data_fabricacao = $input['data_fabricacao'] ?? null;
$data_validade = $input['data_validade'] ?? '';
$quantidade = (int) ($input['quantidade'] ?? 0);

if ($produto_id <= 0 || $numero_lote === '' || $data_validade === '' || $quantidade <= 0) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Produto, número do lote, validade e quantidade são obrigatórios.']);
    exit;
}

// Validar data de validade
if (!DateTime::createFromFormat('Y-m-d', $data_validade)) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Data de validade inválida. Use formato YYYY-MM-DD.']);
    exit;
}

// Validar data de fabricação se fornecida
if ($data_fabricacao !== null && $data_fabricacao !== '' && !DateTime::createFromFormat('Y-m-d', $data_fabricacao)) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Data de fabricação inválida. Use formato YYYY-MM-DD.']);
    exit;
}

try {
    // Verificar se produto existe
    $stmt = $pdo->prepare('SELECT id FROM produtos WHERE id = :id AND ativo = 1');
    $stmt->execute(['id' => $produto_id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Produto não encontrado ou inativo.']);
        exit;
    }

    $pdo->beginTransaction();

    // Verificar se lote já existe
    $stmt = $pdo->prepare('SELECT id FROM lotes WHERE numero_lote = :numero_lote');
    $stmt->execute(['numero_lote' => $numero_lote]);
    $lote = $stmt->fetch();

    if ($lote) {
        $lote_id = $lote['id'];
        
        // Verificar se já está vinculado a este produto
        $stmt = $pdo->prepare('SELECT id, quantidade FROM produto_lote WHERE produto_id = :produto_id AND lote_id = :lote_id');
        $stmt->execute(['produto_id' => $produto_id, 'lote_id' => $lote_id]);
        $produtoLote = $stmt->fetch();

        if ($produtoLote) {
            // Atualizar quantidade existente
            $novaQuantidade = $produtoLote['quantidade'] + $quantidade;
            $stmt = $pdo->prepare('UPDATE produto_lote SET quantidade = :quantidade WHERE id = :id');
            $stmt->execute(['quantidade' => $novaQuantidade, 'id' => $produtoLote['id']]);
        } else {
            // Vincular lote ao produto
            $stmt = $pdo->prepare('INSERT INTO produto_lote (produto_id, lote_id, quantidade) VALUES (:produto_id, :lote_id, :quantidade)');
            $stmt->execute(['produto_id' => $produto_id, 'lote_id' => $lote_id, 'quantidade' => $quantidade]);
        }
    } else {
        // Criar novo lote
        $stmt = $pdo->prepare('INSERT INTO lotes (numero_lote, data_fabricacao, data_validade) VALUES (:numero_lote, :data_fabricacao, :data_validade)');
        $stmt->execute([
            'numero_lote' => $numero_lote,
            'data_fabricacao' => $data_fabricacao ?: null,
            'data_validade' => $data_validade
        ]);
        $lote_id = $pdo->lastInsertId();

        // Vincular ao produto
        $stmt = $pdo->prepare('INSERT INTO produto_lote (produto_id, lote_id, quantidade) VALUES (:produto_id, :lote_id, :quantidade)');
        $stmt->execute(['produto_id' => $produto_id, 'lote_id' => $lote_id, 'quantidade' => $quantidade]);
    }

    $pdo->commit();

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Lote cadastrado/vinculado com sucesso.',
        'dados' => ['lote_id' => $lote_id]
    ]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Verificar erro de duplicata
    if ($e->getCode() === '23000') {
        http_response_code(409);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Número de lote já existe.']);
    } else {
        http_response_code(500);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao cadastrar lote.']);
    }
}