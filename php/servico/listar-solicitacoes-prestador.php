<?php
session_start();
require_once __DIR__ . '/../conexao.php';

// Verificar se usuário está logado como prestador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'prestador') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Acesso negado. Faça login como prestador.'
    ]);
    exit;
}

try {
    // PRESTADORES: Visualizam todas as solicitações de clientes (somente visualização)
    $sql = "SELECT 
                request_id as id,
                titulo,
                categoria,
                descricao,
                endereco,
                cidade,
                prazo_desejado,
                orcamento_maximo,
                observacoes,
                status,
                created_at as data_criacao,
                data_atualizacao
            FROM service_request 
            WHERE status = 'pendente'
            ORDER BY created_at DESC";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Erro na consulta: " . $conn->error);
    }

    $solicitacoes = [];
    while ($row = $result->fetch_assoc()) {
        $solicitacoes[] = $row;
    }

    echo json_encode([
        'success' => true,
        'solicitacoes' => $solicitacoes,
        'total' => count($solicitacoes),
        'tipo' => 'visualizacao_prestador'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar solicitações: ' . $e->getMessage()
    ]);
}

$conn->close();
