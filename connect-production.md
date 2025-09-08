# Configuração para conectar ao sistema de produção
# Arquivo: connect-production.md

## 🔗 Conectar Sistema de Produção

### 1. Túnel SSH para Banco de Dados
```bash
# Criar túnel SSH para MySQL de produção
ssh -L 3307:localhost:3306 root@SEU_IP_VPS

# Em outro terminal, conectar ao banco
mysql -h 127.0.0.1 -P 3307 -u busca -p buscaprecos
```

### 2. Variáveis de Ambiente para Produção
Criar arquivo `.env.production`:
```env
# Configurações de Produção (via túnel SSH)
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=buscaprecos
DB_USER=busca
DB_PASSWORD=busca_password

# Manter outras configs locais
APP_ENV=production_debug
APP_DEBUG=true
```

### 3. Acessar Aplicação de Produção
```bash
# Via túnel SSH para aplicação web
ssh -L 8081:localhost:80 root@SEU_IP_VPS

# Acessar: http://localhost:8081
```