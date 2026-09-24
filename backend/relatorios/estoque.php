<?php
/**
 * Relatório de estoque atual
 * Agrupado por categoria com totais
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
    // Estoque por produto
    $sql = '
        SELECT 
            p.id,
            p.nome,
            p.unidade_medida,
            p.estoque_minimo,
            p.localizacao,
            c.nome AS categoria,
            COALESCE(SUM(pl.quantidade), 0) AS quantidade_total,
            CASE 
                WHEN COALESCE(SUM(pl.quantidade), 0) = 0 THEN "sem_estoque"
                WHEN COALESCE(SUM(pl.quantidade), 0) <= p.estoque_minimo THEN "baixo"
                ELSE "ok"
            END AS status_estoque
        FROM produtos p
        INNER JOIN categorias c ON p.categoria_id = c.id
        LEFT JOIN produto_lote pl ON p.id = pl.produto_id
        WHERE p.ativo = 1
        GROUP BY p.id
        ORDER BY c.nome, p.nome
    ';

    $stmt = $pdo->query($sql);
    $produtos = $stmt->fetchAll();

    // Resumo por categoria
    $sqlResumo = '
        SELECT 
            c.nome AS categoria,
            COUNT(DISTINCT p.id) AS total_produtos,
            COALESCE(SUM(pl.quantidade), 0) AS quantidade_total,
            SUM(CASE WHEN COALESCE(SUM(pl.quantidade), 0) = 0 THEN 1 ELSE 0 END) AS sem_estoque,
            SUM(CASE WHEN COALESCE(SUM(pl.quantidade), 0) <= p.estoque_minimo AND COALESCE(SUM(pl.quantidade), 0) > 0 THEN 1 ELSE 0 END) AS estoque_baixo,
            SUM(CASE WHEN COALESCE(SUM(pl.quantidade), 0) > p.estoque_minimo THEN 1 ELSE 0 END) AS estoque_ok
        FROM produtos p
        INNER JOIN categorias c ON p.categoria_id = c.id
        LEFT JOIN produto_lote pl ON p.id = pl.produto_id
        WHERE p.ativo = 1
        GROUP BY c.id
        ORDER BY c.nome
    ';

    // A query acima não funciona bem com SUM dentro de CASE, vamos fazer em PHP
    $resumoPorCategoria = [];
    foreach ($produtos as $produto) {
        $cat = $produto['categoria'];
        if (!isset($resumoPorCategoria[$cat])) {
            $resumoPorCategoria[$cat] = [
                'categoria' => $cat,
                'total_produtos' => 0,
                'quantidade_total' => 0,
                'sem_estoque' => 0,
                'estoque_baixo' => 0,
                'estoque_ok' => 0
            ];
        }
        $resumoPorCategoria[$cat]['total_produtos']++;
        $resumoPorCategoria[$cat]['quantidade_total'] += (int) $produto['quantidade_total'];
        if ($produto['status_estoque'] === 'sem_estoque') $resumoPorCategoria[$cat]['sem_estoque']++;
        elseif ($produto['status_estoque'] === 'baixo') $resumoPorCategoria[$cat]['estoque_baixo']++;
        else $resumoPorCategoria[$cat]['estoque_ok']++;
    }

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Relatório de estoque gerado com sucesso.',
        'dados' => [
            'produtos' => $produtos,
            'resumo_por_categoria' => array_values($resumoPorCategoria)
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao gerar relatório de estoque.']);
}