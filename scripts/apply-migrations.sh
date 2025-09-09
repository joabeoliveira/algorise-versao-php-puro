#!/bin/bash

# ===============================================
# SCRIPT DE APLICAÇÃO DE MIGRATIONS
# ===============================================
# 
# Descrição: Script para aplicar migrations de forma controlada
# Uso: ./scripts/apply-migrations.sh [opcoes]
# 

set -e  # Parar execução em caso de erro

# ===============================================
# CONFIGURAÇÕES
# ===============================================

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configurações do banco
DB_CONTAINER="db_db"
DB_USER="root"
DB_NAME="buscaprecos"
MIGRATIONS_DIR="./migrations"
APPLIED_MIGRATIONS_TABLE="applied_migrations"

# ===============================================
# FUNÇÕES AUXILIARES
# ===============================================

print_success() { echo -e "${GREEN}✅ $1${NC}"; }
print_error() { echo -e "${RED}❌ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠️ $1${NC}"; }
print_info() { echo -e "${BLUE}ℹ️ $1${NC}"; }

# Obter senha do banco
get_db_password() {
    if [ -z "$DB_PASSWORD" ]; then
        echo -n "Digite a senha do banco de dados: "
        read -s DB_PASSWORD
        echo
    fi
}

# Criar tabela de controle de migrations
create_migrations_table() {
    print_info "Criando tabela de controle de migrations..."
    
    docker exec -i $DB_CONTAINER mysql -u $DB_USER -p$DB_PASSWORD $DB_NAME << EOF
CREATE TABLE IF NOT EXISTS $APPLIED_MIGRATIONS_TABLE (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL UNIQUE,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_filename (filename)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
EOF

    print_success "Tabela de controle criada/verificada"
}

# Verificar se migration já foi aplicada
is_migration_applied() {
    local filename="$1"
    
    local count=$(docker exec $DB_CONTAINER mysql -u $DB_USER -p$DB_PASSWORD -s -N $DB_NAME << EOF
SELECT COUNT(*) FROM $APPLIED_MIGRATIONS_TABLE WHERE filename = '$filename';
EOF
)
    
    [ "$count" -gt 0 ]
}

# Marcar migration como aplicada
mark_migration_applied() {
    local filename="$1"
    
    docker exec -i $DB_CONTAINER mysql -u $DB_USER -p$DB_PASSWORD $DB_NAME << EOF
INSERT INTO $APPLIED_MIGRATIONS_TABLE (filename) VALUES ('$filename');
EOF
}

# Aplicar uma migration
apply_migration() {
    local migration_file="$1"
    local filename=$(basename "$migration_file")
    
    print_info "Aplicando migration: $filename"
    
    # Verificar se já foi aplicada
    if is_migration_applied "$filename"; then
        print_warning "Migration já foi aplicada: $filename"
        return 0
    fi
    
    # Aplicar migration
    if docker exec -i $DB_CONTAINER mysql -u $DB_USER -p$DB_PASSWORD $DB_NAME < "$migration_file"; then
        # Marcar como aplicada
        mark_migration_applied "$filename"
        print_success "Migration aplicada: $filename"
        return 0
    else
        print_error "Falha ao aplicar migration: $filename"
        return 1
    fi
}

# Listar migrations pendentes
list_pending_migrations() {
    print_info "MIGRATIONS PENDENTES:"
    
    local pending_count=0
    
    if [ -d "$MIGRATIONS_DIR" ]; then
        for migration in "$MIGRATIONS_DIR"/*.sql; do
            if [ -f "$migration" ]; then
                local filename=$(basename "$migration")
                if ! is_migration_applied "$filename"; then
                    echo "  📄 $filename"
                    ((pending_count++))
                fi
            fi
        done
    fi
    
    if [ $pending_count -eq 0 ]; then
        print_success "Nenhuma migration pendente"
    else
        print_info "Total de migrations pendentes: $pending_count"
    fi
    
    return $pending_count
}

# Listar migrations aplicadas
list_applied_migrations() {
    print_info "MIGRATIONS APLICADAS:"
    
    docker exec $DB_CONTAINER mysql -u $DB_USER -p$DB_PASSWORD $DB_NAME << EOF
SELECT filename, applied_at FROM $APPLIED_MIGRATIONS_TABLE ORDER BY applied_at DESC;
EOF
}

# Aplicar todas as migrations pendentes
apply_all_migrations() {
    print_info "🚀 APLICANDO TODAS AS MIGRATIONS PENDENTES"
    
    local applied_count=0
    local failed_count=0
    
    if [ -d "$MIGRATIONS_DIR" ]; then
        # Ordenar migrations por nome (assumindo formato YYYY-MM-DD)
        for migration in $(ls "$MIGRATIONS_DIR"/*.sql 2>/dev/null | sort); do
            if [ -f "$migration" ]; then
                local filename=$(basename "$migration")
                
                if ! is_migration_applied "$filename"; then
                    if apply_migration "$migration"; then
                        ((applied_count++))
                    else
                        ((failed_count++))
                        print_error "Parando execução devido à falha"
                        break
                    fi
                fi
            fi
        done
    else
        print_error "Diretório de migrations não encontrado: $MIGRATIONS_DIR"
        return 1
    fi
    
    echo
    print_info "RESUMO DA EXECUÇÃO:"
    print_success "Migrations aplicadas: $applied_count"
    if [ $failed_count -gt 0 ]; then
        print_error "Migrations com falha: $failed_count"
        return 1
    else
        print_success "Todas as migrations foram aplicadas com sucesso!"
        return 0
    fi
}

# Simular aplicação de migrations (dry-run)
dry_run_migrations() {
    print_info "🔍 SIMULAÇÃO - NÃO SERÁ APLICADO NADA"
    
    if [ -d "$MIGRATIONS_DIR" ]; then
        for migration in $(ls "$MIGRATIONS_DIR"/*.sql 2>/dev/null | sort); do
            if [ -f "$migration" ]; then
                local filename=$(basename "$migration")
                
                if ! is_migration_applied "$filename"; then
                    print_info "SERIA APLICADA: $filename"
                    
                    # Mostrar primeiras linhas do arquivo
                    echo "  Prévia:"
                    head -20 "$migration" | grep -E "^(CREATE|ALTER|INSERT|UPDATE|DELETE)" | head -5 | sed 's/^/    /'
                    echo
                fi
            fi
        done
    fi
}

# ===============================================
# FUNÇÃO PRINCIPAL
# ===============================================

show_usage() {
    echo "Uso: $0 [COMANDO] [OPCOES]"
    echo
    echo "COMANDOS:"
    echo "  apply       - Aplicar todas as migrations pendentes"
    echo "  list        - Listar migrations pendentes"
    echo "  applied     - Listar migrations já aplicadas"
    echo "  dry-run     - Simular aplicação (não aplica nada)"
    echo "  status      - Status geral das migrations"
    echo
    echo "OPÇÕES:"
    echo "  --force     - Forçar aplicação mesmo em produção"
    echo "  --backup    - Fazer backup antes de aplicar"
    echo
    echo "Exemplos:"
    echo "  $0 apply"
    echo "  $0 list"
    echo "  $0 dry-run"
    echo
}

main() {
    echo -e "${BLUE}"
    echo "==============================================="
    echo "      ALGORISE - MIGRATION MANAGER"
    echo "==============================================="
    echo -e "${NC}"
    
    local command="$1"
    local force=false
    local backup=false
    
    # Processar opções
    shift
    while [[ $# -gt 0 ]]; do
        case $1 in
            --force)
                force=true
                shift
                ;;
            --backup)
                backup=true
                shift
                ;;
            *)
                echo "Opção desconhecida: $1"
                show_usage
                exit 1
                ;;
        esac
    done
    
    if [ -z "$command" ]; then
        show_usage
        exit 1
    fi
    
    # Verificar se Docker está rodando
    if ! docker ps &> /dev/null; then
        print_error "Docker não está rodando"
        exit 1
    fi
    
    # Verificar se container do banco existe
    if ! docker ps | grep -q "$DB_CONTAINER"; then
        print_error "Container do banco não está rodando: $DB_CONTAINER"
        exit 1
    fi
    
    # Obter senha do banco
    get_db_password
    
    # Criar tabela de controle se necessário
    create_migrations_table
    
    case "$command" in
        "apply")
            if [ "$backup" = true ]; then
                print_info "Fazendo backup antes de aplicar migrations..."
                # Chamar script de backup
                ./scripts/backup.sh full "Pre-migration backup"
            fi
            apply_all_migrations
            ;;
        "list")
            list_pending_migrations
            ;;
        "applied")
            list_applied_migrations
            ;;
        "dry-run")
            dry_run_migrations
            ;;
        "status")
            list_applied_migrations
            echo
            list_pending_migrations
            ;;
        *)
            print_error "Comando inválido: $command"
            show_usage
            exit 1
            ;;
    esac
}

# Executar função principal
main "$@"