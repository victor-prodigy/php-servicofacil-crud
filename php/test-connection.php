<?php

// OBS: remover depois do teste

/**
 * Teste de conexão com o banco
 */

echo "<h1>Teste de Conexão</h1>";

// Testar conexão PDO
try {
  $host = "localhost";
  $username = "root";
  $password = "";
  $dbname = "servicofacil";

  $dsn = "mysql:host=$host;charset=utf8mb4";
  $pdo_test = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
  ]);

  echo "<p style='color: green;'>✅ Conexão com MySQL estabelecida</p>";

  // Verificar se o banco existe
  $stmt = $pdo_test->query("SHOW DATABASES LIKE 'servicofacil'");
  $db_exists = $stmt->fetch();

  if ($db_exists) {
    echo "<p style='color: green;'>✅ Banco 'servicofacil' encontrado</p>";

    // Conectar ao banco específico
    $dsn_with_db = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn_with_db, $username, $password, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "<p style='color: green;'>✅ Conexão com banco 'servicofacil' estabelecida</p>";

    // Verificar tabelas
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($tables) > 0) {
      echo "<p style='color: green;'>✅ Tabelas encontradas: " . implode(', ', $tables) . "</p>";
    } else {
      echo "<p style='color: orange;'>⚠️ Nenhuma tabela encontrada no banco</p>";
    }
  } else {
    echo "<p style='color: red;'>❌ Banco 'servicofacil' não encontrado</p>";
    echo "<p>Execute o arquivo db.sql para criar o banco e as tabelas</p>";
  }
} catch (PDOException $e) {
  echo "<p style='color: red;'>❌ Erro de conexão: " . $e->getMessage() . "</p>";
}
