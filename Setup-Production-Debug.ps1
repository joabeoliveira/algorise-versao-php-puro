# Script para configurar ambiente de debug com produção
# Este script:
# 1. Conecta ao sistema de produção via SSH
# 2. Inicia aplicação local conectada ao banco de produção
# 3. Abre todas as URLs no navegador

param(
    [switch]$SkipBrowser  # Usar para não abrir navegador automaticamente
)

$VPS_IP = "194.163.131.97"
$VPS_USER = "root"

Write-Host "🔧 CONFIGURANDO AMBIENTE DE DEBUG COM PRODUÇÃO" -ForegroundColor Green
Write-Host "=================================================" -ForegroundColor Yellow

# Verificar se há containers rodando
Write-Host "🔍 Verificando containers em execução..." -ForegroundColor Cyan
$containers = docker ps -q
if ($containers) {
    Write-Host "⚠️  Containers detectados rodando. Parando..." -ForegroundColor Yellow
    docker-compose -f docker-compose.dev.yml down 2>$null
    docker-compose -f docker-compose.production-debug.yml down 2>$null
}

Write-Host "📋 PASSO 1: Preparando configuração..." -ForegroundColor Cyan
# Copiar configuração de produção
Copy-Item ".env.production" ".env" -Force
Write-Host "✅ Arquivo .env configurado para produção" -ForegroundColor Green

Write-Host "📋 PASSO 2: Iniciando túnel SSH..." -ForegroundColor Cyan
Write-Host "🔗 Conectando a $VPS_IP..." -ForegroundColor White

# Iniciar túnel SSH em background usando PowerShell Job
$sshJob = Start-Job -ScriptBlock {
    param($VPS_USER, $VPS_IP)
    ssh -L 3307:localhost:3306 -L 8081:localhost:80 -L 9001:localhost:9000 $VPS_USER@$VPS_IP
} -ArgumentList $VPS_USER, $VPS_IP

# Aguardar um pouco para o túnel se estabelecer
Write-Host "⏳ Aguardando túnel SSH se estabelecer..." -ForegroundColor Yellow
Start-Sleep -Seconds 5

Write-Host "📋 PASSO 3: Iniciando aplicação local..." -ForegroundColor Cyan
# Iniciar aplicação conectada à produção
docker-compose -f docker-compose.production-debug.yml up -d

Write-Host "⏳ Aguardando serviços iniciarem..." -ForegroundColor Yellow
Start-Sleep -Seconds 10

Write-Host "🎉 AMBIENTE CONFIGURADO!" -ForegroundColor Green
Write-Host "========================" -ForegroundColor Yellow

Write-Host "📊 ACESSOS DISPONÍVEIS:" -ForegroundColor Cyan
Write-Host "🌐 Aplicação Local (com banco prod): http://localhost:8082" -ForegroundColor White
Write-Host "🌐 Aplicação Produção Direta:        http://localhost:8081" -ForegroundColor White
Write-Host "🐳 Portainer (Gestão Produção):      http://localhost:9001" -ForegroundColor White
Write-Host "📊 phpMyAdmin (Banco Produção):      http://localhost:8083" -ForegroundColor White

Write-Host ""
Write-Host "🔑 CREDENCIAIS:" -ForegroundColor Cyan
Write-Host "Banco: busca / busca_password" -ForegroundColor White
Write-Host "Portainer: algoadmin / dsfkjh3h2j%21DW" -ForegroundColor White

Write-Host ""
Write-Host "⚠️  IMPORTANTE:" -ForegroundColor Red
Write-Host "• Não feche este terminal (SSH ativo)" -ForegroundColor Yellow
Write-Host "• Use Ctrl+C para desconectar tudo" -ForegroundColor Yellow
Write-Host "• Mudanças na aplicação local afetam só o desenvolvimento" -ForegroundColor Yellow
Write-Host "• Dados do banco são da PRODUÇÃO (cuidado!)" -ForegroundColor Red

if (-not $SkipBrowser) {
    Write-Host ""
    Write-Host "🌐 Abrindo navegador..." -ForegroundColor Cyan
    Start-Process "http://localhost:8082"  # Aplicação local
    Start-Sleep -Seconds 2
    Start-Process "http://localhost:9001"  # Portainer
    Start-Sleep -Seconds 2
    Start-Process "http://localhost:8083"  # phpMyAdmin
}

Write-Host ""
Write-Host "✅ Tudo configurado! Pressione qualquer tecla para monitorar ou Ctrl+C para sair..." -ForegroundColor Green

# Aguardar input do usuário ou manter ativo
try {
    while ($true) {
        Start-Sleep -Seconds 5
        # Verificar se túnel SSH ainda está ativo
        $jobState = Get-Job -Id $sshJob.Id | Select-Object -ExpandProperty State
        if ($jobState -eq "Failed" -or $jobState -eq "Stopped") {
            Write-Host "❌ Túnel SSH desconectado!" -ForegroundColor Red
            break
        }
    }
} finally {
    Write-Host "🔄 Limpando ambiente..." -ForegroundColor Yellow
    docker-compose -f docker-compose.production-debug.yml down
    Stop-Job -Id $sshJob.Id -ErrorAction SilentlyContinue
    Remove-Job -Id $sshJob.Id -ErrorAction SilentlyContinue
    Write-Host "✅ Ambiente limpo!" -ForegroundColor Green
}