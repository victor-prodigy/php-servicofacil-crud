<?php
// ✏️ EDITAR POSTAGEM DE SERVIÇO - Clean Code
session_start();
require_once '../conexao.php';

// 🔒 Verificar se o usuário está logado e é um prestador
if (!isset($_SESSION['prestador_id']) || $_SESSION['usuario_tipo'] !== 'prestador') {
    enviarResposta(false, "Acesso não autorizado");
}

// 📝 Função para validar dados do serviço
function validarDadosServico($dados)
{
    $erros = [];

    if (empty($dados['titulo']) || strlen($dados['titulo']) < 5) {
        $erros[] = "Título deve ter pelo menos 5 caracteres";
    }

    if (empty($dados['descricao']) || strlen($dados['descricao']) < 10) {
        $erros[] = "Descrição deve ter pelo menos 10 caracteres";
    }

    if (empty($dados['categoria'])) {
        $erros[] = "Categoria é obrigatória";
    }

    if (empty($dados['preco']) || !is_numeric($dados['preco']) || floatval($dados['preco']) <= 0) {
        $erros[] = "Preço deve ser um valor numérico maior que zero";
    }

    if (empty($dados['disponibilidade']) || !in_array($dados['disponibilidade'], ['disponivel', 'indisponivel'])) {
        $erros[] = "Disponibilidade inválida";
    }

    return $erros;
}

// 📤 Função para enviar resposta
function enviarResposta($success, $message, $data = [])
{
    header('Content-Type: application/json');
    $resposta = [
        'success' => $success,
        'message' => $message
    ];

    if (!empty($data)) {
        $resposta = array_merge($resposta, $data);
    }

    echo json_encode($resposta);
    exit;
}

// 🚀 PROCESSO PRINCIPAL
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 📋 Coleta de dados
        $service_id = $_POST['service_id'] ?? null;
        $dados = [
            'titulo' => $_POST['titulo'] ?? '',
            'descricao' => $_POST['descricao'] ?? '',
            'categoria' => $_POST['categoria'] ?? '',
            'preco' => $_POST['preco'] ?? '',
            'disponibilidade' => $_POST['disponibilidade'] ?? 'disponivel'
        ];

        // ✅ Validação
        if (empty($service_id)) {
            enviarResposta(false, "ID do serviço é obrigatório");
        }

        $erros = validarDadosServico($dados);
        if (!empty($erros)) {
            enviarResposta(false, implode(', ', $erros));
        }

        // 🔒 Verificar se o serviço pertence ao prestador
        $prestador_id = $_SESSION['prestador_id'];
        
        // Se prestador_id não estiver na sessão, tentar buscar pelo user_id
        if (empty($prestador_id) && isset($_SESSION['usuario_id'])) {
            $sql_buscar = "SELECT service_provider_id FROM service_provider WHERE user_id = ?";
            $stmt_buscar = $pdo->prepare($sql_buscar);
            $stmt_buscar->execute([$_SESSION['usuario_id']]);
            $prestador_buscado = $stmt_buscar->fetch();
            
            if ($prestador_buscado) {
                $prestador_id = $prestador_buscado['service_provider_id'];
                $_SESSION['prestador_id'] = $prestador_id; // Atualizar sessão
            }
        }
        
        if (empty($prestador_id)) {
            enviarResposta(false, "Erro: ID do prestador não encontrado. Por favor, faça login novamente como prestador.");
        }
        
        $sql_check = "SELECT service_id FROM provider_service WHERE service_id = ? AND service_provider_id = ?";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([$service_id, $prestador_id]);
        
        if (!$stmt_check->fetch()) {
            enviarResposta(false, "Serviço não encontrado ou você não tem permissão para editá-lo");
        }

        // 💾 Preparar dados para atualização
        $preco = floatval($dados['preco']);

        // 🏗️ Atualizar no banco
        $sql = "UPDATE provider_service 
                SET titulo = ?, descricao = ?, categoria = ?, preco = ?, disponibilidade = ?, updated_at = NOW()
                WHERE service_id = ? AND service_provider_id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $dados['titulo'],
            $dados['descricao'],
            $dados['categoria'],
            $preco,
            $dados['disponibilidade'],
            $service_id,
            $prestador_id
        ]);

        // ✨ Sucesso
        enviarResposta(true, "Postagem de serviço atualizada com sucesso!");
    } catch (Exception $e) {
        enviarResposta(false, "Erro interno: " . $e->getMessage());
    }
} else {
    enviarResposta(false, "Método não permitido");
}

