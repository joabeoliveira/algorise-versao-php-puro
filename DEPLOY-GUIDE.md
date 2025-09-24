# 🚀 Guia Completo de Deploy - Algorise

## 📋 Índice
1. [Deploy de Código (sem alterações de BD)](#deploy-código)
2. [Deploy com Alterações de Banco de Dados](#deploy-banco)
3. [Configuração do GitHub Actions](#github-actions)
4. [Comandos Úteis](#comandos)
5. [Troubleshooting](#troubleshooting)

---

## 🔧 Deploy de Código (sem alterações de BD)

### ✅ Quando usar:
- Correções de bugs
- Novas funcionalidades que não alteram BD
- Mudanças de CSS/JavaScript
- Alterações de lógica de negócio

### 📝 Processo Simplificado:

#### 1. Desenvolvimento Local
```bash
# Iniciar ambiente de desenvolvimento
docker-compose -f docker-compose.dev.yml up -d

# Fazer suas alterações no código
# Testar localmente

# Parar ambiente
docker-compose -f docker-compose.dev.yml down
```

#### 2. Commit e Push
```bash
git add .
git commit -m "feat: descrição da funcionalidade"
git push origin main
```

#### 3. Deploy Automático (GitHub Actions)
- O GitHub Actions detecta o push
- Executa testes automáticos
- Faz deploy na produção automaticamente
- Reinicia apenas os containers necessários

#### 4. Verificação
- Verificar logs no Portainer
- Testar funcionalidade em produção
- Monitorar por alguns minutos

---

## 🗃️ Deploy com Alterações de Banco de Dados

### ⚠️ Quando usar:
- Criação de novas tabelas
- Alteração de estrutura de tabelas
- Adição/remoção de colunas
- Mudanças de índices

### 📝 Processo Completo:

#### 1. Desenvolvimento Local
```bash
# Iniciar ambiente de desenvolvimento
docker-compose -f docker-compose.dev.yml up -d

# Criar/alterar tabelas no banco local
# Desenvolver funcionalidade
# Testar completamente
```

#### 2. Criar Migration Scripts
```bash
# Criar diretório se não existir
mkdir -p migrations

# Criar arquivo de migration
# Exemplo: migrations/2024-01-15_criar_tabela_exemplo.sql
```

**Exemplo de Migration:**
```sql
-- migrations/2024-01-15_criar_tabela_exemplo.sql
-- Descrição: Criação da tabela para nova funcionalidade X

-- FORWARD MIGRATION
CREATE TABLE nova_funcionalidade (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir dados iniciais se necessário
INSERT INTO nova_funcionalidade (nome, descricao) VALUES 
('Item 1', 'Descrição do item 1'),
('Item 2', 'Descrição do item 2');

-- ROLLBACK MIGRATION (comentado, para uso manual se necessário)
-- DROP TABLE IF EXISTS nova_funcionalidade;
```

#### 3. Teste em Ambiente Híbrido
```bash
# IMPORTANTE: Fazer backup da produção primeiro
# Conectar via SSH na VPS e fazer backup:
# mysqldump --host=localhost --port=3307 --user=root --password=SENHA buscaprecos > backup_pre_migration_$(date +%Y%m%d_%H%M%S).sql

# Iniciar ambiente híbrido (conecta no banco de produção)
docker-compose -f docker-compose.production-debug.yml up -d

# Aplicar migration em CÓPIA da produção
# NUNCA aplicar diretamente na produção nesta etapa!

# Testar funcionalidade completamente
# Verificar se não quebrou nada existente

# Parar ambiente
docker-compose -f docker-compose.production-debug.yml down
```

#### 4. Commit e Documentação
```bash
git add .
git commit -m "feat: nova funcionalidade X com migration de BD

- Criação da tabela nova_funcionalidade
- Implementação da funcionalidade X
- Migration: 2024-01-15_criar_tabela_exemplo.sql"

git push origin main
```

#### 5. Deploy em Produção (Manual ou Automático)

##### 5.1. Backup Obrigatório
```bash
# Conectar na VPS via SSH
ssh root@194.163.131.97
# Senha: Ku1bV7ptjetr1cJ

# Navegar para diretório do projeto
cd /root/buscaprecos-main

# Backup da produção
docker exec db_db mysqldump -u root -proot_password_123 buscaprecos > backup_pre_deploy_$(date +%Y%m%d_%H%M%S).sql
```

##### 5.2. Aplicar Migration
```bash
# Conectar no container do banco
docker exec -it db_db mysql -u root -proot_password_123 buscaprecos

# Aplicar migration manualmente
source /root/buscaprecos-main/migrations/2024-01-15_criar_tabela_exemplo.sql

# Verificar se foi aplicada corretamente
SHOW TABLES;
DESCRIBE nova_funcionalidade;
```

##### 5.3. Deploy do Código
```bash
# Via Portainer ou comandos Docker
docker service update --force app_app
docker service update --force webserver_webserver
```

#### 6. Verificação Pós-Deploy
```bash
# Verificar logs
docker service logs app_app --tail 50
docker service logs webserver_webserver --tail 50

# Testar funcionalidade
# Verificar se não quebrou funcionalidades existentes
# Monitorar por 15-30 minutos
```

#### 7. Rollback (se necessário)
```bash
# Parar serviços
docker service update --replicas 0 app_app

# Restaurar backup
docker exec -i db_db mysql -u root -p buscaprecos < backup_pre_deploy_TIMESTAMP.sql

# Voltar versão anterior do código
git revert <commit_hash>
# ou
docker service update --image versao_anterior app_app

# Reiniciar serviços
docker service update --replicas 1 app_app
```

---

## 🤖 Configuração do GitHub Actions

### 📁 Estrutura de Arquivos
```
.github/
└── workflows/
    ├── deploy-code.yml          # Deploy simples (só código)
    ├── deploy-with-migration.yml # Deploy com BD
    └── backup.yml               # Backup automático
```

### 🔑 Secrets Necessários
No GitHub, configurar em Settings > Secrets and Variables > Actions:

**Secrets obrigatórios:**
```
SSH_HOST=194.163.131.97
SSH_USER=root
SSH_PASSWORD=Ku1bV7ptjetr1cJ
SSH_PORT=22
DB_ROOT_PASSWORD=root_password_123
DB_PASSWORD=busca_password
DB_USER=busca
DB_NAME=buscaprecos
PORTAINER_USER=algoadmin
PORTAINER_PASS=dsfkjh3h2j%21DW
```

**Como configurar:**
1. Acesse seu repositório no GitHub
2. Vá em Settings > Secrets and Variables > Actions
3. Clique em "New repository secret"
4. Adicione cada secret com nome e valor exatos

### 📝 Workflow para Deploy Simples
```yaml
# .github/workflows/deploy-code.yml
name: Deploy Código (sem BD)

on:
  push:
    branches: [ main ]
    paths-ignore:
      - 'migrations/**'

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@v3
    
    - name: Deploy via SSH
      uses: appleboy/ssh-action@v0.1.5
      with:
        host: ${{ secrets.SSH_HOST }}
        username: ${{ secrets.SSH_USER }}
        key: ${{ secrets.SSH_KEY }}
        script: |
          cd /caminho/para/buscaprecos
          git pull origin main
          docker service update --force app_app
          docker service update --force webserver_webserver
```

### 🗃️ Workflow para Deploy com BD
```yaml
# .github/workflows/deploy-with-migration.yml
name: Deploy com Migração de BD

on:
  push:
    branches: [ main ]
    paths:
      - 'migrations/**'

jobs:
  deploy-with-migration:
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@v3
    
    - name: Backup e Deploy
      uses: appleboy/ssh-action@v0.1.5
      with:
        host: ${{ secrets.SSH_HOST }}
        username: ${{ secrets.SSH_USER }}
        key: ${{ secrets.SSH_KEY }}
        script: |
          cd /caminho/para/buscaprecos
          
          # Backup automático
          docker exec db_db mysqldump -u root -p${{ secrets.DB_PASSWORD }} buscaprecos > backup_auto_$(date +%Y%m%d_%H%M%S).sql
          
          # Aplicar migrations (implementar lógica)
          # git pull origin main
          # aplicar_migrations.sh
          
          # Deploy do código
          docker service update --force app_app
          docker service update --force webserver_webserver
```

---

## 🛠️ Comandos Úteis

### 🏠 Desenvolvimento Local
```bash
# Iniciar ambiente completo
docker-compose -f docker-compose.dev.yml up -d

# Ver logs
docker-compose -f docker-compose.dev.yml logs -f

# Parar ambiente
docker-compose -f docker-compose.dev.yml down

# Rebuild após mudanças no Dockerfile
docker-compose -f docker-compose.dev.yml up -d --build

# Acessar aplicação local
http://localhost:8080
```

### 🔧 Debug com Produção
```bash
# Ambiente híbrido (app local + BD produção)
docker-compose -f docker-compose.production-debug.yml up -d

# CUIDADO: Sempre usar cópia da produção para testes!
# NUNCA testar diretamente no banco de produção!
```

### 🏭 Produção
```bash
# Conectar via SSH
ssh usuario@servidor

# Ver status dos serviços
docker service ls

# Ver logs
docker service logs app_app --tail 50
docker service logs webserver_webserver --tail 50

# Reiniciar serviço específico
docker service update --force app_app

# Backup manual
docker exec db_db mysqldump -u root -p buscaprecos > backup_$(date +%Y%m%d_%H%M%S).sql
```

### 🔄 Git
```bash
# Verificar arquivos alterados
git status

# Commit com padrão
git commit -m "tipo: descrição

- detalhes da mudança
- impacto esperado"

# Push
git push origin main

# Ver histórico
git log --oneline -10
```

---

## 🆘 Troubleshooting

### ❌ Container com Exit Code 2
```bash
# Ver logs detalhados
docker service logs app_app --tail 100

# Verificar variáveis de ambiente
docker service inspect app_app

# Reiniciar forçado
docker service update --force app_app
```

### 🗃️ Problemas de Conexão com BD
```bash
# Verificar se BD está rodando
docker service ls | grep db

# Testar conexão
docker exec -it db_db mysql -u root -p

# Verificar networks
docker network ls
```

### 🔧 Problemas de Permissão
```bash
# PowerShell (Windows)
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser

# Linux/Mac
chmod +x script.sh
```

### 🔄 Rollback de Emergência
```bash
# Parar aplicação
docker service update --replicas 0 app_app

# Restaurar backup
docker exec -i db_db mysql -u root -p buscaprecos < backup_file.sql

# Voltar código anterior
git reset --hard HEAD~1
git push --force origin main

# Reiniciar
docker service update --replicas 1 app_app
```

---

## 📚 Padrões e Convenções

### 📝 Commit Messages
```
feat: nova funcionalidade
fix: correção de bug
refactor: refatoração
docs: documentação
style: formatação
test: testes
chore: tarefas gerais
migration: alteração de BD
```

### 📁 Nomenclatura de Migrations
```
migrations/YYYY-MM-DD_descrição_da_mudança.sql
migrations/2024-01-15_criar_tabela_usuarios.sql
migrations/2024-01-16_alterar_coluna_processos.sql
```

### 🏷️ Tags de Versão
```bash
# Criar tag para release
git tag -a v1.0.0 -m "Release 1.0.0"
git push origin v1.0.0
```

---

## ⚠️ Avisos Importantes

1. **SEMPRE** fazer backup antes de alterações de BD
2. **NUNCA** testar migrations diretamente na produção
3. **SEMPRE** validar em ambiente de staging primeiro
4. **SEMPRE** ter plano de rollback preparado
5. **MONITORAR** aplicação por 15-30 min após deploy

---

*Última atualização: $(date +%Y-%m-%d)*
*Mantenha este documento sempre atualizado!*