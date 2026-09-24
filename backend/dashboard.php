<?php
/**
 * Dashboard API - Retorna dados consolidados para o painel principal
 */

require_once __DIR__ . '/config/conexao.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/auth/verificar_sessao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

try {
    // 1. Total de produtos ativos
    $stmt = $pdo->query('SELECT COUNT(*) FROM produtos WHERE ativo = 1');
    $totalProdutos = (int) $stmt->fetchColumn();

    // 2. Total de itens em estoque (soma de todas as quantidades)
    $stmt = $pdo->query('SELECT COALESCE(SUM(pl.quantidade), 0) FROM produto_lote pl INNER JOIN produtos p ON p.id = pl.produto_id WHERE p.ativo = 1');
    $totalItensEstoque = (int) $stmt->fetchColumn();

    // 3. Produtos com estoque baixo
    $sqlBaixo = '
        SELECT COUNT(*)
        FROM produtos p
        WHERE p.ativo = 1
          AND COALESCE((
              SELECT SUM(pl.quantidade)
              FROM produto_lote pl
              WHERE pl.produto_id = p.id
          ), 0) <= p.estoque_minimo
    ';
    $stmt = $pdo->query($sqlBaixo);
    $estoqueBaixo = (int) $stmt->fetchColumn();

    // 4. Próximos do vencimento (30 dias)
    $diasAlerta = DIAS_ALERTA_VALIDADE;
    $dataLimite = date('Y-m-d', strtotime("+{$diasAlerta} days"));

    $sqlProximos = '
        SELECT COUNT(DISTINCT pl.produto_id)
        FROM produto_lote pl
        INNER JOIN lotes l ON pl.lote_id = l.id
        INNER JOIN produtos p ON pl.produto_id = p.id
        WHERE p.ativo = 1
          AND pl.quantidade > 0
          AND l.data_validade >= CURDATE()
          AND l.data_validade <= :data_limite
    ';
    $stmt = $pdo->prepare($sqlProximos);
    $stmt->execute(['data_limite' => $dataLimite]);
    $proximosValidade = (int) $stmt->fetchColumn();

    // 5. Vencidos
    $sqlVencidos = '
        SELECT COUNT(DISTINCT pl.produto_id)
        FROM produto_lote pl
        INNER JOIN lotes l ON pl.lote_id = l.id
        INNER JOIN produtos p ON pl.produto_id = p.id
        WHERE p.ativo = 1
          AND pl.quantidade > 0
          AND l.data_validade < CURDATE()
    ';
    $stmt = $pdo->query($sqlVencidos);
    $vencidos = (int) $stmt->fetchColumn();

    // 6. Movimentações recentes (últimas 10)
    $sqlMov = '
        SELECT
            m.id,
            m.tipo,
            m.quantidade,
            m.data_movimentacao,
            p.nome AS produto_nome,
            l.numero_lote,
            u.nome AS usuario_nome
        FROM movimentacoes m
        INNER JOIN produtos p ON m.produto_id = p.id
        INNER JOIN lotes l ON m.lote_id = l.id
        INNER JOIN usuarios u ON m.usuario_id = u.id
        ORDER BY m.data_movimentacao DESC
        LIMIT 10
    ';
    $stmt = $pdo->query($sqlMov);
    $movimentacoesRecentes = $stmt->fetchAll();

    // 7. Dados para gráfico: Estoque por categoria
    $sqlCat = '
        SELECT
            c.nome AS categoria,
            COALESCE(SUM(pl.quantidade), 0) AS total
        FROM categorias c
        LEFT JOIN produtos p ON c.id = p.categoria_id AND p.ativo = 1
        LEFT JOIN produto_lote pl ON p.id = pl.produto_id
        GROUP BY c.id
        ORDER BY total DESC
    ';
    $stmt = $pdo->query($sqlCat);
    $estoquePorCategoria = $stmt->fetchAll();

    // 8. Dados para gráfico: Entradas vs Saídas (últimos 30 dias)
    $sqlMov30 = '
        SELECT
            DATE(data_movimentacao) AS data,
            tipo,
            SUM(quantidade) AS total
        FROM movimentacoes
        WHERE data_movimentacao >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY DATE(data_movimentacao), tipo
        ORDER BY data
    ';
    $stmt = $pdo->query($sqlMov30);
    $movimentacoes30dias = $stmt->fetchAll();

    // Organizar dados para gráfico de entradas/saídas
    $datas = [];
    $entradas = [];
    $saidas = [];

    // Gerar últimos 30 dias
    for ($i = 29; $i >= 0; $i--) {
        $data = date('Y-m-d', strtotime("-{$i} days"));
        $datas[] = date('d/m', strtotime($data));
        $entradas[$data] = 0;
        $saidas[$data] = 0;
    }

    foreach ($movimentacoes30dias as $mov) {
        $data = $mov['data'];
        if ($mov['tipo'] === 'entrada') {
            $entradas[$data] = (int) $mov['total'];
        } else {
            $saidas[$data] = (int) $mov['total'];
        }
    }

    $graficoEntradasSaidas = [
        'labels' => $datas,
        'entradas' => array_values($entradas),
        'saidas' => array_values($saidas)
    ];

    // Gráfico estoque por categoria
    $graficoCategoria = [
        'labels' => array_column($estoquePorCategoria, 'categoria'),
        'data' => array_map('intval', array_column($estoquePorCategoria, 'total'))
    ];

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Dados do dashboard obtidos com sucesso.',
        'dados' => [
            'usuario' => [
                'id' => (int) $_SESSION['usuario_id'],
                'nome' => $_SESSION['nome'] ?? $_SESSION['usuario'] ?? 'Usuário',
                'nivel_acesso' => $_SESSION['nivel_acesso'] ?? 'operador'
            ],
            'estatisticas' => [
                'total_produtos' => $totalProdutos,
                'total_itens_estoque' => $totalItensEstoque,
                'estoque_baixo' => $estoqueBaixo,
                'proximos_validade' => $proximosValidade,
                'vencidos' => $vencidos
            ],
            'movimentacoes_recentes' => $movimentacoesRecentes,
            'graficos' => [
                'estoque_por_categoria' => $graficoCategoria,
                'entradas_saidas_30dias' => $graficoEntradasSaidas
            ]
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao obter dados do dashboard.']);
}