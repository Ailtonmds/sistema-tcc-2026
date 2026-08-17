<?php
    include_once 'config/database.php';

    $stmt = $pdo->query('SELECT COUNT(*) FROM produto');
    $totalProdutos = $stmt->fetchColumn();

    $stmt = $pdo->query('SELECT SUM(quantidade) FROM produto');
    $totalItensEstoque = $stmt->fetchColumn();

    $stmt = $pdo->query('SELECT COUNT(*) FROM produto WHERE quantidade < 10');
    $estoqueBaixo = $stmt->fetchColumn();
?>