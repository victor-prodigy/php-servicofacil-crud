<?php

// OBS: remover depois do teste

/**
 * Teste do cadastro de cliente
 */

// Simular dados POST
$_POST = [
  'name' => 'João Silva',
  'email' => 'joao.teste@email.com',
  'phone_number' => '11999999999',
  'password' => '123456',
  'confirm_password' => '123456',
  'user_type' => 'customer'
];

$_SERVER['REQUEST_METHOD'] = 'POST';

// Incluir o arquivo de cadastro
ob_start();
include 'app/cliente-signup.php';
$output = ob_get_clean();

echo "📝 Resultado do teste de cadastro:\n";
echo $output . "\n";
