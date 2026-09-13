<?php

namespace App\Traits;

use App\Helpers\ImageUrlHelper;
use App\Services\ImageValidationService;
use Illuminate\Support\Facades\Storage;

trait HasImages
{
    /**
     * Obtener URL de imagen con fallback automático
     *
     * @param string|null $imageField
     * @return string
     */
    public function getImageUrl(?string $imageField = null): string
    {
        $field = $imageField ?? 'imagen';
        return ImageUrlHelper::getImageUrl($this->$field);
    }

    /**
     * Obtener URLs responsive de imagen
     *
     * @param string|null $imageField
     * @return array
     */
    public function getResponsiveImageUrls(?string $imageField = null): array
    {
        $field = $imageField ?? 'imagen';
        return ImageUrlHelper::getResponsiveImageUrls($this->$field);
    }

    /**
     * Generar HTML de imagen
     *
     * @param string $alt
     * @param array $attributes
     * @param string|null $imageField
     * @return string
     */
    public function getImageHtml(string $alt = '', array $attributes = [], ?string $imageField = null): string
    {
        $field = $imageField ?? 'imagen';
        return ImageUrlHelper::generateImageHtml($this->$field, $alt, $attributes);
    }

    /**
     * Verificar si la imagen existe
     *
     * @param string|null $imageField
     * @return bool
     */
    public function hasValidImage(?string $imageField = null): bool
    {
        $field = $imageField ?? 'imagen';
        $imagePath = $this->$field;
        
        if (empty($imagePath)) {
            return false;
        }

        return Storage::disk('public')->exists($imagePath);
    }

    /**
     * Obtener información de imagen para admin
     *
     * @param string|null $imageField
     * @return array
     */
    public function getImageInfo(?string $imageField = null): array
    {
        $field = $imageField ?? 'imagen';
        $imageValidationService = app(ImageValidationService::class);
        return $imageValidationService->getImageInfo($this->$field);
    }

    /**
     * Obtener información de debug de imagen
     *
     * @param string|null $imageField
     * @return array
     */
    public function getImageDebugInfo(?string $imageField = null): array
    {
        $field = $imageField ?? 'imagen';
        return ImageUrlHelper::getImageDebugInfo($this->$field);
    }

    /**
     * Scope para noticias con imágenes válidas
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $imageField
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithValidImages($query, ?string $imageField = null)
    {
        $field = $imageField ?? 'imagen';
        return $query->whereNotNull($field)->where($field, '!=', '');
    }

    /**
     * Scope para noticias sin imágenes
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $imageField
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithoutImages($query, ?string $imageField = null)
    {
        $field = $imageField ?? 'imagen';
        return $query->where(function($q) use ($field) {
            $q->whereNull($field)->orWhere($field, '');
        });
    }

    /**
     * Accessor para URL de imagen
     */
    public function getImageUrlAttribute(): string
    {
        return $this->getImageUrl();
    }

    /**
     * Accessor para URLs responsive
     */
    public function getResponsiveImageUrlsAttribute(): array
    {
        return $this->getResponsiveImageUrls();
    }

    /**
     * Accessor para verificar si tiene imagen válida
     */
    public function getHasValidImageAttribute(): bool
    {
        return $this->hasValidImage();
    }
}