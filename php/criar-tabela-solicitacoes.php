<?php
require_once 'conexao.php';

// Função para criar a tabela solicitacoes_servico
function criarTabelaSolicitacoes($conexao) {
    $sql = "
    CREATE TABLE IF NOT EXISTS `solicitacoes_servico` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `cliente_id` INT(11) NOT NULL,
        `titulo` VARCHAR(100) NOT NULL,
        `categoria` VARCHAR(50) NOT NULL,
        `descricao` TEXT NOT NULL,
        `endereco` VARCHAR(200) NOT NULL,
        `cidade` VARCHAR(100) NOT NULL,
        `prazo_desejado` VARCHAR(50) NOT NULL,
        `orcamento_maximo` DECIMAL(10,2) DEFAULT NULL,
        `observacoes` TEXT DEFAULT NULL,
        `status` ENUM('pendente', 'em_andamento', 'concluido', 'cancelado') DEFAULT 'pendente',
        `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `data_atualizacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `fk_solicitacao_cliente` (`cliente_id`),
        KEY `idx_categoria` (`categoria`),
        KEY `idx_status` (`status`),
        KEY `idx_cidade` (`cidade`),
        KEY `idx_data_criacao` (`data_criacao`),
        CONSTRAINT `fk_solicitacao_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    if ($conexao->query($sql) === TRUE) {
        echo "✅ Tabela 'solicitacoes_servico' criada com sucesso!<br>";
        return true;
    } else {
        echo "❌ Erro ao criar tabela 'solicitacoes_servico': " . $conexao->error . "<br>";
        return false;
    }
}

// Função para verificar se a tabela existe
function verificarTabela($conexao) {
    $sql = "SHOW TABLES LIKE 'solicitacoes_servico'";
    $resultado = $conexao->query($sql);
    return $resultado->num_rows > 0;
}

// Função para inserir dados de teste
function inserirDadosTeste($conexao) {
    // Verificar se já existem clientes
    $sql = "SELECT id FROM cliente LIMIT 1";
    $resultado = $conexao->query($sql);
    
    if ($resultado->num_rows == 0) {
        echo "⚠️ Nenhum cliente encontrado. Crie clientes primeiro para inserir dados de teste.<br>";
        return false;
    }
    
    $cliente = $resultado->fetch_assoc();
    $cliente_id = $cliente['id'];
    
    // Verificar se já existem solicitações
    $sql = "SELECT COUNT(*) as total FROM solicitacoes_servico";
    $resultado = $conexao->query($sql);
    $row = $resultado->fetch_assoc();
    
    if ($row['total'] > 0) {
        echo "ℹ️ Dados de teste já existem. Total de solicitações: " . $row['total'] . "<br>";
        return true;
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
            'orcamento_maximo' => 200.00,
            'observacoes' => null
        ]
    ];
    
    $sql = "INSERT INTO solicitacoes_servico (cliente_id, titulo, categoria, descricao, endereco, cidade, prazo_desejado, orcamento_maximo, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexao->prepare($sql);
    
    if (!$stmt) {
        echo "❌ Erro na preparação da consulta: " . $conexao->error . "<br>";
        return false;
    }
    
    $inseridos = 0;
    foreach ($dadosTeste as $dados) {
        $stmt->bind_param(
            'issssssds',
            $cliente_id,
            $dados['titulo'],
            $dados['categoria'],
            $dados['descricao'],
            $dados['endereco'],
            $dados['cidade'],
            $dados['prazo_desejado'],
            $dados['orcamento_maximo'],
            $dados['observacoes']
        );
        
        if ($stmt->execute()) {
            $inseridos++;
        } else {
            echo "❌ Erro ao inserir registro: " . $stmt->error . "<br>";
        }
    }
    
    $stmt->close();
    
    if ($inseridos > 0) {
        echo "✅ $inseridos solicitações de teste inseridas com sucesso!<br>";
        return true;
    }
    
    return false;
}

// Executar as operações
try {
    echo "<h3>🛠️ Configuração da Tabela de Solicitações de Serviço</h3>";
    
    // Verificar conexão
    if (!$conexao) {
        throw new Exception("Erro na conexão com o banco de dados");
    }
    
    echo "✅ Conexão com banco de dados estabelecida.<br><br>";
    
    // Verificar se tabela já existe
    if (verificarTabela($conexao)) {
        echo "ℹ️ Tabela 'solicitacoes_servico' já existe.<br>";
    } else {
        // Criar tabela
        echo "📋 Criando tabela 'solicitacoes_servico'...<br>";
        if (!criarTabelaSolicitacoes($conexao)) {
            throw new Exception("Falha ao criar tabela");
        }
    }
    
    // Inserir dados de teste (opcional)
    echo "<br>📊 Verificando dados de teste...<br>";
    inserirDadosTeste($conexao);
    
    echo "<br>✅ <strong>Configuração concluída com sucesso!</strong><br>";
    echo "<br>🔗 <a href='../client/servico/solicitar-servico.html'>Acessar formulário de solicitação</a><br>";
    echo "🔗 <a href='../client/cliente-dashboard.html'>Acessar dashboard do cliente</a><br>";
    
} catch (Exception $e) {
    echo "<br>❌ <strong>Erro:</strong> " . $e->getMessage() . "<br>";
} finally {
    if (isset($conexao)) {
        $conexao->close();
    }
}
?>