<?php
    require_once 'config/database.php';

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        try {
            // 1. Prepara a consulta SQL 
            $query = "SELECT * FROM user_login";
            $stmt = $pdo->prepare($query); // $pdo vem do database.php
            
            // 2. Executa a busca
            $stmt->execute();
            
            // 3. Pega os resultados em formato de array associativo
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // 4. Retorna os dados (Exemplo em JSON para APIs)
            header('Content-Type: application/json');
            echo json_encode($resultados);
            
        } catch (PDOException $e) {
            echo "Erro na consulta: " . $e->getMessage();
        }
    }
?>
