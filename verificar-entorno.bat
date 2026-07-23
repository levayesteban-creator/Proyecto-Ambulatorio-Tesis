@echo off
chcp 65001 >nul
title Verificacion de Entorno - Sistema de Gestion de Salud

echo.
echo ========================================
echo   SISTEMA DE GESTION DE SALUD
echo   Verificacion de Entorno
echo ========================================
echo.

set ERRORS=0

echo [1/7] Verificando PHP...
php -v >nul 2>&1
if %errorlevel% neq 0 (
    echo   [X] PHP no encontrado. Instalar Laragon o PHP 8.1+
    set /a ERRORS+=1
) else (
    for /f "tokens=2 delims= " %%v in ('php -v 2^>nul ^| findstr /b "PHP"') do set PHPVER=%%v
    echo   [OK] PHP %PHPVER%
)

echo [2/7] Verificando Composer...
composer -V >nul 2>&1
if %errorlevel% neq 0 (
    echo   [X] Composer no encontrado. Descargar de getcomposer.org
    set /a ERRORS+=1
) else (
    echo   [OK] Composer instalado
)

echo [3/7] Verificando Node.js...
node -v >nul 2>&1
if %errorlevel% neq 0 (
    echo   [X] Node.js no encontrado. Instalar desde nodejs.org
    set /a ERRORS+=1
) else (
    for /f %%v in ('node -v') do set NODEVER=%%v
    echo   [OK] Node.js %NODEVER%
)

echo [4/7] Verificando npm...
npm -v >nul 2>&1
if %errorlevel% neq 0 (
    echo   [X] npm no encontrado
    set /a ERRORS+=1
) else (
    echo   [OK] npm instalado
)

echo [5/7] Verificando Git...
git --version >nul 2>&1
if %errorlevel% neq 0 (
    echo   [X] Git no encontrado. Instalar desde git-scm.com
    set /a ERRORS+=1
) else (
    echo   [OK] Git instalado
)

echo [6/7] Verificando MySQL...
mysql --version >nul 2>&1
if %errorlevel% neq 0 (
    echo   [!] MySQL no encontrado en PATH (puede estar en Laragon)
) else (
    echo   [OK] MySQL instalado
)

echo [7/7] Verificando extensiones PHP...
php -m | findstr /i "dom mbstring gd pdo_mysql" >nul 2>&1
if %errorlevel% neq 0 (
    echo   [!] Faltan algunas extensiones PHP (dom, mbstring, gd, pdo_mysql)
) else (
    echo   [OK] Extensiones PHP OK
)

echo.
if %ERRORS% gtr 0 (
    echo ========================================
    echo   FALTAN %ERRORS% COMPONENTES
    echo   Instalar lo que se indica arriba
    echo ========================================
) else (
    echo ========================================
    echo   TODO OK - Entorno listo
    echo ========================================
)
echo.
pause
