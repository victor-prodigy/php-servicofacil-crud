<?php
// NOTE: conexao com banco de dados

$host = "localhost";
$username = "root";
$password = "";
$dbname = "servicofacil";

// NOTE: a sequencia dos params aqui importa
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
