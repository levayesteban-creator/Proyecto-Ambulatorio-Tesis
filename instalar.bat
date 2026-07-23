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

echo [1/6] Copiando archivo de entorno...
if not exist .env (
    copy .env.example .env
    php artisan key:generate
    echo   [OK] .env creado y llave generada
) else (
    echo   [OK] .env ya existe
)

echo [2/6] Instalando dependencias PHP...
call composer install --no-interaction
if %errorlevel% neq 0 (
    echo   [X] Error en composer install
    pause
    exit /b 1
)
echo   [OK] Dependencias PHP instaladas

echo [3/6] Instalando dependencias JS...
call npm install
if %errorlevel% neq 0 (
    echo   [X] Error en npm install
    pause
    exit /b 1
)
echo   [OK] Dependencias JS instaladas

echo [4/6] Compilando frontend...
call npm run build
if %errorlevel% neq 0 (
    echo   [X] Error en npm run build
    pause
    exit /b 1
)
echo   [OK] Frontend compilado

echo [5/6] Configurando base de datos...
echo   Verificando conexion a MySQL...
php artisan db:show >nul 2>&1
if %errorlevel% neq 0 (
    echo   [!] No se pudo conectar a MySQL.
    echo   Verificar que MySQL este corriendo y las credenciales en .env sean correctas.
    echo   Presiona cualquier tecla para continuar o Ctrl+C para cancelar...
    pause >nul
)

php artisan migrate --force
if %errorlevel% neq 0 (
    echo   [X] Error en migraciones
    pause
    exit /b 1
)
echo   [OK] Migraciones ejecutadas

echo [6/6] Poblando base de datos...
php artisan db:seed --force
if %errorlevel% neq 0 (
    echo   [!] Advertencia: Error en seeders (puede que ya existan datos)
)
echo   [OK] Datos iniciales cargados

echo.
echo ========================================
echo   INSTALACION COMPLETADA
echo ========================================
echo.
echo   Cuenta de administrador:
echo   Email:    levayesteban@gmail.com
echo   Contrasena: Estebanmiguel*
echo.
echo   Para iniciar el servidor:
echo   php artisan serve
echo.
echo   Luego abrir: http://localhost:8000
echo.
pause
