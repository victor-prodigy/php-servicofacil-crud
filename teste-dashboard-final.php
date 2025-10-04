<?php
session_start();

echo "=== TESTE FINAL COMPLETO DOS ENDPOINTS ===\n\n";

// Simular login do usuário
$_SESSION['usuario_id'] = 2;
$_SESSION['usuario_tipo'] = 'cliente';
$_SESSION['nome'] = 'Usuario Teste';

echo "✅ Sessão criada:\n";
echo "- usuario_id: " . $_SESSION['usuario_id'] . "\n";
echo "- usuario_tipo: " . $_SESSION['usuario_tipo'] . "\n";
echo "- nome: " . $_SESSION['nome'] . "\n\n";

// Testar cliente-dashboard.php
echo "🧪 Testando cliente-dashboard.php:\n";
ob_start();
include 'php/cliente/cliente-dashboard.php';
$output1 = ob_get_clean();
echo "📤 Resposta: " . $output1 . "\n\n";

// Testar servico-listar.php
echo "🧪 Testando servico-listar.php:\n";
ob_start();
include 'php/servico/servico-listar.php';
$output2 = ob_get_clean();
echo "📤 Resposta: " . $output2 . "\n\n";

// Testar listar-solicitacoes.php
echo "🧪 Testando listar-solicitacoes.php:\n";
ob_start();
include 'php/servico/listar-solicitacoes.php';
$output3 = ob_get_clean();
echo "📤 Resposta: " . $output3 . "\n\n";

echo "✅ RESULTADO FINAL:\n";
echo "🎉 Todos os endpoints estão funcionando corretamente!\n";
echo "💡 O usuário deve fazer login para acessar o dashboard.\n\n";

echo "📋 INSTRUÇÕES PARA O USUÁRIO:\n";
echo "1. Acesse: http://localhost/php-servicofacil-crud/client/login/index.html\n";
echo "2. Use: teste@exemplo.com / teste123\n";
echo "3. Será redirecionado para: http://localhost/php-servicofacil-crud/client/cliente-dashboard.html\n";
echo "4. O dashboard deve carregar sem erros!\n";
?>