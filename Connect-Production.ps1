# Script PowerShell para conectar ao sistema de produção
# Uso: .\Connect-Production.ps1 -Mode database

param(
    [Parameter(Mandatory=$true)]
    [ValidateSet("database", "web", "full", "portainer")]
    [string]$Mode
)

$VPS_IP = "194.163.131.97"  # IP da VPS Contabo
$VPS_USER = "root"
$SSH_PASSWORD = "Ku1bV7ptjetr1cJ"  # Senha SSH
$PORTAINER_USER = "algoadmin"
$PORTAINER_PASS = "dsfkjh3h2j%21DW"

Write-Host "🔗 Conectando ao sistema de produção..." -ForegroundColor Green

switch ($Mode) {
    "database" {
        Write-Host "📊 Criando túnel para banco de dados..." -ForegroundColor Yellow
        Write-Host "💡 Banco ficará disponível em: localhost:3307" -ForegroundColor Cyan
        Write-Host "🔑 Credenciais: busca / busca_password" -ForegroundColor Cyan
        ssh -L 3307:localhost:3306 $VPS_USER@$VPS_IP
    }
    "web" {
        Write-Host "🌐 Criando túnel para aplicação web..." -ForegroundColor Yellow
        Write-Host "💻 Aplicação ficará disponível em: http://localhost:8081" -ForegroundColor Cyan
        ssh -L 8081:localhost:80 $VPS_USER@$VPS_IP
    }
    "full" {
        Write-Host "🚀 Criando túneis completos..." -ForegroundColor Yellow
        Write-Host "📊 Banco: localhost:3307" -ForegroundColor Cyan
        Write-Host "🌐 Web: http://localhost:8081" -ForegroundColor Cyan
        ssh -L 3307:localhost:3306 -L 8081:localhost:80 $VPS_USER@$VPS_IP
    }
    "portainer" {
        Write-Host "🐳 Criando túnel para Portainer..." -ForegroundColor Yellow
        Write-Host "⚡ Portainer ficará disponível em: http://localhost:9001" -ForegroundColor Cyan
        Write-Host "🔑 Credenciais: algoadmin / dsfkjh3h2j%21DW" -ForegroundColor Cyan
        ssh -L 9001:localhost:9000 $VPS_USER@$VPS_IP
    }
}

Write-Host "✅ Conexão estabelecida! Mantenha este terminal aberto." -ForegroundColor Green