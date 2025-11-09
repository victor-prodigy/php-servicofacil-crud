-- ============================================
-- SEED DATA - ServiçoFácil
-- Dados de exemplo para testes e desenvolvimento
-- Execute este arquivo APÓS executar o db.sql e seed-admin.sql
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
-- TRUNCATE TABLE service_provider;
-- TRUNCATE TABLE cliente;
-- TRUNCATE TABLE user;
-- SET FOREIGN_KEY_CHECKS = 1;
-- ============================================
-- INSERIR USUÁRIOS BASE
-- ============================================
INSERT INTO
  `user` (
    `user_id`,
    `email`,
    `password`,
    `name`,
    `phone_number`,
    `user_type`,
    `status`,
    `identity_verified`
  )
VALUES
  -- Clientes
  (
    2,
    'joao.cliente@email.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'João Silva',
    '(11) 99999-1111',
    'cliente',
    'ativo',
    TRUE
  ),
  (
    3,
    'pedro.cliente@email.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Pedro Costa',
    '(11) 99999-3333',
    'cliente',
    'ativo',
    FALSE
  ),
  (
    4,
    'carlos.cliente@email.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Carlos Mendes',
    '(11) 99999-5555',
    'cliente',
    'ativo',
    TRUE
  ),
  (
    5,
    'joao123@joao.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'João Teste',
    '(11) 99999-0001',
    'cliente',
    'ativo',
    TRUE
  ),
  -- Prestadores
  (
    6,
    'maria.prestadora@email.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Maria Santos',
    '(11) 99999-2222',
    'prestador',
    'ativo',
    TRUE
  ),
  (
    7,
    'ana.prestadora@email.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Ana Oliveira',
    '(11) 99999-4444',
    'prestador',
    'ativo',
    TRUE
  ),
  (
    8,
    'juliana.prestadora@email.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Juliana Ferreira',
    '(11) 99999-6666',
    'prestador',
    'ativo',
    TRUE
  ),
  (
    9,
    'carlos123@carlos.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Carlos Teste',
    '(11) 99999-0002',
    'prestador',
    'ativo',
    TRUE
  );

-- ============================================
-- INSERIR CLIENTES
-- ============================================
INSERT INTO
  `cliente` (`id`, `user_id`)
VALUES
  (1, 2), -- João Silva
  (2, 3), -- Pedro Costa  
  (3, 4), -- Carlos Mendes
  (4, 5);

-- João Teste
-- ============================================
-- INSERIR PRESTADORES DE SERVIÇO
-- ============================================
INSERT INTO
  `service_provider` (
    `service_provider_id`,
    `user_id`,
    `specialty`,
    `location`
  )
VALUES
  (1, 6, 'Encanamento', 'São Paulo, SP'), -- Maria Santos
  (2, 7, 'Elétrica', 'Rio de Janeiro, RJ'), -- Ana Oliveira
  (3, 8, 'Pintura', 'Belo Horizonte, MG'), -- Juliana Ferreira
  (4, 9, 'Elétrica', 'São Paulo, SP');

-- Carlos Teste
-- ============================================
-- INSERIR SOLICITAÇÕES DE SERVIÇO
-- ============================================
INSERT INTO
  `service_request` (
    `request_id`,
    `cliente_id`,
    `titulo`,
    `categoria`,
    `descricao`,
    `endereco`,
    `cidade`,
    `prazo_desejado`,
    `orcamento_maximo`,
    `observacoes`,
    `status`
  )
VALUES
  (
    1,
    1,
    'Reparo de Encanamento',
    'Encanamento',
    'Preciso de reparo urgente em torneira que está vazando na cozinha',
    'Rua das Flores, 123',
    'São Paulo',
    '2025-10-15',
    500.00,
    'Preferência para horário da manhã',
    'pendente'
  ),
  (
    2,
    2,
    'Instalação Elétrica',
    'Elétrica',
    'Necessito instalação de novos pontos elétricos na sala',
    'Av. Atlântica, 456',
    'Rio de Janeiro',
    '2025-10-20',
    300.00,
    NULL,
    'pendente'
  ),
  (
    3,
    1,
    'Pintura de Apartamento',
    'Pintura',
    'Pintura completa de apartamento de 2 quartos',
    'Rua das Acácias, 789',
    'São Paulo',
    '2025-11-01',
    800.00,
    'Cores já escolhidas',
    'pendente'
  ),
  (
    4,
    3,
    'Limpeza Pós-Obra',
    'Limpeza',
    'Limpeza completa após reforma',
    'Rua dos Ipês, 321',
    'Belo Horizonte',
    '2025-10-25',
    200.00,
    NULL,
    'pendente'
  ),
  (
    5,
    2,
    'Jardinagem e Paisagismo',
    'Jardinagem',
    'Manutenção de jardim e criação de novos canteiros',
    'Av. Beira Mar, 654',
    'Rio de Janeiro',
    '2025-11-10',
    400.00,
    'Jardim de aproximadamente 50m²',
    'pendente'
  );

