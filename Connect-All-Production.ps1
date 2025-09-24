# Script PowerShell para conectar TUDO do sistema de produção
# Conecta: Banco de Dados + Web + Portainer + phpMyAdmin
# Uso: .\Connect-All-Production.ps1

$VPS_IP = "194.163.131.97"  # IP da VPS Contabo
$VPS_USER = "root"
$PORTAINER_USER = "algoadmin"
$PORTAINER_PASS = "dsfkjh3h2j%21DW"

Write-Host "🚀 CONECTANDO TUDO DO SISTEMA DE PRODUÇÃO" -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Yellow

Write-Host "📋 Configurações:" -ForegroundColor Cyan
Write-Host "🖥️  VPS IP: $VPS_IP" -ForegroundColor White
Write-Host "👤 SSH User: $VPS_USER" -ForegroundColor White
Write-Host "🐳 Portainer: $PORTAINER_USER / $PORTAINER_PASS" -ForegroundColor White
Write-Host ""

Write-Host "🔗 Criando túneis SSH..." -ForegroundColor Yellow
Write-Host "📊 Banco MySQL:    localhost:3307" -ForegroundColor Cyan
Write-Host "🌐 Aplicação Web:  http://localhost:8081" -ForegroundColor Cyan
Write-Host "🐳 Portainer:      http://localhost:9001" -ForegroundColor Cyan
Write-Host "📈 phpMyAdmin:     http://localhost:8083" -ForegroundColor Cyan
Write-Host ""

Write-Host "📝 INSTRUÇÕES DE USO:" -ForegroundColor Green
Write-Host "1. Banco de Dados de Produção:" -ForegroundColor White
Write-Host "   Host: 127.0.0.1" -ForegroundColor Gray
Write-Host "   Porta: 3307" -ForegroundColor Gray
Write-Host "   Usuário: busca" -ForegroundColor Gray
Write-Host "   Senha: busca_password" -ForegroundColor Gray
Write-Host ""
Write-Host "2. Portainer (Gerenciamento Docker):" -ForegroundColor White
Write-Host "   URL: http://localhost:9001" -ForegroundColor Gray
Write-Host "   Usuário: algoadmin" -ForegroundColor Gray
Write-Host "   Senha: dsfkjh3h2j%21DW" -ForegroundColor Gray
Write-Host ""
Write-Host "3. Aplicação de Produção:" -ForegroundColor White
Write-Host "   URL: http://localhost:8081" -ForegroundColor Gray
Write-Host ""

Write-Host "⚠️  MANTENHA ESTE TERMINAL ABERTO!" -ForegroundColor Red -BackgroundColor Yellow
Write-Host "   Pressione Ctrl+C para desconectar" -ForegroundColor Red

Write-Host ""
Write-Host "🔄 Estabelecendo conexão..." -ForegroundColor Yellow

# Criar todos os túneis SSH
ssh -L 3307:localhost:3306 -L 8081:localhost:80 -L 9001:localhost:9000 $VPS_USER@$VPS_IP