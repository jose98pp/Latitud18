<?php

namespace App\Repositories;

use App\Models\Noticia;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class NoticiaRepository
{
    /**
     * Obtener noticias publicadas con paginación opcional
     */
    public function getPublishedNews($limit = null, $paginate = false)
    {
        $query = Noticia::publicadaActiva()
            ->orderBy('created_at', 'desc');

        if ($paginate) {
            return $query->paginate($limit ?? 10);
        }

        return $limit ? $query->take($limit)->get() : $query->get();
    }

    /**
     * Obtener noticias por categoría
     */
    public function getNewsByCategory($categoryId, $limit = null)
    {
        $query = Noticia::publicadaActiva()
            ->where('category_id', $categoryId)
            ->orderBy('created_at', 'desc');

        return $limit ? $query->take($limit)->get() : $query->get();
    }

    /**
     * Obtener noticias excluyendo una categoría específica
     */
    public function getNewsExcludingCategory($categoryId, $limit = 6)
    {
        return Noticia::publicadaActiva()
            ->where('category_id', '!=', $categoryId)
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Obtener noticias excluyendo una noticia específica
     */
    public function getRelatedNews($excludeId, $limit = 5)
    {
        $noticias = Noticia::select('id', 'titulo', 'imagen', 'contenido', 'created_at')
            ->publicadaActiva()
            ->where('id', '!=', $excludeId)
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
            
        // Ensure contenido is not null for any related news
        $noticias->each(function ($noticia) {
            if ($noticia->contenido === null) {
                $noticia->contenido = '';
            }
        });
        
        return $noticias;
    }

    /**
     * Obtener una noticia por ID (solo publicadas)
     */
    public function getPublishedNewsById($id)
    {
        $noticia = Noticia::with('galeria')
            ->publicadaActiva()
            ->where('id', $id)
            ->firstOrFail();
            
        // Ensure contenido is not null
        if ($noticia->contenido === null) {
            $noticia->contenido = '';
        }
        
        return $noticia;
    }

    /**
     * Obtener categorías con sus noticias (optimizado para portada)
     */
    public function getCategoriesWithNews($newsLimit = 1)
    {
        return Category::with(['noticias' => function ($query) use ($newsLimit) {
            $query->select('id', 'titulo', 'imagen', 'contenido', 'category_id', 'created_at')
                  ->publicadaActiva()
                  ->orderBy('created_at', 'desc')
                  ->take($newsLimit);
        }])
        ->whereHas('noticias', function ($query) {
            $query->publicadaActiva();
        })
        ->orderBy('name', 'asc')
        ->take(8)
        ->get();
    }

    /**
     * Buscar noticias por término
     */
    public function searchNews($term, $limit = 10)
    {
        return Noticia::publicadaActiva()
            ->where(function ($query) use ($term) {
                $query->where('titulo', 'LIKE', "%{$term}%")
                      ->orWhere('contenido', 'LIKE', "%{$term}%");
            })
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Obtener estadísticas de noticias
     */
    public function getNewsStats()
    {
        try {
            return [
                'total_published' => Noticia::publicadaActiva()->count(),
                'total_draft' => Noticia::where('publicada', false)->count(),
                'total_scheduled' => Noticia::where('publicada', true)->where('publicar_en', '>', now())->count(),
                'total_categories' => Category::count(),
                'recent_news' => Noticia::where('created_at', '>=', now()->subDays(7))->count(),
            ];
        } catch (\Exception $e) {
            return [
                'total_published' => 0,
                'total_draft' => 0,
                'total_scheduled' => 0,
                'total_categories' => 0,
                'recent_news' => 0
            ];
        }
    }

    /**
     * Obtener noticias más vistas
     */
    public function getMostViewedNews($limit = 5)
    {
        return Noticia::publicadaActiva()
            ->orderBy('views', 'desc')
            ->take($limit)
            ->get();
    }
}