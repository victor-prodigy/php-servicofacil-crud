<?php
session_start();
header('Content-Type: application/json');

require_once '../conexao.php';

try {
  $prestador_id = $_GET['prestador_id'] ?? null;

  if (!$prestador_id) {
    throw new Exception('ID do prestador não fornecido');
  }

  // Buscar avaliações do prestador
  $sql = "SELECT 
                r.review_id,
                r.rating,
                r.comment,
                r.created_at,
                u.name as cliente_nome,
                sr.titulo as servico_titulo,
                sr.categoria
            FROM review r
            INNER JOIN contract c ON r.contract_id = c.contract_id
            INNER JOIN cliente cl ON r.cliente_id = cl.id
            INNER JOIN user u ON cl.user_id = u.user_id
            INNER JOIN service_request sr ON c.request_id = sr.request_id
            WHERE c.service_provider_id = ?
            ORDER BY r.created_at DESC";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([$prestador_id]);
  $avaliacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Calcular média de avaliações
  $sql_media = "SELECT AVG(r.rating) as media, COUNT(r.review_id) as total
                  FROM review r
                  INNER JOIN contract c ON r.contract_id = c.contract_id
                  WHERE c.service_provider_id = ?";
  $stmt_media = $pdo->prepare($sql_media);
  $stmt_media->execute([$prestador_id]);
  $estatisticas = $stmt_media->fetch(PDO::FETCH_ASSOC);

  echo json_encode([
    'success' => true,
    'avaliacoes' => $avaliacoes,
    'media' => round($estatisticas['media'], 1),
    'total' => $estatisticas['total']
  ]);
} catch (Exception $e) {
  http_response_code(400);
  echo json_encode([
    'success' => false,
    'message' => $e->getMessage()
  ]);
}
