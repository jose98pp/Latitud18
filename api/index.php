<?php

// Forward Vercel Serverless Function requests to Laravel
// Prepare /tmp directories for caching, views, sessions, logs, and bootstrap manifests
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
    '/tmp/storage/bootstrap',
    '/tmp/storage/bootstrap/cache',
];

foreach ($storagePaths as $path) {
    if (!is_dir($path)) {
        @mkdir($path, 0777, true);
    }
}

// Redirect all Laravel cache & manifest files to writable /tmp
$cacheDir = '/tmp/storage/bootstrap/cache';
putenv("APP_PACKAGES_CACHE={$cacheDir}/packages.php");
putenv("APP_SERVICES_CACHE={$cacheDir}/services.php");
putenv("APP_CONFIG_CACHE={$cacheDir}/config.php");
putenv("APP_ROUTES_CACHE={$cacheDir}/routes.php");
putenv("APP_EVENTS_CACHE={$cacheDir}/events.php");

$_ENV['APP_PACKAGES_CACHE'] = "{$cacheDir}/packages.php";
$_ENV['APP_SERVICES_CACHE'] = "{$cacheDir}/services.php";
$_ENV['APP_CONFIG_CACHE'] = "{$cacheDir}/config.php";
$_ENV['APP_ROUTES_CACHE'] = "{$cacheDir}/routes.php";
$_ENV['APP_EVENTS_CACHE'] = "{$cacheDir}/events.php";

$_SERVER['APP_PACKAGES_CACHE'] = "{$cacheDir}/packages.php";
$_SERVER['APP_SERVICES_CACHE'] = "{$cacheDir}/services.php";
$_SERVER['APP_CONFIG_CACHE'] = "{$cacheDir}/config.php";
$_SERVER['APP_ROUTES_CACHE'] = "{$cacheDir}/routes.php";
$_SERVER['APP_EVENTS_CACHE'] = "{$cacheDir}/events.php";

putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';
$_SERVER['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';

// Ensure numeric/safe environment defaults for serverless execution
if (empty($_ENV['SESSION_LIFETIME']) || !is_numeric($_ENV['SESSION_LIFETIME'])) {
    $_ENV['SESSION_LIFETIME'] = '120';
    $_SERVER['SESSION_LIFETIME'] = '120';
    putenv('SESSION_LIFETIME=120');
}
if (empty($_ENV['DB_CONNECTION'])) {
    $_ENV['DB_CONNECTION'] = 'mysql';
    $_SERVER['DB_CONNECTION'] = 'mysql';
    putenv('DB_CONNECTION=mysql');
}

// Mark Vercel environment flag
$_ENV['VERCEL'] = '1';
$_SERVER['VERCEL'] = '1';
putenv('VERCEL=1');

require __DIR__ . '/../public/index.php';
