<?php
session_start();

// Verificar se o usuário está logado e é um cliente
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    $response = [
        'authenticated' => false,
        'message' => 'Acesso não autorizado. Faça login como cliente.'
    ];
    echo json_encode($response);
    exit;
}

// Se chegou aqui, o usuário está autenticado como cliente
$response = [
    'authenticated' => true,
    'user_id' => $_SESSION['usuario_id'],
    'nome' => $_SESSION['nome'] ?? 'Cliente'
];

echo json_encode($response);
?>
