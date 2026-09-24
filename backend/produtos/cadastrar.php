<?php
/**
 * Cadastrar produto
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

$categoria_id = (int) ($input['categoria_id'] ?? 0);
$nome = trim($input['nome'] ?? '');
$descricao = trim($input['descricao'] ?? '');
$unidade_medida = trim($input['unidade_medida'] ?? '');
$estoque_minimo = (int) ($input['estoque_minimo'] ?? 0);
$localizacao = trim($input['localizacao'] ?? '');
$ativo = isset($input['ativo']) ? (bool) $input['ativo'] : true;

if ($categoria_id <= 0 || $nome === '' || $unidade_medida === '') {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Categoria, nome e unidade de medida são obrigatórios.']);
    exit;
}

if ($estoque_minimo < 0) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Estoque mínimo não pode ser negativo.']);
    exit;
}

try {
    // Verificar se categoria existe
    $stmt = $pdo->prepare('SELECT id FROM categorias WHERE id = :id AND ativo = 1');
    $stmt->execute(['id' => $categoria_id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Categoria não encontrada ou inativa.']);
        exit;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO produtos (categoria_id, nome, descricao, unidade_medida, estoque_minimo, localizacao, ativo) 
         VALUES (:categoria_id, :nome, :descricao, :unidade_medida, :estoque_minimo, :localizacao, :ativo)'
    );
    $stmt->execute([
        'categoria_id' => $categoria_id,
        'nome' => $nome,
        'descricao' => $descricao,
        'unidade_medida' => $unidade_medida,
        'estoque_minimo' => $estoque_minimo,
        'localizacao' => $localizacao,
        'ativo' => $ativo
    ]);

    $id = $pdo->lastInsertId();

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Produto cadastrado com sucesso.',
        'dados' => ['id' => $id]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao cadastrar produto.']);
}