<?php
// 🔧 PRESTADOR - ENVIAR MENSAGEM NO CHAT
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

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $prestador_id = $_SESSION['prestador_id'];
    
    // Validar dados recebidos
    $cliente_id = filter_input(INPUT_POST, 'cliente_id', FILTER_VALIDATE_INT);
    $contract_id = filter_input(INPUT_POST, 'contract_id', FILTER_VALIDATE_INT);
    $message = trim($_POST['message'] ?? '');
    
    if (!$cliente_id || !$contract_id) {
        echo json_encode([
            'success' => false,
            'message' => 'ID do cliente e contrato são obrigatórios'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (empty($message)) {
        echo json_encode([
            'success' => false,
            'message' => 'A mensagem não pode estar vazia'
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
    
    // Inserir mensagem no chat
    // Verificar se o campo sender_type existe
    $check_column = $pdo->query("SHOW COLUMNS FROM chat LIKE 'sender_type'")->fetch();
    
    if ($check_column) {
        $sql = "
            INSERT INTO chat (cliente_id, service_provider_id, sender_type, message, sent_at) 
            VALUES (?, ?, 'prestador', ?, NOW())
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$cliente_id, $prestador_id, $message]);
    } else {
        // Fallback se o campo não existir
        $sql = "
            INSERT INTO chat (cliente_id, service_provider_id, message, sent_at) 
            VALUES (?, ?, ?, NOW())
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$cliente_id, $prestador_id, $message]);
    }
    
    $chat_id = $pdo->lastInsertId();
    
    // Buscar dados da mensagem enviada
    $msg_sql = "
        SELECT 
            chat_id,
            message,
            sent_at
        FROM chat
        WHERE chat_id = ?
    ";
    
    $msg_stmt = $pdo->prepare($msg_sql);
    $msg_stmt->execute([$chat_id]);
    $mensagem = $msg_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Criar notificação para o cliente
    $notif_sql = "
        SELECT user_id 
        FROM cliente 
        WHERE id = ?
    ";
    
    $notif_stmt = $pdo->prepare($notif_sql);
    $notif_stmt->execute([$cliente_id]);
    $cliente_user = $notif_stmt->fetch();
    
    if ($cliente_user) {
        $notif_insert = "
            INSERT INTO notificacoes (usuario_id, tipo, mensagem, data_criacao, lida) 
            VALUES (?, 'sistema', ?, NOW(), FALSE)
        ";
        
        $notif_msg = "Você recebeu uma nova mensagem do prestador de serviços.";
        $notif_stmt = $pdo->prepare($notif_insert);
        $notif_stmt->execute([$cliente_user['user_id'], $notif_msg]);
    }
    
    $data_envio = new DateTime($mensagem['sent_at']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Mensagem enviada com sucesso',
        'mensagem' => [
            'chat_id' => $mensagem['chat_id'],
            'message' => $mensagem['message'],
            'sent_at' => $data_envio->format('d/m/Y H:i:s')
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    error_log("Erro ao enviar mensagem: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor ao enviar mensagem'
    ], JSON_UNESCAPED_UNICODE);
}
?>

