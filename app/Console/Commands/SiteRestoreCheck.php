<?php

namespace App\Console\Commands;

use App\Services\SiteBackupService;
use Illuminate\Console\Command;

class SiteRestoreCheck extends Command
{
    protected $signature = 'site:restore-check {archive} {--database= : Otra base de datos existente y vacía} {--directory= : Otra carpeta privada y vacía}';
    protected $description = 'Verifica y restaura una copia en una base y carpeta aisladas, sin sobrescribir el sitio.';

    public function handle(SiteBackupService $service): int
    {
        try {
            $result = $service->restoreCheck($this->argument('archive'), getenv('SITE_BACKUP_PASSWORD') ?: config('backup.password'), (string)$this->option('database'), (string)$this->option('directory'));
            $this->info('Restauración de prueba verificada: '.$result['database']);
            $this->line($result['tables'].' tablas y '.$result['files'].' archivos coinciden. Carpeta: '.$result['directory']);
            return self::SUCCESS;
        } catch (\Throwable $e) { $this->error($e->getMessage()); return self::FAILURE; }
    }
}
