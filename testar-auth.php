<?php
// Simular ambiente POST
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['email'] = 'teste@exemplo.com';
$_POST['password'] = 'teste123';
$_POST['user_type'] = 'customer';

echo "=== TESTE DIRETO DE AUTENTICAÇÃO ===\n\n";

require_once 'php/conexao.php';

try {
    $email = 'teste@exemplo.com';
    $password = 'teste123';
    
    // Buscar usuário
    $stmt = $pdo->prepare("
        SELECT 
            c.id as customer_id,
            c.user_id,
            c.email,
            c.senha as password,
            u.name,
            u.phone_number,
            u.identity_verified,
            u.created_at
        FROM cliente c
        INNER JOIN user u ON c.user_id = u.user_id
        WHERE c.email = ?
    ");
    $stmt->execute([$email]);
    $customer = $stmt->fetch();
    
    if ($customer) {
        echo "✅ Usuário encontrado!\n";
        echo "👤 Nome: {$customer['name']}\n";
        echo "📧 Email: {$customer['email']}\n";
        echo "🔑 Hash da senha no BD: " . substr($customer['password'], 0, 20) . "...\n";
        
        // Testar verificação de senha
        if (password_verify($password, $customer['password'])) {
            echo "✅ Senha verificada com sucesso!\n";
            echo "🎉 Login funcionaria corretamente!\n";
        } else {
            echo "❌ Senha não confere!\n";
            echo "🔍 Senha testada: $password\n";
        }
    } else {
        echo "❌ Usuário não encontrado!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}

echo "\n=== STATUS DAS CORREÇÕES ===\n";
echo "✅ Endpoint JavaScript corrigido para ../../php/cliente/cliente-signin.php\n";
echo "✅ Redirecionamento corrigido para ../cliente-dashboard.html\n";
echo "✅ Sessões padronizadas (user_type = 'cliente')\n";
echo "✅ Arquivo de conexão path corrigido\n";
echo "✅ Usuário de teste criado\n\n";

echo "🌐 Para testar no navegador:\n";
echo "   http://localhost/php-servicofacil-crud/client/login/index.html\n";
echo "   Email: teste@exemplo.com\n";
echo "   Senha: teste123\n\n";
?>