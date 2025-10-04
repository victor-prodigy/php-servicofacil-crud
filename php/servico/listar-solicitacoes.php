<?php
session_start();
require_once '../conexao.php';

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'message' => 'Acesso negado. Faça login como cliente.'
    ]);
    exit;
}

try {
    // Preparar consulta para buscar solicitações do cliente logado
    $sql = "SELECT 
                id,
                titulo,
                categoria,
                descricao,
                endereco,
                cidade,
                prazo_desejado,
                orcamento_maximo,
                observacoes,
                status,
                data_criacao,
                data_atualizacao
            FROM solicitacoes_servico 
            WHERE cliente_id = ? 
            ORDER BY data_criacao DESC";
    
    $stmt = $conexao->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Erro na preparação da consulta: ' . $conexao->error);
    }
    
    $stmt->bind_param('i', $_SESSION['usuario_id']);
    
    if (!$stmt->execute()) {
        throw new Exception('Erro na execução da consulta: ' . $stmt->error);
    }
    
    $resultado = $stmt->get_result();
    $solicitacoes = [];
    
    while ($row = $resultado->fetch_assoc()) {
        $solicitacoes[] = [
            'id' => (int)$row['id'],
            'titulo' => $row['titulo'],
            'categoria' => $row['categoria'],
            'descricao' => $row['descricao'],
            'endereco' => $row['endereco'],
            'cidade' => $row['cidade'],
            'prazo_desejado' => $row['prazo_desejado'],
            'orcamento_maximo' => $row['orcamento_maximo'] ? (float)$row['orcamento_maximo'] : null,
            'observacoes' => $row['observacoes'],
            'status' => $row['status'],
            'data_criacao' => $row['data_criacao'],
            'data_atualizacao' => $row['data_atualizacao']
        ];
    }
    
    $stmt->close();
    
    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'solicitacoes' => $solicitacoes,
        'total' => count($solicitacoes)
    ]);
    
} catch (Exception $e) {
    // Log do erro (em produção, use um sistema de log adequado)
    error_log('Erro ao listar solicitações: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor',
        'error' => $e->getMessage()
    ]);
    
} finally {
    if (isset($conexao)) {
        $conexao->close();
    }
}
?>