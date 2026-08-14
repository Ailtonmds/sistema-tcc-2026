<?php 
    $host = '127.0.0.1;port=3030'; // Added quotes and standard port format
    $db   = 'almoxarifado';        // Added quotes
    $user = 'root';                // Added quotes
    $pass = '';                    // Added empty quotes

    try { 
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass); 
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    } catch (PDOException $e) { 
        die("Erro ao conectar ao banco de dados: " . $e->getMessage()); 
    } 
?>