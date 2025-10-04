<?php
require_once 'php/conexao.php';

echo "=== ESTRUTURA DA TABELA SERVICE_REQUEST ===\n\n";

try {
    $result = $conexao->query('DESCRIBE service_request');
    echo "Campos atuais:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- {$row['Field']} ({$row['Type']}) - {$row['Null']} - {$row['Default']}\n";
    }
    
    echo "\n=== DADOS ATUAIS ===\n";
    $result = $conexao->query('SELECT COUNT(*) as total FROM service_request');
    $row = $result->fetch_assoc();
    echo "Total de registros: {$row['total']}\n";
    
    if ($row['total'] > 0) {
        echo "\nPrimeiros 3 registros:\n";
        $result = $conexao->query('SELECT * FROM service_request LIMIT 3');
        while ($row = $result->fetch_assoc()) {
            echo "ID: {$row['request_id']} - Tipo: {$row['service_type']} - Local: {$row['location']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}

$conexao->close();
?>