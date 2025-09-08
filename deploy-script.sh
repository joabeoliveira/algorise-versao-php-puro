#!/bin/bash

# Script de deploy automático para BuscaPreços
# Salve como: /root/scripts/deploy-buscaprecos.sh

echo "🚀 Iniciando deploy do BuscaPreços..."

# Navegar para diretório do projeto
cd /root/buscaprecos

# Fazer backup antes da atualização
echo "📦 Fazendo backup do banco de dados..."
docker exec buscaprecos_db mysqldump -u root -p$MYSQL_ROOT_PASSWORD buscaprecos > backup_$(date +%Y%m%d_%H%M%S).sql

# Baixar alterações do GitHub
echo "📥 Baixando alterações do GitHub..."
git pull origin main

# Verificar se houve mudanças
if [ $? -eq 0 ]; then
    echo "✅ Código atualizado com sucesso!"
    
    # Recriar containers
    echo "🔄 Recriando containers..."
    docker-compose down
    docker-compose up -d --build
    
    echo "🎉 Deploy concluído com sucesso!"
    echo "🌐 Aplicação disponível em: http://seu-dominio.com"
else
    echo "❌ Erro ao atualizar código do GitHub"
    exit 1
fi