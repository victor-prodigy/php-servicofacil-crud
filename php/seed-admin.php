<?php

/**
 * Script para criar seed do usuário Administrador
 * ServiçoFácil - Sistema de Gestão de Serviços
 * 
 * Uso: php seed-admin.php
 */

// Incluir arquivo de conexão
require_once __DIR__ . '/conexao.php';

try {
    // Dados do administrador
    $admin_email = 'admin@servicofacil.com';
    $admin_password = 'admin123';
    $admin_name = 'Administrador do Sistema';
    $admin_phone = '(11) 99999-9999';

    // Criptografar senha
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

    echo "========================================\n";
    echo "SEED - USUÁRIO ADMINISTRADOR\n";
    echo "========================================\n\n";

    // Verificar se já existe
    $check_sql = "SELECT user_id, email, name, status FROM user WHERE email = ? OR user_type = 'administrador' LIMIT 1";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$admin_email]);
    $existing = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        echo "⚠️  Administrador já existe no banco de dados!\n\n";
        echo "📋 Dados do administrador existente:\n";
        echo "   ID: " . $existing['user_id'] . "\n";
        echo "   Email: " . $existing['email'] . "\n";
        echo "   Nome: " . $existing['name'] . "\n";
        echo "   Status: " . $existing['status'] . "\n\n";

        // Perguntar se deseja atualizar
        echo "Deseja atualizar o administrador? (s/n): ";
        $handle = fopen("php://stdin", "r");
        $line = trim(fgets($handle));
        fclose($handle);

        if (strtolower($line) !== 's' && strtolower($line) !== 'sim') {
            echo "\n❌ Operação cancelada.\n";
            exit(0);
        }

        // Atualizar administrador existente
        $update_sql = "UPDATE user SET 
            password = ?,
            name = ?,
            phone_number = ?,
            status = 'ativo',
            identity_verified = 1,
            updated_at = NOW()
            WHERE user_id = ?";

        $update_stmt = $pdo->prepare($update_sql);
        $result = $update_stmt->execute([
            $hashed_password,
            $admin_name,
            $admin_phone,
            $existing['user_id']
        ]);

        if ($result) {
            echo "\n✅ Administrador atualizado com sucesso!\n";
            echo "\n📋 Dados atualizados:\n";
            echo "   ID: " . $existing['user_id'] . "\n";
            echo "   Email: $admin_email\n";
            echo "   Nome: $admin_name\n";
            echo "   Telefone: $admin_phone\n";
            echo "   Senha: $admin_password\n";
        } else {
            echo "\n❌ Erro ao atualizar administrador!\n";
            exit(1);
        }
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
        } else {
            echo "❌ Erro ao criar administrador!\n";
            exit(1);
        }
    }

    // Exibir informações de acesso
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "CREDENCIAIS DE ACESSO:\n";
    echo str_repeat("=", 50) . "\n";
    echo "Email: $admin_email\n";
    echo "Senha: $admin_password\n\n";

    echo "URLs DE ACESSO:\n";
    echo str_repeat("-", 50) . "\n";
    echo "Login: http://localhost/php-servicofacil-crud-incompleto/client/login/administrador-signin.html\n";
    echo "Dashboard: http://localhost/php-servicofacil-crud-incompleto/client/administrador-dashboard.html\n\n";

    echo "HASH DA SENHA (para uso em SQL):\n";
    echo str_repeat("-", 50) . "\n";
    echo "$hashed_password\n\n";

    echo "✅ Seed executado com sucesso!\n";
    echo str_repeat("=", 50) . "\n";

} catch (PDOException $e) {
    echo "\n❌ Erro de banco de dados: " . $e->getMessage() . "\n";

    // Verificar se o problema é estrutura da tabela
    if (strpos($e->getMessage(), 'user_type') !== false || strpos($e->getMessage(), 'status') !== false) {
        echo "\n⚠️  Parece que a tabela 'user' não tem as colunas necessárias.\n";
        echo "   Execute o script de criação de tabelas primeiro:\n";
        echo "   php create-tables.php\n\n";
    }
    exit(1);
} catch (Exception $e) {
    echo "\n❌ Erro geral: " . $e->getMessage() . "\n";
    exit(1);
}

