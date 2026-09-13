<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Iniciando organización de imágenes...\n";

// Usamos el ImageStorageService
$service = $app->make(\App\Services\ImageStorageService::class);

try {
    $resultados = $service->migrateExistingImages(false); // false = no dry-run
    echo json_encode($resultados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    echo "Organización completada!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
