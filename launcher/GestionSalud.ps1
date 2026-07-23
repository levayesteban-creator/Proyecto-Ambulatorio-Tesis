# Tepuy - Launcher PowerShell
$rootPath = "C:\laragon\www\gestion-salud"
$appUrl = "http://localhost:8000"

Write-Host "=== Tepuy - El Chaparro de Guanta ===" -ForegroundColor Green
Write-Host "Sistema digital de salud" -ForegroundColor Yellow
Write-Host ""

# Kill old processes
Write-Host "[1/5] Limpiando procesos anteriores..." -ForegroundColor Yellow
Get-Process php, node -ErrorAction SilentlyContinue | Stop-Process -Force
Start-Sleep -Seconds 2

# Start Laragon if not running (for MySQL)
Write-Host "[2/5] Verificando Laragon..." -ForegroundColor Yellow
$laragon = Get-Process laragon -ErrorAction SilentlyContinue
if (-not $laragon) {
    Write-Host "  -> Iniciando Laragon..."
    Start-Process "C:\laragon\laragon.exe"
    Start-Sleep -Seconds 10
} else {
    Write-Host "  -> Laragon ya está en ejecución."
    Start-Sleep -Seconds 2
}

# Start npm run dev (Vite)
Write-Host "[3/5] Iniciando Vite (npm run dev)..." -ForegroundColor Yellow
$viteJob = Start-Job -ScriptBlock { param($p) Set-Location $p; npm run dev } -ArgumentList $rootPath
Start-Sleep -Seconds 10

# Start PHP artisan serve
Write-Host "[4/5] Iniciando servidor PHP..." -ForegroundColor Yellow
$phpJob = Start-Job -ScriptBlock { param($p) Set-Location $p; php artisan serve --host=0.0.0.0 --port=8000 } -ArgumentList $rootPath
Start-Sleep -Seconds 5

# Verify server is running
Write-Host "[5/5] Verificando servidor..." -ForegroundColor Yellow
$maxRetries = 10
$retry = 0
do {
    try {
        $req = [System.Net.WebRequest]::CreateHttp($appUrl)
        $req.Timeout = 2000
        $resp = $req.GetResponse()
        $resp.Close()
        Write-Host "  -> Servidor respondiendo OK!" -ForegroundColor Green
        break
    } catch {
        $retry++
        if ($retry -ge $maxRetries) {
            Write-Host "  -> ADVERTENCIA: No se pudo verificar el servidor, abriendo de todas formas..." -ForegroundColor Yellow
        } else {
            Write-Host "  -> Esperando servidor... ($retry/$maxRetries)" -ForegroundColor Gray
            Start-Sleep -Seconds 2
        }
    }
} while ($retry -lt $maxRetries)

# Open browser
Write-Host "" 
Write-Host "Abriendo $appUrl ..." -ForegroundColor Green
Start-Process $appUrl

Write-Host ""
Write-Host "Presione cualquier tecla para cerrar..."
$host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown") | Out-Null

# Cleanup jobs on exit
$viteJob, $phpJob | Remove-Job -Force -ErrorAction SilentlyContinue
