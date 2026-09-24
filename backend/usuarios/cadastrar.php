<?php
/**
 * Cadastrar usuário
 */

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/verificar_sessao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

exigir_nivel_acesso(['admin']);

$input = json_decode(file_get_contents('php://input'), true);
if ($input === null) {
    $input = $_POST;
}

$nome = trim($input['nome'] ?? '');
$usuario = trim($input['usuario'] ?? '');
$senha = $input['senha'] ?? '';
$nivel_acesso = $input['nivel_acesso'] ?? 'operador';

if ($nome === '' || $usuario === '' || $senha === '') {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Nome, usuário e senha são obrigatórios.']);
    exit;
}

if (strlen($senha) < 6) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Senha deve ter pelo menos 6 caracteres.']);
    exit;
}

if (!in_array($nivel_acesso, ['admin', 'operador'])) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Nível de acesso inválido.']);
    exit;
}

try {
    // Verificar se usuário já existe
    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE usuario = :usuario');
    $stmt->execute(['usuario' => $usuario]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Nome de usuário já está em uso.']);
        exit;
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('INSERT INTO usuarios (nome, usuario, senha_hash, nivel_acesso) VALUES (:nome, :usuario, :senha_hash, :nivel_acesso)');
    $stmt->execute([
        'nome' => $nome,
        'usuario' => $usuario,
        'senha_hash' => $senhaHash,
        'nivel_acesso' => $nivel_acesso
    ]);

    $id = $pdo->lastInsertId();

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Usuário cadastrado com sucesso.',
        'dados' => ['id' => $id]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao cadastrar usuário.']);
}