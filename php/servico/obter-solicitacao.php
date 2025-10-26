<?php

/**
 * 🔍 OBTER SOLICITAÇÃO DE SERVIÇO - Clean Code
 * Carrega dados de uma solicitação específica do cliente
 */

session_start();

// 🔒 Configurações de segurança
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

/**
 * 🚨 Função para retornar erro JSON
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
 * 📅 Função para converter data em texto de prazo
 */
function convertDateToPrazo($dataString)
{
    if (!$dataString) return '';

    $dataAtual = new DateTime();
    $dataPrazo = new DateTime($dataString);
    $diferenca = $dataAtual->diff($dataPrazo)->days;

    if ($diferenca <= 1) {
        return 'Urgente (até 24h)';
    } elseif ($diferenca <= 3) {
        return 'Até 3 dias';
    } elseif ($diferenca <= 7) {
        return 'Até 1 semana';
    } elseif ($diferenca <= 14) {
        return 'Até 2 semanas';
    } else {
        return 'Sem pressa';
    }
}

try {
    // 🔐 Verificar se usuário está logado como cliente
    if (!isset($_SESSION['cliente_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
        retornarErro('Acesso negado. Faça login como cliente.', 401);
    }

    // 📝 Validar parâmetros
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        retornarErro('ID da solicitação é obrigatório.');
    }

    $solicitacaoId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($solicitacaoId === false || $solicitacaoId <= 0) {
        retornarErro('ID da solicitação inválido.');
    }

    $clienteId = $_SESSION['cliente_id'];

    // 🗄️ Conectar ao banco
    require_once __DIR__ . '/../conexao.php';

    // 🔍 Buscar solicitação
    $sql = "SELECT 
                request_id,
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
            WHERE request_id = :request_id 
            AND cliente_id = :cliente_id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':request_id', $solicitacaoId, PDO::PARAM_INT);
    $stmt->bindParam(':cliente_id', $clienteId, PDO::PARAM_INT);
    $stmt->execute();

    $solicitacao = $stmt->fetch(PDO::FETCH_ASSOC);

    // ✅ Verificar se encontrou a solicitação
    if (!$solicitacao) {
        retornarErro('Solicitação não encontrada ou você não tem permissão para acessá-la.', 404);
    }

    // 📅 Converter data para texto de prazo
    if ($solicitacao['prazo_desejado']) {
        $solicitacao['prazo_desejado'] = convertDateToPrazo($solicitacao['prazo_desejado']);
    }

    // 💰 Formatar valor
    if ($solicitacao['orcamento_maximo']) {
        $solicitacao['orcamento_maximo'] = number_format($solicitacao['orcamento_maximo'], 2, '.', '');
    }

    // 🎉 Retornar sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Solicitação carregada com sucesso.',
        'solicitacao' => $solicitacao
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    error_log("Erro PDO em obter-solicitacao.php: " . $e->getMessage());
    retornarErro('Erro interno do servidor. Tente novamente.', 500);
} catch (Exception $e) {
    error_log("Erro geral em obter-solicitacao.php: " . $e->getMessage());
    retornarErro('Erro inesperado. Tente novamente.', 500);
}
