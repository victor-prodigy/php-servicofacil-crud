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
                    s.preco as orcamento,
                    s.prazo,
                    s.localizacao,
                    s.status,
                    s.data_postagem,
                    u.name as prestador_nome,
                    sp.specialty as prestador_especialidade
                  FROM servicos s
                  INNER JOIN service_provider sp ON s.prestador_id = sp.service_provider_id
                  INNER JOIN user u ON sp.user_id = u.user_id
                  WHERE s.status = 'ativo'
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
        // PRESTADORES: Gerenciam apenas seus próprios serviços
        // Primeiro, encontrar o service_provider_id do usuário logado
        $findProvider = "SELECT service_provider_id FROM service_provider 
                        INNER JOIN user ON service_provider.user_id = user.user_id 
                        WHERE user.user_id = (SELECT user_id FROM user WHERE user_id = ?)";
        
        $stmt = $conn->prepare($findProvider);
        $stmt->bind_param("i", $_SESSION['usuario_id']);
        $stmt->execute();
        $providerResult = $stmt->get_result();
        
        if ($providerResult->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Prestador não encontrado']);
            exit;
        }
        
        $providerRow = $providerResult->fetch_assoc();
        $prestador_id = $providerRow['service_provider_id'];
        
        // Buscar serviços do prestador logado
        $query = "SELECT 
                    id,
                    titulo,
                    categoria,
                    descricao,
                    preco as orcamento,
                    prazo,
                    localizacao,
                    status,
                    data_postagem
                  FROM servicos 
                  WHERE prestador_id = ? 
                  ORDER BY data_postagem DESC";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $prestador_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $servicos = [];
        while ($row = $result->fetch_assoc()) {
            $servicos[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'servicos' => $servicos,
            'tipo' => 'gerenciamento'
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