<?php
session_start();
require_once __DIR__ . '/../conexao.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_tipo'])) {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

try {
    $usuario_tipo = $_SESSION['usuario_tipo'];

    if ($usuario_tipo === 'cliente') {
        // CLIENTES: Visualizam todos os serviços publicados pelos prestadores (para contratar)
        $query = "SELECT 
                    s.id,
                    s.titulo,
                    s.categoria,
                    s.descricao,
                    s.orcamento,
                    s.prazo,
                    s.localizacao,
                    s.status,
                    s.data_postagem,
                    u.name as cliente_nome
                  FROM servicos s
                  INNER JOIN cliente c ON s.cliente_id = c.id
                  INNER JOIN user u ON c.user_id = u.user_id
                  WHERE s.status = 'aberto'
                  ORDER BY s.data_postagem DESC";

        $result = $conn->query($query);
        $servicos = [];

        while ($row = $result->fetch_assoc()) {
            $servicos[] = $row;
        }

        echo json_encode([
            'success' => true,
            'servicos' => $servicos,
            'tipo' => 'visualizacao'
        ]);
    } else if ($usuario_tipo === 'prestador') {
        // PRESTADORES: Visualizam solicitações de serviços dos clientes (service_request)
        $query = "SELECT 
                    sr.request_id as id,
                    sr.titulo,
                    sr.categoria,
                    sr.descricao,
                    sr.orcamento_maximo as orcamento,
                    sr.prazo_desejado as prazo,
                    CONCAT(sr.endereco, ', ', sr.cidade) as localizacao,
                    sr.status,
                    sr.created_at as data_postagem,
                    u.name as cliente_nome
                  FROM service_request sr
                  INNER JOIN cliente c ON sr.cliente_id = c.id
                  INNER JOIN user u ON c.user_id = u.user_id
                  WHERE sr.status = 'pendente'
                  ORDER BY sr.created_at DESC";

        $result = $conn->query($query);
        $servicos = [];
        while ($row = $result->fetch_assoc()) {
            $servicos[] = $row;
        }

        echo json_encode([
            'success' => true,
            'servicos' => $servicos,
            'tipo' => 'solicitacoes'
        ]);
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
