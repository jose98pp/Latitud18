<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Noticia;
use App\Models\Category;
use App\Services\ContentSanitizationService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected ContentSanitizationService $sanitizationService;
    
    public function __construct(ContentSanitizationService $sanitizationService)
    {
        $this->sanitizationService = $sanitizationService;
    }
    public function index()
    {
        // Obtener las categorías paginadas (10 por página)
        
        $categories = Category::all();
        $categories = Category::paginate(10);
        // Retornar la vista con las categorías
        return view('admin.categorias.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categorias.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'descripcion' => 'nullable|string',
        ]);

        // Validate content length if descripcion is provided
        if ($request->descripcion) {
            $contentLengthError = $this->sanitizationService->validateContentLength($request->descripcion, 10000);
            if ($contentLengthError) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $contentLengthError);
            }

            // Check for dangerous content
            $warnings = $this->sanitizationService->hasDangerousContent($request->descripcion);
            if (!empty($warnings)) {
                \Log::warning('Contenido peligroso detectado en categoría', [
                    'warnings' => $warnings,
                    'user_id' => auth()->id()
                ]);
            }
        }

        $categories = new Category();
        $categories->name = $request->name;
        $categories->descripcion = $request->descripcion ? 
            $this->sanitizationService->sanitizeContent($request->descripcion) : null;
        $categories->save();

        $successMessage = 'Categoría creada con éxito.';
        if ($request->descripcion && !empty($warnings ?? [])) {
            $successMessage .= ' Nota: Se removió contenido potencialmente peligroso por seguridad.';
        }

        return redirect()->route('admin.categorias.index')->with('success', $successMessage);
    }

    public function edit(string $id)
    {
        $category = Category::findOrFail($id); // Cambiar $categories a $category
        return view('admin.categorias.edit', compact('category'));
    }


    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
            'descripcion' => 'nullable|string',
        ]);

        // Validate content length if descripcion is provided
        if ($request->descripcion) {
            $contentLengthError = $this->sanitizationService->validateContentLength($request->descripcion, 10000);
            if ($contentLengthError) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $contentLengthError);
            }

            // Check for dangerous content
            $warnings = $this->sanitizationService->hasDangerousContent($request->descripcion);
            if (!empty($warnings)) {
                \Log::warning('Contenido peligroso detectado en actualización de categoría', [
                    'warnings' => $warnings,
                    'category_id' => $id,
                    'user_id' => auth()->id()
                ]);
            }
        }

        $categories = Category::findOrFail($id);
        $categories->name = $request->name;
        $categories->descripcion = $request->descripcion ? 
            $this->sanitizationService->sanitizeContent($request->descripcion) : null;
        $categories->save();

        $successMessage = 'Categoría actualizada con éxito.';
        if ($request->descripcion && !empty($warnings ?? [])) {
            $successMessage .= ' Nota: Se removió contenido potencialmente peligroso por seguridad.';
        }

        return redirect()->route('admin.categorias.index')->with('success', $successMessage);
    }

    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);

        if ($category->noticias()->exists()) { // Verificar si hay noticias asociadas
        return redirect()->route('admin.categorias.index')->withErrors('No se puede eliminar la categoría porque tiene noticias asociadas.');
    }

        $category->delete();
        return redirect()->route('admin.categorias.index')->with('success', 'Categoría eliminada con éxito.');
    }  
    
     public function noticiasPorCategoria($id)
    {
        // Obtener la categoría con sus noticias
        $categoria = Category::with(['noticias' => function ($query) {
            $query->where('publicada', true)->orderBy('created_at', 'desc');
        }])->findOrFail($id);
        // Obtener todas las categorías para la barra de navegación
        $categorias = Category::all();
        // Pasar la categoría y sus noticias a la vista
        return view('categoria.noticias', compact('categoria'));
    }



}
