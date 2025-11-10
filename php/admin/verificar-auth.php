<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

function retornarResposta($authenticated, $dados = []) {
    echo json_encode([
        'authenticated' => $authenticated,
        'usuario' => $dados
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar se há sessão ativa de administrador
if (!isset($_SESSION['admin_id']) || 
    !isset($_SESSION['usuario_tipo']) || 
    $_SESSION['usuario_tipo'] !== 'administrador') {
    
    // Limpar sessão inválida
    session_destroy();
    
    retornarResposta(false, [
        'message' => 'Acesso negado. Você não tem permissões de administrador.'
    ]);
}

try {
    require_once __DIR__ . '/../conexao.php';
    
    // Verificar se o usuário ainda existe e está ativo no banco
    $stmt = $pdo->prepare("
        SELECT user_id, email, name, user_type, status 
        FROM user 
        WHERE user_id = ? AND user_type = 'administrador' AND status = 'ativo'
    ");
    
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        // Usuário não existe mais ou foi desativado
        session_destroy();
        
        retornarResposta(false, [
            'message' => 'Conta de administrador não encontrada ou desativada.'
        ]);
    }
    
    // Atualizar dados da sessão se necessário
    $_SESSION['admin_name'] = $admin['name'];
    $_SESSION['admin_email'] = $admin['email'];
    
    // Retornar sucesso com dados do usuário
    retornarResposta(true, [
        'nome' => $admin['name'],
        'email' => $admin['email'],
        'user_id' => $admin['user_id'],
        'message' => 'Acesso autorizado'
    ]);
    
} catch (Exception $e) {
    error_log("Erro na verificação de autenticação admin: " . $e->getMessage());
    
    retornarResposta(false, [
        'message' => 'Erro interno do servidor. Tente novamente.'
    ]);
}
?>

