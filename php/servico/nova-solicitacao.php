<?php

/**
 * 📝 SOLICITAR SERVIÇO
 * Criar uma nova solicitação de serviço
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

// 🧹 Função para limpar entrada
function limparEntrada($dados)
{
  return trim(strip_tags($dados));
}

// 📅 Função para converter prazo em data
function convertPrazoToDate($prazoTexto)
{
  $dataAtual = new DateTime();

  switch ($prazoTexto) {
    case 'Urgente (até 24h)':
      $dataAtual->add(new DateInterval('P1D'));
      break;
    case 'Até 3 dias':
      $dataAtual->add(new DateInterval('P3D'));
      break;
    case 'Até 1 semana':
      $dataAtual->add(new DateInterval('P7D'));
      break;
    case 'Até 2 semanas':
      $dataAtual->add(new DateInterval('P14D'));
      break;
    case 'Sem pressa':
      $dataAtual->add(new DateInterval('P30D'));
      break;
    default:
      $dataAtual->add(new DateInterval('P7D'));
  }

  return $dataAtual->format('Y-m-d');
}

// ✅ Função para validar dados
function validarDados($dados)
{
  $erros = [];

  if (empty($dados['titulo'])) {
    $erros[] = 'Título é obrigatório';
  }
  if (strlen($dados['titulo']) > 100) {
    $erros[] = 'Título deve ter no máximo 100 caracteres';
  }

  if (empty($dados['categoria'])) {
    $erros[] = 'Categoria é obrigatória';
  }

  if (empty($dados['descricao'])) {
    $erros[] = 'Descrição é obrigatória';
  }
  if (strlen($dados['descricao']) > 500) {
    $erros[] = 'Descrição deve ter no máximo 500 caracteres';
  }

  if (empty($dados['endereco'])) {
    $erros[] = 'Endereço é obrigatório';
  }
  if (strlen($dados['endereco']) > 200) {
    $erros[] = 'Endereço deve ter no máximo 200 caracteres';
  }

  if (empty($dados['cidade'])) {
    $erros[] = 'Cidade é obrigatória';
  }
  if (strlen($dados['cidade']) > 100) {
    $erros[] = 'Cidade deve ter no máximo 100 caracteres';
  }

  if (empty($dados['prazo_desejado'])) {
    $erros[] = 'Prazo desejado é obrigatório';
  }

  if (!empty($dados['orcamento_maximo'])) {
    if (!is_numeric($dados['orcamento_maximo']) || $dados['orcamento_maximo'] < 0) {
      $erros[] = 'Orçamento deve ser um valor válido';
    }
    if ($dados['orcamento_maximo'] > 99999.99) {
      $erros[] = 'Orçamento máximo é R$ 99.999,99';
    }
  }

  if (!empty($dados['observacoes']) && strlen($dados['observacoes']) > 300) {
    $erros[] = 'Observações devem ter no máximo 300 caracteres';
  }

  return $erros;
}

try {
  // 🔐 Verificar se usuário está logado como cliente
  error_log("DEBUG: Verificando autenticação...");
  error_log("SESSION: " . print_r($_SESSION, true));


  if (!isset($_SESSION['cliente_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    error_log("ERROR: Usuário não autenticado ou não é cliente");
    enviarResposta(false, 'Acesso negado. Faça login como cliente.');
  }

  // 📝 Verificar método da requisição
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("ERROR: Método não é POST: " . $_SERVER['REQUEST_METHOD']);
    enviarResposta(false, 'Método não permitido. Use POST.');
  }

  error_log("DEBUG: POST data: " . print_r($_POST, true));

  // 📋 Coletar e limpar dados do formulário
  $dados = [
    'titulo' => limparEntrada($_POST['titulo'] ?? ''),
    'categoria' => limparEntrada($_POST['categoria'] ?? ''),
    'descricao' => limparEntrada($_POST['descricao'] ?? ''),
    'endereco' => limparEntrada($_POST['endereco'] ?? ''),
    'cidade' => limparEntrada($_POST['cidade'] ?? ''),
    'prazo_desejado' => limparEntrada($_POST['prazo_desejado'] ?? ''),
    'orcamento_maximo' => $_POST['orcamento_maximo'] ?? null,
    'observacoes' => limparEntrada($_POST['observacoes'] ?? '')
  ];

  error_log("DEBUG: Dados coletados: " . print_r($dados, true));

  // ✅ Validar dados
  $erros = validarDados($dados);
  if (!empty($erros)) {
    enviarResposta(false, implode(', ', $erros));
  }

  // Converter orçamento para NULL se vazio
  if (empty($dados['orcamento_maximo'])) {
    $dados['orcamento_maximo'] = null;
  }

  // Converter observações para NULL se vazio
  if (empty($dados['observacoes'])) {
    $dados['observacoes'] = null;
  }

  // Converter prazo para data
  $prazoDesejadoData = convertPrazoToDate($dados['prazo_desejado']);

  $clienteId = $_SESSION['cliente_id'];

  // 📝 Inserir nova solicitação
  $sql = "INSERT INTO service_request (
            cliente_id, 
            titulo, 
            categoria, 
            descricao, 
            endereco, 
            cidade, 
            prazo_desejado, 
            orcamento_maximo, 
            observacoes,
            status
          ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendente')";

  try {
    $stmt = $pdo->prepare($sql);
    if (!$stmt) {
      throw new Exception('Erro na preparação da query: ' . print_r($pdo->errorInfo(), true));
    }

    $resultado = $stmt->execute([
      $clienteId,
      $dados['titulo'],
      $dados['categoria'],
      $dados['descricao'],
      $dados['endereco'],
      $dados['cidade'],
      $prazoDesejadoData,
      $dados['orcamento_maximo'],
      $dados['observacoes']
    ]);

    if (!$resultado) {
      throw new Exception('Erro na execução da query: ' . print_r($stmt->errorInfo(), true));
    }

    $solicitacaoId = $pdo->lastInsertId();

    if (!$solicitacaoId) {
      throw new Exception('Erro ao obter ID da solicitação criada');
    }

    // ✨ Sucesso
    enviarResposta(true, 'Solicitação de serviço criada com sucesso!', [
      'solicitacao_id' => $solicitacaoId,
      'redirect' => '../cliente-dashboard.html'
    ]);
  } catch (PDOException $e) {
    error_log("Erro PDO em nova-solicitacao.php: " . $e->getMessage());
    error_log("SQL: " . $sql);
    error_log("Dados: " . print_r([
      $clienteId,
      $dados['titulo'],
      $dados['categoria'],
      $dados['descricao'],
      $dados['endereco'],
      $dados['cidade'],
      $prazoDesejadoData,
      $dados['orcamento_maximo'],
      $dados['observacoes']
    ], true));
    enviarResposta(false, 'Erro ao inserir no banco de dados: ' . $e->getMessage());
  }
} catch (Exception $e) {
  error_log("Erro em nova-solicitacao.php: " . $e->getMessage());
  enviarResposta(false, 'Erro interno do servidor. Tente novamente.');
}
