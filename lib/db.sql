USE servicofacil;

-- ============================================
-- TABELA: USER (Usuário Base)
-- ============================================
CREATE TABLE
    `user` (
        `user_id` INT (11) NOT NULL AUTO_INCREMENT,
        `email` VARCHAR(100) NOT NULL,
        `password` VARCHAR(255) NOT NULL,
        `name` VARCHAR(100) NOT NULL,
        `phone_number` VARCHAR(15) DEFAULT NULL,
        `user_type` ENUM ('cliente', 'prestador', 'administrador') DEFAULT 'cliente',
        `status` ENUM ('ativo', 'inativo') DEFAULT 'ativo',
        `identity_verified` BOOLEAN DEFAULT FALSE,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`user_id`),
        UNIQUE KEY `email` (`email`)
    );

-- ============================================
-- TABELA: CLIENTE (Cliente)
-- ============================================
CREATE TABLE
    `cliente` (
        `id` INT (11) NOT NULL AUTO_INCREMENT,
        `user_id` INT (11) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `fk_cliente_user` (`user_id`),
        CONSTRAINT `fk_cliente_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE
    );

-- ============================================
-- TABELA: SERVICE_PROVIDER (Prestador de Serviço)
-- ============================================
CREATE TABLE
    `service_provider` (
        `service_provider_id` INT (11) NOT NULL AUTO_INCREMENT,
        `user_id` INT (11) NOT NULL,
        `specialty` VARCHAR(50) DEFAULT NULL,
        `location` VARCHAR(100) DEFAULT NULL,
        PRIMARY KEY (`service_provider_id`),
        KEY `fk_service_provider_user` (`user_id`),
        CONSTRAINT `fk_service_provider_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE
    );

-- ============================================
-- TABELA: SERVICE_REQUEST (Solicitação de Serviço)
-- ============================================
CREATE TABLE
    `service_request` (
        `request_id` INT (11) NOT NULL AUTO_INCREMENT,
        `cliente_id` INT (11) NOT NULL,
        `titulo` VARCHAR(200) NOT NULL,
        `categoria` VARCHAR(50) NOT NULL,
        `descricao` TEXT NOT NULL,
        `endereco` VARCHAR(200) NOT NULL,
        `cidade` VARCHAR(100) NOT NULL,
        `prazo_desejado` DATE DEFAULT NULL,
        `orcamento_maximo` DECIMAL(10, 2) DEFAULT NULL,
        `observacoes` TEXT DEFAULT NULL,
        `status` VARCHAR(20) DEFAULT 'pendente',
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`request_id`),
        KEY `fk_service_request_cliente` (`cliente_id`),
        CONSTRAINT `fk_service_request_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE CASCADE
    );

-- ============================================
-- TABELA: SERVICOS (Compatibilidade com código PHP)
-- ============================================
CREATE TABLE
    `servicos` (
        `id` INT (11) NOT NULL AUTO_INCREMENT,
        `cliente_id` INT (11) NOT NULL,
        `titulo` VARCHAR(200) NOT NULL,
        `descricao` TEXT NOT NULL,
        `categoria` VARCHAR(50) NOT NULL,
        `orcamento` DECIMAL(10, 2) DEFAULT NULL,
        `prazo` DATE DEFAULT NULL,
        `localizacao` VARCHAR(100) NOT NULL,
        `status` VARCHAR(20) DEFAULT 'aberto',
        `data_postagem` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `fk_servicos_cliente` (`cliente_id`),
        CONSTRAINT `fk_servicos_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE CASCADE
    );

-- ============================================
-- TABELA: PROVIDER_SERVICE (Serviços Postados por Prestadores)
-- ============================================
CREATE TABLE
    `provider_service` (
        `service_id` INT (11) NOT NULL AUTO_INCREMENT,
        `service_provider_id` INT (11) NOT NULL,
        `titulo` VARCHAR(200) NOT NULL,
        `descricao` TEXT NOT NULL,
        `categoria` VARCHAR(50) NOT NULL,
        `preco` DECIMAL(10, 2) NOT NULL,
        `disponibilidade` ENUM ('disponivel', 'indisponivel') DEFAULT 'disponivel',
        `status` ENUM ('ativo', 'inativo') DEFAULT 'ativo',
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`service_id`),
        KEY `fk_provider_service_provider` (`service_provider_id`),
        CONSTRAINT `fk_provider_service_provider` FOREIGN KEY (`service_provider_id`) REFERENCES `service_provider` (`service_provider_id`) ON DELETE CASCADE
    );

