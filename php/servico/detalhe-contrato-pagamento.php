<?php
/**
 * 🔍 OBTER DETALHES DO CONTRATO PARA PAGAMENTO
 * Retorna informações do contrato e serviço para processar pagamento
 */

session_start();
require_once __DIR__ . '/../conexao.php';

header('Content-Type: application/json; charset=utf-8');

// Verificar se o usuário está logado como cliente
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    echo json_encode([
        'success' => false,
        'message' => 'Acesso não autorizado. Faça login como cliente.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Obter cliente_id da sessão
    $clienteId = $_SESSION['cliente_id'] ?? null;
    
    if (!$clienteId) {
        echo json_encode([
            'success' => false,
            'message' => 'ID do cliente não encontrado na sessão.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Validar parâmetro contract_id
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID do contrato é obrigatório.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $contractId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($contractId === false || $contractId <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'ID do contrato inválido.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Buscar detalhes do contrato com informações do serviço e prestador
    $query = "SELECT 
                c.contract_id,
                c.request_id,
                c.service_provider_id,
                c.cliente_id,
                c.contract_terms,
                c.status as contract_status,
                c.created_at as contract_created_at,
                sr.titulo,
                sr.categoria,
                sr.descricao,
                sr.endereco,
                sr.cidade,
                u.name as prestador_nome,
                u.email as prestador_email,
                sp.specialty,
                p.amount as valor_total,
                p.payment_id,
                p.status as payment_status
            FROM contract c
            INNER JOIN service_request sr ON c.request_id = sr.request_id
            INNER JOIN service_provider sp ON c.service_provider_id = sp.service_provider_id
            INNER JOIN user u ON sp.user_id = u.user_id
            LEFT JOIN payment p ON p.contract_id = c.contract_id
            WHERE c.contract_id = ? AND c.cliente_id = ?";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$contractId, $clienteId]);
    $contrato = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$contrato) {
        echo json_encode([
            'success' => false,
            'message' => 'Contrato não encontrado ou não pertence a este cliente.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Verificar se o contrato está ativo ou concluído (pode ser pago)
    if ($contrato['contract_status'] !== 'active' && $contrato['contract_status'] !== 'completed') {
        echo json_encode([
            'success' => false,
            'message' => 'Este contrato não está disponível para pagamento.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Se já existe pagamento, verificar status
    if ($contrato['payment_id']) {
        if ($contrato['payment_status'] === 'completed' || $contrato['payment_status'] === 'paid') {
            echo json_encode([
                'success' => false,
                'message' => 'Este contrato já foi pago.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // Buscar valor da proposta aprovada
    $queryProposta = "SELECT amount FROM proposal 
                     WHERE request_id = ? AND service_provider_id = ? 
                     ORDER BY proposal_id DESC LIMIT 1";
    $stmtProposta = $pdo->prepare($queryProposta);
    $stmtProposta->execute([$contrato['request_id'], $contrato['service_provider_id']]);
    $proposta = $stmtProposta->fetch(PDO::FETCH_ASSOC);

    $valorTotal = $proposta['amount'] ?? $contrato['valor_total'] ?? 0;

    // Formatar dados para resposta
    $dados = [
        'contract_id' => (int)$contrato['contract_id'],
        'request_id' => (int)$contrato['request_id'],
        'titulo' => $contrato['titulo'],
        'descricao' => $contrato['descricao'],
        'categoria' => $contrato['categoria'],
        'prestador_nome' => $contrato['prestador_nome'],
        'prestador_email' => $contrato['prestador_email'],
        'valor_total' => (float)$valorTotal,
        'contract_status' => $contrato['contract_status'],
        'payment_status' => $contrato['payment_status'] ?? null,
        'chave_pix' => '00020126360014BR.GOV.BCB.PIX0114+55119999999995204000053039865802BR5909SERVICOFACIL6009SAO PAULO62070503***6304' . strtoupper(substr(md5($contractId . $clienteId), 0, 4)) // Chave PIX simulada
    ];

    echo json_encode([
        'success' => true,
        'servico' => $dados
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log("Erro PDO em detalhe-contrato-pagamento.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor. Tente novamente.'
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log("Erro geral em detalhe-contrato-pagamento.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar detalhes do contrato: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

