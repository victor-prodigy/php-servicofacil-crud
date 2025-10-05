<?php
// NOTE: iniciar sessão
session_start();

// NOTE: verificar se é cliente logado
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    $resposta = [
        'authenticated' => false,
        'msg' => 'Acesso negado. Faça login como cliente.'
    ];
} else {
    $resposta = [
        'authenticated' => true,
        'usuario_id' => $_SESSION['usuario_id'],
        'nome' => $_SESSION['nome'],
        'email' => $_SESSION['email'],
        'usuario_tipo' => $_SESSION['usuario_tipo']
    ];
}

header('Content-Type: application/json');
echo json_encode($resposta);
