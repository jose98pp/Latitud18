<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NewsService;
use App\Models\Category;

class TestCategoryData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:category-data {categoryId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar datos de categoría para debug';

    /**
     * Execute the console command.
     */
    public function handle(NewsService $newsService)
    {
        $categoryId = $this->argument('categoryId') ?: Category::first()->id;
        
        $this->info("🔍 Probando datos para categoría ID: {$categoryId}");
        
        try {
            $data = $newsService->getCategoryPageData($categoryId);
            
            $this->info("✅ Datos obtenidos exitosamente:");
            $this->info("📂 Categoría: " . $data['categoria']->name);
            $this->info("📰 Noticias de la categoría: " . $data['categoria']->noticias->count());
            $this->info("🔗 Noticias relacionadas: " . $data['noticias']->count());
            $this->info("📋 Total categorías: " . $data['categorias']->count());
            
            if ($data['categoria']->noticias->count() > 0) {
                $this->info("📝 Primera noticia: " . $data['categoria']->noticias->first()->titulo);
            }
            
            if ($data['noticias']->count() > 0) {
                $this->info("🔗 Primera noticia relacionada: " . $data['noticias']->first()->titulo);
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            $this->error("📍 Archivo: " . $e->getFile() . ":" . $e->getLine());
        }
    }
}
