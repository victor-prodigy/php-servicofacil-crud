<?php
// 📋 LISTAR POSTAGENS DO PRESTADOR - Clean Code
session_start();
require_once '../conexao.php';

// 🔒 Verificar se o usuário está logado e é um prestador
if (!isset($_SESSION['prestador_id']) || $_SESSION['usuario_tipo'] !== 'prestador') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Acesso não autorizado',
        'postagens' => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
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

    // 📋 Buscar todas as postagens do prestador
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
            WHERE service_provider_id = ?
            ORDER BY created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$prestador_id]);
    $postagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✨ Formatar dados
    foreach ($postagens as &$postagem) {
        $postagem['preco'] = number_format($postagem['preco'], 2, ',', '.');
        $postagem['created_at'] = date('d/m/Y H:i', strtotime($postagem['created_at']));
        if ($postagem['updated_at']) {
            $postagem['updated_at'] = date('d/m/Y H:i', strtotime($postagem['updated_at']));
        }
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'postagens' => $postagens ? $postagens : []
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar postagens: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar postagens: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

