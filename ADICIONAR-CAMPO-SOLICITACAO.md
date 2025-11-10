USE servicofacil;

-- ============================================
-- ADICIONAR CAMPO observacao NA TABELA service_request
-- ============================================
ALTER TABLE `service_request`
ADD COLUMN `observacao` VARCHAR(200) DEFAULT NULL
AFTER `cidade`;
