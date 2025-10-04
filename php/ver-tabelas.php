<?php
require_once 'php/conexao.php';

echo "=== LISTANDO TABELAS ===\n";
$result = $conn->query('SHOW TABLES');
while ($row = $result->fetch_row()) {
    echo "- " . $row[0] . "\n";
}
?>