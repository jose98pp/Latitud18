<?php

/**
 * Script para crear la tabla banners
 * 
 * Uso: php scripts/create_banners_table.php
 */

// Configuración
define('BASE_PATH', dirname(__DIR__));

// Cargar configuración de base de datos desde .env
$envFile = BASE_PATH . '/.env';
if (!file_exists($envFile)) {
    die("Error: Archivo .env no encontrado\n");
}

// Leer el archivo .env línea por línea
$envConfig = [];
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) {
        continue;
    }
    
    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $envConfig[trim($key)] = trim($value);
    }
}

$dbHost = $envConfig['DB_HOST'] ?? 'localhost';
$dbDatabase = $envConfig['DB_DATABASE'] ?? '';
$dbUsername = $envConfig['DB_USERNAME'] ?? '';
$dbPassword = $envConfig['DB_PASSWORD'] ?? '';

echo "Conectando a base de datos: $dbHost/$dbDatabase\n";

try {
    // Conectar a la base de datos
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbDatabase;charset=utf8mb4", $dbUsername, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexión exitosa\n\n";
    
    // Verificar si la tabla banners ya existe
    $checkStmt = $pdo->query("SHOW TABLES LIKE 'banners'");
    
    if ($checkStmt->rowCount() > 0) {
        echo "⚠️  La tabla 'banners' ya existe\n";
        echo "ℹ️  No se requiere acción adicional\n";
        exit(0);
    }
    
    echo "📝 Creando tabla 'banners'...\n";
    
    // Crear la tabla banners
    $createTable = "
        CREATE TABLE banners (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            image_path VARCHAR(255) NOT NULL,
            link VARCHAR(255) NULL,
            location VARCHAR(50) NOT NULL COMMENT 'portada_top, portada_middle, sidebar, footer, etc.',
            active TINYINT(1) DEFAULT 1,
            position INT DEFAULT 0,
            created_at TIMESTAMP NULL DEFAULT NULL,
            updated_at TIMESTAMP NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($createTable);
    
    echo "✅ Tabla 'banners' creada exitosamente\n\n";
    
    // Verificar la creación
    $verifyStmt = $pdo->query("DESCRIBE banners");
    $columns = $verifyStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📊 Estructura de la tabla:\n";
    foreach ($columns as $column) {
        echo "   - {$column['Field']}: {$column['Type']}\n";
    }
    
    echo "\n✨ Migración completada.\n";
    
} catch (PDOException $e) {
    echo "❌ Error de base de datos: " . $e->getMessage() . "\n";
    exit(1);
}
