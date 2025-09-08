# 🔄 Guia de Atualização para Algorise no Portainer

## 📋 Passos para Atualizar o Nome no Portainer

### 1. **Acessar o Portainer**
- Faça login no Portainer através da URL: `https://pp.sheep7.com/` (ou sua URL do Portainer)
- Entre com suas credenciais de administrador

### 2. **Localizar as Stacks Existentes**
- Vá para **Stacks** no menu lateral
- Procure pelas stacks relacionadas ao BuscaPreços:
  - `buscaprecos-app` (aplicação PHP)
  - `buscaprecos-web` (servidor web/nginx)
  - `buscaprecos-db` (banco de dados)

### 3. **Atualizar Stack da Aplicação Web** 
🎯 **Stack: `buscaprecos-web`**

**Opção A - Editar Stack Existente:**
1. Clique na stack `buscaprecos-web`
2. Clique em **"Editor"** 
3. Substitua o conteúdo pelo arquivo atualizado `web-stack.yml`
4. **Configure as variáveis de ambiente:**
   ```
   APP_HOST=app.algorise.com.br
   ```
5. Clique em **"Update the stack"**

**Opção B - Recriar Stack:**
1. **Parar** a stack existente
2. **Deletar** a stack `buscaprecos-web`
3. Criar nova stack com nome `algorise-web`
4. Usar o conteúdo do arquivo `web-stack.yml` atualizado
5. Definir variáveis de ambiente

### 4. **Atualizar Stack da Aplicação**
🎯 **Stack: `buscaprecos-app`**

1. Clique na stack `buscaprecos-app`
2. Clique em **"Editor"**
3. Atualize as variáveis de ambiente conforme necessário
4. **Alterar nome da imagem** (se aplicável):
   ```yaml
   services:
     app:
       image: pbraconnot/algorise-app:1.0.0  # Nome atualizado
   ```

### 5. **Configurações de DNS/Domínio**
🌐 **Verificar se o domínio `app.algorise.com.br` está:**
- Apontando para o IP do servidor
- Configurado no DNS
- Certificado SSL será gerado automaticamente pelo Let's Encrypt

### 6. **Variáveis de Ambiente Importantes**
```env
# Domínio principal
APP_HOST=app.algorise.com.br

# Configurações do banco (manter existentes)
DB_HOST=db
DB_DATABASE=buscaprecos
DB_USER=busca
DB_PASSWORD=[sua_senha]

# Configurações de email (manter existentes)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=[seu_email]
MAIL_PASSWORD=[sua_senha_app]
MAIL_FROM_ADDRESS=[seu_email]
MAIL_FROM_NAME="Algorise AI"

# SSL
HTTP_VERIFY_SSL=true
```

### 7. **Verificar Funcionamento**
Após as atualizações:

1. **Aguardar deploy** (1-2 minutos)
2. **Acessar**: `https://app.algorise.com.br`
3. **Verificar logs** das stacks em caso de erro
4. **Testar login** e funcionalidades principais

### 8. **Troubleshooting**
Se houver problemas:

1. **Verificar logs das stacks** no Portainer
2. **Confirmar redes Docker**:
   - `network_public` (para Traefik)
   - `network_internal` (comunicação interna)
3. **Verificar status dos containers**
4. **Testar conectividade do banco de dados**

---

## 🔧 Comandos Úteis (se necessário acessar via SSH)

```bash
# Verificar stacks em execução
docker stack ls

# Verificar serviços de uma stack
docker stack services [nome-da-stack]

# Ver logs de um serviço
docker service logs [nome-do-servico] --tail 50

# Verificar redes
docker network ls
```

---

## ⚠️ **IMPORTANTE**

- **Fazer backup** das configurações atuais antes de alterar
- **Coordenar** a atualização para evitar downtime
- **Testar** em ambiente de desenvolvimento primeiro (se possível)
- **Verificar** se todas as URLs internas ainda funcionam após mudança

---

## 📞 **Suporte**

Em caso de dúvidas ou problemas:
1. Verificar logs no Portainer
2. Consultar documentação do Traefik/Docker Swarm
3. Revisar configurações de DNS