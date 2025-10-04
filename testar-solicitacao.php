<?php
// Simular login para testar solicitação
session_start();

echo "=== SIMULANDO LOGIN E TESTE DE SOLICITAÇÃO ===\n\n";

// Simular login do usuário de teste
$_SESSION['user_id'] = 2;
$_SESSION['usuario_id'] = 2;
$_SESSION['customer_id'] = 2;
$_SESSION['user_type'] = 'cliente';
$_SESSION['usuario_tipo'] = 'cliente';
$_SESSION['nome'] = 'Usuario Teste';
$_SESSION['name'] = 'Usuario Teste';
$_SESSION['email'] = 'teste@exemplo.com';

echo "✅ Sessão simulada criada!\n";
echo "📋 Dados da sessão:\n";
foreach ($_SESSION as $key => $value) {
    echo "- $key: $value\n";
}

// Simular dados POST para teste
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'titulo' => 'Teste de Solicitação',
    'categoria' => 'Teste',
    'descricao' => 'Esta é uma solicitação de teste para verificar o funcionamento.',
    'endereco' => 'Rua de Teste, 123',
    'cidade' => 'São Paulo',
    'prazo_desejado' => 'Até 3 dias',
    'orcamento_maximo' => '150.00',
    'observacoes' => 'Teste de observações'
];

echo "\n🧪 Testando criação de solicitação...\n";

// Capturar saída do script
ob_start();
try {
    include 'php/servico/solicitar-servico.php';
    $output = ob_get_clean();
    
    echo "📤 Resposta do servidor:\n";
    echo $output . "\n\n";
    
    echo "🔍 Análise da resposta:\n";
    $json = json_decode($output, true);
    if ($json) {
        if (isset($json['sucesso']) && $json['sucesso']) {
            echo "✅ Solicitação criada com sucesso!\n";
            echo "📄 ID da solicitação: " . ($json['solicitacao_id'] ?? 'N/A') . "\n";
            echo "💬 Mensagem: " . ($json['mensagem'] ?? 'N/A') . "\n";
        } else {
            echo "❌ Erro na criação:\n";
            echo "🚨 Erro: " . ($json['erro'] ?? 'N/A') . "\n";
            if (isset($json['detalhes'])) {
                echo "📋 Detalhes: " . (is_array($json['detalhes']) ? implode(', ', $json['detalhes']) : $json['detalhes']) . "\n";
            }
        }
    } else {
        echo "⚠️ Resposta não é JSON válido ou está vazia\n";
        echo "📄 Saída bruta: $output\n";
    }
    
} catch (Exception $e) {
    ob_end_clean();
    echo "❌ Erro durante execução: " . $e->getMessage() . "\n";
}

echo "\n💡 Conclusão:\n";
echo "Se o teste foi bem-sucedido, o problema é que o usuário não está logado.\n";
echo "🔗 Para resolver: faça login em http://localhost/php-servicofacil-crud/client/login/index.html\n";
?>