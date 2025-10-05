<?php
// 🔐 CLIENTE SIGNIN - Clean Code & Normalized DB
include "../conexao.php";

// 🔥 Iniciar sessão
session_start();

// 📝 Função para validar dados de entrada
function validarDadosLogin($dados)
{
    $erros = [];

    if (empty($dados['email']) || !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        $erros[] = "Email válido é obrigatório";
    }

    if (empty($dados['password'])) {
        $erros[] = "Senha é obrigatória";
    }

    return $erros;
}

// 🔍 Função para buscar cliente no banco
function buscarCliente($pdo, $email)
{
    $sql = "
        SELECT 
            c.id as cliente_id,
            c.user_id,
            u.email,
            u.password,
            u.name
        FROM cliente c
        INNER JOIN user u ON c.user_id = u.user_id
        WHERE u.email = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    return $stmt->fetch();
}

// 🔒 Função para criar sessão do cliente
function criarSessaoCliente($cliente)
{
    $_SESSION['usuario_id'] = $cliente['user_id'];
    $_SESSION['cliente_id'] = $cliente['cliente_id'];
    $_SESSION['usuario_tipo'] = 'cliente';
    $_SESSION['nome'] = $cliente['name'];
    $_SESSION['email'] = $cliente['email'];
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
        'email' => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? ''
    ];

    // ✅ Validação
    $erros = validarDadosLogin($dados);
    if (!empty($erros)) {
        enviarResposta(false, implode(', ', $erros));
    }

    // 🔍 Buscar cliente
    $cliente = buscarCliente($pdo, $dados['email']);

    // 🔒 Verificar senha
    if ($cliente && password_verify($dados['password'], $cliente['password'])) {
        // ✨ Login bem-sucedido
        criarSessaoCliente($cliente);

        enviarResposta(true, "Login realizado com sucesso", [
            'nome' => $cliente['name'],
            'usuario_tipo' => 'cliente'
        ]);
    } else {
        // ❌ Credenciais inválidas
        enviarResposta(false, "Email ou senha incorretos");
    }
} catch (Exception $e) {
    enviarResposta(false, "Erro interno do servidor");
}
