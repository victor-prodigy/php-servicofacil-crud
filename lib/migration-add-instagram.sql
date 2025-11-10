-- ============================================
-- MIGRAÇÃO: Adicionar campo 'instagram' à tabela cliente
-- ============================================
USE servicofacil;

-- Verificar se a coluna já existe antes de adicionar
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'servicofacil' 
    AND TABLE_NAME = 'cliente' 
    AND COLUMN_NAME = 'instagram'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `cliente` ADD COLUMN `instagram` VARCHAR(100) DEFAULT NULL AFTER `user_id`',
    'SELECT ''Coluna instagram já existe na tabela cliente'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

