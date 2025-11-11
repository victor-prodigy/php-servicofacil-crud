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
    // Verificar se a coluna instagram existe na tabela user
    $checkInstagram = $pdo->query("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS 
                                 WHERE TABLE_SCHEMA = DATABASE() 
                                 AND TABLE_NAME = 'user' 
                                 AND COLUMN_NAME = 'instagram'");
    $resultInstagram = $checkInstagram->fetch(PDO::FETCH_ASSOC);
    $instagramExists = ($resultInstagram && $resultInstagram['count'] > 0);

    // Verificar se a coluna observacao existe
    $checkObservacao = $pdo->query("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'user'
        AND COLUMN_NAME = 'observacao'");
    $resultObservacao = $checkObservacao->fetch(PDO::FETCH_ASSOC);
    $observacaoExists = ($resultObservacao && $resultObservacao['count'] > 0);

    $senha_hash = password_hash($dados['password'], PASSWORD_DEFAULT);

    if ($instagramExists && $observacaoExists) {
        // ✅ 8 colunas = 8 placeholders (6 com ? + 2 literais)
        $sql = "INSERT INTO user (email, password, name, phone_number, instagram, observacao, user_type, identity_verified) 
                VALUES (?, ?, ?, ?, ?, ?, 'cliente', FALSE)";
        $stmt = $pdo->prepare($sql);
        $instagram = !empty($dados['instagram']) ? $dados['instagram'] : null;
        $observacao = !empty($dados['observacao']) ? $dados['observacao'] : null;

        if ($stmt->execute([$dados['email'], $senha_hash, $dados['name'], $dados['phone_number'], $instagram, $observacao])) {
            return $pdo->lastInsertId();
        }
    } elseif ($instagramExists) {
        // ✅ Apenas instagram (7 colunas = 5 placeholders + 2 literais)
        $sql = "INSERT INTO user (email, password, name, phone_number, instagram, user_type, identity_verified) 
                VALUES (?, ?, ?, ?, ?, 'cliente', FALSE)";
        $stmt = $pdo->prepare($sql);
        $instagram = !empty($dados['instagram']) ? $dados['instagram'] : null;

        if ($stmt->execute([$dados['email'], $senha_hash, $dados['name'], $dados['phone_number'], $instagram])) {
            return $pdo->lastInsertId();
        }
    } elseif ($observacaoExists) {
        // ✅ Apenas observacao (7 colunas = 5 placeholders + 2 literais)
        $sql = "INSERT INTO user (email, password, name, phone_number, observacao, user_type, identity_verified) 
                VALUES (?, ?, ?, ?, ?, 'cliente', FALSE)";
        $stmt = $pdo->prepare($sql);
        $observacao = !empty($dados['observacao']) ? $dados['observacao'] : null;

        if ($stmt->execute([$dados['email'], $senha_hash, $dados['name'], $dados['phone_number'], $observacao])) {
            return $pdo->lastInsertId();
        }
    } else {
        // ✅ Sem campos extras (6 colunas = 4 placeholders + 2 literais)
        $sql = "INSERT INTO user (email, password, name, phone_number, user_type, identity_verified) 
                VALUES (?, ?, ?, ?, 'cliente', FALSE)";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$dados['email'], $senha_hash, $dados['name'], $dados['phone_number']])) {
            return $pdo->lastInsertId();
        }
    }
    return false;
}

// 👤 Função para criar cliente
function criarCliente($pdo, $user_id)
{
    try {
        $sql = "INSERT INTO cliente (user_id) VALUES (?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$user_id])) {
            return $pdo->lastInsertId();
        }
    } catch (PDOException $e) {
        error_log("Erro ao criar cliente: " . $e->getMessage());
        return false;
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
        'password' => $_POST['password'] ?? '',
        'instagram' => $_POST['instagram'] ?? '',
        'observacao' => $_POST['observacao'] ?? ''
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
    $cliente_id = criarCliente($pdo, $user_id);
    if (!$cliente_id) {
        enviarResposta(false, "Erro ao criar cliente");
    }

    // ✨ Sucesso
    enviarResposta(true, "Cliente cadastrado com sucesso", [
        'user_id' => $user_id,
        'cliente_id' => $cliente_id
    ]);
} catch (PDOException $e) {
    error_log("Erro PDO no cadastro de cliente: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    enviarResposta(false, "Erro no banco de dados: " . $e->getMessage());
} catch (Exception $e) {
    error_log("Erro no cadastro de cliente: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    enviarResposta(false, "Erro interno do servidor: " . $e->getMessage());
}
