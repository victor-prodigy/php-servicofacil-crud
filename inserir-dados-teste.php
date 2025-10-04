<?php
require_once 'php/conexao.php';

echo "=== INSERINDO DADOS DE TESTE NA SERVICE_REQUEST ===\n\n";

try {
    // Verificar se já existem clientes
    $sql = "SELECT id FROM cliente LIMIT 1";
    $resultado = $conexao->query($sql);
    
    if ($resultado->num_rows == 0) {
        echo "⚠️ Nenhum cliente encontrado. Usando cliente ID = 2 (usuário de teste).\n";
        $cliente_id = 2; // ID do usuário de teste criado anteriormente
    } else {
        $cliente = $resultado->fetch_assoc();
        $cliente_id = $cliente['id'];
        echo "✅ Usando cliente ID: $cliente_id\n";
    }
    
    // Verificar se já existem solicitações
    $sql = "SELECT COUNT(*) as total FROM service_request";
    $resultado = $conexao->query($sql);
    $row = $resultado->fetch_assoc();
    
    if ($row['total'] > 0) {
        echo "ℹ️ Dados já existem. Total de solicitações: {$row['total']}\n\n";
        
        // Mostrar registros existentes
        echo "📋 Solicitações existentes:\n";
        $sql = "SELECT request_id, titulo, categoria, status FROM service_request ORDER BY created_at DESC LIMIT 5";
        $resultado = $conexao->query($sql);
        while ($row = $resultado->fetch_assoc()) {
            echo "- ID: {$row['request_id']} | {$row['titulo']} | {$row['categoria']} | {$row['status']}\n";
        }
        exit;
    }
    
    // Inserir dados de teste
    $dadosTeste = [
        [
            'titulo' => 'Reparo de torneira na cozinha',
            'categoria' => 'Encanamento',
            'descricao' => 'Torneira da cozinha está pingando constantemente. Precisa de reparo urgente.',
            'endereco' => 'Rua das Flores, 123 - Centro',
            'cidade' => 'São Paulo',
            'prazo_desejado' => 'Urgente (até 24h)',
            'service_type' => 'Encanamento',
            'location' => 'São Paulo - Centro',
            'orcamento_maximo' => 150.00,
            'observacoes' => 'Prefiro atendimento pela manhã'
        ],
        [
            'titulo' => 'Pintura da sala de estar',
            'categoria' => 'Pintura',
            'descricao' => 'Preciso pintar a sala de estar. Área de aproximadamente 20m². Tinta já comprada.',
            'endereco' => 'Av. Principal, 456 - Jardins',
            'cidade' => 'São Paulo',
            'prazo_desejado' => 'Até 1 semana',
            'service_type' => 'Pintura',
            'location' => 'São Paulo - Jardins',
            'orcamento_maximo' => 800.00,
            'observacoes' => 'Material já disponível. Disponível finais de semana.'
        ],
        [
            'titulo' => 'Instalação de ventilador de teto',
            'categoria' => 'Elétrica',
            'descricao' => 'Instalar ventilador de teto no quarto principal. Ventilador novo, ainda na caixa.',
            'endereco' => 'Rua dos Pássaros, 789 - Vila Nova',
            'cidade' => 'São Paulo',
            'prazo_desejado' => 'Até 3 dias',
            'service_type' => 'Elétrica',
            'location' => 'São Paulo - Vila Nova',
            'orcamento_maximo' => 200.00,
            'observacoes' => null
        ]
    ];
    
    $sql = "INSERT INTO service_request 
            (cliente_id, titulo, categoria, descricao, endereco, cidade, prazo_desejado, 
             service_type, location, orcamento_maximo, observacoes, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendente')";
    
    $stmt = $conexao->prepare($sql);
    
    if (!$stmt) {
        echo "❌ Erro na preparação da consulta: " . $conexao->error . "\n";
        exit;
    }
    
    $inseridos = 0;
    foreach ($dadosTeste as $dados) {
        $stmt->bind_param(
            'issssssssds',
            $cliente_id,
            $dados['titulo'],
            $dados['categoria'],
            $dados['descricao'],
            $dados['endereco'],
            $dados['cidade'],
            $dados['prazo_desejado'],
            $dados['service_type'],
            $dados['location'],
            $dados['orcamento_maximo'],
            $dados['observacoes']
        );
        
        if ($stmt->execute()) {
            $inseridos++;
            echo "✅ Inserido: {$dados['titulo']}\n";
        } else {
            echo "❌ Erro ao inserir '{$dados['titulo']}': " . $stmt->error . "\n";
        }
    }
    
    $stmt->close();
    
    echo "\n🎉 $inseridos solicitações de teste inseridas com sucesso!\n\n";
    
    echo "🔗 Para testar:\n";
    echo "   http://localhost/php-servicofacil-crud/client/cliente-dashboard.html\n";
    echo "   http://localhost/php-servicofacil-crud/client/servico/solicitar-servico.html\n\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}

$conexao->close();
?>