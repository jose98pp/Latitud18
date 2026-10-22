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
        $noticia = Noticia::with('category')->where('id', $id)->publicadaActiva()->firstOrFail();
        return redirect()->to($noticia->url, 301);
    }

    public function showBySlug($categoria, $slug, $id)
    {
        $noticia = Noticia::with('category')->where('id', $id)->publicadaActiva()->firstOrFail();

        // Si la noticia es de la sección Deportes, redirigir a Contra Ataque para que tenga su diseño deportivo especializado
        if ($noticia->category?->es_deportiva) {
            $newsSlug = \Illuminate\Support\Str::slug($noticia->titulo) ?: 'noticia';
            return redirect()->route('contraataque.show', ['id' => $noticia->id, 'slug' => $newsSlug]);
        }

        // Incrementar contador de vistas
        $noticia->increment('views');

        $data = $this->newsService->getNewsDetailData($id);

        return view('show', $data);
    }


    public function noticiasPorCategoria($slug, Request $request)
    {
        $cleanSlug = strtolower(trim($slug));
        if (\App\Models\Category::deportivas()->get()->contains(fn ($category) => $category->slug === $cleanSlug || (string) $category->id === $cleanSlug)) {
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

    public function reportajes(Request $request)
    {
        $filters = $request->validate(['q' => 'nullable|string|max:150', 'categoria' => 'nullable|integer|exists:categories,id']);
        $query = Noticia::investigacion()->with('category');
        $search = trim($filters['q'] ?? '');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('titulo', 'LIKE', '%'.$search.'%')->orWhere('contenido', 'LIKE', '%'.$search.'%');
            });
        }
        if (!empty($filters['categoria'])) $query->where('category_id', $filters['categoria']);
        $reportajes = $query->orderByDesc('created_at')->orderByDesc('id')->paginate(12)->withQueryString();
        $categorias = Category::whereHas('noticias', fn ($q) => $q->investigacion())->orderBy('name')->get();
        return view('reportajes.index', compact('reportajes', 'categorias', 'search'));
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
