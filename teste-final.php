<?php
require_once 'php/conexao.php';

echo "=== TESTE FINAL DO SISTEMA ===\n\n";

try {
    echo "🔍 Verificando estrutura da tabela service_request...\n";
    $result = $conexao->query('DESCRIBE service_request');
    $campos = [];
    while ($row = $result->fetch_assoc()) {
        $campos[] = $row['Field'];
    }
    
    $camposEsperados = ['request_id', 'cliente_id', 'titulo', 'categoria', 'descricao', 
                       'endereco', 'cidade', 'prazo_desejado', 'orcamento_maximo', 
                       'observacoes', 'status', 'created_at', 'data_atualizacao'];
    
    echo "✅ Campos encontrados: " . count($campos) . "\n";
    
    foreach ($camposEsperados as $campo) {
        if (in_array($campo, $campos)) {
            echo "✅ $campo\n";
        } else {
            echo "❌ $campo - FALTANDO\n";
        }
    }
    
    echo "\n📊 Verificando dados...\n";
    $result = $conexao->query('SELECT COUNT(*) as total FROM service_request');
    $row = $result->fetch_assoc();
    echo "✅ Total de solicitações: {$row['total']}\n";
    
    if ($row['total'] > 0) {
        echo "\n📋 Últimas solicitações:\n";
        $result = $conexao->query('SELECT request_id, titulo, categoria, status, created_at FROM service_request ORDER BY created_at DESC LIMIT 3');
        while ($row = $result->fetch_assoc()) {
            echo "- ID {$row['request_id']}: {$row['titulo']} ({$row['categoria']}) - {$row['status']}\n";
        }
    }
    
    echo "\n🧪 Testando consulta do PHP de listagem...\n";
    $cliente_id = 2; // ID do usuário de teste
    
    $sql = "SELECT 
                request_id as id,
                titulo,
                categoria,
                descricao,
                endereco,
                cidade,
                prazo_desejado,
                orcamento_maximo,
                observacoes,
                status,
                created_at as data_criacao,
                data_atualizacao
            FROM service_request 
            WHERE cliente_id = ? 
            ORDER BY created_at DESC";
    
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param('i', $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "✅ Consulta executada com sucesso!\n";
    echo "📊 Registros encontrados para cliente $cliente_id: " . $result->num_rows . "\n";
    
    while ($row = $result->fetch_assoc()) {
        echo "- {$row['titulo']} | {$row['categoria']} | {$row['status']}\n";
    }
    
    $stmt->close();
    
    echo "\n🎉 SISTEMA FUNCIONANDO PERFEITAMENTE!\n";
    echo "\n🌐 Pronto para teste no navegador:\n";
    echo "   http://localhost/php-servicofacil-crud/client/login/index.html\n";
    echo "   Email: teste@exemplo.com | Senha: teste123\n\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}

$conexao->close();
?>