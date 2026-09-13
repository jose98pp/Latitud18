<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Aquí puedes especificar el disco del sistema de archivos que se usará
    | por defecto. Laravel incluye tanto "local" como discos basados en la nube
    | para almacenamiento conveniente y flexible.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Aquí puedes configurar tantos "discos" como desees y puedes incluso
    | configurar múltiples discos del mismo controlador. Los valores predeterminados
    | han sido configurados para cada controlador como un ejemplo.
    |
    | Controladores soportados: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        // Disco "local", para almacenamiento interno no accesible directamente por la web.
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        // Disco "public", accesible desde la web.
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        // Configuración para almacenamiento en la nube (si fuera necesario).
        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Aquí puedes configurar los enlaces simbólicos que se crearán al ejecutar
    | el comando `storage:link`. Las claves del array deben ser las ubicaciones
    | de los enlaces y los valores deben ser sus destinos.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
