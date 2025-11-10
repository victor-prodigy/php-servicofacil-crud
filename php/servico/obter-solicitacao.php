<?php

/**
 * 📋 OBTER SOLICITAÇÃO
 * Buscar uma solicitação específica por ID para edição
 */

session_start();
require_once __DIR__ . '/../conexao.php';

// 📤 Função para enviar resposta JSON
function enviarResposta($success, $message, $data = [])
{
  header('Content-Type: application/json; charset=utf-8');
  $resposta = [
    'success' => $success,
    'message' => $message
  ];

  if (!empty($data)) {
    $resposta = array_merge($resposta, $data);
  }

  echo json_encode($resposta, JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  // 🔐 Verificar se usuário está logado como cliente
  if (!isset($_SESSION['cliente_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    enviarResposta(false, 'Acesso negado. Faça login como cliente.');
  }

  // 📝 Verificar se ID foi fornecido
  if (!isset($_GET['id']) || empty($_GET['id'])) {
    enviarResposta(false, 'ID da solicitação não fornecido.');
  }

  $solicitacaoId = (int)$_GET['id'];
  $clienteId = $_SESSION['cliente_id'];

  // 🔍 Buscar solicitação
  $sql = "SELECT 
            request_id,
            cliente_id,
            titulo,
            categoria,
            descricao,
            endereco,
            cidade,
            prazo_desejado,
            orcamento_maximo,
            observacoes,
            status,
            created_at
          FROM service_request
          WHERE request_id = ? AND cliente_id = ?";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([$solicitacaoId, $clienteId]);
  $solicitacao = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$solicitacao) {
    enviarResposta(false, 'Solicitação não encontrada ou você não tem permissão para editá-la.');
  }

  // ✨ Sucesso
  enviarResposta(true, 'Solicitação encontrada.', [
    'solicitacao' => $solicitacao
  ]);

} catch (PDOException $e) {
  error_log("Erro PDO em obter-solicitacao.php: " . $e->getMessage());
  enviarResposta(false, 'Erro ao buscar solicitação: ' . $e->getMessage());
} catch (Exception $e) {
  error_log("Erro em obter-solicitacao.php: " . $e->getMessage());
  enviarResposta(false, 'Erro interno do servidor. Tente novamente.');
}

