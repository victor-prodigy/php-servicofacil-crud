<?php
/**
 * Script para criar as tabelas no banco de dados servicofacil
 * Este script deve ser executado após o create-database.php
 */

// Configurações de conexão com o banco de dados
$host = "localhost";
$username = "root";
$password = "";
$dbname = "servicofacil";

// Tenta conectar ao banco de dados
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Lê o conteúdo do arquivo db.sql
    $sql = file_get_contents(__DIR__ . '/../lib/db.sql');
    
    // Executa o SQL
    $conn->exec($sql);
    
    echo "<h2>✅ Tabelas criadas com sucesso!</h2>";
    echo "<p>Agora você pode <a href='seed-database.php'>popular o banco de dados</a> com dados de exemplo.</p>";
    
} catch (PDOException $e) {
    echo "<h2>❌ Erro ao criar tabelas</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<p>Certifique-se de que o banco de dados 'servicofacil' foi criado executando primeiro o script <a href='create-database.php'>create-database.php</a>.</p>";
}
?>