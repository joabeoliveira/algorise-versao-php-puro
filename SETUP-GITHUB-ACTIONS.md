# 🚀 Configuração do GitHub Actions - Algorise

## 📋 Guia Completo de Configuração

### 1. 🔐 Configurar Secrets no GitHub

Acesse seu repositório no GitHub e configure os secrets:

**Caminho:** Settings > Secrets and Variables > Actions > New repository secret

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

### 2. 📁 Estrutura de Arquivos Criados

```
.github/
└── workflows/
    ├── deploy-code.yml          # Deploy simples (sem BD)
    ├── deploy-with-migration.yml # Deploy com migrations
    └── backup.yml               # Backup automático

migrations/
├── README.md                    # Documentação
├── 2024-01-15_criar_tabela_logs_sistema.sql
└── 2024-01-16_adicionar_auditoria_tabelas.sql

scripts/
├── README.md                    # Documentação
├── backup.sh                    # Script de backup
├── deploy.sh                    # Script de deploy
└── apply-migrations.sh          # Script de migrations

.env.example                     # Template de configuração
.gitignore                       # Atualizado com proteções
DEPLOY-GUIDE.md                  # Guia completo
```

### 3. 🔄 Como Funciona

#### Deploy Automático (Código)
- Trigger: Push para `main` (exceto pasta migrations)
- Processo: Testa → Deploy → Verifica

#### Deploy com Migration
- Trigger: Push para `main` com mudanças em `migrations/`
- Processo: Backup → Migrations → Deploy → Valida

#### Backup Automático
- Trigger: Todo dia às 02:00 UTC
- Processo: Backup → Comprime → Remove antigos

### 4. ✅ Checklist de Ativação

- [ ] Repositório no GitHub criado
- [ ] Secrets configurados
- [ ] Arquivos commitados
- [ ] Permissões SSH funcionando
- [ ] Containers em produção rodando
- [ ] Primeiro teste manual

### 5. 🧪 Teste Inicial

1. **Fazer um commit simples:**
```bash
git add .
git commit -m "docs: atualizar README"
git push origin main
```

2. **Verificar no GitHub:**
   - Actions > Workflows
   - Deve executar "Deploy Código"

3. **Teste com migration:**
```bash
# Criar nova migration
touch migrations/2024-01-17_teste.sql
git add migrations/
git commit -m "feat: migration de teste"
git push origin main
```

### 6. 🚨 Troubleshooting

**Falha de SSH:**
- Verificar secrets SSH_HOST, SSH_USER, SSH_PASSWORD
- Testar conexão manual: `ssh root@194.163.131.97`

**Falha de Database:**
- Verificar secrets DB_* 
- Verificar se container db_db está rodando

**Falha de Deploy:**
- Verificar logs no GitHub Actions
- Verificar se serviços estão rodando: `docker service ls`

### 7. 🎯 Próximos Passos

Após configuração inicial:

1. **Testar fluxo completo**
2. **Configurar notificações** (Slack, email)
3. **Ajustar horários** de backup se necessário
4. **Documentar** processo para equipe
5. **Treinar** desenvolvedores no novo fluxo

### 8. 📞 Comandos Úteis

**Verificar status:**
```bash
# Na VPS
./scripts/deploy.sh status
./scripts/apply-migrations.sh status
```

**Backup manual:**
```bash
./scripts/backup.sh full "Backup antes de mudança importante"
```

**Deploy manual (emergência):**
```bash
./scripts/deploy.sh code
./scripts/deploy.sh migration
```

---

## 🎉 Parabéns! 

Seu sistema de CI/CD está configurado e pronto para uso! 

O GitHub Actions agora irá:
- ✅ Detectar automaticamente se há mudanças de BD
- ✅ Aplicar o processo correto para cada tipo de deploy  
- ✅ Fazer backup automático antes de migrations
- ✅ Fazer rollback automático em caso de falha
- ✅ Manter backups diários automáticos

**Fluxo final:** Desenvolvimento → Git Push → Deploy Automático → Produção 🚀