<?php
// 📋 LISTAR TODAS AS POSTAGENS DE PRESTADORES - Para Administrador
session_start();
require_once '../conexao.php';

header('Content-Type: application/json; charset=utf-8');

// 🔒 Verificar se o usuário está logado e é um administrador
if (!isset($_SESSION['admin_id']) || $_SESSION['usuario_tipo'] !== 'administrador') {
    echo json_encode([
        'success' => false,
        'message' => 'Acesso não autorizado',
        'postagens' => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    $disponibilidade = $_GET['disponibilidade'] ?? '';

    // 📋 Buscar todas as postagens de prestadores
    $sql = "SELECT 
                ps.service_id,
                ps.titulo,
                ps.descricao,
                ps.categoria,
                ps.preco,
                ps.disponibilidade,
                ps.status,
                ps.created_at,
                ps.updated_at,
                u.name as prestador_nome,
                u.email as prestador_email,
                sp.service_provider_id,
                sp.specialty as prestador_especialidade
            FROM provider_service ps
            INNER JOIN service_provider sp ON ps.service_provider_id = sp.service_provider_id
            INNER JOIN user u ON sp.user_id = u.user_id
            WHERE 1=1";

    $params = [];

    if (!empty($search)) {
        $sql .= " AND (ps.titulo LIKE ? OR ps.descricao LIKE ? OR u.name LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if (!empty($status)) {
        $sql .= " AND ps.status = ?";
        $params[] = $status;
    }

    if (!empty($disponibilidade)) {
        $sql .= " AND ps.disponibilidade = ?";
        $params[] = $disponibilidade;
    }

    $sql .= " ORDER BY ps.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $postagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✨ Formatar dados
    foreach ($postagens as &$postagem) {
        $postagem['created_at'] = date('d/m/Y H:i', strtotime($postagem['created_at']));
        if ($postagem['updated_at']) {
            $postagem['updated_at'] = date('d/m/Y H:i', strtotime($postagem['updated_at']));
        }
        $postagem['preco'] = number_format($postagem['preco'], 2, ',', '.');
    }

    echo json_encode([
        'success' => true,
        'postagens' => $postagens ? $postagens : []
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar postagens: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar postagens: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

