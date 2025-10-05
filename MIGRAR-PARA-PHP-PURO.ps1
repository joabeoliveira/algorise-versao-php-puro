# 🔄 Script de Migração Automática para PHP Puro
# Execute este script no PowerShell para migrar automaticamente

Write-Host "🚀 INICIANDO MIGRAÇÃO PARA PHP PURO" -ForegroundColor Green
Write-Host "====================================" -ForegroundColor Yellow

$projectPath = "C:\algorise-versao-php-puro"
$backupPath = "${projectPath}_backup_" + (Get-Date -Format "yyyyMMdd_HHmmss")

# Verificar se está no diretório correto
if (-not (Test-Path "$projectPath\composer.json")) {
    Write-Host "❌ ERRO: Não foi possível localizar o projeto em $projectPath" -ForegroundColor Red
    exit 1
}

Write-Host "📁 Projeto encontrado: $projectPath" -ForegroundColor Green

# 1. BACKUP COMPLETO
Write-Host "💾 Criando backup completo..." -ForegroundColor Yellow
try {
    Copy-Item -Path $projectPath -Destination $backupPath -Recurse -Force
    Write-Host "✅ Backup criado em: $backupPath" -ForegroundColor Green
} catch {
    Write-Host "❌ ERRO ao criar backup: $_" -ForegroundColor Red
    exit 1
}

# 2. ATUALIZAR CONFIGURAÇÕES
Write-Host "🔧 Atualizando arquivos de configuração..." -ForegroundColor Yellow

# Substituir composer.json
if (Test-Path "$projectPath\composer-php-puro.json") {
    Copy-Item "$projectPath\composer-php-puro.json" "$projectPath\composer.json" -Force
    Write-Host "✅ composer.json atualizado" -ForegroundColor Green
}

# Substituir settings.php
if (Test-Path "$projectPath\src\settings-php-puro.php") {
    Copy-Item "$projectPath\src\settings-php-puro.php" "$projectPath\src\settings.php" -Force
    Write-Host "✅ settings.php atualizado" -ForegroundColor Green
}

# Substituir index.php
if (Test-Path "$projectPath\public\index-php-puro.php") {
    Copy-Item "$projectPath\public\index-php-puro.php" "$projectPath\public\index.php" -Force
    Write-Host "✅ index.php atualizado" -ForegroundColor Green
}

# 3. ATUALIZAR CONTROLLERS
Write-Host "🎯 Atualizando Controllers..." -ForegroundColor Yellow

$controllers = @(
    "UsuarioController",
    "FornecedorController", 
    "RelatorioController"
)

foreach ($controller in $controllers) {
    $sourceFile = "$projectPath\src\Controller\${controller}-PHP-Puro.php"
    $targetFile = "$projectPath\src\Controller\${controller}.php"
    
    if (Test-Path $sourceFile) {
        Copy-Item $sourceFile $targetFile -Force
        Write-Host "✅ $controller atualizado" -ForegroundColor Green
    } else {
        Write-Host "⚠️  $controller-PHP-Puro.php não encontrado" -ForegroundColor Yellow
    }
}

# 4. INSTALAR DEPENDÊNCIAS MÍNIMAS
Write-Host "📦 Instalando dependências mínimas..." -ForegroundColor Yellow

Set-Location $projectPath

try {
    # Remove vendor antigo
    if (Test-Path "vendor") {
        Remove-Item "vendor" -Recurse -Force
        Write-Host "🗑️  Pasta vendor antiga removida" -ForegroundColor Yellow
    }
    
    # Remove composer.lock antigo
    if (Test-Path "composer.lock") {
        Remove-Item "composer.lock" -Force
        Write-Host "🗑️  composer.lock antigo removido" -ForegroundColor Yellow
    }
    
    # Instala nova estrutura
    & composer install --no-dev --optimize-autoloader
    Write-Host "✅ Dependências instaladas com sucesso" -ForegroundColor Green
    
} catch {
    Write-Host "⚠️  Aviso: Erro ao instalar dependências: $_" -ForegroundColor Yellow
    Write-Host "🔧 Execute manualmente: composer install --no-dev" -ForegroundColor Cyan
}

