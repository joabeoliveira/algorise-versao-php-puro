#!/bin/bash

# ===============================================
# SCRIPT DE REBUILD E DEPLOY DE IMAGEM DOCKER
# ===============================================
# 
# Descrição: Script para rebuild da imagem Docker e deploy em produção
# Uso: ./scripts/rebuild-image.sh [nova_tag]
#

set -e  # Parar execução em caso de erro

# ===============================================
# CONFIGURAÇÕES
# ===============================================

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configurações
PROJECT_DIR="/root/buscaprecos-main"
IMAGE_NAME="pbraconnot/buscaprecos-app"
SERVICE_NAME="app_app"
DOCKERFILE_PATH="docker/php/Dockerfile"

# ===============================================
# FUNÇÕES AUXILIARES
# ===============================================

print_header() {
    echo -e "${BLUE}"
    echo "==============================================="
    echo "      ALGORISE - REBUILD IMAGEM DOCKER"
    echo "==============================================="
    echo -e "${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️ $1${NC}"
}

print_step() {
    echo -e "${YELLOW}🔄 $1${NC}"
}

# Verificar se está no ambiente correto
check_environment() {
    if [ ! -d "$PROJECT_DIR" ]; then
        print_error "Diretório do projeto não encontrado: $PROJECT_DIR"
        print_info "Este script deve ser executado na VPS de produção"
        exit 1
    fi
    
    if [ ! -f "$PROJECT_DIR/$DOCKERFILE_PATH" ]; then
        print_error "Dockerfile não encontrado: $PROJECT_DIR/$DOCKERFILE_PATH"
        exit 1
    fi
}

# Verificar se Docker está rodando
check_docker() {
    if ! docker ps &> /dev/null; then
        print_error "Docker não está rodando ou não há permissão para acessá-lo"
        exit 1
    fi
}

# Gerar nova tag
generate_tag() {
    local custom_tag="$1"
    
    if [ ! -z "$custom_tag" ]; then
        echo "$custom_tag"
    else
        # Gerar tag baseada em timestamp
        echo "1.0.$(date +%Y%m%d%H%M)"
    fi
}

# Atualizar código do Git
update_code() {
    print_step "Atualizando código do Git..."
    
    cd "$PROJECT_DIR" || exit 1
    
    if [ -d ".git" ]; then
        # Fazer backup do estado atual
        cp -r . "../backup_pre_rebuild_$(date +%Y%m%d_%H%M%S)" || {
            print_warning "Falha ao fazer backup, continuando..."
        }
        
        # Atualizar código
        git fetch origin || {
            print_error "Falha ao buscar atualizações do Git"
            exit 1
        }
        
        git reset --hard origin/main || {
            print_error "Falha ao aplicar atualizações"
            exit 1
        }
        
        print_success "Código atualizado com sucesso"
    else
        print_error "Diretório não é um repositório Git"
        exit 1
    fi
}

# Instalar dependências
update_dependencies() {
    print_step "Atualizando dependências..."
    
    cd "$PROJECT_DIR" || exit 1
    
    if [ -f "composer.json" ]; then
        composer install --no-dev --optimize-autoloader || {
            print_error "Falha ao instalar dependências"
            exit 1
        }
        print_success "Dependências atualizadas"
    else
        print_warning "composer.json não encontrado, pulando dependências"
    fi
}

# Build da nova imagem
build_image() {
    local new_tag="$1"
    
    print_step "Fazendo build da imagem: $IMAGE_NAME:$new_tag"
    
    cd "$PROJECT_DIR" || exit 1
    
    # Build da imagem
    docker build -f "$DOCKERFILE_PATH" -t "$IMAGE_NAME:$new_tag" . || {
        print_error "Falha no build da imagem"
        exit 1
    }
    
    print_success "Imagem criada: $IMAGE_NAME:$new_tag"
    
    # Verificar se imagem foi criada
    if docker images "$IMAGE_NAME:$new_tag" | grep -q "$new_tag"; then
        local size=$(docker images "$IMAGE_NAME:$new_tag" --format "{{.Size}}")
        print_info "Tamanho da imagem: $size"
    else
        print_error "Imagem não foi criada corretamente"
        exit 1
    fi
}

# Atualizar serviço
update_service() {
    local new_tag="$1"
    
    print_step "Atualizando serviço: $SERVICE_NAME"
    
    # Verificar se serviço existe
    if ! docker service ls | grep -q "$SERVICE_NAME"; then
        print_error "Serviço não encontrado: $SERVICE_NAME"
        exit 1
    fi
    
    # Atualizar serviço com nova imagem
    docker service update --image "$IMAGE_NAME:$new_tag" "$SERVICE_NAME" || {
        print_error "Falha ao atualizar serviço"
        exit 1
    }
    
    print_success "Serviço atualizado"
    
    # Aguardar convergência
    print_step "Aguardando convergência do serviço..."
    sleep 20
    
    # Verificar status
    local replicas=$(docker service ls --filter name=$SERVICE_NAME --format "{{.Replicas}}")
    print_info "Status do serviço: $replicas"
}