-- ============================================
-- INSERIR PROPOSTAS
-- ============================================
INSERT INTO
  `proposal` (
    `proposal_id`,
    `request_id`,
    `service_provider_id`,
    `amount`,
    `estimate`,
    `message`
  )
VALUES
  (
    1,
    1,
    1,
    450.00,
    '2 dias',
    'Posso realizar o serviço com materiais de qualidade'
  ),
  (
    2,
    2,
    2,
    280.00,
    '1 dia',
    'Especialista em instalações elétricas residenciais'
  ),
  (
    3,
    1,
    2,
    500.00,
    '3 dias',
    'Ofereço garantia de 6 meses no serviço'
  ),
  (
    4,
    3,
    3,
    750.00,
    '5 dias',
    'Pintura completa com tinta premium'
  ),
  (
    5,
    3,
    1,
    720.00,
    '4 dias',
    'Inclui preparação das paredes e acabamento'
  ),
  (
    6,
    4,
    3,
    180.00,
    '1 dia',
    'Limpeza completa e organização'
  ),
  (
    7,
    5,
    1,
    350.00,
    '2 dias',
    'Manutenção e paisagismo básico'
  );

-- ============================================
-- INSERIR CONTRATOS
-- ============================================
INSERT INTO
  `contract` (
    `contract_id`,
    `request_id`,
    `service_provider_id`,
    `cliente_id`,
    `contract_terms`,
    `status`
  )
VALUES
  (
    1,
    1,
    1,
    1,
    'Serviço de encanamento com garantia de 3 meses. Materiais inclusos.',
    'completed'
  ),
  (
    2,
    2,
    2,
    2,
    'Instalação elétrica completa com certificado de segurança.',
    'completed'
  ),
  (
    3,
    3,
    3,
    1,
    'Pintura completa de apartamento de 2 quartos com tinta Suvinil.',
    'active'
  ),
  (
    4,
    4,
    3,
    3,
    'Limpeza geral pós-obra com produtos profissionais.',
    'completed'
  );

-- ============================================
-- INSERIR PAGAMENTOS
-- ============================================
INSERT INTO
  `payment` (
    `payment_id`,
    `contract_id`,
    `amount`,
    `payment_date`,
    `status`
  )
VALUES
  (1, 1, 450.00, '2025-09-25', 'completed'),
  (2, 2, 280.00, '2025-09-20', 'completed'),
  (3, 3, 375.00, NULL, 'pending'),
  (4, 3, 375.00, NULL, 'pending'),
  (5, 4, 180.00, '2025-09-22', 'completed');

-- ============================================
-- INSERIR AVALIAÇÕES
-- ============================================
INSERT INTO
  `review` (
    `review_id`,
    `contract_id`,
    `cliente_id`,
    `rating`,
    `comment`
  )
VALUES
  (
    1,
    1,
    1,
    5,
    'Excelente serviço! Muito profissional e pontual.'
  ),
  (
    2,
    2,
    2,
    4,
    'Bom trabalho, mas poderia ter sido mais rápido.'
  ),
  (3, 4, 3, 5, 'Limpeza impecável! Recomendo muito.');

-- ============================================
-- INSERIR MENSAGENS DE CHAT
-- ============================================
INSERT INTO
  `chat` (
    `chat_id`,
    `cliente_id`,
    `service_provider_id`,
    `message`
  )
VALUES
  (
    1,
    1,
    1,
    'Olá, gostaria de saber mais detalhes sobre o serviço de encanamento.'
  ),
  (
    2,
    1,
    1,
    'Claro! Posso fazer uma visita técnica amanhã para avaliar o problema.'
  ),
  (
    3,
    2,
    2,
    'Preciso de uma instalação elétrica urgente. Está disponível?'
  ),
  (
    4,
    2,
    2,
    'Sim, posso atender ainda esta semana. Qual o melhor horário para você?'
  );