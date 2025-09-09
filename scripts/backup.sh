#!/bin/bash

# ===============================================
# SCRIPT DE BACKUP MANUAL DO ALGORISE
# ===============================================
# 
# Descrição: Script para realizar backup manual do banco de dados
# Uso: ./scripts/backup.sh [tipo] [descrição]
# Exemplos:
#   ./scripts/backup.sh full "Backup antes da migração"
#   ./scripts/backup.sh structure "Backup da estrutura"
#   ./scripts/backup.sh data "Backup apenas dos dados"
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

# Configurações do banco
DB_CONTAINER="db_db"
DB_USER="root"
DB_NAME="buscaprecos"
BACKUP_DIR="/root/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# ===============================================
# FUNÇÕES AUXILIARES
# ===============================================

print_header() {
    echo -e "${BLUE}"
    echo "==============================================="
    echo "          ALGORISE - BACKUP SCRIPT"
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

# Verificar se Docker está rodando
check_docker() {
    if ! docker ps &> /dev/null; then
        print_error "Docker não está rodando ou não há permissão para acessá-lo"
        exit 1
    fi
}

# Verificar se container do banco existe
check_database_container() {
    if ! docker ps | grep -q "$DB_CONTAINER"; then
        print_error "Container do banco de dados '$DB_CONTAINER' não está rodando"
        print_info "Containers disponíveis:"
        docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
        exit 1
    fi
}

# Criar diretório de backup se não existir
create_backup_directory() {
    docker exec $DB_CONTAINER mkdir -p $BACKUP_DIR || {
        print_error "Falha ao criar diretório de backup"
        exit 1
    }
}

# Obter senha do banco (solicitar do usuário se não estiver nas variáveis)
get_db_password() {
    if [ -z "$DB_PASSWORD" ]; then
        echo -n "Digite a senha do banco de dados: "
        read -s DB_PASSWORD
        echo
    fi
}

# ===============================================
# TIPOS DE BACKUP
# ===============================================

backup_full() {
    local description="$1"
    local filename="backup_full_${TIMESTAMP}"
    [ ! -z "$description" ] && filename="${filename}_$(echo $description | tr ' ' '_')"
    filename="${filename}.sql"
    
    print_info "Iniciando backup completo..."
    
    docker exec $DB_CONTAINER mysqldump \
        -u $DB_USER \
        -p$DB_PASSWORD \
        --single-transaction \
        --routines \
        --triggers \
        --add-drop-database \
        --databases $DB_NAME > "$BACKUP_DIR/$filename" || {
        print_error "Falha ao criar backup completo"
        return 1
    }
    
    echo "$BACKUP_DIR/$filename"
}

backup_structure() {
    local description="$1"
    local filename="backup_structure_${TIMESTAMP}"
    [ ! -z "$description" ] && filename="${filename}_$(echo $description | tr ' ' '_')"
    filename="${filename}.sql"
    
    print_info "Iniciando backup da estrutura..."
    
    docker exec $DB_CONTAINER mysqldump \
        -u $DB_USER \
        -p$DB_PASSWORD \
        --no-data \
        --routines \
        --triggers \
        --add-drop-database \
        --databases $DB_NAME > "$BACKUP_DIR/$filename" || {
        print_error "Falha ao criar backup da estrutura"
        return 1
    }
    
    echo "$BACKUP_DIR/$filename"
}

backup_data() {
    local description="$1"
    local filename="backup_data_${TIMESTAMP}"
    [ ! -z "$description" ] && filename="${filename}_$(echo $description | tr ' ' '_')"
    filename="${filename}.sql"
    
    print_info "Iniciando backup dos dados..."
    
    docker exec $DB_CONTAINER mysqldump \
        -u $DB_USER \
        -p$DB_PASSWORD \
        --no-create-info \
        --single-transaction \
        $DB_NAME > "$BACKUP_DIR/$filename" || {
        print_error "Falha ao criar backup dos dados"
        return 1
    }
    
    echo "$BACKUP_DIR/$filename"
}

# ===============================================
# FUNÇÕES DE UTILIDADE
# ===============================================

compress_backup() {
    local backup_file="$1"
    
    print_info "Comprimindo backup..."
    
    docker exec $DB_CONTAINER gzip "$backup_file" || {
        print_warning "Falha ao comprimir backup (arquivo mantido sem compressão)"
        return 1
    }
    
    echo "${backup_file}.gz"
}

validate_backup() {
    local backup_file="$1"
    
    print_info "Validando backup..."
    
    # Verificar se arquivo existe e tem tamanho > 0
    if docker exec $DB_CONTAINER test -s "$backup_file"; then
        local size=$(docker exec $DB_CONTAINER du -h "$backup_file" | cut -f1)
        print_success "Backup válido (tamanho: $size)"
        return 0
    else
        print_error "Backup inválido ou vazio"
        return 1
    fi
}

show_backup_info() {
    local backup_file="$1"
    
    echo
    print_info "INFORMAÇÕES DO BACKUP:"
    echo "📁 Arquivo: $backup_file"
    echo "📅 Data: $(date)"
    echo "🏷️ Timestamp: $TIMESTAMP"
    
    if docker exec $DB_CONTAINER test -f "$backup_file"; then
        local size=$(docker exec $DB_CONTAINER du -h "$backup_file" | cut -f1)
        echo "📊 Tamanho: $size"
    fi
}

cleanup_old_backups() {
    local retention_days=${1:-30}
    
    print_info "Removendo backups antigos (>$retention_days dias)..."
    
    docker exec $DB_CONTAINER find $BACKUP_DIR -name "backup_*.sql*" -mtime +$retention_days -delete || {
        print_warning "Falha na limpeza de backups antigos"
    }
    
    local remaining=$(docker exec $DB_CONTAINER find $BACKUP_DIR -name "backup_*.sql*" | wc -l)
    print_info "Backups restantes: $remaining"
}

list_backups() {
    print_info "BACKUPS DISPONÍVEIS:"
    echo
    
    if docker exec $DB_CONTAINER test -d "$BACKUP_DIR"; then
        docker exec $DB_CONTAINER ls -lah $BACKUP_DIR/backup_*.sql* 2>/dev/null || {
            print_warning "Nenhum backup encontrado"
        }
    else
        print_warning "Diretório de backup não existe"
    fi
}

# ===============================================
# FUNÇÃO PRINCIPAL
# ===============================================

show_usage() {
    echo "Uso: $0 [TIPO] [DESCRIÇÃO]"
    echo
    echo "TIPOS disponíveis:"
    echo "  full        - Backup completo (estrutura + dados)"
    echo "  structure   - Backup apenas da estrutura"
    echo "  data        - Backup apenas dos dados"
    echo "  list        - Listar backups existentes"
    echo "  cleanup     - Remover backups antigos"
    echo
    echo "Exemplos:"
    echo "  $0 full \"Backup antes da migração\""
    echo "  $0 structure \"Estrutura v1.0\""
    echo "  $0 data \"Dados de produção\""
    echo "  $0 list"
    echo "  $0 cleanup 15"
    echo
}

main() {
    print_header
    
    local backup_type="$1"
    local description="$2"
    
    if [ -z "$backup_type" ]; then
        show_usage
        exit 1
    fi
    
    # Verificações iniciais
    check_docker
    check_database_container
    create_backup_directory
    
    case "$backup_type" in
        "full")
            get_db_password
            backup_file=$(backup_full "$description")
            if [ $? -eq 0 ]; then
                validate_backup "$backup_file"
                compressed_file=$(compress_backup "$backup_file")
                show_backup_info "${compressed_file:-$backup_file}"
                print_success "Backup completo realizado com sucesso!"
            fi
            ;;
        "structure")
            get_db_password
            backup_file=$(backup_structure "$description")
            if [ $? -eq 0 ]; then
                validate_backup "$backup_file"
                compressed_file=$(compress_backup "$backup_file")
                show_backup_info "${compressed_file:-$backup_file}"
                print_success "Backup da estrutura realizado com sucesso!"
            fi
            ;;
        "data")
            get_db_password
            backup_file=$(backup_data "$description")
            if [ $? -eq 0 ]; then
                validate_backup "$backup_file"
                compressed_file=$(compress_backup "$backup_file")
                show_backup_info "${compressed_file:-$backup_file}"
                print_success "Backup dos dados realizado com sucesso!"
            fi
            ;;
        "list")
            list_backups
            ;;
        "cleanup")
            local retention_days="${description:-30}"
            cleanup_old_backups "$retention_days"
            ;;
        *)
            print_error "Tipo de backup inválido: $backup_type"
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