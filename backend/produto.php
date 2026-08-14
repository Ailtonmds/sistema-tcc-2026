<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = htmlspecialchars($_POST['nome'] ?? '');
    $id_categoria = intval($_POST['id_categoria'] ?? 0);
    $unidade = htmlspecialchars($_POST['unidade'] ?? '');
    $id_lote = intval($_POST['id_lote'] ?? 0);
    $validade = htmlspecialchars($_POST['validade'] ?? '');
    $localizacao = htmlspecialchars($_POST['localizacao'] ?? '');

    try {
        $query = "INSERT INTO produto (nome, id_categoria, unidade, id_lote, validade, localizacao) 
                  VALUES (:nome, :id_categoria, :unidade, :id_lote, :validade, :localizacao)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
        $stmt->bindParam(':unidade', $unidade);
        $stmt->bindParam(':id_lote', $id_lote, PDO::PARAM_INT);
        $stmt->bindParam(':validade', $validade);
        $stmt->bindParam(':localizacao', $localizacao);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Produto inserido com sucesso.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Falha ao inserir o produto.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro na consulta: ' . $e->getMessage()]);
    }
}
?>
