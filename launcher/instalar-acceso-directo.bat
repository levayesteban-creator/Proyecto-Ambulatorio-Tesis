@echo off
title Instalar Tepuy - Acceso directo
cd /d "%~dp0"
powershell.exe -ExecutionPolicy ByPass -File "%~dp0instalar-acceso-directo.ps1"
echo.
pause
