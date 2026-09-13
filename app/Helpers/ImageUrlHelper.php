<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;

class ImageUrlHelper
{
    /**
     * Generar URL de imagen con fallback automático
     *
     * @param string|null $imagePath
     * @param string|null $size
     * @return string
     */
    public static function getImageUrl(?string $imagePath, ?string $size = null): string
    {
        // Si no hay imagen, devolver imagen por defecto
        if (empty($imagePath)) {
            return self::getDefaultImageUrl();
        }

        $resolvedPath = self::resolveImagePath($imagePath);

        // Si no se pudo resolver la imagen, devolver imagen por defecto
        if (!$resolvedPath) {
            return self::getDefaultImageUrl();
        }

        // Si hay CDN configurado, usarlo
        if (Config::get('images.cdn.enabled')) {
            return self::getCdnUrl($resolvedPath, $size);
        }

        // URL local estándar
        return asset('storage/' . $resolvedPath);
    }

    /**
     * Resolver la ruta real de una imagen aunque haya sido movida a una subcarpeta por categoría.
     *
     * @param string|null $imagePath
     * @return string|null
     */
    public static function resolveImagePath(?string $imagePath): ?string
    {
        if (empty($imagePath)) {
            return null;
        }

        $normalizedPath = trim(str_replace('\\', '/', $imagePath), '/');
        $normalizedPath = preg_replace('#^(storage|public)/#', '', $normalizedPath) ?? $normalizedPath;
        $normalizedPath = ltrim($normalizedPath, '/');

        if (empty($normalizedPath)) {
            return null;
        }

        if (Storage::disk('public')->exists($normalizedPath)) {
            return $normalizedPath;
        }

        $basename = basename($normalizedPath);
        $candidates = [$normalizedPath];

        if ($normalizedPath !== $basename) {
            $candidates[] = $basename;
            $candidates[] = 'noticias/' . $basename;
        }

        foreach ($candidates as $candidate) {
            if (Storage::disk('public')->exists($candidate)) {
                return $candidate;
            }
        }

        // Evitar escanear todo el disco (puede consumir mucha memoria en discos grandes).
        // En su lugar solo intentamos rutas deterministas construidas arriba.
        // Si no se encuentra, devolvemos null para que el caller pueda decidir (migrator, logs, etc.).
        return null;
    }

    /**
     * Obtener URL de imagen por defecto
     *
     * @return string
     */
    public static function getDefaultImageUrl(): string
    {
        $defaultImage = Config::get('images.fallback.default_image', 'images/default-news.svg');
        return asset($defaultImage);
    }

    /**
     * Generar URL de CDN
     *
     * @param string $imagePath
     * @param string|null $size
     * @return string
     */
    private static function getCdnUrl(string $imagePath, ?string $size = null): string
    {
        $cdnBaseUrl = Config::get('images.cdn.base_url');
        
        if (!$cdnBaseUrl) {
            return asset('storage/' . $imagePath);
        }

        $url = rtrim($cdnBaseUrl, '/') . '/' . ltrim($imagePath, '/');
        
        // Agregar parámetros de tamaño si se especifica
        if ($size && Config::get('images.performance.responsive_images')) {
            $sizes = Config::get('images.performance.thumbnail_sizes', []);
            if (isset($sizes[$size])) {
                [$width, $height] = $sizes[$size];
                $url .= "?w={$width}&h={$height}&fit=crop";
            }
        }

        return $url;
    }

    /**
     * Generar múltiples tamaños de imagen para responsive design
     *
     * @param string|null $imagePath
     * @return array
     */
    public static function getResponsiveImageUrls(?string $imagePath): array
    {
        if (empty($imagePath) || !Storage::disk('public')->exists($imagePath)) {
            $defaultUrl = self::getDefaultImageUrl();
            return [
                'small' => $defaultUrl,
                'medium' => $defaultUrl,
                'large' => $defaultUrl,
                'original' => $defaultUrl,
            ];
        }

        return [
            'small' => self::getImageUrl($imagePath, 'small'),
            'medium' => self::getImageUrl($imagePath, 'medium'),
            'large' => self::getImageUrl($imagePath, 'large'),
            'original' => self::getImageUrl($imagePath),
        ];
    }

    /**
     * Generar atributos srcset para imágenes responsive
     *
     * @param string|null $imagePath
     * @return string
     */
    public static function getSrcSet(?string $imagePath): string
    {
        $urls = self::getResponsiveImageUrls($imagePath);
        
        $srcset = [];
        $srcset[] = $urls['small'] . ' 150w';
        $srcset[] = $urls['medium'] . ' 300w';
        $srcset[] = $urls['large'] . ' 600w';
        $srcset[] = $urls['original'] . ' 1200w';
        
        return implode(', ', $srcset);
    }

    /**
     * Generar HTML de imagen con lazy loading y responsive
     *
     * @param string|null $imagePath
     * @param string $alt
     * @param array $attributes
     * @return string
     */
    public static function generateImageHtml(?string $imagePath, string $alt = '', array $attributes = []): string
    {
        $url = self::getImageUrl($imagePath);
        $srcset = self::getSrcSet($imagePath);
        
        $defaultAttributes = [
            'src' => $url,
            'alt' => $alt,
            'loading' => 'lazy',
            'decoding' => 'async',
        ];

        if (Config::get('images.performance.responsive_images')) {
            $defaultAttributes['srcset'] = $srcset;
            $defaultAttributes['sizes'] = '(max-width: 300px) 150px, (max-width: 600px) 300px, (max-width: 1200px) 600px, 1200px';
        }

        $attributes = array_merge($defaultAttributes, $attributes);
        
        $attributeString = '';
        foreach ($attributes as $key => $value) {
            $attributeString .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }

        return '<img' . $attributeString . '>';
    }

    /**
     * Verificar si una imagen necesita optimización
     *
     * @param string $imagePath
     * @return bool
     */
    public static function needsOptimization(string $imagePath): bool
    {
        if (!Storage::disk('public')->exists($imagePath)) {
            return false;
        }

        $fullPath = Storage::disk('public')->path($imagePath);
        $fileSize = filesize($fullPath);
        $maxSize = Config::get('images.validation.max_file_size', 5 * 1024 * 1024);

        return $fileSize > $maxSize;
    }

    /**
     * Obtener información de imagen para debugging
     *
     * @param string|null $imagePath
     * @return array
     */
    public static function getImageDebugInfo(?string $imagePath): array
    {
        if (empty($imagePath)) {
            return [
                'exists' => false,
                'using_default' => true,
                'url' => self::getDefaultImageUrl(),
            ];
        }

        $resolvedPath = self::resolveImagePath($imagePath);
        $exists = !empty($resolvedPath);
        
        return [
            'path' => $imagePath,
            'resolved_path' => $resolvedPath,
            'exists' => $exists,
            'using_default' => !$exists,
            'url' => self::getImageUrl($imagePath),
            'size' => $exists ? Storage::disk('public')->size($resolvedPath) : null,
            'last_modified' => $exists ? Storage::disk('public')->lastModified($resolvedPath) : null,
            'responsive_urls' => self::getResponsiveImageUrls($imagePath),
        ];
    }
}