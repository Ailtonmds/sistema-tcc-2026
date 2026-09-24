<?php
/**
 * Script para inicializar o banco de dados
 * Acesse via: http://localhost/sistema-tcc-2026/init_db.php
 */

require_once __DIR__ . '/backend/config/conexao.php';

echo "<h1>Inicialização do Banco de Dados</h1>";

// Ler o arquivo SQL
$sql = file_get_contents(__DIR__ . '/database/database.sql');

if ($sql === false) {
    die("<p style='color:red'>Erro ao ler database.sql</p>");
}

// Dividir em statements individuais
$sql = preg_replace('/^\s*--.*$/m', '', $sql);
$statements = array_filter(array_map('trim', explode(';', $sql)));

$success = 0;
$skipped = 0;
$errors = 0;

foreach ($statements as $stmt) {
    if ($stmt === '') continue;

    try {
        $pdo->exec($stmt);
        $success++;
        echo "<p style='color:green'>✓ Executado</p>";
    } catch (PDOException $e) {
        $errorInfo = $e->errorInfo;
        $driverCode = (int) ($errorInfo[1] ?? 0);
        $isDuplicateIndex = preg_match('/^CREATE\s+INDEX\b/i', $stmt) === 1
            && ($driverCode === 1061 || stripos($e->getMessage(), 'Duplicate key name') !== false);

        if ($isDuplicateIndex) {
            $skipped++;
            echo "<p style='color:#b26a00'>↷ Ignorado: índice já existe</p>";
            continue;
        }

        $errors++;
        echo "<p style='color:red'>✗ Erro: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
        echo "<pre style='background:#f5f5f5;padding:10px'>" . htmlspecialchars($stmt, ENT_QUOTES, 'UTF-8') . "</pre>";
    }
}

echo "<hr>";
echo "<h2>Resumo</h2>";
echo "<p>Sucessos: $success</p>";
echo "<p>Ignorados: $skipped</p>";
echo "<p>Erros: $errors</p>";

if ($errors === 0) {
    echo "<p style='color:green'><strong>Banco de dados inicializado com sucesso!</strong></p>";
    echo "<p><a href='frontend/login/'>Ir para o Login</a></p>";
} else {
    echo "<p style='color:red'>Verifique os erros acima.</p>";
}