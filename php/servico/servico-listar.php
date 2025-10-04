<?php
session_start();
require_once '../../conexao.php';

// Verificar se o usuário está logado e é um cliente
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'cliente') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

try {
    $cliente_id = $_SESSION['user_id'];
    
    // Buscar todos os serviços do cliente
    $query = "SELECT id, titulo, descricao, categoria, orcamento, prazo, localizacao, status, data_postagem 
              FROM servicos 
              WHERE cliente_id = ? 
              ORDER BY data_postagem DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $servicos = [];
    while ($row = $result->fetch_assoc()) {
        $servicos[] = $row;
    }

    echo json_encode([
        'success' => true,
        'servicos' => $servicos
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar serviços: ' . $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();
?>