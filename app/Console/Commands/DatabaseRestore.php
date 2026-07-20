<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DatabaseRestore extends Command
{
    protected $signature = 'db:restore {file? : Ruta del archivo .sql a restaurar}';
    protected $description = 'Restaura la base de datos desde un respaldo';

    public function handle(): int
    {
        $file = $this->argument('file');

        if (!$file) {
            $backups = glob(storage_path('app/backups/*.sql'));
            if (empty($backups)) {
                $this->error('No hay respaldos en storage/app/backups/');
                return self::FAILURE;
            }
            rsort($backups);
            $this->info('Respaldos disponibles:');
            foreach ($backups as $i => $b) {
                $size = filesize($b);
                $this->line("  [{$i}] " . basename($b) . " (" . number_format($size / 1024, 1) . " KB)");
            }
            $idx = $this->ask('Selecciona el número del respaldo a restaurar');
            if (!isset($backups[(int)$idx])) {
                $this->error('Selección inválida');
                return self::FAILURE;
            }
            $file = $backups[(int)$idx];
        }

        if (!file_exists($file)) {
            $this->error("Archivo no encontrado: {$file}");
            return self::FAILURE;
        }

        $db = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');

        // Detect mysql in common Laragon path on Windows
        $mysql = 'mysql';
        $laragonBin = 'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe';
        if (PHP_OS_FAMILY === 'Windows' && file_exists($laragonBin)) {
            $mysql = $laragonBin;
        }

        if (!$this->confirm("¿Restaurar DB '{$db}' con el archivo " . basename($file) . "? Se borrarán los datos actuales.")) {
            $this->info('Operación cancelada.');
            return self::SUCCESS;
        }

        $cmd = "\"{$mysql}\" --host={$host} --port={$port} --user={$user}"
            . ($pass ? " --password=\"{$pass}\"" : '')
            . " \"{$db}\" < \"" . realpath($file) . '" 2>&1';

        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0) {
            $this->error("Error al restaurar: " . implode("\n", $output));
            return self::FAILURE;
        }

        $this->info("Base de datos '{$db}' restaurada exitosamente desde " . basename($file));
        return self::SUCCESS;
    }
}
