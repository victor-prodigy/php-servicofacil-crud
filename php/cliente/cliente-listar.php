<?php
// NOTE: tabela usuário

// NOTE: Define que a resposta será JSON
header("Content-Type: application/json");

// NOTE: Captura erros para evitar HTML indesejado
try {
  // NOTE: para se conectar com o db
  include "conexao.php";

  $stmt = $conn->prepare("SELECT id, email FROM cliente ORDER BY id DESC");

  $resposta = [];
  if ($stmt->execute()) {
    $result = $stmt->get_result();
    $clientes = [];

    while ($row = $result->fetch_assoc()) {
      $clientes[] = $row;
    }

    $resposta["msg"] = "Clientes listados com sucesso";
    $resposta["codigo"] = true;
    $resposta["lista"] = $clientes;
  } else {
    $resposta["msg"] = "Falha ao buscar clientes: " . $stmt->error;
    $resposta["codigo"] = false;
    $resposta["lista"] = [];
  }

  echo json_encode($resposta);
} catch (Exception $e) {
  $resposta = [
    "msg" => "Erro interno: " . $e->getMessage(),
    "codigo" => false
  ];
  echo json_encode($resposta);
}
