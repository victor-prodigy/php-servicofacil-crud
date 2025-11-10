-- ============================================
-- SEED DATA - ADMINISTRADOR
-- ServiçoFácil - Sistema de Gestão de Serviços
-- ============================================
USE servicofacil;

-- ============================================
-- 1. INSERIR USUÁRIO ADMINISTRADOR
-- ============================================
-- Verificar se já existe e limpar se necessário
DELETE FROM `user`
WHERE
    `email` = 'admin@servicofacil.com';

-- Inserir usuário administrador
-- Email: admin@servicofacil.com
-- Senha: admin123
-- Hash da senha gerado com: password_hash('admin123', PASSWORD_DEFAULT)
-- NOTA: O hash pode variar a cada execução. Use o script PHP seed-admin.php para garantir um hash válido.
INSERT INTO
    `user` (
        `email`,
        `password`,
        `name`,
        `phone_number`,
        `user_type`,
        `status`,
        `identity_verified`,
        `created_at`,
        `updated_at`
    )
VALUES
    (
        'admin@servicofacil.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'Administrador do Sistema',
        '(11) 99999-9999',
        'administrador',
        'ativo',
        TRUE,
        CURRENT_TIMESTAMP,
        CURRENT_TIMESTAMP
    )
ON DUPLICATE KEY UPDATE
    `password` = VALUES(`password`),
    `name` = VALUES(`name`),
    `phone_number` = VALUES(`phone_number`),
    `status` = 'ativo',
    `identity_verified` = TRUE,
    `updated_at` = CURRENT_TIMESTAMP;

-- ============================================
-- 2. CONFIRMAR INSERÇÃO
-- ============================================
SELECT
    user_id,
    email,
    name,
    user_type,
    status,
    identity_verified,
    created_at
FROM
    `user`
WHERE
    `user_type` = 'administrador';

-- ============================================
-- 3. INFORMAÇÕES DE ACESSO
-- ============================================
/*
CREDENCIAIS DO ADMINISTRADOR:
=============================
Email: admin@servicofacil.com
Senha: admin123

URL DE ACESSO:
==============
Login: http://localhost/php-servicofacil-crud-incompleto/client/login/administrador-signin.html
Dashboard: http://localhost/php-servicofacil-crud-incompleto/client/administrador-dashboard.html

COMO EXECUTAR:
==============
1. Via SQL: Execute este arquivo no MySQL/MariaDB
2. Via PHP: Execute o script php/seed-admin.php no terminal
   php seed-admin.php

FUNCIONALIDADES:
================
- Visualizar todos os usuários (clientes e prestadores)
- Ativar/Desativar usuários
- Ver detalhes completos dos usuários
- Estatísticas do sistema
- Filtros e busca avançada
- Excluir usuários (com validações)

SEGURANÇA:
==========
- Senha criptografada com bcrypt
- Verificação de autenticação em todas as páginas
- Sessão administrativa separada
- Validações de segurança para exclusões
 */