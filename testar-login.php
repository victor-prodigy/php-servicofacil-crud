<?php
echo "=== TESTE DE LOGIN ===\n\n";

// Simular dados de login
$_POST['email'] = 'teste@exemplo.com';
$_POST['password'] = 'teste123';
$_POST['user_type'] = 'customer';

// Incluir o arquivo de login
ob_start();
include 'php/cliente/cliente-signin.php';
$output = ob_get_clean();

echo "📤 Resposta do servidor:\n";
echo $output . "\n\n";

echo "🔍 Análise:\n";
$json = json_decode($output, true);
if ($json) {
    if ($json['success']) {
        echo "✅ Login bem-sucedido!\n";
        echo "👤 Nome: " . $json['data']['name'] . "\n";
        echo "📧 Email: " . $json['data']['email'] . "\n";
        echo "🔗 Redirecionamento: " . $json['data']['redirect_url'] . "\n";
    } else {
        echo "❌ Erro no login: " . $json['error'] . "\n";
    }
} else {
    echo "⚠️ Resposta não é JSON válido\n";
}
?>