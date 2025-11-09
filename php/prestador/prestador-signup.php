<?php
// 🔧 PRESTADOR SIGNUP - Clean Code & Normalized DB
include "../conexao.php";

// 📝 Função para validar dados de entrada
function validarDadosPrestador($dados)
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

  if (empty($dados['specialty'])) {
    $erros[] = "Especialidade é obrigatória";
  }

  if (empty($dados['location'])) {
    $erros[] = "Localização é obrigatória";
  }

  return $erros;
}

// 🔒 Função para criar usuário
function criarUsuario($pdo, $dados)
{
  $sql = "INSERT INTO user (email, password, name, phone_number, user_type, identity_verified) VALUES (?, ?, ?, ?, 'prestador', FALSE)";
  $stmt = $pdo->prepare($sql);
  $senha_hash = password_hash($dados['password'], PASSWORD_DEFAULT);

  if ($stmt->execute([$dados['email'], $senha_hash, $dados['name'], $dados['phone_number']])) {
    return $pdo->lastInsertId();
  }
  return false;
}

// 🔧 Função para criar prestador
function criarPrestador($pdo, $user_id, $dados)
{
  $sql = "INSERT INTO service_provider (user_id, specialty, location) VALUES (?, ?, ?)";
  $stmt = $pdo->prepare($sql);

  if ($stmt->execute([$user_id, $dados['specialty'], $dados['location']])) {
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
    'phone_number' => $_POST['phone_number'] ?? null,
    'password' => $_POST['password'] ?? '',
    'specialty' => $_POST['specialty'] ?? '',
    'location' => $_POST['location'] ?? ''
  ];

  // ✅ Validação
  $erros = validarDadosPrestador($dados);
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

  // 🔧 Criação do prestador
  $prestador_id = criarPrestador($pdo, $user_id, $dados);
  if (!$prestador_id) {
    enviarResposta(false, "Erro ao criar prestador");
  }

  // ✨ Sucesso
  enviarResposta(true, "Prestador cadastrado com sucesso", [
    'user_id' => $user_id,
    'prestador_id' => $prestador_id
  ]);
} catch (Exception $e) {
  enviarResposta(false, "Erro interno do servidor");
}
?>

