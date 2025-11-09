<?php
session_start();
require_once __DIR__ . '/../conexao.php';

// Verificar se o usuário está logado como prestador
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'prestador') {
  echo json_encode(['success' => false, 'message' => 'Acesso não autorizado. Faça login como prestador.']);
  exit;
}

try {
  // Obter parâmetros de busca
  $search = $_GET['search'] ?? '';
  $categoria = $_GET['categoria'] ?? '';
  $cidade = $_GET['cidade'] ?? '';
  $preco_min = $_GET['preco_min'] ?? '';
  $preco_max = $_GET['preco_max'] ?? '';

  // Construir query base
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
                sr.created_at as data_criacao,
                u.name as cliente_nome,
                u.email as cliente_email
              FROM service_request sr
              INNER JOIN cliente c ON sr.cliente_id = c.id
              INNER JOIN user u ON c.user_id = u.user_id
              WHERE sr.status = 'pendente'";

  $params = [];

  // Adicionar filtros
  if (!empty($search)) {
    $query .= " AND (sr.titulo LIKE ? OR sr.descricao LIKE ? OR sr.categoria LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
  }

  if (!empty($categoria)) {
    $query .= " AND sr.categoria = ?";
    $params[] = $categoria;
  }

  if (!empty($cidade)) {
    $query .= " AND sr.cidade LIKE ?";
    $params[] = "%{$cidade}%";
  }

  if (!empty($preco_min)) {
    $query .= " AND sr.orcamento_maximo >= ?";
    $params[] = floatval($preco_min);
  }

  if (!empty($preco_max)) {
    $query .= " AND sr.orcamento_maximo <= ?";
    $params[] = floatval($preco_max);
  }

  // Ordenação: por data (mais recentes primeiro), categoria, ou localização
  $orderBy = $_GET['order_by'] ?? 'data';
  
  switch ($orderBy) {
    case 'categoria':
      $query .= " ORDER BY sr.categoria ASC, sr.created_at DESC";
      break;
    case 'localizacao':
      $query .= " ORDER BY sr.cidade ASC, sr.created_at DESC";
      break;
    case 'preco':
      $query .= " ORDER BY sr.orcamento_maximo ASC, sr.created_at DESC";
      break;
    case 'data':
    default:
      $query .= " ORDER BY sr.created_at DESC";
      break;
  }

  // Usar PDO para a consulta preparada
  $stmt = $pdo->prepare($query);
  $stmt->execute($params);
  $solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Formatar dados
  foreach ($solicitacoes as &$solicitacao) {
    $solicitacao['id'] = (int)$solicitacao['id'];
    $solicitacao['orcamento_maximo'] = $solicitacao['orcamento_maximo'] !== null 
      ? (float)$solicitacao['orcamento_maximo'] 
      : null;
  }

  echo json_encode([
    'success' => true,
    'solicitacoes' => $solicitacoes,
    'total' => count($solicitacoes),
    'filtros' => [
      'search' => $search,
      'categoria' => $categoria,
      'cidade' => $cidade,
      'preco_min' => $preco_min,
      'preco_max' => $preco_max
    ]
  ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'message' => 'Erro ao buscar solicitações: ' . $e->getMessage()
  ], JSON_UNESCAPED_UNICODE);
}

