<?php

/**
 * Universal Signup Handler
 * Processa o cadastro unificado de clientes e prestadores
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
  $password = $_POST['password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';
  $user_type = $_POST['user_type'] ?? '';

  // Campos específicos por tipo
  $phone_number = trim($_POST['phone_number'] ?? '');
  $specialty = trim($_POST['specialty'] ?? '');
  $location = trim($_POST['location'] ?? '');

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

  // Validações específicas por tipo de usuário
  if ($user_type === 'customer') {
    // Cliente: telefone é opcional
    // Nenhuma validação adicional necessária

  } elseif ($user_type === 'service_provider') {
    // Prestador: especialidade e localização obrigatórias
    if (empty($specialty)) {
      throw new Exception('Especialidade é obrigatória');
    }

    if (empty($location)) {
      throw new Exception('Localização é obrigatória');
    }

    // Validar especialidade
    $valid_specialties = [
      'Encanamento',
      'Elétrica',
      'Pintura',
      'Limpeza',
      'Jardinagem',
      'Marcenaria',
      'Pedreiro',
      'Mecânica',
      'Informática',
      'Outros'
    ];

    if (!in_array($specialty, $valid_specialties)) {
      throw new Exception('Especialidade inválida');
    }
  } else {
    throw new Exception('Tipo de usuário inválido');
  }

  // Verificar se o e-mail já existe na tabela user
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE email = ?");
  $stmt->execute([$email]);
  if ($stmt->fetchColumn() > 0) {
    throw new Exception('Este e-mail já está cadastrado');
  }

  // Verificar se o e-mail já existe nas tabelas específicas
  if ($user_type === 'customer') {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cliente WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
      throw new Exception('Este e-mail já está cadastrado como cliente');
    }
  } elseif ($user_type === 'service_provider') {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM service_provider WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
      throw new Exception('Este e-mail já está cadastrado como prestador');
    }
  }

  // Iniciar transação
  $pdo->beginTransaction();

  // Hash da senha
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

  // Inserir na tabela user primeiro
  $phone_for_user = $user_type === 'customer' ? $phone_number : null;
  $stmt = $pdo->prepare("
        INSERT INTO user (email, password, name, phone_number, identity_verified) 
        VALUES (?, ?, ?, ?, FALSE)
    ");
  $stmt->execute([$email, $hashed_password, $name, $phone_for_user]);
  $user_id = $pdo->lastInsertId();

  $response_data = [
    'user_id' => $user_id,
    'name' => $name,
    'email' => $email,
    'user_type' => $user_type
  ];

  // Inserir na tabela específica baseada no tipo
  if ($user_type === 'customer') {
    $stmt = $pdo->prepare("
            INSERT INTO cliente (user_id, email, senha) 
            VALUES (?, ?, ?)
        ");
    $stmt->execute([$user_id, $email, $hashed_password]);
    $customer_id = $pdo->lastInsertId();

    $response_data['customer_id'] = $customer_id;
    $response_data['phone_number'] = $phone_number;
    $success_message = 'Cliente cadastrado com sucesso!';
  } elseif ($user_type === 'service_provider') {
    $stmt = $pdo->prepare("
            INSERT INTO service_provider (user_id, email, password, name, specialty, location, identity_verified) 
            VALUES (?, ?, ?, ?, ?, ?, FALSE)
        ");
    $stmt->execute([$user_id, $email, $hashed_password, $name, $specialty, $location]);
    $service_provider_id = $pdo->lastInsertId();

    $response_data['service_provider_id'] = $service_provider_id;
    $response_data['specialty'] = $specialty;
    $response_data['location'] = $location;
    $success_message = 'Prestador cadastrado com sucesso!';
  }

  // Confirmar transação
  $pdo->commit();

  // Resposta de sucesso
  echo json_encode([
    'success' => true,
    'message' => $success_message,
    'data' => $response_data
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
  error_log("Erro de banco no cadastro unificado: " . $e->getMessage());

  // Resposta de erro genérica
  http_response_code(500);
  echo json_encode([
    'success' => false,
    'error' => 'Erro interno do servidor. Tente novamente.'
  ]);
}
