-- ============================================
-- NOVA TABELA: SOLICITACOES_SERVICO
-- Implementação do PBI [SF-002] - Cadastrar Solicitação de Serviço
-- ============================================

CREATE TABLE `solicitacoes_servico` (
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

-- ============================================
-- INSERIR DADOS DE TESTE (OPCIONAL)
-- ============================================

-- Inserir algumas categorias de exemplo (se não existirem clientes)
-- INSERT INTO `solicitacoes_servico` (`cliente_id`, `titulo`, `categoria`, `descricao`, `endereco`, `cidade`, `prazo_desejado`, `orcamento_maximo`, `observacoes`, `status`) VALUES
-- (1, 'Reparo de torneira na cozinha', 'Encanamento', 'Torneira da cozinha está pingando constantemente. Precisa de reparo urgente.', 'Rua das Flores, 123 - Centro', 'São Paulo', 'Urgente (até 24h)', 150.00, 'Prefiro atendimento pela manhã', 'pendente'),
-- (1, 'Pintura da sala de estar', 'Pintura', 'Preciso pintar a sala de estar. Área de aproximadamente 20m². Tinta já comprada.', 'Av. Principal, 456 - Jardins', 'São Paulo', 'Até 1 semana', 800.00, 'Material já disponível. Disponível finais de semana.', 'pendente'),
-- (1, 'Instalação de ventilador de teto', 'Elétrica', 'Instalar ventilador de teto no quarto principal. Ventilador novo, ainda na caixa.', 'Rua dos Pássaros, 789 - Vila Nova', 'São Paulo', 'Até 3 dias', 200.00, NULL, 'pendente');

-- ============================================
-- COMANDOS PARA VERIFICAÇÃO
-- ============================================

-- Para verificar se a tabela foi criada corretamente:
-- DESCRIBE solicitacoes_servico;

-- Para verificar os dados:
-- SELECT * FROM solicitacoes_servico ORDER BY data_criacao DESC;

-- Para verificar estatísticas por categoria:
-- SELECT categoria, COUNT(*) as total, AVG(orcamento_maximo) as orcamento_medio 
-- FROM solicitacoes_servico 
-- WHERE orcamento_maximo IS NOT NULL 
-- GROUP BY categoria 
-- ORDER BY total DESC;