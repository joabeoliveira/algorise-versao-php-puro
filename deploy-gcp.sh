#!/bin/bash

# ===============================================
# SCRIPT DE DEPLOY PARA GOOGLE CLOUD PLATFORM
# ===============================================

echo "🚀 Iniciando deploy do Algorise para Google Cloud..."

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para log colorido
log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Verificar se gcloud está instalado
if ! command -v gcloud &> /dev/null; then
    log_error "Google Cloud CLI não está instalado!"
    log_info "Instale em: https://cloud.google.com/sdk/docs/install"
    exit 1
fi

# Verificar se está autenticado
if ! gcloud auth list --filter=status:ACTIVE --format="value(account)" &> /dev/null; then
    log_error "Você não está autenticado no Google Cloud!"
    log_info "Execute: gcloud auth login"
    exit 1
fi

# Verificar se o projeto está configurado
PROJECT_ID=$(gcloud config get-value project)
if [[ -z "$PROJECT_ID" ]]; then
    log_error "Projeto Google Cloud não está configurado!"
    log_info "Execute: gcloud config set project YOUR_PROJECT_ID"
    exit 1
fi

log_info "Projeto atual: $PROJECT_ID"

# 1. Verificar arquivos necessários
log_info "Verificando arquivos necessários..."

if [[ ! -f "app.yaml" ]]; then
    log_error "Arquivo app.yaml não encontrado!"
    exit 1
fi

if [[ ! -f "composer.json" ]]; then
    log_error "Arquivo composer.json não encontrado!"
    exit 1
fi

if [[ ! -f "composer.lock" ]]; then
    log_warning "Arquivo composer.lock não encontrado! É recomendado versionar este arquivo para garantir a consistência das dependências."
    exit 1
fi

log_success "Arquivos necessários encontrados"

# 2. Instalar dependências de produção
log_info "Instalando dependências de produção..."
composer install --no-dev --optimize-autoloader --no-interaction

if [[ $? -ne 0 ]]; then
    log_error "Falha na instalação das dependências!"
    exit 1
fi

log_success "Dependências instaladas com sucesso"

# 3. Verificar se Cloud SQL está configurado
log_info "Verificando configuração do Cloud SQL..."

# Extrair connection name do app.yaml
CONNECTION_NAME=$(grep "CLOUD_SQL_CONNECTION_NAME" app.yaml | cut -d'"' -f2)

if [[ "$CONNECTION_NAME" == "your-project-id:us-central1:algorise-db" ]]; then
    log_warning "ATENÇÃO: Configure o CLOUD_SQL_CONNECTION_NAME no app.yaml"
    log_info "Formato: seu-projeto:região:nome-instancia"
    read -p "Continuar mesmo assim? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        log_info "Deploy cancelado. Configure o Cloud SQL primeiro."
        exit 1
    fi
fi

# 4. Verificar secrets (opcional)
log_info "Verificando secrets configurados..."

SECRETS=("db-password" "supabase-anon-key" "mail-password")
for secret in "${SECRETS[@]}"; do
    if gcloud secrets describe $secret &> /dev/null; then
        log_success "Secret '$secret' configurado"
    else
        log_warning "Secret '$secret' não encontrado"
        log_info "Configure com: echo 'valor' | gcloud secrets create $secret --data-file=-"
    fi
done

# 5. Fazer backup do .env atual
if [[ -f ".env" ]]; then
    log_info "Fazendo backup do .env atual..."
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
    log_success "Backup criado"
fi

# 6. Perguntar sobre domínio personalizado
log_info "Configuração de domínio..."
echo "Você quer configurar um domínio personalizado?"
echo "1) Usar domínio padrão (your-app.uc.r.appspot.com)"
echo "2) Configurar domínio personalizado"
read -p "Escolha (1/2): " -n 1 -r DOMAIN_CHOICE
echo

# 7. Deploy para App Engine
log_info "Iniciando deploy para App Engine..."
log_warning "Este processo pode levar alguns minutos..."

gcloud app deploy app.yaml --quiet --promote

if [[ $? -ne 0 ]]; then
    log_error "Falha no deploy!"
    
    # Restaurar dependências de desenvolvimento
    log_info "Restaurando dependências de desenvolvimento..."
    composer install
    
    exit 1
fi

log_success "Deploy realizado com sucesso!"

# 8. Configurar domínio personalizado se escolhido
if [[ $DOMAIN_CHOICE == "2" ]]; then
    read -p "Digite seu domínio (ex: algorise.com): " CUSTOM_DOMAIN
    if [[ ! -z "$CUSTOM_DOMAIN" ]]; then
        log_info "Configurando domínio personalizado: $CUSTOM_DOMAIN"
        gcloud app domain-mappings create $CUSTOM_DOMAIN --certificate-management=AUTOMATIC
        
        if [[ $? -eq 0 ]]; then
            log_success "Domínio configurado com sucesso!"
            log_info "Configure seu DNS para apontar para ghs.googlehosted.com"
        else
            log_warning "Falha na configuração do domínio"
        fi
    fi
fi

# 9. Restaurar dependências de desenvolvimento
log_info "Restaurando dependências de desenvolvimento..."
composer install

# 10. Mostrar URLs da aplicação
log_success "🎉 Deploy concluído com sucesso!"
echo
log_info "URLs da aplicação:"
echo "   📱 App Engine: https://$PROJECT_ID.uc.r.appspot.com"
echo "   🔍 Console: https://console.cloud.google.com/appengine?project=$PROJECT_ID"
echo "   📊 Logs: https://console.cloud.google.com/logs/query?project=$PROJECT_ID"

if [[ ! -z "$CUSTOM_DOMAIN" ]]; then
    echo "   🌐 Domínio: https://$CUSTOM_DOMAIN (após configurar DNS)"
fi

echo
log_info "Próximos passos:"
echo "   1. ✅ Teste a aplicação na URL acima"
echo "   2. 🔍 Monitore os logs para erros"
echo "   3. 🔐 Configure os secrets se ainda não fez"
echo "   4. 🗄️  Importe os dados do banco se necessário"

echo
log_success "🚀 Algorise está no ar!"