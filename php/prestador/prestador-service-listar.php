<?php
require_once __DIR__ . '/../conexao.php';

header('Content-Type: application/json');

try {
    // Seleciona todos os serviços (sem filtro de prestador)
    $sql = "SELECT 
                service_id AS id,
                title AS titulo,
                category AS categoria,
                price AS orcamento,
                status,
                created_at AS data_postagem,
                location AS localizacao
            FROM service
            ORDER BY created_at DESC";

    $result = $conn->query($sql);

    $servicos = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $servicos[] = $row;
        }
    }

    echo json_encode([
        'success' => true,
        'servicos' => $servicos
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao listar serviços: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
