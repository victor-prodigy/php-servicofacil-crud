<?php
session_start();
header('Content-Type: application/json');

// Verificar se o usuário está logado e é prestador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'prestador') {
    http_response_code(401);
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

require_once '../conexao.php';

try {
    $prestador_id = $_SESSION['prestador_id'];
    
    // Query para buscar propostas enviadas pelo prestador
    $sql = "SELECT 
                p.proposal_id,
                p.amount,
                p.estimate,
                p.message,
                p.submitted_at,
                sr.titulo as solicitacao_titulo,
                sr.categoria,
                sr.cidade,
                sr.orcamento_maximo,
                sr.status as solicitacao_status,
                u.name as cliente_nome,
                u.email as cliente_email
            FROM proposal p
            INNER JOIN service_request sr ON p.request_id = sr.request_id
            INNER JOIN cliente c ON sr.cliente_id = c.id
            INNER JOIN user u ON c.user_id = u.user_id
            WHERE p.service_provider_id = ?
            ORDER BY p.submitted_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$prestador_id]);
    $propostas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatar os dados para o frontend
    $propostas_formatadas = [];
    foreach ($propostas as $proposta) {
        $propostas_formatadas[] = [
            'proposal_id' => $proposta['proposal_id'],
            'solicitacao_titulo' => $proposta['solicitacao_titulo'],
            'categoria' => $proposta['categoria'],
            'cliente_nome' => $proposta['cliente_nome'],
            'cliente_email' => $proposta['cliente_email'],
            'valor_proposto' => number_format($proposta['amount'], 2, ',', '.'),
            'valor_proposto_raw' => $proposta['amount'],
            'prazo_estimado' => $proposta['estimate'] ?: 'Não informado',
            'mensagem' => $proposta['message'] ?: 'Sem mensagem',
            'cidade' => $proposta['cidade'],
            'orcamento_maximo' => $proposta['orcamento_maximo'] ? 
                'R$ ' . number_format($proposta['orcamento_maximo'], 2, ',', '.') : 'Não informado',
            'status_solicitacao' => $proposta['solicitacao_status'],
            'data_proposta' => date('d/m/Y H:i', strtotime($proposta['submitted_at'])),
            'data_proposta_raw' => $proposta['submitted_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'propostas' => $propostas_formatadas,
        'total' => count($propostas_formatadas)
    ]);

} catch (PDOException $e) {
    error_log("Erro ao buscar propostas: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?>