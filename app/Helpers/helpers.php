<?php

use App\Models\SiteSetting;

if (!function_exists('setting')) {
    /**
     * Helper para obtener o establecer una configuración del sitio
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function setting(?string $key = null, $default = null)
    {
        if (is_null($key)) {
            return app(SiteSetting::class);
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                SiteSetting::set($k, $v);
            }
            return true;
        }

        return SiteSetting::get($key, $default);
    }
}

if (!function_exists('extract_youtube_id')) {
    /**
     * Extrae de forma limpia el ID de 11 caracteres de cualquier enlace de YouTube
     * (soporta directos /live/, /watch?v=, youtu.be/, /embed/, /shorts/, etc.)
     *
     * @param string|null $value
     * @return string|null
     */
    function extract_youtube_id(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = trim($value);

        // Si ya es únicamente el ID de 11 caracteres
        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $value)) {
            return $value;
        }

        // Si es una URL completa (soporta /live/, /watch?v=, youtu.be/, /embed/, /shorts/)
        if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=|live\/|shorts\/))([a-zA-Z0-9_-]{11})/i', $value, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
