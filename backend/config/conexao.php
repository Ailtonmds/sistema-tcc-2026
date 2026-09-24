<?php

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    http_response_code(404);
    exit;
}
/**
 * Conexão com banco de dados MySQL/MariaDB usando PDO
 * Configuração centralizada para todo o backend
 */

$host = '127.0.0.1';
$port = '3306';
$db = 'almoxarifado';
$user = 'root';
$pass = '';

$dsnServidor = "mysql:host={$host};port={$port};charset=utf8mb4";
$opcoes = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO(
        "{$dsnServidor};dbname={$db}",
        $user,
        $pass,
        $opcoes
    );
} catch (PDOException $e) {
    try {
        $pdo = new PDO($dsnServidor, $user, $pass, $opcoes);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$db}`");
    } catch (PDOException $fallback) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Erro ao conectar ao banco de dados. Verifique a configuração em backend/config/conexao.php.'
        ]);
        exit;
    }
}