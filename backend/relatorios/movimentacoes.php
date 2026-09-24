<?php
/**
 * Relatório de movimentações
 * Com resumo de entradas e saídas por período
 */

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/verificar_sessao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

$data_inicio = $_GET['data_inicio'] ?? date('Y-m-01'); // Início do mês atual
$data_fim = $_GET['data_fim'] ?? date('Y-m-d'); // Hoje
$produto_id = isset($_GET['produto_id']) ? (int) $_GET['produto_id'] : 0;

try {
    $sql = '
        SELECT 
            m.id,
            m.produto_id,
            p.nome AS produto_nome,
            p.unidade_medida,
            m.lote_id,
            l.numero_lote,
            m.tipo,
            m.quantidade,
            m.data_movimentacao,
            m.usuario_id,
            u.nome AS usuario_nome,
            m.observacao
        FROM movimentacoes m
        INNER JOIN produtos p ON m.produto_id = p.id
        INNER JOIN lotes l ON m.lote_id = l.id
        INNER JOIN usuarios u ON m.usuario_id = u.id
        WHERE DATE(m.data_movimentacao) BETWEEN :data_inicio AND :data_fim
    ';
    $params = [
        'data_inicio' => $data_inicio,
        'data_fim' => $data_fim
    ];

    if ($produto_id > 0) {
        $sql .= ' AND m.produto_id = :produto_id';
        $params['produto_id'] = $produto_id;
    }

    $sql .= ' ORDER BY m.data_movimentacao DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $movimentacoes = $stmt->fetchAll();

    // Resumo: total de entradas e saídas
    $sqlResumo = '
        SELECT 
            m.tipo,
            COUNT(*) AS total_movimentacoes,
            SUM(m.quantidade) AS total_quantidade
        FROM movimentacoes m
        WHERE DATE(m.data_movimentacao) BETWEEN :data_inicio AND :data_fim
    ';
    $resumoParams = ['data_inicio' => $data_inicio, 'data_fim' => $data_fim];
    if ($produto_id > 0) {
        $sqlResumo .= ' AND m.produto_id = :produto_id';
        $resumoParams['produto_id'] = $produto_id;
    }
    $sqlResumo .= ' GROUP BY m.tipo';

    $stmt = $pdo->prepare($sqlResumo);
    $stmt->execute($resumoParams);
    $resumo = $stmt->fetchAll();

    // Organizar resumo
    $resumoFormatado = [
        'entrada' => ['total_movimentacoes' => 0, 'total_quantidade' => 0],
        'saida' => ['total_movimentacoes' => 0, 'total_quantidade' => 0]
    ];
    foreach ($resumo as $r) {
        $resumoFormatado[$r['tipo']] = [
            'total_movimentacoes' => (int) $r['total_movimentacoes'],
            'total_quantidade' => (int) $r['total_quantidade']
        ];
    }

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Relatório de movimentações gerado com sucesso.',
        'dados' => [
            'movimentacoes' => $movimentacoes,
            'resumo' => $resumoFormatado,
            'periodo' => ['inicio' => $data_inicio, 'fim' => $data_fim]
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao gerar relatório de movimentações.']);
}