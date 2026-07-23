@echo off
title Tepuy - El Chaparro de Guanta
color 0A

echo ============================================
echo   Tepuy - El Chaparro de Guanta
echo ============================================
echo   Sistema digital de salud
echo.

SET ROOT=C:\laragon\www\gestion-salud

:: 0. Matar procesos anteriores
echo [*] Limpiando procesos anteriores...
taskkill /f /im php.exe >nul 2>&1
taskkill /f /im node.exe >nul 2>&1
timeout /T 2 /NOBREAK >nul

:: 1. Iniciar Laragon si no está corriendo
tasklist /FI "IMAGENAME eq laragon.exe" 2>NUL | find /I /N "laragon.exe" >NUL
if "%ERRORLEVEL%" NEQ "0" (
    echo [1/5] Iniciando Laragon...
    start "" "C:\laragon\laragon.exe"
    echo       Esperando 12 segundos...
    timeout /T 12 /NOBREAK >nul
) else (
    echo [1/5] Laragon ya esta en ejecucion.
    timeout /T 2 /NOBREAK >nul
)

:: 2. Iniciar Vite
echo [2/5] Iniciando Vite (npm run dev)...
start "Vite" cmd /c "cd /d "%ROOT%" && npm run dev"
echo       Esperando 10 segundos...
timeout /T 10 /NOBREAK >nul

:: 3. Iniciar servidor PHP
echo [3/5] Iniciando servidor PHP...
start "PHP" cmd /c "cd /d "%ROOT%" && php artisan serve --host=0.0.0.0 --port=8000"
echo       Esperando 5 segundos...
timeout /T 5 /NOBREAK >nul

:: 4. Verificar servidor
echo [4/5] Verificando servidor...
setlocal enabledelayedexpansion
set RETRIES=0
:CHECK
timeout /T 2 /NOBREAK >nul
for /f %%i in ('powershell -Command "try { $r = [System.Net.WebRequest]::CreateHttp('http://localhost:8000'); $r.Timeout=2000; $resp = $r.GetResponse(); $resp.Close(); Write-Output 'OK' } catch { Write-Output 'FAIL' }"') do set STATUS=%%i
if "!STATUS!"=="OK" (
    echo       Servidor respondiendo OK!
) else (
    set /A RETRIES+=1
    if !RETRIES! LSS 8 (
        echo       Esperando servidor... (!RETRIES!/8)
        goto CHECK
    ) else (
        echo       ADVERTENCIA: No se pudo verificar, abriendo de todas formas...
    )
)
endlocal

:: 5. Abrir navegador
echo [5/5] Abriendo navegador...
start "" "http://localhost:8000"

echo.
echo ============================================
echo  LISTO. Aplicacion iniciada.
echo  Desde esta PC:      http://localhost:8000
echo  Desde la red:       http://192.168.1.109:8000
echo ============================================
echo.
echo  Presione cualquier tecla para cerrar...
pause >nul
