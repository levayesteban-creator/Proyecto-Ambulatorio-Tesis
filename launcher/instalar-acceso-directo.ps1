<#
.SYNOPSIS
    Instala acceso directo profesional para Tepuy - Sistema Digital de Salud
.DESCRIPTION
    Crea un acceso directo en el Escritorio que abre la aplicaci�n
    en modo ventana (sin barra de direcciones del navegador),
    como si fuera un programa de escritorio nativo.
#>

$projectRoot = "C:\laragon\www\gestion-salud"
$vbsPath = "$projectRoot\launcher\GestionSalud.vbs"
$iconPath = "$projectRoot\public\favicon.ico"
$desktop = [Environment]::GetFolderPath("Desktop")
$shortcutPath = "$desktop\Tepuy - Sistema Digital de Salud.lnk"

# Validar que los archivos existen
if (-not (Test-Path $vbsPath)) {
    Write-Host "ERROR: No se encuentra $vbsPath" -ForegroundColor Red
    exit 1
}

# Crear acceso directo
$wsh = New-Object -ComObject WScript.Shell
$shortcut = $wsh.CreateShortcut($shortcutPath)
$shortcut.TargetPath = "wscript.exe"
$shortcut.Arguments = """" + $vbsPath + """"
$shortcut.WorkingDirectory = "$projectRoot\launcher"
$shortcut.Description = "Tepuy - Sistema Digital de Salud - MPPS"
$shortcut.WindowStyle = 7

# Asignar icono personalizado
if (Test-Path $iconPath) {
    $shortcut.IconLocation = "$iconPath, 0"
}

$shortcut.Save()

Write-Host "`u{2713} Acceso directo creado en el Escritorio:" -ForegroundColor Green
Write-Host "  $shortcutPath" -ForegroundColor Cyan
Write-Host ""
Write-Host "`u{1F4A1} Para una experiencia óptima:" -ForegroundColor Yellow
Write-Host "  1. Botón derecho sobre el acceso directo" -ForegroundColor White
Write-Host "     → 'Anclar a la barra de tareas'" -ForegroundColor White
Write-Host "  2. O arrástrelo a la barra de tareas" -ForegroundColor White
Write-Host ""
Write-Host "`u{2699} Configure Laragon para inicio automático:" -ForegroundColor Yellow
Write-Host "  Laragon → Menú → Autostart → Marcar 'Launch Laragon at startup'" -ForegroundColor White
Write-Host "  Así los servicios arrancan solos al encender la PC." -ForegroundColor White
Write-Host ""
Write-Host "`u{1F4A5} Al hacer doble clic en el acceso directo:" -ForegroundColor Green
Write-Host "  1. Laragon arranca (si no estaba corriendo)" -ForegroundColor White
Write-Host "  2. Se abre la aplicación en una ventana limpia" -ForegroundColor White
Write-Host "     (sin barra de direcciones, como si fuera Word)" -ForegroundColor White
Write-Host ""
Write-Host "`u{1F4F1} Acceso desde cualquier dispositivo de la red:" -ForegroundColor Yellow
Write-Host "  Abra el navegador y visite:" -ForegroundColor White
Write-Host "  http://192.168.1.109:8000" -ForegroundColor Cyan
