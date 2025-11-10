<?php
session_start();
require_once __DIR__ . '/../conexao.php';

header('Content-Type: application/json');

// Verificar se o usuário está logado como cliente
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'cliente') {
  echo json_encode([
    'success' => false, 
    'message' => 'Acesso não autorizado. Faça login como cliente.',
    'contratos' => []
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  // Obter cliente_id da sessão
  $clienteId = $_SESSION['cliente_id'] ?? null;
  
  if (!$clienteId) {
    echo json_encode([
      'success' => false,
      'message' => 'ID do cliente não encontrado na sessão.',
      'contratos' => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // Buscar todos os contratos do cliente com informações do prestador e serviço
  $query = "SELECT 
              c.contract_id,
              c.request_id,
              c.service_provider_id,
              c.cliente_id,
              c.contract_terms,
              c.status,
              c.created_at,
              sr.titulo,
              sr.categoria,
              u.name as prestador_nome,
              sp.specialty,
              CASE WHEN r.review_id IS NOT NULL THEN 1 ELSE 0 END as ja_avaliado,
              p.payment_id,
              p.status as payment_status,
              p.amount as payment_amount
            FROM contract c
            INNER JOIN service_request sr ON c.request_id = sr.request_id
            INNER JOIN service_provider sp ON c.service_provider_id = sp.service_provider_id
            INNER JOIN user u ON sp.user_id = u.user_id
            LEFT JOIN review r ON c.contract_id = r.contract_id AND r.cliente_id = c.cliente_id
            LEFT JOIN payment p ON p.contract_id = c.contract_id
            WHERE c.cliente_id = ?
            ORDER BY c.created_at DESC";

  // Usar PDO para a consulta preparada
  $stmt = $pdo->prepare($query);
  $stmt->execute([$clienteId]);
  $contratos = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Formatar dados
  foreach ($contratos as &$contrato) {
    $contrato['contract_id'] = (int)$contrato['contract_id'];
    $contrato['request_id'] = (int)$contrato['request_id'];
    $contrato['service_provider_id'] = (int)$contrato['service_provider_id'];
    $contrato['cliente_id'] = (int)$contrato['cliente_id'];
    $contrato['ja_avaliado'] = (int)$contrato['ja_avaliado'];
  }

  echo json_encode([
    'success' => true,
    'contratos' => $contratos,
    'total' => count($contratos)
  ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'message' => 'Erro ao listar contratos: ' . $e->getMessage(),
    'contratos' => []
  ], JSON_UNESCAPED_UNICODE);
}

