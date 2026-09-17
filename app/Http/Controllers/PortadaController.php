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
        $noticia = Noticia::with('category')->where('id', $id)->where('publicada', true)->firstOrFail();

        // Si la noticia es de la sección Deportes, redirigir a Contra Ataque para que tenga su diseño deportivo especializado
        $catName = strtolower($noticia->category->name ?? '');
        $catSlug = strtolower($categoria ?? '');
        if (str_contains($catName, 'deport') || str_contains($catName, 'futbol') || str_contains($catSlug, 'deport') || str_contains($catSlug, 'futbol') || $noticia->category_id == 9) {
            return redirect()->route('contraataque.show', $noticia->id);
        }

        // Incrementar contador de vistas
        $noticia->increment('views');

        $data = $this->newsService->getNewsDetailData($id);

        return view('show', $data);
    }


    public function noticiasPorCategoria($slug, Request $request)
    {
        $cleanSlug = strtolower(trim($slug));
        if ($cleanSlug === 'deportes' || $cleanSlug === 'futbol' || str_contains($cleanSlug, 'deport')) {
            return redirect()->route('contraataque.index');
        }

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
