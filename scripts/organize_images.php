<?php

/**
 * Script para organizar imágenes de noticias por categorías
 * Elimina duplicados y actualiza rutas en la base de datos
 * 
 * Uso: php scripts/organize_images.php
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
$imageHashes = [];
$duplicatesFound = 0;
$imagesMoved = 0;
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

echo "🔍 Iniciando organización de imágenes de noticias...\n\n";

// Paso 1: Escanear imágenes y detectar duplicados
echo "📂 Escaneando imágenes en storage/app/public/noticias...\n";
scanNewsImages();

// Paso 2: Escanear backups para encontrar más duplicados
echo "\n📂 Escaneando imágenes en storage/app/public/backups/images...\n";
scanBackupImages();

// Paso 3: Organizar imágenes por categoría
echo "\n📁 Organizando imágenes por categorías...\n";
organizeImagesByCategory();

// Paso 4: Actualizar base de datos
echo "\n💾 Actualizando rutas en la base de datos...\n";
updateDatabasePaths();

// Paso 5: Eliminar duplicados
echo "\n🗑️  Eliminando imágenes duplicadas...\n";
removeDuplicates();

// Resumen
displaySummary();

// ============================================
// FUNCIONES
// ============================================

function scanNewsImages()
{
    global $imageHashes, $duplicatesFound;
    $newsPath = STORAGE_PATH . '/noticias';
    
    if (!is_dir($newsPath)) {
        echo "⚠️  Directorio noticias no encontrado: $newsPath\n";
        return;
    }

    scanDirectory($newsPath, 'noticias');
}

function scanBackupImages()
{
    global $imageHashes, $duplicatesFound;
    $backupPath = STORAGE_PATH . '/backups/images';
    
    if (!is_dir($backupPath)) {
        echo "⚠️  Directorio backups no encontrado: $backupPath\n";
        return;
    }

    scanDirectory($backupPath, 'backups');
}

function scanDirectory($directory, $source)
{
    global $imageHashes, $duplicatesFound;
    
    if (!is_dir($directory)) {
        return;
    }
    
    $items = scandir($directory);
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $path = $directory . DIRECTORY_SEPARATOR . $item;
        
        if (is_dir($path)) {
            scanDirectory($path, $source);
        } elseif (is_file($path) && isImageFile($path)) {
            processImageFile($path, $source);
        }
    }
}

function isImageFile($filePath)
{
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
}

function processImageFile($filePath, $source)
{
    global $imageHashes, $duplicatesFound;
    
    $hash = md5_file($filePath);
    
    if (!isset($imageHashes[$hash])) {
        $imageHashes[$hash] = [
            'original' => $filePath,
            'duplicates' => [],
            'source' => $source,
            'category' => detectCategoryFromPath($filePath)
        ];
    } else {
        $imageHashes[$hash]['duplicates'][] = $filePath;
        $duplicatesFound++;
        echo "   🔁 Duplicado encontrado: " . basename($filePath) . "\n";
    }
}

function detectCategoryFromPath($filePath)
{
    global $categoryMapping;
    
    // Normalizar ruta para detectar categoría
    $normalizedPath = str_replace('\\', '/', $filePath);
    
    foreach ($categoryMapping as $dir => $category) {
        if (strpos($normalizedPath, '/noticias/' . $dir . '/') !== false) {
            return $category;
        }
    }
    
    return 'Sociedad'; // Categoría por defecto
}

function organizeImagesByCategory()
{
    global $imageHashes, $imagesMoved, $errors, $categoryMapping;
    
    $baseNewsPath = STORAGE_PATH . '/noticias';
    
    // Crear estructura de directorios por categoría
    foreach ($categoryMapping as $dir => $category) {
        $categoryPath = $baseNewsPath . '/' . $dir;
        if (!is_dir($categoryPath)) {
            mkdir($categoryPath, 0755, true);
        }
    }

    foreach ($imageHashes as $hash => $imageInfo) {
        if (empty($imageInfo['duplicates'])) {
            // Solo mover si no es duplicado y está en la ubicación correcta
            moveImageToCategory($imageInfo['original'], $imageInfo['category']);
        }
    }
}

function moveImageToCategory($sourcePath, $category)
{
    global $imagesMoved, $errors, $categoryMapping, $imageHashes;
    
    $categoryDir = array_search($category, $categoryMapping);
    if ($categoryDir === false) {
        $categoryDir = 'noticias';
    }

    $destDir = STORAGE_PATH . '/noticias/' . $categoryDir;
    $fileName = basename($sourcePath);
    $destPath = $destDir . '/' . $fileName;

    // Verificar si ya está en el lugar correcto
    $normalizedSource = str_replace('\\', '/', $sourcePath);
    $normalizedDest = str_replace('\\', '/', $destPath);
    
    if ($normalizedSource === $normalizedDest) {
        return; // Ya está en el lugar correcto
    }

    // Crear directorio si no existe
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }

    // Mover archivo
    if (rename($sourcePath, $destPath)) {
        $imagesMoved++;
        echo "   ✅ Movido: $fileName -> $categoryDir/\n";
        
        // Actualizar la ruta en el array de hashes
        foreach ($imageHashes as $hash => &$info) {
            if ($info['original'] === $sourcePath) {
                $info['original'] = $destPath;
            }
            foreach ($info['duplicates'] as $key => $dup) {
                if ($dup === $sourcePath) {
                    $info['duplicates'][$key] = $destPath;
                }
            }
        }
    } else {
        $errors[] = "Error moviendo $fileName: " . error_get_last()['message'];
    }
}

function updateDatabasePaths()
{
    global $dbUpdated, $errors, $categoryMapping;
    
    try {
        // Conectar a la base de datos
        $envFile = BASE_PATH . '/.env';
        $envConfig = parse_ini_file($envFile);
        
        $dbHost = $envConfig['DB_HOST'] ?? 'localhost';
        $dbDatabase = $envConfig['DB_DATABASE'] ?? '';
        $dbUsername = $envConfig['DB_USERNAME'] ?? '';
        $dbPassword = $envConfig['DB_PASSWORD'] ?? '';
        
        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbDatabase;charset=utf8mb4", $dbUsername, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Obtener categorías de la base de datos
        $categoryStmt = $pdo->query("SELECT id, name FROM categories");
        $categories = [];
        while ($row = $categoryStmt->fetch(PDO::FETCH_ASSOC)) {
            $categories[$row['id']] = $row['name'];
        }
        
        // Obtener noticias con imágenes
        $stmt = $pdo->query("SELECT id, imagen, category_id FROM noticias WHERE imagen IS NOT NULL AND imagen != ''");
        
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
            }
        }
        
    } catch (PDOException $e) {
        $errors[] = "Error de base de datos: " . $e->getMessage();
    }
}

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
    $fullPath = STORAGE_PATH . '/' . $newPath;
    
    if (file_exists($fullPath)) {
        return $newPath;
    }
    
    return null;
}

function removeDuplicates()
{
    global $imageHashes, $errors;
    
    foreach ($imageHashes as $hash => $imageInfo) {
        if (!empty($imageInfo['duplicates'])) {
            foreach ($imageInfo['duplicates'] as $duplicate) {
                if (file_exists($duplicate)) {
                    if (unlink($duplicate)) {
                        echo "   🗑️  Eliminado duplicado: " . basename($duplicate) . "\n";
                    } else {
                        $errors[] = "Error eliminando " . basename($duplicate);
                    }
                }
            }
        }
    }
}

function displaySummary()
{
    global $imagesMoved, $duplicatesFound, $dbUpdated, $errors;
    
    echo "\n";
    echo "📊 RESUMEN DE LA OPERACIÓN:\n";
    echo "================================\n";
    echo "✅ Imágenes movidas: $imagesMoved\n";
    echo "🔁 Duplicados encontrados: $duplicatesFound\n";
    echo "📝 Rutas actualizadas en BD: $dbUpdated\n";
    
    if (!empty($errors)) {
        echo "\n❌ ERRORES:\n";
        foreach ($errors as $error) {
            echo "   - $error\n";
        }
    }
    
    echo "\n✨ Organización completada.\n";
}
