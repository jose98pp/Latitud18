<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ImageStorageService;
use App\Models\Category;

class TestImageStorage extends Command
{
    protected $signature = 'test:image-storage';
    protected $description = 'Probar el servicio de almacenamiento de imágenes';

    public function handle()
    {
        $this->info('🧪 Probando servicio de almacenamiento de imágenes...');
        
        // Verificar categorías
        $categories = Category::all();
        $this->info('📂 Categorías disponibles:');
        foreach ($categories as $category) {
            $this->line("  - ID: {$category->id}, Nombre: {$category->name}");
        }
        
        // Verificar servicio
        try {
            $imageStorageService = app(ImageStorageService::class);
            $this->info('✅ Servicio ImageStorageService cargado correctamente');
            
            // Probar método de obtener imágenes por categoría
            if ($categories->count() > 0) {
                $firstCategory = $categories->first();
                $images = $imageStorageService->getImagesByCategory($firstCategory->id);
                $this->info("📸 Imágenes en categoría '{$firstCategory->name}': " . count($images));
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Error al cargar servicio: ' . $e->getMessage());
        }
        
        // Verificar estructura de directorios
        $this->info('📁 Verificando estructura de directorios...');
        $baseDir = storage_path('app/public/noticias');
        
        if (is_dir($baseDir)) {
            $this->info("✅ Directorio base existe: {$baseDir}");
            
            // Listar subdirectorios
            $subdirs = array_filter(glob($baseDir . '/*'), 'is_dir');
            $this->info('📂 Subdirectorios encontrados:');
            foreach ($subdirs as $dir) {
                $dirName = basename($dir);
                $fileCount = count(glob($dir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE));
                $this->line("  - {$dirName}: {$fileCount} imágenes");
            }
        } else {
            $this->error("❌ Directorio base no existe: {$baseDir}");
        }
    }
}