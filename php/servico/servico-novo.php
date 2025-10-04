<?php
session_start();
require_once '../conexao.php';

// Verificar se o usuário está logado e é um cliente
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'cliente') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obter dados do formulário
    $cliente_id = $_SESSION['user_id'];
    $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_STRING);
    $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_STRING);
    $categoria = filter_input(INPUT_POST, 'categoria', FILTER_SANITIZE_STRING);
    $orcamento = filter_input(INPUT_POST, 'orcamento', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $prazo = filter_input(INPUT_POST, 'prazo', FILTER_SANITIZE_STRING);
    $localizacao = filter_input(INPUT_POST, 'localizacao', FILTER_SANITIZE_STRING);
    $status = 'aberto'; // Status inicial do serviço
    $data_postagem = date('Y-m-d H:i:s');

    try {
        // Preparar a query
        $query = "INSERT INTO servicos (cliente_id, titulo, descricao, categoria, orcamento, prazo, localizacao, status, data_postagem) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isssdsssss", 
            $cliente_id, 
            $titulo, 
            $descricao, 
            $categoria, 
            $orcamento, 
            $prazo, 
            $localizacao, 
            $status, 
            $data_postagem
        );

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Serviço criado com sucesso']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao criar serviço']);
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