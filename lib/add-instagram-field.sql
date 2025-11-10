-- ============================================
-- MIGRAÇÃO: Adicionar campo instagram à tabela user
-- ============================================
-- Este script adiciona um campo para armazenar o Instagram do usuário

USE servicofacil;

-- Verificar se a coluna já existe antes de adicionar
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'servicofacil' 
    AND TABLE_NAME = 'user' 
    AND COLUMN_NAME = 'instagram'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `user` ADD COLUMN `instagram` VARCHAR(100) DEFAULT NULL AFTER `phone_number`',
    'SELECT ''Coluna instagram já existe na tabela user'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

