# 🗃️ Sistema de Migrations - Algorise

## 📋 Visão Geral
Este diretório contém todas as migrations (migrações) do banco de dados do sistema Algorise. As migrations são scripts SQL versionados que permitem evoluir a estrutura do banco de dados de forma controlada e rastreável.

## 📁 Estrutura de Arquivos
```
migrations/
├── README.md                                    # Este arquivo
├── 2024-01-15_criar_tabela_logs_sistema.sql    # Exemplo: Nova tabela
├── 2024-01-16_adicionar_auditoria_tabelas.sql  # Exemplo: Alterar tabelas
└── YYYY-MM-DD_descrição_da_mudança.sql         # Padrão de nomenclatura
```

## 🏷️ Convenções de Nomenclatura

### Padrão de Nome
```
YYYY-MM-DD_descrição_da_mudança.sql
```

### Exemplos de Nomes Válidos
- `2024-01-15_criar_tabela_usuarios.sql`
- `2024-01-16_alterar_coluna_processos.sql`
- `2024-01-17_adicionar_indices_performance.sql`
- `2024-01-18_remover_tabela_deprecated.sql`

### Tipos de Prefixos Recomendados
- `criar_tabela_*` - Para criação de novas tabelas
- `alterar_*` - Para modificações em tabelas existentes
- `adicionar_*` - Para adição de colunas, índices, etc.
- `remover_*` - Para remoção de elementos
- `corrigir_*` - Para correções de dados
- `otimizar_*` - Para melhorias de performance

## 📝 Estrutura Padrão de Migration

Cada arquivo de migration deve seguir esta estrutura:

```
-- ===============================================
-- Migration: [Título descritivo]
-- Data: YYYY-MM-DD
-- Autor: [Nome do autor]
-- Descrição: [Descrição detalhada da mudança]
-- ===============================================

-- IMPORTANTE: 
-- 1. Sempre testar em ambiente de desenvolvimento primeiro
-- 2. Fazer backup antes de aplicar em produção
-- 3. Verificar dependências e conflitos

-- ===============================================
-- VERIFICAÇÕES PRÉ-MIGRATION
-- ===============================================
-- Scripts para verificar estado atual

-- ===============================================
-- FORWARD MIGRATION (Aplicar mudanças)
-- ===============================================
-- Scripts SQL para aplicar as mudanças

-- ===============================================
-- VERIFICAÇÕES PÓS-MIGRATION
-- ===============================================
-- Scripts para validar se migration foi aplicada corretamente

-- ===============================================
-- ROLLBACK MIGRATION (Reverter mudanças)
-- ===============================================
-- Scripts SQL para reverter as mudanças (comentados)

-- ===============================================
-- NOTAS DE ROLLBACK
-- ===============================================
-- Instruções específicas para rollback

-- ===============================================
-- VALIDAÇÃO FINAL
-- ===============================================
-- Checklist de validação pós-aplicação
```

## 🚀 Como Aplicar Migrations

### 1. Ambiente de Desenvolvimento
```bash
# Conectar no container do banco
docker exec -it db_db mysql -u root -p buscaprecos

# Aplicar migration
source /caminho/para/migration/arquivo.sql
```

### 2. Ambiente de Produção (Manual)
```bash
# 1. SEMPRE fazer backup primeiro
docker exec db_db mysqldump -u root -p buscaprecos > backup_$(date +%Y%m%d_%H%M%S).sql

# 2. Aplicar migration
docker exec -i db_db mysql -u root -p buscaprecos < migration_file.sql

# 3. Verificar se foi aplicada corretamente
docker exec -it db_db mysql -u root -p -e "SHOW TABLES;" buscaprecos
```

### 3. Via GitHub Actions (Automático)
As migrations são aplicadas automaticamente quando:
- Arquivos são adicionados/modificados na pasta `migrations/`
- Push é feito para branch `main`
- Workflow `deploy-with-migration.yml` é executado

## ⚠️ Regras e Boas Práticas

