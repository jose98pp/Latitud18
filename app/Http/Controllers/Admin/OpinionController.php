<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Columnista;
use App\Models\ArticuloOpinion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OpinionController extends Controller
{
    public function index()
    {
        $columnistas = Columnista::orderBy('orden', 'asc')->withCount('articulos')->get();
        $articulos = ArticuloOpinion::with('columnista')->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.opinion.index', compact('columnistas', 'articulos'));
    }

    // ==========================================
    // GESTIÓN DE COLUMNISTAS
    // ==========================================

    public function createColumnista()
    {
        return view('admin.opinion.columnista_form', [
            'columnista' => new Columnista(),
            'isEdit' => false
        ]);
    }

    public function storeColumnista(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'cargo' => 'nullable|string|max:150',
            'bio' => 'nullable|string',
            'orden' => 'nullable|integer',
            'avatar_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'avatar_url' => 'nullable|url',
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar_file')) {
            $file = $request->file('avatar_file');
            $filename = time() . '_' . Str::slug($request->nombre) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/columnistas'), $filename);
            $avatarPath = 'images/columnistas/' . $filename;
        } elseif ($request->filled('avatar_url')) {
            $avatarPath = $request->avatar_url;
        }

        Columnista::create([
            'nombre' => $validated['nombre'],
            'cargo' => $validated['cargo'],
            'bio' => $validated['bio'],
            'orden' => $validated['orden'] ?? 0,
            'activo' => $request->has('activo'),
            'avatar' => $avatarPath,
        ]);

        return redirect()->route('admin.opinion.index')->with('success', 'Columnista creado exitosamente.');
    }

    public function editColumnista($id)
    {
        $columnista = Columnista::findOrFail($id);
        return view('admin.opinion.columnista_form', [
            'columnista' => $columnista,
            'isEdit' => true
        ]);
    }

    public function updateColumnista(Request $request, $id)
    {
        $columnista = Columnista::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'cargo' => 'nullable|string|max:150',
            'bio' => 'nullable|string',
            'orden' => 'nullable|integer',
            'avatar_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'avatar_url' => 'nullable|url',
        ]);

        if ($request->hasFile('avatar_file')) {
            $file = $request->file('avatar_file');
            $filename = time() . '_' . Str::slug($request->nombre) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/columnistas'), $filename);
            $columnista->avatar = 'images/columnistas/' . $filename;
        } elseif ($request->filled('avatar_url')) {
            $columnista->avatar = $request->avatar_url;
        }

        $columnista->update([
            'nombre' => $validated['nombre'],
            'cargo' => $validated['cargo'],
            'bio' => $validated['bio'],
            'orden' => $validated['orden'] ?? 0,
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('admin.opinion.index')->with('success', 'Columnista actualizado exitosamente.');
    }

    public function destroyColumnista($id)
    {
        $columnista = Columnista::findOrFail($id);
        $columnista->delete();

        return redirect()->route('admin.opinion.index')->with('success', 'Columnista eliminado.');
    }

    // ==========================================
    // GESTIÓN DE ARTÍCULOS DE OPINIÓN
    // ==========================================

    public function createArticulo()
    {
        $columnistas = Columnista::orderBy('nombre')->get();
        return view('admin.opinion.articulo_form', [
            'articulo' => new ArticuloOpinion(),
            'columnistas' => $columnistas,
            'isEdit' => false
        ]);
    }

    public function storeArticulo(Request $request)
    {
        $validated = $request->validate([
            'columnista_id' => 'required|exists:columnistas,id',
            'tipo' => 'required|string|in:EDITORIAL,COLUMNA,ANÁLISIS,COMENTARIO',
            'titulo' => 'required|string|max:255',
            'contenido' => 'required|string',
        ]);

        ArticuloOpinion::create([
            'columnista_id' => $validated['columnista_id'],
            'tipo' => $validated['tipo'],
            'titulo' => $validated['titulo'],
            'contenido' => $validated['contenido'],
            'publicado' => $request->has('publicado'),
        ]);

        return redirect()->route('admin.opinion.index')->with('success', 'Artículo de opinión publicado con éxito.');
    }

    public function editArticulo($id)
    {
        $articulo = ArticuloOpinion::findOrFail($id);
        $columnistas = Columnista::orderBy('nombre')->get();

        return view('admin.opinion.articulo_form', [
            'articulo' => $articulo,
            'columnistas' => $columnistas,
            'isEdit' => true
        ]);
    }

    public function updateArticulo(Request $request, $id)
    {
        $articulo = ArticuloOpinion::findOrFail($id);

        $validated = $request->validate([
            'columnista_id' => 'required|exists:columnistas,id',
            'tipo' => 'required|string|in:EDITORIAL,COLUMNA,ANÁLISIS,COMENTARIO',
            'titulo' => 'required|string|max:255',
            'contenido' => 'required|string',
        ]);

        $articulo->update([
            'columnista_id' => $validated['columnista_id'],
            'tipo' => $validated['tipo'],
            'titulo' => $validated['titulo'],
            'contenido' => $validated['contenido'],
            'publicado' => $request->has('publicado'),
        ]);

        return redirect()->route('admin.opinion.index')->with('success', 'Artículo de opinión actualizado.');
    }

    public function destroyArticulo($id)
    {
        $articulo = ArticuloOpinion::findOrFail($id);
        $articulo->delete();

        return redirect()->route('admin.opinion.index')->with('success', 'Artículo de opinión eliminado.');
    }
}
