<?php
session_start();
header('Content-Type: application/json');

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'administrador') {
    echo json_encode([
        'authenticated' => false,
        'message' => 'Acesso não autorizado. Faça login como administrador.'
    ]);
    exit;
}

// Usuário autenticado como administrador
echo json_encode([
    'authenticated' => true,
    'usuario' => [
        'id' => $_SESSION['usuario_id'],
        'nome' => $_SESSION['nome'],
        'email' => $_SESSION['email'],
        'tipo' => $_SESSION['usuario_tipo']
    ]
]);
?>