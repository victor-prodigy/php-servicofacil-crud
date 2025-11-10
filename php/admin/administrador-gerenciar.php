/************* ✨ Windsurf Command 🌟 *************/
<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

function retornarErro($mensagem, $codigo = 400)
{
    http_response_code($codigo);
    echo json_encode([
        'success' => false,
        'message' => $mensagem
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function retornarSucesso($mensagem, $dados = [])
{
    echo json_encode([
        'success' => true,
        'message' => $mensagem,
        'data' => $dados
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar autenticação
if (!isset($_SESSION['admin_id']) || $_SESSION['usuario_tipo'] !== 'administrador') {
    retornarErro('Acesso negado', 401);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    require_once __DIR__ . '/../conexao.php';

    switch ($method) {
        case 'GET':
            if ($action === 'details') {
                // Buscar detalhes de um serviço específico
                $service_id = $_GET['id'] ?? '';

                if (empty($service_id)) {
                    retornarErro('ID do serviço é obrigatório');
                }

                $sql = "SELECT 
                            sr.*,
                            u.name as cliente_nome,
                            u.email as cliente_email,
                            u.phone_number as cliente_telefone,
                            u.instagram as cliente_instagram
                            u.phone_number as cliente_telefone
                        FROM service_request sr
                        JOIN cliente c ON sr.cliente_id = c.id
                        JOIN user u ON c.user_id = u.id
                        JOIN user u ON c.user_id = u.user_id
                        WHERE sr.request_id = ?";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([$service_id]);
                $service = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$service) {
                    retornarErro('Serviço não encontrado', 404);
                }

                retornarSucesso('Detalhes do serviço', $service);
            }
            break;

        case 'PUT':
        case 'PATCH':
            // Atualizar serviço
            $input = json_decode(file_get_contents('php://input'), true);
            $service_id = $input['service_id'] ?? '';

            if (empty($service_id)) {
                retornarErro('ID do serviço é obrigatório');
            }

            // Campos permitidos para atualização
            // 1. novo campo instagram
            $allowed_fields = ['titulo', 'categoria', 'descricao', 'endereco', 'cidade', 'orcamento_maximo', 'prazo_desejado', 'status', 'observacoes', 'instagram'];
            $allowed_fields = ['titulo', 'categoria', 'descricao', 'endereco', 'cidade', 'orcamento_maximo', 'prazo_desejado', 'status', 'observacoes'];
            $update_fields = [];
            $params = [];

            foreach ($allowed_fields as $field) {
                if (isset($input[$field])) {
                    $update_fields[] = "{$field} = ?";
                    $params[] = $input[$field];
                }
            }

            if (empty($update_fields)) {
                retornarErro('Nenhum campo para atualizar');
            }

            $params[] = $service_id;

            $sql = "UPDATE service_request SET " . implode(', ', $update_fields) . " WHERE request_id = ?";
            $stmt = $pdo->prepare($sql);

            if ($stmt->execute($params)) {
                retornarSucesso('Serviço atualizado com sucesso');
            } else {
                retornarErro('Erro ao atualizar serviço');
            }
            break;

        case 'DELETE':
            // Remover serviço
            $service_id = $_GET['id'] ?? '';

            if (empty($service_id)) {
                retornarErro('ID do serviço é obrigatório');
            }

            // Verificar se existem propostas para este serviço
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM proposal WHERE request_id = ?");
            $stmt->execute([$service_id]);
            $proposals_count = $stmt->fetch()['total'];

            if ($proposals_count > 0) {
                retornarErro('Não é possível remover serviço com propostas associadas. Cancele o serviço em vez de removê-lo.');
            }

            // Remover serviço
            $stmt = $pdo->prepare("DELETE FROM service_request WHERE request_id = ?");

            if ($stmt->execute([$service_id])) {
                retornarSucesso('Serviço removido com sucesso');
            } else {
                retornarErro('Erro ao remover serviço');
            }
            break;

        case 'POST':
            if ($action === 'change_status') {
                // Alterar status do serviço
                $service_id = $_POST['service_id'] ?? '';
                $new_status = $_POST['status'] ?? '';

                if (empty($service_id) || empty($new_status)) {
                    retornarErro('ID do serviço e status são obrigatórios');
                }

                $valid_statuses = ['Pendente', 'Em Andamento', 'Concluído', 'Cancelado'];
                if (!in_array($new_status, $valid_statuses)) {
                    retornarErro('Status inválido');
                }

                $stmt = $pdo->prepare("UPDATE service_request SET status = ? WHERE request_id = ?");

                if ($stmt->execute([$new_status, $service_id])) {
                    retornarSucesso('Status do serviço alterado com sucesso');
                } else {
                    retornarErro('Erro ao alterar status do serviço');
                }
            }
            break;

        default:
            retornarErro('Método não permitido', 405);
    }
} catch (Exception $e) {
    error_log("Erro no gerenciamento de serviços: " . $e->getMessage());
    retornarErro('Erro interno do servidor', 500);
}
?>


/******* eca086aa-6e9c-4c0d-bed8-c90e3fc44df3 *******/