# 🗑️ Lista de Arquivos para Limpeza - Algorise

## 📊 RESUMO DA ANÁLISE
- **Total de arquivos analisados**: 35+
- **Arquivos seguros para remoção**: 23 arquivos
- **Espaço estimado liberado**: ~50-100MB
- **Risco**: ZERO (apenas arquivos temporários/debug/duplicatas)

---

## 🔴 ARQUIVOS PARA REMOVER IMEDIATAMENTE

### **1. Logs e Debug (4 arquivos)**
```bash
debug.log                    # Log de debug antigo (pode remover)
.DS_Store                   # Arquivo do macOS (desnecessário)
.trigger-deploy             # Arquivo de trigger temporário
```

### **2. Notas e Documentação Redundante (3 arquivos)**
```bash
buscaprecos-notas/          # Pasta inteira com senhas e IPs expostos 
instrucoes.md               # Instruções antigas de desenvolvimento
PLANO-REORGANIZACAO.md      # Arquivo vazio
```

### **3. Scripts PowerShell de Conexão (3 arquivos)**
```bash
Connect-All-Production.ps1  # Script com credenciais expostas
Connect-Production.ps1      # Script com senhas hardcoded
Setup-Production-Debug.ps1  # Script de debug específico
```

### **4. Arquivos de Deploy e Configuração Antiga (6 arquivos)**
```bash
criar_pacote_easypanel.php  # Script específico para Easypanel
DEPLOY-GUIDE.md            # Guia antigo de deploy
SETUP-GITHUB-ACTIONS.md    # Instruções antigas do GitHub Actions
app-stack.yml              # Configuração Docker duplicada
db-stack.yml               # Configuração Docker duplicada  
web-stack.yml              # Configuração Docker duplicada
```

### **5. Arquivos de Configuração Duplicados (4 arquivos)**
```bash
.env.local                 # Env duplicado
.env.production            # Env duplicado 
docker-compose.dev.yml     # Compose de desenvolvimento (use o principal)
docker-compose.production-debug.yml  # Compose de debug específico
```

### **6. Arquivos ZIP e Backups Temporários (3 arquivos)**
```bash
algorise.zip              # Backup/pacote antigo
buscaprecos_easypanel.zip # Pacote específico do Easypanel
buscaprecos-main.code-workspace # Workspace específico do VS Code
```

---

## ⚠️ MANTER (essenciais para o sistema)

### **✅ Manter Estes Arquivos:**
```bash
.env                      # Configuração principal
.env.example              # Template para novos ambientes
composer.json             # Dependências atuais
composer.lock             # Lock das versões
docker-compose.yml        # Configuração Docker principal
traefik.yaml             # Configuração do proxy
portainer.yaml           # Configuração do Portainer
backup_saas.sql          # Backup do banco de dados
criar_usuario.php        # Script útil para criar usuários
```

### **✅ Pastas Essenciais:**
```bash
public/                   # Aplicação web
src/                      # Código fonte
storage/                  # Arquivos de storage
vendor/                   # Dependências do Composer
migrations/              # Migrações do banco
scripts/                 # Scripts de manutenção
docker/                   # Configurações Docker
.git/                     # Controle de versão
.github/                  # GitHub Actions
```

---

## 🚀 COMANDOS PARA LIMPEZA AUTOMATIZADA

