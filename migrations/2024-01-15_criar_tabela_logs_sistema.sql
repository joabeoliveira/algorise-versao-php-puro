-- ===============================================
-- Migration: Exemplo de criação de tabela
-- Data: 2024-01-15
-- Autor: Sistema de Migrations
-- Descrição: Criação da tabela de logs do sistema
-- ===============================================

-- IMPORTANTE: 
-- 1. Sempre testar em ambiente de desenvolvimento primeiro
-- 2. Fazer backup antes de aplicar em produção
-- 3. Verificar se não há conflitos com tabelas existentes

-- ===============================================
-- FORWARD MIGRATION (Aplicar mudanças)
-- ===============================================

CREATE TABLE IF NOT EXISTS logs_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    acao VARCHAR(100) NOT NULL,
    tabela_afetada VARCHAR(50) NULL,
    registro_id INT NULL,
    detalhes JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Índices para performance
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_acao (acao),
    INDEX idx_tabela_afetada (tabela_afetada),
    INDEX idx_created_at (created_at),
    
    -- Chave estrangeira (se tabela usuarios existir)
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir dados iniciais (se necessário)
INSERT INTO logs_sistema (acao, detalhes, created_at) VALUES 
('SYSTEM_INIT', '{"message": "Sistema de logs inicializado", "version": "1.0"}', NOW());

-- ===============================================
-- VERIFICAÇÕES PÓS-MIGRATION
-- ===============================================

-- Verificar se tabela foi criada
SELECT 
    TABLE_NAME,
    ENGINE,
    TABLE_ROWS,
    CREATE_TIME
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'logs_sistema';

-- Verificar estrutura da tabela
DESCRIBE logs_sistema;

-- ===============================================
-- ROLLBACK MIGRATION (Reverter mudanças)
-- ===============================================
-- ATENÇÃO: Descomente apenas se precisar fazer rollback manual!
-- CUIDADO: Isto irá apagar todos os dados da tabela!

-- DROP TABLE IF EXISTS logs_sistema;

-- ===============================================
-- NOTAS DE ROLLBACK
-- ===============================================
-- Para fazer rollback desta migration:
-- 1. Fazer backup dos dados se necessário:
--    CREATE TABLE logs_sistema_backup AS SELECT * FROM logs_sistema;
-- 2. Executar o comando DROP acima
-- 3. Verificar se outras tabelas dependem desta (chaves estrangeiras)

-- ===============================================
-- VALIDAÇÃO FINAL
-- ===============================================
-- Após aplicar esta migration, verificar:
-- 1. Tabela criada com sucesso: SHOW TABLES LIKE 'logs_sistema';
-- 2. Estrutura correta: DESCRIBE logs_sistema;
-- 3. Dados iniciais inseridos: SELECT COUNT(*) FROM logs_sistema;
-- 4. Índices criados: SHOW INDEX FROM logs_sistema;