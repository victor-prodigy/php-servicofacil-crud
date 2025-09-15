<?php
// NOTE: Define que a resposta será JSON
header('Content-Type: application/json');

// NOTE: Captura erros para evitar HTML indesejado
try {
  // NOTE: para se conectar com o db
  include "conexao.php";

  $stmt = $conn->prepare("SELECT * FROM cliente");

  $resposta = [];
  if ($stmt->execute()) {
    $result = $stmt->get_result();
    $clientes = [];

    while ($row = $result->fetch_assoc()) {
      $clientes[] = $row;
    }

    $resposta["msg"] = "Cliente cadastrado com sucesso";
    $resposta["codigo"] = true;
    $resposta["lista"] = $clientes;
  } else {
    // Tratamento específico para email duplicado
    if ($stmt->errno == 1062) { // Código MySQL para duplicate entry
      $resposta["msg"] = "Este email já está cadastrado no sistema";
    } else {
      $resposta["msg"] = "Falha ao cadastrar o cliente: " . $stmt->error;
    }
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