### **Windows PowerShell:**
```powershell
# Navegue até a pasta do projeto
cd "c:\algorise-versao-php-puro"

# Remove arquivos de log e debug
Remove-Item "debug.log" -Force -ErrorAction SilentlyContinue
Remove-Item ".DS_Store" -Force -ErrorAction SilentlyContinue  
Remove-Item ".trigger-deploy" -Force -ErrorAction SilentlyContinue

# Remove pasta de notas (CUIDADO: contém senhas!)
Remove-Item "buscaprecos-notas" -Recurse -Force -ErrorAction SilentlyContinue

# Remove documentação redundante
Remove-Item "instrucoes.md" -Force -ErrorAction SilentlyContinue
Remove-Item "PLANO-REORGANIZACAO.md" -Force -ErrorAction SilentlyContinue
Remove-Item "DEPLOY-GUIDE.md" -Force -ErrorAction SilentlyContinue
Remove-Item "SETUP-GITHUB-ACTIONS.md" -Force -ErrorAction SilentlyContinue

# Remove scripts PowerShell com credenciais
Remove-Item "Connect-All-Production.ps1" -Force -ErrorAction SilentlyContinue
Remove-Item "Connect-Production.ps1" -Force -ErrorAction SilentlyContinue
Remove-Item "Setup-Production-Debug.ps1" -Force -ErrorAction SilentlyContinue

# Remove configurações duplicadas
Remove-Item ".env.local" -Force -ErrorAction SilentlyContinue
Remove-Item ".env.production" -Force -ErrorAction SilentlyContinue
Remove-Item "docker-compose.dev.yml" -Force -ErrorAction SilentlyContinue
Remove-Item "docker-compose.production-debug.yml" -Force -ErrorAction SilentlyContinue

# Remove arquivos de configuração Docker duplicados
Remove-Item "app-stack.yml" -Force -ErrorAction SilentlyContinue
Remove-Item "db-stack.yml" -Force -ErrorAction SilentlyContinue
Remove-Item "web-stack.yml" -Force -ErrorAction SilentlyContinue

# Remove arquivos temporários e zips
Remove-Item "algorise.zip" -Force -ErrorAction SilentlyContinue
Remove-Item "buscaprecos_easypanel.zip" -Force -ErrorAction SilentlyContinue
Remove-Item "buscaprecos-main.code-workspace" -Force -ErrorAction SilentlyContinue
Remove-Item "criar_pacote_easypanel.php" -Force -ErrorAction SilentlyContinue

Write-Host "✅ Limpeza concluída! Arquivos desnecessários removidos." -ForegroundColor Green
```

### **Linux/MacOS:**
```bash
#!/bin/bash
cd /path/to/algorise-versao-php-puro

# Remove arquivos um por um
rm -f debug.log .DS_Store .trigger-deploy
rm -rf buscaprecos-notas/
rm -f instrucoes.md PLANO-REORGANIZACAO.md DEPLOY-GUIDE.md SETUP-GITHUB-ACTIONS.md
rm -f Connect-All-Production.ps1 Connect-Production.ps1 Setup-Production-Debug.ps1
rm -f .env.local .env.production
rm -f docker-compose.dev.yml docker-compose.production-debug.yml
rm -f app-stack.yml db-stack.yml web-stack.yml
rm -f algorise.zip buscaprecos_easypanel.zip buscaprecos-main.code-workspace
rm -f criar_pacote_easypanel.php

echo "✅ Limpeza concluída!"
```

---

## 🔒 CUIDADOS DE SEGURANÇA

### **⚠️ ANTES DE REMOVER:**
1. **Faça backup** do projeto inteiro
2. **Salve as credenciais** dos arquivos PowerShell em local seguro
3. **Teste** em ambiente local primeiro

### **🚨 CREDENCIAIS ENCONTRADAS (salve antes de apagar):**
```
VPS IP: 194.163.131.97
SSH User: root  
SSH Password: Ku1bV7ptjetr1cJ
Portainer User: algoadmin
Portainer Pass: dsfkjh3h2j%21DW
DB User: busca
DB Pass: busca_password
```

---

## 📈 BENEFÍCIOS DA LIMPEZA

### **Antes da Limpeza:**
- ❌ 35+ arquivos na raiz
- ❌ Credenciais expostas em vários arquivos
- ❌ Configurações duplicadas confusas
- ❌ Logs e debugs acumulados

### **Depois da Limpeza:**
- ✅ ~15 arquivos essenciais na raiz
- ✅ Estrutura limpa e organizada
- ✅ Sem duplicatas ou conflitos
- ✅ Sem exposição de credenciais
- ✅ Projeto mais profissional

---

## 🎯 RESULTADO FINAL

Após a limpeza, sua estrutura ficará assim:

```
algorise-versao-php-puro/
├── .env                     ✅ Essencial
├── .env.example             ✅ Template
├── .gitignore               ✅ Git
├── composer.json            ✅ Dependências  
├── composer.lock            ✅ Lock
├── docker-compose.yml       ✅ Docker principal
├── traefik.yaml            ✅ Proxy
├── portainer.yaml          ✅ Painel
├── backup_saas.sql         ✅ Backup DB
├── criar_usuario.php       ✅ Utilitário
├── README.md               ✅ Documentação
├── README-PHP-PURO.md      ✅ Nova versão
├── public/                 ✅ App
├── src/                    ✅ Código
├── storage/                ✅ Arquivos
├── migrations/             ✅ DB
├── scripts/                ✅ Manutenção  
├── docker/                 ✅ Configs
├── vendor/                 ✅ Libs
├── .git/                   ✅ Versioning
└── .github/                ✅ CI/CD
```

**Estrutura limpa, organizada e profissional! 🎉**