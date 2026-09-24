<?php
/**
 * Editar categoria
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
$nome = trim($input['nome'] ?? '');
$descricao = trim($input['descricao'] ?? '');
$ativo = isset($input['ativo']) ? (bool) $input['ativo'] : true;

if ($id <= 0 || $nome === '') {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'ID e nome são obrigatórios.']);
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

    // Verificar se nome já existe em outra categoria
    $stmt = $pdo->prepare('SELECT id FROM categorias WHERE nome = :nome AND id != :id');
    $stmt->execute(['nome' => $nome, 'id' => $id]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Já existe outra categoria com este nome.']);
        exit;
    }

    $stmt = $pdo->prepare('UPDATE categorias SET nome = :nome, descricao = :descricao, ativo = :ativo WHERE id = :id');
    $stmt->execute(['nome' => $nome, 'descricao' => $descricao, 'ativo' => $ativo, 'id' => $id]);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Categoria atualizada com sucesso.',
        'dados' => ['id' => $id, 'nome' => $nome, 'descricao' => $descricao, 'ativo' => $ativo]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao atualizar categoria.']);
}