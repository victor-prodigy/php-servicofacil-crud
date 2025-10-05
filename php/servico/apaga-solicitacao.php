<?php
session_start();
require_once __DIR__ . '/../conexao.php';

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Acesso negado. Faça login como cliente.'
    ]);
    exit;
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido'
    ]);
    exit;
}

// Verificar se o ID da solicitação foi fornecido
if (!isset($_POST['solicitacao_id']) || empty($_POST['solicitacao_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID da solicitação é obrigatório'
    ]);
    exit;
}

$solicitacao_id = (int)$_POST['solicitacao_id'];

try {
    // Verificar se a solicitação existe e pertence ao cliente logado
    $sql = "SELECT request_id, titulo FROM service_request WHERE request_id = ? AND cliente_id = ?";
    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        throw new Exception('Erro na preparação da consulta de verificação: ' . $conexao->error);
    }

    $stmt->bind_param('ii', $solicitacao_id, $_SESSION['usuario_id']);

    if (!$stmt->execute()) {
        throw new Exception('Erro na execução da consulta de verificação: ' . $stmt->error);
    }

    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        $stmt->close();
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Solicitação não encontrada ou você não tem permissão para excluí-la'
        ]);
        exit;
    }

    $solicitacao = $resultado->fetch_assoc();
    $stmt->close();

    // Excluir a solicitação
    $sql = "DELETE FROM service_request WHERE request_id = ? AND cliente_id = ?";
    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        throw new Exception('Erro na preparação da consulta de exclusão: ' . $conexao->error);
    }

    $stmt->bind_param('ii', $solicitacao_id, $_SESSION['usuario_id']);

    if (!$stmt->execute()) {
        throw new Exception('Erro na execução da consulta de exclusão: ' . $stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception('Nenhum registro foi excluído');
    }

    $stmt->close();

    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'message' => "Solicitação '{$solicitacao['titulo']}' excluída com sucesso!",
        'solicitacao_id' => $solicitacao_id
    ]);
} catch (Exception $e) {
    // Log do erro (em produção, use um sistema de log adequado)
    error_log('Erro ao excluir solicitação: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor ao excluir solicitação',
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($conexao)) {
        $conexao->close();
    }
}