-- ============================================
-- TABELA: PROPOSAL (Proposta)
-- ============================================
CREATE TABLE
    `proposal` (
        `proposal_id` INT (11) NOT NULL AUTO_INCREMENT,
        `request_id` INT (11) NOT NULL,
        `service_provider_id` INT (11) NOT NULL,
        `amount` DECIMAL(10, 2) NOT NULL,
        `estimate` VARCHAR(50) DEFAULT NULL,
        `message` TEXT DEFAULT NULL,
        `submitted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`proposal_id`),
        KEY `fk_proposal_request` (`request_id`),
        KEY `fk_proposal_service_provider` (`service_provider_id`),
        CONSTRAINT `fk_proposal_request` FOREIGN KEY (`request_id`) REFERENCES `service_request` (`request_id`) ON DELETE CASCADE,
        CONSTRAINT `fk_proposal_service_provider` FOREIGN KEY (`service_provider_id`) REFERENCES `service_provider` (`service_provider_id`) ON DELETE CASCADE
    );

-- ============================================
-- TABELA: CONTRACT (Contrato)
-- ============================================
CREATE TABLE
    `contract` (
        `contract_id` INT (11) NOT NULL AUTO_INCREMENT,
        `request_id` INT (11) NOT NULL,
        `service_provider_id` INT (11) NOT NULL,
        `cliente_id` INT (11) NOT NULL,
        `contract_terms` TEXT NOT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `status` VARCHAR(20) DEFAULT 'active',
        PRIMARY KEY (`contract_id`),
        KEY `fk_contract_request` (`request_id`),
        KEY `fk_contract_service_provider` (`service_provider_id`),
        KEY `fk_contract_cliente` (`cliente_id`),
        CONSTRAINT `fk_contract_request` FOREIGN KEY (`request_id`) REFERENCES `service_request` (`request_id`) ON DELETE CASCADE,
        CONSTRAINT `fk_contract_service_provider` FOREIGN KEY (`service_provider_id`) REFERENCES `service_provider` (`service_provider_id`) ON DELETE CASCADE,
        CONSTRAINT `fk_contract_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE CASCADE
    );

-- ============================================
-- TABELA: PAYMENT (Pagamento)
-- ============================================
CREATE TABLE
    `payment` (
        `payment_id` INT (11) NOT NULL AUTO_INCREMENT,
        `contract_id` INT (11) NOT NULL,
        `amount` DECIMAL(10, 2) NOT NULL,
        `payment_date` DATE DEFAULT NULL,
        `status` VARCHAR(20) DEFAULT 'pending',
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`payment_id`),
        KEY `fk_payment_contract` (`contract_id`),
        CONSTRAINT `fk_payment_contract` FOREIGN KEY (`contract_id`) REFERENCES `contract` (`contract_id`) ON DELETE CASCADE
    );

-- ============================================
-- TABELA: REVIEW (Avaliação)
-- ============================================
CREATE TABLE
    `review` (
        `review_id` INT (11) NOT NULL AUTO_INCREMENT,
        `contract_id` INT (11) NOT NULL,
        `cliente_id` INT (11) NOT NULL,
        `rating` TINYINT NOT NULL CHECK (
            rating >= 1
            AND rating <= 5
        ),
        `comment` TEXT DEFAULT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`review_id`),
        KEY `fk_review_contract` (`contract_id`),
        KEY `fk_review_cliente` (`cliente_id`),
        CONSTRAINT `fk_review_contract` FOREIGN KEY (`contract_id`) REFERENCES `contract` (`contract_id`) ON DELETE CASCADE,
        CONSTRAINT `fk_review_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE CASCADE
    );

-- ============================================
-- TABELA: CHAT (Chat/Mensagens)
-- ============================================
CREATE TABLE
    `chat` (
        `chat_id` INT (11) NOT NULL AUTO_INCREMENT,
        `cliente_id` INT (11) NOT NULL,
        `service_provider_id` INT (11) NOT NULL,
        `sender_type` ENUM('cliente', 'prestador') NOT NULL DEFAULT 'prestador',
        `message` TEXT NOT NULL,
        `sent_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`chat_id`),
        KEY `fk_chat_cliente` (`cliente_id`),
        KEY `fk_chat_service_provider` (`service_provider_id`),
        CONSTRAINT `fk_chat_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_chat_service_provider` FOREIGN KEY (`service_provider_id`) REFERENCES `service_provider` (`service_provider_id`) ON DELETE CASCADE
    );


-- ============================================
-- TABELA: NOTIFICACOES (Notificações do Sistema)
-- ============================================
CREATE TABLE
    `notificacoes` (
        `id` INT (11) NOT NULL AUTO_INCREMENT,
        `usuario_id` INT (11) NOT NULL,
        `tipo` ENUM ('pagamento', 'servico', 'sistema') NOT NULL,
        `mensagem` TEXT NOT NULL,
        `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `lida` BOOLEAN DEFAULT FALSE,
        PRIMARY KEY (`id`),
        KEY `fk_notificacoes_usuario` (`usuario_id`),
        KEY `idx_usuario_lida` (`usuario_id`, `lida`),
        CONSTRAINT `fk_notificacoes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ============================================
-- MIGRAÇÃO: Adicionar campo sender_type à tabela chat
-- ============================================
-- Este script adiciona um campo para identificar quem enviou a mensagem
-- (cliente ou prestador)
-- Execute apenas se a tabela chat já existir sem o campo sender_type

-- Verificar se a coluna já existe antes de adicionar
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'servicofacil' 
    AND TABLE_NAME = 'chat' 
    AND COLUMN_NAME = 'sender_type'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `chat` ADD COLUMN `sender_type` ENUM(''cliente'', ''prestador'') NOT NULL DEFAULT ''prestador'' AFTER `service_provider_id`',
    'SELECT ''Coluna sender_type já existe na tabela chat'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Atualizar mensagens existentes (assumindo que todas foram enviadas por prestador)
-- Se houver mensagens enviadas por clientes, será necessário ajustar manualmente
UPDATE `chat` SET `sender_type` = 'prestador' WHERE `sender_type` = 'prestador';