<?php

/**
 * Conexão com banco de dados usando PDO
 */

$host = "localhost";
$username = "root";
$db_password = "";
$dbname = "servicofacil";

try {
    // Criar conexão PDO
    // PDO (PHP Data Objects) é uma extensão do PHP que fornece uma interface consistente para acessar diferentes tipos de banco de dados. É uma camada de abstração que permite trabalhar com vários SGBDs usando a mesma sintaxe.
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

    // Manter compatibilidade com código antigo (mysqli)
    $conn = new mysqli($host, $username, $db_password, $dbname);
    $conexao = $conn; // Alias para compatibilidade

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}
