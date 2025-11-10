<?php
/**
 * 💳 PROCESSAR PAGAMENTO
 * Processa pagamento de um contrato
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido. Use POST.'
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

    // Validar dados básicos
    $contractId = filter_input(INPUT_POST, 'contract_id', FILTER_VALIDATE_INT);
    $formaPagamento = filter_input(INPUT_POST, 'forma_pagamento', FILTER_SANITIZE_STRING);

    if (!$contractId || !$formaPagamento) {
        echo json_encode([
            'success' => false,
            'message' => 'Dados do pagamento incompletos.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Verificar se o contrato existe e pertence ao cliente
    $query = "SELECT 
                c.contract_id,
                c.request_id,
                c.service_provider_id,
                c.status as contract_status,
                sr.titulo,
                p.amount as valor_proposta
            FROM contract c
            INNER JOIN service_request sr ON c.request_id = sr.request_id
            LEFT JOIN proposal p ON p.request_id = c.request_id AND p.service_provider_id = c.service_provider_id
            WHERE c.contract_id = ? AND c.cliente_id = ? AND c.status IN ('active', 'completed')";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$contractId, $clienteId]);
    $contrato = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$contrato) {
        echo json_encode([
            'success' => false,
            'message' => 'Contrato não encontrado ou não está disponível para pagamento.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Verificar se já existe pagamento para este contrato
    $queryPagamento = "SELECT payment_id, status FROM payment WHERE contract_id = ?";
    $stmtPagamento = $pdo->prepare($queryPagamento);
    $stmtPagamento->execute([$contractId]);
    $pagamentoExistente = $stmtPagamento->fetch(PDO::FETCH_ASSOC);

    if ($pagamentoExistente) {
        if ($pagamentoExistente['status'] === 'completed' || $pagamentoExistente['status'] === 'paid') {
            echo json_encode([
                'success' => false,
                'message' => 'Este contrato já foi pago.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // Buscar valor da proposta
    $queryProposta = "SELECT amount FROM proposal 
                     WHERE request_id = ? AND service_provider_id = ? 
                     ORDER BY proposal_id DESC LIMIT 1";
    $stmtProposta = $pdo->prepare($queryProposta);
    $stmtProposta->execute([$contrato['request_id'], $contrato['service_provider_id']]);
    $proposta = $stmtProposta->fetch(PDO::FETCH_ASSOC);

    $valorTotal = $proposta['amount'] ?? 0;

    if ($valorTotal <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Valor do contrato inválido.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Iniciar transação
    $pdo->beginTransaction();

    // Determinar status do pagamento conforme o método
    $statusPagamento = 'pending';
    $paymentDate = null;
    $nomeArquivo = null;

    switch ($formaPagamento) {
        case 'cartao':
            // Validar dados do cartão
            $numeroCartao = filter_input(INPUT_POST, 'numero_cartao', FILTER_SANITIZE_STRING);
            $nomeCartao = filter_input(INPUT_POST, 'nome_cartao', FILTER_SANITIZE_STRING);
            $validadeCartao = filter_input(INPUT_POST, 'validade_cartao', FILTER_SANITIZE_STRING);
            $parcelas = filter_input(INPUT_POST, 'parcelas', FILTER_VALIDATE_INT);

            if (!$numeroCartao || !$nomeCartao || !$validadeCartao || !$parcelas) {
                throw new Exception('Dados do cartão incompletos.');
            }

            // Simular processamento de cartão (em produção, integrar com gateway de pagamento)
            $statusPagamento = 'completed';
            $paymentDate = date('Y-m-d');
            break;

        case 'pix':
            // Pagamento PIX - aguardando confirmação
            $statusPagamento = 'pending';
            break;

        case 'transferencia':
            // Processar upload do comprovante
            if (!isset($_FILES['comprovante']) || $_FILES['comprovante']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Comprovante não enviado ou com erro.');
            }

            // Criar diretório de uploads se não existir
            $uploadDir = __DIR__ . '/../../uploads/comprovantes/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $comprovante = $_FILES['comprovante'];
            $extensao = pathinfo($comprovante['name'], PATHINFO_EXTENSION);
            $nomeArquivo = time() . '_' . $contractId . '.' . $extensao;
            $caminhoCompleto = $uploadDir . $nomeArquivo;

            if (!move_uploaded_file($comprovante['tmp_name'], $caminhoCompleto)) {
                throw new Exception('Erro ao salvar comprovante.');
            }

            $statusPagamento = 'pending';
            break;

        default:
            throw new Exception('Forma de pagamento inválida.');
    }

    // Preparar dados adicionais
    $paymentMethod = $formaPagamento;
    $comprovantePath = $nomeArquivo;

    // Inserir ou atualizar pagamento
    if ($pagamentoExistente) {
        // Verificar se a coluna payment_method existe
        $checkColumn = $pdo->query("SHOW COLUMNS FROM payment LIKE 'payment_method'");
        if ($checkColumn->rowCount() > 0) {
            $queryInsert = "UPDATE payment 
                           SET amount = ?, payment_date = ?, status = ?, payment_method = ?, comprovante = ? 
                           WHERE payment_id = ?";
            $stmtInsert = $pdo->prepare($queryInsert);
            $stmtInsert->execute([$valorTotal, $paymentDate, $statusPagamento, $paymentMethod, $comprovantePath, $pagamentoExistente['payment_id']]);
        } else {
            $queryInsert = "UPDATE payment 
                           SET amount = ?, payment_date = ?, status = ? 
                           WHERE payment_id = ?";
            $stmtInsert = $pdo->prepare($queryInsert);
            $stmtInsert->execute([$valorTotal, $paymentDate, $statusPagamento, $pagamentoExistente['payment_id']]);
        }
        $paymentId = $pagamentoExistente['payment_id'];
    } else {
        // Verificar se a coluna payment_method existe
        $checkColumn = $pdo->query("SHOW COLUMNS FROM payment LIKE 'payment_method'");
        if ($checkColumn->rowCount() > 0) {
            $queryInsert = "INSERT INTO payment (contract_id, amount, payment_date, status, payment_method, comprovante) 
                           VALUES (?, ?, ?, ?, ?, ?)";
            $stmtInsert = $pdo->prepare($queryInsert);
            $stmtInsert->execute([$contractId, $valorTotal, $paymentDate, $statusPagamento, $paymentMethod, $comprovantePath]);
        } else {
            $queryInsert = "INSERT INTO payment (contract_id, amount, payment_date, status) 
                           VALUES (?, ?, ?, ?)";
            $stmtInsert = $pdo->prepare($queryInsert);
            $stmtInsert->execute([$contractId, $valorTotal, $paymentDate, $statusPagamento]);
        }
        $paymentId = $pdo->lastInsertId();
    }

    // Se pagamento foi aprovado, criar notificação para o prestador
    if ($statusPagamento === 'completed') {
        // Buscar user_id do prestador
        $queryPrestador = "SELECT user_id FROM service_provider WHERE service_provider_id = ?";
        $stmtPrestador = $pdo->prepare($queryPrestador);
        $stmtPrestador->execute([$contrato['service_provider_id']]);
        $prestador = $stmtPrestador->fetch(PDO::FETCH_ASSOC);

        if ($prestador) {
            $queryNotificacao = "INSERT INTO notificacoes (usuario_id, tipo, mensagem) 
                               VALUES (?, 'pagamento', ?)";
            $stmtNotificacao = $pdo->prepare($queryNotificacao);
            $mensagem = "Novo pagamento recebido para o serviço: {$contrato['titulo']}";
            $stmtNotificacao->execute([$prestador['user_id'], $mensagem]);

            // Criar notificação para o cliente também
            $queryNotificacaoCliente = "INSERT INTO notificacoes (usuario_id, tipo, mensagem) 
                                      VALUES (?, 'pagamento', ?)";
            $stmtNotificacaoCliente = $pdo->prepare($queryNotificacaoCliente);
            $mensagemCliente = "Pagamento confirmado para o serviço: {$contrato['titulo']}";
            $stmtNotificacaoCliente->execute([$_SESSION['usuario_id'], $mensagemCliente]);
        }

        // Atualizar status do contrato para "completed" se ainda não estiver
        if ($contrato['contract_status'] !== 'completed') {
            $queryUpdateContrato = "UPDATE contract SET status = 'completed' WHERE contract_id = ?";
            $stmtUpdateContrato = $pdo->prepare($queryUpdateContrato);
            $stmtUpdateContrato->execute([$contractId]);
        }
    }

    // Commit da transação
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Pagamento processado com sucesso',
        'payment_id' => $paymentId,
        'status' => $statusPagamento
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // Rollback em caso de erro
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Erro em processar.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar pagamento: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

