<?php
/**
 * Alerta de validade
 * Retorna produtos com lotes vencidos ou próximos do vencimento
 * DIAS_ALERTA_VALIDADE definido em config.php (padrão 30 dias)
 */

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/verificar_sessao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

$diasAlerta = DIAS_ALERTA_VALIDADE;
$dataLimite = date('Y-m-d', strtotime("+{$diasAlerta} days"));
$dataHoje = date('Y-m-d');

try {
    // Produtos vencidos
    $sqlVencidos = '
        SELECT 
            p.id AS produto_id,
            p.nome AS produto_nome,
            p.unidade_medida,
            c.nome AS categoria_nome,
            l.id AS lote_id,
            l.numero_lote,
            l.data_validade,
            pl.quantidade,
            DATEDIFF(l.data_validade, CURDATE()) AS dias_restantes,
            "vencido" AS status
        FROM produtos p
        INNER JOIN categorias c ON p.categoria_id = c.id
        INNER JOIN produto_lote pl ON p.id = pl.produto_id
        INNER JOIN lotes l ON pl.lote_id = l.id
        WHERE p.ativo = 1
          AND pl.quantidade > 0
          AND l.data_validade < CURDATE()
        ORDER BY l.data_validade
    ';

    // Produtos próximos do vencimento
    $sqlProximos = '
        SELECT 
            p.id AS produto_id,
            p.nome AS produto_nome,
            p.unidade_medida,
            c.nome AS categoria_nome,
            l.id AS lote_id,
            l.numero_lote,
            l.data_validade,
            pl.quantidade,
            DATEDIFF(l.data_validade, CURDATE()) AS dias_restantes,
            "proximo" AS status
        FROM produtos p
        INNER JOIN categorias c ON p.categoria_id = c.id
        INNER JOIN produto_lote pl ON p.id = pl.produto_id
        INNER JOIN lotes l ON pl.lote_id = l.id
        WHERE p.ativo = 1
          AND pl.quantidade > 0
          AND l.data_validade >= CURDATE()
          AND l.data_validade <= :data_limite
        ORDER BY l.data_validade
    ';

    $stmt = $pdo->prepare($sqlVencidos);
    $stmt->execute();
    $vencidos = $stmt->fetchAll();

    $stmt = $pdo->prepare($sqlProximos);
    $stmt->execute(['data_limite' => $dataLimite]);
    $proximos = $stmt->fetchAll();

    $todos = array_merge($vencidos, $proximos);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Alertas de validade obtidos com sucesso.',
        'dados' => [
            'vencidos' => $vencidos,
            'proximos_vencimento' => $proximos,
            'todos' => $todos
        ],
        'total_vencidos' => count($vencidos),
        'total_proximos' => count($proximos),
        'total' => count($todos),
        'dias_alerta' => $diasAlerta
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao buscar alertas de validade.']);
}