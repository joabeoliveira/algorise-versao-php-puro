-- ===============================================
-- Migration: Exemplo de alteração de tabela existente
-- Data: 2024-01-16
-- Autor: Sistema de Migrations
-- Descrição: Adição de colunas de auditoria nas tabelas principais
-- ===============================================

-- IMPORTANTE: 
-- 1. Esta migration altera tabelas existentes
-- 2. Backup é OBRIGATÓRIO antes de aplicar
-- 3. Testar em cópia da produção primeiro

-- ===============================================
-- VERIFICAÇÕES PRÉ-MIGRATION
-- ===============================================

-- Verificar se as tabelas existem
SELECT TABLE_NAME 
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME IN ('processos', 'itens', 'fornecedores', 'usuarios');

-- ===============================================
-- FORWARD MIGRATION (Aplicar mudanças)
-- ===============================================

-- Adicionar colunas de auditoria na tabela processos
ALTER TABLE processos 
ADD COLUMN IF NOT EXISTS created_by INT NULL AFTER numero,
ADD COLUMN IF NOT EXISTS updated_by INT NULL AFTER created_by,
ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL AFTER updated_at,
ADD COLUMN IF NOT EXISTS deleted_by INT NULL AFTER deleted_at;

-- Adicionar índices para as novas colunas
ALTER TABLE processos
ADD INDEX IF NOT EXISTS idx_created_by (created_by),
ADD INDEX IF NOT EXISTS idx_updated_by (updated_by),
ADD INDEX IF NOT EXISTS idx_deleted_at (deleted_at);

-- Adicionar colunas de auditoria na tabela itens
ALTER TABLE itens
ADD COLUMN IF NOT EXISTS created_by INT NULL AFTER descricao,
ADD COLUMN IF NOT EXISTS updated_by INT NULL AFTER created_by,
ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL AFTER updated_at,
ADD COLUMN IF NOT EXISTS deleted_by INT NULL AFTER deleted_at;

-- Adicionar índices para as novas colunas
ALTER TABLE itens
ADD INDEX IF NOT EXISTS idx_created_by (created_by),
ADD INDEX IF NOT EXISTS idx_updated_by (updated_by),
ADD INDEX IF NOT EXISTS idx_deleted_at (deleted_at);

-- Adicionar colunas de auditoria na tabela fornecedores
ALTER TABLE fornecedores
ADD COLUMN IF NOT EXISTS created_by INT NULL AFTER email,
ADD COLUMN IF NOT EXISTS updated_by INT NULL AFTER created_by,
ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL AFTER updated_at,
ADD COLUMN IF NOT EXISTS deleted_by INT NULL AFTER deleted_at;

-- Adicionar índices para as novas colunas
ALTER TABLE fornecedores
ADD INDEX IF NOT EXISTS idx_created_by (created_by),
ADD INDEX IF NOT EXISTS idx_updated_by (updated_by),
ADD INDEX IF NOT EXISTS idx_deleted_at (deleted_at);

-- ===============================================
-- ATUALIZAR DADOS EXISTENTES (Opcional)
-- ===============================================

-- Definir usuário padrão para registros existentes (substitua 1 pelo ID do admin)
-- UPDATE processos SET created_by = 1, updated_by = 1 WHERE created_by IS NULL;
-- UPDATE itens SET created_by = 1, updated_by = 1 WHERE created_by IS NULL;
-- UPDATE fornecedores SET created_by = 1, updated_by = 1 WHERE created_by IS NULL;

-- ===============================================
-- VERIFICAÇÕES PÓS-MIGRATION
-- ===============================================

-- Verificar se colunas foram adicionadas em processos
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'processos' 
AND COLUMN_NAME IN ('created_by', 'updated_by', 'deleted_at', 'deleted_by');

-- Verificar se colunas foram adicionadas em itens
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'itens' 
AND COLUMN_NAME IN ('created_by', 'updated_by', 'deleted_at', 'deleted_by');

-- Verificar se colunas foram adicionadas em fornecedores
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'fornecedores' 
AND COLUMN_NAME IN ('created_by', 'updated_by', 'deleted_at', 'deleted_by');

-- ===============================================
-- ROLLBACK MIGRATION (Reverter mudanças)
-- ===============================================
-- ATENÇÃO: Descomente apenas se precisar fazer rollback manual!
-- CUIDADO: Isto irá remover as colunas e seus dados!

-- -- Remover colunas da tabela processos
-- ALTER TABLE processos 
-- DROP COLUMN IF EXISTS deleted_by,
-- DROP COLUMN IF EXISTS deleted_at,
-- DROP COLUMN IF EXISTS updated_by,
-- DROP COLUMN IF EXISTS created_by;

-- -- Remover colunas da tabela itens
-- ALTER TABLE itens 
-- DROP COLUMN IF EXISTS deleted_by,
-- DROP COLUMN IF EXISTS deleted_at,
-- DROP COLUMN IF EXISTS updated_by,
-- DROP COLUMN IF EXISTS created_by;

-- -- Remover colunas da tabela fornecedores
-- ALTER TABLE fornecedores 
-- DROP COLUMN IF EXISTS deleted_by,
-- DROP COLUMN IF EXISTS deleted_at,
-- DROP COLUMN IF EXISTS updated_by,
-- DROP COLUMN IF EXISTS created_by;

-- ===============================================
-- NOTAS DE ROLLBACK
-- ===============================================
-- Para fazer rollback desta migration:
-- 1. Fazer backup dos dados das novas colunas se necessário
-- 2. Verificar se algum código já está usando essas colunas
-- 3. Executar os comandos DROP acima
-- 4. Reiniciar aplicação para evitar erros de campo inexistente

-- ===============================================
-- VALIDAÇÃO FINAL
-- ===============================================
-- Após aplicar esta migration, verificar:
-- 1. Todas as colunas foram criadas
-- 2. Índices foram criados corretamente: SHOW INDEX FROM processos;
-- 3. Não há erros na aplicação ao acessar essas tabelas
-- 4. Funcionalidades de soft delete funcionando (se implementadas)