<?php
/**
 * Relatório de validade
 * Lista todos os lotes com status de validade
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
$status = $_GET['status'] ?? 'todos'; // todos, vencidos, proximos, ok

try {
    $sql = '
        SELECT 
            p.id AS produto_id,
            p.nome AS produto_nome,
            p.unidade_medida,
            c.nome AS categoria_nome,
            l.id AS lote_id,
            l.numero_lote,
            l.data_fabricacao,
            l.data_validade,
            pl.quantidade,
            DATEDIFF(l.data_validade, CURDATE()) AS dias_restantes,
            CASE 
                WHEN l.data_validade < CURDATE() THEN "vencido"
                WHEN l.data_validade <= :data_limite_status THEN "proximo"
                ELSE "ok"
            END AS status_validade
        FROM produtos p
        INNER JOIN categorias c ON p.categoria_id = c.id
        INNER JOIN produto_lote pl ON p.id = pl.produto_id
        INNER JOIN lotes l ON pl.lote_id = l.id
        WHERE p.ativo = 1
          AND pl.quantidade > 0
    ';
    $params = ['data_limite_status' => $dataLimite];

    if ($status === 'vencidos' || $status === 'vencido') {
        $sql .= ' AND l.data_validade < CURDATE()';
    } elseif ($status === 'proximos') {
        $sql .= ' AND l.data_validade >= CURDATE() AND l.data_validade <= :data_limite_filtro';
        $params['data_limite_filtro'] = $dataLimite;
    } elseif ($status === 'ok') {
        $sql .= ' AND l.data_validade > :data_limite_filtro';
        $params['data_limite_filtro'] = $dataLimite;
    }

    $sql .= ' ORDER BY l.data_validade';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $lotes = $stmt->fetchAll();

    // Estatísticas
    $stats = [
        'vencidos' => 0,
        'proximos' => 0,
        'ok' => 0,
        'total_lotes' => 0,
        'total_quantidade' => 0
    ];

    foreach ($lotes as $lote) {
        $stats['total_lotes']++;
        $stats['total_quantidade'] += (int) $lote['quantidade'];
        if ($lote['status_validade'] === 'vencido') $stats['vencidos']++;
        elseif ($lote['status_validade'] === 'proximo') $stats['proximos']++;
        else $stats['ok']++;
    }

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Relatório de validade gerado com sucesso.',
        'dados' => [
            'lotes' => $lotes,
            'estatisticas' => $stats,
            'dias_alerta' => $diasAlerta,
            'filtro_status' => $status
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao gerar relatório de validade.']);
}