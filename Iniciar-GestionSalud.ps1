$ErrorActionPreference = 'SilentlyContinue'
$projectRoot = 'C:\laragon\www\gestion-salud'
$laragonExe = 'C:\laragon\laragon.exe'
$apacheExe = 'C:\laragon\bin\apache\httpd-2.4.62-240904-win64-VS17\bin\httpd.exe'
$apacheDir = 'C:/laragon/bin/apache/httpd-2.4.62-240904-win64-VS17'
$mysqlExe = 'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqld.exe'
$mysqlLog = 'C:\laragon\data\mysql-8.4\mysqld.log'
$chromeExe = 'C:\Program Files\Google\Chrome\Application\chrome.exe'
$logFile = "$env:TEMP\gestion-salud-startup.log"
$maxRetries = 30

function Log($msg) {
    $timestamp = Get-Date -Format 'HH:mm:ss'
    "$timestamp $msg" | Out-File -FilePath $logFile -Append
}

Log '=== Iniciando Gestion Salud ==='

# Limpiar archivos stale de Vite (evita pantalla en blanco)
Remove-Item -Force "$projectRoot\public\hot" -ErrorAction SilentlyContinue
Log 'Archivo hot eliminado (si existia)'

# Matar procesos Node huérfanos de sesiones anteriores
Get-Process -Name "node" -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 1
Log 'Procesos Node anteriores limpiados'

# --- Apache (port 80) ---
$apacheOk = Get-NetTCPConnection -LocalPort 80 -State Listen -ErrorAction SilentlyContinue
if (-not $apacheOk) {
    Log 'Iniciando Apache...'
    Start-Process -FilePath $apacheExe -ArgumentList "-d `"$apacheDir`"" -WindowStyle Hidden
    $retry = 0
    while ($retry -lt $maxRetries) {
        $apacheOk = Get-NetTCPConnection -LocalPort 80 -State Listen -ErrorAction SilentlyContinue
        if ($apacheOk) { break }
        $retry++; Start-Sleep -Seconds 2
    }
    if ($retry -ge $maxRetries) { Log 'ERROR: Apache no inici?'; exit 1 }
    Log 'Apache listo'
} else { Log 'Apache ya esta corriendo' }

# --- MySQL (port 3306) ---
$mysqlOk = Get-NetTCPConnection -LocalPort 3306 -State Listen -ErrorAction SilentlyContinue
if (-not $mysqlOk) {
    Log 'Iniciando MySQL...'
    Start-Process -FilePath $mysqlExe -ArgumentList "--log-error=`"$mysqlLog`"" -WindowStyle Hidden
    $retry = 0
    while ($retry -lt $maxRetries) {
        $mysqlOk = Get-NetTCPConnection -LocalPort 3306 -State Listen -ErrorAction SilentlyContinue
        if ($mysqlOk) { break }
        $retry++; Start-Sleep -Seconds 2
    }
    if ($retry -ge $maxRetries) { Log 'ERROR: MySQL no inici?'; exit 1 }
    Log 'MySQL listo'
} else { Log 'MySQL ya esta corriendo' }

# --- Laragon (tray icon) ---
if (-not (Get-Process -Name 'laragon' -ErrorAction SilentlyContinue)) {
    Log 'Iniciando Laragon...'
    Start-Process -FilePath $laragonExe -WindowStyle Hidden
}

# --- Vite (port 5173) — DESHABILITADO: usar compilación estática (npm run build) ---
Log 'Vite omitido — usando compilacion estatica'

# --- Aplicacion ---
Log 'Verificando aplicacion...'
$retry = 0
while ($retry -lt 15) {
    try {
        $r = Invoke-WebRequest -Uri 'http://gestion-salud.test' -UseBasicParsing -TimeoutSec 2
        if ($r.StatusCode -eq 200) { break }
    } catch {}
    $retry++; Start-Sleep -Seconds 1
}
if ($retry -ge 15) { Log 'App no responde, abriendo de todas formas' }

# --- Chrome ---
Log 'Abriendo aplicacion...'
Start-Process -FilePath $chromeExe -ArgumentList '--app=http://gestion-salud.test --start-maximized'
Log '=== Gesti?n Salud iniciado correctamente ==='
