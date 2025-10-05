<?php
session_start();
header('Content-Type: application/json');

// Verificar se o usuário está logado e é prestador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'prestador') {
    http_response_code(401);
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

require_once '../conexao.php';

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

try {
    $prestador_id = $_SESSION['prestador_id'];
    
    // Validar dados recebidos
    $request_id = filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT);
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $estimate = trim($_POST['estimate'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (!$request_id || !$amount || $amount <= 0) {
        echo json_encode(['error' => 'Dados inválidos. Verifique o ID da solicitação e o valor da proposta.']);
        exit;
    }
    
    if (empty($estimate)) {
        echo json_encode(['error' => 'Prazo estimado é obrigatório.']);
        exit;
    }
    
    if (empty($message)) {
        echo json_encode(['error' => 'Descrição do trabalho é obrigatória.']);
        exit;
    }
    
    // Verificar se a solicitação existe e está ativa
    $check_sql = "SELECT sr.request_id, sr.titulo, sr.status, c.id as cliente_id 
                  FROM service_request sr 
                  INNER JOIN cliente c ON sr.cliente_id = c.id 
                  WHERE sr.request_id = ? AND sr.status = 'pendente'";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$request_id]);
    $solicitacao = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$solicitacao) {
        echo json_encode(['error' => 'Solicitação não encontrada ou não está mais disponível']);
        exit;
    }
    
    // Verificar se o prestador já enviou uma proposta para esta solicitação
    $proposta_existente_sql = "SELECT proposal_id FROM proposal 
                               WHERE request_id = ? AND service_provider_id = ?";
    $proposta_stmt = $pdo->prepare($proposta_existente_sql);
    $proposta_stmt->execute([$request_id, $prestador_id]);
    
    if ($proposta_stmt->fetch()) {
        echo json_encode(['error' => 'Você já enviou uma proposta para esta solicitação']);
        exit;
    }
    
    // Inserir a nova proposta
    $sql = "INSERT INTO proposal (request_id, service_provider_id, amount, estimate, message, submitted_at) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$request_id, $prestador_id, $amount, $estimate, $message]);
    
    $proposal_id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Proposta enviada com sucesso!',
        'proposal_id' => $proposal_id,
        'solicitacao_titulo' => $solicitacao['titulo']
    ]);

} catch (PDOException $e) {
    error_log("Erro ao criar proposta: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor ao enviar proposta']);
}
?>