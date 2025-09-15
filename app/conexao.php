<!-- NOTE: conexao com banco de dados -->
<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "servicofacil";

// NOTE: a sequencia dos params aqui importa
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Falha na conexão");
}

?>