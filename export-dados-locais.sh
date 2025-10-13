#!/bin/bash

# =============================================
# SCRIPT PARA EXPORTAR DADOS ATUAIS DO XAMPP
# Para importar no Google Cloud SQL
# =============================================

echo "🗄️  Exportando dados atuais do banco local..."

# Verificar se MySQL está disponível
if ! command -v mysql &> /dev/null; then
    echo "❌ MySQL não encontrado. Verifique se o XAMPP está rodando."
    exit 1
fi

# Criar diretório de backup se não existir
mkdir -p backups

# Data atual para nome do arquivo
DATE=$(date +"%Y%m%d_%H%M%S")

# Exportar apenas os dados (sem estrutura)
echo "📊 Exportando dados das tabelas..."

# Exportar dados de todas as tabelas importantes
mysqldump -u root -p algorise \
  --no-create-info \
  --complete-insert \
  --single-transaction \
  --routines=false \
  --triggers=false \
  processos \
  itens \
  usuarios \
  configuracoes \
  fornecedores \
  precos \
  cotacoes_rapidas \
  cotacoes_rapidas_itens \
  cotacoes_rapidas_precos \
  > "backups/algorise-dados-${DATE}.sql"

if [ $? -eq 0 ]; then
    echo "✅ Dados exportados com sucesso!"
    echo "📁 Arquivo: backups/algorise-dados-${DATE}.sql"
    
    # Mostrar estatísticas
    echo ""
    echo "📊 Estatísticas dos dados exportados:"
    mysql -u root -p algorise -e "
    SELECT 'Processos' as Tabela, COUNT(*) as Total FROM processos
    UNION ALL
    SELECT 'Itens', COUNT(*) FROM itens
    UNION ALL
    SELECT 'Usuários', COUNT(*) FROM usuarios
    UNION ALL
    SELECT 'Configurações', COUNT(*) FROM configuracoes
    UNION ALL
    SELECT 'Fornecedores', COUNT(*) FROM fornecedores
    UNION ALL
    SELECT 'Preços', COUNT(*) FROM precos
    UNION ALL
    SELECT 'Cotações Rápidas', COUNT(*) FROM cotacoes_rapidas;"
    
    echo ""
    echo "🚀 Próximos passos:"
    echo "1. Fazer upload do arquivo para Cloud Storage"
    echo "2. Importar no Cloud SQL após criar a estrutura"
    echo ""
    echo "📋 Comandos para importar no GCP:"
    echo "gsutil cp backups/algorise-dados-${DATE}.sql gs://seu-bucket/"
    echo "gcloud sql import sql algorise-db gs://seu-bucket/algorise-dados-${DATE}.sql --database=algorise"
    
else
    echo "❌ Erro ao exportar dados!"
    exit 1
fi