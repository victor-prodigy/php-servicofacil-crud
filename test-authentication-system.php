<?php

/**
 * Teste Completo do Sistema de Autenticação Unificado
 * Testa signup e signin para cliente e prestador
 */

// Incluir conexão com banco
require_once 'app/conexao.php';

echo "🧪 TESTE COMPLETO DO SISTEMA DE AUTENTICAÇÃO\n";
echo "=" . str_repeat("=", 60) . "\n\n";

// Função para limpar dados de teste
function cleanTestData($pdo)
{
  try {
    $pdo->exec("DELETE FROM customer WHERE email LIKE '%teste.auth%'");
    $pdo->exec("DELETE FROM service_provider WHERE email LIKE '%teste.auth%'");
    $pdo->exec("DELETE FROM user WHERE email LIKE '%teste.auth%'");
    echo "✅ Dados de teste anteriores limpos\n";
  } catch (Exception $e) {
    echo "⚠️  Aviso: " . $e->getMessage() . "\n";
  }
}

// Função para testar cadastro
function testSignup($userData, $endpoint)
{
  echo "\n📝 Testando cadastro via {$endpoint}...\n";
  echo "Dados: " . json_encode($userData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

  // Simular requisição POST
  $_POST = $userData;
  $_SERVER['REQUEST_METHOD'] = 'POST';

  ob_start();
  include $endpoint;
  $response = ob_get_clean();

  echo "Resposta do servidor:\n{$response}\n";

  $responseData = json_decode($response, true);
  return $responseData;
}

// Função para testar login
function testSignin($loginData, $endpoint)
{
  echo "\n🔐 Testando login via {$endpoint}...\n";
  echo "Dados: " . json_encode($loginData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

  // Limpar sessão anterior
  session_destroy();
  session_start();

  // Simular requisição POST
  $_POST = $loginData;
  $_SERVER['REQUEST_METHOD'] = 'POST';

  ob_start();
  include $endpoint;
  $response = ob_get_clean();

  echo "Resposta do servidor:\n{$response}\n";

  $responseData = json_decode($response, true);
  return $responseData;
}

// Função para verificar dados no banco
function checkDatabaseData($pdo, $email, $userType)
{
  echo "\n🔍 Verificando dados no banco para: {$email} (tipo: {$userType})\n";

  try {
    // Verificar na tabela user
    $stmt = $pdo->prepare("SELECT user_id, email, name FROM user WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
      echo "✅ Usuário encontrado na tabela 'user': ID {$user['user_id']}, Nome: {$user['name']}\n";

      if ($userType === 'customer') {
        // Verificar na tabela customer
        $stmt = $pdo->prepare("SELECT customer_id, phone_number FROM customer WHERE email = ?");
        $stmt->execute([$email]);
        $customer = $stmt->fetch();

        if ($customer) {
          echo "✅ Cliente encontrado na tabela 'customer': ID {$customer['customer_id']}, Telefone: {$customer['phone_number']}\n";
        } else {
          echo "❌ Cliente NÃO encontrado na tabela 'customer'\n";
        }
      } elseif ($userType === 'service_provider') {
        // Verificar na tabela service_provider
        $stmt = $pdo->prepare("SELECT service_provider_id, specialty, location FROM service_provider WHERE email = ?");
        $stmt->execute([$email]);
        $provider = $stmt->fetch();

        if ($provider) {
          echo "✅ Prestador encontrado na tabela 'service_provider': ID {$provider['service_provider_id']}, Especialidade: {$provider['specialty']}, Local: {$provider['location']}\n";
        } else {
          echo "❌ Prestador NÃO encontrado na tabela 'service_provider'\n";
        }
      }
    } else {
      echo "❌ Usuário NÃO encontrado na tabela 'user'\n";
    }
  } catch (Exception $e) {
    echo "❌ Erro ao consultar banco: " . $e->getMessage() . "\n";
  }
}

// Iniciar testes
echo "🚀 Iniciando testes...\n";
cleanTestData($pdo);

// ==================== TESTE 1: CADASTRO CLIENTE ====================
echo "\n" . str_repeat("=", 60) . "\n";
echo "TESTE 1: CADASTRO DE CLIENTE (Sistema Unificado)\n";
echo str_repeat("=", 60) . "\n";

$clienteData = [
  'name' => 'João Silva Teste',
  'email' => 'joao.teste.auth@email.com',
  'phone_number' => '11999888777',
  'password' => '123456',
  'confirm_password' => '123456',
  'user_type' => 'customer'
];

$signupResult = testSignup($clienteData, 'app/universal-signup.php');
checkDatabaseData($pdo, $clienteData['email'], 'customer');

// ==================== TESTE 2: CADASTRO PRESTADOR ====================
echo "\n" . str_repeat("=", 60) . "\n";
echo "TESTE 2: CADASTRO DE PRESTADOR (Sistema Unificado)\n";
echo str_repeat("=", 60) . "\n";

$prestadorData = [
  'name' => 'Maria Santos Teste',
  'email' => 'maria.teste.auth@email.com',
  'specialty' => 'Limpeza',
  'location' => 'São Paulo, SP',
  'password' => '123456',
  'confirm_password' => '123456',
  'user_type' => 'service_provider'
];

$signupResult2 = testSignup($prestadorData, 'app/universal-signup.php');
checkDatabaseData($pdo, $prestadorData['email'], 'service_provider');

// ==================== TESTE 3: LOGIN CLIENTE ====================
echo "\n" . str_repeat("=", 60) . "\n";
echo "TESTE 3: LOGIN DE CLIENTE\n";
echo str_repeat("=", 60) . "\n";

$clienteLogin = [
  'email' => 'joao.teste.auth@email.com',
  'password' => '123456',
  'user_type' => 'customer'
];

$signinResult = testSignin($clienteLogin, 'app/cliente-signin.php');

// ==================== TESTE 4: LOGIN PRESTADOR ====================
echo "\n" . str_repeat("=", 60) . "\n";
echo "TESTE 4: LOGIN DE PRESTADOR\n";
echo str_repeat("=", 60) . "\n";

$prestadorLogin = [
  'email' => 'maria.teste.auth@email.com',
  'password' => '123456',
  'user_type' => 'service_provider'
];

$signinResult2 = testSignin($prestadorLogin, 'app/prestador-signin.php');

// ==================== TESTE 5: TENTAR LOGIN COM SENHA ERRADA ====================
echo "\n" . str_repeat("=", 60) . "\n";
echo "TESTE 5: LOGIN COM SENHA INCORRETA\n";
echo str_repeat("=", 60) . "\n";

$wrongLogin = [
  'email' => 'joao.teste.auth@email.com',
  'password' => 'senha_errada',
  'user_type' => 'customer'
];

$wrongSigninResult = testSignin($wrongLogin, 'app/cliente-signin.php');

// ==================== RESUMO DOS TESTES ====================
echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 RESUMO DOS TESTES\n";
echo str_repeat("=", 60) . "\n";

$tests = [
  "Cadastro Cliente (Sistema Unificado)" => $signupResult['success'] ?? false,
  "Cadastro Prestador (Sistema Unificado)" => $signupResult2['success'] ?? false,
  "Login Cliente" => $signinResult['success'] ?? false,
  "Login Prestador" => $signinResult2['success'] ?? false,
  "Rejeição Senha Incorreta" => !($wrongSigninResult['success'] ?? true)
];

foreach ($tests as $testName => $result) {
  $status = $result ? "✅ PASSOU" : "❌ FALHOU";
  echo "{$status} - {$testName}\n";
}

$passed = array_sum($tests);
$total = count($tests);
echo "\n🎯 Resultado Final: {$passed}/{$total} testes passaram\n";

if ($passed === $total) {
  echo "🎉 TODOS OS TESTES PASSARAM! Sistema funcionando perfeitamente!\n";
} else {
  echo "⚠️  Alguns testes falharam. Verifique os erros acima.\n";
}

// Limpar dados de teste
echo "\n🧹 Limpando dados de teste...\n";
cleanTestData($pdo);
echo "✅ Limpeza concluída!\n";

echo "\n" . str_repeat("=", 60) . "\n";
echo "🏁 TESTE COMPLETO FINALIZADO\n";
echo str_repeat("=", 60) . "\n";
