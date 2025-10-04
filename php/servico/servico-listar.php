<?php
session_start();
require_once __DIR__ . '/../conexao.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_tipo'])) {
        echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
        exit;
    }

    $usuario_tipo = $_SESSION['usuario_tipo'];

    if ($usuario_tipo === 'cliente') {
        // Mostrar todos os serviços (propostas) disponíveis para contratação
        $sql = "
            SELECT 
                p.proposal_id,
                sr.request_id,
                sr.service_type AS titulo,
                sr.location AS localizacao,
                sr.budget AS preco,
                sr.deadline AS prazo,
                sp.service_provider_id,
                u.name AS prestador_nome,
                sp.specialty AS prestador_especialidade,
                p.amount AS valor_proposta,
                p.message AS descricao,
                p.submitted_at AS data_postagem,
                p.estimate AS status
            FROM proposal p
            INNER JOIN service_request sr ON p.request_id = sr.request_id
            INNER JOIN service_provider sp ON p.service_provider_id = sp.service_provider_id
            INNER JOIN user u ON sp.user_id = u.user_id
            ORDER BY p.submitted_at DESC
        ";

        $result = $conn->query($sql);

        $servicos = [];
        while ($row = $result->fetch_assoc()) {
            $servicos[] = $row;
        }

        echo json_encode([
            'success' => true,
            'servicos' => $servicos,
        ]);

    } else if ($usuario_tipo === 'prestador') {
        // Prestador visualiza apenas suas próprias propostas
        $sql = "
            SELECT 
                p.proposal_id,
                sr.request_id,
                sr.service_type AS titulo,
                sr.location AS localizacao,
                sr.budget AS preco,
                sr.deadline AS prazo,
                sp.service_provider_id,
                u.name AS prestador_nome,
                sp.specialty AS prestador_especialidade,
                p.amount AS valor_proposta,
                p.message AS descricao,
                p.submitted_at AS data_postagem,
                p.estimate AS status
            FROM proposal p
            INNER JOIN service_request sr ON p.request_id = sr.request_id
            INNER JOIN service_provider sp ON p.service_provider_id = sp.service_provider_id
            INNER JOIN user u ON sp.user_id = u.user_id
            WHERE sp.user_id = ?
            ORDER BY p.submitted_at DESC
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_SESSION['usuario_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        $servicos = [];
        while ($row = $result->fetch_assoc()) {
            $servicos[] = $row;
        }

        echo json_encode([
            'success' => true,
            'servicos' => $servicos,
        ]);
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Tipo de usuário não reconhecido']);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar serviços: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
