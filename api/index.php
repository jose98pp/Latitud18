<?php

// Forward Vercel Serverless Function requests to Laravel
// Prepare /tmp directories for caching and views since Vercel's root filesystem is read-only
$tempPaths = [
    '/tmp/views',
    '/tmp/cache',
    '/tmp/sessions',
    '/tmp/logs',
];

foreach ($tempPaths as $path) {
    if (!is_dir($path)) {
        @mkdir($path, 0755, true);
    }
}

require __DIR__ . '/../public/index.php';
