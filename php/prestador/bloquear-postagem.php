<?php
// 🔒 BLOQUEAR/DESBLOQUEAR POSTAGEM - Prestador
session_start();
require_once '../conexao.php';

header('Content-Type: application/json; charset=utf-8');

// 🔒 Verificar se o usuário está logado e é um prestador
if (!isset($_SESSION['prestador_id']) || $_SESSION['usuario_tipo'] !== 'prestador') {
    echo json_encode([
        'success' => false,
        'message' => 'Acesso não autorizado'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 📤 Função para enviar resposta
function enviarResposta($success, $message, $data = [])
{
    header('Content-Type: application/json');
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

// 🚀 PROCESSO PRINCIPAL
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 📋 Coleta de dados
        $service_id = $_POST['service_id'] ?? null;
        $acao = $_POST['acao'] ?? 'bloquear'; // 'bloquear' ou 'desbloquear'

        if (empty($service_id)) {
            enviarResposta(false, "ID do serviço é obrigatório");
        }

        if (!in_array($acao, ['bloquear', 'desbloquear'])) {
            enviarResposta(false, "Ação inválida");
        }

        // 🔒 Verificar se o serviço pertence ao prestador
        $prestador_id = $_SESSION['prestador_id'];
        
        // Se prestador_id não estiver na sessão, tentar buscar pelo user_id
        if (empty($prestador_id) && isset($_SESSION['usuario_id'])) {
            $sql_buscar = "SELECT service_provider_id FROM service_provider WHERE user_id = ?";
            $stmt_buscar = $pdo->prepare($sql_buscar);
            $stmt_buscar->execute([$_SESSION['usuario_id']]);
            $prestador_buscado = $stmt_buscar->fetch();
            
            if ($prestador_buscado) {
                $prestador_id = $prestador_buscado['service_provider_id'];
                $_SESSION['prestador_id'] = $prestador_id; // Atualizar sessão
            }
        }
        
        if (empty($prestador_id)) {
            enviarResposta(false, "Erro: ID do prestador não encontrado. Por favor, faça login novamente como prestador.");
        }
        
        $sql_check = "SELECT service_id, titulo, status FROM provider_service WHERE service_id = ? AND service_provider_id = ?";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([$service_id, $prestador_id]);
        $postagem = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        if (!$postagem) {
            enviarResposta(false, "Postagem não encontrada ou você não tem permissão para gerenciá-la");
        }

        // 🔒 Atualizar status da postagem
        // Quando bloqueada, mudamos o status para 'inativo' para que fique invisível
        $novo_status = ($acao === 'bloquear') ? 'inativo' : 'ativo';
        
        $sql = "UPDATE provider_service SET status = ? WHERE service_id = ? AND service_provider_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$novo_status, $service_id, $prestador_id]);

        // 📧 Criar notificação para o prestador sobre o bloqueio
        if ($acao === 'bloquear') {
            try {
                $user_id = $_SESSION['usuario_id'];
                $sql_notificacao = "INSERT INTO notificacoes (usuario_id, tipo, mensagem) 
                                   VALUES (?, 'sistema', ?)";
                $stmt_notificacao = $pdo->prepare($sql_notificacao);
                $mensagem = "Sua postagem '{$postagem['titulo']}' foi bloqueada temporariamente e não está visível para outros usuários.";
                $stmt_notificacao->execute([$user_id, $mensagem]);
            } catch (Exception $e) {
                // Log do erro mas continua
                error_log("Erro ao criar notificação: " . $e->getMessage());
            }
        }

        // ✨ Sucesso
        $mensagem_sucesso = $acao === 'bloquear' 
            ? "Postagem bloqueada com sucesso! Ela não está mais visível para outros usuários."
            : "Postagem desbloqueada com sucesso! Ela está novamente visível para outros usuários.";
        
        enviarResposta(true, $mensagem_sucesso, [
            'status' => $novo_status
        ]);
    } catch (Exception $e) {
        enviarResposta(false, "Erro interno: " . $e->getMessage());
    }
} else {
    enviarResposta(false, "Método não permitido");
}

