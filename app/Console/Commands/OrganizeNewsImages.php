<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Noticia;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrganizeNewsImages extends Command
{
    protected $signature = 'images:organize';
    protected $description = 'Organiza las imágenes de noticias por categorías, elimina duplicados y actualiza la base de datos';

    private $imageHashes = [];
    private $duplicatesFound = 0;
    private $imagesMoved = 0;
    private $dbUpdated = 0;
    private $errors = [];

    // Mapeo de directorios a categorías de la base de datos
    private $categoryMapping = [
        'politica' => 'Política',
        'deportes' => 'Deportes',
        'economia' => 'Economía',
        'cultura' => 'Cultura',
        'espectaculo' => 'Entretenimiento',
        'mundo' => 'Internacional',
        'nacional' => 'Sociedad',
        'negocios' => 'Economía',
        'sociedad' => 'Sociedad',
        'noticias' => 'Sociedad', // Categoría por defecto
    ];

    public function handle()
    {
        $this->info('🔍 Iniciando organización de imágenes de noticias...');
        
        // Paso 1: Escanear imágenes y detectar duplicados
        $this->info('\n📂 Escaneando imágenes en storage/app/public/noticias...');
        $this->scanNewsImages();
        
        // Paso 2: Escanear backups para encontrar más duplicados
        $this->info('\n📂 Escaneando imágenes en storage/app/public/backups/images...');
        $this->scanBackupImages();
        
        // Paso 3: Organizar imágenes por categoría
        $this->info('\n📁 Organizando imágenes por categorías...');
        $this->organizeImagesByCategory();
        
        // Paso 4: Actualizar base de datos
        $this->info('\n💾 Actualizando rutas en la base de datos...');
        $this->updateDatabasePaths();
        
        // Paso 5: Eliminar duplicados
        $this->info('\n🗑️  Eliminando imágenes duplicadas...');
        $this->removeDuplicates();
        
        // Resumen
        $this->displaySummary();
        
        return Command::SUCCESS;
    }

    private function scanNewsImages()
    {
        $newsPath = storage_path('app/public/noticias');
        
        if (!is_dir($newsPath)) {
            $this->warn("⚠️  Directorio noticias no encontrado: $newsPath");
            return;
        }

        $this->scanDirectory($newsPath, 'noticias');
    }

    private function scanBackupImages()
    {
        $backupPath = storage_path('app/public/backups/images');
        
        if (!is_dir($backupPath)) {
            $this->warn("⚠️  Directorio backups no encontrado: $backupPath");
            return;
        }

        $this->scanDirectory($backupPath, 'backups');
    }

    private function scanDirectory($directory, $source)
    {
        // Usar DirectoryIterator en lugar de RecursiveDirectoryIterator para mejor gestión de memoria
        $this->scanDirectoryRecursive($directory, $source);
    }

    private function scanDirectoryRecursive($directory, $source)
    {
        $items = scandir($directory);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            
            $path = $directory . DIRECTORY_SEPARATOR . $item;
            
            if (is_dir($path)) {
                $this->scanDirectoryRecursive($path, $source);
            } elseif (is_file($path) && $this->isImageFileByPath($path)) {
                $this->processImageFileByPath($path, $source);
            }
        }
    }

    private function isImageFileByPath($filePath)
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    private function processImageFileByPath($filePath, $source)
    {
        $hash = md5_file($filePath);
        
        if (!isset($this->imageHashes[$hash])) {
            $this->imageHashes[$hash] = [
                'original' => $filePath,
                'duplicates' => [],
                'source' => $source,
                'category' => $this->detectCategoryFromPath($filePath)
            ];
        } else {
            $this->imageHashes[$hash]['duplicates'][] = $filePath;
            $this->duplicatesFound++;
            $this->line("   🔁 Duplicado encontrado: " . basename($filePath));
        }
    }

    private function isImageFile($file)
    {
        $extension = strtolower($file->getExtension());
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    private function processImageFile($file, $source)
    {
        $filePath = $file->getPathname();
        $hash = md5_file($filePath);
        
        if (!isset($this->imageHashes[$hash])) {
            $this->imageHashes[$hash] = [
                'original' => $filePath,
                'duplicates' => [],
                'source' => $source,
                'category' => $this->detectCategoryFromPath($filePath)
            ];
        } else {
            $this->imageHashes[$hash]['duplicates'][] = $filePath;
            $this->duplicatesFound++;
            $this->line("   🔁 Duplicado encontrado: " . basename($filePath));
        }
    }

    private function detectCategoryFromPath($filePath)
    {
        // Normalizar ruta para detectar categoría
        $normalizedPath = str_replace('\\', '/', $filePath);
        
        foreach ($this->categoryMapping as $dir => $category) {
            if (strpos($normalizedPath, '/noticias/' . $dir . '/') !== false) {
                return $category;
            }
        }
        
        return 'Sociedad'; // Categoría por defecto
    }

    private function organizeImagesByCategory()
    {
        $baseNewsPath = storage_path('app/public/noticias');
        
        // Crear estructura de directorios por categoría
        foreach ($this->categoryMapping as $dir => $category) {
            $categoryPath = $baseNewsPath . '/' . $dir;
            if (!is_dir($categoryPath)) {
                mkdir($categoryPath, 0755, true);
            }
        }

        foreach ($this->imageHashes as $hash => $imageInfo) {
            if (empty($imageInfo['duplicates'])) {
                // Solo mover si no es duplicado y está en la ubicación correcta
                $this->moveImageToCategory($imageInfo['original'], $imageInfo['category']);
            }
        }
    }

    private function moveImageToCategory($sourcePath, $category)
    {
        $categoryDir = array_search($category, $this->categoryMapping);
        if ($categoryDir === false) {
            $categoryDir = 'noticias';
        }

        $destDir = storage_path('app/public/noticias/' . $categoryDir);
        $fileName = basename($sourcePath);
        $destPath = $destDir . '/' . $fileName;

        // Verificar si ya está en el lugar correcto
        $normalizedSource = str_replace('\\', '/', $sourcePath);
        $normalizedDest = str_replace('\\', '/', $destPath);
        
        if ($normalizedSource === $normalizedDest) {
            return; // Ya está en el lugar correcto
        }

        // Crear directorio si no existe
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        // Mover archivo
        if (rename($sourcePath, $destPath)) {
            $this->imagesMoved++;
            $this->line("   ✅ Movido: $fileName -> $categoryDir/");
            
            // Actualizar la ruta en el array de hashes
            foreach ($this->imageHashes as $hash => &$info) {
                if ($info['original'] === $sourcePath) {
                    $info['original'] = $destPath;
                }
                foreach ($info['duplicates'] as $key => $dup) {
                    if ($dup === $sourcePath) {
                        $info['duplicates'][$key] = $destPath;
                    }
                }
            }
        } else {
            $this->errors[] = "Error moviendo $fileName: " . error_get_last()['message'];
        }
    }

    private function updateDatabasePaths()
    {
        // Procesar por lotes para evitar problemas de memoria
        Noticia::whereNotNull('imagen')->where('imagen', '!=', '')
            ->chunk(100, function ($noticias) {
                foreach ($noticias as $noticia) {
                    $currentPath = $noticia->imagen;
                    
                    // Normalizar ruta
                    $normalizedPath = str_replace('\\', '/', $currentPath);
                    
                    // Extraer nombre del archivo
                    $fileName = basename($normalizedPath);
                    
                    // Buscar el archivo en la nueva estructura
                    $newPath = $this->findImageInNewStructure($fileName, $noticia->category_id);
                    
                    if ($newPath && $newPath !== $currentPath) {
                        $noticia->imagen = $newPath;
                        $noticia->save();
                        $this->dbUpdated++;
                        $this->line("   📝 Actualizado: $fileName en noticia ID {$noticia->id}");
                    }
                }
            });
    }

    private function findImageInNewStructure($fileName, $categoryId)
    {
        $category = Category::find($categoryId);
        if (!$category) {
            return null;
        }

        $categoryDir = array_search($category->name, $this->categoryMapping);
        if ($categoryDir === false) {
            $categoryDir = 'noticias';
        }

        $newPath = 'noticias/' . $categoryDir . '/' . $fileName;
        $fullPath = storage_path('app/public/' . $newPath);
        
        if (file_exists($fullPath)) {
            return $newPath;
        }
        
        return null;
    }

    private function removeDuplicates()
    {
        foreach ($this->imageHashes as $hash => $imageInfo) {
            if (!empty($imageInfo['duplicates'])) {
                foreach ($imageInfo['duplicates'] as $duplicate) {
                    if (file_exists($duplicate)) {
                        if (unlink($duplicate)) {
                            $this->line("   🗑️  Eliminado duplicado: " . basename($duplicate));
                        } else {
                            $this->errors[] = "Error eliminando " . basename($duplicate);
                        }
                    }
                }
            }
        }
    }

    private function displaySummary()
    {
        $this->newLine();
        $this->info('📊 RESUMEN DE LA OPERACIÓN:');
        $this->line('================================');
        $this->line("✅ Imágenes movidas: {$this->imagesMoved}");
        $this->line("🔁 Duplicados encontrados: {$this->duplicatesFound}");
        $this->line("📝 Rutas actualizadas en BD: {$this->dbUpdated}");
        
        if (!empty($this->errors)) {
            $this->newLine();
            $this->error('❌ ERRORES:');
            foreach ($this->errors as $error) {
                $this->line("   - $error");
            }
        }
        
        $this->newLine();
        $this->info('✨ Organización completada.');
    }
}
