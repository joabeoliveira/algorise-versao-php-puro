#!/bin/bash

# ===============================================
# SCRIPT DE DEPLOY MANUAL DO ALGORISE
# ===============================================
# 
# Descrição: Script para deploy manual da aplicação
# Uso: ./scripts/deploy.sh [tipo] [opcoes]
# Exemplos:
#   ./scripts/deploy.sh code         # Deploy apenas código
#   ./scripts/deploy.sh migration    # Deploy com migrations
#   ./scripts/deploy.sh rollback     # Rollback para versão anterior
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

# Configurações da aplicação
PROJECT_DIR="/root/buscaprecos-main"
BACKUP_DIR="/root/backups"
SERVICES=("app_app" "webserver_webserver")
DB_CONTAINER="db_db"
DB_USER="root"
DB_NAME="buscaprecos"

# ===============================================
# FUNÇÕES AUXILIARES
# ===============================================

print_header() {
    echo -e "${BLUE}"
    echo "==============================================="
    echo "          ALGORISE - DEPLOY SCRIPT"
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

# Verificar se está rodando na VPS
check_environment() {
    if [ ! -d "$PROJECT_DIR" ]; then
        print_error "Diretório do projeto não encontrado: $PROJECT_DIR"
        print_info "Este script deve ser executado na VPS de produção"
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

# Verificar status dos serviços
check_services() {
    print_step "Verificando status dos serviços..."
    
    for service in "${SERVICES[@]}"; do
        if docker service ls | grep -q "$service"; then
            local replicas=$(docker service ls --filter name=$service --format "{{.Replicas}}")
            print_info "Serviço $service: $replicas"
        else
            print_warning "Serviço $service não encontrado"
        fi
    done
}

# Fazer backup do código atual
backup_code() {
    print_step "Fazendo backup do código atual..."
    
    local backup_name="backup_code_$(date +%Y%m%d_%H%M%S)"
    local backup_path="../$backup_name"
    
    cp -r "$PROJECT_DIR" "$backup_path" || {
        print_error "Falha ao fazer backup do código"
        exit 1
    }
    
    print_success "Backup do código criado: $backup_path"
}

# Fazer backup do banco de dados
backup_database() {
    print_step "Fazendo backup do banco de dados..."
    
    local backup_file="$BACKUP_DIR/backup_pre_deploy_$(date +%Y%m%d_%H%M%S).sql"
    
    # Criar diretório se não existir
    mkdir -p "$BACKUP_DIR"
    
    # Obter senha do banco
    echo -n "Digite a senha do banco de dados: "
    read -s DB_PASSWORD
    echo
    
    docker exec $DB_CONTAINER mysqldump \
        -u $DB_USER \
        -p$DB_PASSWORD \
        --single-transaction \
        --routines \
        --triggers \
        $DB_NAME > "$backup_file" || {
        print_error "Falha ao fazer backup do banco"
        exit 1
    }
    
    # Comprimir backup
    gzip "$backup_file"
    
    print_success "Backup do banco criado: ${backup_file}.gz"
}

# Atualizar código do Git
update_code() {
    print_step "Atualizando código do Git..."
    
    cd "$PROJECT_DIR" || exit 1
    
    # Verificar status do git
    if [ -d ".git" ]; then
        git fetch origin || {
            print_error "Falha ao buscar atualizações do Git"
            exit 1
        }
        
        # Mostrar diferenças
        local behind=$(git rev-list HEAD..origin/main --count)
        if [ $behind -gt 0 ]; then
            print_info "Existem $behind commits para atualizar"
            git log --oneline HEAD..origin/main
        else
            print_info "Código já está atualizado"
        fi
        
        # Aplicar atualizações
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

# Instalar/atualizar dependências
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
        print_warning "composer.json não encontrado"
    fi
}

# Aplicar migrations
apply_migrations() {
    print_step "Aplicando migrations..."
    
    local migration_dir="$PROJECT_DIR/migrations"
    
    if [ -d "$migration_dir" ] && [ "$(ls -A $migration_dir)" ]; then
        echo -n "Digite a senha do banco de dados: "
        read -s DB_PASSWORD
        echo
        
        for migration in $migration_dir/*.sql; do
            if [ -f "$migration" ]; then
                print_info "Aplicando: $(basename $migration)"
                
                docker exec -i $DB_CONTAINER mysql \
                    -u $DB_USER \
                    -p$DB_PASSWORD \
                    $DB_NAME < "$migration" || {
                    print_error "Falha ao aplicar migration: $(basename $migration)"
                    print_error "Considere fazer rollback manual"
                    exit 1
                }
                
                print_success "Migration aplicada: $(basename $migration)"
            fi
        done
        
        print_success "Todas as migrations foram aplicadas"
    else
        print_info "Nenhuma migration encontrada"
    fi
}

# Reiniciar serviços
restart_services() {
    print_step "Reiniciando serviços..."
    
    for service in "${SERVICES[@]}"; do
        print_info "Reiniciando $service..."
        
        docker service update --force "$service" || {
            print_error "Falha ao reiniciar $service"
            return 1
        }
        
        print_success "Serviço $service reiniciado"
    done
    
    # Aguardar inicialização
    print_step "Aguardando inicialização dos serviços..."
    sleep 15
}

# Verificar saúde da aplicação
health_check() {
    print_step "Verificando saúde da aplicação..."
    
    local healthy=true
    
    # Verificar se serviços estão rodando
    for service in "${SERVICES[@]}"; do
        if ! docker service ls | grep -q "$service.*1/1"; then
            print_error "Serviço $service não está saudável"
            healthy=false
        else
            print_success "Serviço $service OK"
        fi
    done
    
    # Verificar conectividade com banco
    if docker exec $DB_CONTAINER mysql -u $DB_USER -p$DB_PASSWORD -e "SELECT 1" $DB_NAME > /dev/null 2>&1; then
        print_success "Conectividade com banco OK"
    else
        print_error "Problema de conectividade com banco"
        healthy=false
    fi
    
    # Teste básico de PHP
    if docker exec app_app php -r "echo 'PHP OK\n';" > /dev/null 2>&1; then
        print_success "Aplicação PHP OK"
    else
        print_error "Problema com aplicação PHP"
        healthy=false
    fi
    
    if [ "$healthy" = true ]; then
        print_success "✅ Health check passou!"
        return 0
    else
        print_error "❌ Health check falhou!"
        return 1
    fi
}

# Mostrar logs dos serviços
show_logs() {
    print_step "Mostrando logs dos serviços..."
    
    for service in "${SERVICES[@]}"; do
        echo
        print_info "Logs do $service (últimas 10 linhas):"
        docker service logs "$service" --tail 10 || true
    done
}

# Rollback para versão anterior
rollback() {
    print_warning "INICIANDO ROLLBACK..."
    
    # Listar backups disponíveis
    print_info "Backups de código disponíveis:"
    ls -la ../backup_code_* 2>/dev/null || {
        print_error "Nenhum backup de código encontrado"
        exit 1
    }
    
    echo -n "Digite o nome do backup para restaurar: "
    read backup_name
    
    if [ -d "../$backup_name" ]; then
        print_step "Restaurando código..."
        
        # Backup do estado atual
        cp -r "$PROJECT_DIR" "../backup_pre_rollback_$(date +%Y%m%d_%H%M%S)"
        
        # Restaurar código
        rm -rf "$PROJECT_DIR"/*
        cp -r "../$backup_name"/* "$PROJECT_DIR"/
        
        print_success "Código restaurado"
        
        # Reiniciar serviços
        restart_services
        
        # Verificar saúde
        if health_check; then
            print_success "Rollback concluído com sucesso!"
        else
            print_error "Rollback pode ter problemas, verifique logs"
        fi
    else
        print_error "Backup não encontrado: $backup_name"
        exit 1
    fi
}

# ===============================================
# TIPOS DE DEPLOY
# ===============================================

deploy_code_only() {
    print_info "🚀 DEPLOY APENAS CÓDIGO"
    
    backup_code
    update_code
    update_dependencies
    restart_services
    health_check || {
        print_error "Deploy falhou no health check"
        show_logs
        exit 1
    }
    
    print_success "🎉 Deploy de código concluído com sucesso!"
}

deploy_with_migration() {
    print_info "🗃️ DEPLOY COM MIGRATION"
    
    backup_database
    backup_code
    update_code
    update_dependencies
    apply_migrations
    restart_services
    health_check || {
        print_error "Deploy falhou no health check"
        show_logs
        print_warning "Considere fazer rollback do banco de dados"
        exit 1
    }
    
    print_success "🎉 Deploy com migration concluído com sucesso!"
}

# ===============================================
# FUNÇÃO PRINCIPAL
# ===============================================

show_usage() {
    echo "Uso: $0 [COMANDO]"
    echo
    echo "COMANDOS disponíveis:"
    echo "  code        - Deploy apenas código (sem migrations)"
    echo "  migration   - Deploy com migrations de banco"
    echo "  rollback    - Rollback para versão anterior"
    echo "  status      - Verificar status dos serviços"
    echo "  logs        - Mostrar logs dos serviços"
    echo "  health      - Verificar saúde da aplicação"
    echo
    echo "Exemplos:"
    echo "  $0 code"
    echo "  $0 migration"
    echo "  $0 rollback"
    echo "  $0 status"
    echo
}

main() {
    print_header
    
    local command="$1"
    
    if [ -z "$command" ]; then
        show_usage
        exit 1
    fi
    
    # Verificações iniciais
    check_environment
    check_docker
    
    case "$command" in
        "code")
            deploy_code_only
            ;;
        "migration")
            deploy_with_migration
            ;;
        "rollback")
            rollback
            ;;
        "status")
            check_services
            ;;
        "logs")
            show_logs
            ;;
        "health")
            health_check
            ;;
        *)
            print_error "Comando inválido: $command"
            show_usage
            exit 1
            ;;
    esac
}

# ===============================================
# EXECUÇÃO
# ===============================================

# Executar função principal com todos os argumentos
main "$@"