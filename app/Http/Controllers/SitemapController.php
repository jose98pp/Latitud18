<?php

namespace App\Http\Controllers;

use App\Models\Noticia;
use App\Models\Category;
use App\Models\ArticuloOpinion;
use App\Models\Columnista;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Genera el mapa del sitio sitemap.xml
     */
    public function index(): Response
    {
        $noticias = Noticia::with('category')
            ->where('publicada', true)
            ->orderBy('updated_at', 'desc')
            ->get();

        $categorias = Category::all();

        $articulosOpinion = ArticuloOpinion::where('publicado', true)
            ->orderBy('updated_at', 'desc')
            ->get();

        $columnistas = Columnista::where('activo', true)->get();

        $xml = view('sitemap', compact('noticias', 'categorias', 'articulosOpinion', 'columnistas'))->render();

        return response($xml, 200)
            ->header('Content-Type', 'application/xml');
    }
}
