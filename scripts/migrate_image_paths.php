<?php

// Load .env file manually
$envFile = dirname(__DIR__) . '/.env';
$env = [];
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        // Split into key and value
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        // Remove quotes if present
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || 
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }
        $env[$key] = $value;
    }
} else {
    die("No .env file found!\n");
}

$baseDir = dirname(__DIR__) . '/storage/app/public';
// Use public/storage as base if storage/app/public doesn't exist
if (!is_dir($baseDir)) {
    $baseDir = dirname(__DIR__) . '/public/storage';
}

$dsn = 'mysql:host=' . ($env['DB_HOST'] ?? 'localhost') . ';port=' . ($env['DB_PORT'] ?? '3306') . ';dbname=' . ($env['DB_DATABASE'] ?? 'noticiadev') . ';charset=utf8mb4';
$user = $env['DB_USERNAME'] ?? 'root';
$pass = $env['DB_PASSWORD'] ?? '';

$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$categorySlugs = [];
$stmt = $pdo->query('SELECT id, name FROM categories');
foreach ($stmt->fetchAll() as $row) {
    $categorySlugs[(int) $row['id']] = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $row['name']));
}

$files = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($baseDir . '/noticias', FilesystemIterator::SKIP_DOTS)
);
foreach ($iterator as $file) {
    if (!$file->isFile()) {
        continue;
    }

    $fullPath = $file->getPathname();
    $relativePath = str_replace('\\', '/', substr($fullPath, strlen($baseDir . '/')));
    $relativePath = ltrim($relativePath, '/');
    $files[] = $relativePath;
}

$updated = 0;
$skipped = 0;
$failed = 0;

$rows = $pdo->query("SELECT id, imagen, category_id FROM noticias WHERE imagen IS NOT NULL AND TRIM(imagen) <> '' ORDER BY id");
foreach ($rows->fetchAll() as $row) {
    $current = trim($row['imagen']);
    $id = (int) $row['id'];
    $categoryId = (int) $row['category_id'];

    $normalized = trim(str_replace('\\', '/', $current), '/');
    $normalized = preg_replace('#^(storage|public)/#', '', $normalized) ?? $normalized;
    $normalized = ltrim($normalized, '/');

    $candidates = [];
    if ($normalized !== '') {
        $candidates[] = $normalized;
        $basename = basename($normalized);
        if ($basename !== '') {
            $candidates[] = 'noticias/' . $basename;
            if (!empty($categorySlugs[$categoryId])) {
                $candidates[] = 'noticias/' . $categorySlugs[$categoryId] . '/' . $basename;
            }
        }
    }

    $resolved = null;
    foreach ($candidates as $candidate) {
        if (is_file($baseDir . '/' . $candidate)) {
            $resolved = $candidate;
            break;
        }
    }

    if ($resolved === null) {
        $skipped++;
        continue;
    }

    if ($resolved === $normalized) {
        $skipped++;
        continue;
    }

    try {
        $pdo->prepare('UPDATE noticias SET imagen = :imagen WHERE id = :id')->execute([
            ':imagen' => $resolved,
            ':id' => $id,
        ]);
        $updated++;
    } catch (Throwable $e) {
        $failed++;
        fwrite(STDERR, "Error al actualizar noticia {$id}: {$e->getMessage()}\n");
    }
}

fwrite(STDOUT, json_encode([
    'updated' => $updated,
    'skipped' => $skipped,
    'failed' => $failed,
], JSON_UNESCAPED_UNICODE) . PHP_EOL);
