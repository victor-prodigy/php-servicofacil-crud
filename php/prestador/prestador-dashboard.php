<?php
session_start();
require_once __DIR__ . '/../conexao.php';

// Verificar se o usuário está logado e é prestador
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'prestador') {
    $resposta = [
        'authenticated' => false,
        'message' => 'Acesso negado. Faça login como prestador.'
    ];
} else {
    // Garantir que prestador_id está na sessão
    if (!isset($_SESSION['prestador_id']) && isset($_SESSION['usuario_id'])) {
        try {
            $sql_buscar = "SELECT service_provider_id FROM service_provider WHERE user_id = ?";
            $stmt_buscar = $pdo->prepare($sql_buscar);
            $stmt_buscar->execute([$_SESSION['usuario_id']]);
            $prestador_buscado = $stmt_buscar->fetch();
            
            if ($prestador_buscado) {
                $_SESSION['prestador_id'] = $prestador_buscado['service_provider_id'];
            }
        } catch (PDOException $e) {
            error_log("Erro ao buscar prestador_id: " . $e->getMessage());
        }
    }
    
    $resposta = [
        'authenticated' => true,
        'usuario_id' => $_SESSION['usuario_id'],
        'nome' => $_SESSION['nome'] ?? 'Usuário',
        'email' => $_SESSION['email'] ?? '',
        'specialty' => $_SESSION['specialty'] ?? null,
        'location' => $_SESSION['location'] ?? null,
        'usuario_tipo' => $_SESSION['usuario_tipo'],
        'prestador_id' => $_SESSION['prestador_id'] ?? null
    ];
}

header('Content-Type: application/json');
echo json_encode($resposta, JSON_UNESCAPED_UNICODE);

