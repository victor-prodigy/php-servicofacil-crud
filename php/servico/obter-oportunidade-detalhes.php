<?php
/**
 * 🔍 OBTER DETALHES DE OPORTUNIDADE
 * Retorna informações completas de uma oportunidade para o prestador
 */

session_start();
require_once __DIR__ . '/../conexao.php';

// 📤 Função para enviar resposta JSON
function enviarResposta($success, $message, $data = [])
{
    header('Content-Type: application/json; charset=utf-8');
    $resposta = [
        'success' => $success,
        'message' => $message
    ];

    if (!empty($data)) {
        $resposta = array_merge($resposta, $data);
    }

    echo json_encode($resposta, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // 🔐 Verificar se usuário está logado como prestador
    if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'prestador') {
        enviarResposta(false, 'Acesso negado. Faça login como prestador.');
    }

    // 📝 Validar parâmetros
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        enviarResposta(false, 'ID da oportunidade é obrigatório.');
    }

    $oportunidadeId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($oportunidadeId === false || $oportunidadeId <= 0) {
        enviarResposta(false, 'ID da oportunidade inválido.');
    }

    $prestadorId = $_SESSION['prestador_id'] ?? null;
    
    // Se prestador_id não estiver na sessão, tentar buscar pelo user_id
    if (empty($prestadorId) && isset($_SESSION['usuario_id'])) {
        $sql_buscar = "SELECT service_provider_id FROM service_provider WHERE user_id = ?";
        $stmt_buscar = $pdo->prepare($sql_buscar);
        $stmt_buscar->execute([$_SESSION['usuario_id']]);
        $prestador_buscado = $stmt_buscar->fetch();
        
        if ($prestador_buscado) {
            $prestadorId = $prestador_buscado['service_provider_id'];
            $_SESSION['prestador_id'] = $prestadorId; // Atualizar sessão
        }
    }
    
    if (empty($prestadorId)) {
        enviarResposta(false, 'Prestador não encontrado. Faça login novamente.');
    }

    // 🔍 Buscar detalhes da oportunidade
    $sql = "SELECT 
                sr.request_id as id,
                sr.titulo,
                sr.categoria,
                sr.descricao,
                sr.endereco,
                sr.cidade,
                sr.prazo_desejado,
                sr.orcamento_maximo,
                sr.observacoes,
                sr.status,
                sr.created_at as data_criacao,
                u.name as cliente_nome,
                u.email as cliente_email,
                u.phone_number as cliente_telefone,
                CASE 
                    WHEN p.proposal_id IS NOT NULL THEN 1 
                    ELSE 0 
                END as ja_enviou_proposta,
                p.proposal_id,
                p.amount as valor_proposta_enviada,
                p.estimate as prazo_proposta_enviada
            FROM service_request sr
            INNER JOIN cliente c ON sr.cliente_id = c.id
            INNER JOIN user u ON c.user_id = u.user_id
            LEFT JOIN proposal p ON sr.request_id = p.request_id AND p.service_provider_id = ?
            WHERE sr.request_id = ? AND sr.status = 'pendente'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$prestadorId, $oportunidadeId]);

    $oportunidade = $stmt->fetch(PDO::FETCH_ASSOC);

    // ✅ Verificar se encontrou a oportunidade
    if (!$oportunidade) {
        enviarResposta(false, 'Oportunidade não encontrada ou não está mais disponível.');
    }

    // 📅 Formatar data
    if ($oportunidade['prazo_desejado']) {
        $dataPrazo = new DateTime($oportunidade['prazo_desejado']);
        $oportunidade['prazo_desejado_formatado'] = $dataPrazo->format('d/m/Y');
        
        // Formatar com mês em português
        setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'portuguese');
        $meses = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho',
                  'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
        $mes = $meses[(int)$dataPrazo->format('n') - 1];
        $oportunidade['prazo_desejado_texto'] = $dataPrazo->format('d') . ' de ' . $mes . ' de ' . $dataPrazo->format('Y');
    } else {
        $oportunidade['prazo_desejado_formatado'] = null;
        $oportunidade['prazo_desejado_texto'] = 'Não especificado';
    }

    // 💰 Formatar valor
    if ($oportunidade['orcamento_maximo']) {
        $oportunidade['orcamento_maximo_formatado'] = 'R$ ' . number_format($oportunidade['orcamento_maximo'], 2, ',', '.');
    } else {
        $oportunidade['orcamento_maximo_formatado'] = 'A combinar';
    }

    // 📅 Formatar data de criação
    if ($oportunidade['data_criacao']) {
        $dataCriacao = new DateTime($oportunidade['data_criacao']);
        $oportunidade['data_criacao_formatada'] = $dataCriacao->format('d/m/Y H:i');
    }

    // Limpar dados da resposta
    $dados = [
        'id' => (int)$oportunidade['id'],
        'titulo' => $oportunidade['titulo'],
        'categoria' => $oportunidade['categoria'],
        'descricao' => $oportunidade['descricao'],
        'endereco' => $oportunidade['endereco'],
        'cidade' => $oportunidade['cidade'],
        'prazo_desejado' => $oportunidade['prazo_desejado'],
        'prazo_desejado_formatado' => $oportunidade['prazo_desejado_formatado'],
        'prazo_desejado_texto' => $oportunidade['prazo_desejado_texto'],
        'orcamento_maximo' => $oportunidade['orcamento_maximo'] ? (float)$oportunidade['orcamento_maximo'] : null,
        'orcamento_maximo_formatado' => $oportunidade['orcamento_maximo_formatado'],
        'observacoes' => $oportunidade['observacoes'],
        'status' => $oportunidade['status'],
        'data_criacao' => $oportunidade['data_criacao'],
        'data_criacao_formatada' => $oportunidade['data_criacao_formatada'],
        'cliente_nome' => $oportunidade['cliente_nome'],
        'cliente_email' => $oportunidade['cliente_email'],
        'cliente_telefone' => $oportunidade['cliente_telefone'],
        'ja_enviou_proposta' => (bool)$oportunidade['ja_enviou_proposta'],
        'proposta_existente' => $oportunidade['ja_enviou_proposta'] ? [
            'proposal_id' => (int)$oportunidade['proposal_id'],
            'valor' => $oportunidade['valor_proposta_enviada'] ? (float)$oportunidade['valor_proposta_enviada'] : null,
            'prazo' => $oportunidade['prazo_proposta_enviada']
        ] : null
    ];

    // 🎉 Retornar sucesso
    enviarResposta(true, 'Detalhes da oportunidade carregados com sucesso.', [
        'oportunidade' => $dados
    ]);
} catch (PDOException $e) {
    error_log("Erro PDO em obter-oportunidade-detalhes.php: " . $e->getMessage());
    enviarResposta(false, 'Erro interno do servidor. Tente novamente.');
} catch (Exception $e) {
    error_log("Erro geral em obter-oportunidade-detalhes.php: " . $e->getMessage());
    enviarResposta(false, 'Erro inesperado. Tente novamente.');
}

