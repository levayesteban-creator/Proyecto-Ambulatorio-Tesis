@echo off
title Tepuy - Inicio Manual
color 0A
echo ============================================
echo   Tepuy - El Chaparro de Guanta
echo ============================================
echo   Sistema digital de salud
echo.

:: Matar procesos viejos
echo [*] Limpiando procesos anteriores...
taskkill /f /im php.exe >nul 2>&1
taskkill /f /im node.exe >nul 2>&1
timeout /t 2 /nobreak >nul

:: Ir al proyecto
cd /d "C:\laragon\www\gestion-salud"

:: Iniciar Vite
echo [1/3] Iniciando Vite (npm run dev)...
start "Vite" cmd /c "npm run dev"
timeout /t 8 /nobreak >nul

:: Iniciar PHP
echo [2/3] Iniciando servidor PHP...
start "PHP" cmd /c "php artisan serve --host=0.0.0.0 --port=8000"
timeout /t 4 /nobreak >nul

:: Abrir navegador
echo [3/3] Abriendo navegador...
start "" "http://localhost:8000"

echo.
echo ============================================
echo  LISTO. Deberia abrirse el navegador.
echo  Si ve pantalla en blanco, espere 5 segundos
echo  y recargue la pagina (F5).
echo ============================================
echo.
pause
