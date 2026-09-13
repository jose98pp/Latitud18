<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:migrate {--dry-run : Ejecutar en modo de prueba sin realizar cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrar y normalizar rutas de imágenes existentes a la nueva estructura por categoría';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Iniciando migración de imágenes...');

        if ($this->option('dry-run')) {
            $this->warn('Modo dry-run activado: no se moverán archivos ni se actualizará la base de datos.');
        }

        $service = app(\App\Services\ImageStorageService::class);
        $results = $service->migrateExistingImages($this->option('dry-run'));

        $this->info('Resultado de la migración:');
        $this->line('Migradas: ' . ($results['migrated'] ?? 0));
        $this->line('Fallidas: ' . ($results['failed'] ?? 0));
        $this->line('Omitidas: ' . ($results['skipped'] ?? 0));

        if (!empty($results['errors'])) {
            $this->error('Se encontraron errores durante la migración:');
            foreach ($results['errors'] as $err) {
                $this->line('- ' . $err);
            }
        }

        $this->info('Migración finalizada.');

        return 0;
    }
}
