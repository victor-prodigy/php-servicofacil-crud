<?php
session_start();
require_once __DIR__ . '/../conexao.php';

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    http_response_code(401);
    echo json_encode(['erro' => 'Acesso negado. Faça login como cliente.']);
    exit;
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

// Função para limpar e validar entrada
function limparEntrada($dados)
{
    return htmlspecialchars(strip_tags(trim($dados)));
}

// Função para validar dados obrigatórios
function validarDados($dados)
{
    $erros = [];

    if (empty($dados['titulo'])) {
        $erros[] = 'Título é obrigatório';
    } elseif (strlen($dados['titulo']) > 100) {
        $erros[] = 'Título deve ter no máximo 100 caracteres';
    }

    if (empty($dados['categoria'])) {
        $erros[] = 'Categoria é obrigatória';
    }

    if (empty($dados['descricao'])) {
        $erros[] = 'Descrição é obrigatória';
    } elseif (strlen($dados['descricao']) > 500) {
        $erros[] = 'Descrição deve ter no máximo 500 caracteres';
    }

    if (empty($dados['endereco'])) {
        $erros[] = 'Endereço é obrigatório';
    } elseif (strlen($dados['endereco']) > 200) {
        $erros[] = 'Endereço deve ter no máximo 200 caracteres';
    }

    if (empty($dados['cidade'])) {
        $erros[] = 'Cidade é obrigatória';
    } elseif (strlen($dados['cidade']) > 100) {
        $erros[] = 'Cidade deve ter no máximo 100 caracteres';
    }

    if (empty($dados['prazo_desejado'])) {
        $erros[] = 'Prazo desejado é obrigatório';
    }

    // Validar orçamento se fornecido
    if (!empty($dados['orcamento_maximo'])) {
        if (!is_numeric($dados['orcamento_maximo']) || $dados['orcamento_maximo'] < 0) {
            $erros[] = 'Orçamento deve ser um valor válido';
        } elseif ($dados['orcamento_maximo'] > 99999.99) {
            $erros[] = 'Orçamento máximo é R$ 99.999,99';
        }
    }

    // Validar observações se fornecidas
    if (!empty($dados['observacoes']) && strlen($dados['observacoes']) > 300) {
        $erros[] = 'Observações devem ter no máximo 300 caracteres';
    }

    return $erros;
}

// Função para converter prazo para data
function calcularDeadline($prazo)
{
    switch ($prazo) {
        case 'Urgente (até 24h)':
            return date('Y-m-d', strtotime('+1 day'));
        case 'Até 3 dias':
            return date('Y-m-d', strtotime('+3 days'));
        case 'Até 1 semana':
            return date('Y-m-d', strtotime('+7 days'));
        case 'Até 2 semanas':
            return date('Y-m-d', strtotime('+14 days'));
        case 'Sem pressa':
        default:
            return null;
    }
}

try {
    // Coletar e limpar dados do formulário
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

    // Validar dados
    $erros = validarDados($dados);

    if (!empty($erros)) {
        http_response_code(400);
        echo json_encode(['erro' => 'Dados inválidos', 'detalhes' => $erros]);
        exit;
    }

    // Preparar dados para inserção
    $location = $dados['endereco'] . ', ' . $dados['cidade'];
    $deadline = calcularDeadline($dados['prazo_desejado']);
    $budget = !empty($dados['orcamento_maximo']) ? $dados['orcamento_maximo'] : null;
    $photos = !empty($dados['observacoes']) ? $dados['observacoes'] : null;

    // Inserir no banco
    $sql = "INSERT INTO service_request (
                cliente_id,
                service_type,
                location,
                deadline,
                budget,
                photos,
                created_at
            ) VALUES (
                :cliente_id,
                :service_type,
                :location,
                :deadline,
                :budget,
                :photos,
                NOW()
            )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':cliente_id' => $_SESSION['usuario_id'],
        ':service_type' => $dados['titulo'],
        ':location' => $location,
        ':deadline' => $deadline,
        ':budget' => $budget,
        ':photos' => $photos
    ]);

    $solicitacao_id = $pdo->lastInsertId();

    http_response_code(201);
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Solicitação de serviço criada com sucesso!',
        'solicitacao_id' => $solicitacao_id,
        'redirect' => '../cliente-dashboard.html'
    ]);

} catch (Exception $e) {
    error_log('Erro na criação de solicitação: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'erro' => 'Erro interno do servidor. Tente novamente.',
        'detalhes' => 'Se o problema persistir, entre em contato com o suporte.'
    ]);
}
