# Script de Deploy Algorise para Portainer
# Arquivo: Deploy-Algorise-Portainer.ps1

Write-Host "🚀 Deploy Algorise para Portainer" -ForegroundColor Green
Write-Host "=================================" -ForegroundColor Green

# Verificar se os arquivos necessários existem
$arquivos = @(
    "web-stack.yml",
    "app-stack.yml", 
    "db-stack.yml",
    ".env.algorise.production"
)

Write-Host "`n📋 Verificando arquivos necessários..." -ForegroundColor Yellow

foreach ($arquivo in $arquivos) {
    if (Test-Path $arquivo) {
        Write-Host "✅ $arquivo - OK" -ForegroundColor Green
    } else {
        Write-Host "❌ $arquivo - NÃO ENCONTRADO" -ForegroundColor Red
    }
}

Write-Host "`n🔧 Instruções para Deploy no Portainer:" -ForegroundColor Cyan
Write-Host "=======================================" -ForegroundColor Cyan

Write-Host "`n1. Acesse o Portainer:" -ForegroundColor White
Write-Host "   URL: https://pp.sheep7.com/" -ForegroundColor Gray

Write-Host "`n2. Vá para Stacks > Add Stack" -ForegroundColor White

Write-Host "`n3. Para a Stack WEB (algorise-web):" -ForegroundColor White
Write-Host "   - Nome: algorise-web" -ForegroundColor Gray
Write-Host "   - Copie o conteúdo de: web-stack.yml" -ForegroundColor Gray
Write-Host "   - Variáveis de ambiente:" -ForegroundColor Gray
Write-Host "     APP_HOST=app.algorise.com.br" -ForegroundColor DarkGray

Write-Host "`n4. Para a Stack APP (algorise-app):" -ForegroundColor White  
Write-Host "   - Nome: algorise-app" -ForegroundColor Gray
Write-Host "   - Copie o conteúdo de: app-stack.yml" -ForegroundColor Gray
Write-Host "   - Use as variáveis do arquivo: .env.algorise.production" -ForegroundColor Gray

Write-Host "`n5. Para a Stack DB (algorise-db):" -ForegroundColor White
Write-Host "   - Nome: algorise-db" -ForegroundColor Gray  
Write-Host "   - Copie o conteúdo de: db-stack.yml" -ForegroundColor Gray

Write-Host "`n🌐 Após o deploy:" -ForegroundColor Cyan
Write-Host "   - Aguarde 2-3 minutos para o certificado SSL" -ForegroundColor Gray
Write-Host "   - Acesse: https://app.algorise.com.br" -ForegroundColor Gray
Write-Host "   - Verifique os logs em caso de erro" -ForegroundColor Gray

Write-Host "`n⚠️  IMPORTANTE:" -ForegroundColor Yellow
Write-Host "   - Faça backup das stacks atuais antes de alterar" -ForegroundColor Red
Write-Host "   - Coordene com a equipe para evitar downtime" -ForegroundColor Red
Write-Host "   - Teste todas as funcionalidades após deploy" -ForegroundColor Red

Write-Host "`n✅ Script concluído! Consulte o arquivo:" -ForegroundColor Green
Write-Host "   GUIA-ATUALIZACAO-PORTAINER-ALGORISE.md" -ForegroundColor Gray

# Pausa para leitura
Read-Host "`nPressione Enter para continuar"