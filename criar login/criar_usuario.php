<?php
declare(strict_types=1);

require_once __DIR__ . '/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

$user = trim($_POST['user'] ?? '');
$senha = $_POST['senha'] ?? '';

if (strlen($user) < 3 || strlen($senha) < 6) {
    http_response_code(422);
    $mensagem = 'O usuário deve ter pelo menos 3 caracteres e a senha, 6.';
    $sucesso = false;
} else {
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    try {
        $pdo = conectarBanco();
        $comando = $pdo->prepare(
            'INSERT INTO user_login (usuario, senha_hash) VALUES (:usuario, :senha_hash)'
        );
        $comando->execute([
            ':usuario' => $user,
            ':senha_hash' => $senhaHash,
        ]);

        $mensagem = 'Usuário "' . htmlspecialchars($user, ENT_QUOTES, 'UTF-8') . '" criado com sucesso!';
        $sucesso = true;
    } catch (PDOException $erro) {
        http_response_code(500);
        $mensagem = $erro->getCode() === '23000'
            ? 'Este nome de usuário já está em uso.'
            : 'Não foi possível salvar o usuário. Verifique a conexão com o banco de dados.';
        $sucesso = false;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Criação de usuário</title>
  <style>
    body { margin: 0; min-height: 100vh; display: grid; place-items: center; font-family: Arial, sans-serif; background: #f1f5f9; }
    .message { width: min(92%, 420px); padding: 32px; border-radius: 14px; background: #fff; box-shadow: 0 10px 30px rgba(15, 23, 42, .12); text-align: center; }
    .success { color: #15803d; } .error { color: #b91c1c; }
    a { display: inline-block; margin-top: 18px; color: #2563eb; font-weight: bold; text-decoration: none; }
  </style>
</head>
<body>
  <main class="message">
    <h1 class="<?= $sucesso ? 'success' : 'error' ?>"><?= $mensagem ?></h1>
    <a href="index.html">Voltar ao formulário</a>
  </main>
</body>
</html>
