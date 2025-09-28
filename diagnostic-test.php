<?php

/**
 * Teste de Diagnóstico do Sistema
 * Verifica se o XAMPP MySQL está rodando e testa conexões
 */

echo "🔍 DIAGNÓSTICO DO SISTEMA DE AUTENTICAÇÃO\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// 1. Verificar se o PHP está funcionando
echo "1. ✅ PHP está funcionando (versão: " . phpversion() . ")\n";

// 2. Verificar extensões necessárias
echo "2. Verificando extensões PHP:\n";
$extensions = ['pdo', 'pdo_mysql', 'mysqli', 'json'];
foreach ($extensions as $ext) {
  $status = extension_loaded($ext) ? "✅" : "❌";
  echo "   {$status} {$ext}\n";
}

// 3. Testar conexão MySQL
echo "\n3. Testando conexão MySQL:\n";
$host = "localhost";
$username = "root";
$password = "";
$dbname = "servicofacil";

try {
  // Primeiro tentar conectar sem especificar database
  $dsn = "mysql:host=$host;charset=utf8mb4";
  $pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
  ]);

  echo "   ✅ MySQL Server está rodando\n";

  // Verificar se o banco existe
  $stmt = $pdo->query("SHOW DATABASES LIKE 'servicofacil'");
  $dbExists = $stmt->rowCount() > 0;

  if ($dbExists) {
    echo "   ✅ Database 'servicofacil' existe\n";

    // Conectar ao banco específico
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false
    ]);

    echo "   ✅ Conexão com database 'servicofacil' bem-sucedida\n";

    // Verificar tabelas
    echo "\n4. Verificando estrutura do banco:\n";
    $tables = ['user', 'customer', 'service_provider'];

    foreach ($tables as $table) {
      try {
        $stmt = $pdo->query("DESCRIBE $table");
        $columns = $stmt->fetchAll();
        echo "   ✅ Tabela '$table' existe (" . count($columns) . " colunas)\n";

        // Mostrar colunas importantes
        foreach ($columns as $column) {
          if (in_array($column['Field'], ['user_id', 'email', 'name', 'password', 'specialty', 'phone_number'])) {
            echo "      - {$column['Field']} ({$column['Type']})\n";
          }
        }
      } catch (Exception $e) {
        echo "   ❌ Tabela '$table' não existe ou erro: " . $e->getMessage() . "\n";
      }
    }

    // Teste simples de inserção/consulta
    echo "\n5. Teste de operações básicas:\n";
    try {
      // Tentar inserir um registro de teste
      $stmt = $pdo->prepare("INSERT INTO user (email, password, name, phone_number, identity_verified) VALUES (?, ?, ?, ?, ?)");
      $stmt->execute(['teste.diagnostico@email.com', password_hash('123456', PASSWORD_DEFAULT), 'Teste Diagnóstico', '11999999999', false]);
      echo "   ✅ Inserção na tabela 'user' funcionando\n";

      // Tentar consultar
      $stmt = $pdo->prepare("SELECT user_id, email, name FROM user WHERE email = ?");
      $stmt->execute(['teste.diagnostico@email.com']);
      $user = $stmt->fetch();

      if ($user) {
        echo "   ✅ Consulta na tabela 'user' funcionando (ID: {$user['user_id']})\n";
      }

      // Limpar teste
      $pdo->prepare("DELETE FROM user WHERE email = ?")->execute(['teste.diagnostico@email.com']);
      echo "   ✅ Exclusão na tabela 'user' funcionando\n";
    } catch (Exception $e) {
      echo "   ❌ Erro nas operações básicas: " . $e->getMessage() . "\n";
    }
  } else {
    echo "   ❌ Database 'servicofacil' não existe\n";
    echo "   💡 Execute o arquivo 'phpmyadmin.sql' para criar o banco\n";
  }
} catch (PDOException $e) {
  echo "   ❌ Erro de conexão MySQL: " . $e->getMessage() . "\n";
  echo "   💡 Verifique se o MySQL do XAMPP está rodando\n";
  echo "   💡 Inicie o XAMPP Control Panel e start MySQL\n";
}

// 6. Verificar arquivos do sistema
echo "\n6. Verificando arquivos do sistema:\n";
$files = [
  'app/conexao.php' => 'Arquivo de conexão',
  'app/universal-signup.php' => 'Endpoint unificado de cadastro',
  'app/cliente-signin.php' => 'Endpoint de login cliente',
  'app/prestador-signin.php' => 'Endpoint de login prestador',
  'client/signup.html' => 'Página de cadastro unificada',
  'client/signin.html' => 'Página de login unificada'
];

foreach ($files as $file => $description) {
  $status = file_exists($file) ? "✅" : "❌";
  echo "   {$status} {$description}: {$file}\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "📋 PRÓXIMOS PASSOS:\n";
echo "\n1. Se MySQL não estiver rodando:\n";
echo "   - Abra XAMPP Control Panel\n";
echo "   - Clique em 'Start' ao lado do MySQL\n";
echo "   - Aguarde ficar verde\n";

echo "\n2. Se database não existir:\n";
echo "   - Acesse http://localhost/phpmyadmin\n";
echo "   - Importe o arquivo 'phpmyadmin.sql'\n";

echo "\n3. Após resolver problemas:\n";
echo "   - Execute: php test-authentication-system.php\n";

echo "\n🏁 DIAGNÓSTICO CONCLUÍDO\n";
echo str_repeat("=", 50) . "\n";
