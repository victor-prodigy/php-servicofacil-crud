<?php
session_start();
require_once '../../conexao.php';

// Verificar se o usuário está logado e é um cliente
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'cliente') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar dados básicos
        $servico_id = filter_input(INPUT_POST, 'servico_id', FILTER_SANITIZE_NUMBER_INT);
        $forma_pagamento = filter_input(INPUT_POST, 'forma_pagamento', FILTER_SANITIZE_STRING);
        $cliente_id = $_SESSION['user_id'];

        if (!$servico_id || !$forma_pagamento) {
            throw new Exception('Dados do pagamento incompletos');
        }

        // Verificar se o serviço existe e está concluído
        $query = "SELECT s.*, p.id as prestador_id, p.nome as prestador_nome 
                 FROM servicos s 
                 JOIN prestadores p ON s.prestador_id = p.id
                 WHERE s.id = ? AND s.cliente_id = ? AND s.status = 'concluido'";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $servico_id, $cliente_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Serviço não encontrado ou não está concluído');
        }

        $servico = $result->fetch_assoc();

        // Iniciar transação
        $conn->begin_transaction();

        // Registrar pagamento
        $status = 'pendente';
        $data_pagamento = date('Y-m-d H:i:s');

        $query = "INSERT INTO pagamentos (
                    servico_id, 
                    cliente_id, 
                    prestador_id, 
                    valor, 
                    forma_pagamento, 
                    status, 
                    data_pagamento
                 ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param(
            "iiidsss",
            $servico_id,
            $cliente_id,
            $servico['prestador_id'],
            $servico['valor_total'],
            $forma_pagamento,
            $status,
            $data_pagamento
        );

        if (!$stmt->execute()) {
            throw new Exception('Erro ao registrar pagamento');
        }

        $pagamento_id = $stmt->insert_id;

        // Processar pagamento conforme o método
        switch ($forma_pagamento) {
            case 'cartao':
                // Validar dados do cartão
                $numero_cartao = filter_input(INPUT_POST, 'numero_cartao', FILTER_SANITIZE_STRING);
                $nome_cartao = filter_input(INPUT_POST, 'nome_cartao', FILTER_SANITIZE_STRING);
                $validade_cartao = filter_input(INPUT_POST, 'validade_cartao', FILTER_SANITIZE_STRING);
                $parcelas = filter_input(INPUT_POST, 'parcelas', FILTER_SANITIZE_NUMBER_INT);

                if (!$numero_cartao || !$nome_cartao || !$validade_cartao || !$parcelas) {
                    throw new Exception('Dados do cartão incompletos');
                }

                // Aqui seria integrado com gateway de pagamento
                $status = 'aprovado';
                break;

            case 'pix':
                // Gerar chave PIX
                $status = 'aguardando_pix';
                break;

            case 'transferencia':
                // Processar upload do comprovante
                if (!isset($_FILES['comprovante'])) {
                    throw new Exception('Comprovante não enviado');
                }

                $comprovante = $_FILES['comprovante'];
                $nome_arquivo = time() . '_' . $comprovante['name'];
                $caminho = '../../uploads/comprovantes/' . $nome_arquivo;

                if (!move_uploaded_file($comprovante['tmp_name'], $caminho)) {
                    throw new Exception('Erro ao salvar comprovante');
                }

                // Atualizar pagamento com caminho do comprovante
                $query = "UPDATE pagamentos SET comprovante = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("si", $nome_arquivo, $pagamento_id);
                $stmt->execute();

                $status = 'aguardando_confirmacao';
                break;

            default:
                throw new Exception('Forma de pagamento inválida');
        }

        // Atualizar status do pagamento
        $query = "UPDATE pagamentos SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $status, $pagamento_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Erro ao atualizar status do pagamento');
        }

        // Se pagamento foi aprovado, criar notificação para o prestador
        if ($status === 'aprovado' || $status === 'aguardando_confirmacao') {
            $query = "INSERT INTO notificacoes (
                        usuario_id, 
                        tipo, 
                        mensagem, 
                        data_criacao
                     ) VALUES (?, 'pagamento', ?, NOW())";
            
            $stmt = $conn->prepare($query);
            $mensagem = "Novo pagamento recebido para o serviço #{$servico_id}";
            $stmt->bind_param("is", $servico['prestador_id'], $mensagem);
            $stmt->execute();

            // Atualizar status do serviço
            $query = "UPDATE servicos SET status = 'pago' WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $servico_id);
            $stmt->execute();
        }

        // Commit da transação
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Pagamento processado com sucesso',
            'status' => $status
        ]);

    } catch (Exception $e) {
        // Rollback em caso de erro
        if ($conn && $conn->connect_errno === 0) {
            $conn->rollback();
        }

        echo json_encode([
            'success' => false,
            'message' => 'Erro ao processar pagamento: ' . $e->getMessage()
        ]);
    }

    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}
?>