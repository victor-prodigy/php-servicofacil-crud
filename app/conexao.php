<?php

/**
 * Conexão com banco de dados usando PDO
 */

$host = "localhost";
$username = "root";
$password = "";
$dbname = "servicofacil";

try {
    // Criar conexão PDO
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

    // Manter compatibilidade com código antigo (mysqli)
    $conn = new mysqli($host, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}
