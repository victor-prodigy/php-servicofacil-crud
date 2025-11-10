<?php
// 🔧 PRESTADOR - LISTAR SOLICITAÇÕES APROVADAS
session_start();
header('Content-Type: application/json; charset=utf-8');

// Verificar se o usuário está logado e é prestador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'prestador') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Acesso negado. Faça login como prestador.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once '../conexao.php';

try {
    $prestador_id = $_SESSION['prestador_id'];
    
    // Buscar solicitações aprovadas (com contrato ativo)
    $sql = "
        SELECT 
            c.contract_id,
            c.request_id,
            c.status as contract_status,
            c.created_at as contract_date,
            sr.titulo,
            sr.categoria,
            sr.descricao,
            sr.cidade,
            sr.endereco,
            u.name as cliente_nome,
            u.email as cliente_email,
            u.phone_number as cliente_telefone,
            u.user_id as cliente_user_id,
            cl.id as cliente_id
        FROM contract c
        INNER JOIN service_request sr ON c.request_id = sr.request_id
        INNER JOIN cliente cl ON c.cliente_id = cl.id
        INNER JOIN user u ON cl.user_id = u.user_id
        WHERE c.service_provider_id = ?
        AND c.status = 'active'
        ORDER BY c.created_at DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$prestador_id]);
    $contratos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatar dados
    $contratos_formatados = [];
    foreach ($contratos as $contrato) {
        $data_contrato = new DateTime($contrato['contract_date']);
        $contratos_formatados[] = [
            'contract_id' => $contrato['contract_id'],
            'request_id' => $contrato['request_id'],
            'titulo' => $contrato['titulo'],
            'categoria' => $contrato['categoria'],
            'descricao' => $contrato['descricao'],
            'cidade' => $contrato['cidade'],
            'endereco' => $contrato['endereco'],
            'cliente_nome' => $contrato['cliente_nome'],
            'cliente_email' => $contrato['cliente_email'],
            'cliente_telefone' => $contrato['cliente_telefone'],
            'cliente_id' => $contrato['cliente_id'],
            'cliente_user_id' => $contrato['cliente_user_id'],
            'contract_status' => $contrato['contract_status'],
            'contract_date' => $data_contrato->format('d/m/Y H:i')
        ];
    }
    
    echo json_encode([
        'success' => true,
        'contratos' => $contratos_formatados
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    error_log("Erro ao listar solicitações aprovadas: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor'
    ], JSON_UNESCAPED_UNICODE);
}
?>

