<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NewsService;

class ClearNewsCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:clear-cache {--all : Clear all cache including application cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpiar caché de noticias y categorías';

    /**
     * Execute the console command.
     */
    public function handle(NewsService $newsService)
    {
        $this->info('🧹 Limpiando caché de noticias...');
        
        // Limpiar caché específico de noticias
        $newsService->clearNewsCache();
        $this->info('✅ Caché de noticias limpiado');
        
        if ($this->option('all')) {
            $this->info('🧹 Limpiando todo el caché de la aplicación...');
            $this->call('cache:clear');
            $this->call('config:clear');
            $this->call('route:clear');
            $this->call('view:clear');
            $this->info('✅ Todo el caché de la aplicación limpiado');
        }
        
        $this->info('🎉 ¡Proceso completado exitosamente!');
        
        return Command::SUCCESS;
    }
}
