<?php

/**
 * 📋 LISTAR SOLICITAÇÕES DE SERVIÇO
 * Lista todas as solicitações do cliente logado
 */

session_start();
require_once __DIR__ . '/../conexao.php';

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
    exit;
}

try {
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
            'data_criacao' => $row['data_criacao']
        ];
    }

    // ✨ Retornar sucesso
    enviarResposta(true, 'Solicitações carregadas com sucesso.', [
        'solicitacoes' => $solicitacoes,
        'total' => count($solicitacoes)
    ]);
} catch (Exception $e) {
    error_log("Erro em listar-solicitacoes.php: " . $e->getMessage());
    enviarResposta(false, 'Erro interno do servidor. Tente novamente.');
}
