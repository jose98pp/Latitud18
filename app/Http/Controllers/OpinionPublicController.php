<?php

namespace App\Http\Controllers;

use App\Models\Columnista;
use App\Models\ArticuloOpinion;
use App\Models\Category;
use Illuminate\Http\Request;

class OpinionPublicController extends Controller
{
    public function index()
    {
        $columnistas = Columnista::activo()
            ->with(['articulos' => function ($q) {
                $q->where('publicado', true)->orderBy('created_at', 'desc')->take(3);
            }])
            ->get();

        $articulosRecientes = ArticuloOpinion::with('columnista')
            ->where('publicado', true)
            ->whereHas('columnista', fn($q) => $q->where('activo', true))
            ->orderBy('created_at', 'desc')
            ->paginate(9);

        $categorias = Category::all();

        return view('opinion.index', compact('columnistas', 'articulosRecientes', 'categorias'));
    }

    public function columnista($id)
    {
        $columnista = Columnista::where('activo', true)->findOrFail($id);
        $articulos = ArticuloOpinion::where('columnista_id', $id)
            ->where('publicado', true)
            ->orderBy('created_at', 'desc')
            ->paginate(8);

        $otrosColumnistas = Columnista::activo()->where('id', '!=', $id)->take(4)->get();
        $categorias = Category::all();

        return view('opinion.columnista', compact('columnista', 'articulos', 'otrosColumnistas', 'categorias'));
    }

    public function articulo($id)
    {
        $articulo = ArticuloOpinion::with('columnista')
            ->where('publicado', true)
            ->findOrFail($id);

        // Incrementar vistas
        $articulo->increment('vistas');

        // Otros artículos del mismo columnista
        $relacionados = ArticuloOpinion::with('columnista')
            ->where('publicado', true)
            ->where('id', '!=', $id)
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        $categorias = Category::all();

        return view('opinion.articulo', compact('articulo', 'relacionados', 'categorias'));
    }
}
