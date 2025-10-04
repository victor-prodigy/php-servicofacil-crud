<?php
require_once 'php/conexao.php';

echo "=== ESTRUTURA DA TABELA SERVICE_PROVIDER ===\n";
try {
    $result = $conn->query('DESCRIBE service_provider');
    while($row = $result->fetch_assoc()) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}

echo "\n=== DADOS DA TABELA SERVICE_PROVIDER ===\n";
try {
    $result = $conn->query('SELECT * FROM service_provider LIMIT 5');
    $count = 0;
    while($row = $result->fetch_assoc()) {
        $count++;
        echo "Registro $count: " . print_r($row, true) . "\n";
    }
    if ($count == 0) {
        echo "Nenhum registro encontrado.\n";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>