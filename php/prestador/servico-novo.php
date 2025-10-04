<?php
// php/prestador/servico-novo.php
// VERSÃO PARA TESTES (sem validação de sessão). REMOVA EM PRODUÇÃO.

require_once __DIR__ . '/../conexao.php'; // Caminho relativo ajustado

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Para teste: pegar qualquer prestador existente
        $fallback = $conn->query("SELECT service_provider_id, user_id FROM service_provider LIMIT 1");
        if ($fallback && $fallback->num_rows > 0) {
            $row = $fallback->fetch_assoc();
            $service_provider_id = (int) $row['service_provider_id'];
            $user_id = (int) $row['user_id'];
        } else {
            throw new Exception("Nenhum prestador encontrado no banco. Crie um registro em service_provider para testes.");
        }

        // Obter e limpar dados do formulário
        $titulo = trim(filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS));
        $descricao = trim(filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS));
        $categoria = trim(filter_input(INPUT_POST, 'categoria', FILTER_SANITIZE_SPECIAL_CHARS));
        $localizacao = trim(filter_input(INPUT_POST, 'localizacao', FILTER_SANITIZE_SPECIAL_CHARS));
        $orcamento = filter_input(INPUT_POST, 'orcamento', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $prazo = trim(filter_input(INPUT_POST, 'prazo', FILTER_SANITIZE_SPECIAL_CHARS));

        // Validar campos obrigatórios (básico)
        if ($titulo === '' || $descricao === '' || $categoria === '' || $localizacao === '' || $orcamento === '' || $prazo === '') {
            header('Location: ../../client/servico/servico-novo.html?error=empty_fields');
            exit;
        }

        // Inserir na tabela service
        $query = "INSERT INTO service 
                    (service_provider_id, title, description, category, price, estimated_time, location, status, created_at)
                  VALUES (?, ?, ?, ?, ?, ?, ?, 'ativo', NOW())";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Erro ao preparar INSERT: " . $conn->error);
        }

        // Tipos: i (int) s s s d (double) s s
        $stmt->bind_param(
            "isssdss",
            $service_provider_id,
            $titulo,
            $descricao,
            $categoria,
            $orcamento,
            $prazo,
            $localizacao
        );

        if ($stmt->execute()) {
            $insert_id = $stmt->insert_id;
            $stmt->close();
            $conn->close();
            // Redirecionar para dashboard do prestador (com flag)
            header('Location: ../../client/prestador-dashboard.html?success=1&id=' . $insert_id);
            exit;
        } else {
            throw new Exception("Erro ao executar INSERT: " . $stmt->error);
        }

    } catch (Exception $e) {
        if (isset($stmt) && $stmt) $stmt->close();
        if (isset($conn) && $conn) $conn->close();
        error_log("Erro ao criar serviço (TEST): " . $e->getMessage());
        header('Location: ../../client/servico/servico-novo.html?error=exception&msg=' . urlencode($e->getMessage()));
        exit;
    }
} else {
    // Método não permitido
    header('Location: ../../client/servico/servico-novo.html?error=invalid_method');
    exit;
}
?>
