# BuscaPreços AI

Micro SaaS para automação de processos de cotação, análise de preços e gestão de fornecedores.

## 🚀 Acesso ao Sistema

### Ambiente de Produção
- **Aplicação:** https://algorise.com.br
- **Portainer:** https://portainer.algorise.com.br
  - Usuário: `algoadmin`
  - Senha: `dsfkjh3h2j%21DW`

## 🛠️ Desenvolvimento

### Iniciar ambiente de desenvolvimento
```bash
docker-compose -f docker-compose.dev.yml up -d
```

### Parar ambiente de desenvolvimento
```bash
docker-compose -f docker-compose.dev.yml down
```

### Debug com dados de produção
```bash
docker-compose -f docker-compose.production-debug.yml up -d
```

## 📋 Tecnologias
- PHP 8.2
- Slim Framework 4
- MySQL 8.0
- Docker & Docker Compose
- Nginx
- Traefik (produção)

## 🔧 Deploy
1. Faça suas alterações localmente
2. Commit e push para GitHub
3. Acesse o Portainer em https://portainer.algorise.com.br
4. Atualize os stacks conforme necessário