<?php
require_once 'php/conexao.php';

echo "=== CRIANDO USUÁRIO DE TESTE ===\n\n";

try {
    // Dados do usuário de teste
    $email = 'teste@exemplo.com';
    $password = 'teste123';
    $name = 'Usuario Teste';
    $phone = '11999999999';
    
    // Verificar se já existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetchColumn() > 0) {
        echo "✅ Usuário de teste já existe!\n";
        echo "📧 Email: $email\n";
        echo "🔑 Senha: $password\n\n";
        
        // Verificar dados na tabela cliente
        $stmt = $pdo->prepare("
            SELECT c.id, c.email, u.name 
            FROM cliente c 
            INNER JOIN user u ON c.user_id = u.user_id 
            WHERE c.email = ?
        ");
        $stmt->execute([$email]);
        $cliente = $stmt->fetch();
        
        if ($cliente) {
            echo "✅ Cliente encontrado:\n";
            echo "   ID: {$cliente['id']}\n";
            echo "   Nome: {$cliente['name']}\n";
            echo "   Email: {$cliente['email']}\n\n";
        } else {
            echo "❌ Cliente não encontrado na tabela cliente\n\n";
        }
        
        exit;
    }
    
    // Iniciar transação
    $pdo->beginTransaction();
    
    // Hash da senha
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Inserir na tabela user
    $stmt = $pdo->prepare("
        INSERT INTO user (email, password, name, phone_number, identity_verified) 
        VALUES (?, ?, ?, ?, FALSE)
    ");
    $stmt->execute([$email, $hashed_password, $name, $phone]);
    $user_id = $pdo->lastInsertId();
    
    // Inserir na tabela cliente
    $stmt = $pdo->prepare("
        INSERT INTO cliente (user_id, email, senha) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$user_id, $email, $hashed_password]);
    $cliente_id = $pdo->lastInsertId();
    
    // Confirmar transação
    $pdo->commit();
    
    echo "✅ Usuário de teste criado com sucesso!\n\n";
    echo "📋 DADOS PARA LOGIN:\n";
    echo "📧 Email: $email\n";
    echo "🔑 Senha: $password\n";
    echo "👤 Nome: $name\n";
    echo "🆔 User ID: $user_id\n";
    echo "🆔 Cliente ID: $cliente_id\n\n";
    
    echo "🔗 Para testar:\n";
    echo "   http://localhost/php-servicofacil-crud/client/login/index.html\n\n";
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>