Set WshShell = CreateObject("WScript.Shell")
WshShell.Run "powershell.exe -ExecutionPolicy Bypass -File """ & _
    "C:\laragon\www\gestion-salud\Iniciar-GestionSalud.ps1""", 0, False
