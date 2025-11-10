<?php
// 🛡️ CLIENTE SIGNUP - Clean Code & Normalized DB
include "../conexao.php";

// 📝 Função para validar dados de entrada
function validarDadosCliente($dados)
{
    $erros = [];

    if (empty($dados['name'])) {
        $erros[] = "Nome é obrigatório";
    }

    if (empty($dados['email']) || !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        $erros[] = "Email válido é obrigatório";
    }

    if (empty($dados['password']) || strlen($dados['password']) < 6) {
        $erros[] = "Senha deve ter pelo menos 6 caracteres";
    }

    return $erros;
}

// 🔒 Função para criar usuário
function criarUsuario($pdo, $dados)
{
    // 1. novo dado
    $sql = "INSERT INTO user (email, password, name, phone_number, instagram, user_type, identity_verified) VALUES (?, ?, ?, ?, ?, 'cliente', FALSE)";
    $stmt = $pdo->prepare($sql);
    $senha_hash = password_hash($dados['password'], PASSWORD_DEFAULT);

    // 2. novo dado
    if ($stmt->execute([$dados['email'], $senha_hash, $dados['name'], $dados['phone_number'], $dados['instagram'] ?? null])) {
        return $pdo->lastInsertId();
    }
    return false;
}

// 👤 Função para criar cliente
<<<<<<< HEAD
function criarCliente($pdo, $user_id, $instagram = null) {
    $sql = "INSERT INTO cliente (user_id, instagram) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$user_id, $instagram])) {
=======
function criarCliente($pdo, $user_id)
{
    $sql = "INSERT INTO cliente (user_id) VALUES (?)";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$user_id])) {
>>>>>>> 9c0aa016888fa96833f36704ef09b85568c383e8
        return $pdo->lastInsertId();
    }
    return false;
}

// 📤 Função para enviar resposta
function enviarResposta($success, $message, $data = [])
{
    header('Content-Type: application/json');
    $resposta = [
        'success' => $success,
        'message' => $message
    ];

    if (!empty($data)) {
        $resposta = array_merge($resposta, $data);
    }

    echo json_encode($resposta);
    exit;
}

// 🚀 PROCESSO PRINCIPAL
try {
    // 📋 Coleta de dados
    $dados = [
        'name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone_number' => $_POST['phone_number'] ?? '',
<<<<<<< HEAD
        'password' => $_POST['password'] ?? '',
        'instagram' => $_POST['instagram'] ?? ''
=======
        // 3. novo dado
        'instagram' => $_POST['instagram'] ?? '',
        'password' => $_POST['password'] ?? ''
>>>>>>> 9c0aa016888fa96833f36704ef09b85568c383e8
    ];

    // ✅ Validação
    $erros = validarDadosCliente($dados);
    if (!empty($erros)) {
        enviarResposta(false, implode(', ', $erros));
    }

    // 🔍 Verifica se email já existe
    $stmt = $pdo->prepare("SELECT user_id FROM user WHERE email = ?");
    $stmt->execute([$dados['email']]);
    if ($stmt->fetch()) {
        enviarResposta(false, "Email já está em uso");
    }

    // 💾 Criação do usuário
    $user_id = criarUsuario($pdo, $dados);
    if (!$user_id) {
        enviarResposta(false, "Erro ao criar usuário");
    }

    // 👤 Criação do cliente
    $cliente_id = criarCliente($pdo, $user_id, $dados['instagram']);
    if (!$cliente_id) {
        enviarResposta(false, "Erro ao criar cliente");
    }

    // ✨ Sucesso
    enviarResposta(true, "Cliente cadastrado com sucesso", [
        'user_id' => $user_id,
        'cliente_id' => $cliente_id
    ]);
} catch (Exception $e) {
    enviarResposta(false, "Erro interno do servidor");
}
