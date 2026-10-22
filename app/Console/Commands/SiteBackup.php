<?php

namespace App\Console\Commands;

use App\Services\SiteBackupService;
use Illuminate\Console\Command;

class SiteBackup extends Command
{
    protected $signature = 'site:backup {--destination= : Carpeta privada fuera de public_html y del proyecto}';
    protected $description = 'Respalda y verifica la base de datos, archivos públicos y configuración en un ZIP cifrado.';

    public function handle(SiteBackupService $service): int
    {
        try {
            $result = $service->create($this->option('destination') ?: config('backup.directory'), getenv('SITE_BACKUP_PASSWORD') ?: config('backup.password'));
            $this->info('Respaldo cifrado y verificado: '.$result['archive']);
            $this->line($result['tables'].' tablas; '.$result['files'].' archivos comprobados.');
            return self::SUCCESS;
        } catch (\Throwable $e) { $this->error($e->getMessage()); return self::FAILURE; }
    }
}
