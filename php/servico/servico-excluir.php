<?php
session_start();
require_once '../../conexao.php';

// Verificar se o usuário está logado e é um cliente
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'cliente') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $servico_id = filter_input(INPUT_POST, 'servico_id', FILTER_SANITIZE_NUMBER_INT);
    $cliente_id = $_SESSION['user_id'];

    try {
        // Primeiro, verificar se o serviço pertence ao cliente
        $query = "SELECT cliente_id FROM servicos WHERE id = ? AND cliente_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $servico_id, $cliente_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Serviço não encontrado ou não pertence a este cliente']);
            exit;
        }

        // Se chegou aqui, pode excluir o serviço
        $query = "DELETE FROM servicos WHERE id = ? AND cliente_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $servico_id, $cliente_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Serviço excluído com sucesso']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir serviço']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}
?>
