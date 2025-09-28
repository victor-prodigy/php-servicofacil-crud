<?php

/**
 * Teste Completo do Sistema de Autenticação - Versão Web
 * Acesse via browser: http://localhost/php-servicofacil-crud/web-test-authentication.php
 */

// Incluir conexão com banco
require_once 'app/conexao.php';

// Função para fazer requisição POST interna
function makePostRequest($endpoint, $data)
{
  $url = 'http://localhost/php-servicofacil-crud/' . $endpoint;

  $postData = http_build_query($data);

  $context = stream_context_create([
    'http' => [
      'method' => 'POST',
      'header' => "Content-type: application/x-www-form-urlencoded\r\n",
      'content' => $postData
    ]
  ]);

  $response = file_get_contents($url, false, $context);
  return json_decode($response, true);
}

// Função para limpar dados de teste
function cleanTestData($pdo)
{
  try {
    $pdo->exec("DELETE FROM customer WHERE email LIKE '%teste.auth%'");
    $pdo->exec("DELETE FROM service_provider WHERE email LIKE '%teste.auth%'");
    $pdo->exec("DELETE FROM user WHERE email LIKE '%teste.auth%'");
    return "✅ Dados de teste limpos";
  } catch (Exception $e) {
    return "⚠️  Aviso: " . $e->getMessage();
  }
}

// Função para verificar dados no banco
function checkDatabase($pdo, $email, $userType)
{
  $results = [];

  try {
    // Verificar na tabela user
    $stmt = $pdo->prepare("SELECT user_id, email, name FROM user WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
      $results[] = "✅ User na tabela 'user': ID {$user['user_id']}, Nome: {$user['name']}";

      if ($userType === 'customer') {
        $stmt = $pdo->prepare("SELECT customer_id, phone_number FROM customer WHERE email = ?");
        $stmt->execute([$email]);
        $customer = $stmt->fetch();

        if ($customer) {
          $results[] = "✅ Cliente na tabela 'customer': ID {$customer['customer_id']}, Tel: {$customer['phone_number']}";
        } else {
          $results[] = "❌ Cliente NÃO encontrado na tabela 'customer'";
        }
      } elseif ($userType === 'service_provider') {
        $stmt = $pdo->prepare("SELECT service_provider_id, specialty, location FROM service_provider WHERE email = ?");
        $stmt->execute([$email]);
        $provider = $stmt->fetch();

        if ($provider) {
          $results[] = "✅ Prestador na tabela 'service_provider': ID {$provider['service_provider_id']}, {$provider['specialty']} em {$provider['location']}";
        } else {
          $results[] = "❌ Prestador NÃO encontrado na tabela 'service_provider'";
        }
      }
    } else {
      $results[] = "❌ Usuário NÃO encontrado na tabela 'user'";
    }
  } catch (Exception $e) {
    $results[] = "❌ Erro ao consultar banco: " . $e->getMessage();
  }

  return $results;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teste do Sistema de Autenticação</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
      background-color: #f5f5f5;
    }

    .container {
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .test-section {
      margin: 30px 0;
      padding: 20px;
      border: 1px solid #ddd;
      border-radius: 8px;
      background: #fafafa;
    }

    .success {
      color: #28a745;
    }

    .error {
      color: #dc3545;
    }

    .warning {
      color: #ffc107;
    }

    .info {
      color: #17a2b8;
    }

    pre {
      background: #f8f9fa;
      padding: 15px;
      border-radius: 6px;
      overflow-x: auto;
      border-left: 4px solid #007bff;
    }

    .btn {
      background: #007bff;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      margin: 5px;
    }

    .btn:hover {
      background: #0056b3;
    }

    .result-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin: 20px 0;
    }

    .test-card {
      padding: 15px;
      border-radius: 8px;
      border: 1px solid #ddd;
    }

    .passed {
      border-left: 4px solid #28a745;
      background: #f8fff9;
    }

    .failed {
      border-left: 4px solid #dc3545;
      background: #fff8f8;
    }

    h1,
    h2,
    h3 {
      color: #333;
    }

    .emoji {
      font-size: 1.2em;
    }
  </style>
