<?php
session_start();
require_once __DIR__ . '/../conexao.php';

header('Content-Type: application/json');

// Verificar se o usuário está logado como cliente
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'cliente') {
  echo json_encode([
    'success' => false, 
    'message' => 'Acesso não autorizado. Faça login como cliente.',
    'solicitacoes' => []
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
      'solicitacoes' => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // Buscar todas as solicitações do cliente (independente do status)
  $query = "SELECT 
              sr.request_id as id,
              sr.titulo,
              sr.categoria,
              sr.descricao,
              sr.endereco,
              sr.cidade,
              sr.prazo_desejado,
              sr.orcamento_maximo,
              sr.observacoes,
              sr.status,
              sr.created_at as data_criacao
            FROM service_request sr
            WHERE sr.cliente_id = ?
            ORDER BY sr.created_at DESC";

  // Usar PDO para a consulta preparada
  $stmt = $pdo->prepare($query);
  $stmt->execute([$clienteId]);
  $solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Formatar dados
  foreach ($solicitacoes as &$solicitacao) {
    $solicitacao['id'] = (int)$solicitacao['id'];
    $solicitacao['orcamento_maximo'] = $solicitacao['orcamento_maximo'] !== null 
      ? (float)$solicitacao['orcamento_maximo'] 
      : null;
    // Garantir que prazo_desejado está no formato correto
    if ($solicitacao['prazo_desejado']) {
      $solicitacao['prazo_desejado'] = date('Y-m-d', strtotime($solicitacao['prazo_desejado']));
    }
  }

  echo json_encode([
    'success' => true,
    'solicitacoes' => $solicitacoes,
    'total' => count($solicitacoes)
  ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'message' => 'Erro ao listar solicitações: ' . $e->getMessage(),
    'solicitacoes' => []
  ], JSON_UNESCAPED_UNICODE);
}

