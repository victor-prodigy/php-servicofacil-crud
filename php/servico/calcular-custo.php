<?php

/**
 * 💰 CALCULAR CUSTO AUTOMÁTICO DO SERVIÇO
 * Calcula estimativa de custo dividida por materiais e mão de obra
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

// 💰 Função para calcular custo baseado no tipo de serviço
function calcularCustoMateriais($tipoServico, $area, $especificacoes)
{
  // Valores base por m² para materiais (R$/m²)
  $valoresBase = [
    'Encanamento' => 45.00,
    'Elétrica' => 60.00,
    'Pintura' => 25.00,
    'Limpeza' => 8.00,
    'Jardinagem' => 15.00,
    'Marcenaria' => 80.00,
    'Pedreiro' => 35.00,
    'Mecânica' => 120.00,
    'Informática' => 150.00,
    'Outros' => 50.00
  ];

  $valorBase = $valoresBase[$tipoServico] ?? 50.00;

  // Multiplicadores por especificação
  $multiplicadores = [
    'basico' => 0.8,
    'intermediario' => 1.0,
    'avancado' => 1.3,
    'premium' => 1.8
  ];

  $multiplicador = $multiplicadores[$especificacoes] ?? 1.0;

  return $area * $valorBase * $multiplicador;
}

// 👷 Função para calcular custo de mão de obra
function calcularCustoMaoObra($tipoServico, $area, $especificacoes)
{
  // Valores base por m² para mão de obra (R$/m²)
  $valoresBase = [
    'Encanamento' => 80.00,
    'Elétrica' => 100.00,
    'Pintura' => 40.00,
    'Limpeza' => 25.00,
    'Jardinagem' => 30.00,
    'Marcenaria' => 120.00,
    'Pedreiro' => 60.00,
    'Mecânica' => 150.00,
    'Informática' => 200.00,
    'Outros' => 70.00
  ];

  $valorBase = $valoresBase[$tipoServico] ?? 70.00;

  // Multiplicadores por especificação
  $multiplicadores = [
    'basico' => 0.9,
    'intermediario' => 1.0,
    'avancado' => 1.4,
    'premium' => 2.0
  ];

  $multiplicador = $multiplicadores[$especificacoes] ?? 1.0;

  return $area * $valorBase * $multiplicador;
}

// ✅ Função para validar dados
function validarDados($dados)
{
  $erros = [];

  if (empty($dados['tipo_servico'])) {
    $erros[] = 'Tipo de serviço é obrigatório';
  }

  if (empty($dados['largura']) || !is_numeric($dados['largura']) || $dados['largura'] <= 0) {
    $erros[] = 'Largura deve ser um número maior que zero';
  }

  if (empty($dados['comprimento']) || !is_numeric($dados['comprimento']) || $dados['comprimento'] <= 0) {
    $erros[] = 'Comprimento deve ser um número maior que zero';
  }

  if (!empty($dados['altura']) && (!is_numeric($dados['altura']) || $dados['altura'] < 0)) {
    $erros[] = 'Altura deve ser um número válido';
  }

  return $erros;
}

try {
  // 📝 Verificar método da requisição
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    enviarResposta(false, 'Método não permitido. Use POST.');
  }

  // 🔐 Verificar autenticação (opcional - calculadora pode ser usada sem login)
  // Mas recomendamos login para melhor experiência
  $usuarioAutenticado = isset($_SESSION['cliente_id']) && $_SESSION['usuario_tipo'] === 'cliente';

  // 📋 Coletar e limpar dados do formulário
  $dados = [
    'tipo_servico' => limparEntrada($_POST['tipo_servico'] ?? ''),
    'largura' => $_POST['largura'] ?? 0,
    'comprimento' => $_POST['comprimento'] ?? 0,
    'altura' => $_POST['altura'] ?? 0,
    'especificacoes' => limparEntrada($_POST['especificacoes'] ?? 'intermediario')
  ];

  // ✅ Validar dados
  $erros = validarDados($dados);
  if (!empty($erros)) {
    enviarResposta(false, implode(', ', $erros));
  }

  // 📐 Calcular área
  $largura = floatval($dados['largura']);
  $comprimento = floatval($dados['comprimento']);
  $altura = floatval($dados['altura']);

  // Se altura fornecida, calcular volume; senão, calcular área
  if ($altura > 0) {
    // Para serviços que envolvem volume (ex: pintura de parede, construção)
    $area = ($largura * $comprimento) + (2 * $largura * $altura) + (2 * $comprimento * $altura);
  } else {
    // Para serviços que envolvem apenas área (ex: limpeza, jardinagem)
    $area = $largura * $comprimento;
  }

  // Garantir área mínima de 1m²
  if ($area < 1) {
    $area = 1;
  }

  // 💰 Calcular custos
  $custoMateriais = calcularCustoMateriais(
    $dados['tipo_servico'],
    $area,
    $dados['especificacoes']
  );

  $custoMaoObra = calcularCustoMaoObra(
    $dados['tipo_servico'],
    $area,
    $dados['especificacoes']
  );

  $custoTotal = $custoMateriais + $custoMaoObra;

  // ✨ Sucesso
  enviarResposta(true, 'Cálculo realizado com sucesso!', [
    'custo_materiais' => round($custoMateriais, 2),
    'custo_mao_obra' => round($custoMaoObra, 2),
    'custo_total' => round($custoTotal, 2),
    'area_calculada' => round($area, 2)
  ]);
} catch (Exception $e) {
  error_log("Erro em calcular-custo.php: " . $e->getMessage());
  enviarResposta(false, 'Erro interno do servidor. Tente novamente.');
}

