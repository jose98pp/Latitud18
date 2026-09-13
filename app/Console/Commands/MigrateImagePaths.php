<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Noticia;
use Illuminate\Support\Facades\Storage;

class MigrateImagePaths extends Command
{
    protected $signature = 'migrate:image-paths {--dry-run : Show what would be updated without making changes}';
    protected $description = 'Migrate old image paths to new category-based structure';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('MODO DRY-RUN: Solo mostrando cambios, no se actualizará nada');
        } else {
            $this->info('Migrando rutas de imágenes a la nueva estructura...');
        }
        
        $noticias = Noticia::with('category')
            ->where('imagen', 'LIKE', 'noticias/%')
            ->where('imagen', 'NOT LIKE', 'noticias/%/%/%/%')
            ->get();
        
        $this->info("Encontradas {$noticias->count()} noticias con rutas antiguas");
        
        $updated = 0;
        $notFound = 0;
        
        foreach ($noticias as $noticia) {
            $oldPath = $noticia->imagen;
            $fileName = basename($oldPath);
            
            // Buscar la imagen en las carpetas de categorías
            $newPath = $this->findImageInCategoryFolders($fileName, $noticia->category);
            
            if ($newPath) {
                $this->line("✓ ID {$noticia->id}: {$oldPath} → {$newPath}");
                
                if (!$dryRun) {
                    $noticia->update(['imagen' => $newPath]);
                }
                $updated++;
            } else {
                $this->error("✗ ID {$noticia->id}: No se encontró {$fileName}");
                $notFound++;
            }
        }
        
        $this->info("Resumen:");
        $this->line("- Actualizadas: {$updated}");
        $this->line("- No encontradas: {$notFound}");
        
        if ($dryRun) {
            $this->warn("Para aplicar los cambios, ejecuta: php artisan migrate:image-paths");
        }
        
        return 0;
    }
    
    private function findImageInCategoryFolders($fileName, $category)
    {
        if (!$category) {
            return null;
        }
        
        $categorySlug = strtolower($category->name);
        $categoryMap = [
            'mundo' => 'mundo',
            'economía' => 'economia',
            'economia' => 'economia',
            'sociedad' => 'sociedad',
            'espectáculo' => 'espectaculo',
            'espectaculo' => 'espectaculo',
            'política' => 'politica',
            'politica' => 'politica',
            'deportes' => 'deportes',
            'cultura' => 'cultura',
            'nacional' => 'nacional',
            'negocios' => 'negocios',
        ];
        
        $categoryFolder = $categoryMap[$categorySlug] ?? 'noticias';
        
        // Buscar en la estructura año/mes
        $years = ['2024', '2025'];
        $months = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        
        foreach ($years as $year) {
            foreach ($months as $month) {
                $path = "noticias/{$categoryFolder}/{$year}/{$month}/{$fileName}";
                if (Storage::disk('public')->exists($path)) {
                    return $path;
                }
            }
        }
        
        return null;
    }
}