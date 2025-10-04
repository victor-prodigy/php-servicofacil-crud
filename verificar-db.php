<?php
require_once 'php/conexao.php';

echo "=== VERIFICAÇÃO DO BANCO DE DADOS ===\n\n";

// Verificar se o banco existe e as tabelas estão criadas
$tables = ['user', 'cliente', 'service_provider', 'solicitacoes_servico'];

foreach ($tables as $table) {
    try {
        $result = $conexao->query("SELECT COUNT(*) as total FROM $table");
        $row = $result->fetch_assoc();
        echo "✅ Tabela '$table': {$row['total']} registros\n";
    } catch (Exception $e) {
        echo "❌ Tabela '$table': " . $e->getMessage() . "\n";
    }
}

echo "\n=== ESTRUTURA DA TABELA CLIENTE ===\n";
try {
    $result = $conexao->query("DESCRIBE cliente");
    while ($row = $result->fetch_assoc()) {
        echo "- {$row['Field']} ({$row['Type']})\n";
    }
} catch (Exception $e) {
    echo "❌ Erro ao verificar estrutura: " . $e->getMessage() . "\n";
}

echo "\n=== ESTRUTURA DA TABELA SOLICITACOES_SERVICO ===\n";
try {
    $result = $conexao->query("DESCRIBE solicitacoes_servico");
    while ($row = $result->fetch_assoc()) {
        echo "- {$row['Field']} ({$row['Type']})\n";
    }
} catch (Exception $e) {
    echo "❌ Erro ao verificar estrutura: " . $e->getMessage() . "\n";
}

$conexao->close();
?>