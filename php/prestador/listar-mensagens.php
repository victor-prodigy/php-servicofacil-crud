<?php
// 🔧 PRESTADOR - LISTAR MENSAGENS DO CHAT
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
    
    // Buscar mensagens do chat
    // Verificar se o campo sender_type existe
    $check_column = $pdo->query("SHOW COLUMNS FROM chat LIKE 'sender_type'")->fetch();
    
    if ($check_column) {
        $sql = "
            SELECT 
                chat_id,
                cliente_id,
                service_provider_id,
                sender_type,
                message,
                sent_at
            FROM chat
            WHERE cliente_id = ? 
            AND service_provider_id = ?
            ORDER BY sent_at ASC
        ";
    } else {
        $sql = "
            SELECT 
                chat_id,
                cliente_id,
                service_provider_id,
                message,
                sent_at
            FROM chat
            WHERE cliente_id = ? 
            AND service_provider_id = ?
            ORDER BY sent_at ASC
        ";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id, $prestador_id]);
    $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatar mensagens
    $mensagens_formatadas = [];
    foreach ($mensagens as $msg) {
        $data_envio = new DateTime($msg['sent_at']);
        
        // Determinar se a mensagem foi enviada pelo prestador
        if (isset($msg['sender_type'])) {
            $is_sent = ($msg['sender_type'] === 'prestador');
        } else {
            // Fallback: assumir que se service_provider_id corresponde, foi enviada pelo prestador
            $is_sent = ($msg['service_provider_id'] == $prestador_id);
        }
        
        $mensagens_formatadas[] = [
            'chat_id' => $msg['chat_id'],
            'message' => $msg['message'],
            'sent_at' => $data_envio->format('d/m/Y H:i:s'),
            'is_sent' => $is_sent
        ];
    }
    
    echo json_encode([
        'success' => true,
        'mensagens' => $mensagens_formatadas
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    error_log("Erro ao listar mensagens: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor'
    ], JSON_UNESCAPED_UNICODE);
}
?>

