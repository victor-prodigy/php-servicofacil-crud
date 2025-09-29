<?php
// TODO: fazer try catch
include "conexao.php";

$recepcao = $_POST;

// NOTE: obtendo informacoes do front
$id = $_POST["id"];

$stmt = $conn->prepare("DELETE FROM cliente WHERE id = ?");
$stmt->bind_param("i", $id);

$resposta = [];
if ($stmt->execute()) {
  $resposta["msg"] = "Cliente excluido com sucesso";
  $resposta["codigo"] = true;
} else {
  // Tratamento específico para email duplicado
  if ($stmt->errno == 1062) { // Código MySQL para duplicate entry
    $resposta["msg"] = "Este email já está cadastrado no sistema";
  } else {
    $resposta["msg"] = "Falha ao excluir o cliente: " . $stmt->error;
  }
  $resposta["codigo"] = false;
}

echo json_encode($resposta);
