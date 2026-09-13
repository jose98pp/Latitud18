<?php

/**
 * Script para corregir las ubicaciones de imágenes según la categoría de la noticia
 * 
 * Uso: php scripts/fix_image_paths.php
 */

// Configuración
define('BASE_PATH', dirname(__DIR__));
define('STORAGE_PATH', BASE_PATH . '/storage/app/public');

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
$imagesMoved = 0;
$errors = [];
$notFound = [];

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
    
    echo "🔍 Verificando y corrigiendo ubicaciones de imágenes...\n\n";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $currentPath = $row['imagen'];
        $categoryId = $row['category_id'];
        $noticiaId = $row['id'];
        
        // Normalizar ruta
        $normalizedPath = str_replace('\\', '/', $currentPath);
        
        // Extraer nombre del archivo
        $fileName = basename($normalizedPath);
        
        // Obtener la categoría correcta
        if (!isset($categories[$categoryId])) {
            $notFound[] = "Noticia ID $noticiaId: Categoría $categoryId no encontrada";
            continue;
        }
        
        $categoryName = $categories[$categoryId];
        $categoryDir = array_search($categoryName, $categoryMapping);
        if ($categoryDir === false) {
            $categoryDir = 'noticias';
        }
        
        // Ruta correcta esperada
        $expectedPath = 'noticias/' . $categoryDir . '/' . $fileName;
        $fullExpectedPath = STORAGE_PATH . '/' . $expectedPath;
        
        // Ruta actual del archivo
        $fullCurrentPath = STORAGE_PATH . '/' . $currentPath;
        
        // Verificar si el archivo existe en la ubicación actual
        if (!file_exists($fullCurrentPath)) {
            // Buscar el archivo en todas las categorías
            $foundPath = findImageInCategories($fileName, STORAGE_PATH . '/noticias');
            
            if ($foundPath) {
                // Mover a la ubicación correcta
                $moved = moveImageToCorrectLocation($foundPath, $fullExpectedPath, $categoryDir);
                
                if ($moved) {
                    $imagesMoved++;
                    echo "   ✅ Movido: $fileName -> $categoryDir/\n";
                } else {
                    $errors[] = "Error moviendo $fileName";
                }
            } else {
                $notFound[] = "Noticia ID $noticiaId: Imagen $fileName no encontrada en ninguna ubicación";
            }
        } elseif ($currentPath !== $expectedPath) {
            // El archivo existe pero en la ubicación incorrecta
            $moved = moveImageToCorrectLocation($fullCurrentPath, $fullExpectedPath, $categoryDir);
            
            if ($moved) {
                $imagesMoved++;
                echo "   ✅ Movido: $fileName -> $categoryDir/\n";
            } else {
                $errors[] = "Error moviendo $fileName";
            }
        }
    }
    
    echo "\n📊 RESUMEN:\n";
    echo "================================\n";
    echo "✅ Imágenes movidas: $imagesMoved\n";
    
    if (!empty($notFound)) {
        echo "\n⚠️  NO ENCONTRADAS (" . count($notFound) . "):\n";
        foreach (array_slice($notFound, 0, 10) as $item) {
            echo "   - $item\n";
        }
        if (count($notFound) > 10) {
            echo "   ... y " . (count($notFound) - 10) . " más\n";
        }
    }
    
    if (!empty($errors)) {
        echo "\n❌ ERRORES:\n";
        foreach ($errors as $error) {
            echo "   - $error\n";
        }
    }
    
    echo "\n✨ Corrección completada.\n";
    
} catch (PDOException $e) {
    echo "❌ Error de base de datos: " . $e->getMessage() . "\n";
    exit(1);
}

// ============================================
// FUNCIONES
// ============================================

function findImageInCategories($fileName, $baseNoticiasPath)
{
    $directories = ['politica', 'deportes', 'economia', 'cultura', 'espectaculo', 'mundo', 'nacional', 'negocios', 'sociedad', 'noticias'];
    
    foreach ($directories as $dir) {
        $path = $baseNoticiasPath . '/' . $dir . '/' . $fileName;
        if (file_exists($path)) {
            return $path;
        }
        
        // También buscar en subdirectorios de fecha
        $yearMonthPath = $baseNoticiasPath . '/' . $dir . '/2025/11/' . $fileName;
        if (file_exists($yearMonthPath)) {
            return $yearMonthPath;
        }
    }
    
    return null;
}

function moveImageToCorrectLocation($sourcePath, $destPath, $categoryDir)
{
    global $errors;
    
    // Crear directorio si no existe
    $destDir = dirname($destPath);
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }
    
    // Mover archivo
    if (rename($sourcePath, $destPath)) {
        return true;
    } else {
        $errors[] = "Error moviendo " . basename($sourcePath) . ": " . error_get_last()['message'];
        return false;
    }
}
