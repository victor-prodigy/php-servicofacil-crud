<?php

// OBS: remover depois do teste

/**
 * Script para criar o banco de dados servicofacil
 */

$host = "localhost";
$username = "root";
$password = "";

try {
  // Conectar sem especificar banco
  $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
  ]);

  // Criar banco se não existir
  $pdo->exec("CREATE DATABASE IF NOT EXISTS servicofacil CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
  echo "✅ Banco 'servicofacil' criado/verificado com sucesso!\n";
} catch (PDOException $e) {
  echo "❌ Erro ao criar banco: " . $e->getMessage() . "\n";
}
