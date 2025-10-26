<?php
// 🔧 PRESTADOR SIGNIN - Clean Code & Normalized DB
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

// 🔍 Função para buscar prestador no banco
function buscarPrestador($pdo, $email)
{
    $sql = "
        SELECT 
            sp.service_provider_id as prestador_id,
            sp.user_id,
            u.email,
            u.password,
            u.name,
            sp.specialty,
            sp.location
        FROM service_provider sp
        INNER JOIN user u ON sp.user_id = u.user_id
        WHERE u.email = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    return $stmt->fetch();
}

// 🔒 Função para criar sessão do prestador
function criarSessaoPrestador($prestador)
{
    $_SESSION['usuario_id'] = $prestador['user_id'];
    $_SESSION['prestador_id'] = $prestador['prestador_id'];
    $_SESSION['usuario_tipo'] = 'prestador';
    $_SESSION['nome'] = $prestador['name'];
    $_SESSION['email'] = $prestador['email'];
    $_SESSION['specialty'] = $prestador['specialty'];
    $_SESSION['location'] = $prestador['location'];
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

    // 🔍 Buscar prestador
    $prestador = buscarPrestador($pdo, $dados['email']);

    // 🔒 Verificar senha
    if ($prestador && password_verify($dados['password'], $prestador['password'])) {
        // ✨ Login bem-sucedido
        criarSessaoPrestador($prestador);

        enviarResposta(true, "Login realizado com sucesso", [
            'nome' => $prestador['name'],
            'usuario_tipo' => 'prestador'
        ]);
    } else {
        // ❌ Credenciais inválidas
        enviarResposta(false, "Email ou senha incorretos");
    }
} catch (Exception $e) {
    enviarResposta(false, "Erro interno do servidor");
}
