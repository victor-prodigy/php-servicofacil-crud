-- ============================================
-- SEED DATA - ServiçoFácil
-- Dados de exemplo para testes e desenvolvimento
-- Execute este arquivo APÓS executar o db.sql
-- ============================================

USE servicofacil;

-- ============================================
-- LIMPAR DADOS EXISTENTES (opcional)
-- ============================================
-- Descomente as linhas abaixo se quiser limpar dados existentes
-- SET FOREIGN_KEY_CHECKS = 0;
-- TRUNCATE TABLE chat;
-- TRUNCATE TABLE review;
-- TRUNCATE TABLE payment;
-- TRUNCATE TABLE contract;
-- TRUNCATE TABLE proposal;
-- TRUNCATE TABLE service_request;
-- TRUNCATE TABLE customer;
-- TRUNCATE TABLE service_provider;
-- TRUNCATE TABLE user;
-- TRUNCATE TABLE cliente;
-- SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- INSERIR USUÁRIOS BASE
-- ============================================
INSERT INTO `user` (`user_id`, `email`, `password`, `name`, `phone_number`, `identity_verified`) VALUES
(1, 'joao.cliente@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'João Silva', '(11) 99999-1111', TRUE),
(2, 'maria.prestadora@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Maria Santos', '(11) 99999-2222', TRUE),
(3, 'pedro.cliente@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pedro Costa', '(11) 99999-3333', FALSE),
(4, 'ana.prestadora@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ana Oliveira', '(11) 99999-4444', TRUE),
(5, 'carlos.cliente@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos Mendes', '(11) 99999-5555', TRUE),
(6, 'juliana.prestadora@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juliana Ferreira', '(11) 99999-6666', TRUE);

-- ============================================
-- INSERIR CLIENTES
-- ============================================
INSERT INTO `customer` (`customer_id`, `user_id`, `email`, `password`, `name`, `phone_number`, `identity_verified`) VALUES
(1, 1, 'joao.cliente@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'João Silva', '(11) 99999-1111', TRUE),
(2, 3, 'pedro.cliente@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pedro Costa', '(11) 99999-3333', FALSE),
(3, 5, 'carlos.cliente@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos Mendes', '(11) 99999-5555', TRUE);

-- ============================================
-- INSERIR PRESTADORES DE SERVIÇO
-- ============================================
INSERT INTO `service_provider` (`service_provider_id`, `user_id`, `email`, `password`, `name`, `specialty`, `location`, `identity_verified`) VALUES
(1, 2, 'maria.prestadora@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Maria Santos', 'Encanamento', 'São Paulo, SP', TRUE),
(2, 4, 'ana.prestadora@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ana Oliveira', 'Elétrica', 'Rio de Janeiro, RJ', TRUE),
(3, 6, 'juliana.prestadora@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juliana Ferreira', 'Pintura', 'Belo Horizonte, MG', TRUE);

-- ============================================
-- INSERIR SOLICITAÇÕES DE SERVIÇO
-- ============================================
INSERT INTO `service_request` (`request_id`, `customer_id`, `service_type`, `location`, `deadline`, `budget`) VALUES
(1, 1, 'Encanamento', 'São Paulo, SP', '2025-10-15', 500.00),
(2, 2, 'Elétrica', 'Rio de Janeiro, RJ', '2025-10-20', 300.00),
(3, 1, 'Pintura', 'São Paulo, SP', '2025-11-01', 800.00),
(4, 3, 'Limpeza', 'Belo Horizonte, MG', '2025-10-25', 200.00),
(5, 2, 'Jardinagem', 'Rio de Janeiro, RJ', '2025-11-10', 400.00);

-- ============================================
-- INSERIR PROPOSTAS
-- ============================================
INSERT INTO `proposal` (`proposal_id`, `request_id`, `service_provider_id`, `proposal_content`, `estimated_time`, `message`) VALUES
(1, 1, 1, 450.00, '2 dias', 'Posso realizar o serviço com materiais de qualidade'),
(2, 2, 2, 280.00, '1 dia', 'Especialista em instalações elétricas residenciais'),
(3, 1, 2, 500.00, '3 dias', 'Ofereço garantia de 6 meses no serviço'),
(4, 3, 3, 750.00, '5 dias', 'Pintura completa com tinta premium'),
(5, 3, 1, 720.00, '4 dias', 'Inclui preparação das paredes e acabamento'),
(6, 4, 3, 180.00, '1 dia', 'Limpeza completa e organização'),
(7, 5, 1, 350.00, '2 dias', 'Manutenção e paisagismo básico');

-- ============================================
-- INSERIR CONTRATOS
-- ============================================
INSERT INTO `contract` (`contract_id`, `request_id`, `service_provider_id`, `contract_terms`, `status`) VALUES
(1, 1, 1, 'Serviço de encanamento com garantia de 3 meses. Materiais inclusos.', 'completed'),
(2, 2, 2, 'Instalação elétrica completa com certificado de segurança.', 'completed'),
(3, 3, 3, 'Pintura completa de apartamento de 2 quartos com tinta Suvinil.', 'active'),
(4, 4, 3, 'Limpeza geral pós-obra com produtos profissionais.', 'completed');

-- ============================================
-- INSERIR PAGAMENTOS
-- ============================================
INSERT INTO `payment` (`payment_id`, `contract_id`, `amount`, `payment_date`, `status`) VALUES
(1, 1, 450.00, '2025-09-25', 'completed'),
(2, 2, 280.00, '2025-09-20', 'completed'),
(3, 3, 375.00, NULL, 'pending'),
(4, 3, 375.00, NULL, 'pending'),
(5, 4, 180.00, '2025-09-22', 'completed');

-- ============================================
-- INSERIR AVALIAÇÕES
-- ============================================
INSERT INTO `review` (`review_id`, `contract_id`, `customer_id`, `rating`, `comment`) VALUES
(1, 1, 1, 5, 'Excelente serviço! Muito profissional e pontual.'),
(2, 2, 2, 4, 'Bom trabalho, mas poderia ter sido mais rápido.'),
(3, 4, 3, 5, 'Limpeza impecável! Recomendo muito.'),
(4, 1, 1, 5, 'Trabalho de qualidade e preço justo.');

-- ============================================
-- INSERIR MENSAGENS DE CHAT
-- ============================================
INSERT INTO `chat` (`chat_id`, `customer_id`, `service_provider_id`, `message`) VALUES
(1, 1, 1, 'Olá! Quando você pode começar o serviço?'),
(2, 1, 1, 'Posso começar amanhã pela manhã.'),
(3, 1, 1, 'Perfeito! Estarei esperando às 8h.'),
(4, 2, 2, 'Você tem disponibilidade para hoje?'),
(5, 2, 2, 'Sim, posso ir aí às 14h.'),
(6, 2, 2, 'Ótimo! Vou estar em casa.'),
(7, 3, 3, 'Preciso de um orçamento para pintura.'),
(8, 3, 3, 'Posso fazer uma visita técnica amanhã.'),
(9, 1, 3, 'Qual o melhor horário para você?'),
(10, 1, 3, 'Prefiro pela manhã, entre 9h e 11h.');

-- ============================================
-- INSERIR DADOS NA TABELA CLIENTE ORIGINAL (Compatibilidade)
-- ============================================
INSERT INTO `cliente` (`email`, `senha`) VALUES
('admin@servicofacil.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('teste@servicofacil.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('usuario@exemplo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('demo@servicofacil.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ============================================
-- MENSAGEM DE CONFIRMAÇÃO
-- ============================================
SELECT 'Dados de exemplo inseridos com sucesso!' AS status;
SELECT 
    'Usuários: ' AS tabela, COUNT(*) AS total FROM user
UNION ALL
SELECT 'Clientes: ', COUNT(*) FROM customer
UNION ALL
SELECT 'Prestadores: ', COUNT(*) FROM service_provider
UNION ALL
SELECT 'Solicitações: ', COUNT(*) FROM service_request
UNION ALL
SELECT 'Propostas: ', COUNT(*) FROM proposal
UNION ALL
SELECT 'Contratos: ', COUNT(*) FROM contract
UNION ALL
SELECT 'Pagamentos: ', COUNT(*) FROM payment
UNION ALL
SELECT 'Avaliações: ', COUNT(*) FROM review
UNION ALL
SELECT 'Mensagens: ', COUNT(*) FROM chat
UNION ALL
SELECT 'Cliente (orig): ', COUNT(*) FROM cliente;