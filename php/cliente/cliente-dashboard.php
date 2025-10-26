<?php
// NOTE: iniciar sessão
session_start();

// NOTE: verificar se é cliente logado
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    $resposta = [
        'authenticated' => false,
        'message' => 'Acesso negado. Faça login como cliente.'
    ];
} else {
    // Buscar dados do usuário no banco se não estiverem na sessão
    if (!isset($_SESSION['nome']) || !isset($_SESSION['email'])) {
        require_once '../conexao.php';

        try {
            $stmt = $pdo->prepare("SELECT name, email FROM user WHERE user_id = ?");
            $stmt->execute([$_SESSION['usuario_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $_SESSION['nome'] = $user['name'];
                $_SESSION['email'] = $user['email'];
            }
        } catch (Exception $e) {
            error_log("Erro ao buscar dados do usuário: " . $e->getMessage());
        }
    }

    $resposta = [
        'authenticated' => true,
        'usuario_id' => $_SESSION['usuario_id'],
        'nome' => $_SESSION['nome'] ?? 'Usuário',
        'email' => $_SESSION['email'] ?? '',
        'usuario_tipo' => $_SESSION['usuario_tipo']
    ];
}

header('Content-Type: application/json');
echo json_encode($resposta);
