<?php

// Forward Vercel Serverless Function requests to Laravel
// Prepare /tmp directories for caching, views, sessions and logs
$storagePaths = [
    '/tmp/storage',
    '/tmp/storage/app',
    '/tmp/storage/app/public',
    '/tmp/storage/framework',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/logs',
];

foreach ($storagePaths as $path) {
    if (!is_dir($path)) {
        @mkdir($path, 0777, true);
    }
}

// Mark Vercel environment flag
$_ENV['VERCEL'] = '1';
$_SERVER['VERCEL'] = '1';

require __DIR__ . '/../public/index.php';
