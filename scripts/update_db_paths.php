<?php

/**
 * Script para actualizar las rutas de imágenes en la base de datos
 * después de la organización por categorías
 * 
 * Uso: php scripts/update_db_paths.php
 */

// Configuración
define('BASE_PATH', dirname(__DIR__));

// Mapeo de directorios a categorías de la base de datos
$categoryMapping = [
    'politica' => 'Política',
    'deportes' => 'Deportes',
    'economia' => 'Economía',
    'cultura' => 'Cultura',
    'espectaculo' => 'Entretenimiento',
    'mundo' => 'Internacional',
    'nacional' => 'Sociedad',
    'negocios' => 'Economía',
    'sociedad' => 'Sociedad',
    'noticias' => 'Sociedad', // Categoría por defecto
];

// Variables de estadísticas
$dbUpdated = 0;
$errors = [];

// Cargar configuración de base de datos desde .env
$envFile = BASE_PATH . '/.env';
if (!file_exists($envFile)) {
    die("Error: Archivo .env no encontrado\n");
}

// Leer el archivo .env línea por línea
$envConfig = [];
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    // Ignorar comentarios
    if (strpos(trim($line), '#') === 0) {
        continue;
    }
    
    // Parsear líneas de configuración
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
    
    // Obtener categorías de la base de datos
    $categoryStmt = $pdo->query("SELECT id, name FROM categories");
    $categories = [];
    while ($row = $categoryStmt->fetch(PDO::FETCH_ASSOC)) {
        $categories[$row['id']] = $row['name'];
    }
    
    echo "📝 Categorías encontradas: " . count($categories) . "\n";
    
    // Obtener noticias con imágenes
    $stmt = $pdo->query("SELECT id, imagen, category_id FROM noticias WHERE imagen IS NOT NULL AND imagen != ''");
    $totalNoticias = $stmt->rowCount();
    echo "📰 Noticias con imágenes: $totalNoticias\n\n";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $currentPath = $row['imagen'];
        $categoryId = $row['category_id'];
        $noticiaId = $row['id'];
        
        // Normalizar ruta
        $normalizedPath = str_replace('\\', '/', $currentPath);
        
        // Extraer nombre del archivo
        $fileName = basename($normalizedPath);
        
        // Buscar el archivo en la nueva estructura
        $newPath = findImageInNewStructure($fileName, $categoryId, $categories);
        
        if ($newPath && $newPath !== $currentPath) {
            $updateStmt = $pdo->prepare("UPDATE noticias SET imagen = ? WHERE id = ?");
            $updateStmt->execute([$newPath, $noticiaId]);
            $dbUpdated++;
            echo "   📝 Actualizado: $fileName en noticia ID $noticiaId\n";
            echo "      Antes: $currentPath\n";
            echo "      Después: $newPath\n";
        }
    }
    
    echo "\n📊 RESUMEN:\n";
    echo "================================\n";
    echo "✅ Rutas actualizadas en BD: $dbUpdated\n";
    
    if (!empty($errors)) {
        echo "\n❌ ERRORES:\n";
        foreach ($errors as $error) {
            echo "   - $error\n";
        }
    }
    
    echo "\n✨ Actualización completada.\n";
    
} catch (PDOException $e) {
    echo "❌ Error de base de datos: " . $e->getMessage() . "\n";
    exit(1);
}

// ============================================
// FUNCIONES
// ============================================

function findImageInNewStructure($fileName, $categoryId, $categories)
{
    global $categoryMapping;
    
    if (!isset($categories[$categoryId])) {
        return null;
    }

    $categoryName = $categories[$categoryId];
    $categoryDir = array_search($categoryName, $categoryMapping);
    if ($categoryDir === false) {
        $categoryDir = 'noticias';
    }

    $newPath = 'noticias/' . $categoryDir . '/' . $fileName;
    
    return $newPath;
}
