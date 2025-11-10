<?php
// 🔧 PRESTADOR - OBTER DADOS DE CONTATO DO CLIENTE
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
    $cliente_id = filter_input(INPUT_GET, 'cliente_id', FILTER_VALIDATE_INT);
    $contract_id = filter_input(INPUT_GET, 'contract_id', FILTER_VALIDATE_INT);
    
    if (!$cliente_id || !$contract_id) {
        echo json_encode([
            'success' => false,
            'message' => 'ID do cliente e contrato são obrigatórios'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Verificar se o prestador tem acesso a este contrato
    $check_sql = "
        SELECT contract_id 
        FROM contract 
        WHERE contract_id = ? 
        AND service_provider_id = ? 
        AND cliente_id = ?
        AND status = 'active'
    ";
    
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$contract_id, $prestador_id, $cliente_id]);
    $contrato = $check_stmt->fetch();
    
    if (!$contrato) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Acesso negado a este contrato'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Buscar dados de contato do cliente
    $sql = "
        SELECT 
            u.name as cliente_nome,
            u.email as cliente_email,
            u.phone_number as cliente_telefone,
            cl.id as cliente_id
        FROM cliente cl
        INNER JOIN user u ON cl.user_id = u.user_id
        WHERE cl.id = ?
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cliente) {
        echo json_encode([
            'success' => false,
            'message' => 'Cliente não encontrado'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'cliente' => [
            'nome' => $cliente['cliente_nome'],
            'email' => $cliente['cliente_email'],
            'telefone' => $cliente['cliente_telefone'] ?? 'Não informado',
            'cliente_id' => $cliente['cliente_id']
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    error_log("Erro ao obter dados de contato: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor'
    ], JSON_UNESCAPED_UNICODE);
}
?>

