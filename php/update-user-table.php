<?php
/**
 * Script para atualizar estrutura da tabela user
 * Adiciona colunas user_type e status
 */

require_once 'conexao.php';

try {
    echo "🔄 Atualizando estrutura da tabela 'user'...\n\n";
    
    // Verificar se as colunas já existem
    $check_columns = "SHOW COLUMNS FROM user LIKE 'user_type'";
    $result = $pdo->query($check_columns);
    
    if ($result->rowCount() == 0) {
        // Adicionar coluna user_type
        echo "➕ Adicionando coluna 'user_type'...\n";
        $sql1 = "ALTER TABLE user ADD COLUMN user_type ENUM('cliente', 'prestador', 'administrador') DEFAULT 'cliente' AFTER phone_number";
        $pdo->exec($sql1);
        echo "✅ Coluna 'user_type' adicionada com sucesso!\n";
    } else {
        echo "ℹ️ Coluna 'user_type' já existe.\n";
    }
    
    // Verificar coluna status
    $check_status = "SHOW COLUMNS FROM user LIKE 'status'";
    $result2 = $pdo->query($check_status);
    
    if ($result2->rowCount() == 0) {
        // Adicionar coluna status
        echo "➕ Adicionando coluna 'status'...\n";
        $sql2 = "ALTER TABLE user ADD COLUMN status ENUM('ativo', 'inativo') DEFAULT 'ativo' AFTER user_type";
        $pdo->exec($sql2);
        echo "✅ Coluna 'status' adicionada com sucesso!\n";
    } else {
        echo "ℹ️ Coluna 'status' já existe.\n";
    }
    
    // Mostrar estrutura atualizada
    echo "\n📋 Estrutura atual da tabela 'user':\n";
    $show_structure = "DESCRIBE user";
    $structure = $pdo->query($show_structure);
    
    while ($row = $structure->fetch(PDO::FETCH_ASSOC)) {
        echo "   {$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Default']}\n";
    }
    
    echo "\n✅ Estrutura da tabela atualizada com sucesso!\n";
    echo "🔄 Agora você pode executar o script create-admin.php\n";
    
} catch (PDOException $e) {
    echo "❌ Erro ao atualizar estrutura: " . $e->getMessage() . "\n";
}
?>