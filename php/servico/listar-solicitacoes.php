<?php
session_start();
require_once __DIR__ . '/../conexao.php';

// Verifica se o usuário está logado e é cliente
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Acesso negado. Faça login como cliente.'
    ]);
    exit;
}

try {
    // Consulta solicitações do cliente logado
    $sql = "SELECT 
                request_id AS id,
                service_type AS titulo,
                location AS cidade,
                deadline AS prazo_desejado,
                budget AS orcamento_maximo,
                created_at AS data_criacao,
                NULL AS status
            FROM service_request
            WHERE cliente_id = ?
            ORDER BY created_at DESC";

    $stmt = $conexao->prepare($sql);
    if (!$stmt) {
        throw new Exception("Erro na preparação da consulta: " . $conexao->error);
    }

    $stmt->bind_param('i', $_SESSION['usuario_id']);
    if (!$stmt->execute()) {
        throw new Exception("Erro na execução da consulta: " . $stmt->error);
    }

    $resultado = $stmt->get_result();
    $solicitacoes = [];

    while ($row = $resultado->fetch_assoc()) {
        $solicitacoes[] = [
            'id' => (int)$row['id'],
            'titulo' => $row['titulo'],
            'cidade' => $row['cidade'],
            'prazo_desejado' => $row['prazo_desejado'],
            'orcamento_maximo' => $row['orcamento_maximo'] !== null ? (float)$row['orcamento_maximo'] : null,
            'status' => 'pendente', // você pode ajustar conforme lógica futura
            'data_criacao' => $row['data_criacao']
        ];
    }

    $stmt->close();

    echo json_encode([
        'success' => true,
        'solicitacoes' => $solicitacoes,
        'total' => count($solicitacoes)
    ]);

} catch (Exception $e) {
    error_log("Erro ao listar solicitações: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor',
        'error' => $e->getMessage()
    ]);
} finally {
    $conexao->close();
}
?>
