<?php
/**
 * Login - Autenticação de usuário
 * Retorna JSON para comunicação via fetch()
 */

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido.'
    ]);
    exit;
}

// Obter dados do corpo da requisição (JSON ou form-data)
$input = json_decode(file_get_contents('php://input'), true);
if ($input === null) {
    $input = $_POST;
}

$usuario = is_string($input['usuario'] ?? null) ? trim($input['usuario']) : '';
$senha = is_string($input['senha'] ?? null) ? $input['senha'] : '';

if ($usuario === '' || $senha === '') {
    http_response_code(422);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Usuário e senha são obrigatórios.'
    ]);
    exit;
}

try {
    // Buscar usuário
    $stmt = $pdo->prepare('SELECT id, usuario, senha_hash, nome, nivel_acesso, ativo FROM usuarios WHERE usuario = :usuario LIMIT 1');
    $stmt->execute(['usuario' => $usuario]);
    $usuarioEncontrado = $stmt->fetch();

    if (!$usuarioEncontrado || !password_verify($senha, $usuarioEncontrado['senha_hash'])) {
        http_response_code(401);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Usuário ou senha inválidos.'
        ]);
        exit;
    }

    if (!$usuarioEncontrado['ativo']) {
        http_response_code(403);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Usuário inativo. Entre em contato com o administrador.'
        ]);
        exit;
    }

    // Iniciar sessão
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = (int) $usuarioEncontrado['id'];
    $_SESSION['usuario'] = $usuarioEncontrado['usuario'];
    $_SESSION['nome'] = $usuarioEncontrado['nome'];
    $_SESSION['nivel_acesso'] = $usuarioEncontrado['nivel_acesso'];

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Login realizado com sucesso.',
        'dados' => [
            'id' => (int) $usuarioEncontrado['id'],
            'usuario' => $usuarioEncontrado['usuario'],
            'nome' => $usuarioEncontrado['nome'],
            'nivel_acesso' => $usuarioEncontrado['nivel_acesso']
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Não foi possível realizar o login.'
    ]);
}