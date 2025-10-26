<?php

/**
 * Arquivo de teste para verificar a funcionalidade de avaliação
 * Este arquivo pode ser removido após os testes
 */

session_start();
require_once 'conexao.php';

echo "<h1>Teste da Funcionalidade de Avaliação</h1>";

try {
  // Verificar se as tabelas existem
  echo "<h2>1. Verificando estrutura do banco:</h2>";

  $tables = ['user', 'cliente', 'service_provider', 'service_request', 'contract', 'review'];

  foreach ($tables as $table) {
    $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
    if ($stmt->rowCount() > 0) {
      echo "✅ Tabela '$table' existe<br>";
    } else {
      echo "❌ Tabela '$table' NÃO existe<br>";
    }
  }

  // Verificar se há dados de teste
  echo "<h2>2. Verificando dados de teste:</h2>";

  // Contar usuários
  $stmt = $pdo->query("SELECT COUNT(*) as total FROM user");
  $userCount = $stmt->fetch()['total'];
  echo "Usuários cadastrados: $userCount<br>";

  // Contar clientes
  $stmt = $pdo->query("SELECT COUNT(*) as total FROM cliente");
  $clienteCount = $stmt->fetch()['total'];
  echo "Clientes cadastrados: $clienteCount<br>";

  // Contar prestadores
  $stmt = $pdo->query("SELECT COUNT(*) as total FROM service_provider");
  $prestadorCount = $stmt->fetch()['total'];
  echo "Prestadores cadastrados: $prestadorCount<br>";

  // Contar contratos
  $stmt = $pdo->query("SELECT COUNT(*) as total FROM contract");
  $contractCount = $stmt->fetch()['total'];
  echo "Contratos cadastrados: $contractCount<br>";

  // Contar avaliações
  $stmt = $pdo->query("SELECT COUNT(*) as total FROM review");
  $reviewCount = $stmt->fetch()['total'];
  echo "Avaliações cadastradas: $reviewCount<br>";

  // Se não há dados, criar dados de teste
  if ($contractCount == 0) {
    echo "<h2>3. Criando dados de teste:</h2>";

    // Criar cliente de teste
    $stmt = $pdo->prepare("INSERT INTO user (email, password, name, user_type) VALUES (?, ?, ?, ?)");
    $stmt->execute(['cliente@teste.com', password_hash('123456', PASSWORD_DEFAULT), 'Cliente Teste', 'cliente']);
    $userId = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO cliente (user_id) VALUES (?)");
    $stmt->execute([$userId]);
    $clienteId = $pdo->lastInsertId();

    // Criar prestador de teste
    $stmt = $pdo->prepare("INSERT INTO user (email, password, name, user_type) VALUES (?, ?, ?, ?)");
    $stmt->execute(['prestador@teste.com', password_hash('123456', PASSWORD_DEFAULT), 'Prestador Teste', 'prestador']);
    $prestadorUserId = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO service_provider (user_id, specialty, location) VALUES (?, ?, ?)");
    $stmt->execute([$prestadorUserId, 'Eletricista', 'São Paulo']);
    $prestadorId = $pdo->lastInsertId();

    // Criar solicitação de teste
    $stmt = $pdo->prepare("INSERT INTO service_request (cliente_id, titulo, categoria, descricao, endereco, cidade, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$clienteId, 'Instalação de tomada', 'Eletricidade', 'Preciso instalar uma tomada na sala', 'Rua Teste, 123', 'São Paulo', 'concluido']);
    $requestId = $pdo->lastInsertId();

    // Criar contrato de teste
    $stmt = $pdo->prepare("INSERT INTO contract (request_id, service_provider_id, cliente_id, contract_terms, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$requestId, $prestadorId, $clienteId, 'Contrato de instalação de tomada', 'completed']);
    $contractId = $pdo->lastInsertId();

    echo "✅ Dados de teste criados:<br>";
    echo "- Cliente ID: $clienteId<br>";
    echo "- Prestador ID: $prestadorId<br>";
    echo "- Solicitação ID: $requestId<br>";
    echo "- Contrato ID: $contractId<br>";

    // Simular login do cliente
    $_SESSION['usuario_id'] = $userId;
    $_SESSION['usuario_tipo'] = 'cliente';
    $_SESSION['cliente_id'] = $clienteId;

    echo "<br>✅ Sessão do cliente configurada<br>";
  }

  echo "<h2>4. Testando API de contratos:</h2>";

  // Simular chamada da API
  if (isset($_SESSION['cliente_id'])) {
    $cliente_id = $_SESSION['cliente_id'];

    $sql = "SELECT 
                    c.contract_id,
                    c.status,
                    c.created_at,
                    sr.titulo,
                    sr.categoria,
                    u.name as prestador_nome,
                    sp.specialty,
                    sp.location,
                    CASE 
                        WHEN r.review_id IS NOT NULL THEN 1
                        ELSE 0
                    END as ja_avaliado
                FROM contract c
                INNER JOIN service_request sr ON c.request_id = sr.request_id
                INNER JOIN service_provider sp ON c.service_provider_id = sp.service_provider_id
                INNER JOIN user u ON sp.user_id = u.user_id
                LEFT JOIN review r ON c.contract_id = r.contract_id AND r.cliente_id = ?
                WHERE c.cliente_id = ?
                ORDER BY c.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id, $cliente_id]);
    $contratos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "✅ API de contratos funcionando<br>";
    echo "Contratos encontrados: " . count($contratos) . "<br>";

    if (count($contratos) > 0) {
      echo "<h3>Contratos encontrados:</h3>";
      foreach ($contratos as $contrato) {
        echo "- Contrato ID: {$contrato['contract_id']} | Status: {$contrato['status']} | Já avaliado: {$contrato['ja_avaliado']}<br>";
      }
    }
  }

  echo "<h2>5. Links de teste:</h2>";
  echo "<a href='../client/cliente-dashboard.html' target='_blank'>Abrir Dashboard do Cliente</a><br>";

  if (isset($contractId)) {
    echo "<a href='../client/servico/avaliar-prestador.html?contract_id=$contractId' target='_blank'>Avaliar Prestador (Contrato $contractId)</a><br>";
  }
} catch (Exception $e) {
  echo "❌ Erro: " . $e->getMessage();
}
