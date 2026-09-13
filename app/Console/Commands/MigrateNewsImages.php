<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ImageStorageService;

class MigrateNewsImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:migrate
                            {--dry-run : Ejecutar en modo de prueba sin realizar cambios}
                            {--force : Forzar migración sin confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrar imágenes de noticias a la nueva estructura organizada por categorías';

    protected ImageStorageService $imageStorageService;

    public function __construct(ImageStorageService $imageStorageService)
    {
        parent::__construct();
        $this->imageStorageService = $imageStorageService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('===========================================');
        $this->info('  Migración de Imágenes de Noticias');
        $this->info('===========================================');
        $this->newLine();

        // Modo dry-run
        if ($this->option('dry-run')) {
            $this->warn('⚠️  MODO DE PRUEBA - No se realizarán cambios');
            $this->newLine();
        }

        // Confirmación
        if (!$this->option('force') && !$this->option('dry-run')) {
            if (!$this->confirm('¿Deseas continuar con la migración? Se crearán backups automáticamente.')) {
                $this->warn('Migración cancelada.');
                return 0;
            }
        }

        $this->info('Iniciando migración (procesando por lotes, sin escanear todo el disco)...');
        $this->newLine();

        // Ejecutar migración usando ImageStorageService con soporte dry-run
        $results = $this->imageStorageService->migrateExistingImages($this->option('dry-run'));

        // Mostrar resultados
        $this->displayResults($results);

        return 0;
    }

    private function buildFileMap(): array
    {
        $map = [];
        $this->imageStorageService->streamNewsFiles(function (string $relativePath) use (&$map): void {
            $map[basename($relativePath)] = $relativePath;
        });

        return $map;
    }

    private function runMigration(array $fileMap): array
    {
        $results = [
            'migrated' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        $noticias = \App\Models\Noticia::whereNotNull('imagen')
            ->where('imagen', '!=', '')
            ->with('category')
            ->get();

        $bar = $this->output->createProgressBar(count($noticias));
        $bar->start();

        foreach ($noticias as $noticia) {
            try {
                if (!$noticia->category) {
                    $results['skipped']++;
                    $bar->advance();
                    continue;
                }

                // Verificar si la imagen ya está en la estructura correcta
                $categorySlug = \Illuminate\Support\Str::slug($noticia->category->name);
                // Check if path ends with category/filename
                if (\Illuminate\Support\Str::contains($noticia->imagen, "/{$categorySlug}/" . basename($noticia->imagen))) {
                    $results['skipped']++;
                    $bar->advance();
                    continue;
                }

                // Encontrar la ruta real del archivo
                $currentPath = $noticia->imagen;
                if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($currentPath)) {
                    $basename = basename($currentPath);
                    if (isset($fileMap[$basename])) {
                        $currentPath = $fileMap[$basename];
                    } else {
                        $results['failed']++;
                        $results['errors'][] = "Imagen no encontrada para noticia ID {$noticia->id}: {$noticia->imagen}";
                        $bar->advance();
                        continue;
                    }
                }

                // Crear backup antes de mover
                $this->imageStorageService->createImageBackup($currentPath);

                // Mover imagen a nueva estructura
                $newPath = $this->imageStorageService->moveImageToCategory($currentPath, $noticia->category_id);
                
                if ($newPath) {
                    // Actualizar la base de datos
                    $noticia->update(['imagen' => $newPath]);
                    $results['migrated']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = "Error al mover imagen de noticia ID: {$noticia->id}";
                }

            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Error en noticia ID {$noticia->id}: " . $e->getMessage();
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        return $results;
    }

    /**
     * Ejecutar migración en modo de prueba
     */
    private function dryRunMigration(array $fileMap): array
    {
        $results = [
            'migrated' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        $noticias = \App\Models\Noticia::whereNotNull('imagen')
            ->where('imagen', '!=', '')
            ->with('category')
            ->get();

        foreach ($noticias as $noticia) {
            if (!$noticia->category) {
                $results['skipped']++;
                continue;
            }

            $categorySlug = \Illuminate\Support\Str::slug($noticia->category->name);
            if (\Illuminate\Support\Str::contains($noticia->imagen, "/{$categorySlug}/" . basename($noticia->imagen))) {
                $results['skipped']++;
                continue;
            }

            // Check if file exists
            $currentPath = $noticia->imagen;
            $found = \Illuminate\Support\Facades\Storage::disk('public')->exists($currentPath);
            
            if (!$found) {
                $basename = basename($currentPath);
                if (isset($fileMap[$basename])) {
                    $found = true;
                }
            }

            if ($found) {
                $results['migrated']++;
            } else {
                $results['failed']++;
                $results['errors'][] = "Imagen perdida: {$noticia->imagen}";
            }
        }

        return $results;
    }

    /**
     * Mostrar resultados de la migración
     */
    private function displayResults(array $results): void
    {
        $this->newLine();
        $this->info('===========================================');
        $this->info('  Resultados de la Migración');
        $this->info('===========================================');
        $this->newLine();

        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['✅ Imágenes migradas', $results['migrated']],
                ['⏭️  Imágenes omitidas', $results['skipped']],
                ['❌ Errores', $results['failed']],
            ]
        );

        if (!empty($results['errors'])) {
            $this->newLine();
            $this->error('Errores encontrados:');
            foreach ($results['errors'] as $error) {
                $this->line('  • ' . $error);
            }
        }

        $this->newLine();
        
        if ($results['migrated'] > 0) {
            $this->info('✨ Migración completada exitosamente!');
        } elseif ($results['skipped'] > 0 && $results['failed'] === 0) {
            $this->info('ℹ️  No se encontraron imágenes para migrar.');
        } else {
            $this->warn('⚠️  La migración finalizó con errores.');
        }
    }
}
