#!/bin/bash

# Script para conectar ao sistema de produção
# Uso: ./connect-production.sh [database|web|full]

VPS_IP="SEU_IP_VPS_AQUI"  # Substitua pelo IP da sua VPS
VPS_USER="root"

echo "🔗 Conectando ao sistema de produção..."

case $1 in
    "database"|"db")
        echo "📊 Criando túnel para banco de dados..."
        echo "💡 Banco ficará disponível em: localhost:3307"
        echo "🔑 Credenciais: busca / busca_password"
        ssh -L 3307:localhost:3306 $VPS_USER@$VPS_IP
        ;;
    "web")
        echo "🌐 Criando túnel para aplicação web..."
        echo "💻 Aplicação ficará disponível em: http://localhost:8081"
        ssh -L 8081:localhost:80 $VPS_USER@$VPS_IP
        ;;
    "full")
        echo "🚀 Criando túneis completos..."
        echo "📊 Banco: localhost:3307"
        echo "🌐 Web: http://localhost:8081"
        ssh -L 3307:localhost:3306 -L 8081:localhost:80 $VPS_USER@$VPS_IP
        ;;
    "portainer")
        echo "🐳 Criando túnel para Portainer..."
        echo "⚡ Portainer ficará disponível em: http://localhost:9001"
        ssh -L 9001:localhost:9000 $VPS_USER@$VPS_IP
        ;;
    *)
        echo "❓ Uso: ./connect-production.sh [database|web|full|portainer]"
        echo ""
        echo "Opções disponíveis:"
        echo "  database  - Conectar apenas ao banco de dados (porta 3307)"
        echo "  web       - Conectar apenas à aplicação web (porta 8081)"
        echo "  full      - Conectar ao banco E aplicação (portas 3307 e 8081)"
        echo "  portainer - Conectar ao Portainer (porta 9001)"
        echo ""
        echo "Exemplo: ./connect-production.sh database"
        ;;
esac