<?php
$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    echo ".env file not found.\n";
    exit(1);
}
$lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$env = [];
foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') continue;
    if (strpos($line, '=') === false) continue;
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
$pdo = new PDO("mysql:host={$dbhost};port={$dbport};dbname={$dbname};charset=utf8mb4", $dbuser, $dbpass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$columns = [];
foreach ($pdo->query('SHOW COLUMNS FROM noticias') as $row) {
    $columns[$row['Field']] = $row;
}
if (isset($columns['user_id'])) {
    echo "Column user_id already exists.\n";
    exit(0);
}
$sql = "ALTER TABLE noticias ADD COLUMN user_id BIGINT UNSIGNED NULL AFTER id";
echo "Running: $sql\n";
$pdo->exec($sql);
$sql = "ALTER TABLE noticias ADD CONSTRAINT noticias_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL";
echo "Running: $sql\n";
$pdo->exec($sql);
echo "Done. user_id added to noticias.\n";
