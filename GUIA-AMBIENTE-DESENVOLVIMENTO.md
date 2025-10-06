# 🚀 Guia Rápido - Iniciar Desenvolvimento

**Projeto:** algorise-versao-php-puro  
**Para quem já tem XAMPP + banco configurado**

---

## ⚡ **Início Rápido (3 comandos)**

### **1. Abrir XAMPP Control Panel**
- Iniciar **Apache** ✅
- Iniciar **MySQL** ✅

### **2. Navegar para o projeto**
```powershell
cd C:\xampp\htdocs\algorise-versao-php-puro
```

### **3. Iniciar servidor**
```powershell
php -S localhost:8080 -t public
```

### **4. Acessar**
- **URL:** http://localhost:8080
- **Login:** admin  
- **Senha:** 123456

---

## � **Estrutura Principal**

```
algorise-versao-php-puro/
├── public/index.php      # Router principal
├── src/Controller/       # Lógica MVC
├── src/View/            # Templates
├── storage/propostas/   # PDFs enviados
├── .env                 # Configurações
└── composer.json        # Dependências
```

---

## 🔧 **Comandos Úteis**

```powershell
# Instalar/atualizar dependências
composer install

# Logs de debug
type debug.log

# Backup banco
mysqldump -u root -p algorise > backup.sql

# Git
git add .; git commit -m "mensagem"; git push
```

---

## ❌ **Problemas Comuns**

| Erro | Solução |
|------|---------|
| Banco não conecta | Verificar MySQL no XAMPP |
| Email não envia | Conferir `.env` com senha de app Gmail |
| Upload falha | Criar pasta `storage/propostas/` |

---

## 🧪 **Teste Rápido**

1. **Login** → Dashboard carrega ✅
2. **Email** → Processos → Enviar cotação ✅  
3. **Upload** → Responder como fornecedor ✅

---

**Pronto para desenvolver!** 🎯