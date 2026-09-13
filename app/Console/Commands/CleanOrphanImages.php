<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Noticia;
use App\Services\ImageValidationService;

class CleanOrphanImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:clean-orphans 
                            {--dry-run : Solo mostrar qué se eliminaría sin ejecutar}
                            {--move-to-archive : Mover a archivo en lugar de eliminar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpiar imágenes huérfanas que no están asociadas a ninguna noticia';

    protected ImageValidationService $imageValidationService;
    protected \App\Services\ImageStorageService $imageStorageService;

    public function __construct(ImageValidationService $imageValidationService)
    {
        parent::__construct();
        $this->imageValidationService = $imageValidationService;
        $this->imageStorageService = app(\App\Services\ImageStorageService::class);
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Iniciando limpieza de imágenes huérfanas...');
        
        $dryRun = $this->option('dry-run');
        $moveToArchive = $this->option('move-to-archive');

        if ($dryRun) {
            $this->warn('⚠️  MODO DRY-RUN: No se realizarán cambios reales');
        }

        // Obtener todas las imágenes en el directorio de noticias
        $allImages = $this->getAllNewsImages();
        $this->info("📁 Total de imágenes encontradas: " . count($allImages));

        // Obtener imágenes referenciadas en la base de datos
        $referencedImages = $this->getReferencedImages();
        $this->info("🔗 Imágenes referenciadas en BD: " . count($referencedImages));

        // Encontrar imágenes huérfanas
        $orphanImages = array_diff($allImages, $referencedImages);
        $this->info("🗑️  Imágenes huérfanas encontradas: " . count($orphanImages));

        if (empty($orphanImages)) {
            $this->info('✅ No se encontraron imágenes huérfanas');
            return;
        }

        // Mostrar algunas imágenes huérfanas como ejemplo
        $this->showOrphanSamples($orphanImages);

        if (!$dryRun) {
            if ($this->confirm('¿Desea proceder con la limpieza?')) {
                $this->processOrphanImages($orphanImages, $moveToArchive);
            } else {
                $this->info('❌ Limpieza cancelada por el usuario');
            }
        }
    }

    /**
     * Obtener todas las imágenes en el directorio de noticias
     */
    private function getAllNewsImages(): array
    {
        try {
            $files = [];
            $this->imageStorageService->streamNewsFiles(function (string $relativePath) use (&$files): void {
                $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));
                if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                    $files[] = $relativePath;
                }
            });

            return $files;
        } catch (\Exception $e) {
            $this->error("❌ Error al obtener imágenes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener imágenes referenciadas en la base de datos
     */
    private function getReferencedImages(): array
    {
        try {
            return Noticia::whereNotNull('imagen')
                ->where('imagen', '!=', '')
                ->pluck('imagen')
                ->filter()
                ->unique()
                ->values()
                ->toArray();
                
        } catch (\Exception $e) {
            $this->error("❌ Error al obtener referencias de BD: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Mostrar muestras de imágenes huérfanas
     */
    private function showOrphanSamples(array $orphanImages): void
    {
        $samples = array_slice($orphanImages, 0, 10);
        
        $this->info('📋 Primeras 10 imágenes huérfanas:');
        
        $tableData = [];
        foreach ($samples as $image) {
            $fullPath = storage_path('app/public/' . $image);
            $size = file_exists($fullPath) ? $this->formatBytes(filesize($fullPath)) : 'N/A';
            $modified = file_exists($fullPath) ? date('Y-m-d H:i:s', filemtime($fullPath)) : 'N/A';
            
            $tableData[] = [
                'path' => $image,
                'size' => $size,
                'modified' => $modified
            ];
        }
        
        $this->table(['Ruta', 'Tamaño', 'Modificado'], $tableData);
        
        if (count($orphanImages) > 10) {
            $remaining = count($orphanImages) - 10;
            $this->info("... y {$remaining} imágenes más");
        }
    }

    /**
     * Procesar imágenes huérfanas
     */
    private function processOrphanImages(array $orphanImages, bool $moveToArchive): void
    {
        $processed = 0;
        $failed = 0;
        $totalSize = 0;

        $this->output->progressStart(count($orphanImages));

        foreach ($orphanImages as $image) {
            try {
                $fullPath = storage_path('app/public/' . $image);
                
                if (file_exists($fullPath)) {
                    $totalSize += filesize($fullPath);
                }

                if ($moveToArchive) {
                    $success = $this->moveToArchive($image);
                } else {
                    $success = Storage::disk('public')->delete($image);
                }

                if ($success) {
                    $processed++;
                } else {
                    $failed++;
                }

            } catch (\Exception $e) {
                $failed++;
                $this->error("Error procesando {$image}: " . $e->getMessage());
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        // Mostrar resultados
        $this->info('📊 Resultados de la limpieza:');
        $this->table([
            'Resultado', 'Cantidad'
        ], [
            ['✅ Procesadas exitosamente', $processed],
            ['❌ Fallidas', $failed],
            ['💾 Espacio liberado', $this->formatBytes($totalSize)],
        ]);

        if ($processed > 0) {
            $action = $moveToArchive ? 'archivadas' : 'eliminadas';
            $this->info("🎉 {$processed} imágenes {$action} exitosamente!");
        }
    }

    /**
     * Mover imagen a archivo
     */
    private function moveToArchive(string $imagePath): bool
    {
        try {
            $archivePath = 'archive/orphan_images/' . now()->format('Y/m/d') . '/' . basename($imagePath);
            return Storage::disk('public')->move($imagePath, $archivePath);
            
        } catch (\Exception $e) {
            $this->error("Error archivando {$imagePath}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Formatear bytes a formato legible
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}