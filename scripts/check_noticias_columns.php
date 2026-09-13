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
$stmt = $pdo->query("SHOW COLUMNS FROM noticias");
$rows = $stmt->fetchAll();
foreach ($rows as $row) {
    echo $row['Field'] . ' | ' . $row['Type'] . ' | ' . $row['Null'] . ' | ' . $row['Key'] . ' | ' . $row['Default'] . ' | ' . $row['Extra'] . "\n";
}
