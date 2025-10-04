-- Criação da tabela servicos para prestadores publicarem seus serviços
USE servicofacil;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir alguns dados de exemplo
INSERT INTO `servicos` (`prestador_id`, `titulo`, `categoria`, `descricao`, `preco`, `prazo`, `localizacao`, `status`) VALUES
(1, 'Instalação Elétrica Residencial', 'Elétrica', 'Instalação completa de sistema elétrico em residências. Trabalho com materiais de qualidade e garantia de 1 ano.', 500.00, 'Até 3 dias', 'São Paulo - SP', 'ativo'),
(1, 'Reparo em Chuveiro Elétrico', 'Elétrica', 'Conserto e manutenção de chuveiros elétricos. Atendimento rápido e preço justo.', 80.00, 'No mesmo dia', 'São Paulo - SP', 'ativo');

-- Criar prestador de exemplo se não existir
-- Primeiro, criar usuário base
INSERT IGNORE INTO `user` (`email`, `password`, `name`, `phone_number`) VALUES
('prestador@exemplo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'João Silva - Eletricista', '11987654321');

-- Depois, criar prestador
INSERT IGNORE INTO `service_provider` (`user_id`, `specialty`, `location`) 
SELECT u.user_id, 'Elétrica', 'São Paulo - SP' 
FROM `user` u 
WHERE u.email = 'prestador@exemplo.com' 
AND NOT EXISTS (SELECT 1 FROM `service_provider` sp WHERE sp.user_id = u.user_id);

-- Atualizar IDs dos serviços para usar o prestador correto
UPDATE `servicos` s 
SET s.prestador_id = (
    SELECT sp.service_provider_id 
    FROM `service_provider` sp 
    INNER JOIN `user` u ON sp.user_id = u.user_id 
    WHERE u.email = 'prestador@exemplo.com' 
    LIMIT 1
) 
WHERE s.prestador_id = 1;