<?php
/**
 * Alerta de estoque baixo
 * Retorna produtos com quantidade total <= estoque_minimo
 */

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/verificar_sessao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

try {
    $sql = '
        SELECT 
            p.id,
            p.nome,
            p.unidade_medida,
            p.estoque_minimo,
            p.localizacao,
            c.nome AS categoria_nome,
            COALESCE(SUM(pl.quantidade), 0) AS quantidade_total
        FROM produtos p
        INNER JOIN categorias c ON p.categoria_id = c.id
        LEFT JOIN produto_lote pl ON p.id = pl.produto_id
        WHERE p.ativo = 1
        GROUP BY p.id
        HAVING COALESCE(SUM(pl.quantidade), 0) <= p.estoque_minimo
        ORDER BY p.nome
    ';

    $stmt = $pdo->query($sql);
    $produtos = $stmt->fetchAll();

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Alertas de estoque baixo obtidos com sucesso.',
        'dados' => $produtos,
        'total' => count($produtos)
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao buscar alertas de estoque baixo.']);
}