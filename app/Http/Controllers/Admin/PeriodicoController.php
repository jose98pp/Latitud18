<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Noticia;
use App\Models\Category;
use App\Models\PeriodicoPlantilla;
use App\Models\PeriodicoEdicion;
use App\Models\PeriodicoPagina;
use App\Models\PeriodicoElemento;
use App\Services\PeriodicoPdfService;
use Illuminate\Support\Str;

class PeriodicoController extends Controller
{
    public function __construct(private PeriodicoPdfService $pdfService)
    {
    }

    /**
     * Muestra el editor visual de periódico digital
     */
    public function index(Request $request)
    {
        // 1. Obtener todas las ediciones desde la base de datos
        $edicionesModels = PeriodicoEdicion::with(['paginas.elementos'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Si no existe ninguna edición, crear la primera edición inicial a partir de la plantilla clásica
        if ($edicionesModels->isEmpty()) {
            $tpl = PeriodicoPlantilla::find('tpl_portada_clasica') ?: PeriodicoPlantilla::first();
            if ($tpl) {
                $primeraEdicion = $tpl->crearEdicion([
                    'numero_edicion' => 'Edición 142',
                    'fecha' => date('d \d\e F \d\e Y'),
                    'titulo' => 'Latitud 18 — Edición Semanal',
                    'subtitulo' => 'Información Sin Ruido',
                    'slogan' => 'El Periódico Digital de Santa Cruz',
                    'ciudad' => 'Santa Cruz de la Sierra',
                    'precio' => 'Bs 7,00',
                ]);
                $primeraEdicion->update(['publicada' => true, 'activa' => true, 'estado' => 'publicado']);
            }
            $edicionesModels = PeriodicoEdicion::with(['paginas.elementos'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $ediciones = $edicionesModels->map(fn($e) => $e->toEditorArray())->toArray();

        // 2. Determinar la edición seleccionada
        $selectedId = $request->query('edicion_id');
        $currentEdicion = null;

        if ($selectedId) {
            foreach ($ediciones as $ed) {
                if ($ed['id'] === $selectedId) {
                    $currentEdicion = $ed;
                    break;
                }
            }
        }

        if (!$currentEdicion) {
            foreach ($ediciones as $ed) {
                if (!empty($ed['activa'])) {
                    $currentEdicion = $ed;
                    break;
                }
            }
            if (!$currentEdicion && !empty($ediciones)) {
                $currentEdicion = $ediciones[0];
            }
        }

        // 3. Catálogo de plantillas oficiales para la biblioteca y creación
        $plantillasModels = PeriodicoPlantilla::orderBy('is_custom', 'asc')
            ->orderBy('nombre', 'asc')
            ->get();

        $categorias = Category::all();
        $noticiasPublicadas = Noticia::where('publicada', true)
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->limit(60)
            ->get();

        return view('admin.periodico.index', compact('ediciones', 'currentEdicion', 'plantillasModels', 'categorias', 'noticiasPublicadas'));
    }

    /**
     * Crea una nueva edición a partir de una plantilla o clonando una existente
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero_edicion' => 'required|string|max:100',
            'fecha' => 'required|string|max:100',
            'titulo' => 'nullable|string|max:255',
            'precio' => 'nullable|string|max:50',
            'plantilla_id' => 'nullable|string',
            'clonar_de' => 'nullable|string',
        ]);

        $edicionId = 'ed-' . time() . '-' . Str::random(5);

        // Caso 1: Clonar de una edición existente
        if (!empty($validated['clonar_de'])) {
            $origen = PeriodicoEdicion::with(['paginas.elementos'])->find($validated['clonar_de']);
            if ($origen) {
                $nuevaEdicion = PeriodicoEdicion::create([
                    'id' => $edicionId,
                    'numero_edicion' => $validated['numero_edicion'],
                    'fecha' => $validated['fecha'],
                    'titulo' => $validated['titulo'] ?: $origen->titulo,
                    'subtitulo' => $origen->subtitulo,
                    'slogan' => $origen->slogan,
                    'ciudad' => $origen->ciudad,
                    'precio' => $validated['precio'] ?: $origen->precio,
                    'num_paginas' => $origen->num_paginas,
                    'publicada' => false,
                    'activa' => false,
                    'estado' => 'borrador',
                    'plantilla_id' => $origen->plantilla_id,
                ]);

                foreach ($origen->paginas as $pOrig) {
                    $nuevaPagina = $nuevaEdicion->paginas()->create([
                        'plantilla_id' => $pOrig->plantilla_id,
                        'numero' => $pOrig->numero,
                        'nombre' => $pOrig->nombre,
                        'seccion' => $pOrig->seccion,
                        'ancho' => $pOrig->ancho,
                        'alto' => $pOrig->alto,
                        'fondo_color' => $pOrig->fondo_color,
                        'configuracion' => $pOrig->configuracion,
                    ]);

                    foreach ($pOrig->elementos as $elem) {
                        $nuevaPagina->elementos()->create([
                            'frame_id' => 'f-' . time() . '-' . Str::random(5),
                            'tipo' => $elem->tipo,
                            'x' => $elem->x,
                            'y' => $elem->y,
                            'w' => $elem->w,
                            'h' => $elem->h,
                            'z' => $elem->z,
                            'contenido' => $elem->contenido,
                            'propiedades' => $elem->propiedades,
                        ]);
                    }
                }

                $edicionArray = $nuevaEdicion->toEditorArray();

                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Nueva edición clonada exitosamente.',
                        'edicion' => $edicionArray,
                        'redirect_url' => route('admin.periodico.index', ['edicion_id' => $nuevaEdicion->id])
                    ]);
                }

                return redirect()->route('admin.periodico.index', ['edicion_id' => $nuevaEdicion->id])
                    ->with('success', 'Nueva edición clonada exitosamente.');
            }
        }

        // Caso 2: Crear a partir de una plantilla específica seleccionada
        $tplId = !empty($validated['plantilla_id']) ? $validated['plantilla_id'] : 'tpl_portada_clasica';
        $plantilla = PeriodicoPlantilla::find($tplId) ?: PeriodicoPlantilla::first();

        if ($plantilla) {
            $nuevaEdicion = $plantilla->crearEdicion([
                'numero_edicion' => $validated['numero_edicion'],
                'fecha' => $validated['fecha'],
                'titulo' => $validated['titulo'] ?? ('Latitud 18 — ' . $validated['numero_edicion']),
                'precio' => $validated['precio'] ?? 'Bs 7,00',
            ]);
        } else {
            $nuevaEdicion = PeriodicoEdicion::create([
                'id' => $edicionId,
                'numero_edicion' => $validated['numero_edicion'],
                'fecha' => $validated['fecha'],
                'titulo' => $validated['titulo'] ?? ('Latitud 18 — ' . $validated['numero_edicion']),
                'subtitulo' => 'Información Sin Ruido',
                'slogan' => 'El Periódico Digital de Santa Cruz',
                'ciudad' => 'Santa Cruz de la Sierra',
                'precio' => $validated['precio'] ?? 'Bs 7,00',
                'num_paginas' => 1,
                'publicada' => false,
                'activa' => false,
                'estado' => 'borrador',
            ]);
            $nuevaEdicion->paginas()->create([
                'numero' => 1,
                'nombre' => 'Página 1: Portada',
                'seccion' => 'Portada',
                'ancho' => 720,
                'alto' => 1040,
                'fondo_color' => '#ffffff',
            ]);
        }

        $edicionArray = $nuevaEdicion->toEditorArray();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Nueva edición creada exitosamente desde plantilla.',
                'edicion' => $edicionArray,
                'redirect_url' => route('admin.periodico.index', ['edicion_id' => $nuevaEdicion->id])
            ]);
        }

        return redirect()->route('admin.periodico.index', ['edicion_id' => $nuevaEdicion->id])
            ->with('success', 'Nueva edición semanal creada exitosamente.');
    }

    /**
     * Crea una nueva edición directamente desde una plantilla con 1 clic
     */
    public function createEditionFromTemplate(Request $request, $templateId)
    {
        $plantilla = PeriodicoPlantilla::findOrFail($templateId);

        $numero = $request->input('numero_edicion', 'Edición ' . rand(110, 990));
        $fecha = $request->input('fecha', date('d \d\e F \d\e Y'));

        $edicion = $plantilla->crearEdicion([
            'numero_edicion' => $numero,
            'fecha' => $fecha,
            'titulo' => 'Latitud 18 — ' . $numero,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Edición "' . $numero . '" creada desde la plantilla "' . $plantilla->nombre . '".',
                'edicion' => $edicion->toEditorArray(),
                'redirect_url' => route('admin.periodico.index', ['edicion_id' => $edicion->id])
            ]);
        }

        return redirect()->route('admin.periodico.index', ['edicion_id' => $edicion->id])
            ->with('success', 'Edición creada a partir de la plantilla "' . $plantilla->nombre . '". Ahora puedes editar los textos y fotos sin alterar la plantilla original.');
    }

    /**
     * Actualiza el contenido completo de una edición (páginas, textos, widgets, imágenes) en la BD
     */
    public function update(Request $request, $id)
    {
        $edicion = PeriodicoEdicion::find($id);

        if (!$edicion) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Edición no encontrada.'], 404);
            }
            return redirect()->route('admin.periodico.index')->with('error', 'Edición no encontrada.');
        }

        $data = $request->json()->all() ?: $request->all();

        $edicion->syncFromEditorData($data);

        $edicionArray = $edicion->toEditorArray();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Edición guardada correctamente en la base de datos.',
                'edicion' => $edicionArray
            ]);
        }

