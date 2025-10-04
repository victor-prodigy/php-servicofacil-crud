<?php
session_start();

// Verificar se o usuário está logado e é um prestador
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'prestador') {
    $response = [
        'authenticated' => false,
        'message' => 'Acesso não autorizado. Faça login como prestador.'
    ];
    echo json_encode($response);
    exit;
}

// Se chegou aqui, o usuário está autenticado como prestador
$response = [
    'authenticated' => true,
    'user_id' => $_SESSION['usuario_id'],
    'nome' => $_SESSION['nome'] ?? 'Prestador'
];

echo json_encode($response);
?>