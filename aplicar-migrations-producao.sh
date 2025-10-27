#!/bin/bash

# Script para aplicar migrations no banco de dados de produção via Cloud SQL Proxy
# Data: 2025-10-25

echo "=== APLICANDO MIGRATIONS NO BANCO DE PRODUÇÃO ==="
echo ""

# Configurações
PROJECT_ID="algorise-producao"
INSTANCE_CONNECTION_NAME="algorise-producao:southamerica-east1:algorise-db"
DB_NAME="algorise"
DB_USER="root"

# Solicita a senha do banco
read -sp "Digite a senha do banco de dados: " DB_PASSWORD
echo ""

# Lista de migrations a serem aplicadas
MIGRATIONS=(
    "migrations/2025-10-25_criar_tabelas_lotes_solicitacao.sql"
    "migrations/2025-10-25_corrigir_notas_tecnicas_processo_id_nullable.sql"
)

echo ""
echo "Migrations a serem aplicadas:"
for migration in "${MIGRATIONS[@]}"; do
    echo "  - $migration"
done
echo ""

read -p "Deseja continuar? (s/n): " CONFIRM
if [[ ! "$CONFIRM" =~ ^[Ss]$ ]]; then
    echo "Operação cancelada."
    exit 0
fi

echo ""
echo "Aplicando migrations..."
echo ""

# Aplica cada migration
for migration in "${MIGRATIONS[@]}"; do
    echo "Aplicando: $migration"
    
    # Usa gcloud sql execute para executar o SQL diretamente
    gcloud sql execute $INSTANCE_CONNECTION_NAME \
        --project=$PROJECT_ID \
        --query="$(cat $migration)" \
        2>&1
    
    if [ $? -eq 0 ]; then
        echo "✓ $migration aplicada com sucesso"
    else
        echo "✗ Erro ao aplicar $migration"
        read -p "Deseja continuar com as próximas migrations? (s/n): " CONTINUE
        if [[ ! "$CONTINUE" =~ ^[Ss]$ ]]; then
            echo "Processo interrompido."
            exit 1
        fi
    fi
    echo ""
done

echo ""
echo "=== MIGRATIONS APLICADAS COM SUCESSO ==="
echo ""
echo "Próximos passos:"
echo "1. Faça o deploy da aplicação: gcloud app deploy --project=algorise-producao"
echo "2. Teste as funcionalidades corrigidas:"
echo "   - Envio de solicitação de cotação em lote"
echo "   - Pesquisa de preços"
echo "   - Cotação rápida"
echo "   - Mesa de análise geral"
echo ""
