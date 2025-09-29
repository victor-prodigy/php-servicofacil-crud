<?php

// OBS: remover depois do teste

/**
 * Script para executar o db.sql via PHP
 */

$host = "localhost";
$username = "root";
$password = "";
$dbname = "servicofacil";

try {
  // Conectar ao banco
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
  ]);

  echo "✅ Conectado ao banco servicofacil\n";

  // Ler arquivo SQL
  $sql = file_get_contents('lib/db.sql');

  if ($sql === false) {
    throw new Exception("Erro ao ler arquivo db.sql");
  }

  // Remover comentários e linhas vazias, dividir por comandos
  $sql = preg_replace('/--.*$/m', '', $sql); // Remove comentários
  $sql = preg_replace('/\/\*.*?\*\//s', '', $sql); // Remove comentários de bloco
  $commands = preg_split('/;\s*$/m', $sql, -1, PREG_SPLIT_NO_EMPTY);

  $success_count = 0;
  $error_count = 0;

  foreach ($commands as $command) {
    $command = trim($command);
    if (empty($command) || strtoupper($command) === 'USE SERVICOFACIL') {
      continue;
    }

    try {
      $pdo->exec($command);
      $success_count++;
      echo "✅ Comando executado com sucesso\n";
    } catch (PDOException $e) {
      $error_count++;
      echo "❌ Erro ao executar comando: " . $e->getMessage() . "\n";
      echo "Comando: " . substr($command, 0, 100) . "...\n";
    }
  }

  echo "\n📊 Resultado:\n";
  echo "✅ Comandos executados com sucesso: $success_count\n";
  echo "❌ Comandos com erro: $error_count\n";

  // Verificar tabelas criadas
  $stmt = $pdo->query("SHOW TABLES");
  $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

  echo "\n📋 Tabelas no banco:\n";
  foreach ($tables as $table) {
    echo "- $table\n";
  }
} catch (Exception $e) {
  echo "❌ Erro: " . $e->getMessage() . "\n";
}
