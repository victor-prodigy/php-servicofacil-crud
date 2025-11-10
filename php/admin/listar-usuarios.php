<?php
session_start();
header('Content-Type: application/json');

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['admin_id']) || $_SESSION['usuario_tipo'] !== 'administrador') {
    http_response_code(401);
    echo json_encode(['error' => 'Acesso negado. Apenas administradores podem acessar esta funcionalidade.']);
    exit;
}

require_once '../conexao.php';

try {
    // Query para buscar todos os usuários com informações detalhadas
    $sql = "SELECT 
                u.user_id,
                u.email,
                u.name,
                u.phone_number,
                u.status,
                u.identity_verified,
                u.created_at,
                u.updated_at,
                CASE 
                    WHEN c.id IS NOT NULL THEN 'cliente'
                    WHEN sp.service_provider_id IS NOT NULL THEN 'prestador'
                    ELSE 'indefinido'
                END as tipo_usuario,
                CASE 
                    WHEN c.id IS NOT NULL THEN c.id
                    WHEN sp.service_provider_id IS NOT NULL THEN sp.service_provider_id
                    ELSE NULL
                END as tipo_id,
                c.instagram,
                sp.specialty,
                sp.location as prestador_location,
                -- Contagem de atividades
                (SELECT COUNT(*) FROM service_request sr WHERE sr.cliente_id = c.id) as total_solicitacoes,
                (SELECT COUNT(*) FROM proposal p WHERE p.service_provider_id = sp.service_provider_id) as total_propostas
            FROM user u
            LEFT JOIN cliente c ON u.user_id = c.user_id
            LEFT JOIN service_provider sp ON u.user_id = sp.user_id
            WHERE u.user_type IN ('cliente', 'prestador')
            ORDER BY u.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatar os dados para o frontend
    $usuarios_formatados = [];
    foreach ($usuarios as $usuario) {
        $usuarios_formatados[] = [
            'user_id' => $usuario['user_id'],
            'nome' => $usuario['name'],
            'email' => $usuario['email'],
            'telefone' => $usuario['phone_number'] ?: 'Não informado',
            'instagram' => $usuario['instagram'] ?: 'Não informado',
            'tipo_usuario' => $usuario['tipo_usuario'],
            'tipo_id' => $usuario['tipo_id'],
            'verificado' => $usuario['identity_verified'] ? true : false,
            'status' => $usuario['status'] ?: 'ativo',
            'data_cadastro' => date('d/m/Y H:i', strtotime($usuario['created_at'])),
            'ultima_atualizacao' => date('d/m/Y H:i', strtotime($usuario['updated_at'])),
            // Dados específicos do prestador
            'especialidade' => $usuario['specialty'] ?: null,
            'localizacao' => $usuario['prestador_location'] ?: null,
            // Estatísticas
            'total_solicitacoes' => (int)$usuario['total_solicitacoes'],
            'total_propostas' => (int)$usuario['total_propostas'],
            // Dados raw para filtros
            'created_at_raw' => $usuario['created_at'],
            'updated_at_raw' => $usuario['updated_at']
        ];
    }
    
    // Calcular estatísticas gerais
    $stats = [
        'total_usuarios' => count($usuarios_formatados),
        'total_clientes' => count(array_filter($usuarios_formatados, fn($u) => $u['tipo_usuario'] === 'cliente')),
        'total_prestadores' => count(array_filter($usuarios_formatados, fn($u) => $u['tipo_usuario'] === 'prestador')),
        'usuarios_ativos' => count(array_filter($usuarios_formatados, fn($u) => $u['status'] === 'ativo')),
        'usuarios_inativos' => count(array_filter($usuarios_formatados, fn($u) => $u['status'] === 'inativo'))
    ];
    
    echo json_encode([
        'success' => true,
        'usuarios' => $usuarios_formatados,
        'estatisticas' => $stats
    ]);

} catch (PDOException $e) {
    error_log("Erro ao buscar usuários: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?>

