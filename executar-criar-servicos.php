<?php
require_once 'php/conexao.php';

echo "=== CRIANDO TABELA SERVICOS ===\n\n";

// SQL para criar a tabela servicos
$createTable = "
CREATE TABLE IF NOT EXISTS `servicos` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `prestador_id` INT(11) NOT NULL,
    `titulo` VARCHAR(200) NOT NULL,
    `categoria` VARCHAR(100) NOT NULL,
    `descricao` TEXT NOT NULL,
    `preco` DECIMAL(10,2) DEFAULT NULL,
    `prazo` VARCHAR(100) DEFAULT NULL,
    `localizacao` VARCHAR(200) NOT NULL,
    `status` ENUM('ativo', 'inativo', 'pausado') DEFAULT 'ativo',
    `data_postagem` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `data_atualizacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_prestador_id` (`prestador_id`),
    KEY `idx_categoria` (`categoria`),
    KEY `idx_status` (`status`),
    CONSTRAINT `fk_servicos_prestador` FOREIGN KEY (`prestador_id`) REFERENCES `service_provider` (`service_provider_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";

try {
    if ($conn->query($createTable)) {
        echo "✅ Tabela 'servicos' criada com sucesso!\n\n";
    } else {
        echo "❌ Erro ao criar tabela: " . $conn->error . "\n\n";
    }

    // Criar usuário prestador de exemplo
    echo "🧪 Criando usuário prestador de exemplo...\n";
    
    $userSql = "INSERT IGNORE INTO `user` (`email`, `password`, `name`, `phone_number`) VALUES
    ('prestador@exemplo.com', '$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'João Silva - Eletricista', '11987654321')";
    
    if ($conn->query($userSql)) {
        echo "✅ Usuário base criado!\n";
    } else {
        echo "ℹ️ Usuário já existe ou erro: " . $conn->error . "\n";
    }

    // Criar prestador
    $providerSql = "INSERT IGNORE INTO `service_provider` (`user_id`, `specialty`, `location`) 
    SELECT u.user_id, 'Elétrica', 'São Paulo - SP' 
    FROM `user` u 
    WHERE u.email = 'prestador@exemplo.com' 
    AND NOT EXISTS (SELECT 1 FROM `service_provider` sp WHERE sp.user_id = u.user_id)";
    
    if ($conn->query($providerSql)) {
        echo "✅ Prestador criado!\n";
    } else {
        echo "ℹ️ Prestador já existe ou erro: " . $conn->error . "\n";
    }

    // Buscar ID do prestador
    $result = $conn->query("SELECT sp.service_provider_id FROM service_provider sp 
                           INNER JOIN user u ON sp.user_id = u.user_id 
                           WHERE u.email = 'prestador@exemplo.com'");
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $prestadorId = $row['service_provider_id'];
        echo "✅ Prestador ID: $prestadorId\n\n";

        // Inserir serviços de exemplo
        echo "📝 Inserindo serviços de exemplo...\n";
        
        $servicos = [
            [
                'titulo' => 'Instalação Elétrica Residencial',
                'categoria' => 'Elétrica',
                'descricao' => 'Instalação completa de sistema elétrico em residências. Trabalho com materiais de qualidade e garantia de 1 ano.',
                'preco' => 500.00,
                'prazo' => 'Até 3 dias',
                'localizacao' => 'São Paulo - SP'
            ],
            [
                'titulo' => 'Reparo em Chuveiro Elétrico',
                'categoria' => 'Elétrica',
                'descricao' => 'Conserto e manutenção de chuveiros elétricos. Atendimento rápido e preço justo.',
                'preco' => 80.00,
                'prazo' => 'No mesmo dia',
                'localizacao' => 'São Paulo - SP'
            ]
        ];

        foreach ($servicos as $index => $servico) {
            $servicoSql = "INSERT IGNORE INTO `servicos` 
                          (`prestador_id`, `titulo`, `categoria`, `descricao`, `preco`, `prazo`, `localizacao`, `status`) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, 'ativo')";
            
            $stmt = $conn->prepare($servicoSql);
            $stmt->bind_param(
                'isssdss',
                $prestadorId,
                $servico['titulo'],
                $servico['categoria'],
                $servico['descricao'],
                $servico['preco'],
                $servico['prazo'],
                $servico['localizacao']
            );
            
            if ($stmt->execute()) {
                echo "✅ Serviço " . ($index + 1) . " inserido!\n";
            } else {
                echo "ℹ️ Serviço " . ($index + 1) . " já existe ou erro: " . $stmt->error . "\n";
            }
            $stmt->close();
        }
    }

    // Verificar resultado final
    echo "\n📊 Verificando dados finais...\n";
    $result = $conn->query("SELECT COUNT(*) as total FROM servicos");
    $row = $result->fetch_assoc();
    echo "Total de serviços na tabela: " . $row['total'] . "\n";

} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}

$conn->close();
echo "\n🎉 Processo concluído!\n";
?>