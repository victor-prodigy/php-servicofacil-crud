<?php
session_start();
require_once '../../conexao.php';

// Verificar se o usuário está logado e é um cliente
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'cliente') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $prestador_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

    try {
        // Buscar dados básicos do prestador
        $query = "
            SELECT 
                p.*,
                COALESCE(AVG(a.nota), 0) as avaliacao_media,
                COUNT(DISTINCT a.id) as total_avaliacoes
            FROM prestadores p
            LEFT JOIN avaliacoes a ON p.id = a.prestador_id
            WHERE p.id = :id
            GROUP BY p.id
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', $prestador_id);
        $stmt->execute();
        
        $prestador = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$prestador) {
            throw new Error('Prestador não encontrado');
        }

        // Buscar certificações
        $query = "SELECT * FROM certificacoes WHERE prestador_id = :prestador_id";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':prestador_id', $prestador_id);
        $stmt->execute();
        $prestador['certificacoes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Buscar portfólio
        $query = "SELECT * FROM portfolio WHERE prestador_id = :prestador_id";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':prestador_id', $prestador_id);
        $stmt->execute();
        $prestador['portfolio'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Buscar avaliações com dados do cliente
        $query = "
            SELECT 
                a.*,
                c.nome as cliente_nome
            FROM avaliacoes a
            JOIN clientes c ON a.cliente_id = c.id
            WHERE a.prestador_id = :prestador_id
            ORDER BY a.data DESC
            LIMIT 10
        ";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':prestador_id', $prestador_id);
        $stmt->execute();
        $prestador['avaliacoes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Formatar dados numéricos
        $prestador['avaliacao_media'] = floatval($prestador['avaliacao_media']);
        $prestador['preco_medio'] = floatval($prestador['preco_medio']);
        $prestador['certificado'] = (bool)$prestador['certificado'];

        echo json_encode([
            'success' => true,
            'prestador' => $prestador
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao buscar detalhes do prestador: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}
?>