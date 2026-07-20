' Gestión Salud - Launcher Silencioso
' Abre la aplicación como si fuera un programa de escritorio.

Dim shell, fso, rootPath, appUrl, chromePath, edgePath, logFile
Set shell = CreateObject("WScript.Shell")
Set fso = CreateObject("Scripting.FileSystemObject")

rootPath = "C:\laragon\www\gestion-salud"
appUrl = "http://localhost:8000"
logFile = rootPath & "\launcher\launcher.log"
chromePath = "C:\Program Files\Google\Chrome\Application\chrome.exe"
edgePath = "C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"

' Helper: log message
Sub Log(msg)
    On Error Resume Next
    Dim ts, nowStr
    nowStr = Now()
    Set ts = fso.OpenTextFile(logFile, 8, True)
    ts.WriteLine "[" & nowStr & "] " & msg
    ts.Close
End Sub

Log "=== INICIO ==="

' Helper: run a command hidden
Sub RunHidden(cmd)
    shell.Run "cmd.exe /c " & cmd, 0, False
End Sub

' 0. Kill old processes
Log "Matando procesos anteriores..."
RunHidden "taskkill /f /im php.exe >nul 2>&1"
RunHidden "taskkill /f /im node.exe >nul 2>&1"
WScript.Sleep 2000

' 1. Start Laragon if not running (for MySQL)
Dim isRunning
isRunning = False
On Error Resume Next
Dim proc
Set proc = GetObject("winmgmts:root\cimv2").ExecQuery("SELECT * FROM Win32_Process WHERE Name='laragon.exe'")
If proc.Count > 0 Then isRunning = True
On Error GoTo 0

If Not isRunning Then
    Log "Iniciando Laragon..."
    shell.Run chr(34) & "C:\laragon\laragon.exe" & chr(34), 4, False
    WScript.Sleep 12000
Else
    Log "Laragon ya está en ejecución."
    WScript.Sleep 2000
End If

' 2. Start npm run dev (Vite)
Log "Iniciando npm run dev..."
RunHidden "cd /d " & chr(34) & rootPath & chr(34) & " && npm run dev"
WScript.Sleep 10000

' 3. Start PHP artisan serve
Log "Iniciando php artisan serve..."
RunHidden "cd /d " & chr(34) & rootPath & chr(34) & " && php artisan serve --host=0.0.0.0 --port=8000"
WScript.Sleep 5000

' 4. Find browser (Chrome preferred, Edge fallback)
If Not fso.FileExists(chromePath) Then
    chromePath = edgePath
End If

' 5. Open in app mode
Log "Abriendo " & appUrl
Dim cmd
cmd = chr(34) & chromePath & chr(34) & " --app=" & appUrl & " --no-first-run --disable-sync"
shell.Run cmd, 1, False

Log "=== FIN ==="
