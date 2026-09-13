<?php

namespace App\Services;

use App\Repositories\NoticiaRepository;
use App\Services\ImageValidationService;
use App\Services\ContentSanitizationService;
use App\Models\Category;
use App\Models\Noticia;
use Illuminate\Support\Facades\Cache;

class NewsService
{
    public function __construct(
        private NoticiaRepository $noticiaRepository,
        private ImageValidationService $imageValidationService,
        private ContentSanitizationService $contentSanitizationService
    ) {}

    /**
     * Obtener datos para la página principal
     */
    public function getHomePageData()
    {
        return Cache::remember('homepage_data', 300, function () {
            // 1. Obtener noticias para el carrusel principal (priorizar marcadas como destacada_hero)
            $heroNoticias = Noticia::hero()
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            // Si hay menos de 5 marcadas para el hero, completar con las más recientes
            if ($heroNoticias->count() < 5) {
                $excludeIds = $heroNoticias->pluck('id')->toArray();
                $fillerNews = Noticia::publicadaActiva()
                    ->whereNotIn('id', $excludeIds)
                    ->orderBy('created_at', 'desc')
                    ->take(5 - $heroNoticias->count())
                    ->get();
                $heroNoticias = $heroNoticias->merge($fillerNews);
            }

            // Procesar noticias del hero
            $noticias = $heroNoticias->map(function ($noticia) {
                return $this->processNewsItem($noticia);
            });

            // 2. Noticia de Investigación (LATITUD 18 INVESTIGA)
            $noticiaInvestigacion = Noticia::investigacion()
                ->orderBy('created_at', 'desc')
                ->first();

            if ($noticiaInvestigacion) {
                $noticiaInvestigacion = $this->processNewsItem($noticiaInvestigacion);
            }

            // 3. Ticker de Última Hora (priorizar marcadas como urgentes activas con vigencia)
            $urgentes = Noticia::urgenteActivo()
                ->orderBy('created_at', 'desc')
                ->take(8)
                ->get();
            
            $excludeTickerIds = $urgentes->pluck('id')->toArray();
            $recientes = Noticia::publicadaActiva()
                ->whereNotIn('id', $excludeTickerIds)
                ->orderBy('created_at', 'desc')
                ->take(15 - $urgentes->count())
                ->get();

            $ultimasNoticias = $urgentes->merge($recientes)->map(function ($noticia) {
                return $this->processNewsItem($noticia);
            });

            // 4. Obtener categorías con múltiples noticias
            $categorias = $this->noticiaRepository->getCategoriesWithNews(6);
            foreach ($categorias as $categoria) {
                if ($categoria->noticias) {
                    $categoria->noticias = $categoria->noticias->map(function ($noticia) {
                        return $this->processNewsItem($noticia);
                    });
                }
            }

            // 5. Noticias por categoría individual
            $noticiasPorCategoria = [];
            foreach ($categorias as $categoria) {
                $noticiasCategoria = $this->noticiaRepository->getNewsByCategory($categoria->id, 6);
                $noticiasPorCategoria[$categoria->id] = $noticiasCategoria->map(function ($noticia) {
                    return $this->processNewsItem($noticia);
                });
            }

            // 6. Más leídas (5 noticias más vistas para los puestos 01 al 05)
            $masLeidas = $this->noticiaRepository->getMostViewedNews(5);
            $masLeidas = $masLeidas->map(function ($noticia) {
                return $this->processNewsItem($noticia);
            });

            // 7. Artículos de Opinión Reales con Columnistas
            $articulosOpinion = \App\Models\ArticuloOpinion::with('columnista')
                ->where('publicado', true)
                ->whereHas('columnista', function($q) {
                    $q->where('activo', true);
                })
                ->orderBy('created_at', 'desc')
                ->take(4)
                ->get();

            // 8. Noticias con Video (LATITUD 18 TV)
            $noticiasConVideo = Noticia::publicadaActiva()
                ->whereNotNull('video_youtube')
                ->where('video_youtube', '!=', '')
                ->orderBy('created_at', 'desc')
                ->take(6)
                ->get()
                ->map(function ($noticia) {
                    return $this->processNewsItem($noticia);
                });

            // 9. Banners Publicitarios Activos
            $banners = \App\Models\Banner::where('active', true)
                ->orderBy('position', 'asc')
                ->get()
                ->groupBy('location');

            return [
                'noticias' => $noticias,
                'noticiaInvestigacion' => $noticiaInvestigacion,
                'categorias' => $categorias,
                'ultimasNoticias' => $ultimasNoticias,
                'noticiasPorCategoria' => $noticiasPorCategoria,
                'masLeidas' => $masLeidas,
                'articulosOpinion' => $articulosOpinion,
                'noticiasConVideo' => $noticiasConVideo,
                'banners' => $banners,
            ];
        });
    }

