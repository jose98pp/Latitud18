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
     * Buscar noticias por término con optimización FullText y fallback
     */
    public function searchNews($term, $limit = 10)
    {
        $term = trim($term);
        if (empty($term)) {
            return collect();
        }

        preg_match_all('/[\p{L}\p{N}_]+/u', $term, $matches);
        $words = $matches[0] ?? [];
        $validWords = array_filter($words, fn($w) => mb_strlen($w) >= 3);

        $query = Noticia::publicadaActiva()->with('category');

        if (!empty($validWords)) {
            $booleanQuery = implode(' ', array_map(fn($w) => '+' . $w . '*', $validWords));
            try {
                $results = (clone $query)->whereRaw(
                    "MATCH(titulo, contenido) AGAINST(? IN BOOLEAN MODE)",
                    [$booleanQuery]
                )->selectRaw(
                    "noticias.*, (MATCH(titulo) AGAINST(? IN BOOLEAN MODE) * 2 + MATCH(contenido) AGAINST(? IN BOOLEAN MODE)) AS search_relevance",
                    [$booleanQuery, $booleanQuery]
                )->orderByDesc('search_relevance')
                ->orderByDesc('created_at')
                ->take($limit)
                ->get();

                if ($results->isNotEmpty()) {
                    return $results;
                }
            } catch (\Throwable $e) {
                \Log::warning('Fulltext searchNews fallback: ' . $e->getMessage());
            }
        }

        return $query->where(function ($q) use ($term) {
            $q->where('titulo', 'LIKE', "%{$term}%")
              ->orWhere('contenido', 'LIKE', "%{$term}%");
        })
        ->orderBy('created_at', 'desc')
        ->take($limit)
        ->get();
    }

    /**
     * Buscar noticias paginadas por término con optimización FullText y ranking
     */
    public function searchPaginatedNews($term, $perPage = 12)
    {
        $term = trim($term);
        if (empty($term)) {
            return Noticia::whereRaw('1 = 0')->paginate($perPage);
        }

        preg_match_all('/[\p{L}\p{N}_]+/u', $term, $matches);
        $words = $matches[0] ?? [];
        $validWords = array_filter($words, fn($w) => mb_strlen($w) >= 3);

        $query = Noticia::publicadaActiva()->with('category');

        if (!empty($validWords)) {
            $booleanQuery = implode(' ', array_map(fn($w) => '+' . $w . '*', $validWords));
            try {
                $fulltextQuery = (clone $query)->whereRaw(
                    "MATCH(titulo, contenido) AGAINST(? IN BOOLEAN MODE)",
                    [$booleanQuery]
                )->selectRaw(
                    "noticias.*, (MATCH(titulo) AGAINST(? IN BOOLEAN MODE) * 2 + MATCH(contenido) AGAINST(? IN BOOLEAN MODE)) AS search_relevance",
                    [$booleanQuery, $booleanQuery]
                )->orderByDesc('search_relevance')
                ->orderByDesc('created_at');

                if ((clone $fulltextQuery)->count() > 0) {
                    return $fulltextQuery->paginate($perPage);
                }
            } catch (\Throwable $e) {
                \Log::warning('Fulltext searchPaginatedNews fallback: ' . $e->getMessage());
            }
        }

        return $query->where(function ($q) use ($term, $words) {
            $q->where('titulo', 'LIKE', "%{$term}%")
              ->orWhere('contenido', 'LIKE', "%{$term}%");
            foreach ($words as $word) {
                if (mb_strlen($word) >= 3) {
                    $q->orWhere('titulo', 'LIKE', "%{$word}%");
                }
            }
        })
        ->orderByDesc('created_at')
        ->paginate($perPage);
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