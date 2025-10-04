-- ============================================
-- DATABASE SERVICOFACIL
-- ============================================
CREATE DATABASE IF NOT EXISTS servicofacil;
USE servicofacil;

-- ============================================
-- TABELA: USER (Usuário Base)
-- ============================================
CREATE TABLE IF NOT EXISTS `user` (
    `user_id` INT(11) NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `phone_number` VARCHAR(15) DEFAULT NULL,
    `identity_verified` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`),
    UNIQUE KEY `email` (`email`)
);

-- ============================================
-- TABELA: CLIENTE (Cliente)
-- ============================================
CREATE TABLE IF NOT EXISTS `cliente` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `senha` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`),
    KEY `fk_cliente_user` (`user_id`),
    CONSTRAINT `fk_cliente_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE
);

-- ============================================
-- TABELA: SERVICE_PROVIDER (Prestador de Serviço)
-- ============================================
CREATE TABLE IF NOT EXISTS `service_provider` (
    `service_provider_id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `specialty` VARCHAR(50) DEFAULT NULL,
    `location` VARCHAR(100) DEFAULT NULL,
    PRIMARY KEY (`service_provider_id`),
    KEY `fk_service_provider_user` (`user_id`),
    CONSTRAINT `fk_service_provider_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE
);

-- ============================================
-- TABELA: SERVICE (Serviços Publicados pelo Prestador)
-- ============================================
CREATE TABLE IF NOT EXISTS `service` (
    `service_id` INT(11) NOT NULL AUTO_INCREMENT,
    `service_provider_id` INT(11) NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `estimated_time` VARCHAR(50) NOT NULL,
    `location` VARCHAR(100) NOT NULL,
    `status` VARCHAR(20) DEFAULT 'ativo',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`service_id`),
    KEY `fk_service_provider` (`service_provider_id`),
    CONSTRAINT `fk_service_provider` FOREIGN KEY (`service_provider_id`) REFERENCES `service_provider` (`service_provider_id`) ON DELETE CASCADE
);

-- ============================================
-- TABELA: SERVICE_REQUEST (Solicitação de Serviço)
-- ============================================
CREATE TABLE IF NOT EXISTS `service_request` (
    `request_id` INT(11) NOT NULL AUTO_INCREMENT,
    `cliente_id` INT(11) NOT NULL,
    `service_type` VARCHAR(50) NOT NULL,
    `location` VARCHAR(100) NOT NULL,
    `deadline` DATE DEFAULT NULL,
    `budget` DECIMAL(10,2) DEFAULT NULL,
    `photos` VARCHAR(200) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`request_id`),
    KEY `fk_service_request_cliente` (`cliente_id`),
    CONSTRAINT `fk_service_request_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE CASCADE
);

-- ============================================
-- TABELA: PROPOSAL (Proposta)
-- ============================================
CREATE TABLE IF NOT EXISTS `proposal` (
    `proposal_id` INT(11) NOT NULL AUTO_INCREMENT,
    `request_id` INT(11) NOT NULL,
    `service_provider_id` INT(11) NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
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
CREATE TABLE IF NOT EXISTS `contract` (
    `contract_id` INT(11) NOT NULL AUTO_INCREMENT,
    `request_id` INT(11) NOT NULL,
    `service_provider_id` INT(11) NOT NULL,
    `cliente_id` INT(11) NOT NULL,
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
CREATE TABLE IF NOT EXISTS `payment` (
    `payment_id` INT(11) NOT NULL AUTO_INCREMENT,
    `contract_id` INT(11) NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
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
CREATE TABLE IF NOT EXISTS `review` (
    `review_id` INT(11) NOT NULL AUTO_INCREMENT,
    `contract_id` INT(11) NOT NULL,
    `cliente_id` INT(11) NOT NULL,
    `rating` TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    `comment` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`review_id`),
    KEY `fk_review_contract` (`contract_id`),
    KEY `fk_review_cliente` (`cliente_id`),
    CONSTRAINT `fk_review_contract` FOREIGN KEY (`contract_id`) REFERENCES `contract` (`contract_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_review_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE CASCADE
);

-- ============================================
-- TABELA: CHAT (Mensagens)
-- ============================================
CREATE TABLE IF NOT EXISTS `chat` (
    `chat_id` INT(11) NOT NULL AUTO_INCREMENT,
    `cliente_id` INT(11) NOT NULL,
    `service_provider_id` INT(11) NOT NULL,
    `message` TEXT NOT NULL,
    `sent_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`chat_id`),
    KEY `fk_chat_cliente` (`cliente_id`),
    KEY `fk_chat_service_provider` (`service_provider_id`),
    CONSTRAINT `fk_chat_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_chat_service_provider` FOREIGN KEY (`service_provider_id`) REFERENCES `service_provider` (`service_provider_id`) ON DELETE CASCADE
);
