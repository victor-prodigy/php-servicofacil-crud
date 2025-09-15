<?php
// NOTE: Define que a resposta será JSON
header('Content-Type: application/json');

// NOTE: Captura erros para evitar HTML indesejado
try {
  // NOTE: para se conectar com o db
  include "conexao.php";

  $recepcao = $_POST;

  // NOTE: obtendo informacoes do front
  $email = $_POST["email"];
  $senha = $_POST["senha"]; // Usando "senha" que é o nome correto enviado pelo JS

  // NOTE: Hash da senha por segurança
  $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

  $stmt = $conn->prepare("INSERT INTO cliente (email, senha) VALUES (?, ?)");
  $stmt->bind_param("ss", $email, $senha_hash);

  $resposta = [];
  if ($stmt->execute()) {
    $resposta["msg"] = "Cliente cadastrado com sucesso";
    $resposta["codigo"] = true;
  } else {
    // Tratamento específico para email duplicado
    if ($stmt->errno == 1062) { // Código MySQL para duplicate entry
      $resposta["msg"] = "Este email já está cadastrado no sistema";
    } else {
      $resposta["msg"] = "Falha ao cadastrar o cliente: " . $stmt->error;
    }
    $resposta["codigo"] = false;
  }

  echo json_encode($resposta);
} catch (Exception $e) {
  $resposta = [
    "msg" => "Erro interno: " . $e->getMessage(),
    "codigo" => false
  ];
  echo json_encode($resposta);
}


// <?php
// // NOTE: para se conectar com o db
// include "conexao.php";

// $recepcao = $_POST;

// // NOTE: obtendo informacoes do front
// $email = $_POST["email"];
// $senha = $_POST["pwd"];

// $stmt = $conn->prepare("INSERT INTO cliente (email, senha) VALUES (?, ?)");
// $stmt->bind_param("ss", $email, $senha);
// // $stmt->execute();

// $resposta = [];
// if ($stmt->execute()) {
//   $resposta["msg"] = "Cliente cadastrado com sucesso";
//   $resposta["codigo"] = true;
// } else {
//   $resposta["msg"] = "Falha ao cadastrar o cliente";
//   $resposta["codigo"] = false;
// }

// header('Content-Type: application/json');
// echo json_encode($resposta);
