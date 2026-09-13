<?php

/**
 * Script para agregar el campo views a la tabla noticias
 * 
 * Uso: php scripts/add_views_column.php
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
    
    // Verificar si el campo views ya existe
    $checkStmt = $pdo->query("SHOW COLUMNS FROM noticias LIKE 'views'");
    
    if ($checkStmt->rowCount() > 0) {
        echo "⚠️  El campo 'views' ya existe en la tabla noticias\n";
        echo "ℹ️  No se requiere acción adicional\n";
        exit(0);
    }
    
    echo "📝 Agregando campo 'views' a la tabla noticias...\n";
    
    // Agregar el campo views
    $alterStmt = $pdo->query("ALTER TABLE noticias ADD COLUMN views INT DEFAULT 0 AFTER imagen");
    
    echo "✅ Campo 'views' agregado exitosamente\n\n";
    
    // Verificar el cambio
    $verifyStmt = $pdo->query("SHOW COLUMNS FROM noticias LIKE 'views'");
    $column = $verifyStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($column) {
        echo "📊 Información del campo agregado:\n";
        echo "   Campo: " . $column['Field'] . "\n";
        echo "   Tipo: " . $column['Type'] . "\n";
        echo "   Default: " . $column['Default'] . "\n";
    }
    
    echo "\n✨ Migración completada.\n";
    
} catch (PDOException $e) {
    echo "❌ Error de base de datos: " . $e->getMessage() . "\n";
    exit(1);
}
