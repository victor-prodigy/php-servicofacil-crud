<?php
require_once 'php/conexao.php';

echo "=== ADAPTANDO TABELA SERVICE_REQUEST ===\n\n";

try {
    echo "🔧 Adicionando novos campos à tabela service_request...\n\n";
    
    // Lista de alterações necessárias
    $alteracoes = [
        "ADD COLUMN `titulo` VARCHAR(100) NOT NULL AFTER `cliente_id`",
        "ADD COLUMN `categoria` VARCHAR(50) NOT NULL AFTER `titulo`", 
        "ADD COLUMN `descricao` TEXT NOT NULL AFTER `categoria`",
        "ADD COLUMN `endereco` VARCHAR(200) NOT NULL AFTER `descricao`",
        "ADD COLUMN `cidade` VARCHAR(100) NOT NULL AFTER `endereco`",
        "ADD COLUMN `prazo_desejado` VARCHAR(50) NOT NULL AFTER `cidade`",
        "ADD COLUMN `observacoes` TEXT DEFAULT NULL AFTER `budget`",
        "ADD COLUMN `status` ENUM('pendente', 'em_andamento', 'concluido', 'cancelado') DEFAULT 'pendente' AFTER `observacoes`",
        "ADD COLUMN `data_atualizacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`",
        "CHANGE `budget` `orcamento_maximo` DECIMAL(10,2) DEFAULT NULL"
    ];
    
    foreach ($alteracoes as $index => $alteracao) {
        try {
            $sql = "ALTER TABLE service_request $alteracao";
            echo "Executando: $alteracao\n";
            
            if ($conexao->query($sql)) {
                echo "✅ Sucesso!\n";
            } else {
                if (strpos($conexao->error, 'Duplicate column name') !== false) {
                    echo "ℹ️ Campo já existe - ignorando.\n";
                } else {
                    echo "⚠️ Erro: " . $conexao->error . "\n";
                }
            }
        } catch (Exception $e) {
            echo "⚠️ Erro na alteração: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
    
    echo "📋 Adicionando índices...\n";
    $indices = [
        "ADD INDEX `idx_categoria` (`categoria`)",
        "ADD INDEX `idx_status` (`status`)", 
        "ADD INDEX `idx_cidade` (`cidade`)",
        "ADD INDEX `idx_data_criacao` (`created_at`)"
    ];
    
    foreach ($indices as $indice) {
        try {
            $sql = "ALTER TABLE service_request $indice";
            echo "Executando: $indice\n";
            
            if ($conexao->query($sql)) {
                echo "✅ Índice criado!\n";
            } else {
                if (strpos($conexao->error, 'Duplicate key name') !== false) {
                    echo "ℹ️ Índice já existe - ignorando.\n";
                } else {
                    echo "⚠️ Erro: " . $conexao->error . "\n";
                }
            }
        } catch (Exception $e) {
            echo "⚠️ Erro no índice: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
    
    echo "✅ Adaptação da tabela service_request concluída!\n\n";
    
    // Verificar estrutura final
    echo "=== ESTRUTURA FINAL ===\n";
    $result = $conexao->query('DESCRIBE service_request');
    while ($row = $result->fetch_assoc()) {
        echo "- {$row['Field']} ({$row['Type']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}

$conexao->close();
?>