    /**
     * Obtener datos para página de categoría con paginación (soporta Slug e ID)
     */
    public function getCategoryPageData($categoryIdentifier, $perPage = 10)
    {
        // Obtener la categoría por ID o por Slug
        if (is_numeric($categoryIdentifier)) {
            $categoria = Category::find($categoryIdentifier);
        } else {
            $categoria = Category::all()->first(function ($cat) use ($categoryIdentifier) {
                return \Illuminate\Support\Str::slug($cat->name) === $categoryIdentifier;
            });
            if (!$categoria) {
                $categoria = Category::where('name', 'LIKE', str_replace('-', ' ', $categoryIdentifier))->first();
            }
        }

        if (!$categoria) {
            abort(404, 'Categoría no encontrada');
        }

        $categoryId = $categoria->id;
        
        // Obtener noticias de la categoría con paginación
        $noticiasCategoria = Noticia::where('category_id', $categoryId)
            ->publicadaActiva()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        // Procesar noticias de la categoría con contenido sanitizado e imágenes seguras
        $noticiasCategoria->getCollection()->transform(function ($noticia) {
            return $this->processNewsItem($noticia);
        });
        
        // Obtener noticias relacionadas (otras categorías)
        $noticias = $this->noticiaRepository->getNewsExcludingCategory($categoryId, 6);
        
        // Procesar noticias relacionadas con contenido sanitizado e imágenes seguras
        $noticias = $noticias->map(function ($noticia) {
            return $this->processNewsItem($noticia);
        });
        
        // Obtener todas las categorías para navegación
        $categorias = Cache::remember('all_categories', 600, function () {
            return Category::all();
        });

        return compact('categoria', 'noticiasCategoria', 'noticias', 'categorias');
    }

    /**
     * Obtener datos para página de detalle de noticia
     */
    public function getNewsDetailData($newsId)
    {
        $noticia = $this->noticiaRepository->getPublishedNewsById($newsId);
        
        // Procesar noticia principal con contenido sanitizado e imagen segura
        $noticia = $this->processNewsItem($noticia);
        $imagenUrl = $noticia->imagenUrl;
        
        // Obtener y procesar noticias relacionadas
        $noticias = $this->noticiaRepository->getRelatedNews($newsId, 5);
        $noticias = $noticias->map(function ($noticia) {
            return $this->processNewsItem($noticia);
        });
        
        $categorias = Cache::remember('all_categories', 600, function () {
            return Category::all();
        });

        return compact('noticia', 'noticias', 'categorias', 'imagenUrl');
    }

    /**
     * Obtener URL segura de imagen (método legacy - usa ImageValidationService internamente)
     * 
     * @deprecated Usar ImageValidationService::getImageUrlOrDefault() directamente
     */
    public function getSecureImageUrl($imagePath)
    {
        return $this->imageValidationService->getImageUrlOrDefault($imagePath);
    }

    /**
     * Buscar noticias
     */
    public function searchNews($term, $limit = 10)
    {
        if (empty(trim($term))) {
            return collect();
        }

        return $this->noticiaRepository->searchNews($term, $limit);
    }

    /**
     * Buscar noticias paginadas con FullText
     */
    public function searchPaginatedNews($term, $perPage = 12)
    {
        if (empty(trim($term))) {
            return \App\Models\Noticia::whereRaw('1 = 0')->paginate($perPage);
        }

        return $this->noticiaRepository->searchPaginatedNews($term, $perPage);
    }

    /**
     * Obtener estadísticas del dashboard
     */
    public function getDashboardStats()
    {
        try {
            return Cache::remember('dashboard_stats', 300, function () {
                return $this->noticiaRepository->getNewsStats();
            });
        } catch (\Exception $e) {
            return [
                'total_published' => 0,
                'total_draft' => 0,
                'total_categories' => 0,
                'recent_news' => 0
            ];
        }
    }

    /**
     * Get sanitized content for display
     */
    public function getSanitizedContent(?string $content): string
    {
        if (empty($content)) {
            return '';
        }
        
        return $this->contentSanitizationService->processRichTextContent($content);
    }
    
    /**
     * Get clean excerpt for previews
     */
    public function getCleanExcerpt(?string $content, int $length = 200): string
    {
        if (empty($content)) {
            return '';
        }
        
        return $this->contentSanitizationService->getCleanExcerpt($content, $length);
    }
    
    /**
     * Process news item with sanitized content and secure image URL
     */
    public function processNewsItem($noticia): object
    {
        $noticia->imagenUrl = $this->imageValidationService->getImageUrlOrDefault($noticia->imagen);
        $noticia->contenidoSanitizado = $this->getSanitizedContent($noticia->contenido ?? '');
        $noticia->excerptLimpio = $this->getCleanExcerpt($noticia->contenido ?? '', 120);
        return $noticia;
    }
    
    /**
     * Limpiar caché relacionado con noticias
     */
    public function clearNewsCache()
    {
        Cache::forget('homepage_data');
        Cache::forget('all_categories');
        Cache::forget('dashboard_stats');
        
        // Limpiar caché de categorías específicas si es necesario
        $categories = Category::all();
        foreach ($categories as $category) {
            Cache::forget("category_{$category->id}_data");
        }
    }
}