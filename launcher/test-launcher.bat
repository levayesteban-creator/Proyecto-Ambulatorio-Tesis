@echo off
title Gestión Salud - Diagnóstico
color 0A

echo ============================================
echo   Gestión Salud - Modo Diagnóstico
echo ============================================
echo.

echo [1/4] Verificando Node.js...
where npm >nul 2>&1
if %errorlevel% neq 0 ( echo  [ERROR] npm no encontrado & pause & exit /b )
echo  [OK] npm encontrado

echo [2/4] Iniciando Vite...
cd /d "C:\laragon\www\gestion-salud"
start "Vite" cmd /c "npm run dev"
echo  Esperando 8 segundos...
timeout /t 8 /nobreak >nul

echo [3/4] Verificando Vite en puerto 5173...
netstat -an | find "127.0.0.1:5173" >nul
if %errorlevel% equ 0 ( echo  [OK] Vite corriendo ) else ( echo  [WARN] Vite podria no haber arrancado )

echo [4/4] Iniciando servidor PHP...
cd /d "C:\laragon\www\gestion-salud"
start "PHP Serve" cmd /c "php artisan serve --host=0.0.0.0 --port=8000"
timeout /t 4 /nobreak >nul

echo.
echo Abriendo navegador...
start "" "http://localhost:8000"

echo.
echo ============================================
echo  Aplicacion abierta. Cierre esta ventana
echo  cuando termine de usar el sistema.
echo ============================================
echo.
pause
