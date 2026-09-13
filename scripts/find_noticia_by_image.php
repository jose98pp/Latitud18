<?php

$path = $argv[1] ?? null;
if (!$path) {
    echo "Usage: php scripts/find_noticia_by_image.php <imagen_path>\n";
    exit(1);
}

$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    echo ".env file not found.\n";
    exit(1);
}

$lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$env = [];
foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') {
        continue;
    }
    if (strpos($line, '=') === false) {
        continue;
    }
    list($k, $v) = explode('=', $line, 2);
    $env[trim($k)] = trim($v);
}

$dbhost = $env['DB_HOST'] ?? '127.0.0.1';
$dbport = $env['DB_PORT'] ?? '3306';
$dbname = $env['DB_DATABASE'] ?? null;
$dbuser = $env['DB_USERNAME'] ?? null;
$dbpass = $env['DB_PASSWORD'] ?? null;
if (!$dbname || !$dbuser) {
    echo "Missing DB_DATABASE or DB_USERNAME in .env\n";
    exit(1);
}

$dsn = "mysql:host={$dbhost};port={$dbport};dbname={$dbname};charset=utf8mb4";
$pdo = new PDO($dsn, $dbuser, $dbpass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$stmt = $pdo->prepare('SELECT id, titulo, category_id, imagen FROM noticias WHERE imagen = ? LIMIT 1');
$stmt->execute([$path]);
$row = $stmt->fetch();
if (!$row) {
    echo "NOT FOUND\n";
    exit(0);
}
echo "id={$row['id']}\n";
echo "titulo={$row['titulo']}\n";
echo "category_id={$row['category_id']}\n";
echo "imagen={$row['imagen']}\n";
