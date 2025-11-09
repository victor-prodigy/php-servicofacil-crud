<?php
// 🗑️ APAGAR POSTAGEM DE SERVIÇO - Clean Code
session_start();
require_once '../conexao.php';

// 🔒 Verificar se o usuário está logado e é um prestador
if (!isset($_SESSION['prestador_id']) || $_SESSION['usuario_tipo'] !== 'prestador') {
    enviarResposta(false, "Acesso não autorizado");
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

    echo json_encode($resposta);
    exit;
}

// 🚀 PROCESSO PRINCIPAL
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 📋 Coleta de dados
        $service_id = $_POST['service_id'] ?? null;

        if (empty($service_id)) {
            enviarResposta(false, "ID do serviço é obrigatório");
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
        
        $sql_check = "SELECT service_id FROM provider_service WHERE service_id = ? AND service_provider_id = ?";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([$service_id, $prestador_id]);
        
        if (!$stmt_check->fetch()) {
            enviarResposta(false, "Serviço não encontrado ou você não tem permissão para excluí-lo");
        }

        // 🗑️ Excluir do banco
        $sql = "DELETE FROM provider_service WHERE service_id = ? AND service_provider_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$service_id, $prestador_id]);

        // ✨ Sucesso
        enviarResposta(true, "Postagem de serviço excluída com sucesso!");
    } catch (Exception $e) {
        enviarResposta(false, "Erro interno: " . $e->getMessage());
    }
} else {
    enviarResposta(false, "Método não permitido");
}

