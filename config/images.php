<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Image Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para el manejo y almacenamiento de imágenes en el sistema
    |
    */

    'storage' => [
        'disk' => 'public',
        'base_directory' => 'noticias',
        'organize_by_category' => true,
        'organize_by_date' => true,
        'date_format' => 'Y/m', // Año/Mes
    ],

    'validation' => [
        'max_file_size' => 5 * 1024 * 1024, // 5MB en bytes
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png', 
            'image/gif',
            'image/webp'
        ],
        'max_width' => 2048,
        'max_height' => 2048,
        'min_width' => 100,
        'min_height' => 100,
    ],

    'optimization' => [
        'auto_optimize' => env('IMAGE_AUTO_OPTIMIZE', true),
        'quality' => env('IMAGE_QUALITY', 85),
        'progressive_jpeg' => true,
        'strip_metadata' => true,
    ],

    'backup' => [
        'enabled' => env('IMAGE_BACKUP_ENABLED', true),
        'directory' => 'backups/images',
        'retention_days' => 30,
        'auto_cleanup' => true,
    ],

    'fallback' => [
        'default_image' => 'images/default-news.svg',
        'placeholder_service' => null, // URL de servicio de placeholders
    ],

    'cdn' => [
        'enabled' => env('IMAGE_CDN_ENABLED', false),
        'base_url' => env('IMAGE_CDN_URL'),
        'cache_control' => 'public, max-age=31536000', // 1 año
    ],

    'security' => [
        'scan_uploads' => env('IMAGE_SCAN_UPLOADS', true),
        'allowed_domains' => [], // Dominios permitidos para imágenes externas
        'block_suspicious_files' => true,
    ],

    'performance' => [
        'lazy_loading' => true,
        'responsive_images' => true,
        'webp_conversion' => env('IMAGE_WEBP_CONVERSION', false),
        'thumbnail_sizes' => [
            'small' => [150, 150],
            'medium' => [300, 300],
            'large' => [600, 600],
        ],
    ],

    'cleanup' => [
        'orphan_detection' => true,
        'auto_cleanup_orphans' => env('IMAGE_AUTO_CLEANUP_ORPHANS', false),
        'cleanup_schedule' => 'weekly',
        'archive_before_delete' => true,
    ],
];