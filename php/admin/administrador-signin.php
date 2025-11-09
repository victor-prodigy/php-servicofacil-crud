<?php

session_start();

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

function retornarErro($mensagem, $codigo = 400)
{
  http_response_code($codigo);
  echo json_encode([
    'success' => false,
    'message' => $mensagem
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

function retornarSucesso($mensagem, $dados = [])
{
  echo json_encode([
    'success' => true,
    'message' => $mensagem,
    'data' => $dados
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  retornarErro('Método não permitido', 405);
}

// Validar dados
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

// Debug log
error_log("Admin Login Attempt - Email: $email, Password length: " . strlen($password));

if (empty($email) || empty($password)) {
  error_log("Admin Login Failed - Empty credentials");
  retornarErro('Email e senha são obrigatórios');
}

try {
  // Conectar ao banco
  require_once __DIR__ . '/../conexao.php';

  // Buscar administrador
  $sql = "SELECT user_id, email, password, name FROM user 
            WHERE email = ? AND user_type = 'administrador' AND status = 'ativo'";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$email]);
  $admin = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$admin) {
    error_log("Admin Login Failed - User not found for email: $email");
    retornarErro('Credenciais inválidas ou acesso negado', 401);
  }

  // Debug
  error_log("Admin found: " . $admin['name'] . ", verifying password...");

  // Verificar senha
  if (!password_verify($password, $admin['password'])) {
    error_log("Admin Login Failed - Invalid password for: $email");
    retornarErro('Credenciais inválidas', 401);
  }

  error_log("Admin Login Success - " . $admin['name']);

  // Criar sessão
  $_SESSION['admin_id'] = $admin['user_id'];
  $_SESSION['admin_email'] = $admin['email'];
  $_SESSION['admin_name'] = $admin['name'];
  $_SESSION['usuario_tipo'] = 'administrador';
  $_SESSION['user_type'] = 'administrador';

  retornarSucesso('Login realizado com sucesso', [
    'admin_id' => $admin['user_id'],
    'name' => $admin['name'],
    'email' => $admin['email']
  ]);
} catch (Exception $e) {
  error_log("Erro no login admin: " . $e->getMessage());
  retornarErro('Erro interno do servidor', 500);
}
?>

