<?php
session_start();
header('Content-Type: application/json');

// Verificar se o usuário está logado e é cliente
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'Acesso negado']);
  exit;
}

require_once '../conexao.php';

try {
  // Receber dados do POST
  $data = json_decode(file_get_contents('php://input'), true);
  $contract_id = $data['contract_id'] ?? null;
  $rating = $data['rating'] ?? null;
  $comment = $data['comment'] ?? '';
  $cliente_id = $_SESSION['cliente_id'];

  // Validações
  if (!$contract_id || !$rating) {
    throw new Exception('Dados obrigatórios não fornecidos');
  }

  if ($rating < 1 || $rating > 5) {
    throw new Exception('Nota deve estar entre 1 e 5');
  }

  // Verificar se o contrato pertence ao cliente e está concluído
  $sql_check = "SELECT c.contract_id, c.status, c.cliente_id 
                  FROM contract c 
                  WHERE c.contract_id = ? AND c.cliente_id = ?";
  $stmt_check = $pdo->prepare($sql_check);
  $stmt_check->execute([$contract_id, $cliente_id]);
  $contract = $stmt_check->fetch(PDO::FETCH_ASSOC);

  if (!$contract) {
    throw new Exception('Contrato não encontrado ou você não tem permissão');
  }

  if ($contract['status'] !== 'completed') {
    throw new Exception('Só é possível avaliar contratos concluídos');
  }

  // Verificar se já existe avaliação para este contrato
  $sql_exists = "SELECT review_id FROM review WHERE contract_id = ? AND cliente_id = ?";
  $stmt_exists = $pdo->prepare($sql_exists);
  $stmt_exists->execute([$contract_id, $cliente_id]);

  if ($stmt_exists->fetch()) {
    throw new Exception('Você já avaliou este serviço');
  }

  // Inserir avaliação
  $sql_insert = "INSERT INTO review (contract_id, cliente_id, rating, comment) 
                   VALUES (?, ?, ?, ?)";
  $stmt_insert = $pdo->prepare($sql_insert);
  $stmt_insert->execute([$contract_id, $cliente_id, $rating, $comment]);

  echo json_encode([
    'success' => true,
    'message' => 'Avaliação registrada com sucesso!',
    'review_id' => $pdo->lastInsertId()
  ]);
} catch (Exception $e) {
  http_response_code(400);
  echo json_encode([
    'success' => false,
    'message' => $e->getMessage()
  ]);
}

