<?php
// 🗑️ REMOVER POSTAGEM DE PRESTADOR - Administrador
session_start();
require_once '../conexao.php';

header('Content-Type: application/json; charset=utf-8');

// 🔒 Verificar se o usuário está logado e é um administrador
if (!isset($_SESSION['admin_id']) || $_SESSION['usuario_tipo'] !== 'administrador') {
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
        $motivo = $_POST['motivo'] ?? 'Conteúdo inapropriado';

        if (empty($service_id)) {
            enviarResposta(false, "ID do serviço é obrigatório");
        }

        $admin_id = $_SESSION['admin_id'];
        $admin_name = $_SESSION['admin_name'] ?? 'Administrador';

        // 🔍 Buscar informações da postagem antes de remover
        $sql_buscar = "SELECT 
                        ps.service_id,
                        ps.titulo,
                        ps.service_provider_id,
                        u.user_id,
                        u.name as prestador_nome,
                        u.email as prestador_email
                    FROM provider_service ps
                    INNER JOIN service_provider sp ON ps.service_provider_id = sp.service_provider_id
                    INNER JOIN user u ON sp.user_id = u.user_id
                    WHERE ps.service_id = ?";
        
        $stmt_buscar = $pdo->prepare($sql_buscar);
        $stmt_buscar->execute([$service_id]);
        $postagem = $stmt_buscar->fetch(PDO::FETCH_ASSOC);

        if (!$postagem) {
            enviarResposta(false, "Postagem não encontrada");
        }

        // 📝 Registrar no histórico administrativo
        try {
            // Verificar se a tabela existe, se não existir, criar
            $sql_check_table = "SHOW TABLES LIKE 'admin_history'";
            $stmt_check = $pdo->query($sql_check_table);
            
            if ($stmt_check->rowCount() == 0) {
                // Criar tabela de histórico administrativo
                $sql_create_table = "CREATE TABLE IF NOT EXISTS `admin_history` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `admin_id` INT(11) NOT NULL,
                    `action_type` VARCHAR(50) NOT NULL,
                    `target_type` VARCHAR(50) NOT NULL,
                    `target_id` INT(11) NOT NULL,
                    `description` TEXT,
                    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `fk_admin_history_admin` (`admin_id`),
                    CONSTRAINT `fk_admin_history_admin` FOREIGN KEY (`admin_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
                
                $pdo->exec($sql_create_table);
            }

            // Inserir no histórico
            $sql_history = "INSERT INTO admin_history (admin_id, action_type, target_type, target_id, description) 
                           VALUES (?, 'remover_postagem', 'provider_service', ?, ?)";
            $stmt_history = $pdo->prepare($sql_history);
            $descricao = "Postagem removida: '{$postagem['titulo']}' do prestador {$postagem['prestador_nome']}. Motivo: {$motivo}";
            $stmt_history->execute([$admin_id, $service_id, $descricao]);
        } catch (Exception $e) {
            // Log do erro mas continua com a remoção
            error_log("Erro ao registrar histórico administrativo: " . $e->getMessage());
        }

        // 📧 Criar notificação para o prestador (se a tabela de notificações existir)
        try {
            $sql_notificacao = "INSERT INTO notificacoes (usuario_id, tipo, mensagem) 
                               VALUES (?, 'sistema', ?)";
            $stmt_notificacao = $pdo->prepare($sql_notificacao);
            $mensagem = "Sua postagem '{$postagem['titulo']}' foi removida por um administrador. Motivo: {$motivo}";
            $stmt_notificacao->execute([$postagem['user_id'], $mensagem]);
        } catch (Exception $e) {
            // Log do erro mas continua
            error_log("Erro ao criar notificação: " . $e->getMessage());
        }

        // 🗑️ Excluir do banco
        $sql = "DELETE FROM provider_service WHERE service_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$service_id]);

        // ✨ Sucesso
        enviarResposta(true, "Postagem removida com sucesso e ação registrada no histórico administrativo!");
    } catch (Exception $e) {
        enviarResposta(false, "Erro interno: " . $e->getMessage());
    }
} else {
    enviarResposta(false, "Método não permitido");
}

