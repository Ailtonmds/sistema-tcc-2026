<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metodo nao permitido.');
}

// CORRECAO: impede inclusao de produtos por requisicoes sem uma sessao autenticada.
session_start();
if (empty($_SESSION['usuario_id'])) {
    header('Location: ../index.html?erro=autenticacao', true, 303);
    exit;
}

// CORRECAO: os nomes abaixo correspondem exatamente aos campos enviados pelo formulario.
$dados = [
    'nome' => trim($_POST['nome'] ?? ''),
    'categoria' => trim($_POST['categoria'] ?? ''),
    'unidade' => trim($_POST['unidade'] ?? ''),
    'lote' => trim($_POST['lote'] ?? ''),
    'validade' => trim($_POST['validade'] ?? ''),
    'localizacao' => trim($_POST['localizacao'] ?? ''),
    // O cadastro cria o produto sem movimentar o estoque.
    'quantidade' => 0,
];

if (in_array('', $dados, true) || !DateTime::createFromFormat('Y-m-d', $dados['validade'])) {
    http_response_code(422);
    exit('Preencha todos os campos com dados validos.');
}

try {
    // CORRECAO: valores de entrada sao parametrizados; htmlspecialchars e para saida HTML, nao SQL.
    $stmt = $pdo->prepare(
        'INSERT INTO produto (nome, categoria, unidade, lote, validade, localizacao, quantidade)
         VALUES (:nome, :categoria, :unidade, :lote, :validade, :localizacao, :quantidade)'
    );
    $stmt->execute($dados);

    header('Location: ../frontend/cadastroprodutos.html?sucesso=1', true, 303);
    exit;
} catch (PDOException $e) {
    http_response_code(500);
    exit('Nao foi possivel registrar o produto.');
}
