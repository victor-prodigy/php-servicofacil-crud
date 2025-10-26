<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teste - Buscar Prestadores</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f8f9fa;
      padding: 2rem;
    }

    .test-result {
      margin: 10px 0;
      padding: 15px;
      border-radius: 5px;
    }

    .success {
      background: #d4edda;
      color: #155724;
    }

    .error {
      background: #f8d7da;
      color: #721c24;
    }

    .info {
      background: #d1ecf1;
      color: #0c5460;
    }

    pre {
      background: #f4f4f4;
      padding: 10px;
      border-radius: 5px;
      overflow-x: auto;
    }
  </style>
</head>

<body>
  <div class="container">
    <h1 class="mb-4">🔍 Teste - Buscar Prestadores</h1>

    <?php
    session_start();
    require_once 'conexao.php';

    echo '<div class="test-result info"><strong>📝 Testando sistema de busca de prestadores...</strong></div>';

    // Teste 1: Verificar se há prestadores no banco
    echo '<div class="test-result info"><strong>Teste 1:</strong> Verificando prestadores no banco de dados...</div>';

    try {
      $query = "SELECT COUNT(*) as total FROM service_provider sp 
                      INNER JOIN user u ON sp.user_id = u.user_id 
                      WHERE u.status = 'ativo' AND u.user_type = 'prestador'";
      $stmt = $pdo->prepare($query);
      $stmt->execute();
      $result = $stmt->fetch();
      $total = $result['total'];

      if ($total > 0) {
        echo '<div class="test-result success">✅ Encontrados ' . $total . ' prestador(es) ativo(s) no banco!</div>';
      } else {
        echo '<div class="test-result error">❌ Nenhum prestador encontrado no banco de dados!</div>';
        echo '<div class="test-result info">💡 Execute o arquivo lib/seed.sql para adicionar dados de teste.</div>';
      }
    } catch (Exception $e) {
      echo '<div class="test-result error">❌ Erro ao verificar banco: ' . $e->getMessage() . '</div>';
    }

    // Teste 2: Listar prestadores
    echo '<div class="test-result info"><strong>Teste 2:</strong> Listando todos os prestadores...</div>';

    try {
      $query = "SELECT 
                        sp.service_provider_id as prestador_id,
                        sp.specialty as especialidade,
                        sp.location as localizacao,
                        u.name as nome,
                        u.email,
                        u.phone_number as telefone
                      FROM service_provider sp
                      INNER JOIN user u ON sp.user_id = u.user_id
                      WHERE u.status = 'ativo' AND u.user_type = 'prestador'
                      ORDER BY u.name ASC";
      $stmt = $pdo->prepare($query);
      $stmt->execute();
      $prestadores = $stmt->fetchAll();

      if (count($prestadores) > 0) {
        echo '<div class="test-result success">';
        echo '<strong>✅ Prestadores cadastrados:</strong><br><br>';
        foreach ($prestadores as $prestador) {
          echo '• <strong>' . htmlspecialchars($prestador['nome']) . '</strong><br>';
          echo '  - Especialidade: ' . htmlspecialchars($prestador['especialidade']) . '<br>';
          echo '  - Localização: ' . htmlspecialchars($prestador['localizacao']) . '<br>';
          echo '  - Email: ' . htmlspecialchars($prestador['email']) . '<br>';
          echo '  - Telefone: ' . htmlspecialchars($prestador['telefone']) . '<br><br>';
        }
        echo '</div>';
      } else {
        echo '<div class="test-result error">❌ Nenhum prestador encontrado!</div>';
      }
    } catch (Exception $e) {
      echo '<div class="test-result error">❌ Erro ao listar prestadores: ' . $e->getMessage() . '</div>';
    }

    // Teste 3: Testar busca
    echo '<div class="test-result info"><strong>Teste 3:</strong> Testando busca por nome "Maria"...</div>';

    try {
      $query = "SELECT 
                        sp.service_provider_id as prestador_id,
                        sp.specialty as especialidade,
                        sp.location as localizacao,
                        u.name as nome,
                        u.email
                      FROM service_provider sp
                      INNER JOIN user u ON sp.user_id = u.user_id
                      WHERE u.status = 'ativo' 
                      AND u.user_type = 'prestador'
                      AND u.name LIKE ?
                      ORDER BY u.name ASC";
      $stmt = $pdo->prepare($query);
      $stmt->execute(['%Maria%']);
      $resultados = $stmt->fetchAll();

      if (count($resultados) > 0) {
        echo '<div class="test-result success">';
        echo '<strong>✅ Busca funcionando! Encontrados ' . count($resultados) . ' resultado(s):</strong><br><br>';
        foreach ($resultados as $resultado) {
          echo '• <strong>' . htmlspecialchars($resultado['nome']) . '</strong> - ' .
            htmlspecialchars($resultado['especialidade']) . '<br>';
        }
        echo '</div>';
      } else {
        echo '<div class="test-result error">❌ Nenhum resultado encontrado para "Maria"</div>';
      }
    } catch (Exception $e) {
      echo '<div class="test-result error">❌ Erro ao testar busca: ' . $e->getMessage() . '</div>';
    }

    // Teste 4: Testar filtro por especialidade
    echo '<div class="test-result info"><strong>Teste 4:</strong> Testando filtro por especialidade "Elétrica"...</div>';

    try {
      $query = "SELECT 
                        sp.service_provider_id as prestador_id,
                        sp.specialty as especialidade,
                        sp.location as localizacao,
                        u.name as nome
                      FROM service_provider sp
                      INNER JOIN user u ON sp.user_id = u.user_id
                      WHERE u.status = 'ativo' 
                      AND u.user_type = 'prestador'
                      AND sp.specialty LIKE ?
                      ORDER BY u.name ASC";
      $stmt = $pdo->prepare($query);
      $stmt->execute(['%Elétrica%']);
      $resultados = $stmt->fetchAll();

      if (count($resultados) > 0) {
        echo '<div class="test-result success">';
        echo '<strong>✅ Filtro por especialidade funcionando! Encontrados ' . count($resultados) . ' resultado(s):</strong><br><br>';
        foreach ($resultados as $resultado) {
          echo '• <strong>' . htmlspecialchars($resultado['nome']) . '</strong> - ' .
            htmlspecialchars($resultado['localizacao']) . '<br>';
        }
        echo '</div>';
      } else {
        echo '<div class="test-result error">❌ Nenhum resultado encontrado para "Elétrica"</div>';
      }
    } catch (Exception $e) {
      echo '<div class="test-result error">❌ Erro ao testar filtro: ' . $e->getMessage() . '</div>';
    }

    echo '<hr>';
    echo '<div class="test-result success">';
    echo '<strong>🎉 Teste concluído!</strong><br><br>';
    echo '<h5>Acessar a página de busca:</h5>';
    echo '<a href="../client/servico/buscar-prestadores.html" class="btn btn-primary btn-lg">Abrir Página de Busca</a>';
    echo '</div>';
    ?>

  </div>
</body>

</html>