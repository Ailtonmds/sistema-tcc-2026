<?php
/**
 * Editar usuário
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

$id = (int) ($input['id'] ?? 0);
$nome = trim($input['nome'] ?? '');
$usuario = trim($input['usuario'] ?? '');
$senha = $input['senha'] ?? ''; // Opcional
$nivel_acesso = $input['nivel_acesso'] ?? 'operador';
$ativo = isset($input['ativo']) ? (bool) $input['ativo'] : true;

if ($id <= 0 || $nome === '' || $usuario === '') {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'ID, nome e usuário são obrigatórios.']);
    exit;
}

if (!in_array($nivel_acesso, ['admin', 'operador'])) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Nível de acesso inválido.']);
    exit;
}

// Não permitir desativar a si mesmo
if ($id === $_SESSION['usuario_id'] && !$ativo) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Não é possível desativar seu próprio usuário.']);
    exit;
}

try {
    // Verificar se usuário existe
    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE id = :id');
    $stmt->execute(['id' => $id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário não encontrado.']);
        exit;
    }

    // Verificar se nome de usuário já existe em outro
    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE usuario = :usuario AND id != :id');
    $stmt->execute(['usuario' => $usuario, 'id' => $id]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Nome de usuário já está em uso.']);
        exit;
    }

    if ($senha !== '') {
        if (strlen($senha) < 6) {
            http_response_code(422);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Senha deve ter pelo menos 6 caracteres.']);
            exit;
        }
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE usuarios SET nome = :nome, usuario = :usuario, senha_hash = :senha_hash, nivel_acesso = :nivel_acesso, ativo = :ativo WHERE id = :id');
        $stmt->execute([
            'nome' => $nome,
            'usuario' => $usuario,
            'senha_hash' => $senhaHash,
            'nivel_acesso' => $nivel_acesso,
            'ativo' => $ativo,
            'id' => $id
        ]);
    } else {
        $stmt = $pdo->prepare('UPDATE usuarios SET nome = :nome, usuario = :usuario, nivel_acesso = :nivel_acesso, ativo = :ativo WHERE id = :id');
        $stmt->execute([
            'nome' => $nome,
            'usuario' => $usuario,
            'nivel_acesso' => $nivel_acesso,
            'ativo' => $ativo,
            'id' => $id
        ]);
    }

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Usuário atualizado com sucesso.',
        'dados' => ['id' => $id]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao atualizar usuário.']);
}