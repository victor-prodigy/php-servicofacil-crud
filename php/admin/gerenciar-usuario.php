<?php
session_start();
header('Content-Type: application/json');

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['admin_id']) || $_SESSION['usuario_tipo'] !== 'administrador') {
    http_response_code(401);
    echo json_encode(['error' => 'Acesso negado. Apenas administradores podem acessar esta funcionalidade.']);
    exit;
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

require_once '../conexao.php';

try {
    // Validar dados recebidos
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $acao = trim($_POST['acao'] ?? '');
    
    if (!$user_id || !in_array($acao, ['ativar', 'desativar', 'excluir'])) {
        echo json_encode(['error' => 'Dados inválidos. Verifique o ID do usuário e a ação.']);
        exit;
    }
    
    // Verificar se o usuário existe
    $check_sql = "SELECT user_id, name, email FROM user WHERE user_id = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$user_id]);
    $usuario = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        echo json_encode(['error' => 'Usuário não encontrado']);
        exit;
    }
    
    $response = [];
    
    switch ($acao) {
        case 'ativar':
            $sql = "UPDATE user SET status = 'ativo', identity_verified = 1, updated_at = NOW() WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id]);
            
            $response = [
                'success' => true,
                'message' => "Usuário {$usuario['name']} foi ativado com sucesso",
                'acao' => 'ativado'
            ];
            break;
            
        case 'desativar':
            $sql = "UPDATE user SET status = 'inativo', identity_verified = 0, updated_at = NOW() WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id]);
            
            $response = [
                'success' => true,
                'message' => "Usuário {$usuario['name']} foi desativado",
                'acao' => 'desativado'
            ];
            break;
            
        case 'excluir':
            // Verificar se o usuário tem atividades antes de excluir
            $atividades_sql = "SELECT 
                                (SELECT COUNT(*) FROM service_request sr 
                                 JOIN cliente c ON sr.cliente_id = c.id 
                                 WHERE c.user_id = ?) as solicitacoes,
                                (SELECT COUNT(*) FROM proposal p 
                                 JOIN service_provider sp ON p.service_provider_id = sp.service_provider_id 
                                 WHERE sp.user_id = ?) as propostas";
            
            $atividades_stmt = $pdo->prepare($atividades_sql);
            $atividades_stmt->execute([$user_id, $user_id]);
            $atividades = $atividades_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($atividades['solicitacoes'] > 0 || $atividades['propostas'] > 0) {
                echo json_encode([
                    'error' => "Não é possível excluir o usuário {$usuario['name']} pois possui atividades na plataforma (solicitações: {$atividades['solicitacoes']}, propostas: {$atividades['propostas']}). Considere desativar em vez de excluir."
                ]);
                exit;
            }
            
            // Excluir usuário (as constraints CASCADE irão cuidar das tabelas relacionadas)
            $sql = "DELETE FROM user WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id]);
            
            $response = [
                'success' => true,
                'message' => "Usuário {$usuario['name']} foi excluído permanentemente",
                'acao' => 'excluido'
            ];
            break;
    }
    
    // Log da ação administrativa (opcional - pode falhar se a tabela não existir)
    try {
        $log_sql = "INSERT INTO admin_log (admin_id, acao, detalhes, target_user_id, created_at) 
                    VALUES (?, ?, ?, ?, NOW())";
        $log_stmt = $pdo->prepare($log_sql);
        $log_detalhes = "Usuário {$usuario['name']} ({$usuario['email']}) foi {$acao}";
        $log_stmt->execute([$_SESSION['admin_id'], $acao, $log_detalhes, $user_id]);
    } catch (PDOException $e) {
        // Log falhou, mas não interrompe a operação principal
        error_log("Erro ao registrar log administrativo: " . $e->getMessage());
    }
    
    echo json_encode($response);

} catch (PDOException $e) {
    error_log("Erro ao gerenciar usuário: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor ao processar a ação']);
}
?>

