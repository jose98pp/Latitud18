<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar helpers globales
        if (file_exists(app_path('Helpers/helpers.php'))) {
            require_once app_path('Helpers/helpers.php');
        }

        // Registrar Repository
        $this->app->bind(
            \App\Repositories\NoticiaRepository::class,
            \App\Repositories\NoticiaRepository::class
        );

        // Registrar Service
        $this->app->bind(
            \App\Services\NewsService::class,
            \App\Services\NewsService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Compartir banners y categorías con todas las vistas (solo en peticiones web, no en consola/tests)
        if (!$this->app->runningInConsole()) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('banners')) {
                    $banners = \App\Models\Banner::active()
                        ->orderBy('position')
                        ->get()
                        ->groupBy('location');
                    
                    \Illuminate\Support\Facades\View::share('banners', $banners);
                }

                if (\Illuminate\Support\Facades\Schema::hasTable('categories')) {
                    \Illuminate\Support\Facades\View::composer(
                        ['components.navbar', 'components.footer', 'layouts.main', 'portada', 'categoria.noticias'],
                        function ($view) {
                            $navCategorias = \Illuminate\Support\Facades\Cache::remember('site_nav_categories', 3600, function () {
                                return \App\Models\Category::orderBy('name', 'asc')->get();
                            });
                            $view->with('navCategorias', $navCategorias);
                        }
                    );
                }
            } catch (\Exception $e) {
                // Si falla (ej. durante migración), no detener la app
                \Illuminate\Support\Facades\Log::error('Error loading shared view data: ' . $e->getMessage());
                \Illuminate\Support\Facades\View::share('banners', collect());
            }
        }
    }
}
