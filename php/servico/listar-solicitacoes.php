<?php

/**
 * 📋 LISTAR SOLICITAÇÕES DE SERVIÇO
 * Lista todas as solicitações do cliente logado
 */

session_start();
require_once __DIR__ . '/../conexao.php';

<<<<<<< HEAD
// 📤 Função para enviar resposta JSON
function enviarResposta($success, $message, $data = [])
{
    header('Content-Type: application/json; charset=utf-8');
    $resposta = [
        'success' => $success,
        'message' => $message
    ];

    if (!empty($data)) {
        $resposta = array_merge($resposta, $data);
    }

    echo json_encode($resposta, JSON_UNESCAPED_UNICODE);
=======
// Verifica se o usuário está logado e é cliente
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Acesso negado. Faça login como cliente.'
    ]);
>>>>>>> 4b5a058c67068a9290b4ab02665f7f078599019b
    exit;
}

try {
<<<<<<< HEAD
    // 🔐 Verificar se usuário está logado como cliente
    if (!isset($_SESSION['cliente_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
        enviarResposta(false, 'Acesso negado. Faça login como cliente.');
    }

    // 📋 Buscar solicitações do cliente logado
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
            created_at as data_criacao
          FROM service_request 
          WHERE cliente_id = ? 
          ORDER BY created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['cliente_id']]);

    $solicitacoes = [];

    while ($row = $stmt->fetch()) {
=======
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
>>>>>>> 4b5a058c67068a9290b4ab02665f7f078599019b
        $solicitacoes[] = [
            'id' => (int)$row['id'],
            'titulo' => $row['titulo'],
            'cidade' => $row['cidade'],
            'prazo_desejado' => $row['prazo_desejado'],
<<<<<<< HEAD
            'orcamento_maximo' => $row['orcamento_maximo'] ? (float)$row['orcamento_maximo'] : null,
            'observacoes' => $row['observacoes'],
            'status' => $row['status'],
=======
            'orcamento_maximo' => $row['orcamento_maximo'] !== null ? (float)$row['orcamento_maximo'] : null,
            'status' => 'pendente', // você pode ajustar conforme lógica futura
>>>>>>> 4b5a058c67068a9290b4ab02665f7f078599019b
            'data_criacao' => $row['data_criacao']
        ];
    }

<<<<<<< HEAD
    // ✨ Retornar sucesso
    enviarResposta(true, 'Solicitações carregadas com sucesso.', [
        'solicitacoes' => $solicitacoes,
        'total' => count($solicitacoes)
    ]);
} catch (Exception $e) {
    error_log("Erro em listar-solicitacoes.php: " . $e->getMessage());
    enviarResposta(false, 'Erro interno do servidor. Tente novamente.');
}
=======
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
>>>>>>> 4b5a058c67068a9290b4ab02665f7f078599019b
