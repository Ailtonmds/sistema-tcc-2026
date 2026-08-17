<?php
declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_NOME = 'almoxarifado';
const DB_USUARIO = 'root';
const DB_SENHA = '';

function conectarBanco(): PDO
{
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NOME . ';charset=utf8mb4';

    return new PDO($dsn, DB_USUARIO, DB_SENHA, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}
