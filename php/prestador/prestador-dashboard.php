<?php
// NOTE: iniciar sessão
session_start();

// NOTE: verificar se é prestador logado
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'prestador') {
    $resposta = [
        'authenticated' => false,
        'msg' => 'Acesso negado. Faça login como prestador.'
    ];
} else {
    $resposta = [
        'authenticated' => true,
        'usuario_id' => $_SESSION['usuario_id'],
        'nome' => $_SESSION['nome'],
        'email' => $_SESSION['email'],
        'specialty' => $_SESSION['specialty'] ?? null,
        'location' => $_SESSION['location'] ?? null,
        'usuario_tipo' => $_SESSION['usuario_tipo']
    ];
}

header('Content-Type: application/json');
echo json_encode($resposta);

