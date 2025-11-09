<?php
// 📊 CLIENTE DASHBOARD - Verificação de Autenticação
session_start();

header('Content-Type: application/json');

try {
  // Verificar se o usuário está autenticado
  if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    echo json_encode([
      'authenticated' => false,
      'message' => 'Você precisa fazer login para acessar esta página.'
    ]);
    exit;
  }

  // Usuário autenticado - retornar informações
  echo json_encode([
    'authenticated' => true,
    'nome' => $_SESSION['nome'] ?? 'Cliente',
    'email' => $_SESSION['email'] ?? '',
    'cliente_id' => $_SESSION['cliente_id'] ?? null,
    'usuario_id' => $_SESSION['usuario_id'] ?? null
  ]);
} catch (Exception $e) {
  echo json_encode([
    'authenticated' => false,
    'message' => 'Erro ao verificar autenticação: ' . $e->getMessage()
  ]);
}