        return redirect()->route('admin.periodico.index', ['edicion_id' => $id])
            ->with('success', 'Edición guardada exitosamente.');
    }

    /**
     * Publica una edición y la marca como la edición semanal activa en el portal
     */
    public function publish(Request $request, $id)
    {
        $edicion = PeriodicoEdicion::find($id);

        if (!$edicion) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Edición no encontrada.'], 404);
            }
            return redirect()->route('admin.periodico.index')->with('error', 'Edición no encontrada.');
        }

        // Desactivar cualquier otra edición activa
        PeriodicoEdicion::where('id', '!=', $id)->update(['activa' => false]);

        $edicion->update([
            'publicada' => true,
            'activa' => true,
            'estado' => 'publicado',
            'fecha_publicacion' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => '¡Edición semanal publicada con éxito en la página web!'
            ]);
        }

        return redirect()->route('admin.periodico.index', ['edicion_id' => $id])
            ->with('success', '¡Edición semanal publicada con éxito en la página web!');
    }

    /**
     * Actualiza el estado editorial de la edición (Flujo de trabajo de 5 estados)
     * Borrador -> En Revisión -> Aprobado -> Programado -> Publicado
     */
    public function updateEstado(Request $request, $id)
    {
        $validated = $request->validate([
            'estado' => 'required|string|in:borrador,revision,aprobado,programado,publicado',
            'fecha_programada' => 'nullable|string'
        ]);

        $edicion = PeriodicoEdicion::find($id);

        if (!$edicion) {
            return response()->json(['success' => false, 'message' => 'Edición no encontrada.'], 404);
        }

        $edicion->estado = $validated['estado'];
        $edicion->fecha_programada = !empty($validated['fecha_programada']) ? $validated['fecha_programada'] : null;

        if ($validated['estado'] === 'publicado') {
            PeriodicoEdicion::where('id', '!=', $id)->update(['activa' => false]);
            $edicion->publicada = true;
            $edicion->activa = true;
            $edicion->fecha_publicacion = now();
        } elseif ($validated['estado'] === 'programado') {
            $edicion->publicada = false;
            $edicion->activa = false;
        }

        $edicion->save();

        return response()->json([
            'success' => true,
            'message' => 'Estado editorial actualizado a: ' . ucfirst($validated['estado']),
            'estado' => $validated['estado'],
            'fecha_programada' => $edicion->fecha_programada ? $edicion->fecha_programada->toISOString() : null,
            'edicion' => $edicion->toEditorArray()
        ]);
    }

    /**
     * Elimina una edición de la base de datos
     */
    public function destroy($id)
    {
        $edicion = PeriodicoEdicion::find($id);
        if ($edicion) {
            $edicion->delete(); // Elimina en cascada páginas y elementos
        }

        return redirect()->route('admin.periodico.index')
            ->with('success', 'Edición eliminada correctamente.');
    }

    /**
     * Subida de imágenes para cualquier marco del periódico
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'periodico_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();

            $destDir = public_path('images/periodico');
            if (!file_exists($destDir)) {
                mkdir($destDir, 0755, true);
            }

            $file->move($destDir, $filename);

            $publicUrl = asset('images/periodico/' . $filename);

            return response()->json([
                'success' => true,
                'url' => $publicUrl,
                'filename' => $filename,
                'message' => 'Imagen subida exitosamente.'
            ]);
        }

        return response()->json(['success' => false, 'message' => 'No se recibió ningún archivo de imagen válido.'], 400);
    }

    /**
     * Subida de archivo PDF completo para la edición
     */
    public function uploadPdf(Request $request, $id)
    {
        $request->validate([
            'pdf_file' => 'required|mimes:pdf|max:51200',
        ]);

        if ($request->hasFile('pdf_file')) {
            $file = $request->file('pdf_file');
            $filename = 'edicion_' . $id . '_' . time() . '.pdf';

            $destDir = public_path('ediciones_pdf');
            if (!file_exists($destDir)) {
                mkdir($destDir, 0755, true);
            }

            $file->move($destDir, $filename);
            $publicUrl = asset('ediciones_pdf/' . $filename);

            $edicion = PeriodicoEdicion::find($id);
            if ($edicion) {
                $edicion->update(['pdf_url' => $publicUrl]);
            }

            return response()->json([
                'success' => true,
                'pdf_url' => $publicUrl,
                'message' => 'Archivo PDF oficial guardado y vinculado a la edición.'
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Error al subir el archivo PDF.'], 400);
    }

    /**
     * Generación y descarga directa del PDF real de la edición
     */
    public function generatePdf($id)
    {
        $edicion = PeriodicoEdicion::with(['paginas.elementos'])->find($id);

        if (!$edicion) {
            return redirect()->route('admin.periodico.index')->with('error', 'Edición no encontrada.');
        }

        try {
            return $this->pdfService->descarga($edicion->toEditorArray());
        } catch (\Throwable $e) {
            \Log::error('Error al generar PDF: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('admin.periodico.index', ['edicion_id' => $id])
                ->with('error', 'No se pudo generar el PDF: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene noticias de una categoría específica para el panel InDesign
     */
    public function getNoticiasByCategory($categoryId)
    {
        $query = Noticia::where('publicada', true)->with('category');

        if ($categoryId !== 'all') {
            $query->where('category_id', $categoryId);
        }

        $noticias = $query->orderBy('created_at', 'desc')->limit(30)->get();

        return response()->json([
            'success' => true,
            'noticias' => $noticias->map(function ($noticia) {
                return [
                    'id' => $noticia->id,
                    'titulo' => $noticia->titulo,
                    'subtitulo' => $noticia->subtitulo ?? '',
                    'categoria' => $noticia->category->name ?? 'General',
                    'categoria_color' => $noticia->category->color ?? '#D71920',
                    'fecha' => $noticia->created_at->format('d/m/Y'),
                    'imagen' => $noticia->imagen ? asset('storage/' . $noticia->imagen) : null,
                    'contenido_limpio' => Str::limit(strip_tags($noticia->contenido), 450),
                    'autor' => $noticia->autor ?? ($noticia->user->name ?? 'Redacción')
                ];
            })
        ]);
    }

    /**
     * Devuelve el catálogo completo de plantillas en formato JSON para el editor
     */
    public function getTemplates()
    {
        $templates = $this->getAllTemplates();
        return response()->json([
            'success' => true,
            'templates' => $templates
        ]);
    }

    /**
     * Lee las plantillas guardadas en la base de datos
     */
    public function getAllTemplates(): array
    {
        $plantillas = PeriodicoPlantilla::orderBy('is_custom', 'asc')
            ->orderBy('nombre', 'asc')
            ->get();

        return $plantillas->map(function ($t) {
            return [
                'id' => $t->id,
                'name' => $t->nombre,
                'category' => $t->categoria,
                'description' => $t->descripcion ?? '',
                'preview_color' => $t->preview_color ?? '#1e293b',
                'is_custom' => (bool)$t->is_custom,
                'frames' => is_array($t->frames) ? $t->frames : (json_decode($t->frames, true) ?: []),
                'configuracion' => $t->configuracion,
                'created_at' => $t->created_at ? $t->created_at->toISOString() : now()->toISOString(),
            ];
        })->toArray();
    }

    /**
     * Guarda una nueva plantilla personalizada a partir de la página actual
     */
    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'category' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
            'frames' => 'required|array',
            'preview_color' => 'nullable|string|max:30',
        ]);

        $tplId = 'tpl_custom_' . time() . '_' . Str::random(5);

        $plantilla = PeriodicoPlantilla::create([
            'id' => $tplId,
            'nombre' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'categoria' => $validated['category'],
            'descripcion' => $validated['description'] ?? '',
            'preview_color' => $validated['preview_color'] ?? '#1e293b',
            'frames' => $validated['frames'],
            'is_custom' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plantilla "' . $plantilla->nombre . '" guardada en la base de datos.',
            'template' => [
                'id' => $plantilla->id,
                'name' => $plantilla->nombre,
                'category' => $plantilla->categoria,
                'description' => $plantilla->descripcion,
                'preview_color' => $plantilla->preview_color,
                'frames' => $plantilla->frames,
                'is_custom' => true,
            ]
        ]);
    }

    /**
     * Importa una plantilla desde archivo .latitud-template o JSON subido
     */
    public function importTemplate(Request $request)
    {
        $request->validate([
            'template_file' => 'required|file|max:10240',
        ]);

        $file = $request->file('template_file');
        $content = file_get_contents($file->getRealPath());
        $data = json_decode($content, true);

        if (!$data || (!isset($data['frames']) && !isset($data['template']['frames']))) {
            return response()->json(['success' => false, 'message' => 'El archivo no contiene una estructura válida de plantilla .latitud-template.'], 422);
        }

        $templateData = isset($data['template']) ? $data['template'] : $data;
        $tplId = 'tpl_imp_' . time() . '_' . Str::random(5);

        $plantilla = PeriodicoPlantilla::create([
            'id' => $tplId,
            'nombre' => ($templateData['name'] ?? 'Plantilla Importada') . ' (Importada)',
            'slug' => Str::slug($templateData['name'] ?? 'importada'),
            'categoria' => $templateData['category'] ?? 'general',
            'descripcion' => $templateData['description'] ?? 'Plantilla importada desde archivo externo',
            'preview_color' => $templateData['preview_color'] ?? '#0284c7',
            'frames' => $templateData['frames'] ?? [],
            'is_custom' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => '¡Plantilla "' . $plantilla->nombre . '" importada exitosamente!',
            'template' => [
                'id' => $plantilla->id,
                'name' => $plantilla->nombre,
                'category' => $plantilla->categoria,
                'description' => $plantilla->descripcion,
                'preview_color' => $plantilla->preview_color,
                'frames' => $plantilla->frames,
                'is_custom' => true,
            ]
        ]);
    }

    /**
     * Elimina una plantilla personalizada de la base de datos
     */
    public function deleteTemplate($id)
    {
        $tpl = PeriodicoPlantilla::find($id);
        if ($tpl) {
            $tpl->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Plantilla eliminada de la biblioteca.'
        ]);
    }

    /**
     * Helper de compatibilidad para obtener todas las ediciones en array
     */
    public function getAllEdiciones(): array
    {
        return PeriodicoEdicion::with(['paginas.elementos'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($e) => $e->toEditorArray())
            ->toArray();
    }
}