<?php
session_start();
require_once __DIR__ . '/../conexao.php';

// Verificar se o usuário está logado e é um cliente
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

<?php
session_start();
require_once __DIR__ . '/../conexao.php';

// Verificar se o usuário está logado e é um cliente
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

try {
    // Clientes não publicam serviços, apenas fazem solicitações
    // Retornar lista vazia para clientes
    echo json_encode([
        'success' => true,
        'servicos' => []
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar serviços: ' . $e->getMessage()
    ]);
}
?>
?>