<?php
// NOTE: para se conectar com o db
include "conexao.php";

$recepcao = $_POST;

// NOTE: obtendo informacoes do front
$email = $_POST["email"];
$senha = $_POST["pwd"];

$stmt = $conn->prepare("INSERT INTO cliente (email, senha) VALUES (?, ?)");
$stmt->bind_param("ss", $email, $senha);
// $stmt->execute();

$resposta = [];
if ($stmt->execute()) {
  $resposta["msg"] = "Aluno cadastrado com sucesso";
} else {
  $resposta["msg"] = "Falha ao cadastrar o aluno";
}

header('Content-Type: application/json');
echo json_encode($recepcao);
