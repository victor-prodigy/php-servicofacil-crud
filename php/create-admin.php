<?php

/**
 * Script para criar usuário administrador
 * ServiçoFácil - Sistema de Gestão de Serviços
 */

// Incluir arquivo de conexão
require_once 'conexao.php';

try {
    // Dados do administrador
    $admin_email = 'admin@servicofacil.com';
    $admin_password = 'admin123';
    $admin_name = 'Administrador do Sistema';
    $admin_phone = '(11) 99999-9999';

    // Criptografar senha
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

    // Verificar se já existe
    $check_sql = "SELECT user_id FROM user WHERE email = ? OR user_type = 'administrador'";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$admin_email]);

    if ($check_stmt->rowCount() > 0) {
        echo "⚠️ Administrador já existe no banco de dados!\n";

        // Mostrar dados existentes
        $existing = $check_stmt->fetch(PDO::FETCH_ASSOC);
        $admin_sql = "SELECT * FROM user WHERE user_type = 'administrador' LIMIT 1";
        $admin_stmt = $pdo->prepare($admin_sql);
        $admin_stmt->execute();
        $admin_data = $admin_stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin_data) {
            echo "\n📋 Dados do administrador existente:\n";
            echo "   ID: " . $admin_data['user_id'] . "\n";
            echo "   Email: " . $admin_data['email'] . "\n";
            echo "   Nome: " . $admin_data['name'] . "\n";
            echo "   Telefone: " . $admin_data['phone_number'] . "\n";
            echo "   Status: " . $admin_data['status'] . "\n";
            echo "   Criado em: " . $admin_data['created_at'] . "\n";
        }

        echo "\n🔄 Para recriar o administrador, execute o comando com parâmetro 'reset':\n";
        echo "   php create-admin.php reset\n";
    } else {
        // Inserir novo administrador
        $insert_sql = "INSERT INTO user (
            email, 
            password, 
            name, 
            phone_number, 
            user_type, 
            status, 
            identity_verified, 
            created_at, 
            updated_at
        ) VALUES (?, ?, ?, ?, 'administrador', 'ativo', 1, NOW(), NOW())";

        $insert_stmt = $pdo->prepare($insert_sql);
        $result = $insert_stmt->execute([
            $admin_email,
            $hashed_password,
            $admin_name,
            $admin_phone
        ]);

        if ($result) {
            $admin_id = $pdo->lastInsertId();
            echo "✅ Administrador criado com sucesso!\n";
            echo "\n📋 Dados do administrador:\n";
            echo "   ID: $admin_id\n";
            echo "   Email: $admin_email\n";
            echo "   Nome: $admin_name\n";
            echo "   Telefone: $admin_phone\n";
            echo "   Senha: $admin_password\n";
            echo "\n🔗 URLs de acesso:\n";
            echo "   Login: http://localhost/php-servicofacil-crud-incompleto/client/login/admin-signin.html\n";
            echo "   Dashboard: http://localhost/php-servicofacil-crud-incompleto/client/administrador-dashboard.html\n";
        } else {
            echo "❌ Erro ao criar administrador!\n";
        }
    }

    // Se parâmetro 'reset' foi passado, resetar o administrador
    if (isset($argv[1]) && $argv[1] === 'reset') {
        echo "\n🔄 Resetando administrador...\n";

        // Deletar administrador existente
        $delete_sql = "DELETE FROM user WHERE user_type = 'administrador'";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute();

        // Inserir novo administrador
        $insert_stmt = $pdo->prepare($insert_sql);
        $result = $insert_stmt->execute([
            $admin_email,
            $hashed_password,
            $admin_name,
            $admin_phone
        ]);

        if ($result) {
            $admin_id = $pdo->lastInsertId();
            echo "✅ Administrador resetado com sucesso!\n";
            echo "\n📋 Novos dados do administrador:\n";
            echo "   ID: $admin_id\n";
            echo "   Email: $admin_email\n";
            echo "   Nome: $admin_name\n";
            echo "   Telefone: $admin_phone\n";
            echo "   Senha: $admin_password\n";
        } else {
            echo "❌ Erro ao resetar administrador!\n";
        }
    }
} catch (PDOException $e) {
    echo "❌ Erro de banco de dados: " . $e->getMessage() . "\n";

    // Verificar se o problema é estrutura da tabela
    if (strpos($e->getMessage(), 'user_type') !== false) {
        echo "\n⚠️ Parece que a tabela 'user' não tem as colunas 'user_type' e 'status'.\n";
        echo "   Execute o comando SQL para atualizar a estrutura:\n\n";
        echo "   ALTER TABLE user ADD COLUMN user_type ENUM('cliente', 'prestador', 'administrador') DEFAULT 'cliente';\n";
        echo "   ALTER TABLE user ADD COLUMN status ENUM('ativo', 'inativo') DEFAULT 'ativo';\n\n";
        echo "   Depois execute este script novamente.\n";
    }
} catch (Exception $e) {
    echo "❌ Erro geral: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Script finalizado.\n";
echo str_repeat("=", 50) . "\n";
