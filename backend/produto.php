<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $descricao = htmlspecialchars($post['descricao']);
        $categoria = htmlspecialchars($post['categoria']);
        $unidade = htmlspecialchars($post['unidade']);
        $lote = htmlspecialchars($post['lote']);
        $validade = htmlspecialchars($post['validade']);
        $categoria = htmlspecialchars($post['localizacao']);
    }
?>