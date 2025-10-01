<?php
session_start();
require_once '../../conexao.php';

// Verificar se o usuário está logado e é um cliente
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'cliente') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $servico_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    $cliente_id = $_SESSION['user_id'];

    try {
        // Buscar o serviço específico do cliente
        $query = "SELECT id, titulo, descricao, categoria, orcamento, prazo, localizacao, status, data_postagem 
                  FROM servicos 
                  WHERE id = ? AND cliente_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $servico_id, $cliente_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Serviço não encontrado ou não pertence a este cliente'
            ]);
            exit;
        }

        $servico = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'servico' => $servico
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao buscar detalhes do serviço: ' . $e->getMessage()
        ]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}
?>