<?php

namespace App\Http\Controllers;

use App\Models\Noticia;
use App\Models\Category;
use App\Services\NewsService;
use Illuminate\Http\Request;

class PortadaController extends Controller
{
    public function __construct(
        private NewsService $newsService
    ) {}

    public function index(Request $request)
    {
        $data = $this->newsService->getHomePageData();
        
        return view('portada', $data);
    }

    

    public function show($id)
    {
        $noticia = Noticia::with('category')->where('id', $id)->where('publicada', true)->firstOrFail();
        return redirect()->to($noticia->url, 301);
    }

    public function showBySlug($categoria, $slug, $id)
    {
        // Incrementar contador de vistas
        $noticia = Noticia::where('id', $id)->where('publicada', true)->firstOrFail();
        $noticia->increment('views');

        $data = $this->newsService->getNewsDetailData($id);

        return view('show', $data);
    }


    public function noticiasPorCategoria($slug, Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $data = $this->newsService->getCategoryPageData($slug, $perPage);
        
        // Si el parámetro recibido fue numérico, redirigir 301 a la URL canónica con slug
        if (is_numeric($slug) && isset($data['categoria'])) {
            $canonicalSlug = $data['categoria']->slug;
            if ($canonicalSlug && $canonicalSlug !== (string) $slug) {
                return redirect()->route('categoria.noticias', $canonicalSlug, 301);
            }
        }
        
        return view('categoria.noticias', $data);
    }

    public function search(Request $request)
    {
        $query = trim($request->get('q', ''));
        $perPage = (int) $request->get('per_page', 12);
        
        if (empty($query)) {
            return redirect()->route('portada')->with('error', 'Por favor ingresa un término de búsqueda.');
        }

        // Buscar noticias optimizado con FullText
        $noticias = $this->newsService->searchPaginatedNews($query, $perPage);

        // Procesar noticias con el servicio
        $noticias->getCollection()->transform(function ($noticia) {
            return $this->newsService->processNewsItem($noticia);
        });

        // Obtener categorías para el layout
        $categorias = Category::all();

        return view('search', [
            'noticias' => $noticias,
            'categorias' => $categorias,
            'query' => $query,
            'total' => $noticias->total()
        ]);
    }
}
