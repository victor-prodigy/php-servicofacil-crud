<?php
session_start();
require_once __DIR__ . '/../conexao.php';

header('Content-Type: application/json');

// Verificar se o usuário está logado como cliente
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'cliente') {
  echo json_encode([
    'success' => false, 
    'message' => 'Acesso não autorizado. Faça login como cliente.',
    'servicos' => []
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  // Buscar todos os serviços disponíveis dos prestadores (apenas serviços ativos e disponíveis)
  $query = "SELECT 
              ps.service_id as id,
              ps.titulo,
              ps.descricao,
              ps.categoria,
              ps.preco as orcamento,
              ps.status,
              ps.disponibilidade,
              ps.created_at as data_postagem,
              u.name as prestador_nome,
              sp.specialty as prestador_especialidade
            FROM provider_service ps
            INNER JOIN service_provider sp ON ps.service_provider_id = sp.service_provider_id
            INNER JOIN user u ON sp.user_id = u.user_id
            WHERE ps.status = 'ativo' 
              AND ps.disponibilidade = 'disponivel'
            ORDER BY ps.created_at DESC";

  // Usar PDO para a consulta preparada
  $stmt = $pdo->prepare($query);
  $stmt->execute();
  $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Formatar dados
  foreach ($servicos as &$servico) {
    $servico['id'] = (int)$servico['id'];
    $servico['orcamento'] = $servico['orcamento'] !== null 
      ? (float)$servico['orcamento'] 
      : null;
  }

  echo json_encode([
    'success' => true,
    'servicos' => $servicos,
    'total' => count($servicos)
  ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'message' => 'Erro ao listar serviços: ' . $e->getMessage(),
    'servicos' => []
  ], JSON_UNESCAPED_UNICODE);
}

