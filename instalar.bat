@echo off
chcp 65001 >nul
title Instalacion - Sistema de Gestion de Salud

echo.
echo ========================================
echo   SISTEMA DE GESTION DE SALUD
echo   Instalacion Automatica
echo ========================================
echo.

:: Verificar PHP
php -v >nul 2>&1
if %errorlevel% neq 0 (
    echo [X] PHP no encontrado. Instalar Laragon primero.
    pause
    exit /b 1
)

:: Verificar Composer
composer -V >nul 2>&1
if %errorlevel% neq 0 (
    echo [X] Composer no encontrado. Descargar de getcomposer.org
    pause
    exit /b 1
)

:: Verificar Node
node -v >nul 2>&1
if %errorlevel% neq 0 (
    echo [X] Node.js no encontrado. Instalar desde nodejs.org
    pause
    exit /b 1
)

echo [1/7] Copiando archivo de entorno...
if not exist .env (
    copy .env.example .env
    php artisan key:generate
    echo   [OK] .env creado y llave generada
) else (
    echo   [OK] .env ya existe
)

echo.
echo ========================================
echo   CONFIGURAR CUENTA DE ADMINISTRADOR
echo ========================================
echo.
set /p ADMIN_EMAIL="Email del admin: "
set /p ADMIN_PASSWORD="Contrasena del admin: "
set /p ADMIN_NAME="Nombre del admin: "

:: Actualizar .env con las credenciales
php -r "$f='.env'; $c=file_get_contents($f); $c=preg_replace('/ADMIN_EMAIL=.*/','ADMIN_EMAIL=\"%ADMIN_EMAIL%\"',$c); $c=preg_replace('/ADMIN_PASSWORD=.*/','ADMIN_PASSWORD=\"%ADMIN_PASSWORD%\"',$c); $c=preg_replace('/ADMIN_NAME=.*/','ADMIN_NAME=\"%ADMIN_NAME%\"',$c); file_put_contents($f,$c);"
echo   [OK] Credenciales configuradas en .env

echo [2/7] Instalando dependencias PHP...
call composer install --no-interaction
if %errorlevel% neq 0 (
    echo   [X] Error en composer install
    pause
    exit /b 1
)
echo   [OK] Dependencias PHP instaladas

echo [3/7] Instalando dependencias JS...
call npm install
if %errorlevel% neq 0 (
    echo   [X] Error en npm install
    pause
    exit /b 1
)
echo   [OK] Dependencias JS instaladas

echo [4/7] Compilando frontend...
call npm run build
if %errorlevel% neq 0 (
    echo   [X] Error en npm run build
    pause
    exit /b 1
)
echo   [OK] Frontend compilado

echo [5/7] Configurando base de datos...
php artisan migrate --force
if %errorlevel% neq 0 (
    echo   [X] Error en migraciones
    pause
    exit /b 1
)
echo   [OK] Migraciones ejecutadas

echo [6/7] Poblando base de datos...
php artisan db:seed --force
if %errorlevel% neq 0 (
    echo   [!] Advertencia: Error en seeders (puede que ya existan datos)
)
echo   [OK] Datos iniciales cargados

echo [7/7] Limpiando cache...
php artisan config:clear
php artisan route:clear
php artisan cache:clear
echo   [OK] Cache limpiado

echo.
echo ========================================
echo   INSTALACION COMPLETADA
echo ========================================
echo.
echo   Cuenta de administrador:
echo   Email:       %ADMIN_EMAIL%
echo   Contrasena:  %ADMIN_PASSWORD%
echo.
echo   Para iniciar el servidor:
echo   php artisan serve
echo.
echo   Luego abrir: http://localhost:8000
echo.
echo   IMPORTANTE: Cambiar la contrasena despues del primer inicio.
echo.
pause
