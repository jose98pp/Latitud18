<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NewsService;
use App\Models\Noticia;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private NewsService $newsService
    ) {}

    public function index()
    {
        try {
            // Obtener estadísticas
            $stats = $this->newsService->getDashboardStats();
        } catch (\Exception $e) {
            $stats = [
                'total_published' => 0,
                'total_draft' => 0,
                'total_categories' => 0,
                'recent_news' => 0
            ];
        }
        
        try {
            // Obtener noticias recientes
            $recentNews = Noticia::with('category')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
            
            // Procesar cada noticia para obtener imagenUrl
            $recentNews = $recentNews->map(function ($noticia) {
                return $this->newsService->processNewsItem($noticia);
            });
        } catch (\Exception $e) {
            $recentNews = collect();
        }
        
        return view('admin.dashboard', compact('stats', 'recentNews'));
    }
}
