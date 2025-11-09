<?php
session_start();
require_once __DIR__ . '/../conexao.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_tipo'])) {
  echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
  exit;
}

try {
  // Obter parâmetros de busca
  $search = trim($_GET['search'] ?? '');
  $specialty = trim($_GET['specialty'] ?? '');
  $location = trim($_GET['location'] ?? '');

  // Construir query base
  $query = "SELECT 
                sp.service_provider_id as prestador_id,
                sp.specialty as especialidade,
                sp.location as localizacao,
                u.user_id,
                u.name as nome,
                u.email,
                u.phone_number as telefone,
                u.status,
                u.created_at as data_registro,
                (SELECT AVG(rating) 
                 FROM review r
                 INNER JOIN contract c ON r.contract_id = c.contract_id
                 WHERE c.service_provider_id = sp.service_provider_id) as avaliacao_media,
                (SELECT COUNT(*) 
                 FROM review r
                 INNER JOIN contract c ON r.contract_id = c.contract_id
                 WHERE c.service_provider_id = sp.service_provider_id) as total_avaliacoes,
                (SELECT COUNT(*) 
                 FROM contract c
                 WHERE c.service_provider_id = sp.service_provider_id 
                 AND c.status = 'completed') as servicos_concluidos
              FROM service_provider sp
              INNER JOIN user u ON sp.user_id = u.user_id
              WHERE u.status = 'ativo' AND u.user_type = 'prestador'";

  $params = [];

  // Adicionar filtros
  if (!empty($search)) {
    $query .= " AND (u.name LIKE ? OR sp.specialty LIKE ? OR sp.location LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
  }

  if (!empty($specialty)) {
    $query .= " AND sp.specialty LIKE ?";
    $params[] = "%{$specialty}%";
  }

  if (!empty($location)) {
    $query .= " AND sp.location LIKE ?";
    $params[] = "%{$location}%";
  }

  // Ordenar tratando NULLs corretamente
  $query .= " ORDER BY COALESCE(avaliacao_media, 0) DESC, total_avaliacoes DESC, u.name ASC";

  // Usar PDO para a consulta preparada
  $stmt = $pdo->prepare($query);
  $stmt->execute($params);
  $prestadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Formatar dados
  foreach ($prestadores as &$prestador) {
    $prestador['avaliacao_media'] = $prestador['avaliacao_media'] !== null ?
      round((float)$prestador['avaliacao_media'], 1) : 0;
    $prestador['total_avaliacoes'] = (int)$prestador['total_avaliacoes'];
    $prestador['servicos_concluidos'] = (int)$prestador['servicos_concluidos'];
  }

  echo json_encode([
    'success' => true,
    'prestadores' => $prestadores,
    'total' => count($prestadores),
    'filtros' => [
      'search' => $search,
      'specialty' => $specialty,
      'location' => $location
    ]
  ]);
} catch (PDOException $e) {
  error_log('Erro PDO ao buscar prestadores: ' . $e->getMessage());
  echo json_encode([
    'success' => false,
    'message' => 'Erro ao buscar prestadores: ' . $e->getMessage(),
    'debug' => $e->getTraceAsString()
  ]);
} catch (Exception $e) {
  error_log('Erro ao buscar prestadores: ' . $e->getMessage());
  echo json_encode([
    'success' => false,
    'message' => 'Erro ao buscar prestadores: ' . $e->getMessage()
  ]);
}