# Verificar saúde do serviço
health_check() {
    print_step "Verificando saúde do serviço..."
    
    # Verificar se serviço está rodando
    if docker service ls | grep -q "$SERVICE_NAME.*1/1"; then
        print_success "Serviço está saudável"
    else
        print_error "Serviço não está saudável"
        
        # Mostrar logs para debug
        print_info "Logs do serviço (últimas 20 linhas):"
        docker service logs "$SERVICE_NAME" --tail 20
        
        return 1
    fi
    
    # Teste básico se possível
    if docker exec $(docker ps -q --filter label=com.docker.swarm.service.name=$SERVICE_NAME) php -v > /dev/null 2>&1; then
        print_success "PHP está funcionando"
    else
        print_warning "Não foi possível testar PHP"
    fi
}

# Limpeza de imagens antigas
cleanup_old_images() {
    print_step "Limpando imagens antigas..."
    
    # Listar imagens existentes
    local images=$(docker images "$IMAGE_NAME" --format "{{.Tag}}" | grep -v "^<none>$" | sort -r)
    local count=$(echo "$images" | wc -l)
    
    print_info "Imagens encontradas: $count"
    
    # Manter apenas as 3 mais recentes
    if [ $count -gt 3 ]; then
        local to_remove=$(echo "$images" | tail -n +4)
        
        echo "$to_remove" | while read -r tag; do
            if [ ! -z "$tag" ]; then
                print_info "Removendo imagem antiga: $IMAGE_NAME:$tag"
                docker rmi "$IMAGE_NAME:$tag" || print_warning "Falha ao remover $IMAGE_NAME:$tag"
            fi
        done
        
        print_success "Limpeza concluída"
    else
        print_info "Não há imagens antigas para remover"
    fi
}

# Mostrar informações finais
show_final_info() {
    local new_tag="$1"
    
    echo
    print_info "INFORMAÇÕES FINAIS:"
    echo "🏷️ Nova tag: $new_tag"
    echo "🐳 Imagem: $IMAGE_NAME:$new_tag"
    echo "⚙️ Serviço: $SERVICE_NAME"
    echo "📅 Data: $(date)"
    
    # Status do serviço
    echo
    print_info "STATUS DOS SERVIÇOS:"
    docker service ls | grep -E "(app_|webserver_)" || true
}

# ===============================================
# FUNÇÃO PRINCIPAL
# ===============================================

show_usage() {
    echo "Uso: $0 [TAG_PERSONALIZADA]"
    echo
    echo "Exemplos:"
    echo "  $0                    # Usa tag automática baseada em timestamp"
    echo "  $0 1.0.algorise       # Usa tag personalizada"
    echo "  $0 1.1.0              # Usa versão específica"
    echo
}

main() {
    print_header
    
    local custom_tag="$1"
    
    if [ "$custom_tag" = "--help" ] || [ "$custom_tag" = "-h" ]; then
        show_usage
        exit 0
    fi
    
    # Verificações iniciais
    check_environment
    check_docker
    
    # Gerar tag
    local new_tag=$(generate_tag "$custom_tag")
    print_info "Tag que será usada: $new_tag"
    
    # Confirmar operação
    echo
    print_warning "Esta operação irá:"
    echo "  1. Atualizar código do Git"
    echo "  2. Fazer build de nova imagem Docker"
    echo "  3. Atualizar serviço em produção"
    echo "  4. Limpar imagens antigas"
    echo
    read -p "Deseja continuar? (y/N): " confirm
    
    if [ "$confirm" != "y" ] && [ "$confirm" != "Y" ]; then
        print_info "Operação cancelada"
        exit 0
    fi
    
    # Executar processo
    echo
    print_step "Iniciando processo de rebuild..."
    
    update_code
    update_dependencies
    build_image "$new_tag"
    update_service "$new_tag"
    
    if health_check; then
        cleanup_old_images
        show_final_info "$new_tag"
        print_success "🎉 Rebuild e deploy concluídos com sucesso!"
    else
        print_error "Deploy falhou no health check"
        print_warning "Considere fazer rollback para versão anterior"
        exit 1
    fi
}

# ===============================================
# EXECUÇÃO
# ===============================================

# Executar função principal com todos os argumentos
main "$@"