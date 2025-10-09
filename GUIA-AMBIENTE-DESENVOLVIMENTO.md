# 🚀 Guia Ambiente de Desenvolvimento - Algorise

**Projeto:** algorise-versao-php-puro  
**Versão:** PHP Puro (migração do Slim Framework)  
**Data:** 09/10/2025

---

## ⚡ **Início Rápido**

### **1. 🔧 Pré-requisitos**
- **XAMPP** instalado e funcionando
- **PHP 8.2+** ativo
- **Composer** instalado
- **Banco** `algorise_db` importado

### **2. 🚀 Inicialização (4 passos)**

#### **Passo 1: Abrir XAMPP Control Panel**
- Iniciar **Apache** ✅
- Iniciar **MySQL** ✅

#### **Passo 2: Navegação e Dependências**
```powershell
cd C:\xampp\htdocs\algorise-versao-php-puro
composer install
```

#### **Passo 3: Opções de Servidor**

**🎯 Opção 1: XAMPP (Recomendado para desenvolvimento)**
```powershell
# Acesse diretamente via XAMPP Apache
http://localhost/algorise-versao-php-puro
```

**🛠️ Opção 2: Servidor PHP Built-in**
```powershell
php -S localhost:8080 -t public
# Depois acesse: http://localhost:8080
```

#### **Passo 4: Login no Sistema**
- **URL:** http://localhost/algorise-versao-php-puro (XAMPP) ou http://localhost:8080 (PHP built-in)
- **Email:** `admin@algorise.com`  
- **Senha:** `admin123`

---

## 📁 **Estrutura do Projeto**

```
algorise-versao-php-puro/
├── public/
│   ├── index.php            # Router principal (PHP Puro)
│   ├── .htaccess           # Configuração Apache
│   └── uploads/            # Arquivos enviados
├── src/
│   ├── Controller/         # Controllers MVC
│   ├── Core/              # Router, PDF, Mail
│   ├── View/              # Templates PHP
│   └── settings-php-puro.php  # Configurações
├── migrations/            # Scripts SQL de migração
├── storage/propostas/     # PDFs de propostas
├── vendor/               # Dependências Composer
├── .env                  # Variáveis de ambiente
├── algorise_db.sql       # Estrutura do banco (ATUALIZADA)
└── composer.json         # Dependências do projeto
```

---

## 🔧 **Comandos Úteis para Desenvolvimento**

```powershell
# Instalar/atualizar dependências
composer install
composer update

# Verificar sintaxe PHP
php -l src/Controller/ProcessoController.php

# Limpar cache do Composer
composer clear-cache

# Importar banco atualizado
mysql -u root -p algorise_db < algorise_db.sql

# Backup do banco
"C:\xampp\mysql\bin\mysqldump.exe" -u root -p algorise_db > backup_$(Get-Date -Format 'yyyyMMdd').sql

# Git (controle de versão)
git status
git add .
git commit -m "Descrição das alterações"
git push origin main
```

---

## ❌ **Problemas Comuns e Soluções**

| ❌ Erro | ✅ Solução |
|---------|-----------|
| **settings.php não encontrado** | Arquivo foi renomeado para `settings-php-puro.php` |
| **Banco não conecta** | Verificar se MySQL está rodando no XAMPP |
| **Email não funciona** | Configurar SMTP em `/configuracoes/email` |
| **Upload de logo falha** | Verificar se pasta `public/uploads/interface/` existe |
| **Erro 404 nas rotas** | Verificar se `.htaccess` existe no `/public/` |
| **Erro de permissão** | Dar permissão de escrita na pasta `storage/` |

---

## 🗄️ **Configuração do Banco de Dados**

### **Importação Inicial:**
```powershell
# Via XAMPP phpMyAdmin
1. Acesse: http://localhost/phpmyadmin
2. Crie database: algorise_db
3. Importe: algorise_db.sql

# Via MySQL CLI (se disponível)
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS algorise_db;"
mysql -u root -p algorise_db < algorise_db.sql
```

### **Verificações Pós-Importação:**
- ✅ 12 tabelas criadas
- ✅ Usuário admin: `admin@algorise.com`
- ✅ 45+ configurações padrão inseridas
- ✅ Sistema de logs ativo

---

## 🧪 **Testes de Funcionamento**

### **1. 🔐 Autenticação**
- Login com `admin@algorise.com` / `admin123` ✅
- Redirecionamento para dashboard ✅
- Logout funcionando ✅

### **2. ⚙️ Configurações**
- Dados da empresa em `/configuracoes/geral` ✅
- SMTP em `/configuracoes/email` ✅  
- Interface em `/configuracoes/interface` ✅

### **3. 📋 Funcionalidades Core**
- Criar/editar processos ✅
- Adicionar/editar itens ✅
- Gerenciar fornecedores ✅
- Upload de logos/arquivos ✅

### **4. 🎨 Personalização**
- Cores aplicadas nas tabelas ✅
- Logo na sidebar e relatórios ✅
- Tema claro/escuro ✅

---

## 🔗 **Links Úteis de Desenvolvimento**

- **Dashboard:** http://localhost/algorise-versao-php-puro/dashboard
- **Configurações:** http://localhost/algorise-versao-php-puro/configuracoes
- **phpMyAdmin:** http://localhost/phpmyadmin
- **Logs do Apache:** `C:\xampp\apache\logs\error.log`
- **Logs do PHP:** `public/php_errors.log`

---

## 🚀 **Deploy para Produção**

1. **Configurar `.env`** com dados de produção
2. **Importar `algorise_db.sql`** no servidor
3. **Configurar virtual host** Apache/Nginx
4. **Ajustar permissões** de arquivos
5. **Configurar SMTP** real para emails
6. **Alterar senha** do usuário admin

---

**✅ Ambiente configurado e pronto para desenvolvimento!** 🎯

> **💡 Dica:** Use a URL do XAMPP (`http://localhost/algorise-versao-php-puro`) para desenvolvimento, pois funciona melhor com uploads e sessões.