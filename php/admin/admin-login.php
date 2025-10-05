<?php
session_start();
header('Content-Type: application/json');

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

require_once '../conexao.php';

try {
    // Validar dados recebidos
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($email) || empty($password)) {
        echo json_encode(['error' => 'Email e senha são obrigatórios']);
        exit;
    }
    
    // Por enquanto, vou usar credenciais fixas para o administrador
    // Em produção, isso deveria estar na base de dados com hash de senha
    $admin_email = 'admin@servicofacil.com';
    $admin_password = 'admin123';
    
    if ($email === $admin_email && $password === $admin_password) {
        // Login bem-sucedido
        $_SESSION['usuario_id'] = 1;
        $_SESSION['usuario_tipo'] = 'administrador';
        $_SESSION['nome'] = 'Administrador';
        $_SESSION['email'] = $admin_email;
        
        echo json_encode([
            'success' => true,
            'message' => 'Login realizado com sucesso',
            'usuario' => [
                'id' => 1,
                'nome' => 'Administrador',
                'email' => $admin_email,
                'tipo' => 'administrador'
            ]
        ]);
        
    } else {
        // Credenciais inválidas
        echo json_encode(['error' => 'Email ou senha inválidos']);
    }

} catch (Exception $e) {
    error_log("Erro no login administrativo: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?>