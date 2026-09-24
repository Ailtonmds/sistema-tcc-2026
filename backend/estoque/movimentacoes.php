<?php
/**
 * Listar movimentações com filtros
 * Filtros: período, produto, lote, tipo
 */

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/verificar_sessao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

// Parâmetros de filtro
$data_inicio = $_GET['data_inicio'] ?? null;
$data_fim = $_GET['data_fim'] ?? null;
$produto_id = isset($_GET['produto_id']) ? (int) $_GET['produto_id'] : 0;
$lote_id = isset($_GET['lote_id']) ? (int) $_GET['lote_id'] : 0;
$tipo = $_GET['tipo'] ?? '';
$limite = filter_var($_GET['limite'] ?? 100, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);
$pagina = filter_var($_GET['pagina'] ?? 1, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

if ($limite === false || $pagina === false) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Página e limite devem ser maiores que zero.']);
    exit;
}

$offset = ($pagina - 1) * $limite;

try {
    $sql = '
        SELECT 
            m.id,
            m.produto_id,
            p.nome AS produto_nome,
            m.lote_id,
            l.numero_lote,
            l.data_validade,
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
        WHERE 1=1
    ';
    $params = [];

    if ($data_inicio) {
        $sql .= ' AND DATE(m.data_movimentacao) >= :data_inicio';
        $params['data_inicio'] = $data_inicio;
    }

    if ($data_fim) {
        $sql .= ' AND DATE(m.data_movimentacao) <= :data_fim';
        $params['data_fim'] = $data_fim;
    }

    if ($produto_id > 0) {
        $sql .= ' AND m.produto_id = :produto_id';
        $params['produto_id'] = $produto_id;
    }

    if ($lote_id > 0) {
        $sql .= ' AND m.lote_id = :lote_id';
        $params['lote_id'] = $lote_id;
    }

    if ($tipo !== '') {
        $sql .= ' AND m.tipo = :tipo';
        $params['tipo'] = $tipo;
    }

    $sql .= ' ORDER BY m.data_movimentacao DESC LIMIT :limite OFFSET :offset';
    $params['limite'] = $limite;
    $params['offset'] = $offset;

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $val) {
        $stmt->bindValue(":$key", $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    $movimentacoes = $stmt->fetchAll();

    // Contar total para paginação
    $countSql = 'SELECT COUNT(*) FROM movimentacoes m WHERE 1=1';
    $countParams = [];
    if ($data_inicio) { $countSql .= ' AND DATE(m.data_movimentacao) >= :data_inicio'; $countParams['data_inicio'] = $data_inicio; }
    if ($data_fim) { $countSql .= ' AND DATE(m.data_movimentacao) <= :data_fim'; $countParams['data_fim'] = $data_fim; }
    if ($produto_id > 0) { $countSql .= ' AND m.produto_id = :produto_id'; $countParams['produto_id'] = $produto_id; }
    if ($lote_id > 0) { $countSql .= ' AND m.lote_id = :lote_id'; $countParams['lote_id'] = $lote_id; }
    if ($tipo !== '') { $countSql .= ' AND m.tipo = :tipo'; $countParams['tipo'] = $tipo; }
    
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($countParams);
    $total = $stmt->fetchColumn();

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Movimentações listadas com sucesso.',
        'dados' => $movimentacoes,
        'paginacao' => [
            'pagina' => $pagina,
            'limite' => $limite,
            'total' => (int) $total,
            'total_paginas' => ceil($total / $limite)
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao listar movimentações.']);
}