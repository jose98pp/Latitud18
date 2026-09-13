<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ImageValidationService
{
    /**
     * Imagen por defecto cuando no se encuentra la imagen original
     */
    private const DEFAULT_IMAGE = 'images/default-news.svg';

    /**
     * Extensiones de imagen permitidas
     */
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * Validar si la ruta de imagen existe y es válida
     *
     * @param string|null $imagePath
     * @return bool
     */
    public function validateImagePath(?string $imagePath): bool
    {
        if (empty($imagePath)) {
            return false;
        }

        // Verificar si el archivo existe en el storage público
        if (!Storage::disk('public')->exists($imagePath)) {
            Log::warning('Imagen no encontrada', [
                'path' => $imagePath,
                'full_path' => Storage::disk('public')->path($imagePath)
            ]);
            return false;
        }

        // Verificar que sea un archivo de imagen válido
        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            Log::warning('Extensión de imagen no válida', [
                'path' => $imagePath,
                'extension' => $extension
            ]);
            return false;
        }

        return true;
    }

    /**
     * Obtener URL de imagen o imagen por defecto como fallback
     *
     * @param string|null $imagePath
     * @return string
     */
    public function getImageUrlOrDefault(?string $imagePath): string
    {
        if ($this->validateImagePath($imagePath)) {
            return $this->generateSecureImageUrl($imagePath);
        }

        return asset(self::DEFAULT_IMAGE);
    }

    /**
     * Generar URL segura de imagen con validaciones
     *
     * @param string $imagePath
     * @return string
     */
    public function generateSecureImageUrl(string $imagePath): string
    {
        // Limpiar la ruta de caracteres peligrosos
        $cleanPath = $this->sanitizeImagePath($imagePath);
        
        // Verificar que la imagen existe
        if (!$this->validateImagePath($cleanPath)) {
            return asset(self::DEFAULT_IMAGE);
        }

        return asset('storage/' . $cleanPath);
    }

    /**
     * Limpiar ruta de imagen de caracteres peligrosos
     *
     * @param string $imagePath
     * @return string
     */
    private function sanitizeImagePath(string $imagePath): string
    {
        // Remover caracteres peligrosos y normalizar la ruta
        $cleanPath = preg_replace('/[^a-zA-Z0-9\/_.-]/', '', $imagePath);
        
        // Remover dobles barras y normalizar
        $cleanPath = preg_replace('/\/+/', '/', $cleanPath);
        
        // Remover barras al inicio y final
        $cleanPath = trim($cleanPath, '/');
        
        return $cleanPath;
    }

    /**
     * Validar múltiples rutas de imagen
     *
     * @param array $imagePaths
     * @return array Array con rutas válidas e inválidas
     */
    public function validateMultipleImagePaths(array $imagePaths): array
    {
        $valid = [];
        $invalid = [];

        foreach ($imagePaths as $path) {
            if ($this->validateImagePath($path)) {
                $valid[] = $path;
            } else {
                $invalid[] = $path;
            }
        }

        return [
            'valid' => $valid,
            'invalid' => $invalid
        ];
    }

    /**
     * Obtener información detallada de una imagen
     *
     * @param string|null $imagePath
     * @return array
     */
    public function getImageInfo(?string $imagePath): array
    {
        if (empty($imagePath)) {
            return [
                'exists' => false,
                'url' => asset(self::DEFAULT_IMAGE),
                'is_default' => true,
                'size' => null,
                'extension' => null
            ];
        }

        $exists = Storage::disk('public')->exists($imagePath);

        return [
            'exists' => $exists,
            'url' => $exists ? asset('storage/' . $imagePath) : asset(self::DEFAULT_IMAGE),
            'is_default' => !$exists,
            'size' => $exists ? Storage::disk('public')->size($imagePath) : null,
            'extension' => $exists ? strtolower(pathinfo($imagePath, PATHINFO_EXTENSION)) : null,
            'path' => $imagePath
        ];
    }

    /**
     * Verificar si una imagen necesita ser reemplazada por la imagen por defecto
     *
     * @param string|null $imagePath
     * @return bool
     */
    public function needsDefaultImage(?string $imagePath): bool
    {
        return !$this->validateImagePath($imagePath);
    }
}