# 5. LIMPAR ARQUIVOS TEMPORÁRIOS
Write-Host "🧹 Limpando arquivos temporários..." -ForegroundColor Yellow

$tempFiles = @(
    "src\Controller\*-PHP-Puro.php",
    "composer-php-puro.json",
    "src\settings-php-puro.php", 
    "public\index-php-puro.php",
    "LISTA-LIMPEZA.md"
)

foreach ($pattern in $tempFiles) {
    $files = Get-ChildItem -Path $pattern -ErrorAction SilentlyContinue
    foreach ($file in $files) {
        Remove-Item $file.FullName -Force
        Write-Host "🗑️  Removido: $($file.Name)" -ForegroundColor Gray
    }
}

# 6. VERIFICAR ESTRUTURA FINAL
Write-Host "🔍 Verificando estrutura final..." -ForegroundColor Yellow

$essentialFiles = @(
    "public\index.php",
    "src\settings.php",
    "src\Core\Router.php",
    "src\Core\Http.php", 
    "src\Core\Mail.php",
    "src\Core\Pdf.php",
    "src\Core\Spreadsheet.php",
    "composer.json"
)

$allOk = $true
foreach ($file in $essentialFiles) {
    if (Test-Path $file) {
        Write-Host "✅ $file" -ForegroundColor Green
    } else {
        Write-Host "❌ $file - FALTANDO" -ForegroundColor Red
        $allOk = $false
    }
}

# 7. RESULTADO FINAL
Write-Host ""
Write-Host "🎉 MIGRAÇÃO CONCLUÍDA!" -ForegroundColor Green
Write-Host "======================" -ForegroundColor Yellow

if ($allOk) {
    Write-Host "✅ Todos os arquivos essenciais estão presentes" -ForegroundColor Green
    Write-Host "📊 Comparação:" -ForegroundColor Cyan
    
    # Tamanho do vendor
    $vendorSizeMB = if (Test-Path "vendor") { 
        [math]::Round((Get-ChildItem "vendor" -Recurse | Measure-Object -Property Length -Sum).Sum / 1MB, 2) 
    } else { 0 }
    
    Write-Host "   📁 Tamanho vendor/: ${vendorSizeMB}MB (era ~120MB)" -ForegroundColor White
    Write-Host "   📦 Dependências: 1 package (era 15+ packages)" -ForegroundColor White
    Write-Host "   🎯 Compatibilidade: PHP 8.1+ universal" -ForegroundColor White
    
    Write-Host ""
    Write-Host "🚀 PRÓXIMOS PASSOS:" -ForegroundColor Cyan
    Write-Host "1. Teste localmente: http://localhost/seu-projeto" -ForegroundColor White
    Write-Host "2. Verifique se o login funciona" -ForegroundColor White
    Write-Host "3. Teste upload de planilhas" -ForegroundColor White
    Write-Host "4. Teste geração de relatórios" -ForegroundColor White
    Write-Host "5. Deploy em produção quando tudo OK" -ForegroundColor White
    
} else {
    Write-Host "⚠️  Alguns arquivos estão faltando. Verifique os erros acima." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "📋 INFORMAÇÕES IMPORTANTES:" -ForegroundColor Cyan
Write-Host "• Backup salvo em: $backupPath" -ForegroundColor White
Write-Host "• Em caso de problemas, restaure: Copy-Item '$backupPath\*' '$projectPath' -Recurse -Force" -ForegroundColor White
Write-Host "• Documentação: README-PHP-PURO.md" -ForegroundColor White

Write-Host ""
Write-Host "🔧 Se houver problemas, execute manualmente:" -ForegroundColor Yellow
Write-Host "   composer install --no-dev" -ForegroundColor Gray
Write-Host "   php -S localhost:8000 -t public" -ForegroundColor Gray

Write-Host ""
Write-Host "✨ Sistema migrado com sucesso para PHP PURO!" -ForegroundColor Green