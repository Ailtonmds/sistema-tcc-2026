<?php
/**
 * Listar lotes
 * Suporta filtro por produto_id
 */

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/verificar_sessao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

$produto_id = isset($_GET['produto_id']) ? (int) $_GET['produto_id'] : 0;

try {
    $sql = '
        SELECT 
            l.id,
            l.numero_lote,
            l.data_fabricacao,
            l.data_validade,
            l.criado_em,
            pl.produto_id,
            p.nome AS produto_nome,
            pl.quantidade
        FROM lotes l
        INNER JOIN produto_lote pl ON l.id = pl.lote_id
        INNER JOIN produtos p ON pl.produto_id = p.id
    ';
    $params = [];

    if ($produto_id > 0) {
        $sql .= ' WHERE pl.produto_id = :produto_id';
        $params['produto_id'] = $produto_id;
    }

    $sql .= ' ORDER BY l.data_validade, p.nome';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $lotes = $stmt->fetchAll();

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Lotes listados com sucesso.',
        'dados' => $lotes
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao listar lotes.']);
}