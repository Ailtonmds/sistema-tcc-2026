<?php
/**
 * Criar usuário - Retorna JSON para comunicação via fetch()
 */

require_once __DIR__ . '/../../backend/config/conexao.php';
require_once __DIR__ . '/../../backend/config/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if ($input === null) {
    $input = $_POST;
}

$nome = is_string($input['nome'] ?? null) ? trim($input['nome']) : '';
$usuario = is_string($input['usuario'] ?? null) ? trim($input['usuario']) : '';
$senha = is_string($input['senha'] ?? null) ? $input['senha'] : '';
$nivel_acesso = 'operador';

if ($nome === '' || $usuario === '' || $senha === '') {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Nome, usuário e senha são obrigatórios.']);
    exit;
}

if (strlen($senha) < 6) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'A senha deve ter pelo menos 6 caracteres.']);
    exit;
}

if (!in_array($nivel_acesso, ['admin', 'operador'])) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Nível de acesso inválido.']);
    exit;
}

try {
    $stmt = $pdo->query('SELECT COUNT(*) FROM usuarios');
    if ((int) $stmt->fetchColumn() > 0) {
        http_response_code(403);
        echo json_encode(['sucesso' => false, 'mensagem' => 'O cadastro público está disponível somente para o primeiro usuário.']);
        exit;
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('INSERT INTO usuarios (nome, usuario, senha_hash, nivel_acesso) VALUES (:nome, :usuario, :senha_hash, :nivel_acesso)');
    $stmt->execute([
        ':nome' => $nome,
        ':usuario' => $usuario,
        ':senha_hash' => $senhaHash,
        ':nivel_acesso' => $nivel_acesso
    ]);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Usuário "' . htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8') . '" criado com sucesso!'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    if ($e->getCode() === '23000') {
        http_response_code(409);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Este nome de usuário já está em uso.']);
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Não foi possível salvar o usuário. Verifique a conexão com o banco de dados.']);
    }
}