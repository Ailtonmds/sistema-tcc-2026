<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metodo nao permitido.');
}

$usuario = trim($_POST['usuario'] ?? '');
$senha = $_POST['senha'] ?? '';

if ($usuario === '' || $senha === '') {
    http_response_code(422);
    exit('Usuario e senha sao obrigatorios.');
}

try {
    // CORRECAO: busca apenas o usuario informado; a versao anterior expunha todos os logins.
    $stmt = $pdo->prepare('SELECT id, usuario, senha_hash FROM user_login WHERE usuario = :usuario LIMIT 1');
    $stmt->execute(['usuario' => $usuario]);
    $usuarioEncontrado = $stmt->fetch();

    if (!$usuarioEncontrado || !password_verify($senha, $usuarioEncontrado['senha_hash'])) {
        header('Location: ../index.html?erro=credenciais', true, 303);
        exit;
    }

    // CORRECAO: a sessao guarda identificacao, nunca a senha.
    session_start();
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = (int) $usuarioEncontrado['id'];
    $_SESSION['usuario'] = $usuarioEncontrado['usuario'];

    header('Location: ../frontend/dashboard.html', true, 303);
    exit;
} catch (PDOException $e) {
    http_response_code(500);
    exit('Nao foi possivel realizar o login.');
}
