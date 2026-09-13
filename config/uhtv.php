<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración UHTV
    |--------------------------------------------------------------------------
    |
    | Configuraciones específicas para la aplicación UHTV
    |
    */

    'cache' => [
        'homepage_ttl' => env('UHTV_CACHE_HOMEPAGE_TTL', 300), // 5 minutos
        'categories_ttl' => env('UHTV_CACHE_CATEGORIES_TTL', 600), // 10 minutos
        'dashboard_ttl' => env('UHTV_CACHE_DASHBOARD_TTL', 300), // 5 minutos
    ],

    'pagination' => [
        'admin_news' => env('UHTV_ADMIN_NEWS_PER_PAGE', 15),
        'public_news' => env('UHTV_PUBLIC_NEWS_PER_PAGE', 10),
    ],

    'images' => [
        'default_news' => 'images/default-news.svg',
        'max_size' => env('UHTV_MAX_IMAGE_SIZE', 2048), // KB
        'allowed_types' => ['jpeg', 'png', 'jpg', 'webp'],
    ],

    'youtube' => [
        'channel_id' => env('UHTV_YOUTUBE_CHANNEL_ID', 'UUx8c9O9qP3IjtnEKkEr-Bng'),
        'api_key' => env('YOUTUBE_API_KEY', null),
    ],

    'security' => [
        'content_sanitization' => env('UHTV_SANITIZE_CONTENT', true),
        'log_errors' => env('UHTV_LOG_ERRORS', true),
        'max_title_length' => env('UHTV_MAX_TITLE_LENGTH', 255),
        'max_content_length' => env('UHTV_MAX_CONTENT_LENGTH', 250000),
    ],

    'performance' => [
        'lazy_loading' => env('UHTV_LAZY_LOADING', true),
        'compress_images' => env('UHTV_COMPRESS_IMAGES', true),
        'minify_assets' => env('UHTV_MINIFY_ASSETS', false),
    ],

    'social' => [
        'facebook' => 'https://facebook.com/uhtvbolivia',
        'youtube' => 'https://www.youtube.com/@UHTVBolivia',
        'instagram' => 'https://instagram.com/uhtvbolivia',
        'twitter' => 'https://x.com/UhtvBol',
        'tiktok' => 'https://tiktok.com/@uhtvbolivia',
    ],
];