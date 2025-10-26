<?php
session_start();
require_once '../../conexao.php';

// Verificar se o usuário está logado e é um cliente
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'cliente') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

try {
    // Obter parâmetros da busca
    $categoria = filter_input(INPUT_GET, 'categoria', FILTER_SANITIZE_STRING);
    $localizacao = filter_input(INPUT_GET, 'localizacao', FILTER_SANITIZE_STRING);
    $raio = filter_input(INPUT_GET, 'raio', FILTER_SANITIZE_NUMBER_INT);
    $avaliacaoMinima = filter_input(INPUT_GET, 'avaliacaoMinima', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $precoMinimo = filter_input(INPUT_GET, 'precoMinimo', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $precoMaximo = filter_input(INPUT_GET, 'precoMaximo', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $disponibilidade = filter_input(INPUT_GET, 'disponibilidade', FILTER_SANITIZE_STRING);
    $certificacoes = filter_input(INPUT_GET, 'certificacoes', FILTER_SANITIZE_STRING);

    // Construir a query base
    $query = "
        SELECT 
            p.id,
            p.nome,
            p.categoria,
            p.cidade,
            p.estado,
            p.preco_medio,
            p.disponibilidade as status_disponibilidade,
            p.certificado,
            COALESCE(AVG(a.nota), 0) as avaliacao_media,
            COUNT(a.id) as total_avaliacoes,
            -- Cálculo simplificado de distância (pode ser melhorado com geolocalização real)
            RAND() * :raio as distancia
        FROM prestadores p
        LEFT JOIN avaliacoes a ON p.id = a.prestador_id
        WHERE 1=1
    ";
    
    $params = [':raio' => $raio];

    // Adicionar filtros
    if ($categoria) {
        $query .= " AND p.categoria = :categoria";
        $params[':categoria'] = $categoria;
    }

    if ($localizacao) {
        $query .= " AND (p.cidade LIKE :localizacao OR p.estado LIKE :localizacao)";
        $params[':localizacao'] = "%$localizacao%";
    }

    if ($avaliacaoMinima) {
        $query .= " HAVING avaliacao_media >= :avaliacao_minima";
        $params[':avaliacao_minima'] = $avaliacaoMinima;
    }

    if ($precoMinimo) {
        $query .= " AND p.preco_medio >= :preco_minimo";
        $params[':preco_minimo'] = $precoMinimo;
    }

    if ($precoMaximo) {
        $query .= " AND p.preco_medio <= :preco_maximo";
        $params[':preco_maximo'] = $precoMaximo;
    }

    if ($disponibilidade) {
        $query .= " AND p.disponibilidade = :disponibilidade";
        $params[':disponibilidade'] = $disponibilidade;
    }

    if ($certificacoes === 'sim') {
        $query .= " AND p.certificado = 1";
    }

    // Agrupar para calcular média de avaliações
    $query .= " GROUP BY p.id";

    // Ordenar por avaliação e total de avaliações
    $query .= " ORDER BY avaliacao_media DESC, total_avaliacoes DESC";

    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    
    $prestadores = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Formatar dados antes de enviar
        $row['preco_medio'] = floatval($row['preco_medio']);
        $row['avaliacao_media'] = floatval($row['avaliacao_media']);
        $row['distancia'] = floatval($row['distancia']);
        $row['certificado'] = (bool)$row['certificado'];
        $prestadores[] = $row;
    }

    echo json_encode([
        'success' => true,
        'prestadores' => $prestadores
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar prestadores: ' . $e->getMessage()
    ]);
}
?>