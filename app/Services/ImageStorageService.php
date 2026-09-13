<?php

namespace App\Services;

use App\Models\Category;
use Carbon\Carbon;
use FilesystemIterator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ImageStorageService
{
    /**
     * Directorio base para imágenes de noticias
     */
    private const BASE_DIRECTORY = 'noticias';

    /**
     * Extensiones de imagen permitidas
     */
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * Tamaño máximo de archivo en bytes (5MB)
     */
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;

    protected ImageValidationService $imageValidationService;

    public function __construct(ImageValidationService $imageValidationService)
    {
        $this->imageValidationService = $imageValidationService;
    }

    /**
     * Almacenar imagen organizándola por categoría
     */
    public function storeImageByCategory(UploadedFile $file, int $categoryId): ?string
    {
        try {
            $this->validateImageFile($file);

            $category = Category::find($categoryId);
            if (!$category) {
                throw new \Exception('Categoría no encontrada');
            }

            $categorySlug = Str::slug($category->name);
            $directory = self::BASE_DIRECTORY . '/' . $categorySlug;
            $fileName = $this->generateUniqueFileName($file, $categoryId);
            $storedPath = $file->storeAs($directory, $fileName, 'public');

            if (!$storedPath) {
                throw new \Exception('Error al almacenar el archivo');
            }

            if (!$this->imageValidationService->validateImagePath($storedPath)) {
                Storage::disk('public')->delete($storedPath);
                throw new \Exception('Error en la validación post-almacenamiento');
            }

            Log::info('Imagen almacenada exitosamente', [
                'path' => $storedPath,
                'category' => $category->name,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
            ]);

            return $storedPath;
        } catch (\Exception $e) {
            Log::error('Error al almacenar imagen por categoría', [
                'error' => $e->getMessage(),
                'category_id' => $categoryId,
                'file_name' => $file->getClientOriginalName() ?? 'unknown',
            ]);
            throw $e;
        }
    }

    /**
     * Mover imagen existente a nueva estructura de categorías
     */
    public function moveImageToCategory(string $currentPath, int $categoryId): ?string
    {
        try {
            $resolvedPath = \App\Helpers\ImageUrlHelper::resolveImagePath($currentPath);

            if ($resolvedPath && $resolvedPath !== $currentPath) {
                Log::info('Imagen ya resolvible en la nueva estructura, se reutiliza la ruta encontrada', [
                    'original' => $currentPath,
                    'resolved' => $resolvedPath,
                    'category_id' => $categoryId,
                ]);
                return $resolvedPath;
            }

            if (!$this->imageValidationService->validateImagePath($currentPath)) {
                Log::warning('Imagen no encontrada para mover', ['path' => $currentPath]);
                return null;
            }

            $category = Category::find($categoryId);
            if (!$category) {
                throw new \Exception('Categoría no encontrada');
            }

            $categorySlug = Str::slug($category->name);
            $directory = self::BASE_DIRECTORY . '/' . $categorySlug;
            $fileName = basename($currentPath);
            $newPath = $directory . '/' . $fileName;

            if (Storage::disk('public')->exists($newPath)) {
                $pathInfo = pathinfo($fileName);
                $fileName = $pathInfo['filename'] . '_' . time() . '.' . $pathInfo['extension'];
                $newPath = $directory . '/' . $fileName;
            }

            if (Storage::disk('public')->move($currentPath, $newPath)) {
                Log::info('Imagen movida exitosamente', [
                    'from' => $currentPath,
                    'to' => $newPath,
                    'category' => $category->name,
                ]);
                return $newPath;
            }

            throw new \Exception('Error al mover el archivo');
        } catch (\Exception $e) {
            Log::error('Error al mover imagen a categoría', [
                'error' => $e->getMessage(),
                'current_path' => $currentPath,
                'category_id' => $categoryId,
            ]);
            return null;
        }
    }

    /**
     * Eliminar imagen de forma segura
     */
    public function deleteImage(string $imagePath): bool
    {
        try {
            if ($this->imageValidationService->validateImagePath($imagePath)) {
                $deleted = Storage::disk('public')->delete($imagePath);

                if ($deleted) {
                    Log::info('Imagen eliminada exitosamente', ['path' => $imagePath]);
                } else {
                    Log::warning('No se pudo eliminar la imagen', ['path' => $imagePath]);
                }

                return $deleted;
            }

            Log::warning('Intento de eliminar imagen inexistente', ['path' => $imagePath]);
            return false;
        } catch (\Exception $e) {
            Log::error('Error al eliminar imagen', [
                'error' => $e->getMessage(),
                'path' => $imagePath,
            ]);
            return false;
        }
    }

    /**
     * Obtener todas las imágenes de una categoría
     */
    public function getImagesByCategory(int $categoryId): array
    {
        try {
            $category = Category::find($categoryId);
            if (!$category) {
                return [];
            }

            $categorySlug = Str::slug($category->name);
            $categoryDirectory = self::BASE_DIRECTORY . '/' . $categorySlug;
            $files = [];

            $this->streamNewsFiles(function (string $relativePath) use (&$files, $categoryDirectory): void {
                if (strpos($relativePath, $categoryDirectory) !== 0) {
                    return;
                }

                $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));
                if (in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                    $files[] = $relativePath;
                }
            });

            return $files;
        } catch (\Exception $e) {
            Log::error('Error al obtener imágenes por categoría', [
                'error' => $e->getMessage(),
                'category_id' => $categoryId,
            ]);
            return [];
        }
    }

    /**
     * Iterar los archivos de noticias sin cargar todo el árbol en memoria.
     */
    public function streamNewsFiles(callable $callback): void
    {
        $basePath = storage_path('app/public/' . self::BASE_DIRECTORY);
        if (!is_dir($basePath)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($basePath, FilesystemIterator::SKIP_DOTS)
        );

        $prefixLength = strlen(storage_path('app/public/'));

        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }

            $fullPath = $fileInfo->getPathname();
            $relativePath = str_replace('\\', '/', substr($fullPath, $prefixLength));
            $relativePath = ltrim($relativePath, '/');
            $callback($relativePath);
        }
    }

    /**
     * Crear backup de imagen antes de operaciones críticas
     */
    public function createImageBackup(string $imagePath): ?string
    {
        try {
            if (!$this->imageValidationService->validateImagePath($imagePath)) {
                return null;
            }

            $backupDirectory = 'backups/images/' . Carbon::now()->format('Y/m/d');
            $fileName = basename($imagePath);
            $backupPath = $backupDirectory . '/' . time() . '_' . $fileName;

            if (Storage::disk('public')->copy($imagePath, $backupPath)) {
                Log::info('Backup de imagen creado', [
                    'original' => $imagePath,
                    'backup' => $backupPath,
                ]);
                return $backupPath;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error al crear backup de imagen', [
                'error' => $e->getMessage(),
                'path' => $imagePath,
            ]);
            return null;
        }
    }

    /**
     * Migrar todas las imágenes existentes a la nueva estructura
     */
    public function migrateExistingImages(bool $dryRun = false): array
    {
        $results = [
            'migrated' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        try {
            $categorySlugs = Category::all()->mapWithKeys(function ($category) {
                return [$category->id => Str::slug($category->name)];
            })->toArray();

            \App\Models\Noticia::whereNotNull('imagen')
                ->where('imagen', '!=', '')
                ->with('category')
                ->chunk(200, function ($noticias) use (&$results, $categorySlugs, $dryRun): void {
                    foreach ($noticias as $noticia) {
                        try {
                            if (!$noticia->category) {
                                $results['skipped']++;
                                continue;
                            }

                            $categorySlug = $categorySlugs[$noticia->category_id] ?? Str::slug($noticia->category->name);
                            if (Str::contains($noticia->imagen, $categorySlug)) {
                                $results['skipped']++;
                                continue;
                            }

                            $basename = basename($noticia->imagen);
                            $candidates = [
                                $noticia->imagen,
                                self::BASE_DIRECTORY . '/' . $basename,
                                self::BASE_DIRECTORY . '/' . $categorySlug . '/' . $basename,
                            ];

                            $found = null;
                            foreach ($candidates as $candidate) {
                                if (Storage::disk('public')->exists($candidate)) {
                                    $found = $candidate;
                                    break;
                                }
                            }

                            if ($found) {
                                if ($dryRun) {
                                    $results['migrated']++;
                                } else {
                                    $noticia->update(['imagen' => $found]);
                                    $results['migrated']++;
                                }
                                continue;
                            }

                            if ($dryRun) {
                                $results['failed']++;
                                $results['errors'][] = "Imagen no encontrada (dry-run) para noticia ID: {$noticia->id} - {$noticia->imagen}";
                                continue;
                            }

                            $this->createImageBackup($noticia->imagen);
                            $newPath = $this->moveImageToCategory($noticia->imagen, $noticia->category_id);

                            if ($newPath) {
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
                    }
                });
        } catch (\Exception $e) {
            $results['errors'][] = 'Error general en migración: ' . $e->getMessage();
        }

        Log::info('Migración de imágenes completada', $results);
        return $results;
    }

    /**
     * Validar archivo de imagen
     */
    private function validateImageFile(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new \Exception('El archivo no es válido');
        }

        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new \Exception('El archivo es demasiado grande. Máximo 5MB permitido.');
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new \Exception('Tipo de archivo no permitido. Solo se permiten: ' . implode(', ', self::ALLOWED_EXTENSIONS));
        }

        $imageInfo = getimagesize($file->getPathname());
        if (!$imageInfo) {
            throw new \Exception('El archivo no es una imagen válida');
        }
    }

    /**
     * Generar nombre único para el archivo
     */
    private function generateUniqueFileName(UploadedFile $file, int $categoryId): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $timestamp = Carbon::now()->format('YmdHis');
        $random = Str::random(8);

        return "cat{$categoryId}_{$timestamp}_{$random}.{$extension}";
    }
}