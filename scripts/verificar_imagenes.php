<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Verificando imágenes...\n";

$noticias = \App\Models\Noticia::with('category')
    ->whereNotNull('imagen')
    ->where('imagen', '!=', '')
    ->limit(10)
    ->get();

foreach ($noticias as $noticia) {
    echo "ID: {$noticia->id}, Título: {$noticia->titulo}\n";
    echo "  Imagen actual: {$noticia->imagen}\n";
    echo "  Categoría: " . ($noticia->category ? $noticia->category->name : 'Sin categoría') . "\n";
    $rutaCompleta = public_path('storage/' . $noticia->imagen);
    echo "  Existe archivo: " . (file_exists($rutaCompleta) ? 'SI' : 'NO') . "\n";
    echo "---\n";
}

echo "Total de noticias con imagen: " . \App\Models\Noticia::whereNotNull('imagen')->where('imagen', '!=', '')->count() . "\n";