### ✅ O Que FAZER
1. **Sempre** fazer backup antes de aplicar
2. **Sempre** testar em ambiente de desenvolvimento primeiro
3. **Sempre** incluir verificações pré e pós-migration
4. **Sempre** incluir scripts de rollback (comentados)
5. **Sempre** usar nomenclatura padronizada
6. **Sempre** documentar o que a migration faz
7. **Sempre** verificar dependências entre migrations
8. **Sempre** usar `IF EXISTS` e `IF NOT EXISTS` quando apropriado

### ❌ O Que NÃO Fazer
1. **NUNCA** aplicar diretamente em produção sem teste
2. **NUNCA** modificar migrations já aplicadas em produção
3. **NUNCA** fazer rollback sem backup
4. **NUNCA** ignorar erros durante aplicação
5. **NUNCA** aplicar migrations manualmente sem controle
6. **NUNCA** misturar mudanças de estrutura com mudanças de dados críticos
7. **NUNCA** assumir que migration foi aplicada sem verificar

## 🔄 Estratégias de Rollback

### Rollback Automático
O GitHub Actions faz rollback automático em caso de falha:
1. Detecta erro na aplicação da migration
2. Restaura backup automaticamente
3. Para o processo de deploy
4. Notifica sobre a falha

### Rollback Manual
```
# 1. Conectar na VPS
ssh root@194.163.131.97

# 2. Listar backups disponíveis
ls -la /root/backups/

# 3. Restaurar backup específico
docker exec -i db_db mysql -u root -p buscaprecos < backup_file.sql

# 4. Verificar restauração
docker exec -it db_db mysql -u root -p -e "SHOW TABLES;" buscaprecos

# 5. Reiniciar aplicação
docker service update --force app_app
```

## 📊 Monitoramento e Logs

### Verificar Status das Migrations
```sql
-- Verificar se migration foi aplicada (exemplo)
SELECT TABLE_NAME 
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'nova_tabela';

-- Verificar colunas adicionadas
SELECT COLUMN_NAME, DATA_TYPE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tabela_modificada';
```

### Logs do GitHub Actions
- Acesse: GitHub → Actions → Workflows
- Verifique logs detalhados de cada step
- Monitore falhas e sucessos

## 🛠️ Comandos Úteis

### Backup Manual
```bash
# Backup completo
docker exec db_db mysqldump -u root -p buscaprecos > backup_manual_$(date +%Y%m%d_%H%M%S).sql

# Backup apenas estrutura
docker exec db_db mysqldump -u root -p --no-data buscaprecos > structure_backup.sql

# Backup apenas dados
docker exec db_db mysqldump -u root -p --no-create-info buscaprecos > data_backup.sql
```

### Validação de Migrations
```bash
# Verificar sintaxe SQL
mysql --help | grep -A1 "Default options"

# Testar connection
docker exec -it db_db mysql -u root -p -e "SELECT VERSION();"
```

## 📋 Checklist Pré-Deploy

Antes de fazer push de uma nova migration:

- [ ] Migration testada em ambiente de desenvolvimento
- [ ] Backup do banco de desenvolvimento realizado
- [ ] Verificações pré-migration incluídas
- [ ] Scripts de rollback documentados
- [ ] Nomenclatura seguindo padrão
- [ ] Documentação da migration completa
- [ ] Dependências verificadas
- [ ] Impacto na aplicação avaliado

## 🆘 Troubleshooting

### Problemas Comuns

**Migration falha por tabela já existir:**
```
-- Usar IF NOT EXISTS
CREATE TABLE IF NOT EXISTS nova_tabela (...);
```

**Migration falha por coluna já existir:**
```
-- Verificar antes de adicionar
ALTER TABLE tabela ADD COLUMN IF NOT EXISTS nova_coluna VARCHAR(255);
```

**Rollback não funciona:**
- Verificar se backup existe
- Verificar permissões do arquivo
- Verificar conectividade com banco

**GitHub Actions falha:**
- Verificar secrets configurados
- Verificar conectividade SSH
- Verificar logs detalhados

## 📞 Suporte

Em caso de dúvidas ou problemas:
1. Verificar logs do GitHub Actions
2. Verificar documentação no [DEPLOY-GUIDE.md](../DEPLOY-GUIDE.md)
3. Fazer rollback se necessário
4. Analisar migration step-by-step

---

*Última atualização: 2024-01-15*
*Mantenha este documento sempre atualizado com novas práticas!*