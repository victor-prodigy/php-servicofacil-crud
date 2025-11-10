<?php
/**
 * 📊 VERIFICAR STATUS DO PAGAMENTO
 * Retorna o status atual de um pagamento
 */

session_start();
require_once __DIR__ . '/../conexao.php';

header('Content-Type: application/json; charset=utf-8');

// Verificar se o usuário está logado como cliente
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    echo json_encode([
        'success' => false,
        'message' => 'Acesso não autorizado. Faça login como cliente.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $paymentId = filter_input(INPUT_POST, 'payment_id', FILTER_VALIDATE_INT);
        $clienteId = $_SESSION['cliente_id'] ?? null;

        if (!$paymentId) {
            echo json_encode([
                'success' => false,
                'message' => 'ID do pagamento não fornecido.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!$clienteId) {
            echo json_encode([
                'success' => false,
                'message' => 'ID do cliente não encontrado na sessão.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Verificar se o pagamento existe e pertence a um contrato do cliente
        $query = "SELECT p.status, p.amount, p.payment_date, p.created_at
                 FROM payment p
                 INNER JOIN contract c ON p.contract_id = c.contract_id
                 WHERE p.payment_id = ? AND c.cliente_id = ?";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$paymentId, $clienteId]);
        $pagamento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pagamento) {
            echo json_encode([
                'success' => false,
                'message' => 'Pagamento não encontrado.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode([
            'success' => true,
            'status' => $pagamento['status'],
            'amount' => (float)$pagamento['amount'],
            'payment_date' => $pagamento['payment_date'],
            'created_at' => $pagamento['created_at']
        ], JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) {
        error_log("Erro em status.php: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao consultar status: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido. Use POST.'
    ], JSON_UNESCAPED_UNICODE);
}

