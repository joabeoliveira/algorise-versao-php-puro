# 🚀 GUIA: Conectar Sistema de Produção

## 🎯 **Conexão Rápida (Recomendado)**

```powershell
# Configurar tudo automaticamente
.\Setup-Production-Debug.ps1
```

## 📊 **O que será configurado:**

### **🔗 Túneis SSH criados:**
- **Banco MySQL:** `localhost:3307` → Produção MySQL
- **Aplicação Web:** `localhost:8081` → Produção Web  
- **Portainer:** `localhost:9001` → Gestão Docker

### **🌐 URLs disponíveis:**
- **Aplicação Local + Banco Prod:** http://localhost:8082
- **Aplicação Produção Direta:** http://localhost:8081
- **Portainer (Gestão):** http://localhost:9001
- **phpMyAdmin:** http://localhost:8083

### **🔑 Credenciais:**
```
Banco de Dados:
- Host: 127.0.0.1:3307
- User: busca
- Pass: busca_password
- DB: buscaprecos

Portainer:
- User: algoadmin  
- Pass: dsfkjh3h2j%21DW

SSH VPS:
- IP: 194.163.131.97
- User: root
```

## 🛠️ **Opções Manuais**

### **1. Conectar apenas Banco:**
```powershell
.\Connect-Production.ps1 -Mode database
```

### **2. Conectar apenas Portainer:**
```powershell
.\Connect-Production.ps1 -Mode portainer
```

### **3. Conectar tudo (manual):**
```powershell
.\Connect-All-Production.ps1
```

## 💡 **Casos de Uso**

### **🐛 Debug com dados reais:**
1. Execute `.\Setup-Production-Debug.ps1`
2. Acesse http://localhost:8082
3. Sua aplicação local usa banco de produção
4. Faça debug sem afetar produção

### **⚙️ Gerenciar Docker:**
1. Execute `.\Connect-Production.ps1 -Mode portainer`
2. Acesse http://localhost:9001
3. Gerencie containers, stacks, logs

### **📊 Consultar banco:**
1. Execute `.\Connect-Production.ps1 -Mode database`
2. Acesse http://localhost:8083 (phpMyAdmin)
3. Ou use cliente MySQL: `127.0.0.1:3307`

## ⚠️ **Cuidados Importantes**

1. **🔒 Dados Reais:** Banco de produção contém dados reais
2. **⏰ Performance:** Conexão via SSH é mais lenta
3. **🔄 Backup:** Sempre faça backup antes de alterações
4. **🖥️ Terminal:** Mantenha terminal aberto durante uso
5. **🛑 Desconectar:** Use Ctrl+C para parar tudo

## 🔧 **Solução de Problemas**

### **SSH não conecta:**
```bash
# Testar conexão SSH
ssh root@194.163.131.97

# Verificar se portas estão ocupadas
netstat -an | findstr :3307
netstat -an | findstr :9001
```

### **Containers não sobem:**
```powershell
# Verificar Docker
docker --version
docker ps

# Limpar containers
docker-compose down
docker system prune -f
```

### **Aplicação não funciona:**
```powershell
# Verificar logs
docker-compose -f docker-compose.production-debug.yml logs -f

# Verificar conectividade com banco
telnet localhost 3307
```

## 📝 **Logs e Monitoramento**

### **Ver logs em tempo real:**
```powershell
# Logs da aplicação local
docker-compose -f docker-compose.production-debug.yml logs -f app

# Logs via Portainer
# Acesse http://localhost:9001 → Containers → Logs
```

---

## 🎯 **Fluxo Recomendado de Trabalho**

1. **Desenvolvimento Normal:** Use ambiente local (`docker-compose.dev.yml`)
2. **Debug com Dados Reais:** Use `Setup-Production-Debug.ps1`
3. **Deploy:** Commit → Push → SSH → Git Pull → Docker restart
4. **Gestão:** Use Portainer via túnel SSH

**💡 Dica:** Use VSCode com extensão Docker para visualizar containers facilmente!