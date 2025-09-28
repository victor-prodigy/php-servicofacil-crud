<?php

/**
 * Teste do sistema de login
 * NOTA: Este teste usa os backends PHP específicos que ainda são necessários
 * para o sistema unificado (signin.html usa cliente-signin.php e prestador-signin.php)
 */

echo "📝 Testando Sistema de Login\n\n";

// Primeiro, vamos cadastrar um usuário de teste para fazer login
echo "1. Cadastrando usuário de teste...\n";

// Simular dados POST para cadastro
$_POST = [
    'name' => 'Teste Cliente Login',
    'email' => 'teste.login@email.com',
    'phone_number' => '11888888888',
    'password' => '123456',
    'confirm_password' => '123456',
    'user_type' => 'customer'
];

$_SERVER['REQUEST_METHOD'] = 'POST';

// Incluir o arquivo de cadastro
ob_start();
include 'app/cliente-signup.php';
$signup_output = ob_get_clean();

echo "Resultado do cadastro: " . $signup_output . "\n\n";

// Agora vamos testar o login
echo "2. Testando login...\n";

// Simular dados POST para login
$_POST = [
    'email' => 'teste.login@email.com',
    'password' => '123456',
    'user_type' => 'customer'
];

$_SERVER['REQUEST_METHOD'] = 'POST';

// Incluir o arquivo de login
ob_start();
include 'app/cliente-signin.php';
$signin_output = ob_get_clean();

echo "Resultado do login: " . $signin_output . "\n\n";

// Testar login com senha errada
echo "3. Testando login com senha incorreta...\n";

$_POST = [
    'email' => 'teste.login@email.com',
    'password' => 'senha_errada',
    'user_type' => 'customer'
];

ob_start();
include 'app/cliente-signin.php';
$signin_error_output = ob_get_clean();

echo "Resultado do login com senha errada: " . $signin_error_output . "\n\n";

echo "✅ Teste concluído!\n";
