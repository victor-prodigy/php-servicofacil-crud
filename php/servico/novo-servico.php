<?php
// 📝 CRIAR NOVA SOLICITAÇÃO DE SERVIÇO - Clean Code
session_start();
require_once '../conexao.php';

// 🔒 Verificar se o usuário está logado e é um cliente
if (!isset($_SESSION['cliente_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
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

    if (empty($dados['localizacao'])) {
        $erros[] = "Localização é obrigatória";
    }

    if (!empty($dados['orcamento']) && !is_numeric($dados['orcamento'])) {
        $erros[] = "Orçamento deve ser um valor numérico";
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
        $dados = [
            'titulo' => $_POST['titulo'] ?? '',
            'descricao' => $_POST['descricao'] ?? '',
            'categoria' => $_POST['categoria'] ?? '',
            'orcamento' => $_POST['orcamento'] ?? null,
            'prazo' => $_POST['prazo'] ?? null,
            'localizacao' => $_POST['localizacao'] ?? ''
        ];

        // ✅ Validação
        $erros = validarDadosServico($dados);
        if (!empty($erros)) {
            enviarResposta(false, implode(', ', $erros));
        }

        // 💾 Preparar dados para inserção
        $cliente_id = $_SESSION['cliente_id'];
        $orcamento = !empty($dados['orcamento']) ? floatval($dados['orcamento']) : null;
        $prazo = !empty($dados['prazo']) ? $dados['prazo'] : null;

        // 🏗️ Inserir no banco
        $sql = "INSERT INTO servicos (cliente_id, titulo, descricao, categoria, orcamento, prazo, localizacao, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'aberto')";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $cliente_id,
            $dados['titulo'],
            $dados['descricao'],
            $dados['categoria'],
            $orcamento,
            $prazo,
            $dados['localizacao']
        ]);

        // ✨ Sucesso
        $servico_id = $pdo->lastInsertId();
        enviarResposta(true, "Solicitação de serviço criada com sucesso!", [
            'servico_id' => $servico_id
        ]);
    } catch (Exception $e) {
        enviarResposta(false, "Erro interno: " . $e->getMessage());
    }
} else {
    enviarResposta(false, "Método não permitido");
}
