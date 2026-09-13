<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $table = 'site_settings';

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * Obtiene el valor de una configuración por su clave
     */
    public static function get(string $key, $default = null)
    {
        return Cache::rememberForever("site_setting_{$key}", function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Guarda o actualiza una configuración
     */
    public static function set(string $key, $value, string $group = 'general'): self
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        Cache::forget("site_setting_{$key}");
        Cache::forget('site_settings_all');

        return $setting;
    }

    /**
     * Obtiene todas las configuraciones agrupadas
     */
    public static function getAllGrouped(): array
    {
        $all = static::all();
        $grouped = [];
        foreach ($all as $item) {
            $grouped[$item->group][$item->key] = $item->value;
        }
        return $grouped;
    }
}
