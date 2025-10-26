<?php
session_start();
require_once '../../conexao.php';

// Verificar se o usuário está logado e é um cliente
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'cliente') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pagamento_id = filter_input(INPUT_POST, 'pagamento_id', FILTER_SANITIZE_NUMBER_INT);
        $cliente_id = $_SESSION['user_id'];

        if (!$pagamento_id) {
            throw new Exception('ID do pagamento não fornecido');
        }

        // Verificar se o pagamento existe e pertence ao cliente
        $query = "SELECT status FROM pagamentos WHERE id = ? AND cliente_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $pagamento_id, $cliente_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Pagamento não encontrado');
        }

        $pagamento = $result->fetch_assoc();

        echo json_encode([
            'success' => true,
            'status' => $pagamento['status']
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao consultar status: ' . $e->getMessage()
        ]);
    }

    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}
?>