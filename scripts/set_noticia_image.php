<?php

// Lightweight script: copy an image into storage and update noticias.imagen using PDO.
// Usage: php scripts/set_noticia_image.php <noticia_id> <source_image_path>

if ($argc < 3) {
    echo "Usage: php scripts/set_noticia_image.php <noticia_id> <source_image_path>\n";
    exit(1);
}

$noticiaId = (int)$argv[1];
$source = $argv[2];

// resolve source relative to project root if necessary
if (!file_exists($source)) {
    $try = __DIR__ . '/../' . ltrim($source, '/\\');
    if (file_exists($try)) {
        $source = $try;
    }
}

if (!file_exists($source)) {
    echo "Source image not found: {$source}\n";
    exit(1);
}

// read DB config from .env
$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    echo ".env not found at project root. Can't read DB credentials.\n";
    exit(1);
}

$env = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$cfg = [];
foreach ($env as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    if (!strpos($line, '=')) continue;
    list($k, $v) = explode('=', $line, 2);
    $cfg[trim($k)] = trim($v);
}

$dbDriver = $cfg['DB_CONNECTION'] ?? 'mysql';
$dbHost = $cfg['DB_HOST'] ?? '127.0.0.1';
$dbPort = $cfg['DB_PORT'] ?? '3306';
$dbName = $cfg['DB_DATABASE'] ?? null;
$dbUser = $cfg['DB_USERNAME'] ?? null;
$dbPass = $cfg['DB_PASSWORD'] ?? null;

if (!$dbName || !$dbUser) {
    echo "DB credentials incomplete in .env (DB_DATABASE/DB_USERNAME).\n";
    exit(1);
}

try {
    $dsn = "$dbDriver:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // get noticia and category name
    $stmt = $pdo->prepare('SELECT id, category_id FROM noticias WHERE id = ?');
    $stmt->execute([$noticiaId]);
    $noticia = $stmt->fetch();
    if (!$noticia) {
        throw new Exception("Noticia with id {$noticiaId} not found.");
    }

    $categoryId = $noticia['category_id'];
    $categorySlug = 'uncategorized';
    if ($categoryId) {
        $stmt = $pdo->prepare('SELECT name FROM categories WHERE id = ?');
        $stmt->execute([$categoryId]);
        $cat = $stmt->fetch();
        if ($cat && !empty($cat['name'])) {
            $categorySlug = slugify($cat['name']);
        }
    }

    $destDir = __DIR__ . '/../storage/app/public/noticias/' . $categorySlug;
    if (!is_dir($destDir)) {
        if (!mkdir($destDir, 0755, true)) {
            throw new Exception('Failed to create dest dir: ' . $destDir);
        }
    }

    $basename = basename($source);
    $destName = time() . '_' . bin2hex(random_bytes(3)) . '_' . $basename;
    $destPath = $destDir . DIRECTORY_SEPARATOR . $destName;

    if (!copy($source, $destPath)) {
        throw new Exception('Failed to copy file to ' . $destPath);
    }
    chmod($destPath, 0644);

    $relativePath = 'noticias/' . $categorySlug . '/' . $destName;

    $upd = $pdo->prepare('UPDATE noticias SET imagen = ? WHERE id = ?');
    $upd->execute([$relativePath, $noticiaId]);

    echo "Success: noticia {$noticiaId} updated with image {$relativePath}\n";
    echo "Public URL (relative to app base): /storage/{$relativePath}\n";

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    exit(1);
}

function slugify($text)
{
    // replace non letter or digits by -
    $text = preg_replace('~[^\\pL\\d]+~u', '-', $text);
    // transliterate
    if (function_exists('iconv')) {
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    }
    // remove unwanted characters
    $text = preg_replace('~[^-a-zA-Z0-9]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    if (empty($text)) {
        return 'cat';
    }
    return $text;
}
