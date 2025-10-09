@echo off
echo ============================================
echo   ALGORISE - Verificacao do Ambiente
echo ============================================

echo.
echo [1/5] Verificando estrutura de arquivos...
if exist "public\index.php" (
    echo [OK] index.php encontrado
) else (
    echo [ERRO] index.php nao encontrado!
    goto :erro
)

if exist "src\settings-php-puro.php" (
    echo [OK] settings-php-puro.php encontrado
) else (
    echo [ERRO] settings-php-puro.php nao encontrado!
    goto :erro
)

if exist "vendor\autoload.php" (
    echo [OK] Dependencias do Composer instaladas
) else (
    echo [AVISO] Execute: composer install
)

echo.
echo [2/5] Verificando sintaxe PHP...
php -l public\index.php > nul 2>&1
if %errorlevel% eq 0 (
    echo [OK] Sintaxe do PHP correta
) else (
    echo [ERRO] Erro de sintaxe no PHP!
    goto :erro
)

echo.
echo [3/5] Verificando conexao com banco...
php -r "try { $pdo = new PDO('mysql:host=127.0.0.1;dbname=algorise_db', 'root', ''); echo '[OK] Conexao com banco estabelecida'; } catch(Exception $e) { echo '[ERRO] Nao foi possivel conectar ao banco: ' . $e->getMessage(); }" 2>nul

echo.
echo [4/5] Verificando pastas necessarias...
if not exist "storage" mkdir storage
if not exist "storage\propostas" mkdir storage\propostas
if not exist "public\uploads" mkdir public\uploads
if not exist "public\uploads\interface" mkdir public\uploads\interface
echo [OK] Pastas criadas/verificadas

echo.
echo [5/5] URLs de acesso:
echo [INFO] Via XAMPP: http://localhost/algorise-versao-php-puro
echo [INFO] Via PHP built-in: php -S localhost:8080 -t public
echo [INFO] Login: admin@algorise.com / admin123

echo.
echo ============================================
echo   AMBIENTE PRONTO PARA DESENVOLVIMENTO!
echo ============================================
goto :fim

:erro
echo.
echo ============================================
echo   ERRO - Verifique as configuracoes
echo ============================================

:fim
echo.
pause