<?php
session_start();
header('Content-Type: application/json');

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['admin_id']) || $_SESSION['usuario_tipo'] !== 'administrador') {
    http_response_code(401);
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

require_once '../conexao.php';

try {
    $user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);

    if (!$user_id) {
        echo json_encode(['error' => 'ID de usuário inválido']);
        exit;
    }

    // Buscar informações detalhadas do usuário
    $sql = "SELECT 
                u.user_id,
                u.email,
                u.name,
                u.phone_number,
                u.instagram,
                u.status,
                u.identity_verified,
                u.created_at,
                u.updated_at,
                CASE 
                    WHEN c.id IS NOT NULL THEN 'cliente'
                    WHEN sp.service_provider_id IS NOT NULL THEN 'prestador'
                    ELSE 'indefinido'
                END as tipo_usuario,
                c.id as cliente_id,
                sp.service_provider_id,
                sp.specialty,
                sp.location as prestador_location
            FROM user u
            LEFT JOIN cliente c ON u.user_id = c.user_id
            LEFT JOIN service_provider sp ON u.user_id = sp.user_id
            WHERE u.user_id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo json_encode(['error' => 'Usuário não encontrado']);
        exit;
    }

    // Buscar estatísticas específicas do usuário
    $stats = [];

    if ($usuario['tipo_usuario'] === 'cliente') {
        // Estatísticas para clientes
        $stats_sql = "SELECT 
                        COUNT(sr.request_id) as total_solicitacoes,
                        COUNT(CASE WHEN sr.status = 'Pendente' THEN 1 END) as solicitacoes_pendentes,
                        COUNT(CASE WHEN sr.status = 'Concluído' THEN 1 END) as solicitacoes_concluidas,
                        (SELECT COUNT(*) FROM proposal p 
                         JOIN service_request sr2 ON p.request_id = sr2.request_id 
                         WHERE sr2.cliente_id = ?) as propostas_recebidas
                      FROM service_request sr 
                      WHERE sr.cliente_id = ?";

        $stats_stmt = $pdo->prepare($stats_sql);
        $stats_stmt->execute([$usuario['cliente_id'], $usuario['cliente_id']]);
        $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

        // Buscar últimas solicitações
        $solicitacoes_sql = "SELECT titulo, categoria, status, created_at 
                            FROM service_request 
                            WHERE cliente_id = ? 
                            ORDER BY created_at DESC 
                            LIMIT 5";
        $solicitacoes_stmt = $pdo->prepare($solicitacoes_sql);
        $solicitacoes_stmt->execute([$usuario['cliente_id']]);
        $stats['ultimas_solicitacoes'] = $solicitacoes_stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($usuario['tipo_usuario'] === 'prestador') {
        // Estatísticas para prestadores
        $stats_sql = "SELECT 
                        COUNT(p.proposal_id) as total_propostas,
                        COUNT(CASE WHEN sr.status = 'Pendente' THEN 1 END) as propostas_pendentes,
                        COUNT(CASE WHEN sr.status = 'Concluído' THEN 1 END) as propostas_aceitas,
                        AVG(p.amount) as valor_medio_propostas
                      FROM proposal p 
                      JOIN service_request sr ON p.request_id = sr.request_id
                      WHERE p.service_provider_id = ?";

        $stats_stmt = $pdo->prepare($stats_sql);
        $stats_stmt->execute([$usuario['service_provider_id']]);
        $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

        // Buscar últimas propostas
        $propostas_sql = "SELECT sr.titulo, p.amount, p.submitted_at, sr.status
                         FROM proposal p 
                         JOIN service_request sr ON p.request_id = sr.request_id
                         WHERE p.service_provider_id = ? 
                         ORDER BY p.submitted_at DESC 
                         LIMIT 5";
        $propostas_stmt = $pdo->prepare($propostas_sql);
        $propostas_stmt->execute([$usuario['service_provider_id']]);
        $stats['ultimas_propostas'] = $propostas_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Formatar resposta
    $resposta = [
        'success' => true,
        'usuario' => [
            'user_id' => $usuario['user_id'],
            'nome' => $usuario['name'],
            'email' => $usuario['email'],
            'telefone' => $usuario['phone_number'] ?: 'Não informado',
            'instagram' => $usuario['instagram'] ?: 'Não informado',
            'tipo_usuario' => $usuario['tipo_usuario'],
            'verificado' => $usuario['identity_verified'] ? true : false,
            'status' => $usuario['status'] ?: 'ativo',
            'data_cadastro' => date('d/m/Y H:i', strtotime($usuario['created_at'])),
            'ultima_atualizacao' => date('d/m/Y H:i', strtotime($usuario['updated_at'])),
            'especialidade' => $usuario['specialty'],
            'localizacao' => $usuario['prestador_location']
        ],
        'estatisticas' => $stats
    ];

    echo json_encode($resposta);
} catch (PDOException $e) {
    error_log("Erro ao buscar detalhes do usuário: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
