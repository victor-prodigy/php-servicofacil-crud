<?php

/**
 * Cliente Signup
 * Processa o cadastro de novos clientes
 */

// Headers para resposta JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Método não permitido']);
  exit;
}

// Incluir arquivo de conexão
require_once 'conexao.php';

try {
  // Receber e validar dados do formulário
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone_number = trim($_POST['phone_number'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';
  $user_type = $_POST['user_type'] ?? '';

  // Validações básicas
  if (empty($name)) {
    throw new Exception('Nome é obrigatório');
  }

  if (empty($email)) {
    throw new Exception('E-mail é obrigatório');
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    throw new Exception('E-mail inválido');
  }

  if (empty($password)) {
    throw new Exception('Senha é obrigatória');
  }

  if (strlen($password) < 6) {
    throw new Exception('Senha deve ter pelo menos 6 caracteres');
  }

  if ($password !== $confirm_password) {
    throw new Exception('As senhas não coincidem');
  }

  if ($user_type !== 'customer') {
    throw new Exception('Tipo de usuário inválido');
  }

  // Verificar se o e-mail já existe
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE email = ?");
  $stmt->execute([$email]);
  if ($stmt->fetchColumn() > 0) {
    throw new Exception('Este e-mail já está cadastrado');
  }

  // Verificar na tabela customer também
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM customer WHERE email = ?");
  $stmt->execute([$email]);
  if ($stmt->fetchColumn() > 0) {
    throw new Exception('Este e-mail já está cadastrado como cliente');
  }

  // Iniciar transação
  $pdo->beginTransaction();

  // Hash da senha
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

  // Inserir na tabela user primeiro
  $stmt = $pdo->prepare("
        INSERT INTO user (email, password, name, phone_number, identity_verified) 
        VALUES (?, ?, ?, ?, FALSE)
    ");
  $stmt->execute([$email, $hashed_password, $name, $phone_number]);
  $user_id = $pdo->lastInsertId();

  // Inserir na tabela customer
  $stmt = $pdo->prepare("
        INSERT INTO customer (user_id, email, password, name, phone_number, identity_verified) 
        VALUES (?, ?, ?, ?, ?, FALSE)
    ");
  $stmt->execute([$user_id, $email, $hashed_password, $name, $phone_number]);
  $customer_id = $pdo->lastInsertId();

  // Confirmar transação
  $pdo->commit();

  // Resposta de sucesso
  echo json_encode([
    'success' => true,
    'message' => 'Cliente cadastrado com sucesso!',
    'data' => [
      'user_id' => $user_id,
      'customer_id' => $customer_id,
      'name' => $name,
      'email' => $email
    ]
  ]);
} catch (Exception $e) {
  // Reverter transação em caso de erro
  if ($pdo->inTransaction()) {
    $pdo->rollBack();
  }

  // Resposta de erro
  http_response_code(400);
  echo json_encode([
    'success' => false,
    'error' => $e->getMessage()
  ]);
} catch (PDOException $e) {
  // Reverter transação em caso de erro de banco
  if ($pdo->inTransaction()) {
    $pdo->rollBack();
  }

  // Log do erro (em produção, não mostrar detalhes do banco)
  error_log("Erro de banco no cadastro de cliente: " . $e->getMessage());

  // Resposta de erro genérica
  http_response_code(500);
  echo json_encode([
    'success' => false,
    'error' => 'Erro interno do servidor. Tente novamente.'
  ]);
}
