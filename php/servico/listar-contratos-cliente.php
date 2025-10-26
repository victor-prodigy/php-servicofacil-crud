<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente' || !isset($_SESSION['cliente_id'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'Acesso negado']);
  exit;
}
require_once '../conexao.php';

try {
  $cliente_id = $_SESSION['cliente_id'];

  // Buscar contratos do cliente
  $sql = "SELECT 
                c.contract_id,
                c.status,
                c.created_at,
                sr.titulo,
                sr.categoria,
                u.name as prestador_nome,
                sp.specialty,
                sp.location,
                CASE 
                    WHEN r.review_id IS NOT NULL THEN 1
                    ELSE 0
                END as ja_avaliado
            FROM contract c
            INNER JOIN service_request sr ON c.request_id = sr.request_id
            INNER JOIN service_provider sp ON c.service_provider_id = sp.service_provider_id
            INNER JOIN user u ON sp.user_id = u.user_id
            LEFT JOIN review r ON c.contract_id = r.contract_id AND r.cliente_id = ?
            WHERE c.cliente_id = ?
            ORDER BY c.created_at DESC";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([$cliente_id, $cliente_id]);
  $contratos = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode([
    'success' => true,
    'contratos' => $contratos
  ]);
} catch (Exception $e) {
  // Log the detailed error for debugging purposes
  error_log("Error in listar-contratos-cliente.php: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());

  http_response_code(500);
  echo json_encode([
    'success' => false,
    'message' => 'Ocorreu um erro interno. Tente novamente mais tarde.'
  ]);
}
