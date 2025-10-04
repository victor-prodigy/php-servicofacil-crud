<?php

/**
 * Cliente Signin
 * Processa o login de clientes
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
require_once 'conexao.php';

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

    if ($user_type !== 'customer') {
        throw new Exception('Tipo de usuário inválido');
    }

    // Buscar usuário na tabela cliente
    $stmt = $pdo->prepare("
        SELECT 
            c.id as customer_id,
            c.user_id,
            c.email,
            c.senha as password,
            u.name,
            u.phone_number,
            u.identity_verified,
            u.created_at
        FROM cliente c
        INNER JOIN user u ON c.user_id = u.user_id
        WHERE c.email = ?
    ");
    $stmt->execute([$email]);
    $customer = $stmt->fetch();

    if (!$customer) {
        throw new Exception('E-mail ou senha incorretos');
    }

    // Verificar senha
    if (!password_verify($password, $customer['password'])) {
        throw new Exception('E-mail ou senha incorretos');
    }

    // Login bem-sucedido - criar sessão
    $_SESSION['user_id'] = $customer['user_id'];
    $_SESSION['customer_id'] = $customer['customer_id'];
    $_SESSION['user_type'] = 'customer';
    $_SESSION['name'] = $customer['name'];
    $_SESSION['email'] = $customer['email'];
    $_SESSION['identity_verified'] = $customer['identity_verified'];

    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Login realizado com sucesso!',
        'data' => [
            'user_id' => $customer['user_id'],
            'customer_id' => $customer['customer_id'],
            'name' => $customer['name'],
            'email' => $customer['email'],
            'user_type' => 'customer',
            'identity_verified' => $customer['identity_verified'],
            'redirect_url' => '../client/cliente-dashboard.html'
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
    error_log("Erro de banco no login de cliente: " . $e->getMessage());

    // Resposta de erro genérica
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor. Tente novamente.'
    ]);
}
