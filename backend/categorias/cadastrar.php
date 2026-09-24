<?php
/**
 * Cadastrar categoria
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

$nome = trim($input['nome'] ?? '');
$descricao = trim($input['descricao'] ?? '');

if ($nome === '') {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Nome da categoria é obrigatório.']);
    exit;
}

try {
    // Verificar se já existe
    $stmt = $pdo->prepare('SELECT id FROM categorias WHERE nome = :nome');
    $stmt->execute(['nome' => $nome]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Categoria já existe.']);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO categorias (nome, descricao) VALUES (:nome, :descricao)');
    $stmt->execute(['nome' => $nome, 'descricao' => $descricao]);

    $id = $pdo->lastInsertId();

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Categoria cadastrada com sucesso.',
        'dados' => ['id' => $id, 'nome' => $nome, 'descricao' => $descricao]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao cadastrar categoria.']);
}