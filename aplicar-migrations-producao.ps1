# Script PowerShell para aplicar migrations no banco de produção
# Data: 2025-10-25

Write-Host "=== APLICANDO MIGRATIONS NO BANCO DE PRODUÇÃO ===" -ForegroundColor Cyan
Write-Host ""

# Configurações
$PROJECT_ID = "algorise-producao"
$INSTANCE_NAME = "algorise-db"
$DB_NAME = "algorise"

# Lista de migrations
$migrations = @(
    "migrations\2025-10-25_criar_tabelas_lotes_solicitacao.sql",
    "migrations\2025-10-25_corrigir_notas_tecnicas_processo_id_nullable.sql"
)

Write-Host "Migrations a serem aplicadas:" -ForegroundColor Yellow
foreach ($migration in $migrations) {
    Write-Host "  - $migration"
}
Write-Host ""

$confirm = Read-Host "Deseja continuar? (S/N)"
if ($confirm -notmatch '^[Ss]$') {
    Write-Host "Operação cancelada." -ForegroundColor Red
    exit
}

Write-Host ""
Write-Host "Aplicando migrations..." -ForegroundColor Cyan
Write-Host ""

foreach ($migration in $migrations) {
    Write-Host "Aplicando: $migration" -ForegroundColor Yellow
    
    # Lê o conteúdo do arquivo SQL
    $sqlContent = Get-Content $migration -Raw
    
    # Aplica a migration usando gcloud
    $sqlContent | gcloud sql connect $INSTANCE_NAME --project=$PROJECT_ID --database=$DB_NAME
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ $migration aplicada com sucesso" -ForegroundColor Green
    } else {
        Write-Host "✗ Erro ao aplicar $migration" -ForegroundColor Red
        $continue = Read-Host "Deseja continuar? (S/N)"
        if ($continue -notmatch '^[Ss]$') {
            Write-Host "Processo interrompido." -ForegroundColor Red
            exit 1
        }
    }
    Write-Host ""
}

Write-Host ""
Write-Host "=== MIGRATIONS APLICADAS COM SUCESSO ===" -ForegroundColor Green
Write-Host ""
Write-Host "Próximos passos:" -ForegroundColor Cyan
Write-Host "1. Faça o deploy: gcloud app deploy --project=algorise-producao"
Write-Host "2. Teste as funcionalidades corrigidas"
Write-Host ""
