<?php
/**
 * Listar produtos
 * Suporta filtros: busca, categoria_id, ativo
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
$busca = trim($_GET['busca'] ?? '');
$categoria_id = isset($_GET['categoria_id']) ? (int) $_GET['categoria_id'] : 0;
$ativo = isset($_GET['ativo']) && trim((string) $_GET['ativo']) !== '' ? (bool) $_GET['ativo'] : null;

try {
    $sql = '
        SELECT 
            p.id,
            p.categoria_id,
            c.nome AS categoria_nome,
            p.nome,
            p.descricao,
            p.unidade_medida,
            p.estoque_minimo,
            p.localizacao,
            p.ativo,
            p.criado_em,
            COALESCE(SUM(pl.quantidade), 0) AS quantidade_total
        FROM produtos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        LEFT JOIN produto_lote pl ON p.id = pl.produto_id
        WHERE 1=1
    ';
    $params = [];

    if ($busca !== '') {
        $sql .= ' AND (p.nome LIKE :busca OR p.descricao LIKE :busca)';
        $params['busca'] = "%{$busca}%";
    }

    if ($categoria_id > 0) {
        $sql .= ' AND p.categoria_id = :categoria_id';
        $params['categoria_id'] = $categoria_id;
    }

    if ($ativo !== null) {
        $sql .= ' AND p.ativo = :ativo';
        $params['ativo'] = $ativo;
    }

    $sql .= ' GROUP BY p.id ORDER BY p.nome';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $produtos = $stmt->fetchAll();

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Produtos listados com sucesso.',
        'dados' => $produtos
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao listar produtos.']);
}