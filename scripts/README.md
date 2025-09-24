# 🛠️ Scripts de Deploy e Backup - Algorise

Este diretório contém scripts auxiliares para facilitar o deploy e backup da aplicação Algorise.

## 📁 Arquivos Disponíveis

### 🗃️ `backup.sh`
Script completo para backup do banco de dados com múltiplas opções.

**Uso:**
```bash
# Backup completo
./scripts/backup.sh full "Backup antes da migração"

# Backup apenas estrutura
./scripts/backup.sh structure "Estrutura v1.0"

# Backup apenas dados
./scripts/backup.sh data "Dados de produção"

# Listar backups existentes
./scripts/backup.sh list

# Limpar backups antigos (>30 dias)
./scripts/backup.sh cleanup
```

### 🚀 `deploy.sh`
Script para deploy manual da aplicação com diferentes opções.

**Uso:**
```bash
# Deploy apenas código
./scripts/deploy.sh code

# Deploy com migrations
./scripts/deploy.sh migration

# Rollback para versão anterior
./scripts/deploy.sh rollback

# Verificar status
./scripts/deploy.sh status

# Ver logs
./scripts/deploy.sh logs

# Health check
./scripts/deploy.sh health
```

### 🗃️ `apply-migrations.sh`
Script especializado para aplicação controlada de migrations.

**Uso:**
```bash
# Aplicar todas as migrations pendentes
./scripts/apply-migrations.sh apply

# Listar migrations pendentes
./scripts/apply-migrations.sh list

# Ver migrations já aplicadas
./scripts/apply-migrations.sh applied

# Simular aplicação (dry-run)
./scripts/apply-migrations.sh dry-run

# Status geral
./scripts/apply-migrations.sh status

# Com backup automático
./scripts/apply-migrations.sh apply --backup
```

## 🔧 Configuração Inicial

### 1. Dar Permissões de Execução
```bash
chmod +x scripts/*.sh
```

### 2. Verificar Dependências
Os scripts requerem:
- Docker em execução
- Containers `db_db`, `app_app`, `webserver_webserver` rodando
- Acesso ao diretório `/root/buscaprecos-main`
- Permissões para executar comandos Docker

### 3. Configurar Variáveis (Opcional)
Você pode definir variáveis de ambiente para evitar digitação de senhas:
```bash
export DB_PASSWORD="sua_senha_do_banco"
```

## 🚨 Segurança e Boas Práticas

### ⚠️ IMPORTANTE
- **SEMPRE** teste em ambiente de desenvolvimento primeiro
- **SEMPRE** faça backup antes de deploy em produção
- **NUNCA** execute scripts em produção sem entender o que fazem
- **SEMPRE** verifique logs após deploy

### 🔐 Senhas
- Scripts solicitam senha do banco interativamente
- Senhas nunca são expostas em logs
- Use variáveis de ambiente para automação

### 📋 Checklist Pré-Deploy
- [ ] Código testado em desenvolvimento
- [ ] Backup realizado
- [ ] Migrations validadas
- [ ] Horário de baixo tráfego
- [ ] Plano de rollback definido

## 🎯 Fluxos Recomendados

### Deploy Simples (sem BD)
```bash
# 1. Verificar status
./scripts/deploy.sh status

# 2. Deploy
./scripts/deploy.sh code

# 3. Verificar saúde
./scripts/deploy.sh health
```

### Deploy com Migrations
```bash
# 1. Backup
./scripts/backup.sh full "Pre-deploy backup"

# 2. Preview das migrations
./scripts/apply-migrations.sh dry-run

# 3. Deploy completo
./scripts/deploy.sh migration

# 4. Verificar
./scripts/deploy.sh health
```

### Rollback de Emergência
```bash
# 1. Rollback do código
./scripts/deploy.sh rollback

# 2. Se necessário, restaurar banco
# (usar backup mais recente)
```

## 🔍 Troubleshooting

### Problemas Comuns

**Erro de permissão:**
```bash
chmod +x scripts/*.sh
```

**Container não encontrado:**
```bash
docker service ls
# Verificar nomes corretos dos serviços
```

**Falha na conexão com banco:**
```bash
docker exec -it db_db mysql -u root -p
# Testar conexão manual
```

**Migration falha:**
```bash
# Verificar logs detalhados
./scripts/apply-migrations.sh status
# Ver última migration aplicada
```

### Logs e Debug

**Ver logs dos serviços:**
```bash
./scripts/deploy.sh logs
```

**Debug de migrations:**
```bash
./scripts/apply-migrations.sh dry-run
```

**Status completo:**
```bash
./scripts/deploy.sh status
./scripts/apply-migrations.sh status
```

## 📞 Suporte

Em caso de problemas:

1. **Verificar logs** dos scripts e serviços
2. **Consultar documentação** no [DEPLOY-GUIDE.md](../DEPLOY-GUIDE.md)
3. **Fazer rollback** se necessário
4. **Analisar step-by-step** o que deu errado

## 🔄 Automação via GitHub Actions

Estes scripts são também utilizados pelos workflows do GitHub Actions:
- [deploy-code.yml](../.github/workflows/deploy-code.yml)
- [deploy-with-migration.yml](../.github/workflows/deploy-with-migration.yml)
- [backup.yml](../.github/workflows/backup.yml)

---

*Última atualização: 2024-01-15*
*Mantenha os scripts sempre atualizados com as necessidades do projeto!*