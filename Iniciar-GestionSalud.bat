@echo off
title Gesti?n Salud - El Chaparro
color 0A
cd /d "C:\laragon\www\gestion-salud"

set MAX_RETRIES=30

echo ============================================
echo   Gesti?n Salud - El Chaparro
echo ============================================
echo   Iniciando servicios...
echo ============================================
echo.

:: ------------ Apache ------------
netstat -an 2>NUL | find ":80 " | find "LISTENING" >NUL
if not errorlevel 1 goto apache_ok

echo [..] Iniciando Apache...
start "" /B "C:\laragon\bin\apache\httpd-2.4.62-240904-win64-VS17\bin\httpd.exe" -d "C:/laragon/bin/apache/httpd-2.4.62-240904-win64-VS17"

set RETRY=0
:wait_apache
if %RETRY% geq %MAX_RETRIES% goto error_apache
timeout /t 2 /nobreak >NUL
netstat -an 2>NUL | find ":80 " | find "LISTENING" >NUL
if errorlevel 1 ( set /a RETRY+=1 & goto wait_apache )
echo [OK] Apache listo
goto mysql_check

:apache_ok
echo [OK] Apache ya esta corriendo

:: ------------ MySQL ------------
:mysql_check
netstat -an 2>NUL | find ":3306 " | find "LISTENING" >NUL
if not errorlevel 1 goto mysql_ok

echo [..] Iniciando MySQL...
start "" /B "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqld.exe" --log-error=C:\laragon\data\mysql-8.4\mysqld.log

set RETRY=0
:wait_mysql
if %RETRY% geq %MAX_RETRIES% goto error_mysql
timeout /t 2 /nobreak >NUL
netstat -an 2>NUL | find ":3306 " | find "LISTENING" >NUL
if errorlevel 1 ( set /a RETRY+=1 & goto wait_mysql )
echo [OK] MySQL listo
goto vite_check

:mysql_ok
echo [OK] MySQL ya esta corriendo

:: ------------ Vite ------------
:vite_check
netstat -an 2>NUL | find ":5173 " | find "LISTENING" >NUL
if not errorlevel 1 goto vite_ok

echo [..] Iniciando Vite...
start "Vite" /min cmd /c "cd /d C:\laragon\www\gestion-salud && npm run dev"

set RETRY=0
:wait_vite
if %RETRY% geq %MAX_RETRIES% goto error_vite
timeout /t 2 /nobreak >NUL
netstat -an 2>NUL | find ":5173 " | find "LISTENING" >NUL
if errorlevel 1 ( set /a RETRY+=1 & goto wait_vite )
echo [OK] Vite listo
goto app_check

:vite_ok
echo [OK] Vite ya esta corriendo

:: ------------ App check ------------
:app_check
set RETRY=0
echo [..] Verificando aplicacion...
:wait_app
if %RETRY% geq 15 goto error_app
timeout /t 1 /nobreak >NUL
PowerShell -NoProfile -Command "try { $r = Invoke-WebRequest -Uri 'http://gestion-salud.test' -UseBasicParsing -TimeoutSec 2; exit(0) } catch { exit(1) }" 2>NUL
if errorlevel 1 ( set /a RETRY+=1 & goto wait_app )
echo [OK] Aplicacion lista
echo.

:: ------------ Launch ------------
echo [OK] Abriendo aplicacion...
start "" "C:\Program Files\Google\Chrome\Application\chrome.exe" --app=http://gestion-salud.test --start-maximized

echo ============================================
echo   Gesti?n Salud iniciado correctamente
echo   Esta ventana se cerrara en 5 segundos...
echo ============================================
timeout /t 5 /nobreak >NUL
exit

:: ------------ Errors ------------
:error_apache
echo.
echo [ERROR] No se pudo iniciar Apache (puerto 80).
echo         Verifique que Laragon este funcionando.
pause
exit /b 1

:error_mysql
echo.
echo [ERROR] No se pudo iniciar MySQL (puerto 3306).
echo         Verifique que Laragon este funcionando.
pause
exit /b 1

:error_vite
echo.
echo [ERROR] No se pudo iniciar Vite (puerto 5173).
echo         Ejecute "Iniciar-Vite.bat" desde la carpeta del proyecto.
pause
exit /b 1

:error_app
echo.
echo [ERROR] La aplicacion no responde en http://gestion-salud.test
echo         Verifique que Apache y Vite esten funcionando.
pause
exit /b 1
