<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Noticia;

class CheckImages extends Command
{
    protected $signature = 'check:images';
    protected $description = 'Check image paths in database';

    public function handle()
    {
        $this->info('Revisando rutas de imágenes en la base de datos...');
        
        $noticias = Noticia::with('category')->take(10)->get(['id', 'titulo', 'imagen', 'category_id']);
        
        foreach ($noticias as $noticia) {
            $this->line("ID: {$noticia->id}");
            $this->line("Título: " . substr($noticia->titulo, 0, 50) . "...");
            $this->line("Imagen: " . ($noticia->imagen ?? 'NULL'));
            $this->line("Categoría: " . ($noticia->category->name ?? 'Sin categoría'));
            $this->line("---");
        }
        
        // Verificar diferentes patrones de rutas
        $this->info('Analizando patrones de rutas...');
        
        $patterns = [
            'noticias/' => Noticia::where('imagen', 'LIKE', 'noticias/%')->count(),
            'storage/' => Noticia::where('imagen', 'LIKE', 'storage/%')->count(),
            'images/' => Noticia::where('imagen', 'LIKE', 'images/%')->count(),
            'uploads/' => Noticia::where('imagen', 'LIKE', 'uploads/%')->count(),
            'Sin imagen' => Noticia::whereNull('imagen')->count(),
        ];
        
        foreach ($patterns as $pattern => $count) {
            $this->line("{$pattern}: {$count} noticias");
        }
        
        return 0;
    }
}