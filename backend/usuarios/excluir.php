<?php
/**
 * Excluir usuário
 * Não permite excluir a si mesmo
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

if ($id <= 0) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'ID é obrigatório.']);
    exit;
}

// Não permitir excluir a si mesmo
if ($id === $_SESSION['usuario_id']) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Não é possível excluir seu próprio usuário.']);
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

    // Verificar se há movimentações vinculadas
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM movimentacoes WHERE usuario_id = :id');
    $stmt->execute(['id' => $id]);
    $temMovimentacoes = $stmt->fetchColumn() > 0;

    if ($temMovimentacoes) {
        // Apenas desativar
        $stmt = $pdo->prepare('UPDATE usuarios SET ativo = 0 WHERE id = :id');
        $stmt->execute(['id' => $id]);

        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Usuário desativado (possui movimentações vinculadas).',
            'dados' => ['acao' => 'desativado']
        ]);
    } else {
        // Excluir fisicamente
        $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = :id');
        $stmt->execute(['id' => $id]);

        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Usuário excluído com sucesso.',
            'dados' => ['acao' => 'excluido']
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao excluir usuário.']);
}