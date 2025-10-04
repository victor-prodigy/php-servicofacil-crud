<?php

/**
 * Prestador Signin
 * Processa o login de prestadores de serviço
 */

// Iniciar sessão
session_start();

// Headers para resposta JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

// Incluir arquivo de conexão
require_once __DIR__ . '/../conexao.php';

try {
    // Receber e validar dados do formulário
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $user_type = $_POST['user_type'] ?? '';

    // Validações básicas
    if (empty($email)) {
        throw new Exception('E-mail é obrigatório');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('E-mail inválido');
    }

    if (empty($password)) {
        throw new Exception('Senha é obrigatória');
    }

    if ($user_type !== 'prestador' && $user_type !== 'service_provider') {
        throw new Exception('Tipo de usuário inválido para prestador');
    }

    // Buscar usuário na tabela service_provider através do JOIN com user
    $stmt = $pdo->prepare("
        SELECT 
            sp.service_provider_id,
            sp.user_id,
            u.email,
            u.password,
            u.name,
            sp.specialty,
            sp.location,
            u.identity_verified,
            u.created_at
        FROM service_provider sp
        INNER JOIN user u ON sp.user_id = u.user_id
        WHERE u.email = ?
    ");
    $stmt->execute([$email]);
    $service_provider = $stmt->fetch();

    if (!$service_provider) {
        throw new Exception('E-mail ou senha incorretos');
    }

    // Verificar senha
    if (!password_verify($password, $service_provider['password'])) {
        throw new Exception('E-mail ou senha incorretos');
    }

    // Login bem-sucedido - criar sessão
    $_SESSION['usuario_id'] = $service_provider['user_id'];
    $_SESSION['service_provider_id'] = $service_provider['service_provider_id'];
    $_SESSION['usuario_tipo'] = 'prestador';
    $_SESSION['nome'] = $service_provider['name'];
    $_SESSION['email'] = $service_provider['email'];
    $_SESSION['specialty'] = $service_provider['specialty'];
    $_SESSION['location'] = $service_provider['location'];
    $_SESSION['identity_verified'] = $service_provider['identity_verified'];

    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Login realizado com sucesso!',
        'data' => [
            'user_id' => $service_provider['user_id'],
            'service_provider_id' => $service_provider['service_provider_id'],
            'nome' => $service_provider['name'],
            'email' => $service_provider['email'],
            'specialty' => $service_provider['specialty'],
            'location' => $service_provider['location'],
            'usuario_tipo' => 'prestador',
            'identity_verified' => $service_provider['identity_verified'],
            'redirect_url' => '../prestador-dashboard.html'
        ]
    ]);
} catch (Exception $e) {
    // Resposta de erro
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    // Log do erro (em produção, não mostrar detalhes do banco)
    error_log("Erro de banco no login de prestador: " . $e->getMessage());

    // Resposta de erro genérica
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor. Tente novamente.'
    ]);
}
