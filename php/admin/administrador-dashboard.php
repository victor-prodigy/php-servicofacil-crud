/************* ✨ Windsurf Command 🌟 *************/
<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

function retornarErro($mensagem, $codigo = 400)
{
  http_response_code($codigo);
  echo json_encode([
    'success' => false,
    'message' => $mensagem
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

function retornarSucesso($dados = [])
{
  echo json_encode([
    'success' => true,
    'data' => $dados
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

// Verificar autenticação
if (!isset($_SESSION['admin_id']) || $_SESSION['usuario_tipo'] !== 'administrador') {
  retornarErro('Acesso negado. Faça login como administrador.', 401);
}

try {
  require_once __DIR__ . '/../conexao.php';

  $action = $_GET['action'] ?? '';

  switch ($action) {
    case 'stats':
      // Buscar estatísticas gerais
      $stats = [];

      // Total de usuários
      $stmt = $pdo->query("SELECT COUNT(*) as total FROM user WHERE user_type IN ('cliente', 'prestador')");
      $stats['total_users'] = $stmt->fetch()['total'];

      // Total de serviços ativos
      $stmt = $pdo->query("SELECT COUNT(*) as total FROM service_request WHERE status IN ('Pendente', 'Em Andamento')");
      $stats['total_services'] = $stmt->fetch()['total'];

      // Total de propostas pendentes
      $stmt = $pdo->query("SELECT COUNT(*) as total FROM proposal");
      $stats['total_proposals'] = $stmt->fetch()['total'];

      // Contratos ativos (simulado - pode ser adaptado)
      $stats['total_contracts'] = 0;

      // 1. novo campo Instagram
      $stmt = $pdo->query("SELECT instagram FROM setting");
      $stats['instagram'] = $stmt->fetch()['instagram'];

      retornarSucesso($stats);
      break;

    case 'services':
      // Listar todos os serviços com filtros
      $status = $_GET['status'] ?? '';
      $search = $_GET['search'] ?? '';

      $sql = "SELECT 
                        sr.request_id,
                        sr.titulo,
                        sr.categoria,
                        sr.descricao,
                        sr.endereco,
                        sr.cidade,
                        sr.orcamento_maximo,
                        sr.prazo_desejado,
                        sr.status,
                        sr.created_at,
                        u.name as cliente_nome,
                        u.email as cliente_email,
                        u.instagram as cliente_telefone
                        u.
                    FROM service_request sr
                    JOIN cliente c ON sr.cliente_id = c.id
                    JOIN user u ON c.user_id = u.user_id
                    WHERE 1=1";

      $params = [];

      if (!empty($status)) {
        $sql .= " AND sr.status = ?";
        $params[] = $status;
      }

      if (!empty($search)) {
        $sql .= " AND (sr.titulo LIKE ? OR sr.categoria LIKE ? OR u.name LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
      }

      $sql .= " ORDER BY sr.created_at DESC";

      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

      retornarSucesso($services);
      break;

    case 'admin_info':
      // Retornar informações do admin logado
      retornarSucesso([
        'name' => $_SESSION['admin_name'],
        'email' => $_SESSION['admin_email']
      ]);
      break;

    default:
      retornarErro('Ação não encontrada', 404);
  }
} catch (Exception $e) {
  error_log("Erro no dashboard admin: " . $e->getMessage());
  retornarErro('Erro interno do servidor', 500);
}


/*******  e8fd0f97-5311-49bf-9084-83021882528d  *******/
