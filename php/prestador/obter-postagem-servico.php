<?php
// 📄 OBTER POSTAGEM DE SERVIÇO - Clean Code
session_start();
require_once '../conexao.php';

// 🔒 Verificar se o usuário está logado e é um prestador
if (!isset($_SESSION['prestador_id']) || $_SESSION['usuario_tipo'] !== 'prestador') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Acesso não autorizado'
    ]);
    exit;
}

try {
    $service_id = $_GET['service_id'] ?? null;

    if (empty($service_id)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'ID do serviço é obrigatório'
        ]);
        exit;
    }

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

    // 📋 Buscar postagem
    $sql = "SELECT 
                service_id,
                titulo,
                descricao,
                categoria,
                preco,
                disponibilidade,
                status,
                created_at,
                updated_at
            FROM provider_service
            WHERE service_id = ? AND service_provider_id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$service_id, $prestador_id]);
    $postagem = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$postagem) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Postagem não encontrada'
        ]);
        exit;
    }

    // ✨ Formatar dados
    $postagem['preco'] = number_format($postagem['preco'], 2, '.', '');
    $postagem['created_at'] = date('Y-m-d\TH:i', strtotime($postagem['created_at']));
    if ($postagem['updated_at']) {
        $postagem['updated_at'] = date('Y-m-d\TH:i', strtotime($postagem['updated_at']));
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'postagem' => $postagem
    ]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar postagem: ' . $e->getMessage()
    ]);
}

