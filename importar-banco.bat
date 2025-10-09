@echo off
echo ============================================
echo ALGORISE - Script de Importacao do Banco
echo Arquivo: algorise_db.sql
echo Data: 2025-10-09
echo ============================================

echo.
echo [INFO] Verificando conexao com MySQL...
mysql -u root -p -e "SELECT VERSION();" 2>nul
if %errorlevel% neq 0 (
    echo [ERRO] Nao foi possivel conectar ao MySQL!
    echo Verifique se o MySQL esta rodando e as credenciais estao corretas.
    pause
    exit /b 1
)

echo [OK] Conexao com MySQL estabelecida!
echo.

echo [INFO] Removendo banco anterior (se existir)...
mysql -u root -p -e "DROP DATABASE IF EXISTS algorise_db;"

echo [INFO] Importando estrutura do banco...
mysql -u root -p < algorise_db.sql

if %errorlevel% eq 0 (
    echo.
    echo ============================================
    echo [SUCESSO] Banco importado com sucesso!
    echo ============================================
    echo.
    echo Tabelas criadas:
    mysql -u root -p algorise_db -e "SHOW TABLES;"
    echo.
    echo Usuario padrao criado:
    echo Email: admin@algorise.com
    echo Senha: admin123
    echo.
    echo Configuracoes padrao inseridas:
    mysql -u root -p algorise_db -e "SELECT categoria, COUNT(*) as total FROM configuracoes GROUP BY categoria;"
    echo.
) else (
    echo [ERRO] Falha na importacao do banco!
    echo Verifique o arquivo algorise_db.sql
)

echo.
echo Pressione qualquer tecla para continuar...
pause >nul