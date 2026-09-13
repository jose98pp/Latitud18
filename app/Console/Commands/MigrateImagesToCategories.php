<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ImageStorageService;
use Illuminate\Support\Facades\Storage;

class MigrateImagesToCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:migrate-to-categories 
                            {--dry-run : Solo mostrar qué se haría sin ejecutar cambios}
                            {--backup : Crear backup antes de migrar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrar imágenes existentes a estructura organizada por categorías';

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
        $this->info('🚀 Iniciando migración de imágenes a estructura por categorías...');
        
        $dryRun = $this->option('dry-run');
        $createBackup = $this->option('backup');

        if ($dryRun) {
            $this->warn('⚠️  MODO DRY-RUN: No se realizarán cambios reales');
        }

        // Mostrar estadísticas actuales
        $this->showCurrentStats();

        if (!$dryRun && $this->confirm('¿Desea continuar con la migración?')) {
            
            if ($createBackup) {
                $this->info('📦 Creando backup completo...');
                $this->createFullBackup();
            }

            $this->info('🔄 Ejecutando migración...');
            $results = $this->imageStorageService->migrateExistingImages();
            
            $this->displayResults($results);
            
        } elseif ($dryRun) {
            $this->simulateMigration();
        } else {
            $this->info('❌ Migración cancelada por el usuario');
        }
    }

    /**
     * Mostrar estadísticas actuales
     */
    private function showCurrentStats(): void
    {
        $this->info('📊 Estadísticas actuales:');
        
        $noticias = \App\Models\Noticia::whereNotNull('imagen')
            ->where('imagen', '!=', '')
            ->with('category')
            ->get();

        $totalImages = $noticias->count();
        $imagesWithCategory = $noticias->whereNotNull('category')->count();
        $imagesWithoutCategory = $totalImages - $imagesWithCategory;

        $this->table([
            'Métrica', 'Cantidad'
        ], [
            ['Total de imágenes', $totalImages],
            ['Con categoría asignada', $imagesWithCategory],
            ['Sin categoría asignada', $imagesWithoutCategory],
        ]);

        // Mostrar distribución por categorías
        $categoryStats = $noticias->groupBy('category.name')->map(function ($items, $categoryName) {
            return [
                'category' => $categoryName ?: 'Sin categoría',
                'count' => $items->count()
            ];
        })->values()->toArray();

        if (!empty($categoryStats)) {
            $this->info('📈 Distribución por categorías:');
            $this->table(['Categoría', 'Cantidad de imágenes'], $categoryStats);
        }
    }

    /**
     * Simular migración (dry-run)
     */
    private function simulateMigration(): void
    {
        $this->info('🔍 Simulando migración...');
        
        $noticias = \App\Models\Noticia::whereNotNull('imagen')
            ->where('imagen', '!=', '')
            ->with('category')
            ->get();

        $actions = [];
        
        foreach ($noticias as $noticia) {
            if (!$noticia->category) {
                $actions[] = [
                    'id' => $noticia->id,
                    'current_path' => $noticia->imagen,
                    'action' => 'SKIP - Sin categoría',
                    'new_path' => 'N/A'
                ];
                continue;
            }

            $categorySlug = \Illuminate\Support\Str::slug($noticia->category->name);
            
            if (\Illuminate\Support\Str::contains($noticia->imagen, $categorySlug)) {
                $actions[] = [
                    'id' => $noticia->id,
                    'current_path' => $noticia->imagen,
                    'action' => 'SKIP - Ya organizada',
                    'new_path' => 'N/A'
                ];
                continue;
            }

            $yearMonth = \Carbon\Carbon::parse($noticia->created_at)->format('Y/m');
            $fileName = basename($noticia->imagen);
            $newPath = "noticias/{$categorySlug}/{$yearMonth}/{$fileName}";

            $actions[] = [
                'id' => $noticia->id,
                'current_path' => $noticia->imagen,
                'action' => 'MOVE',
                'new_path' => $newPath
            ];
        }

        // Mostrar primeras 10 acciones como ejemplo
        $this->info('📋 Primeras 10 acciones a realizar:');
        $this->table([
            'ID', 'Ruta Actual', 'Acción', 'Nueva Ruta'
        ], array_slice($actions, 0, 10));

        $moveCount = collect($actions)->where('action', 'MOVE')->count();
        $skipCount = collect($actions)->where('action', '!=', 'MOVE')->count();

        $this->info("✅ Se moverían: {$moveCount} imágenes");
        $this->info("⏭️  Se omitirían: {$skipCount} imágenes");
    }

    /**
     * Crear backup completo
     */
    private function createFullBackup(): void
    {
        try {
            $backupPath = 'backups/full_migration_' . now()->format('Y_m_d_H_i_s');
            
            $files = [];
            $this->imageStorageService->streamNewsFiles(function (string $relativePath) use (&$files): void {
                $files[] = $relativePath;
            });

            $this->output->progressStart(count($files));

            foreach ($files as $file) {
                $backupFilePath = $backupPath . '/' . $file;
                Storage::disk('public')->copy($file, $backupFilePath);
                $this->output->progressAdvance();
            }
            
            $this->output->progressFinish();
            $this->info("✅ Backup creado en: storage/app/public/{$backupPath}");
            
        } catch (\Exception $e) {
            $this->error("❌ Error al crear backup: " . $e->getMessage());
            if (!$this->confirm('¿Desea continuar sin backup?')) {
                $this->info('❌ Migración cancelada');
                return;
            }
        }
    }

    /**
     * Mostrar resultados de la migración
     */
    private function displayResults(array $results): void
    {
        $this->info('📊 Resultados de la migración:');
        
        $this->table([
            'Resultado', 'Cantidad'
        ], [
            ['✅ Migradas exitosamente', $results['migrated']],
            ['❌ Fallidas', $results['failed']],
            ['⏭️  Omitidas', $results['skipped']],
        ]);

        if (!empty($results['errors'])) {
            $this->error('❌ Errores encontrados:');
            foreach ($results['errors'] as $error) {
                $this->line("  • {$error}");
            }
        }

        if ($results['migrated'] > 0) {
            $this->info('🎉 Migración completada exitosamente!');
            $this->info('💡 Recuerde ejecutar el comando de limpieza si es necesario');
        }
    }
}