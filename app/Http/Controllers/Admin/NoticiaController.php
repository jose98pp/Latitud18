<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Noticia;
use App\Models\Category;
use App\Http\Requests\NoticiaRequest;
use App\Services\ContentSanitizationService;
use App\Services\ImageValidationService;
use App\Services\ImageStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;



class NoticiaController extends Controller
{
    protected ContentSanitizationService $sanitizationService;
    protected ImageValidationService $imageValidationService;
    protected ImageStorageService $imageStorageService;
    
    public function __construct(
        ContentSanitizationService $sanitizationService,
        ImageValidationService $imageValidationService,
        ImageStorageService $imageStorageService
    ) {
        $this->sanitizationService = $sanitizationService;
        $this->imageValidationService = $imageValidationService;
        $this->imageStorageService = $imageStorageService;
    }
    public function index(Request $request)
    {
        $query = Noticia::with('category');
        
        // Calculate statistics before applying filters
        $statistics = $this->calculateNewsStatistics();
        
        // Filtro por búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titulo', 'LIKE', "%{$search}%")
                  ->orWhere('contenido', 'LIKE', "%{$search}%");
            });
        }
        
        // Filtro por categoría
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        
        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('publicada', $request->status);
        }
        
        // Get per_page parameter with validation
        $perPage = $request->get('per_page', 15);
        $allowedPerPage = [15, 25, 50, 100];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 15;
        }
        
        $news = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        // Mantener parámetros de búsqueda en la paginación
        $news->appends($request->query());
        
        // Add image information for each news item
        $news->getCollection()->transform(function ($noticia) {
            $noticia->image_info = $this->getImageInfoForAdmin($noticia->imagen);
            return $noticia;
        });
        
        // Obtener categorías para el filtro
        $categorias = \App\Models\Category::orderBy('name')->get();
        
        return view('admin.noticias.index', compact('news', 'categorias', 'statistics'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.noticias.create', compact('categories'));
    }

    public function show($id)
    {
        $noticia = Noticia::where('id', $id)
            ->where('publicada', true)
            ->firstOrFail();

        $imagenUrl = $noticia->imagenUrl;

        $noticias = Noticia::select('id', 'titulo', 'imagen', 'created_at')
            ->where('publicada', true)
            ->where('id', '!=', $id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $categorias = Category::all();

        return view('show', compact('noticia', 'noticias', 'categorias', 'imagenUrl'));
    }

  public function store(NoticiaRequest $request)
{
    try {
        $validatedData = $request->validated();

        // Validate content length
        $contentLengthError = $this->sanitizationService->validateContentLength(
            $validatedData['contenido'], 
            config('uhtv.security.max_content_length', 250000)
        );
        if ($contentLengthError) {
            return redirect()->back()
                ->withInput()
                ->with('error', $contentLengthError);
        }

        // Check for dangerous content and warn user
        $warnings = $this->sanitizationService->hasDangerousContent($validatedData['contenido']);
        if (!empty($warnings)) {
            Log::warning('Contenido peligroso detectado en noticia', [
                'warnings' => $warnings,
                'user_id' => auth()->id()
            ]);
        }

        // Enhanced image handling with category-based organization
        $imagePath = null;
        if ($request->hasFile('imagen')) {
            try {
                $imagePath = $this->imageStorageService->storeImageByCategory(
                    $request->file('imagen'),
                    $validatedData['category_id']
                );
                
                Log::info('Imagen subida exitosamente con organización por categoría', [
                    'path' => $imagePath,
                    'category_id' => $validatedData['category_id'],
                    'original_name' => $request->file('imagen')->getClientOriginalName(),
                    'user_id' => auth()->id()
                ]);
                
            } catch (\Exception $e) {
                Log::error('Error al subir imagen', [
                    'error' => $e->getMessage(),
                    'category_id' => $validatedData['category_id'],
                    'user_id' => auth()->id()
                ]);
                
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Error al subir la imagen: ' . $e->getMessage());
            }
        }

        $noticia = Noticia::create([
            'titulo' => strip_tags($validatedData['titulo']), // Sanitizar título
            'contenido' => $this->sanitizationService->sanitizeContent($validatedData['contenido']), // Sanitizar contenido
            'category_id' => $validatedData['category_id'],
            'user_id' => auth()->id(), // Asignar el usuario actual
            'imagen' => $imagePath,
            'video_youtube' => $this->getYouTubeVideoID($validatedData['video_youtube'] ?? null),
            'publicada' => $request->has('publicada'),
            'destacada_hero' => $request->has('destacada_hero'),
            'es_investigacion' => $request->has('es_investigacion'),
            'es_urgente' => $request->has('es_urgente'),
            'urgente_hasta' => $request->has('es_urgente') ? $request->input('urgente_hasta') : null,
            'publicar_en' => $request->filled('publicar_en') ? $request->input('publicar_en') : null,
        ]);

        // Procesar fotos adicionales de la galería
        $this->handleGalleryUploads($request, $noticia);

        Log::info('Noticia creada', ['noticia_id' => $noticia->id, 'user_id' => auth()->id()]);

        $successMessage = 'Noticia creada exitosamente.';
        if (!empty($warnings)) {
            $successMessage .= ' Nota: Se removió contenido potencialmente peligroso por seguridad.';
        }

        return redirect()->route('admin.noticias.index')
            ->with('success', $successMessage);
            
    } catch (\Exception $e) {
        Log::error('Error al crear noticia', ['error' => $e->getMessage(), 'user_id' => auth()->id()]);
        
        return redirect()->back()
            ->withInput()
            ->with('error', 'Error al crear la noticia. Por favor, inténtelo de nuevo.');
    }
}
    public function edit($id)
    {
        $noticia = Noticia::with('galeria')->findOrFail($id);
        $categories = Category::all();
        
        // Add image information for the edit view
        $noticia->image_info = $this->getImageInfoForAdmin($noticia->imagen);
        
        return view('admin.noticias.edit', compact('noticia', 'categories'));
    }

    public function update(NoticiaRequest $request, $id)
    {
        try {
            $validatedData = $request->validated();
            $noticia = Noticia::findOrFail($id);

            // Validate content length
            $contentLengthError = $this->sanitizationService->validateContentLength(
                $validatedData['contenido'], 
                config('uhtv.security.max_content_length', 250000)
            );
            if ($contentLengthError) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $contentLengthError);
            }

            // Check for dangerous content and warn user
            $warnings = $this->sanitizationService->hasDangerousContent($validatedData['contenido']);
            if (!empty($warnings)) {
                Log::warning('Contenido peligroso detectado en actualización de noticia', [
                    'warnings' => $warnings,
                    'noticia_id' => $id,
                    'user_id' => auth()->id()
                ]);
            }

            // Enhanced image handling with category-based organization
            if ($request->hasFile('imagen')) {
                try {
                    // Store new image with category organization
                    $newImagePath = $this->imageStorageService->storeImageByCategory(
                        $request->file('imagen'),
                        $validatedData['category_id']
                    );
                    
                    // Delete old image only after successful upload
                    if ($noticia->imagen) {
                        $this->imageStorageService->deleteImage($noticia->imagen);
                        Log::info('Imagen anterior eliminada', [
                            'old_path' => $noticia->imagen,
                            'noticia_id' => $id,
                            'user_id' => auth()->id()
                        ]);
                    }
                    
                    $noticia->imagen = $newImagePath;
                    
                    Log::info('Imagen actualizada exitosamente con organización por categoría', [
                        'new_path' => $newImagePath,
                        'category_id' => $validatedData['category_id'],
                        'original_name' => $request->file('imagen')->getClientOriginalName(),
                        'noticia_id' => $id,
                        'user_id' => auth()->id()
                    ]);
                    
                } catch (\Exception $e) {
                    Log::error('Error al actualizar imagen', [
                        'error' => $e->getMessage(),
                        'category_id' => $validatedData['category_id'],
                        'noticia_id' => $id,
                        'user_id' => auth()->id()
                    ]);
                    
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Error al actualizar la imagen: ' . $e->getMessage());
                }
            }

            $noticia->update([
                'titulo' => strip_tags($validatedData['titulo']), // Sanitizar título
                'contenido' => $this->sanitizationService->sanitizeContent($validatedData['contenido']), // Sanitizar contenido
                'category_id' => $validatedData['category_id'],
                'user_id' => auth()->id(), // Mantener el usuario actual
                'video_youtube' => $this->getYouTubeVideoID($validatedData['video_youtube'] ?? null),
                'publicada' => $request->has('publicada'),
                'destacada_hero' => $request->has('destacada_hero'),
                'es_investigacion' => $request->has('es_investigacion'),
                'es_urgente' => $request->has('es_urgente'),
                'urgente_hasta' => $request->has('es_urgente') ? $request->input('urgente_hasta') : null,
                'publicar_en' => $request->filled('publicar_en') ? $request->input('publicar_en') : null,
            ]);

            // Procesar nuevas fotos de galería adjuntadas
            $this->handleGalleryUploads($request, $noticia);

            // Actualizar pies de foto existentes si se enviaron
            if ($request->has('existing_galeria_pie')) {
                foreach ($request->input('existing_galeria_pie') as $fotoId => $pie) {
                    \App\Models\NoticiaFoto::where('id', $fotoId)
                        ->where('noticia_id', $noticia->id)
                        ->update(['pie_de_foto' => trim(strip_tags($pie))]);
                }
            }

            Log::info('Noticia actualizada', ['noticia_id' => $noticia->id, 'user_id' => auth()->id()]);

            $successMessage = 'Noticia actualizada exitosamente.';
            if (!empty($warnings)) {
                $successMessage .= ' Nota: Se removió contenido potencialmente peligroso por seguridad.';
            }

            return redirect()->route('admin.noticias.index')
                ->with('success', $successMessage);
                
        } catch (\Exception $e) {
            Log::error('Error al actualizar noticia', ['error' => $e->getMessage(), 'noticia_id' => $id, 'user_id' => auth()->id()]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar la noticia. Por favor, inténtelo de nuevo.');
        }
    }

    public function destroy($id)
    {
        try {
            $noticia = Noticia::findOrFail($id);

            // Enhanced image deletion with validation
            if ($noticia->imagen) {
                $deleted = $this->imageStorageService->deleteImage($noticia->imagen);
                
                if ($deleted) {
                    Log::info('Imagen eliminada con noticia', [
                        'image_path' => $noticia->imagen,
                        'noticia_id' => $id,
                        'user_id' => auth()->id()
                    ]);
                } else {
                    Log::warning('No se pudo eliminar imagen al eliminar noticia', [
                        'image_path' => $noticia->imagen,
                        'noticia_id' => $id,
                        'user_id' => auth()->id()
                    ]);
                }
            }

            $noticia->delete();
            
            Log::info('Noticia eliminada', ['noticia_id' => $id, 'user_id' => auth()->id()]);

            return redirect()->route('admin.noticias.index')
                ->with('success', 'Noticia eliminada exitosamente.');
                
        } catch (\Exception $e) {
            Log::error('Error al eliminar noticia', [
                'error' => $e->getMessage(),
                'noticia_id' => $id,
                'user_id' => auth()->id()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al eliminar la noticia. Por favor, inténtelo de nuevo.');
        }
    }

    private function getYouTubeVideoID($url)
    {
        if ($url && (Str::contains($url, 'youtube.com') || Str::contains($url, 'youtu.be'))) {
            preg_match('/(?:youtu\.be\/|v=|\/embed\/|\/shorts\/)([^\?&]+)/', $url, $matches);
            return $matches[1] ?? null;
        }
        return null;
    }

    /**
     * Get image information for admin display
     *
     * @param string|null $imagePath
     * @return array
     */
    private function getImageInfoForAdmin(?string $imagePath): array
    {
        return $this->imageValidationService->getImageInfo($imagePath);
    }

    /**
     * AJAX endpoint for dynamic filtering
     */
    public function filter(Request $request)
    {
        $query = Noticia::with('category');
        
        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titulo', 'LIKE', "%{$search}%")
                  ->orWhere('contenido', 'LIKE', "%{$search}%");
            });
        }
        
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        
        if ($request->filled('status')) {
            $query->where('publicada', $request->status);
        }
        
        $news = $query->orderBy('created_at', 'desc')->paginate(15);
        $news->appends($request->query());
        
        // Add image information
        $news->getCollection()->transform(function ($noticia) {
            $noticia->image_info = $this->getImageInfoForAdmin($noticia->imagen);
            return $noticia;
        });
        
        // Calculate filtered statistics
        $filteredStats = $this->calculateFilteredStatistics($request);
        
        return response()->json([
            'success' => true,
            'html' => view('admin.noticias.partials.news-cards', compact('news'))->render(),
            'pagination' => view('admin.noticias.partials.pagination', compact('news'))->render(),
            'statistics' => $filteredStats,
            'total_results' => $news->total()
        ]);
    }

    /**
     * Calculate statistics for filtered results
     */
    private function calculateFilteredStatistics(Request $request): array
    {
        $query = Noticia::query();
        
        // Apply same filters as main query
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titulo', 'LIKE', "%{$search}%")
                  ->orWhere('contenido', 'LIKE', "%{$search}%");
            });
        }
        
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        
        if ($request->filled('status')) {
            $query->where('publicada', $request->status);
        }
        
        $totalFiltered = $query->count();
        $publishedFiltered = (clone $query)->where('publicada', true)->count();
        $draftFiltered = (clone $query)->where('publicada', false)->count();
        
        return [
            'total' => $totalFiltered,
            'published' => $publishedFiltered,
            'drafts' => $draftFiltered,
            'published_percentage' => $totalFiltered > 0 ? round(($publishedFiltered / $totalFiltered) * 100, 1) : 0
        ];
    }

    /**
     * Calculate news statistics for the dashboard
     *
     * @return array
     */
    private function calculateNewsStatistics(): array
    {
        $totalNews = Noticia::count();
        $publishedNews = Noticia::where('publicada', true)->count();
        $draftNews = Noticia::where('publicada', false)->count();
        $totalCategories = Category::count();
        
        // Calculate news by category
        $newsByCategory = Noticia::select('category_id')
            ->selectRaw('COUNT(*) as count')
            ->with('category:id,name')
            ->groupBy('category_id')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category->name ?? 'Sin Categoría',
                    'count' => $item->count
                ];
            });
        
        // Calculate recent activity (last 7 days)
        $recentNews = Noticia::where('created_at', '>=', Carbon::now()->subDays(7))->count();
        
        return [
            'total' => $totalNews,
            'published' => $publishedNews,
            'drafts' => $draftNews,
            'categories' => $totalCategories,
            'by_category' => $newsByCategory,
            'recent' => $recentNews,
            'published_percentage' => $totalNews > 0 ? round(($publishedNews / $totalNews) * 100, 1) : 0
        ];
    }

    /**
     * Procesa la subida de fotografías adicionales para la fotogalería
     */
    private function handleGalleryUploads(Request $request, Noticia $noticia): void
    {
        if ($request->hasFile('galeria_fotos')) {
            $pies = $request->input('galeria_pie', []);
            $currentCount = $noticia->galeria()->count();
            
            foreach ($request->file('galeria_fotos') as $index => $foto) {
                if ($foto && $foto->isValid()) {
                    $path = $this->imageStorageService->storeImageByCategory($foto, $noticia->category_id);
                    $pie = isset($pies[$index]) ? trim(strip_tags($pies[$index])) : null;
                    
                    \App\Models\NoticiaFoto::create([
                        'noticia_id' => $noticia->id,
                        'image_path' => $path,
                        'pie_de_foto' => $pie,
                        'orden' => $currentCount + $index + 1,
                    ]);
                }
            }
        }
    }

    /**
     * Elimina una foto de la galería vía AJAX
     */
    public function deleteGalleryPhoto($photoId)
    {
        try {
            $foto = \App\Models\NoticiaFoto::findOrFail($photoId);
            if ($foto->image_path) {
                $this->imageStorageService->deleteImage($foto->image_path);
            }
            $foto->delete();

            return response()->json(['success' => true, 'message' => 'Fotografía eliminada de la galería']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar la fotografía: ' . $e->getMessage()], 500);
        }
    }
}