</head>

<body>
  <div class="container">
    <h1>🧪 Teste Completo do Sistema de Autenticação</h1>
    <p class="info">Este teste verifica se o cadastro e login estão funcionando para clientes e prestadores.</p>

    <?php
    if (isset($_POST['run_test'])) {
      echo "<div class='test-section'>";
      echo "<h2>🚀 Executando Testes...</h2>";

      // Limpar dados anteriores
      echo "<p>" . cleanTestData($pdo) . "</p>";

      // TESTE 1: Cadastro Cliente
      echo "<div class='test-card'>";
      echo "<h3>📝 Teste 1: Cadastro de Cliente</h3>";

      $clienteData = [
        'name' => 'João Silva Teste',
        'email' => 'joao.teste.auth@email.com',
        'phone_number' => '11999888777',
        'password' => '123456',
        'confirm_password' => '123456',
        'user_type' => 'customer'
      ];

      echo "<pre>Dados enviados:\n" . json_encode($clienteData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

      $result1 = makePostRequest('app/universal-signup.php', $clienteData);

      if ($result1 && isset($result1['success']) && $result1['success']) {
        echo "<p class='success'>✅ Cadastro de cliente bem-sucedido: " . $result1['message'] . "</p>";
        $dbResults = checkDatabase($pdo, $clienteData['email'], 'customer');
        foreach ($dbResults as $dbResult) {
          echo "<p class='info'>" . $dbResult . "</p>";
        }
      } else {
        echo "<p class='error'>❌ Erro no cadastro: " . ($result1['error'] ?? 'Erro desconhecido') . "</p>";
      }
      echo "</div>";

      // TESTE 2: Cadastro Prestador
      echo "<div class='test-card'>";
      echo "<h3>⭐ Teste 2: Cadastro de Prestador</h3>";

      $prestadorData = [
        'name' => 'Maria Santos Teste',
        'email' => 'maria.teste.auth@email.com',
        'specialty' => 'Limpeza',
        'location' => 'São Paulo, SP',
        'password' => '123456',
        'confirm_password' => '123456',
        'user_type' => 'service_provider'
      ];

      echo "<pre>Dados enviados:\n" . json_encode($prestadorData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

      $result2 = makePostRequest('app/universal-signup.php', $prestadorData);

      if ($result2 && isset($result2['success']) && $result2['success']) {
        echo "<p class='success'>✅ Cadastro de prestador bem-sucedido: " . $result2['message'] . "</p>";
        $dbResults = checkDatabase($pdo, $prestadorData['email'], 'service_provider');
        foreach ($dbResults as $dbResult) {
          echo "<p class='info'>" . $dbResult . "</p>";
        }
      } else {
        echo "<p class='error'>❌ Erro no cadastro: " . ($result2['error'] ?? 'Erro desconhecido') . "</p>";
      }
      echo "</div>";

      // TESTE 3: Login Cliente
      echo "<div class='test-card'>";
      echo "<h3>🔐 Teste 3: Login de Cliente</h3>";

      $clienteLogin = [
        'email' => 'joao.teste.auth@email.com',
        'password' => '123456',
        'user_type' => 'customer'
      ];

      echo "<pre>Dados enviados:\n" . json_encode($clienteLogin, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

      $result3 = makePostRequest('app/cliente-signin.php', $clienteLogin);

      if ($result3 && isset($result3['success']) && $result3['success']) {
        echo "<p class='success'>✅ Login de cliente bem-sucedido: " . $result3['message'] . "</p>";
        echo "<p class='info'>🔗 Redirecionaria para: " . $result3['data']['redirect_url'] . "</p>";
      } else {
        echo "<p class='error'>❌ Erro no login: " . ($result3['error'] ?? 'Erro desconhecido') . "</p>";
      }
      echo "</div>";

      // TESTE 4: Login Prestador  
      echo "<div class='test-card'>";
      echo "<h3>⭐ Teste 4: Login de Prestador</h3>";

      $prestadorLogin = [
        'email' => 'maria.teste.auth@email.com',
        'password' => '123456',
        'user_type' => 'service_provider'
      ];

      echo "<pre>Dados enviados:\n" . json_encode($prestadorLogin, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

      $result4 = makePostRequest('app/prestador-signin.php', $prestadorLogin);

      if ($result4 && isset($result4['success']) && $result4['success']) {
        echo "<p class='success'>✅ Login de prestador bem-sucedido: " . $result4['message'] . "</p>";
        echo "<p class='info'>🔗 Redirecionaria para: " . $result4['data']['redirect_url'] . "</p>";
      } else {
        echo "<p class='error'>❌ Erro no login: " . ($result4['error'] ?? 'Erro desconhecido') . "</p>";
      }
      echo "</div>";

      // TESTE 5: Tentativa de login com senha errada
      echo "<div class='test-card'>";
      echo "<h3>🚫 Teste 5: Login com Senha Incorreta</h3>";

      $wrongLogin = [
        'email' => 'joao.teste.auth@email.com',
        'password' => 'senha_errada',
        'user_type' => 'customer'
      ];

      $result5 = makePostRequest('app/cliente-signin.php', $wrongLogin);

      if ($result5 && isset($result5['success']) && !$result5['success']) {
        echo "<p class='success'>✅ Segurança funcionando: Login rejeitado corretamente</p>";
        echo "<p class='info'>Erro: " . $result5['error'] . "</p>";
      } else {
        echo "<p class='error'>❌ Falha de segurança: Login deveria ter sido rejeitado</p>";
      }
      echo "</div>";

      // Resumo
      echo "<div class='test-section'>";
      echo "<h2>📊 Resumo dos Testes</h2>";
      echo "<div class='result-grid'>";

      $tests = [
        "Cadastro Cliente" => ($result1['success'] ?? false),
        "Cadastro Prestador" => ($result2['success'] ?? false),
        "Login Cliente" => ($result3['success'] ?? false),
        "Login Prestador" => ($result4['success'] ?? false),
        "Rejeição Senha Incorreta" => !($result5['success'] ?? true)
      ];

      $passed = 0;
      foreach ($tests as $testName => $result) {
        $class = $result ? 'passed success' : 'failed error';
        $icon = $result ? '✅' : '❌';
        $status = $result ? 'PASSOU' : 'FALHOU';
        echo "<div class='test-card $class'>";
        echo "<strong>$icon $testName</strong><br>Status: $status";
        echo "</div>";
        if ($result) $passed++;
      }
      echo "</div>";

      $total = count($tests);
      echo "<h3>🎯 Resultado Final: $passed/$total testes passaram</h3>";

      if ($passed === $total) {
        echo "<div class='test-card passed'>";
        echo "<h3 class='success'>🎉 TODOS OS TESTES PASSARAM!</h3>";
        echo "<p>O sistema de autenticação está funcionando perfeitamente!</p>";
        echo "</div>";
      } else {
        echo "<div class='test-card failed'>";
        echo "<h3 class='error'>⚠️ Alguns testes falharam</h3>";
        echo "<p>Verifique os erros acima e corrija os problemas.</p>";
        echo "</div>";
      }

      // Limpar dados de teste
      echo "<p class='info'>" . cleanTestData($pdo) . "</p>";

      echo "</div>";
      echo "</div>";
    }
    ?>

    <div class="test-section">
      <h2>🎮 Controles de Teste</h2>
      <form method="POST">
        <button type="submit" name="run_test" class="btn">🚀 Executar Todos os Testes</button>
      </form>

      <h3>🔗 Links Úteis</h3>
      <a href="client/signup.html" class="btn" target="_blank">📝 Testar Cadastro Manual</a>
      <a href="client/signin.html" class="btn" target="_blank">🔐 Testar Login Manual</a>
      <a href="unified-system-test.html" class="btn" target="_blank">📊 Documentação do Sistema</a>
    </div>
  </div>
</body>

</html>