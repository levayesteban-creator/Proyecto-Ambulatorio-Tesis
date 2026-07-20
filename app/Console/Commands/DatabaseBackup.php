<?php

namespace App\Console\Commands;

use App\Mail\BackupMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class DatabaseBackup extends Command
{
    protected $signature = 'db:backup {--cloud : Envía el respaldo por correo electrónico}';
    protected $description = 'Realiza respaldo de la base de datos MySQL';

    public function handle(): int
    {
        $db = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');

        $date = now()->format('Y-m_d-H-i-s');
        $filename = "backup_{$db}_{$date}.sql";
        $localPath = storage_path("app/backups/{$filename}");

        if (!is_dir(dirname($localPath))) {
            mkdir(dirname($localPath), 0755, true);
        }

        // Detect mysqldump in common Laragon path on Windows
        $mysqldump = 'mysqldump';
        $laragonBin = 'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe';
        if (PHP_OS_FAMILY === 'Windows' && file_exists($laragonBin)) {
            $mysqldump = $laragonBin;
        }

        $cmd = "\"{$mysqldump}\" --host={$host} --port={$port} --user={$user}"
            . ($pass ? " --password=\"{$pass}\"" : '')
            . " --routines --single-transaction \"{$db}\" 2>&1";

        $output = [];
        $exitCode = 0;
        exec($cmd . ' > "' . $localPath . '"', $output, $exitCode);

        if ($exitCode !== 0) {
            $this->error("Error al generar el respaldo: " . implode("\n", $output));
            return self::FAILURE;
        }

        $size = filesize($localPath);
        $this->info("Respaldo creado: {$filename} (" . number_format($size / 1024, 1) . " KB)");

        if ($this->option('cloud')) {
            $this->info("Enviando por correo a consultoriochaparrodeguanta@gmail.com...");

            $recipient = env('BACKUP_EMAIL', 'consultoriochaparrodeguanta@gmail.com');

            Mail::to($recipient)->send(new BackupMail($localPath, $filename, $size));

            $this->info("Respaldo enviado por correo exitosamente.");
        }

        return self::SUCCESS;
    }
}
