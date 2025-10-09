# 📋 Atualizações do Banco de Dados - algorise_db.sql

## 🔄 **Status:** ATUALIZADO PARA PRODUÇÃO ✅

### 📅 **Data da Atualização:** 09/10/2025

---

## 🆕 **Principais Mudanças Implementadas:**

### 1. **🏗️ Correção do Nome do Banco**
- ❌ **ANTES:** `saas_compras` (nome antigo)
- ✅ **DEPOIS:** `algorise_db` (nome correto)

### 2. **📊 Nova Tabela: `configuracoes`**
```sql
-- Tabela completamente nova para sistema de configurações
CREATE TABLE `configuracoes` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `chave` varchar(100) NOT NULL,
    `valor` text,
    `categoria` varchar(50) DEFAULT 'geral',
    -- ... campos adicionais
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3. **👥 Tabela `usuarios` Atualizada**
- Adicionado campo `role` (admin/usuario)
- Adicionado campo `ativo` para controle de status
- Melhorados os timestamps

### 4. **📋 Tabelas de Processos Aprimoradas**
- Campo `agente_matricula` adicionado em `processos`
- Constraints de chave estrangeira otimizadas
- Índices únicos para evitar duplicatas

### 5. **📈 Nova Tabela: `logs_sistema`**
```sql
-- Sistema de auditoria e logs
CREATE TABLE `logs_sistema` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `nivel` enum('debug','info','warning','error','critical'),
    `mensagem` text NOT NULL,
    `contexto` json DEFAULT NULL,
    -- ... campos adicionais
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 6. **📮 Nova Tabela: `solicitacoes_cotacao`**
```sql
-- Sistema de acompanhamento de cotações
CREATE TABLE `solicitacoes_cotacao` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `processo_id` int(11) NOT NULL,
    `fornecedor_id` int(11) NOT NULL,
    `status` enum('pendente','enviada','respondida','vencida'),
    -- ... campos adicionais
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## ⚙️ **Configurações Padrão Inseridas:**

### 🏢 **Categoria: Empresa (10 configurações)**
- `empresa_nome`, `empresa_cnpj`, `empresa_endereco`
- `empresa_cidade`, `empresa_estado`, `empresa_cep`
- `empresa_telefone`, `empresa_email`, `empresa_site`
- `empresa_logo_path`

### 📧 **Categoria: Email (12 configurações)**
- `email_smtp_host`, `email_smtp_port`, `email_smtp_security`
- `email_smtp_username`, `email_smtp_password`
- `email_from_address`, `email_from_name`, `email_reply_to`
- Configurações avançadas de timeout e debug

### 🎨 **Categoria: Interface (17 configurações)**
- **Cores:** `interface_cor_primaria`, `interface_cor_secundaria`
- **Layout:** `interface_sidebar_largura`, `interface_fonte_familia`
- **Visual:** `interface_bordas_arredondadas`, `interface_sombras`
- **Identidade:** `interface_logo_path`, `interface_nome_sistema`

### 🔧 **Categoria: Sistema (6 configurações)**
- `sistema_timezone`, `sistema_moeda`, `sistema_formato_data`
- `upload_max_size`, `upload_extensoes`

---

## 👤 **Usuário Administrador Padrão:**
- **Email:** `admin@algorise.com`
- **Senha:** `admin123`
- **Role:** `admin`

---

## 🚀 **Para Importar em Produção:**

### **Método 1: Script Automático**
```bash
# Execute o script (Windows)
importar-banco.bat
```

### **Método 2: MySQL CLI**
```bash
# Criar banco e importar
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS algorise_db;"
mysql -u root -p algorise_db < algorise_db.sql
```

### **Método 3: phpMyAdmin**
1. Acesse phpMyAdmin
2. Crie o banco `algorise_db`
3. Importe o arquivo `algorise_db.sql`

---

## ✅ **Verificações Pós-Importação:**

```sql
-- Verificar tabelas criadas
SHOW TABLES;

-- Verificar configurações
SELECT categoria, COUNT(*) as total FROM configuracoes GROUP BY categoria;

-- Verificar usuário admin
SELECT nome, email, role FROM usuarios WHERE role = 'admin';
```

---

## 📋 **Checklist de Produção:**

- ✅ **Database corrigido:** `algorise_db` (não mais `saas_compras`)
- ✅ **Tabela de configurações:** Criada com 45+ configurações
- ✅ **Sistema de logs:** Implementado para auditoria
- ✅ **Usuário admin:** Criado com credenciais padrão
- ✅ **Constraints FK:** Todas as relações definidas
- ✅ **Charset UTF-8:** Suporte completo a caracteres especiais
- ✅ **Índices otimizados:** Performance melhorada
- ✅ **Dados padrão:** Configurações essenciais inseridas

---

## 🔐 **Importante para Produção:**

1. **Altere a senha do admin** após primeira importação
2. **Configure backup automático** do banco
3. **Ajuste as configurações de email** SMTP
4. **Personalize dados da empresa**
5. **Configure logo e identidade visual**

---

**✅ O banco está pronto para produção e inclui todas as funcionalidades implementadas no sistema!**