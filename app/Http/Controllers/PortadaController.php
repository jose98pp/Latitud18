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


    public function noticiasPorCategoria($id, Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10); // Permitir personalizar items por página
            $data = $this->newsService->getCategoryPageData($id, $perPage);
            
            // Verificar que tenemos los datos necesarios
            if (!isset($data['categoria']) || !isset($data['noticiasCategoria']) || !isset($data['categorias'])) {
                \Log::error('Datos faltantes en getCategoryPageData', $data);
                return redirect()->route('portada')->with('error', 'Error al cargar la categoría.');
            }
            
            return view('categoria.noticias', $data);
            
        } catch (\Exception $e) {
            \Log::error('Error en noticiasPorCategoria', [
                'category_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('portada')->with('error', 'La categoría solicitada no existe.');
        }
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $perPage = $request->get('per_page', 12);
        
        if (empty($query)) {
            return redirect()->route('portada')->with('error', 'Por favor ingresa un término de búsqueda.');
        }

        // Buscar noticias
        $noticias = Noticia::where('publicada', true)
            ->where(function ($q) use ($query) {
                $q->where('titulo', 'LIKE', "%{$query}%")
                  ->orWhere('contenido', 'LIKE', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

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
