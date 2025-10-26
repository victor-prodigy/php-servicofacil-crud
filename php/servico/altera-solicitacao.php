<?php


session_start();

// Configurações de segurança HTTP
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// =====================================
// FUNÇÕES AUXILIARES
// =====================================

/**
 * Retorna uma resposta de erro em formato JSON
 * 
 * @param string $mensagem Mensagem de erro
 * @param int $codigo Código HTTP de resposta
 * @return void
 */
function retornarErro($mensagem, $codigo = 400)
{
  http_response_code($codigo);
  echo json_encode([
    'success' => false,
    'message' => $mensagem
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

/**
 * Sanitiza string removendo tags HTML e espaços extras
 * 
 * @param string $input String a ser sanitizada
 * @return string String sanitizada
 */
function sanitizeString($input)
{
  return trim(strip_tags($input));
}

/**
 * Converte texto de prazo em data específica
 * 
 * @param string $prazoTexto Texto do prazo selecionado
 * @return string Data formatada (Y-m-d)
 */
function convertPrazoToDate($prazoTexto)
{
  $dataAtual = new DateTime();

  $prazosMap = [
    'Urgente (até 24h)' => 'P1D',
    'Até 3 dias' => 'P3D',
    'Até 1 semana' => 'P7D',
    'Até 2 semanas' => 'P14D',
    'Sem pressa' => 'P30D'
  ];

  if (isset($prazosMap[$prazoTexto])) {
    $dataAtual->add(new DateInterval($prazosMap[$prazoTexto]));
  } elseif (DateTime::createFromFormat('Y-m-d', $prazoTexto)) {
    return $prazoTexto;
  } else {
    $dataAtual->add(new DateInterval('P7D')); // Padrão: 7 dias
  }

  return $dataAtual->format('Y-m-d');
}

/**
 * Retorna array com categorias válidas
 * 
 * @return array Lista de categorias permitidas
 */
function getCategoriasValidas()
{
  return [
    'Encanamento',
    'Elétrica',
    'Pintura',
    'Limpeza',
    'Jardinagem',
    'Marcenaria',
    'Pedreiro',
    'Mecânica',
    'Informática',
    'Outros'
  ];
}

/**
 * Retorna array com prazos válidos
 * 
 * @return array Lista de prazos permitidos
 */
function getPrazosValidos()
{
  return [
    'Urgente (até 24h)',
    'Até 3 dias',
    'Até 1 semana',
    'Até 2 semanas',
    'Sem pressa'
  ];
}

// =====================================
// VALIDAÇÕES DE SEGURANÇA
// =====================================

/**
 * Verifica autenticação do cliente
 * 
 * @return void
 * @throws Exception Se não estiver autenticado
 */
function verificarAutenticacao()
{
  if (!isset($_SESSION['cliente_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    retornarErro('Acesso negado. Faça login como cliente.', 401);
  }
}

/**
 * Verifica se é uma requisição POST
 * 
 * @return void
 * @throws Exception Se não for POST
 */
function verificarMetodoRequisicao()
{
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    retornarErro('Método não permitido. Use POST.', 405);
  }
}

// =====================================
// VALIDAÇÕES DE DADOS
// =====================================

/**
 * Valida e sanitiza todos os dados de entrada
 * 
 * @return array Dados validados e sanitizados
 * @throws Exception Se algum dado for inválido
 */
function validarDadosEntrada()
{
  // Validar ID da solicitação
  $solicitacaoId = filter_input(INPUT_POST, 'solicitacao_id', FILTER_VALIDATE_INT);
  if (!$solicitacaoId || $solicitacaoId <= 0) {
    retornarErro('ID da solicitação é obrigatório e deve ser válido.');
  }

  // Sanitizar campos de texto
  $dados = [
    'id' => $solicitacaoId,
    'titulo' => sanitizeString($_POST['titulo'] ?? ''),
    'categoria' => sanitizeString($_POST['categoria'] ?? ''),
    'descricao' => sanitizeString($_POST['descricao'] ?? ''),
    'endereco' => sanitizeString($_POST['endereco'] ?? ''),
    'cidade' => sanitizeString($_POST['cidade'] ?? ''),
    'prazo_desejado' => sanitizeString($_POST['prazo_desejado'] ?? ''),
    'observacoes' => sanitizeString($_POST['observacoes'] ?? ''),
    'orcamento_maximo' => filter_input(INPUT_POST, 'orcamento_maximo', FILTER_VALIDATE_FLOAT)
  ];

  // Validar campos obrigatórios
  validarCamposObrigatorios($dados);

  // Validar limites de caracteres
  validarLimitesCaracteres($dados);

  // Validar categoria
  validarCategoria($dados['categoria']);

  // Validar prazo
  validarPrazo($dados['prazo_desejado']);

  // Validar orçamento
  validarOrcamento($dados['orcamento_maximo']);

  return $dados;
}

/**
 * Valida campos obrigatórios
 * 
 * @param array $dados Dados a serem validados
 * @return void
 * @throws Exception Se algum campo obrigatório estiver vazio
 */
function validarCamposObrigatorios($dados)
{
  $camposObrigatorios = ['titulo', 'categoria', 'descricao', 'endereco', 'cidade', 'prazo_desejado'];

  foreach ($camposObrigatorios as $campo) {
    if (empty($dados[$campo])) {
      $nomeAmigavel = ucfirst(str_replace('_', ' ', $campo));
      retornarErro("{$nomeAmigavel} é obrigatório.");
    }
  }
}

/**
 * Valida limites de caracteres dos campos
 * 
 * @param array $dados Dados a serem validados
 * @return void
 * @throws Exception Se algum campo exceder o limite
 */
function validarLimitesCaracteres($dados)
{
  $limites = [
    'titulo' => 100,
    'descricao' => 500,
    'endereco' => 200,
    'cidade' => 100,
    'observacoes' => 300
  ];

  foreach ($limites as $campo => $limite) {
    if (strlen($dados[$campo]) > $limite) {
      $nomeAmigavel = ucfirst(str_replace('_', ' ', $campo));
      retornarErro("{$nomeAmigavel} deve ter no máximo {$limite} caracteres.");
    }
  }
}

/**
 * Valida se a categoria é válida
 * 
 * @param string $categoria Categoria a ser validada
 * @return void
 * @throws Exception Se categoria for inválida
 */
function validarCategoria($categoria)
{
  if (!in_array($categoria, getCategoriasValidas())) {
    retornarErro('Categoria inválida.');
  }
}

/**
 * Valida se o prazo é válido
 * 
 * @param string $prazo Prazo a ser validado
 * @return void
 * @throws Exception Se prazo for inválido
 */
function validarPrazo($prazo)
{
  if (!in_array($prazo, getPrazosValidos())) {
    retornarErro('Prazo desejado inválido.');
  }
}

/**
 * Valida orçamento máximo
 * 
 * @param float|null $orcamento Orçamento a ser validado
 * @return void
 * @throws Exception Se orçamento for inválido
 */
function validarOrcamento($orcamento)
{
  if ($orcamento !== null && $orcamento !== false) {
    if ($orcamento < 0 || $orcamento > 99999.99) {
      retornarErro('Orçamento deve estar entre R$ 0,00 e R$ 99.999,99.');
    }
  }
}

// =====================================
// OPERAÇÕES DE BANCO DE DADOS
// =====================================

/**
 * Verifica se a solicitação existe e pertence ao cliente
 * 
 * @param PDO $pdo Conexão com banco de dados
 * @param int $solicitacaoId ID da solicitação
 * @param int $clienteId ID do cliente
 * @return array Dados da solicitação existente
 * @throws Exception Se solicitação não existir ou não pertencer ao cliente
 */
function verificarPropriedadeSolicitacao($pdo, $solicitacaoId, $clienteId)
{
  $sql = "SELECT request_id, status FROM service_request 
            WHERE request_id = :request_id AND cliente_id = :cliente_id";

  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':request_id', $solicitacaoId, PDO::PARAM_INT);
  $stmt->bindParam(':cliente_id', $clienteId, PDO::PARAM_INT);
  $stmt->execute();

  $solicitacao = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$solicitacao) {
    retornarErro('Solicitação não encontrada ou você não tem permissão para alterá-la.', 404);
  }

  return $solicitacao;
}

/**
 * Verifica se a solicitação pode ser editada
 * 
 * @param array $solicitacao Dados da solicitação
 * @return void
 * @throws Exception Se solicitação não puder ser editada
 */
function verificarStatusEdicao($solicitacao)
{
  $statusIneditaveis = ['concluido', 'cancelado'];

  if (in_array($solicitacao['status'], $statusIneditaveis)) {
    retornarErro('Não é possível editar solicitações concluídas ou canceladas.');
  }
}

/**
 * Atualiza a solicitação no banco de dados
 * 
 * @param PDO $pdo Conexão com banco de dados
 * @param array $dados Dados validados para atualização
 * @param int $clienteId ID do cliente
 * @return bool Resultado da operação
 * @throws Exception Se erro na atualização
 */
function atualizarSolicitacao($pdo, $dados, $clienteId)
{
  $sql = "UPDATE service_request SET 
                titulo = :titulo,
                categoria = :categoria,
                descricao = :descricao,
                endereco = :endereco,
                cidade = :cidade,
                prazo_desejado = :prazo_desejado,
                orcamento_maximo = :orcamento_maximo,
                observacoes = :observacoes
            WHERE request_id = :request_id AND cliente_id = :cliente_id";

  $stmt = $pdo->prepare($sql);

  // Bind dos parâmetros
  $stmt->bindParam(':titulo', $dados['titulo'], PDO::PARAM_STR);
  $stmt->bindParam(':categoria', $dados['categoria'], PDO::PARAM_STR);
  $stmt->bindParam(':descricao', $dados['descricao'], PDO::PARAM_STR);
  $stmt->bindParam(':endereco', $dados['endereco'], PDO::PARAM_STR);
  $stmt->bindParam(':cidade', $dados['cidade'], PDO::PARAM_STR);

  $prazoData = convertPrazoToDate($dados['prazo_desejado']);
  $stmt->bindParam(':prazo_desejado', $prazoData, PDO::PARAM_STR);

  $orcamento = ($dados['orcamento_maximo'] !== null && $dados['orcamento_maximo'] !== false)
    ? $dados['orcamento_maximo'] : null;
  $stmt->bindParam(
    ':orcamento_maximo',
    $orcamento,
    $orcamento !== null ? PDO::PARAM_STR : PDO::PARAM_NULL
  );

  $stmt->bindParam(':observacoes', $dados['observacoes'], PDO::PARAM_STR);
  $stmt->bindParam(':request_id', $dados['id'], PDO::PARAM_INT);
  $stmt->bindParam(':cliente_id', $clienteId, PDO::PARAM_INT);

  return $stmt->execute();
}

// =====================================
// FLUXO PRINCIPAL
// =====================================

try {
  // Verificações de segurança
  verificarAutenticacao();
  verificarMetodoRequisicao();

  // Validar dados de entrada
  $dados = validarDadosEntrada();

  $clienteId = $_SESSION['cliente_id'];

  // Conectar ao banco de dados
  require_once __DIR__ . '/../conexao.php';

  // Verificar propriedade e status da solicitação
  $solicitacaoExistente = verificarPropriedadeSolicitacao($pdo, $dados['id'], $clienteId);
  verificarStatusEdicao($solicitacaoExistente);

  // Executar atualização com transação
  $pdo->beginTransaction();

  try {
    $resultado = atualizarSolicitacao($pdo, $dados, $clienteId);

    if (!$resultado) {
      throw new Exception('Erro ao atualizar a solicitação no banco de dados.');
    }

    $pdo->commit();

    // Resposta de sucesso
    echo json_encode([
      'success' => true,
      'message' => 'Solicitação atualizada com sucesso!',
      'solicitacao_id' => $dados['id']
    ], JSON_UNESCAPED_UNICODE);
  } catch (Exception $e) {
    $pdo->rollBack();
    throw $e;
  }
} catch (PDOException $e) {
  error_log("Erro PDO em altera-solicitacao.php: " . $e->getMessage());
  retornarErro('Erro interno do servidor. Tente novamente.', 500);
} catch (Exception $e) {
  error_log("Erro geral em altera-solicitacao.php: " . $e->getMessage());
  retornarErro('Erro inesperado: ' . $e->getMessage(), 500);
}
