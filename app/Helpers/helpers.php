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
